{{-- 
    Breadcrumb Component
    
    Usage:
    <x-breadcrumb :items="[
        ['label' => 'Accueil', 'url' => route('home')],
        ['label' => 'Formations', 'url' => route('formations.index')],
        ['label' => 'Détail', 'active' => true]
    ]" />
    
    Or with simple array:
    <x-breadcrumb :items="[
        'Accueil' => route('home'),
        'Formations' => route('formations.index'),
        'Détail' => null
    ]" />
--}}

@props(['items' => []])

@if(count($items) > 0)
<nav aria-label="breadcrumb" class="breadcrumb-container">
    <ol class="breadcrumb-custom">
        @foreach($items as $key => $item)
            @php
                // Support both array formats
                if (is_array($item)) {
                    $label = $item['label'] ?? $item['name'] ?? 'Item';
                    $url = $item['url'] ?? $item['route'] ?? null;
                    $isActive = $item['active'] ?? false;
                } else {
                    $label = is_numeric($key) ? $item : $key;
                    $url = is_numeric($key) ? null : $item;
                    $isActive = $url === null;
                }
                
                $isLast = $loop->last;
            @endphp
            
            <li class="breadcrumb-item-custom {{ $isActive || $isLast ? 'active' : '' }}">
                @if($url && !$isLast && !$isActive)
                    <a href="{{ $url }}" class="breadcrumb-link">
                        @if($loop->first)
                            <i class="fas fa-home me-1"></i>
                        @endif
                        {{ $label }}
                    </a>
                @else
                    @if($loop->first)
                        <i class="fas fa-home me-1"></i>
                    @endif
                    <span class="breadcrumb-current">{{ $label }}</span>
                @endif
                
                @if(!$isLast)
                    <i class="fas fa-chevron-right breadcrumb-separator"></i>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif

<style>
    /* Breadcrumb Container */
    .breadcrumb-container {
        background: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        animation: slideInDown 0.4s ease-out;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Breadcrumb List */
    .breadcrumb-custom {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 8px;
    }

    /* Breadcrumb Items */
    .breadcrumb-item-custom {
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        color: #6c757d;
        transition: all 0.3s ease;
    }

    .breadcrumb-item-custom.active {
        color: #800020;
        font-weight: 600;
    }

    /* Breadcrumb Links */
    .breadcrumb-link {
        color: #6c757d;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 6px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }

    .breadcrumb-link:hover {
        color: #800020;
        background-color: rgba(128, 0, 32, 0.05);
        transform: translateX(2px);
    }

    /* Current/Active Item */
    .breadcrumb-current {
        padding: 5px 10px;
        color: #800020;
        font-weight: 600;
        background-color: rgba(128, 0, 32, 0.08);
        border-radius: 6px;
        display: flex;
        align-items: center;
    }

    /* Separator */
    .breadcrumb-separator {
        font-size: 0.7rem;
        color: #dee2e6;
        margin: 0 5px;
        transition: color 0.3s ease;
    }

    .breadcrumb-item-custom:hover .breadcrumb-separator {
        color: #800020;
    }

    /* Home Icon */
    .breadcrumb-link i.fa-home,
    .breadcrumb-current i.fa-home {
        font-size: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .breadcrumb-container {
            padding: 12px 15px;
        }

        .breadcrumb-custom {
            gap: 5px;
        }

        .breadcrumb-item-custom {
            font-size: 0.85rem;
        }

        .breadcrumb-link,
        .breadcrumb-current {
            padding: 4px 8px;
        }

        /* Hide middle items on very small screens if needed */
        @media (max-width: 480px) {
            .breadcrumb-item-custom:not(:first-child):not(:last-child):not(.active) {
                display: none;
            }
            
            /* Show ellipsis for hidden items */
            .breadcrumb-item-custom:first-child:not(:last-child)::after {
                content: '...';
                margin: 0 10px;
                color: #6c757d;
            }
        }
    }

    /* Print Styles */
    @media print {
        .breadcrumb-container {
            box-shadow: none;
            border: 1px solid #dee2e6;
        }
    }
</style>