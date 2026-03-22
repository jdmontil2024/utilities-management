@extends('layouts.app')

@section('title', 'Edit Lease ' . $lease->lease_number)

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 class="page-title">
                    <span>✏️</span> Edit Lease
                </h1>
                <p class="page-subtitle">Update lease information for {{ $lease->lease_number }}</p>
            </div>
            <div>
                <span class="status-badge status-{{ $lease->lease_status }}">
                    {{ $lease->status_label }}
                </span>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Info Card -->
        <div class="info-card">
            <div class="info-card-title">
                <span>📄</span> Lease Information
            </div>
            <div class="info-card-content">
                <div class="info-card-item">
                    <span class="info-card-label">Tenant</span>
                    <span class="info-card-value">
                        <a href="{{ route('tenants.show', $lease->tenant) }}" style="color: #4a5568; text-decoration: none;">
                            {{ $lease->tenant->full_name }}
                        </a>
                    </span>
                </div>
                <div class="info-card-item">
                    <span class="info-card-label">Unit</span>
                    <span class="info-card-value">
                        <a href="{{ route('units.show', $lease->unit) }}" style="color: #4a5568; text-decoration: none;">
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
                <strong style="display: block; margin-bottom: 8px;">⚠️ Please fix the following errors:</strong>
                <ul style="margin: 0; padding-left: 20px;">
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
                    <div>📅</div>
                    <span>Lease Period</span>
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
                    <div>💰</div>
                    <span>Financial Details</span>
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
                    <div>📋</div>
                    <span>Lease Details</span>
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
                    <div>📝</div>
                    <span>Terms & Conditions</span>
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
                    <div>⚡</div>
                    <span>Utilities Included</span>
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
                    <div>📎</div>
                    <span>Lease Agreement</span>
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
                    <div>📝</div>
                    <span>Notes</span>
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
                    💾 Update Lease
                </button>
            </div>
        </form>
        <!-- END UPDATE FORM -->
        
        <!-- Danger Zone (commented out in original) 
        <div class="danger-zone">
            <div class="danger-zone-title">
                <span>⚠️</span> Danger Zone
            </div>
            <div class="danger-zone-description">
                Once you delete a lease, there is no going back. Please be certain.
            </div>
            <form action="{{ route('leases.destroy', $lease) }}" method="POST" 
                  onsubmit="return confirmDeleteLease(this, '{{ addslashes($lease->lease_number) }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    🗑️ Delete Lease
                </button>
            </form>
        </div>-->
    </div>
</div>

<style>
/* Additional styles specific to edit lease form */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

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

.error {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
}

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
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

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
}

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

.info-card {
    background: #e8f4fc;
    border: 1px solid #d1e4ff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.info-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-card-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-card-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-card-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.info-card-value {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
}

.json-editor {
    font-family: monospace;
    font-size: 13px;
    line-height: 1.5;
}

@media (max-width: 768px) {
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

    .info-card-content {
        grid-template-columns: 1fr;
    }
}
</style>

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
                alert('Move-in date cannot be before lease start date');
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
            submitBtn.innerHTML = '⏳ Updating...';
            submitBtn.style.opacity = '0.7';
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
    } else {
        // Fallback to original implementation
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            background: white;
            border-radius: 4px;
            padding: 15px 20px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
            display: flex;
            align-items: center;
            min-width: 300px;
            max-width: 400px;
            animation: slideIn 0.3s ease;
            border-left: 4px solid ${type === 'success' ? '#28a745' : 
                                  type === 'error' ? '#dc3545' : 
                                  type === 'warning' ? '#ffc107' : '#17a2b8'};
        `;
        
        toast.innerHTML = `
            <div style="flex-grow: 1;">${message}</div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; font-size: 18px; color: #666;">&times;</button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }
};

// Delete confirmation
window.confirmDeleteLease = function(form, leaseNumber) {
    event.preventDefault();
    
    if (confirm(`Are you sure you want to delete lease "${leaseNumber}"? This action cannot be undone.`)) {
        const button = form.querySelector('button[type="submit"]');
        button.innerHTML = '⏳';
        button.disabled = true;
        form.submit();
    }
    
    return false;
};

// Show session messages as toasts
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session("success") }}', 'success');
    });
@endif

@if(session('error'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session("error") }}', 'error');
    });
@endif

@if(session('warning'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session("warning") }}', 'warning');
    });
@endif

@if(session('info'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session("info") }}', 'info');
    });
@endif
</script>
@endsection