@extends('layouts.app')

@section('title', 'My Profile')

@section('head')
@vite(['app/Modules/Profile/assets/css/profile.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="profile-page">
    @php
        $userName  = $user->name  ?? 'Admin';
        $userEmail = $user->email ?? '';
        $userRole  = match($user->role ?? '') {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            default       => 'Admin',
        };
        $nameParts = preg_split('/\s+/', trim($userName)) ?: [];
        $initials  = '';
        foreach (array_slice($nameParts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        if ($initials === '') { $initials = 'AD'; }
        $createdAt = $user->created_at?->format('M d, Y') ?? '—';
        $lastLogin = $user->last_login_at?->format('M d, Y h:i A') ?? '—';
    @endphp

    <div class="profile-shell">

        {{-- ── Hero ─────────────────────────────────────────── --}}
        <header class="profile-hero">
            <div class="profile-avatar-frame" aria-hidden="true">
                <span class="profile-avatar-fallback" style="display:grid;">{{ $initials }}</span>
            </div>
            <div class="profile-hero__identity">
                <p class="profile-eyebrow">System User</p>
                <h1 class="profile-title">{{ $userName }}</h1>
                <p class="profile-subtitle">{{ $userEmail }}</p>
                <span class="profile-role-badge">{{ $userRole }}</span>
            </div>
        </header>

        {{-- ── Tabs ─────────────────────────────────────────── --}}
        <section class="profile-panel">
            <div class="profile-tabs" role="tablist" aria-label="Profile Sections">
                <button type="button" class="tab-button active" data-tab="basic"    onclick="switchTab('basic',    event)">Basic Info</button>
                <button type="button" class="tab-button"        data-tab="security" onclick="switchTab('security', event)">Security</button>
            </div>

            <div class="profile-panel__body">

                {{-- Basic Info --}}
                <div id="basic-tab" class="tab-pane active profile-pane">
                    <h3>Basic Information</h3>
                    <div class="form-grid">
                        <div class="field-group">
                            <label>Full Name</label>
                            <input type="text" value="{{ $userName }}" readonly>
                        </div>
                        <div class="field-group">
                            <label>Email Address</label>
                            <input type="email" value="{{ $userEmail }}" readonly>
                        </div>
                        <div class="field-group">
                            <label>Role</label>
                            <input type="text" value="{{ $userRole }}" readonly>
                        </div>
                    </div>

                    <div class="profile-sysinfo-divider"></div>
                    <h3>System Information</h3>
                    <div class="form-grid">
                        <div class="field-group">
                            <label>Last Login</label>
                            <input type="text" value="{{ $lastLogin }}" readonly>
                        </div>
                        <div class="field-group">
                            <label>Account Created</label>
                            <input type="text" value="{{ $createdAt }}" readonly>
                        </div>
                    </div>
                </div>

                {{-- Security --}}
                <div id="security-tab" class="tab-pane profile-pane" hidden>
                    <h3>Security</h3>

                    <div class="security-option-row">
                        <div class="security-option-info">
                            <p class="security-option-title">Change Password</p>
                            <p class="security-option-desc">Update your account password via email verification.</p>
                        </div>
                        <a href="{{ route('profile.change-password') }}" class="btn btn-primary security-action-btn">Change Password</a>
                    </div>

                    <div class="security-option-row" style="margin-top:0.75rem;">
                        <div class="security-option-info">
                            <p class="security-option-title">Change Email</p>
                            <p class="security-option-desc">Update your account email with verification.</p>
                        </div>
                        <a href="{{ route('profile.change-email') }}" class="btn btn-primary security-action-btn">Change Email</a>
                    </div>
                </div>

            </div>
        </section>

    </div>{{-- /.profile-shell --}}
</div>{{-- /#mainContent --}}

<script>
function switchTab(tabName, e) {
    document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => { p.classList.remove('active'); p.hidden = true; });
    const btn = (e && e.target) ? e.target : document.querySelector('.tab-button[data-tab="' + tabName + '"]');
    if (btn) btn.classList.add('active');
    const pane = document.getElementById(tabName + '-tab');
    if (pane) { pane.classList.add('active'); pane.hidden = false; }
}
</script>
@endsection
