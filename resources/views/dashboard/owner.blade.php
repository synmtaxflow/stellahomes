@extends('layouts.app')

@section('title', 'Owner Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>Owner Dashboard
                </h1>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                    <i class="bi bi-person-gear me-1"></i>Update Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications Section -->
    @if($newBookingsCount > 0 || $expiredBookingsCount > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-bell-fill me-2"></i>Notifications
                        @if($newBookingsCount > 0)
                            <span class="badge bg-danger ms-2">{{ $newBookingsCount }} New</span>
                        @endif
                        @if($expiredBookingsCount > 0)
                            <span class="badge bg-danger ms-2">{{ $expiredBookingsCount }} Expired</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Room/Bed</th>
                                    <th>Time Remaining</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookingsWithTime as $booking)
                                <tr class="{{ $booking['is_expired'] ? 'table-danger' : ($booking['hours_remaining'] < 2 ? 'table-warning' : '') }}">
                                    <td>
                                        <strong>{{ $booking['student']->full_name }}</strong><br>
                                        <small class="text-muted">{{ $booking['student']->phone }}</small>
                                    </td>
                                    <td>
                                        {{ $booking['room']->name ?? 'N/A' }}
                                        @if($booking['bed'])
                                            <br><small class="text-muted">Bed: {{ $booking['bed']->name }}</small>
                                        @endif
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
                                            @if($booking['is_expired'])
                                                <button type="button" class="btn btn-danger" onclick="cancelBooking({{ $booking['student']->id }})">
                                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                                </button>
                                            @endif
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
                                                    <div class="mb-3">
                                                        <label class="form-label">Amount (Tsh) *</label>
                                                        <input type="number" class="form-control" name="amount" required min="0" step="0.01">
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
                                                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('bookings.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-right me-1"></i>View All Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="bi bi-building fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Blocks</h6>
                            <h3 class="mb-0">{{ $totalBlocks }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="bi bi-people-fill fs-2 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Students</h6>
                            <h3 class="mb-0">{{ $totalStudents }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="bi bi-bed fs-2 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Empty Beds</h6>
                            <h3 class="mb-0">{{ $emptyBeds }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="bi bi-door-open fs-2 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Empty Rooms</h6>
                            <h3 class="mb-0">{{ $emptyRooms }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Payment Chart -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Rent Payments (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Bed Occupancy Chart -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Bed Occupancy</h5>
                </div>
                <div class="card-body">
                    <canvas id="bedOccupancyChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-building fs-1 text-primary mb-3"></i>
                    <h5>Manage Blocks</h5>
                    <a href="{{ route('blocks.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-1"></i>Go to Blocks
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-1 text-success mb-3"></i>
                    <h5>Manage Students</h5>
                    <a href="{{ route('students.index') }}" class="btn btn-success">
                        <i class="bi bi-arrow-right me-1"></i>Go to Students
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-file-earmark-text fs-1 text-info mb-3"></i>
                    <h5>View Reports</h5>
                    <a href="{{ route('reports.rent-status') }}" class="btn btn-info">
                        <i class="bi bi-arrow-right me-1"></i>Go to Reports
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-credit-card-2-front fs-1 text-primary mb-3"></i>
                    <h5>Control Numbers</h5>
                    <a href="{{ route('control-numbers.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-1"></i>View Control Numbers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Management Section -->
    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-file-text fs-1 text-warning mb-3"></i>
                    <h5>Terms & Conditions</h5>
                    <a href="{{ route('terms.index') }}" class="btn btn-warning">
                        <i class="bi bi-arrow-right me-1"></i>Manage Terms
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Suggestions and Incidences Section -->
    <div class="row mt-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="suggestionIncidenceTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="suggestions-tab" data-bs-toggle="tab" data-bs-target="#suggestions" type="button" role="tab">
                        <i class="bi bi-lightbulb me-2"></i>Suggestions
                        <span class="badge bg-info ms-2" id="suggestionsCount">0</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="incidences-tab" data-bs-toggle="tab" data-bs-target="#incidences" type="button" role="tab">
                        <i class="bi bi-exclamation-triangle me-2"></i>Incidences
                        <span class="badge bg-warning ms-2" id="incidencesCount">0</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="suggestionIncidenceTabContent">
                <div class="tab-pane fade show active" id="suggestions" role="tabpanel">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <div id="suggestionsList" class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tarehe</th>
                                            <th>Mwanafunzi</th>
                                            <th>Jina</th>
                                            <th>Ujumbe</th>
                                            <th>Status</th>
                                            <th>Vitendo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="suggestionsTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="incidences" role="tabpanel">
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <div id="incidencesList" class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tarehe</th>
                                            <th>Mwanafunzi</th>
                                            <th>Jina</th>
                                            <th>Maelezo</th>
                                            <th>Kipaumbele</th>
                                            <th>Status</th>
                                            <th>Vitendo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="incidencesTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    $(document).ready(function() {
        // Payment Chart
        const paymentCtx = document.getElementById('paymentChart');
        const paymentData = @json($paymentData);
        
        new Chart(paymentCtx, {
            type: 'line',
            data: {
                labels: paymentData.map(item => item.month),
                datasets: [{
                    label: 'Payments (Tsh)',
                    data: paymentData.map(item => item.amount),
                    borderColor: 'rgb(30, 60, 114)',
                    backgroundColor: 'rgba(30, 60, 114, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Tsh ' + parseFloat(context.parsed.y).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Tsh ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Bed Occupancy Chart
        const bedCtx = document.getElementById('bedOccupancyChart');
        
        new Chart(bedCtx, {
            type: 'doughnut',
            data: {
                labels: ['Occupied', 'Empty'],
                datasets: [{
                    data: [{{ $occupiedBeds }}, {{ $freeBeds }}],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = {{ $totalBeds }};
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    });

    function cancelBooking(bookingId) {
        Swal.fire({
            title: 'Thibitisha Ufutaji',
            text: 'Je, una uhakika unataka kufuta booking hii? Kitanda kitaachwa huru na akaunti ya mwanafunzi itaondolewa.',
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

    // Load Suggestions and Incidences
    function loadSuggestions() {
        fetch('{{ route("suggestions.index") }}')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('suggestionsTableBody');
                const countBadge = document.getElementById('suggestionsCount');
                
                countBadge.textContent = data.length;
                
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Hakuna suggestions bado.</td></tr>';
                    return;
                }
                
                tbody.innerHTML = data.map(suggestion => {
                    const date = new Date(suggestion.created_at);
                    const statusClass = {
                        'pending': 'warning',
                        'read': 'info',
                        'resolved': 'success'
                    }[suggestion.status] || 'secondary';
                    
                    const studentName = suggestion.student ? suggestion.student.full_name : (suggestion.user ? suggestion.user.name : 'N/A');
                    const isRead = suggestion.status !== 'pending';
                    
                    return `
                        <tr class="${!isRead ? 'table-warning' : ''}">
                            <td>${date.toLocaleDateString('en-GB')}</td>
                            <td>${studentName}</td>
                            <td>${suggestion.subject}</td>
                            <td>${suggestion.message.substring(0, 50)}${suggestion.message.length > 50 ? '...' : ''}</td>
                            <td><span class="badge bg-${statusClass}">${suggestion.status}</span></td>
                            <td>
                                ${!isRead ? `<button class="btn btn-sm btn-primary" onclick="markSuggestionRead(${suggestion.id})">
                                    <i class="bi bi-check"></i> Mark Read
                                </button>` : ''}
                                <button class="btn btn-sm btn-info" onclick="viewSuggestion(${suggestion.id})" data-bs-toggle="modal" data-bs-target="#suggestionModal">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            })
            .catch(error => {
                console.error('Error loading suggestions:', error);
                document.getElementById('suggestionsTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading suggestions.</td></tr>';
            });
    }

    function loadIncidences() {
        fetch('{{ route("incidences.index") }}')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('incidencesTableBody');
                const countBadge = document.getElementById('incidencesCount');
                
                countBadge.textContent = data.length;
                
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Hakuna incidences bado.</td></tr>';
                    return;
                }
                
                tbody.innerHTML = data.map(incidence => {
                    const date = new Date(incidence.created_at);
                    const priorityClass = {
                        'low': 'secondary',
                        'medium': 'info',
                        'high': 'warning',
                        'urgent': 'danger'
                    }[incidence.priority] || 'secondary';
                    
                    const statusClass = {
                        'pending': 'warning',
                        'in_progress': 'info',
                        'resolved': 'success',
                        'closed': 'secondary'
                    }[incidence.status] || 'secondary';
                    
                    const studentName = incidence.student ? incidence.student.full_name : (incidence.user ? incidence.user.name : 'N/A');
                    const isPending = incidence.status === 'pending';
                    
                    return `
                        <tr class="${isPending ? 'table-warning' : ''}">
                            <td>${date.toLocaleDateString('en-GB')}</td>
                            <td>${studentName}</td>
                            <td>${incidence.subject}</td>
                            <td>${incidence.description.substring(0, 50)}${incidence.description.length > 50 ? '...' : ''}</td>
                            <td><span class="badge bg-${priorityClass}">${incidence.priority}</span></td>
                            <td><span class="badge bg-${statusClass}">${incidence.status}</span></td>
                            <td>
                                ${isPending ? `<button class="btn btn-sm btn-success" onclick="markIncidenceResolved(${incidence.id})" data-bs-toggle="modal" data-bs-target="#incidenceModal">
                                    <i class="bi bi-check-circle"></i> Resolve
                                </button>` : ''}
                                <button class="btn btn-sm btn-info" onclick="viewIncidence(${incidence.id})" data-bs-toggle="modal" data-bs-target="#incidenceModal">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            })
            .catch(error => {
                console.error('Error loading incidences:', error);
                document.getElementById('incidencesTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading incidences.</td></tr>';
            });
    }

    function markSuggestionRead(id) {
        const response = prompt('Enter response to student (optional):');
        if (response === null) return; // User cancelled
        
        fetch(`/suggestions/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ response: response || '' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadSuggestions();
                if (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Imefanikiwa!',
                        text: 'Jibu limetumwa kwa mwanafunzi kupitia SMS!',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#1e3c72'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hitilafu',
                text: 'Kushindwa kuweka mapendekezo kama yamekusomwa.',
                confirmButtonText: 'Sawa',
                confirmButtonColor: '#1e3c72'
            });
        });
    }

    function markIncidenceResolved(id) {
        Swal.fire({
            title: 'Ingiza Jibu',
            text: 'Ingiza jibu (si lazima):',
            input: 'text',
            inputPlaceholder: 'Jibu...',
            showCancelButton: true,
            confirmButtonText: 'Wasilisha',
            cancelButtonText: 'Ghairi',
            confirmButtonColor: '#1e3c72',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isDismissed) return;
            const response = result.value || '';
            
            fetch(`/incidences/${id}/resolve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ response: response })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadIncidences();
                    Swal.fire({
                        icon: 'success',
                        title: 'Imefanikiwa!',
                        text: 'Tatizo limewekwa kama limetatuliwa.',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#1e3c72'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Hitilafu',
                    text: 'Kushindwa kuweka tatizo kama limetatuliwa.',
                    confirmButtonText: 'Sawa',
                    confirmButtonColor: '#1e3c72'
                });
            });
        });
    }

    function viewSuggestion(id) {
        // Load and display suggestion details in modal
        fetch('{{ route("suggestions.index") }}')
            .then(response => response.json())
            .then(data => {
                const suggestion = data.find(s => s.id === id);
                if (suggestion) {
                    document.getElementById('suggestionModalTitle').textContent = suggestion.subject;
                    document.getElementById('suggestionModalBody').innerHTML = `
                        <p><strong>From:</strong> ${suggestion.student ? suggestion.student.full_name : (suggestion.user ? suggestion.user.name : 'N/A')}</p>
                        <p><strong>Date:</strong> ${new Date(suggestion.created_at).toLocaleString()}</p>
                        <p><strong>Message:</strong></p>
                        <p>${suggestion.message}</p>
                        ${suggestion.response ? `<p><strong>Response:</strong> ${suggestion.response}</p>` : ''}
                    `;
                }
            });
    }

    function viewIncidence(id) {
        // Load and display incidence details in modal
        fetch('{{ route("incidences.index") }}')
            .then(response => response.json())
            .then(data => {
                const incidence = data.find(i => i.id === id);
                if (incidence) {
                    document.getElementById('incidenceModalTitle').textContent = incidence.subject;
                    document.getElementById('incidenceModalBody').innerHTML = `
                        <p><strong>From:</strong> ${incidence.student ? incidence.student.full_name : (incidence.user ? incidence.user.name : 'N/A')}</p>
                        <p><strong>Date:</strong> ${new Date(incidence.created_at).toLocaleString()}</p>
                        <p><strong>Priority:</strong> <span class="badge bg-${incidence.priority === 'urgent' ? 'danger' : 'warning'}">${incidence.priority}</span></p>
                        <p><strong>Description:</strong></p>
                        <p>${incidence.description}</p>
                        ${incidence.response ? `<p><strong>Response:</strong> ${incidence.response}</p>` : ''}
                    `;
                }
            });
    }

    // Load data on page load
    $(document).ready(function() {
        loadSuggestions();
        loadIncidences();
        
        // Refresh every 30 seconds
        setInterval(function() {
            loadSuggestions();
            loadIncidences();
        }, 30000);
    });
</script>
@endpush

<!-- Suggestion Modal -->
<div class="modal fade" id="suggestionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suggestionModalTitle">Suggestion Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="suggestionModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Incidence Modal -->
<div class="modal fade" id="incidenceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="incidenceModalTitle">Incidence Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="incidenceModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection
