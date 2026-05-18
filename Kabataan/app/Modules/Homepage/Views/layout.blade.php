<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0450a8">
    <meta name="description" content="SK OnePortal Kabataan - Youth Community Platform for Santa Cruz, Laguna">
    <title>@yield('title', 'SK OnePortal Kabataan')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Homepage/assets/css/about.css',
        'app/Modules/Homepage/assets/css/pages.css',
        'app/Modules/Homepage/assets/css/faqs.css',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])

    @stack('styles')
</head>
<body class="homepage-body @if(Route::currentRouteName() === 'about') about-body @endif @if(Route::currentRouteName() === 'faqs') faqs-body @endif">
    {{-- LOADING OVERLAY --}}
    @include('dashboard::loading')

    {{-- TOP NAVBAR (shared kabataan markup) --}}
    <nav class="kabataan-nav" aria-label="Primary navigation">
        <div class="kabataan-nav-inner">
            <a href="{{ route('homepage') }}" class="kabataan-brand">
                <img src="/images/skoneportal_logo.webp" alt="Kabataan logo" class="kabataan-brand-logo">
                <span class="kabataan-brand-copy">
                    <strong>Kabataan</strong>
                    <small>SK OnePortal Santa Cruz</small>
                </span>
            </a>

            <div class="kabataan-nav-links" id="kabataanNavLinks">
                <a href="{{ route('homepage') }}" class="kabataan-nav-link @if(Route::currentRouteName() === 'homepage') active @endif">Home</a>
                <a href="{{ route('about') }}" class="kabataan-nav-link @if(Route::currentRouteName() === 'about') active @endif">About</a>
                <a href="{{ route('faqs') }}" class="kabataan-nav-link @if(Route::currentRouteName() === 'faqs') active @endif">FAQs</a>
                <a href="{{ route('contact') }}" class="kabataan-nav-link @if(Route::currentRouteName() === 'contact') active @endif">Contact</a>
            </div>

            <div class="kabataan-nav-actions">
                <a href="#" class="kabataan-nav-secondary" data-action="signin">Sign In</a>
                <a href="#" class="kabataan-nav-primary" data-action="signup">Sign Up</a>
                <button type="button" class="kabataan-nav-toggle" id="kabataanNavToggle" aria-label="Open menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <div class="kabataan-drawer" id="kabataanDrawer" aria-hidden="true">
        <a href="{{ route('homepage') }}" class="kabataan-drawer-link @if(Route::currentRouteName() === 'homepage') active @endif">Home</a>
        <a href="{{ route('about') }}" class="kabataan-drawer-link @if(Route::currentRouteName() === 'about') active @endif">About</a>
        <a href="{{ route('faqs') }}" class="kabataan-drawer-link @if(Route::currentRouteName() === 'faqs') active @endif">FAQs</a>
        <a href="{{ route('contact') }}" class="kabataan-drawer-link @if(Route::currentRouteName() === 'contact') active @endif">Contact</a>
        <div class="kabataan-drawer-actions">
            <a href="#" class="kabataan-nav-secondary" data-action="signin">Sign In</a>
            <a href="#" class="kabataan-nav-primary" data-action="signup">Sign Up</a>
        </div>
    </div>

    {{-- Sign In/Sign Up Confirmation Modal --}}
    <div class="kabataan-modal" id="authModal" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="kabataan-modal-backdrop" data-close-modal></div>
        <div class="kabataan-modal-content">
            <div class="kabataan-modal-decoration">
                <div class="modal-sparkle modal-sparkle-1">✨</div>
                <div class="modal-sparkle modal-sparkle-2">⭐</div>
                <div class="modal-sparkle modal-sparkle-3">💫</div>
            </div>
            
            <button type="button" class="kabataan-modal-close" data-close-modal aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
            
            <div class="kabataan-modal-emoji" id="modalEmoji">🚀</div>
            
            <div class="kabataan-modal-header">
                <h3 id="modalTitle">Ready to Join?</h3>
                <p id="modalSubtitle">Let's get you started!</p>
            </div>

            <div class="kabataan-modal-body">
                <p id="modalMessage">You're about to join the coolest youth community in Santa Cruz! Ready?</p>
            </div>

            <div class="kabataan-modal-actions">
                <button type="button" class="kabataan-modal-btn kabataan-modal-btn-secondary" data-close-modal>
                    <span>Maybe Later</span>
                </button>
                <button type="button" class="kabataan-modal-btn kabataan-modal-btn-primary" id="modalConfirm">
                    <span id="modalConfirmText">Let's Go!</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <main class="kabataan-main">
        @yield('content')
    </main>

    <footer class="kabataan-footer">
        <div class="kabataan-shell kabataan-footer-inner">
            <div class="kabataan-footer-brand">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="kabataan-footer-logo">
                <div>
                    <strong>Kabataan</strong>
                    <p>SK OnePortal Santa Cruz</p>
                </div>
            </div>
            <p>&copy; {{ date('Y') }} SK OnePortal. Built for the youth of Santa Cruz, Laguna.</p>
        </div>
    </footer>

    {{-- SCRIPTS --}}
    @vite('app/Modules/Homepage/assets/js/homepage.js')
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hamburger = document.getElementById('kabataanNavToggle');
            const drawer = document.getElementById('kabataanDrawer');
            const authModal = document.getElementById('authModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalSubtitle = document.getElementById('modalSubtitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalConfirm = document.getElementById('modalConfirm');
            let pendingAction = null;

            // Hamburger menu
            if (hamburger && drawer) {
                hamburger.addEventListener('click', () => drawer.classList.toggle('open'));
                document.addEventListener('click', (e) => {
                    if (!hamburger.contains(e.target) && !drawer.contains(e.target)) {
                        drawer.classList.remove('open');
                    }
                });
            }

            // Auth modal handlers
            const authButtons = document.querySelectorAll('[data-action="signin"], [data-action="signup"]');
            const modalEmoji = document.getElementById('modalEmoji');
            const modalConfirmText = document.getElementById('modalConfirmText');
            
            authButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const action = button.dataset.action;
                    pendingAction = action;

                    if (action === 'signin') {
                        modalEmoji.textContent = '👋';
                        modalTitle.textContent = 'Welcome Back, Kabataan!';
                        modalSubtitle.textContent = 'Ready to dive back in?';
                        modalMessage.textContent = 'Sign in to access your account, connect with your community, and stay updated with the latest programs!';
                        modalConfirmText.textContent = "Let's Go!";
                    } else {
                        modalEmoji.textContent = '🎉';
                        modalTitle.textContent = 'Join the Squad!';
                        modalSubtitle.textContent = 'Your journey starts here';
                        modalMessage.textContent = 'Create your account and become part of Santa Cruz\'s most active youth community. Let\'s make a difference together!';
                        modalConfirmText.textContent = 'Sign Me Up!';
                    }

                    authModal.classList.add('open');
                    authModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    drawer?.classList.remove('open');
                });
            });

            // Modal confirm button
            modalConfirm.addEventListener('click', () => {
                if (pendingAction === 'signin') {
                    window.location.href = "{{ route('login') }}";
                } else if (pendingAction === 'signup') {
                    window.location.href = "{{ route('register') }}";
                }
            });

            // Close modal handlers
            const closeModalButtons = document.querySelectorAll('[data-close-modal]');
            closeModalButtons.forEach(button => {
                button.addEventListener('click', () => {
                    authModal.classList.remove('open');
                    authModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    pendingAction = null;
                });
            });

            // Close modal on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && authModal.classList.contains('open')) {
                    authModal.classList.remove('open');
                    authModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    pendingAction = null;
                }
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(a => {
                a.addEventListener('click', function(e) {
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        drawer?.classList.remove('open');
                    }
                });
            });
        });
    </script>
</body>
</html>
