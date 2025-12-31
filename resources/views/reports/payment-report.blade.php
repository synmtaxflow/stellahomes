@extends('layouts.app')

@section('title', 'Payment Report')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-file-earmark-text me-2" style="color: #1e3c72;"></i>Payment Report
                </h2>
                <a href="{{ route('reports.rent-status') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                    <i class="bi bi-calendar-check me-2"></i>Rent Status Report
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Report</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.payment-report') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="filterType" class="form-label">Filter Type</label>
                        <select class="form-select" id="filterType" name="filter_type" required>
                            <option value="month" {{ $filterType === 'month' ? 'selected' : '' }}>By Month</option>
                            <option value="year" {{ $filterType === 'year' ? 'selected' : '' }}>By Year</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="filterValue" class="form-label" id="filterValueLabel">
                            @if($filterType === 'month')
                                Select Month
                            @else
                                Select Year
                            @endif
                        </label>
                        @if($filterType === 'month')
                            <input type="month" class="form-control" id="filterValue" name="filter_value" value="{{ $filterValue }}" required>
                        @else
                            <input type="number" class="form-control" id="filterValue" name="filter_value" value="{{ $filterValue }}" min="2020" max="2100" required>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                            <i class="bi bi-search me-2"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Expected</h6>
                    <h3 class="mb-0 text-primary">Tsh {{ number_format($totalExpected, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Paid</h6>
                    <h3 class="mb-0 text-success">Tsh {{ number_format($totalPaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Pending</h6>
                    <h3 class="mb-0 text-danger">Tsh {{ number_format($totalPending, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Payment Summary -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Students Payment Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="studentsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Block</th>
                            <th>Room</th>
                            <th>Bed</th>
                            <th>Expected Amount</th>
                            <th>Paid Amount</th>
                            <th>Pending Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentsData as $data)
                        @php
                            $pending = $data['expected_amount'] - $data['paid_amount'];
                            $statusClass = $pending <= 0 ? 'success' : 'danger';
                            $statusText = $pending <= 0 ? 'Paid' : 'Pending';
                        @endphp
                        <tr>
                            <td>{{ $data['student']->full_name }}</td>
                            <td>{{ $data['student']->room ? $data['student']->room->block->name : 'N/A' }}</td>
                            <td>{{ $data['student']->room ? $data['student']->room->name : 'N/A' }}</td>
                            <td>{{ $data['student']->bed ? $data['student']->bed->name : 'N/A' }}</td>
                            <td><strong>Tsh {{ number_format($data['expected_amount'], 2) }}</strong></td>
                            <td class="text-success"><strong>Tsh {{ number_format($data['paid_amount'], 2) }}</strong></td>
                            <td class="text-{{ $statusClass }}">
                                <strong>Tsh {{ number_format(max(0, $pending), 2) }}</strong>
                            </td>
                            <td>
                                @if($pending <= 0)
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-danger">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No payments found for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payments Details -->
    <div class="card shadow-sm">
        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Payment Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="paymentsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Student</th>
                            <th>Block</th>
                            <th>Room</th>
                            <th>Bed</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Period</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('j F Y') }}</td>
                            <td>{{ $payment->student->full_name }}</td>
                            <td>{{ $payment->student->room ? $payment->student->room->block->name : 'N/A' }}</td>
                            <td>{{ $payment->student->room ? $payment->student->room->name : 'N/A' }}</td>
                            <td>{{ $payment->student->bed ? $payment->student->bed->name : 'N/A' }}</td>
                            <td><strong>Tsh {{ number_format($payment->amount, 2) }}</strong></td>
                            <td>
                                @if($payment->payment_method === 'cash')
                                    <span class="badge bg-primary">Cash</span>
                                @else
                                    <span class="badge bg-info">Bank</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->period_start_date && $payment->period_end_date)
                                    <small>
                                        {{ $payment->period_start_date->format('j M Y') }}<br>
                                        <strong>to</strong><br>
                                        {{ $payment->period_end_date->format('j M Y') }}
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No payments found for the selected period.</td>
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
        // Initialize DataTables
        $('#studentsTable').DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[0, 'asc']],
            responsive: true,
        });

        $('#paymentsTable').DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[0, 'desc']],
            responsive: true,
        });

        // Handle filter type change
        $('#filterType').on('change', function() {
            const filterType = $(this).val();
            const filterValueInput = $('#filterValue');
            const filterValueLabel = $('#filterValueLabel');

            if (filterType === 'month') {
                filterValueLabel.text('Select Month');
                filterValueInput.attr('type', 'month');
                filterValueInput.attr('min', null);
                filterValueInput.attr('max', null);
                filterValueInput.val('{{ date('Y-m') }}');
            } else {
                filterValueLabel.text('Select Year');
                filterValueInput.attr('type', 'number');
                filterValueInput.attr('min', '2020');
                filterValueInput.attr('max', '2100');
                filterValueInput.val('{{ date('Y') }}');
            }
        });
    });
</script>
@endpush

@endsection

