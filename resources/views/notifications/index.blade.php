<x-layouts.app :pageTitle="__('Notifications')">
    <style>
        .notifications-header {
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .notifications-title {
            font-family: var(--font-display); font-size: 20px; font-weight: 700;
            color: var(--text-primary);
        }
        .notifications-subtitle { margin-top: 4px; font-size: 13px; color: var(--text-muted); }
        .notifications-list {
            background: var(--surface); border: 1px solid var(--border-soft);
            border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-card);
        }
        .notification-item {
            display: flex; align-items: flex-start; gap: 14px; padding: 18px 20px;
            border-bottom: 1px solid var(--border-soft); text-decoration: none;
            color: inherit; transition: background 0.15s;
        }
        .notification-item:last-child { border-bottom: none; }
        .notification-item:hover { background: var(--surface-2); }
        .notification-item.unread { background: var(--primary-tint); }
        .notification-icon {
            width: 36px; height: 36px; flex-shrink: 0; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary-dark); background: var(--surface);
        }
        .notification-icon svg { width: 18px; height: 18px; }
        .notification-content { flex: 1; min-width: 0; }
        .notification-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
        .notification-message { margin-top: 4px; font-size: 13px; color: var(--text-secondary); line-height: 1.5; }
        .notification-time { margin-top: 7px; font-size: 11px; color: var(--text-muted); }
        .notification-dot { width: 7px; height: 7px; margin-top: 7px; border-radius: 50%; background: var(--primary); flex-shrink: 0; }
        .notification-empty { padding: 52px 20px; text-align: center; color: var(--text-muted); font-size: 14px; }
        .notification-actions { display: flex; align-items: center; gap: 8px; }
        .notification-action {
            padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border);
            background: var(--surface); color: var(--text-secondary); font: 600 12px var(--font-body); cursor: pointer;
        }
        .notification-action:hover { border-color: var(--primary); color: var(--primary-dark); }
        .pagination { display: flex; justify-content: center; margin-top: 20px; }
    </style>

    <div class="notifications-header">
        <div>
            <div class="notifications-title">{{ __('Notifications') }}</div>
            <div class="notifications-subtitle">{{ __('Stay up to date with activity in your account.') }}</div>
        </div>
        @if($notifications->contains(fn ($notification) => $notification->read_at === null))
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="notification-action" type="submit">{{ __('Mark all as read') }}</button>
            </form>
        @endif
    </div>

    <div class="notifications-list">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $notificationTitle = __($data['title_key'] ?? $data['title'] ?? 'Notification');
                $notificationMessage = __($data['message_key'] ?? $data['message'] ?? '', $data['message_data'] ?? []);
            @endphp
            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                @csrf
                <button type="submit" class="notification-item {{ $notification->read_at ? '' : 'unread' }}" style="width: 100%; border-top: 0; border-inline: 0; text-align: start; cursor: pointer;">
                    <span class="notification-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </span>
                    <span class="notification-content">
                        <span class="notification-title">{{ $notificationTitle }}</span>
                        <span class="notification-message">{{ $notificationMessage }}</span>
                        <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                    @if(!$notification->read_at)
                        <span class="notification-dot" aria-label="{{ __('Unread') }}"></span>
                    @endif
                </button>
            </form>
        @empty
            <div class="notification-empty">{{ __('You have no notifications yet.') }}</div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="pagination">{{ $notifications->links() }}</div>
    @endif
</x-layouts.app>
