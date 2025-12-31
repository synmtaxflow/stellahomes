@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-credit-card-2-front me-2"></i>Control Numbers
                </h2>
                <a href="{{ route('dashboard.owner') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Control Numbers Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Control Numbers</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Control Number</th>
                            <th>Student Name</th>
                            <th>Student Number</th>
                            <th>Phone</th>
                            <th>Starting Balance</th>
                            <th>Total Paid</th>
                            <th>Remaining Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($controlNumbers as $controlNumber)
                        <tr>
                            <td><strong>{{ $controlNumber->control_number }}</strong></td>
                            <td>{{ $controlNumber->student->full_name ?? 'N/A' }}</td>
                            <td>{{ $controlNumber->student->student_number ?? 'N/A' }}</td>
                            <td>{{ $controlNumber->student->phone ?? 'N/A' }}</td>
                            <td>Tsh {{ number_format($controlNumber->starting_balance ?? 100000, 0) }}</td>
                            <td>Tsh {{ number_format($controlNumber->total_paid, 0) }}</td>
                            <td>
                                <span class="badge {{ $controlNumber->remaining_balance > 0 ? 'bg-danger' : 'bg-success' }}">
                                    Tsh {{ number_format($controlNumber->remaining_balance, 0) }}
                                </span>
                            </td>
                            <td>
                                @if($controlNumber->is_fully_paid)
                                    <span class="badge bg-success">Fully Paid</span>
                                @elseif($controlNumber->is_active)
                                    <span class="badge bg-warning">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('control-numbers.show', $controlNumber->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No control numbers found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

