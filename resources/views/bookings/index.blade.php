@extends('layouts.app')

@section('title', 'Pending Bookings')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="bi bi-calendar-check me-2"></i>Pending Bookings
                </h1>
                <a href="{{ route('dashboard.owner') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
            <p class="text-muted">Manage pending bookings and record payments. Bookings expire after {{ $timeoutHours }} hours if payment is not made.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Pending Bookings Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pending Bookings</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Contact</th>
                            <th>Room/Bed</th>
                            <th>Booking Date</th>
                            <th>Time Remaining</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr class="{{ $booking['is_expired'] ? 'table-danger' : ($booking['hours_remaining'] < 2 ? 'table-warning' : '') }}">
                            <td>
                                <strong>{{ $booking['student']->full_name }}</strong><br>
                                <small class="text-muted">{{ $booking['student']->student_number }}</small>
                            </td>
                            <td>
                                <i class="bi bi-telephone me-1"></i>{{ $booking['student']->phone }}<br>
                                <i class="bi bi-envelope me-1"></i>{{ $booking['student']->email }}
                            </td>
                            <td>
                                <strong>{{ $booking['room']->name ?? 'N/A' }}</strong>
                                @if($booking['bed'])
                                    <br><span class="badge bg-info">Bed: {{ $booking['bed']->name }}</span>
                                @else
                                    <br><span class="badge bg-secondary">Key Room</span>
                                @endif
                            </td>
                            <td>
                                {{ $booking['student']->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td>
                                @if($booking['is_expired'])
                                    <span class="badge bg-danger">Expired</span>
                                @else
                                    <span class="badge bg-{{ $booking['hours_remaining'] < 2 ? 'warning' : 'info' }}">
                                        {{ $booking['time_remaining'] ?? 'N/A' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($booking['is_expired'])
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($booking['hours_remaining'] < 2)
                                    <span class="badge bg-warning">Urgent</span>
                                @else
                                    <span class="badge bg-primary">Pending</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal{{ $booking['student']->id }}">
                                        <i class="bi bi-cash-coin me-1"></i>Record Payment
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="cancelBooking({{ $booking['student']->id }})">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Record Payment Modal -->
                        <div class="modal fade" id="recordPaymentModal{{ $booking['student']->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Record Payment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('bookings.record-payment', $booking['student']->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Student</label>
                                                <input type="text" class="form-control" value="{{ $booking['student']->full_name }}" readonly>
                                            </div>
                                            
                                            <!-- Check-in Date Display -->
                                            @if($booking['student']->check_in_date)
                                            <div class="alert alert-info mb-3">
                                                <strong>Check-in Date:</strong> {{ \Carbon\Carbon::parse($booking['student']->check_in_date)->format('d F Y') }}
                                                <br><small class="text-muted">Rent will start counting from this date</small>
                                            </div>
                                            @endif
                                            
                                            <!-- Payment Period Preview -->
                                            <div class="alert alert-success mb-3" id="paymentPeriodPreview{{ $booking['student']->id }}" style="display: none;">
                                                <h6 class="mb-2"><i class="bi bi-calendar-range me-2"></i>Payment Period Preview</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Start Date:</strong> <span id="previewStartDate{{ $booking['student']->id }}">-</span>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>End Date:</strong> <span id="previewEndDate{{ $booking['student']->id }}">-</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Amount (Tsh) *</label>
                                                <input type="number" class="form-control" name="amount" id="amount{{ $booking['student']->id }}" required min="0" step="0.01" data-student-id="{{ $booking['student']->id }}" data-check-in-date="{{ $booking['student']->check_in_date ? \Carbon\Carbon::parse($booking['student']->check_in_date)->format('Y-m-d') : '' }}" data-rent-price="{{ $booking['student']->bed ? ($booking['student']->bed->rent_price ?? 0) : ($booking['student']->room ? ($booking['student']->room->rent_price ?? 0) : 0) }}" data-rent-duration="{{ $booking['student']->bed ? ($booking['student']->bed->rent_duration ?? 'monthly') : ($booking['student']->room ? ($booking['student']->room->rent_duration ?? 'monthly') : 'monthly') }}" data-semester-months="{{ $booking['student']->bed ? ($booking['student']->bed->semester_months ?? null) : ($booking['student']->room ? ($booking['student']->room->semester_months ?? null) : null) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Payment Method *</label>
                                                <select class="form-select" name="payment_method" required>
                                                    <option value="cash">Cash</option>
                                                    <option value="bank">Bank</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Payment Date *</label>
                                                <input type="date" class="form-control" name="payment_date" id="paymentDate{{ $booking['student']->id }}" value="{{ date('Y-m-d') }}" required data-student-id="{{ $booking['student']->id }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea class="form-control" name="notes" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Record Payment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted">No pending bookings at the moment.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paid Bookings Section -->
    @if(isset($paidBookingsData) && $paidBookingsData->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Paid Bookings</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="paidBookingsTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Contact</th>
                            <th>Room/Bed</th>
                            <th>Booking Date</th>
                            <th>Payment Date</th>
                            <th>Total Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paidBookingsData as $paidBooking)
                        <tr>
                            <td>
                                <strong>{{ $paidBooking['student']->full_name }}</strong><br>
                                <small class="text-muted">{{ $paidBooking['student']->student_number }}</small>
                            </td>
                            <td>
                                <i class="bi bi-telephone me-1"></i>{{ $paidBooking['student']->phone }}<br>
                                <i class="bi bi-envelope me-1"></i>{{ $paidBooking['student']->email }}
                            </td>
                            <td>
                                <strong>{{ $paidBooking['room']->name ?? 'N/A' }}</strong>
                                @if($paidBooking['bed'])
                                    <br><span class="badge bg-info">Bed: {{ $paidBooking['bed']->name }}</span>
                                @else
                                    <br><span class="badge bg-secondary">Key Room</span>
                                @endif
                            </td>
                            <td>
                                {{ $paidBooking['student']->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td>
                                @if($paidBooking['payment_date'])
                                    {{ \Carbon\Carbon::parse($paidBooking['payment_date'])->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">Tsh {{ number_format($paidBooking['total_paid'], 0) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-success">Paid</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function cancelBooking(bookingId) {
        Swal.fire({
            title: 'Thibitisha Ufutaji',
            text: 'Je, una uhakika unataka kufuta booking hii? Kitanda kitaachwa huru, na akaunti ya mwanafunzi na user zitaondolewa kabisa. Hatua hii haitaweza kutenguliwa.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ndio, Futa',
            cancelButtonText: 'Ghairi',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/bookings/' + bookingId + '/cancel';
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrfToken;
                
                form.appendChild(methodInput);
                form.appendChild(tokenInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Calculate and display payment period preview
    function calculatePeriodPreview(studentId) {
        const amountInput = document.getElementById('amount' + studentId);
        const paymentDateInput = document.getElementById('paymentDate' + studentId);
        const previewDiv = document.getElementById('paymentPeriodPreview' + studentId);
        const startDateSpan = document.getElementById('previewStartDate' + studentId);
        const endDateSpan = document.getElementById('previewEndDate' + studentId);
        
        if (!amountInput || !paymentDateInput || !previewDiv) return;
        
        const amount = parseFloat(amountInput.value) || 0;
        const paymentDate = paymentDateInput.value;
        const checkInDate = amountInput.getAttribute('data-check-in-date');
        const rentPrice = parseFloat(amountInput.getAttribute('data-rent-price')) || 0;
        const rentDuration = amountInput.getAttribute('data-rent-duration') || 'monthly';
        const semesterMonths = parseInt(amountInput.getAttribute('data-semester-months')) || null;
        
        if (amount > 0 && paymentDate) {
            // Calculate start date (use check-in date if available)
            let startDate = null;
            if (checkInDate) {
                startDate = new Date(checkInDate);
            } else {
                startDate = new Date(paymentDate);
            }
            
            // Calculate end date
            let endDate = new Date(startDate);
            if (rentPrice > 0) {
                if (rentDuration === 'semester' && semesterMonths && semesterMonths > 0) {
                    const semestersCovered = Math.floor(amount / rentPrice);
                    if (semestersCovered > 0) {
                        endDate = new Date(startDate);
                        endDate.setMonth(endDate.getMonth() + (semesterMonths * semestersCovered));
                    } else {
                        endDate = new Date(startDate);
                        endDate.setMonth(endDate.getMonth() + 1);
                    }
                } else {
                    const monthsCovered = Math.floor(amount / rentPrice);
                    if (monthsCovered > 0) {
                        endDate = new Date(startDate);
                        endDate.setMonth(endDate.getMonth() + monthsCovered);
                    } else {
                        endDate = new Date(startDate);
                        endDate.setMonth(endDate.getMonth() + 1);
                    }
                }
            } else {
                endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + 1);
            }
            
            // Format dates
            const formatDate = (date) => {
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                               'July', 'August', 'September', 'October', 'November', 'December'];
                return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
            };
            
            startDateSpan.textContent = formatDate(startDate);
            endDateSpan.textContent = formatDate(endDate);
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    }
    
    // Add event listeners for all payment modals
    document.addEventListener('DOMContentLoaded', function() {
        // Find all amount and payment date inputs
        const amountInputs = document.querySelectorAll('[id^="amount"]');
        const paymentDateInputs = document.querySelectorAll('[id^="paymentDate"]');
        
        amountInputs.forEach(input => {
            const studentId = input.getAttribute('data-student-id');
            if (studentId) {
                input.addEventListener('input', function() {
                    calculatePeriodPreview(studentId);
                });
            }
        });
        
        paymentDateInputs.forEach(input => {
            const studentId = input.getAttribute('data-student-id');
            if (studentId) {
                input.addEventListener('change', function() {
                    calculatePeriodPreview(studentId);
                });
            }
        });
        
        // Calculate on modal show
        const modals = document.querySelectorAll('[id^="recordPaymentModal"]');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                const studentId = modal.id.replace('recordPaymentModal', '');
                setTimeout(() => calculatePeriodPreview(studentId), 100);
            });
        });
    });

    // Auto-refresh every 60 seconds to update time remaining
    setInterval(function() {
        location.reload();
    }, 60000);
</script>
@endpush

@endsection
