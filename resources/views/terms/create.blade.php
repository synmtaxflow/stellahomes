@extends('layouts.app')

@section('title', 'Create Terms and Conditions')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="bi bi-file-text me-2"></i>Create New Terms and Conditions
                </h1>
                <a href="{{ route('terms.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('terms.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="version" class="form-label">
                                <i class="bi bi-tag me-1"></i>Version <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('version') is-invalid @enderror" 
                                   id="version" 
                                   name="version" 
                                   value="{{ old('version', '1.0') }}" 
                                   placeholder="e.g., 1.0, 2.0, 2024.1"
                                   required>
                            @error('version')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Use a version number to track different versions of terms.</small>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">
                                <i class="bi bi-file-text me-1"></i>Terms and Conditions Content <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="15" 
                                      required
                                      placeholder="Enter the terms and conditions content here...">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter all terms and conditions that students must agree to when booking.</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="is_active" 
                                   name="is_active" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Activate these terms immediately</strong>
                            </label>
                            <small class="form-text text-muted d-block">If checked, these terms will become active and all previous terms will be deactivated.</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('terms.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Create Terms
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
