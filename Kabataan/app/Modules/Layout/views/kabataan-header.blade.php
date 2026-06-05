@php
    $headerUser = $user ?? auth()->user();
    $userName = $headerUser->name ?? 'Youth User';
    $userEmail = $headerUser->email ?? 'youth@skportal.com';
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=667eea&color=fff';
    $showSearch = $showSearch ?? true;
    $pageBadge = $pageBadge ?? null;
@endphp

<nav class="kabataan-header" id="kabataanHeader" aria-label="Main navigation">
    <div class="kabataan-header__container">
        <a href="{{ route('dashboard') }}" class="kabataan-header__brand">
            <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="kabataan-header__logo">
            <span class="kabataan-header__title">
                Kabataan
                <small>SK OnePortal Santa Cruz</small>
            </span>
        </a>

        @if ($pageBadge)
            <span class="kabataan-header__page-badge">{{ $pageBadge }}</span>
        @endif

        @if ($showSearch)
        <div class="kabataan-header__search">
            <svg class="kabataan-header__search-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
            </svg>
            <input type="search" class="kabataan-header__search-input" placeholder="Search posts, programs, announcements..." autocomplete="off">
        </div>
        @endif

        <div class="kabataan-header__actions">
            <a href="{{ route('dashboard') }}" class="kabataan-header__icon-btn" title="Home" aria-label="Home">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
            </a>

            @include('dashboard::notification')
            @include('dashboard::chatbot')

            <div class="kabataan-header__user" id="kabataanHeaderUser">
                <button type="button" class="kabataan-header__avatar-btn user-avatar-btn" aria-expanded="false" aria-haspopup="true">
                    <img src="{{ $avatarUrl }}" alt="{{ $userName }}">
                </button>
                <div class="kabataan-header__dropdown user-dropdown">
                    <div class="kabataan-header__dropdown-head dropdown-header">
                        <img src="{{ $avatarUrl }}" alt="">
                        <div>
                            <p class="user-name">{{ $userName }}</p>
                            <p class="user-email">{{ $userEmail }}</p>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile') }}" class="kabataan-header__dropdown-link dropdown-item">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        My Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="kabataan-header__dropdown-link dropdown-item logout-btn">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

@include('layout::kabataan-logout-modal')
