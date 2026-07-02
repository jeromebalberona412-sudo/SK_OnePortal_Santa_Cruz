<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications — SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Profile/assets/css/notification.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content" id="mainContent">
<div class="notif-page-container">

    <div class="notif-page-header">
        <div class="notif-page-header-left">
            <h1 class="notif-page-title">Notifications</h1>
            <p class="notif-page-sub">Stay updated with KK profiling requests, survey responses, and program alerts.</p>
        </div>
        <button class="notif-page-mark-all" id="pageMarkAllBtn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Mark all as read
        </button>
    </div>

    @php
        $totalCount = count($notifications ?? []);
        $unreadTotal = (int) ($unreadCount ?? collect($notifications ?? [])->where('unread', true)->count());
        $readTotal = max(0, $totalCount - $unreadTotal);
    @endphp

    <div class="notif-stats-row">
        <div class="notif-stat-card">
            <span class="notif-stat-value" id="totalCount">{{ $totalCount }}</span>
            <span class="notif-stat-label">Total</span>
        </div>
        <div class="notif-stat-card notif-stat-unread">
            <span class="notif-stat-value" id="unreadCount">{{ $unreadTotal }}</span>
            <span class="notif-stat-label">Unread</span>
        </div>
        <div class="notif-stat-card notif-stat-read">
            <span class="notif-stat-value" id="readCount">{{ $readTotal }}</span>
            <span class="notif-stat-label">Read</span>
        </div>
    </div>

    <div class="notif-filter-bar">
        <button class="notif-filter-btn active" data-filter="all">All</button>
        <button class="notif-filter-btn" data-filter="unread">Unread</button>
        <button class="notif-filter-btn" data-filter="read">Read</button>
    </div>

    <div class="notif-page-list" id="notifPageList" @if($totalCount === 0) style="display:none;" @endif>
        @foreach(($notifications ?? []) as $notification)
            <div
                class="notif-page-item {{ ($notification['unread'] ?? false) ? 'notif-page-unread' : '' }}"
                data-id="{{ $notification['id'] }}"
                data-category="{{ $notification['category'] ?? 'general' }}"
                data-action-url="{{ $notification['action_url'] ?? '' }}"
                role="button"
                tabindex="0"
            >
                <div class="notif-page-body">
                    <div class="notif-page-category">{{ $notification['category_label'] ?? 'General' }}</div>
                    <div class="notif-page-title">{{ $notification['title'] }}</div>
                    <div class="notif-page-text">{{ $notification['text'] }}</div>
                    <div class="notif-page-time">{{ $notification['time'] }}</div>
                </div>
                <div class="notif-page-actions">
                    @if($notification['unread'] ?? false)
                        <span class="notif-page-dot"></span>
                        <button class="notif-page-read-btn" data-id="{{ $notification['id'] }}" title="Mark as read" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </button>
                    @else
                        <button class="notif-page-read-btn notif-read-done" data-id="{{ $notification['id'] }}" title="Already read" type="button" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="notif-page-empty" id="notifPageEmpty" @if($totalCount > 0) style="display:none;" @endif>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <h3>No notifications</h3>
        <p>You're all caught up! New KK profiling requests and survey responses will appear here.</p>
    </div>

</div>
</main>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Profile/assets/js/notification.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
