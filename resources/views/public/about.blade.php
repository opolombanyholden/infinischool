@extends('layouts.app')

@section('title', 'À propos - InfiniSchool')
@section('description', 'Découvrez InfiniSchool, la plateforme e-learning qui révolutionne l\'apprentissage en ligne au Gabon avec des cours en direct et une communauté engagée.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">À Propos d'InfiniSchool</h1>
                <p class="page-header-subtitle">
                    Votre partenaire pour une éducation sans limites
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">À propos</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section class="story-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="story-image">
                    <img src="{{ asset('images/about-story.svg') }}" alt="Notre Histoire" class="img-fluid">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="story-content">
                    <span class="section-badge">Notre Histoire</span>
                    <h2 class="section-title mb-4">L'Excellence de l'Apprentissage en Ligne</h2>
                    <p class="lead">
                        InfiniSchool est née d'une vision simple : rendre l'éducation de qualité accessible à tous, 
                        partout et à tout moment.
                    </p>
                    <p>
                        Fondée en 2020, notre plateforme s'est rapidement imposée comme la référence de l'e-learning 
                        au Gabon. Nous croyons fermement que chaque personne mérite d'avoir accès à une éducation 
                        de qualité, peu importe sa localisation ou ses contraintes de temps.
                    </p>
                    <p>
                        Aujourd'hui, nous sommes fiers de compter plus de <strong>{{ number_format($stats['total_students']) }} étudiants</strong>, 
                        <strong>{{ $stats['total_teachers'] }} formateurs experts</strong>, et d'avoir délivré 
                        <strong>{{ $stats['total_certificates'] }} certificats</strong> qui ont changé des vies.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission, Vision, Values Section -->
<section class="mvv-section py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Mission -->
            <div class="col-lg-4">
                <div class="mvv-card">
                    <div class="mvv-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 class="mvv-title">Notre Mission</h3>
                    <p class="mvv-description">
                        Démocratiser l'accès à l'éducation en offrant des formations de qualité, accessibles et 
                        interactives qui permettent à chacun de développer ses compétences et réaliser son potentiel.
                    </p>
                </div>
            </div>

            <!-- Vision -->
            <div class="col-lg-4">
                <div class="mvv-card">
                    <div class="mvv-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="mvv-title">Notre Vision</h3>
                    <p class="mvv-description">
                        Devenir la plateforme d'apprentissage en ligne de référence en Afrique centrale, reconnue 
                        pour l'excellence de ses formations et l'impact positif sur la vie de nos apprenants.
                    </p>
                </div>
            </div>

            <!-- Values -->
            <div class="col-lg-4">
                <div class="mvv-card">
                    <div class="mvv-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="mvv-title">Nos Valeurs</h3>
                    <p class="mvv-description">
                        Excellence, innovation, accessibilité et bienveillance guident chacune de nos actions. 
                        Nous croyons en l'apprentissage continu et au pouvoir transformateur de l'éducation.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-12 mb-5">
                <h2 class="section-title">InfiniSchool en Chiffres</h2>
                <p class="section-description">Des résultats qui parlent d'eux-mêmes</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number" data-count="{{ $stats['total_students'] }}">0</div>
                    <div class="stat-label">Étudiants Actifs</div>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number" data-count="{{ $stats['total_formations'] }}">0</div>
                    <div class="stat-label">Formations</div>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-number" data-count="{{ $stats['total_teachers'] }}">0</div>
                    <div class="stat-label">Formateurs Experts</div>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-number" data-count="{{ $stats['success_rate'] ?? 95 }}">0</div>
                    <div class="stat-label">Taux de Réussite</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What Makes Us Different -->
<section class="different-section py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <span class="section-badge">Notre Différence</span>
                <h2 class="section-title">Ce Qui Nous Rend Uniques</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="difference-card">
                    <div class="difference-number">01</div>
                    <div class="difference-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h4 class="difference-title">Cours en Direct</h4>
                    <p class="difference-description">
                        Des sessions live avec interactions en temps réel pour un apprentissage dynamique et engageant.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="difference-card">
                    <div class="difference-number">02</div>
                    <div class="difference-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4 class="difference-title">Suivi Personnalisé</h4>
                    <p class="difference-description">
                        Chaque étudiant bénéficie d'un accompagnement individualisé pour maximiser sa progression.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="difference-card">
                    <div class="difference-number">03</div>
                    <div class="difference-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h4 class="difference-title">Certifications Reconnues</h4>
                    <p class="difference-description">
                        Des diplômes et certificats valorisés par les employeurs pour booster votre carrière.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="difference-card">
                    <div class="difference-number">04</div>
                    <div class="difference-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h4 class="difference-title">Communauté Active</h4>
                    <p class="difference-description">
                        Rejoignez une communauté dynamique d'apprenants motivés et créez votre réseau professionnel.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose InfiniSchool -->
<section class="why-choose-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="section-title">Pourquoi Choisir InfiniSchool ?</h2>
                <p class="section-description">Les avantages qui font la différence</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4 class="advantage-title">Flexibilité Totale</h4>
                    <p class="advantage-description">
                        Apprenez à votre rythme, quand vous voulez et où vous voulez. Nos cours s'adaptent à votre emploi du temps.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h4 class="advantage-title">Formateurs Qualifiés</h4>
                    <p class="advantage-description">
                        Apprenez des meilleurs experts avec des années d'expérience pratique dans leur domaine.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h4 class="advantage-title">Plateforme Moderne</h4>
                    <p class="advantage-description">
                        Interface intuitive et technologie de pointe pour une expérience d'apprentissage optimale.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4 class="advantage-title">Support 24/7</h4>
                    <p class="advantage-description">
                        Notre équipe est disponible à tout moment pour répondre à vos questions et vous accompagner.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h4 class="advantage-title">Prix Accessibles</h4>
                    <p class="advantage-description">
                        Des tarifs compétitifs et des facilités de paiement pour rendre l'éducation accessible à tous.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="advantage-title">Suivi de Progression</h4>
                    <p class="advantage-description">
                        Tableaux de bord détaillés pour suivre vos progrès et mesurer votre évolution en temps réel.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="cta-box text-center">
            <h2 class="cta-title">Prêt à Transformer Votre Avenir ?</h2>
            <p class="cta-description mb-4">
                Rejoignez InfiniSchool aujourd'hui et donnez un nouveau souffle à votre carrière
            </p>
            <div class="cta-buttons">
                <a href="{{ route('register') }}" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-user-plus me-2"></i>S'inscrire Gratuitement
                </a>
                <a href="{{ route('formations.index') }}" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-book me-2"></i>Découvrir nos Formations
                </a>
            </div>
        </div>
    </div>
</section>

@endsection

@section('styles')
<style>
    /* ========================================
       PAGE HEADER
    ======================================== */
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
        opacity: 0.3;
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

    .page-header-section .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.6);
    }

    /* ========================================
       STORY SECTION
    ======================================== */
    .story-section {
        background: white;
    }

    .story-image img {
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    /* ========================================
       MVV SECTION (Mission, Vision, Values)
    ======================================== */
    .mvv-card {
        background: white;
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
    }

    .mvv-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .mvv-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 25px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
    }

    .mvv-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: var(--text-dark);
    }

    .mvv-description {
        color: var(--text-light);
        line-height: 1.8;
        margin: 0;
    }

    /* ========================================
       STATISTICS SECTION
    ======================================== */
    .stats-section {
        background: white;
    }

    .stat-card {
        text-align: center;
        padding: 30px 20px;
        background: #f8f9fa;
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        background: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }

    .stat-icon {
        font-size: 3rem;
        color: var(--primary-color);
        margin-bottom: 15px;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .stat-label {
        font-size: 1rem;
        color: var(--text-light);
        font-weight: 600;
    }

    /* ========================================
       DIFFERENCE SECTION
    ======================================== */
    .difference-card {
        background: white;
        padding: 35px 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
        height: 100%;
        position: relative;
        transition: all 0.3s ease;
    }

    .difference-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .difference-number {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 3rem;
        font-weight: 800;
        color: rgba(128, 0, 32, 0.1);
    }

    .difference-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 20px;
        background: rgba(128, 0, 32, 0.1);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--primary-color);
    }

    .difference-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: var(--text-dark);
    }

    .difference-description {
        color: var(--text-light);
        line-height: 1.6;
        margin: 0;
    }

    /* ========================================
       ADVANTAGE CARDS
    ======================================== */
    .advantage-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        height: 100%;
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }

    .advantage-card:hover {
        transform: translateX(10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .advantage-icon {
        width: 60px;
        height: 60px;
        background: rgba(128, 0, 32, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .advantage-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 12px;
        color: var(--text-dark);
    }

    .advantage-description {
        color: var(--text-light);
        line-height: 1.6;
        margin: 0;
        font-size: 0.95rem;
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
        padding: 70px 50px;
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

    .cta-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .cta-box .btn-light {
        background: white;
        color: var(--primary-color);
        border: none;
    }

    .cta-box .btn-light:hover {
        background: #f8f9fa;
        transform: translateY(-3px);
    }

    .cta-box .btn-outline-light {
        border: 2px solid white;
        color: white;
    }

    .cta-box .btn-outline-light:hover {
        background: white;
        color: var(--primary-color);
        transform: translateY(-3px);
    }

    /* ========================================
       RESPONSIVE
    ======================================== */
    @media (max-width: 992px) {
        .page-header-title {
            font-size: 2.5rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .cta-title {
            font-size: 2rem;
        }

        .cta-box {
            padding: 50px 30px;
        }
    }

    @media (max-width: 576px) {
        .page-header-title {
            font-size: 2rem;
        }

        .stat-number {
            font-size: 2.5rem;
        }

        .difference-number {
            font-size: 2rem;
            top: 15px;
            right: 15px;
        }

        .cta-buttons {
            flex-direction: column;
        }

        .cta-buttons .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate counters
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200; // Animation speed

        const animateCounter = (counter) => {
            const target = parseInt(counter.getAttribute('data-count'));
            const increment = target / speed;
            let count = 0;

            const updateCount = () => {
                count += increment;
                if (count < target) {
                    counter.textContent = Math.ceil(count);
                    requestAnimationFrame(updateCount);
                } else {
                    counter.textContent = target + '+';
                }
            };

            updateCount();
        };

        // Intersection Observer for counters
        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    animateCounter(entry.target);
                    entry.target.classList.add('counted');
                }
            });
        }, observerOptions);

        counters.forEach(counter => observer.observe(counter));
    });
</script>
@endsection