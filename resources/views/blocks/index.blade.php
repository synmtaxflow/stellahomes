@extends('layouts.app')

@section('title', 'Blocks Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-building me-2" style="color: #1e3c72;"></i>Blocks Management
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerBlockModal" 
            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
        <i class="bi bi-plus-circle me-1"></i>Register Block
    </button>
</div>

<!-- Blocks Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Blocks</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive" id="blocksTableWrapper">
            <table id="blocksTable" class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Rooms</th>
                        <th class="table-desktop-only">Floors</th>
                        <th class="table-desktop-only">Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($blocks as $block)
                        <tr>
                            <td>
                                @if($block->image)
                                    <img src="{{ asset('storage/' . $block->image) }}" alt="{{ $block->name }}" 
                                         class="rounded" style="width: 50px; height: 50px; object-fit: cover;"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'50\' height=\'50\'%3E%3Crect width=\'50\' height=\'50\' fill=\'%23e0e0e0\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-size=\'10\'%3E{{ $block->name }}%3C/text%3E%3C/svg%3E';">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-building text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td><strong>{{ $block->name }}</strong></td>
                            <td>
                                <span class="badge {{ $block->type === 'flat' ? 'bg-success' : 'bg-info' }}">
                                    {{ ucfirst($block->type) }}
                                </span>
                            </td>
                            <td><span class="badge bg-secondary">{{ $block->rooms->count() }}</span></td>
                            <td class="table-desktop-only">{{ $block->floors ?? 'N/A' }}</td>
                            <td class="table-desktop-only">{{ $block->created_at->format('M d, Y') }}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-block" data-block-id="{{ $block->id }}" title="View More">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-block d-none d-md-inline-block" data-block-id="{{ $block->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-block d-none d-md-inline-block" data-block-id="{{ $block->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-center mt-2">
                <button class="btn btn-sm btn-outline-primary view-more-cols-btn" onclick="toggleViewMore('blocksTableWrapper')">
                    <i class="bi bi-arrows-expand me-1"></i><span class="view-more-text">View More</span><span class="view-less-text d-none">View Less</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Register Block Modal -->
<div class="modal fade" id="registerBlockModal" tabindex="-1" aria-labelledby="registerBlockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="registerBlockModalLabel">
                    <i class="bi bi-building me-2"></i>Register New Block
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="registerBlockForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                        <label for="blockName" class="form-label">Block Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="blockName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="blockImage" class="form-label">Block Image (Optional)</label>
                        <input type="file" class="form-control" id="blockImage" name="image" accept="image/*">
                        <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)</small>
                    </div>

                    <div class="mb-3">
                        <label for="blockType" class="form-label">Block Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="blockType" name="type" required>
                            <option value="">Select Type</option>
                            <option value="flat">Flat (Gorofa)</option>
                            <option value="normal">Normal Building</option>
                        </select>
                    </div>

                    <div class="mb-3" id="floorsContainer" style="display: none;">
                        <label for="blockFloors" class="form-label">Number of Floors <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="blockFloors" name="floors" min="1" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" 
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                        <span id="submitText">Register Block</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Block Modal -->
<div class="modal fade" id="viewBlockModal" tabindex="-1" aria-labelledby="viewBlockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="viewBlockModalLabel">
                    <i class="bi bi-building me-2"></i>Block Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="viewBlockImage" src="" alt="Block Image" class="img-fluid rounded" style="max-height: 200px; display: none;">
                </div>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Block Name:</th>
                        <td id="viewBlockName"></td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td id="viewBlockType"></td>
                    </tr>
                    <tr>
                        <th>Floors:</th>
                        <td id="viewBlockFloors"></td>
                    </tr>
                    <tr>
                        <th>Total Rooms:</th>
                        <td id="viewBlockRooms"></td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td id="viewBlockCreated"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Block Modal -->
<div class="modal fade" id="editBlockModal" tabindex="-1" aria-labelledby="editBlockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="editBlockModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Block
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBlockForm" enctype="multipart/form-data">
                <input type="hidden" id="editBlockId" name="id">
                <div class="modal-body">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                        <label for="editBlockName" class="form-label">Block Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editBlockName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editBlockImage" class="form-label">Block Image (Optional)</label>
                        <input type="file" class="form-control" id="editBlockImage" name="image" accept="image/*">
                        <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)</small>
                    </div>

                    <div class="mb-3">
                        <label for="editBlockType" class="form-label">Block Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="editBlockType" name="type" required>
                            <option value="">Select Type</option>
                            <option value="flat">Flat (Gorofa)</option>
                            <option value="normal">Normal Building</option>
                        </select>
                    </div>

                    <div class="mb-3" id="editFloorsContainer" style="display: none;">
                        <label for="editBlockFloors" class="form-label">Number of Floors <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="editBlockFloors" name="floors" min="1" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" 
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        Update Block
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#blocksTable').DataTable({
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            order: [[1, 'asc']],
        });

        // Toggle floors field
        $('#blockType').on('change', function() {
            if ($(this).val() === 'flat') {
                $('#floorsContainer').slideDown();
                $('#blockFloors').prop('required', true);
            } else {
                $('#floorsContainer').slideUp();
                $('#blockFloors').prop('required', false);
                $('#blockFloors').val('');
            }
        });

        // Toggle floors field for edit
        $('#editBlockType').on('change', function() {
            if ($(this).val() === 'flat') {
                $('#editFloorsContainer').slideDown();
                $('#editBlockFloors').prop('required', true);
            } else {
                $('#editFloorsContainer').slideUp();
                $('#editBlockFloors').prop('required', false);
                $('#editBlockFloors').val('');
            }
        });

        // Handle form submission
        $('#registerBlockForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = $('#submitBtn');
            const submitSpinner = $('#submitSpinner');
            const submitText = $('#submitText');
            const formErrors = $('#formErrors');
            
            formErrors.addClass('d-none').html('');
            submitBtn.prop('disabled', true);
            submitSpinner.removeClass('d-none');
            submitText.text('Registering...');

            $.ajax({
                url: '{{ route("blocks.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<i class="bi bi-check-circle-fill me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('.container-fluid').prepend(alert);
                    $('#registerBlockModal').modal('hide');
                    $('#registerBlockForm')[0].reset();
                    $('#floorsContainer').hide();
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false);
                    submitSpinner.addClass('d-none');
                    submitText.text('Register Block');
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                        errors += '</ul>';
                        formErrors.removeClass('d-none').html(errors);
                    } else {
                        formErrors.removeClass('d-none').html('An error occurred. Please try again.');
                    }
                }
            });
        });

        // View block details
        $(document).on('click', '.view-block', function() {
            const blockId = $(this).data('block-id');
            $.ajax({
                url: `/blocks/${blockId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#viewBlockName').text(response.name);
                    $('#viewBlockType').text(response.type.charAt(0).toUpperCase() + response.type.slice(1));
                    $('#viewBlockFloors').text(response.floors || 'N/A');
                    $('#viewBlockRooms').text(response.rooms_count || 0);
                    $('#viewBlockCreated').text(new Date(response.created_at).toLocaleDateString());
                    if (response.image) {
                        $('#viewBlockImage').attr('src', `/storage/${response.image}`).show();
                    } else {
                        $('#viewBlockImage').hide();
                    }
                    $('#viewBlockModal').modal('show');
                }
            });
        });

        // Edit block
        $(document).on('click', '.edit-block', function() {
            const blockId = $(this).data('block-id');
            $.ajax({
                url: `/blocks/${blockId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editBlockId').val(response.id);
                    $('#editBlockName').val(response.name);
                    $('#editBlockType').val(response.type);
                    if (response.type === 'flat') {
                        $('#editFloorsContainer').show();
                        $('#editBlockFloors').val(response.floors);
                    } else {
                        $('#editFloorsContainer').hide();
                    }
                    $('#editBlockModal').modal('show');
                }
            });
        });

        // Handle edit block form submission
        $('#editBlockForm').on('submit', function(e) {
            e.preventDefault();
            const blockId = $('#editBlockId').val();
            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            
            $.ajax({
                url: `/blocks/${blockId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editBlockModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                        errors += '</ul>';
                        $('#editFormErrors').removeClass('d-none').html(errors);
                    }
                }
            });
        });

        // Delete block
        $(document).on('click', '.delete-block', function() {
            const blockId = $(this).data('block-id');
            Swal.fire({
                title: 'Thibitisha Ufutaji',
                text: 'Je, una uhakika unataka kufuta block hii? Hii pia itafuta vyumba vyote na vitanda vilivyomo kwenye block hii.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ndio, Futa',
                cancelButtonText: 'Ghairi',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/blocks/${blockId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Imefanikiwa!',
                                text: 'Block imefutwa kwa mafanikio.',
                                confirmButtonText: 'Sawa',
                                confirmButtonColor: '#1e3c72'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        });

        // Reset form when modal is closed
        $('#registerBlockModal').on('hidden.bs.modal', function() {
            $('#registerBlockForm')[0].reset();
            $('#formErrors').addClass('d-none').html('');
            $('#floorsContainer').hide();
            $('#submitBtn').prop('disabled', false);
            $('#submitSpinner').addClass('d-none');
            $('#submitText').text('Register Block');
        });
        
        // Toggle view more columns
        window.toggleViewMore = function(tableWrapperId) {
            const wrapper = document.getElementById(tableWrapperId);
            const btn = wrapper.querySelector('.view-more-cols-btn');
            const viewMoreText = btn.querySelector('.view-more-text');
            const viewLessText = btn.querySelector('.view-less-text');
            
            if (wrapper.classList.contains('show-all')) {
                wrapper.classList.remove('show-all');
                viewMoreText.classList.remove('d-none');
                viewLessText.classList.add('d-none');
            } else {
                wrapper.classList.add('show-all');
                viewMoreText.classList.add('d-none');
                viewLessText.classList.remove('d-none');
            }
        };
    });
</script>
@endpush
