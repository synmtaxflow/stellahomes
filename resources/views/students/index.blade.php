@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-people-fill me-2"></i>Student Management
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerStudentModal"
                        style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                    <i class="bi bi-person-plus me-2"></i>Register Student
                </button>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Students/Tenants</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="studentsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>Full Name</th>
                            <th class="table-desktop-only">Email</th>
                            <th class="table-desktop-only">Phone</th>
                            <th class="table-desktop-only">Course</th>
                            <th class="table-desktop-only">Block</th>
                            <th class="table-desktop-only">Room</th>
                            <th class="table-desktop-only">Bed</th>
                            <th class="table-desktop-only">Total Paid</th>
                            <th>Status</th>
                            <th class="table-desktop-only">Check-in Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td>{{ $student->student_number }}</td>
                            <td>{{ $student->full_name }}</td>
                            <td class="table-desktop-only">{{ $student->email ?? 'N/A' }}</td>
                            <td class="table-desktop-only">{{ $student->phone ?? 'N/A' }}</td>
                            <td class="table-desktop-only">{{ $student->course ?? 'N/A' }}</td>
                            <td class="table-desktop-only">{{ $student->room ? $student->room->block->name : 'N/A' }}</td>
                            <td class="table-desktop-only">{{ $student->room ? $student->room->name : 'N/A' }}</td>
                            <td class="table-desktop-only">{{ $student->bed ? $student->bed->name : 'N/A' }}</td>
                            <td class="table-desktop-only">
                                @php
                                    $totalPaid = \App\Models\Payment::where('student_id', $student->id)
                                        ->where('status', 'completed')
                                        ->sum('amount');
                                @endphp
                                <strong class="text-success">Tsh {{ number_format($totalPaid, 2) }}</strong>
                            </td>
                            <td>
                                @if($student->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($student->status === 'inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                @elseif($student->status === 'removed')
                                    <span class="badge bg-danger">Removed</span>
                                @else
                                    <span class="badge bg-info">Graduated</span>
                                @endif
                            </td>
                            <td class="table-desktop-only">{{ $student->check_in_date ? $student->check_in_date->format('Y-m-d') : 'N/A' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-student" data-student-id="{{ $student->id }}" title="View More">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-student d-none d-md-inline-block" data-student-id="{{ $student->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($student->status === 'active')
                                    <button class="btn btn-sm btn-danger remove-student d-none d-md-inline-block" data-student-id="{{ $student->id }}" title="Remove Student">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center">No students registered yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Register Student Modal - Shows Rooms -->
<div class="modal fade" id="registerStudentModal" tabindex="-1" aria-labelledby="registerStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="registerStudentModalLabel">
                    <i class="bi bi-door-open me-2"></i>Select Room to Register Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                @foreach($blocks as $block)
                    <div class="mb-4">
                        <h5 class="mb-3" style="color: #1e3c72;">
                            <i class="bi bi-building me-2"></i>{{ $block->name }}
                            <span class="badge bg-secondary">{{ $block->rooms->count() }} Rooms</span>
                        </h5>
                        <div class="row g-3">
                            @forelse($block->rooms as $room)
                                @php
                                    $freeBeds = $room->beds->where('status', 'free')->count();
                                    $totalBeds = $room->beds->count();
                                    $occupiedBeds = $room->beds->where('status', 'occupied')->count();
                                    $pendingBeds = $room->beds->where('status', 'pending_payment')->count();
                                    
                                    if ($room->has_beds) {
                                        if ($freeBeds > 0) {
                                            $roomStatus = 'available';
                                            $statusClass = 'success';
                                        } elseif ($occupiedBeds > 0 || $pendingBeds > 0) {
                                            $roomStatus = 'occupied';
                                            $statusClass = 'danger';
                                        } else {
                                            $roomStatus = 'empty';
                                            $statusClass = 'secondary';
                                        }
                                    } else {
                                        $hasStudent = $room->students->where('status', 'active')->whereNull('check_out_date')->count() > 0;
                                        $roomStatus = $hasStudent ? 'occupied' : 'available';
                                        $statusClass = $hasStudent ? 'danger' : 'success';
                                    }
                                @endphp
                                <div class="col-md-4 col-lg-3">
                                    <div class="card room-card h-100" 
                                         data-room-id="{{ $room->id }}"
                                         data-has-beds="{{ $room->has_beds ? '1' : '0' }}"
                                         data-status="{{ $roomStatus }}"
                                         style="cursor: pointer; transition: transform 0.2s;"
                                         onmouseover="this.style.transform='scale(1.05)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0 fw-bold">{{ $room->name }}</h6>
                                                <span class="badge bg-{{ $statusClass }}">
                                                    @if($roomStatus === 'available')
                                                        Available
                                                    @elseif($roomStatus === 'occupied')
                                                        Occupied
                                                    @else
                                                        Empty
                                                    @endif
                                                </span>
                                            </div>
                                            @if($room->location)
                                                <p class="text-muted small mb-2">
                                                    <i class="bi bi-geo-alt"></i> {{ $room->location }}
                                                </p>
                                            @endif
                                            @if($room->has_beds)
                                                <div class="small">
                                                    <i class="bi bi-bed"></i> 
                                                    <strong>{{ $freeBeds }}</strong> free / 
                                                    <strong>{{ $totalBeds }}</strong> total
                                                    @if($pendingBeds > 0)
                                                        <br><span class="text-warning"><i class="bi bi-clock"></i> {{ $pendingBeds }} pending</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="small">
                                                    <i class="bi bi-door-open"></i> Room rental
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted">No rooms in this block.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Form Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="addStudentModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Register Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStudentForm">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Room:</strong> <span id="selectedRoomName">-</span> | 
                        <strong>Block:</strong> <span id="selectedBlockName">-</span>
                    </div>
                    
                    <input type="hidden" id="selectedRoomId" name="room_id">
                    
                    <div class="mb-3" id="bedSelectionContainer" style="display: none;">
                        <h6 class="mb-3"><i class="bi bi-bed me-2"></i>Select Bed</h6>
                        <label for="studentBed" class="form-label">Select Bed <span class="text-danger">*</span></label>
                        <select class="form-select" id="studentBed" name="bed_id">
                            <option value="">Select Bed</option>
                        </select>
                        <div id="bedInfo" class="mt-2"></div>
                        <div class="alert alert-warning mt-2" id="bedSelectionNote" style="display: none;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <small>Please select a free bed to continue with student registration.</small>
                        </div>
                    </div>
                    
                    <div class="student-details-section" style="display: none;">
                        <hr class="my-3">
                        <h6 class="mb-3"><i class="bi bi-person me-2"></i>Student Details</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="studentNumber" class="form-label">Student Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="studentNumber" name="student_number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullName" name="full_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="255612345678" pattern="^255\d{9}$" required maxlength="12" autocomplete="tel">
                                <small class="text-muted">
                                    <span id="phoneHint">Phone number must start with 255 followed by 9 digits (e.g., 255612345678)</span>
                                    <span id="phoneLength" class="float-end text-muted"></span>
                                </small>
                                <div class="invalid-feedback" id="phoneError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nationalId" class="form-label">National ID</label>
                                <input type="text" class="form-control" id="nationalId" name="national_id">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="checkInDate" class="form-label">Check-in Date</label>
                                <input type="date" class="form-control" id="checkInDate" name="check_in_date">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="course" class="form-label">Course</label>
                                <input type="text" class="form-control" id="course" name="course">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="yearOfStudy" class="form-label">Year of Study</label>
                                <input type="text" class="form-control" id="yearOfStudy" name="year_of_study">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitStudentBtn"
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="submitStudentSpinner" role="status"></span>
                        <span id="submitStudentText">Register Student</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Student Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="viewStudentModalLabel">
                    <i class="bi bi-eye me-2"></i>Student Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Student Number:</strong>
                        <p id="viewStudentNumber">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Full Name:</strong>
                        <p id="viewFullName">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong>
                        <p id="viewEmail">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone:</strong>
                        <p id="viewPhone">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>National ID:</strong>
                        <p id="viewNationalId">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Course:</strong>
                        <p id="viewCourse">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Year of Study:</strong>
                        <p id="viewYearOfStudy">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong>
                        <p id="viewStatus">-</p>
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
                        <p id="viewTotalPaid" class="text-success fw-bold">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Check-in Date:</strong>
                        <p id="viewCheckInDate">-</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Check-out Date:</strong>
                        <p id="viewCheckOutDate">-</p>
                    </div>
                    <div class="col-12 mb-3">
                        <strong>Notes:</strong>
                        <p id="viewNotes">-</p>
                    </div>
                    <div class="col-12 mb-3">
                        <hr>
                        <h6 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Payment History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="studentPaymentsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Reserve Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody id="studentPaymentsBody">
                                    <tr>
                                        <td colspan="6" class="text-center">Loading payments...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="editStudentModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStudentForm">
                <input type="hidden" id="editStudentId" name="student_id">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editStudentNumber" class="form-label">Student Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editStudentNumber" name="student_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editFullName" name="full_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editPhone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editPhone" name="phone" placeholder="255612345678" pattern="^255\d{9}$" required maxlength="12">
                            <small class="text-muted">
                                <span id="editPhoneHint">Phone number must start with 255 followed by 9 digits (e.g., 255612345678)</span>
                                <span id="editPhoneLength" class="float-end text-muted"></span>
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editNationalId" class="form-label">National ID</label>
                            <input type="text" class="form-control" id="editNationalId" name="national_id">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editStatus" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="editStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="graduated">Graduated</option>
                                <option value="removed">Removed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCourse" class="form-label">Course</label>
                            <input type="text" class="form-control" id="editCourse" name="course">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editYearOfStudy" class="form-label">Year of Study</label>
                            <input type="text" class="form-control" id="editYearOfStudy" name="year_of_study">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCheckInDate" class="form-label">Check-in Date</label>
                            <input type="date" class="form-control" id="editCheckInDate" name="check_in_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCheckOutDate" class="form-label">Check-out Date</label>
                            <input type="date" class="form-control" id="editCheckOutDate" name="check_out_date">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="editNotes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateStudentBtn"
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="updateStudentSpinner" role="status"></span>
                        <span id="updateStudentText">Update Student</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTables - only if we have data rows
        setTimeout(function() {
            if ($('#studentsTable').length) {
                const table = $('#studentsTable');
                const tbody = table.find('tbody');
                const rows = tbody.find('tr');
                
                // Check if we have actual data rows (not just empty placeholder with colspan)
                const hasData = rows.length > 0 && !rows.first().find('td[colspan]').length;
                
                if (hasData) {
                    try {
                        // Destroy existing DataTable instance if it exists
                        if ($.fn.DataTable.isDataTable('#studentsTable')) {
                            table.DataTable().destroy();
                        }
                        
                        // Initialize DataTable
                        table.DataTable({
                            pageLength: 5,
                            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                            order: [[0, 'desc']],
                            responsive: true,
                            columnDefs: [
                                { orderable: false, targets: -1 } // Disable sorting on Actions column
                            ]
                        });
                    } catch (e) {
                        console.error('Error initializing DataTable:', e);
                    }
                }
            }
        }, 100);

        // Room card click handler
        $(document).on('click', '.room-card', function() {
            console.log('Room card clicked');
            const roomId = $(this).data('room-id');
            const hasBeds = $(this).data('has-beds') == '1';
            const status = $(this).data('status');
            const roomName = $(this).find('h6').text().trim();
            const blockName = $(this).closest('.mb-4').find('h5').text().replace(/\d+ Rooms/g, '').replace('Rooms', '').trim();
            
            console.log('Room ID:', roomId, 'Has Beds:', hasBeds, 'Status:', status);
            
            // Don't allow selection of occupied rooms
            if (status === 'occupied') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chumba Tayari Kimejaa',
                    text: 'Chumba hiki tayari kimejaa. Tafadhali chagua chumba kingine.',
                    confirmButtonText: 'Sawa',
                    confirmButtonColor: '#1e3c72'
                });
                return;
            }
            
            // Set room info
            $('#selectedRoomId').val(roomId);
            $('#selectedRoomName').text(roomName);
            $('#selectedBlockName').text(blockName);
            
            // Close register modal
            $('#registerStudentModal').modal('hide');
            
            // Reset form
            $('#addStudentForm')[0].reset();
            $('.student-details-section').hide();
            $('#bedSelectionContainer').hide();
            $('#studentBed').html('<option value="">Select Bed</option>');
            $('#bedInfo').html('');
            
            // Load beds if room has beds
            if (hasBeds) {
                console.log('Room has beds, loading beds...');
                // Show bed selection container first
                $('#bedSelectionContainer').show();
                $('#studentBed').prop('required', true);
                // Load beds
                loadBedsForRoom(roomId);
            } else {
                console.log('Room has no beds, showing student details directly');
                // Room without beds - show student details directly
                $('#bedSelectionContainer').hide();
                $('#studentBed').prop('required', false);
                $('.student-details-section').show();
            }
            
            // Show add student modal after a short delay to ensure modal is ready
            setTimeout(function() {
                $('#addStudentModal').modal('show');
            }, 300);
        });

        // Load beds for selected room
        function loadBedsForRoom(roomId) {
            console.log('Loading beds for room:', roomId);
            $.ajax({
                url: `/rooms/${roomId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Beds response:', response);
                    const freeBeds = response.beds ? response.beds.filter(bed => bed.status === 'free') : [];
                    const bedSelect = $('#studentBed');
                    const bedInfo = $('#bedInfo');
                    const bedContainer = $('#bedSelectionContainer');
                    const bedNote = $('#bedSelectionNote');
                    const studentDetailsSection = $('.student-details-section');
                    
                    console.log('Free beds:', freeBeds);
                    
                    if (freeBeds.length > 0) {
                        bedSelect.html('<option value="">Select a free bed</option>');
                        freeBeds.forEach(function(bed) {
                            const price = bed.rent_price ? `Tsh ${parseFloat(bed.rent_price).toLocaleString()}` : 'Price not set';
                            bedSelect.append(`<option value="${bed.id}">${bed.name} - ${price} (${bed.rent_duration || 'N/A'})</option>`);
                        });
                        bedInfo.html(`<small class="text-success"><i class="bi bi-check-circle"></i> ${freeBeds.length} free bed(s) available</small>`);
                        bedNote.hide();
                        bedContainer.show();
                        bedSelect.prop('required', true);
                        
                        // Hide student details until bed is selected
                        studentDetailsSection.hide();
                    } else {
                        bedSelect.html('<option value="">No free beds available</option>');
                        bedInfo.html('<small class="text-danger"><i class="bi bi-x-circle"></i> No free beds in this room. All beds are either occupied or have pending payment.</small>');
                        bedNote.show();
                        bedContainer.show();
                        bedSelect.prop('required', true);
                        studentDetailsSection.hide();
                    }
                },
                error: function(xhr) {
                    console.error('Error loading beds:', xhr);
                    console.error('Response:', xhr.responseText);
                    $('#bedSelectionContainer').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Hitilafu',
                        text: 'Kuna hitilafu wakati wa kupakia vitanda. Tafadhali jaribu tena.',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#1e3c72'
                    });
                }
            });
        }

        // Show student details when bed is selected
        $(document).on('change', '#studentBed', function() {
            const selectedBed = $(this).val();
            const studentDetailsSection = $('.student-details-section');
            
            if (selectedBed && selectedBed !== '') {
                studentDetailsSection.slideDown(function() {
                    // Initialize phone validation when section is shown
                    const phoneInput = $('#phone');
                    if (phoneInput.length) {
                        // Trigger validation on first show
                        phoneInput.trigger('input');
                    }
                });
            } else {
                studentDetailsSection.slideUp();
            }
        });

        // Handle add student form submission
        $('#addStudentForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this)[0];
            const formErrors = $('#formErrors');
            const phoneInput = $('#phone');
            const phoneValue = phoneInput.val().trim();
            
            // Clear previous errors
            formErrors.addClass('d-none').html('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            
            // Client-side validation
            let hasErrors = false;
            let errorMessages = [];
            
            // Validate phone number
            if (!phoneValue) {
                phoneInput.addClass('is-invalid');
                phoneInput.after('<div class="invalid-feedback">Phone number is required.</div>');
                hasErrors = true;
                errorMessages.push('Phone number is required.');
            } else if (!/^255\d{9}$/.test(phoneValue)) {
                phoneInput.addClass('is-invalid');
                phoneInput.after('<div class="invalid-feedback">Phone number must start with 255 followed by 9 digits (e.g., 255612345678).</div>');
                hasErrors = true;
                errorMessages.push('Phone number must start with 255 followed by 9 digits (e.g., 255612345678).');
            }
            
            // Check HTML5 validation
            if (!form.checkValidity()) {
                form.reportValidity();
                hasErrors = true;
            }
            
            // If there are client-side errors, stop submission
            if (hasErrors) {
                if (errorMessages.length > 0) {
                    let errors = '<ul class="mb-0">';
                    errorMessages.forEach(function(msg) {
                        errors += '<li>' + msg + '</li>';
                    });
                    errors += '</ul>';
                    formErrors.removeClass('d-none').html(errors);
                }
                return false;
            }
            
            const formData = $(this).serialize();
            const submitBtn = $('#submitStudentBtn');
            const submitSpinner = $('#submitStudentSpinner');
            const submitText = $('#submitStudentText');
            
            submitBtn.prop('disabled', true);
            submitSpinner.removeClass('d-none');
            submitText.text('Registering...');

            $.ajax({
                url: '{{ route("students.store") }}',
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
                    
                    $('#addStudentModal').modal('hide');
                    $('#addStudentForm')[0].reset();
                    $('#bedSelectionContainer').hide();
                    $('.student-details-section').hide();
                    $('#bedInfo').html('');
                    $('#studentBed').val('').trigger('change');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false);
                    submitSpinner.addClass('d-none');
                    submitText.text('Register Student');
                    
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

        // Real-time phone validation function
        function validatePhoneNumber(phoneInput) {
            const phoneValue = phoneInput.val().trim();
            const inputId = phoneInput.attr('id');
            const lengthSpan = inputId === 'phone' ? $('#phoneLength') : $('#editPhoneLength');
            
            // Update character count
            if (lengthSpan.length) {
                const length = phoneValue.length;
                const maxLength = 12;
                if (length > 0) {
                    lengthSpan.text(`${length}/${maxLength}`);
                    if (length === maxLength && /^255\d{9}$/.test(phoneValue)) {
                        lengthSpan.removeClass('text-muted text-danger').addClass('text-success');
                    } else if (length > maxLength) {
                        lengthSpan.removeClass('text-muted text-success').addClass('text-danger');
                    } else {
                        lengthSpan.removeClass('text-success text-danger').addClass('text-muted');
                    }
                } else {
                    lengthSpan.text('');
                }
            }
            
            // Remove previous validation classes and messages
            phoneInput.removeClass('is-invalid is-valid');
            phoneInput.siblings('.invalid-feedback').remove();
            phoneInput.next('.invalid-feedback').remove();
            
            if (!phoneValue) {
                phoneInput.addClass('is-invalid');
                phoneInput.after('<div class="invalid-feedback">Phone number is required.</div>');
                return false;
            } else if (phoneValue.length < 12) {
                phoneInput.addClass('is-invalid');
                phoneInput.after('<div class="invalid-feedback">Phone number must be 12 digits (255 + 9 digits).</div>');
                return false;
            } else if (!/^255\d{9}$/.test(phoneValue)) {
                phoneInput.addClass('is-invalid');
                if (!phoneValue.startsWith('255')) {
                    phoneInput.after('<div class="invalid-feedback">Phone number must start with 255.</div>');
                } else {
                    phoneInput.after('<div class="invalid-feedback">Phone number must have exactly 9 digits after 255 (e.g., 255612345678).</div>');
                }
                return false;
            } else {
                phoneInput.addClass('is-valid');
                return true;
            }
        }

        // Real-time phone validation for register form (using event delegation)
        $(document).on('input keyup paste blur', '#phone', function(e) {
            // Allow only numbers
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length > 12) {
                value = value.substring(0, 12);
            }
            $(this).val(value);
            validatePhoneNumber($(this));
        });

        // Real-time phone validation for edit form (using event delegation)
        $(document).on('input keyup paste blur', '#editPhone', function(e) {
            // Allow only numbers
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length > 12) {
                value = value.substring(0, 12);
            }
            $(this).val(value);
            validatePhoneNumber($(this));
        });

        // Initialize validation when modals are shown
        $('#addStudentModal').on('shown.bs.modal', function() {
            // Attach validation to phone input
            const phoneInput = $('#phone');
            if (phoneInput.length) {
                // Validate phone if it has value
                if (phoneInput.val()) {
                    validatePhoneNumber(phoneInput);
                }
                // Focus on phone input to trigger validation
                phoneInput.on('focus', function() {
                    validatePhoneNumber($(this));
                });
            }
        });

        $('#editStudentModal').on('shown.bs.modal', function() {
            // Attach validation to phone input
            const phoneInput = $('#editPhone');
            if (phoneInput.length) {
                // Validate phone if it has value
                if (phoneInput.val()) {
                    validatePhoneNumber(phoneInput);
                }
                // Focus on phone input to trigger validation
                phoneInput.on('focus', function() {
                    validatePhoneNumber($(this));
                });
            }
        });

        // Ensure validation works when student details section is shown
        // Use setInterval to check for visibility (fallback for older browsers)
        setInterval(function() {
            const phoneInput = $('#phone');
            if (phoneInput.length && phoneInput.is(':visible') && !phoneInput.data('validation-attached')) {
                phoneInput.data('validation-attached', true);
                // Ensure validation is attached
                phoneInput.off('input keyup paste blur').on('input keyup paste blur', function(e) {
                    let value = $(this).val().replace(/[^0-9]/g, '');
                    if (value.length > 12) {
                        value = value.substring(0, 12);
                    }
                    $(this).val(value);
                    validatePhoneNumber($(this));
                });
            }
        }, 500);

        // View student
        $(document).on('click', '.view-student', function() {
            const studentId = $(this).data('student-id');
            $.ajax({
                url: `/students/${studentId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#viewStudentNumber').text(response.student_number);
                    $('#viewFullName').text(response.full_name);
                    $('#viewEmail').text(response.email || 'N/A');
                    $('#viewPhone').text(response.phone || 'N/A');
                    $('#viewNationalId').text(response.national_id || 'N/A');
                    $('#viewCourse').text(response.course || 'N/A');
                    $('#viewYearOfStudy').text(response.year_of_study || 'N/A');
                    $('#viewStatus').html(response.status === 'active' ? '<span class="badge bg-success">Active</span>' : 
                                         response.status === 'inactive' ? '<span class="badge bg-secondary">Inactive</span>' : 
                                         '<span class="badge bg-info">Graduated</span>');
                    $('#viewBlock').text(response.room ? response.room.block.name : 'N/A');
                    $('#viewRoom').text(response.room ? response.room.name : 'N/A');
                    $('#viewBed').text(response.bed ? response.bed.name : 'N/A');
                    $('#viewCheckInDate').text(response.check_in_date ? new Date(response.check_in_date).toLocaleDateString() : 'N/A');
                    $('#viewCheckOutDate').text(response.check_out_date ? new Date(response.check_out_date).toLocaleDateString() : 'N/A');
                    $('#viewNotes').text(response.notes || 'N/A');
                    
                    // Load payment details
                    $.ajax({
                        url: `/students/${studentId}/payment-details`,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(paymentResponse) {
                            if (paymentResponse.success) {
                                $('#viewTotalPaid').text('Tsh ' + parseFloat(paymentResponse.total_paid).toLocaleString());
                                
                                // Display payment history (latest first)
                                const paymentsBody = $('#studentPaymentsBody');
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
                                        
                                        const reserveAmount = payment.reserve_amount || 0;
                                        paymentsBody.append(`
                                            <tr>
                                                <td>${new Date(payment.payment_date).toLocaleDateString()}</td>
                                                <td><strong>Tsh ${parseFloat(payment.amount).toLocaleString()}</strong></td>
                                                <td><strong class="text-success">Tsh ${parseFloat(reserveAmount).toLocaleString()}</strong></td>
                                                <td>${methodBadge}</td>
                                                <td>${statusBadge}</td>
                                                <td>${payment.reference_number || 'N/A'}</td>
                                            </tr>
                                        `);
                                    });
                                } else {
                                    paymentsBody.html('<tr><td colspan="6" class="text-center">No payments recorded yet.</td></tr>');
                                }
                            } else {
                                $('#viewTotalPaid').text('Tsh 0.00');
                                $('#studentPaymentsBody').html('<tr><td colspan="6" class="text-center">No payments recorded yet.</td></tr>');
                            }
                        },
                        error: function() {
                            $('#viewTotalPaid').text('Tsh 0.00');
                            $('#studentPaymentsBody').html('<tr><td colspan="5" class="text-center">Error loading payments.</td></tr>');
                        }
                    });
                    
                    $('#viewStudentModal').modal('show');
                }
            });
        });

        // Edit student
        $(document).on('click', '.edit-student', function() {
            const studentId = $(this).data('student-id');
            $.ajax({
                url: `/students/${studentId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editStudentId').val(response.id);
                    $('#editStudentNumber').val(response.student_number);
                    $('#editFullName').val(response.full_name);
                    $('#editEmail').val(response.email || '');
                    $('#editPhone').val(response.phone || '');
                    $('#editNationalId').val(response.national_id || '');
                    $('#editCourse').val(response.course || '');
                    $('#editYearOfStudy').val(response.year_of_study || '');
                    $('#editStatus').val(response.status);
                    $('#editCheckInDate').val(response.check_in_date ? response.check_in_date.split('T')[0] : '');
                    $('#editCheckOutDate').val(response.check_out_date ? response.check_out_date.split('T')[0] : '');
                    $('#editNotes').val(response.notes || '');
                    $('#editStudentModal').modal('show');
                }
            });
        });

        // Handle edit student form submission
        $('#editStudentForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this)[0];
            const formErrors = $('#editFormErrors');
            const phoneInput = $('#editPhone');
            const phoneValue = phoneInput.val().trim();
            
            // Clear previous errors
            if (formErrors.length) {
                formErrors.addClass('d-none').html('');
            }
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            
            // Client-side validation
            let hasErrors = false;
            let errorMessages = [];
            
            // Validate phone number
            if (!phoneValue) {
                phoneInput.addClass('is-invalid');
                phoneInput.after('<div class="invalid-feedback">Phone number is required.</div>');
                hasErrors = true;
                errorMessages.push('Phone number is required.');
            } else if (!/^255\d{9}$/.test(phoneValue)) {
                phoneInput.addClass('is-invalid');
                phoneInput.after('<div class="invalid-feedback">Phone number must start with 255 followed by 9 digits (e.g., 255612345678).</div>');
                hasErrors = true;
                errorMessages.push('Phone number must start with 255 followed by 9 digits (e.g., 255612345678).');
            }
            
            // Check HTML5 validation
            if (!form.checkValidity()) {
                form.reportValidity();
                hasErrors = true;
            }
            
            // If there are client-side errors, stop submission
            if (hasErrors) {
                if (errorMessages.length > 0 && formErrors.length) {
                    let errors = '<ul class="mb-0">';
                    errorMessages.forEach(function(msg) {
                        errors += '<li>' + msg + '</li>';
                    });
                    errors += '</ul>';
                    formErrors.removeClass('d-none').html(errors);
                }
                return false;
            }
            
            const studentId = $('#editStudentId').val();
            const formData = $(this).serialize();
            
            $.ajax({
                url: `/students/${studentId}`,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editStudentModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                        errors += '</ul>';
                        $('#editFormErrors').removeClass('d-none').html(errors);
                    }
                }
            });
        });

        // Remove student
        $(document).on('click', '.remove-student', function() {
            const studentId = $(this).data('student-id');
            const button = $(this);
            
            Swal.fire({
                title: 'Thibitisha Uondoaji',
                text: 'Je, una uhakika unataka kumwondoa mwanafunzi huyu? Kitanda chake kitaachwa huru.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ndio, Onda',
                cancelButtonText: 'Ghairi',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    button.prop('disabled', true);
                    button.html('<span class="spinner-border spinner-border-sm"></span>');
                    
                    $.ajax({
                        url: `/students/${studentId}/remove`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Imefanikiwa!',
                                    text: response.message,
                                    confirmButtonText: 'Sawa',
                                    confirmButtonColor: '#1e3c72'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Hitilafu',
                                    text: response.message || 'Kuna hitilafu. Tafadhali jaribu tena.',
                                    confirmButtonText: 'Sawa',
                                    confirmButtonColor: '#1e3c72'
                                });
                                button.prop('disabled', false);
                                button.html('<i class="bi bi-person-x"></i>');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'Kuna hitilafu. Tafadhali jaribu tena.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Hitilafu',
                                text: errorMessage,
                                confirmButtonText: 'Sawa',
                                confirmButtonColor: '#1e3c72'
                            });
                            button.prop('disabled', false);
                            button.html('<i class="bi bi-person-x"></i>');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

@endsection
