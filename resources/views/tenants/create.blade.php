@extends('layouts.app')

@section('title', 'Add New Tenant - Utility Wise')

@push('styles')
<style>
    /* PAGE HEADER */
    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-subtitle {
        color: #6c757d;
        margin-top: 5px;
    }

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

    /* SELECT STYLING */
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
        padding-right: 40px;
    }

    /* OPTGROUP STYLING */
    optgroup {
        font-weight: 600;
        color: #2c3e50;
        font-size: 13px;
    }

    optgroup option {
        font-weight: normal;
        padding-left: 20px;
    }

    optgroup[label^="✅"] {
        color: #28a745;
    }

    optgroup[label^="❌"] {
        color: #dc3545;
    }

    optgroup[label^="⚠️"] {
        color: #856404;
    }

    /* INPUT GROUP FOR CURRENCY */
    .input-group {
        display: flex;
        align-items: center;
    }

    .input-group-text {
        background: #f8f9fa;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-right: none;
        border-radius: 4px 0 0 4px;
        color: #666;
        font-size: 14px;
    }

    .input-group .form-control {
        border-radius: 0 4px 4px 0;
        flex: 1;
    }

    /* INFO CARD FOR PRE-SELECTED UNIT */
    .info-card {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
        border: 1px solid #e9ecef;
        margin-bottom: 20px;
    }

    .info-card-title {
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-card-content {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .info-card-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #2c3e50;
    }

    /* STATUS BADGE */
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-available {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-occupied {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
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

    /* CHECKBOX STYLING */
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

    /* FILE INPUT STYLING */
    input[type="file"].form-control {
        padding: 8px 15px;
    }

    /* RESPONSIVE */
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

        .info-card-content {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">👤 Add New Tenant</h1>
            <p class="page-subtitle">Fill in the details below to add a new tenant to your portfolio</p>
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

        <form action="{{ route('tenants.store') }}" method="POST" enctype="multipart/form-data" id="tenantForm">
            @csrf

            <!-- Hidden fields for pre-selected values from query parameters -->
            @if(request()->has('building_id'))
                <input type="hidden" name="building_id" value="{{ request('building_id') }}" id="preselected_building">
            @endif
            @if(request()->has('unit_id'))
                <input type="hidden" name="unit_id" value="{{ request('unit_id') }}" id="preselected_unit">
            @endif

            <!-- Unit Assignment Info - Shown if pre-selected -->
            @if(request()->has('unit_id') && isset($selectedUnit))
            <div class="info-card" id="preselectedUnitCard">
                <div class="info-card-title">
                    🏢 PRE-SELECTED UNIT
                    <span class="status-badge status-{{ $selectedUnit->status === 'occupied' ? 'occupied' : 'available' }}" 
                          style="margin-left: auto;" 
                          id="preselectedUnitStatus">
                        {{ ucfirst($selectedUnit->status) }}
                    </span>
                </div>
                <div class="info-card-content">
                    <div class="info-card-item">
                        <span>🏠</span>
                        <span><strong>Unit:</strong> {{ $selectedUnit->unit_number }}</span>
                    </div>
                    <div class="info-card-item">
                        <span>📍</span>
                        <span><strong>Building:</strong> {{ $selectedBuilding->name ?? $selectedUnit->building->name }}</span>
                    </div>
                    <div class="info-card-item">
                        <span>💰</span>
                        <span><strong>Monthly Rent:</strong> ₱{{ number_format($selectedUnit->monthly_rent, 0) }}</span>
                    </div>
                    <div class="info-card-item">
                        <span>📐</span>
                        <span><strong>Unit Type:</strong> {{ $selectedUnit->unit_type_label ?? ucfirst($selectedUnit->unit_type) }}</span>
                    </div>
                </div>
                
                <!-- Current Tenant Info (if occupied) -->
                <div id="preselectedUnitTenantInfo" style="margin-top: 15px; display: none;"></div>
            </div>
            @endif

            <!-- Personal Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>👤</div>
                    <span>Personal Information</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required 
                               class="form-control @error('first_name') error @enderror" placeholder="Enter first name">
                        @error('first_name') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required 
                               class="form-control @error('last_name') error @enderror" placeholder="Enter last name">
                        @error('last_name') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               class="form-control @error('email') error @enderror" placeholder="tenant@example.com">
                        @error('email') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required 
                               class="form-control @error('phone') error @enderror" placeholder="+63 XXX XXX XXXX">
                        @error('phone') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Alternate Phone</label>
                        <input type="text" name="alternate_phone" value="{{ old('alternate_phone') }}" 
                               class="form-control @error('alternate_phone') error @enderror" placeholder="Optional">
                        @error('alternate_phone') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" 
                               class="form-control @error('date_of_birth') error @enderror">
                        @error('date_of_birth') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Emergency Contact Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>🆘</div>
                    <span>Emergency Contact</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" 
                               class="form-control @error('emergency_contact_name') error @enderror" placeholder="Full name">
                        @error('emergency_contact_name') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" 
                               class="form-control @error('emergency_contact_phone') error @enderror" placeholder="Phone number">
                        @error('emergency_contact_phone') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="emergency_contact_relation" value="{{ old('emergency_contact_relation') }}" 
                           class="form-control @error('emergency_contact_relation') error @enderror" placeholder="e.g., Spouse, Parent, Sibling">
                    @error('emergency_contact_relation') <div class="error-message">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Unit Assignment Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>🏢</div>
                    <span>Unit Assignment</span>
                </div>

                @if(!request()->has('unit_id') || !isset($selectedUnit))
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Building <span class="required">*</span></label>
                        <select name="building_id" id="building_id" class="form-control @error('building_id') error @enderror" required>
                            <option value="">Select Building</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}" {{ $selectedBuilding?->id == $building->id ? 'selected' : '' }}>
                                    {{ $building->name }} - {{ $building->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('building_id') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-control @error('unit_id') error @enderror" required>
                            <option value="">Select Building First</option>
                        </select>
                        <div id="unit_status" class="validation-status" style="display: none;"></div>
                        <div class="help-text" id="unit_help">Select a building first to load available units</div>
                        @error('unit_id') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>
                @else
                <!-- Read-only display for pre-selected unit -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Building <span class="required">*</span></label>
                        <input type="text" value="{{ $selectedBuilding->name ?? $selectedUnit->building->name }}" 
                               class="form-control" readonly disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <input type="text" value="Unit {{ $selectedUnit->unit_number }} - {{ $selectedUnit->unit_type_label ?? ucfirst($selectedUnit->unit_type) }}" 
                               class="form-control" readonly disabled>
                    </div>
                </div>
                
                <!-- Hidden but active unit validation for pre-selected -->
                <input type="hidden" id="preselected_unit_id" value="{{ $selectedUnit->id }}">
                <input type="hidden" id="preselected_building_id" value="{{ $selectedBuilding->id ?? $selectedUnit->building_id }}">
                <div id="preselected_unit_validation" class="validation-status" style="margin-top: 10px;"></div>
                @endif
            </div>

            <!-- Lease Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>📄</div>
                    <span>Lease Information</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Lease Start Date <span class="required">*</span></label>
                        <input type="date" name="lease_start_date" id="lease_start_date" 
                               value="{{ old('lease_start_date', date('Y-m-d')) }}" required 
                               class="form-control @error('lease_start_date') error @enderror">
                        @error('lease_start_date') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lease End Date <span class="required">*</span></label>
                        <input type="date" name="lease_end_date" id="lease_end_date" 
                               value="{{ old('lease_end_date', date('Y-m-d', strtotime('+1 year'))) }}" required 
                               class="form-control @error('lease_end_date') error @enderror">
                        @error('lease_end_date') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monthly Rent <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="monthly_rent" id="monthly_rent" 
                                   value="{{ old('monthly_rent', $selectedUnit->monthly_rent ?? '') }}" 
                                   step="0.01" min="0" required 
                                   class="form-control @error('monthly_rent') error @enderror">
                        </div>
                        @error('monthly_rent') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Security Deposit</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="security_deposit" value="{{ old('security_deposit') }}" 
                                   step="0.01" min="0" class="form-control @error('security_deposit') error @enderror" placeholder="Optional">
                        </div>
                        @error('security_deposit') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Lease Status <span class="required">*</span></label>
                        <select name="lease_status" id="lease_status" class="form-control @error('lease_status') error @enderror" required>
                            <option value="active" {{ old('lease_status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('lease_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ old('lease_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="terminated" {{ old('lease_status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        @error('lease_status') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Number of Occupants</label>
                        <input type="number" name="number_of_occupants" value="{{ old('number_of_occupants', 1) }}" 
                               min="1" class="form-control @error('number_of_occupants') error @enderror">
                        @error('number_of_occupants') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Identification Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>🪪</div>
                    <span>Identification</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ID Type</label>
                        <select name="id_type" class="form-control @error('id_type') error @enderror">
                            <option value="">Select ID Type</option>
                            <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                            <option value="drivers_license" {{ old('id_type') == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                            <option value="national_id" {{ old('id_type') == 'national_id' ? 'selected' : '' }}>National ID</option>
                            <option value="postal_id" {{ old('id_type') == 'postal_id' ? 'selected' : '' }}>Postal ID</option>
                            <option value="voters_id" {{ old('id_type') == 'voters_id' ? 'selected' : '' }}>Voter's ID</option>
                            <option value="other" {{ old('id_type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('id_type') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Government ID</label>
                        <input type="file" name="government_id" accept=".jpg,.jpeg,.png,.pdf" class="form-control @error('government_id') error @enderror">
                        <div class="help-text">Max 5MB. Accepted: JPG, PNG, PDF</div>
                        @error('government_id') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Employment Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>💼</div>
                    <span>Employment & Income</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="occupation" value="{{ old('occupation') }}" 
                               class="form-control @error('occupation') error @enderror" placeholder="e.g., Software Engineer">
                        @error('occupation') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Employer</label>
                        <input type="text" name="employer" value="{{ old('employer') }}" 
                               class="form-control @error('employer') error @enderror" placeholder="Company name">
                        @error('employer') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Annual Income</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" name="annual_income" value="{{ old('annual_income') }}" 
                               step="0.01" min="0" class="form-control @error('annual_income') error @enderror" placeholder="Optional">
                    </div>
                    @error('annual_income') <div class="error-message">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Lease Agreement Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>📎</div>
                    <span>Lease Agreement</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Lease Agreement (PDF/DOC)</label>
                    <input type="file" name="lease_agreement" accept=".pdf,.doc,.docx" class="form-control @error('lease_agreement') error @enderror">
                    <div class="help-text">Max 10MB. Accepted: PDF, DOC, DOCX</div>
                    @error('lease_agreement') <div class="error-message">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Notes Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>📝</div>
                    <span>Notes</span>
                </div>

                <div class="form-group">
                    <textarea name="notes" rows="4" class="form-control form-textarea @error('notes') error @enderror" 
                              placeholder="Enter any additional notes about the tenant...">{{ old('notes') }}</textarea>
                    @error('notes') <div class="error-message">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Status Section -->
            <div class="form-section" style="border-bottom: none;">
                <div class="section-title">
                    <div>⚙️</div>
                    <span>Account Status</span>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span style="font-weight: 500; color: #2c3e50;">Active Account</span>
                    </label>
                    <div class="help-text" style="margin-left: 26px;">Inactive tenants won't appear in active tenant lists</div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ request()->has('unit_id') ? route('units.show', request('unit_id')) : route('tenants.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    ➕ Create Tenant
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // State variables
        let checkTimeout;
        let isCheckingUnit = false;
        let isUnitValid = true;
        let currentUnitId = null;
        let currentTenantName = null;
        const submitBtn = document.getElementById('submitBtn');
        
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

        // Function to check if unit already has an active tenant
        function checkUnitAvailability(unitId, buildingId) {
            if (!unitId || !buildingId) {
                return;
            }

            // Show checking state
            const unitStatus = document.getElementById('unit_status') || document.getElementById('preselected_unit_validation');
            if (unitStatus) {
                unitStatus.style.display = 'flex';
                unitStatus.className = 'validation-status info';
                unitStatus.innerHTML = '<span class="spinner"></span> Checking unit availability...';
            }

            // Clear previous timeout
            if (checkTimeout) {
                clearTimeout(checkTimeout);
            }

            // Debounce the check
            checkTimeout = setTimeout(() => {
                fetch(`/tenants/check-unit-availability?unit_id=${unitId}&building_id=${buildingId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.has_active_tenant) {
                            // Unit is occupied
                            if (unitStatus) {
                                unitStatus.className = 'validation-status error';
                                unitStatus.innerHTML = `❌ This unit is currently occupied by <strong>${data.tenant_name}</strong> until ${data.lease_end_date}. Please select a different unit.`;
                            }
                            
                            // Update unit select if it exists
                            const unitSelect = document.getElementById('unit_id');
                            if (unitSelect) {
                                const selectedOption = unitSelect.options[unitSelect.selectedIndex];
                                if (selectedOption) {
                                    selectedOption.disabled = true;
                                    unitSelect.value = '';
                                }
                            }
                            
                            // Disable submit button
                            if (submitBtn) {
                                submitBtn.disabled = true;
                            }
                            
                            // Update unit help text
                            const unitHelp = document.getElementById('unit_help');
                            if (unitHelp) {
                                unitHelp.innerHTML = '❌ This unit is already occupied';
                                unitHelp.className = 'help-text help-text-error';
                            }
                            
                            isUnitValid = false;
                        } else if (data.status && data.status !== 'vacant' && data.status !== 'ready') {
                            // Unit is not available (maintenance, etc.)
                            if (unitStatus) {
                                unitStatus.className = 'validation-status warning';
                                unitStatus.innerHTML = `⚠️ This unit is currently <strong>${data.status}</strong>. It may not be ready for occupancy.`;
                            }
                            
                            // Allow submission but warn
                            if (submitBtn) {
                                submitBtn.disabled = false;
                            }
                            
                            const unitHelp = document.getElementById('unit_help');
                            if (unitHelp) {
                                unitHelp.innerHTML = `⚠️ Unit status: ${data.status}`;
                                unitHelp.className = 'help-text help-text-warning';
                            }
                            
                            isUnitValid = true;
                        } else {
                            // Unit is available
                            if (unitStatus) {
                                unitStatus.className = 'validation-status success';
                                unitStatus.innerHTML = '✅ This unit is available for new tenant!';
                            }
                            
                            // Enable submit button
                            if (submitBtn) {
                                submitBtn.disabled = false;
                            }
                            
                            const unitHelp = document.getElementById('unit_help');
                            if (unitHelp) {
                                unitHelp.innerHTML = '✅ Unit is available';
                                unitHelp.className = 'help-text help-text-success';
                            }
                            
                            isUnitValid = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error checking unit:', error);
                        if (unitStatus) {
                            unitStatus.className = 'validation-status warning';
                            unitStatus.innerHTML = '⚠️ Could not verify unit availability. Please proceed with caution.';
                        }
                        
                        // Allow submission but warn
                        if (submitBtn) {
                            submitBtn.disabled = false;
                        }
                        
                        isUnitValid = true;
                    });
            }, 500);
        }

        // Handle pre-selected unit check
        @if(request()->has('unit_id') && isset($selectedUnit))
            const preselectedUnitId = document.getElementById('preselected_unit_id')?.value;
            const preselectedBuildingId = document.getElementById('preselected_building_id')?.value;
            const preselectedUnitStatus = document.getElementById('preselectedUnitStatus');
            const preselectedUnitInfo = document.getElementById('preselectedUnitTenantInfo');
            
            if (preselectedUnitId && preselectedBuildingId) {
                fetch(`/tenants/check-unit-availability?unit_id=${preselectedUnitId}&building_id=${preselectedBuildingId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.has_active_tenant) {
                            preselectedUnitStatus.className = 'status-badge status-occupied';
                            preselectedUnitStatus.textContent = 'OCCUPIED';
                            
                            preselectedUnitInfo.style.display = 'block';
                            preselectedUnitInfo.className = 'validation-status error';
                            preselectedUnitInfo.innerHTML = `❌ This unit is currently occupied by <strong>${data.tenant_name}</strong> until ${data.lease_end_date}. Please select a different unit.`;
                            
                            if (submitBtn) {
                                submitBtn.disabled = true;
                            }
                        } else if (data.status && data.status !== 'vacant' && data.status !== 'ready') {
                            preselectedUnitStatus.className = 'status-badge status-warning';
                            preselectedUnitStatus.textContent = data.status.toUpperCase();
                            
                            preselectedUnitInfo.style.display = 'block';
                            preselectedUnitInfo.className = 'validation-status warning';
                            preselectedUnitInfo.innerHTML = `⚠️ This unit is currently <strong>${data.status}</strong>. It may not be ready for occupancy.`;
                            
                            if (submitBtn) {
                                submitBtn.disabled = false;
                            }
                        } else {
                            preselectedUnitStatus.className = 'status-badge status-available';
                            preselectedUnitStatus.textContent = 'AVAILABLE';
                            
                            preselectedUnitInfo.style.display = 'block';
                            preselectedUnitInfo.className = 'validation-status success';
                            preselectedUnitInfo.innerHTML = '✅ This unit is available for new tenant!';
                            
                            if (submitBtn) {
                                submitBtn.disabled = false;
                            }
                        }
                    });
            }
        @endif

        // Load units when building is selected (for non-preselected)
        const buildingSelect = document.getElementById('building_id');
        const unitSelect = document.getElementById('unit_id');
        const monthlyRentInput = document.getElementById('monthly_rent');
        const unitStatus = document.getElementById('unit_status');
        const unitHelp = document.getElementById('unit_help');

        function loadUnits(buildingId) {
            if (!buildingId) {
                if (unitSelect) {
                    unitSelect.innerHTML = '<option value="">Select Building First</option>';
                    unitSelect.disabled = true;
                }
                if (unitStatus) {
                    unitStatus.style.display = 'none';
                }
                if (unitHelp) {
                    unitHelp.innerHTML = 'Select a building first to load available units';
                    unitHelp.className = 'help-text';
                }
                return;
            }
            
            unitSelect.disabled = true;
            unitSelect.innerHTML = '<option value="">Loading units...</option>';
            
            fetch(`/tenants/get-units/${buildingId}`)
                .then(response => response.json())
                .then(units => {
                    if (unitSelect) {
                        unitSelect.innerHTML = '<option value="">Select Unit</option>';
                        unitSelect.disabled = false;
                        
                        if (units.length === 0) {
                            unitSelect.innerHTML += '<option value="" disabled>No units available in this building</option>';
                            if (unitStatus) {
                                unitStatus.style.display = 'flex';
                                unitStatus.className = 'validation-status warning';
                                unitStatus.innerHTML = '⚠️ No units available in this building';
                            }
                            return;
                        }
                        
                        // Separate units by availability for better UX
                        const availableUnits = units.filter(u => u.is_available === true);
                        const occupiedUnits = units.filter(u => u.has_active_tenant === true);
                        const otherUnits = units.filter(u => !u.is_available && !u.has_active_tenant);
                        
                        // Add Available Units group
                        if (availableUnits.length > 0) {
                            const optgroup = document.createElement('optgroup');
                            optgroup.label = '✅ AVAILABLE UNITS';
                            availableUnits.forEach(unit => {
                                const option = document.createElement('option');
                                option.value = unit.id;
                                option.dataset.rent = unit.monthly_rent;
                                option.dataset.status = unit.status;
                                option.dataset.hasTenant = unit.has_active_tenant ? 'true' : 'false';
                                option.dataset.available = 'true';
                                option.textContent = `Unit ${unit.unit_number} - ${getUnitTypeLabel(unit)} (₱${Number(unit.monthly_rent).toLocaleString()})`;
                                optgroup.appendChild(option);
                            });
                            unitSelect.appendChild(optgroup);
                        }
                        
                        // Add Occupied Units group (disabled)
                        if (occupiedUnits.length > 0) {
                            const optgroup = document.createElement('optgroup');
                            optgroup.label = '❌ OCCUPIED UNITS (Not Available)';
                            occupiedUnits.forEach(unit => {
                                const option = document.createElement('option');
                                option.value = unit.id;
                                option.dataset.rent = unit.monthly_rent;
                                option.dataset.status = unit.status;
                                option.dataset.hasTenant = 'true';
                                option.dataset.available = 'false';
                                option.disabled = true;
                                option.textContent = `Unit ${unit.unit_number} - ${getUnitTypeLabel(unit)} (₱${Number(unit.monthly_rent).toLocaleString()}) - OCCUPIED`;
                                optgroup.appendChild(option);
                            });
                            unitSelect.appendChild(optgroup);
                        }
                        
                        // Add Other Units group (maintenance, etc.)
                        if (otherUnits.length > 0) {
                            const optgroup = document.createElement('optgroup');
                            optgroup.label = '⚠️ OTHER UNITS';
                            otherUnits.forEach(unit => {
                                const option = document.createElement('option');
                                option.value = unit.id;
                                option.dataset.rent = unit.monthly_rent;
                                option.dataset.status = unit.status;
                                option.dataset.hasTenant = unit.has_active_tenant ? 'true' : 'false';
                                option.dataset.available = 'false';
                                option.disabled = unit.status !== 'vacant' && unit.status !== 'ready';
                                option.textContent = `Unit ${unit.unit_number} - ${getUnitTypeLabel(unit)} (${unit.status.toUpperCase()}) - ₱${Number(unit.monthly_rent).toLocaleString()}`;
                                optgroup.appendChild(option);
                            });
                            unitSelect.appendChild(optgroup);
                        }
                        
                        // Check if there was a previously selected unit
                        @if(old('unit_id'))
                            unitSelect.value = '{{ old('unit_id') }}';
                            if (unitSelect.value) {
                                unitSelect.dispatchEvent(new Event('change'));
                            }
                        @endif
                    }
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    if (unitSelect) {
                        unitSelect.innerHTML = '<option value="">Error loading units</option>';
                        unitSelect.disabled = false;
                    }
                    if (unitStatus) {
                        unitStatus.style.display = 'flex';
                        unitStatus.className = 'validation-status error';
                        unitStatus.innerHTML = '❌ Failed to load units. Please try again.';
                    }
                });
        }
        
        // Building change event
        if (buildingSelect) {
            buildingSelect.addEventListener('change', function() {
                loadUnits(this.value);
                if (monthlyRentInput) {
                    monthlyRentInput.value = ''; // Clear rent when building changes
                }
                if (unitStatus) {
                    unitStatus.style.display = 'none';
                }
            });
            
            // Trigger initial load if building is selected
            if (buildingSelect.value) {
                loadUnits(buildingSelect.value);
            }
        }
        
        // Unit change event - check availability and auto-fill rent
        if (unitSelect) {
            unitSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const unitId = selectedOption.value;
                    const buildingId = buildingSelect?.value;
                    const rent = selectedOption.dataset.rent;
                    const status = selectedOption.dataset.status;
                    const hasTenant = selectedOption.dataset.hasTenant === 'true';
                    const isAvailable = selectedOption.dataset.available === 'true';
                    
                    if (rent && monthlyRentInput) {
                        monthlyRentInput.value = rent;
                    }
                    
                    // Update status display based on unit occupancy
                    if (unitStatus) {
                        unitStatus.style.display = 'flex';
                        
                        if (hasTenant) {
                            unitStatus.className = 'validation-status error';
                            unitStatus.innerHTML = '❌ This unit is already occupied by another tenant. Please select a different unit.';
                            
                            if (unitHelp) {
                                unitHelp.innerHTML = '❌ Unit is occupied - not available';
                                unitHelp.className = 'help-text help-text-error';
                            }
                            
                            // Disable submit button
                            if (submitBtn) {
                                submitBtn.disabled = true;
                            }
                        } else if (status !== 'vacant' && status !== 'ready') {
                            unitStatus.className = 'validation-status warning';
                            unitStatus.innerHTML = `⚠️ This unit is currently <strong>${status}</strong>. It may not be ready for occupancy.`;
                            
                            if (unitHelp) {
                                unitHelp.innerHTML = `⚠️ Unit status: ${status}`;
                                unitHelp.className = 'help-text help-text-warning';
                            }
                            
                            // Enable submit button but warn
                            if (submitBtn) {
                                submitBtn.disabled = false;
                            }
                        } else {
                            unitStatus.className = 'validation-status success';
                            unitStatus.innerHTML = '✅ This unit is available for new tenant!';
                            
                            if (unitHelp) {
                                unitHelp.innerHTML = '✅ Unit is available';
                                unitHelp.className = 'help-text help-text-success';
                            }
                            
                            // Enable submit button
                            if (submitBtn) {
                                submitBtn.disabled = false;
                            }
                        }
                    }
                    
                    // Also check via API for more detailed info (current tenant name, etc.)
                    if (unitId && buildingId && hasTenant) {
                        fetch(`/tenants/check-unit-availability?unit_id=${unitId}&building_id=${buildingId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.has_active_tenant && unitStatus) {
                                    unitStatus.innerHTML = `❌ This unit is currently occupied by <strong>${data.tenant_name}</strong> until ${data.lease_end_date}. Please select a different unit.`;
                                }
                            })
                            .catch(error => console.error('Error checking unit details:', error));
                    }
                } else {
                    if (unitStatus) {
                        unitStatus.style.display = 'none';
                    }
                    if (unitHelp) {
                        unitHelp.innerHTML = 'Select a unit to check availability';
                        unitHelp.className = 'help-text';
                    }
                    if (monthlyRentInput) {
                        monthlyRentInput.value = '';
                    }
                    
                    // Enable submit button if no unit selected
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                }
            });
        }
        
        // Auto-calculate lease end date when start date changes (1 year default)
        const startDateInput = document.getElementById('lease_start_date');
        const endDateInput = document.getElementById('lease_end_date');
        
        if (startDateInput && endDateInput) {
            startDateInput.addEventListener('change', function() {
                if (this.value && !endDateInput.value) {
                    const date = new Date(this.value);
                    date.setFullYear(date.getFullYear() + 1);
                    endDateInput.value = date.toISOString().split('T')[0];
                }
            });
        }
        
        // Format phone number
        const phoneField = document.querySelector('input[name="phone"]');
        if (phoneField) {
            phoneField.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.substring(0, 11);
                
                if (value.length > 0) {
                    if (value.length <= 3) {
                        e.target.value = value;
                    } else if (value.length <= 6) {
                        e.target.value = value.substring(0, 3) + '-' + value.substring(3);
                    } else {
                        e.target.value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
                    }
                }
            });
        }

        // Form submission validation
        const form = document.getElementById('tenantForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!isUnitValid) {
                    e.preventDefault();
                    Utilities.showToast('Cannot create tenant: This unit already has an active tenant', 'error');
                }
            });
        }
    });
</script>
@endpush