<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Dashboard')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    @stack('styles')
    
    <style>
        /* Template-based Layout Styles */
        :root {
            --layout-vertical-nav-width: 260px;
            --layout-navbar-height: 64px;
            --layout-overlay-z-index: 11;
            --layout-vertical-nav-z-index: 12;
        }
        
        * {
            box-sizing: border-box;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
        }
        
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
        }
        
        /* Vertical Nav (Sidebar) */
        .layout-vertical-nav {
            position: fixed;
            z-index: var(--layout-vertical-nav-z-index);
            top: 0;
            left: 0;
            width: var(--layout-vertical-nav-width);
            height: 100vh;
            background-color: #fff;
            box-shadow: 0 0 0.375rem 0.25rem rgba(161, 172, 184, 0.15);
            transition: transform 0.25s ease-in-out;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .layout-vertical-nav.overlay-nav:not(.visible) {
            transform: translateX(-100%);
        }
        
        .layout-vertical-nav.overlay-nav.visible {
            transform: translateX(0) !important;
        }
        
        /* Desktop: Show sidebar by default */
        @media (min-width: 992px) {
            .layout-vertical-nav.overlay-nav {
                transform: translateX(0);
            }
        }
        
        /* Nav Header (Logo Section) */
        .nav-header {
            display: flex;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            min-height: var(--layout-navbar-height);
        }
        
        .app-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }
        
        .app-logo h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Nav Items */
        .nav-items {
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
        }
        
        .nav-link {
            margin: 0.25rem 0.75rem;
        }
        
        .nav-link a {
            display: flex;
            align-items: center;
            padding: 0.625rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            color: #697a8d;
            transition: all 0.2s ease;
            font-weight: 400;
        }
        
        .nav-link a:hover {
            background-color: rgba(105, 122, 141, 0.08);
            color: #1e3c72;
        }
        
        .nav-link a.active {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            font-weight: 500;
        }
        
        .nav-item-icon {
            font-size: 1.25rem;
            margin-right: 0.75rem;
            width: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .nav-item-title {
            flex: 1;
        }
        
        /* Navbar (Header) */
        .layout-navbar {
            position: sticky;
            top: 0;
            z-index: var(--layout-overlay-z-index);
            height: var(--layout-navbar-height);
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            box-shadow: 0 1px 0 0 rgba(0, 0, 0, 0.06);
        }
        
        .navbar-content-container {
            display: flex;
            align-items: center;
            height: 100%;
            padding: 0 1.5rem;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Content Wrapper */
        .layout-content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: var(--layout-vertical-nav-width);
            transition: margin-left 0.25s ease-in-out;
            min-height: 100vh;
            width: calc(100% - var(--layout-vertical-nav-width));
            max-width: calc(100% - var(--layout-vertical-nav-width));
            overflow-x: hidden;
        }
        
        .layout-content-wrapper.sidebar-hidden {
            margin-left: 0;
            width: 100%;
            max-width: 100%;
        }
        
        /* Page Content */
        .layout-page-content {
            flex: 1;
            padding: 1.5rem;
            background-color: #f5f5f9;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Overlay for Mobile */
        .layout-overlay {
            position: fixed;
            z-index: var(--layout-overlay-z-index);
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease-in-out;
        }
        
        .layout-overlay.visible {
            opacity: 1;
            pointer-events: auto;
        }
        
        /* Mobile Toggle Button */
        .nav-toggle-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1050;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Desktop Toggle Button */
        .sidebar-toggle-btn {
            position: relative;
            z-index: 1050;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: all 0.25s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* User Profile Dropdown */
        .user-profile-dropdown {
            margin-left: auto;
            position: relative;
        }
        
        /* Fix dropdown menu z-index to appear above everything */
        .layout-navbar .dropdown-menu {
            z-index: 1050 !important;
        }
        
        .layout-navbar .dropdown-menu.show {
            display: block !important;
        }
        
        .layout-navbar {
            overflow: visible !important;
        }
        
        .navbar-content-container {
            overflow: visible !important;
        }
        
        /* Fix dropdown on mobile */
        @media (max-width: 991.98px) {
            .layout-navbar .dropdown-menu {
                position: absolute !important;
                top: 100% !important;
                right: 0 !important;
                left: auto !important;
                margin-top: 0.5rem;
                max-width: 200px;
            }
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .layout-vertical-nav {
                transform: translateX(-100%);
            }
            
            .layout-vertical-nav.visible {
                transform: translateX(0);
            }
            
            .layout-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .layout-page-content {
                padding: 1rem;
            }
            
            .navbar-content-container {
                padding: 0 1rem;
            }
            
            .nav-toggle-btn {
                display: block;
            }
            
            .sidebar-toggle-btn {
                display: none;
            }
        }
        
        @media (min-width: 992px) {
            .nav-toggle-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <!-- Vertical Nav (Sidebar) -->
        <aside class="layout-vertical-nav overlay-nav" id="verticalNav">
            <!-- Nav Header -->
            <div class="nav-header">
                <a href="{{ route('dashboard.student') }}" class="app-logo">
                    <i class="bi bi-building-fill me-2" style="font-size: 1.5rem; color: #1e3c72;"></i>
                    <h1>{{ \App\Models\HostelDetail::getHostelDetail()->hostel_name ?? 'Hostel' }}</h1>
                </a>
            </div>
            
            <!-- Nav Items -->
            <ul class="nav-items">
                <li class="nav-link">
                    <a href="{{ route('dashboard.student') }}" class="{{ request()->routeIs('dashboard.student') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 nav-item-icon"></i>
                        <span class="nav-item-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="{{ route('dashboard.student') }}#profile-section" class="scroll-to-section">
                        <i class="bi bi-person nav-item-icon"></i>
                        <span class="nav-item-title">Profile Yangu</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="{{ route('dashboard.student') }}#room-section" class="scroll-to-section">
                        <i class="bi bi-house-door nav-item-icon"></i>
                        <span class="nav-item-title">Chumba Changu</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="{{ route('dashboard.student') }}#payments-section" class="scroll-to-section">
                        <i class="bi bi-cash-coin nav-item-icon"></i>
                        <span class="nav-item-title">Malipo Yangu</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="{{ route('dashboard.student') }}#balance-section" class="scroll-to-section">
                        <i class="bi bi-wallet2 nav-item-icon"></i>
                        <span class="nav-item-title">Balance Yangu</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="{{ route('dashboard.student') }}#suggestions-section" class="scroll-to-section">
                        <i class="bi bi-chat-left-text nav-item-icon"></i>
                        <span class="nav-item-title">Mapendekezo & Matukio</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="bi bi-gear nav-item-icon"></i>
                        <span class="nav-item-title">Settings</span>
                    </a>
                </li>
                <li class="nav-link">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline w-100" id="logoutForm">
                        @csrf
                        <button type="submit" class="w-100 text-start border-0 bg-transparent p-0 d-flex align-items-center" style="color: #dc3545; cursor: pointer; text-decoration: none;">
                            <i class="bi bi-box-arrow-right nav-item-icon"></i>
                            <span class="nav-item-title">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </aside>
        
        <!-- Content Wrapper -->
        <div class="layout-content-wrapper" id="contentWrapper">
            <!-- Navbar (Header) -->
            <header class="layout-navbar">
                <div class="navbar-content-container">
                    <!-- Mobile Toggle -->
                    <button class="nav-toggle-btn" id="mobileToggle" type="button">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <!-- Desktop Toggle -->
                    <button class="sidebar-toggle-btn" id="desktopToggle" type="button">
                        <i class="bi bi-list" id="toggleIcon"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <!-- User Profile -->
                        <div class="dropdown user-profile-dropdown">
                            <a class="dropdown-toggle d-flex align-items-center text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #697a8d;">
                                <div class="d-flex align-items-center">
                                    <img 
                                        src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&size=32&background=1e3c72&color=fff' }}" 
                                        alt="Profile" 
                                        class="rounded-circle me-2"
                                        style="width: 32px; height: 32px; object-fit: cover;"
                                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&size=32&background=1e3c72&color=fff'"
                                    >
                                    <div class="d-none d-md-block text-start">
                                        <div class="fw-semibold" style="font-size: 0.875rem; color: #566a7f;">{{ Auth::user()->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ ucfirst(Auth::user()->role) }}</div>
                                    </div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="layout-page-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="mt-auto py-3" style="background-color: #f5f5f9; border-top: 1px solid #dee2e6; margin-top: 2rem;">
                <div class="container-fluid">
                    <div class="text-center text-muted small">
                        <p class="mb-0">
                            Powered by <a href="https://emca.tech/" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold" style="color: #1e3c72;">EmCa Technologies</a>
                        </p>
                    </div>
                </div>
            </footer>
        </div>
        
        <!-- Overlay for Mobile -->
        <div class="layout-overlay" id="layoutOverlay"></div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Initialize sidebar state on page load
        const verticalNav = document.getElementById('verticalNav');
        const contentWrapper = document.getElementById('contentWrapper');
        
        // Ensure sidebar is visible on desktop by default
        function initializeSidebar() {
            if (window.innerWidth >= 992) {
                // Desktop: Show sidebar by default
                verticalNav.style.transform = 'translateX(0)';
                contentWrapper.classList.remove('sidebar-hidden');
            } else {
                // Mobile: Hide sidebar by default
                verticalNav.style.transform = 'translateX(-100%)';
                verticalNav.classList.remove('visible');
            }
        }
        
        // Initialize on page load
        initializeSidebar();
        
        // Re-initialize on window resize
        window.addEventListener('resize', function() {
            initializeSidebar();
        });
        
        // Mobile Toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const layoutOverlay = document.getElementById('layoutOverlay');
        
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const isVisible = verticalNav.classList.contains('visible');
                
                if (isVisible) {
                    verticalNav.classList.remove('visible');
                    if (layoutOverlay) {
                        layoutOverlay.classList.remove('visible');
                    }
                    document.body.style.overflow = '';
                } else {
                    verticalNav.classList.add('visible');
                    if (layoutOverlay) {
                        layoutOverlay.classList.add('visible');
                    }
                    document.body.style.overflow = 'hidden';
                }
            });
        }
        
        // Close sidebar when overlay is clicked
        if (layoutOverlay) {
            layoutOverlay.addEventListener('click', function() {
                verticalNav.classList.remove('visible');
                layoutOverlay.classList.remove('visible');
            });
        }
        
        // Desktop Toggle
        const desktopToggle = document.getElementById('desktopToggle');
        const toggleIcon = document.getElementById('toggleIcon');
        let sidebarVisible = true;
        
        if (desktopToggle) {
            desktopToggle.addEventListener('click', function() {
                sidebarVisible = !sidebarVisible;
                
                if (sidebarVisible) {
                    verticalNav.style.transform = 'translateX(0)';
                    contentWrapper.classList.remove('sidebar-hidden');
                    toggleIcon.classList.remove('bi-x-lg');
                    toggleIcon.classList.add('bi-list');
                } else {
                    verticalNav.style.transform = 'translateX(-100%)';
                    contentWrapper.classList.add('sidebar-hidden');
                    toggleIcon.classList.remove('bi-list');
                    toggleIcon.classList.add('bi-x-lg');
                }
            });
        }
        
        // Close sidebar on mobile when link is clicked and handle scroll to section
        // Use event delegation to handle dynamically added links
        document.addEventListener('click', function(e) {
            // Skip if clicking on logout form button
            if (e.target.closest('#logoutForm button')) {
                return; // Let form submit normally
            }
            
            const link = e.target.closest('.nav-link a');
            if (!link) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            // Handle scroll to section
            if (link.classList.contains('scroll-to-section')) {
                var href = link.getAttribute('href');
                var sectionId = href ? href.split('#')[1] : null;
                
                if (sectionId) {
                    var section = document.getElementById(sectionId);
                    if (section) {
                        // Close sidebar on mobile
                        if (window.innerWidth < 992) {
                            verticalNav.classList.remove('visible');
                            if (layoutOverlay) {
                                layoutOverlay.classList.remove('visible');
                            }
                            document.body.style.overflow = '';
                        }
                        
                        // Scroll to section
                        setTimeout(function() {
                            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 100);
                    } else {
                        // If section not found, navigate to dashboard first
                        window.location.href = href.split('#')[0];
                    }
                } else {
                    // No section ID, just navigate
                    window.location.href = href;
                }
            } else {
                // Regular link - navigate normally
                var href = link.getAttribute('href');
                if (href) {
                    // Close sidebar on mobile
                    if (window.innerWidth < 992) {
                        verticalNav.classList.remove('visible');
                        if (layoutOverlay) {
                            layoutOverlay.classList.remove('visible');
                        }
                        document.body.style.overflow = '';
                    }
                    
                    // Navigate to link
                    window.location.href = href;
                }
            }
        });
    </script>
    
    <!-- Auto-logout script for idle detection (3 minutes) -->
    <script>
        (function() {
            let idleTimer;
            const idleTimeout = 3 * 60 * 1000; // 3 minutes in milliseconds
            const logoutUrl = '{{ route("logout") }}';
            const csrfToken = '{{ csrf_token() }}';
            
            // Events that indicate user activity
            const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
            
            function resetIdleTimer() {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(function() {
                    // User has been idle for 3 minutes, logout
                    Swal.fire({
                        icon: 'warning',
                        title: 'Session Timeout',
                        text: 'You have been inactive for 3 minutes. You will be logged out for security reasons.',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        timer: 3000,
                        timerProgressBar: true
                    }).then(function() {
                        // Create and submit logout form
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = logoutUrl;
                        
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    });
                }, idleTimeout);
            }
            
            // Initialize timer
            resetIdleTimer();
            
            // Listen for user activity
            activityEvents.forEach(function(event) {
                document.addEventListener(event, resetIdleTimer, true);
            });
            
            // Also listen for visibility change (tab switch)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    resetIdleTimer();
                }
            });
        })();
    </script>
    
    @stack('scripts')
</body>
</html>


