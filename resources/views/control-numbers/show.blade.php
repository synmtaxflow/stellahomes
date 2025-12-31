@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-credit-card-2-front me-2"></i>Control Number Details
                </h2>
                <div>
                    <a href="{{ route('control-numbers.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Control Numbers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Control Number Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Control Number Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Control Number:</th>
                            <td><strong class="fs-5">{{ $controlNumber->control_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Student Name:</th>
                            <td>{{ $controlNumber->student->full_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Student Number:</th>
                            <td>{{ $controlNumber->student->student_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $controlNumber->student->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $controlNumber->student->email ?? 'N/A' }}</td>
                        </tr>
                        @if($controlNumber->student->room)
                        <tr>
                            <th>Room:</th>
                            <td>
                                {{ $controlNumber->student->room->block->name ?? '' }} - 
                                {{ $controlNumber->student->room->name ?? 'N/A' }}
                                @if($controlNumber->student->bed)
                                    - {{ $controlNumber->student->bed->name }}
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($controlNumber->is_fully_paid)
                                    <span class="badge bg-success">Fully Paid</span>
                                @elseif($controlNumber->is_active)
                                    <span class="badge bg-warning">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Payment Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="50%">Starting Balance:</th>
                            <td><strong class="fs-5 text-primary">Tsh {{ number_format($controlNumber->starting_balance ?? 100000, 0) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Total Paid:</th>
                            <td><strong class="fs-5 text-success">Tsh {{ number_format($controlNumber->total_paid, 0) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Remaining Balance:</th>
                            <td>
                                <strong class="fs-5 {{ $controlNumber->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                    Tsh {{ number_format($controlNumber->remaining_balance, 0) }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Progress:</th>
                            <td>
                                @php
                                    $startingBalance = $controlNumber->starting_balance ?? 100000;
                                    $percentage = $startingBalance > 0 
                                        ? ($controlNumber->total_paid / $startingBalance) * 100 
                                        : 0;
                                @endphp
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar {{ $percentage >= 100 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ min($percentage, 100) }}%"
                                         aria-valuenow="{{ $percentage }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($percentage, 1) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow-sm">
        <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Reference Number</th>
                            <th>Period Start</th>
                            <th>Period End</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td><strong>Tsh {{ number_format($payment->amount, 0) }}</strong></td>
                            <td>
                                <span class="badge {{ $payment->payment_method === 'cash' ? 'bg-success' : 'bg-primary' }}">
                                    {{ ucfirst($payment->payment_method) }}
                                </span>
                            </td>
                            <td>
                                {{ $payment->reference_number ?? 'N/A' }}
                                @if($payment->merchant_reference)
                                    <br><small class="text-muted">Merchant: {{ substr($payment->merchant_reference, 0, 8) }}...</small>
                                @endif
                            </td>
                            <td>{{ $payment->period_start_date ? $payment->period_start_date->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $payment->period_end_date ? $payment->period_end_date->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-success">Completed</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No transactions found for this control number.</p>
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

