@extends('layouts.app')

@section('title', 'Create Maintenance Request')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">🔧 Create Maintenance Request</h1>
            <p class="page-subtitle">Submit a new maintenance request for a unit</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Info Box -->
        <div class="info-box">
            <div class="info-box-title">
                <span>ℹ️</span>
                <span>Important Information</span>
            </div>
            <p style="margin: 0; font-size: 13px;">
                Please provide as much detail as possible about the issue. This will help us assign the right technician and prioritize the request appropriately.
            </p>
        </div>

        <!-- CREATE FORM -->
        <form action="{{ route('maintenance-requests.store') }}" method="POST" id="createForm">
            @csrf
            
            <!-- Location Information -->
            <div class="form-section">
                <div class="section-title">
                    <div>📍</div>
                    <span>Location Information</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Building <span class="required">*</span></label>
                        <select name="building_id" id="building_id" class="form-control" required onchange="loadUnits()">
                            <option value="">Select Building</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}" {{ $selectedBuilding && $selectedBuilding->id == $building->id ? 'selected' : '' }}
                                        data-units="{{ $building->units->count() }}">
                                    {{ $building->name }} ({{ $building->units->count() }} units)
                                </option>
                            @endforeach
                        </select>
                        @error('building_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-control" required>
                            <option value="">First select a building</option>
                            @if($selectedUnit)
                                <option value="{{ $selectedUnit->id }}" selected>
                                    Unit {{ $selectedUnit->unit_number }} - {{ $selectedUnit->unit_type ?? 'Standard' }}
                                </option>
                            @endif
                        </select>
                        @error('unit_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                        <div class="help-text" id="unit-help"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Requested By (Tenant)</label>
                    <select name="tenant_id" id="tenant_id" class="form-control">
                        <option value="">Select Tenant (Optional)</option>
                    </select>
                    @error('tenant_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <div class="help-text">If not specified, request will be marked as reported by management</div>
                </div>
            </div>
            
            <!-- Request Details -->
            <div class="form-section">
                <div class="section-title">
                    <div>📋</div>
                    <span>Request Details</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" required 
                           value="{{ old('title') }}"
                           placeholder="e.g., Leaking faucet, Broken AC, Electrical issue">
                    @error('title')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="maintenance_category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('maintenance_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} 
                                    @if($category->sla_hours) 
                                        (SLA: {{ $category->formatted_sla }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('maintenance_category_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Request Date <span class="required">*</span></label>
                        <input type="date" name="request_date" class="form-control" required 
                               value="{{ old('request_date', date('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}">
                        @error('request_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control form-textarea" required 
                              placeholder="Please provide detailed description of the issue...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Priority Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>⚠️</div>
                    <span>Priority Level</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Priority <span class="required">*</span></label>
                    <div class="priority-options">
                        <div class="priority-option priority-low">
                            <input type="radio" name="priority" id="priority_low" value="low" 
                                   {{ old('priority', 'medium') == 'low' ? 'checked' : '' }}>
                            <label for="priority_low">🐢 Low</label>
                        </div>
                        <div class="priority-option priority-medium">
                            <input type="radio" name="priority" id="priority_medium" value="medium" 
                                   {{ old('priority', 'medium') == 'medium' ? 'checked' : '' }}>
                            <label for="priority_medium">⚡ Medium</label>
                        </div>
                        <div class="priority-option priority-high">
                            <input type="radio" name="priority" id="priority_high" value="high" 
                                   {{ old('priority') == 'high' ? 'checked' : '' }}>
                            <label for="priority_high">🔥 High</label>
                        </div>
                        <div class="priority-option priority-emergency">
                            <input type="radio" name="priority" id="priority_emergency" value="emergency" 
                                   {{ old('priority') == 'emergency' ? 'checked' : '' }}>
                            <label for="priority_emergency">🚨 Emergency</label>
                        </div>
                    </div>
                    @error('priority')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <div class="help-text">
                        <strong>Low:</strong> Non-urgent, cosmetic issues<br>
                        <strong>Medium:</strong> Minor functional issues<br>
                        <strong>High:</strong> Major issues affecting usability<br>
                        <strong>Emergency:</strong> Immediate safety or health hazards
                    </div>
                </div>
            </div>
            
            <!-- Optional Details -->
            <div class="form-section">
                <div class="section-title">
                    <div>📎</div>
                    <span>Additional Information (Optional)</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Scheduled Date</label>
                        <input type="date" name="scheduled_date" class="form-control" 
                               value="{{ old('scheduled_date') }}"
                               min="{{ date('Y-m-d') }}">
                        @error('scheduled_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                        <div class="help-text">Leave blank if not yet scheduled</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Estimated Cost</label>
                        <input type="number" name="estimated_cost" class="form-control" step="0.01" min="0"
                               value="{{ old('estimated_cost') }}"
                               placeholder="e.g., 150.00">
                        @error('estimated_cost')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Access Instructions</label>
                    <textarea name="access_instructions" class="form-control" rows="3" 
                              placeholder="e.g., Call tenant before arrival, Gate code 1234, etc.">{{ old('access_instructions') }}</textarea>
                    @error('access_instructions')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Internal Notes</label>
                    <textarea name="internal_notes" class="form-control" rows="3" 
                              placeholder="Any internal notes for staff (not visible to tenant)">{{ old('internal_notes') }}</textarea>
                    @error('internal_notes')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('maintenance-requests.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Create Request
                </button>
            </div>
        </form>
        <!-- END CREATE FORM -->
    </div>
</div>

<style>
/* Additional styles specific to create maintenance request form */
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
    min-height: 120px;
    resize: vertical;
}

.priority-options {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.priority-option {
    flex: 1;
    min-width: 120px;
}

.priority-option input[type="radio"] {
    display: none;
}

.priority-option label {
    display: block;
    padding: 12px;
    text-align: center;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.priority-option input[type="radio"]:checked + label {
    border-color: #3498db;
    background: #ebf5ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.priority-low label { border-color: #27ae60; color: #27ae60; }
.priority-medium label { border-color: #f39c12; color: #f39c12; }
.priority-high label { border-color: #e67e22; color: #e67e22; }
.priority-emergency label { border-color: #e74c3c; color: #e74c3c; }

.priority-low input[type="radio"]:checked + label { background: #e8f5e9; }
.priority-medium input[type="radio"]:checked + label { background: #fff3e0; }
.priority-high input[type="radio"]:checked + label { background: #fbe9e7; }
.priority-emergency input[type="radio"]:checked + label { background: #ffebee; }

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
    font-family: 'Inter', sans-serif;
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
    display: none;
}

.info-box {
    background: #e8f4fc;
    border: 1px solid #b8e0ff;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
    color: #2c3e50;
}

.info-box-title {
    font-weight: 600;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

@media (max-width: 768px) {
    .form-container {
        padding: 20px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .priority-options {
        flex-direction: column;
    }

    .priority-option {
        min-width: auto;
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

<script>
// Store units and tenants data
let unitsData = {};

// Load units when building is selected
function loadUnits() {
    const buildingId = document.getElementById('building_id').value;
    const unitSelect = document.getElementById('unit_id');
    const tenantSelect = document.getElementById('tenant_id');
    const unitHelp = document.getElementById('unit-help');
    
    // Clear current options
    unitSelect.innerHTML = '<option value="">Select Unit</option>';
    tenantSelect.innerHTML = '<option value="">Select Tenant (Optional)</option>';
    
    if (!buildingId) {
        unitHelp.textContent = '';
        return;
    }

    // Show loading
    unitSelect.disabled = true;
    unitSelect.innerHTML = '<option value="">Loading units...</option>';
    
    // Fetch units for selected building
    fetch(`/buildings/${buildingId}/units-json`)
        .then(response => response.json())
        .then(units => {
            unitSelect.disabled = false;
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            
            if (units.length === 0) {
                unitHelp.textContent = 'No units found in this building';
                return;
            }

            unitsData[buildingId] = units;
            unitHelp.textContent = `${units.length} units available`;
            
            units.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = `Unit ${unit.unit_number} - ${unit.unit_type || 'Standard'} (₱${unit.monthly_rent?.toLocaleString() || '0'})`;
                if (unit.status !== 'vacant' && unit.status !== 'ready') {
                    option.textContent += ' [Occupied]';
                }
                unitSelect.appendChild(option);
            });

            // If there's a selected unit from the controller, preselect it
            @if(isset($selectedUnit) && $selectedUnit)
                const selectedUnitId = {{ $selectedUnit->id }};
                if (unitSelect.querySelector(`option[value="${selectedUnitId}"]`)) {
                    unitSelect.value = selectedUnitId;
                    loadTenants(buildingId, selectedUnitId);
                }
            @endif
        })
        .catch(error => {
            console.error('Error loading units:', error);
            unitSelect.disabled = false;
            unitSelect.innerHTML = '<option value="">Error loading units</option>';
            unitHelp.textContent = 'Failed to load units';
        });
}

// Load tenants for selected unit
function loadTenants(buildingId, unitId) {
    const tenantSelect = document.getElementById('tenant_id');
    
    if (!buildingId || !unitId) {
        return;
    }

    // Show loading
    tenantSelect.disabled = true;
    tenantSelect.innerHTML = '<option value="">Loading tenants...</option>';
    
    // Fetch tenants for selected unit
    fetch(`/api/unit/${unitId}/tenants`)
        .then(response => response.json())
        .then(tenants => {
            tenantSelect.disabled = false;
            tenantSelect.innerHTML = '<option value="">Select Tenant (Optional)</option>';
            
            tenants.forEach(tenant => {
                const option = document.createElement('option');
                option.value = tenant.id;
                option.textContent = `${tenant.full_name} - ${tenant.email}`;
                tenantSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading tenants:', error);
            tenantSelect.disabled = false;
            tenantSelect.innerHTML = '<option value="">No tenants found for this unit</option>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    // When unit is selected, load its tenants
    document.getElementById('unit_id').addEventListener('change', function() {
        const buildingId = document.getElementById('building_id').value;
        const unitId = this.value;
        if (unitId) {
            loadTenants(buildingId, unitId);
        }
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Check if building is preselected
    @if(isset($selectedBuilding) && $selectedBuilding)
        const buildingSelect = document.getElementById('building_id');
        if (buildingSelect) {
            loadUnits();
        }
    @endif

    // Format currency input
    const costInput = document.querySelector('input[name="estimated_cost"]');
    if (costInput) {
        costInput.addEventListener('blur', function() {
            if (this.value) {
                const value = parseFloat(this.value).toFixed(2);
                this.value = value;
            }
        });
    }
});

// Toast notification system - use layout's Utilities if available
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