"""Exercise real forms against a temporary PHP server and isolated MySQL database."""
from datetime import date, timedelta
import base64
import html
import http.cookiejar
import json
import os
from pathlib import Path
import re
import secrets
import socket
import subprocess
import tempfile
import time
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('PHP_BINARY', 'php')
env = dict(os.environ, SOLARIS_DB_NAME='solaris_workflow_http_' + secrets.token_hex(5))
flags = subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0

def fixture(action):
    return subprocess.check_output([PHP, str(ROOT / 'tests/workflow-http-fixture.php'), action], cwd=ROOT, env=env, creationflags=flags, text=True)

def client():
    return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

def request(browser, page, data=None):
    if data and data.get('action') == 'submit_stage':
        data = dict(data)
        data.setdefault('next_start_date', today)
    body = urllib.parse.urlencode(data, doseq=True).encode() if data is not None else None
    response = browser.open(base + '/index.php?' + page, body, timeout=15)
    text = response.read().decode('utf-8')
    assert response.status == 200, response.status
    assert not re.search(r'<b>(?:Warning|Fatal error|Notice|Deprecated)</b>', text), text[:300]
    return text, response.url

def field(text, name):
    match = re.search(r'<input\b[^>]*name="' + re.escape(name) + r'"[^>]*value="([^"]*)"', text)
    assert match, 'Missing field: ' + name
    return html.unescape(match.group(1))

def state():
    return json.loads(fixture('state'))

def login(browser, user):
    request(browser, 'page=auth', {'auth_action': 'login', 'identity': user, 'password': 'WorkflowTest!2026'})

def register(browser, source):
    page = 'page=calculator&source=' + source
    text, _ = request(browser, page)
    data = dict(csrf=field(text, 'csrf'), request_key=field(text, 'request_key'), calc_source=source,
                monthly_bill='3000000', roof_area='100', package='residential', roof_type='metal_roof',
                customer_name='HTTP survey', customer_phone='0901234567', customer_email='http@example.com',
                address='HTTP installation address', action='confirm_to_install')
    result = request(browser, page, data)
    return data, result

def start_current(browser, page):
    text, _ = request(browser, page)
    assert not re.search(r'<button\b[^>]*disabled[^>]*>\s*Bắt đầu', text), 'Start button must allow early execution'
    assert 'Bắt đầu giai đoạn' in text or 'Bắt đầu dự án &amp; khảo sát' in text or 'Bắt đầu dự án & khảo sát' in text
    return request(browser, page, {'csrf': field(text, 'csrf'), 'stage_id': field(text, 'stage_id'), 'action': 'start_stage'})

def upload_evidence(browser, page, text):
    boundary = '----SolarisTest' + secrets.token_hex(8)
    image = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN1cAAAAASUVORK5CYII=')
    chunks = []
    for key, value in {'csrf': field(text, 'csrf'), 'action': 'upload', 'document_type': 'stage_photo'}.items():
        chunks.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
    chunks.append(f'--{boundary}\r\nContent-Disposition: form-data; name="document"; filename="evidence.png"\r\nContent-Type: image/png\r\n\r\n'.encode() + image + b'\r\n')
    chunks.append(f'--{boundary}--\r\n'.encode())
    upload = urllib.request.Request(base + '/index.php?' + page, b''.join(chunks), {'Content-Type': 'multipart/form-data; boundary=' + boundary})
    return browser.open(upload, timeout=15).read().decode('utf-8')

server = None
log = tempfile.TemporaryFile(mode='w+b')
checks = 0
try:
    fixture('init')
    today = state()['today']
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        port = sock.getsockname()[1]
    base = 'http://127.0.0.1:' + str(port)
    server = subprocess.Popen([PHP, '-d', 'display_errors=1', '-S', '127.0.0.1:' + str(port), '-t', str(ROOT)], cwd=ROOT, env=env, stdout=log, stderr=log, creationflags=flags)
    guest, customer, admin, technician, other = [client() for _ in range(5)]
    for _ in range(50):
        try:
            request(guest, 'page=calculator')
            break
        except OSError:
            time.sleep(0.1)
    else:
        raise AssertionError('PHP server did not start')
    register(guest, 'bill')
    assert not state()['quotes']; checks += 1
    for browser, user in [(customer, 'http2'), (admin, 'http1'), (technician, 'http3'), (other, 'http4')]:
        login(browser, user)
    data, (_, url) = register(customer, 'bill')
    assert 'page=installations&project=' in url; checks += 1
    first = state()['projects'][0]
    assert first['status'] == 'pending_approval' and first['current_stage'] is None; checks += 1
    request(customer, 'page=calculator&source=bill', data)
    assert len(state()['projects']) == 1 and len(state()['quotes']) == 1; checks += 1
    text, _ = request(other, 'page=calculator')
    assert 'HTTP installation address' not in text; checks += 1
    text, _ = request(other, 'page=installations&project=' + str(first['id']))
    assert 'Bạn không có quyền xem dự án này' in text; checks += 1
    page = 'page=installations&project=' + str(first['id'])
    text, _ = request(admin, page)
    assert 'Duyệt thành dự án mới' in text and 'Chọn kỹ thuật viên' not in text; checks += 1
    request(admin, page, {'csrf': 'bad', 'action': 'approve_project'})
    assert state()['projects'][0]['status'] == 'pending_approval'; checks += 1
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'approve_project', 'review_note': 'Approved HTTP'})
    text, _ = request(admin, page)
    assert 'Chọn kỹ thuật viên' in text; checks += 1
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'assign', 'start_date': (date.fromisoformat(today) + timedelta(days=1)).isoformat(), 'end_date': (date.fromisoformat(today) + timedelta(days=14)).isoformat(), 'technicians[]': ['3'], 'hours_per_day': '4'})
    assert state()['projects'][0]['status'] == 'scheduled' and state()['projects'][0]['started_at'] is None; checks += 1
    start_current(technician, page)
    assert state()['projects'][0]['status'] == 'in_progress' and state()['projects'][0]['started_at'] is not None; checks += 1
    survey = next(stage for stage in state()['stages'] if stage['project_id'] == first['id'] and stage['stage'] == 'survey')
    assert survey['planned_start'] > today and survey['started_at'][:10] == today; checks += 1
    text, _ = request(admin, page)
    assert 'Phát sinh vật tư' in text and 'tv4-material-dialog' in text; checks += 1
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'add_material', 'product_id': '1', 'quantity': '2', 'material_reason': 'Extra cable for HTTP test', 'unit_price': '1'})
    assert state()['materials'][0]['unit_price'] == '2000000.00'; checks += 1
    text, _ = request(customer, page)
    balance = field(text, 'expected_remaining')
    request(customer, page, {'csrf': field(text, 'csrf'), 'action': 'pay_project', 'expected_remaining': balance, 'confirm_simulation': '1'})
    assert len(state()['payments']) == 1; checks += 1
    request(customer, page, {'csrf': field(text, 'csrf'), 'action': 'pay_project', 'expected_remaining': balance, 'confirm_simulation': '1'})
    assert len(state()['payments']) == 1; checks += 1
    text, _ = request(technician, page)
    assert 'Tải minh chứng' in text and 'Yêu cầu duyệt giai đoạn' in text; checks += 1

    text = upload_evidence(technician, page, text)
    assert len(state()['documents']) == 1; checks += 1
    request(technician, page, {'csrf': field(text, 'csrf'), 'action': 'submit_stage'})
    text, _ = request(admin, page)
    assert 'Duyệt hoàn thành &amp; lịch bước tiếp theo' in text or 'Duyệt hoàn thành & lịch bước tiếp theo' in text; checks += 1
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'approve_stage'})
    assert state()['projects'][0]['current_stage'] == 'design'; checks += 1
    design = next(stage for stage in state()['stages'] if stage['project_id'] == first['id'] and stage['stage'] == 'design')
    assert design['status'] == 'scheduled' and design['started_at'] is None; checks += 1
    start_current(technician, page)
    text, _ = request(customer, page + '&invoice=1')
    assert 'Chỉ in hóa đơn từ bước' in text; checks += 1
    # Đã thanh toán trước đó, nhưng phát sinh mới phải được trả đủ trước khi qua bước 3.
    text, _ = request(admin, page)
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'add_material', 'product_id': '2', 'quantity': '1', 'material_reason': 'Extra inverter before procurement'})
    text, _ = request(technician, page)
    text = upload_evidence(technician, page, text)
    request(technician, page, {'csrf': field(text, 'csrf'), 'action': 'submit_stage'})
    text, _ = request(admin, page)
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'approve_stage'})
    assert state()['projects'][0]['current_stage'] == 'procurement'; checks += 1
    start_current(technician, page)
    text, _ = request(customer, page + '&invoice=1')
    assert 'SOLARIS — HÓA ĐƠN DỰ ÁN' in text and 'window.print()' in text and 'Extra cable for HTTP test' in text; checks += 1
    text, _ = request(other, page + '&invoice=1')
    assert 'Bạn không có quyền xem dự án này' in text; checks += 1
    text, _ = request(technician, page)
    text = upload_evidence(technician, page, text)
    request(technician, page, {'csrf': field(text, 'csrf'), 'action': 'submit_stage'})
    text, _ = request(admin, page)
    approve_button = r'<button(?=[^>]*name="action")(?=[^>]*value="approve_stage")(?=[^>]*disabled)[^>]*>'
    assert re.search(approve_button, text) and 'Còn thiếu 10.000.000 đ' in text; checks += 1
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'approve_stage'})
    assert state()['projects'][0]['current_stage'] == 'procurement'; checks += 1
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'reject_stage', 'review_note': 'Need clearer evidence'})
    text, _ = request(technician, page)
    assert 'Need clearer evidence' in text; checks += 1
    request(technician, page, {'csrf': field(text, 'csrf'), 'action': 'submit_stage'})
    text, _ = request(customer, page)
    request(customer, page, {'csrf': field(text, 'csrf'), 'action': 'pay_project', 'expected_remaining': field(text, 'expected_remaining'), 'confirm_simulation': '1'})
    text, _ = request(admin, page)
    assert not re.search(approve_button, text); checks += 1
    request(admin, page, {'csrf': field(text, 'csrf'), 'action': 'approve_stage'})
    assert state()['projects'][0]['current_stage'] == 'installation'; checks += 1
    installation = next(stage for stage in state()['stages'] if stage['project_id'] == first['id'] and stage['stage'] == 'installation')
    assert installation['status'] == 'scheduled' and installation['started_at'] is None; checks += 1

    for product in [1, 2]:
        request(customer, 'page=products', {'cart_action': 'add', 'product_id': product})
    request(customer, 'page=cart', {'cart_action': 'demo_selected', 'selected[]': ['1', '2']})
    data, _ = register(customer, 'plan')
    snapshot = state()
    assert len(snapshot['projects']) == 1 and snapshot['quotes'][-1]['status'] == 'pending_approval'; checks += 1
    quote = snapshot['quotes'][-1]
    text, _ = request(admin, 'page=calculator')
    assert 'Duyệt phương án &amp; tạo yêu cầu dự án' in text or 'Duyệt phương án & tạo yêu cầu dự án' in text; checks += 1
    request(customer, 'page=calculator', {'csrf': data['csrf'], 'action': 'approve_quote', 'quote_id': quote['id']})
    assert state()['quotes'][-1]['status'] == 'pending_approval'; checks += 1
    request(admin, 'page=calculator', {'csrf': field(text, 'csrf'), 'action': 'approve_quote', 'quote_id': quote['id'], 'review_note': 'Configuration approved'})
    second = state()['projects'][-1]
    assert second['status'] == 'pending_approval'; checks += 1
    page2 = 'page=installations&project=' + str(second['id'])
    text, _ = request(admin, page2)
    request(admin, page2, {'csrf': field(text, 'csrf'), 'action': 'reject_project', 'review_note': 'Outside service area'})
    text, _ = request(customer, page2)
    assert 'Outside service area' in text and 'Chọn kỹ thuật viên' not in text; checks += 1
    log.flush(); log.seek(0)
    errors = log.read().decode('utf-8', errors='replace')
    assert not re.search(r'PHP (?:Warning|Fatal error|Parse error|Deprecated)', errors), errors[-3000:]
    print(f'PASS: {checks} HTTP checks; both registration paths, real upload, CSRF and role isolation')
finally:
    if server:
        server.terminate()
        server.wait(timeout=10)
    try:
        for path in state()['documents']:
            target = (ROOT / path).resolve()
            upload_root = (ROOT / 'assets/uploads/installation').resolve()
            if target.parent == upload_root:
                target.unlink(missing_ok=True)
    finally:
        fixture('drop')
    log.close()
