const paymentPage = document.querySelector('[data-payment-page]');

if (paymentPage) {
    const checkoutTemplate = paymentPage.dataset.checkoutTemplate;
    const testProcessTemplate = paymentPage.dataset.testProcessTemplate;
    const paymentModal = document.getElementById('payment-modal');

    const updatePayButton = (select) => {
        const feeId = select.dataset.feeId;
        const selected = select.options[select.selectedIndex];
        const status = selected.dataset.status;
        const payButton = document.getElementById(`pay-btn-${feeId}`);
        const statusBox = document.getElementById(`status-box-${feeId}`);
        const statusText = document.getElementById(`status-text-${feeId}`);
        const payLabel = document.getElementById(`pay-label-${feeId}`);

        if (!payButton || !statusBox || !statusText || !payLabel) return;

        if (status === 'succeeded') {
            statusBox.className = 'fee-status fee-status-paid';
            statusText.textContent = paymentPage.dataset.paidLabel;
            payButton.style.display = 'none';
        } else if (status === 'pending') {
            statusBox.className = 'fee-status fee-status-pending';
            statusText.textContent = paymentPage.dataset.pendingLabel;
            payButton.style.display = 'flex';
            payLabel.textContent = paymentPage.dataset.completeLabel;
        } else {
            statusBox.className = 'fee-status fee-status-available';
            statusText.textContent = paymentPage.dataset.awaitingLabel;
            payButton.style.display = 'flex';
            payLabel.textContent = paymentPage.dataset.payLabel;
        }

        payButton.dataset.studentId = selected.value;
        payButton.dataset.studentName = selected.dataset.name;
    };

    document.querySelectorAll('.child-select').forEach((select) => {
        select.addEventListener('change', () => updatePayButton(select));
        updatePayButton(select);
    });

    document.querySelectorAll('.btn-pay').forEach((button) => {
        button.addEventListener('click', () => {
            const feeId = button.id.replace('pay-btn-', '');

            if (!paymentModal) {
                window.location.href = checkoutTemplate.replace('__FEE__', feeId)
                    + `?student=${encodeURIComponent(button.dataset.studentId)}`;
                return;
            }

            document.getElementById('modal-subtitle').textContent = `${button.dataset.year} — ${button.dataset.studentName || ''}`;
            document.getElementById('modal-amount').textContent = parseFloat(button.dataset.amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
            document.getElementById('modal-currency').textContent = button.dataset.currency;
            document.getElementById('modal-student-id').value = button.dataset.studentId;
            document.getElementById('test-payment-form').action = testProcessTemplate.replace('__FEE__', feeId);
            paymentModal.classList.add('active');
        });
    });

    const closePaymentModal = () => paymentModal?.classList.remove('active');
    document.getElementById('cancel-payment-modal')?.addEventListener('click', closePaymentModal);
    paymentModal?.addEventListener('click', (event) => {
        if (event.target === paymentModal) closePaymentModal();
    });
}
