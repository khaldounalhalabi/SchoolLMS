const slotModal = document.getElementById('slotModal');

const closeSlotModal = () => slotModal?.classList.remove('open');

const openSlotModal = (day, period) => {
    if (!slotModal) return;

    const label = day.charAt(0).toUpperCase() + day.slice(1);
    document.getElementById('modalTitle').textContent = `Assign — ${label}, Period ${period}`;
    document.getElementById('modalSubtitle').textContent = 'Choose a teacher and subject for this period.';
    document.getElementById('modalDay').value = day;
    document.getElementById('modalPeriod').value = period;
    slotModal.classList.add('open');
};

document.addEventListener('click', (event) => {
    const cell = event.target.closest('[data-slot-day]');
    if (cell) openSlotModal(cell.dataset.slotDay, cell.dataset.slotPeriod);

    if (event.target.closest('[data-close-slot-modal]') || event.target === slotModal) {
        closeSlotModal();
    }
});

const invalidSlot = document.querySelector('[data-reopen-slot]');
if (invalidSlot) openSlotModal(invalidSlot.dataset.day, invalidSlot.dataset.period);
