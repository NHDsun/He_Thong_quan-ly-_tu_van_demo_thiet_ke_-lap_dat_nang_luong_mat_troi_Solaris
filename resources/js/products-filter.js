(() => {
    const form = document.getElementById('product-filter-form');
    const category = document.getElementById('product-filter-category');
    if (!form || !category) return;

    category.addEventListener('change', () => {
        form.querySelectorAll('[data-product-category]').forEach(group => {
            const enabled = group.dataset.productCategory === category.value;
            group.hidden = !enabled;
            group.disabled = !enabled;
            group.querySelectorAll('input, select').forEach(input => { input.value = ''; });
        });
        document.getElementById('product-filter-hint').hidden = ['tam-pin', 'inverter', 'pin-luu-tru'].includes(category.value);
    });

    document.getElementById('product-filter-sort')?.addEventListener('change', () => form.requestSubmit());
})();
