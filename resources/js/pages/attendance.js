document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-mark-all]');
    if (!button) return;

    document.querySelectorAll('.radio-group').forEach((group) => {
        const radio = group.querySelector(`input[value="${button.dataset.markAll}"]`);
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
});
