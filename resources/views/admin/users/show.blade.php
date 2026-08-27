<x-layouts.app :pageTitle="__('User Details')">
    <style>
        .back-link {
            font-size: 13px; color: var(--text-secondary); text-decoration: none;
            display: inline-flex; align-items: center; gap: 4px; margin-bottom: 20px;
        }
        .back-link:hover { color: var(--text-primary); }
        .detail-header {
            padding: 28px; display: flex; align-items: center; gap: 16px;
            border-bottom: 1px solid var(--border-soft);
        }
        .detail-avatar {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 700; color: white; flex-shrink: 0;
        }
        .detail-name { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); }
        .detail-email { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
        .detail-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 28px; border-bottom: 1px solid var(--surface-2);
        }
        .detail-label { font-size: 13px; font-weight: 500; color: var(--text-muted); }
        .detail-value { font-size: 14px; font-weight: 600; color: var(--text-primary); }
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .badge-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }

        /* Related sections */
        .related-section { margin-top: 24px; max-width: 640px; }
        .related-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 12px;
        }
        .related-title { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .related-card {
            background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-card); overflow: hidden;
        }
        .related-item {
            padding: 14px 20px; font-size: 13.5px; color: var(--text-primary);
            border-bottom: 1px solid var(--surface-2);
            display: flex; align-items: center; gap: 10px;
        }
        .related-item:last-child { border-bottom: none; }
        .related-item-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .empty-state { padding: 24px 20px; text-align: center; color: var(--text-muted); font-size: 13.5px; }

        /* Link form inside card */
        .link-form-card {
            background: var(--surface); border-radius: 14px; border: 1px dashed var(--primary-light);
            padding: 20px; margin-top: 12px; max-width: 640px;
        }
        .link-form-title { font-size: 13.5px; font-weight: 600; color: var(--text-primary); margin-bottom: 14px; }
        .link-form-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
        .link-form-row select, .link-form-row .select-wrap select {
            padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 13.5px; font-family: var(--font-body); color: var(--text-primary);
            background: var(--surface-3); outline: none; transition: all 0.2s; min-width: 180px;
        }
        .link-form-row select:focus { border-color: var(--primary); background: var(--surface); }
        .btn-link {
            padding: 9px 18px; background: var(--primary); color: var(--on-primary); border: none;
            border-radius: 10px; font-size: 13px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-link:hover { background: var(--primary-dark); }
        .btn-unlink {
            margin-inline-start: auto; padding: 4px 10px;
            background: var(--danger-tint); color: var(--rose-dark);
            border: 1px solid var(--danger-border); border-radius: 7px;
            font-size: 12px; font-weight: 600; cursor: pointer;
            font-family: var(--font-body); transition: all 0.15s;
        }
        .btn-unlink:hover { background: var(--danger-tint); }
        .relation-chip {
            font-size: 11.5px; font-weight: 600; padding: 2px 8px;
            border-radius: 5px; background: var(--surface-2); color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        .alert-success {
            background: var(--success-tint); border: 1px solid var(--success-border); border-radius: 10px;
            padding: 12px 16px; font-size: 13.5px; color: var(--success-text); margin-bottom: 16px;
        }
    </style>

    <a href="{{ route('admin.users.index') }}" class="back-link">
        &larr; {{ __('Back to Users') }}
    </a>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @php
        $roleBadge = [
            'admin'   => ['label' => __('Admin'),   'bg' => 'var(--primary-tint)', 'color' => 'var(--primary-dark)', 'dot' => 'var(--primary)'],
            'teacher' => ['label' => __('Teacher'), 'bg' => 'var(--info-tint)', 'color' => 'var(--info-strong)', 'dot' => 'var(--info)'],
            'student' => ['label' => __('Student'), 'bg' => 'var(--success-tint)', 'color' => 'var(--success-text)', 'dot' => 'var(--success)'],
            'parent'  => ['label' => __('Parent'),  'bg' => 'var(--violet-tint)', 'color' => 'var(--violet-strong)', 'dot' => 'var(--violet)'],
        ];
        $rb = $roleBadge[$user->role->value] ?? $roleBadge['student'];
        $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
        $avatarColors = [
            'admin'   => ['from' => 'var(--primary-light)', 'to' => 'var(--primary)'],
            'teacher' => ['from' => '#60a5fa', 'to' => 'var(--info-dark)'],
            'student' => ['from' => 'var(--success)', 'to' => 'var(--success-dark)'],
            'parent'  => ['from' => '#c084fc', 'to' => 'var(--violet-dark)'],
        ];
        $ac = $avatarColors[$user->role->value] ?? $avatarColors['student'];
    @endphp

    {{-- Core detail card --}}
    <div class="detail-card">
        <div class="detail-header">
            <div class="detail-avatar" style="background: linear-gradient(135deg, {{ $ac['from'] }}, {{ $ac['to'] }});">
                {{ $initials }}
            </div>
            <div>
                <div class="detail-name">{{ $user->name }}</div>
                <div class="detail-email">{{ $user->email }}</div>
            </div>
        </div>
        <div class="detail-body">
            <div class="detail-row">
                <span class="detail-label">{{ __('Role') }}</span>
                <span class="badge" style="background: {{ $rb['bg'] }}; color: {{ $rb['color'] }};">
                    <span class="badge-dot" style="background: {{ $rb['dot'] }};"></span>
                    {{ $rb['label'] }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('Phone') }}</span>
                <span class="detail-value">{{ $user->phone ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('Status') }}</span>
                @if($user->is_active)
                    <span class="badge" style="background: var(--success-tint); color: var(--success-text);">
                        <span class="badge-dot" style="background: var(--success);"></span>
                        {{ __('Active') }}
                    </span>
                @else
                    <span class="badge" style="background: var(--surface-2); color: var(--text-muted);">
                        <span class="badge-dot" style="background: var(--text-faint);"></span>
                        {{ __('Inactive') }}
                    </span>
                @endif
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('Joined') }}</span>
                <span class="detail-value">{{ $user->created_at->format('M d, Y') }}</span>
            </div>
        </div>
    </div>

    {{-- ── STUDENT: classroom + parents ─────────────────────────────── --}}
    @if($user->role->value === 'student')

        @if($user->studentProfile)
            <div class="related-section">
                <div class="related-title" style="margin-bottom: 12px;">{{ __('Classroom') }}</div>
                <div class="related-card">
                    <div class="related-item">
                        <div class="related-item-icon" style="background: var(--info-tint);">
                            <svg width="16" height="16" fill="none" stroke="var(--info-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 600;">{{ $user->studentProfile->classroom->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                {{ $user->studentProfile->classroom->grade->name }} —
                                {{ __('Enrolled :date', ['date' => $user->studentProfile->enrollment_date->format('M d, Y')]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Parents list + link form --}}
        <div class="related-section">
            <div class="related-header">
                <div class="related-title">{{ __('Parents (:count)', ['count' => $user->parents->count()]) }}</div>
            </div>
            <div class="related-card">
                @forelse($user->parents as $parent)
                    <div class="related-item">
                        <div class="related-item-icon" style="background: var(--violet-tint);">
                            <svg width="16" height="16" fill="none" stroke="var(--violet-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600;">{{ $parent->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $parent->email }}</div>
                        </div>
                        <span class="relation-chip">{{ __(ucfirst($parent->pivot->relation)) }}</span>
                        <form method="POST" action="{{ route('admin.users.unlink-parent', $user) }}"
                              class="unlink-relation-form"
                              data-confirm="{{ __('Unlink :name?', ['name' => $parent->name]) }}">
                            @csrf @method('DELETE')
                            <input type="hidden" name="parent_user_id" value="{{ $parent->id }}">
                            <button type="submit" class="btn-unlink">{{ __('Unlink') }}</button>
                        </form>
                    </div>
                @empty
                    <div class="empty-state">{{ __('No parents linked yet.') }}</div>
                @endforelse
            </div>

            @if($user->parents->isEmpty() && $availableParents->count() > 0)
                <div class="link-form-card">
                    <div class="link-form-title">{{ __('Link a parent to this student') }}</div>
                    <form method="POST" action="{{ route('admin.users.link-parent', $user) }}">
                        @csrf
                        <div class="link-form-row">
                            <select name="parent_user_id" required>
                                <option value="">{{ __('Select parent…') }}</option>
                                @foreach($availableParents as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->email }})</option>
                                @endforeach
                            </select>
                            <select name="relation" required>
                                <option value="father">{{ __("Father") }}</option>
                                <option value="mother">{{ __("Mother") }}</option>
                                <option value="guardian">{{ __("Guardian") }}</option>
                            </select>
                            <button type="submit" class="btn-link">{{ __('Link Parent') }}</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

    @endif

    {{-- ── PARENT: children list + link form ────────────────────────── --}}
    @if($user->role->value === 'parent')

        <div class="related-section">
            <div class="related-header">
                <div class="related-title">{{ __('Children (:count)', ['count' => $user->children->count()]) }}</div>
            </div>
            <div class="related-card">
                @forelse($user->children as $child)
                    <div class="related-item">
                        <div class="related-item-icon" style="background: var(--success-tint);">
                            <svg width="16" height="16" fill="none" stroke="var(--success-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600;">{{ $child->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                {{ $child->email }}
                                @if($child->studentProfile?->classroom)
                                    — {{ $child->studentProfile->classroom->name }}, {{ $child->studentProfile->classroom->grade->name }}
                                @endif
                            </div>
                        </div>
                        <span class="relation-chip">{{ __(ucfirst($child->pivot->relation)) }}</span>
                        <form method="POST" action="{{ route('admin.users.unlink-child', $user) }}"
                              class="unlink-relation-form"
                              data-confirm="{{ __('Unlink :name?', ['name' => $child->name]) }}">
                            @csrf @method('DELETE')
                            <input type="hidden" name="student_user_id" value="{{ $child->id }}">
                            <button type="submit" class="btn-unlink">{{ __('Unlink') }}</button>
                        </form>
                    </div>
                @empty
                    <div class="empty-state">{{ __('No children linked yet.') }}</div>
                @endforelse
            </div>

            @if($availableStudents->count() > 0)
                <div class="link-form-card">
                    <div class="link-form-title">{{ __('Link a student to this parent') }}</div>
                    <form method="POST" action="{{ route('admin.users.link-child', $user) }}">
                        @csrf
                        <div class="link-form-row">
                            <select name="student_user_id" required>
                                <option value="">{{ __('Select student…') }}</option>
                                @foreach($availableStudents as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->email }})</option>
                                @endforeach
                            </select>
                            <select name="relation" required>
                                <option value="father">{{ __("Father") }}</option>
                                <option value="mother">{{ __("Mother") }}</option>
                                <option value="guardian">{{ __("Guardian") }}</option>
                            </select>
                            <button type="submit" class="btn-link">{{ __('Link Student') }}</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

    @endif

    {{-- ── TEACHER: subject assignments ─────────────────────────────── --}}
    @if($user->role->value === 'teacher' && $user->teacherAssignments->count() > 0)
        <div class="related-section">
            <div class="related-title" style="margin-bottom: 12px;">{{ __('Subject Assignments') }}</div>
            <div class="related-card">
                @foreach($user->teacherAssignments as $assignment)
                    <div class="related-item">
                        <div class="related-item-icon" style="background: var(--info-tint);">
                            <svg width="16" height="16" fill="none" stroke="var(--info-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 600;">{{ $assignment->subject->name }} — {{ $assignment->classroom->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $assignment->classroom->grade->name }} — {{ $assignment->academicYear->name }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</x-layouts.app>
