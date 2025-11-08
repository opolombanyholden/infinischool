{{-- 
    Alert Component
    
    Usage:
    <x-alert type="success" dismissible="true">
        Votre action a été effectuée avec succès !
    </x-alert>
    
    <x-alert type="danger" icon="exclamation-circle" title="Erreur">
        Une erreur s'est produite lors du traitement.
    </x-alert>
    
    Types: success, danger, warning, info, primary
    Icons: check-circle, exclamation-circle, info-circle, exclamation-triangle, etc.
--}}

@props([
    'type' => 'info',
    'icon' => null,
    'title' => null,
    'dismissible' => true,
    'animate' => true
])

@php
    // Define default icons for each type
    $defaultIcons = [
        'success' => 'check-circle',
        'danger' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'info-circle',
        'primary' => 'lightbulb'
    ];
    
    // Define default titles for each type
    $defaultTitles = [
        'success' => 'Succès !',
        'danger' => 'Erreur !',
        'warning' => 'Attention !',
        'info' => 'Information',
        'primary' => 'Note'
    ];
    
    // Set icon
    $alertIcon = $icon ?? $defaultIcons[$type] ?? 'info-circle';
    
    // Set title if not provided
    $alertTitle = $title;
    
    // Define CSS classes
    $alertClasses = "alert-custom alert-{$type}";
    if ($dismissible) {
        $alertClasses .= ' alert-dismissible';
    }
    if ($animate) {
        $alertClasses .= ' alert-animated';
    }
@endphp

<div class="{{ $alertClasses }}" role="alert">
    <div class="alert-icon">
        <i class="fas fa-{{ $alertIcon }}"></i>
    </div>
    
    <div class="alert-content">
        @if($alertTitle)
            <div class="alert-title">{{ $alertTitle }}</div>
        @endif
        <div class="alert-message">
            {{ $slot }}
        </div>
    </div>
    
    @if($dismissible)
        <button type="button" class="alert-close" data-bs-dismiss="alert" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    @endif
</div>

<style>
    /* Alert Base Styles */
    .alert-custom {
        display: flex;
        align-items: flex-start;
        padding: 16px 20px;
        border-radius: 12px;
        border: none;
        margin-bottom: 20px;
        position: relative;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .alert-custom:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    /* Alert Animation */
    .alert-animated {
        animation: alertSlideIn 0.5s ease-out;
    }

    @keyframes alertSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Dismissing Animation */
    .alert-custom.fade {
        transition: opacity 0.3s ease-out;
    }

    /* Alert Icon */
    .alert-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-right: 15px;
        font-size: 1.2rem;
    }

    /* Alert Content */
    .alert-content {
        flex: 1;
        min-width: 0;
    }

    .alert-title {
        font-weight: 700;
        font-size: 1.05rem;
        margin-bottom: 5px;
        line-height: 1.3;
    }

    .alert-message {
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0;
    }

    .alert-message p:last-child {
        margin-bottom: 0;
    }

    /* Alert Close Button */
    .alert-close {
        flex-shrink: 0;
        background: none;
        border: none;
        padding: 0;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-left: 10px;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .alert-close:hover {
        transform: rotate(90deg);
    }

    /* ========================================
       ALERT TYPES
    ======================================== */

    /* Success Alert */
    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-success .alert-icon {
        background-color: #28a745;
        color: white;
    }

    .alert-success .alert-close {
        color: #155724;
    }

    .alert-success .alert-close:hover {
        background-color: rgba(21, 87, 36, 0.1);
        color: #0c3d1a;
    }

    /* Danger Alert */
    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .alert-danger .alert-icon {
        background-color: #dc3545;
        color: white;
    }

    .alert-danger .alert-close {
        color: #721c24;
    }

    .alert-danger .alert-close:hover {
        background-color: rgba(114, 28, 36, 0.1);
        color: #491217;
    }

    /* Warning Alert */
    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
        border-left: 4px solid #ffc107;
    }

    .alert-warning .alert-icon {
        background-color: #ffc107;
        color: #856404;
    }

    .alert-warning .alert-close {
        color: #856404;
    }

    .alert-warning .alert-close:hover {
        background-color: rgba(133, 100, 4, 0.1);
        color: #533f03;
    }

    /* Info Alert */
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }

    .alert-info .alert-icon {
        background-color: #17a2b8;
        color: white;
    }

    .alert-info .alert-close {
        color: #0c5460;
    }

    .alert-info .alert-close:hover {
        background-color: rgba(12, 84, 96, 0.1);
        color: #062c33;
    }

    /* Primary Alert */
    .alert-primary {
        background: linear-gradient(135deg, #f5e6ec 0%, #f0d9e3 100%);
        color: #800020;
        border-left: 4px solid #800020;
    }

    .alert-primary .alert-icon {
        background-color: #800020;
        color: white;
    }

    .alert-primary .alert-close {
        color: #800020;
    }

    .alert-primary .alert-close:hover {
        background-color: rgba(128, 0, 32, 0.1);
        color: #5a0016;
    }

    /* ========================================
       RESPONSIVE
    ======================================== */
    @media (max-width: 576px) {
        .alert-custom {
            padding: 14px 16px;
        }

        .alert-icon {
            width: 35px;
            height: 35px;
            margin-right: 12px;
            font-size: 1rem;
        }

        .alert-title {
            font-size: 1rem;
        }

        .alert-message {
            font-size: 0.9rem;
        }

        .alert-close {
            width: 28px;
            height: 28px;
        }
    }

    /* ========================================
       ALERT VARIATIONS
    ======================================== */

    /* Solid Alert (Alternative Style) */
    .alert-solid.alert-success {
        background: #28a745;
        color: white;
    }

    .alert-solid.alert-danger {
        background: #dc3545;
        color: white;
    }

    .alert-solid.alert-warning {
        background: #ffc107;
        color: #856404;
    }

    .alert-solid.alert-info {
        background: #17a2b8;
        color: white;
    }

    .alert-solid.alert-primary {
        background: #800020;
        color: white;
    }

    /* Bordered Alert (Alternative Style) */
    .alert-bordered {
        background: white;
        border: 2px solid;
    }

    .alert-bordered.alert-success {
        border-color: #28a745;
        color: #155724;
    }

    .alert-bordered.alert-danger {
        border-color: #dc3545;
        color: #721c24;
    }

    .alert-bordered.alert-warning {
        border-color: #ffc107;
        color: #856404;
    }

    .alert-bordered.alert-info {
        border-color: #17a2b8;
        color: #0c5460;
    }

    .alert-bordered.alert-primary {
        border-color: #800020;
        color: #800020;
    }

    /* Minimal Alert (Alternative Style) */
    .alert-minimal {
        background: transparent;
        box-shadow: none;
        padding: 12px 0;
        border-left: 3px solid;
    }

    .alert-minimal.alert-success {
        border-left-color: #28a745;
        color: #155724;
    }

    .alert-minimal.alert-danger {
        border-left-color: #dc3545;
        color: #721c24;
    }

    .alert-minimal.alert-warning {
        border-left-color: #ffc107;
        color: #856404;
    }

    .alert-minimal.alert-info {
        border-left-color: #17a2b8;
        color: #0c5460;
    }

    .alert-minimal.alert-primary {
        border-left-color: #800020;
        color: #800020;
    }

    .alert-minimal .alert-icon {
        background: transparent;
    }

    /* ========================================
       PRINT STYLES
    ======================================== */
    @media print {
        .alert-custom {
            box-shadow: none;
            border: 1px solid #dee2e6;
        }

        .alert-close {
            display: none;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds if they have auto-dismiss attribute
        document.querySelectorAll('.alert-custom[data-auto-dismiss]').forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>