@extends('layouts.app')

@section('title', 'Create New Unit - Utility Wise')

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
        transition: all 0.3s;
        font-family: 'Inter', sans-serif;
    }

    .form-control:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    .form-control.error {
        border-color: #e74c3c;
        background-color: #fff8f8;
    }

    .form-control.valid {
        border-color: #28a745;
        background-color: #f8fff8;
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

    .btn-primary:hover:not(:disabled) {
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
        transition: color 0.3s;
    }

    .help-text-success {
        color: #28a745;
    }

    .help-text-warning {
        color: #856404;
    }

    .help-text-error {
        color: #e74c3c;
    }

    .error-message {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 5px;
    }

    /* VALIDATION STATUS */
    .validation-status {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 13px;
    }

    .validation-status.warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .validation-status.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .validation-status.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .validation-status.info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* LOADING SPINNER */
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(0,0,0,.1);
        border-radius: 50%;
        border-top-color: #3498db;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* EXISTING UNITS LIST */
    .existing-units-list {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        max-height: 200px;
        overflow-y: auto;
    }

    .existing-unit-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        border-bottom: 1px solid #dee2e6;
    }

    .existing-unit-item:last-child {
        border-bottom: none;
    }

    .unit-details {
        font-size: 12px;
        color: #666;
    }

    .badge-deleted {
        background: #6c757d;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
    }

    .badge-occupied {
        background: #dc3545;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
    }

    .badge-available {
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
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
            <h1 class="page-title">🚪 Create New Unit</h1>
            <p class="page-subtitle">Fill in the details below to add a new unit</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="validation-status error" style="margin-bottom: 20px;">
                <strong>⚠️ Please fix the following errors:</strong>
                <ul style="margin-top: 10px; margin-bottom: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="validation-status error" style="margin-bottom: 20px;">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('units.store') }}" method="POST" id="createForm">
            @csrf
            
            <!-- Building Selection -->
            <div class="form-section">
                <div class="section-title">
                    <div>🏢</div>
                    <span>Building Selection</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Building <span class="required">*</span></label>
                    <select name="building_id" id="building_id" class="form-control @error('building_id') error @enderror" required>
                        <option value="">Select Building</option>
                        @foreach($buildings as $building)
                            <option value="{{ $building->id }}" {{ old('building_id', $selectedBuilding->id ?? '') == $building->id ? 'selected' : '' }}>
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
                        <input type="text" 
                               name="unit_number" 
                               id="unit_number" 
                               class="form-control @error('unit_number') error @enderror" 
                               required 
                               value="{{ old('unit_number') }}"
                               placeholder="e.g., 101, A-201, Suite 300"
                               autocomplete="off">
                        <div id="unit_number_status" class="validation-status" style="display: none;"></div>
                        <div class="help-text" id="unit_number_help">
                            Enter a unique unit number for this building
                        </div>
                        @error('unit_number')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Unit Name (Optional)</label>
                        <input type="text" name="unit_name" class="form-control @error('unit_name') error @enderror" 
                               value="{{ old('unit_name') }}"
                               placeholder="e.g., Garden View, Penthouse Suite">
                        @error('unit_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Existing Units in this Building -->
                <div id="existing_units_container" style="display: none;">
                    <div class="existing-units-list">
                        <strong style="display: block; margin-bottom: 10px;">📋 Existing units in this building:</strong>
                        <div id="existing_units_list"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unit Type <span class="required">*</span></label>
                        <select name="unit_type" id="unit_type" class="form-control @error('unit_type') error @enderror" required>
                            <option value="">Select Type</option>
                            <option value="studio" {{ old('unit_type') == 'studio' ? 'selected' : '' }}>Studio</option>
                            <option value="1br" {{ old('unit_type') == '1br' ? 'selected' : '' }}>1 Bedroom</option>
                            <option value="2br" {{ old('unit_type') == '2br' ? 'selected' : '' }}>2 Bedrooms</option>
                            <option value="3br" {{ old('unit_type') == '3br' ? 'selected' : '' }}>3 Bedrooms</option>
                            <option value="commercial" {{ old('unit_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                            <option value="other" {{ old('unit_type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('unit_type')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Floor Level <span class="required">*</span></label>
                        <input type="number" name="floor" class="form-control @error('floor') error @enderror" required 
                               value="{{ old('floor', 1) }}"
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
                               value="{{ old('area') }}"
                               placeholder="e.g., 750.50" min="0" max="100000">
                        @error('area')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control @error('status') error @enderror" required>
                            <option value="">Select Status</option>
                            <option value="vacant" {{ old('status') == 'vacant' ? 'selected' : '' }}>Vacant</option>
                            <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                            <option value="renovation" {{ old('status') == 'renovation' ? 'selected' : '' }}>Under Renovation</option>
                            <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="ready" {{ old('status') == 'ready' ? 'selected' : '' }}>Ready for Occupancy</option>
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
                               value="{{ old('bedrooms', 0) }}"
                               placeholder="e.g., 1, 2, 3" min="0" max="20">
                        @error('bedrooms')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bathrooms <span class="required">*</span></label>
                        <input type="number" name="bathrooms" class="form-control @error('bathrooms') error @enderror" step="0.5" required
                               value="{{ old('bathrooms', 1) }}"
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
                        @endphp
                        
                        @foreach($features as $value => $label)
                        <div class="feature-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="features[]" value="{{ $value }}"
                                       {{ is_array(old('features')) && in_array($value, old('features')) ? 'checked' : '' }}>
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
                        @endphp
                        
                        @foreach($amenities as $value => $label)
                        <div class="feature-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="amenities[]" value="{{ $value }}"
                                       {{ is_array(old('amenities')) && in_array($value, old('amenities')) ? 'checked' : '' }}>
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
                                   value="{{ old('monthly_rent') }}"
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
                                   value="{{ old('security_deposit', 0) }}"
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
                                   value="{{ old('parking_fee', 0) }}"
                                   placeholder="e.g., 50.00" step="0.01" min="0" max="1000">
                        </div>
                        @error('parking_fee')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Year Renovated (Optional)</label>
                        <input type="number" name="year_renovated" class="form-control @error('year_renovated') error @enderror" 
                               value="{{ old('year_renovated') }}"
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
                               value="{{ old('available_date') }}"
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
                              placeholder="Describe the unit, its features, views, etc.">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Internal Notes (Optional)</label>
                    <textarea name="notes" class="form-control form-textarea @error('notes') error @enderror" rows="3"
                              placeholder="Any internal notes about the unit">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <div class="help-text">These notes are only visible to property managers</div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ isset($selectedBuilding) ? route('buildings.show', $selectedBuilding) : route('units.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Save Unit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Unit create form loaded');
        
        // State variables
        let checkTimeout;
        let isUnitNumberValid = true;
        const submitBtn = document.getElementById('submitBtn');
        const buildingSelect = document.getElementById('building_id');
        const unitNumberInput = document.getElementById('unit_number');
        const unitNumberStatus = document.getElementById('unit_number_status');
        const unitNumberHelp = document.getElementById('unit_number_help');
        const existingUnitsContainer = document.getElementById('existing_units_container');
        const existingUnitsList = document.getElementById('existing_units_list');
        
        // Set today as min date for available date
        const availableDateField = document.querySelector('input[name="available_date"]');
        if (availableDateField && !availableDateField.value) {
            const today = new Date().toISOString().split('T')[0];
            availableDateField.min = today;
        }

        // Function to get unit type label
        function getUnitTypeLabel(unit) {
            const typeMap = {
                'studio': 'Studio',
                '1br': '1 Bedroom',
                '2br': '2 Bedrooms',
                '3br': '3 Bedrooms',
                'commercial': 'Commercial',
                'other': 'Other'
            };
            return typeMap[unit.unit_type] || unit.unit_type || 'Standard';
        }

        // Function to load existing units for a building
        function loadExistingUnits(buildingId) {
            if (!buildingId) {
                existingUnitsContainer.style.display = 'none';
                return;
            }

            fetch(`/units/by-building/${buildingId}`)
                .then(response => response.json())
                .then(units => {
                    if (units.length > 0) {
                        existingUnitsContainer.style.display = 'block';
                        let html = '';
                        
                        units.forEach(unit => {
                            const statusClass = unit.status === 'occupied' ? 'badge-occupied' : 
                                               (unit.status === 'vacant' || unit.status === 'ready') ? 'badge-available' : 'badge-deleted';
                            
                            html += `
                                <div class="existing-unit-item">
                                    <div>
                                        <strong>${unit.unit_number}</strong>
                                        ${unit.unit_name ? ` - ${unit.unit_name}` : ''}
                                        <div class="unit-details">
                                            ${unit.unit_type_label} • ${unit.bedrooms}br • ${unit.bathrooms}ba • ${unit.area} sq ft
                                        </div>
                                    </div>
                                    <div>
                                        <span class="${statusClass}">${unit.status}</span>
                                        ${unit.deleted_at ? '<span class="badge-deleted" style="margin-left: 5px;">Deleted</span>' : ''}
                                    </div>
                                </div>
                            `;
                        });
                        
                        existingUnitsList.innerHTML = html;
                    } else {
                        existingUnitsContainer.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading existing units:', error);
                });
        }

        // Function to check for duplicate unit number
        function checkDuplicateUnitNumber() {
            const buildingId = buildingSelect.value;
            const unitNumber = unitNumberInput.value.trim();
            
            console.log('Checking duplicate:', { buildingId, unitNumber });
            
            if (!buildingId || !unitNumber) {
                unitNumberStatus.style.display = 'none';
                unitNumberInput.classList.remove('error', 'valid');
                unitNumberHelp.innerHTML = 'Enter a unique unit number for this building';
                unitNumberHelp.className = 'help-text';
                isUnitNumberValid = true;
                if (submitBtn) submitBtn.disabled = false;
                return;
            }

            // Show checking state
            unitNumberStatus.style.display = 'flex';
            unitNumberStatus.className = 'validation-status info';
            unitNumberStatus.innerHTML = '<span class="spinner"></span> Checking availability...';
            unitNumberInput.classList.remove('error', 'valid');
            
            // Clear previous timeout
            if (checkTimeout) {
                clearTimeout(checkTimeout);
            }
            
            // Debounce the check
            checkTimeout = setTimeout(() => {
                fetch(`/units/check-duplicate?building_id=${buildingId}&unit_number=${encodeURIComponent(unitNumber)}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response data:', data);
                        
                        if (data.exists) {
                            // Unit number already exists
                            unitNumberStatus.className = 'validation-status error';
                            unitNumberStatus.innerHTML = `❌ Unit number "${unitNumber}" is already taken. Please choose a different number.`;
                            unitNumberInput.classList.add('error');
                            unitNumberInput.classList.remove('valid');
                            unitNumberHelp.innerHTML = '❌ This unit number is already taken';
                            unitNumberHelp.className = 'help-text help-text-error';
                            isUnitNumberValid = false;
                            if (submitBtn) submitBtn.disabled = true;
                            
                        } else if (data.soft_deleted) {
                            // Unit number was used but deleted
                            unitNumberStatus.className = 'validation-status warning';
                            unitNumberStatus.innerHTML = `⚠️ Unit number "${unitNumber}" was previously used but is deleted. You can reuse it.`;
                            unitNumberInput.classList.add('valid');
                            unitNumberInput.classList.remove('error');
                            unitNumberHelp.innerHTML = '⚠️ Previously used unit number (deleted)';
                            unitNumberHelp.className = 'help-text help-text-warning';
                            isUnitNumberValid = true;
                            if (submitBtn) submitBtn.disabled = false;
                            
                        } else {
                            // Unit number is available
                            unitNumberStatus.className = 'validation-status success';
                            unitNumberStatus.innerHTML = `✅ Unit number "${unitNumber}" is available!`;
                            unitNumberInput.classList.add('valid');
                            unitNumberInput.classList.remove('error');
                            unitNumberHelp.innerHTML = '✅ This unit number is available';
                            unitNumberHelp.className = 'help-text help-text-success';
                            isUnitNumberValid = true;
                            if (submitBtn) submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error checking duplicate:', error);
                        // Show warning but allow submission
                        unitNumberStatus.className = 'validation-status warning';
                        unitNumberStatus.innerHTML = '⚠️ Could not verify availability. Please proceed with caution.';
                        unitNumberHelp.innerHTML = 'Unable to verify unit number availability';
                        unitNumberHelp.className = 'help-text help-text-warning';
                        isUnitNumberValid = true;
                        if (submitBtn) submitBtn.disabled = false;
                    });
            }, 500); // 500ms debounce
        }

        // Building change event - load existing units
        if (buildingSelect) {
            buildingSelect.addEventListener('change', function() {
                loadExistingUnits(this.value);
                // Trigger duplicate check if unit number is already entered
                if (unitNumberInput.value.trim()) {
                    checkDuplicateUnitNumber();
                } else {
                    unitNumberStatus.style.display = 'none';
                }
            });

            // Load existing units on page load if building is pre-selected
            if (buildingSelect.value) {
                loadExistingUnits(buildingSelect.value);
            }
        }

        // Unit number input event - check for duplicates
        if (unitNumberInput) {
            unitNumberInput.addEventListener('input', checkDuplicateUnitNumber);
            
            // Trigger initial check if both building and unit number have values
            if (buildingSelect && buildingSelect.value && unitNumberInput.value) {
                checkDuplicateUnitNumber();
            }
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
        const form = document.getElementById('createForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submitted');
                
                // Check if unit number is valid
                if (!isUnitNumberValid) {
                    e.preventDefault();
                    Utilities.showToast('Unit number already exists in this building. Please choose a different number.', 'error');
                    return;
                }
                
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
                    submitBtn.innerHTML = 'Saving...';
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
        const form = document.getElementById('createForm');
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