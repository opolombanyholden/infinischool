@extends('layouts.app')

@section('title', $formation->name . ' - InfiniSchool')
@section('description', $formation->short_description ?? Str::limit($formation->description, 160))

@section('content')

<!-- Hero Section -->
<section class="formation-hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('formations.index') }}">Formations</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $formation->name }}</li>
                    </ol>
                </nav>

                <!-- Category & Level -->
                <div class="formation-badges mb-3">
                    <span class="badge-category">
                        <i class="fas fa-tag me-1"></i>{{ $formation->category }}
                    </span>
                    <span class="badge-level">
                        <i class="fas fa-signal me-1"></i>{{ ucfirst($formation->level) }}
                    </span>
                    @if($formation->is_featured)
                        <span class="badge-featured">
                            <i class="fas fa-star me-1"></i>Populaire
                        </span>
                    @endif
                </div>

                <!-- Title -->
                <h1 class="formation-hero-title">{{ $formation->name }}</h1>

                <!-- Description -->
                <p class="formation-hero-description">
                    {{ $formation->short_description ?? Str::limit($formation->description, 200) }}
                </p>

                <!-- Stats -->
                <div class="formation-hero-stats">
                    <div class="stat-item">
                        <i class="fas fa-star text-warning"></i>
                        <span><strong>{{ number_format($formation->rating ?? 4.8, 1) }}</strong> ({{ $formation->reviews_count ?? 0 }} avis)</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users text-primary"></i>
                        <span><strong>{{ $formation->enrolled_count }}</strong> inscrits</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-clock text-primary"></i>
                        <span><strong>{{ $formation->duration_weeks }} semaines</strong> ({{ $formation->duration_hours }}h)</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-language text-primary"></i>
                        <span><strong>Français</strong></span>
                    </div>
                </div>
            </div>

            <!-- Sidebar Card (Desktop) -->
            <div class="col-lg-4 d-none d-lg-block">
                @include('formations.partials.enroll-card')
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="formation-content-section py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs formation-tabs mb-4" id="formationTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" 
                                data-bs-target="#overview" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Vue d'ensemble
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="program-tab" data-bs-toggle="tab" 
                                data-bs-target="#program" type="button" role="tab">
                            <i class="fas fa-list me-2"></i>Programme
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="instructor-tab" data-bs-toggle="tab" 
                                data-bs-target="#instructor" type="button" role="tab">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Formateur
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                                data-bs-target="#reviews" type="button" role="tab">
                            <i class="fas fa-star me-2"></i>Avis
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="formationTabContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="content-card">
                            <h3 class="content-title">Description</h3>
                            <div class="content-text">
                                {!! nl2br(e($formation->description)) !!}
                            </div>

                            <h3 class="content-title mt-4">Ce que vous allez apprendre</h3>
                            <div class="objectives-grid">
                                @if($formation->objectives)
                                    @foreach(json_decode($formation->objectives) as $objective)
                                    <div class="objective-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>{{ $objective }}</span>
                                    </div>
                                    @endforeach
                                @endif
                            </div>

                            @if($formation->prerequisites()->count() > 0)
                            <h3 class="content-title mt-4">Prérequis</h3>
                            <div class="prerequisites-list">
                                @foreach($formation->prerequisites as $prerequisite)
                                <div class="prerequisite-item">
                                    <i class="fas fa-book text-primary"></i>
                                    <a href="{{ route('formations.show', $prerequisite->slug) }}">
                                        {{ $prerequisite->name }}
                                    </a>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Program Tab -->
                    <div class="tab-pane fade" id="program" role="tabpanel">
                        <div class="content-card">
                            <h3 class="content-title">Programme de la Formation</h3>
                            
                            <div class="program-accordion">
                                @foreach($formation->subjects()->orderBy('order')->get() as $index => $subject)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#subject{{ $subject->id }}">
                                            <span class="module-number">Module {{ $index + 1 }}</span>
                                            <span class="module-title">{{ $subject->name }}</span>
                                            <span class="module-duration">{{ $subject->duration_hours }}h</span>
                                        </button>
                                    </h2>
                                    <div id="subject{{ $subject->id }}" 
                                         class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" 
                                         data-bs-parent=".program-accordion">
                                        <div class="accordion-body">
                                            <p>{{ $subject->description }}</p>
                                            @if($subject->courses()->count() > 0)
                                            <ul class="course-list">
                                                @foreach($subject->courses as $course)
                                                <li>
                                                    <i class="fas fa-play-circle text-primary"></i>
                                                    <span>{{ $course->title }}</span>
                                                    <span class="course-duration">{{ $course->duration_minutes }}min</span>
                                                </li>
                                                @endforeach
                                            </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Instructor Tab -->
                    <div class="tab-pane fade" id="instructor" role="tabpanel">
                        <div class="content-card">
                            @if($formation->teacher)
                            <div class="instructor-profile">
                                <div class="instructor-header">
                                    <div class="instructor-avatar">
                                        @if($formation->teacher->avatar)
                                            <img src="{{ Storage::url($formation->teacher->avatar) }}" alt="{{ $formation->teacher->name }}">
                                        @else
                                            <div class="avatar-placeholder">
                                                {{ strtoupper(substr($formation->teacher->name, 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="instructor-info">
                                        <h3 class="instructor-name">{{ $formation->teacher->name }}</h3>
                                        @if($formation->teacher->specialization)
                                            <p class="instructor-title">{{ $formation->teacher->specialization }}</p>
                                        @endif
                                        <div class="instructor-stats">
                                            <span><i class="fas fa-star text-warning"></i> {{ number_format($formation->teacher->rating ?? 4.9, 1) }}</span>
                                            <span><i class="fas fa-book"></i> {{ $formation->teacher->courses_count ?? 0 }} cours</span>
                                            <span><i class="fas fa-users"></i> {{ $formation->teacher->students_count ?? 0 }} étudiants</span>
                                        </div>
                                    </div>
                                </div>

                                @if($formation->teacher->bio)
                                <div class="instructor-bio">
                                    <h4>À propos</h4>
                                    <p>{{ $formation->teacher->bio }}</p>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="content-card">
                            <div class="reviews-summary">
                                <div class="rating-overview">
                                    <div class="rating-number">{{ number_format($formation->rating ?? 4.8, 1) }}</div>
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= round($formation->rating ?? 4.8) ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                    </div>
                                    <div class="rating-count">{{ $formation->reviews_count ?? 0 }} avis</div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="reviews-list">
                                <p class="text-muted text-center">Les avis des étudiants seront bientôt disponibles.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Desktop) -->
            <div class="col-lg-4 d-none d-lg-block">
                <div class="sticky-sidebar">
                    <!-- Enroll Card included in hero for desktop -->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mobile Enroll Button -->
<div class="mobile-enroll-bar d-lg-none">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="price-info">
                @if($formation->discount_price)
                    <span class="price-old">{{ number_format($formation->price, 0, ',', ' ') }} FCFA</span>
                    <span class="price-current">{{ number_format($formation->discount_price, 0, ',', ' ') }} FCFA</span>
                @else
                    <span class="price-current">{{ number_format($formation->price, 0, ',', ' ') }} FCFA</span>
                @endif
            </div>
            <a href="{{ route('formations.enroll', $formation->slug) }}" class="btn btn-primary">
                S'inscrire Maintenant
            </a>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* Hero Section */
    .formation-hero-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 60px 0;
    }

    .formation-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .badge-category,
    .badge-level,
    .badge-featured {
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-category {
        background: rgba(128, 0, 32, 0.1);
        color: var(--primary-color);
    }

    .badge-level {
        background: #e9ecef;
        color: var(--text-dark);
    }

    .badge-featured {
        background: #ffc107;
        color: #856404;
    }

    .formation-hero-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 20px;
        color: var(--text-dark);
    }

    .formation-hero-description {
        font-size: 1.2rem;
        color: var(--text-light);
        margin-bottom: 25px;
        line-height: 1.7;
    }

    .formation-hero-stats {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        color: var(--text-dark);
    }

    .stat-item i {
        font-size: 1.1rem;
    }

    /* Breadcrumb */
    .formation-hero-section .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }

    .formation-hero-section .breadcrumb-item a {
        color: var(--text-light);
        text-decoration: none;
    }

    .formation-hero-section .breadcrumb-item a:hover {
        color: var(--primary-color);
    }

    /* Navigation Tabs */
    .formation-tabs {
        border-bottom: 2px solid #e9ecef;
    }

    .formation-tabs .nav-link {
        border: none;
        color: var(--text-dark);
        font-weight: 600;
        padding: 15px 25px;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
    }

    .formation-tabs .nav-link:hover {
        color: var(--primary-color);
        background: rgba(128, 0, 32, 0.05);
    }

    .formation-tabs .nav-link.active {
        color: var(--primary-color);
        background: transparent;
        border-bottom-color: var(--primary-color);
    }

    /* Content Card */
    .content-card {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .content-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--text-dark);
    }

    .content-text {
        color: var(--text-light);
        line-height: 1.8;
        font-size: 1.05rem;
    }

    /* Objectives Grid */
    .objectives-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
    }

    .objective-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .objective-item i {
        font-size: 1.2rem;
        margin-top: 3px;
    }

    .objective-item span {
        flex: 1;
        color: var(--text-dark);
        line-height: 1.6;
    }

    /* Program Accordion */
    .program-accordion .accordion-item {
        border: none;
        margin-bottom: 15px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .program-accordion .accordion-button {
        background: white;
        color: var(--text-dark);
        font-weight: 600;
        padding: 20px 25px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .program-accordion .accordion-button:not(.collapsed) {
        background: var(--primary-color);
        color: white;
    }

    .module-number {
        background: rgba(128, 0, 32, 0.1);
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .accordion-button:not(.collapsed) .module-number {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .module-title {
        flex: 1;
    }

    .module-duration {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .course-list {
        list-style: none;
        padding: 0;
        margin: 15px 0 0 0;
    }

    .course-list li {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .course-list li:last-child {
        border-bottom: none;
    }

    .course-duration {
        margin-left: auto;
        font-size: 0.9rem;
        color: var(--text-light);
    }

    /* Instructor Profile */
    .instructor-profile {
        padding: 30px;
        background: #f8f9fa;
        border-radius: 12px;
    }

    .instructor-header {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
    }

    .instructor-avatar {
        flex-shrink: 0;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
    }

    .instructor-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
    }

    .instructor-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .instructor-title {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 10px;
    }

    .instructor-stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .instructor-bio h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .instructor-bio p {
        color: var(--text-light);
        line-height: 1.7;
    }

    /* Reviews */
    .reviews-summary {
        text-align: center;
        padding: 30px;
        background: #f8f9fa;
        border-radius: 12px;
    }

    .rating-number {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .rating-stars {
        font-size: 1.5rem;
        margin-bottom: 10px;
    }

    .rating-count {
        color: var(--text-light);
    }

    /* Mobile Enroll Bar */
    .mobile-enroll-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 15px 0;
        box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .mobile-enroll-bar .price-info {
        display: flex;
        flex-direction: column;
    }

    .mobile-enroll-bar .price-old {
        font-size: 0.85rem;
        color: var(--text-light);
        text-decoration: line-through;
    }

    .mobile-enroll-bar .price-current {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary-color);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .formation-hero-title {
            font-size: 2rem;
        }

        .content-card {
            padding: 25px 20px;
        }

        .formation-tabs .nav-link {
            padding: 12px 15px;
            font-size: 0.9rem;
        }

        .objectives-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .formation-hero-title {
            font-size: 1.7rem;
        }

        .formation-hero-stats {
            flex-direction: column;
            gap: 12px;
        }
    }
</style>
@endsection