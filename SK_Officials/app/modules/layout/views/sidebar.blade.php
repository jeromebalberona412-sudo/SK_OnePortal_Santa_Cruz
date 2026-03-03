<aside class="main-sidebar" id="mainSidebar">
    <div class="sidebar-content">

        <div class="sidebar-admin">
            
            <div class="admin-avatar">
                <!-- Admin Logo Image -->
                <img src="{{ asset('images/logo.png') }}" alt="Admin Logo" class="admin-logo">
            </div>

            <div class="admin-info">
                <span class="admin-name">Sk Officials User</span>
                <span class="admin-role">Calios</span>
            </div>

        </div>

        <nav class="sidebar-nav">
            <ul class="nav-list">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link active">

                        <!-- Clean Dashboard Icon -->
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" 
                             viewBox="0 0 24 24" 
                             fill="none" 
                             stroke="currentColor" 
                             stroke-width="2" 
                             stroke-linecap="round" 
                             stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>

                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <!-- Profile -->
                <li class="nav-item">
                    <a href="{{ route('profile') }}" class="nav-link">

                        <!-- Clean Profile Icon -->
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" 
                             viewBox="0 0 24 24" 
                             fill="none" 
                             stroke="currentColor" 
                             stroke-width="2" 
                             stroke-linecap="round" 
                             stroke-linejoin="round">
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                        </svg>

                        <span class="nav-text">Profile</span>
                    </a>
                </li>

            </ul>
        </nav>

    </div>
</aside>