@extends('layout::app')

@section('title', 'Notifications — SK OnePortal')

@section('content')
    <div class="page-header">
        <h1>Notifications</h1>
        <p>Stay updated on federation activities and account requests.</p>
    </div>

    <div class="notif-page-card">
        <div class="notif-page-list">
            @foreach($notifications as $notification)
                <article class="notif-page-item {{ ($notification['unread'] ?? false) ? 'notif-unread' : '' }}">
                    <div class="notif-icon">
                        <i class="fas {{ $notification['icon'] ?? 'fa-bell' }}"></i>
                    </div>
                    <div class="notif-content">
                        <h3 class="notif-item-title">{{ $notification['title'] }}</h3>
                        <p class="notif-item-text">{{ $notification['text'] }}</p>
                        <div class="notif-item-time">{{ $notification['time'] }}</div>
                    </div>
                    @if($notification['unread'] ?? false)
                        <span class="notif-unread-dot"></span>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/notifications/css/notifications.css') }}?v={{ @filemtime(app_path('Modules/Notifications/assets/css/notifications.css')) ?: time() }}">
@endpush
