@extends('layouts.dashboard')

@section('title', 'Tableau de bord Enseignant - InfiniSchool')
@section('description', 'Gérez vos cours, suivez vos étudiants et consultez vos revenus sur InfiniSchool')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header avec salutation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-2 fw-bold">
                        <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                        Bonjour, {{ Auth::user()->first_name }} !
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="far fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('teacher.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Créer un cours
                    </a>
                    <a href="{{ route('teacher.profile.edit') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-user-edit me-2"></i>Mon profil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Cours actifs -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="fas fa-book-open fa-2x"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success">Actifs</span>
                    </div>
                    <h3 class="h2 fw-bold mb-1">{{ $stats['active_courses'] ?? 0 }}</h3>
                    <p class="text-muted mb-0 small">Cours actifs</p>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ ($stats['active_courses'] ?? 0) / max(($stats['total_courses'] ?? 1), 1) * 100 }}%">
                        </div>
                    </div>
                    <small class="text-muted">{{ $stats['total_courses'] ?? 0 }} cours au total</small>
                </div>
            </div>
        </div>

        <!-- Total étudiants -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <span class="badge bg-info-subtle text-info">
                            <i class="fas fa-arrow-up me-1"></i>+{{ $stats['new_students_this_month'] ?? 0 }}
                        </span>
                    </div>
                    <h3 class="h2 fw-bold mb-1">{{ $stats['total_students'] ?? 0 }}</h3>
                    <p class="text-muted mb-0 small">Étudiants inscrits</p>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
                    </div>
                    <small class="text-muted">{{ $stats['active_students'] ?? 0 }} actifs ce mois</small>
                </div>
            </div>
        </div>

        <!-- Revenus -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success">Ce mois</span>
                    </div>
                    <h3 class="h2 fw-bold mb-1">{{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ') }} FCFA</h3>
                    <p class="text-muted mb-0 small">Revenus mensuels</p>
                    <div class="progress mt-3" style="height: 4px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                    </div>
                    <small class="text-muted">{{ number_format($stats['total_revenue'] ?? 0, 0, ',', ' ') }} FCFA au total</small>
                </div>
            </div>
        </div>

        <!-- Note moyenne -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                        <span class="badge bg-warning-subtle text-warning">Évaluation</span>
                    </div>
                    <h3 class="h2 fw-bold mb-1">
                        {{ number_format($stats['average_rating'] ?? 0, 1) }}
                        <small class="fs-6 text-muted">/5</small>
                    </h3>
                    <p class="text-muted mb-0 small">Note moyenne</p>
                    <div class="mt-3">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= ($stats['average_rating'] ?? 0) ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                        @endfor
                    </div>
                    <small class="text-muted">{{ $stats['total_reviews'] ?? 0 }} avis reçus</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Colonne principale (8 colonnes) -->
        <div class="col-12 col-xl-8">
            <!-- Prochains cours en direct -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-video text-danger me-2"></i>
                            Prochains cours en direct
                        </h5>
                        <a href="{{ route('teacher.schedule') }}" class="btn btn-sm btn-outline-primary">
                            Voir l'agenda complet <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($upcomingClasses ?? [] as $class)
                    <div class="d-flex align-items-center p-3 border-bottom hover-bg-light">
                        <div class="me-3">
                            <div class="text-center bg-primary bg-opacity-10 rounded p-2" style="min-width: 70px;">
                                <div class="fw-bold text-primary">{{ \Carbon\Carbon::parse($class->scheduled_at)->format('d') }}</div>
                                <small class="text-muted text-uppercase">{{ \Carbon\Carbon::parse($class->scheduled_at)->format('M') }}</small>
                            </div>
                        </div>
                        
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">{{ $class->formation->name ?? 'Formation' }}</h6>
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span>
                                    <i class="far fa-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($class->scheduled_at)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($class->scheduled_at)->addHours($class->duration_hours ?? 2)->format('H:i') }}
                                </span>
                                <span>
                                    <i class="fas fa-users me-1"></i>
                                    {{ $class->enrolled_count ?? 0 }} / {{ $class->max_students ?? 30 }} étudiants
                                </span>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $class->type ?? 'Cours en ligne' }}
                                </span>
                            </div>
                        </div>

                        <div class="ms-auto">
                            @if(\Carbon\Carbon::parse($class->scheduled_at)->diffInMinutes(now(), false) >= -15 && \Carbon\Carbon::parse($class->scheduled_at)->diffInMinutes(now(), false) <= 30)
                            <a href="{{ $class->zoom_link ?? '#' }}" 
                               class="btn btn-danger btn-sm pulse-animation" 
                               target="_blank">
                                <i class="fas fa-video me-1"></i>Démarrer
                            </a>
                            @else
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="far fa-clock me-1"></i>
                                Dans {{ \Carbon\Carbon::parse($class->scheduled_at)->diffForHumans() }}
                            </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Aucun cours prévu prochainement</p>
                        <a href="{{ route('teacher.schedule') }}" class="btn btn-sm btn-primary mt-3">
                            <i class="fas fa-plus me-1"></i>Planifier un cours
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Mes formations -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                            Mes formations
                        </h5>
                        <a href="{{ route('teacher.courses.index') }}" class="btn btn-sm btn-outline-primary">
                            Gérer toutes <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($courses ?? [] as $course)
                        <div class="col-12 col-md-6">
                            <div class="card border hover-lift h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="fw-semibold mb-0">{{ $course->name }}</h6>
                                        <span class="badge bg-{{ $course->status === 'published' ? 'success' : ($course->status === 'draft' ? 'warning' : 'secondary') }}-subtle 
                                                     text-{{ $course->status === 'published' ? 'success' : ($course->status === 'draft' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($course->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-light rounded">
                                                <div class="fw-bold text-primary">{{ $course->enrolled_count ?? 0 }}</div>
                                                <small class="text-muted">Inscrits</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-light rounded">
                                                <div class="fw-bold text-warning">
                                                    {{ number_format($course->average_rating ?? 0, 1) }}
                                                    <i class="fas fa-star small"></i>
                                                </div>
                                                <small class="text-muted">Note</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('teacher.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                            <i class="fas fa-edit me-1"></i>Modifier
                                        </a>
                                        <a href="{{ route('teacher.courses.students', $course) }}" class="btn btn-sm btn-outline-secondary flex-grow-1">
                                            <i class="fas fa-users me-1"></i>Étudiants
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Vous n'avez pas encore créé de formation</p>
                                <a href="{{ route('teacher.courses.create') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus me-1"></i>Créer ma première formation
                                </a>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Activités récentes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-bell text-info me-2"></i>
                        Activités récentes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentActivities ?? [] as $activity)
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <div class="rounded-circle bg-{{ $activity->type === 'enrollment' ? 'primary' : ($activity->type === 'question' ? 'info' : 'success') }} bg-opacity-10 p-2">
                                        <i class="fas fa-{{ $activity->type === 'enrollment' ? 'user-plus' : ($activity->type === 'question' ? 'question-circle' : 'check-circle') }} 
                                           text-{{ $activity->type === 'enrollment' ? 'primary' : ($activity->type === 'question' ? 'info' : 'success') }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1">
                                        <strong>{{ $activity->user->full_name ?? 'Étudiant' }}</strong>
                                        {{ $activity->description ?? 's\'est inscrit à votre formation' }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                                    </small>
                                </div>
                                @if($activity->type === 'question')
                                <a href="{{ route('teacher.messages.show', $activity->id) }}" class="btn btn-sm btn-outline-primary">
                                    Répondre
                                </a>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Aucune activité récente</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale (4 colonnes) -->
        <div class="col-12 col-xl-4">
            <!-- Actions rapides -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-bolt me-2"></i>Actions rapides
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route('teacher.courses.create') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-plus-circle me-2"></i>Créer une nouvelle formation
                        </a>
                        <a href="{{ route('teacher.schedule.create') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-calendar-plus me-2"></i>Planifier un cours
                        </a>
                        <a href="{{ route('teacher.resources.upload') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-upload me-2"></i>Ajouter des ressources
                        </a>
                        <a href="{{ route('teacher.messages.index') }}" class="btn btn-outline-primary text-start position-relative">
                            <i class="fas fa-envelope me-2"></i>Voir les messages
                            @if(($unreadMessages ?? 0) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $unreadMessages }}
                            </span>
                            @endif
                        </a>
                        <a href="{{ route('teacher.students.index') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-users me-2"></i>Gérer mes étudiants
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques mensuelles -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chart-line text-success me-2"></i>
                        Statistiques du mois
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Nouvelles inscriptions</span>
                            <span class="fw-bold text-primary">{{ $stats['new_enrollments_this_month'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ min(($stats['new_enrollments_this_month'] ?? 0) / 50 * 100, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Heures de cours données</span>
                            <span class="fw-bold text-info">{{ $stats['hours_taught_this_month'] ?? 0 }}h</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ min(($stats['hours_taught_this_month'] ?? 0) / 100 * 100, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Taux de satisfaction</span>
                            <span class="fw-bold text-success">{{ $stats['satisfaction_rate'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $stats['satisfaction_rate'] ?? 0 }}%">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Questions répondues</span>
                            <span class="fw-bold text-warning">{{ $stats['questions_answered'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ min(($stats['questions_answered'] ?? 0) / 30 * 100, 100) }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        Performance
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Graphique circulaire progression -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <svg width="150" height="150" class="progress-ring">
                                <circle cx="75" cy="75" r="65" stroke="#e9ecef" stroke-width="10" fill="none"/>
                                <circle cx="75" cy="75" r="65" stroke="#800020" stroke-width="10" fill="none"
                                        stroke-dasharray="{{ 2 * 3.14159 * 65 }}"
                                        stroke-dashoffset="{{ 2 * 3.14159 * 65 * (1 - (($stats['completion_rate'] ?? 85) / 100)) }}"
                                        stroke-linecap="round"
                                        transform="rotate(-90 75 75)"
                                        style="transition: stroke-dashoffset 1s ease"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <div class="h2 fw-bold mb-0 text-primary">{{ $stats['completion_rate'] ?? 85 }}%</div>
                                <small class="text-muted">Taux de complétion</small>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">
                                <i class="fas fa-graduation-cap me-1"></i>Étudiants réussis
                            </span>
                            <span class="fw-bold">{{ $stats['graduated_students'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">
                                <i class="fas fa-certificate me-1"></i>Certificats délivrés
                            </span>
                            <span class="fw-bold">{{ $stats['certificates_issued'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="fas fa-medal me-1"></i>Classement
                            </span>
                            <span class="badge bg-warning-subtle text-warning">Top {{ $stats['ranking'] ?? 10 }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prochains paiements -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-money-bill-wave text-success me-2"></i>
                        Paiements
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success border-0 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
                            <div>
                                <div class="fw-bold">Prochain paiement</div>
                                <small>{{ number_format($stats['next_payout'] ?? 0, 0, ',', ' ') }} FCFA</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Date estimée</span>
                        <span class="fw-semibold">{{ \Carbon\Carbon::now()->endOfMonth()->format('d/m/Y') }}</span>
                    </div>

                    <a href="{{ route('teacher.earnings.index') }}" class="btn btn-outline-success w-100">
                        <i class="fas fa-chart-bar me-2"></i>Voir mes revenus
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Animations et effets personnalisés */
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.hover-bg-light:hover {
    background-color: rgba(128, 0, 32, 0.05);
    transition: background-color 0.2s ease;
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #800020 0%, #5a0016 100%);
}

.icon-box {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-ring circle {
    transition: stroke-dashoffset 1s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .icon-box {
        width: 50px;
        height: 50px;
    }
    
    .icon-box i {
        font-size: 1.2rem !important;
    }
}
</style>

<script>
// Animation des compteurs au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Animation du cercle de progression
    const progressRing = document.querySelector('.progress-ring circle:last-child');
    if (progressRing) {
        const circumference = 2 * Math.PI * 65;
        progressRing.style.strokeDasharray = circumference;
        progressRing.style.strokeDashoffset = circumference;
        
        setTimeout(() => {
            const completionRate = {{ $stats['completion_rate'] ?? 85 }};
            const offset = circumference - (completionRate / 100) * circumference;
            progressRing.style.strokeDashoffset = offset;
        }, 200);
    }

    // Animation des barres de progression
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
            bar.style.transition = 'width 1s ease';
        }, 100 * (index + 1));
    });
});

// Actualisation automatique toutes les 5 minutes
setInterval(() => {
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 300000);
</script>
@endsection