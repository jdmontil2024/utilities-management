@extends('layouts.app')

@section('title', 'Edit Unit ' . $unit->unit_number . ' - Utility Wise')

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
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
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

    .btn-primary:disabled {
        background: #95a5a6;
        cursor: not-allowed;
        opacity: 0.7;
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
        padding: 12px;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        transition: all 0.2s;
    }

    .feature-checkbox:hover {
        background: #e9ecef;
    }

    /* Currency input styling */
    .currency-input {
        display: flex;
        align-items: center;
    }

    .currency-prefix {
        padding: 10px 15px;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-right: none;
        border-radius: 4px 0 0 4px;
        font-family: 'Inter', sans-serif;
    }

    .currency-input .form-control {
        border-radius: 0 4px 4px 0;
    }

    /* Error styling */
    .form-control.error {
        border-color: #e74c3c;
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

        .checkbox-group {
            grid-template-columns: 1fr;
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
            <h1 class="page-title">🚪 Edit Unit {{ $unit->unit_number }}</h1>
            <p class="page-subtitle">Update the details for this unit</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Display validation errors -->
        @if ($errors->any())
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #f5c6cb;">
                <strong>Please fix the following errors:</strong>
                <ul style="margin-top: 10px; margin-bottom: 0; padding-left: 20px;">
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
                    <div>🏢</div>
                    <span>Building Selection</span>
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
                    <div>📋</div>
                    <span>Unit Information</span>
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
                    <div>🎯</div>
                    <span>Features & Amenities</span>
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
                    <div>💰</div>
                    <span>Financial Information</span>
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
                    <div>📝</div>
                    <span>Additional Information</span>
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
                    Utilities.showToast('Please fill in all required fields', 'error');
                    return;
                }
                
                // Disable submit button to prevent double submission
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Updating...';
                    submitBtn.style.opacity = '0.7';
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