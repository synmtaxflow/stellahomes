@extends('layouts.app')

@section('title', 'Rooms Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-door-open me-2" style="color: #1e3c72;"></i>Rooms Management
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal" 
            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
        <i class="bi bi-plus-circle me-1"></i>Add New Room
    </button>
</div>

<!-- Rooms Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Rooms</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="roomsTable" class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Room Name/Number</th>
                        <th>Block</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Price/Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="roomsTableBody">
                    @forelse($rooms as $room)
                        <tr data-room-id="{{ $room->id }}">
                            <td>
                                @if($room->image)
                                    <img src="{{ asset('storage/' . $room->image) }}" alt="{{ $room->name }}" 
                                         class="rounded" style="width: 60px; height: 60px; object-fit: cover;"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\'%3E%3Crect width=\'60\' height=\'60\' fill=\'%23e0e0e0\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-size=\'10\'%3ERoom%3C/text%3E%3C/svg%3E';">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="bi bi-door-open text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td><strong>{{ $room->name }}</strong></td>
                            <td>
                                <span class="badge bg-info">{{ $room->block->name }}</span>
                            </td>
                            <td>{{ $room->location ?? 'N/A' }}</td>
                            <td>
                                @if($room->has_beds)
                                    <span class="badge bg-success">With Beds</span>
                                @else
                                    <span class="badge bg-warning text-dark">No Beds</span>
                                @endif
                            </td>
                            <td>
                                @if($room->has_beds)
                                    <small class="text-muted">Beds: {{ $room->beds->count() }}</small>
                                @else
                                    @if($room->rent_price)
                                        <small>Tsh {{ number_format($room->rent_price, 2) }} / {{ $room->rent_duration ? ucfirst($room->rent_duration) : 'N/A' }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info view-room" data-room-id="{{ $room->id }}" title="View More">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-room" data-room-id="{{ $room->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-room" data-room-id="{{ $room->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-door-open" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">No rooms registered yet. Click "Add New Room" to add one.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Beds Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-bed me-2"></i>Beds in Rooms</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="bedsTable" class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Bed Name/Number</th>
                        <th>Room</th>
                        <th>Block</th>
                        <th>Rent Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bedsTableBody">
                    @php
                        $allBeds = \App\Models\Bed::with(['room.block'])->get();
                    @endphp
                    @forelse($allBeds as $bed)
                        <tr data-bed-id="{{ $bed->id }}">
                            <td><strong>{{ $bed->name }}</strong></td>
                            <td>{{ $bed->room->name }}</td>
                            <td><span class="badge bg-info">{{ $bed->room->block->name }}</span></td>
                            <td>
                                @if($bed->rent_price)
                                    Tsh {{ number_format($bed->rent_price, 2) }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($bed->rent_duration)
                                    {{ ucfirst($bed->rent_duration) }}
                                    @if($bed->rent_duration === 'semester' && $bed->semester_months)
                                        ({{ $bed->semester_months }} months)
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($bed->status === 'free')
                                    <span class="badge bg-success">Free</span>
                                @elseif($bed->status === 'occupied')
                                    <span class="badge bg-danger">Occupied</span>
                                @elseif($bed->status === 'pending_payment')
                                    <span class="badge bg-warning text-dark">Pending Payment</span>
                                @else
                                    <span class="badge bg-secondary">Free</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info view-bed" data-bed-id="{{ $bed->id }}" title="View More">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-bed" data-bed-id="{{ $bed->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger remove-bed" data-bed-id="{{ $bed->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-bed" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">No beds registered yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Room Items Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Room Items</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="roomItemsTable" class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Room</th>
                        <th>Block</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allItems = \App\Models\RoomItem::with(['room.block'])->get();
                    @endphp
                    @forelse($allItems as $item)
                        <tr>
                            <td><strong>{{ $item->item_name }}</strong></td>
                            <td>{{ $item->room->name }}</td>
                            <td><span class="badge bg-info">{{ $item->room->block->name }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-info view-item" data-item-id="{{ $item->id }}" title="View More">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-item" data-item-id="{{ $item->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-item" data-item-id="{{ $item->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">No items registered yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="addRoomModalLabel">
                    <i class="bi bi-door-open me-2"></i>Add New Room
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRoomForm" enctype="multipart/form-data">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                            <label for="roomBlock" class="form-label">Select Block <span class="text-danger">*</span></label>
                            <select class="form-select" id="roomBlock" name="block_id" required>
                            <option value="">Select Block</option>
                                @foreach($blocks as $block)
                                    <option value="{{ $block->id }}">{{ $block->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    <!-- Rooms Container -->
                    <div id="roomsContainer">
                        <div class="room-item mb-4 p-3 border rounded" data-room-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="bi bi-door-open me-2"></i>Room 1</h6>
                                <button type="button" class="btn btn-sm btn-danger remove-room-btn" style="display: none;">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                        </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Room Name/Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control room-name" name="rooms[0][name]" placeholder="Room Name/Number" required>
                                    </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control room-location" name="rooms[0][location]" placeholder="Location">
                                </div>
                                    </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control room-description" name="rooms[0][description]" rows="2" placeholder="Room description..."></textarea>
                                </div>

                            <div class="mb-3">
                                <label class="form-label">Room Image (Optional)</label>
                                <input type="file" class="form-control room-image" name="rooms[0][image]" accept="image/*">
                                <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)</small>
                                    </div>

                            <div class="mb-3">
                                <label class="form-label">Available Items</label>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="items-container" data-room-index="0">
                                            <div class="item-row mb-2">
                                                <div class="input-group">
                                                    <input type="text" class="form-control item-name" name="rooms[0][items][]" placeholder="Item name (e.g., Bulb, Table, Chair)">
                                                    <button type="button" class="btn btn-danger remove-item-btn">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                </div>
                                    </div>
                                </div>
                                        <button type="button" class="btn btn-sm btn-info mt-2 add-item-btn" data-room-index="0">
                                            <i class="bi bi-plus-circle me-1"></i>Add Item
                                        </button>
                            </div>
                            </div>
                        </div>

                            <div class="mb-3">
                            <div class="form-check">
                                    <input class="form-check-input room-has-beds" type="checkbox" name="rooms[0][has_beds]" value="1" data-room-index="0">
                                    <label class="form-check-label">
                                    <strong>Room has beds</strong>
                                </label>
                            </div>
                        </div>

                            <!-- Room Pricing Section (when no beds) -->
                            <div class="room-pricing-section" data-room-index="0">
                                <div class="card bg-light mb-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Room Pricing</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label small">Rent Price <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control room-rent-price" name="rooms[0][rent_price]" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label small">Rent Duration <span class="text-danger">*</span></label>
                                                <select class="form-select room-rent-duration" name="rooms[0][rent_duration]">
                                                    <option value="">Select Duration</option>
                                                    <option value="monthly">Monthly</option>
                                                    <option value="semester">Semester</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3 room-payment-frequency-container" data-room-index="0" style="display: none;">
                                                <label class="form-label small">Payment Frequency <span class="text-danger">*</span></label>
                                                <select class="form-select room-payment-frequency" name="rooms[0][payment_frequency]">
                                                    <option value="">Select</option>
                                                    <option value="one_month">One Month</option>
                                                    <option value="two_months">Two Months</option>
                                                    <option value="three_months">Three Months</option>
                                                    <option value="four_months">Four Months</option>
                                                    <option value="five_months">Five Months</option>
                                                    <option value="six_months">Six Months</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3 room-semester-months-container" data-room-index="0" style="display: none;">
                                                <label class="form-label small">Semester Months <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control room-semester-months" name="rooms[0][semester_months]" min="1" placeholder="e.g., 4">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Beds Section for this room -->
                            <div class="beds-section" data-room-index="0" style="display: none;">
                                <div class="card bg-light mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bi bi-bed me-2"></i>Beds</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="beds-container" data-room-index="0">
                                            <div class="bed-item mb-3 p-3 border rounded">
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Bed Name/Number <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control bed-name" name="rooms[0][beds][0][name]" placeholder="Bed Name/Number" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small">Rent Price</label>
                                                        <input type="number" class="form-control bed-rent-price" name="rooms[0][beds][0][rent_price]" step="0.01" min="0" placeholder="0.00">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label small">Duration</label>
                                                        <select class="form-select bed-rent-duration" name="rooms[0][beds][0][rent_duration]">
                                                            <option value="">Select</option>
                                                            <option value="monthly">Monthly</option>
                                                            <option value="semester">Semester</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 bed-payment-frequency-container" style="display: none;">
                                                        <label class="form-label small">Payment Frequency <span class="text-danger">*</span></label>
                                                        <select class="form-select bed-payment-frequency" name="rooms[0][beds][0][payment_frequency]">
                                                            <option value="">Select</option>
                                                            <option value="one_month">One Month</option>
                                                            <option value="two_months">Two Months</option>
                                                            <option value="three_months">Three Months</option>
                                                            <option value="four_months">Four Months</option>
                                                            <option value="five_months">Five Months</option>
                                                            <option value="six_months">Six Months</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 bed-semester-months-container" style="display: none;">
                                                        <label class="form-label small">Semester Months <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control bed-semester-months" name="rooms[0][beds][0][semester_months]" min="1" placeholder="Months">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label small">&nbsp;</label>
                                                        <button type="button" class="btn btn-danger btn-sm w-100 remove-bed-btn">
                                                            <i class="bi bi-trash"></i>
                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-success mt-2 add-bed-btn" data-room-index="0">
                                            <i class="bi bi-plus-circle me-1"></i>Add Another Bed
                                        </button>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-primary" id="addAnotherRoomBtn">
                            <i class="bi bi-plus-circle me-1"></i>Add Another Room
                        </button>
                                </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitRoomBtn" 
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="submitRoomSpinner" role="status"></span>
                        <span id="submitRoomText">Add Room</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Room Modal -->
<div class="modal fade" id="viewRoomModal" tabindex="-1" aria-labelledby="viewRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="viewRoomModalLabel">
                    <i class="bi bi-door-open me-2"></i>Room Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="viewRoomImage" src="" alt="Room Image" class="img-fluid rounded" style="max-height: 200px; display: none;">
                </div>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Room Name:</th>
                        <td id="viewRoomName"></td>
                    </tr>
                    <tr>
                        <th>Block:</th>
                        <td id="viewRoomBlock"></td>
                    </tr>
                    <tr>
                        <th>Location:</th>
                        <td id="viewRoomLocation"></td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td id="viewRoomType"></td>
                    </tr>
                    <tr>
                        <th>Total Beds:</th>
                        <td id="viewRoomBeds"></td>
                    </tr>
                    <tr>
                        <th>Total Items:</th>
                        <td id="viewRoomItems"></td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td id="viewRoomCreated"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="editRoomModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Room
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRoomForm" enctype="multipart/form-data">
                <input type="hidden" id="editRoomId" name="id">
                <div class="modal-body">
                    <div id="editRoomFormErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                        <label for="editRoomName" class="form-label">Room Name/Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editRoomName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editRoomLocation" class="form-label">Location</label>
                        <input type="text" class="form-control" id="editRoomLocation" name="location">
                    </div>

                    <div class="mb-3">
                        <label for="editRoomImage" class="form-label">Room Image (Optional)</label>
                        <input type="file" class="form-control" id="editRoomImage" name="image" accept="image/*">
                        <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" 
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        Update Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Bed Modal -->
<div class="modal fade" id="viewBedModal" tabindex="-1" aria-labelledby="viewBedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="viewBedModalLabel">
                    <i class="bi bi-bed me-2"></i>Bed Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Bed Name/Number:</th>
                        <td id="viewBedName"></td>
                    </tr>
                    <tr>
                        <th>Room:</th>
                        <td id="viewBedRoom"></td>
                    </tr>
                    <tr>
                        <th>Block:</th>
                        <td id="viewBedBlock"></td>
                    </tr>
                    <tr>
                        <th>Rent Price:</th>
                        <td id="viewBedRentPrice"></td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td id="viewBedDuration"></td>
                    </tr>
                    <tr>
                        <th>Payment Frequency:</th>
                        <td id="viewBedPaymentFreq"></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td id="viewBedStatus"></td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td id="viewBedCreated"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Bed Modal -->
<div class="modal fade" id="editBedModal" tabindex="-1" aria-labelledby="editBedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="editBedModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Bed
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBedForm">
                <input type="hidden" id="editBedId" name="id">
                <div class="modal-body">
                    <div id="editBedFormErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                        <label for="editBedName" class="form-label">Bed Name/Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editBedName" name="name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editBedRentPrice" class="form-label">Rent Price</label>
                            <input type="number" class="form-control" id="editBedRentPrice" name="rent_price" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editBedRentDuration" class="form-label">Duration</label>
                            <select class="form-select" id="editBedRentDuration" name="rent_duration">
                                <option value="">Select</option>
                                <option value="monthly">Monthly</option>
                                <option value="semester">Semester</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="editBedSemesterMonthsContainer" style="display: none;">
                            <label for="editBedSemesterMonths" class="form-label">Semester Months</label>
                            <input type="number" class="form-control" id="editBedSemesterMonths" name="semester_months" min="1">
                        </div>
                        <div class="col-md-6 mb-3" id="editBedPaymentFrequencyContainer" style="display: none;">
                            <label for="editBedPaymentFrequency" class="form-label">Payment Frequency</label>
                            <select class="form-select" id="editBedPaymentFrequency" name="payment_frequency">
                                <option value="">Select</option>
                                <option value="one_month">One Month</option>
                                <option value="two_months">Two Months</option>
                                <option value="three_months">Three Months</option>
                                <option value="four_months">Four Months</option>
                                <option value="five_months">Five Months</option>
                                <option value="six_months">Six Months</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editBedStatus" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="editBedStatus" name="status" required>
                            <option value="free">Free</option>
                            <option value="occupied">Occupied</option>
                            <option value="pending_payment">Pending Payment</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Bed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Item Modal -->
<div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewItemModalLabel">
                    <i class="bi bi-box-seam me-2"></i>Item Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Item Name:</th>
                        <td id="viewItemName"></td>
                    </tr>
                    <tr>
                        <th>Room:</th>
                        <td id="viewItemRoom"></td>
                    </tr>
                    <tr>
                        <th>Block:</th>
                        <td id="viewItemBlock"></td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td id="viewItemCreated"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="editItemModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editItemForm">
                <input type="hidden" id="editItemId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editItemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editItemName" name="item_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white">Update Item</button>
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
    .card {
        transition: transform 0.2s;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    // Ensure jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
    }

    let roomCounter = 0;
    let bedCounters = {};
    let itemCounters = {};

    $(document).ready(function() {
        console.log('Rooms page scripts loaded');
        // Initialize DataTables with error handling
        try {
            if ($('#roomsTable').length && $('#roomsTable tbody tr').length > 0) {
                $('#roomsTable').DataTable({
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    order: [[1, 'asc']],
                    destroy: true,
                    responsive: true
                });
            }
        } catch(e) {
            console.error('Error initializing rooms table:', e);
        }

        try {
            if ($('#bedsTable').length && $('#bedsTable tbody tr').length > 0) {
                $('#bedsTable').DataTable({
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    order: [[0, 'asc']],
                    destroy: true,
                    responsive: true
                });
            }
        } catch(e) {
            console.error('Error initializing beds table:', e);
        }

        try {
            if ($('#roomItemsTable').length && $('#roomItemsTable tbody tr').length > 0) {
                $('#roomItemsTable').DataTable({
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    order: [[0, 'asc']],
                    destroy: true,
                    responsive: true
                });
            }
        } catch(e) {
            console.error('Error initializing room items table:', e);
        }

        // Initialize counters for first room
        bedCounters[0] = 1;
        itemCounters[0] = 1;

        // Toggle beds section and pricing section for each room
        $(document).on('change', '.room-has-beds', function(e) {
            e.preventDefault();
            console.log('Room has beds checkbox changed');
            const roomIndex = $(this).data('room-index');
            console.log('Room Index:', roomIndex);
            const bedsSection = $(`.beds-section[data-room-index="${roomIndex}"]`);
            const pricingSection = $(`.room-pricing-section[data-room-index="${roomIndex}"]`);
            console.log('Beds Section found:', bedsSection.length);
            console.log('Pricing Section found:', pricingSection.length);
            if ($(this).is(':checked')) {
                console.log('Showing beds section, hiding pricing section');
                bedsSection.slideDown();
                pricingSection.slideUp();
                // Clear pricing fields
                pricingSection.find('.room-rent-price, .room-rent-duration, .room-payment-frequency, .room-semester-months').val('').prop('required', false);
            } else {
                console.log('Hiding beds section, showing pricing section');
                bedsSection.slideUp();
                pricingSection.slideDown();
                // Clear bed fields
                bedsSection.find('.bed-name, .bed-rent-price, .bed-rent-duration, .bed-payment-frequency, .bed-semester-months').val('').prop('required', false);
            }
        });

        // Initialize: Show pricing section by default for first room (since checkbox is unchecked)
        $('.room-pricing-section[data-room-index="0"]').show();

        // Toggle semester months and payment frequency for room pricing
        $(document).on('change', '.room-rent-duration', function() {
            const roomIndex = $(this).closest('.room-item').data('room-index');
            const roomItem = $(this).closest('.room-item');
            const semesterMonthsInput = roomItem.find('.room-semester-months');
            const paymentFrequencySelect = roomItem.find('.room-payment-frequency');
            const semesterContainer = roomItem.find('.room-semester-months-container');
            const paymentContainer = roomItem.find('.room-payment-frequency-container');
            
            if ($(this).val() === 'semester') {
                semesterContainer.slideDown();
                semesterMonthsInput.prop('required', true);
                paymentContainer.slideUp();
                paymentFrequencySelect.prop('required', false).val('');
            } else if ($(this).val() === 'monthly') {
                paymentContainer.slideDown();
                paymentFrequencySelect.prop('required', true);
                semesterContainer.slideUp();
                semesterMonthsInput.prop('required', false).val('');
            } else {
                semesterContainer.slideUp();
                paymentContainer.slideUp();
                semesterMonthsInput.prop('required', false).val('');
                paymentFrequencySelect.prop('required', false).val('');
            }
        });

        // Add Another Room Button Handler
        $(document).on('click', '#addAnotherRoomBtn', function(e) {
            e.preventDefault();
            console.log('Add Another Room clicked');
            roomCounter++;
            bedCounters[roomCounter] = 1;
            itemCounters[roomCounter] = 1;
            
            const roomHtml = `
                <div class="room-item mb-4 p-3 border rounded" data-room-index="${roomCounter}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="bi bi-door-open me-2"></i>Room ${roomCounter + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-room-btn">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Room Name/Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control room-name" name="rooms[${roomCounter}][name]" placeholder="Room Name/Number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control room-location" name="rooms[${roomCounter}][location]" placeholder="Location">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control room-description" name="rooms[${roomCounter}][description]" rows="2" placeholder="Room description..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Room Image (Optional)</label>
                        <input type="file" class="form-control room-image" name="rooms[${roomCounter}][image]" accept="image/*">
                        <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Available Items</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="items-container" data-room-index="${roomCounter}">
                                    <div class="item-row mb-2">
                                        <div class="input-group">
                                            <input type="text" class="form-control item-name" name="rooms[${roomCounter}][items][]" placeholder="Item name (e.g., Bulb, Table, Chair)">
                                            <button type="button" class="btn btn-danger remove-item-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-info mt-2 add-item-btn" data-room-index="${roomCounter}">
                                    <i class="bi bi-plus-circle me-1"></i>Add Item
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input room-has-beds" type="checkbox" name="rooms[${roomCounter}][has_beds]" value="1" data-room-index="${roomCounter}">
                            <label class="form-check-label">
                                <strong>Room has beds</strong>
                            </label>
                        </div>
                    </div>

                    <!-- Room Pricing Section (when no beds) -->
                    <div class="room-pricing-section" data-room-index="${roomCounter}">
                        <div class="card bg-light mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Room Pricing</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small">Rent Price <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control room-rent-price" name="rooms[${roomCounter}][rent_price]" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small">Rent Duration <span class="text-danger">*</span></label>
                                        <select class="form-select room-rent-duration" name="rooms[${roomCounter}][rent_duration]">
                                            <option value="">Select Duration</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="semester">Semester</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3 room-payment-frequency-container" data-room-index="${roomCounter}" style="display: none;">
                                        <label class="form-label small">Payment Frequency <span class="text-danger">*</span></label>
                                        <select class="form-select room-payment-frequency" name="rooms[${roomCounter}][payment_frequency]">
                                            <option value="">Select</option>
                                            <option value="one_month">One Month</option>
                                            <option value="two_months">Two Months</option>
                                            <option value="three_months">Three Months</option>
                                            <option value="four_months">Four Months</option>
                                            <option value="five_months">Five Months</option>
                                            <option value="six_months">Six Months</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3 room-semester-months-container" data-room-index="${roomCounter}" style="display: none;">
                                        <label class="form-label small">Semester Months <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control room-semester-months" name="rooms[${roomCounter}][semester_months]" min="1" placeholder="e.g., 4">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Beds Section for this room -->
                    <div class="beds-section" data-room-index="${roomCounter}" style="display: none;">
                        <div class="card bg-light mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="bi bi-bed me-2"></i>Beds</h6>
                            </div>
                            <div class="card-body">
                                <div class="beds-container" data-room-index="${roomCounter}">
                                    <div class="bed-item mb-3 p-3 border rounded">
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label small">Bed Name/Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control bed-name" name="rooms[${roomCounter}][beds][0][name]" placeholder="Bed Name/Number" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Rent Price</label>
                                                <input type="number" class="form-control bed-rent-price" name="rooms[${roomCounter}][beds][0][rent_price]" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Duration</label>
                                                <select class="form-select bed-rent-duration" name="rooms[${roomCounter}][beds][0][rent_duration]">
                                                    <option value="">Select</option>
                                                    <option value="monthly">Monthly</option>
                                                    <option value="semester">Semester</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 bed-payment-frequency-container" style="display: none;">
                                                <label class="form-label small">Payment Frequency <span class="text-danger">*</span></label>
                                                <select class="form-select bed-payment-frequency" name="rooms[${roomCounter}][beds][0][payment_frequency]">
                                                    <option value="">Select</option>
                                                    <option value="one_month">One Month</option>
                                                    <option value="two_months">Two Months</option>
                                                    <option value="three_months">Three Months</option>
                                                    <option value="four_months">Four Months</option>
                                                    <option value="five_months">Five Months</option>
                                                    <option value="six_months">Six Months</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 bed-semester-months-container" style="display: none;">
                                                <label class="form-label small">Semester Months <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control bed-semester-months" name="rooms[${roomCounter}][beds][0][semester_months]" min="1" placeholder="Months">
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label small">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-bed-btn">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-success mt-2 add-bed-btn" data-room-index="${roomCounter}">
                                    <i class="bi bi-plus-circle me-1"></i>Add Another Bed
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#roomsContainer').append(roomHtml);
            $('.remove-room-btn').show();
        });

        // Remove Room Button Handler
        $(document).on('click', '.remove-room-btn', function() {
            $(this).closest('.room-item').remove();
            const remainingRooms = $('.room-item').length;
            if (remainingRooms <= 1) {
                $('.remove-room-btn').hide();
            }
        });

        // Add Item Button Handler (per room)
        $(document).on('click', '.add-item-btn', function(e) {
            e.preventDefault();
            console.log('Add Item clicked');
            const roomIndex = $(this).data('room-index');
            console.log('Room Index:', roomIndex);
            const itemCounter = itemCounters[roomIndex] || 1;
            const itemsContainer = $(`.items-container[data-room-index="${roomIndex}"]`);
            console.log('Items Container:', itemsContainer.length);
            
            const itemHtml = `
                <div class="item-row mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control item-name" name="rooms[${roomIndex}][items][]" placeholder="Item name (e.g., Bulb, Table, Chair)">
                        <button type="button" class="btn btn-danger remove-item-btn">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            itemsContainer.append(itemHtml);
            itemCounters[roomIndex] = itemCounter + 1;
        });

        // Remove item
        $(document).on('click', '.remove-item-btn', function() {
            $(this).closest('.item-row').remove();
        });

        // Add Bed Button Handler (per room)
        $(document).on('click', '.add-bed-btn', function() {
            const roomIndex = $(this).data('room-index');
            const bedCounter = bedCounters[roomIndex] || 1;
            const bedsContainer = $(`.beds-container[data-room-index="${roomIndex}"]`);
            
            const bedHtml = `
                <div class="bed-item mb-3 p-3 border rounded">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small">Bed Name/Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bed-name" name="rooms[${roomIndex}][beds][${bedCounter}][name]" placeholder="Bed Name/Number" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Rent Price</label>
                            <input type="number" class="form-control bed-rent-price" name="rooms[${roomIndex}][beds][${bedCounter}][rent_price]" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Duration</label>
                            <select class="form-select bed-rent-duration" name="rooms[${roomIndex}][beds][${bedCounter}][rent_duration]">
                                <option value="">Select</option>
                                <option value="monthly">Monthly</option>
                                <option value="semester">Semester</option>
                            </select>
                        </div>
                        <div class="col-md-2 bed-payment-frequency-container" style="display: none;">
                            <label class="form-label small">Payment Frequency <span class="text-danger">*</span></label>
                            <select class="form-select bed-payment-frequency" name="rooms[${roomIndex}][beds][${bedCounter}][payment_frequency]">
                                <option value="">Select</option>
                                <option value="one_month">One Month</option>
                                <option value="two_months">Two Months</option>
                                <option value="three_months">Three Months</option>
                                <option value="four_months">Four Months</option>
                                <option value="five_months">Five Months</option>
                                <option value="six_months">Six Months</option>
                            </select>
                        </div>
                        <div class="col-md-2 bed-semester-months-container" style="display: none;">
                            <label class="form-label small">Semester Months <span class="text-danger">*</span></label>
                            <input type="number" class="form-control bed-semester-months" name="rooms[${roomIndex}][beds][${bedCounter}][semester_months]" min="1" placeholder="Months">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-bed-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            bedsContainer.append(bedHtml);
            bedCounters[roomIndex] = bedCounter + 1;
        });

        // Toggle semester months and payment frequency for beds
        $(document).on('change', '.bed-rent-duration', function() {
            const bedItem = $(this).closest('.bed-item');
            const semesterMonthsInput = bedItem.find('.bed-semester-months');
            const paymentFrequencySelect = bedItem.find('.bed-payment-frequency');
            const semesterContainer = bedItem.find('.bed-semester-months-container');
            const paymentContainer = bedItem.find('.bed-payment-frequency-container');
            
            if ($(this).val() === 'semester') {
                semesterContainer.slideDown();
                semesterMonthsInput.prop('required', true);
                paymentContainer.slideUp();
                paymentFrequencySelect.prop('required', false).val('');
            } else if ($(this).val() === 'monthly') {
                paymentContainer.slideDown();
                paymentFrequencySelect.prop('required', true);
                semesterContainer.slideUp();
                semesterMonthsInput.prop('required', false).val('');
            } else {
                semesterContainer.slideUp();
                paymentContainer.slideUp();
                semesterMonthsInput.prop('required', false).val('');
                paymentFrequencySelect.prop('required', false).val('');
            }
        });

        // Remove bed (from form)
        $(document).on('click', '.remove-bed-btn', function() {
            $(this).closest('.bed-item').remove();
        });

        // Handle form submission
        $('#addRoomForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = $('#submitRoomBtn');
            const submitSpinner = $('#submitRoomSpinner');
            const submitText = $('#submitRoomText');
            const formErrors = $('#formErrors');
            
            formErrors.addClass('d-none').html('');
            submitBtn.prop('disabled', true);
            submitSpinner.removeClass('d-none');
            submitText.text('Adding...');

            // Debug: Log form data
            console.log('Form Data:', formData);
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: '{{ route("rooms.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Success response:', response);
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<i class="bi bi-check-circle-fill me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('.container-fluid').prepend(alert);
                    
                    $('#addRoomModal').modal('hide');
                    $('#addRoomForm')[0].reset();
                    // Reset to first room only
                    const firstRoom = $('.room-item').first();
                    $('.room-item').not(firstRoom).remove();
                    $('.remove-room-btn').hide();
                    roomCounter = 0;
                    bedCounters = {0: 1};
                    itemCounters = {0: 1};
                    $('.beds-section').hide();
                    $('.room-has-beds').prop('checked', false);
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    submitBtn.prop('disabled', false);
                    submitSpinner.addClass('d-none');
                    submitText.text('Add Room');
                    
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errors += '<li><strong>' + key + ':</strong> ' + value[0] + '</li>';
                            });
                        } else {
                            errors += '<li>Validation error occurred</li>';
                        }
                        errors += '</ul>';
                        formErrors.removeClass('d-none').html(errors);
                    } else if (xhr.status === 500) {
                        formErrors.removeClass('d-none').html('Server error: ' + (xhr.responseJSON?.message || xhr.responseText || 'Please check server logs'));
                    } else {
                        formErrors.removeClass('d-none').html('Error ' + xhr.status + ': ' + (xhr.responseJSON?.message || xhr.responseText || 'An error occurred. Please try again.'));
                    }
                }
            });
        });

        // Remove bed from room (AJAX)
        $(document).on('click', '.remove-bed', function() {
            const bedId = $(this).data('bed-id');
            Swal.fire({
                title: 'Thibitisha Uondoaji',
                text: 'Je, una uhakika unataka kuondoa kitanda hiki?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ndio, Onda',
                cancelButtonText: 'Ghairi',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/rooms/beds/${bedId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $(`tr[data-bed-id="${bedId}"]`).fadeOut(function() {
                                $(this).remove();
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Imefanikiwa!',
                                text: response.message,
                                confirmButtonText: 'Sawa',
                                confirmButtonColor: '#1e3c72'
                            });
                        }
                    });
                }
            });
        });

        // View room
        $(document).on('click', '.view-room', function() {
            const roomId = $(this).data('room-id');
            $.ajax({
                url: `/rooms/${roomId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#viewRoomName').text(response.name);
                    $('#viewRoomBlock').text(response.block.name);
                    $('#viewRoomLocation').text(response.location || 'N/A');
                    $('#viewRoomType').text(response.has_beds ? 'With Beds' : 'No Beds');
                    $('#viewRoomBeds').text(response.beds.length);
                    $('#viewRoomItems').text(response.items.length);
                    $('#viewRoomCreated').text(new Date(response.created_at).toLocaleDateString());
                    if (response.image) {
                        $('#viewRoomImage').attr('src', `/storage/${response.image}`).show();
                    } else {
                        $('#viewRoomImage').hide();
                    }
                    $('#viewRoomModal').modal('show');
                }
            });
        });

        // Edit room
        $(document).on('click', '.edit-room', function() {
            const roomId = $(this).data('room-id');
            $.ajax({
                url: `/rooms/${roomId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editRoomId').val(response.id);
                    $('#editRoomName').val(response.name);
                    $('#editRoomLocation').val(response.location || '');
                    $('#editRoomModal').modal('show');
                }
            });
        });

        // Handle edit room form submission
        $('#editRoomForm').on('submit', function(e) {
            e.preventDefault();
            const roomId = $('#editRoomId').val();
            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            
            $.ajax({
                url: `/rooms/${roomId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editRoomModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                        errors += '</ul>';
                        $('#editRoomFormErrors').removeClass('d-none').html(errors);
                    }
                }
            });
        });

        // Delete room
        $(document).on('click', '.delete-room', function() {
            const roomId = $(this).data('room-id');
            Swal.fire({
                title: 'Thibitisha Ufutaji',
                text: 'Je, una uhakika unataka kufuta chumba hiki? Hii pia itafuta vitanda vyote na vitu vilivyomo kwenye chumba hiki.',
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
                url: `/rooms/${roomId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Imefanikiwa!',
                        text: 'Chumba kimefutwa kwa mafanikio.',
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

        // View bed
        $(document).on('click', '.view-bed', function() {
            const bedId = $(this).data('bed-id');
            $.ajax({
                url: `/beds/${bedId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#viewBedName').text(response.name);
                    $('#viewBedRoom').text(response.room.name);
                    $('#viewBedBlock').text(response.room.block.name);
                    $('#viewBedRentPrice').text(response.rent_price ? 'Tsh ' + parseFloat(response.rent_price).toLocaleString() : 'N/A');
                    $('#viewBedDuration').text(response.rent_duration ? (response.rent_duration.charAt(0).toUpperCase() + response.rent_duration.slice(1) + (response.rent_duration === 'semester' && response.semester_months ? ' (' + response.semester_months + ' months)' : '')) : 'N/A');
                    $('#viewBedPaymentFreq').text(response.payment_frequency ? response.payment_frequency.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A');
                    const statusBadge = response.status === 'free' ? '<span class="badge bg-success">Free</span>' : 
                                       response.status === 'occupied' ? '<span class="badge bg-danger">Occupied</span>' : 
                                       '<span class="badge bg-warning text-dark">Pending Payment</span>';
                    $('#viewBedStatus').html(statusBadge);
                    $('#viewBedCreated').text(new Date(response.created_at).toLocaleDateString());
                    $('#viewBedModal').modal('show');
                }
            });
        });

        // Edit bed
        $(document).on('click', '.edit-bed', function() {
            const bedId = $(this).data('bed-id');
            $.ajax({
                url: `/beds/${bedId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editBedId').val(response.id);
                    $('#editBedName').val(response.name);
                    $('#editBedRentPrice').val(response.rent_price || '');
                    $('#editBedRentDuration').val(response.rent_duration || '');
                    $('#editBedSemesterMonths').val(response.semester_months || '');
                    $('#editBedPaymentFrequency').val(response.payment_frequency || '');
                    $('#editBedStatus').val(response.status || 'free');
                    
                    if (response.rent_duration === 'semester') {
                        $('#editBedSemesterMonthsContainer').show();
                        $('#editBedPaymentFrequencyContainer').hide();
                    } else if (response.rent_duration === 'monthly') {
                        $('#editBedPaymentFrequencyContainer').show();
                        $('#editBedSemesterMonthsContainer').hide();
                    } else {
                        $('#editBedSemesterMonthsContainer').hide();
                        $('#editBedPaymentFrequencyContainer').hide();
                    }
                    
                    $('#editBedModal').modal('show');
                }
            });
        });

        // Handle edit bed form submission
        $('#editBedForm').on('submit', function(e) {
            e.preventDefault();
            const bedId = $('#editBedId').val();
            const formData = $(this).serialize();
            
            $.ajax({
                url: `/beds/${bedId}`,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editBedModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                        errors += '</ul>';
                        $('#editBedFormErrors').removeClass('d-none').html(errors);
                    }
                }
            });
        });

        // Toggle edit bed duration fields
        $('#editBedRentDuration').on('change', function() {
            if ($(this).val() === 'semester') {
                $('#editBedSemesterMonthsContainer').slideDown();
                $('#editBedPaymentFrequencyContainer').slideUp();
                $('#editBedSemesterMonths').prop('required', true);
                $('#editBedPaymentFrequency').prop('required', false).val('');
            } else if ($(this).val() === 'monthly') {
                $('#editBedPaymentFrequencyContainer').slideDown();
                $('#editBedSemesterMonthsContainer').slideUp();
                $('#editBedPaymentFrequency').prop('required', true);
                $('#editBedSemesterMonths').prop('required', false).val('');
            } else {
                $('#editBedSemesterMonthsContainer, #editBedPaymentFrequencyContainer').slideUp();
                $('#editBedSemesterMonths, #editBedPaymentFrequency').prop('required', false).val('');
            }
        });

        // View item
        $(document).on('click', '.view-item', function() {
            const itemId = $(this).data('item-id');
            $.ajax({
                url: `/room-items/${itemId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#viewItemName').text(response.item_name);
                    $('#viewItemRoom').text(response.room.name);
                    $('#viewItemBlock').text(response.room.block.name);
                    $('#viewItemCreated').text(new Date(response.created_at).toLocaleDateString());
                    $('#viewItemModal').modal('show');
                },
                error: function() {
                    // Fallback - get from table
                    const row = $(`button[data-item-id="${itemId}"]`).closest('tr');
                    $('#viewItemName').text(row.find('td:first').text().trim());
                    $('#viewItemRoom').text(row.find('td:nth-child(2)').text().trim());
                    $('#viewItemBlock').text(row.find('td:nth-child(3)').text().trim());
                    $('#viewItemCreated').text('N/A');
                    $('#viewItemModal').modal('show');
                }
            });
        });

        // Edit item
        $(document).on('click', '.edit-item', function() {
            const itemId = $(this).data('item-id');
            const row = $(this).closest('tr');
            $('#editItemId').val(itemId);
            $('#editItemName').val(row.find('td:first').text().trim());
            $('#editItemModal').modal('show');
        });

        // Handle edit item form submission
        $('#editItemForm').on('submit', function(e) {
            e.preventDefault();
            const itemId = $('#editItemId').val();
            const formData = $(this).serialize();
            
            $.ajax({
                url: `/room-items/${itemId}`,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editItemModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = '<ul class="mb-0">';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                        errors += '</ul>';
                        Swal.fire({
                            icon: 'error',
                            title: 'Hitilafu',
                            html: errors,
                            confirmButtonText: 'Sawa',
                            confirmButtonColor: '#1e3c72'
                        });
                    }
                }
            });
        });

        // Delete item
        $(document).on('click', '.delete-item', function() {
            const itemId = $(this).data('item-id');
            Swal.fire({
                title: 'Thibitisha Ufutaji',
                text: 'Je, una uhakika unataka kufuta kitu hiki?',
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
                        url: `/room-items/${itemId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Imefanikiwa!',
                                text: 'Kitu kimefutwa kwa mafanikio.',
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
        $('#addRoomModal').on('hidden.bs.modal', function() {
            $('#addRoomForm')[0].reset();
            $('#formErrors').addClass('d-none').html('');
            // Reset to first room only
            const firstRoom = $('.room-item').first();
            $('.room-item').not(firstRoom).remove();
            $('.remove-room-btn').hide();
            roomCounter = 0;
            bedCounters = {0: 1};
            itemCounters = {0: 1};
            $('.beds-section').hide();
            $('.room-pricing-section').show(); // Show pricing by default
            $('.room-has-beds').prop('checked', false);
        });
    });
</script>
@endpush
