@extends('layouts.app')

@section('title', 'Create New Lease')

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
    
    /* INPUT GROUP */
    .input-group {
        display: flex;
        align-items: center;
    }
    
    .input-group-text {
        padding: 0.6rem 0.75rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-right: none;
        border-radius: 6px 0 0 6px;
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    
    .input-group .form-control {
        border-radius: 0 6px 6px 0;
    }
    
    /* HELP TEXT & ERRORS */
    .help-text {
        font-size: 0.65rem;
        color: var(--text-muted);
        margin-top: 0.35rem;
    }
    
    .help-text-warning {
        color: var(--accent-warning);
    }
    
    .help-text-success {
        color: var(--accent-emerald);
    }
    
    .error {
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
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    /* INFO CARDS */
    .info-card {
        background: var(--bg-surface);
        border-radius: 10px;
        padding: 1rem;
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }
    
    .info-card-title {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-card-content {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .info-card-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-main);
        font-size: 0.85rem;
    }
    
    /* STATUS BADGES */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 500;
        background: transparent;
        border: 1px solid;
    }
    
    .status-active {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .status-vacant, .status-available {
        border-color: var(--accent-emerald);
        color: var(--accent-emerald);
    }
    
    .status-occupied {
        border-color: var(--accent-warning);
        color: var(--accent-warning);
    }
    
    /* FILE INPUT */
    input[type="file"].form-control {
        padding: 0.5rem 0.75rem;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .dashboard-wrapper { padding: 1rem; }
        .form-container { padding: 1.25rem; }
        .form-row { grid-template-columns: 1fr; gap: 1rem; }
        .form-actions { flex-direction: column; }
        .btn { width: 100%; justify-content: center; }
        .info-card-content { flex-direction: column; gap: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Create New Lease</h1>
            <p class="page-subtitle">Create a new lease agreement for a tenant and unit</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <form action="{{ route('leases.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Hidden fields for pre-selected values from query parameters -->
            @if(request()->has('tenant_id'))
                <input type="hidden" name="tenant_id" value="{{ request('tenant_id') }}">
            @endif
            @if(request()->has('unit_id'))
                <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">
            @endif

            <!-- Pre-selected Tenant/Unit Info Cards -->
            @if(isset($tenant) && $tenant)
            <div class="info-card">
                <div class="info-card-title">
                    PRE-SELECTED TENANT
                    @if($tenant->currentLease)
                        <span class="status-badge status-occupied" style="margin-left: auto;">Has Active Lease</span>
                    @else
                        <span class="status-badge status-available" style="margin-left: auto;">Available for New Lease</span>
                    @endif
                </div>
                <div class="info-card-content">
                    <div class="info-card-item">
                        <span>👤</span>
                        <span><strong>Name:</strong> {{ $tenant->full_name }}</span>
                    </div>
                    <div class="info-card-item">
                        <span>✉️</span>
                        <span><strong>Email:</strong> {{ $tenant->email }}</span>
                    </div>
                    <div class="info-card-item">
                        <span>📞</span>
                        <span><strong>Phone:</strong> {{ $tenant->phone }}</span>
                    </div>
                    @if($tenant->currentLease)
                    <div class="info-card-item">
                        <span>⚠️</span>
                        <span><strong>Active Lease Ends:</strong> {{ $tenant->currentLease->end_date->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if(isset($unit) && $unit)
            <div class="info-card">
                <div class="info-card-title">
                    PRE-SELECTED UNIT
                    <span class="status-badge status-{{ $unit->status }}" style="margin-left: auto;">{{ ucfirst($unit->status) }}</span>
                </div>
                <div class="info-card-content">
                    <div class="info-card-item">
                        <span>🏠</span>
                        <span><strong>Unit:</strong> {{ $unit->unit_number }}</span>
                    </div>
                    <div class="info-card-item">
                        <span>📍</span>
                        <span><strong>Building:</strong> {{ $unit->building->name }}</span>
                    </div>
                    <div class="info-card-item">
                        <span>💰</span>
                        <span><strong>Monthly Rent:</strong> ₱{{ number_format($unit->monthly_rent, 0) }}</span>
                    </div>
                    <div class="info-card-item">
                        <span>📐</span>
                        <span><strong>Unit Type:</strong> {{ $unit->unit_type_label ?? ucfirst($unit->unit_type) }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tenant Selection Section -->
            <div class="form-section">
                <div class="section-title">
                    Tenant Information
                </div>

                @if(!isset($tenant))
                <div class="form-group">
                    <label class="form-label">Select Tenant <span class="required">*</span></label>
                    <select name="tenant_id" id="tenant_select" class="form-control" required>
                        <option value="">Select Tenant</option>
                        
                        @if(isset($tenantsForNewLease) && $tenantsForNewLease->count() > 0)
                            <optgroup label="✅ Tenants Available for New Lease">
                                @foreach($tenantsForNewLease as $tenantOption)
                                    <option value="{{ $tenantOption->id }}" 
                                            {{ old('tenant_id') == $tenantOption->id ? 'selected' : '' }}
                                            data-has-lease="false">
                                        {{ $tenantOption->full_name }} - {{ $tenantOption->email }} (No active lease)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        
                        @if(isset($tenantsForRenewal) && $tenantsForRenewal->count() > 0)
                            <optgroup label="🔄 Tenants Eligible for Renewal">
                                @foreach($tenantsForRenewal as $tenantOption)
                                    <option value="{{ $tenantOption->id }}" 
                                            {{ old('tenant_id') == $tenantOption->id ? 'selected' : '' }}
                                            data-has-lease="true"
                                            data-lease-end="{{ $tenantOption->currentLease->end_date->format('Y-m-d') }}">
                                        {{ $tenantOption->full_name }} - {{ $tenantOption->email }} 
                                        (Current lease ends {{ $tenantOption->currentLease->end_date->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if((!isset($tenantsForNewLease) || $tenantsForNewLease->count() === 0) && 
                            (!isset($tenantsForRenewal) || $tenantsForRenewal->count() === 0))
                            <option value="" disabled>No tenants available</option>
                        @endif
                    </select>
                    <div class="help-text" id="tenant_help">
                        Select a tenant to create a lease. Tenants with existing active leases can only renew.
                    </div>
                    @error('tenant_id') <div class="error">{{ $message }}</div> @enderror
                </div>
                @else
                <!-- Read-only display for pre-selected tenant -->
                <div class="form-group">
                    <label class="form-label">Selected Tenant <span class="required">*</span></label>
                    <input type="text" value="{{ $tenant->full_name }} - {{ $tenant->email }}" 
                           class="form-control" readonly disabled>
                    @if($tenant->currentLease)
                    <div class="help-text help-text-warning">
                        ⚠️ This tenant already has an active lease ending {{ $tenant->currentLease->end_date->format('M d, Y') }}. 
                        This will create a renewal lease.
                    </div>
                    @else
                    <div class="help-text help-text-success">
                        ✅ This tenant is eligible for a new lease.
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Unit Selection Section -->
            <div class="form-section">
                <div class="section-title">
                    Unit Assignment
                </div>

                @if(!isset($unit))
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Building</label>
                        <select name="building_id" id="building_id" class="form-control">
                            <option value="">Select Building</option>
                            @foreach($buildings ?? [] as $building)
                                <option value="{{ $building->id }}" {{ old('building_id') == $building->id ? 'selected' : '' }}>
                                    {{ $building->name }} - {{ $building->city }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-control" required>
                            <option value="">Select Building First</option>
                            @if(isset($units) && $units->count() > 0)
                                @foreach($units as $unitOption)
                                    <option value="{{ $unitOption->id }}" 
                                            data-rent="{{ $unitOption->monthly_rent }}"
                                            data-status="{{ $unitOption->status }}"
                                            {{ old('unit_id') == $unitOption->id ? 'selected' : '' }}>
                                        Unit {{ $unitOption->unit_number }} - {{ $unitOption->unit_type_label ?? ucfirst($unitOption->unit_type) }} 
                                        (₱{{ number_format($unitOption->monthly_rent, 0) }}) - {{ ucfirst($unitOption->status) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="help-text" id="unit_help">
                            @if(isset($units) && $units->count() > 0)
                                Available units for lease. Only vacant units can be selected.
                            @endif
                        </div>
                        @error('unit_id') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
                @else
                <!-- Read-only display for pre-selected unit -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Building</label>
                        <input type="text" value="{{ $unit->building->name }}" 
                               class="form-control" readonly disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <input type="text" value="Unit {{ $unit->unit_number }} - {{ $unit->unit_type_label ?? ucfirst($unit->unit_type) }}" 
                               class="form-control" readonly disabled>
                        @if($unit->status !== 'vacant' && $unit->status !== 'ready')
                        <div class="help-text help-text-warning">
                            ⚠️ This unit is currently {{ $unit->status }}. Creating a lease will change its status to occupied.
                        </div>
                        @else
                        <div class="help-text help-text-success">
                            ✅ This unit is available for lease.
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Lease Period Section -->
            <div class="form-section">
                <div class="section-title">
                    Lease Period
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date <span class="required">*</span></label>
                        <input type="date" name="start_date" id="start_date" 
                               value="{{ old('start_date', date('Y-m-d')) }}" required 
                               class="form-control">
                        @error('start_date') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date <span class="required">*</span></label>
                        <input type="date" name="end_date" id="end_date" 
                               value="{{ old('end_date', date('Y-m-d', strtotime('+1 year'))) }}" required 
                               class="form-control">
                        @error('end_date') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Move-in Date</label>
                        <input type="date" name="move_in_date" id="move_in_date" 
                               value="{{ old('move_in_date', date('Y-m-d')) }}" 
                               class="form-control">
                        <div class="help-text">Leave blank to use start date</div>
                        @error('move_in_date') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Due Day</label>
                        <input type="number" name="payment_due_day" value="{{ old('payment_due_day', 1) }}" 
                               min="1" max="31" class="form-control">
                        <div class="help-text">Day of month (1-31)</div>
                    </div>
                </div>

                <div id="date_validation_message" class="help-text"></div>
            </div>

            <!-- Financial Details Section -->
            <div class="form-section">
                <div class="section-title">
                    Financial Details
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monthly Rent <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="monthly_rent" id="monthly_rent" 
                                   value="{{ old('monthly_rent', $unit->monthly_rent ?? '') }}" 
                                   step="0.01" min="0" required class="form-control">
                        </div>
                        @error('monthly_rent') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Security Deposit</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="security_deposit" value="{{ old('security_deposit', 0) }}" 
                                   step="0.01" min="0" class="form-control">
                        </div>
                        @error('security_deposit') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Lease Details Section -->
            <div class="form-section">
                <div class="section-title">
                    Lease Details
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Lease Status <span class="required">*</span></label>
                        <select name="lease_status" class="form-control" required>
                            <option value="active" {{ old('lease_status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('lease_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ old('lease_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="terminated" {{ old('lease_status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        @error('lease_status') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lease Type</label>
                        <select name="lease_type" id="lease_type" class="form-control">
                            <option value="">Select Lease Type</option>
                            <option value="Standard" {{ old('lease_type', 'Standard') == 'Standard' ? 'selected' : '' }}>Standard</option>
                            <option value="Renewal" {{ old('lease_type') == 'Renewal' ? 'selected' : '' }}>Renewal</option>
                            <option value="Short-term" {{ old('lease_type') == 'Short-term' ? 'selected' : '' }}>Short-term</option>
                            <option value="Month-to-Month" {{ old('lease_type') == 'Month-to-Month' ? 'selected' : '' }}>Month-to-Month</option>
                            <option value="Commercial" {{ old('lease_type') == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                            <option value="Sublease" {{ old('lease_type') == 'Sublease' ? 'selected' : '' }}>Sublease</option>
                            <option value="Other" {{ old('lease_type') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('lease_type') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions Section -->
            <div class="form-section">
                <div class="section-title">
                    Terms & Conditions
                </div>

                <div class="form-group">
                    <label class="form-label">Terms (JSON)</label>
                    <textarea name="terms" rows="4" class="form-control form-textarea" 
                              placeholder='{"late_fee": 500, "allowed_pets": false, "notice_period_days": 30}'>{{ old('terms') }}</textarea>
                    <div class="help-text">Enter terms as JSON object</div>
                    @error('terms') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Utilities Included Section -->
            <div class="form-section">
                <div class="section-title">
                    Utilities Included
                </div>

                <div class="form-group">
                    <label class="form-label">Utilities (JSON)</label>
                    <textarea name="utilities_included" rows="3" class="form-control form-textarea" 
                              placeholder='["water", "electricity", "internet"]'>{{ old('utilities_included') }}</textarea>
                    <div class="help-text">Enter utilities as JSON array</div>
                    @error('utilities_included') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Lease Agreement Section -->
            <div class="form-section">
                <div class="section-title">
                    Lease Agreement
                </div>

                <div class="form-group">
                    <label class="form-label">Lease Agreement (PDF/DOC)</label>
                    <input type="file" name="lease_agreement_path" accept=".pdf,.doc,.docx" class="form-control">
                    <div class="help-text">Max 10MB. Accepted: PDF, DOC, DOCX</div>
                    @error('lease_agreement_path') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Notes Section -->
            <div class="form-section">
                <div class="section-title">
                    Notes
                </div>

                <div class="form-group">
                    <textarea name="notes" rows="4" class="form-control form-textarea" 
                              placeholder="Enter any additional notes about this lease...">{{ old('notes') }}</textarea>
                    @error('notes') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('leases.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Create Lease
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Intelligent Tenant Selection Feedback
    const tenantSelect = document.getElementById('tenant_select');
    const tenantHelp = document.getElementById('tenant_help');
    const leaseTypeSelect = document.getElementById('lease_type');
    
    if (tenantSelect) {
        tenantSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const hasLease = selectedOption.dataset.hasLease === 'true';
            
            if (this.value === '') {
                tenantHelp.innerHTML = 'Select a tenant to create a lease. Tenants with existing active leases can only renew.';
                tenantHelp.className = 'help-text';
            } else if (hasLease) {
                const leaseEnd = selectedOption.dataset.leaseEnd;
                const endDate = new Date(leaseEnd);
                const today = new Date();
                
                tenantHelp.innerHTML = `⚠️ This tenant already has an active lease ending ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}. You can only renew (not create new).`;
                tenantHelp.className = 'help-text help-text-warning';
                
                // Auto-select Renewal lease type if available
                if (leaseTypeSelect) {
                    Array.from(leaseTypeSelect.options).forEach(option => {
                        if (option.value === 'Renewal') {
                            option.selected = true;
                        }
                    });
                }
            } else {
                tenantHelp.innerHTML = '✅ This tenant is eligible for a new lease.';
                tenantHelp.className = 'help-text help-text-success';
                
                // Auto-select Standard lease type if available
                if (leaseTypeSelect && leaseTypeSelect.value === '') {
                    Array.from(leaseTypeSelect.options).forEach(option => {
                        if (option.value === 'Standard') {
                            option.selected = true;
                        }
                    });
                }
            }
        });

        // Trigger on page load if there's a selected tenant
        if (tenantSelect.value) {
            tenantSelect.dispatchEvent(new Event('change'));
        }
    }

    // Unit selection feedback
    const unitSelect = document.getElementById('unit_id');
    const unitHelp = document.getElementById('unit_help');
    
    if (unitSelect) {
        unitSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value === '') {
                if (unitHelp) {
                    unitHelp.innerHTML = 'Select a unit for this lease.';
                    unitHelp.className = 'help-text';
                }
            } else if (selectedOption) {
                const status = selectedOption.dataset.status;
                
                if (status && status !== 'vacant' && status !== 'ready') {
                    if (unitHelp) {
                        unitHelp.innerHTML = `⚠️ This unit is currently ${status}. Creating a lease will change its status to occupied.`;
                        unitHelp.className = 'help-text help-text-warning';
                    }
                } else {
                    if (unitHelp) {
                        unitHelp.innerHTML = '✅ This unit is available for lease.';
                        unitHelp.className = 'help-text help-text-success';
                    }
                }
                
                // Auto-fill monthly rent
                const rentInput = document.getElementById('monthly_rent');
                if (rentInput && selectedOption.dataset.rent) {
                    rentInput.value = selectedOption.dataset.rent;
                }
            }
        });

        // Trigger on page load if there's a selected unit
        if (unitSelect.value) {
            unitSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Load units when building is selected
    const buildingSelect = document.getElementById('building_id');
    
    function loadUnits(buildingId) {
        if (!buildingId) {
            if (unitSelect) {
                unitSelect.innerHTML = '<option value="">Select Building First</option>';
                unitSelect.disabled = true;
            }
            return;
        }
        
        unitSelect.disabled = true;
        unitSelect.innerHTML = '<option value="">Loading units...</option>';
        
        fetch(`/leases/get-units/${buildingId}`)
            .then(response => response.json())
            .then(units => {
                if (unitSelect) {
                    unitSelect.innerHTML = '<option value="">Select Unit</option>';
                    unitSelect.disabled = false;
                    
                    if (units.length === 0) {
                        unitSelect.innerHTML += '<option value="" disabled>No units available</option>';
                        return;
                    }
                    
                    units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.id;
                        option.dataset.rent = unit.monthly_rent;
                        option.dataset.status = unit.status;
                        option.textContent = `Unit ${unit.unit_number} - ${unit.unit_type || 'Standard'} (₱${Number(unit.monthly_rent).toLocaleString()}) - ${unit.status}`;
                        
                        // Check if this unit was pre-selected
                        @if(isset($unit) && $unit)
                            if (unit.id === {{ $unit->id ?? 'null' }}) {
                                option.selected = true;
                            }
                        @endif
                        
                        unitSelect.appendChild(option);
                    });

                    // Trigger change event to update help text
                    if (unitSelect.value) {
                        unitSelect.dispatchEvent(new Event('change'));
                    }
                }
            })
            .catch(error => {
                console.error('Error loading units:', error);
                if (unitSelect) {
                    unitSelect.innerHTML = '<option value="">Error loading units</option>';
                    unitSelect.disabled = false;
                }
            });
    }
    
    // Building change event
    if (buildingSelect) {
        buildingSelect.addEventListener('change', function() {
            loadUnits(this.value);
            const rentInput = document.getElementById('monthly_rent');
            if (rentInput) {
                rentInput.value = ''; // Clear rent when building changes
            }
        });
    }
    
    // Load units on page load if building is pre-selected
    @if(old('building_id'))
        loadUnits({{ old('building_id') }});
    @endif
    
    // Auto-calculate end date when start date changes (1 year default)
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const moveInDateInput = document.getElementById('move_in_date');
    const dateValidationMsg = document.getElementById('date_validation_message');
    
    function validateDates() {
        if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            
            if (end <= start) {
                dateValidationMsg.innerHTML = '⚠️ End date must be after start date.';
                dateValidationMsg.className = 'help-text help-text-warning';
            } else {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const diffMonths = Math.round(diffDays / 30.44 * 10) / 10;
                
                dateValidationMsg.innerHTML = `✅ Lease duration: ${diffDays} days (${diffMonths} months)`;
                dateValidationMsg.className = 'help-text help-text-success';
            }
        }
    }
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            if (this.value && !endDateInput.value) {
                const date = new Date(this.value);
                date.setFullYear(date.getFullYear() + 1);
                endDateInput.value = date.toISOString().split('T')[0];
            }
            
            // Set move-in date to start date if not set
            if (moveInDateInput && !moveInDateInput.value) {
                moveInDateInput.value = this.value;
            }
            
            validateDates();
        });
        
        endDateInput.addEventListener('change', validateDates);
    }

    // Toast notification system
    window.showToast = function(message, type = 'success') {
        if (window.Utilities && typeof window.Utilities.showToast === 'function') {
            window.Utilities.showToast(message, type);
        }
    };

    // Show session messages as toasts
    @if(session('success'))
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('success') }}', 'success');
        });
    @endif
    
    @if(session('error'))
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('error') }}', 'error');
        });
    @endif
    
    @if(session('warning'))
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('warning') }}', 'warning');
        });
    @endif
    
    @if(session('info'))
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('info') }}', 'info');
        });
    @endif
});
</script>
@endpush