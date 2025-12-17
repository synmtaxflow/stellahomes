@extends('layouts.app')

@section('title', 'Rent Schedules Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-calendar-event me-2" style="color: #1e3c72;"></i>Rent Schedules Management
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal"
                        style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                    <i class="bi bi-plus-circle me-2"></i>Record Rent Schedule
                </button>
            </div>
        </div>
    </div>

    <!-- Rent Schedules Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Rent Schedules</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="schedulesTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Schedule Type</th>
                            <th>Start Date</th>
                            <th>Semester Months</th>
                            <th>Delay Days</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        <tr data-schedule-id="{{ $schedule->id }}">
                            <td>
                                @if($schedule->schedule_type === 'begin_of_semester')
                                    <span class="badge bg-primary">Begin of Semester</span>
                                @elseif($schedule->schedule_type === 'first_payment')
                                    <span class="badge bg-warning text-dark">First Payment</span>
                                @else
                                    <span class="badge bg-secondary">Custom</span>
                                @endif
                            </td>
                            <td>
                                @if($schedule->schedule_type === 'begin_of_semester' && $schedule->semester_start_date)
                                    {{ \Carbon\Carbon::parse($schedule->semester_start_date)->format('F') }} (All Years)
                                @elseif($schedule->schedule_type === 'custom' && $schedule->custom_start_date)
                                    {{ \Carbon\Carbon::parse($schedule->custom_start_date)->format('j F Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($schedule->semester_months)
                                    {{ $schedule->semester_months }} months
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($schedule->schedule_type === 'begin_of_semester' && $schedule->delay_days !== null)
                                    {{ $schedule->delay_days }} days
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($schedule->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $schedule->notes ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info view-schedule" data-schedule-id="{{ $schedule->id }}" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-schedule" data-schedule-id="{{ $schedule->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-schedule" data-schedule-id="{{ $schedule->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No rent schedules recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Rent Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="addScheduleModalLabel">
                    <i class="bi bi-calendar-event me-2"></i>Record Rent Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addScheduleForm">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> This rent schedule will apply to the entire hostel. Only one active schedule can exist at a time.
                    </div>

                    <div class="mb-3">
                        <label for="scheduleType" class="form-label">Schedule Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="scheduleType" name="schedule_type" required>
                            <option value="">Select Schedule Type</option>
                            <option value="begin_of_semester">Begin of Semester</option>
                            <option value="first_payment">First Payment (Alternative)</option>
                            <option value="custom">Custom Date</option>
                        </select>
                        <small class="text-muted">
                            <strong>Begin of Semester:</strong> Rent starts from the semester start date you define below. If more than 15 days or 1 month has passed since the semester start date when recording payment, rent will start from the payment date instead.<br>
                            <strong>First Payment:</strong> Rent starts from the first payment date. If the semester has already passed, rent starts from the payment date.<br>
                            <strong>Custom:</strong> Set a specific start date for rent
                        </small>
                    </div>

                    <div class="mb-3" id="semesterMonthContainer" style="display: none;">
                            <label for="semesterMonth" class="form-label">Semester Start Month <span class="text-danger">*</span></label>
                            <select class="form-select" id="semesterMonth" name="semester_month">
                                <option value="">Select Month</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        <small class="text-muted">Select the month when the semester starts. This will apply to all years - if the month changes, just update it here.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="semesterMonthsContainer" style="display: none;">
                            <label for="semesterMonths" class="form-label">Semester Duration (Months) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="semesterMonths" name="semester_months" min="1" placeholder="e.g., 4">
                            <small class="text-muted">How many months does the semester last?</small>
                        </div>
                        <div class="col-md-6 mb-3" id="delayDaysContainer" style="display: none;">
                            <label for="delayDays" class="form-label">Delay Days <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="delayDays" name="delay_days" min="0" placeholder="e.g., 15" value="15">
                            <small class="text-muted">If semester has started and more than this many days have passed, rent will start from payment date instead of semester start date.</small>
                        </div>
                    </div>

                    <div class="mb-3" id="customStartContainer" style="display: none;">
                        <label for="customStartDate" class="form-label">Custom Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="customStartDate" name="custom_start_date">
                    </div>

                    <div class="mb-3">
                        <label for="scheduleNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="scheduleNotes" name="notes" rows="2" placeholder="Additional notes about this schedule..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitScheduleBtn"
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="submitScheduleSpinner" role="status"></span>
                        <span id="submitScheduleText">Record Schedule</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Schedule Modal -->
<div class="modal fade" id="viewScheduleModal" tabindex="-1" aria-labelledby="viewScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="viewScheduleModalLabel">
                    <i class="bi bi-eye me-2"></i>View Rent Schedule Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewScheduleContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Rent Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="editScheduleModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Rent Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editScheduleForm">
                <input type="hidden" id="editScheduleId" name="schedule_id">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> This rent schedule will apply to the entire hostel. Only one active schedule can exist at a time.
                    </div>

                    <div class="mb-3">
                        <label for="editScheduleType" class="form-label">Schedule Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="editScheduleType" name="schedule_type" required>
                            <option value="">Select Schedule Type</option>
                            <option value="begin_of_semester">Begin of Semester</option>
                            <option value="first_payment">First Payment (Alternative)</option>
                            <option value="custom">Custom Date</option>
                        </select>
                        <small class="text-muted">
                            <strong>Begin of Semester:</strong> Rent starts from the semester start date you define below. If more than 15 days or 1 month has passed since the semester start date when recording payment, rent will start from the payment date instead.<br>
                            <strong>First Payment:</strong> Rent starts from the first payment date. If the semester has already passed, rent starts from the payment date.<br>
                            <strong>Custom:</strong> Set a specific start date for rent
                        </small>
                    </div>

                    <div class="mb-3" id="editSemesterMonthContainer" style="display: none;">
                        <label for="editSemesterMonth" class="form-label">Semester Start Month <span class="text-danger">*</span></label>
                        <select class="form-select" id="editSemesterMonth" name="semester_month">
                            <option value="">Select Month</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <small class="text-muted">Select the month when the semester starts. This will apply to all years - if the month changes, just update it here.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="editSemesterMonthsContainer" style="display: none;">
                            <label for="editSemesterMonths" class="form-label">Semester Duration (Months) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editSemesterMonths" name="semester_months" min="1" placeholder="e.g., 4">
                            <small class="text-muted">How many months does the semester last?</small>
                        </div>
                        <div class="col-md-6 mb-3" id="editDelayDaysContainer" style="display: none;">
                            <label for="editDelayDays" class="form-label">Delay Days <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editDelayDays" name="delay_days" min="0" placeholder="e.g., 15" value="15">
                            <small class="text-muted">If semester has started and more than this many days have passed, rent will start from payment date instead of semester start date.</small>
                        </div>
                    </div>

                    <div class="mb-3" id="editCustomStartContainer" style="display: none;">
                        <label for="editCustomStartDate" class="form-label">Custom Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="editCustomStartDate" name="custom_start_date">
                    </div>

                    <div class="mb-3">
                        <label for="editScheduleNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="editScheduleNotes" name="notes" rows="2" placeholder="Additional notes about this schedule..."></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editIsActive" name="is_active" value="1">
                            <label class="form-check-label" for="editIsActive">
                                Active Schedule
                            </label>
                            <small class="text-muted d-block">Only one active schedule can exist at a time. Activating this will deactivate others.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateScheduleBtn"
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="updateScheduleSpinner" role="status"></span>
                        <span id="updateScheduleText">Update Schedule</span>
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
        // Initialize DataTables only if table has data rows (not empty row)
        const table = $('#schedulesTable');
        const tbody = table.find('tbody');
        const rows = tbody.find('tr');
        const hasDataRows = rows.length > 0 && !rows.first().find('td[colspan]').length;
        
        if (table.length && hasDataRows) {
            // Destroy existing DataTable instance if it exists
            if ($.fn.DataTable.isDataTable('#schedulesTable')) {
                try {
                $('#schedulesTable').DataTable().destroy();
                } catch(e) {
                    // Ignore errors if table wasn't initialized
                }
            }
            
            try {
            $('#schedulesTable').DataTable({
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                order: [[0, 'desc']],
                responsive: true,
                columnDefs: [
                    { orderable: false, targets: -1 }
                ],
                    autoWidth: false,
                    destroy: true
            });
            } catch(e) {
                console.error('DataTables initialization error:', e);
            }
        }


        // Toggle schedule type fields
        $('#scheduleType').on('change', function() {
            const scheduleType = $(this).val();
            
            // Hide all containers first
            $('#semesterMonthContainer, #semesterMonthsContainer, #delayDaysContainer, #customStartContainer').hide();
            // Remove required attribute and clear values
            $('#semesterMonth, #semesterMonths, #delayDays, #customStartDate').removeAttr('required').val('');
            
            if (scheduleType === 'begin_of_semester') {
                // Show semester fields
                $('#semesterMonthContainer').show();
                $('#semesterMonthsContainer').show();
                $('#delayDaysContainer').show();
                // Add required attribute only when fields are visible
                $('#semesterMonth, #semesterMonths, #delayDays').attr('required', 'required');
            } else if (scheduleType === 'custom') {
                $('#customStartContainer').show();
                $('#customStartDate').attr('required', 'required');
            }
            // first_payment doesn't need additional fields
        });

        // Handle form submission
        $('#addScheduleForm').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove required from hidden fields before validation
            const scheduleType = $('#scheduleType').val();
            if (scheduleType !== 'begin_of_semester') {
                $('#semesterMonth, #semesterMonths, #delayDays').removeAttr('required');
            }
            if (scheduleType !== 'custom') {
                $('#customStartDate').removeAttr('required');
            }
            
            // Serialize form data
            const formData = $(this).serialize();
            
            const submitBtn = $('#submitScheduleBtn');
            const submitSpinner = $('#submitScheduleSpinner');
            const submitText = $('#submitScheduleText');
            const formErrors = $('#formErrors');
            
            formErrors.addClass('d-none').html('');
            submitBtn.prop('disabled', true);
            submitSpinner.removeClass('d-none');
            submitText.text('Recording...');

            $.ajax({
                url: '{{ route("rent-schedules.store") }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                    } else {
                        formErrors.removeClass('d-none').html(response.message || 'An error occurred.');
                        submitBtn.prop('disabled', false);
                        submitSpinner.addClass('d-none');
                        submitText.text('Record Schedule');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr); // Debug log
                    submitBtn.prop('disabled', false);
                    submitSpinner.addClass('d-none');
                    submitText.text('Record Schedule');
                    
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                        } else {
                            errors += '<li>Validation failed. Please check your input.</li>';
                        }
                        errors += '</ul>';
                        formErrors.removeClass('d-none').html(errors);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        formErrors.removeClass('d-none').html(xhr.responseJSON.message);
                    } else {
                        let errorMsg = 'An error occurred. Please try again.';
                        if (xhr.status === 0) {
                            errorMsg = 'Network error. Please check your connection.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server error. Please contact administrator.';
                        }
                        formErrors.removeClass('d-none').html(errorMsg);
                    }
                }
            });
            
            return false;
        });

        // View schedule details
        $(document).on('click', '.view-schedule', function() {
            const scheduleId = $(this).data('schedule-id');
            const modal = new bootstrap.Modal(document.getElementById('viewScheduleModal'));
            const content = $('#viewScheduleContent');
            
            // Show loading
            content.html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            modal.show();
            
            $.ajax({
                url: `/rent-schedules/${scheduleId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    const schedule = response;
                    let scheduleType = '';
                    if (schedule.schedule_type === 'begin_of_semester') {
                        scheduleType = '<span class="badge bg-primary">Begin of Semester</span>';
                    } else if (schedule.schedule_type === 'first_payment') {
                        scheduleType = '<span class="badge bg-warning text-dark">First Payment</span>';
                    } else {
                        scheduleType = '<span class="badge bg-secondary">Custom</span>';
                    }
                    
                    let startDate = 'N/A';
                    if (schedule.schedule_type === 'begin_of_semester' && schedule.semester_start_date) {
                        // Parse date string to avoid timezone issues (format: YYYY-MM-DD)
                        const dateParts = schedule.semester_start_date.split('-');
                        const date = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                        startDate = date.toLocaleString('en-US', { month: 'long' }) + ' (All Years)';
                    } else if (schedule.schedule_type === 'custom' && schedule.custom_start_date) {
                        // Parse date string to avoid timezone issues (format: YYYY-MM-DD)
                        const dateParts = schedule.custom_start_date.split('-');
                        const date = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                        startDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    }
                    
                    const html = `
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Schedule Type:</strong>
                                <div class="mt-1">${scheduleType}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Status:</strong>
                                <div class="mt-1">
                                    ${schedule.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Start Date:</strong>
                                <div class="mt-1">${startDate}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Semester Duration:</strong>
                                <div class="mt-1">${schedule.semester_months ? schedule.semester_months + ' months' : 'N/A'}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Delay Days:</strong>
                                <div class="mt-1">${schedule.delay_days !== null ? schedule.delay_days + ' days' : 'N/A'}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Created At:</strong>
                                <div class="mt-1">${new Date(schedule.created_at).toLocaleString()}</div>
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Notes:</strong>
                                <div class="mt-1">${schedule.notes || 'No notes available'}</div>
                            </div>
                        </div>
                    `;
                    content.html(html);
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'Error loading schedule details. Please try again.';
                    content.html(`<div class="alert alert-danger">${errorMessage}</div>`);
                }
            });
        });

        // Edit schedule - Load data
        $(document).on('click', '.edit-schedule', function() {
            const scheduleId = $(this).data('schedule-id');
            const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
            const formErrors = $('#editFormErrors');
            
            formErrors.addClass('d-none').html('');
            
            // Show loading
            $('#editScheduleForm input, #editScheduleForm select, #editScheduleForm textarea').prop('disabled', true);
            modal.show();
            
            $.ajax({
                url: `/rent-schedules/${scheduleId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    const schedule = response;
                    
                    // Set form values
                    $('#editScheduleId').val(schedule.id);
                    $('#editScheduleType').val(schedule.schedule_type);
                    $('#editScheduleNotes').val(schedule.notes || '');
                    $('#editIsActive').prop('checked', schedule.is_active);
                    
                    // Handle schedule type specific fields
                    if (schedule.schedule_type === 'begin_of_semester') {
                        if (schedule.semester_start_date) {
                            const date = new Date(schedule.semester_start_date);
                            $('#editSemesterMonth').val(date.getMonth() + 1);
                        }
                        $('#editSemesterMonths').val(schedule.semester_months || '');
                        $('#editDelayDays').val(schedule.delay_days || 15);
                        $('#editSemesterMonthContainer, #editSemesterMonthsContainer, #editDelayDaysContainer').show();
                        $('#editSemesterMonth, #editSemesterMonths, #editDelayDays').attr('required', 'required');
                    } else if (schedule.schedule_type === 'custom') {
                        if (schedule.custom_start_date) {
                            const date = new Date(schedule.custom_start_date);
                            const formattedDate = date.toISOString().split('T')[0];
                            $('#editCustomStartDate').val(formattedDate);
                        }
                        $('#editCustomStartContainer').show();
                        $('#editCustomStartDate').attr('required', 'required');
                    }
                    
                    // Enable form fields
                    $('#editScheduleForm input, #editScheduleForm select, #editScheduleForm textarea').prop('disabled', false);
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'Error loading schedule. Please try again.';
                    formErrors.removeClass('d-none').html(errorMessage);
                    $('#editScheduleForm input, #editScheduleForm select, #editScheduleForm textarea').prop('disabled', false);
                }
            });
        });

        // Toggle edit schedule type fields
        $('#editScheduleType').on('change', function() {
            const scheduleType = $(this).val();
            
            // Hide all containers first
            $('#editSemesterMonthContainer, #editSemesterMonthsContainer, #editDelayDaysContainer, #editCustomStartContainer').hide();
            // Remove required attribute and clear values
            $('#editSemesterMonth, #editSemesterMonths, #editDelayDays, #editCustomStartDate').removeAttr('required').val('');
            
            if (scheduleType === 'begin_of_semester') {
                // Show semester fields
                $('#editSemesterMonthContainer').show();
                $('#editSemesterMonthsContainer').show();
                $('#editDelayDaysContainer').show();
                // Add required attribute only when fields are visible
                $('#editSemesterMonth, #editSemesterMonths, #editDelayDays').attr('required', 'required');
            } else if (scheduleType === 'custom') {
                $('#editCustomStartContainer').show();
                $('#editCustomStartDate').attr('required', 'required');
            }
            // first_payment doesn't need additional fields
        });

        // Handle edit form submission
        $('#editScheduleForm').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const scheduleId = $('#editScheduleId').val();
            
            // Remove required from hidden fields before validation
            const scheduleType = $('#editScheduleType').val();
            if (scheduleType !== 'begin_of_semester') {
                $('#editSemesterMonth, #editSemesterMonths, #editDelayDays').removeAttr('required');
            }
            if (scheduleType !== 'custom') {
                $('#editCustomStartDate').removeAttr('required');
            }
            
            // Serialize form data and handle checkbox
            let formData = $(this).serializeArray();
            // Always include is_active value (0 if unchecked, 1 if checked)
            formData = formData.filter(item => item.name !== 'is_active');
            formData.push({
                name: 'is_active',
                value: $('#editIsActive').is(':checked') ? '1' : '0'
            });
            formData = $.param(formData);
            
            const submitBtn = $('#updateScheduleBtn');
            const submitSpinner = $('#updateScheduleSpinner');
            const submitText = $('#updateScheduleText');
            const formErrors = $('#editFormErrors');
            
            formErrors.addClass('d-none').html('');
            submitBtn.prop('disabled', true);
            submitSpinner.removeClass('d-none');
            submitText.text('Updating...');

            $.ajax({
                url: `/rent-schedules/${scheduleId}`,
                type: 'PUT',
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        formErrors.removeClass('d-none').html(response.message || 'An error occurred.');
                        submitBtn.prop('disabled', false);
                        submitSpinner.addClass('d-none');
                        submitText.text('Update Schedule');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr);
                    submitBtn.prop('disabled', false);
                    submitSpinner.addClass('d-none');
                    submitText.text('Update Schedule');
                    
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errors += '<li>' + value[0] + '</li>';
                            });
                        } else {
                            errors += '<li>Validation failed. Please check your input.</li>';
                        }
                        errors += '</ul>';
                        formErrors.removeClass('d-none').html(errors);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        formErrors.removeClass('d-none').html(xhr.responseJSON.message);
                    } else {
                        let errorMsg = 'An error occurred. Please try again.';
                        if (xhr.status === 0) {
                            errorMsg = 'Network error. Please check your connection.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server error. Please contact administrator.';
                        }
                        formErrors.removeClass('d-none').html(errorMsg);
                    }
                }
            });
            
            return false;
        });

        // Reset edit form when modal is closed
        $('#editScheduleModal').on('hidden.bs.modal', function() {
            $('#editScheduleForm')[0].reset();
            $('#editFormErrors').addClass('d-none').html('');
            $('#editSemesterMonthContainer, #editSemesterMonthsContainer, #editDelayDaysContainer, #editCustomStartContainer').hide();
            $('#editScheduleForm input, #editScheduleForm select, #editScheduleForm textarea').prop('disabled', false);
        });

        // Delete schedule
        $(document).on('click', '.delete-schedule', function() {
            const scheduleId = $(this).data('schedule-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this rent schedule? This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/rent-schedules/${scheduleId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            const errorMessage = xhr.responseJSON?.message || 'Error deleting schedule. Please try again.';
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
        });

        // Reset form when modal is closed
        $('#addScheduleModal').on('hidden.bs.modal', function() {
            $('#addScheduleForm')[0].reset();
            $('#formErrors').addClass('d-none').html('');
            $('#semesterMonthContainer, #semesterMonthsContainer, #delayDaysContainer, #customStartContainer').hide();
        });
    });
</script>
@endpush

@endsection
