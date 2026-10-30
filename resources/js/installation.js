(() => {
    const form = document.querySelector('#tv4-assignment-form');
    if (!form) return;
    const key = `solaris-crew-${form.dataset.project}`;
    let selected = {};
    try { selected = JSON.parse(sessionStorage.getItem(key) || '{}'); } catch (_) { /* Storage không khả dụng. */ }
    const boxes = [...form.querySelectorAll('input[name="technicians[]"]')];
    const update = () => {
        form.querySelectorAll('[data-persisted]').forEach(node => node.remove());
        const visible = new Set(boxes.map(box => box.value));
        Object.keys(selected).forEach(id => {
            if (visible.has(id)) return;
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'technicians[]'; input.value = id;
            input.dataset.persisted = '1'; form.append(input);
        });
        document.querySelector('#tv4-selected').textContent = Object.keys(selected).length
            ? `Đã chọn ${Object.keys(selected).length} người: ${Object.values(selected).join(', ')}`
            : 'Chưa chọn kỹ thuật viên.';
        try { sessionStorage.setItem(key, JSON.stringify(selected)); } catch (_) { /* Storage không khả dụng. */ }
    };
    boxes.forEach(box => {
        box.checked = Object.hasOwn(selected, box.value);
        box.addEventListener('change', () => {
            if (box.checked) selected[box.value] = box.dataset.person;
            else delete selected[box.value];
            update();
        });
    });
    form.addEventListener('submit', event => {
        if (!Object.keys(selected).length) {
            event.preventDefault();
            document.querySelector('#tv4-selected').textContent = 'Vui lòng chọn ít nhất một kỹ thuật viên.';
        }
    });
    update();
})();
