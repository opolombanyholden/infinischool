@extends('layouts.dashboard')

@section('title', 'Mon Tableau de Bord - InfiniSchool')

@section('sidebar-menu')
<!-- Student Navigation Menu -->
<div class="sidebar-nav-title">Navigation</div>

<div class="sidebar-nav-item">
    <a href="{{ route('student.dashboard') }}" class="sidebar-nav-link active">
        <i class="fas fa-home sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Tableau de Bord</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('student.courses') }}" class="sidebar-nav-link">
        <i class="fas fa-book sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Mes Cours</span>
        <span class="sidebar-nav-badge">{{ $enrolledCoursesCount ?? 0 }}</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('student.schedule') }}" class="sidebar-nav-link">
        <i class="fas fa-calendar sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Planning</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('student.progress') }}" class="sidebar-nav-link">
        <i class="fas fa-chart-line sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Ma Progression</span>
    </a>
</div>

<div class="sidebar-nav-title">Apprentissage</div>

<div class="sidebar-nav-item">
    <a href="{{ route('student.assignments') }}" class="sidebar-nav-link">
        <i class="fas fa-tasks sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Devoirs</span>
        <span class="sidebar-nav-badge">{{ $pendingAssignmentsCount ?? 0 }}</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('student.resources') }}" class="sidebar-nav-link">
        <i class="fas fa-folder sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Ressources</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('student.certificates') }}" class="sidebar-nav-link">
        <i class="fas fa-certificate sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Certificats</span>
    </a>
</div>

<div class="sidebar-nav-title">Communication</div>

<div class="sidebar-nav-item">
    <a href="{{ route('messages.index') }}" class="sidebar-nav-link">
        <i class="fas fa-comments sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Messages</span>
        <span class="sidebar-nav-badge">{{ $unreadMessagesCount ?? 0 }}</span>
    </a>
</div>

<div class="sidebar-nav-item">
    <a href="{{ route('student.community') }}" class="sidebar-nav-link">
        <i class="fas fa-users sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Communauté</span>
    </a>
</div>

<div class="sidebar-nav-title">Compte</div>

<div class="sidebar-nav-item">
    <a href="{{ route('profile.edit') }}" class="sidebar-nav-link">
        <i class="fas fa-user-cog sidebar-nav-icon"></i>
        <span class="sidebar-nav-text">Paramètres</span>
    </a>
</div>
@endsection

@section('content')

<!-- Page Header -->
<div class="page-header mb-4">
    <h1 class="page-title">Bonjour, {{ Auth::user()->name }} ! 👋</h1>
    <p class="page-subtitle">Voici un aperçu de votre progression et activités récentes</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['enrolled_courses'] ?? 0 }}</div>
                <div class="stat-label">Formations Actives</div>
            </div>
            <div class="stat-progress">
                <div class="progress">
                    <div class="progress-bar bg-primary" style="width: 75%"></div>
                </div>
                <small class="text-muted">En cours</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['completed_courses'] ?? 0 }}</div>
                <div class="stat-label">Formations Terminées</div>
            </div>
            <div class="stat-progress">
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
                <small class="text-muted">Complétées</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['hours_learned'] ?? 0 }}h</div>
                <div class="stat-label">Heures d'Apprentissage</div>
            </div>
            <div class="stat-progress">
                <div class="progress">
                    <div class="progress-bar bg-warning" style="width: 60%"></div>
                </div>
                <small class="text-muted">Ce mois-ci</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-certificate"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['certificates'] ?? 0 }}</div>
                <div class="stat-label">Certificats Obtenus</div>
            </div>
            <div class="stat-progress">
                <div class="progress">
                    <div class="progress-bar bg-info" style="width: 90%"></div>
                </div>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        
        <!-- Current Courses -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-book text-primary me-2"></i>
                    Mes Formations en Cours
                </h5>
                <a href="{{ route('student.courses') }}" class="btn btn-sm btn-outline-primary">
                    Voir tout <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                @forelse($currentCourses ?? [] as $enrollment)
                <div class="course-item">
                    <div class="course-item-image">
                        @if($enrollment->formation->image)
                            <img src="{{ Storage::url($enrollment->formation->image) }}" alt="{{ $enrollment->formation->name }}">
                        @else
                            <div class="course-placeholder">
                                <i class="fas fa-book"></i>
                            </div>
                        @endif
                    </div>
                    <div class="course-item-content">
                        <h6 class="course-item-title">{{ $enrollment->formation->name }}</h6>
                        <p class="course-item-category">
                            <i class="fas fa-tag me-1"></i>{{ $enrollment->formation->category }}
                        </p>
                        <div class="course-item-progress">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Progression</span>
                                <span class="fw-bold text-primary">{{ $enrollment->progress ?? 0 }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width: {{ $enrollment->progress ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="course-item-actions">
                        <a href="{{ route('student.courses.show', $enrollment->id) }}" class="btn btn-primary btn-sm">
                            Continuer
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-book-open text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="text-muted mt-3">Vous n'êtes inscrit à aucune formation pour le moment.</p>
                    <a href="{{ route('formations.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Découvrir les Formations
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    Prochains Cours en Direct
                </h5>
                <a href="{{ route('student.schedule') }}" class="btn btn-sm btn-outline-primary">
                    Planning complet <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                @forelse($upcomingClasses ?? [] as $class)
                <div class="class-item">
                    <div class="class-date">
                        <div class="class-day">{{ $class->start_time->format('d') }}</div>
                        <div class="class-month">{{ $class->start_time->format('M') }}</div>
                    </div>
                    <div class="class-content">
                        <h6 class="class-title">{{ $class->subject->name }}</h6>
                        <p class="class-formation">{{ $class->formation->name }}</p>
                        <div class="class-meta">
                            <span class="class-time">
                                <i class="fas fa-clock me-1"></i>
                                {{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}
                            </span>
                            <span class="class-teacher">
                                <i class="fas fa-user me-1"></i>
                                {{ $class->teacher->name }}
                            </span>
                        </div>
                    </div>
                    <div class="class-actions">
                        @if($class->zoom_link)
                            <a href="{{ $class->zoom_link }}" target="_blank" class="btn btn-success btn-sm">
                                <i class="fas fa-video me-1"></i>Rejoindre
                            </a>
                        @else
                            <span class="badge bg-secondary">Bientôt disponible</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="text-muted mt-3">Aucun cours programmé pour le moment.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt text-warning me-2"></i>
                    Actions Rapides
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('formations.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Nouvelle Formation
                    </a>
                    <a href="{{ route('student.schedule') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar me-2"></i>Mon Planning
                    </a>
                    <a href="{{ route('messages.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>Mes Messages
                        @if(($unreadMessagesCount ?? 0) > 0)
                            <span class="badge bg-danger ms-1">{{ $unreadMessagesCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- Overall Progress -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    Progression Globale
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="progress-circle mx-auto" style="width: 150px; height: 150px;">
                        <svg viewBox="0 0 36 36">
                            <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="circle" stroke-dasharray="{{ $stats['overall_progress'] ?? 68 }}, 100" 
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <text x="18" y="20.35" class="percentage">{{ $stats['overall_progress'] ?? 68 }}%</text>
                        </svg>
                    </div>
                    <p class="text-muted mt-3 mb-0">Vous progressez bien ! Continuez ainsi.</p>
                </div>
                
                <div class="progress-details">
                    <div class="progress-detail-item">
                        <span class="text-muted">Cours complétés</span>
                        <strong>{{ $stats['completed_lessons'] ?? 0 }}/{{ $stats['total_lessons'] ?? 0 }}</strong>
                    </div>
                    <div class="progress-detail-item">
                        <span class="text-muted">Devoirs rendus</span>
                        <strong>{{ $stats['submitted_assignments'] ?? 0 }}/{{ $stats['total_assignments'] ?? 0 }}</strong>
                    </div>
                    <div class="progress-detail-item">
                        <span class="text-muted">Note moyenne</span>
                        <strong class="text-success">{{ $stats['average_grade'] ?? 0 }}/20</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Achievements -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy text-warning me-2"></i>
                    Succès Récents
                </h5>
            </div>
            <div class="card-body">
                @forelse($recentAchievements ?? [] as $achievement)
                <div class="achievement-item">
                    <div class="achievement-icon">
                        <i class="{{ $achievement->icon }} text-warning"></i>
                    </div>
                    <div class="achievement-content">
                        <h6 class="achievement-title">{{ $achievement->title }}</h6>
                        <p class="achievement-date">{{ $achievement->earned_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-3">
                    <i class="fas fa-medal text-muted" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.9rem;">
                        Continuez à apprendre pour débloquer des succès !
                    </p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection

@section('styles')
<style>
    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary-color);
    }

    .stat-card.stat-primary::before { background: #800020; }
    .stat-card.stat-success::before { background: #28a745; }
    .stat-card.stat-warning::before { background: #ffc107; }
    .stat-card.stat-info::before { background: #17a2b8; }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .stat-primary .stat-icon { background: rgba(128, 0, 32, 0.1); color: #800020; }
    .stat-success .stat-icon { background: rgba(40, 167, 69, 0.1); color: #28a745; }
    .stat-warning .stat-icon { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .stat-info .stat-icon { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-light);
        font-weight: 500;
    }

    .stat-progress {
        margin-top: 15px;
    }

    .stat-progress .progress {
        height: 6px;
        border-radius: 3px;
        margin-bottom: 5px;
    }

    /* Course Items */
    .course-item {
        display: flex;
        gap: 20px;
        padding: 20px;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .course-item:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-color: var(--primary-color);
    }

    .course-item:last-child {
        margin-bottom: 0;
    }

    .course-item-image {
        flex-shrink: 0;
        width: 100px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
    }

    .course-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .course-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #800020 0%, #5a0016 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }

    .course-item-content {
        flex: 1;
    }

    .course-item-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--text-dark);
    }

    .course-item-category {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-bottom: 10px;
    }

    .course-item-progress .progress {
        height: 8px;
        border-radius: 4px;
    }

    /* Class Items */
    .class-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .class-item:last-child {
        margin-bottom: 0;
    }

    .class-date {
        flex-shrink: 0;
        width: 60px;
        text-align: center;
        padding: 10px;
        background: var(--primary-color);
        color: white;
        border-radius: 8px;
    }

    .class-day {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .class-month {
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .class-content {
        flex: 1;
    }

    .class-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .class-formation {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-bottom: 8px;
    }

    .class-meta {
        display: flex;
        gap: 15px;
        font-size: 0.85rem;
        color: var(--text-light);
    }

    /* Progress Circle */
    .progress-circle {
        position: relative;
    }

    .progress-circle svg {
        transform: rotate(-90deg);
    }

    .circle-bg {
        fill: none;
        stroke: #e9ecef;
        stroke-width: 2.8;
    }

    .circle {
        fill: none;
        stroke: #800020;
        stroke-width: 2.8;
        stroke-linecap: round;
        animation: progress 1s ease-out forwards;
    }

    @keyframes progress {
        0% { stroke-dasharray: 0 100; }
    }

    .percentage {
        fill: #800020;
        font-size: 0.4em;
        font-weight: bold;
        text-anchor: middle;
        transform: rotate(90deg) translateX(-10px);
    }

    .progress-details {
        margin-top: 20px;
    }

    .progress-detail-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .progress-detail-item:last-child {
        border-bottom: none;
    }

    /* Achievement Items */
    .achievement-item {
        display: flex;
        gap: 15px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .achievement-item:last-child {
        margin-bottom: 0;
    }

    .achievement-icon {
        flex-shrink: 0;
        width: 45px;
        height: 45px;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .achievement-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .achievement-date {
        font-size: 0.8rem;
        color: var(--text-light);
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .course-item {
            flex-direction: column;
        }

        .course-item-image {
            width: 100%;
            height: 150px;
        }

        .class-item {
            flex-wrap: wrap;
        }

        .class-actions {
            width: 100%;
            margin-top: 10px;
        }

        .class-actions .btn {
            width: 100%;
        }
    }
</style>
@endsection