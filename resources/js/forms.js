document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');
    if (!toggle) return;

    const input = document.getElementById(toggle.dataset.passwordToggle);
    if (!input) return;

    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    toggle.setAttribute('aria-pressed', String(!isVisible));
    toggle.setAttribute('aria-label', isVisible ? toggle.dataset.showLabel : toggle.dataset.hideLabel);
});

document.addEventListener('input', (event) => {
    const filter = event.target.closest('[data-table-filter]');
    if (!filter) return;

    const query = filter.value.toLowerCase();
    document.querySelectorAll(`${filter.dataset.tableFilter} tbody tr`).forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
});

const maxScoreInput = document.getElementById('maxScore');
const scoreInputs = document.querySelectorAll('.score-input');

if (maxScoreInput || scoreInputs.length) {
    const validateScores = () => {
        const max = parseFloat(maxScoreInput?.value) || 0;
        scoreInputs.forEach((input) => {
            input.classList.toggle('invalid', input.value !== '' && parseFloat(input.value) > max);
        });
    };

    maxScoreInput?.addEventListener('input', validateScores);
    scoreInputs.forEach((input) => input.addEventListener('input', validateScores));

    document.getElementById('gradeForm')?.addEventListener('submit', (event) => {
        validateScores();
        if (document.querySelector('.score-input.invalid')) {
            event.preventDefault();
            window.alert(document.getElementById('gradeForm').dataset.invalidMessage);
        }
    });
}
