const testForm = document.getElementById('testForm');

if (testForm) {
    testForm.addEventListener('submit', (event) => {
        const questions = testForm.querySelectorAll('.question-card').length;
        const answered = testForm.querySelectorAll('input[type="radio"]:checked').length;

        if (answered < questions) {
            const message = testForm.dataset.unansweredMessage.replace(':count', questions - answered);
            if (!window.confirm(message)) event.preventDefault();
        }
    });
}
