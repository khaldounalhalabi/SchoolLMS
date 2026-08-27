<x-layouts.app :pageTitle="__('Classroom Details')">
    <style>
        .back-link {
            font-size: 13px;
            color: var(--text-secondary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 20px;
        }
        .back-link:hover { color: var(--text-primary); }
        .detail-header {
            padding: 28px;
            border-bottom: 1px solid var(--border-soft);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .detail-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--info-tint), var(--info-tint-2));
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .detail-title {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .detail-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }
        .detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 28px;
            border-bottom: 1px solid var(--surface-2);
        }
        .detail-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
        }
        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .related-section {
            margin-top: 24px;
            max-width: 640px;
        }
        .related-title {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        .related-card {
            background: var(--surface);
            border-radius: 14px;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }
        .related-item {
            padding: 14px 20px;
            font-size: 13.5px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--surface-2);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .related-item:last-child { border-bottom: none; }
        .related-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .empty-msg {
            padding: 24px;
            text-align: center;
            color: var(--text-faint);
            font-size: 13px;
        }
    </style>

    <a href="{{ route('classrooms.index') }}" class="back-link">
        &larr; {{ __("Back to Classrooms") }}
    </a>

    <div class="detail-card">
        <div class="detail-header">
            <div class="detail-icon">
                <svg width="24" height="24" fill="none" stroke="var(--info-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <div class="detail-title">{{ $classroom->name }}</div>
                <div class="detail-subtitle">
                    {{ $classroom->grade->name }}
                    @if($classroom->academicYear)
                        · {{ $classroom->academicYear->name }}
                    @endif
                </div>
            </div>
        </div>
        <div class="detail-body">
            <div class="detail-row">
                <span class="detail-label">{{ __("Grade") }}</span>
                <span class="detail-value">{{ $classroom->grade->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('Students') }}</span>
                <span class="badge" style="background: var(--success-tint); color: var(--success-text);">
                    {{ $classroom->studentEnrollments->count() }} / {{ $classroom->capacity }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __("Capacity") }}</span>
                <span class="detail-value">{{ $classroom->capacity }}</span>
            </div>
        </div>
    </div>

    @if(auth()->user()->role->value === 'admin')
        <div class="related-section">
            <div class="related-title">{{ __('Add Students') }}</div>
            <div class="related-card" style="padding: 20px;">
                @if($errors->has('student_user_ids'))
                    <div style="margin-bottom: 12px; color: var(--danger-text); font-size: 13px;">{{ $errors->first('student_user_ids') }}</div>
                @endif
                @if($availableStudents->isEmpty())
                    <div class="empty-msg" style="padding: 8px 0;">{{ __('All students are already enrolled in this academic year.') }}</div>
                @else
                    <form method="POST" action="{{ route('admin.classrooms.students.store', $classroom) }}">
                        @csrf
                        <label for="student_user_ids" class="detail-label" style="display: block; margin-bottom: 8px;">{{ __('Available students') }}</label>
                        <select id="student_user_ids" name="student_user_ids[]" class="form-control" multiple size="6" required>
                            @foreach($availableStudents as $student)
                                <option value="{{ $student->id }}" {{ collect(old('student_user_ids', []))->contains($student->id) ? 'selected' : '' }}>
                                    {{ $student->name }} — {{ $student->email }}
                                </option>
                            @endforeach
                        </select>
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 12px;">
                            <span style="font-size: 12px; color: var(--text-muted);">{{ __('Select one or more students. Capacity: :available places left.', ['available' => max(0, $classroom->capacity - $classroom->studentEnrollments->count())]) }}</span>
                            <button type="submit" class="btn btn-primary">{{ __('Enroll Selected') }}</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Students --}}
    <div class="related-section">
        <div class="related-title">{{ __('Students (:count)', ['count' => $classroom->studentEnrollments->count()]) }}</div>
        <div class="related-card">
            @forelse($classroom->studentEnrollments as $enrollment)
                <div class="related-item">
                    <div class="related-item-icon" style="background: var(--success-tint);">
                        <svg width="16" height="16" fill="none" stroke="var(--success-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422c-.523.28-1.12.422-1.735.422H7.575c-.615 0-1.212-.142-1.735-.422L12 14z"/></svg>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600;">{{ $enrollment->student->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $enrollment->student->email }}</div>
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted);">{{ __('Enrolled :date', ['date' => $enrollment->enrollment_date->format('M d, Y')]) }}</span>
                </div>
            @empty
                <div class="empty-msg">{{ __("No students enrolled yet.") }}</div>
            @endforelse
        </div>
    </div>

    @if(auth()->user()->role->value === 'admin')
        <div class="related-section">
            <div class="related-title">{{ __('Add Subject to Classroom') }}</div>
            <div class="related-card" style="padding: 20px;">
                @if($errors->has('subject_id') || $errors->has('teacher_user_id'))
                    <div style="margin-bottom: 12px; color: var(--danger-text); font-size: 13px;">
                        {{ $errors->first('subject_id') ?: $errors->first('teacher_user_id') }}
                    </div>
                @endif

                @if($availableSubjects->isEmpty())
                    <div class="empty-msg" style="padding: 8px 0;">{{ __('All subjects are already assigned to this classroom.') }}</div>
                @else
                    <form method="POST" action="{{ route('admin.classrooms.subjects.store', $classroom) }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label class="detail-label" for="subject_id" style="display: block; margin-bottom: 8px;">{{ __('Subject') }}</label>
                                <select id="subject_id" name="subject_id" class="form-control" required>
                                    <option value="">{{ __('Select subject…') }}</option>
                                    @foreach($availableSubjects as $subject)
                                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }} ({{ $subject->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="detail-label" for="teacher_user_id" style="display: block; margin-bottom: 8px;">{{ __('Teacher') }}</label>
                                <select id="teacher_user_id" name="teacher_user_id" class="form-control" required>
                                    <option value="">{{ __('Select teacher…') }}</option>
                                    @foreach($availableTeachers as $teacher)
                                        <option value="{{ $teacher->id }}" @selected(old('teacher_user_id') == $teacher->id)>{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: flex-end; margin-top: 12px;">
                            <button type="submit" class="btn btn-primary">{{ __('Assign Subject') }}</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Teacher Assignments --}}
    @if($classroom->teacherAssignments->count() > 0)
        <div class="related-section">
            <div class="related-title">{{ __("Subject Teachers") }}</div>
            <div class="related-card">
                @foreach($classroom->teacherAssignments->groupBy('subject.name') as $subjectName => $assignments)
                    @foreach($assignments as $assignment)
                        <div class="related-item">
                            <div class="related-item-icon" style="background: var(--primary-tint);">
                                <svg width="16" height="16" fill="none" stroke="var(--primary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600;">{{ $assignment->subject->name }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $assignment->teacher->name }}</div>
                            </div>
                            <span class="badge" style="background: var(--primary-tint); color: var(--primary-dark); font-size: 10px;">{{ $assignment->academicYear->name }}</span>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif
</x-layouts.app>
