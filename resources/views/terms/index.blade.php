@extends('layouts.app')

@section('title', 'Terms and Conditions Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="bi bi-file-text me-2"></i>Terms and Conditions Management
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTermsModal">
                    <i class="bi bi-plus-circle me-1"></i>Create New Terms
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Active Terms Card -->
    @if($activeTerms)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>Currently Active Terms
                        <span class="badge bg-light text-dark ms-2">Version {{ $activeTerms->version }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Version:</strong> {{ $activeTerms->version }}<br>
                        <strong>Created:</strong> {{ $activeTerms->created_at->format('d/m/Y H:i') }}<br>
                        @if($activeTerms->creator)
                        <strong>Created by:</strong> {{ $activeTerms->creator->name }}
                        @endif
                    </div>
                    <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                        @php
                            $items = explode("\n", $activeTerms->content);
                            $items = array_filter(array_map('trim', $items));
                        @endphp
                        <ul class="mb-0">
                            @foreach($items as $item)
                                @if(!empty($item))
                                    <li>{{ str_replace('- ', '', $item) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- All Terms Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Terms and Conditions</h5>
                </div>
                <div class="card-body">
                    @if($terms->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>Content Preview</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($terms as $term)
                                <tr class="{{ $term->is_active ? 'table-success' : '' }}">
                                    <td><strong>{{ $term->version }}</strong></td>
                                    <td>
                                        @php
                                            $items = explode("\n", $term->content);
                                            $items = array_filter(array_map('trim', $items));
                                            $preview = '';
                                            if (!empty($items)) {
                                                $firstItem = str_replace('- ', '', $items[0]);
                                                $preview = Str::limit($firstItem, 80);
                                                if (count($items) > 1) {
                                                    $preview .= ' (+' . (count($items) - 1) . ' more)';
                                                }
                                            }
                                        @endphp
                                        <div style="max-width: 300px;">
                                            {{ $preview ?: 'No content' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($term->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $term->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $term->creator ? $term->creator->name : 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('terms.edit', $term->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('terms.toggle-active', $term->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $term->is_active ? 'warning' : 'success' }}" title="{{ $term->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi bi-{{ $term->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('terms.destroy', $term->id) }}" method="POST" class="d-inline delete-term-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-text fs-1 text-muted d-block mb-3"></i>
                        <p class="text-muted">No terms and conditions found. <a href="{{ route('terms.create') }}">Create the first one</a>.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Terms Modal -->
<div class="modal fade" id="createTermsModal" tabindex="-1" aria-labelledby="createTermsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createTermsModalLabel">
                    <i class="bi bi-file-text me-2"></i>Create New Terms and Conditions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createTermsForm">
                    @csrf
                    <div id="termsContainer">
                        <!-- First term will be added here -->
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-success" id="addAnotherTerm">
                            <i class="bi bi-plus-circle me-1"></i>Add Another Term
                        </button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Save All Terms
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .term-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background-color: #f8f9fa;
    }
    .term-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .remove-term-btn {
        background: none;
        border: none;
        color: #dc3545;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
    }
    .remove-term-btn:hover {
        color: #c82333;
    }
    .term-items-list {
        max-height: 300px;
        overflow-y: auto;
        padding: 0.5rem;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .term-item-input {
        border-radius: 4px 0 0 4px;
    }
    .remove-item-btn {
        border-radius: 0 4px 4px 0;
    }
    .add-item-btn {
        margin-top: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let termCount = 0;

    // Initialize first term when modal opens
    $('#createTermsModal').on('show.bs.modal', function() {
        termCount = 0;
        $('#termsContainer').empty();
        addTermItem();
    });

    // Add another term
    $('#addAnotherTerm').on('click', function() {
        addTermItem();
    });

    function addTermItem() {
        termCount++;
        const termHtml = `
            <div class="term-item" data-term-index="${termCount}">
                <div class="term-item-header">
                    <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Term ${termCount}</h6>
                    ${termCount > 1 ? '<button type="button" class="remove-term-btn" onclick="removeTermItem(this)"><i class="bi bi-trash"></i></button>' : ''}
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-tag me-1"></i>Version <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control term-version" 
                           name="terms[${termCount}][version]" 
                           placeholder="e.g., 1.0, 2.0, 2024.1"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-file-text me-1"></i>Terms Items <span class="text-danger">*</span>
                    </label>
                    <div class="term-items-list" id="termItems_${termCount}">
                        <div class="input-group mb-2">
                            <input type="text" 
                                   class="form-control term-item-input" 
                                   placeholder="e.g., Marufuku kuvuta sigara"
                                   data-term-index="${termCount}">
                            <button type="button" class="btn btn-outline-danger remove-item-btn" onclick="removeTermItemInput(this)" style="display: none;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-success add-item-btn" onclick="addTermItemInput(${termCount})">
                        <i class="bi bi-plus-circle me-1"></i>Add Item
                    </button>
                    <input type="hidden" class="term-content" name="terms[${termCount}][content]" required>
                    <small class="form-text text-muted d-block mt-2">Add each rule or condition as a separate item. They will be displayed as a list.</small>
                </div>
                <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input term-active" 
                           name="terms[${termCount}][is_active]" 
                           id="term_active_${termCount}">
                    <label class="form-check-label" for="term_active_${termCount}">
                        <strong>Activate these terms</strong>
                    </label>
                </div>
            </div>
        `;
        $('#termsContainer').append(termHtml);
    }

    function removeTermItem(btn) {
        $(btn).closest('.term-item').remove();
        // Renumber remaining terms
        $('.term-item').each(function(index) {
            $(this).find('h6').html(`<i class="bi bi-file-text me-2"></i>Term ${index + 1}`);
            $(this).attr('data-term-index', index + 1);
            if (index === 0) {
                $(this).find('.remove-term-btn').remove();
            }
        });
    }

    // Handle form submission
    $('#createTermsForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Validate at least one term
        if (termCount === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please add at least one term before saving.'
            });
            return;
        }

        // Collect all term items and build content as list
        let isValid = true;
        $('.term-item').each(function() {
            const version = $(this).find('.term-version').val().trim();
            const termIndex = $(this).data('term-index');
            const items = [];
            
            $(this).find(`#termItems_${termIndex} .term-item-input`).each(function() {
                const itemText = $(this).val().trim();
                if (itemText) {
                    items.push(itemText);
                }
            });
            
            if (!version || items.length === 0) {
                isValid = false;
                return false;
            }
            
            // Build content as list format
            const content = items.map(item => `- ${item}`).join('\n');
            $(this).find('.term-content').val(content);
        });

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in version and add at least one item for all terms.'
            });
            return;
        }

        // Disable submit button
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        // Get form data
        const formData = form.serialize();

        // Submit via AJAX
        $.ajax({
            url: '{{ route("terms.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#1e3c72',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Close modal
                            $('#createTermsModal').modal('hide');
                            // Reload page to show new terms
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to create terms.'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while creating terms.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Add term item input
    function addTermItemInput(termIndex) {
        const itemsList = $(`#termItems_${termIndex}`);
        const itemCount = itemsList.find('.term-item-input').length;
        
        const itemHtml = `
            <div class="input-group mb-2">
                <input type="text" 
                       class="form-control term-item-input" 
                       placeholder="e.g., Marufuku kuvuta sigara"
                       data-term-index="${termIndex}">
                <button type="button" class="btn btn-outline-danger remove-item-btn" onclick="removeTermItemInput(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        itemsList.append(itemHtml);
        
        // Show remove buttons if more than one item
        if (itemCount >= 0) {
            itemsList.find('.remove-item-btn').show();
        }
    }

    // Remove term item input
    function removeTermItemInput(btn) {
        $(btn).closest('.input-group').remove();
        
        // Hide remove buttons if only one item left
        $('.term-item').each(function() {
            const termIndex = $(this).data('term-index');
            const itemsList = $(`#termItems_${termIndex}`);
            const itemCount = itemsList.find('.term-item-input').length;
            if (itemCount <= 1) {
                itemsList.find('.remove-item-btn').hide();
            }
        });
    }

    // Clear form when modal is closed
    $('#createTermsModal').on('hidden.bs.modal', function() {
        $('#createTermsForm')[0].reset();
        $('#termsContainer').empty();
        termCount = 0;
    });
    
    // Handle delete term forms with SweetAlert
    $(document).on('submit', '.delete-term-form', function(e) {
        e.preventDefault();
        const form = $(this);
        
        Swal.fire({
            title: 'Thibitisha Ufutaji',
            text: 'Je, una uhakika unataka kufuta masharti haya?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ndio, Futa',
            cancelButtonText: 'Ghairi',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit();
            }
        });
    });
</script>
@endpush

@endsection
