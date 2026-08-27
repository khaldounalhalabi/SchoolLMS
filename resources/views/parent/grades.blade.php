<x-layouts.app :pageTitle="__('Children\'s Grades')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }
        .empty-state {
            text-align: center; padding: 80px 20px; color: var(--text-faint);
            background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
        }
        .empty-state svg { margin-bottom: 16px; }
        .empty-state h3 { font-family: var(--font-display); font-size: 18px; color: var(--text-muted); margin-bottom: 8px; }
        .empty-state p { font-size: 13px; color: var(--text-faint); }
    </style>

    <div class="page-header">
        <h2>{{ __("Children's Grades") }}</h2>
        <p>{{ __("View your children's academic results") }}</p>
    </div>

    <div class="empty-state">
        <svg width="56" height="56" fill="none" stroke="var(--border)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        <h3>{{ __("Grades Module") }}</h3>
        <p>{{ __("This feature is coming soon. You'll be able to view your children's grades and academic performance here.") }}</p>
    </div>
</x-layouts.app>
