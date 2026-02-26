@extends('layouts.app')

@section('title', 'Dashboard')

@section('head')
@endsection

@section('content')
<!-- Include Header -->
@include('dashboard::layouts.header')

<!-- Include Sidebar -->
@include('dashboard::layouts.sidebar')

<div class="main-content-modern" id="mainContent">
    <!-- Original Dashboard Content -->
    <div id="dashboardContent">
    <!-- Welcome Section -->
    <div class="welcome-section" style="padding-top: 32px; margin-top: 24px;">
        <h1 class="welcome-title">Welcome Back!</h1>
        <p class="welcome-subtitle">SK OnePortal Admin Dashboard</p>
    </div>

    <!-- Administrator Information -->
    <div class="dashboard-card-modern mb-8">
        <h2 class="section-title-modern">Administrator Information</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="info-item-modern">
                    <div class="info-icon-modern">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label-modern">Full Name</div>
                        <div class="info-value-modern">{{ $user->name }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-item-modern">
                    <div class="info-icon-modern">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label-modern">Email Address</div>
                        <div class="info-value-modern">{{ $user->email }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-item-modern">
                    <div class="info-icon-modern">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12,6 12,12 16,14"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label-modern">Last Login</div>
                        <div class="info-value-modern">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('F d, Y g:i A') }}
                            @else
                                First login
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-item-modern">
                    <div class="info-icon-modern">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label-modern">Last Login IP</div>
                        <div class="info-value-modern">{{ $user->last_login_ip ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-item-modern">
                    <div class="info-icon-modern">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label-modern">Two-Factor Authentication</div>
                        <div class="info-value-modern">
                            @if($user->two_factor_confirmed_at)
                                <span class="status-badge-modern enabled">
                                    <span class="status-indicator-modern"></span>
                                    Enabled
                                </span>
                            @else
                                <span class="status-badge-modern disabled">
                                    <span class="status-indicator-modern"></span>
                                    Not Enabled
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-item-modern">
                    <div class="info-icon-modern">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label-modern">Account Status</div>
                        <div class="info-value-modern">
                            <span class="status-badge-modern enabled">
                                <span class="status-indicator-modern"></span>
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-card-modern mb-8">
        <h2 class="section-title-modern">Quick Actions</h2>
        <div class="row">
            @if($user->two_factor_confirmed_at)
            <div class="col-lg-4 col-md-6">
                <div class="quick-action-card-modern" onclick="window.location.href='{{ route('two-factor.recovery-codes') }}'">
                    <div class="action-icon-modern">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div class="action-title-modern">Recovery Codes</div>
                    <div class="action-description-modern">Access your 2FA backup codes</div>
                </div>
            </div>
            @endif

            <div class="col-lg-4 col-md-6">
                <div class="quick-action-card-modern">
                    <div class="action-icon-modern">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="action-title-modern">User Management</div>
                    <div class="action-description-modern">Manage system users</div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="quick-action-card-modern">
                    <div class="action-icon-modern">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="action-title-modern">Analytics</div>
                    <div class="action-description-modern">View system analytics</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Information -->
    <div class="security-info-modern">
        <h3 class="security-title-modern">Security Information</h3>
        <p class="security-text-modern">
            Your session will expire after 45 minutes of inactivity. All administrative actions are logged and monitored for security purposes. 
            Please ensure you log out when finished to maintain account security.
        </p>
    </div>
</div>
</div>
@endsection

@section('scripts')
    <script>
        function toggleProfile() {
            const profileContent = document.getElementById('profileContent');
            const dashboardContent = document.getElementById('dashboardContent');
            
            if (profileContent.style.display === 'none') {
                profileContent.style.display = 'block';
                dashboardContent.style.display = 'none';
            } else {
                profileContent.style.display = 'none';
                dashboardContent.style.display = 'block';
            }
        }
    </script>
@endsection
