@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-cash-coin me-2"></i>Payments Management
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal"
                        style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                    <i class="bi bi-plus-circle me-2"></i>Record Payment
                </button>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow-sm mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Last Payments (Cash & Bank)</h5>
            </div>
            <div class="card-body">
            <div class="table-responsive" id="paymentsTableWrapper">
                <table id="paymentsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Student</th>
                            <th>Amount (Tsh)</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th class="table-desktop-only">Block</th>
                            <th class="table-desktop-only">Room</th>
                            <th class="table-desktop-only">Bed</th>
                            <th class="table-desktop-only">Period Code</th>
                            <th class="table-desktop-only">Period</th>
                            <th class="table-desktop-only">Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr data-payment-id="{{ $payment->id }}">
                            <td>{{ $payment->payment_date->format('j F Y') }}</td>
                            <td>{{ $payment->student->full_name }}</td>
                            <td><strong>Tsh {{ number_format($payment->amount, 2) }}</strong></td>
                            <td>
                                @if($payment->payment_method === 'cash')
                                    <span class="badge bg-primary">Cash</span>
                                @else
                                    <span class="badge bg-info">Bank</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($payment->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td class="table-desktop-only">{{ $payment->student->room ? $payment->student->room->block->name : 'N/A' }}</td>
                            <td class="table-desktop-only">{{ $payment->student->room ? $payment->student->room->name : 'N/A' }}</td>
                            <td class="table-desktop-only">{{ $payment->student->bed ? $payment->student->bed->name : 'N/A' }}</td>
                            <td class="table-desktop-only">
                                @if($payment->period_code)
                                    <span class="badge bg-secondary">{{ $payment->period_code }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="table-desktop-only">
                                @if($payment->period_start_date && $payment->period_end_date)
                                    <small>
                                        {{ $payment->period_start_date->format('j F Y') }}<br>
                                        <strong>to</strong><br>
                                        {{ $payment->period_end_date->format('j F Y') }}
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="table-desktop-only">{{ $payment->reference_number ?? 'N/A' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-payment" data-student-id="{{ $payment->student_id }}" title="View All Payments">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-payment-main d-none d-md-inline-block" data-payment-id="{{ $payment->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-payment-main d-none d-md-inline-block" data-payment-id="{{ $payment->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center">No payments recorded yet.</td>
                        </tr>
                        @endforelse
                        </tbody>
                </table>
                <div class="text-center mt-2">
                    <button class="btn btn-sm btn-outline-primary view-more-cols-btn" onclick="toggleViewMore('paymentsTableWrapper')">
                        <i class="bi bi-arrows-expand me-1"></i><span class="view-more-text">View More</span><span class="view-less-text d-none">View Less</span>
                    </button>
                </div>
            </div>
            </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="addPaymentModalLabel">
                    <i class="bi bi-cash-coin me-2"></i>Record Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                        <label for="paymentStudent" class="form-label">Select Student <span class="text-danger">*</span></label>
                        <select class="form-select" id="paymentStudent" name="student_id" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->student_number }} - {{ $student->full_name }}
                                    @if($student->room)
                                        ({{ $student->room->block->name }} - {{ $student->room->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="studentPaymentInfo" class="alert alert-info" style="display: none;">
                        <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Rent Information</h6>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Payment Type:</strong> <span id="paymentType">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Rent Duration:</strong> <span id="rentDuration">-</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Rent Price:</strong> <span id="expectedAmount" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Payment Frequency:</strong> <span id="paymentFrequency">-</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Check-in Date:</strong> <span id="checkInDate" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Rent End Date:</strong> <span id="rentEndDate">-</span>
                            </div>
                        </div>
                        <div class="row mb-2" id="rentStartDateRow">
                            <div class="col-md-12">
                                <strong>Rent Start Date (for this payment):</strong> 
                                <span id="rentStartDate" class="text-primary fw-bold">-</span>
                                <small class="text-muted d-block mt-1" id="rentStartDateNote"></small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-12">
                                <strong>Next Payment Due:</strong> <span id="nextPaymentDue" class="text-warning fw-bold">-</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Total Paid:</strong> <span id="totalPaid" class="text-success fw-bold">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Pending Amount:</strong> <span id="pendingAmount" class="text-danger fw-bold">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in Date Display (only for new students) - Display at the top -->
                    <div class="alert alert-success" id="checkInDateRow" style="display: none;">
                        <h6 class="mb-2"><i class="bi bi-calendar-check me-2"></i>Check-in Date (from Booking)</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold">Check-in Date:</label>
                                <div class="form-control form-control-lg bg-light" id="checkInDateDisplay" style="border: 2px solid #28a745;">
                                    <i class="bi bi-calendar3 me-2"></i><span id="checkInDateText">-</span>
                                </div>
                                <small class="form-text text-muted mt-1">
                                    <i class="bi bi-info-circle me-1"></i>Rent calculation will start from this date (set during booking)
                                </small>
                                <input type="hidden" id="checkInDateInput" name="check_in_date">
                            </div>
                        </div>
                    </div>

                    <div id="paymentPeriodPreview" class="alert alert-info" style="display: none;">
                        <h6 class="mb-3"><i class="bi bi-calendar-range me-2"></i>Payment Period Preview</h6>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <strong>Period Code:</strong> <span id="previewPeriodCode" class="badge bg-secondary">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Start Date:</strong> <span id="previewStartDate" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>End Date:</strong> <span id="previewEndDate" class="fw-bold">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="paymentAmount" class="form-label">Amount (Tsh) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="paymentAmount" name="amount" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="paymentDate" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="paymentDate" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="referenceNumberContainer" style="display: none;">
                            <label for="referenceNumber" class="form-label">Reference Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="referenceNumber" name="reference_number" placeholder="e.g., Receipt number, Transaction ID">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="paymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentMethod" name="payment_method" required>
                                <option value="cash" selected>Cash</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="paymentNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="paymentNotes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitPaymentBtn"
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="submitPaymentSpinner" role="status"></span>
                        <span id="submitPaymentText">Record Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payment Modal -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1" aria-labelledby="viewPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="viewPaymentModalLabel">
                    <i class="bi bi-eye me-2"></i>Payment Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <strong>Student:</strong>
                        <p id="viewStudentName">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Student Number:</strong>
                        <p id="viewStudentNumber">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Block:</strong>
                        <p id="viewBlock">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Room:</strong>
                        <p id="viewRoom">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Bed:</strong>
                        <p id="viewBed">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Total Paid:</strong>
                        <p id="viewAmount">-</p>
                    </div>
                </div>
                
                <hr>
                <h6 class="mb-3"><i class="bi bi-cash-coin me-2"></i>All Payments History</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Reserve Amount</th>
                                <th>Period Code</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="viewPaymentsBody">
                            <tr>
                                <td colspan="11" class="text-center">Loading payments...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="editPaymentModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPaymentForm">
                <input type="hidden" id="editPaymentId" name="id">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPaymentAmount" class="form-label">Amount (Tsh) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editPaymentAmount" name="amount" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editPaymentDate" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="editPaymentDate" name="payment_date" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="editReferenceNumberContainer">
                            <label for="editReferenceNumber" class="form-label">Reference Number <span class="text-danger" id="editReferenceRequired" style="display: none;">*</span></label>
                            <input type="text" class="form-control" id="editReferenceNumber" name="reference_number" placeholder="e.g., Receipt number, Transaction ID">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editPaymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="editPaymentMethod" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editPaymentNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="editPaymentNotes" name="notes" rows="2"></textarea>
                    </div>

                    <div id="editPaymentPeriodPreview" class="alert alert-info" style="display: none;">
                        <h6 class="mb-3"><i class="bi bi-calendar-range me-2"></i>Payment Period Preview</h6>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <strong>Period Code:</strong> <span id="editPreviewPeriodCode" class="badge bg-secondary">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Start Date:</strong> <span id="editPreviewStartDate" class="fw-bold">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>End Date:</strong> <span id="editPreviewEndDate" class="fw-bold">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitEditPaymentBtn"
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="submitEditPaymentSpinner" role="status"></span>
                        <span id="submitEditPaymentText">Update Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTables
        setTimeout(function() {
            if ($('#paymentsTable').length) {
                const table = $('#paymentsTable');
                const tbody = table.find('tbody');
                const rows = tbody.find('tr');
                
                const hasData = rows.length > 0 && !rows.first().find('td[colspan]').length;
                
                if (hasData) {
                    try {
                        if ($.fn.DataTable.isDataTable('#paymentsTable')) {
                            table.DataTable().destroy();
                        }
                        
                        table.DataTable({
                            pageLength: 5,
                            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                            order: [[0, 'desc']],
                            responsive: true,
                            columnDefs: [
                                { orderable: false, targets: -1 }
                            ]
                        });
                    } catch (e) {
                        console.error('Error initializing DataTable:', e);
                    }
                }
            }
        }, 100);

        let studentPaymentDetails = null;

        // Function to calculate and display period preview
        function calculatePeriodPreview() {
            const previewDiv = $('#paymentPeriodPreview');
            const studentId = $('#paymentStudent').val();
            const amount = parseFloat($('#paymentAmount').val()) || 0;
            const paymentDate = $('#paymentDate').val();
            
            if (!studentId || !amount || !paymentDate || !studentPaymentDetails) {
                previewDiv.hide();
                return;
            }
            
            // Get rent_price directly from response (base price per month or per semester)
            const rentPrice = parseFloat(studentPaymentDetails.rent_price) || 0;
            const rentDuration = studentPaymentDetails.rent_duration;
            const semesterMonths = studentPaymentDetails.semester_months;
            
            if (rentPrice <= 0) {
                previewDiv.hide();
                return;
            }
            
            // Generate period code
            const periodCode = 'PAY-' + Math.random().toString(36).substring(2, 10).toUpperCase();
            $('#previewPeriodCode').text(periodCode);
            
            // Determine start date: 
            // - For new students (no previous payments): Use check-in date
            // - For existing students: Use last payment end date + 1 day
            let startDate = null;
            
            // Check if there are previous payments
            const hasPreviousPayments = studentPaymentDetails.payments && studentPaymentDetails.payments.length > 0;
            
            if (hasPreviousPayments) {
                // Existing student - find the last payment with period_end_date
                const paymentsWithEndDate = studentPaymentDetails.payments
                    .filter(p => p.period_end_date)
                    .sort((a, b) => new Date(b.period_end_date) - new Date(a.period_end_date));
                
                if (paymentsWithEndDate.length > 0) {
                    const lastPayment = paymentsWithEndDate[0];
                    // Start from where last payment ended + 1 day
                    const endDateParts = lastPayment.period_end_date.split('-');
                    if (endDateParts.length === 3) {
                        startDate = new Date(parseInt(endDateParts[0]), parseInt(endDateParts[1]) - 1, parseInt(endDateParts[2]));
                        startDate.setDate(startDate.getDate() + 1); // Add 1 day
                    } else {
                        startDate = new Date(lastPayment.period_end_date);
                        startDate.setDate(startDate.getDate() + 1);
                    }
                }
            }
            
            // If no start date determined yet (new student), use check-in date from booking
            if (!startDate) {
                // Get check-in date from hidden input (set from booking)
                const checkInDateInput = $('#checkInDateInput').val();
                if (checkInDateInput) {
                    const dateParts = checkInDateInput.split('-');
                    if (dateParts.length === 3) {
                        startDate = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                    } else {
                        startDate = new Date(checkInDateInput);
                    }
                } else if (studentPaymentDetails.student && studentPaymentDetails.student.check_in_date) {
                    // Fallback to existing check-in date from student record
                    const checkInDate = studentPaymentDetails.student.check_in_date;
                    const dateParts = checkInDate.split('-');
                    if (dateParts.length === 3) {
                        startDate = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                    } else {
                        startDate = new Date(checkInDate);
                    }
                } else {
                    // Final fallback to payment date
                    const paymentDateParts = paymentDate.split('-');
                    if (paymentDateParts.length === 3) {
                        startDate = new Date(parseInt(paymentDateParts[0]), parseInt(paymentDateParts[1]) - 1, parseInt(paymentDateParts[2]));
                    } else {
                        startDate = new Date(paymentDate);
                    }
                }
            }
            
            // Calculate end date based on rent_price and amount
            // For monthly: months_covered = amount / rent_price
            // For semester: semesters_covered = amount / rent_price
            let endDate = new Date(startDate);
            
            if (rentDuration === 'semester' && semesterMonths) {
                // For semester: rent_price is per semester
                const semestersCovered = Math.floor(amount / rentPrice);
                if (semestersCovered > 0) {
                    endDate.setMonth(endDate.getMonth() + (semesterMonths * semestersCovered));
                } else {
                    endDate.setMonth(endDate.getMonth() + 1); // Default 1 month
                }
            } else {
                // For monthly: rent_price is per month
                // Calculate months directly: amount / rent_price
                const monthsCovered = Math.floor(amount / rentPrice);
                if (monthsCovered > 0) {
                    endDate.setMonth(endDate.getMonth() + monthsCovered);
                } else {
                    endDate.setMonth(endDate.getMonth() + 1); // Default 1 month
                }
            }
            
            // Format dates as "9 December 2025"
            const formatDatePreview = (date) => {
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                               'July', 'August', 'September', 'October', 'November', 'December'];
                return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
            };
            
            $('#previewStartDate').text(formatDatePreview(startDate));
            $('#previewEndDate').text(formatDatePreview(endDate));
            previewDiv.show();
        }

        // Load student payment details when student is selected
        $('#paymentStudent').on('change', function() {
            const studentId = $(this).val();
            const studentInfo = $('#studentPaymentInfo');
            
            if (!studentId) {
                studentInfo.hide();
                $('#paymentPeriodPreview').hide();
                $('#checkInDateRow').hide();
                $('#checkInDateInput').val('');
                studentPaymentDetails = null;
                return;
            }

            $.ajax({
                url: `/students/${studentId}/payment-details`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        studentPaymentDetails = response;
                        
                        $('#paymentType').text(response.payment_type || 'N/A');
                        $('#rentDuration').text(response.rent_duration ? response.rent_duration.charAt(0).toUpperCase() + response.rent_duration.slice(1) : 'N/A');
                        $('#paymentFrequency').text(response.payment_frequency ? response.payment_frequency.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A');
                        $('#expectedAmount').text('Tsh ' + parseFloat(response.expected_amount).toLocaleString());
                        $('#totalPaid').text('Tsh ' + parseFloat(response.total_paid).toLocaleString());
                        $('#pendingAmount').text('Tsh ' + parseFloat(response.pending_amount).toLocaleString());
                        
                        // Format dates to "9 December 2025" format
                        const formatDateDisplay = (dateString) => {
                            if (!dateString) return 'N/A';
                            // Parse date string to avoid timezone issues (format: YYYY-MM-DD)
                            const dateParts = dateString.split('-');
                            if (dateParts.length !== 3) {
                                // Fallback to regular parsing if format is different
                            const date = new Date(dateString);
                                const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                               'July', 'August', 'September', 'October', 'November', 'December'];
                                return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
                            }
                            const date = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                           'July', 'August', 'September', 'October', 'November', 'December'];
                            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
                        };
                        
                        // Display check-in date info
                        if (response.check_in_date) {
                            $('#checkInDate').text(formatDateDisplay(response.check_in_date));
                        } else {
                            $('#checkInDate').text('Not set');
                        }
                        
                        // Show/hide check-in date display based on whether student has previous payments
                        if (response.has_previous_payments) {
                            // Existing student - hide check-in date field (will use last payment end date)
                            $('#checkInDateRow').hide();
                            $('#checkInDateInput').val('');
                            
                            // Show rent start date info (will be calculated from last payment)
                            $('#rentStartDateRow').show();
                            $('#rentStartDate').text('Will be calculated from last payment end date + 1 day');
                            $('#rentStartDateNote').text('(Calculated automatically from last payment period end date)');
                        } else {
                            // New student - show check-in date from booking
                            if (response.check_in_date) {
                                // Display check-in date from booking
                                $('#checkInDateRow').show();
                                $('#checkInDateText').text(formatDateDisplay(response.check_in_date));
                                $('#checkInDateInput').val(response.check_in_date);
                                
                                // Scroll to check-in date field for better visibility
                                setTimeout(function() {
                                    $('html, body').animate({
                                        scrollTop: $('#checkInDateRow').offset().top - 100
                                    }, 300);
                                }, 100);
                                
                                // Show rent start date info
                                $('#rentStartDateRow').show();
                                $('#rentStartDate').text(formatDateDisplay(response.check_in_date));
                                $('#rentStartDateNote').text('(Rent will start counting from the Check-in Date set during booking)');
                            } else {
                                // No check-in date from booking - hide the field
                                $('#checkInDateRow').hide();
                                $('#checkInDateInput').val('');
                                
                                // Show rent start date info (will use payment date)
                                $('#rentStartDateRow').show();
                                $('#rentStartDate').text('Will use Payment Date');
                                $('#rentStartDateNote').text('(No check-in date set during booking - will use payment date)');
                            }
                        }
                        
                        $('#rentEndDate').text(formatDateDisplay(response.rent_end_date));
                        $('#nextPaymentDue').text(formatDateDisplay(response.next_payment_due));
                        
                        // Set suggested amount as pending amount
                        if (response.pending_amount > 0) {
                            $('#paymentAmount').val(response.pending_amount);
                        } else {
                            $('#paymentAmount').val(response.expected_amount);
                        }
                        
                        studentInfo.show();
                        calculatePeriodPreview();
                    }
                },
                error: function(xhr) {
                    console.error('Error loading student details:', xhr);
                    studentInfo.hide();
                    $('#paymentPeriodPreview').hide();
                    $('#checkInDateRow').hide();
                    $('#checkInDateInput').val('');
                    studentPaymentDetails = null;
                }
            });
        });

                        // Calculate period preview when amount or date changes
        $('#paymentAmount, #paymentDate').on('input change', function() {
            calculatePeriodPreview();
        });

        // Show/hide reference number based on payment method
        $('#paymentMethod').on('change', function() {
            const paymentMethod = $(this).val();
            const referenceContainer = $('#referenceNumberContainer');
            const referenceInput = $('#referenceNumber');
            
            if (paymentMethod === 'bank') {
                referenceContainer.show();
                referenceInput.prop('required', true);
            } else {
                referenceContainer.hide();
                referenceInput.prop('required', false).val('');
            }
        });

        // Initialize on page load
        $('#paymentMethod').trigger('change');

        // Handle add payment form submission
        $('#addPaymentForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const submitBtn = $('#submitPaymentBtn');
            const submitSpinner = $('#submitPaymentSpinner');
            const submitText = $('#submitPaymentText');
            const formErrors = $('#formErrors');
            
            formErrors.addClass('d-none').html('');
            submitBtn.prop('disabled', true);
            submitSpinner.removeClass('d-none');
            submitText.text('Recording...');

            $.ajax({
                url: '{{ route("payments.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<i class="bi bi-check-circle-fill me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('.container-fluid').prepend(alert);
                    
                    $('#addPaymentModal').modal('hide');
                    $('#addPaymentForm')[0].reset();
                    $('#studentPaymentInfo').hide();
                    $('#paymentPeriodPreview').hide();
                    $('#checkInDateRow').hide();
                    $('#checkInDateInput').val('');
                    $('#paymentMethod').val('cash').trigger('change'); // Reset to cash and hide reference
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false);
                    submitSpinner.addClass('d-none');
                    submitText.text('Record Payment');
                    
                    console.error('Payment Error:', xhr);
                    console.error('Response:', xhr.responseJSON);
                    
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                                errors += '<li><strong>' + key + ':</strong> ' + value[0] + '</li>';
                        });
                        } else {
                            errors += '<li>Validation error occurred</li>';
                        }
                        errors += '</ul>';
                        formErrors.removeClass('d-none').html(errors);
                    } else if (xhr.status === 500) {
                        const errorMessage = xhr.responseJSON?.message || xhr.responseText || 'Server error occurred';
                        formErrors.removeClass('d-none').html('<strong>Server Error:</strong> ' + errorMessage);
                    } else {
                        const errorMessage = xhr.responseJSON?.message || xhr.responseText || 'An error occurred. Please try again.';
                        formErrors.removeClass('d-none').html('<strong>Error:</strong> ' + errorMessage);
                    }
                }
            });
        });

        // View payment - show all payments for student
        $(document).on('click', '.view-payment', function() {
            console.log('View payment clicked');
            const studentId = $(this).data('student-id');
            console.log('Student ID:', studentId);
            
            if (!studentId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hitilafu',
                    text: 'Nambari ya mwanafunzi haijapatikana',
                    confirmButtonText: 'Sawa',
                    confirmButtonColor: '#1e3c72'
                });
                return;
            }
            
            // Load student details first
            $.ajax({
                url: `/students/${studentId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(studentResponse) {
                    console.log('Student response:', studentResponse);
                    $('#viewStudentName').text(studentResponse.full_name);
                    $('#viewStudentNumber').text(studentResponse.student_number);
                    $('#viewBlock').text(studentResponse.room ? studentResponse.room.block.name : 'N/A');
                    $('#viewRoom').text(studentResponse.room ? studentResponse.room.name : 'N/A');
                    $('#viewBed').text(studentResponse.bed ? studentResponse.bed.name : 'N/A');
                    
                    // Load all payments for this student
                    $.ajax({
                        url: `/students/${studentId}/payment-details`,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(paymentResponse) {
                            console.log('Payment response:', paymentResponse);
                            if (paymentResponse.success) {
                                // Calculate total paid
                                const totalPaid = paymentResponse.total_paid || 0;
                                $('#viewAmount').html('<strong class="text-success">Total: Tsh ' + parseFloat(totalPaid).toLocaleString() + '</strong>');
                                
                                // Display all payments
                                const paymentsBody = $('#viewPaymentsBody');
                                if (paymentResponse.payments && paymentResponse.payments.length > 0) {
                                    paymentsBody.html('');
                                    paymentResponse.payments.forEach(function(payment) {
                                        const statusBadge = payment.status === 'completed' ? 
                                            '<span class="badge bg-success">Completed</span>' : 
                                            payment.status === 'pending' ? 
                                            '<span class="badge bg-warning text-dark">Pending</span>' : 
                                            '<span class="badge bg-danger">Failed</span>';
                                        
                                        const methodBadge = payment.payment_method === 'cash' ? 
                                            '<span class="badge bg-primary">Cash</span>' : 
                                            '<span class="badge bg-info">Bank</span>';
                                        
                                        // Format date to "9 December 2025" format
                                        const formatDateForTable = (dateString) => {
                                            if (!dateString) return 'N/A';
                                            // Parse date string to avoid timezone issues (format: YYYY-MM-DD)
                                            const dateParts = dateString.split('-');
                                            if (dateParts.length === 3) {
                                                const date = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                                                const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                                               'July', 'August', 'September', 'October', 'November', 'December'];
                                                return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
                                            } else {
                                                // Fallback to regular parsing if format is different
                                            const date = new Date(dateString);
                                            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                                           'July', 'August', 'September', 'October', 'November', 'December'];
                                            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
                                            }
                                        };
                                        
                                        const startDate = formatDateForTable(payment.period_start_date);
                                        const endDate = formatDateForTable(payment.period_end_date);
                                        const periodCode = payment.period_code || 'N/A';
                                        
                                        const reserveAmount = payment.reserve_amount || 0;
                                        paymentsBody.append(`
                                            <tr data-payment-id="${payment.id}">
                                                <td>${formatDateForTable(payment.payment_date)}</td>
                                                <td><strong>Tsh ${parseFloat(payment.amount).toLocaleString()}</strong></td>
                                                <td><strong class="text-success">Tsh ${parseFloat(reserveAmount).toLocaleString()}</strong></td>
                                                <td><span class="badge bg-secondary">${periodCode}</span></td>
                                                <td>${startDate}</td>
                                                <td>${endDate}</td>
                                                <td>${methodBadge}</td>
                                                <td>${statusBadge}</td>
                                                <td>${payment.reference_number || 'N/A'}</td>
                                                <td>${payment.notes || 'N/A'}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning edit-payment-btn" data-payment-id="${payment.id}" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-payment-btn" data-payment-id="${payment.id}" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        `);
                                    });
                                } else {
                                    paymentsBody.html('<tr><td colspan="11" class="text-center">No payments recorded yet.</td></tr>');
                                }
                            } else {
                                $('#viewAmount').text('Tsh 0.00');
                                $('#viewPaymentsBody').html('<tr><td colspan="11" class="text-center">No payments recorded yet.</td></tr>');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error loading payments:', xhr);
                            $('#viewAmount').text('Tsh 0.00');
                            $('#viewPaymentsBody').html('<tr><td colspan="11" class="text-center">Error loading payments.</td></tr>');
                        }
                    });
                    
                    $('#viewPaymentModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error loading student:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Hitilafu',
                        text: 'Kuna hitilafu wakati wa kupakia taarifa za mwanafunzi. Tafadhali jaribu tena.',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#1e3c72'
                    });
                }
            });
        });

        // Edit payment button handler (from main table)
        $(document).on('click', '.edit-payment-main', function() {
            const paymentId = $(this).data('payment-id');
            editPayment(paymentId);
        });

        // Edit payment button handler (from view modal)
        $(document).on('click', '.edit-payment-btn', function() {
            const paymentId = $(this).data('payment-id');
            $('#viewPaymentModal').modal('hide');
            editPayment(paymentId);
        });

        // Edit payment function
        function editPayment(paymentId) {
            $.ajax({
                url: `/payments/${paymentId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editPaymentId').val(response.id);
                    $('#editPaymentAmount').val(response.amount);
                    $('#editPaymentDate').val(response.payment_date);
                    $('#editReferenceNumber').val(response.reference_number || '');
                    $('#editPaymentMethod').val(response.payment_method);
                    $('#editPaymentNotes').val(response.notes || '');
                    
                    // Show/hide reference number based on payment method
                    if (response.payment_method === 'bank') {
                        $('#editReferenceRequired').show();
                        $('#editReferenceNumber').prop('required', true);
                    } else {
                        $('#editReferenceRequired').hide();
                        $('#editReferenceNumber').prop('required', false);
                    }
                    
                    // Show period preview
                    if (response.period_code) {
                        $('#editPreviewPeriodCode').text(response.period_code);
                        $('#editPreviewStartDate').text(response.period_start_date ? formatDateToDisplay(response.period_start_date) : 'N/A');
                        $('#editPreviewEndDate').text(response.period_end_date ? formatDateToDisplay(response.period_end_date) : 'N/A');
                        $('#editPaymentPeriodPreview').show();
                    }
                    
                    $('#editPaymentModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error loading payment:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Hitilafu',
                        text: 'Kuna hitilafu wakati wa kupakia taarifa za malipo. Tafadhali jaribu tena.',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#1e3c72'
                    });
                }
            });
        }

        // Format date to "9 December 2025" format
        function formatDateToDisplay(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        }

        // Show/hide reference number for edit form based on payment method
        $('#editPaymentMethod').on('change', function() {
            const paymentMethod = $(this).val();
            const referenceRequired = $('#editReferenceRequired');
            const referenceInput = $('#editReferenceNumber');
            
            if (paymentMethod === 'bank') {
                referenceRequired.show();
                referenceInput.prop('required', true);
            } else {
                referenceRequired.hide();
                referenceInput.prop('required', false);
            }
        });

        // Calculate period preview for edit form
        $('#editPaymentAmount, #editPaymentDate').on('input change', function() {
            // This would need student details to calculate, so we'll skip for now
            // The period will be recalculated on server side when updating
        });

        // Handle edit payment form submission
        $('#editPaymentForm').on('submit', function(e) {
            e.preventDefault();
            
            const paymentId = $('#editPaymentId').val();
            const formData = $(this).serialize();
            const submitBtn = $('#submitEditPaymentBtn');
            const submitSpinner = $('#submitEditPaymentSpinner');
            const submitText = $('#submitEditPaymentText');
            const formErrors = $('#editFormErrors');
            
            formErrors.addClass('d-none').html('');
            submitBtn.prop('disabled', true);
            submitSpinner.removeClass('d-none');
            submitText.text('Updating...');

            $.ajax({
                url: `/payments/${paymentId}`,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<i class="bi bi-check-circle-fill me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('.container-fluid').prepend(alert);
                    
                    $('#editPaymentModal').modal('hide');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false);
                    submitSpinner.addClass('d-none');
                    submitText.text('Update Payment');
                    
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                        errors += '</ul>';
                        formErrors.removeClass('d-none').html(errors);
                    } else {
                        formErrors.removeClass('d-none').html('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Delete payment button handler (from main table)
        $(document).on('click', '.delete-payment-main', function() {
            const paymentId = $(this).data('payment-id');
            deletePayment(paymentId);
        });

        // Delete payment button handler (from view modal)
        $(document).on('click', '.delete-payment-btn', function() {
            const paymentId = $(this).data('payment-id');
            deletePayment(paymentId);
        });

        // Delete payment function
        function deletePayment(paymentId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this payment? This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/payments/${paymentId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message || 'Payment has been deleted successfully.',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Remove the row from table
                                $(`tr[data-payment-id="${paymentId}"]`).fadeOut(function() {
                                    $(this).remove();
                                });
                                
                                // If in view modal, reload the payments
                                if ($('#viewPaymentModal').is(':visible')) {
                                    const studentId = $('.view-payment').first().data('student-id');
                                    if (studentId) {
                                        // Trigger view payment to reload
                                        $('.view-payment').first().click();
                                    }
                                }
                                
                                // Reload page after a short delay
                                setTimeout(function() {
                                    location.reload();
                                }, 500);
                            });
                        },
                        error: function(xhr) {
                            console.error('Error deleting payment:', xhr);
                            const errorMessage = xhr.responseJSON?.message || 'Error deleting payment. Please try again.';
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }

        // Reset form when modal is closed
        $('#addPaymentModal').on('hidden.bs.modal', function() {
            $('#addPaymentForm')[0].reset();
            $('#studentPaymentInfo').hide();
            $('#paymentPeriodPreview').hide();
            $('#formErrors').addClass('d-none').html('');
            $('#paymentMethod').val('cash').trigger('change'); // Reset to cash and hide reference
            studentPaymentDetails = null;
        });
        
        // Toggle view more columns
        window.toggleViewMore = function(tableWrapperId) {
            const wrapper = document.getElementById(tableWrapperId);
            const btn = wrapper.querySelector('.view-more-cols-btn');
            const viewMoreText = btn.querySelector('.view-more-text');
            const viewLessText = btn.querySelector('.view-less-text');
            
            if (wrapper.classList.contains('show-all')) {
                wrapper.classList.remove('show-all');
                viewMoreText.classList.remove('d-none');
                viewLessText.classList.add('d-none');
            } else {
                wrapper.classList.add('show-all');
                viewMoreText.classList.add('d-none');
                viewLessText.classList.remove('d-none');
            }
        };
    });
</script>
@endpush

@endsection

