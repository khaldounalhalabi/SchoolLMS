<x-layouts.app :pageTitle="__('My Knowledge Map')">
<style>
    .page-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }

    .filter-card {
        background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
        box-shadow: var(--shadow-card); padding: 18px 20px; margin-bottom: 20px;
        display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;
    }
    .filter-select {
        padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 8px;
        font-size: 13.5px; font-family: var(--font-body); color: var(--text-strong);
        background: var(--surface-3); outline: none; min-width: 220px;
    }

    .btn { padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: background 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: var(--primary); color: var(--on-primary); }
    .btn-primary:hover { background: var(--primary-dark); }

    .card { background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft); box-shadow: var(--shadow-card); padding: 24px; }

    /* Summary bar */
    .summary-bar {
        display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;
    }
    .summary-stat { background: var(--surface-2); border-radius: 12px; padding: 14px 20px; flex: 1; min-width: 120px; text-align: center; }
    .stat-value { font-size: 24px; font-weight: 800; color: var(--primary); }
    .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-top: 2px; }

    /* Tree — mastery ledger: each node is a small progress dial
       (conic-gradient arc) rather than a flat colored dot, so depth of
       mastery reads at a glance and not just its category. */
    .tree { font-size: 13px; position: relative; }
    .tree ul { list-style: none; padding-inline-start: 34px; margin: 0; position: relative; }
    .tree > ul { padding-inline-start: 0; }
    .tree ul ul::before {
        content: '';
        position: absolute;
        inset-inline-start: 23px;
        top: 0;
        bottom: 24px;
        width: 1px;
        background: var(--border);
    }
    .tree-node { margin: 2px 0; position: relative; }
    .tree ul ul > .tree-node::before {
        content: '';
        position: absolute;
        inset-inline-start: -11px;
        top: 28px;
        width: 11px;
        height: 1px;
        background: var(--border);
    }
    .node-row {
        display: flex; align-items: center; gap: 12px; cursor: pointer;
        padding: 10px 14px; border-radius: 10px; transition: background 0.15s;
        border: 1px solid transparent;
    }
    .node-row:hover { background: var(--surface-2); border-color: var(--border); }
    .node-gauge {
        --arc: 0deg;
        width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: conic-gradient(var(--gauge-fill, var(--text-faint)) var(--arc), var(--border-soft) 0);
        padding: 3px;
    }
    .node-gauge-label {
        width: 100%; height: 100%; border-radius: 50%; background: var(--surface);
        display: flex; align-items: center; justify-content: center;
        font-size: 11.5px; font-weight: 700; font-family: var(--font-body);
        color: var(--gauge-ink, var(--text-muted));
    }
    .mastery-green  { --gauge-fill: var(--success); --gauge-ink: var(--success-text); }
    .mastery-yellow { --gauge-fill: var(--warning); --gauge-ink: var(--warning-text); }
    .mastery-red    { --gauge-fill: var(--danger); --gauge-ink: var(--danger-text); }
    .mastery-grey   { --gauge-fill: var(--text-faint); --gauge-ink: var(--text-secondary); }
    .node-name { font-weight: 600; color: var(--text-primary); flex: 1; }
    .node-desc { font-size: 11px; color: var(--text-muted); }
    .node-toggle { color: var(--text-muted); font-size: 11px; transition: transform 0.2s; }
    .node-toggle.open { transform: rotate(90deg); }
    .node-children { display: none; }
    .node-children.open { display: block; }
    .legend { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; font-size: 12px; }
    .legend-item { display: flex; align-items: center; gap: 6px; }
    .legend-dot { width: 14px; height: 14px; border-radius: 50%; }
    .empty-state { padding: 60px 20px; text-align: center; color: var(--text-muted); }
    .alert-success { background: var(--success-tint); color: var(--success-text); padding: 10px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
</style>

<div class="page-title">{{ __("My Knowledge Map") }}</div>
<div class="page-desc">{{ __("See your mastery level across learning objectives.") }}</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

<form method="GET" action="{{ route('student.diagnostic.knowledge-map') }}">
    <div class="filter-card">
        <div class="filter-group">
            <label class="filter-label">{{ __("Subject") }}</label>
            <select class="filter-select" name="subject_id" data-auto-submit>
                <option value="">{{ __("-- Select Subject --") }}</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ $subject?->id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        @if($subject)
            <div style="margin-inline-start:auto; display:flex; align-items:flex-end;">
                <a href="{{ route('student.diagnostic.test', ['subject_id' => $subject->id]) }}" class="btn btn-primary">
                    {{ __("Take Diagnostic Test") }}
                </a>
            </div>
        @endif
    </div>
</form>

@if($subject)
    @php
        $flatTree = collect();
        $flatten = function($nodes) use (&$flatten, &$flatTree) {
            foreach ($nodes as $n) {
                $flatTree->push($n);
                if (!empty($n['children'])) $flatten($n['children']);
            }
        };
        $flatten($tree);
        $assessed   = $flatTree->filter(fn($n) => $n['mastery_percent'] !== null);
        $mastered   = $assessed->filter(fn($n) => $n['level'] === \App\Domain\MasteryLevel::Mastered)->count();
        $developing = $assessed->filter(fn($n) => $n['level'] === \App\Domain\MasteryLevel::Developing)->count();
        $needsWork  = $assessed->filter(fn($n) => $n['level'] === \App\Domain\MasteryLevel::NeedsWork)->count();
        $avgMastery = $assessed->isNotEmpty() ? round($assessed->avg('mastery_percent'), 1) : null;
    @endphp

    @if($assessed->isNotEmpty())
        <div class="summary-bar">
            <div class="summary-stat">
                <div class="stat-value" style="color:var(--success-text);">{{ $mastered }}</div>
                <div class="stat-label">{{ __("Mastered") }}</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value" style="color:var(--warning-text);">{{ $developing }}</div>
                <div class="stat-label">{{ __("Developing") }}</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value" style="color:var(--danger-text);">{{ $needsWork }}</div>
                <div class="stat-label">{{ __("Needs Work") }}</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value">{{ $avgMastery }}%</div>
                <div class="stat-label">{{ __("Overall Avg") }}</div>
            </div>
        </div>
    @endif

    <div class="legend">
        <div class="legend-item"><div class="legend-dot" style="background:var(--success-tint); border:1px solid var(--success-border);"></div> {{ __("Mastered (≥70%)") }}</div>
        <div class="legend-item"><div class="legend-dot" style="background:var(--warning-tint); border:1px solid var(--warning-border);"></div> {{ __("Developing (40–69%)") }}</div>
        <div class="legend-item"><div class="legend-dot" style="background:var(--danger-tint); border:1px solid var(--danger-border);"></div> {{ __("Needs Work (<40%)") }}</div>
        <div class="legend-item"><div class="legend-dot" style="background:var(--border-soft); border:1px solid var(--border);"></div> {{ __("Not Assessed") }}</div>
    </div>

    <div class="card">
        @if(count($tree) > 0)
            <div class="tree">
                <ul>
                    @foreach($tree as $node)
                        @include('components.diagnostic.tree-node', ['node' => $node])
                    @endforeach
                </ul>
            </div>
        @else
            <div class="empty-state">
                <div style="font-size:48px; margin-bottom:12px;">🗺️</div>
                <div style="font-size:15px; font-weight:700; color:var(--text-strong); margin-bottom:8px;">{{ __("No objectives defined yet") }}</div>
                <div>{{ __("Your teacher hasn't set up learning objectives for :subject yet.", ['subject' => $subject->name]) }}</div>
            </div>
        @endif
    </div>
@endif

</x-layouts.app>
