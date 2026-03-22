@extends('layouts.app')

@section('title', $building->name . ' - Building Details')

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
    }

    /* BUILDING HEADER */
    .building-header {
        background: white;
        color: #2c3e50;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
    }
    
    .building-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #2c3e50;
    }
    
    .building-address {
        font-size: 15px;
        color: #6c757d;
        margin-bottom: 20px;
    }
    
    .building-meta {
        display: flex;
        gap: 20px;
        font-size: 14px;
        flex-wrap: wrap;
    }
    
    .meta-item {
        background: #f8f9fa;
        padding: 8px 15px;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }
    
    /* BUTTONS */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        border-radius: 4px;
        border: 1px solid #4a5568;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif;
        background: transparent;
        color: #4a5568;
    }

    .btn:hover {
        background: #4a5568;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
        text-decoration: none;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border: 1px solid #4a5568;
        background: transparent;
        color: #4a5568;
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
        background: #4a5568;
        color: white;
        text-decoration: none;
    }

    .btn-danger {
        border: 1px solid #2d3748;
        color: #2d3748;
    }

    .btn-danger:hover {
        background: #2d3748;
        color: white;
    }

    /* STATS GRID */
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
    
    /* TAB CONTAINER */
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
        color: #4a5568;
        border-bottom-color: #4a5568;
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
    
    /* OVERVIEW 4-BOX LAYOUT */
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

    /* Responsive for overview grid */
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
    
    /* UNITS TABLE */
    .units-table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        overflow-x: auto;
        border: 1px solid #dee2e6;
        margin-bottom: 30px;
    }

    .units-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .units-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
        text-align: center;
    }

    .units-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
        text-align: center;
    }

    .units-table td:first-child,
    .units-table td:nth-child(2),
    .units-table td:nth-child(6) {
        text-align: left;
    }

    .units-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }
    
    /* TENANTS TABLE */
    .tenants-table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        overflow-x: auto;
        border: 1px solid #dee2e6;
        margin-bottom: 30px;
    }

    .tenants-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .tenants-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
        text-align: center;
    }

    .tenants-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
        text-align: center;
    }

    .tenants-table td:first-child,
    .tenants-table td:nth-child(2),
    .tenants-table td:nth-child(3) {
        text-align: left;
    }

    .tenants-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }
    
    /* MAINTENANCE TABLE */
    .maintenance-table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        overflow-x: auto;
        border: 1px solid #dee2e6;
        margin-bottom: 30px;
    }

    .maintenance-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1100px;
    }

    .maintenance-table th {
        background: #f8f9fa;
        padding: 18px 15px;
        font-weight: 600;
        color: #2c3e50;
        border: 1px solid #dee2e6;
        font-size: 14px;
        white-space: nowrap;
        text-align: center;
    }

    .maintenance-table td {
        padding: 16px 15px;
        border: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        line-height: 1.4;
        text-align: center;
    }

    .maintenance-table td:nth-child(1),
    .maintenance-table td:nth-child(5) {
        text-align: left;
    }

    .maintenance-table tbody tr:hover td {
        background: #f8f9fa;
        border-color: #cfe2ff;
    }
    
    /* BADGES - No background colors */
    .status-badge {
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

    .status-occupied {
        border-color: #155724;
        color: #155724;
    }

    .status-vacant {
        border-color: #495057;
        color: #495057;
    }

    .status-maintenance {
        border-color: #856404;
        color: #856404;
    }

    .status-pending {
        border-color: #856404;
        color: #856404;
    }

    .status-reserved {
        border-color: #2c3e50;
        color: #2c3e50;
    }

    .status-ready {
        border-color: #155724;
        color: #155724;
    }

    .status-expired {
        border-color: #721c24;
        color: #721c24;
    }

    .status-terminated {
        border-color: #2c3e50;
        color: #2c3e50;
    }

    .status-active {
        border-color: #155724;
        color: #155724;
    }

    /* PRIORITY BADGES */
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

    /* STATUS BADGES for maintenance */
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

    /* Overdue badge */
    .overdue-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        min-width: 70px;
        border: 1px solid #e74c3c;
        color: #e74c3c;
        margin-left: 5px;
    }

    /* ACTION BUTTONS */
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    /* DELETE FORM */
    .delete-form {
        margin: 0;
        padding: 0;
        display: flex;
        flex: 1;
    }

    /* EMPTY STATE */
    .no-data {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin: 20px;
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
    
    /* HEADER CONTENT LAYOUT */
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
    
    /* PHOTO SECTION */
    .photo-section {
        margin-bottom: 30px;
    }

    .photo-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .photo-title {
        font-size: 18px;
        color: #2c3e50;
        font-weight: 600;
    }

    .photo-upload-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .photo-upload-toggle .btn {
        padding: 8px 16px;
        border: 1px solid #4a5568;
        background: transparent;
        color: #4a5568;
    }

    .photo-upload-toggle .btn:hover {
        background: #4a5568;
        color: white;
    }

    .photo-upload-panel {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        display: none;
    }

    .photo-upload-panel.active {
        display: block;
    }

    .photo-upload-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }

    .photo-upload-field {
        flex: 1;
        min-width: 200px;
    }

    .photo-upload-field label {
        display: block;
        margin-bottom: 5px;
        font-size: 12px;
        font-weight: 500;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .photo-upload-field input,
    .photo-upload-field select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
    }

    .photo-upload-field input:focus,
    .photo-upload-field select:focus {
        outline: none;
        border-color: #4a5568;
    }

    .file-input-wrapper {
        position: relative;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .file-input-button {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 13px;
        color: #495057;
        cursor: pointer;
    }

    .file-input-button:hover {
        background: #f1f3f5;
    }

    .selected-files {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }

    .category-filter {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    
    .category-btn {
        padding: 6px 14px;
        background: transparent;
        border: 1px solid #dee2e6;
        border-radius: 20px;
        cursor: pointer;
        font-size: 12px;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    
    .category-btn:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }
    
    .category-btn.active {
        background: #4a5568;
        color: white;
        border-color: #4a5568;
    }

    /* Photo Gallery */
    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-top: 20px;
    }

    /* Photo Card */
    .photo-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
        position: relative;
        height: 100%;
        min-height: 280px;
        animation: fadeIn 0.3s ease forwards;
    }

    .photo-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,.15);
    }

    /* Photo Image Container */
    .photo-image-container {
        height: 160px;
        width: 100%;
        overflow: hidden;
        position: relative;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .photo-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .photo-card:hover .photo-image {
        transform: scale(1.05);
    }

    .primary-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #4a5568;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* Photo Card Content */
    .photo-card-content {
        padding: 12px 15px;
    }

    /* Photo Card Header */
    .photo-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .photo-category-badge {
        font-size: 11px;
        font-weight: 600;
        color: #4a5568;
        background: #f8f9fa;
        padding: 3px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .photo-date {
        font-size: 10px;
        color: #6c757d;
    }

    /* Photo Description */
    .photo-description {
        font-size: 14px;
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 12px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 40px;
    }

    /* Photo Card Actions */
    .photo-card-actions {
        display: flex;
        gap: 6px;
    }

    .photo-card-actions .btn-sm {
        flex: 1;
        height: 28px;
        font-size: 11px;
        padding: 0 8px;
        min-width: 0;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }

    .modal-close {
        position: absolute;
        top: -40px;
        right: 0;
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
    }

    .modal-image {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 4px;
    }

    .modal-description {
        color: white;
        text-align: center;
        margin-top: 15px;
        font-size: 16px;
    }

    /* Days remaining badge */
    .days-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 8px;
        background: transparent;
    }
    
    .days-warning {
        border: 1px solid #856404;
        color: #856404;
    }
    
    .days-danger {
        border: 1px solid #721c24;
        color: #721c24;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .photo-gallery {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1100px) {
        .photo-gallery {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-content {
            padding: 20px 15px !important;
        }

        .header-content {
            flex-direction: column;
            gap: 20px;
        }
        
        .building-header {
            padding: 25px;
        }
        
        .building-title {
            font-size: 24px;
        }
        
        .building-meta {
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
        
        .photo-gallery {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .photo-card {
            min-height: 260px;
        }
        
        .photo-image-container {
            height: 180px;
        }
        
        .photo-card-actions {
            flex-direction: row;
            gap: 6px;
        }
        
        .photo-card-actions .btn-sm {
            height: 32px;
            font-size: 12px;
        }
        
        .photo-upload-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .photo-upload-field {
            width: 100%;
        }
        
        .tenants-table {
            min-width: 800px;
        }
        
        .maintenance-table {
            min-width: 900px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .photo-gallery {
            grid-template-columns: 1fr;
        }
        
        .overview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Building Details</h1>
            <p>View and manage building information</p>
        </div>
    </div>

    <!-- Success/Error Messages (using layout's toast system) -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Utilities.showToast('{{ session("success") }}', 'success');
            });
        </script>
    @endif
    
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Utilities.showToast('{{ session("error") }}', 'error');
            });
        </script>
    @endif

    <!-- Building Header -->
    <div class="building-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="building-title">{{ $building->name }}</h1>
                <div class="building-address">
                    {{ $building->full_address }}
                </div>
                <div class="building-meta">
                    @if($building->building_type)
                    <div class="meta-item">
                        {{ $building->building_type_label }}
                    </div>
                    @endif
                    
                    @if($building->total_floors)
                    <div class="meta-item">
                        {{ $building->total_floors }} floors
                    </div>
                    @endif
                    
                    <div class="meta-item">
                        Status: {{ $building->status_label }}
                    </div>
                    
                    @if($building->year_built)
                    <div class="meta-item">
                        Built: {{ $building->year_built }}
                    </div>
                    @endif
                    
                    <div class="meta-item">
                        {{ $building->active_tenants_count ?? 0 }} Active Tenants
                    </div>
                </div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('buildings.edit', $building) }}" class="btn">
                    Edit Building
                </a>
                <a href="{{ route('buildings.index') }}" class="btn">
                    Back to Buildings
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @php
        $totalUnits = $building->units_count ?? $building->units->count();
        $occupiedUnits = $building->occupied_units_count ?? $building->units->where('status', 'occupied')->count();
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;
        $monthlyRevenue = $building->monthly_revenue ?? $building->total_monthly_tenant_revenue ?? 0;
        $totalTenants = $building->active_tenants_count ?? $building->currentTenants->count() ?? 0;
        
        // Safely get maintenance requests count
        $pendingMaintenanceCount = 0;
        if (method_exists($building, 'maintenanceRequests') && $building->maintenanceRequests) {
            $pendingMaintenanceCount = $building->maintenanceRequests->whereNotIn('status', ['completed', 'cancelled'])->count();
        }
    @endphp
    
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value">{{ $totalUnits }}</span>
            <span class="stat-label">Total Units</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $occupancyRate }}%</span>
            <span class="stat-label">Occupancy Rate</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">₱{{ number_format($monthlyRevenue, 0) }}</span>
            <span class="stat-label">Monthly Revenue</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $totalTenants }}</span>
            <span class="stat-label">Active Tenants</span>
        </div>
    </div>

    <!-- Tab Interface -->
    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" data-tab="overview">Overview</button>
            <button class="tab-button" data-tab="units">Units ({{ $totalUnits }})</button>
            <button class="tab-button" data-tab="tenants">Tenants ({{ $totalTenants }})</button>
            <button class="tab-button" data-tab="maintenance">Maintenance 
                <span style="background: {{ $pendingMaintenanceCount > 0 ? '#e74c3c' : '#6c757d' }}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 5px;">
                    {{ $pendingMaintenanceCount }}
                </span>
            </button>
            <button class="tab-button" data-tab="photos">Photos</button>
        </div>
        
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane active" id="overview">
                <div class="overview-grid">
                    <!-- Box 1: Contact Information -->
                    <div class="overview-box">
                        <div class="overview-box-header">Contact Information</div>
                        <div class="overview-box-content">
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value">{{ $building->contact_phone ?? 'Not provided' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $building->contact_email ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Box 2: Building Specifications -->
                    <div class="overview-box">
                        <div class="overview-box-header">Building Specifications</div>
                        <div class="overview-box-content">
                            
                            @if($building->total_area)
                            <div class="info-item">
                                <div class="info-label">Total Area</div>
                                <div class="info-value">{{ number_format($building->total_area) }} sq ft</div>
                            </div>
                            @endif
                            
                            <div class="info-item">
                                <div class="info-label">Elevator</div>
                                <div class="info-value">{{ $building->has_elevator ? 'Available' : 'Not available' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Parking</div>
                                <div class="info-value">{{ $building->has_parking ? 'Available' : 'Not available' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    
                    
                    <!-- Box 4: Description -->
                    <div class="overview-box">
                        <div class="overview-box-header">Description</div>
                        <div class="overview-box-content">
                            <p class="description-text">{{ $building->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Units Tab -->
            <div class="tab-pane" id="units">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="font-size: 18px; color: #2c3e50;">Units in {{ $building->name }}</h3>
                    <a href="{{ route('units.create', ['building' => $building->id]) }}" class="btn">
                        Add Unit
                    </a>
                </div>
                
                @if($building->units && $building->units->count() > 0)
                <div class="units-table-container">
                    <table class="units-table">
                        <thead>
                            <tr>
                                <th>Unit #</th>
                                <th>Type</th>
                                <th>Bed/Bath</th>
                                <th>Rent</th>
                                <th>Status</th>
                                <th>Current Tenant</th>
                                <th>Lease End</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($building->units as $unit)
                            <tr>
                                <td style="font-weight: 600;">{{ $unit->unit_number }}</td>
                                <td>{{ $unit->unit_type_label ?? ucfirst($unit->unit_type ?? 'N/A') }}</td>
                                <td>{{ $unit->bedrooms ?? '-' }} / {{ $unit->bathrooms ?? '-' }}</td>
                                <td style="font-weight: 600; color: #2c3e50;">₱{{ number_format($unit->monthly_rent ?? 0, 0) }}</td>
                                <td>
                                    @if($unit->status === 'occupied')
                                        <span class="status-badge status-occupied">Occupied</span>
                                    @elseif($unit->status === 'vacant')
                                        <span class="status-badge status-vacant">Vacant</span>
                                    @elseif($unit->status === 'maintenance' || $unit->status === 'under_maintenance')
                                        <span class="status-badge status-maintenance">Maintenance</span>
                                    @elseif($unit->status === 'ready')
                                        <span class="status-badge status-ready">Ready</span>
                                    @elseif($unit->status === 'reserved')
                                        <span class="status-badge status-reserved">Reserved</span>
                                    @else
                                        <span class="status-badge">{{ ucfirst($unit->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($unit->currentTenant)
                                        <a href="{{ route('tenants.show', $unit->currentTenant) }}" style="color: #4a5568; text-decoration: none; font-weight: 500;">
                                            {{ $unit->currentTenant->full_name }}
                                        </a>
                                    @else
                                        <span style="color: #6c757d;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($unit->currentTenant && $unit->currentTenant->currentLease)
                                        @php
                                            $leaseEndDate = $unit->currentTenant->currentLease->end_date;
                                            $daysLeft = $unit->currentTenant->currentLease->days_remaining ?? null;
                                        @endphp
                                        {{ $leaseEndDate ? \Carbon\Carbon::parse($leaseEndDate)->format('M d, Y') : 'N/A' }}
                                        @if($daysLeft && $daysLeft > 0 && $daysLeft <= 30)
                                            <span class="days-badge days-warning">{{ $daysLeft }} days</span>
                                        @elseif($daysLeft && $daysLeft <= 0)
                                            <span class="days-badge days-danger">Expired</span>
                                        @endif
                                    @else
                                        <span style="color: #6c757d;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px; justify-content: center;">
                                        <a href="{{ route('units.show', $unit) }}" class="btn-sm">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="no-data">
                    <h3>No Units Added Yet</h3>
                    <p>Add units to start managing this building</p>
                    <a href="{{ route('units.create', ['building' => $building->id]) }}" class="btn" style="margin-top: 15px;">
                        Add Your First Unit
                    </a>
                </div>
                @endif
            </div>
            
            <!-- Tenants Tab -->
            <div class="tab-pane" id="tenants">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; color: #2c3e50; margin-bottom: 5px;">Current Tenants in {{ $building->name }}</h3>
                    </div>
                    <a href="{{ route('tenants.create', ['building_id' => $building->id]) }}" class="btn">
                        Add Tenant
                    </a>
                </div>
                
                @php
                    // Get ALL tenants in this building with their units
                    $allTenants = $building->tenants()
                        ->with('unit')
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    // Filter to only tenants that have an active lease by directly querying the lease table
                    $currentTenants = [];
                    foreach($allTenants as $tenant) {
                        $activeLease = \App\Models\Lease::where('tenant_id', $tenant->id)
                            ->where('lease_status', 'active')
                            ->latest('start_date')
                            ->first();
                        
                        if ($activeLease) {
                            $currentTenants[] = [
                                'tenant' => $tenant,
                                'lease' => $activeLease
                            ];
                        }
                    }
                @endphp
                
                @if(count($currentTenants) > 0)
                <div class="tenants-table-container">
                    <table class="tenants-table">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Contact</th>
                                <th>Unit</th>
                                <th>Lease Period</th>
                                <th>Monthly Rent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($currentTenants as $item)
                                @php
                                    $tenant = $item['tenant'];
                                    $lease = $item['lease'];
                                @endphp
                                <tr>
                                    <td>
                                        <div>
                                            <a href="{{ route('tenants.show', $tenant) }}" style="font-weight: 600; color: #2c3e50; text-decoration: none; display: block; margin-bottom: 2px;">
                                                {{ $tenant->full_name }}
                                            </a>
                                            <div style="color: #6c757d; font-size: 12px;">
                                                @if($lease && $lease->start_date)
                                                    Tenant since {{ \Carbon\Carbon::parse($lease->start_date)->format('M Y') }}
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 2px;">
                                            <div style="color: #495057;">{{ $tenant->phone ?? 'N/A' }}</div>
                                            <div style="color: #6c757d; font-size: 12px;">{{ $tenant->email }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($tenant->unit)
                                            <div style="font-weight: 600; color: #2c3e50;">
                                                Unit {{ $tenant->unit->unit_number ?? 'N/A' }}
                                            </div>
                                            <div style="color: #6c757d; font-size: 12px;">
                                                {{ $tenant->unit->unit_type_label ?? ucfirst($tenant->unit->unit_type ?? '') }}
                                            </div>
                                        @else
                                            <div style="color: #6c757d;">No unit assigned</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="color: #495057; font-size: 13px;">
                                            {{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->format('M d, Y') : 'N/A' }}
                                        </div>
                                        <div style="color: #495057; font-size: 13px;">
                                            → {{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('M d, Y') : 'N/A' }}
                                        </div>
                                    </td>
                                    <td style="font-weight: 600; color: #2c3e50;">
                                        ₱{{ number_format($lease->monthly_rent ?? 0, 0) }}
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($lease->lease_status) {
                                                'active' => 'status-active',
                                                'pending' => 'status-pending',
                                                'expired' => 'status-expired',
                                                'terminated' => 'status-terminated',
                                                default => 'status-vacant'
                                            };
                                            $statusLabel = ucfirst($lease->lease_status);
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            <a href="{{ route('tenants.show', $tenant) }}" class="btn-sm">
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="no-data">
                    <h3>No Current Tenants</h3>
                    <p>This building has no active tenants at the moment</p>
                    <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">
                        <a href="{{ route('tenants.create', ['building_id' => $building->id]) }}" class="btn">
                            Add Your First Tenant
                        </a>
                        @if($totalUnits == 0)
                        <a href="{{ route('units.create', ['building' => $building->id]) }}" class="btn">
                            Add Unit First
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Maintenance Tab -->
            <div class="tab-pane" id="maintenance">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; color: #2c3e50; margin-bottom: 5px;">Maintenance Requests for {{ $building->name }}</h3>
                        
                    </div>
                    <a href="{{ route('maintenance-requests.create', ['building_id' => $building->id]) }}" class="btn">
                        New Request
                    </a>
                </div>
                
                @php
                    // Safely get maintenance requests
                    $maintenanceRequests = collect([]);
                    if (method_exists($building, 'maintenanceRequests')) {
                        try {
                            $maintenanceRequests = $building->maintenanceRequests()
                                ->with(['unit', 'tenant', 'maintenanceCategory', 'assignedVendor', 'assignedStaff'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                        } catch (\Exception $e) {
                            $maintenanceRequests = collect([]);
                        }
                    }
                @endphp
                
                @if($maintenanceRequests->count() > 0)
                <div class="maintenance-table-container">
                    <table class="maintenance-table">
                        <thead>
                            <tr>
                                <th>Title / Unit</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th>Request Date</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($maintenanceRequests as $request)
                            <tr>
                                <td>
                                    <a href="{{ route('maintenance-requests.show', $request) }}" style="color: #4a5568; text-decoration: none; font-weight: 500; display: block;">
                                        {{ Str::limit($request->title, 25) }}
                                    </a>
                                    @if($request->unit)
                                        <span style="color: #6c757d; font-size: 11px; display: block; margin-top: 2px;">
                                            <a href="{{ route('units.show', $request->unit) }}" style="color: #6c757d; text-decoration: none;">
                                                Unit {{ $request->unit->unit_number }}
                                            </a>
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $request->maintenanceCategory->name ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $priorityClass = match($request->priority ?? '') {
                                            'emergency' => 'priority-emergency',
                                            'high' => 'priority-high',
                                            'medium' => 'priority-medium',
                                            'low' => 'priority-low',
                                            default => ''
                                        };
                                    @endphp
                                    <span class="status-badge {{ $priorityClass }}" style="min-width: 80px;">
                                        {{ ucfirst($request->priority ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($request->status ?? '') {
                                            'submitted' => 'status-submitted',
                                            'assigned' => 'status-assigned',
                                            'in_progress' => 'status-in_progress',
                                            'completed' => 'status-completed',
                                            'cancelled' => 'status-cancelled',
                                            default => ''
                                        };
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}" style="min-width: 100px;">
                                        {{ ucfirst(str_replace('_', ' ', $request->status ?? 'N/A')) }}
                                    </span>
                                    <!-- Overdue badge only shows for assigned or in_progress status, not for submitted -->
                                    @if(method_exists($request, 'isOverdue') && $request->isOverdue() && !in_array($request->status, ['submitted', 'completed', 'cancelled']))
                                        <span class="overdue-badge">
                                            Overdue
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->tenant)
                                        <a href="{{ route('tenants.show', $request->tenant) }}" style="color: #4a5568; text-decoration: none;">
                                            {{ $request->tenant->full_name }}
                                        </a>
                                    @else
                                        <span style="color: #6c757d;">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $request->request_date ? $request->request_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @if($request->assignedVendor)
                                        <span>{{ $request->assignedVendor->company_name }}</span>
                                    @elseif($request->assignedStaff)
                                        <span>{{ $request->assignedStaff->name }}</span>
                                    @else
                                        <span style="color: #6c757d;">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px; justify-content: center;">
                                        <a href="{{ route('maintenance-requests.show', $request) }}" 
                                           class="btn-sm">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="no-data">
                    <h3>No Maintenance Requests</h3>
                    <p>This building has no maintenance requests yet</p>
                    <a href="{{ route('maintenance-requests.create', ['building_id' => $building->id]) }}" class="btn" style="margin-top: 15px;">
                        Create First Request
                    </a>
                </div>
                @endif
            </div>
            
            <!-- Photos Tab -->
            <div class="tab-pane" id="photos">
                <div class="photo-section">
                    <div class="photo-header">
                        <div>
                            <h3 class="photo-title">Building Photos</h3>
                            <p style="color: #6c757d; font-size: 13px; margin-top: 5px;">
                                Upload and manage photos of {{ $building->name }}
                            </p>
                        </div>
                        <div class="photo-upload-toggle">
                            <button class="btn" onclick="toggleUploadPanel()">
                                + Upload Photos
                            </button>
                        </div>
                    </div>
                    
                    <!-- Success/Error Messages for Photos -->
                    <div id="photo-messages"></div>
                    
                    <!-- Compact Upload Panel (hidden by default) -->
                    <div id="photoUploadPanel" class="photo-upload-panel">
                        <form id="photoUploadForm" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="photo-upload-form">
                                <div class="photo-upload-field">
                                    <label>Photos</label>
                                    <div class="file-input-wrapper">
                                        <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple>
                                        <div class="file-input-button">
                                            <span>📁</span> Choose Files
                                        </div>
                                    </div>
                                    <div id="selectedFiles" class="selected-files"></div>
                                </div>
                                
                                <div class="photo-upload-field">
                                    <label>Category</label>
                                    <select id="photoCategory" name="category">
                                        <option value="exterior">Exterior</option>
                                        <option value="lobby">Lobby</option>
                                        <option value="amenities">Amenities</option>
                                        <option value="unit_sample">Unit Sample</option>
                                        <option value="floor_plans">Floor Plans</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="photo-upload-field">
                                    <label>Description</label>
                                    <input type="text" id="photoDescription" name="description" placeholder="Brief description">
                                </div>
                                
                                <div class="photo-upload-field">
                                    <button type="submit" class="btn" id="uploadBtn" style="padding: 8px 16px;">
                                        Upload
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="category-filter">
                        <button class="category-btn active" data-category="all">All Photos</button>
                        <button class="category-btn" data-category="exterior">Exterior</button>
                        <button class="category-btn" data-category="lobby">Lobby</button>
                        <button class="category-btn" data-category="amenities">Amenities</button>
                        <button class="category-btn" data-category="unit_sample">Unit Sample</button>
                        <button class="category-btn" data-category="floor_plans">Floor Plans</button>
                    </div>
                    
                    <!-- Photo Gallery -->
                    <div id="photoGallery">
                        @php
                            $categoryLabels = [
                                'exterior' => 'Exterior',
                                'lobby' => 'Lobby',
                                'amenities' => 'Amenities',
                                'unit_sample' => 'Unit Sample',
                                'floor_plans' => 'Floor Plans',
                                'other' => 'Other'
                            ];
                        @endphp
                        
                        @if($building->photos && $building->photos->count() > 0)
                        <div class="photo-gallery" id="photoGalleryContainer">
                            @foreach($building->photos as $photo)
                            <div class="photo-card" data-category="{{ $photo->category }}" data-photo-id="{{ $photo->id }}" @if($photo->is_primary) style="border: 2px solid #4a5568;" @endif>
                                <!-- Photo Image Container -->
                                <div class="photo-image-container">
                                    <img src="{{ Storage::url($photo->path) }}" alt="{{ $photo->description }}" class="photo-image">
                                    @if($photo->is_primary)
                                    <div class="primary-badge">Primary</div>
                                    @endif
                                </div>
                                
                                <!-- Photo Card Content -->
                                <div class="photo-card-content">
                                    <!-- Header with Category and Date -->
                                    <div class="photo-card-header">
                                        <span class="photo-category-badge">{{ $categoryLabels[$photo->category] ?? ucfirst($photo->category) }}</span>
                                        <span class="photo-date">{{ $photo->created_at ? $photo->created_at->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="photo-description" title="{{ $photo->description ?? 'No description' }}">
                                        {{ $photo->description ?? 'No description' }}
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="photo-card-actions">
                                        <button onclick="setAsPrimary({{ $photo->id }})" class="btn-sm" style="background: #4a5568; color: white;">
                                            {{ $photo->is_primary ? '✓ Primary' : 'Set Primary' }}
                                        </button>
                                        <button onclick="viewPhoto('{{ Storage::url($photo->path) }}', '{{ $photo->description }}')" class="btn-sm" style="background: #718096; color: white;">
                                            View
                                        </button>
                                        <button onclick="deletePhoto({{ $photo->id }})" class="btn-sm" style="background: #2d3748; color: white;">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div id="noPhotosMessage" class="no-data">
                            <h3>No Photos Uploaded Yet</h3>
                            <p>Upload photos to showcase this building</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div id="photoModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closePhotoModal()">&times;</button>
        <img id="modalImage" src="" alt="" class="modal-image">
        <div id="modalDescription" class="modal-description"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Category labels mapping
    const categoryLabels = {
        'exterior': 'Exterior',
        'lobby': 'Lobby',
        'amenities': 'Amenities',
        'unit_sample': 'Unit Sample',
        'floor_plans': 'Floor Plans',
        'other': 'Other'
    };

    // Toggle upload panel
    function toggleUploadPanel() {
        const panel = document.getElementById('photoUploadPanel');
        panel.classList.toggle('active');
    }

    // Tab Switching
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and panes
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Add active class to clicked button and corresponding pane
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
                
                // Update URL hash for bookmarking
                window.location.hash = tabId;
            });
        });
        
        // Check for hash in URL to activate specific tab
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const activeTab = document.querySelector(`.tab-button[data-tab="${hash}"]`);
            if (activeTab) {
                activeTab.click();
            }
        }
        
        // File selection display for photo upload
        const photoInput = document.getElementById('photoInput');
        const selectedFiles = document.getElementById('selectedFiles');
        
        if (photoInput && selectedFiles) {
            photoInput.addEventListener('change', function() {
                const files = this.files;
                if (files.length > 0) {
                    let totalSize = 0;
                    let fileNames = [];
                    
                    for (let i = 0; i < Math.min(files.length, 3); i++) {
                        fileNames.push(files[i].name);
                        totalSize += files[i].size;
                    }
                    
                    // Check file size (max 5MB per file)
                    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
                    let validFiles = true;
                    
                    for (let i = 0; i < files.length; i++) {
                        if (files[i].size > maxSize) {
                            validFiles = false;
                            showMessage(`File "${files[i].name}" exceeds 5MB limit.`, 'error');
                            break;
                        }
                    }
                    
                    let message = `Selected ${files.length} file${files.length > 1 ? 's' : ''}`;
                    if (files.length > 3) {
                        message += `: ${fileNames.join(', ')} and ${files.length - 3} more`;
                    } else {
                        message += `: ${fileNames.join(', ')}`;
                    }
                    
                    message += ` (${formatBytes(totalSize)})`;
                    selectedFiles.textContent = message;
                    selectedFiles.style.color = validFiles ? '#6c757d' : '#e74c3c';
                    
                    // Enable/disable upload button based on validation
                    const uploadBtn = document.getElementById('uploadBtn');
                    if (uploadBtn) {
                        uploadBtn.disabled = !validFiles;
                        uploadBtn.style.opacity = validFiles ? '1' : '0.5';
                        uploadBtn.style.cursor = validFiles ? 'pointer' : 'not-allowed';
                    }
                } else {
                    selectedFiles.textContent = '';
                }
            });
        }
        
        // Initialize photo upload form with AJAX
        initializePhotoUpload();
        
        // Category filter for photos
        const categoryButtons = document.querySelectorAll('.category-btn');
        
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                
                // Update active button
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter photos
                filterPhotosByCategory(category);
            });
        });
        
        // Initial load
        filterPhotosByCategory('all');
    });

    // Initialize photo upload with AJAX
    function initializePhotoUpload() {
        const photoUploadForm = document.getElementById('photoUploadForm');
        
        if (photoUploadForm) {
            photoUploadForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const uploadBtn = document.getElementById('uploadBtn');
                const originalText = uploadBtn.innerHTML;
                
                // Disable button and show loading state
                uploadBtn.innerHTML = '⏳ Uploading...';
                uploadBtn.disabled = true;
                
                try {
                    const response = await fetch('{{ route("buildings.photos.upload", $building->id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Clear form
                        document.getElementById('photoInput').value = '';
                        document.getElementById('selectedFiles').textContent = '';
                        document.getElementById('photoDescription').value = '';
                        
                        // Show success message
                        showMessage(data.message || 'Photos uploaded successfully!', 'success');
                        
                        // Add new photos to gallery
                        if (data.photos && data.photos.length > 0) {
                            data.photos.forEach(photo => {
                                addPhotoToGallery(photo);
                            });
                        }
                    } else {
                        showMessage(data.message || 'Upload failed', 'error');
                    }
                } catch (error) {
                    console.error('Error uploading photos:', error);
                    showMessage('An error occurred while uploading photos', 'error');
                } finally {
                    // Reset button state
                    uploadBtn.innerHTML = originalText;
                    uploadBtn.disabled = false;
                }
            });
        }
    }

    // Add photo to gallery dynamically
    function addPhotoToGallery(photo) {
        let galleryContainer = document.getElementById('photoGalleryContainer');
        let noPhotosMessage = document.getElementById('noPhotosMessage');
        
        // If gallery container doesn't exist, create it
        if (!galleryContainer) {
            const photoGallery = document.getElementById('photoGallery');
            photoGallery.innerHTML = '<div class="photo-gallery" id="photoGalleryContainer"></div>';
            galleryContainer = document.getElementById('photoGalleryContainer');
        }
        
        // Remove no photos message if it exists
        if (noPhotosMessage) {
            noPhotosMessage.remove();
        }
        
        // Create photo URL
        const photoUrl = '/storage/' + photo.path;
        
        // Format date
        const uploadDate = new Date(photo.created_at);
        const formattedDate = uploadDate.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
        
        // Create photo card HTML
        const photoCard = document.createElement('div');
        photoCard.className = 'photo-card';
        photoCard.setAttribute('data-category', photo.category);
        photoCard.setAttribute('data-photo-id', photo.id);
        if (photo.is_primary) {
            photoCard.style.border = '2px solid #4a5568';
        }
        
        photoCard.innerHTML = `
            <div class="photo-image-container">
                <img src="${photoUrl}" alt="${photo.description || ''}" class="photo-image">
                ${photo.is_primary ? '<div class="primary-badge">Primary</div>' : ''}
            </div>
            <div class="photo-card-content">
                <div class="photo-card-header">
                    <span class="photo-category-badge">${categoryLabels[photo.category] || photo.category.charAt(0).toUpperCase() + photo.category.slice(1)}</span>
                    <span class="photo-date">${formattedDate}</span>
                </div>
                <div class="photo-description" title="${photo.description || 'No description'}">
                    ${photo.description || 'No description'}
                </div>
                <div class="photo-card-actions">
                    <button onclick="setAsPrimary(${photo.id})" class="btn-sm" style="background: #4a5568; color: white;">
                        ${photo.is_primary ? '✓ Primary' : 'Set Primary'}
                    </button>
                    <button onclick="viewPhoto('${photoUrl}', '${photo.description || ''}')" class="btn-sm" style="background: #718096; color: white;">
                        View
                    </button>
                    <button onclick="deletePhoto(${photo.id})" class="btn-sm" style="background: #2d3748; color: white;">
                        Delete
                    </button>
                </div>
            </div>
        `;
        
        // Add to gallery
        galleryContainer.appendChild(photoCard);
        
        // Apply current category filter
        const activeCategoryBtn = document.querySelector('.category-btn.active');
        if (activeCategoryBtn) {
            const category = activeCategoryBtn.getAttribute('data-category');
            filterPhotosByCategory(category);
        }
    }

    // Helper function to format bytes
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Function to show messages
    function showMessage(message, type = 'success') {
        const container = document.getElementById('photo-messages');
        if (!container) return;
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.style.cssText = `
            margin-bottom: 15px;
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 14px;
            animation: fadeIn 0.3s ease;
        `;
        
        if (type === 'success') {
            alertDiv.style.background = '#d4edda';
            alertDiv.style.color = '#155724';
            alertDiv.style.border = '1px solid #c3e6cb';
        } else if (type === 'error') {
            alertDiv.style.background = '#f8d7da';
            alertDiv.style.color = '#721c24';
            alertDiv.style.border = '1px solid #f5c6cb';
        } else {
            alertDiv.style.background = '#e2e3e5';
            alertDiv.style.color = '#383d41';
            alertDiv.style.border = '1px solid #d6d8db';
        }
        
        alertDiv.innerHTML = `
            ${message}
            <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; cursor: pointer; font-size: 18px; color: inherit; opacity: 0.7;">&times;</button>
        `;
        
        container.innerHTML = '';
        container.appendChild(alertDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.style.opacity = '0';
                alertDiv.style.transition = 'opacity 0.5s';
                setTimeout(() => alertDiv.remove(), 500);
            }
        }, 5000);
    }

    // Function to filter photos by category
    function filterPhotosByCategory(category) {
        const photoCards = document.querySelectorAll('.photo-card');
        
        photoCards.forEach(card => {
            if (category === 'all' || card.getAttribute('data-category') === category) {
                card.style.display = 'block';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                }, 10);
            } else {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });
    }

    // Modal functions
    function closePhotoModal() {
        document.getElementById('photoModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Photo management functions
    async function setAsPrimary(photoId) {
        try {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Setting...';
            button.disabled = true;
            
            const response = await fetch(`/buildings/{{ $building->id }}/photos/${photoId}/set-primary`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage('Primary photo updated successfully!', 'success');
                
                const photoCards = document.querySelectorAll('.photo-card');
                photoCards.forEach(card => {
                    card.style.border = '1px solid #dee2e6';
                    const primaryBtn = card.querySelector('.btn-sm:first-child');
                    if (primaryBtn && primaryBtn.textContent.includes('✓ Primary')) {
                        primaryBtn.textContent = 'Set Primary';
                        primaryBtn.style.background = '#4a5568';
                    }
                    
                    // Remove primary badge
                    const badge = card.querySelector('.primary-badge');
                    if (badge) badge.remove();
                });
                
                const selectedCard = document.querySelector(`.photo-card[data-photo-id="${photoId}"]`);
                if (selectedCard) {
                    selectedCard.style.border = '2px solid #4a5568';
                    
                    // Add primary badge to image container
                    const imageContainer = selectedCard.querySelector('.photo-image-container');
                    const badge = document.createElement('div');
                    badge.className = 'primary-badge';
                    badge.textContent = 'Primary';
                    imageContainer.appendChild(badge);
                    
                    const primaryBtn = selectedCard.querySelector('.btn-sm:first-child');
                    if (primaryBtn) {
                        primaryBtn.textContent = '✓ Primary';
                    }
                }
            } else {
                showMessage(data.message || 'Failed to set primary photo', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error setting primary photo:', error);
            showMessage('An error occurred. Please try again.', 'error');
            const button = event.target;
            button.innerHTML = 'Set Primary';
            button.disabled = false;
        }
    }
    
    function viewPhoto(url, description) {
        const modal = document.getElementById('photoModal');
        const modalImage = document.getElementById('modalImage');
        const modalDescription = document.getElementById('modalDescription');
        
        modalImage.src = url;
        modalDescription.textContent = description;
        modal.style.display = 'flex';
        
        document.body.style.overflow = 'hidden';
    }
    
    async function deletePhoto(photoId) {
        try {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Deleting...';
            button.disabled = true;
            
            const response = await fetch(`/buildings/{{ $building->id }}/photos/${photoId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage('Photo deleted successfully!', 'success');
                
                const photoCard = document.querySelector(`.photo-card[data-photo-id="${photoId}"]`);
                if (photoCard) {
                    photoCard.style.opacity = '0.5';
                    photoCard.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        photoCard.remove();
                        
                        const photoCards = document.querySelectorAll('.photo-card');
                        const galleryContainer = document.getElementById('photoGalleryContainer');
                        
                        if (photoCards.length === 0 && galleryContainer) {
                            const photoGallery = document.getElementById('photoGallery');
                            photoGallery.innerHTML = `
                                <div id="noPhotosMessage" class="no-data">
                                    <h3>No Photos Uploaded Yet</h3>
                                    <p>Upload photos to showcase this building</p>
                                </div>
                            `;
                        }
                    }, 300);
                }
            } else {
                showMessage(data.message || 'Failed to delete photo', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error deleting photo:', error);
            showMessage('An error occurred while deleting the photo', 'error');
            const button = event.target;
            button.innerHTML = 'Delete';
            button.disabled = false;
        }
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhotoModal();
        }
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('photoModal');
        if (event.target === modal) {
            closePhotoModal();
        }
    };
</script>
@endpush