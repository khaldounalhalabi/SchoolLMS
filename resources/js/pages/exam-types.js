const editModal = document.getElementById('editModal');
const editForm = document.getElementById('editForm');

if (editModal && editForm) {
    const closeEdit = () => editModal.classList.remove('open');

    document.querySelectorAll('.edit-exam-type').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('editName').value = button.dataset.name;
            document.getElementById('editWeight').value = button.dataset.weight;
            editForm.action = `/admin/exam-types/${button.dataset.id}`;
            editModal.classList.add('open');
        });
    });

    document.getElementById('close-edit')?.addEventListener('click', closeEdit);
    editModal.addEventListener('click', (event) => {
        if (event.target === editModal) closeEdit();
    });
}
