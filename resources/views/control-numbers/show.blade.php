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
                    <button type="button" class="btn btn-info me-2" onclick="generateNameLookupHash('{{ $controlNumber->control_number }}')">
                        <i class="bi bi-key me-1"></i>Generate Name Lookup Hash
                    </button>
                    <button type="button" class="btn btn-success me-2" onclick="generatePaymentHash('{{ $controlNumber->control_number }}')">
                        <i class="bi bi-credit-card me-1"></i>Generate Payment Hash
                    </button>
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

    <!-- API Testing Widget -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-flask me-2"></i>API Testing Widget</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <button type="button" class="btn btn-info w-100" onclick="testNameLookup()">
                        <i class="bi bi-search me-2"></i>Test Name Lookup
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-success w-100" onclick="testPayment()">
                        <i class="bi bi-credit-card me-2"></i>Test Payment
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-warning w-100" onclick="testStatusCheck()">
                        <i class="bi bi-check-circle me-2"></i>Test Status Check
                    </button>
                </div>
            </div>
            <div id="apiTestResults" class="d-none">
                <hr>
                <h6><i class="bi bi-code-square me-2"></i>API Response:</h6>
                <div class="bg-light p-3 rounded">
                    <pre id="apiResponse" class="mb-0" style="max-height: 400px; overflow-y: auto;"></pre>
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
                                @if($payment->merchant_reference)
                                    <br>
                                    <button type="button" class="btn btn-sm btn-outline-warning mt-1" 
                                            onclick="testStatusCheckForPayment('{{ $payment->merchant_reference }}')"
                                            title="Test Status Check for this payment">
                                        <i class="bi bi-check-circle"></i> Test Status
                                    </button>
                                @endif
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

<!-- Hash Generation Modal -->
<div class="modal fade" id="hashModal" tabindex="-1" aria-labelledby="hashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="hashModalLabel">
                    <i class="bi bi-key me-2"></i>Generated Hash for Postman
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

<!-- Payment Hash Modal -->
<div class="modal fade" id="paymentHashModal" tabindex="-1" aria-labelledby="paymentHashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentHashModalLabel">
                    <i class="bi bi-credit-card me-2"></i>Generate Payment Hash
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentHashForm">
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Payment Amount (TZS)</label>
                        <input type="number" class="form-control" id="paymentAmount" name="amount" 
                               min="1" step="0.01" required placeholder="Enter payment amount">
                        <small class="text-muted">Enter the amount to be paid (e.g., 100000, 200000, etc.)</small>
                    </div>
                    <div id="paymentHashContent" class="d-none">
                        <!-- Hash content will be displayed here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="submitPaymentHash()">
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
    function generateNameLookupHash(controlNumber) {
        const modal = new bootstrap.Modal(document.getElementById('hashModal'));
        modal.show();
        
        document.getElementById('hashContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-info" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Generating hash...</p>
            </div>
        `;
        
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
    
    function generatePaymentHash(controlNumber) {
        const modal = new bootstrap.Modal(document.getElementById('paymentHashModal'));
        modal.show();
        document.getElementById('paymentHashContent').classList.add('d-none');
        document.getElementById('copyPaymentHashBtn').classList.add('d-none');
        document.getElementById('paymentAmount').value = '';
    }
    
    function submitPaymentHash() {
        const controlNumber = '{{ $controlNumber->control_number }}';
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
                control_number: controlNumber,
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
                <strong>Ready for Postman!</strong> Copy the request body above and paste it in Postman to test the payment endpoint. This will record the payment and calculate rent periods.
            </div>
        `;
    }
    
    function copyHashToClipboard() {
        const textarea = document.getElementById('requestBodyText');
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
    
    function copyHash() {
        const hashInput = document.getElementById('hashValue');
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
    
    // API Testing Functions
    const controlNumber = '{{ $controlNumber->control_number }}';
    
    function showApiResults(data) {
        document.getElementById('apiTestResults').classList.remove('d-none');
        document.getElementById('apiResponse').textContent = JSON.stringify(data, null, 2);
    }
    
    async function testNameLookup() {
        try {
            // First generate hash
            const hashResponse = await fetch('{{ route("control-numbers.generate-hash") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    control_number: controlNumber
                })
            });
            
            const hashData = await hashResponse.json();
            
            if (!hashData.success) {
                showApiResults({ error: hashData.message });
                return;
            }
            
            // Now test the actual API
            const apiResponse = await fetch('{{ url("api/merchant/name-lookup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(hashData.request_body)
            });
            
            const result = await apiResponse.json();
            showApiResults({
                endpoint: 'Name Lookup',
                url: '{{ url("api/merchant/name-lookup") }}',
                request: hashData.request_body,
                response: result,
                status: apiResponse.status
            });
        } catch (error) {
            showApiResults({ error: error.message });
        }
    }
    
    async function testPayment() {
        const amount = prompt('Enter payment amount (TZS):', '100000');
        if (!amount || amount <= 0) {
            alert('Please enter a valid amount');
            return;
        }
        
        try {
            // First generate hash
            const hashResponse = await fetch('{{ route("control-numbers.generate-payment-hash") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    control_number: controlNumber,
                    amount: amount
                })
            });
            
            const hashData = await hashResponse.json();
            
            if (!hashData.success) {
                showApiResults({ error: hashData.message });
                return;
            }
            
            // Now test the actual API
            const apiResponse = await fetch('{{ url("api/merchant/payment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(hashData.request_body)
            });
            
            const result = await apiResponse.json();
            showApiResults({
                endpoint: 'Payment',
                url: '{{ url("api/merchant/payment") }}',
                request: hashData.request_body,
                response: result,
                status: apiResponse.status
            });
            
            // Reload page after 2 seconds to show new payment
            if (result.Status === 'Success') {
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        } catch (error) {
            showApiResults({ error: error.message });
        }
    }
    
    async function testStatusCheck() {
        const merchantRef = prompt('Enter Merchant Reference ID:', '');
        if (!merchantRef) {
            alert('Please enter a merchant reference ID');
            return;
        }
        
        try {
            const apiResponse = await fetch('{{ url("api/merchant/status-check") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    MerchantReferenceId: merchantRef
                })
            });
            
            const result = await apiResponse.json();
            showApiResults({
                endpoint: 'Status Check',
                url: '{{ url("api/merchant/status-check") }}',
                request: { MerchantReferenceId: merchantRef },
                response: result,
                status: apiResponse.status
            });
        } catch (error) {
            showApiResults({ error: error.message });
        }
    }
    
    async function testStatusCheckForPayment(merchantRef) {
        try {
            const apiResponse = await fetch('{{ url("api/merchant/status-check") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    MerchantReferenceId: merchantRef
                })
            });
            
            const result = await apiResponse.json();
            
            // Show result in alert
            alert('Status Check Result:\n\n' + 
                  'Merchant Reference: ' + merchantRef + '\n' +
                  'Status: ' + result.Status + '\n' +
                  'Message: ' + result.Message);
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
</script>
@endpush
@endsection

