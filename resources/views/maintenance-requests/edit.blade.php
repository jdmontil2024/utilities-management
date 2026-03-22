@extends('layouts.app')

@section('title', 'Edit Maintenance Request #' . $maintenanceRequest->id)

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">🔧 Edit Maintenance Request #{{ $maintenanceRequest->id }}</h1>
            <p class="page-subtitle">Update the details of this maintenance request</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <!-- Status Bar -->
    <div class="status-bar">
        <div class="status-info">
            @php
                $priorityBadgeClass = match($maintenanceRequest->priority) {
                    'emergency' => 'priority-badge-emergency',
                    'high' => 'priority-badge-high',
                    'medium' => 'priority-badge-medium',
                    'low' => 'priority-badge-low',
                    default => 'bg-secondary'
                };
                
                $statusClass = match($maintenanceRequest->status) {
                    'submitted' => 'status-submitted',
                    'assigned' => 'status-assigned',
                    'in_progress' => 'status-in_progress',
                    'completed' => 'status-completed',
                    'cancelled' => 'status-cancelled',
                    default => 'bg-secondary'
                };
            @endphp
            
            <span class="badge {{ $priorityBadgeClass }}">
                {{ strtoupper($maintenanceRequest->priority) }} PRIORITY
            </span>
            
            <span class="badge {{ $statusClass }}">
                {{ ucfirst(str_replace('_', ' ', $maintenanceRequest->status)) }}
            </span>
            
            @if(method_exists($maintenanceRequest, 'isOverdue') && $maintenanceRequest->isOverdue())
                <span class="badge" style="background: #e74c3c; color: white;">
                    ⚠️ OVERDUE
                </span>
            @endif
        </div>
        
        <div>
            <a href="{{ route('maintenance-requests.show', $maintenanceRequest) }}" class="btn btn-sm btn-secondary">
                ← Back to Details
            </a>
        </div>
    </div>

    <!-- Warning for Completed/Cancelled -->
    @if(in_array($maintenanceRequest->status, ['completed', 'cancelled']))
    <div class="warning-message">
        <span>⚠️</span>
        <span>This request is <strong>{{ $maintenanceRequest->status }}</strong>. Some fields may be read-only.</span>
    </div>
    @endif

    <!-- Form Container -->
    <div class="form-container">
        <!-- EDIT FORM -->
        <form action="{{ route('maintenance-requests.update', $maintenanceRequest) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            
            <!-- Location Information -->
            <div class="form-section">
                <div class="section-title">
                    <div>📍</div>
                    <span>Location Information</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Building <span class="required">*</span></label>
                        <select name="building_id" id="building_id" class="form-control" required 
                                {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}
                                onchange="loadUnits()">
                            <option value="">Select Building</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}" 
                                        {{ ($maintenanceRequest->unit->building_id ?? old('building_id')) == $building->id ? 'selected' : '' }}
                                        data-units="{{ $building->units->count() }}">
                                    {{ $building->name }} ({{ $building->units->count() }} units)
                                </option>
                            @endforeach
                        </select>
                        @if(in_array($maintenanceRequest->status, ['completed', 'cancelled']))
                            <input type="hidden" name="building_id" value="{{ $maintenanceRequest->unit->building_id }}">
                        @endif
                        @error('building_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-control" required 
                                {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                            <option value="">Select Unit</option>
                        </select>
                        @if(in_array($maintenanceRequest->status, ['completed', 'cancelled']))
                            <input type="hidden" name="unit_id" value="{{ $maintenanceRequest->unit_id }}">
                        @endif
                        @error('unit_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                        <div class="help-text" id="unit-help"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Requested By (Tenant)</label>
                    <select name="tenant_id" id="tenant_id" class="form-control" 
                            {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                        <option value="">Select Tenant (Optional)</option>
                        @if($maintenanceRequest->tenant)
                            <option value="{{ $maintenanceRequest->tenant_id }}" selected>{{ $maintenanceRequest->tenant->full_name }}</option>
                        @endif
                    </select>
                    @if(in_array($maintenanceRequest->status, ['completed', 'cancelled']) && $maintenanceRequest->tenant_id)
                        <input type="hidden" name="tenant_id" value="{{ $maintenanceRequest->tenant_id }}">
                    @endif
                    @error('tenant_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <div class="help-text">Leave empty if request is reported by management</div>
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
                           value="{{ old('title', $maintenanceRequest->title) }}"
                           placeholder="e.g., Leaking faucet, Broken AC, Electrical issue"
                           {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                    @error('title')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="maintenance_category_id" class="form-control" required 
                                {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ (old('maintenance_category_id', $maintenanceRequest->maintenance_category_id) == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }} @if($category->sla_hours) (SLA: {{ $category->sla_hours }}hrs) @endif
                                </option>
                            @endforeach
                        </select>
                        @if(in_array($maintenanceRequest->status, ['completed', 'cancelled']))
                            <input type="hidden" name="maintenance_category_id" value="{{ $maintenanceRequest->maintenance_category_id }}">
                        @endif
                        @error('maintenance_category_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Request Date <span class="required">*</span></label>
                        <input type="date" name="request_date" class="form-control" required 
                               value="{{ old('request_date', $maintenanceRequest->request_date->format('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                        @error('request_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control form-textarea" required 
                              {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}
                              placeholder="Please provide detailed description of the issue...">{{ old('description', $maintenanceRequest->description) }}</textarea>
                    @error('description')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Priority Section - Matching create file design exactly -->
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
                                   {{ (old('priority', $maintenanceRequest->priority) == 'low') ? 'checked' : '' }}
                                   {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                            <label for="priority_low">🐢 Low</label>
                        </div>
                        <div class="priority-option priority-medium">
                            <input type="radio" name="priority" id="priority_medium" value="medium" 
                                   {{ (old('priority', $maintenanceRequest->priority) == 'medium') ? 'checked' : '' }}
                                   {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                            <label for="priority_medium">⚡ Medium</label>
                        </div>
                        <div class="priority-option priority-high">
                            <input type="radio" name="priority" id="priority_high" value="high" 
                                   {{ (old('priority', $maintenanceRequest->priority) == 'high') ? 'checked' : '' }}
                                   {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                            <label for="priority_high">🔥 High</label>
                        </div>
                        <div class="priority-option priority-emergency">
                            <input type="radio" name="priority" id="priority_emergency" value="emergency" 
                                   {{ (old('priority', $maintenanceRequest->priority) == 'emergency') ? 'checked' : '' }}
                                   {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                            <label for="priority_emergency">🚨 Emergency</label>
                        </div>
                    </div>
                    @if(in_array($maintenanceRequest->status, ['completed', 'cancelled']))
                        <input type="hidden" name="priority" value="{{ $maintenanceRequest->priority }}">
                    @endif
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
            
            <!-- Assignment & Cost Section -->
            <div class="form-section">
                <div class="section-title">
                    <div>👥</div>
                    <span>Assignment & Cost</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Assigned Vendor</label>
                        <select name="assigned_vendor_id" class="form-control" 
                                {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                            <option value="">Select Vendor (Optional)</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" 
                                        {{ old('assigned_vendor_id', $maintenanceRequest->assigned_vendor_id) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @if(in_array($maintenanceRequest->status, ['completed', 'cancelled']) && $maintenanceRequest->assigned_vendor_id)
                            <input type="hidden" name="assigned_vendor_id" value="{{ $maintenanceRequest->assigned_vendor_id }}">
                        @endif
                        @error('assigned_vendor_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Assigned Staff</label>
                        <input type="text" name="assigned_staff" class="form-control" 
                               value="{{ old('assigned_staff', $maintenanceRequest->assigned_staff) }}"
                               placeholder="e.g., John Doe"
                               {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                        @error('assigned_staff')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Scheduled Date</label>
                        <input type="date" name="scheduled_date" class="form-control" 
                               value="{{ old('scheduled_date', $maintenanceRequest->scheduled_date ? $maintenanceRequest->scheduled_date->format('Y-m-d') : '') }}"
                               min="{{ date('Y-m-d') }}"
                               {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                        @error('scheduled_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Completion Date</label>
                        <input type="date" name="completion_date" class="form-control" 
                               value="{{ old('completion_date', $maintenanceRequest->completion_date ? $maintenanceRequest->completion_date->format('Y-m-d') : '') }}"
                               max="{{ date('Y-m-d') }}"
                               {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                        @error('completion_date')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Estimated Cost</label>
                        <input type="number" name="estimated_cost" class="form-control" step="0.01" min="0"
                               value="{{ old('estimated_cost', $maintenanceRequest->estimated_cost) }}"
                               placeholder="e.g., 150.00"
                               {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                        @error('estimated_cost')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Actual Cost</label>
                        <input type="number" name="actual_cost" class="form-control" step="0.01" min="0"
                               value="{{ old('actual_cost', $maintenanceRequest->actual_cost) }}"
                               placeholder="e.g., 150.00"
                               {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                        @error('actual_cost')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="form-section">
                <div class="section-title">
                    <div>📎</div>
                    <span>Additional Information</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Access Instructions</label>
                    <textarea name="access_instructions" class="form-control" rows="3" 
                              {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}
                              placeholder="e.g., Call tenant before arrival, Gate code 1234, etc.">{{ old('access_instructions', $maintenanceRequest->access_instructions) }}</textarea>
                    @error('access_instructions')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Internal Notes</label>
                    <textarea name="internal_notes" class="form-control" rows="3" 
                              {{ in_array($maintenanceRequest->status, ['completed', 'cancelled']) ? 'readonly' : '' }}
                              placeholder="Any internal notes for staff (not visible to tenant)">{{ old('internal_notes', $maintenanceRequest->internal_notes) }}</textarea>
                    @error('internal_notes')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="checkFormChanges()">
                    Cancel
                </button>
                
                @if(!in_array($maintenanceRequest->status, ['completed', 'cancelled']))
                    <button type="submit" class="btn btn-primary">
                        Update Request
                    </button>
                @endif
            </div>
        </form> <!-- END EDIT FORM -->
    </div> <!-- Close form-container -->
</div> <!-- Close container -->

<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Discard Changes</h3>
            <button class="modal-close" onclick="closeCancelModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to discard your changes? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeCancelModal()">Continue Editing</button>
            <a href="{{ route('maintenance-requests.show', $maintenanceRequest) }}" class="btn btn-danger">Discard</a>
        </div>
    </div>
</div>

<style>
/* Additional styles specific to edit maintenance request form */
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

.status-bar {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    border: 1px solid #dee2e6;
}

.status-info {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-badge-emergency {
    background: #e74c3c;
    color: white;
}

.priority-badge-high {
    background: #e67e22;
    color: white;
}

.priority-badge-medium {
    background: #f39c12;
    color: white;
}

.priority-badge-low {
    background: #27ae60;
    color: white;
}

.status-submitted {
    background: #e8f4fc;
    color: #2c3e50;
    border: 1px solid #d1e4ff;
}

.status-assigned {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-in_progress {
    background: #cce5ff;
    color: #004085;
    border: 1px solid #b8daff;
}

.status-completed {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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

.form-control:disabled, .form-control[readonly] {
    background: #f8f9fa;
    cursor: not-allowed;
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
    display: inline-flex;
    align-items: center;
    gap: 8px;
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

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
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

.warning-message {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 99999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #666;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    margin-bottom: 20px;
    color: #666;
    line-height: 1.5;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.alert {
    padding: 15px 20px;
    border-radius: 4px;
    margin-bottom: 20px;
    border-left: 4px solid;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left-color: #28a745;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border-left-color: #dc3545;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border-left-color: #ffc107;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border-left-color: #17a2b8;
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

    .status-bar {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-actions {
        flex-direction: column;
        gap: 10px;
    }

    .btn {
        width: 100%;
        text-align: center;
    }

    .modal-content {
        margin: 30% auto;
        width: 95%;
    }
}
</style>

<script>
// Store units data
let unitsData = {};
let formDirty = false;

// Load units when building is selected
function loadUnits() {
    const buildingId = document.getElementById('building_id').value;
    const unitSelect = document.getElementById('unit_id');
    const unitHelp = document.getElementById('unit-help');
    const currentUnitId = {{ $maintenanceRequest->unit_id }};
    
    // Clear current options
    unitSelect.innerHTML = '<option value="">Select Unit</option>';
    
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
                
                // Preselect current unit
                if (unit.id == currentUnitId) {
                    option.selected = true;
                    loadTenants(buildingId, unit.id);
                }
                
                unitSelect.appendChild(option);
            });
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
    const currentTenantId = {{ $maintenanceRequest->tenant_id ?? 'null' }};
    
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
                
                // Preselect current tenant
                if (tenant.id == currentTenantId) {
                    option.selected = true;
                }
                
                tenantSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading tenants:', error);
            tenantSelect.disabled = false;
            tenantSelect.innerHTML = '<option value="">No tenants found for this unit</option>';
        });
}

// When unit is selected, load its tenants
const unitSelect = document.getElementById('unit_id');
if (unitSelect) {
    unitSelect.addEventListener('change', function() {
        const buildingId = document.getElementById('building_id').value;
        const unitId = this.value;
        if (unitId) {
            loadTenants(buildingId, unitId);
        }
    });
}

// Track form changes
function trackFormChanges() {
    const form = document.getElementById('editForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        if (input.type !== 'hidden' && !input.disabled && !input.readonly) {
            input.addEventListener('change', () => {
                formDirty = true;
            });
            input.addEventListener('keyup', () => {
                formDirty = true;
            });
        }
    });
}

// Check form changes before cancel
function checkFormChanges() {
    if (formDirty) {
        showCancelModal();
    } else {
        window.location.href = '{{ route("maintenance-requests.show", $maintenanceRequest) }}';
    }
}

// Modal functions
function showCancelModal() {
    document.getElementById('cancelModal').style.display = 'block';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('cancelModal');
    if (event.target == modal) {
        closeCancelModal();
    }
}

// Load units on page load
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

    // Load units if building is already selected
    const buildingSelect = document.getElementById('building_id');
    if (buildingSelect && buildingSelect.value) {
        loadUnits();
    }

    // Format currency inputs
    const costInputs = document.querySelectorAll('input[name="estimated_cost"], input[name="actual_cost"]');
    costInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                const value = parseFloat(this.value).toFixed(2);
                this.value = value;
            }
        });
    });

    // Track form changes
    trackFormChanges();

    // Reset dirty flag on form submit
    const form = document.getElementById('editForm');
    form.addEventListener('submit', function() {
        formDirty = false;
    });
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