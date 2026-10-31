document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.querySelector('[data-coverflow]');
    if (!carousel) {
        return;
    }

    const cards = carousel.querySelectorAll('.tv4-process-card');
    const dots = carousel.querySelectorAll('[data-coverflow-step]');
    const track = carousel.querySelector('.tv4-process-cards');
    const status = carousel.querySelector('[data-coverflow-status]');
    const description = carousel.querySelector('[data-coverflow-description]');
    let current = 0;
    let startX = 0;
    let startY = 0;
    let pointerId = null;
    let swiped = false;

    function showStage(index) {
        current = (index + cards.length) % cards.length;
        for (let i = 0; i < cards.length; i++) {
            // Đặt các thẻ trước và sau quanh thẻ giữa, kể cả khi quay vòng.
            let offset = (i - current + cards.length) % cards.length;
            if (offset > cards.length / 2) {
                offset -= cards.length;
            }
            cards[i].style.setProperty('--offset', offset);
            cards[i].style.setProperty('--depth', Math.abs(offset));
            cards[i].style.setProperty('--direction', Math.sign(offset));
            cards[i].style.zIndex = cards.length - Math.abs(offset);
            cards[i].classList.toggle('is-active', i === current);
            cards[i].classList.toggle('is-distant', Math.abs(offset) > 2);
            cards[i].setAttribute('aria-hidden', String(i !== current));
            if (i === current) {
                dots[i].setAttribute('aria-current', 'step');
            } else {
                dots[i].removeAttribute('aria-current');
            }
        }
        const title = cards[current].querySelector('h3').textContent;
        status.textContent = 'Giai đoạn ' + (current + 1) + ' / ' + cards.length + ' · ' + title;
        description.textContent = cards[current].querySelector('.tv4-process-content p').textContent;
    }

    carousel.querySelector('[data-coverflow-prev]').addEventListener('click', function () {
        showStage(current - 1);
    });
    carousel.querySelector('[data-coverflow-next]').addEventListener('click', function () {
        showStage(current + 1);
    });
    for (let i = 0; i < cards.length; i++) {
        dots[i].addEventListener('click', function () { showStage(i); });
        cards[i].addEventListener('click', function () {
            if (!swiped) {
                showStage(i);
            }
        });
        cards[i].querySelector('img').draggable = false;
    }
    carousel.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
            showStage(current - 1);
        } else if (event.key === 'ArrowRight') {
            showStage(current + 1);
        } else if (event.key === 'Home') {
            showStage(0);
        } else if (event.key === 'End') {
            showStage(cards.length - 1);
        } else {
            return;
        }
        event.preventDefault();
    });
    track.addEventListener('pointerdown', function (event) {
        if (!event.isPrimary || event.button !== 0) {
            return;
        }
        startX = event.clientX;
        startY = event.clientY;
        pointerId = event.pointerId;
        swiped = false;
    });
    track.addEventListener('pointerup', function (event) {
        if (pointerId !== event.pointerId) {
            return;
        }
        pointerId = null;
        const distanceX = event.clientX - startX;
        const distanceY = event.clientY - startY;
        if (Math.abs(distanceX) > 45 && Math.abs(distanceX) > Math.abs(distanceY)) {
            swiped = true;
            showStage(current + (distanceX < 0 ? 1 : -1));
        }
    });
    track.addEventListener('pointercancel', function () { pointerId = null; });

    showStage(0);
    carousel.classList.add('is-ready');
    carousel.querySelector('.tv4-coverflow-controls').hidden = false;
    carousel.querySelector('.tv4-coverflow-dots').hidden = false;
    carousel.querySelector('.tv4-coverflow-hint').hidden = false;
});

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
