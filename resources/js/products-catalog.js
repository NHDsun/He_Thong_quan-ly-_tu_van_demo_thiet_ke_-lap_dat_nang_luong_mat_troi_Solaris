(() => {
    const feedback = message => {
        let box = document.querySelector('[data-products-feedback]');
        if (!box) { box = document.createElement('div'); box.className = 'products-notice'; box.setAttribute('role', 'status'); document.querySelector('.products-page')?.prepend(box); }
        box.textContent = message;
    };
    document.querySelectorAll('[data-add-to-plan]').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.dataset.submitting === '1') return;
        const button = form.querySelector('button[type="submit"]');
        const original = button.textContent;
        form.dataset.submitting = '1'; button.disabled = true; button.textContent = 'Đang thêm...';
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: { Accept: 'application/json' } });
            let data;
            try { data = await response.json(); }
            catch { throw new Error('Không thể đọc phản hồi máy chủ. Vui lòng tải lại trang và thử lại.'); }
            if (!response.ok || !data.ok) throw new Error(data.message || 'Không thể thêm sản phẩm. Vui lòng thử lại.');
            document.querySelectorAll('.cart-page-button span').forEach(count => { count.textContent = String(data.count); });
            feedback(data.message); button.textContent = 'Đã thêm ✓';
        } catch (error) { feedback(error instanceof TypeError ? 'Không thể kết nối máy chủ. Vui lòng thử lại.' : error.message); }
        finally { window.setTimeout(() => { button.textContent = original; button.disabled = false; form.dataset.submitting = '0'; }, 700); }
    }));

    const tray = document.querySelector('[data-compare-tray]');
    if (!tray) return;
    const key = 'solaris-tv2-compare:' + location.pathname;
    let selected = [];
    try { const saved = JSON.parse(localStorage.getItem(key) || '[]'); if (Array.isArray(saved)) selected = saved.filter(item => Number.isInteger(item.id) && typeof item.name === 'string' && typeof item.category === 'string').slice(0, 3); } catch {}
    const render = () => {
        try { localStorage.setItem(key, JSON.stringify(selected)); } catch {}
        tray.replaceChildren(); tray.hidden = !selected.length;
        const caption = document.createElement('strong'); caption.textContent = `So sánh thiết bị (${selected.length}/3)`; tray.append(caption);
        selected.forEach(item => {
            const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'products-filter-chip'; remove.textContent = item.name + ' ×'; remove.setAttribute('aria-label', 'Bỏ so sánh ' + item.name);
            remove.addEventListener('click', () => { selected = selected.filter(value => value.id !== item.id); render(); }); tray.append(remove);
        });
        const params = new URLSearchParams({ page: 'products', action: 'compare' }); selected.forEach(item => params.append('ids[]', String(item.id)));
        const open = document.createElement('a'); open.className = 'sol-btn sol-btn--solar'; open.textContent = 'Xem bảng so sánh →'; open.href = 'index.php?' + params;
        open.setAttribute('aria-disabled', String(selected.length < 2)); open.addEventListener('click', event => { if (selected.length < 2) { event.preventDefault(); feedback('Chọn thêm một sản phẩm cùng danh mục để so sánh.'); } }); tray.append(open);
        const clear = document.createElement('button'); clear.type = 'button'; clear.className = 'sol-btn sol-btn--outline'; clear.textContent = 'Bỏ tất cả'; clear.addEventListener('click', () => { selected = []; render(); }); tray.append(clear);
        document.querySelectorAll('[data-compare-product]').forEach(button => { const item = JSON.parse(button.dataset.compareProduct); const active = selected.some(value => value.id === item.id); button.setAttribute('aria-pressed', String(active)); button.textContent = active ? '✓ Đang so sánh' : '＋ So sánh sản phẩm'; });
    };
    document.querySelectorAll('[data-compare-product]').forEach(button => button.addEventListener('click', () => {
        const item = JSON.parse(button.dataset.compareProduct);
        if (selected.some(value => value.id === item.id)) selected = selected.filter(value => value.id !== item.id);
        else if (selected.length && selected[0].category !== item.category) { feedback('Chỉ so sánh sản phẩm cùng danh mục. Hãy bỏ danh sách cũ để chọn danh mục khác.'); return; }
        else if (selected.length >= 3) { feedback('Có thể so sánh tối đa 3 sản phẩm.'); return; }
        else selected.push(item);
        render();
    }));
    render();
})();
