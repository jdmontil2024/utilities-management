@extends('layouts.app')

@section('title', $maintenanceRequest->title . ' - Maintenance Request #' . $maintenanceRequest->id)

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Maintenance Request Details</h1>
            <p class="page-subtitle">View and manage maintenance request information</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Request Header -->
    <div class="request-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="request-title">{{ $maintenanceRequest->title }}</h1>
                <div class="request-meta">
                    @php
                        $priorityClass = match($maintenanceRequest->priority) {
                            'emergency' => 'priority-emergency',
                            'high' => 'priority-high',
                            'medium' => 'priority-medium',
                            'low' => 'priority-low',
                            default => ''
                        };
                        
                        $statusValue = $maintenanceRequest->status;
                        $normalizedStatus = match($statusValue) {
                            'complete', 'done', 'finished' => 'completed',
                            default => $statusValue
                        };
                        
                        $statusClass = match($normalizedStatus) {
                            'submitted' => 'status-submitted',
                            'assigned' => 'status-assigned',
                            'in_progress' => 'status-in_progress',
                            'completed' => 'status-completed',
                            'cancelled' => 'status-cancelled',
                            default => ''
                        };
                        
                        // Calculate if request is overdue based on SLA
                        $isOverdue = false;
                        if ($maintenanceRequest->maintenanceCategory && $maintenanceRequest->maintenanceCategory->sla_hours) {
                            $slaDeadline = $maintenanceRequest->created_at->addHours($maintenanceRequest->maintenanceCategory->sla_hours);
                            $isOverdue = now()->greaterThan($slaDeadline) && !in_array($maintenanceRequest->status, ['completed', 'cancelled']);
                        }
                    @endphp
                    
                    <div class="meta-item">
                        <span class="badge {{ $priorityClass }}">
                            {{ strtoupper($maintenanceRequest->priority) }} PRIORITY
                        </span>
                    </div>
                    
                    <div class="meta-item">
                        <span class="badge {{ $statusClass }}">
                            {{ ucfirst(str_replace('_', ' ', $normalizedStatus)) }}
                        </span>
                    </div>
                    
                    @if($isOverdue)
                    <div class="meta-item">
                        <span class="badge" style="border-color: #e74c3c; color: #e74c3c;">
                            ⚠️ OVERDUE
                        </span>
                    </div>
                    @endif
                    
                    <div class="meta-item">
                        Requested: {{ $maintenanceRequest->request_date->format('M d, Y') }}
                    </div>
                    
                    <div class="meta-item">
                        {{ $maintenanceRequest->maintenanceCategory->name ?? 'Uncategorized' }}
                    </div>
                    
                    @if($maintenanceRequest->maintenanceCategory && $maintenanceRequest->maintenanceCategory->sla_hours)
                    <div class="meta-item">
                        SLA: {{ $maintenanceRequest->maintenanceCategory->sla_hours }} hours
                    </div>
                    @endif
                </div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('maintenance-requests.edit', $maintenanceRequest) }}" class="btn">
                    Edit Request
                </a>
                <a href="{{ route('maintenance-requests.index') }}" class="btn">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @php
        $daysOpen = (int)$maintenanceRequest->created_at->diffInDays(now());
    @endphp
    
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value">{{ $maintenanceRequest->tenant->full_name ?? 'Management' }}</span>
            <span class="stat-label">Reported By</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $maintenanceRequest->unit->unit_number ?? 'N/A' }}</span>
            <span class="stat-label">Unit</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">—</span>
            <span class="stat-label">No Cost</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $daysOpen }}</span>
            <span class="stat-label">Days Open</span>
        </div>
    </div>

    <!-- Tab Interface -->
    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" data-tab="overview">Overview</button>
            <button class="tab-button" data-tab="details">Details</button>
            <button class="tab-button" data-tab="timeline">Timeline</button>
            <button class="tab-button" data-tab="costs">Costs</button>
        </div>
        
        <div class="tab-content">
            <!-- Overview Tab - 4-box layout with combined boxes -->
            <div class="tab-pane active" id="overview">
                <div class="overview-grid">
                    <!-- Box 1: Location Details -->
                    <div class="overview-box">
                        <div class="overview-box-header">Location Details</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Building</div>
                                <div class="info-value">
                                    @if($maintenanceRequest->unit && $maintenanceRequest->unit->building)
                                        <a href="{{ route('buildings.show', $maintenanceRequest->unit->building) }}" style="color: #4a5568; text-decoration: none;">
                                            {{ $maintenanceRequest->unit->building->name }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Unit</div>
                                <div class="info-value">
                                    @if($maintenanceRequest->unit)
                                        <a href="{{ route('units.show', $maintenanceRequest->unit) }}" style="color: #4a5568; text-decoration: none;">
                                            Unit {{ $maintenanceRequest->unit->unit_number }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            @if($maintenanceRequest->unit && $maintenanceRequest->unit->building)
                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value">{{ $maintenanceRequest->unit->building->full_address ?? 'N/A' }}</div>
                            </div>
                            @endif
                            @if($maintenanceRequest->unit && $maintenanceRequest->unit->floor)
                            <div class="info-item">
                                <div class="info-label">Floor</div>
                                <div class="info-value">{{ $maintenanceRequest->unit->floor }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Box 2: Assignment -->
                    <div class="overview-box">
                        <div class="overview-box-header">Assignment</div>
                        <div class="overview-box-content">
                            @if($maintenanceRequest->assignedVendor)
                                <div class="info-item">
                                    <div class="info-label">Vendor</div>
                                    <div class="info-value">
                                        <a href="{{ route('vendors.show', $maintenanceRequest->assignedVendor) }}" style="color: #4a5568; text-decoration: none;">
                                            {{ $maintenanceRequest->assignedVendor->company_name }}
                                        </a>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Contact</div>
                                    <div class="info-value">{{ $maintenanceRequest->assignedVendor->phone ?? 'N/A' }}</div>
                                </div>
                            @elseif($maintenanceRequest->assignedStaff)
                                <div class="info-item">
                                    <div class="info-label">Staff</div>
                                    <div class="info-value">{{ $maintenanceRequest->assignedStaff->name }}</div>
                                </div>
                            @else
                                <div class="info-item">
                                    <div class="info-value" style="color: #e74c3c;">Not Assigned</div>
                                </div>
                            @endif
                            
                            @if($maintenanceRequest->assigned_date)
                            <div class="info-item">
                                <div class="info-label">Assigned Date</div>
                                <div class="info-value">{{ $maintenanceRequest->assigned_date->format('M d, Y') }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Box 3: Tenant Information & Request Details (Combined) -->
                    <div class="overview-box">
                        <div class="overview-box-header">Tenant & Request Details</div>
                        <div class="overview-box-content">
                            <!-- Tenant Information -->
                            @if($maintenanceRequest->tenant)
                            <div class="info-item">
                                <div class="info-label">Tenant Name</div>
                                <div class="info-value">
                                    <a href="{{ route('tenants.show', $maintenanceRequest->tenant) }}" style="color: #4a5568; text-decoration: none;">
                                        {{ $maintenanceRequest->tenant->full_name }}
                                    </a>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Tenant Phone</div>
                                <div class="info-value">{{ $maintenanceRequest->tenant->phone ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Tenant Email</div>
                                <div class="info-value">{{ $maintenanceRequest->tenant->email ?? 'N/A' }}</div>
                            </div>
                            @else
                            <div class="info-item">
                                <div class="info-label">Reported By</div>
                                <div class="info-value">Management</div>
                            </div>
                            @endif
                            
                            <!-- Request Details -->
                            <div class="info-item">
                                <div class="info-label">Request Date</div>
                                <div class="info-value">{{ $maintenanceRequest->request_date->format('M d, Y') }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Created</div>
                                <div class="info-value">{{ $maintenanceRequest->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                            @if($maintenanceRequest->scheduled_date)
                            <div class="info-item">
                                <div class="info-label">Scheduled</div>
                                <div class="info-value">{{ $maintenanceRequest->scheduled_date->format('M d, Y') }}</div>
                            </div>
                            @endif
                            @if($maintenanceRequest->completion_date)
                            <div class="info-item">
                                <div class="info-label">Completed</div>
                                <div class="info-value">{{ $maintenanceRequest->completion_date->format('M d, Y') }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Box 4: Description & Resolution Notes (Combined) -->
                    <div class="overview-box">
                        <div class="overview-box-header">Description & Resolution</div>
                        <div class="overview-box-content">
                            <!-- Description -->
                            <div class="info-item">
                                <div class="info-label">Description</div>
                                <div class="info-value description-text">{{ $maintenanceRequest->description }}</div>
                            </div>
                            
                            <!-- Resolution Notes (if exists) -->
                            @if($maintenanceRequest->status === 'completed' && $maintenanceRequest->resolution_notes)
                            <div class="info-item" style="margin-top: 15px; border-top: 2px solid #dee2e6; padding-top: 15px;">
                                <div class="info-label" style="color: #155724;">Resolution Notes</div>
                                <div class="info-value description-text" style="color: #155724;">{{ $maintenanceRequest->resolution_notes }}</div>
                                @if($maintenanceRequest->completion_date)
                                <div style="margin-top: 10px; color: #6c757d; font-size: 11px; text-align: right;">
                                    Completed: {{ $maintenanceRequest->completion_date->format('M d, Y h:i A') }}
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Feedback Card (if exists) -->
                @if($maintenanceRequest->tenant_rating || $maintenanceRequest->tenant_feedback)
                <div style="margin-top: 30px;">
                    <div class="feedback-box">
                        <div class="feedback-header">Tenant Feedback</div>
                        <div class="feedback-content">
                            @if($maintenanceRequest->tenant_rating)
                            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                <span class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $maintenanceRequest->tenant_rating)
                                            ★
                                        @else
                                            ☆
                                        @endif
                                    @endfor
                                </span>
                                <span style="font-weight: 600; margin-left: 10px;">{{ $maintenanceRequest->tenant_rating }}/5</span>
                            </div>
                            @endif
                            
                            @if($maintenanceRequest->tenant_feedback)
                            <div class="feedback-text">
                                "{{ $maintenanceRequest->tenant_feedback }}"
                            </div>
                            @endif
                            
                            @if($maintenanceRequest->feedback_date)
                            <div style="margin-top: 15px; color: #6c757d; font-size: 12px; text-align: right;">
                                Feedback: {{ $maintenanceRequest->feedback_date->format('M d, Y') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Quick Actions -->
                <div class="action-buttons" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                    @if($maintenanceRequest->status !== 'completed' && $maintenanceRequest->status !== 'cancelled')
                        <button onclick="openModal('updateStatusModal')" class="btn">
                            Update Status
                        </button>
                    @endif
                    
                    @if(!$maintenanceRequest->assigned_vendor_id && $maintenanceRequest->status === 'submitted')
                        <button onclick="openModal('assignVendorModal')" class="btn">
                            Assign Vendor
                        </button>
                    @endif
                    
                    @if($maintenanceRequest->status === 'completed' && !$maintenanceRequest->tenant_rating)
                        <button onclick="openModal('feedbackModal')" class="btn">
                            Add Feedback
                        </button>
                    @endif
                    
                    <form action="{{ route('maintenance-requests.destroy', $maintenanceRequest) }}" 
                          method="POST" 
                          class="delete-form"
                          onsubmit="return confirmDelete()">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            Delete Request
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Details Tab -->
            <div class="tab-pane" id="details">
                @if($maintenanceRequest->access_instructions)
                <div class="access-box">
                    <div class="access-header">
                        <span>🔑</span>
                        <span>Access Instructions</span>
                    </div>
                    <div class="access-body">
                        {{ $maintenanceRequest->access_instructions }}
                    </div>
                </div>
                @endif
                
                @if($maintenanceRequest->internal_notes)
                <div class="access-box internal">
                    <div class="access-header internal">
                        <span>📌</span>
                        <span>Internal Notes</span>
                    </div>
                    <div class="access-body">
                        {{ $maintenanceRequest->internal_notes }}
                    </div>
                </div>
                @endif
                
                @if(!$maintenanceRequest->access_instructions && !$maintenanceRequest->internal_notes)
                <div class="no-data" style="margin: 20px;">
                    <div class="no-data-icon">📋</div>
                    <h3>No Additional Details</h3>
                    <p>This request has no access instructions or internal notes.</p>
                </div>
                @endif
            </div>
            
            <!-- Timeline Tab -->
            <div class="tab-pane" id="timeline">
                <div class="timeline">
                    @if(isset($timeline) && $timeline->count() > 0)
                        @foreach($timeline as $event)
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $event->created_at->format('M d, Y h:i A') }}</div>
                            <div class="timeline-content">
                                <div class="timeline-status">
                                    @php
                                        $statusIcon = match($event->status) {
                                            'submitted' => '📝',
                                            'assigned' => '👤',
                                            'in_progress' => '🔨',
                                            'completed' => '✅',
                                            'cancelled' => '❌',
                                            default => '📌'
                                        };
                                    @endphp
                                    {{ $statusIcon }} {{ ucfirst(str_replace('_', ' ', $event->status)) }}
                                </div>
                                @if($event->notes)
                                <div class="timeline-note">{{ $event->notes }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="no-data" style="margin: 0;">
                            <div class="no-data-icon">⏰</div>
                            <h3>No Timeline Events</h3>
                            <p>No activity timeline is available for this request.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Costs Tab -->
            <div class="tab-pane" id="costs">
                <div class="cost-grid">
                    @if($maintenanceRequest->estimated_cost)
                    <div class="cost-item">
                        <div class="cost-label">Estimated Cost</div>
                        <div class="cost-value">₱{{ number_format($maintenanceRequest->estimated_cost, 2) }}</div>
                    </div>
                    @endif
                    
                    @if($maintenanceRequest->actual_cost)
                    <div class="cost-item">
                        <div class="cost-label">Actual Cost</div>
                        <div class="cost-value">₱{{ number_format($maintenanceRequest->actual_cost, 2) }}</div>
                    </div>
                    @endif
                    
                    @if($maintenanceRequest->estimated_cost && $maintenanceRequest->actual_cost)
                    @php
                        $variance = $maintenanceRequest->actual_cost - $maintenanceRequest->estimated_cost;
                        $varianceClass = $variance > 0 ? 'text-danger' : ($variance < 0 ? 'text-success' : '');
                    @endphp
                    <div class="cost-item">
                        <div class="cost-label">Variance</div>
                        <div class="cost-value {{ $varianceClass }}">
                            {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                        </div>
                    </div>
                    @endif
                    
                    @if(!$maintenanceRequest->estimated_cost && !$maintenanceRequest->actual_cost)
                    <div class="cost-item" style="grid-column: 1/-1; text-align: center;">
                        <div class="cost-value" style="color: #6c757d;">No cost information available</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal" id="updateStatusModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Status - #{{ $maintenanceRequest->id }}</h3>
            <button class="modal-close" onclick="closeModal('updateStatusModal')">&times;</button>
        </div>
        <form action="{{ route('maintenance-requests.update-status', $maintenanceRequest) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">New Status</label>
                    <select name="status" id="statusSelect" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="assigned" {{ $maintenanceRequest->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ $maintenanceRequest->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $maintenanceRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $maintenanceRequest->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                <!-- Fields that appear when "Completed" is selected -->
                <div id="completedFields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Actual Cost (₱) <span style="color: #e74c3c;">*</span></label>
                        <input type="number" name="actual_cost" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Resolution Notes <span style="color: #e74c3c;">*</span></label>
                        <textarea name="resolution_notes" class="form-control" rows="3" placeholder="Describe what was done..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('updateStatusModal')">Cancel</button>
                <button type="submit" class="btn">Update Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Vendor Modal -->
<div class="modal" id="assignVendorModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Assign Vendor - #{{ $maintenanceRequest->id }}</h3>
            <button class="modal-close" onclick="closeModal('assignVendorModal')">&times;</button>
        </div>
        <form action="{{ route('maintenance-requests.assign-vendor', $maintenanceRequest) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Vendor</label>
                    <select name="vendor_id" class="form-control" required>
                        <option value="">Choose Vendor</option>
                        @foreach(App\Models\Vendor::all() as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Scheduled Date</label>
                    <input type="date" name="scheduled_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Estimated Cost (₱)</label>
                    <input type="number" name="estimated_cost" class="form-control" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('assignVendorModal')">Cancel</button>
                <button type="submit" class="btn">Assign Vendor</button>
            </div>
        </form>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal" id="feedbackModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add Feedback - #{{ $maintenanceRequest->id }}</h3>
            <button class="modal-close" onclick="closeModal('feedbackModal')">&times;</button>
        </div>
        <form action="{{ route('maintenance-requests.feedback', $maintenanceRequest) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-control" required>
                        <option value="">Select Rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Very Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Feedback</label>
                    <textarea name="feedback" class="form-control" rows="4" required placeholder="Share your experience..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('feedbackModal')">Cancel</button>
                <button type="submit" class="btn">Submit Feedback</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Additional styles specific to maintenance request details view */
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
}

.page-subtitle {
    color: #6c757d;
    margin-top: 5px;
}

.request-header {
    background: white;
    color: #2c3e50;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
    border: 1px solid #dee2e6;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 20px;
}

.header-left {
    flex: 1;
    min-width: 300px;
}

.request-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #2c3e50;
}

.request-meta {
    display: flex;
    gap: 15px;
    font-size: 14px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.meta-item {
    background: #f8f9fa;
    padding: 8px 15px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border-radius: 4px;
    border: 1px solid #3498db;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
    background: transparent;
    color: #3498db;
}

.btn:hover {
    background: #3498db;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,.1);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border: 1px solid #3498db;
    background: transparent;
    color: #3498db;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    line-height: 1;
    height: 32px;
}

.btn-sm:hover {
    background: #3498db;
    color: white;
}

.btn-danger {
    border: 1px solid #e74c3c;
    color: #e74c3c;
}

.btn-danger:hover {
    background: #e74c3c;
    color: white;
}

.btn-success {
    border: 1px solid #27ae60;
    color: #27ae60;
}

.btn-success:hover {
    background: #27ae60;
    color: white;
}

.btn-warning {
    border: 1px solid #e67e22;
    color: #e67e22;
}

.btn-warning:hover {
    background: #e67e22;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
    border: 1px solid #dee2e6;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    display: block;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 13px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.tab-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
    border: 1px solid #dee2e6;
    margin-bottom: 40px;
}

.tab-header {
    display: flex;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    overflow-x: auto;
}

.tab-button {
    padding: 18px 30px;
    border: none;
    background: none;
    cursor: pointer;
    font-weight: 500;
    color: #6c757d;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    white-space: nowrap;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
}

.tab-button:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-button.active {
    color: #3498db;
    border-bottom-color: #3498db;
    background: white;
    font-weight: 600;
}

.tab-content {
    padding: 30px;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.overview-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.overview-box {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
    border: 1px solid #dee2e6;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: fit-content;
}

.overview-box-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    font-weight: 700;
    color: #2c3e50;
    font-size: 16px;
    text-align: center;
    letter-spacing: 0.3px;
}

.overview-box-content {
    padding: 20px;
    flex: 1;
}

.info-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.info-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

.info-value {
    font-size: 15px;
    font-weight: 600;
    color: #2c3e50;
    word-break: break-word;
}

.description-text {
    color: #2c3e50;
    font-size: 14px;
    line-height: 1.8;
    margin: 0;
    word-break: break-word;
}

@media (max-width: 992px) {
    .overview-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .overview-grid {
        grid-template-columns: 1fr;
    }
}

.badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    min-width: 100px;
    background: transparent;
    border: 1px solid;
}

.priority-emergency {
    border-color: #e74c3c;
    color: #e74c3c;
}

.priority-high {
    border-color: #e67e22;
    color: #e67e22;
}

.priority-medium {
    border-color: #f39c12;
    color: #f39c12;
}

.priority-low {
    border-color: #27ae60;
    color: #27ae60;
}

.status-submitted {
    border-color: #2c3e50;
    color: #2c3e50;
}

.status-assigned {
    border-color: #856404;
    color: #856404;
}

.status-in_progress {
    border-color: #004085;
    color: #004085;
}

.status-completed {
    border-color: #155724;
    color: #155724;
}

.status-cancelled {
    border-color: #721c24;
    color: #721c24;
}

.type-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    background: transparent;
    border: 1px solid #4a5568;
    color: #4a5568;
    text-align: center;
    min-width: 100px;
}

.feedback-box {
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    overflow: hidden;
    margin-top: 30px;
}

.feedback-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    font-weight: 700;
    color: #2c3e50;
    font-size: 16px;
    letter-spacing: 0.3px;
}

.feedback-content {
    padding: 20px;
}

.rating-stars {
    color: #f39c12;
    font-size: 18px;
    letter-spacing: 2px;
}

.feedback-text {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    color: #2c3e50;
    font-style: italic;
    line-height: 1.6;
    margin-top: 10px;
}

.cost-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.cost-item {
    background: white;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.cost-label {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cost-value {
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
}

.text-success {
    color: #27ae60;
}

.text-danger {
    color: #e74c3c;
}

.timeline {
    margin-top: 30px;
}

.timeline-item {
    display: flex;
    gap: 20px;
    padding: 15px 0;
    border-bottom: 1px solid #e9ecef;
}

.timeline-item:last-child {
    border-bottom: none;
}

.timeline-date {
    min-width: 160px;
    color: #6c757d;
    font-size: 13px;
}

.timeline-content {
    flex: 1;
}

.timeline-status {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.timeline-note {
    color: #6c757d;
    font-size: 13px;
}

.access-box {
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    overflow: hidden;
    margin-bottom: 20px;
}

.access-box.internal {
    border-color: #ffeaa7;
}

.access-header {
    background: #f8f9fa;
    padding: 12px 20px;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.access-header.internal {
    background: #fff3cd;
    color: #856404;
    border-bottom: 1px solid #ffeaa7;
}

.access-body {
    padding: 20px;
}

.delete-form {
    margin: 0;
    padding: 0;
    display: inline;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    padding: 25px;
    animation: modalSlideIn 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6c757d;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
}

.alert {
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 25px;
    font-size: 14px;
    border: 1px solid;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.no-data {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: white;
}

.no-data h3 {
    margin-bottom: 10px;
    font-weight: 600;
    color: #2c3e50;
}

.no-data p {
    margin-bottom: 20px;
    color: #6c757d;
}

.no-data-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.3;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 20px;
    }
    
    .request-header {
        padding: 25px;
    }
    
    .request-title {
        font-size: 24px;
    }
    
    .request-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .tab-button {
        padding: 15px 20px;
        font-size: 13px;
    }
    
    .stat-value {
        font-size: 24px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .cost-grid {
        grid-template-columns: 1fr;
    }
    
    .timeline-item {
        flex-direction: column;
        gap: 5px;
    }
    
    .timeline-date {
        min-width: auto;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Tab Switching
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            window.location.hash = tabId;
        });
    });
    
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const activeTab = document.querySelector(`.tab-button[data-tab="${hash}"]`);
        if (activeTab) {
            activeTab.click();
        }
    }
    
    // Show/hide completed fields based on status selection
    const statusSelect = document.getElementById('statusSelect');
    const completedFields = document.getElementById('completedFields');
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'completed') {
                completedFields.style.display = 'block';
                document.querySelector('[name="actual_cost"]').required = true;
                document.querySelector('[name="resolution_notes"]').required = true;
            } else {
                completedFields.style.display = 'none';
                document.querySelector('[name="actual_cost"]').required = false;
                document.querySelector('[name="resolution_notes"]').required = false;
            }
        });
        
        if (statusSelect.value === 'completed') {
            completedFields.style.display = 'block';
            document.querySelector('[name="actual_cost"]').required = true;
            document.querySelector('[name="resolution_notes"]').required = true;
        }
    }
});

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Delete confirmation
function confirmDelete() {
    return confirm('Are you sure you want to delete this maintenance request? This action cannot be undone.');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
};

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
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
        showToast('{{ session('success') }}', 'success');
    });
@endif

@if(session('error'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session('error') }}', 'error');
    });
@endif
</script>
@endsection