@extends('layouts.app')

@section('title', 'Edit Lease ' . $lease->lease_number)

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
    
    .error {
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
    
    /* INFO CARD */
    .info-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .info-card-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .info-card-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .info-card-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-card-label {
        font-size: 0.65rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-card-value {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-main);
    }
    
    .info-card-value a {
        color: var(--accent-emerald);
        text-decoration: none;
    }
    
    .info-card-value a:hover {
        text-decoration: underline;
    }
    
    /* JSON EDITOR */
    .json-editor {
        font-family: monospace;
        font-size: 0.75rem;
        line-height: 1.5;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .dashboard-wrapper { padding: 1rem; }
        .form-container { padding: 1.25rem; }
        .form-row { grid-template-columns: 1fr; gap: 1rem; }
        .form-actions { flex-direction: column; }
        .btn { width: 100%; justify-content: center; }
        .file-input-group { flex-direction: column; align-items: stretch; }
        .current-file { justify-content: center; }
        .info-card-content { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Lease</h1>
            <p class="page-subtitle">Update lease information for {{ $lease->lease_number }}</p>
        </div>
        <div>
            <span class="status-badge status-{{ $lease->lease_status }}">
                {{ $lease->status_label }}
            </span>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Info Card -->
        <div class="info-card">
            <div class="info-card-title">
                Lease Information
            </div>
            <div class="info-card-content">
                <div class="info-card-item">
                    <span class="info-card-label">Tenant</span>
                    <span class="info-card-value">
                        <a href="{{ route('tenants.show', $lease->tenant) }}">
                            {{ $lease->tenant->full_name }}
                        </a>
                    </span>
                </div>
                <div class="info-card-item">
                    <span class="info-card-label">Unit</span>
                    <span class="info-card-value">
                        <a href="{{ route('units.show', $lease->unit) }}">
                            Unit {{ $lease->unit->unit_number }}
                        </a>
                    </span>
                </div>
                <div class="info-card-item">
                    <span class="info-card-label">Building</span>
                    <span class="info-card-value">{{ $lease->unit->building->name }}</span>
                </div>
                <div class="info-card-item">
                    <span class="info-card-label">Lease Number</span>
                    <span class="info-card-value">{{ $lease->lease_number }}</span>
                </div>
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
        <form action="{{ route('leases.update', $lease) }}" method="POST" enctype="multipart/form-data" id="updateForm">
            @csrf
            @method('PUT')
            
            <!-- Lease Period Section -->
            <div class="form-section">
                <div class="section-title">
                    Lease Period
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date <span class="required">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required 
                               value="{{ old('start_date', $lease->start_date?->format('Y-m-d')) }}">
                        @error('start_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">End Date <span class="required">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control" required 
                               value="{{ old('end_date', $lease->end_date?->format('Y-m-d')) }}">
                        @error('end_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Move-in Date</label>
                        <input type="date" name="move_in_date" id="move_in_date" class="form-control" 
                               value="{{ old('move_in_date', $lease->move_in_date?->format('Y-m-d') ?? $lease->start_date?->format('Y-m-d')) }}">
                        @error('move_in_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Leave blank to use start date</div>
                    </div>
                    
                    @if($lease->lease_status === 'terminated' || $lease->move_out_date)
                    <div class="form-group">
                        <label class="form-label">Move-out Date</label>
                        <input type="date" name="move_out_date" class="form-control" 
                               value="{{ old('move_out_date', $lease->move_out_date?->format('Y-m-d')) }}">
                        @error('move_out_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Financial Details Section -->
            <div class="form-section">
                <div class="section-title">
                    Financial Details
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monthly Rent <span class="required">*</span></label>
                        <div class="currency-input">
                            <span class="currency-prefix">₱</span>
                            <input type="number" name="monthly_rent" class="form-control" required 
                                   value="{{ old('monthly_rent', $lease->monthly_rent) }}" 
                                   step="0.01" min="0"
                                   placeholder="0.00">
                        </div>
                        @error('monthly_rent')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Security Deposit</label>
                        <div class="currency-input">
                            <span class="currency-prefix">₱</span>
                            <input type="number" name="security_deposit" class="form-control" 
                                   value="{{ old('security_deposit', $lease->security_deposit ?? 0) }}" 
                                   step="0.01" min="0"
                                   placeholder="0.00">
                        </div>
                        @error('security_deposit')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Payment Due Day</label>
                        <input type="number" name="payment_due_day" class="form-control" 
                               value="{{ old('payment_due_day', $lease->payment_due_day ?? 1) }}" 
                               min="1" max="31"
                               placeholder="1">
                        @error('payment_due_day')
                            <div class="error">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Day of month (1-31)</div>
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
                            <option value="">Select Status</option>
                            <option value="active" {{ old('lease_status', $lease->lease_status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('lease_status', $lease->lease_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ old('lease_status', $lease->lease_status) == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="terminated" {{ old('lease_status', $lease->lease_status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        @error('lease_status')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Lease Type</label>
                        <select name="lease_type" class="form-control">
                            <option value="">Select Lease Type</option>
                            <option value="Standard" {{ old('lease_type', $lease->lease_type) == 'Standard' ? 'selected' : '' }}>Standard</option>
                            <option value="Renewal" {{ old('lease_type', $lease->lease_type) == 'Renewal' ? 'selected' : '' }}>Renewal</option>
                            <option value="Short-term" {{ old('lease_type', $lease->lease_type) == 'Short-term' ? 'selected' : '' }}>Short-term</option>
                            <option value="Month-to-Month" {{ old('lease_type', $lease->lease_type) == 'Month-to-Month' ? 'selected' : '' }}>Month-to-Month</option>
                            <option value="Commercial" {{ old('lease_type', $lease->lease_type) == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                            <option value="Sublease" {{ old('lease_type', $lease->lease_type) == 'Sublease' ? 'selected' : '' }}>Sublease</option>
                            <option value="Other" {{ old('lease_type', $lease->lease_type) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('lease_type')
                            <div class="error">{{ $message }}</div>
                        @enderror
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
                    <textarea name="terms" class="form-control form-textarea json-editor" 
                              rows="4" 
                              placeholder='{"late_fee": 500, "allowed_pets": false, "notice_period_days": 30}'>{{ old('terms', is_array($lease->terms) ? json_encode($lease->terms, JSON_PRETTY_PRINT) : $lease->terms) }}</textarea>
                    @error('terms')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <div class="help-text">Enter terms as JSON object</div>
                </div>
            </div>
            
            <!-- Utilities Included Section -->
            <div class="form-section">
                <div class="section-title">
                    Utilities Included
                </div>
                
                <div class="form-group">
                    <label class="form-label">Utilities (JSON)</label>
                    <textarea name="utilities_included" class="form-control form-textarea json-editor" 
                              rows="3" 
                              placeholder='["water", "electricity", "internet"]'>{{ old('utilities_included', is_array($lease->utilities_included) ? json_encode($lease->utilities_included, JSON_PRETTY_PRINT) : $lease->utilities_included) }}</textarea>
                    @error('utilities_included')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <div class="help-text">Enter utilities as JSON array</div>
                </div>
            </div>
            
            <!-- Lease Agreement Section -->
            <div class="form-section">
                <div class="section-title">
                    Lease Agreement
                </div>
                
                <div class="form-group">
                    <label class="form-label">Lease Agreement (PDF/DOC)</label>
                    <div class="file-input-group">
                        <input type="file" name="lease_agreement_path" class="form-control" 
                               accept=".pdf,.doc,.docx">
                        @if($lease->lease_agreement_path)
                            <a href="{{ Storage::url($lease->lease_agreement_path) }}" target="_blank" class="current-file">
                                📄 View Current
                            </a>
                        @endif
                    </div>
                    @error('lease_agreement_path')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <div class="help-text">
                        Accepted formats: PDF, DOC, DOCX (Max 10MB)
                        @if($lease->lease_agreement_path)
                            <br>Current file: <strong>{{ basename($lease->lease_agreement_path) }}</strong>
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
                    <textarea name="notes" class="form-control form-textarea" 
                              rows="4" 
                              placeholder="Additional notes about this lease...">{{ old('notes', $lease->notes) }}</textarea>
                    @error('notes')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('leases.show', $lease) }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Update Lease
                </button>
            </div>
        </form>
        <!-- END UPDATE FORM -->
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

    // Date validation
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const moveInDate = document.getElementById('move_in_date');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
            if (endDate.value && endDate.value < this.value) {
                endDate.value = this.value;
            }
            
            // Set move-in date to start date if not set
            if (moveInDate && !moveInDate.value) {
                moveInDate.value = this.value;
            }
        });
        
        endDate.addEventListener('change', function() {
            if (startDate.value && this.value < startDate.value) {
                this.value = startDate.value;
            }
        });
    }
    
    if (moveInDate && startDate) {
        moveInDate.addEventListener('change', function() {
            if (this.value && startDate.value && this.value < startDate.value) {
                if (typeof Utilities !== 'undefined' && Utilities.showToast) {
                    Utilities.showToast('Move-in date cannot be before lease start date', 'warning');
                } else {
                    alert('Move-in date cannot be before lease start date');
                }
                this.value = startDate.value;
            }
        });
    }

    // Form submission handler
    const form = document.getElementById('updateForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Updating...';
        });
    }

    // JSON formatting helper
    function formatJSON(element) {
        try {
            const value = element.value;
            if (value) {
                const parsed = JSON.parse(value);
                element.value = JSON.stringify(parsed, null, 2);
            }
        } catch (e) {
            // Not valid JSON, leave as is
        }
    }

    // Format JSON on blur
    document.querySelectorAll('.json-editor').forEach(textarea => {
        textarea.addEventListener('blur', function() {
            formatJSON(this);
        });
    });
});

// Toast notification system - use the layout's Utilities if available
window.showToast = function(message, type = 'success') {
    if (window.Utilities && typeof window.Utilities.showToast === 'function') {
        window.Utilities.showToast(message, type);
    }
};

// Show session messages as toasts
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Utilities !== 'undefined' && Utilities.showToast) {
            Utilities.showToast('{{ session("success") }}', 'success');
        }
    });
@endif

@if(session('error'))
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Utilities !== 'undefined' && Utilities.showToast) {
            Utilities.showToast('{{ session("error") }}', 'error');
        }
    });
@endif

@if(session('warning'))
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Utilities !== 'undefined' && Utilities.showToast) {
            Utilities.showToast('{{ session("warning") }}', 'warning');
        }
    });
@endif

@if(session('info'))
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Utilities !== 'undefined' && Utilities.showToast) {
            Utilities.showToast('{{ session("info") }}', 'info');
        }
    });
@endif
</script>
@endpush