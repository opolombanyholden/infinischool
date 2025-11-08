@extends('layouts.app')

@section('title', 'Accueil - InfiniSchool | Plateforme d\'apprentissage en ligne')
@section('description', 'Rejoignez InfiniSchool et transformez votre apprentissage avec nos cours en direct, classes virtuelles et formateurs qualifiés. Des milliers d\'étudiants nous font déjà confiance.')

@section('content')

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="hero-content animate-fade-in-up">
                    <span class="hero-badge">
                        <i class="fas fa-star me-2"></i>
                        Plateforme N°1 au Gabon
                    </span>
                    <h1 class="hero-title">
                        Apprenez Sans Limites avec 
                        <span class="text-primary">InfiniSchool</span>
                    </h1>
                    <p class="hero-description">
                        Transformez votre avenir avec nos cours en direct, formateurs experts et communauté dynamique. 
                        Plus de {{ number_format($stats['total_students'] ?? 0) }} étudiants nous font confiance.
                    </p>
                    
                    <div class="hero-cta">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-rocket me-2"></i>Commencer Gratuitement
                        </a>
                        <a href="{{ route('formations.index') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-book me-2"></i>Explorer les Cours
                        </a>
                    </div>

                    <!-- Stats Row -->
                    <div class="hero-stats">
                        <div class="hero-stat-item">
                            <div class="hero-stat-number">{{ $stats['total_students'] ?? 0 }}+</div>
                            <div class="hero-stat-label">Étudiants</div>
                        </div>
                        <div class="hero-stat-item">
                            <div class="hero-stat-number">{{ $stats['total_formations'] ?? 0 }}+</div>
                            <div class="hero-stat-label">Formations</div>
                        </div>
                        <div class="hero-stat-item">
                            <div class="hero-stat-number">{{ $stats['total_teachers'] ?? 0 }}+</div>
                            <div class="hero-stat-label">Formateurs</div>
                        </div>
                        <div class="hero-stat-item">
                            <div class="hero-stat-number">{{ $stats['total_certificates'] ?? 0 }}+</div>
                            <div class="hero-stat-label">Certificats</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-image animate-fade-in">
                    <div class="hero-image-wrapper">
                        <img src="{{ asset('images/hero-education.svg') }}" alt="Online Learning" class="img-fluid">
                        
                        <!-- Floating Cards -->
                        <div class="floating-card floating-card-1">
                            <i class="fas fa-video text-primary"></i>
                            <span>Cours en Direct</span>
                        </div>
                        <div class="floating-card floating-card-2">
                            <i class="fas fa-certificate text-warning"></i>
                            <span>Certificats</span>
                        </div>
                        <div class="floating-card floating-card-3">
                            <i class="fas fa-users text-success"></i>
                            <span>Communauté</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3 class="feature-title">Cours en Direct</h3>
                    <p class="feature-description">
                        Interagissez en temps réel avec vos formateurs et posez vos questions instantanément.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="feature-title">Formateurs Experts</h3>
                    <p class="feature-description">
                        Apprenez des meilleurs professionnels avec des années d'expérience dans leur domaine.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="feature-title">Certifications</h3>
                    <p class="feature-description">
                        Obtenez des certificats reconnus pour valoriser vos compétences professionnelles.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="feature-title">Flexibilité Totale</h3>
                    <p class="feature-description">
                        Apprenez à votre rythme, où vous voulez et quand vous voulez, 24h/24.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Formations Section -->
<section class="formations-section py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge">Nos Meilleures Offres</span>
            <h2 class="section-title">Formations Populaires</h2>
            <p class="section-description">
                Découvrez nos formations les plus demandées et commencez à apprendre dès aujourd'hui
            </p>
        </div>

        <div class="row g-4">
            @forelse($formations as $formation)
            <div class="col-md-6 col-lg-4">
                <div class="formation-card">
                    @if($formation->image)
                        <div class="formation-image">
                            <img src="{{ Storage::url($formation->image) }}" alt="{{ $formation->name }}">
                            @if($formation->is_featured)
                                <span class="formation-badge-featured">
                                    <i class="fas fa-star"></i> Populaire
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
                    Aucune formation disponible pour le moment. Revenez bientôt !
                </div>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('formations.index') }}" class="btn btn-outline-primary btn-lg">
                Voir Toutes les Formations <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="why-image">
                    <img src="{{ asset('images/why-choose-us.svg') }}" alt="Pourquoi InfiniSchool" class="img-fluid">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="why-content">
                    <span class="section-badge">Pourquoi Nous Choisir</span>
                    <h2 class="section-title mb-4">La Meilleure Expérience d'Apprentissage en Ligne</h2>
                    
                    <div class="why-list">
                        <div class="why-item">
                            <div class="why-item-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="why-item-content">
                                <h4>Inscription Simple et Rapide</h4>
                                <p>Créez votre compte en moins de 2 minutes et accédez immédiatement à nos formations.</p>
                            </div>
                        </div>

                        <div class="why-item">
                            <div class="why-item-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="why-item-content">
                                <h4>Suivi Personnalisé</h4>
                                <p>Bénéficiez d'un accompagnement individualisé tout au long de votre parcours d'apprentissage.</p>
                            </div>
                        </div>

                        <div class="why-item">
                            <div class="why-item-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="why-item-content">
                                <h4>Communauté Active</h4>
                                <p>Échangez avec des milliers d'étudiants et créez votre réseau professionnel.</p>
                            </div>
                        </div>

                        <div class="why-item">
                            <div class="why-item-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="why-item-content">
                                <h4>Support 24/7</h4>
                                <p>Notre équipe est disponible à tout moment pour répondre à vos questions.</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('about') }}" class="btn btn-primary btn-lg mt-4">
                        En Savoir Plus <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="cta-box">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2 class="cta-title">Prêt à Commencer Votre Parcours d'Apprentissage ?</h2>
                    <p class="cta-description">
                        Rejoignez des milliers d'étudiants qui transforment leur vie avec InfiniSchool. 
                        Inscription gratuite, sans engagement.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-user-plus me-2"></i>S'inscrire Maintenant
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('styles')
<style>
    /* ========================================
       HERO SECTION
    ======================================== */
    .hero-section {
        padding: 80px 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(128, 0, 32, 0.05) 0%, transparent 70%);
        animation: heroBackground 20s linear infinite;
    }

    @keyframes heroBackground {
        0%, 100% { transform: rotate(0deg); }
        50% { transform: rotate(180deg); }
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 20px;
        color: var(--text-dark);
    }

    .hero-description {
        font-size: 1.2rem;
        color: var(--text-light);
        line-height: 1.8;
        margin-bottom: 30px;
    }

    .hero-cta {
        margin-bottom: 40px;
    }

    .hero-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .hero-stat-item {
        text-align: center;
    }

    .hero-stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .hero-stat-label {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    /* Hero Image */
    .hero-image-wrapper {
        position: relative;
        animation: heroFloat 6s ease-in-out infinite;
    }

    @keyframes heroFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    .floating-card {
        position: absolute;
        background: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        animation: cardFloat 4s ease-in-out infinite;
    }

    .floating-card-1 {
        top: 10%;
        left: -10%;
        animation-delay: 0s;
    }

    .floating-card-2 {
        top: 50%;
        right: -10%;
        animation-delay: 1s;
    }

    .floating-card-3 {
        bottom: 10%;
        left: 10%;
        animation-delay: 2s;
    }

    @keyframes cardFloat {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-15px) rotate(2deg); }
    }

    /* ========================================
       FEATURES SECTION
    ======================================== */
    .features-section {
        background: white;
    }

    .feature-card {
        text-align: center;
        padding: 30px;
        border-radius: 15px;
        transition: all 0.3s ease;
        height: 100%;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
    }

    .feature-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: var(--text-dark);
    }

    .feature-description {
        color: var(--text-light);
        line-height: 1.6;
        margin: 0;
    }

    /* ========================================
       FORMATIONS SECTION
    ======================================== */
    .section-header {
        max-width: 700px;
        margin: 0 auto;
    }

    .section-badge {
        display: inline-block;
        background: rgba(128, 0, 32, 0.1);
        color: var(--primary-color);
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 15px;
        color: var(--text-dark);
    }

    .section-description {
        font-size: 1.1rem;
        color: var(--text-light);
    }

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
        right: 15px;
        background: #ffc107;
        color: #856404;
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
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 15px;
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
        margin-bottom: 20px;
        flex: 1;
    }

    .formation-info {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }

    .formation-info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .formation-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .formation-price {
        display: flex;
        flex-direction: column;
    }

    .price-old {
        font-size: 0.9rem;
        color: var(--text-light);
        text-decoration: line-through;
    }

    .price-current {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary-color);
    }

    /* ========================================
       WHY SECTION
    ======================================== */
    .why-section {
        background: white;
    }

    .why-item {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }

    .why-item-icon {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        background: rgba(128, 0, 32, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--primary-color);
    }

    .why-item-content h4 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-dark);
    }

    .why-item-content p {
        color: var(--text-light);
        line-height: 1.6;
        margin: 0;
    }

    /* ========================================
       CTA SECTION
    ======================================== */
    .cta-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    }

    .cta-box {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 60px;
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
        margin: 0;
    }

    .cta-box .btn-light {
        background: white;
        color: var(--primary-color);
        font-weight: 700;
        border: none;
    }

    .cta-box .btn-light:hover {
        background: #f8f9fa;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    /* ========================================
       RESPONSIVE
    ======================================== */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .hero-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .section-title {
            font-size: 2rem;
        }

        .cta-title {
            font-size: 2rem;
        }

        .cta-box {
            padding: 40px 30px;
        }
    }

    @media (max-width: 576px) {
        .hero-section {
            padding: 50px 0;
        }

        .hero-title {
            font-size: 2rem;
        }

        .hero-description {
            font-size: 1rem;
        }

        .hero-cta {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .hero-cta .btn {
            width: 100%;
            margin: 0 !important;
        }

        .hero-stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .hero-stat-number {
            font-size: 1.5rem;
        }

        .floating-card {
            display: none;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.feature-card, .formation-card, .why-item').forEach(el => {
            observer.observe(el);
        });
    });
</script>
@endsection