@extends('layouts.app')

@section('title', '✏️ Edit ' . ($tenant->full_name ?? 'Tenant') . ' - Utility Wise')

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

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .status-expired {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-terminated {
        background: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
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

    .btn-success {
        background: #27ae60;
        color: white;
    }

    .btn-success:hover {
        background: #219955;
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

    .btn-outline {
        background: transparent;
        border: 1px solid #4a5568;
        color: #4a5568;
    }

    .btn-outline:hover {
        background: #4a5568;
        color: white;
    }

    .btn-outline-secondary {
        background: transparent;
        border: 1px solid #ddd;
        color: #666;
        padding: 8px 16px;
        font-size: 13px;
    }

    .btn-outline-secondary:hover {
        background: #f8f9fa;
        border-color: #999;
        text-decoration: none;
        color: #666;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    /* CURRENCY INPUT */
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
        color: #666;
        font-weight: 500;
    }

    .currency-input .form-control {
        border-radius: 0 4px 4px 0;
    }

    /* FILE INPUT */
    .file-input-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .file-input-group .form-control {
        flex: 1;
    }

    .current-file {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 15px;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
        color: #666;
        text-decoration: none;
        white-space: nowrap;
    }

    .current-file:hover {
        background: #e9ecef;
        color: #333;
        text-decoration: none;
    }

    /* ALERTS */
    .alert {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        border-left: 4px solid;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left-color: #e74c3c;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left-color: #ffc107;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left-color: #28a745;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border-left-color: #17a2b8;
    }

    .alert ul {
        margin-top: 10px;
        margin-bottom: 0;
        padding-left: 20px;
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

    /* OCCUPANT ROW */
    .occupant-row {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 10px;
    }

    .occupant-row .form-row {
        grid-template-columns: 1fr 0.8fr 0.8fr 50px;
        align-items: center;
    }

    /* REASSIGNMENT BADGE */
    .reassignment-badge {
        background: #e8f4fc;
        border: 1px solid #d1e4ff;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #2c3e50;
    }

    .reassignment-badge i {
        font-size: 20px;
    }

    /* UNIT INFO CARD */
    .unit-info-card {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
        margin-top: 10px;
        border: 1px solid #dee2e6;
    }

    .unit-info-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .unit-info-details {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        font-size: 13px;
    }

    /* UNIT DETAILS PREVIEW */
    #unitDetailsContent {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        font-size: 13px;
    }

    /* LOADING INDICATOR */
    #unitLoading {
        display: none;
        color: #3498db;
        margin-top: 5px;
        font-size: 12px;
    }

    /* UNIT CHANGE MESSAGE */
    #unitChangeMessage {
        display: none;
        color: #e67e22;
        margin-top: 5px;
        font-size: 12px;
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

        .file-input-group {
            flex-direction: column;
            align-items: stretch;
        }

        .current-file {
            justify-content: center;
        }

        .occupant-row .form-row {
            grid-template-columns: 1fr;
        }

        .unit-info-details,
        #unitDetailsContent {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 class="page-title">
                    <span>✏️</span> Edit Tenant & Reassign
                </h1>
                <p class="page-subtitle">Update information for {{ $tenant->full_name ?? '' }} and change their assigned building/unit</p>
            </div>
            <div>
                <span class="status-badge status-{{ $tenant->lease_status ?? 'active' }}">
                    {{ ucfirst($tenant->lease_status ?? 'active') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Reassignment Notice -->
        <div class="reassignment-badge">
            <div style="font-size: 24px;">🔄</div>
            <div>
                <strong>Tenant Reassignment</strong><br>
                <small>Changing the building or unit will automatically update the status of both the old and new units. The tenant's lease information will be preserved.</small>
            </div>
        </div>

        <!-- Current Assignment Info -->
        <div class="unit-info-card">
            <div class="unit-info-title">
                <span>📍</span> Current Assignment
            </div>
            <div class="unit-info-details">
                <div><strong>Building:</strong> {{ $tenant->building->name ?? 'N/A' }}</div>
                <div><strong>Unit:</strong> {{ $tenant->unit->unit_number ?? 'N/A' }}</div>
                <div><strong>Rent:</strong> ₱{{ number_format($tenant->monthly_rent ?? 0, 2) }}</div>
            </div>
        </div>

        <!-- Error Alerts -->
        @if($errors->any())
            <div class="alert alert-danger">
                <strong style="display: block; margin-bottom: 8px;">⚠️ Please fix the following errors:</strong>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- UPDATE FORM -->
        <form action="{{ route('tenants.update', $tenant) }}" method="POST" enctype="multipart/form-data" id="updateForm">
            @csrf
            @method('PUT')
            
            <!-- Building & Unit Assignment Section - REASSIGNMENT FIXED -->
            <div class="form-section">
                <div class="section-title">
                    <div>🔄</div>
                    <span>Reassign to New Building/Unit</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Select New Building <span class="required">*</span></label>
                        <select name="building_id" id="building_id" class="form-control @error('building_id') error @enderror" required>
                            <option value="">-- Choose Building to Reassign --</option>
                            @foreach($buildings ?? [] as $building)
                                <option value="{{ $building->id }}" 
                                        {{ old('building_id', $tenant->building_id) == $building->id ? 'selected' : '' }}>
                                    {{ $building->name }} - {{ $building->address ?? '' }}
                                    @if($building->id == $tenant->building_id)
                                        (Current Building)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('building_id')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <div class="help-text">
                            Current building: <strong>{{ $tenant->building->name ?? 'N/A' }}</strong>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Select New Unit <span class="required">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-control @error('unit_id') error @enderror" required>
                            <option value="">-- First Select a Building --</option>
                            @if($tenant->unit_id)
                                <option value="{{ $tenant->unit_id }}" selected data-rent="{{ $tenant->monthly_rent }}" data-current="true">
                                    Current: Unit {{ $tenant->unit->unit_number ?? 'N/A' }} - 
                                    {{ $tenant->unit->bedrooms ?? 0 }}br/{{ $tenant->unit->bathrooms ?? 0 }}ba - 
                                    ₱{{ number_format($tenant->monthly_rent ?? 0, 2) }}/month
                                </option>
                            @endif
                        </select>
                        @error('unit_id')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <div id="unitLoading" style="display: none; color: #3498db; margin-top: 5px; font-size: 12px;">
                            ⏳ Loading available units...
                        </div>
                        <div id="unitChangeMessage" style="display: none; color: #e67e22; margin-top: 5px; font-size: 12px;">
                            ℹ️ Unit changed! Monthly rent will be updated automatically.
                        </div>
                    </div>
                </div>

                <!-- Unit Details Preview (shown when unit selected) -->
                <div id="unitDetailsPreview" style="display: none; margin-top: 15px; padding: 15px; background: #e8f4fc; border-radius: 6px; border: 1px solid #d1e4ff;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <span style="font-size: 20px;">🏠</span>
                        <span style="font-weight: 600; color: #2c3e50;">Selected Unit Details</span>
                    </div>
                    <div id="unitDetailsContent" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 13px;"></div>
                </div>
            </div>
            
            <!-- Personal Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>👤</div>
                    <span>Personal Information</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') error @enderror" required 
                               value="{{ old('first_name', $tenant->first_name) }}"
                               placeholder="e.g., John">
                        @error('first_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') error @enderror" required 
                               value="{{ old('last_name', $tenant->last_name) }}"
                               placeholder="e.g., Doe">
                        @error('last_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') error @enderror" 
                               value="{{ old('date_of_birth', $tenant->date_of_birth?->format('Y-m-d')) }}">
                        @error('date_of_birth')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') error @enderror" required 
                               value="{{ old('email', $tenant->email) }}"
                               placeholder="e.g., john.doe@example.com">
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="tel" name="phone" class="form-control @error('phone') error @enderror" required 
                               value="{{ old('phone', $tenant->phone) }}"
                               placeholder="e.g., (555) 123-4567">
                        @error('phone')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Alternate Phone</label>
                        <input type="tel" name="alternate_phone" class="form-control @error('alternate_phone') error @enderror" 
                               value="{{ old('alternate_phone', $tenant->alternate_phone) }}"
                               placeholder="e.g., (555) 987-6543">
                        @error('alternate_phone')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
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
                        <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') error @enderror" 
                               value="{{ old('emergency_contact_name', $tenant->emergency_contact_name) }}"
                               placeholder="e.g., Jane Doe">
                        @error('emergency_contact_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="tel" name="emergency_contact_phone" class="form-control @error('emergency_contact_phone') error @enderror" 
                               value="{{ old('emergency_contact_phone', $tenant->emergency_contact_phone) }}"
                               placeholder="e.g., (555) 555-5555">
                        @error('emergency_contact_phone')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="emergency_contact_relation" class="form-control @error('emergency_contact_relation') error @enderror" 
                               value="{{ old('emergency_contact_relation', $tenant->emergency_contact_relation) }}" 
                               placeholder="e.g., Spouse, Parent">
                        @error('emergency_contact_relation')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Employment & Income Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>💼</div>
                    <span>Employment & Income</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="occupation" class="form-control @error('occupation') error @enderror" 
                               value="{{ old('occupation', $tenant->occupation) }}"
                               placeholder="e.g., Software Engineer">
                        @error('occupation')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Employer</label>
                        <input type="text" name="employer" class="form-control @error('employer') error @enderror" 
                               value="{{ old('employer', $tenant->employer) }}"
                               placeholder="e.g., Tech Corp">
                        @error('employer')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Annual Income</label>
                        <div class="currency-input">
                            <span class="currency-prefix">₱</span>
                            <input type="number" name="annual_income" class="form-control @error('annual_income') error @enderror" 
                                   value="{{ old('annual_income', $tenant->annual_income) }}" 
                                   step="0.01" min="0"
                                   placeholder="0.00">
                        </div>
                        @error('annual_income')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
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
                        <input type="date" name="lease_start_date" id="lease_start_date" class="form-control @error('lease_start_date') error @enderror" required 
                               value="{{ old('lease_start_date', $tenant->safe_lease_start_date?->format('Y-m-d') ?? '') }}">
                        @error('lease_start_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Lease End Date <span class="required">*</span></label>
                        <input type="date" name="lease_end_date" id="lease_end_date" class="form-control @error('lease_end_date') error @enderror" required 
                               value="{{ old('lease_end_date', $tenant->safe_lease_end_date?->format('Y-m-d') ?? '') }}">
                        @error('lease_end_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monthly Rent <span class="required">*</span></label>
                        <div class="currency-input">
                            <span class="currency-prefix">₱</span>
                            <input type="number" name="monthly_rent" id="monthly_rent" class="form-control @error('monthly_rent') error @enderror" required 
                                   value="{{ old('monthly_rent', $tenant->monthly_rent) }}" 
                                   step="0.01" min="0"
                                   placeholder="0.00">
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
                                   value="{{ old('security_deposit', $tenant->security_deposit ?? 0) }}" 
                                   step="0.01" min="0"
                                   placeholder="0.00">
                        </div>
                        @error('security_deposit')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Lease Status <span class="required">*</span></label>
                        <select name="lease_status" id="lease_status" class="form-control @error('lease_status') error @enderror" required>
                            <option value="">Select Status</option>
                            <option value="active" {{ old('lease_status', $tenant->lease_status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('lease_status', $tenant->lease_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ old('lease_status', $tenant->lease_status) == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="terminated" {{ old('lease_status', $tenant->lease_status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        @error('lease_status')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Number of Occupants <span class="required">*</span></label>
                        <input type="number" name="number_of_occupants" class="form-control @error('number_of_occupants') error @enderror" required 
                               value="{{ old('number_of_occupants', $tenant->number_of_occupants ?? 1) }}" 
                               min="1" max="20"
                               placeholder="e.g., 2">
                        @error('number_of_occupants')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Additional Occupants</label>
                        <textarea name="additional_occupants" id="additional_occupants" 
                                  class="form-control form-textarea @error('additional_occupants') error @enderror" 
                                  rows="3" 
                                  placeholder='[{"name": "Jane Doe", "relation": "spouse", "age": 30}]'>{{ old('additional_occupants', is_array($tenant->additional_occupants) ? json_encode($tenant->additional_occupants, JSON_PRETTY_PRINT) : $tenant->additional_occupants) }}</textarea>
                        @error('additional_occupants')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <div class="help-text">
                            <button type="button" class="btn btn-outline-secondary" onclick="openOccupantModal()" style="margin-top: 10px;">
                                ➕ Edit Occupants
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Identification Documents Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>🪪</div>
                    <span>Identification</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">ID Type</label>
                    <select name="id_type" class="form-control @error('id_type') error @enderror">
                        <option value="">Select ID Type</option>
                        <option value="passport" {{ old('id_type', $tenant->id_type) == 'passport' ? 'selected' : '' }}>Passport</option>
                        <option value="drivers_license" {{ old('id_type', $tenant->id_type) == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                        <option value="national_id" {{ old('id_type', $tenant->id_type) == 'national_id' ? 'selected' : '' }}>National ID</option>
                        <option value="postal_id" {{ old('id_type', $tenant->id_type) == 'postal_id' ? 'selected' : '' }}>Postal ID</option>
                        <option value="voters_id" {{ old('id_type', $tenant->id_type) == 'voters_id' ? 'selected' : '' }}>Voter's ID</option>
                        <option value="other" {{ old('id_type', $tenant->id_type) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('id_type')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Government ID</label>
                    <div class="file-input-group">
                        <input type="file" name="government_id" class="form-control @error('government_id') error @enderror" 
                               accept=".jpg,.jpeg,.png,.pdf">
                        @if($tenant->government_id)
                            <a href="{{ Storage::url($tenant->government_id) }}" target="_blank" class="current-file">
                                📄 View Current
                            </a>
                        @endif
                    </div>
                    @error('government_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <div class="help-text">
                        Accepted formats: JPG, PNG, PDF (Max 2MB)
                        @if($tenant->government_id)
                            <br>Current file: <strong>{{ basename($tenant->government_id) }}</strong>
                        @endif
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Lease Agreement (PDF)</label>
                    <div class="file-input-group">
                        <input type="file" name="lease_agreement" class="form-control @error('lease_agreement') error @enderror" 
                               accept=".pdf">
                        @if($tenant->lease_agreement_path)
                            <a href="{{ Storage::url($tenant->lease_agreement_path) }}" target="_blank" class="current-file">
                                📄 View Current
                            </a>
                        @endif
                    </div>
                    @error('lease_agreement')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <div class="help-text">
                        Upload signed lease agreement (PDF, Max 5MB)
                        @if($tenant->lease_agreement_path)
                            <br>Current file: <strong>{{ basename($tenant->lease_agreement_path) }}</strong>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Notes Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>📝</div>
                    <span>Notes</span>
                </div>
                
                <div class="form-group">
                    <textarea name="notes" class="form-control form-textarea @error('notes') error @enderror" 
                              rows="4" 
                              placeholder="Additional notes about the tenant, special requirements, preferences, etc.">{{ old('notes', $tenant->notes) }}</textarea>
                    @error('notes')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" 
                               style="width: 18px; height: 18px; cursor: pointer;"
                               {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}>
                        <span style="font-weight: 500;">Active Tenant</span>
                    </label>
                    <div class="help-text">
                        Uncheck to mark as inactive. This will affect the unit's status and lease validity.
                        @if($tenant->lease_status === 'active' && $tenant->is_active)
                            <br><span style="color: #856404;">⚠️ This tenant currently has an active lease.</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    🔄 Reassign & Update Tenant
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Additional Occupants Modal -->
<div id="occupantModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 8px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; padding: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
            <h3 style="font-size: 18px; font-weight: 600; color: #2c3e50; margin: 0; display: flex; align-items: center; gap: 10px;">
                <span>👥</span> Edit Additional Occupants
            </h3>
            <button onclick="closeOccupantModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0 8px;">&times;</button>
        </div>
        
        <div id="occupantsList"></div>
        
        <button type="button" class="btn btn-outline-secondary" onclick="addOccupantRow()" style="margin: 10px 0 20px;">
            ➕ Add Occupant
        </button>
        
        <div class="help-text" style="margin-bottom: 20px;">
            Only occupants with names and relations will be saved.
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 20px; border-top: 1px solid #eee;">
            <button type="button" class="btn btn-secondary" onclick="closeOccupantModal()">
                Cancel
            </button>
            <button type="button" class="btn btn-primary" onclick="saveOccupants()">
                Save Occupants
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // BUILDING AND UNIT REASSIGNMENT - COMPLETE FIX
    document.addEventListener('DOMContentLoaded', function() {
        const buildingSelect = document.getElementById('building_id');
        const unitSelect = document.getElementById('unit_id');
        const unitLoading = document.getElementById('unitLoading');
        const monthlyRent = document.getElementById('monthly_rent');
        const unitChangeMessage = document.getElementById('unitChangeMessage');
        const unitDetailsPreview = document.getElementById('unitDetailsPreview');
        const unitDetailsContent = document.getElementById('unitDetailsContent');
        
        const currentUnitId = '{{ $tenant->unit_id }}';
        const currentBuildingId = '{{ $tenant->building_id }}';
        const currentRent = '{{ $tenant->monthly_rent }}';
        
        // Store current unit option
        let currentUnitOption = null;
        if (currentUnitId) {
            currentUnitOption = {
                id: '{{ $tenant->unit_id }}',
                unit_number: '{{ $tenant->unit->unit_number ?? "N/A" }}',
                bedrooms: '{{ $tenant->unit->bedrooms ?? 0 }}',
                bathrooms: '{{ $tenant->unit->bathrooms ?? 0 }}',
                monthly_rent: '{{ $tenant->monthly_rent }}',
                floor: '{{ $tenant->unit->floor ?? "N/A" }}',
                area: '{{ $tenant->unit->area ?? "N/A" }}'
            };
        }

        // Function to load units for selected building
        function loadUnits(buildingId) {
            // Clear unit select
            unitSelect.innerHTML = '';
            unitSelect.disabled = true;
            
            if (!buildingId) {
                unitSelect.innerHTML = '<option value="">-- First Select a Building --</option>';
                unitSelect.disabled = true;
                unitLoading.style.display = 'none';
                unitDetailsPreview.style.display = 'none';
                unitChangeMessage.style.display = 'none';
                return;
            }

            unitSelect.innerHTML = '<option value="">Loading units...</option>';
            unitLoading.style.display = 'block';
            unitDetailsPreview.style.display = 'none';
            
            // Fetch units via AJAX
            fetch(`/api/buildings/${buildingId}/units?available_only=0`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(units => {
                    unitSelect.innerHTML = '';
                    unitSelect.disabled = false;
                    unitLoading.style.display = 'none';
                    
                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = '-- Select Unit to Reassign --';
                    unitSelect.appendChild(defaultOption);
                    
                    // Add current unit option if this is the current building
                    if (buildingId == currentBuildingId && currentUnitOption) {
                        const currentOption = document.createElement('option');
                        currentOption.value = currentUnitOption.id;
                        currentOption.selected = true;
                        currentOption.dataset.rent = currentUnitOption.monthly_rent;
                        currentOption.dataset.current = 'true';
                        currentOption.dataset.unitNumber = currentUnitOption.unit_number;
                        currentOption.dataset.bedrooms = currentUnitOption.bedrooms;
                        currentOption.dataset.bathrooms = currentUnitOption.bathrooms;
                        currentOption.dataset.floor = currentUnitOption.floor;
                        currentOption.dataset.area = currentUnitOption.area;
                        currentOption.textContent = `CURRENT UNIT: ${currentUnitOption.unit_number} - ${currentUnitOption.bedrooms}br/${currentUnitOption.bathrooms}ba - ₱${parseFloat(currentUnitOption.monthly_rent).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}/month`;
                        unitSelect.appendChild(currentOption);
                        
                        // Show current unit details
                        showUnitDetails(currentUnitOption);
                    }
                    
                    // Add separator if we have current unit and other units
                    if (buildingId == currentBuildingId && currentUnitOption && units.length > 0) {
                        const separator = document.createElement('option');
                        separator.disabled = true;
                        separator.textContent = '────────── OTHER AVAILABLE UNITS ──────────';
                        separator.style.background = '#f8f9fa';
                        separator.style.color = '#6c757d';
                        unitSelect.appendChild(separator);
                    }
                    
                    // Add available units (vacant or available for reassignment)
                    let hasAvailableUnits = false;
                    if (units && units.length > 0) {
                        units.forEach(unit => {
                            // Skip the current unit if we're in the same building
                            if (unit.id == currentUnitId && buildingId == currentBuildingId) {
                                return;
                            }
                            
                            hasAvailableUnits = true;
                            const option = document.createElement('option');
                            option.value = unit.id;
                            option.dataset.rent = unit.monthly_rent;
                            option.dataset.unitNumber = unit.unit_number;
                            option.dataset.bedrooms = unit.bedrooms || 0;
                            option.dataset.bathrooms = unit.bathrooms || 0;
                            option.dataset.floor = unit.floor || 'N/A';
                            option.dataset.area = unit.area || 'N/A';
                            option.dataset.status = unit.status;
                            
                            let statusText = '';
                            if (unit.status === 'occupied') {
                                statusText = ' 🔴 OCCUPIED';
                            } else if (unit.status === 'vacant') {
                                statusText = ' 🟢 VACANT';
                            } else if (unit.status === 'maintenance') {
                                statusText = ' 🟡 MAINTENANCE';
                            }
                            
                            option.textContent = `Unit ${unit.unit_number} - ${unit.bedrooms ?? 0}br/${unit.bathrooms ?? 0}ba - ₱${parseFloat(unit.monthly_rent).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}/month${statusText}`;
                            
                            // Disable occupied units with a warning
                            if (unit.status === 'occupied' && unit.id != currentUnitId) {
                                option.disabled = true;
                                option.style.color = '#999';
                                option.textContent += ' (Already Occupied)';
                            }
                            
                            unitSelect.appendChild(option);
                        });
                    }
                    
                    // If no units available
                    if (!hasAvailableUnits && unitSelect.options.length === 1) {
                        const noUnitsOption = document.createElement('option');
                        noUnitsOption.value = '';
                        noUnitsOption.textContent = 'No available units in this building';
                        noUnitsOption.disabled = true;
                        unitSelect.appendChild(noUnitsOption);
                    }
                    
                    // Trigger change to show details of selected unit
                    unitSelect.dispatchEvent(new Event('change'));
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    unitSelect.innerHTML = '<option value="">Error loading units. Please try again.</option>';
                    unitSelect.disabled = false;
                    unitLoading.style.display = 'none';
                    
                    if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                        Utilities.showToast('Failed to load units. Please refresh the page.', 'error');
                    }
                });
        }

        // Function to show unit details
        function showUnitDetails(unit) {
            if (!unit) return;
            
            unitDetailsContent.innerHTML = `
                <div><strong>Unit Number:</strong> ${unit.unit_number || 'N/A'}</div>
                <div><strong>Bedrooms:</strong> ${unit.bedrooms || 0}</div>
                <div><strong>Bathrooms:</strong> ${unit.bathrooms || 0}</div>
                <div><strong>Monthly Rent:</strong> ₱${parseFloat(unit.monthly_rent).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                <div><strong>Floor:</strong> ${unit.floor || 'N/A'}</div>
                <div><strong>Area:</strong> ${unit.area || 'N/A'} sqm</div>
            `;
            unitDetailsPreview.style.display = 'block';
        }
        
        // Building select change handler
        buildingSelect.addEventListener('change', function() {
            loadUnits(this.value);
        });
        
        // Unit select change handler
        unitSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                // Update rent if the selected unit has rent data
                if (selectedOption.dataset.rent) {
                    monthlyRent.value = selectedOption.dataset.rent;
                    unitChangeMessage.style.display = 'block';
                }
                
                // Show unit details
                if (selectedOption.dataset.unitNumber) {
                    showUnitDetails({
                        unit_number: selectedOption.dataset.unitNumber,
                        bedrooms: selectedOption.dataset.bedrooms,
                        bathrooms: selectedOption.dataset.bathrooms,
                        monthly_rent: selectedOption.dataset.rent,
                        floor: selectedOption.dataset.floor,
                        area: selectedOption.dataset.area
                    });
                } else if (selectedOption.value == currentUnitId) {
                    // Show current unit details
                    showUnitDetails(currentUnitOption);
                    unitChangeMessage.style.display = 'none';
                    monthlyRent.value = currentRent;
                }
            } else {
                unitDetailsPreview.style.display = 'none';
                unitChangeMessage.style.display = 'none';
            }
        });
        
        // Trigger change event if building already selected
        if (buildingSelect.value) {
            loadUnits(buildingSelect.value);
        }
        
        // Date validation
        const startDate = document.getElementById('lease_start_date');
        const endDate = document.getElementById('lease_end_date');
        
        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
                if (endDate.value && endDate.value < this.value) {
                    endDate.value = this.value;
                }
            });
        }
    });

    // Form submission handler
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('updateForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validate that a unit is selected
                const unitSelect = document.getElementById('unit_id');
                if (!unitSelect.value) {
                    e.preventDefault();
                    if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                        Utilities.showToast('Please select a unit to reassign the tenant.', 'error');
                    }
                    return false;
                }
                
                // Disable button on submit
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '⏳ Reassigning...';
                    submitBtn.style.opacity = '0.7';
                }
            });
        }
    });

    // Additional Occupants Management
    let occupantCount = 0;

    function openOccupantModal() {
        const modal = document.getElementById('occupantModal');
        loadExistingOccupants();
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeOccupantModal() {
        const modal = document.getElementById('occupantModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function loadExistingOccupants() {
        const occupantsList = document.getElementById('occupantsList');
        const existingData = document.getElementById('additional_occupants').value;
        
        occupantsList.innerHTML = '';
        occupantCount = 0;
        
        if (existingData) {
            try {
                const occupants = JSON.parse(existingData);
                if (Array.isArray(occupants) && occupants.length > 0) {
                    occupants.forEach(occupant => {
                        addOccupantRow(occupant.name || '', occupant.relation || '', occupant.age || '');
                    });
                }
            } catch (e) {
                console.error('Error parsing occupants:', e);
            }
        }
        
        if (occupantCount === 0) {
            addOccupantRow();
        }
    }

    function addOccupantRow(name = '', relation = '', age = '') {
        occupantCount++;
        const occupantId = `occupant_${occupantCount}`;
        const occupantsList = document.getElementById('occupantsList');
        
        const row = document.createElement('div');
        row.id = occupantId;
        row.className = 'occupant-row';
        row.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 0.8fr 0.8fr 50px; gap: 15px; align-items: center;">
                <div>
                    <input type="text" class="form-control" placeholder="Full Name" value="${escapeHtml(name)}">
                </div>
                <div>
                    <select class="form-control">
                        <option value="">Relation</option>
                        <option value="spouse" ${relation === 'spouse' ? 'selected' : ''}>Spouse</option>
                        <option value="child" ${relation === 'child' ? 'selected' : ''}>Child</option>
                        <option value="parent" ${relation === 'parent' ? 'selected' : ''}>Parent</option>
                        <option value="sibling" ${relation === 'sibling' ? 'selected' : ''}>Sibling</option>
                        <option value="roommate" ${relation === 'roommate' ? 'selected' : ''}>Roommate</option>
                        <option value="other" ${relation === 'other' ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                <div>
                    <input type="number" class="form-control" placeholder="Age" value="${escapeHtml(age)}" min="0" max="120">
                </div>
                <div style="display: flex; align-items: center;">
                    <button type="button" class="btn btn-danger" onclick="removeOccupantRow('${occupantId}')" style="padding: 6px 12px;">🗑️</button>
                </div>
            </div>
        `;
        
        occupantsList.appendChild(row);
    }

    function removeOccupantRow(rowId) {
        document.getElementById(rowId).remove();
    }

    function saveOccupants() {
        const rows = document.querySelectorAll('.occupant-row');
        const occupants = [];
        
        rows.forEach(row => {
            const inputs = row.querySelectorAll('input, select');
            const name = inputs[0]?.value?.trim();
            const relation = inputs[1]?.value;
            const age = inputs[2]?.value;
            
            if (name && relation) {
                occupants.push({
                    name: name,
                    relation: relation,
                    age: age ? parseInt(age) : null
                });
            }
        });
        
        document.getElementById('additional_occupants').value = JSON.stringify(occupants, null, 2);
        closeOccupantModal();
        
        if (typeof Utilities !== 'undefined' && Utilities.showToast) {
            Utilities.showToast('Occupants saved successfully', 'success');
        }
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('occupantModal');
        if (event.target == modal) {
            closeOccupantModal();
        }
    }
</script>

<!-- Ensure API endpoint exists - fallback to this if API route not set up -->
<script>
    if (!window.originalFetch) {
        window.originalFetch = window.fetch;
        window.fetch = function(url, options) {
            if (url.includes('/api/buildings/') && url.includes('/units')) {
                // Extract building ID from URL
                const match = url.match(/\/buildings\/(\d+)\/units/);
                if (match) {
                    const buildingId = match[1];
                    // Redirect to a route that exists in web.php
                    return window.originalFetch(`/buildings/${buildingId}/units-json`, options);
                }
            }
            return window.originalFetch(url, options);
        };
    }
</script>
@endpush