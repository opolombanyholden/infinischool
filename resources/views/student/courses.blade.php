@extends('layouts.dashboard')

@section('title', 'Mes formations - InfiniSchool')
@section('description', 'Consultez toutes vos formations inscrites et suivez votre progression')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mes formations</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-2 fw-bold">
                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                        Mes formations
                    </h1>
                    <p class="text-muted mb-0">
                        {{ $enrollments->total() ?? 0 }} formation(s) • 
                        {{ $stats['active_count'] ?? 0 }} en cours • 
                        {{ $stats['completed_count'] ?? 0 }} terminée(s)
                    </p>
                </div>
                <a href="{{ route('formations.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Découvrir d'autres formations
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('student.courses.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <!-- Recherche -->
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0" 
                                   name="search" 
                                   placeholder="Rechercher une formation..." 
                                   value="{{ request('search') }}"
                                   id="searchInput">
                        </div>
                    </div>

                    <!-- Filtre statut -->
                    <div class="col-12 col-md-3">
                        <select class="form-select" name="status" id="statusFilter">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                En cours
                            </option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                Terminées
                            </option>
                            <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>
                                En pause
                            </option>
                        </select>
                    </div>

                    <!-- Filtre catégorie -->
                    <div class="col-12 col-md-3">
                        <select class="form-select" name="category" id="categoryFilter">
                            <option value="">Toutes catégories</option>
                            @foreach($categories ?? [] as $category)
                            <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Boutons actions -->
                    <div class="col-12 col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter me-1"></i>Filtrer
                            </button>
                            <a href="{{ route('student.courses.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Vue d'affichage (grille/liste) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="btn-group btn-group-sm" role="group" aria-label="Vue d'affichage">
            <button type="button" class="btn btn-outline-secondary active" id="gridViewBtn">
                <i class="fas fa-th"></i> Grille
            </button>
            <button type="button" class="btn btn-outline-secondary" id="listViewBtn">
                <i class="fas fa-list"></i> Liste
            </button>
        </div>

        <div class="text-muted small">
            Tri : 
            <select class="form-select form-select-sm d-inline-block w-auto" id="sortSelect">
                <option value="recent">Plus récent</option>
                <option value="name">Nom (A-Z)</option>
                <option value="progress">Progression</option>
            </select>
        </div>
    </div>

    <!-- Liste des formations (Vue grille) -->
    <div id="gridView" class="row g-4 mb-4">
        @forelse($enrollments ?? [] as $enrollment)
        <div class="col-12 col-md-6 col-xl-4 course-item" data-status="{{ $enrollment->status }}" data-progress="{{ $enrollment->progress }}">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <!-- Image -->
                <div class="position-relative">
                    <img src="{{ Storage::url($enrollment->formation->image) ?? 'https://via.placeholder.com/400x225/800020/ffffff?text=Formation' }}" 
                         class="card-img-top" 
                         alt="{{ $enrollment->formation->name }}"
                         style="height: 200px; object-fit: cover;">
                    
                    <!-- Badge statut -->
                    <span class="position-absolute top-0 end-0 m-3 badge bg-{{ $enrollment->status === 'completed' ? 'success' : ($enrollment->status === 'active' ? 'primary' : 'warning') }}">
                        @if($enrollment->status === 'completed')
                            <i class="fas fa-check-circle me-1"></i>Terminée
                        @elseif($enrollment->status === 'active')
                            <i class="fas fa-play-circle me-1"></i>En cours
                        @else
                            <i class="fas fa-pause-circle me-1"></i>En pause
                        @endif
                    </span>

                    <!-- Progression circulaire -->
                    <div class="position-absolute bottom-0 start-0 m-3">
                        <div class="progress-circle" data-progress="{{ $enrollment->progress }}">
                            <svg width="60" height="60">
                                <circle cx="30" cy="30" r="25" stroke="#ffffff" stroke-width="3" fill="none" opacity="0.3"/>
                                <circle cx="30" cy="30" r="25" stroke="#ffffff" stroke-width="3" fill="none"
                                        stroke-dasharray="{{ 2 * 3.14159 * 25 }}"
                                        stroke-dashoffset="{{ 2 * 3.14159 * 25 * (1 - $enrollment->progress / 100) }}"
                                        stroke-linecap="round"
                                        transform="rotate(-90 30 30)"
                                        class="progress-ring"/>
                            </svg>
                            <div class="progress-text">{{ $enrollment->progress }}%</div>
                        </div>
                    </div>
                </div>

                <div class="card-body d-flex flex-column">
                    <!-- Titre et catégorie -->
                    <div class="mb-3">
                        <span class="badge bg-primary-subtle text-primary mb-2">
                            {{ $enrollment->formation->category->name ?? 'Catégorie' }}
                        </span>
                        <h5 class="card-title fw-bold mb-2">
                            <a href="{{ route('student.courses.show', $enrollment) }}" class="text-decoration-none text-dark stretched-link">
                                {{ $enrollment->formation->name }}
                            </a>
                        </h5>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-user me-1"></i>{{ $enrollment->formation->teacher->full_name ?? 'Enseignant' }}
                        </p>
                    </div>

                    <!-- Stats -->
                    <div class="row g-2 mb-3 small text-muted">
                        <div class="col-6">
                            <i class="fas fa-clock me-1"></i>
                            {{ $enrollment->time_spent ?? 0 }}h passées
                        </div>
                        <div class="col-6">
                            <i class="fas fa-video me-1"></i>
                            {{ $enrollment->completed_lessons ?? 0 }}/{{ $enrollment->formation->total_lessons ?? 0 }} leçons
                        </div>
                    </div>

                    <!-- Barre progression -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Progression</small>
                            <small class="fw-semibold text-primary">{{ $enrollment->progress }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" 
                                 role="progressbar" 
                                 style="width: {{ $enrollment->progress }}%"
                                 aria-valuenow="{{ $enrollment->progress }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Prochain cours (si en cours) -->
                    @if($enrollment->status === 'active' && $enrollment->next_class)
                    <div class="alert alert-info border-0 mb-3 py-2 px-3 small">
                        <i class="fas fa-calendar-day me-1"></i>
                        <strong>Prochain cours :</strong><br>
                        {{ \Carbon\Carbon::parse($enrollment->next_class->scheduled_at)->locale('fr')->isoFormat('dddd D MMM à HH:mm') }}
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-auto">
                        @if($enrollment->status === 'completed')
                            <a href="{{ route('student.certificates.download', $enrollment) }}" class="btn btn-success w-100">
                                <i class="fas fa-certificate me-2"></i>Télécharger certificat
                            </a>
                        @elseif($enrollment->status === 'active')
                            <a href="{{ route('student.courses.continue', $enrollment) }}" class="btn btn-primary w-100">
                                <i class="fas fa-play me-2"></i>Continuer la formation
                            </a>
                        @else
                            <a href="{{ route('student.courses.resume', $enrollment) }}" class="btn btn-warning w-100">
                                <i class="fas fa-redo me-2"></i>Reprendre la formation
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-book-open fa-4x text-muted mb-4"></i>
                    <h4 class="mb-3">Aucune formation trouvée</h4>
                    <p class="text-muted mb-4">
                        @if(request()->has('search') || request()->has('status') || request()->has('category'))
                            Aucune formation ne correspond à vos critères de recherche.
                        @else
                            Vous n'êtes inscrit à aucune formation pour le moment.
                        @endif
                    </p>
                    <a href="{{ route('formations.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Découvrir nos formations
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Liste des formations (Vue liste) -->
    <div id="listView" class="d-none">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 300px;">Formation</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Progression</th>
                                <th class="text-center">Leçons</th>
                                <th class="text-center">Temps passé</th>
                                <th class="text-center">Dernière activité</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enrollments ?? [] as $enrollment)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ Storage::url($enrollment->formation->image) ?? 'https://via.placeholder.com/80x60/800020/ffffff?text=F' }}" 
                                             alt="{{ $enrollment->formation->name }}"
                                             class="rounded me-3"
                                             style="width: 80px; height: 60px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-1 fw-semibold">
                                                <a href="{{ route('student.courses.show', $enrollment) }}" class="text-decoration-none text-dark">
                                                    {{ $enrollment->formation->name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $enrollment->formation->teacher->full_name ?? 'Enseignant' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $enrollment->status === 'completed' ? 'success' : ($enrollment->status === 'active' ? 'primary' : 'warning') }}-subtle 
                                                 text-{{ $enrollment->status === 'completed' ? 'success' : ($enrollment->status === 'active' ? 'primary' : 'warning') }}">
                                        @if($enrollment->status === 'completed')
                                            Terminée
                                        @elseif($enrollment->status === 'active')
                                            En cours
                                        @else
                                            En pause
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px; max-width: 100px;">
                                            <div class="progress-bar bg-primary" 
                                                 role="progressbar" 
                                                 style="width: {{ $enrollment->progress }}%">
                                            </div>
                                        </div>
                                        <span class="fw-semibold small">{{ $enrollment->progress }}%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    {{ $enrollment->completed_lessons ?? 0 }}/{{ $enrollment->formation->total_lessons ?? 0 }}
                                </td>
                                <td class="text-center">
                                    {{ $enrollment->time_spent ?? 0 }}h
                                </td>
                                <td class="text-center text-muted small">
                                    {{ \Carbon\Carbon::parse($enrollment->last_activity_at)->diffForHumans() ?? 'Jamais' }}
                                </td>
                                <td class="text-center">
                                    @if($enrollment->status === 'completed')
                                        <a href="{{ route('student.certificates.download', $enrollment) }}" 
                                           class="btn btn-sm btn-success"
                                           title="Télécharger certificat">
                                            <i class="fas fa-certificate"></i>
                                        </a>
                                    @elseif($enrollment->status === 'active')
                                        <a href="{{ route('student.courses.continue', $enrollment) }}" 
                                           class="btn btn-sm btn-primary"
                                           title="Continuer">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('student.courses.resume', $enrollment) }}" 
                                           class="btn btn-sm btn-warning"
                                           title="Reprendre">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p class="mb-0">Aucune formation trouvée</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if(($enrollments ?? collect())->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $enrollments->links() }}
    </div>
    @endif
</div>

<style>
/* Animations et effets */
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

/* Cercle de progression sur image */
.progress-circle {
    position: relative;
    width: 60px;
    height: 60px;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-weight: bold;
    font-size: 14px;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
}

.progress-ring {
    transition: stroke-dashoffset 1s ease;
}

/* Stretched link pour card cliquable */
.stretched-link::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    content: "";
}

/* Boutons actions au-dessus du stretched link */
.card-body .btn {
    position: relative;
    z-index: 2;
}

/* Vue tableau responsive */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table td, .table th {
        padding: 0.5rem;
    }
}

/* Transitions filtres */
.course-item {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.course-item.hidden {
    opacity: 0;
    transform: scale(0.95);
    display: none;
}

/* Style pour select de tri */
#sortSelect {
    cursor: pointer;
    border: 1px solid #dee2e6;
    padding: 0.25rem 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle entre vue grille et liste
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');

    gridViewBtn?.addEventListener('click', function() {
        gridView.classList.remove('d-none');
        listView.classList.add('d-none');
        gridViewBtn.classList.add('active');
        listViewBtn.classList.remove('active');
        localStorage.setItem('coursesView', 'grid');
    });

    listViewBtn?.addEventListener('click', function() {
        gridView.classList.add('d-none');
        listView.classList.remove('d-none');
        listViewBtn.classList.add('active');
        gridViewBtn.classList.remove('active');
        localStorage.setItem('coursesView', 'list');
    });

    // Restaurer la vue préférée
    const savedView = localStorage.getItem('coursesView');
    if (savedView === 'list') {
        listViewBtn?.click();
    }

    // Tri dynamique
    const sortSelect = document.getElementById('sortSelect');
    sortSelect?.addEventListener('change', function() {
        const sortBy = this.value;
        const items = Array.from(document.querySelectorAll('.course-item'));
        
        items.sort((a, b) => {
            if (sortBy === 'name') {
                const nameA = a.querySelector('.card-title').textContent.trim();
                const nameB = b.querySelector('.card-title').textContent.trim();
                return nameA.localeCompare(nameB);
            } else if (sortBy === 'progress') {
                const progressA = parseInt(a.dataset.progress) || 0;
                const progressB = parseInt(b.dataset.progress) || 0;
                return progressB - progressA;
            } else {
                // Tri par défaut (recent) - ne rien faire
                return 0;
            }
        });

        const container = document.getElementById('gridView');
        items.forEach(item => container.appendChild(item));
    });

    // Recherche en temps réel (optionnel)
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    
    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Filtrer visuellement (optionnel, sinon submit le form)
            const searchTerm = this.value.toLowerCase();
            const items = document.querySelectorAll('.course-item');
            
            items.forEach(item => {
                const title = item.querySelector('.card-title').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }, 300);
    });

    // Animation barres de progression
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
            bar.style.transition = 'width 1s ease';
        }, 100 * (index + 1));
    });

    // Auto-submit des filtres au changement
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    
    [statusFilter, categoryFilter].forEach(filter => {
        filter?.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>
@endsection