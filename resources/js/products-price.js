(() => {
    const format = new Intl.NumberFormat('vi-VN');
    document.querySelectorAll('[data-product-price]').forEach(group => {
        const input = group.querySelector('input');
        const form = input.form;
        const category = form?.querySelector('select[name="category"], select[name="category_id"]');
        if (!category) return;
        const steps = JSON.parse(group.dataset.priceSteps);
        const hint = form.querySelector(`[data-price-step-hint="${input.name}"]`);
        let step = steps[''];
        const updateStep = () => {
            const slug = category.selectedOptions[0]?.dataset.slug ?? category.value;
            step = steps[slug] ?? steps[''];
            if (hint) hint.textContent = `Bước ±${format.format(step)} đ`;
            group.querySelectorAll('[data-price-direction]').forEach(button => {
                const verb = button.dataset.priceDirection === '1' ? 'Tăng' : 'Giảm';
                button.title = `${verb} ${format.format(step)} đ`;
                button.setAttribute('aria-label', `${verb} ${group.dataset.priceLabel} ${format.format(step)} đồng`);
            });
        };
        const adjust = direction => {
            const current = Number.isFinite(input.valueAsNumber) ? input.valueAsNumber : 0;
            input.value = String(Math.round(Math.max(0, current + direction * step) * 100) / 100);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };
        group.querySelectorAll('[data-price-direction]').forEach(button => {
            button.addEventListener('click', () => adjust(Number(button.dataset.priceDirection)));
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
                event.preventDefault();
                adjust(event.key === 'ArrowUp' ? 1 : -1);
            }
        });
        category.addEventListener('change', updateStep);
        updateStep();
    });
})();
