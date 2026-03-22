@extends('layouts.app')

@section('title', 'Edit ' . $building->name . ' - Utility Wise')

@push('styles')
<style>
    /* FORM CONTAINER */
    .form-container {
        background: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        max-width: 800px;
        margin: 0 auto;
        border: 1px solid #dee2e6;
    }

    .form-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s;
        font-family: 'Inter', sans-serif;
    }

    .form-control:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 10px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* BUTTONS */
    .btn {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #4a5568;
        color: white;
    }

    .btn-primary:hover {
        background: #2d3748;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        text-decoration: none;
    }

    .btn-secondary {
        background: #718096;
        color: white;
    }

    .btn-secondary:hover {
        background: #4a5568;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        text-decoration: none;
    }

    .btn-danger {
        background: #e74c3c;
        color: white;
    }

    .btn-danger:hover {
        background: #c0392b;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        text-decoration: none;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .required {
        color: #e74c3c;
    }

    .help-text {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .error-message {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 5px;
    }

    .feature-checkbox {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }

    /* Error styling */
    .form-control.error {
        border-color: #e74c3c;
    }

    /* DANGER ZONE */
    .danger-zone {
        background: #fff5f5;
        border: 1px solid #fed7d7;
        border-radius: 8px;
        padding: 20px;
        margin-top: 40px;
    }

    .danger-zone-title {
        color: #e53e3e;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .danger-zone-description {
        color: #718096;
        font-size: 14px;
        margin-bottom: 15px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .form-container {
            padding: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .form-actions {
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            width: 100%;
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">✏️ Edit Building</h1>
            <p class="page-subtitle">Update the details of {{ $building->name }}</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <form action="{{ route('buildings.update', $building) }}" method="POST" id="updateForm">
            @csrf
            @method('PUT')
            
            <!-- Basic Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>📋</div>
                    <span>Basic Information</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Building Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') error @enderror" required 
                           value="{{ old('name', $building->name) }}"
                           placeholder="e.g., Skyline Tower, Garden Apartments">
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Building Type <span class="required">*</span></label>
                        <select name="building_type" class="form-control @error('building_type') error @enderror" required>
                            <option value="">Select Type</option>
                            <option value="residential" {{ old('building_type', $building->building_type) == 'residential' ? 'selected' : '' }}>Residential</option>
                            <option value="commercial" {{ old('building_type', $building->building_type) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                            <option value="mixed" {{ old('building_type', $building->building_type) == 'mixed' ? 'selected' : '' }}>Mixed-Use</option>
                        </select>
                        @error('building_type')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control @error('status') error @enderror" required>
                            <option value="active" {{ old('status', $building->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $building->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="under_construction" {{ old('status', $building->status) == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                            <option value="renovation" {{ old('status', $building->status) == 'renovation' ? 'selected' : '' }}>Under Renovation</option>
                        </select>
                        @error('status')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control form-textarea @error('description') error @enderror" 
                              placeholder="Brief description of the building, features, amenities...">{{ old('description', $building->description) }}</textarea>
                    @error('description')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Address Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>📍</div>
                    <span>Address Information</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Street Address <span class="required">*</span></label>
                    <input type="text" name="address" class="form-control @error('address') error @enderror" required 
                           value="{{ old('address', $building->address) }}"
                           placeholder="e.g., 123 Main Street">
                    @error('address')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">City <span class="required">*</span></label>
                        <input type="text" name="city" class="form-control @error('city') error @enderror" required 
                               value="{{ old('city', $building->city) }}"
                               placeholder="e.g., New York">
                        @error('city')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">State/Province <span class="required">*</span></label>
                        <input type="text" name="state" class="form-control @error('state') error @enderror" required 
                               value="{{ old('state', $building->state) }}"
                               placeholder="e.g., NY">
                        @error('state')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Zip Code <span class="required">*</span></label>
                        <input type="text" name="zip_code" class="form-control @error('zip_code') error @enderror" required 
                               value="{{ old('zip_code', $building->zip_code) }}"
                               placeholder="e.g., 10001">
                        @error('zip_code')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control @error('country') error @enderror" 
                               value="{{ old('country', $building->country) }}"
                               placeholder="e.g., United States">
                        @error('country')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Building Specifications -->
            <div class="form-section">
                <div class="section-title">
                    <div>📐</div>
                    <span>Building Specifications</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Total Floors <span class="required">*</span></label>
                        <input type="number" name="total_floors" class="form-control @error('total_floors') error @enderror" required 
                               value="{{ old('total_floors', $building->total_floors) }}"
                               placeholder="e.g., 10" min="1">
                        @error('total_floors')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Total Units <span class="required">*</span></label>
                        <input type="number" name="total_units" class="form-control @error('total_units') error @enderror" required 
                               value="{{ old('total_units', $building->total_units) }}"
                               placeholder="e.g., 50" min="0">
                        @error('total_units')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Expected total number of units in this building</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Year Built <span class="required">*</span></label>
                        <input type="number" name="year_built" class="form-control @error('year_built') error @enderror" required 
                               value="{{ old('year_built', $building->year_built) }}"
                               placeholder="e.g., 2020" min="1800" max="{{ date('Y') }}">
                        @error('year_built')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Total Area (sq ft)</label>
                        <input type="number" name="total_area" class="form-control @error('total_area') error @enderror" step="0.01"
                               value="{{ old('total_area', $building->total_area) }}"
                               placeholder="e.g., 50000.50" min="0">
                        @error('total_area')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Building Features -->
                <div class="form-group">
                    <label class="form-label">Building Features</label>
                    <div class="checkbox-group">
                        <div class="feature-checkbox">
                            <label class="checkbox-label">
                                <input type="hidden" name="has_elevator" value="0">
                                <input type="checkbox" name="has_elevator" value="1" 
                                       {{ old('has_elevator', $building->has_elevator) ? 'checked' : '' }}>
                                <span>Has Elevator</span>
                            </label>
                        </div>
                        
                        <div class="feature-checkbox">
                            <label class="checkbox-label">
                                <input type="hidden" name="has_parking" value="0">
                                <input type="checkbox" name="has_parking" value="1"
                                       {{ old('has_parking', $building->has_parking) ? 'checked' : '' }}>
                                <span>Has Parking</span>
                            </label>
                        </div>
                    </div>
                    @error('has_elevator')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('has_parking')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="form-section">
                <div class="section-title">
                    <div>📞</div>
                    <span>Contact Information</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="tel" name="contact_phone" class="form-control @error('contact_phone') error @enderror" 
                               value="{{ old('contact_phone', $building->contact_phone) }}"
                               placeholder="e.g., (555) 123-4567">
                        @error('contact_phone')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control @error('contact_email') error @enderror" 
                               value="{{ old('contact_email', $building->contact_email) }}"
                               placeholder="e.g., manager@example.com">
                        @error('contact_email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('buildings.show', $building) }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Update Building
                </button>
            </div>
        </form>
        
        <!-- Danger Zone (Delete Section) -->
        @if(auth()->user()->is_admin ?? false)
        <div class="danger-zone">
            <div class="danger-zone-title">
                <span>⚠️</span>
                <span>Danger Zone</span>
            </div>
            <div class="danger-zone-description">
                Once you delete a building, all associated units, tenants, and lease records will also be permanently deleted. This action cannot be undone.
            </div>
            <form action="{{ route('buildings.destroy', $building) }}" method="POST" onsubmit="return confirmDelete('{{ $building->name }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    🗑️ Delete Building
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Format phone number
        const phoneField = document.querySelector('input[name="contact_phone"]');
        if (phoneField) {
            phoneField.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) value = value.substring(0, 10);
                
                if (value.length > 0) {
                    const formatted = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                    e.target.value = formatted;
                }
            });
        }
        
        // Auto-format ZIP code
        const zipField = document.querySelector('input[name="zip_code"]');
        if (zipField) {
            zipField.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 9) value = value.substring(0, 9);
                
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5);
                }
                e.target.value = value;
            });
        }

        // Form validation
        const form = document.getElementById('updateForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('error');
                        
                        // Add error message if not exists
                        let errorMsg = field.parentNode.querySelector('.error-message');
                        if (!errorMsg) {
                            errorMsg = document.createElement('div');
                            errorMsg.className = 'error-message';
                            errorMsg.textContent = 'This field is required';
                            field.parentNode.appendChild(errorMsg);
                        }
                    } else {
                        field.classList.remove('error');
                        const errorMsg = field.parentNode.querySelector('.error-message');
                        if (errorMsg && errorMsg.textContent === 'This field is required') {
                            errorMsg.remove();
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    Utilities.showToast('Please fill in all required fields', 'error');
                    
                    // Scroll to first error
                    const firstError = form.querySelector('.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }
    });

    // Delete confirmation
    function confirmDelete(buildingName) {
        return confirm(`Are you sure you want to delete "${buildingName}"? This action cannot be undone.`);
    }
</script>
@endpush