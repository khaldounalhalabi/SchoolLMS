const justificationModal = document.getElementById('justifyModal');

const closeJustificationModal = () => justificationModal?.classList.remove('open');

const openJustificationModal = (button) => {
    if (!justificationModal) return;

    justificationModal.classList.add('open');
    document.getElementById('modalSubtitle').textContent = `Absence on ${button.dataset.date}`;
    document.getElementById('justifyForm').action = `/parent/attendance/${button.dataset.attendanceId}/justify`;
};

document.addEventListener('click', (event) => {
    const button = event.target.closest('.justify-button');
    if (button) openJustificationModal(button);

    if (event.target.closest('#cancel-justify-modal') || event.target === justificationModal) {
        closeJustificationModal();
    }
});
