@extends('layouts.app')

@section('title', 'Update Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Update Profile</h5>
                </div>
                <div class="card-body">
                    <!-- Update Profile Form -->
                    <form action="{{ route('profile.update') }}" method="POST" class="mb-4">
                        @csrf
                        <h6 class="mb-3 text-muted">Personal Information</h6>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                    id="username" name="username" value="{{ old('username', Auth::user()->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Profile
                            </button>
                            <a href="{{ route('dashboard.owner') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>
                    
                    <hr>
                    
                    @if(Auth::user()->role === 'owner')
                    <!-- Owner Details Form -->
                    <form action="{{ route('profile.owner-details.update') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <h6 class="mb-3 text-muted">Owner Details</h6>
                        
                        <!-- Profile Image -->
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            @if(isset($ownerDetail) && $ownerDetail->profile_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $ownerDetail->profile_image) }}" 
                                         alt="Profile Image" 
                                         class="img-thumbnail" 
                                         style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                </div>
                            @endif
                            <input type="file" 
                                   class="form-control @error('profile_image') is-invalid @enderror" 
                                   id="profile_image" 
                                   name="profile_image" 
                                   accept="image/*">
                            @error('profile_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Max size: 2MB. Formats: JPEG, PNG, JPG, GIF</small>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" 
                                       class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" 
                                       name="phone_number" 
                                       value="{{ old('phone_number', $ownerDetail->phone_number ?? '') }}" 
                                       placeholder="+255 XXX XXX XXX">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" 
                                       class="form-control @error('account_number') is-invalid @enderror" 
                                       id="account_number" 
                                       name="account_number" 
                                       value="{{ old('account_number', $ownerDetail->account_number ?? '') }}" 
                                       placeholder="Account number for receiving payments">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="account_name" class="form-label">Account Name</label>
                                <input type="text" 
                                       class="form-control @error('account_name') is-invalid @enderror" 
                                       id="account_name" 
                                       name="account_name" 
                                       value="{{ old('account_name', $ownerDetail->account_name ?? '') }}" 
                                       placeholder="Name on the account">
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" 
                                       class="form-control @error('bank_name') is-invalid @enderror" 
                                       id="bank_name" 
                                       name="bank_name" 
                                       value="{{ old('bank_name', $ownerDetail->bank_name ?? '') }}" 
                                       placeholder="Bank name">
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3" 
                                      placeholder="Physical address">{{ old('address', $ownerDetail->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="additional_info" class="form-label">Additional Information</label>
                            <textarea class="form-control @error('additional_info') is-invalid @enderror" 
                                      id="additional_info" 
                                      name="additional_info" 
                                      rows="4" 
                                      placeholder="Any additional information about the owner">{{ old('additional_info', $ownerDetail->additional_info ?? '') }}</textarea>
                            @error('additional_info')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Save Owner Details
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    @endif
                    
                    <!-- Change Password Form -->
                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        <h6 class="mb-3 text-muted">Change Password</h6>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                    id="new_password" name="new_password" required>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="new_password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                    id="new_password_confirmation" name="new_password_confirmation" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key me-1"></i>Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

