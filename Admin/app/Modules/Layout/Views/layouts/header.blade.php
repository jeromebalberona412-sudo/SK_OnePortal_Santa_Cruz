<!-- Header -->
@auth
<header class="top-nav" id="topNav">
    <!-- Left Section with Toggle Button -->
    <div class="flex items-center space-x-4">
        <!-- Sidebar Toggle Button -->
        <button class="sidebar-toggle-btn" id="sidebarToggle" onclick="toggleSidebar()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </div>
    
    <!-- Right Section -->
    <div class="flex items-center space-x-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout-modern">Logout</button>
        </form>
    </div>
</header>
@endauth