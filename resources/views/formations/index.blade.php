@extends('layouts.app')

@section('title', 'Nos Formations - InfiniSchool')
@section('description', 'Découvrez toutes nos formations en ligne. Développez vos compétences avec nos cours en direct et obtenez des certifications reconnues.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Nos Formations</h1>
                <p class="page-header-subtitle">
                    Plus de {{ $formations->total() }} formations pour développer vos compétences
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Formations</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Formations Section -->
<section class="formations-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="filters-wrapper">
                    <div class="filters-header">
                        <h5 class="filters-title">
                            <i class="fas fa-filter me-2"></i>Filtres
                        </h5>
                        <button class="btn-reset-filters" onclick="resetFilters()">
                            <i class="fas fa-redo-alt me-1"></i>Réinitialiser
                        </button>
                    </div>

                    <form method="GET" action="{{ route('formations.index') }}" id="filterForm">
                        <!-- Search -->
                        <div class="filter-group">
                            <label class="filter-label">Rechercher</label>
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Nom de formation...">
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="filter-group">
                            <label class="filter-label">Catégorie</label>
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="">Toutes les catégories</option>
                                <option value="Développement Web" {{ request('category') == 'Développement Web' ? 'selected' : '' }}>Développement Web</option>
                                <option value="Design" {{ request('category') == 'Design' ? 'selected' : '' }}>Design</option>
                                <option value="Marketing Digital" {{ request('category') == 'Marketing Digital' ? 'selected' : '' }}>Marketing Digital</option>
                                <option value="Business" {{ request('category') == 'Business' ? 'selected' : '' }}>Business</option>
                                <option value="Data Science" {{ request('category') == 'Data Science' ? 'selected' : '' }}>Data Science</option>
                                <option value="Langues" {{ request('category') == 'Langues' ? 'selected' : '' }}>Langues</option>
                            </select>
                        </div>

                        <!-- Level -->
                        <div class="filter-group">
                            <label class="filter-label">Niveau</label>
                            <div class="filter-checkboxes">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="level[]" value="debutant" 
                                           id="level_debutant" {{ in_array('debutant', request('level', [])) ? 'checked' : '' }}
                                           onchange="this.form.submit()">
                                    <label class="form-check-label" for="level_debutant">Débutant</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="level[]" value="intermediaire" 
                                           id="level_intermediaire" {{ in_array('intermediaire', request('level', [])) ? 'checked' : '' }}
                                           onchange="this.form.submit()">
                                    <label class="form-check-label" for="level_intermediaire">Intermédiaire</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="level[]" value="avance" 
                                           id="level_avance" {{ in_array('avance', request('level', [])) ? 'checked' : '' }}
                                           onchange="this.form.submit()">
                                    <label class="form-check-label" for="level_avance">Avancé</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="level[]" value="expert" 
                                           id="level_expert" {{ in_array('expert', request('level', [])) ? 'checked' : '' }}
                                           onchange="this.form.submit()">
                                    <label class="form-check-label" for="level_expert">Expert</label>
                                </div>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="filter-group">
                            <label class="filter-label">Fourchette de Prix</label>
                            <select class="form-select" name="price" onchange="this.form.submit()">
                                <option value="">Tous les prix</option>
                                <option value="0-50000" {{ request('price') == '0-50000' ? 'selected' : '' }}>Moins de 50 000 FCFA</option>
                                <option value="50000-100000" {{ request('price') == '50000-100000' ? 'selected' : '' }}>50 000 - 100 000 FCFA</option>
                                <option value="100000-200000" {{ request('price') == '100000-200000' ? 'selected' : '' }}>100 000 - 200 000 FCFA</option>
                                <option value="200000+" {{ request('price') == '200000+' ? 'selected' : '' }}>Plus de 200 000 FCFA</option>
                            </select>
                        </div>

                        <!-- Duration -->
                        <div class="filter-group">
                            <label class="filter-label">Durée</label>
                            <select class="form-select" name="duration" onchange="this.form.submit()">
                                <option value="">Toutes les durées</option>
                                <option value="1-4" {{ request('duration') == '1-4' ? 'selected' : '' }}>1-4 semaines</option>
                                <option value="5-8" {{ request('duration') == '5-8' ? 'selected' : '' }}>5-8 semaines</option>
                                <option value="9-12" {{ request('duration') == '9-12' ? 'selected' : '' }}>9-12 semaines</option>
                                <option value="13+" {{ request('duration') == '13+' ? 'selected' : '' }}>Plus de 12 semaines</option>
                            </select>
                        </div>

                        <!-- Featured Only -->
                        <div class="filter-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="featured" value="1" 
                                       id="featured" {{ request('featured') ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <label class="form-check-label" for="featured">
                                    <i class="fas fa-star text-warning me-1"></i>
                                    Populaires uniquement
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Formations Grid -->
            <div class="col-lg-9">
                <!-- Sort & View Options -->
                <div class="toolbar mb-4">
                    <div class="toolbar-left">
                        <span class="toolbar-results">
                            <strong>{{ $formations->total() }}</strong> formation(s) trouvée(s)
                        </span>
                    </div>
                    <div class="toolbar-right">
                        <form method="GET" action="{{ route('formations.index') }}" class="d-inline">
                            <!-- Keep existing filters -->
                            @foreach(request()->except('sort') as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $item)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            
                            <select class="form-select form-select-sm" name="sort" onchange="this.form.submit()" style="width: auto; display: inline-block;">
                                <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Plus récentes</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Plus populaires</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Formations Grid -->
                <div class="row g-4">
                    @forelse($formations as $formation)
                    <div class="col-md-6 col-xl-4">
                        <div class="formation-card">
                            @if($formation->image)
                                <div class="formation-image">
                                    <img src="{{ Storage::url($formation->image) }}" alt="{{ $formation->name }}">
                                    @if($formation->is_featured)
                                        <span class="formation-badge-featured">
                                            <i class="fas fa-star"></i> Populaire
                                        </span>
                                    @endif
                                    @if($formation->discount_price)
                                        <span class="formation-badge-discount">
                                            -{{ round((($formation->price - $formation->discount_price) / $formation->price) * 100) }}%
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="formation-content">
                                <div class="formation-meta">
                                    <span class="formation-category">
                                        <i class="fas fa-tag me-1"></i>{{ $formation->category }}
                                    </span>
                                    <span class="formation-level">
                                        <i class="fas fa-signal me-1"></i>{{ ucfirst($formation->level) }}
                                    </span>
                                </div>

                                <h3 class="formation-title">
                                    <a href="{{ route('formations.show', $formation->slug) }}">
                                        {{ $formation->name }}
                                    </a>
                                </h3>

                                <p class="formation-description">
                                    {{ Str::limit($formation->short_description ?? $formation->description, 100) }}
                                </p>

                                <div class="formation-info">
                                    <div class="formation-info-item">
                                        <i class="fas fa-clock text-primary"></i>
                                        <span>{{ $formation->duration_weeks }} semaines</span>
                                    </div>
                                    <div class="formation-info-item">
                                        <i class="fas fa-users text-primary"></i>
                                        <span>{{ $formation->enrolled_count }} inscrits</span>
                                    </div>
                                    <div class="formation-info-item">
                                        <i class="fas fa-star text-warning"></i>
                                        <span>{{ number_format($formation->rating ?? 4.8, 1) }}</span>
                                    </div>
                                </div>

                                <div class="formation-footer">
                                    <div class="formation-price">
                                        @if($formation->discount_price)
                                            <span class="price-old">{{ number_format($formation->price, 0, ',', ' ') }} FCFA</span>
                                            <span class="price-current">{{ number_format($formation->discount_price, 0, ',', ' ') }} FCFA</span>
                                        @else
                                            <span class="price-current">{{ number_format($formation->price, 0, ',', ' ') }} FCFA</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('formations.show', $formation->slug) }}" class="btn btn-primary btn-sm">
                                        Découvrir <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucune formation ne correspond à vos critères. Essayez de modifier vos filtres.
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($formations->hasPages())
                <div class="pagination-wrapper mt-5">
                    {{ $formations->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="cta-box text-center">
            <h2 class="cta-title">Vous ne Trouvez pas la Formation Idéale ?</h2>
            <p class="cta-description mb-4">
                Contactez-nous pour des formations personnalisées adaptées à vos besoins spécifiques
            </p>
            <a href="{{ route('contact') }}" class="btn btn-light btn-lg">
                <i class="fas fa-envelope me-2"></i>Nous Contacter
            </a>
        </div>
    </div>
</section>

@endsection

@section('styles')
<style>
    /* Page Header */
    .page-header-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        padding: 80px 0 60px;
        position: relative;
        overflow: hidden;
    }

    .page-header-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
    }

    .page-header-title {
        color: white;
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 15px;
        position: relative;
    }

    .page-header-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.3rem;
        margin-bottom: 25px;
        position: relative;
    }

    .page-header-section .breadcrumb {
        background: transparent;
        position: relative;
    }

    .page-header-section .breadcrumb-item,
    .page-header-section .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
    }

    .page-header-section .breadcrumb-item.active {
        color: white;
    }

    /* Filters Sidebar */
    .filters-wrapper {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 100px;
    }

    .filters-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .filters-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-dark);
    }

    .btn-reset-filters {
        background: none;
        border: none;
        color: var(--primary-color);
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-reset-filters:hover {
        color: var(--primary-dark);
    }

    .filter-group {
        margin-bottom: 25px;
    }

    .filter-label {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 10px;
        display: block;
        color: var(--text-dark);
    }

    .search-box {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        z-index: 5;
    }

    .search-box .form-control {
        padding-left: 40px;
    }

    .filter-checkboxes {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .form-check-label {
        font-size: 0.95rem;
        color: var(--text-dark);
    }

    /* Toolbar */
    .toolbar {
        background: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .toolbar-results {
        font-size: 1rem;
        color: var(--text-dark);
    }

    /* Formation Cards */
    .formation-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .formation-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .formation-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .formation-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .formation-card:hover .formation-image img {
        transform: scale(1.1);
    }

    .formation-badge-featured {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #ffc107;
        color: #856404;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .formation-badge-discount {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #dc3545;
        color: white;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .formation-content {
        padding: 25px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .formation-meta {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .formation-category,
    .formation-level {
        font-size: 0.85rem;
        padding: 4px 12px;
        border-radius: 15px;
        background: #f8f9fa;
        color: var(--text-light);
    }

    .formation-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .formation-title a {
        color: var(--text-dark);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .formation-title a:hover {
        color: var(--primary-color);
    }

    .formation-description {
        color: var(--text-light);
        line-height: 1.6;
        margin-bottom: 15px;
        font-size: 0.95rem;
        flex: 1;
    }

    .formation-info {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
        flex-wrap: wrap;
    }

    .formation-info-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .formation-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
    }

    .formation-price {
        display: flex;
        flex-direction: column;
    }

    .price-old {
        font-size: 0.85rem;
        color: var(--text-light);
        text-decoration: line-through;
    }

    .price-current {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary-color);
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    }

    .cta-box {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 60px 50px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .cta-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
    }

    .cta-description {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .cta-box .btn-light {
        background: white;
        color: var(--primary-color);
        border: none;
        font-weight: 700;
    }

    .cta-box .btn-light:hover {
        background: #f8f9fa;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .page-header-title {
            font-size: 2.5rem;
        }

        .filters-wrapper {
            position: static;
            margin-bottom: 30px;
        }

        .toolbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .cta-title {
            font-size: 2rem;
        }

        .cta-box {
            padding: 40px 30px;
        }
    }

    @media (max-width: 576px) {
        .page-header-title {
            font-size: 2rem;
        }

        .formation-info {
            flex-direction: column;
            gap: 8px;
        }

        .cta-box {
            padding: 30px 20px;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    function resetFilters() {
        window.location.href = "{{ route('formations.index') }}";
    }
</script>
@endsection