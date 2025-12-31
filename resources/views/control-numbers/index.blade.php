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
                                <button type="button" class="btn btn-sm btn-info" onclick="generateHash('{{ $controlNumber->control_number }}')" title="Generate Name Lookup Hash">
                                    <i class="bi bi-key me-1"></i>Name Lookup Hash
                                </button>
                                <button type="button" class="btn btn-sm btn-success" onclick="generatePaymentHash('{{ $controlNumber->control_number }}')" title="Generate Payment Hash">
                                    <i class="bi bi-credit-card me-1"></i>Payment Hash
                                </button>
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

<!-- Hash Generation Modal (Name Lookup) -->
<div class="modal fade" id="hashModal" tabindex="-1" aria-labelledby="hashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="hashModalLabel">
                    <i class="bi bi-key me-2"></i>Generated Hash for Name Lookup (Postman)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="hashContent">
                    <div class="text-center">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Generating hash...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyHashToClipboard()">
                    <i class="bi bi-copy me-1"></i>Copy Request Body
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Hash Generation Modal -->
<div class="modal fade" id="paymentHashModal" tabindex="-1" aria-labelledby="paymentHashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentHashModalLabel">
                    <i class="bi bi-credit-card me-2"></i>Generate Payment Hash for Postman
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentHashForm">
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Payment Amount (TZS)</label>
                        <input type="number" class="form-control" id="paymentAmount" name="amount" 
                               min="1" step="0.01" required placeholder="Enter payment amount (e.g., 100000, 200000)">
                        <small class="text-muted">Enter the amount to be paid. This will be used to generate the payment hash.</small>
                    </div>
                    <div id="paymentHashContent" class="d-none">
                        <!-- Hash content will be displayed here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="submitPaymentHash()" id="generatePaymentHashBtn">
                    <i class="bi bi-key me-1"></i>Generate Hash
                </button>
                <button type="button" class="btn btn-primary d-none" id="copyPaymentHashBtn" onclick="copyPaymentHashToClipboard()">
                    <i class="bi bi-copy me-1"></i>Copy Request Body
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function generateHash(controlNumber) {
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('hashModal'));
        modal.show();
        
        // Reset content
        document.getElementById('hashContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-info" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Generating hash...</p>
            </div>
        `;
        
        // Make AJAX request
        fetch('{{ route("control-numbers.generate-hash") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                control_number: controlNumber
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayHashData(data);
            } else {
                document.getElementById('hashContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>${data.message || 'Error generating hash'}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('hashContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error: ${error.message}
                </div>
            `;
        });
    }
    
    function displayHashData(data) {
        const requestBody = JSON.stringify(data.request_body, null, 2);
        
        document.getElementById('hashContent').innerHTML = `
            <div class="mb-3">
                <h6><i class="bi bi-info-circle me-2"></i>Control Number Information</h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Control Number:</th>
                        <td><strong>${data.control_number}</strong></td>
                    </tr>
                    <tr>
                        <th>Student Name:</th>
                        <td>${data.student_name}</td>
                    </tr>
                    <tr>
                        <th>Remaining Balance:</th>
                        <td>Tsh ${parseFloat(data.remaining_balance).toLocaleString()}</td>
                    </tr>
                </table>
            </div>
            
            <div class="mb-3">
                <h6><i class="bi bi-link-45deg me-2"></i>Postman Configuration</h6>
                <div class="alert alert-info">
                    <strong>URL:</strong> <code>${data.postman_url}</code><br>
                    <strong>Method:</strong> <code>${data.postman_method}</code><br>
                    <strong>Headers:</strong> 
                    <pre class="mb-0 mt-2">${JSON.stringify(data.postman_headers, null, 2)}</pre>
                </div>
            </div>
            
            <div class="mb-3">
                <h6><i class="bi bi-code-square me-2"></i>Request Body (Copy this to Postman)</h6>
                <textarea id="requestBodyText" class="form-control font-monospace" rows="15" readonly>${requestBody}</textarea>
            </div>
            
            <div class="mb-3">
                <h6><i class="bi bi-hash me-2"></i>Generated Hash</h6>
                <div class="input-group">
                    <input type="text" id="hashValue" class="form-control font-monospace" value="${data.hash}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyHash()">
                        <i class="bi bi-copy"></i> Copy Hash
                    </button>
                </div>
            </div>
            
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Ready for Postman!</strong> Copy the request body above and paste it in Postman to test the name lookup endpoint.
            </div>
        `;
    }
    
    function copyHashToClipboard() {
        const textarea = document.getElementById('requestBodyText');
        if (textarea) {
            textarea.select();
            document.execCommand('copy');
            
            // Show success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }
    }
    
    function copyHash() {
        const hashInput = document.getElementById('hashValue');
        if (hashInput) {
            hashInput.select();
            document.execCommand('copy');
            
            // Show success message
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        }
    }
    
    // Payment Hash Functions
    let currentControlNumber = '';
    
    function generatePaymentHash(controlNumber) {
        currentControlNumber = controlNumber;
        const modal = new bootstrap.Modal(document.getElementById('paymentHashModal'));
        modal.show();
        document.getElementById('paymentHashContent').classList.add('d-none');
        document.getElementById('copyPaymentHashBtn').classList.add('d-none');
        document.getElementById('paymentAmount').value = '';
    }
    
    function submitPaymentHash() {
        const amount = document.getElementById('paymentAmount').value;
        
        if (!amount || amount <= 0) {
            alert('Please enter a valid payment amount');
            return;
        }
        
        document.getElementById('paymentHashContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Generating payment hash...</p>
            </div>
        `;
        document.getElementById('paymentHashContent').classList.remove('d-none');
        
        fetch('{{ route("control-numbers.generate-payment-hash") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                control_number: currentControlNumber,
                amount: amount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPaymentHashData(data);
                document.getElementById('copyPaymentHashBtn').classList.remove('d-none');
            } else {
                document.getElementById('paymentHashContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>${data.message || 'Error generating hash'}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('paymentHashContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error: ${error.message}
                </div>
            `;
        });
    }
    
    function displayPaymentHashData(data) {
        const requestBody = JSON.stringify(data.request_body, null, 2);
        
        document.getElementById('paymentHashContent').innerHTML = `
            <div class="mb-3">
                <h6><i class="bi bi-info-circle me-2"></i>Payment Information</h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Control Number:</th>
                        <td><strong>${data.control_number}</strong></td>
                    </tr>
                    <tr>
                        <th>Student Name:</th>
                        <td>${data.student_name}</td>
                    </tr>
                    <tr>
                        <th>Payment Amount:</th>
                        <td><strong class="text-success">Tsh ${parseFloat(data.amount).toLocaleString()}</strong></td>
                    </tr>
                    <tr>
                        <th>Remaining Balance:</th>
                        <td>Tsh ${parseFloat(data.remaining_balance).toLocaleString()}</td>
                    </tr>
                </table>
            </div>
            
            <div class="mb-3">
                <h6><i class="bi bi-link-45deg me-2"></i>Postman Configuration</h6>
                <div class="alert alert-info">
                    <strong>URL:</strong> <code>${data.postman_url}</code><br>
                    <strong>Method:</strong> <code>${data.postman_method}</code><br>
                    <strong>Headers:</strong> 
                    <pre class="mb-0 mt-2">${JSON.stringify(data.postman_headers, null, 2)}</pre>
                </div>
            </div>
            
            <div class="mb-3">
                <h6><i class="bi bi-code-square me-2"></i>Request Body (Copy this to Postman)</h6>
                <textarea id="paymentRequestBodyText" class="form-control font-monospace" rows="15" readonly>${requestBody}</textarea>
            </div>
            
            <div class="mb-3">
                <h6><i class="bi bi-hash me-2"></i>Generated Hash</h6>
                <div class="input-group">
                    <input type="text" id="paymentHashValue" class="form-control font-monospace" value="${data.hash}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyPaymentHash()">
                        <i class="bi bi-copy"></i> Copy Hash
                    </button>
                </div>
            </div>
            
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Ready for Postman!</strong> Copy the request body above and paste it in Postman to test the payment endpoint. This will record the payment and calculate rent periods automatically.
            </div>
        `;
    }
    
    function copyPaymentHashToClipboard() {
        const textarea = document.getElementById('paymentRequestBodyText');
        if (textarea) {
            textarea.select();
            document.execCommand('copy');
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }
    }
    
    function copyPaymentHash() {
        const hashInput = document.getElementById('paymentHashValue');
        if (hashInput) {
            hashInput.select();
            document.execCommand('copy');
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        }
    }
</script>
@endpush
@endsection

