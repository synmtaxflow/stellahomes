@extends('layouts.app')

@section('title', 'Rent Status Report')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-calendar-check me-2" style="color: #1e3c72;"></i>Rent Status Report
                </h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('reports.rent-status.export-pdf') }}" class="btn btn-danger" target="_blank">
                        <i class="bi bi-file-pdf me-2"></i>Export PDF
                    </a>
                    <a href="{{ route('reports.rent-status.export-excel') }}" class="btn btn-success">
                        <i class="bi bi-file-excel me-2"></i>Export Excel
                    </a>
                    <a href="{{ route('reports.payment-report') }}" class="btn btn-info" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); border: none;">
                        <i class="bi bi-file-earmark-text me-2"></i>Payment Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Status Legend:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-danger me-2" style="width: 20px; height: 20px; display: inline-block;"></span>
                            <strong>Expired</strong> - Rent has expired
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning me-2" style="width: 20px; height: 20px; display: inline-block;"></span>
                            <strong>Warning</strong> - Less than 15 days remaining
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-success me-2" style="width: 20px; height: 20px; display: inline-block;"></span>
                            <strong>Active</strong> - Rent is active
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-secondary me-2" style="width: 20px; height: 20px; display: inline-block;"></span>
                            <strong>Unknown</strong> - No payment recorded
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card shadow-sm">
        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Students Rent Status</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="rentStatusTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Block</th>
                            <th>Room</th>
                            <th>Bed</th>
                            <th>Rent Start Date</th>
                            <th>Rent End Date</th>
                            <th>Days Remaining</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentsWithStatus as $item)
                        <tr class="@if($item['status'] === 'expired') table-danger @elseif($item['status'] === 'warning') table-warning @elseif($item['status'] === 'active') table-success @else table-secondary @endif">
                            <td>{{ $item['student']->full_name }}</td>
                            <td>{{ $item['student']->room ? $item['student']->room->block->name : 'N/A' }}</td>
                            <td>{{ $item['student']->room ? $item['student']->room->name : 'N/A' }}</td>
                            <td>{{ $item['student']->bed ? $item['student']->bed->name : 'N/A' }}</td>
                            <td>
                                @if($item['rent_start_date'])
                                    {{ $item['rent_start_date']->format('j F Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($item['rent_end_date'])
                                    {{ $item['rent_end_date']->format('j F Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($item['days_remaining'] !== null)
                                    @if($item['days_remaining'] < 0)
                                        <span class="text-danger"><strong>{{ abs($item['days_remaining']) }} days overdue</strong></span>
                                    @else
                                        {{ $item['days_remaining'] }} days
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($item['status'] === 'expired')
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($item['status'] === 'warning')
                                    <span class="badge bg-warning text-dark">Warning</span>
                                @elseif($item['status'] === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($item['status'] === 'expired' && $item['student']->status === 'active')
                                    <button class="btn btn-sm btn-danger remove-student-report" 
                                            data-student-id="{{ $item['student']->id }}" 
                                            title="Remove Student">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No students found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#rentStatusTable')) {
            $('#rentStatusTable').DataTable().destroy();
        }
        
        // Initialize DataTable
        var table = $('#rentStatusTable').DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[5, 'asc']], // Sort by rent end date (column index 5)
            responsive: true,
            autoWidth: false,
            destroy: true, // Allow re-initialization
            columnDefs: [
                { orderable: false, targets: -1 } // Disable sorting on Actions column
            ],
            drawCallback: function(settings) {
                // Verify column count matches
                var api = this.api();
                var columns = api.columns().count();
                if (columns !== 9) {
                    console.warn('Column count mismatch: Expected 9, found ' + columns);
                }
            }
        });

        // Remove student from report
        $(document).on('click', '.remove-student-report', function() {
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

