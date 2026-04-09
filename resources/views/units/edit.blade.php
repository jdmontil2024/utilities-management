@extends('layouts.app')

@section('title', 'Edit Unit ' . $unit->unit_number . ' - Utility Wise')

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
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.75rem;
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
    
    /* CURRENCY INPUT */
    .currency-input {
        display: flex;
        align-items: center;
    }
    
    .currency-prefix {
        padding: 0.6rem 0.75rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-right: none;
        border-radius: 6px 0 0 6px;
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    
    .currency-input .form-control {
        border-radius: 0 6px 6px 0;
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
    
    .btn:hover:not(:disabled) {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
        transform: translateY(-1px);
    }
    
    .btn-primary {
        background: var(--accent-emerald);
        border-color: var(--accent-emerald);
        color: white;
    }
    
    .btn-primary:hover:not(:disabled) {
        background: #0d9668;
        border-color: #0d9668;
        color: white;
    }
    
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .btn-secondary {
        background: var(--bg-surface);
        border-color: var(--border-color);
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .dashboard-wrapper { padding: 1rem; }
        .form-container { padding: 1.25rem; }
        .form-row { grid-template-columns: 1fr; gap: 1rem; }
        .form-actions { flex-direction: column; }
        .btn { width: 100%; justify-content: center; }
        .checkbox-group { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Unit {{ $unit->unit_number }}</h1>
            <p class="page-subtitle">Update the details for this unit</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="validation-status error" style="margin-bottom: 1.25rem; display: flex; flex-direction: column; align-items: flex-start;">
                <strong>⚠️ Please fix the following errors:</strong>
                <ul style="margin-top: 0.5rem; margin-bottom: 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('units.update', $unit) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            
            <!-- Building Selection -->
            <div class="form-section">
                <div class="section-title">
                    Building Selection
                </div>
                
                <div class="form-group">
                    <label class="form-label">Building <span class="required">*</span></label>
                    <select name="building_id" class="form-control @error('building_id') error @enderror" required>
                        <option value="">Select Building</option>
                        @foreach($buildings as $building)
                            <option value="{{ $building->id }}" {{ old('building_id', $unit->building_id) == $building->id ? 'selected' : '' }}>
                                {{ $building->name }} - {{ $building->address }}, {{ $building->city }}
                            </option>
                        @endforeach
                    </select>
                    @error('building_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Unit Information -->
            <div class="form-section">
                <div class="section-title">
                    Unit Information
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unit Number <span class="required">*</span></label>
                        <input type="text" name="unit_number" class="form-control @error('unit_number') error @enderror" required 
                               value="{{ old('unit_number', $unit->unit_number) }}"
                               placeholder="e.g., 101, A-201, Suite 300">
                        @error('unit_number')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Unit Name (Optional)</label>
                        <input type="text" name="unit_name" class="form-control @error('unit_name') error @enderror" 
                               value="{{ old('unit_name', $unit->unit_name) }}"
                               placeholder="e.g., Garden View, Penthouse Suite">
                        @error('unit_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unit Type <span class="required">*</span></label>
                        <select name="unit_type" class="form-control @error('unit_type') error @enderror" required>
                            <option value="">Select Type</option>
                            <option value="studio" {{ old('unit_type', $unit->unit_type) == 'studio' ? 'selected' : '' }}>Studio</option>
                            <option value="1br" {{ old('unit_type', $unit->unit_type) == '1br' ? 'selected' : '' }}>1 Bedroom</option>
                            <option value="2br" {{ old('unit_type', $unit->unit_type) == '2br' ? 'selected' : '' }}>2 Bedrooms</option>
                            <option value="3br" {{ old('unit_type', $unit->unit_type) == '3br' ? 'selected' : '' }}>3 Bedrooms</option>
                            <option value="commercial" {{ old('unit_type', $unit->unit_type) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                            <option value="other" {{ old('unit_type', $unit->unit_type) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('unit_type')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Floor Level <span class="required">*</span></label>
                        <input type="number" name="floor" class="form-control @error('floor') error @enderror" required 
                               value="{{ old('floor', $unit->floor) }}"
                               placeholder="e.g., 1, 2, 3" min="-10" max="200">
                        @error('floor')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Size (Square Feet) <span class="required">*</span></label>
                        <input type="number" name="area" class="form-control @error('area') error @enderror" step="0.01" required
                               value="{{ old('area', $unit->area) }}"
                               placeholder="e.g., 750.50" min="0" max="100000">
                        @error('area')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control @error('status') error @enderror" required>
                            <option value="">Select Status</option>
                            <option value="vacant" {{ old('status', $unit->status) == 'vacant' ? 'selected' : '' }}>Vacant</option>
                            <option value="occupied" {{ old('status', $unit->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                            <option value="maintenance" {{ old('status', $unit->status) == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                            <option value="renovation" {{ old('status', $unit->status) == 'renovation' ? 'selected' : '' }}>Under Renovation</option>
                            <option value="reserved" {{ old('status', $unit->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="ready" {{ old('status', $unit->status) == 'ready' ? 'selected' : '' }}>Ready for Occupancy</option>
                        </select>
                        @error('status')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bedrooms <span class="required">*</span></label>
                        <input type="number" name="bedrooms" class="form-control @error('bedrooms') error @enderror" required 
                               value="{{ old('bedrooms', $unit->bedrooms) }}"
                               placeholder="e.g., 1, 2, 3" min="0" max="20">
                        @error('bedrooms')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bathrooms <span class="required">*</span></label>
                        <input type="number" name="bathrooms" class="form-control @error('bathrooms') error @enderror" step="0.5" required
                               value="{{ old('bathrooms', $unit->bathrooms) }}"
                               placeholder="e.g., 1, 1.5, 2" min="0" max="20">
                        @error('bathrooms')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Features & Amenities -->
            <div class="form-section">
                <div class="section-title">
                    Features & Amenities
                </div>
                
                <div class="form-group">
                    <label class="form-label">Unit Features (Optional)</label>
                    <div class="checkbox-group">
                        @php
                            $features = [
                                'balcony' => 'Balcony/Patio',
                                'fireplace' => 'Fireplace',
                                'hardwood' => 'Hardwood Floors',
                                'carpet' => 'Carpet',
                                'central_ac' => 'Central A/C',
                                'washer_dryer' => 'Washer/Dryer',
                                'dishwasher' => 'Dishwasher',
                                'disposal' => 'Garbage Disposal',
                                'microwave' => 'Microwave',
                                'refrigerator' => 'Refrigerator',
                                'oven' => 'Oven/Range',
                                'granite' => 'Granite Countertops',
                                'marble' => 'Marble Bathroom',
                                'walkin_closet' => 'Walk-in Closet',
                                'storage' => 'Extra Storage',
                                'parking' => 'Parking Included',
                                'gym' => 'Gym Access',
                                'pool' => 'Pool Access',
                                'concierge' => 'Concierge',
                                'security' => '24/7 Security',
                                'elevator' => 'Elevator',
                                'wheelchair' => 'Wheelchair Accessible',
                                'pets_allowed' => 'Pets Allowed',
                                'smoking' => 'Smoking Allowed',
                            ];
                            
                            // Get current features as array
                            $currentFeatures = is_array($unit->features) ? $unit->features : (json_decode($unit->features, true) ?: []);
                            $oldFeatures = old('features', $currentFeatures);
                            if (!is_array($oldFeatures)) {
                                $oldFeatures = [];
                            }
                        @endphp
                        
                        @foreach($features as $value => $label)
                        <div class="feature-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="features[]" value="{{ $value }}"
                                       {{ in_array($value, $oldFeatures) ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('features')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amenities (Optional)</label>
                    <div class="checkbox-group">
                        @php
                            $amenities = [
                                'pool' => 'Swimming Pool',
                                'gym' => 'Fitness Center',
                                'parking' => 'Parking',
                                'laundry' => 'Laundry Facilities',
                                'elevator' => 'Elevator',
                                'security' => 'Security System',
                                'concierge' => 'Concierge Service',
                                'clubhouse' => 'Clubhouse',
                                'playground' => 'Playground',
                                'bbq' => 'BBQ Area',
                                'gardens' => 'Gardens',
                                'rooftop' => 'Rooftop Terrace',
                                'business_center' => 'Business Center',
                                'package_lockers' => 'Package Lockers',
                                'pet_area' => 'Pet Area',
                                'bike_storage' => 'Bike Storage',
                                'storage_units' => 'Storage Units',
                            ];
                            
                            // Get current amenities as array
                            $currentAmenities = is_array($unit->amenities) ? $unit->amenities : (json_decode($unit->amenities, true) ?: []);
                            $oldAmenities = old('amenities', $currentAmenities);
                            if (!is_array($oldAmenities)) {
                                $oldAmenities = [];
                            }
                        @endphp
                        
                        @foreach($amenities as $value => $label)
                        <div class="feature-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="amenities[]" value="{{ $value }}"
                                       {{ in_array($value, $oldAmenities) ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('amenities')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Financial Information -->
            <div class="form-section">
                <div class="section-title">
                    Financial Information
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monthly Rent <span class="required">*</span></label>
                        <div class="currency-input">
                            <span class="currency-prefix">₱</span>
                            <input type="number" name="monthly_rent" class="form-control @error('monthly_rent') error @enderror" required 
                                   value="{{ old('monthly_rent', $unit->monthly_rent) }}"
                                   placeholder="e.g., 1500.00" step="0.01" min="0" max="1000000">
                        </div>
                        @error('monthly_rent')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Security Deposit</label>
                        <div class="currency-input">
                            <span class="currency-prefix">₱</span>
                            <input type="number" name="security_deposit" class="form-control @error('security_deposit') error @enderror" 
                                   value="{{ old('security_deposit', $unit->security_deposit) }}"
                                   placeholder="e.g., 1500.00" step="0.01" min="0" max="1000000">
                        </div>
                        @error('security_deposit')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Typically equal to one month's rent</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monthly Parking Fee</label>
                        <div class="currency-input">
                            <span class="currency-prefix">₱</span>
                            <input type="number" name="parking_fee" class="form-control @error('parking_fee') error @enderror" 
                                   value="{{ old('parking_fee', $unit->parking_fee) }}"
                                   placeholder="e.g., 50.00" step="0.01" min="0" max="1000">
                        </div>
                        @error('parking_fee')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Year Renovated (Optional)</label>
                        <input type="number" name="year_renovated" class="form-control @error('year_renovated') error @enderror" 
                               value="{{ old('year_renovated', $unit->year_renovated) }}"
                               placeholder="e.g., 2020" min="1900" max="{{ date('Y') }}">
                        @error('year_renovated')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Available Date (Optional)</label>
                        <input type="date" name="available_date" class="form-control @error('available_date') error @enderror" 
                               value="{{ old('available_date', $unit->available_date) }}"
                               min="{{ date('Y-m-d') }}">
                        @error('available_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <div class="help-text">When the unit will be available for occupancy</div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="form-section">
                <div class="section-title">
                    Additional Information
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea name="description" class="form-control form-textarea @error('description') error @enderror" rows="4"
                              placeholder="Describe the unit, its features, views, etc.">{{ old('description', $unit->description) }}</textarea>
                    @error('description')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Internal Notes (Optional)</label>
                    <textarea name="notes" class="form-control form-textarea @error('notes') error @enderror" rows="3"
                              placeholder="Any internal notes about the unit">{{ old('notes', $unit->notes) }}</textarea>
                    @error('notes')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <div class="help-text">These notes are only visible to property managers</div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('units.show', $unit) }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Update Unit
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .validation-status.error {
        background: rgba(239, 68, 68, 0.1);
        color: var(--accent-red);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Unit edit form loaded');
        
        // Set today as min date for available date
        const availableDateField = document.querySelector('input[name="available_date"]');
        if (availableDateField && !availableDateField.value) {
            const today = new Date().toISOString().split('T')[0];
            availableDateField.min = today;
        }

        // Show all validation errors in a summary
        const errorElements = document.querySelectorAll('.error-message');
        if (errorElements.length > 0) {
            const firstError = document.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Form submission handler
        const form = document.getElementById('editForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submitted');
                
                // Validate required fields
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                        Utilities.showToast('Please fill in all required fields', 'error');
                    } else {
                        alert('Please fill in all required fields');
                    }
                    return;
                }
                
                // Disable submit button to prevent double submission
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Updating...';
                }
            });
        }

        // Debug: Log form data before submission
        window.addEventListener('beforeunload', function(e) {
            if (form && form.querySelector('[name="unit_number"]').value) {
                console.log('Form data before leaving:', {
                    unit_number: form.querySelector('[name="unit_number"]').value,
                    unit_type: form.querySelector('[name="unit_type"]').value,
                    building_id: form.querySelector('[name="building_id"]').value
                });
            }
        });
    });

    // Debug helper
    window.debugForm = function() {
        const form = document.getElementById('editForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        console.log('Form data:', data);
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        return data;
    }
</script>
@endpush