<x-layouts.app :pageTitle="__('Exam Types')">
<style>
    .page-header { margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .page-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); }

    .card {
        background: var(--surface);
        border-radius: 14px;
        border: 1px solid var(--border-soft);
        box-shadow: var(--shadow-card);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .card-header {
        padding: 18px 20px;
        border-bottom: 1px solid var(--border-soft);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .card-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }

    .form-row { display: flex; gap: 12px; flex-wrap: wrap; padding: 20px; align-items: flex-end; }
    .form-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 160px; }
    .form-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.7px; }
    .form-input, .form-select {
        padding: 9px 14px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-size: 13.5px;
        font-family: var(--font-body);
        color: var(--text-strong);
        background: var(--surface-3);
        outline: none;
        transition: border 0.2s;
    }
    .form-input:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }

    .btn-primary {
        padding: 9px 20px;
        background: var(--primary);
        color: var(--on-primary);
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: var(--font-body);
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-danger {
        padding: 6px 12px;
        background: var(--danger-tint);
        color: var(--danger-text);
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        font-family: var(--font-body);
    }
    .btn-danger:hover { background: var(--danger-border); }
    .btn-edit {
        padding: 6px 12px;
        background: var(--info-tint-2);
        color: var(--info-strong);
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        font-family: var(--font-body);
    }
    .btn-edit:hover { background: var(--info-tint-2); }

    table { width: 100%; border-collapse: collapse; }
    thead tr { background: var(--surface-2); }
    th { padding: 12px 16px; text-align: start; font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.8px; text-transform: uppercase; }
    td { padding: 12px 16px; border-bottom: 1px solid var(--surface-2); font-size: 13.5px; color: var(--text-strong); }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--surface-3); }

    .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; }
    .badge-purple { background: var(--primary-tint); color: var(--violet-strong); }

    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 500; }
    .alert-success { background: var(--success-tint); color: var(--success-text); }
    .alert-error { background: var(--danger-tint); color: var(--danger-text); }

    .modal-overlay {
        display: none;
        position: fixed; inset: 0;
        background: var(--overlay);
        z-index: 100;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: var(--surface);
        border-radius: 16px;
        padding: 28px;
        width: 100%;
        max-width: 440px;
        box-shadow: var(--shadow-modal);
        /* Scroll inside the box instead of off the viewport on short screens. */
        max-height: calc(100dvh - 32px);
        overflow-y: auto;
    }
    /* Gutter so the modal never sits flush against the screen edges. */
    @media (max-width: 520px) {
        .modal-overlay { padding: 16px; }
        .modal-box { padding: 22px 18px; }
    }
    .modal-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
    .btn-secondary { padding: 9px 20px; background: var(--border-soft); color: var(--text-strong); border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font-body); }
    .btn-secondary:hover { background: var(--border); }
</style>

<div class="page-header">
    <div>
        <div class="page-title">{{ __("Exam Types") }}</div>
        <div class="page-desc">{{ __("Define exam types and their grade weights per semester") }}</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

{{-- Create form --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">{{ __("Add Exam Type") }}</span>
    </div>
    <form method="POST" action="{{ route('admin.exam-types.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">{{ __("Name") }}</label>
                <input class="form-input" name="name" placeholder="{{ __('e.g. Midterm') }}" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __("Weight %") }}</label>
                <input class="form-input" name="weight_percent" type="number" step="0.01" min="0.01" max="100"
                    placeholder="{{ __('e.g. 30') }}" value="{{ old('weight_percent') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __("Semester") }}</label>
                <select class="form-select" name="semester_id" required>
                    <option value="">{{ __("Select semester…") }}</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ old('semester_id') == $sem->id ? 'selected' : '' }}>
                            {{ $sem->academicYear->name ?? '—' }} — {{ $sem->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn-primary">{{ __("Add") }}</button>
            </div>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">{{ __("All Exam Types") }}</span>
        <span style="font-size:12px; color:var(--text-muted);">{{ __(":count total", ['count' => $examTypes->total()]) }}</span>
    </div>
    <div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>{{ __("Name") }}</th>
                <th>{{ __("Weight") }}</th>
                <th>{{ __("Semester") }}</th>
                <th>{{ __("Academic Year") }}</th>
                <th>{{ __("Actions") }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($examTypes as $et)
                <tr>
                    <td style="font-weight:600">{{ $et->name }}</td>
                    <td><span class="badge badge-purple">{{ $et->weight_percent }}%</span></td>
                    <td>{{ $et->semester->name ?? '—' }}</td>
                    <td>{{ $et->semester->academicYear->name ?? '—' }}</td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn-edit edit-exam-type"
                                data-id="{{ $et->id }}"
                                data-name="{{ $et->name }}"
                                data-weight="{{ $et->weight_percent }}">{{ __("Edit") }}</button>
                        <form method="POST" action="{{ route('admin.exam-types.destroy', $et) }}" class="delete-exam-type" data-confirm="{{ __('Delete this exam type?') }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger">{{ __("Delete") }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--text-muted); padding:32px;">{{ __("No exam types yet.") }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($examTypes->hasPages())
        <div style="padding:16px 20px;">{{ $examTypes->links() }}</div>
    @endif
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-title">{{ __("Edit Exam Type") }}</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">{{ __("Name") }}</label>
                <input class="form-input" name="name" id="editName" required style="width:100%">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __("Weight %") }}</label>
                <input class="form-input" name="weight_percent" id="editWeight" type="number" step="0.01" min="0.01" max="100" required style="width:100%">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="close-edit">{{ __("Cancel") }}</button>
                <button type="submit" class="btn-primary">{{ __("Save") }}</button>
            </div>
        </form>
    </div>
</div>

</x-layouts.app>
