<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hostel Management System')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* Prevent horizontal scrolling globally */
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
        
        /* Sidebar Toggle Styles for Owner/Matron/Patron */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed !important;
                left: 0;
                top: 56px;
                height: calc(100vh - 56px);
                width: 280px;
                max-width: 85vw;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1001;
                overflow-y: auto;
                overflow-x: hidden;
                -webkit-overflow-scrolling: touch;
                touch-action: pan-y;
            }
            
            .sidebar.visible {
                transform: translateX(0);
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 56px;
                left: 0;
                width: 100vw;
                max-width: 100vw;
                height: calc(100vh - 56px);
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                display: none;
                transition: opacity 0.3s ease;
                overflow: hidden;
            }
            
            .sidebar-overlay.visible {
                display: block;
                opacity: 1;
            }
            
            /* Prevent horizontal scroll on main content */
            .main-content-wrapper {
                width: 100%;
                max-width: 100vw;
                overflow-x: hidden;
                margin-left: 0 !important;
            }
            
            .container-fluid {
                width: 100%;
                max-width: 100%;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
                overflow-x: hidden;
            }
            
            /* Ensure all content fits */
            .card {
                max-width: 100%;
                overflow-x: hidden;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                max-width: 100%;
            }
        }
        
        @media (min-width: 992px) {
            .sidebar {
                transition: transform 0.3s ease;
                overflow-x: hidden;
            }
            
            .sidebar.hidden {
                transform: translateX(-100%);
            }
            
            .main-content-wrapper.sidebar-hidden {
                margin-left: 0;
            }
        }
        
        .btn-link.text-white:hover {
            opacity: 0.8;
        }
        
        /* Ensure navbar doesn't cause overflow */
        .navbar {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            overflow-y: visible;
            position: relative;
            z-index: 1030;
        }
        
        .navbar .container-fluid {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            overflow-y: visible;
            position: relative;
        }
        
        .navbar .navbar-collapse {
            overflow: visible !important;
            position: relative;
        }
        
        /* Fix dropdown menu z-index to appear above everything */
        .navbar .dropdown-menu {
            z-index: 1050 !important;
        }
        
        .navbar .dropdown-menu.show {
            display: block !important;
        }
        
        /* Ensure dropdown is visible outside navbar container */
        .navbar .nav-item.dropdown {
            position: static;
        }
        
        .navbar .navbar-nav {
            overflow: visible !important;
            position: relative;
        }
        
        /* Ensure dropdown menu is visible when shown - use fixed positioning like student */
        .navbar .dropdown-menu {
            position: fixed !important;
            top: auto !important;
            bottom: auto !important;
            right: 1rem !important;
            left: auto !important;
            margin-top: 0.5rem;
            min-width: 180px;
            z-index: 1050 !important;
            transform: none !important;
        }
        
        .navbar .dropdown-menu.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Desktop dropdown positioning */
        @media (min-width: 992px) {
            .navbar .dropdown-menu {
                position: fixed !important;
                top: auto !important;
                right: 1rem !important;
                left: auto !important;
                margin-top: 0.5rem;
            }
        }
        
        /* Mobile dropdown positioning */
        @media (max-width: 991.98px) {
            .navbar .dropdown-menu {
                position: fixed !important;
                top: auto !important;
                right: 1rem !important;
                left: auto !important;
                margin-top: 0.5rem;
                max-width: 200px;
                min-width: 180px;
            }
            
            .navbar .navbar-collapse {
                max-height: none !important;
                overflow: visible !important;
            }
            
            .navbar .navbar-nav {
                overflow: visible !important;
            }
        }
        
        /* Prevent any element from causing horizontal scroll */
        img, video, iframe, embed, object {
            max-width: 100%;
            height: auto;
        }
        
        /* Mobile container width */
        @media (max-width: 768px) {
            .container-fluid {
                width: 98% !important;
                max-width: 98% !important;
                padding-left: 1% !important;
                padding-right: 1% !important;
            }
            
            .card {
                width: 100% !important;
                max-width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
        }
        
        /* Responsive Tables - NO HORIZONTAL SCROLL */
        .table-responsive {
            overflow-x: visible !important;
            width: 100%;
        }
        
        /* Desktop - Show 6 columns, hide rest */
        .table-desktop-only {
            display: table-cell;
        }
        
        .table-view-more {
            display: none;
        }
        
        /* Mobile table styles - Show only 4 columns, NO SCROLL */
        @media (max-width: 768px) {
            .table-responsive {
                position: relative;
                overflow-x: visible !important;
                width: 100%;
            }
            
            /* Hide all columns by default on mobile */
            .table-responsive table thead th,
            .table-responsive table tbody td {
                display: none;
            }
            
            /* Show only first 4 columns on mobile */
            .table-responsive table thead th:nth-child(1),
            .table-responsive table tbody td:nth-child(1),
            .table-responsive table thead th:nth-child(2),
            .table-responsive table tbody td:nth-child(2),
            .table-responsive table thead th:nth-child(3),
            .table-responsive table tbody td:nth-child(3),
            .table-responsive table thead th:nth-child(4),
            .table-responsive table tbody td:nth-child(4) {
                display: table-cell;
            }
            
            /* Always show Actions column (last column) */
            .table-responsive table thead th:last-child,
            .table-responsive table tbody td:last-child {
                display: table-cell !important;
            }
            
            /* Compact table cells */
            .table-responsive table {
                font-size: 0.75rem;
                width: 100% !important;
                table-layout: fixed;
            }
            
            .table-responsive table th,
            .table-responsive table td {
                padding: 0.4rem 0.2rem;
                word-wrap: break-word;
                overflow-wrap: break-word;
                max-width: 0;
            }
            
            /* Prevent horizontal scroll */
            .table-responsive table th,
            .table-responsive table td {
                white-space: normal !important;
            }
            
            .table-desktop-only {
                display: none !important;
            }
            
            /* Show view more columns when toggled */
            .table-responsive.show-all table thead th,
            .table-responsive.show-all table tbody td {
                display: table-cell;
            }
        }
        
        /* Desktop - Show 6 columns, hide rest */
        @media (min-width: 769px) {
            /* Hide columns after 6th on desktop */
            .table-responsive table thead th:nth-child(n+7):not(:last-child),
            .table-responsive table tbody td:nth-child(n+7):not(:last-child) {
                display: none;
            }
            
            /* Show view more columns when toggled */
            .table-responsive.show-all table thead th,
            .table-responsive.show-all table tbody td {
                display: table-cell;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @auth
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="container-fluid">
                @if(Auth::user()->role !== 'student')
                <!-- Sidebar Toggle Button for Owner/Matron/Patron -->
                <!-- Mobile Toggle (Three Dots) -->
                <button class="btn btn-link text-white me-2 d-lg-none" id="mobileSidebarToggle" type="button" style="text-decoration: none; padding: 0.25rem 0.5rem;">
                    <i class="bi bi-three-dots-vertical" style="font-size: 1.5rem;"></i>
                </button>
                <!-- Desktop Toggle (Toggle Bar) -->
                <button class="btn btn-link text-white me-2 d-none d-lg-inline-block" id="desktopSidebarToggle" type="button" style="text-decoration: none; padding: 0.25rem 0.5rem;">
                    <i class="bi bi-layout-sidebar-inset-reverse" id="sidebarToggleIcon" style="font-size: 1.5rem;"></i>
                </button>
                @endif
                <a class="navbar-brand fw-bold" href="{{ Auth::user()->role === 'student' ? route('dashboard.student') : route('dashboard.owner') }}">
                    <i class="bi bi-building-fill me-2"></i>{{ \App\Models\HostelDetail::getHostelDetail()->hostel_name ?? 'Hostel Management' }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" aria-expanded="false" style="cursor: pointer;">
                                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                                <span class="badge bg-light text-dark ms-2">{{ ucfirst(Auth::user()->role) }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" style="z-index: 1050; display: none;">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        @if(Auth::user()->role === 'student')
            <!-- Student Layout (No Sidebar, Full Width) -->
            <main style="background-color: #f8f9fa; min-height: calc(100vh - 56px);">
                <div class="container-fluid p-0">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show m-2" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show m-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
                
                <!-- Footer -->
                <footer class="mt-auto py-3" style="background-color: #f8f9fa; border-top: 1px solid #dee2e6; margin-top: 2rem;">
                    <div class="container-fluid">
                        <div class="text-center text-muted small">
                            <p class="mb-0">
                                Powered by <a href="https://emca.tech/" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold" style="color: #1e3c72;">EmCa Technologies</a>
                            </p>
                        </div>
                    </div>
                </footer>
            </main>
        @else
            <!-- Owner/Matron/Patron Layout (With Sidebar) -->
            <div class="d-flex position-relative">
                <!-- Overlay for Mobile -->
                <div class="sidebar-overlay" id="sidebarOverlay"></div>
                
                <!-- Sidebar -->
                <div class="sidebar bg-light border-end" id="ownerSidebar" style="min-height: calc(100vh - 56px); width: 250px; position: sticky; top: 56px; z-index: 1000;">
                    <div class="p-3">
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('dashboard.owner') ? 'active' : '' }}" 
                                   href="{{ route('dashboard.owner') }}" 
                                   style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('blocks.*') ? 'active' : '' }}" 
                                   href="{{ route('blocks.index') }}"
                                   style="{{ request()->routeIs('blocks.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-building me-2"></i>Blocks
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}" 
                                   href="{{ route('rooms.index') }}"
                                   style="{{ request()->routeIs('rooms.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-door-open me-2"></i>Rooms
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" 
                                   href="{{ route('students.index') }}"
                                   style="{{ request()->routeIs('students.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-people-fill me-2"></i>Student Management
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}" 
                                   href="{{ route('bookings.index') }}"
                                   style="{{ request()->routeIs('bookings.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-calendar-check me-2"></i>Bookings
                                    @if(isset($newBookingsCount) && $newBookingsCount > 0)
                                        <span class="badge bg-danger ms-2">{{ $newBookingsCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" 
                                   href="{{ route('payments.manual') }}"
                                   style="{{ request()->routeIs('payments.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-cash-coin me-2"></i>Payments
                                </a>
                            </li>
                            @if(Auth::user()->role === 'owner')
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('control-numbers.*') ? 'active' : '' }}" 
                                   href="{{ route('control-numbers.index') }}"
                                   style="{{ request()->routeIs('control-numbers.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-credit-card-2-front me-2"></i>Control Numbers
                                </a>
                            </li>
                            @endif
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('rent-schedules.*') ? 'active' : '' }}" 
                                   href="{{ route('rent-schedules.index') }}"
                                   style="{{ request()->routeIs('rent-schedules.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-calendar-event me-2"></i>Rent Schedules
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
                                   href="{{ route('reports.rent-status') }}"
                                   style="{{ request()->routeIs('reports.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-file-earmark-text me-2"></i>Reports
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" 
                                   href="{{ route('settings.index') }}"
                                   style="{{ request()->routeIs('settings.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-gear me-2"></i>Settings
                                </a>
                            </li>
                            @if(Auth::user()->role === 'owner')
                            <li class="nav-item mb-2">
                                <a class="nav-link {{ request()->routeIs('terms.*') ? 'active' : '' }}" 
                                   href="{{ route('terms.index') }}"
                                   style="{{ request()->routeIs('terms.*') ? 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;' : 'color: #1e3c72;' }}">
                                    <i class="bi bi-file-text me-2"></i>Terms & Conditions
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Main Content -->
                <main class="flex-grow-1 main-content-wrapper" id="mainContentWrapper" style="background-color: #f8f9fa; min-height: calc(100vh - 56px); transition: margin-left 0.3s ease;">
                    <div class="container-fluid p-4">
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
                    </div>
                    
                    <!-- Footer -->
                    <footer class="mt-auto py-3" style="background-color: #f8f9fa; border-top: 1px solid #dee2e6; margin-top: 2rem;">
                        <div class="container-fluid">
                            <div class="text-center text-muted small">
                                <p class="mb-0">
                                    Powered by <a href="https://emca.tech/" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-bold" style="color: #1e3c72;">EmCa Technologies</a>
                                </p>
                            </div>
                        </div>
                    </footer>
                </main>
            </div>
        @endif
    @else
        <main>
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    @endauth

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @auth
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
    @endauth
    
    @auth
    @if(Auth::user()->role !== 'student')
    <script>
        // Sidebar Toggle Functionality for Owner/Matron/Patron
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('ownerSidebar');
            const mainContent = document.getElementById('mainContentWrapper');
            const mobileToggle = document.getElementById('mobileSidebarToggle');
            const desktopToggle = document.getElementById('desktopSidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const toggleIcon = document.getElementById('sidebarToggleIcon');
            
            let sidebarVisible = true;
            
            // Initialize sidebar state
            function initializeSidebar() {
                if (window.innerWidth >= 992) {
                    // Desktop: Show sidebar by default
                    sidebar.classList.remove('hidden');
                    mainContent.classList.remove('sidebar-hidden');
                    if (toggleIcon) {
                        toggleIcon.classList.remove('bi-x-lg');
                        toggleIcon.classList.add('bi-layout-sidebar-inset-reverse');
                    }
                    sidebarVisible = true;
                } else {
                    // Mobile: Hide sidebar by default
                    sidebar.classList.remove('visible');
                    sidebarOverlay.classList.remove('visible');
                    sidebarVisible = false;
                }
            }
            
            // Initialize on page load
            initializeSidebar();
            
            // Re-initialize on window resize
            window.addEventListener('resize', function() {
                initializeSidebar();
            });
            
            // Mobile Toggle
            if (mobileToggle) {
                mobileToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const isVisible = sidebar.classList.contains('visible');
                    
                    if (isVisible) {
                        // Closing sidebar
                        sidebar.classList.remove('visible');
                        sidebarOverlay.classList.remove('visible');
                        document.body.style.overflow = '';
                    } else {
                        // Opening sidebar
                        sidebar.classList.add('visible');
                        sidebarOverlay.classList.add('visible');
                        document.body.style.overflow = 'hidden';
                    }
                });
            }
            
            // Desktop Toggle
            if (desktopToggle) {
                desktopToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebarVisible = !sidebarVisible;
                    
                    if (sidebarVisible) {
                        sidebar.classList.remove('hidden');
                        mainContent.classList.remove('sidebar-hidden');
                        if (toggleIcon) {
                            toggleIcon.classList.remove('bi-x-lg');
                            toggleIcon.classList.add('bi-layout-sidebar-inset-reverse');
                        }
                    } else {
                        sidebar.classList.add('hidden');
                        mainContent.classList.add('sidebar-hidden');
                        if (toggleIcon) {
                            toggleIcon.classList.remove('bi-layout-sidebar-inset-reverse');
                            toggleIcon.classList.add('bi-x-lg');
                        }
                    }
                });
            }
            
            // Close sidebar when overlay is clicked (mobile)
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('visible');
                    sidebarOverlay.classList.remove('visible');
                    document.body.style.overflow = '';
                });
            }
            
            // Close sidebar on mobile when link is clicked
            if (sidebar) {
                const sidebarLinks = sidebar.querySelectorAll('.nav-link');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 992) {
                            sidebar.classList.remove('visible');
                            sidebarOverlay.classList.remove('visible');
                            document.body.style.overflow = '';
                        }
                    });
                });
            }
            
            // Prevent horizontal scroll on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth < 992) {
                    // Ensure sidebar is hidden on mobile resize
                    if (!sidebar.classList.contains('visible')) {
                        document.body.style.overflow = '';
                    }
                } else {
                    // Reset body overflow on desktop
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
    @endif
    @endauth
    
    <script>
        // Ensure Bootstrap dropdowns work properly for owner/student on desktop and mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Remove data-bs-toggle and use manual handling
            document.querySelectorAll('.dropdown-toggle').forEach(function(element) {
                // Remove Bootstrap's default behavior temporarily
                element.removeAttribute('data-bs-toggle');
                
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get the dropdown menu
                    var dropdown = this.closest('.dropdown');
                    var dropdownMenu = dropdown ? dropdown.querySelector('.dropdown-menu') : null;
                    
                    if (dropdownMenu) {
                        // Check if dropdown is currently shown
                        var isShown = dropdownMenu.classList.contains('show');
                        
                        // Close all other dropdowns first
                        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                            if (menu !== dropdownMenu) {
                                menu.classList.remove('show');
                                menu.style.display = 'none';
                            }
                        });
                        
                        // Toggle current dropdown
                        if (isShown) {
                            dropdownMenu.classList.remove('show');
                            dropdownMenu.style.display = 'none';
                        } else {
                            // Calculate position for fixed dropdown (like student layout)
                            var rect = element.getBoundingClientRect();
                            
                            dropdownMenu.classList.add('show');
                            dropdownMenu.style.display = 'block';
                            dropdownMenu.style.visibility = 'visible';
                            dropdownMenu.style.opacity = '1';
                            dropdownMenu.style.zIndex = '1050';
                            dropdownMenu.style.position = 'fixed';
                            dropdownMenu.style.top = (rect.bottom + 5) + 'px';
                            dropdownMenu.style.right = '1rem';
                            dropdownMenu.style.left = 'auto';
                            dropdownMenu.style.transform = 'none';
                        }
                    }
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                        menu.classList.remove('show');
                        menu.style.display = 'none';
                    });
                }
            });
            
            // Prevent dropdown from closing when clicking inside
            document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                menu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
