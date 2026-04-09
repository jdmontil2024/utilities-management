@extends('layouts.app')

@section('title', 'Edit ' . ($tenant->full_name ?? 'Tenant') . ' - Utility Wise')

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
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .page-title { font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff; }
    .page-subtitle { color: var(--text-muted); margin-top: 0.25rem; }

    /* STATUS BADGES */
    .status-badge {
        display: inline-block;
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: transparent;
        border: 1px solid;
    }
    .status-active {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    .status-pending {
        border-color: var(--accent-warning);
        color: var(--accent-warning);
    }
    .status-expired {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
    .status-terminated {
        border-color: #6c757d;
        color: #6c757d;
    }

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
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23a0a0a0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 14px;
        padding-right: 2rem;
    }
    
    select.form-control option {
        background: var(--bg-deep);
        color: var(--text-main);
    }
    
    optgroup {
        font-weight: 600;
        color: var(--text-main);
        background: var(--bg-surface);
    }
    
    optgroup option {
        font-weight: normal;
        padding-left: 1rem;
    }
    
    /* CHECKBOX LABEL */
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
    
    /* FILE INPUT */
    .file-input-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    .file-input-group .form-control {
        flex: 1;
        padding: 0.5rem 0.75rem;
    }
    
    .current-file {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-decoration: none;
        white-space: nowrap;
    }
    
    .current-file:hover {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
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
    
    /* ALERTS */
    .alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        margin-bottom: 1.5rem;
        border-left: 3px solid;
    }
    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        border-left-color: var(--accent-red);
        color: var(--accent-red);
    }
    .alert ul {
        margin-top: 0.5rem;
        margin-bottom: 0;
        padding-left: 1.25rem;
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
    
    .btn-outline-secondary {
        background: transparent;
        border-color: var(--border-color);
    }
    
    .btn-outline-secondary:hover {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    /* REASSIGNMENT BADGE */
    .reassignment-badge {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--text-main);
    }
    
    /* UNIT INFO CARD */
    .unit-info-card {
        background: var(--bg-surface);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
    }
    
    .unit-info-title {
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
    }
    
    .unit-info-details {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        font-size: 0.8rem;
    }
    
    .unit-info-details strong {
        color: var(--text-muted);
    }
    
    /* UNIT DETAILS PREVIEW */
    #unitDetailsContent {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        font-size: 0.8rem;
    }
    
    #unitDetailsContent strong {
        color: var(--text-muted);
    }
    
    /* LOADING INDICATOR */
    #unitLoading {
        display: none;
        color: var(--accent-blue);
        margin-top: 0.35rem;
        font-size: 0.7rem;
    }
    
    /* UNIT CHANGE MESSAGE */
    #unitChangeMessage {
        display: none;
        color: var(--accent-warning);
        margin-top: 0.35rem;
        font-size: 0.7rem;
    }
    
    /* OCCUPANT ROW */
    .occupant-row {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
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
        .unit-info-details, #unitDetailsContent { grid-template-columns: 1fr; }
        .file-input-group { flex-direction: column; align-items: stretch; }
        .current-file { justify-content: center; }
        .occupant-row .form-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Tenant & Reassign</h1>
            <p class="page-subtitle">Update information for {{ $tenant->full_name ?? '' }} and change their assigned building/unit</p>
        </div>
        <div>
            <span class="status-badge status-{{ $tenant->lease_status ?? 'active' }}">
                {{ ucfirst($tenant->lease_status ?? 'active') }}
            </span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Reassignment Notice -->
        <div class="reassignment-badge">
            <div style="font-size: 1.5rem;">🔄</div>
            <div>
                <strong>Tenant Reassignment</strong><br>
                <small>Changing the building or unit will automatically update the status of both the old and new units. The tenant's lease information will be preserved.</small>
            </div>
        </div>

        <!-- Current Assignment Info -->
        <div class="unit-info-card">
            <div class="unit-info-title">
                <span></span> Current Assignment
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
                <strong>⚠️ Please fix the following errors:</strong>
                <ul>
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
            
            <!-- Building & Unit Assignment Section -->
            <div class="form-section">
                <div class="section-title">
                    Reassign to New Building/Unit
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
                        <div id="unitLoading" style="display: none;">
                            <span class="spinner"></span> Loading available units...
                        </div>
                        <div id="unitChangeMessage">
                            ℹ️ Unit changed! Monthly rent will be updated automatically.
                        </div>
                    </div>
                </div>

                <!-- Unit Details Preview -->
                <div id="unitDetailsPreview" style="display: none; margin-top: 1rem; padding: 1rem; background: rgba(16, 185, 129, 0.05); border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <span style="font-size: 1rem;">🏠</span>
                        <span style="font-weight: 600; color: var(--text-main);">Selected Unit Details</span>
                    </div>
                    <div id="unitDetailsContent"></div>
                </div>
            </div>
            
            <!-- Personal Information Section -->
            <div class="form-section">
                <div class="section-title">
                    Personal Information
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
                    Emergency Contact
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
                    Employment & Income
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
                    Lease Information
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
                            <button type="button" class="btn btn-outline-secondary" onclick="openOccupantModal()" style="margin-top: 0.5rem;">
                                ➕ Edit Occupants
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Identification Documents Section -->
            <div class="form-section">
                <div class="section-title">
                    Identification
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
                    Notes
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
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" 
                               {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}>
                        <span>Active Tenant</span>
                    </label>
                    <div class="help-text">
                        Uncheck to mark as inactive. This will affect the unit's status and lease validity.
                        @if($tenant->lease_status === 'active' && $tenant->is_active)
                            <br><span style="color: var(--accent-warning);">⚠️ This tenant currently has an active lease.</span>
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
        
        <!-- Danger Zone -->
        <div class="danger-zone">
            <div class="danger-zone-title">
                Danger Zone
            </div>
            <div class="danger-zone-description">
                Once you delete a tenant, all associated lease records will also be permanently deleted. This action cannot be undone.
            </div>
            <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirmDelete('{{ $tenant->full_name }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    Delete Tenant
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Additional Occupants Modal -->
<div id="occupantModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Additional Occupants</h3>
            <button type="button" class="modal-close" onclick="closeOccupantModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="occupantsList"></div>
            <button type="button" class="btn btn-outline-secondary" onclick="addOccupantRow()" style="margin: 0.75rem 0 1rem;">
                ➕ Add Occupant
            </button>
            <div class="help-text">
                Only occupants with names and relations will be saved.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeOccupantModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveOccupants()">Save Occupants</button>
        </div>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }
    .modal.show {
        display: flex;
    }
    .modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        width: 90%;
        max-width: 700px;
        max-height: 90vh;
        overflow: hidden;
    }
    .modal-header {
        padding: 1rem 1.5rem;
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-muted);
        transition: all 0.2s ease;
    }
    .modal-close:hover {
        color: var(--accent-emerald);
    }
    .modal-body {
        padding: 1.5rem;
        max-height: calc(90vh - 140px);
        overflow: auto;
    }
    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        border-top-color: var(--accent-emerald);
        animation: spin 0.6s linear infinite;
        margin-right: 0.5rem;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
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
                        unitSelect.appendChild(separator);
                    }
                    
                    // Add available units
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
        if (buildingSelect) {
            buildingSelect.addEventListener('change', function() {
                loadUnits(this.value);
            });
        }
        
        // Unit select change handler
        if (unitSelect) {
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
        }
        
        // Trigger change event if building already selected
        if (buildingSelect && buildingSelect.value) {
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
            <div style="display: grid; grid-template-columns: 1fr 0.8fr 0.8fr 50px; gap: 1rem; align-items: center;">
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
                    <button type="button" class="btn btn-danger" onclick="removeOccupantRow('${occupantId}')" style="padding: 0.4rem 0.8rem;">🗑️</button>
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
    
    // Delete confirmation
    function confirmDelete(tenantName) {
        return confirm(`Are you sure you want to delete "${tenantName}"? This action cannot be undone.`);
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