document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#tv4-assignment-form');
    if (!form) {
        return;
    }

    const storageKey = 'solaris-crew-' + form.dataset.project;
    const checkboxes = form.querySelectorAll('input[name="technicians[]"]');
    const message = document.querySelector('#tv4-selected');
    let selected = {};

    // Lưu lựa chọn để khi chuyển trang nhân viên không phải chọn lại.
    try {
        const saved = JSON.parse(sessionStorage.getItem(storageKey));
        if (saved && typeof saved === 'object' && !Array.isArray(saved)) {
            selected = saved;
        }
    } catch (error) {
        selected = {};
    }

    function updateSelection() {
        const oldInputs = form.querySelectorAll('[data-persisted]');
        for (const input of oldInputs) {
            input.remove();
        }

        const visibleIds = [];
        for (const checkbox of checkboxes) {
            visibleIds.push(checkbox.value);
        }

        // Người đã chọn ở trang khác được gửi bằng input ẩn.
        for (const id of Object.keys(selected)) {
            if (visibleIds.includes(id)) {
                continue;
            }
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'technicians[]';
            input.value = id;
            input.dataset.persisted = '1';
            form.appendChild(input);
        }

        const count = Object.keys(selected).length;
        if (count === 0) {
            message.textContent = 'Chưa chọn kỹ thuật viên.';
        } else {
            message.textContent = 'Đã chọn ' + count + ' người: ' + Object.values(selected).join(', ');
        }

        try {
            sessionStorage.setItem(storageKey, JSON.stringify(selected));
        } catch (error) {
            // Vẫn chọn được nhân viên khi trình duyệt không cho dùng storage.
        }
    }

    for (const checkbox of checkboxes) {
        checkbox.checked = selected[checkbox.value] !== undefined;
        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                selected[checkbox.value] = checkbox.dataset.person;
            } else {
                delete selected[checkbox.value];
            }
            updateSelection();
        });
    }

    form.addEventListener('submit', function (event) {
        if (!Object.keys(selected).length) {
            event.preventDefault();
            message.textContent = 'Vui lòng chọn ít nhất một kỹ thuật viên.';
        }
    });

    updateSelection();
});
