<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }} - Student Accommodation</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }} - Best Student Hostel Accommodation" name="keywords">
    <meta content="{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }} - Comfortable and affordable student accommodation" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link href="{{ asset('landing pages/img/favicon.ico') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('landing pages/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('landing pages/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('landing pages/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('landing pages/css/style.css') }}" rel="stylesheet">
    
    <!-- Dynamic Colors from Settings -->
    <style>
        :root {
            --primary: {{ $settings['primary_color'] ?? '#1e3c72' }};
            --secondary: {{ $settings['secondary_color'] ?? '#2a5298' }};
            --light: {{ $settings['light_color'] ?? '#EFF5F9' }};
            --dark: {{ $settings['dark_color'] ?? '#1D2A4D' }};
        }
        
        .btn-primary {
            background: linear-gradient(135deg, {{ $settings['primary_color'] ?? '#1e3c72' }} 0%, {{ $settings['secondary_color'] ?? '#2a5298' }} 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, {{ $settings['secondary_color'] ?? '#2a5298' }} 0%, {{ $settings['primary_color'] ?? '#1e3c72' }} 100%);
        }
        
        .text-primary {
            color: {{ $settings['primary_color'] ?? '#1e3c72' }} !important;
        }
        
        .border-bottom.border-5 {
            border-color: {{ $settings['primary_color'] ?? '#1e3c72' }} !important;
        }
        
        .hero-header {
            /* Background image will be set inline */
        }
        
        @media (min-width: 992px) {
            .navbar-light .navbar-nav .nav-link:hover::before,
            .navbar-light .navbar-nav .nav-link.active::before {
                background: {{ $settings['primary_color'] ?? '#1e3c72' }};
            }
        }
        
        .navbar-light .navbar-nav .nav-link:hover,
        .navbar-light .navbar-nav .nav-link.active {
            color: {{ $settings['primary_color'] ?? '#1e3c72' }} !important;
        }
        
        .service-item .service-icon {
            background: linear-gradient(135deg, {{ $settings['primary_color'] ?? '#1e3c72' }} 0%, {{ $settings['secondary_color'] ?? '#2a5298' }} 100%);
        }

        /* Chatbot Styles */
        #chatbotWidget {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        #chatbotMessages::-webkit-scrollbar {
            width: 6px;
        }

        #chatbotMessages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #chatbotMessages::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        #chatbotMessages::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Mobile Responsive Chatbot */
        @media (max-width: 768px) {
            #chatbotWidget {
                left: 10px !important;
                right: 10px !important;
                bottom: 10px !important;
                width: calc(100% - 20px) !important;
                max-width: calc(100% - 20px) !important;
            }
            /* Scroll to top button - left side */
            a[href="#!"]:has(.bi-arrow-up),
            a.btn-primary:has(.bi-arrow-up) {
                left: 10px !important;
                bottom: 30px !important;
                width: 55px !important;
                height: 55px !important;
            }
            #chatbotWidget .card {
                width: 100% !important;
                max-width: 100% !important;
                max-height: 85vh !important;
            }
            #chatbotWidget .card-body {
                height: calc(85vh - 140px) !important;
                max-height: calc(85vh - 140px) !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }
            #chatbotWidget .card-header h6 {
                font-size: 0.9rem !important;
            }
            #chatbotWidget .card-header small {
                font-size: 0.75rem !important;
            }
            #chatbotMessages {
                padding: 15px !important;
            }
            #chatbotMessages .bg-primary,
            #chatbotMessages .bg-light {
                max-width: 90% !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                font-size: 0.875rem !important;
                line-height: 1.4 !important;
            }
            #chatbotToggle {
                right: 10px !important;
                bottom: 100px !important;
                width: 55px !important;
                height: 55px !important;
            }
            a[href*="wa.me"] {
                right: 10px !important;
                bottom: 170px !important;
                width: 55px !important;
                height: 55px !important;
            }
            a[href="#!"]:has(.bi-arrow-up) {
                left: 10px !important;
                bottom: 30px !important;
                width: 55px !important;
                height: 55px !important;
            }
        }
    </style>
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid py-2 border-bottom d-none d-lg-block">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-lg-start mb-2 mb-lg-0">
                    <div class="d-inline-flex align-items-center">
                        <a class="text-decoration-none text-body pe-3" href="tel:{{ $settings['contact_phone1'] ?? '+255 XXX XXX XXX' }}">
                            <i class="bi bi-telephone me-2"></i>{{ $settings['contact_phone1'] ?? '+255 XXX XXX XXX' }}
                        </a>
                        <span class="text-body">|</span>
                        <a class="text-decoration-none text-body px-3" href="mailto:{{ $settings['contact_email1'] ?? 'info@isackhostel.com' }}">
                            <i class="bi bi-envelope me-2"></i>{{ $settings['contact_email1'] ?? 'info@isackhostel.com' }}
                        </a>
                    </div>
                </div>
                <div class="col-md-6 text-center text-lg-end">
                    <div class="d-inline-flex align-items-center">
                        <a class="text-body px-2" href="#!">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a class="text-body px-2" href="#!">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a class="text-body px-2" href="#!">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a class="text-body px-2" href="#!">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar Start -->
    <div class="container-fluid sticky-top bg-white shadow-sm">
        <div class="container">
            <nav class="navbar navbar-expand-lg bg-white navbar-light py-3 py-lg-0">
                <a href="{{ route('landing') }}" class="navbar-brand">
                    <h1 class="m-0 text-uppercase text-primary"><i class="bi bi-building me-2"></i>{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }}</h1>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto py-0">
                        <a href="{{ route('landing') }}" class="nav-item nav-link active">Home</a>
                        <a href="#about" class="nav-item nav-link">About</a>
                        <a href="#rooms" class="nav-item nav-link">Rooms</a>
                        <a href="#facilities" class="nav-item nav-link">Facilities</a>
                        <a href="#booking" class="nav-item nav-link">Book Now</a>
                        <a href="#contact" class="nav-item nav-link">Contact</a>
                        <a href="{{ route('login') }}" class="nav-item nav-link">Login</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Hero Start -->
    @php
        $primaryColor = $settings['primary_color'] ?? '#1e3c72';
        $secondaryColor = $settings['secondary_color'] ?? '#2a5298';
        $heroImage = $settings['hero_image'] ?? asset('landing pages/img/hero.jpg');
        // Convert hex to rgb for rgba
        $primaryRgb = sscanf($primaryColor, "#%02x%02x%02x");
        $secondaryRgb = sscanf($secondaryColor, "#%02x%02x%02x");
        $primaryRgba = 'rgba(' . $primaryRgb[0] . ', ' . $primaryRgb[1] . ', ' . $primaryRgb[2] . ', 0.85)';
        $secondaryRgba = 'rgba(' . $secondaryRgb[0] . ', ' . $secondaryRgb[1] . ', ' . $secondaryRgb[2] . ', 0.85)';
    @endphp
    <div class="container-fluid bg-primary py-5 mb-5 hero-header" style="background-image: linear-gradient(135deg, {{ $primaryRgba }} 0%, {{ $secondaryRgba }} 100%), url('{{ $heroImage }}') !important; background-size: cover !important; background-position: center !important; background-repeat: no-repeat !important; background-attachment: fixed !important; min-height: 500px; position: relative;">
        <div class="container py-5">
            <div class="row justify-content-start">
                <div class="col-lg-8 text-center text-lg-start">
                    <h5 class="d-inline-block text-white text-uppercase border-bottom border-5"
                        style="border-color: rgba(256, 256, 256, .3) !important;">Welcome To {{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }}</h5>
                    <h1 class="display-1 text-white mb-md-4">Your Home Away From Home</h1>
                    <p class="fs-4 fw-normal text-white mb-4">Comfortable, safe, and affordable student accommodation with modern facilities</p>
                    <div class="pt-2">
                        <a href="#booking" class="btn btn-light rounded-pill py-md-3 px-md-5 mx-2">Book Now</a>
                        <a href="#rooms" class="btn btn-outline-light rounded-pill py-md-3 px-md-5 mx-2">View Rooms</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hero End -->

    <!-- About Start -->
    <div id="about" class="container-fluid py-5">
        <div class="container">
            <div class="row gx-5">
                <div class="col-lg-5 mb-5 mb-lg-0" style="min-height: 500px;">
                    <div class="position-relative h-100">
                        <img class="position-absolute w-100 h-100 rounded" src="{{ $settings['about_image'] ?? asset('landing pages/img/about.jpg') }}"
                            style="object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="mb-4">
                        <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">About Us</h5>
                        <h1 class="display-4">Best Student Accommodation For Your Studies</h1>
                    </div>
                    <p>{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }} provides comfortable and affordable accommodation for students. We offer modern facilities, 
                        secure environment, and a supportive community to help you focus on your studies. Our hostel is designed 
                        to make your university experience memorable and productive.</p>
                    <div class="row g-3 pt-3">
                        <div class="col-sm-3 col-6">
                            <div class="bg-light text-center rounded-circle py-4">
                                <i class="bi bi-building fs-1 text-primary mb-3"></i>
                                <h6 class="mb-0">{{ $totalRooms }}<small class="d-block text-primary">Rooms</small></h6>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light text-center rounded-circle py-4">
                                <i class="bi bi-bed fs-1 text-primary mb-3"></i>
                                <h6 class="mb-0">{{ $totalBeds }}<small class="d-block text-primary">Beds</small></h6>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light text-center rounded-circle py-4">
                                <i class="bi bi-check-circle fs-1 text-primary mb-3"></i>
                                <h6 class="mb-0">{{ $availableRooms }}<small class="d-block text-primary">Available</small></h6>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light text-center rounded-circle py-4">
                                <i class="bi bi-people fs-1 text-primary mb-3"></i>
                                <h6 class="mb-0">24/7<small class="d-block text-primary">Security</small></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Facilities Start -->
    <div id="facilities" class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 500px;">
                <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">Our Facilities</h5>
                <h1 class="display-4">Excellent Facilities For Students</h1>
            </div>
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="service-icon mb-4">
                            <i class="bi bi-wifi fs-1 text-white"></i>
                        </div>
                        <h4 class="mb-3">Free WiFi</h4>
                        <p class="m-0">High-speed internet connection available throughout the hostel for your studies and entertainment</p>
                        <a class="btn btn-lg btn-primary rounded-pill mt-3" href="#booking">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="service-icon mb-4">
                            <i class="bi bi-shield-check fs-1 text-white"></i>
                        </div>
                        <h4 class="mb-3">24/7 Security</h4>
                        <p class="m-0">Round-the-clock security personnel and CCTV surveillance for your safety and peace of mind</p>
                        <a class="btn btn-lg btn-primary rounded-pill mt-3" href="#booking">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="service-icon mb-4">
                            <i class="bi bi-droplet fs-1 text-white"></i>
                        </div>
                        <h4 class="mb-3">Clean Water</h4>
                        <p class="m-0">Clean and safe water supply available 24/7 for all your daily needs</p>
                        <a class="btn btn-lg btn-primary rounded-pill mt-3" href="#booking">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="service-icon mb-4">
                            <i class="bi bi-lightning-charge fs-1 text-white"></i>
                        </div>
                        <h4 class="mb-3">Electricity</h4>
                        <p class="m-0">Reliable electricity supply with backup generator to ensure uninterrupted power</p>
                        <a class="btn btn-lg btn-primary rounded-pill mt-3" href="#booking">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="service-icon mb-4">
                            <i class="bi bi-book fs-1 text-white"></i>
                        </div>
                        <h4 class="mb-3">Study Areas</h4>
                        <p class="m-0">Quiet and comfortable study areas for group and individual study sessions</p>
                        <a class="btn btn-lg btn-primary rounded-pill mt-3" href="#booking">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="service-icon mb-4">
                            <i class="bi bi-people fs-1 text-white"></i>
                        </div>
                        <h4 class="mb-3">Community</h4>
                        <p class="m-0">Friendly and supportive community of students from various universities</p>
                        <a class="btn btn-lg btn-primary rounded-pill mt-3" href="#booking">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Facilities End -->

    <!-- Rooms Start -->
    <div id="rooms" class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 500px;">
                <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">Our Rooms</h5>
                <h1 class="display-4">Available Accommodation Options</h1>
            </div>
            <div class="row g-5">
                @php
                    $displayedRooms = 0;
                    $maxRooms = 6;
                @endphp
                @forelse($blocks as $block)
                    @foreach($block->rooms as $room)
                        @if($displayedRooms >= $maxRooms)
                            @break
                        @endif
                        @php
                            $freeBeds = $room->beds->where('status', 'free')->count();
                            $totalBeds = $room->beds->count();
                            $hasStudent = \App\Models\Student::where('room_id', $room->id)
                                ->where('status', 'active')
                                ->whereNull('check_out_date')
                                ->exists();
                            
                            // Only show rooms with available beds or empty rooms without beds
                            $isAvailable = false;
                            if ($room->has_beds && $freeBeds > 0) {
                                $isAvailable = true;
                            } elseif (!$room->has_beds && !$hasStudent) {
                                $isAvailable = true;
                            }
                            
                            if (!$isAvailable) {
                                continue;
                            }
                            
                            $rentPrice = $room->rent_price ?? ($room->beds->first()->rent_price ?? 0);
                            $displayedRooms++;
                        @endphp
                        <div class="col-lg-4 col-md-6">
                            <div class="bg-light rounded overflow-hidden">
                                <div class="position-relative" style="height: 250px; background-color: #f8f9fa; overflow: hidden;">
                                    @if($room->image)
                                        <img class="img-fluid w-100 h-100" 
                                             src="{{ asset('storage/' . $room->image) }}" 
                                             alt="{{ $room->name }}" 
                                             style="object-fit: cover;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="d-flex align-items-center justify-content-center h-100" style="display: none;">
                                            <div class="text-center">
                                                <i class="bi bi-image" style="font-size: 4rem; color: #6c757d;"></i>
                                                <p class="text-muted mt-2 mb-0 small">No Image Available</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <div class="text-center">
                                                <i class="bi bi-image" style="font-size: 4rem; color: #6c757d;"></i>
                                                <p class="text-muted mt-2 mb-0 small">No Image Available</p>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge bg-primary">{{ $block->name }}</span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="mb-3">{{ $room->name }}</h4>
                                    <p class="mb-3">{{ $room->description ?? 'Comfortable and well-furnished room for students.' }}</p>
                                    @if($room->has_beds)
                                        <p class="mb-2"><i class="bi bi-bed me-2"></i><strong>{{ $freeBeds }}</strong> beds available out of <strong>{{ $totalBeds }}</strong></p>
                                    @else
                                        <p class="mb-2"><i class="bi bi-door-open me-2"></i>Room rental available</p>
                                    @endif
                                    <p class="mb-3"><i class="bi bi-currency-dollar me-2"></i><strong>Tsh {{ number_format($rentPrice, 2) }}</strong> per {{ $room->rent_duration ?? 'month' }}</p>
                                    <a href="#booking" class="btn btn-primary rounded-pill w-100 book-now-btn" data-room-id="{{ $room->id }}" data-has-beds="{{ $room->has_beds ? '1' : '0' }}">Book Now</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">No rooms available at the moment. Please check back later.</p>
                    </div>
                @endforelse
                @if($displayedRooms == 0)
                    <div class="col-12">
                        <p class="text-center text-muted">No available rooms at the moment. Please check back later.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Rooms End -->

    <!-- Pending Bookings Section -->
    @if($bookingsWithTime->count() > 0)
    <div class="container-fluid bg-warning bg-opacity-10 py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 500px;">
                <h5 class="d-inline-block text-warning text-uppercase border-bottom border-5">Payment Pending</h5>
                <h1 class="display-4">Rooms Awaiting Payment</h1>
                <p class="text-muted">These rooms are reserved but payment is pending. They will become available if payment is not made within the timeout period.</p>
            </div>


            <div class="row g-3">
                @foreach($bookingsWithTime as $booking)
                <div class="col-md-3 col-sm-4 col-6">
                    <div class="card h-100 text-center {{ $booking['is_expired'] ? 'border-danger' : ($booking['hours_remaining'] < 2 ? 'border-warning' : 'border-info') }}">
                        <div class="card-body">
                            <h6 class="card-title mb-3">{{ $booking['room']->name ?? 'N/A' }}</h6>
                            @if($booking['bed'])
                                <span class="badge bg-info mb-2">Bed: {{ $booking['bed']->name }}</span>
                            @else
                                <span class="badge bg-secondary mb-2">Key Room</span>
                            @endif
                            <div class="mt-3">
                                @if($booking['is_expired'])
                                    <span class="badge bg-danger fs-6">Expired</span>
                                @else
                                    <div class="countdown-timer" 
                                         data-expires-at="{{ $booking['expires_at']->toIso8601String() }}"
                                         data-booking-id="{{ $booking['student']->id }}">
                                        <span class="badge bg-{{ $booking['hours_remaining'] < 2 ? 'warning' : 'info' }} fs-6">
                                            <i class="bi bi-clock me-1"></i><span class="countdown-text">{{ $booking['time_remaining'] ?? 'N/A' }}</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Booking Start -->
    <div id="booking" class="container-fluid bg-light my-5 py-5">
        <div class="container py-5">
            <div class="text-center mx-auto mb-5" style="max-width: 500px;">
                <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">Book Your Room</h5>
                <h1 class="display-4">Select Your Accommodation</h1>
            </div>

            <!-- Block Selection -->
            <div class="row mb-4">
                <div class="col-12">
                    <label class="form-label fw-bold mb-3">Select Block</label>
                    <select class="form-select form-select-lg" id="blockSelect" style="height: 55px;">
                        <option value="">Choose a Block</option>
                        @foreach($blocks as $block)
                            <option value="{{ $block->id }}">{{ $block->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Visual Map of Rooms -->
            <div id="roomsMapContainer" style="display: none;">
                <div class="mb-3">
                    <h4 id="selectedBlockName" class="mb-3"></h4>
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <span class="badge bg-success"><i class="bi bi-circle-fill me-1"></i>Available</span>
                        <span class="badge bg-warning"><i class="bi bi-circle-fill me-1"></i>Booked</span>
                        <span class="badge bg-danger"><i class="bi bi-circle-fill me-1"></i>Occupied</span>
                        <span class="badge bg-secondary"><i class="bi bi-circle-fill me-1"></i>Key Room</span>
                    </div>
                </div>
                <div id="roomsMap" class="row g-3">
                    <!-- Rooms will be dynamically loaded here -->
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
                    <h5><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</h5>
                    @if(session('booking_info'))
                        <hr>
                        <p class="mb-1"><strong>Username:</strong> {{ session('booking_info')['username'] }}</p>
                        <p class="mb-0"><strong>Password:</strong> {{ session('booking_info')['password'] }}</p>
                        <p class="mt-2 mb-0"><small>These credentials have also been sent to your phone via SMS.</small></p>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>
    <!-- Booking End -->

    <!-- Bed Selection Modal -->
    <div class="modal fade" id="bedSelectionModal" tabindex="-1" aria-labelledby="bedSelectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bedSelectionModalLabel">Select Bed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Available beds in <strong id="selectedRoomName"></strong>:</p>
                    <div id="bedsList" class="row g-3">
                        <!-- Beds will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingDetailsModalLabel">Complete Your Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;">
                    <form action="{{ route('landing.book') }}" method="POST" id="bookingForm">
                        @csrf
                        <input type="hidden" name="block_id" id="form_block_id">
                        <input type="hidden" name="room_id" id="form_room_id">
                        <input type="hidden" name="bed_id" id="form_bed_id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" id="bookingFullName" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Phone Number *</label>
                                <input type="tel" class="form-control" name="phone" id="bookingPhone" placeholder="255612345678" required pattern="^255[67]\d{8}$" maxlength="12">
                                <small class="text-muted">Format: 255 + (6 or 7) + 8 digits (e.g., 255612345678 or 255712345678)</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Check-in Date *</label>
                                <input type="date" class="form-control" name="check_in_date" id="bookingCheckInDate" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Terms and Conditions Section -->
                        <div class="col-12 mt-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Terms and Conditions</h6>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                    <div id="termsContent" class="small">
                                        <p class="text-muted">Loading terms and conditions...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="acceptTerms" name="accept_terms" required>
                                <label class="form-check-label" for="acceptTerms">
                                    <strong>I have read and agree to the Terms and Conditions *</strong>
                                </label>
                                <div class="invalid-feedback">You must accept the terms and conditions to proceed.</div>
                            </div>
                        </div>
                        
                        <div id="bookingAlert" class="alert d-none mt-3"></div>
                        
                        <div class="mt-4">
                            <button type="submit" id="submitBookingBtn" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-check-circle me-2"></i><span id="submitBookingText">Complete Booking</span>
                                <span id="submitBookingSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                            </button>
                        </div>
                        <div class="mt-3 text-center">
                            <p class="text-muted small mb-0">Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Success Modal -->
    <div class="modal fade" id="bookingSuccessModal" tabindex="-1" aria-labelledby="bookingSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="bookingSuccessModalLabel">
                        <i class="bi bi-check-circle me-2"></i>Booking Successful!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="bookingSuccessContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('login') }}" class="btn btn-primary">Login Now</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Start -->
    <div id="contact" class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 500px;">
                <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">Contact Us</h5>
                <h1 class="display-4">Get In Touch</h1>
            </div>
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="bg-light rounded p-4 text-center">
                        <i class="bi bi-geo-alt fs-1 text-primary mb-3"></i>
                        <h4>Address</h4>
                        <p class="mb-0">
                            {{ $settings['contact_address'] ?? '123 Hostel Street' }}
                            @if(isset($settings['contact_city']) && !empty($settings['contact_city']))
                                <br>{{ $settings['contact_city'] }}
                            @else
                                <br>Dar es Salaam, Tanzania
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-light rounded p-4 text-center">
                        <i class="bi bi-telephone fs-1 text-primary mb-3"></i>
                        <h4>Phone</h4>
                        <p class="mb-0">
                            @if(isset($settings['contact_phone1']) && !empty($settings['contact_phone1']))
                                <a href="tel:{{ $settings['contact_phone1'] }}" class="text-decoration-none text-dark">{{ $settings['contact_phone1'] }}</a>
                            @else
                                <a href="tel:+255 XXX XXX XXX" class="text-decoration-none text-dark">+255 XXX XXX XXX</a>
                            @endif
                            @if(isset($settings['contact_phone2']) && !empty($settings['contact_phone2']) && $settings['contact_phone2'] !== '+255 XXX XXX XXX')
                                <br><a href="tel:{{ $settings['contact_phone2'] }}" class="text-decoration-none text-dark">{{ $settings['contact_phone2'] }}</a>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-light rounded p-4 text-center">
                        <i class="bi bi-envelope fs-1 text-primary mb-3"></i>
                        <h4>Email</h4>
                        <p class="mb-0">
                            @if(isset($settings['contact_email1']) && !empty($settings['contact_email1']))
                                <a href="mailto:{{ $settings['contact_email1'] }}" class="text-decoration-none text-dark">{{ $settings['contact_email1'] }}</a>
                            @else
                                <a href="mailto:info@isackhostel.com" class="text-decoration-none text-dark">info@isackhostel.com</a>
                            @endif
                            @if(isset($settings['contact_email2']) && !empty($settings['contact_email2']) && $settings['contact_email2'] !== 'bookings@isackhostel.com')
                                <br><a href="mailto:{{ $settings['contact_email2'] }}" class="text-decoration-none text-dark">{{ $settings['contact_email2'] }}</a>
                            @elseif(isset($settings['contact_email2']) && $settings['contact_email2'] === 'bookings@isackhostel.com')
                                <br><a href="mailto:bookings@isackhostel.com" class="text-decoration-none text-dark">bookings@isackhostel.com</a>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light mt-5 py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-3 col-md-6">
                    <h4 class="d-inline-block text-primary text-uppercase border-bottom border-5 border-secondary mb-4">Get In Touch</h4>
                    <p class="mb-4">{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }} - Your trusted partner for comfortable student accommodation.</p>
                    <p class="mb-2"><i class="fa fa-map-marker-alt text-primary me-3"></i>{{ $settings['contact_address'] ?? '123 Hostel Street' }}, {{ $settings['contact_city'] ?? 'Dar es Salaam, Tanzania' }}</p>
                    <p class="mb-2"><i class="fa fa-envelope text-primary me-3"></i><a href="mailto:{{ $settings['contact_email1'] ?? 'info@isackhostel.com' }}" class="text-light text-decoration-none">{{ $settings['contact_email1'] ?? 'info@isackhostel.com' }}</a></p>
                    <p class="mb-0"><i class="fa fa-phone-alt text-primary me-3"></i><a href="tel:{{ $settings['contact_phone1'] ?? '+255 XXX XXX XXX' }}" class="text-light text-decoration-none">{{ $settings['contact_phone1'] ?? '+255 XXX XXX XXX' }}</a></p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="d-inline-block text-primary text-uppercase border-bottom border-5 border-secondary mb-4">Quick Links</h4>
                    <div class="d-flex flex-column justify-content-start">
                        <a class="text-light mb-2" href="{{ route('landing') }}"><i class="fa fa-angle-right me-2"></i>Home</a>
                        <a class="text-light mb-2" href="#about"><i class="fa fa-angle-right me-2"></i>About Us</a>
                        <a class="text-light mb-2" href="#rooms"><i class="fa fa-angle-right me-2"></i>Our Rooms</a>
                        <a class="text-light mb-2" href="#facilities"><i class="fa fa-angle-right me-2"></i>Facilities</a>
                        <a class="text-light mb-2" href="#booking"><i class="fa fa-angle-right me-2"></i>Book Now</a>
                        <a class="text-light" href="#contact"><i class="fa fa-angle-right me-2"></i>Contact Us</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="d-inline-block text-primary text-uppercase border-bottom border-5 border-secondary mb-4">Services</h4>
                    <div class="d-flex flex-column justify-content-start">
                        <a class="text-light mb-2" href="#!"><i class="fa fa-angle-right me-2"></i>Room Booking</a>
                        <a class="text-light mb-2" href="#!"><i class="fa fa-angle-right me-2"></i>Payment Options</a>
                        <a class="text-light mb-2" href="#!"><i class="fa fa-angle-right me-2"></i>Student Support</a>
                        <a class="text-light mb-2" href="#!"><i class="fa fa-angle-right me-2"></i>Maintenance</a>
                        <a class="text-light mb-2" href="#!"><i class="fa fa-angle-right me-2"></i>Security</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="d-inline-block text-primary text-uppercase border-bottom border-5 border-secondary mb-4">Newsletter</h4>
                    <form action="">
                        <div class="input-group">
                            <input type="text" class="form-control p-3 border-0" placeholder="Your Email Address">
                            <button class="btn btn-primary">Sign Up</button>
                        </div>
                    </form>
                    <h6 class="text-primary text-uppercase mt-4 mb-3">Follow Us</h6>
                    <div class="d-flex">
                        <a class="btn btn-lg btn-primary btn-lg-square rounded-circle me-2" href="#!"><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-lg btn-primary btn-lg-square rounded-circle me-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-lg btn-primary btn-lg-square rounded-circle me-2" href="#!"><i class="fab fa-linkedin-in"></i></a>
                        <a class="btn btn-lg btn-primary btn-lg-square rounded-circle" href="#!"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-light border-top border-secondary py-4">
        <div class="container">
            <div class="row g-5">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-md-0">&copy; <a class="text-primary" href="#!">{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }}</a>. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Designed for <a class="text-primary" href="#!">{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }}</a> - Student Accommodation Management System</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#!" class="btn btn-lg btn-primary btn-lg-square position-fixed" style="bottom: 30px; left: 30px; width: 60px; height: 60px; z-index: 999; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; border-radius: 50%; background: linear-gradient(135deg, {{ $settings['primary_color'] ?? '#1e3c72' }} 0%, {{ $settings['secondary_color'] ?? '#2a5298' }} 100%) !important; border: none;" title="Scroll to top"><i class="bi bi-arrow-up text-white"></i></a>

    <!-- WhatsApp Floating Button -->
    @php
        $whatsappNumber = $settings['whatsapp_number'] ?? '+255 XXX XXX XXX';
        // Remove spaces and special characters for WhatsApp link, but keep + if present
        $cleanNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);
        // Remove + if present and add it back, or just use numbers
        $cleanNumber = str_replace('+', '', $cleanNumber);
        $whatsappLink = 'https://wa.me/' . $cleanNumber;
    @endphp
    <a href="{{ $whatsappLink }}" 
       target="_blank" 
       class="btn btn-success btn-lg-square rounded-circle position-fixed" 
       style="bottom: 100px; right: 30px; width: 60px; height: 60px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"
       title="Chat with us on WhatsApp">
        <i class="fab fa-whatsapp fs-2"></i>
    </a>

    <!-- Chatbot Widget -->
    <div id="chatbotWidget" class="position-fixed" style="bottom: 30px; right: 30px; z-index: 1001; display: none; max-width: 350px; width: calc(100% - 60px);">
        <div class="card shadow-lg" style="width: 100%; max-width: 350px; max-height: 500px; border-radius: 15px; overflow: hidden;">
            <!-- Chatbot Header -->
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, {{ $settings['primary_color'] ?? '#1e3c72' }} 0%, {{ $settings['secondary_color'] ?? '#2a5298' }} 100%) !important;">
                <div>
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }} Assistant</h6>
                    <small>Ask me anything!</small>
                </div>
                <button type="button" class="btn btn-sm btn-light" id="closeChatbot">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <!-- Chatbot Body -->
            <div class="card-body p-0" style="height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                <div id="chatbotMessages" class="p-3" style="min-height: 100%;">
                    <div class="mb-3">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary text-white rounded p-2 me-2" style="border-radius: 15px 15px 15px 0;">
                                <small>Hello! I'm your {{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }} assistant. How can I help you today?</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Chatbot Input -->
            <div class="card-footer bg-white p-2">
                <div class="input-group">
                    <input type="text" 
                           class="form-control border-0" 
                           id="chatbotInput" 
                           placeholder="Type your question..."
                           style="border-radius: 20px;">
                    <button class="btn btn-primary rounded-circle ms-2" 
                            id="sendChatbotMessage"
                            style="width: 40px; height: 40px; background: linear-gradient(135deg, {{ $settings['primary_color'] ?? '#1e3c72' }} 0%, {{ $settings['secondary_color'] ?? '#2a5298' }} 100%); border: none;">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chatbot Toggle Button -->
    <button id="chatbotToggle" 
            class="btn btn-primary btn-lg-square rounded-circle position-fixed" 
            style="bottom: 170px; right: 30px; width: 60px; height: 60px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, {{ $settings['primary_color'] ?? '#1e3c72' }} 0%, {{ $settings['secondary_color'] ?? '#2a5298' }} 100%) !important; border: none;"
            title="Chat with us">
        <i class="bi bi-chat-dots fs-2 text-white"></i>
    </button>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('landing pages/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('landing pages/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('landing pages/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('landing pages/lib/tempusdominus/js/moment.min.js') }}"></script>
    <script src="{{ asset('landing pages/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
    <script src="{{ asset('landing pages/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('landing pages/js/main.js') }}"></script>

    <!-- Chatbot and WhatsApp Script -->
    <script>
        // Chatbot knowledge base
        const hostelName = '{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }}';
        const chatbotKnowledge = {
            'greeting': ['Hello! Welcome to ' + hostelName + '. How can I assist you today?', 'Hi there! I\'m here to help you with any questions about ' + hostelName + '.', 'Hello! How can I help you today?'],
            'hello': ['Hello! Welcome to ' + hostelName + '. How can I assist you today?', 'Hi there! I\'m here to help you with any questions about ' + hostelName + '.', 'Hello! How can I help you today?'],
            'hi': ['Hello! Welcome to ' + hostelName + '. How can I assist you today?', 'Hi there! I\'m here to help you with any questions about ' + hostelName + '.', 'Hello! How can I help you today?'],
            'price': ['Our room prices vary depending on the room type and location. Please contact us at {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }} or {{ $settings["contact_email1"] ?? "info@isackhostel.com" }} for detailed pricing information.', 'Room prices start from different rates. For specific pricing, please reach out to us via phone or email.'],
            'rent': ['Our rent prices vary by room type. Monthly and semester options are available. Contact us for detailed pricing.', 'Rent prices depend on the room and payment frequency. Please contact us for more information.'],
            'room': ['We offer various room types with different amenities. You can view available rooms on our website or contact us for more details.', 'We have comfortable rooms with modern facilities. Check our rooms section or contact us for availability.'],
            'bed': ['We offer both room rentals and bed rentals. Each room has comfortable beds with all necessary amenities.', 'Beds are available in shared rooms. Contact us to check availability.'],
            'facilities': [hostelName + ' offers WiFi, 24/7 security, clean water, electricity, study areas, and a supportive community.', 'Our facilities include WiFi, security, water, electricity, study spaces, and more. Check our facilities section for details.'],
            'wifi': ['Yes, we provide free WiFi throughout the hostel for all residents.', 'WiFi is available in all areas of the hostel.'],
            'security': ['We have 24/7 security to ensure the safety of all our residents.', 'Security is available 24 hours a day, 7 days a week.'],
            'location': ['We are located at {{ $settings["contact_address"] ?? "123 Hostel Street" }}, {{ $settings["contact_city"] ?? "Dar es Salaam, Tanzania" }}.', 'Our address is {{ $settings["contact_address"] ?? "123 Hostel Street" }}, {{ $settings["contact_city"] ?? "Dar es Salaam, Tanzania" }}.'],
            'address': ['We are located at {{ $settings["contact_address"] ?? "123 Hostel Street" }}, {{ $settings["contact_city"] ?? "Dar es Salaam, Tanzania" }}.', 'Our address is {{ $settings["contact_address"] ?? "123 Hostel Street" }}, {{ $settings["contact_city"] ?? "Dar es Salaam, Tanzania" }}.'],
            'contact': ['You can reach us at {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }} or email us at {{ $settings["contact_email1"] ?? "info@isackhostel.com" }}. You can also chat with us on WhatsApp!', 'Contact us at {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }} or {{ $settings["contact_email1"] ?? "info@isackhostel.com" }}.'],
            'phone': ['Our phone number is {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }}. You can also reach us on WhatsApp!', 'Call us at {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }}.'],
            'email': ['You can email us at {{ $settings["contact_email1"] ?? "info@isackhostel.com" }} or {{ $settings["contact_email2"] ?? "bookings@isackhostel.com" }}.', 'Email us at {{ $settings["contact_email1"] ?? "info@isackhostel.com" }}.'],
            'booking': ['You can book a room by filling out the booking form on our website or contacting us directly.', 'To book, use our booking form or contact us at {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }}.'],
            'book': ['You can book a room by filling out the booking form on our website or contacting us directly.', 'To book, use our booking form or contact us at {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }}.'],
            'available': ['Please check our rooms section or contact us to check current availability.', 'Availability changes frequently. Contact us for the latest information.'],
            'availability': ['Please check our rooms section or contact us to check current availability.', 'Availability changes frequently. Contact us for the latest information.'],
            'payment': ['We accept various payment methods. Contact us for payment options and schedules.', 'Payment can be made monthly or per semester. Contact us for details.'],
            'default': ['I\'m here to help! Could you please rephrase your question? You can also contact us directly at {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }} or {{ $settings["contact_email1"] ?? "info@isackhostel.com" }}.', 'I\'m not sure about that. Please contact us at {{ $settings["contact_phone1"] ?? "+255 XXX XXX XXX" }} for more information.', 'Let me help you with that. For detailed information, please contact us directly.']
        };

        // Chatbot functions
        function getChatbotResponse(userMessage) {
            const message = userMessage.toLowerCase().trim();
            
            // Check for keywords
            for (const [keyword, responses] of Object.entries(chatbotKnowledge)) {
                if (message.includes(keyword)) {
                    const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                    return randomResponse;
                }
            }
            
            // Default response
            const defaultResponses = chatbotKnowledge.default;
            return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
        }

        function addMessageToChat(message, isUser = false) {
            const messagesDiv = document.getElementById('chatbotMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'mb-3';
            
            if (isUser) {
                messageDiv.innerHTML = `
                    <div class="d-flex align-items-start justify-content-end">
                        <div class="bg-light border rounded p-2 ms-2" style="border-radius: 15px 15px 0 15px; max-width: 80%; word-wrap: break-word; overflow-wrap: break-word;">
                            <small style="white-space: pre-wrap; word-break: break-word;">${message}</small>
                        </div>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="d-flex align-items-start">
                        <div class="bg-primary text-white rounded p-2 me-2" style="border-radius: 15px 15px 15px 0; max-width: 80%; word-wrap: break-word; overflow-wrap: break-word;">
                            <small style="white-space: pre-wrap; word-break: break-word;">${message}</small>
                        </div>
                    </div>
                `;
            }
            
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // Chatbot toggle
        document.getElementById('chatbotToggle').addEventListener('click', function() {
            const widget = document.getElementById('chatbotWidget');
            const toggle = document.getElementById('chatbotToggle');
            
            if (widget.style.display === 'none' || widget.style.display === '') {
                widget.style.display = 'block';
                toggle.style.display = 'none';
            } else {
                widget.style.display = 'none';
                toggle.style.display = 'flex';
            }
        });

        // Close chatbot
        document.getElementById('closeChatbot').addEventListener('click', function() {
            document.getElementById('chatbotWidget').style.display = 'none';
            document.getElementById('chatbotToggle').style.display = 'flex';
        });

        // Send message
        document.getElementById('sendChatbotMessage').addEventListener('click', function() {
            sendChatbotMessage();
        });

        document.getElementById('chatbotInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendChatbotMessage();
            }
        });

        function sendChatbotMessage() {
            const input = document.getElementById('chatbotInput');
            const message = input.value.trim();
            
            if (message === '') return;
            
            // Add user message
            addMessageToChat(message, true);
            input.value = '';
            
            // Simulate typing delay
            setTimeout(function() {
                const response = getChatbotResponse(message);
                addMessageToChat(response, false);
            }, 500);
        }
    </script>

    <script>
        $(document).ready(function() {
            let selectedBlockId = null;
            let selectedBlockName = null;
            let selectedRoomId = null;
            let selectedRoomName = null;

            // Handle block selection - Load visual map
            $('#blockSelect').on('change', function() {
                const blockId = $(this).val();
                const blockName = $(this).find('option:selected').text();
                const roomsMapContainer = $('#roomsMapContainer');
                const roomsMap = $('#roomsMap');
                
                if (!blockId) {
                    roomsMapContainer.hide();
                    return;
                }

                selectedBlockId = blockId;
                selectedBlockName = blockName;
                $('#selectedBlockName').text(blockName);
                roomsMapContainer.show();
                roomsMap.html('<div class="col-12 text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

                // Load rooms for this block
                $.ajax({
                    url: '/api/blocks/' + blockId + '/rooms',
                    type: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                        success: function(response) {
                            roomsMap.html('');
                            if (response.success && response.rooms) {
                                response.rooms.forEach(function(room) {
                                    // Determine room status
                                    let status = 'available';
                                    let statusClass = 'success';
                                    let statusText = 'Available';
                                    let clickable = true;
                                    let bedInfoText = '';
                                    
                                    if (room.has_beds) {
                                        if (room.free_beds === 0) {
                                            status = 'occupied';
                                            statusClass = 'danger';
                                            statusText = 'Occupied';
                                            clickable = false;
                                            bedInfoText = 'No beds available';
                                        } else {
                                            // Room has free beds - always green (available)
                                            status = 'available';
                                            statusClass = 'success';
                                            statusText = 'Available';
                                            clickable = true;
                                            
                                            // Display bed count: "1 bed available" or "2 beds available"
                                            if (room.free_beds === 1) {
                                                bedInfoText = '1 bed available';
                                            } else {
                                                bedInfoText = room.free_beds + ' beds available';
                                            }
                                        }
                                    } else {
                                        // Key room (no beds)
                                        if (room.is_occupied) {
                                            status = 'occupied';
                                            statusClass = 'danger';
                                            statusText = 'Occupied';
                                            clickable = false;
                                            bedInfoText = 'Room occupied';
                                        } else {
                                            status = 'key';
                                            statusClass = 'secondary';
                                            statusText = 'Key Room';
                                            clickable = true;
                                            bedInfoText = 'Key Room';
                                        }
                                    }

                                    const roomCard = `
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="card room-card h-100 ${clickable ? 'cursor-pointer' : 'opacity-50'}" 
                                                 data-room-id="${room.id}" 
                                                 data-room-name="${room.name}"
                                                 data-has-beds="${room.has_beds ? '1' : '0'}"
                                                 data-status="${status}"
                                                 style="border: 2px solid; ${status === 'available' ? 'border-color: #28a745 !important;' : status === 'booked' ? 'border-color: #ffc107 !important;' : status === 'occupied' ? 'border-color: #dc3545 !important;' : 'border-color: #6c757d !important;'}">
                                                <div class="card-body text-center p-3">
                                                    <h6 class="card-title mb-2">${room.name}</h6>
                                                    <span class="badge bg-${statusClass} mb-2">${statusText}</span>
                                                    <p class="mb-0 small text-muted">${bedInfoText}</p>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    roomsMap.append(roomCard);
                            });
                        } else {
                            roomsMap.html('<div class="col-12"><p class="text-center text-muted">No rooms found in this block.</p></div>');
                        }
                    },
                    error: function() {
                        roomsMap.html('<div class="col-12"><p class="text-center text-danger">Error loading rooms. Please try again.</p></div>');
                    }
                });
            });

            // Handle room click
            $(document).on('click', '.room-card[data-status="available"], .room-card[data-status="key"]', function() {
                const roomId = $(this).data('room-id');
                const roomName = $(this).data('room-name');
                const hasBeds = $(this).data('has-beds') == '1';

                selectedRoomId = roomId;
                selectedRoomName = roomName;

                if (hasBeds) {
                    // Show bed selection modal
                    $('#selectedRoomName').text(roomName);
                    $('#bedsList').html('<div class="col-12 text-center"><div class="spinner-border" role="status"></div></div>');
                    const bedModal = new bootstrap.Modal(document.getElementById('bedSelectionModal'));
                    bedModal.show();

                    // Load beds for this room
                    $.ajax({
                        url: '/api/rooms/' + roomId + '/beds',
                        type: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            $('#bedsList').html('');
                            if (response.success && response.has_free_beds && response.beds && response.beds.length > 0) {
                                response.beds.forEach(function(bed) {
                                    const bedCard = `
                                        <div class="col-md-6">
                                            <div class="card bed-card cursor-pointer" data-bed-id="${bed.id}" data-bed-name="${bed.name}" style="border: 2px solid #28a745;">
                                                <div class="card-body text-center">
                                                    <h6 class="mb-2">${bed.name}</h6>
                                                    <p class="mb-0 text-success fw-bold">Tsh ${bed.rent_price}</p>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    $('#bedsList').append(bedCard);
                                });
                            } else {
                                $('#bedsList').html('<div class="col-12"><p class="text-center text-muted">No free beds available in this room.</p></div>');
                            }
                        },
                        error: function() {
                            $('#bedsList').html('<div class="col-12"><p class="text-center text-danger">Error loading beds.</p></div>');
                        }
                    });
                } else {
                    // No beds, go directly to booking form
                    openBookingDetailsModal(null);
                }
            });

            // Handle bed selection
            $(document).on('click', '.bed-card', function() {
                const bedId = $(this).data('bed-id');
                const bedModal = bootstrap.Modal.getInstance(document.getElementById('bedSelectionModal'));
                bedModal.hide();
                openBookingDetailsModal(bedId);
            });

            // Open booking details modal
            function openBookingDetailsModal(bedId) {
                $('#form_block_id').val(selectedBlockId);
                $('#form_room_id').val(selectedRoomId);
                if (bedId) {
                    $('#form_bed_id').val(bedId);
                } else {
                    $('#form_bed_id').val('');
                }
                
                const bookingModal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
                bookingModal.show();
            }

            // Add cursor pointer style
            $('<style>').prop('type', 'text/css').html(`
                .cursor-pointer { cursor: pointer; }
                .room-card:hover { transform: scale(1.05); transition: transform 0.2s; }
                .bed-card:hover { transform: scale(1.05); transition: transform 0.2s; }
            `).appendTo('head');

            // Load Terms and Conditions when modal opens
            $('#bookingDetailsModal').on('show.bs.modal', function() {
                loadTermsAndConditions();
            });

            function loadTermsAndConditions() {
                $.ajax({
                    url: '{{ route("terms.active") }}',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success && response.terms) {
                            // Convert content to list format
                            const content = response.terms.content || '';
                            const items = content.split('\n')
                                .map(item => item.trim())
                                .filter(item => item && item.startsWith('-'))
                                .map(item => item.replace(/^-\s*/, ''));
                            
                            if (items.length > 0) {
                                let listHtml = '<ul class="mb-0" style="padding-left: 1.5rem;">';
                                items.forEach(function(item) {
                                    if (item) {
                                        listHtml += `<li>${item}</li>`;
                                    }
                                });
                                listHtml += '</ul>';
                                $('#termsContent').html(listHtml);
                            } else if (content) {
                                // Fallback to plain text if no list items
                                const termsContent = content.replace(/\n/g, '<br>');
                                $('#termsContent').html(termsContent);
                            } else {
                                $('#termsContent').html('<p class="text-muted">No terms and conditions available at the moment.</p>');
                            }
                        } else {
                            $('#termsContent').html('<p class="text-muted">No terms and conditions available at the moment.</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading terms:', status, error, xhr);
                        // Check if it's a 404 (no terms found) vs other error
                        if (xhr.status === 404) {
                            $('#termsContent').html('<p class="text-muted">No terms and conditions available at the moment.</p>');
                        } else {
                            $('#termsContent').html('<p class="text-warning">Unable to load terms and conditions. Please try again later.</p>');
                        }
                    }
                });
            }

            // Handle booking form submission via AJAX
            $('#bookingForm').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $submitBtn = $('#submitBookingBtn');
                const $submitText = $('#submitBookingText');
                const $submitSpinner = $('#submitBookingSpinner');
                const $alert = $('#bookingAlert');
                
                // Reset alert and validation errors
                $alert.removeClass('alert-success alert-danger').addClass('d-none').html('');
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').text('');
                
                // Validate required hidden fields
                const blockId = $('#form_block_id').val();
                const roomId = $('#form_room_id').val();
                
                if (!blockId || !roomId) {
                    $alert.removeClass('d-none').addClass('alert-danger').html(
                        '<i class="bi bi-exclamation-circle me-2"></i>Please select a block and room before booking.'
                    );
                    return;
                }

                // Validate terms acceptance
                if (!$('#acceptTerms').is(':checked')) {
                    $('#acceptTerms').addClass('is-invalid');
                    $alert.removeClass('d-none').addClass('alert-danger').html(
                        '<i class="bi bi-exclamation-circle me-2"></i>You must accept the Terms and Conditions to proceed with booking.'
                    );
                    return;
                }
                
                // Disable submit button and show loading
                $submitBtn.prop('disabled', true);
                $submitText.text('Processing...');
                $submitSpinner.removeClass('d-none');
                
                // Get form data and normalize bed_id
                let formData = $form.serializeArray();
                let bedId = $('#form_bed_id').val();
                
                // Remove bed_id if it's empty, otherwise keep it
                formData = formData.filter(function(item) {
                    if (item.name === 'bed_id' && (!bedId || bedId === '')) {
                        return false; // Remove empty bed_id
                    }
                    return true;
                });
                
                // Convert to object for easier manipulation
                let formDataObj = {};
                formData.forEach(function(item) {
                    formDataObj[item.name] = item.value;
                });
                
                // Debug: Log form data
                console.log('Form data being sent:', formDataObj);
                console.log('Block ID:', blockId);
                console.log('Room ID:', roomId);
                console.log('Bed ID:', bedId || 'null');
                console.log('Full Name:', $('#bookingFullName').val());
                console.log('Phone:', $('#bookingPhone').val());
                console.log('Check-in Date:', $('#bookingCheckInDate').val());
                
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formDataObj,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $alert.removeClass('d-none alert-danger').addClass('alert-success').html(
                                '<i class="bi bi-check-circle me-2"></i>' + response.message
                            );
                            
                            // Show success modal with booking details
                            const bookingInfo = response.booking_info;
                            let successContent = `
                                <div class="text-center mb-4">
                                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h5 class="text-center mb-3">Booking Confirmed!</h5>
                                <div class="alert alert-info">
                                    <p class="mb-2"><strong><i class="bi bi-qr-code me-1"></i>Control Number:</strong> 
                                        <span class="badge bg-primary fs-6">${bookingInfo.control_number}</span>
                                    </p>
                                    <p class="mb-2"><strong><i class="bi bi-clock me-1"></i>Expires At:</strong> 
                                        <span class="text-danger fw-bold">${bookingInfo.expires_at || bookingInfo.expires_at_formatted}</span>
                                    </p>
                                    <p class="mb-2"><strong><i class="bi bi-hourglass-split me-1"></i>Time Remaining:</strong> 
                                        <span class="text-warning fw-bold">${bookingInfo.timeout_display || 'Check SMS'}</span>
                                    </p>
                                    <hr>
                                    <p class="mb-2"><strong><i class="bi bi-door-open me-1"></i>Room:</strong> ${bookingInfo.room}${bookingInfo.bed ? ' - Bed: ' + bookingInfo.bed : ''}</p>
                                    <p class="mb-2"><strong><i class="bi bi-person me-1"></i>Username:</strong> ${bookingInfo.username}</p>
                                    <p class="mb-0"><strong><i class="bi bi-key me-1"></i>Password:</strong> ${bookingInfo.password}</p>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> Your login credentials and control number have been sent to your phone via SMS. 
                                    Please make payment using the control number within the specified time (${bookingInfo.timeout_display || 'check SMS'}) to secure your booking.
                                </div>
                            `;
                            $('#bookingSuccessContent').html(successContent);
                            
                            // Close booking modal
                            const bookingModal = bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal'));
                            if (bookingModal) bookingModal.hide();
                            
                            // Show success modal (will not auto-close)
                            const successModal = new bootstrap.Modal(document.getElementById('bookingSuccessModal'), {
                                backdrop: 'static', // Prevent closing by clicking outside
                                keyboard: false     // Prevent closing with ESC key
                            });
                            successModal.show();
                            
                            // Reset form
                            $form[0].reset();
                            
                            // Don't auto-reload page - let user close modal manually
                        } else {
                            // Show error message
                            $alert.removeClass('d-none alert-success').addClass('alert-danger').html(
                                '<i class="bi bi-exclamation-circle me-2"></i>' + response.message
                            );
                            
                            // Re-enable submit button
                            $submitBtn.prop('disabled', false);
                            $submitText.text('Complete Booking');
                            $submitSpinner.addClass('d-none');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Unable to complete booking. Please try again or contact support if the problem persists.';
                        
                        // Log error for debugging (but don't show to user)
                        console.error('Booking error response:', xhr.responseJSON);
                        console.error('Status:', xhr.status);
                        console.error('Response text:', xhr.responseText);
                        
                        // Clear previous validation errors
                        $form.find('.is-invalid').removeClass('is-invalid');
                        $form.find('.invalid-feedback').text('');
                        
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                // Use server message if it's user-friendly
                                const serverMessage = xhr.responseJSON.message;
                                // Check if message contains technical details (SQL, database errors, etc.)
                                if (serverMessage.includes('SQLSTATE') || 
                                    serverMessage.includes('Duplicate entry') || 
                                    serverMessage.includes('Integrity constraint') ||
                                    serverMessage.includes('Connection: mysql') ||
                                    serverMessage.includes('for key')) {
                                    // Don't show technical errors, use generic message
                                    errorMessage = 'This phone number is already registered. Please use a different phone number or login if you already have an account.';
                                } else {
                                    errorMessage = serverMessage;
                                }
                            } else if (xhr.responseJSON.errors) {
                                // Display validation errors and point to input fields
                                let errorList = '<ul class="mb-0">';
                                let firstErrorField = null;
                                
                                $.each(xhr.responseJSON.errors, function(key, errors) {
                                    $.each(errors, function(index, error) {
                                        // Format field name for display
                                        let fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, function(l) {
                                            return l.toUpperCase();
                                        });
                                        errorList += '<li><strong>' + fieldName + ':</strong> ' + error + '</li>';
                                        
                                        // Point to input field
                                        const fieldMap = {
                                            'full_name': '#bookingFullName',
                                            'phone': '#bookingPhone',
                                            'check_in_date': '#bookingCheckInDate',
                                            'block_id': '#form_block_id',
                                            'room_id': '#form_room_id',
                                            'bed_id': '#form_bed_id'
                                        };
                                        
                                        if (fieldMap[key]) {
                                            const $field = $(fieldMap[key]);
                                            $field.addClass('is-invalid');
                                            
                                            // For visible fields, show error message
                                            if ($field.is(':visible')) {
                                                if ($field.siblings('.invalid-feedback').length === 0) {
                                                    $field.after('<div class="invalid-feedback"></div>');
                                                }
                                                $field.siblings('.invalid-feedback').text(error);
                                            }
                                            
                                            // Track first error field for scrolling
                                            if (!firstErrorField && $field.is(':visible')) {
                                                firstErrorField = $field;
                                            }
                                        }
                                    });
                                });
                                errorList += '</ul>';
                                errorMessage = errorList;
                                
                                // Scroll to first error field
                                if (firstErrorField) {
                                    $('html, body').animate({
                                        scrollTop: firstErrorField.offset().top - 100
                                    }, 500);
                                    firstErrorField.focus();
                                }
                            }
                        }
                        
                        // Show error message
                        $alert.removeClass('d-none alert-success').addClass('alert-danger').html(
                            '<i class="bi bi-exclamation-circle me-2"></i>' + errorMessage
                        );
                        
                        // Re-enable submit button
                        $submitBtn.prop('disabled', false);
                        $submitText.text('Complete Booking');
                        $submitSpinner.addClass('d-none');
                    }
                });
            });

            // Countdown timer for pending bookings
            function updateCountdowns() {
                $('.countdown-timer').each(function() {
                    const $timer = $(this);
                    const expiresAtStr = $timer.data('expires-at');
                    if (!expiresAtStr) return;

                    const expiresAt = new Date(expiresAtStr);
                    const now = new Date();
                    const diff = expiresAt - now;

                    if (diff <= 0) {
                        // Expired
                        $timer.find('.countdown-text').text('Expired');
                        $timer.find('.badge').removeClass('bg-info bg-warning').addClass('bg-danger');
                        return;
                    }

                    // Calculate time remaining
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    let timeText = '';
                    if (hours > 0) {
                        timeText = hours + 'h ' + minutes + 'm ' + seconds + 's';
                    } else if (minutes > 0) {
                        timeText = minutes + 'm ' + seconds + 's';
                    } else {
                        timeText = seconds + 's';
                    }

                    $timer.find('.countdown-text').text(timeText);

                    // Update badge color based on time remaining
                    const $badge = $timer.find('.badge');
                    if (hours < 2) {
                        $badge.removeClass('bg-info').addClass('bg-warning');
                    } else {
                        $badge.removeClass('bg-warning').addClass('bg-info');
                    }
                });
            }

            // Update countdowns every second
            setInterval(updateCountdowns, 1000);
            updateCountdowns(); // Initial update

            // Phone number formatting - auto-format to 255XXXXXXXXX
            $('#bookingPhone').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-digits
                
                // If starts with 0, replace with 255
                if (value.startsWith('0')) {
                    value = '255' + value.substring(1);
                }
                // If doesn't start with 255, add it
                else if (!value.startsWith('255')) {
                    if (value.length > 0) {
                        value = '255' + value;
                    }
                }
                
                // Limit to 12 digits (255 + 9 more)
                if (value.length > 12) {
                    value = value.substring(0, 12);
                }
                
                $(this).val(value);
            });

            // Mobile modal scrolling - hide scrollbar but allow scrolling
            $('<style>').prop('type', 'text/css').html(`
                #bookingDetailsModal .modal-dialog {
                    margin: 0.5rem;
                }
                #bookingDetailsModal .modal-body {
                    max-height: 70vh;
                    overflow-y: auto;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: none;
                    -ms-overflow-style: none;
                    padding-bottom: 20px;
                }
                #bookingDetailsModal .modal-body::-webkit-scrollbar {
                    display: none;
                    width: 0;
                    height: 0;
                }
                @media (max-width: 768px) {
                    #bookingDetailsModal .modal-dialog {
                        margin: 0.5rem;
                        max-height: 95vh;
                        height: auto;
                    }
                    #bookingDetailsModal .modal-content {
                        max-height: 95vh;
                        display: flex;
                        flex-direction: column;
                    }
                    #bookingDetailsModal .modal-header {
                        flex-shrink: 0;
                    }
                    #bookingDetailsModal .modal-body {
                        max-height: calc(95vh - 140px);
                        overflow-y: auto;
                        -webkit-overflow-scrolling: touch;
                        scrollbar-width: none;
                        -ms-overflow-style: none;
                        flex: 1 1 auto;
                    }
                    #bookingDetailsModal .modal-body::-webkit-scrollbar {
                        display: none;
                    }
                    #bookingDetailsModal #submitBookingBtn {
                        position: sticky;
                        bottom: 0;
                        z-index: 10;
                        margin-top: 10px;
                    }
                }
            `).appendTo('head');
        });
    </script>
</body>

</html>

