(() => {
    const input = document.getElementById('product-image-file');
    if (!input) return;
    const form = input.form;
    const preview = form.querySelector('[data-product-image-preview]');
    const empty = form.querySelector('[data-product-image-empty]');
    const cancel = form.querySelector('[data-cancel-product-image]');
    const status = document.getElementById('product-image-status');
    let objectUrl = null;
    const restore = () => {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        input.value = '';
        if (preview.dataset.existingSrc) preview.src = preview.dataset.existingSrc;
        else preview.removeAttribute('src');
        preview.hidden = !preview.dataset.existingSrc;
        empty.hidden = !preview.hidden;
        cancel.hidden = true;
    };
    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) { restore(); status.textContent = ''; return; }
        if (!/\.(jpe?g|png)$/i.test(file.name) || (file.type && !['image/jpeg', 'image/png'].includes(file.type))) {
            restore(); status.textContent = 'Chỉ chọn ảnh JPG, JPEG hoặc PNG.'; return;
        }
        if (file.size === 0 || file.size > 5 * 1024 * 1024) {
            restore(); status.textContent = 'Ảnh phải có dung lượng lớn hơn 0 và không quá 5 MB.'; return;
        }
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        preview.hidden = false;
        empty.hidden = true;
        cancel.hidden = false;
        status.textContent = `${file.name} · ${new Intl.NumberFormat('vi-VN').format(Math.ceil(file.size / 1024))} KB. Ảnh được tải lên khi lưu sản phẩm.`;
    });
    cancel.addEventListener('click', () => { restore(); status.textContent = 'Đã bỏ ảnh vừa chọn.'; });
    preview.addEventListener('error', () => {
        if (!objectUrl) return;
        restore();
        status.textContent = 'Không đọc được ảnh đã chọn. Vui lòng chọn tệp ảnh khác.';
    });
    window.addEventListener('pagehide', () => { if (objectUrl) URL.revokeObjectURL(objectUrl); });
})();
