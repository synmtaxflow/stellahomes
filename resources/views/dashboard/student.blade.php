@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('content')
<div class="container-fluid px-2 px-md-3 py-3" style="max-width: 100%; overflow-x: hidden;">
    <!-- Profile Section -->
    <div class="card border-0 shadow-sm mb-3" id="profile-section">
        <div class="card-body text-center py-4">
            <!-- Profile Picture -->
            <div class="position-relative d-inline-block mb-3">
                <img 
                    src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=120&background=1e3c72&color=fff' }}" 
                    alt="Profile Picture" 
                    class="rounded-circle border border-3 border-primary"
                    style="width: 100px; height: 100px; object-fit: cover;"
                    id="profilePicturePreview"
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=120&background=1e3c72&color=fff'"
                >
                <button 
                    type="button" 
                    class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0"
                    style="width: 35px; height: 35px;"
                    data-bs-toggle="modal" 
                    data-bs-target="#profilePictureModal"
                    title="Upload Profile Picture"
                >
                    <i class="bi bi-camera-fill"></i>
                </button>
            </div>
            <h4 class="mb-1">{{ $user->name }}</h4>
            <p class="text-muted small mb-0">{{ $student ? $student->student_number : 'N/A' }}</p>
        </div>
    </div>

    <!-- Reserve Amount Section (Show at top for active students) -->
    @if($student && $student->status === 'active')
    <div class="card border-0 shadow-sm mb-3 border-success" id="balance-section">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Reserve Amount (Balance Yangu)</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <h4 class="mb-0 text-success">Tsh {{ number_format($balance, 0) }}</h4>
                    <small class="text-muted">Salio lako linaloweza kutumika kwa malipo ya baadaye</small>
                </div>
                @if($totalPaid > 0)
                <div class="col-md-6">
                    <div class="text-end">
                        <small class="text-muted d-block">Jumla yaliyolipwa:</small>
                        <h5 class="mb-0 text-info">Tsh {{ number_format($totalPaid, 0) }}</h5>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($student && $student->status === 'booked' && isset($expiresAt))
        <!-- Booking Time Remaining Card -->
        <div class="card border-0 shadow-sm mb-3" id="timeRemainingCard">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-muted small">Muda Ulio Baki</h6>
                        <h4 class="mb-0" id="timeRemainingDisplay">
                            <span id="countdownText">--:--:--</span>
                        </h4>
                    </div>
                    <div class="text-end">
                        <span class="badge fs-6" id="statusBadge">Active</span>
                    </div>
                </div>
                <p class="small mb-0 mt-2" id="timeRemainingMessage"></p>
            </div>
        </div>
        <input type="hidden" id="expiresAt" value="{{ $expiresAt ? $expiresAt->toIso8601String() : '' }}">
    @endif

    @if($student && $student->status !== 'booked' && $student->room)
        <!-- Room & Block Info Card -->
        <div class="card border-0 shadow-sm mb-3" id="room-section">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-house-door-fill me-2"></i>Chumba Changu</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-building fs-3 text-primary mb-2 d-block"></i>
                            <h6 class="mb-1 small text-muted">Block</h6>
                            <h5 class="mb-0 fw-bold">{{ $student->room && $student->room->block ? $student->room->block->name : 'N/A' }}</h5>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-door-open fs-3 text-info mb-2 d-block"></i>
                            <h6 class="mb-1 small text-muted">Chumba</h6>
                            <h5 class="mb-0 fw-bold">{{ $student->room->name ?? 'N/A' }}</h5>
                        </div>
                    </div>
                    @if($student->bed)
                    <div class="col-12">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-layout-text-sidebar-reverse fs-3 text-success mb-2 d-block"></i>
                            <h6 class="mb-1 small text-muted">Kitanda</h6>
                            <h5 class="mb-0 fw-bold">{{ $student->bed->name ?? 'N/A' }}</h5>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Suggestions and Incidences Widgets Section -->
    <div class="row g-2 g-md-3 mb-3">
        <div class="col-6 col-md-6">
            <div class="card border-0 shadow-sm h-100 widget-card" style="cursor: pointer; transition: all 0.3s ease;" 
                 onclick="openSuggestionModal()" 
                 onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)';" 
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';">
                <div class="card-body text-center p-2 p-md-4" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); border-radius: 0.375rem;">
                    <div class="mb-2 mb-md-3">
                        <i class="bi bi-lightbulb-fill widget-icon" style="font-size: 2.5rem; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.2);"></i>
                    </div>
                    <h6 class="text-white mb-1 mb-md-2 fw-bold widget-title">Toa Suggestion</h6>
                    <p class="text-white-50 mb-0 small d-none d-md-block widget-desc">Share your ideas and suggestions with us</p>
                    <div class="mt-2 mt-md-3">
                        <span class="badge bg-light text-info px-2 px-md-3 py-1 py-md-2 widget-badge" style="font-size: 0.7rem;">
                            <i class="bi bi-arrow-right-circle me-1"></i><span class="d-none d-sm-inline">Click</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6">
            <div class="card border-0 shadow-sm h-100 widget-card" style="cursor: pointer; transition: all 0.3s ease;" 
                 onclick="openIncidenceModal()" 
                 onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)';" 
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';">
                <div class="card-body text-center p-2 p-md-4" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border-radius: 0.375rem;">
                    <div class="mb-2 mb-md-3">
                        <i class="bi bi-exclamation-triangle-fill widget-icon" style="font-size: 2.5rem; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.2);"></i>
                    </div>
                    <h6 class="text-white mb-1 mb-md-2 fw-bold widget-title">Ripoti Incidence</h6>
                    <p class="text-white-50 mb-0 small d-none d-md-block widget-desc">Report any issues or problems you encounter</p>
                    <div class="mt-2 mt-md-3">
                        <span class="badge bg-light text-warning px-2 px-md-3 py-1 py-md-2 widget-badge" style="font-size: 0.7rem;">
                            <i class="bi bi-arrow-right-circle me-1"></i><span class="d-none d-sm-inline">Click</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($student && $student->status === 'booked' && (!$student->room || ($student->room && !$student->room->block)))
        <!-- Booking Info (Not Paid Yet) -->
        <div class="card border-0 shadow-sm mb-3 border-warning">
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Booking Pending Payment</strong>
                    <p class="mb-0 small mt-2">Tafadhali lipa ili kuhifadhi booking yako na kupata chumba chako.</p>
                </div>
            </div>
        </div>
    @endif

    @if(!$student)
        <!-- No Student Record Found -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center py-5">
                <i class="bi bi-info-circle fs-1 text-muted d-block mb-3"></i>
                <h5 class="mb-2">Hakuna Taarifa za Booking</h5>
                <p class="text-muted small mb-0">Hujafanya booking bado. Tafadhali fanya booking kupitia tovuti yetu.</p>
            </div>
        </div>
    @endif

    @if($controlNumber)
    <!-- Control Number Section -->
    <div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid #28a745;">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Control Number Yangu</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <h5 class="mb-1 fw-bold">{{ $controlNumber->control_number }}</h5>
                                <p class="mb-0 small text-muted">Tumia Control Number hii kulipa kwenye benki au MNO yoyote</p>
                            </div>
                            <div class="text-end mt-2 mt-md-0">
                                <button class="btn btn-sm btn-outline-light" onclick="copyControlNumber('{{ $controlNumber->control_number }}')">
                                    <i class="bi bi-copy me-1"></i>Nakili
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="mb-1 small text-muted">Salio la Kuanzia</h6>
                        <h5 class="mb-0 fw-bold text-primary">Tsh {{ number_format($controlNumber->starting_balance ?? 100000, 0) }}</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="mb-1 small text-muted">Kiasi Kilicholipwa</h6>
                        <h5 class="mb-0 fw-bold text-success">Tsh {{ number_format($controlNumber->total_paid, 0) }}</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="mb-1 small text-muted">Salio</h6>
                        <h5 class="mb-0 fw-bold text-danger">Tsh {{ number_format($controlNumber->remaining_balance, 0) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payment Codes Section -->
    <div class="card border-0 shadow-sm mb-3" id="payments-section">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Kodi za Malipo Yangu</h6>
        </div>
        <div class="card-body">
            @if($payments && $payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="small">Tarehe</th>
                                <th class="small">Kiasi</th>
                                <th class="small">Kodi ya Malipo</th>
                                <th class="small">Njia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td class="small">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td class="small fw-bold">Tsh {{ number_format($payment->amount, 0) }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $payment->reference_number ?? 'N/A' }}</span>
                                </td>
                                <td class="small">
                                    <span class="badge bg-{{ $payment->payment_method === 'bank' ? 'info' : 'secondary' }}">
                                        {{ ucfirst($payment->payment_method) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                    <p class="text-muted small mb-0">Hakuna malipo yaliyorekodiwa bado.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Status Card -->
    @if($student)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Hali ya Booking</h6>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted small">Status:</span>
                @php
                    $hasPayments = $student->status === 'active' && $totalPaid > 0;
                    $statusText = $hasPayments ? 'Paid' : ucfirst($student->status);
                    $statusColor = $hasPayments ? 'success' : ($student->status === 'active' ? 'success' : ($student->status === 'booked' ? 'warning' : 'secondary'));
                @endphp
                <span class="badge bg-{{ $statusColor }} fs-6">
                    {{ $statusText }}
                </span>
            </div>
            @if($student->check_in_date)
            <div class="d-flex align-items-center justify-content-between mt-2">
                <span class="text-muted small">Tarehe ya Kuingia:</span>
                <span class="fw-bold small">{{ $student->check_in_date->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Rent End Date Section -->
    @if($student && $student->status === 'active' && $rentEndDate)
    <div class="card border-0 shadow-sm mb-3 border-primary">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-calendar-x me-2"></i>Kodi Inaisha</h6>
        </div>
        <div class="card-body">
            <h4 class="mb-0 text-primary">{{ $rentEndDate->format('d F Y') }}</h4>
            <small class="text-muted">Rent period inaisha tarehe hii</small>
        </div>
    </div>
    @endif

    <!-- My Suggestions and Incidences Section -->
    <div class="card border-0 shadow-sm mb-3" id="suggestions-section">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Suggestions na Incidences Zangu</h6>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="mySuggestionsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="suggestions-tab" data-bs-toggle="tab" data-bs-target="#suggestions-pane" type="button" role="tab">
                        <i class="bi bi-lightbulb me-1"></i>Suggestions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="incidences-tab" data-bs-toggle="tab" data-bs-target="#incidences-pane" type="button" role="tab">
                        <i class="bi bi-exclamation-triangle me-1"></i>Incidences
                    </button>
                </li>
            </ul>
            <div class="tab-content mt-3" id="mySuggestionsTabContent">
                <div class="tab-pane fade show active" id="suggestions-pane" role="tabpanel">
                    <div id="mySuggestionsList" class="list-group">
                        <div class="text-center py-3">
                            <span class="spinner-border spinner-border-sm me-2"></span>Loading...
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="incidences-pane" role="tabpanel">
                    <div id="myIncidencesList" class="list-group">
                        <div class="text-center py-3">
                            <span class="spinner-border spinner-border-sm me-2"></span>Loading...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suggestion Modal -->
<div class="modal fade" id="suggestionModal" tabindex="-1" aria-labelledby="suggestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
                <h5 class="modal-title" id="suggestionModalLabel">
                    <i class="bi bi-lightbulb-fill me-2"></i>Toa Suggestion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="suggestionForm">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-lightbulb-fill" style="font-size: 3rem; color: #17a2b8;"></i>
                        <p class="text-muted mt-2 mb-0">Share your ideas and suggestions with us</p>
                    </div>
                    <div class="mb-3">
                        <label for="suggestionSubject" class="form-label fw-bold">
                            <i class="bi bi-tag me-1"></i>Jina la Suggestion
                        </label>
                        <input type="text" class="form-control" id="suggestionSubject" name="subject" placeholder="Ingiza jina la suggestion yako" required>
                    </div>
                    <div class="mb-3">
                        <label for="suggestionMessage" class="form-label fw-bold">
                            <i class="bi bi-chat-left-text me-1"></i>Ujumbe
                        </label>
                        <textarea class="form-control" id="suggestionMessage" name="message" rows="5" placeholder="Andika suggestion yako hapa..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-info" id="submitSuggestionBtn" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="suggestionSpinner"></span>
                        <i class="bi bi-send me-1"></i>Tuma Suggestion
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incidence Modal -->
<div class="modal fade" id="incidenceModal" tabindex="-1" aria-labelledby="incidenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white;">
                <h5 class="modal-title" id="incidenceModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Ripoti Incidence
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="incidenceForm">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem; color: #ffc107;"></i>
                        <p class="text-muted mt-2 mb-0">Report any issues or problems you encounter</p>
                    </div>
                    <div class="mb-3">
                        <label for="incidenceSubject" class="form-label fw-bold">
                            <i class="bi bi-tag me-1"></i>Jina la Incidence
                        </label>
                        <input type="text" class="form-control" id="incidenceSubject" name="subject" placeholder="Ingiza jina la incidence" required>
                    </div>
                    <div class="mb-3">
                        <label for="incidenceDescription" class="form-label fw-bold">
                            <i class="bi bi-file-text me-1"></i>Maelezo
                        </label>
                        <textarea class="form-control" id="incidenceDescription" name="description" rows="4" placeholder="Eleza tatizo au incidence kwa kina..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="incidencePriority" class="form-label fw-bold">
                            <i class="bi bi-flag me-1"></i>Kipaumbele
                        </label>
                        <select class="form-select" id="incidencePriority" name="priority" required>
                            <option value="low">Chini</option>
                            <option value="medium" selected>Wastani</option>
                            <option value="high">Juu</option>
                            <option value="urgent">Dharura</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="submitIncidenceBtn" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border: none; color: white;">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="incidenceSpinner"></span>
                        <i class="bi bi-send me-1"></i>Tuma Incidence
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Profile Picture Upload Modal -->
<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePictureModalLabel">
                    <i class="bi bi-camera-fill me-2"></i>Upload Profile Picture
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="profilePictureForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img 
                            src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200&background=1e3c72&color=fff' }}" 
                            alt="Preview" 
                            class="rounded-circle border border-2 border-primary mb-3"
                            style="width: 150px; height: 150px; object-fit: cover;"
                            id="previewImage"
                            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=200&background=1e3c72&color=fff'"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Chagua Picha</label>
                        <input 
                            type="file" 
                            class="form-control" 
                            id="profile_picture" 
                            name="profile_picture" 
                            accept="image/*"
                            required
                        >
                        <div class="form-text">Picha lazima iwe chini ya 2MB. Aina zinazokubalika: JPG, PNG, GIF</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="uploadSpinner"></span>
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Copy control number to clipboard
    function copyControlNumber(controlNumber) {
        navigator.clipboard.writeText(controlNumber).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Imenakiliwa!',
                text: 'Control Number imenakiliwa kwenye clipboard',
                timer: 2000,
                showConfirmButton: false,
                confirmButtonColor: '#1e3c72'
            });
        }).catch(function(err) {
            Swal.fire({
                icon: 'error',
                title: 'Hitilafu',
                text: 'Haikuweza kunakili Control Number',
                confirmButtonColor: '#1e3c72'
            });
        });
    }

    // Preview image before upload
    const profilePictureInput = document.getElementById('profile_picture');
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Ukubwa wa Picha',
                        text: 'Picha lazima iwe chini ya 2MB!',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#1e3c72'
                    });
                    e.target.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle form submission
    const profilePictureForm = document.getElementById('profilePictureForm');
    if (profilePictureForm) {
        profilePictureForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadSpinner = document.getElementById('uploadSpinner');
            
            uploadBtn.disabled = true;
            uploadSpinner.classList.remove('d-none');
            
            fetch('{{ route("student.upload-profile-picture") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Upload failed');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update profile picture preview
                    const previewImg = document.getElementById('profilePicturePreview');
                    const modalImg = document.getElementById('previewImage');
                    if (previewImg) previewImg.src = data.profile_picture;
                    if (modalImg) modalImg.src = data.profile_picture;
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('profilePictureModal'));
                    if (modal) modal.hide();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Imefanikiwa!',
                        text: 'Picha ya wasifu imepakiwa kwa mafanikio!',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#1e3c72'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hitilafu',
                        text: data.message || 'Kushindwa kupakia picha ya wasifu. Tafadhali jaribu tena.',
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
                    text: 'Kuna hitilafu: ' + error.message,
                    confirmButtonText: 'Sawa',
                    confirmButtonColor: '#1e3c72'
                });
            })
            .finally(() => {
                uploadBtn.disabled = false;
                uploadSpinner.classList.add('d-none');
            });
        });
    }

    // Countdown Timer
    const expiresAtInput = document.getElementById('expiresAt');
    if (expiresAtInput && expiresAtInput.value) {
        const expiresAt = new Date(expiresAtInput.value);
        const countdownText = document.getElementById('countdownText');
        const statusBadge = document.getElementById('statusBadge');
        const timeRemainingCard = document.getElementById('timeRemainingCard');
        const timeRemainingMessage = document.getElementById('timeRemainingMessage');

        function updateCountdown() {
            const now = new Date();
            const diff = expiresAt - now;

            if (diff <= 0) {
                countdownText.textContent = '00:00:00';
                statusBadge.textContent = 'Expired';
                statusBadge.className = 'badge bg-danger fs-6';
                timeRemainingCard.classList.remove('border-info', 'border-warning');
                timeRemainingCard.classList.add('border-danger');
                timeRemainingMessage.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>Booking yako imeisha muda. Tafadhali lipa haraka!';
                timeRemainingMessage.className = 'text-danger small mb-0 mt-2';
                return;
            }

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            countdownText.textContent = 
                String(hours).padStart(2, '0') + ':' + 
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0');

            // Update badge and card styling
            if (hours < 2) {
                statusBadge.textContent = 'Urgent';
                statusBadge.className = 'badge bg-warning fs-6';
                timeRemainingCard.classList.remove('border-info', 'border-danger');
                timeRemainingCard.classList.add('border-warning');
                timeRemainingMessage.innerHTML = '<i class="bi bi-clock-fill me-1"></i>Muda unakwisha! Tafadhali lipa haraka.';
                timeRemainingMessage.className = 'text-warning small mb-0 mt-2';
            } else {
                statusBadge.textContent = 'Active';
                statusBadge.className = 'badge bg-info fs-6';
                timeRemainingCard.classList.remove('border-warning', 'border-danger');
                timeRemainingCard.classList.add('border-info');
                timeRemainingMessage.textContent = '';
            }
        }

        // Update immediately
        updateCountdown();
        // Update every second
        setInterval(updateCountdown, 1000);
    }

    // Function to open suggestion modal
    function openSuggestionModal() {
        const modalElement = document.getElementById('suggestionModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Suggestion modal not found!');
        }
    }
    
    // Function to open incidence modal
    function openIncidenceModal() {
        const modalElement = document.getElementById('incidenceModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Incidence modal not found!');
        }
    }
    
    // Suggestion Form Handler
    const suggestionForm = document.getElementById('suggestionForm');
    if (suggestionForm) {
        suggestionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitSuggestionBtn');
            const spinner = document.getElementById('suggestionSpinner');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Inatumwa...';
            
            fetch('{{ route("suggestions.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Imefanikiwa!',
                        text: data.message,
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#17a2b8'
                    });
                    this.reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('suggestionModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Reload suggestions list
                    if (typeof loadMySuggestionsIncidences === 'function') {
                        loadMySuggestionsIncidences();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hitilafu',
                        text: data.message || 'Kushindwa kutuma pendekezo. Tafadhali jaribu tena.',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#17a2b8'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Hitilafu',
                    text: 'Kuna hitilafu. Tafadhali jaribu tena.',
                    confirmButtonText: 'Sawa',
                    confirmButtonColor: '#17a2b8'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Incidence Form Handler
    const incidenceForm = document.getElementById('incidenceForm');
    if (incidenceForm) {
        incidenceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitIncidenceBtn');
            const spinner = document.getElementById('incidenceSpinner');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Inatumwa...';
            
            fetch('{{ route("incidences.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Imefanikiwa!',
                        text: data.message,
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#ffc107'
                    });
                    this.reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('incidenceModal'));
                    modal.hide();
                    // Reload incidences list
                    if (typeof loadMySuggestionsIncidences === 'function') {
                        loadMySuggestionsIncidences();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hitilafu',
                        text: data.message || 'Kushindwa kutuma tatizo. Tafadhali jaribu tena.',
                        confirmButtonText: 'Sawa',
                        confirmButtonColor: '#ffc107'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Hitilafu',
                    text: 'Kuna hitilafu. Tafadhali jaribu tena.',
                    confirmButtonText: 'Sawa',
                    confirmButtonColor: '#1e3c72'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Load student's suggestions and incidences
    function loadMySuggestionsIncidences() {
        fetch('{{ route("student.suggestions-incidences") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Display suggestions
            const suggestionsList = document.getElementById('mySuggestionsList');
            if (data.suggestions && data.suggestions.length > 0) {
                suggestionsList.innerHTML = data.suggestions.map(suggestion => {
                    const statusBadge = {
                        'pending': '<span class="badge bg-warning">Pending</span>',
                        'read': '<span class="badge bg-info">Read</span>',
                        'resolved': '<span class="badge bg-success">Resolved</span>'
                    }[suggestion.status] || '<span class="badge bg-secondary">' + suggestion.status + '</span>';
                    
                    const responseHtml = suggestion.response ? 
                        `<div class="mt-2 p-2 bg-light rounded">
                            <strong>Jibu:</strong> ${suggestion.response}
                        </div>` : '';
                    
                    return `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${suggestion.subject}</h6>
                                    <p class="mb-1 small">${suggestion.message}</p>
                                    ${responseHtml}
                                    <small class="text-muted">${new Date(suggestion.created_at).toLocaleDateString('sw-TZ')}</small>
                                </div>
                                <div>${statusBadge}</div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                suggestionsList.innerHTML = '<div class="text-center py-3 text-muted">Hakuna suggestions bado.</div>';
            }
            
            // Display incidences
            const incidencesList = document.getElementById('myIncidencesList');
            if (data.incidences && data.incidences.length > 0) {
                incidencesList.innerHTML = data.incidences.map(incidence => {
                    const statusBadge = {
                        'pending': '<span class="badge bg-warning">Pending</span>',
                        'in_progress': '<span class="badge bg-info">In Progress</span>',
                        'resolved': '<span class="badge bg-success">Resolved</span>',
                        'closed': '<span class="badge bg-secondary">Closed</span>'
                    }[incidence.status] || '<span class="badge bg-secondary">' + incidence.status + '</span>';
                    
                    const priorityBadge = {
                        'low': '<span class="badge bg-secondary">Chini</span>',
                        'medium': '<span class="badge bg-info">Wastani</span>',
                        'high': '<span class="badge bg-warning">Juu</span>',
                        'urgent': '<span class="badge bg-danger">Dharura</span>'
                    }[incidence.priority] || '';
                    
                    const responseHtml = incidence.response ? 
                        `<div class="mt-2 p-2 bg-light rounded">
                            <strong>Jibu:</strong> ${incidence.response}
                        </div>` : '';
                    
                    return `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${incidence.subject} ${priorityBadge}</h6>
                                    <p class="mb-1 small">${incidence.description}</p>
                                    ${responseHtml}
                                    <small class="text-muted">${new Date(incidence.created_at).toLocaleDateString('sw-TZ')}</small>
                                </div>
                                <div>${statusBadge}</div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                incidencesList.innerHTML = '<div class="text-center py-3 text-muted">Hakuna incidences bado.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading suggestions/incidences:', error);
            document.getElementById('mySuggestionsList').innerHTML = '<div class="text-center py-3 text-danger">Error loading data.</div>';
            document.getElementById('myIncidencesList').innerHTML = '<div class="text-center py-3 text-danger">Error loading data.</div>';
        });
    }
    
    // Load on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadMySuggestionsIncidences();
        
        // Reload after form submission
        const suggestionForm = document.getElementById('suggestionForm');
        if (suggestionForm) {
            const originalSubmit = suggestionForm.onsubmit;
            suggestionForm.addEventListener('submit', function() {
                setTimeout(loadMySuggestionsIncidences, 1000);
            });
        }
        
        const incidenceForm = document.getElementById('incidenceForm');
        if (incidenceForm) {
            incidenceForm.addEventListener('submit', function() {
                setTimeout(loadMySuggestionsIncidences, 1000);
            });
        }
    });
</script>
@endpush

@push('styles')
<style>
    /* Prevent horizontal scrolling */
    .container-fluid {
        max-width: 100%;
        overflow-x: hidden;
    }
    
    .card {
        max-width: 100%;
        overflow-x: hidden;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Widget styles for mobile */
    .widget-card {
        min-height: auto;
    }
    
    @media (max-width: 767.98px) {
        .widget-icon {
            font-size: 2rem !important;
        }
        
        .widget-title {
            font-size: 0.9rem !important;
        }
        
        .widget-badge {
            font-size: 0.65rem !important;
            padding: 0.25rem 0.5rem !important;
        }
        
        .widget-card .card-body {
            padding: 1rem !important;
        }
    }
    
    /* Mobile-first responsive design */
    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            width: 100%;
            max-width: 100vw;
        }
        
        .card {
            margin-bottom: 1rem;
            width: 100%;
            max-width: 100%;
        }
        
        .card-body {
            padding: 1rem;
            overflow-x: hidden;
        }
        
        .widget-card .card-body {
            padding: 0.75rem !important;
        }
        
        .widget-icon {
            font-size: 1.75rem !important;
        }
        
        .widget-title {
            font-size: 0.8rem !important;
            margin-bottom: 0.5rem !important;
        }
        
        .widget-badge {
            font-size: 0.6rem !important;
            padding: 0.2rem 0.4rem !important;
        }
        
        h4, h5, h6 {
            font-size: 0.9rem;
            word-wrap: break-word;
        }
        
        .table {
            font-size: 0.85rem;
        }
        
        img {
            max-width: 100%;
            height: auto;
        }
    }
    
    /* Ensure cards are touch-friendly on mobile */
    .card {
        transition: transform 0.2s;
    }
    
    .card:active {
        transform: scale(0.98);
    }
    
    /* Better spacing for mobile */
    @media (max-width: 768px) {
        .card-header {
            padding: 0.75rem 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }
    
    /* Fix for profile picture */
    #profilePicturePreview,
    #previewImage {
        background-color: #f8f9fa;
        max-width: 100%;
    }
</style>
@endpush
@endsection
