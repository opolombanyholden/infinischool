@extends('layouts.app')

@section('title', 'Nos Enseignants - InfiniSchool')
@section('description', 'Découvrez nos formateurs experts qui vous accompagneront dans votre parcours d\'apprentissage. Des professionnels passionnés et qualifiés.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Nos Formateurs Experts</h1>
                <p class="page-header-subtitle">
                    Apprenez des meilleurs professionnels passionnés par la transmission
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Enseignants</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="teacher-stats-section py-4 bg-white">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-md-3 col-6">
                <div class="mini-stat">
                    <div class="mini-stat-number">{{ $teachers->count() }}</div>
                    <div class="mini-stat-label">Formateurs</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="mini-stat">
                    <div class="mini-stat-number">{{ $teachers->sum('courses_count') ?? 0 }}</div>
                    <div class="mini-stat-label">Cours Enseignés</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="mini-stat">
                    <div class="mini-stat-number">4.9</div>
                    <div class="mini-stat-label">Note Moyenne</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="mini-stat">
                    <div class="mini-stat-number">15+</div>
                    <div class="mini-stat-label">Années d'Expérience</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Teachers Grid Section -->
<section class="teachers-section py-5 bg-light">
    <div class="container">
        <!-- Filter Bar (Optional) -->
        <div class="filter-bar mb-4">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="mb-0">{{ $teachers->count() }} formateur(s) trouvé(s)</h5>
                </div>
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control" id="teacherSearch" placeholder="Rechercher un formateur...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Teachers Grid -->
        <div class="row g-4" id="teachersGrid">
            @forelse($teachers as $teacher)
            <div class="col-md-6 col-lg-4 teacher-item" data-name="{{ strtolower($teacher->name) }}">
                <div class="teacher-card">
                    <!-- Teacher Image -->
                    <div class="teacher-image">
                        @if($teacher->avatar)
                            <img src="{{ Storage::url($teacher->avatar) }}" alt="{{ $teacher->name }}">
                        @else
                            <div class="teacher-avatar-placeholder">
                                <span>{{ strtoupper(substr($teacher->name, 0, 2)) }}</span>
                            </div>
                        @endif
                        
                        <!-- Status Badge -->
                        @if($teacher->status === 'active')
                            <span class="teacher-status-badge">
                                <i class="fas fa-circle"></i> Actif
                            </span>
                        @endif
                    </div>

                    <!-- Teacher Info -->
                    <div class="teacher-content">
                        <h3 class="teacher-name">{{ $teacher->name }}</h3>
                        
                        @if($teacher->specialization)
                            <p class="teacher-specialization">
                                <i class="fas fa-award me-1"></i>{{ $teacher->specialization }}
                            </p>
                        @endif

                        @if($teacher->bio)
                            <p class="teacher-bio">
                                {{ Str::limit($teacher->bio, 100) }}
                            </p>
                        @endif

                        <!-- Stats -->
                        <div class="teacher-stats">
                            <div class="teacher-stat-item">
                                <i class="fas fa-book text-primary"></i>
                                <span>{{ $teacher->courses_count ?? 0 }} cours</span>
                            </div>
                            <div class="teacher-stat-item">
                                <i class="fas fa-users text-primary"></i>
                                <span>{{ $teacher->students_count ?? 0 }} étudiants</span>
                            </div>
                            <div class="teacher-stat-item">
                                <i class="fas fa-star text-warning"></i>
                                <span>{{ number_format($teacher->rating ?? 4.8, 1) }}</span>
                            </div>
                        </div>

                        <!-- Expertise Tags -->
                        @if($teacher->expertise)
                            <div class="teacher-expertise">
                                @foreach(explode(',', $teacher->expertise) as $skill)
                                    <span class="expertise-tag">{{ trim($skill) }}</span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Social Links -->
                        <div class="teacher-social">
                            @if($teacher->linkedin)
                                <a href="{{ $teacher->linkedin }}" target="_blank" class="social-link" aria-label="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            @endif
                            @if($teacher->twitter)
                                <a href="{{ $teacher->twitter }}" target="_blank" class="social-link" aria-label="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            @endif
                            @if($teacher->website)
                                <a href="{{ $teacher->website }}" target="_blank" class="social-link" aria-label="Website">
                                    <i class="fas fa-globe"></i>
                                </a>
                            @endif
                            <a href="mailto:{{ $teacher->email }}" class="social-link" aria-label="Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>

                        <!-- View Profile Button -->
                        <a href="{{ route('teachers.show', $teacher->id) }}" class="btn btn-primary btn-block">
                            Voir le profil <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucun formateur disponible pour le moment.
                </div>
            </div>
            @endforelse
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="alert alert-warning text-center mt-4" style="display: none;">
            <i class="fas fa-search me-2"></i>
            Aucun formateur ne correspond à votre recherche.
        </div>
    </div>
</section>

<!-- Become a Teacher CTA -->
<section class="become-teacher-section py-5">
    <div class="container">
        <div class="become-teacher-box">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2 class="become-teacher-title">Vous Êtes un Expert ?</h2>
                    <p class="become-teacher-description">
                        Rejoignez notre équipe de formateurs et partagez votre expertise avec des milliers d'étudiants 
                        motivés. Profitez d'une plateforme moderne et d'un accompagnement personnalisé.
                    </p>
                    <ul class="become-teacher-features">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Revenus attractifs</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Flexibilité totale</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Support dédié</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Outils professionnels</li>
                    </ul>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('teacher.apply') }}" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Devenir Formateur
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

    /* ========================================
       MINI STATS
    ======================================== */
    .mini-stat {
        padding: 15px;
    }

    .mini-stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .mini-stat-label {
        font-size: 0.9rem;
        color: var(--text-light);
        font-weight: 500;
    }

    /* ========================================
       FILTER BAR
    ======================================== */
    .filter-bar {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .search-box {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        z-index: 5;
    }

    .search-box .form-control {
        padding-left: 45px;
        border-radius: 10px;
        border: 2px solid #e9ecef;
        height: 45px;
    }

    .search-box .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 32, 0.1);
    }

    /* ========================================
       TEACHER CARDS
    ======================================== */
    .teacher-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .teacher-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    /* Teacher Image */
    .teacher-image {
        position: relative;
        height: 280px;
        overflow: hidden;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .teacher-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .teacher-card:hover .teacher-image img {
        transform: scale(1.1);
    }

    .teacher-avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        font-size: 4rem;
        font-weight: 700;
    }

    .teacher-status-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(40, 167, 69, 0.95);
        color: white;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .teacher-status-badge i {
        font-size: 0.6rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Teacher Content */
    .teacher-content {
        padding: 25px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .teacher-name {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-dark);
    }

    .teacher-specialization {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 12px;
    }

    .teacher-bio {
        color: var(--text-light);
        line-height: 1.6;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    /* Teacher Stats */
    .teacher-stats {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .teacher-stat-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .teacher-stat-item i {
        font-size: 1rem;
    }

    /* Expertise Tags */
    .teacher-expertise {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }

    .expertise-tag {
        background: #f8f9fa;
        color: var(--text-dark);
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Social Links */
    .teacher-social {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .social-link {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .social-link:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-3px);
    }

    /* View Profile Button */
    .teacher-card .btn-block {
        width: 100%;
        margin-top: auto;
    }

    /* ========================================
       BECOME A TEACHER SECTION
    ======================================== */
    .become-teacher-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    }

    .become-teacher-box {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 50px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .become-teacher-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
    }

    .become-teacher-description {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 20px;
        line-height: 1.7;
    }

    .become-teacher-features {
        list-style: none;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin: 0;
    }

    .become-teacher-features li {
        color: white;
        font-size: 1rem;
        font-weight: 500;
    }

    .become-teacher-box .btn-light {
        background: white;
        color: var(--primary-color);
        border: none;
        font-weight: 700;
    }

    .become-teacher-box .btn-light:hover {
        background: #f8f9fa;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    /* ========================================
       RESPONSIVE
    ======================================== */
    @media (max-width: 992px) {
        .page-header-title {
            font-size: 2.5rem;
        }

        .teacher-image {
            height: 250px;
        }

        .become-teacher-title {
            font-size: 2rem;
        }

        .become-teacher-box {
            padding: 40px 30px;
        }

        .become-teacher-features {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .page-header-title {
            font-size: 2rem;
        }

        .teacher-stats {
            flex-direction: column;
            gap: 8px;
        }

        .become-teacher-box {
            padding: 30px 20px;
        }
    }

    /* Hide/Show for search */
    .teacher-item.hidden {
        display: none;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('teacherSearch');
        const teacherItems = document.querySelectorAll('.teacher-item');
        const noResults = document.getElementById('noResults');

        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                let visibleCount = 0;

                teacherItems.forEach(item => {
                    const teacherName = item.getAttribute('data-name');
                    
                    if (teacherName.includes(searchTerm)) {
                        item.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            });
        }

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(30px)';
                    
                    setTimeout(() => {
                        entry.target.style.transition = 'all 0.6s ease-out';
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);
                    
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all teacher cards
        teacherItems.forEach(card => {
            observer.observe(card);
        });
    });
</script>
@endsection