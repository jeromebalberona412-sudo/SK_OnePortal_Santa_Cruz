@php
    $items = $notifications ?? [];
    $hasItems = count($items) > 0;
@endphp

<div class="notif-popover" id="notifPopover" role="menu" aria-label="Notifications">
    <div class="notif-popover-header">
        <div class="notif-popover-title">
            <i class="fas fa-bell"></i>
            <h4>Notifications</h4>
            <span class="notif-count-pill" id="notifCountPill" style="{{ ($unreadNotificationCount ?? 0) > 0 ? '' : 'display: none;' }}">{{ $unreadNotificationCount ?? 0 }}</span>
        </div>
        <button type="button" class="notif-mark-all" id="notifMarkAllBtn" title="Mark all as read">
            <i class="fas fa-check-double"></i>
            Mark all as read
        </button>
    </div>

    <div class="notif-list" id="notifList" @if(! $hasItems) style="display: none;" @endif>
        @foreach($items as $notification)
            <div class="notif-item {{ ($notification['unread'] ?? false) ? 'notif-unread' : '' }}" data-id="{{ $notification['id'] }}">
                <div class="notif-icon">
                    <i class="fas {{ $notification['icon'] ?? 'fa-bell' }}"></i>
                </div>
                <div class="notif-content">
                    <div class="notif-item-title">{{ $notification['title'] }}</div>
                    <div class="notif-item-text">{{ $notification['text'] }}</div>
                    <div class="notif-item-time">{{ $notification['time'] }}</div>
                </div>
                @if($notification['unread'] ?? false)
                    <span class="notif-unread-dot"></span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="notif-empty" id="notifEmpty" @if($hasItems) style="display: none;" @endif>
        <i class="fas fa-bell-slash"></i>
        <p>No notifications yet</p>
    </div>

    <div class="notif-popover-footer">
        <a href="{{ route('notifications.index') }}" class="notif-see-all-btn">
            See All Notifications
        </a>
    </div>
</div>
