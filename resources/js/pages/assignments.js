const teacherSelect = document.getElementById('teacher_user_id');
const subjectSelect = document.getElementById('subject_id');
const classroomSelect = document.getElementById('classroom_id');
const academicYearSelect = document.getElementById('academic_year_id');

if (teacherSelect && subjectSelect) {
    const filterTeachersBySubject = () => {
        const subjectId = subjectSelect.value;

        Array.from(teacherSelect.options).forEach((option) => {
            if (!option.value) return;

            const assignedSubjects = option.dataset.subjectIds.split(',').filter(Boolean);
            const isAvailable = !subjectId || assignedSubjects.length === 0 || assignedSubjects.includes(subjectId);

            option.hidden = !isAvailable;
            option.disabled = !isAvailable;
        });

        if (teacherSelect.selectedOptions[0]?.disabled) teacherSelect.value = '';
    };

    subjectSelect.addEventListener('change', filterTeachersBySubject);
    filterTeachersBySubject();
}

if (classroomSelect && academicYearSelect) {
    const filterClassroomsByAcademicYear = () => {
        const academicYearId = academicYearSelect.value;

        Array.from(classroomSelect.options).forEach((option) => {
            if (!option.value) return;

            const isAvailable = !academicYearId || option.dataset.academicYearId === academicYearId;
            option.hidden = !isAvailable;
            option.disabled = !isAvailable;
        });

        if (classroomSelect.selectedOptions[0]?.disabled) classroomSelect.value = '';
    };

    academicYearSelect.addEventListener('change', filterClassroomsByAcademicYear);
    filterClassroomsByAcademicYear();
}
