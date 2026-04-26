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
                <button type="button" class="tab-button"        data-tab="sysinfo"  onclick="switchTab('sysinfo',  event)">System Info</button>
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
                </div>

                {{-- Security --}}
                <div id="security-tab" class="tab-pane profile-pane" hidden>
                    <h3>Change Password</h3>
                    <form id="changePasswordForm" class="form-grid" onsubmit="submitChangePassword(event)">
                        @csrf
                        <div class="field-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>
                        </div>
                        <div class="field-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" id="new_password" name="password" placeholder="Min. 8 characters" required minlength="8">
                        </div>
                        <div class="field-group">
                            <label for="new_password_confirmation">Confirm New Password *</label>
                            <input type="password" id="new_password_confirmation" name="password_confirmation" placeholder="Repeat new password" required>
                        </div>
                        <div class="field-group field-group--full">
                            <p id="pwFeedback" class="pw-feedback" aria-live="polite"></p>
                        </div>
                        <div class="field-group field-group--full" style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary" id="changePwBtn">Change Password</button>
                        </div>
                    </form>
                </div>

                {{-- System Info --}}
                <div id="sysinfo-tab" class="tab-pane profile-pane" hidden>
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

function submitChangePassword(e) {
    e.preventDefault();
    const form    = document.getElementById('changePasswordForm');
    const btn     = document.getElementById('changePwBtn');
    const feedback = document.getElementById('pwFeedback');
    const np      = document.getElementById('new_password').value;
    const nc      = document.getElementById('new_password_confirmation').value;

    feedback.className = 'pw-feedback';
    feedback.textContent = '';

    if (np !== nc) {
        feedback.textContent = 'New passwords do not match.';
        feedback.classList.add('pw-feedback--error');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Saving…';

    const data = new FormData(form);

    fetch('{{ route("profile.password.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: data,
    })
    .then(r => r.json())
    .then(json => {
        if (json.success) {
            feedback.textContent = 'Password changed successfully.';
            feedback.classList.add('pw-feedback--success');
            form.reset();
        } else {
            const msg = json.message || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Failed to change password.');
            feedback.textContent = msg;
            feedback.classList.add('pw-feedback--error');
        }
    })
    .catch(() => {
        feedback.textContent = 'An error occurred. Please try again.';
        feedback.classList.add('pw-feedback--error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Change Password';
    });
}
</script>
@endsection
