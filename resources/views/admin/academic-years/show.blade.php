<x-layouts.app :pageTitle="__('Academic Year Details')">
    <style>
        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 20px; background: var(--primary); color: var(--on-primary);
            border-radius: 10px; text-decoration: none; font-size: 13.5px;
            font-weight: 600; font-family: var(--font-body);
            transition: all 0.2s; box-shadow: 0 2px 8px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
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
            margin-top: 4px;
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
        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
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
    </style>

    <a href="{{ route('admin.academic-years.index') }}" class="back-link">
        &larr; {{ __("Back to Academic Years") }}
    </a>

    <div class="detail-card">
        <div class="detail-header">
            <div class="detail-title">{{ $year->name }}</div>
            <div class="detail-subtitle">{{ $year->school->name }}</div>
        </div>
        <div class="detail-body">
            <div class="detail-row">
                <span class="detail-label">{{ __("Start Date") }}</span>
                <span class="detail-value">{{ $year->start_date->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __("End Date") }}</span>
                <span class="detail-value">{{ $year->end_date->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __("Duration") }}</span>
                <span class="detail-value">{{ __(':count months', ['count' => (int) ceil($year->start_date->diffInMonths($year->end_date))]) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __("Status") }}</span>
                @if($year->is_active)
                    <span class="badge" style="background: var(--success-tint); color: var(--success-text);">
                        <span class="badge-dot" style="background: var(--success);"></span>
                        {{ __("Active") }}
                    </span>
                @else
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge" style="background: var(--surface-2); color: var(--text-muted);">
                            <span class="badge-dot" style="background: var(--text-faint);"></span>
                            {{ __('Inactive') }}
                        </span>
                        <form method="POST" action="{{ route('admin.academic-years.activate', $year) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Activate') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Classrooms --}}
    <div class="related-section" style="max-width: 900px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div class="related-title" style="margin-bottom: 0;">{{ __('Classrooms (:count)', ['count' => $year->classrooms->count()]) }}</div>
            <a href="{{ route('admin.classrooms.create', ['academic_year_id' => $year->id]) }}" class="btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Add Classroom') }}
            </a>
        </div>
        <div class="related-card" style="overflow-x: auto;">
            @if($year->classrooms->isEmpty())
                <div style="padding: 24px; text-align: center; color: var(--text-faint);">{{ __('No classrooms created for this academic year yet.') }}</div>
            @else
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--surface-2);">
                            <th style="padding: 12px 20px; text-align: start; color: var(--text-muted); font-size: 11px;">{{ __('Grade') }}</th>
                            <th style="padding: 12px 20px; text-align: start; color: var(--text-muted); font-size: 11px;">{{ __('Section') }}</th>
                            <th style="padding: 12px 20px; text-align: start; color: var(--text-muted); font-size: 11px;">{{ __('Students') }}</th>
                            <th style="padding: 12px 20px; text-align: start; color: var(--text-muted); font-size: 11px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($year->classrooms->sortBy(fn ($classroom) => sprintf('%05d-%s', $classroom->grade->order_index, $classroom->name)) as $classroom)
                            <tr>
                                <td style="padding: 13px 20px; border-top: 1px solid var(--border-soft);">{{ $classroom->grade->name }}</td>
                                <td style="padding: 13px 20px; border-top: 1px solid var(--border-soft); font-weight: 600;">{{ $classroom->name }}</td>
                                <td style="padding: 13px 20px; border-top: 1px solid var(--border-soft);">{{ $classroom->studentEnrollments->count() }} / {{ $classroom->capacity }}</td>
                                <td style="padding: 13px 20px; border-top: 1px solid var(--border-soft); text-align: end;"><a href="{{ route('classrooms.show', $classroom) }}" style="color: var(--primary); text-decoration: none;">{{ __('Manage') }} →</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Semesters --}}
    <div class="related-section">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div class="related-title" style="margin-bottom: 0;">{{ __('Semesters (:count)', ['count' => $year->semesters->count()]) }}</div>
            <a href="{{ route('admin.semesters.create', ['academic_year_id' => $year->id]) }}" class="btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Add Semester') }}
            </a>
        </div>
        <div class="related-card">
            @forelse($year->semesters as $semester)
                <div class="related-item">
                    <div class="related-item-icon" style="background: {{ $semester->is_active ? 'var(--success-tint)' : 'var(--surface-2)' }};">
                        <svg width="16" height="16" fill="none" stroke="{{ $semester->is_active ? 'var(--success-dark)' : 'var(--text-muted)' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600;">{{ $semester->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $semester->start_date->format('M d') }} — {{ $semester->end_date->format('M d, Y') }}</div>
                    </div>
                    @if($semester->is_active)
                        <span class="badge" style="background: var(--success-tint); color: var(--success-text); font-size: 10px;">{{ __('Active') }}</span>
                    @endif
                </div>
            @empty
                <div style="padding: 24px; text-align: center; color: var(--text-faint);">{{ __('No semesters created for this academic year yet.') }}</div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
