const questionType = document.querySelector('[data-question-type]');
const optionsSection = document.getElementById('optionsSection');
const optionsList = document.getElementById('optionsList');

if (questionType && optionsSection && optionsList) {
    const renderOptions = () => {
        const trueFalse = questionType.value === 'true_false';
        const labels = trueFalse ? ['True', 'False'] : ['Option A', 'Option B', 'Option C (optional)', 'Option D (optional)'];

        optionsList.innerHTML = labels.map((label, index) => `
            <div class="option-row">
                <input type="radio" name="correct_option" value="${index}" ${index === 0 ? 'checked' : ''}>
                <input type="text" name="options[${index}][option_text]" class="form-input" ${trueFalse ? `value="${label}"` : `placeholder="${label}"`} ${index < 2 ? 'required' : ''}>
            </div>
        `).join('');
    };

    questionType.addEventListener('change', renderOptions);
    renderOptions();
}
