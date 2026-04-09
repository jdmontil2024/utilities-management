@extends('layouts.app')

@section('title', 'Edit ' . $building->name . ' - Utility Wise')

@push('styles')
<style>
    /* MATTE BLACK DESIGN SYSTEM */
    :root {
        --bg-deep: #121212;
        --bg-surface: #181818;
        --bg-card: #1d1d1d;
        --border-color: #2d2d2d;
        --text-main: #ffffff;
        --text-muted: #a0a0a0;
        --accent-emerald: #10b981;
        --accent-red: #ef4444;
        --accent-warning: #f59e0b;
        --accent-blue: #3b82f6;
        --accent-purple: #8b5cf6;
    }

    .dashboard-wrapper { background-color: var(--bg-deep); min-height: 100vh; padding: 2rem; color: var(--text-main); font-family: 'Inter', sans-serif; }
    
    .page-header { 
        padding-bottom: 1.5rem; 
        margin-bottom: 2rem; 
        border-bottom: 1px solid var(--border-color);
    }
    .page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
    .page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

    /* FORM CONTAINER */
    .form-container {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 2rem;
        max-width: 900px;
        margin: 0 auto;
    }

    /* FORM SECTIONS */
    .form-section {
        margin-bottom: 2rem;
    }
    
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }

    /* FORM GROUPS */
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.25rem;
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-label .required {
        color: var(--accent-red);
    }
    
    .form-control {
        width: 100%;
        padding: 0.6rem 0.75rem;
        background: var(--bg-deep);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.85rem;
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--accent-emerald);
    }
    
    .form-control.error {
        border-color: var(--accent-red);
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    
    select.form-control {
        cursor: pointer;
    }
    
    select.form-control option {
        background: var(--bg-deep);
        color: var(--text-main);
    }
    
    /* CHECKBOX GROUP */
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    
    .feature-checkbox {
        background: var(--bg-surface);
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }
    
    .feature-checkbox:hover {
        border-color: var(--accent-emerald);
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.85rem;
        color: var(--text-main);
    }
    
    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--accent-emerald);
    }
    
    /* HELP TEXT & ERRORS */
    .help-text {
        font-size: 0.65rem;
        color: var(--text-muted);
        margin-top: 0.35rem;
    }
    
    .error-message {
        font-size: 0.65rem;
        color: var(--accent-red);
        margin-top: 0.35rem;
    }
    
    /* BUTTONS */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-main);
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
    }
    
    .btn:hover {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
        transform: translateY(-1px);
    }
    
    .btn-primary {
        background: var(--accent-emerald);
        border-color: var(--accent-emerald);
        color: white;
    }
    
    .btn-primary:hover {
        background: #0d9668;
        border-color: #0d9668;
        color: white;
    }
    
    .btn-secondary {
        background: var(--bg-surface);
        border-color: var(--border-color);
    }
    
    .btn-danger {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    
    .btn-danger:hover {
        background: rgba(239, 68, 68, 0.1);
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    /* DANGER ZONE */
    .danger-zone {
        background: rgba(239, 68, 68, 0.05);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 10px;
        padding: 1.5rem;
        margin-top: 2rem;
    }
    
    .danger-zone-title {
        color: var(--accent-red);
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .danger-zone-description {
        color: var(--text-muted);
        font-size: 0.8rem;
        margin-bottom: 1rem;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .dashboard-wrapper { padding: 1rem; }
        .form-container { padding: 1.25rem; }
        .form-row { grid-template-columns: 1fr; gap: 1rem; }
        .form-actions { flex-direction: column; }
        .btn { width: 100%; justify-content: center; }
        .checkbox-group { flex-direction: column; }
        .feature-checkbox { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Building</h1>
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
                    Basic Information
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
                    Address Information
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
                    Building Specifications
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
                    Contact Information
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
                Danger Zone
            </div>
            <div class="danger-zone-description">
                Once you delete a building, all associated units, tenants, and lease records will also be permanently deleted. This action cannot be undone.
            </div>
            <form action="{{ route('buildings.destroy', $building) }}" method="POST" onsubmit="return confirmDelete('{{ $building->name }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    Delete Building
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
                    if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                        Utilities.showToast('Please fill in all required fields', 'error');
                    } else {
                        alert('Please fill in all required fields');
                    }
                    
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