@extends('layouts.app')

@section('title', 'Edit Terms and Conditions')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="bi bi-file-text me-2"></i>Edit Terms and Conditions
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
                    <form action="{{ route('terms.update', $terms->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="version" class="form-label">
                                <i class="bi bi-tag me-1"></i>Version <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('version') is-invalid @enderror" 
                                   id="version" 
                                   name="version" 
                                   value="{{ old('version', $terms->version) }}" 
                                   placeholder="e.g., 1.0, 2.0, 2024.1"
                                   required>
                            @error('version')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">
                                <i class="bi bi-file-text me-1"></i>Terms and Conditions Content <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="15" 
                                      required>{{ old('content', $terms->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="is_active" 
                                   name="is_active" 
                                   {{ old('is_active', $terms->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Activate these terms</strong>
                            </label>
                            <small class="form-text text-muted d-block">If checked, these terms will become active and all other terms will be deactivated.</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('terms.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Update Terms
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
