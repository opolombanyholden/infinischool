@extends('layouts.dashboard')

@section('title', 'Mon planning - InfiniSchool')
@section('description', 'Consultez votre calendrier de cours et rejoignez vos sessions en direct')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mon planning</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-2 fw-bold">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        Mon planning de cours
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-clock me-1"></i>
                        {{ $stats['upcoming_classes'] ?? 0 }} cours à venir • 
                        {{ $stats['today_classes'] ?? 0 }} aujourd'hui
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#syncModal">
                        <i class="fas fa-sync-alt me-2"></i>Synchroniser calendrier
                    </button>
                    <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                        <i class="fas fa-book me-2"></i>Mes formations
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation calendrier et vues -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <!-- Navigation temporelle -->
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm" id="prevPeriod">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm flex-grow-1" id="todayBtn">
                            <i class="far fa-calendar-check me-1"></i>Aujourd'hui
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="nextPeriod">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Période affichée -->
                <div class="col-12 col-md-4 text-center">
                    <h5 class="mb-0 fw-bold" id="currentPeriod">
                        {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('MMMM YYYY') }}
                    </h5>
                </div>

                <!-- Sélecteur de vue -->
                <div class="col-12 col-md-4">
                    <div class="btn-group w-100" role="group" aria-label="Vue calendrier">
                        <button type="button" class="btn btn-outline-primary" id="weekViewBtn">
                            <i class="fas fa-calendar-week me-1"></i>Semaine
                        </button>
                        <button type="button" class="btn btn-outline-primary active" id="monthViewBtn">
                            <i class="fas fa-calendar me-1"></i>Mois
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="listViewBtn">
                            <i class="fas fa-list me-1"></i>Liste
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtre formations -->
            <div class="mt-3">
                <select class="form-select form-select-sm" id="formationFilter" style="max-width: 300px;">
                    <option value="">Toutes les formations</option>
                    @foreach($enrolledFormations ?? [] as $formation)
                    <option value="{{ $formation->id }}">{{ $formation->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Colonne principale - Calendrier -->
        <div class="col-12 col-xl-8">
            <!-- Vue Mois (par défaut) -->
            <div id="monthView" class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 calendar-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center py-3">Lun</th>
                                    <th class="text-center py-3">Mar</th>
                                    <th class="text-center py-3">Mer</th>
                                    <th class="text-center py-3">Jeu</th>
                                    <th class="text-center py-3">Ven</th>
                                    <th class="text-center py-3">Sam</th>
                                    <th class="text-center py-3">Dim</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $today = \Carbon\Carbon::now();
                                    $startOfMonth = $today->copy()->startOfMonth();
                                    $endOfMonth = $today->copy()->endOfMonth();
                                    $startDate = $startOfMonth->copy()->startOfWeek();
                                    $endDate = $endOfMonth->copy()->endOfWeek();
                                    $currentDate = $startDate->copy();
                                @endphp

                                @while($currentDate <= $endDate)
                                    <tr>
                                        @for($i = 0; $i < 7; $i++)
                                            @php
                                                $isCurrentMonth = $currentDate->month === $today->month;
                                                $isToday = $currentDate->isToday();
                                                $dayClasses = collect($classes ?? [])->filter(function($class) use ($currentDate) {
                                                    return \Carbon\Carbon::parse($class->scheduled_at)->isSameDay($currentDate);
                                                });
                                            @endphp
                                            <td class="calendar-day {{ !$isCurrentMonth ? 'text-muted bg-light' : '' }} {{ $isToday ? 'today' : '' }}" 
                                                data-date="{{ $currentDate->format('Y-m-d') }}">
                                                <div class="day-header">
                                                    <span class="day-number {{ $isToday ? 'today-badge' : '' }}">
                                                        {{ $currentDate->day }}
                                                    </span>
                                                </div>
                                                <div class="day-events">
                                                    @foreach($dayClasses as $class)
                                                        <div class="event-item" 
                                                             data-formation="{{ $class->formation_id }}"
                                                             style="border-left: 3px solid {{ $class->formation->color ?? '#800020' }};">
                                                            <div class="event-time">{{ \Carbon\Carbon::parse($class->scheduled_at)->format('H:i') }}</div>
                                                            <div class="event-title">{{ Str::limit($class->formation->name ?? 'Cours', 20) }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            @php $currentDate->addDay(); @endphp
                                        @endfor
                                    </tr>
                                @endwhile
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Vue Semaine -->
            <div id="weekView" class="card border-0 shadow-sm d-none">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 week-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Heure</th>
                                    @for($i = 0; $i < 7; $i++)
                                        @php
                                            $day = \Carbon\Carbon::now()->startOfWeek()->addDays($i);
                                        @endphp
                                        <th class="text-center">
                                            <div>{{ $day->locale('fr')->isoFormat('ddd') }}</div>
                                            <div class="fw-normal small">{{ $day->format('d/m') }}</div>
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @for($hour = 8; $hour <= 20; $hour++)
                                <tr>
                                    <td class="text-center text-muted small bg-light">
                                        {{ sprintf('%02d:00', $hour) }}
                                    </td>
                                    @for($i = 0; $i < 7; $i++)
                                        @php
                                            $day = \Carbon\Carbon::now()->startOfWeek()->addDays($i);
                                            $hourClasses = collect($classes ?? [])->filter(function($class) use ($day, $hour) {
                                                $classDate = \Carbon\Carbon::parse($class->scheduled_at);
                                                return $classDate->isSameDay($day) && $classDate->hour === $hour;
                                            });
                                        @endphp
                                        <td class="week-cell" data-date="{{ $day->format('Y-m-d') }}" data-hour="{{ $hour }}">
                                            @foreach($hourClasses as $class)
                                                <div class="week-event" 
                                                     data-formation="{{ $class->formation_id }}"
                                                     style="background: {{ $class->formation->color ?? '#800020' }}15; border-left: 3px solid {{ $class->formation->color ?? '#800020' }};">
                                                    <strong>{{ \Carbon\Carbon::parse($class->scheduled_at)->format('H:i') }}</strong>
                                                    <div>{{ $class->formation->name ?? 'Cours' }}</div>
                                                    @if($class->zoom_link)
                                                        <a href="{{ $class->zoom_link }}" class="btn btn-sm btn-danger mt-1" target="_blank">
                                                            <i class="fas fa-video"></i> Rejoindre
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                    @endfor
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Vue Liste -->
            <div id="listView" class="d-none">
                @forelse($classes ?? [] as $class)
                    @php
                        $classDate = \Carbon\Carbon::parse($class->scheduled_at);
                        $isToday = $classDate->isToday();
                        $isTomorrow = $classDate->isTomorrow();
                        $isPast = $classDate->isPast();
                        $canJoin = $classDate->diffInMinutes(now(), false) >= -15 && $classDate->diffInMinutes(now(), false) <= 30;
                    @endphp
                    
                    @if($loop->first || !$classDate->isSameDay(\Carbon\Carbon::parse($classes[$loop->index - 1]->scheduled_at)))
                        @if(!$loop->first)
                            </div></div>
                        @endif
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light border-0 py-2">
                                <h6 class="mb-0 fw-bold">
                                    @if($isToday)
                                        <i class="fas fa-star text-warning me-2"></i>Aujourd'hui
                                    @elseif($isTomorrow)
                                        Demain
                                    @else
                                        {{ $classDate->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                                    @endif
                                </h6>
                            </div>
                            <div class="card-body p-0">
                    @endif

                    <div class="list-group-item border-0 py-3 {{ $isPast ? 'opacity-50' : '' }}" 
                         data-formation="{{ $class->formation_id }}">
                        <div class="d-flex align-items-start">
                            <!-- Heure -->
                            <div class="me-3 text-center" style="min-width: 80px;">
                                <div class="fw-bold text-primary" style="font-size: 1.25rem;">
                                    {{ $classDate->format('H:i') }}
                                </div>
                                <small class="text-muted">
                                    {{ $class->duration_hours ?? 2 }}h
                                </small>
                            </div>

                            <!-- Séparateur vertical -->
                            <div class="border-start border-3 me-3" style="border-color: {{ $class->formation->color ?? '#800020' }} !important; margin-top: 5px; margin-bottom: 5px;"></div>

                            <!-- Détails cours -->
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">{{ $class->formation->name ?? 'Formation' }}</h6>
                                        <div class="text-muted small">
                                            <i class="fas fa-user me-1"></i>{{ $class->formation->teacher->full_name ?? 'Enseignant' }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $class->type ?? 'En ligne' }}
                                        </div>
                                    </div>
                                    
                                    @if(!$isPast)
                                        @if($canJoin)
                                            <a href="{{ $class->zoom_link ?? '#' }}" 
                                               class="btn btn-danger pulse-animation" 
                                               target="_blank">
                                                <i class="fas fa-video me-2"></i>Rejoindre maintenant
                                            </a>
                                        @else
                                            <span class="badge bg-info-subtle text-info">
                                                <i class="far fa-clock me-1"></i>
                                                Dans {{ $classDate->diffForHumans() }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-check me-1"></i>Terminé
                                        </span>
                                    @endif
                                </div>

                                @if($class->description)
                                <p class="text-muted small mb-2">{{ $class->description }}</p>
                                @endif

                                <!-- Infos complémentaires -->
                                <div class="d-flex gap-3 text-muted small">
                                    <span><i class="fas fa-users me-1"></i>{{ $class->enrolled_count ?? 0 }} participants</span>
                                    @if($class->resources_count > 0)
                                        <span><i class="fas fa-file me-1"></i>{{ $class->resources_count }} ressources</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($loop->last)
                        </div></div>
                    @endif
                @empty
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="far fa-calendar-times fa-4x text-muted mb-3"></i>
                            <h5 class="mb-2">Aucun cours programmé</h5>
                            <p class="text-muted mb-3">Vous n'avez pas de cours prévu pour le moment.</p>
                            <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Découvrir nos formations
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-xl-4">
            <!-- Prochains cours -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-clock me-2"></i>Prochains cours
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($upcomingClasses ?? [] as $class)
                        @php
                            $classDate = \Carbon\Carbon::parse($class->scheduled_at);
                            $canJoin = $classDate->diffInMinutes(now(), false) >= -15 && $classDate->diffInMinutes(now(), false) <= 30;
                        @endphp
                        <div class="p-3 border-bottom">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded text-center p-2" style="min-width: 60px;">
                                        <div class="fw-bold">{{ $classDate->format('d') }}</div>
                                        <small class="text-uppercase">{{ $classDate->format('M') }}</small>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold small">{{ $class->formation->name ?? 'Formation' }}</h6>
                                    <div class="text-muted small mb-2">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $classDate->format('H:i') }} • {{ $class->duration_hours ?? 2 }}h
                                    </div>
                                    @if($canJoin)
                                        <a href="{{ $class->zoom_link ?? '#' }}" class="btn btn-sm btn-danger w-100" target="_blank">
                                            <i class="fas fa-video me-1"></i>Rejoindre
                                        </a>
                                    @else
                                        <small class="text-muted">
                                            <i class="fas fa-hourglass-half me-1"></i>
                                            {{ $classDate->diffForHumans() }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="far fa-calendar-check fa-2x mb-2"></i>
                            <p class="mb-0 small">Aucun cours à venir</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Légende -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-palette me-2"></i>Légende
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($enrolledFormations ?? [] as $formation)
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-2" style="width: 20px; height: 20px; background: {{ $formation->color ?? '#800020' }}; border-radius: 4px;"></div>
                            <small>{{ $formation->name }}</small>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Stats rapides -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h4 class="fw-bold text-primary mb-1">{{ $stats['total_hours'] ?? 0 }}</h4>
                                <small class="text-muted">Heures ce mois</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h4 class="fw-bold text-success mb-1">{{ $stats['attended_classes'] ?? 0 }}</h4>
                                <small class="text-muted">Cours suivis</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Synchronisation calendrier -->
<div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="syncModalLabel">
                    <i class="fas fa-sync-alt me-2"></i>Synchroniser mon calendrier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Synchronisez vos cours avec votre application de calendrier préférée.</p>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('student.schedule.ical') }}" class="btn btn-outline-primary text-start">
                        <i class="fab fa-google me-2"></i>Google Calendar
                    </a>
                    <a href="{{ route('student.schedule.ical') }}" class="btn btn-outline-primary text-start">
                        <i class="fab fa-apple me-2"></i>Apple Calendar (iCal)
                    </a>
                    <a href="{{ route('student.schedule.ical') }}" class="btn btn-outline-primary text-start">
                        <i class="fab fa-microsoft me-2"></i>Outlook
                    </a>
                </div>

                <hr class="my-3">

                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>
                        Le lien de synchronisation se mettra à jour automatiquement avec vos nouveaux cours.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Calendrier mois */
.calendar-table {
    font-size: 0.875rem;
}

.calendar-day {
    height: 120px;
    vertical-align: top;
    position: relative;
    padding: 8px;
}

.calendar-day.today {
    background: rgba(128, 0, 32, 0.05);
}

.day-header {
    margin-bottom: 5px;
}

.day-number {
    display: inline-block;
    width: 28px;
    height: 28px;
    line-height: 28px;
    text-align: center;
    font-weight: 600;
}

.today-badge {
    background: #800020;
    color: white;
    border-radius: 50%;
}

.day-events {
    overflow-y: auto;
    max-height: 80px;
}

.event-item {
    padding: 4px 6px;
    margin-bottom: 3px;
    background: rgba(128, 0, 32, 0.1);
    border-radius: 3px;
    cursor: pointer;
    transition: transform 0.2s;
}

.event-item:hover {
    transform: translateX(3px);
    background: rgba(128, 0, 32, 0.15);
}

.event-time {
    font-size: 0.7rem;
    font-weight: 600;
    color: #800020;
}

.event-title {
    font-size: 0.75rem;
    line-height: 1.2;
}

/* Calendrier semaine */
.week-table {
    font-size: 0.875rem;
}

.week-cell {
    height: 60px;
    vertical-align: top;
    position: relative;
    padding: 5px;
}

.week-event {
    padding: 5px;
    border-radius: 4px;
    margin-bottom: 3px;
    font-size: 0.75rem;
}

/* Animations */
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

/* Responsive */
@media (max-width: 768px) {
    .calendar-day {
        height: 80px;
        padding: 5px;
    }
    
    .day-events {
        max-height: 50px;
    }
    
    .event-title {
        font-size: 0.65rem;
    }
    
    .week-table {
        font-size: 0.75rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle entre les vues
    const weekViewBtn = document.getElementById('weekViewBtn');
    const monthViewBtn = document.getElementById('monthViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const weekView = document.getElementById('weekView');
    const monthView = document.getElementById('monthView');
    const listView = document.getElementById('listView');

    weekViewBtn?.addEventListener('click', function() {
        showView('week');
    });

    monthViewBtn?.addEventListener('click', function() {
        showView('month');
    });

    listViewBtn?.addEventListener('click', function() {
        showView('list');
    });

    function showView(view) {
        // Masquer toutes les vues
        [weekView, monthView, listView].forEach(v => v?.classList.add('d-none'));
        [weekViewBtn, monthViewBtn, listViewBtn].forEach(btn => btn?.classList.remove('active'));

        // Afficher la vue sélectionnée
        if (view === 'week') {
            weekView?.classList.remove('d-none');
            weekViewBtn?.classList.add('active');
        } else if (view === 'month') {
            monthView?.classList.remove('d-none');
            monthViewBtn?.classList.add('active');
        } else {
            listView?.classList.remove('d-none');
            listViewBtn?.classList.add('active');
        }

        localStorage.setItem('scheduleView', view);
    }

    // Restaurer la vue préférée
    const savedView = localStorage.getItem('scheduleView');
    if (savedView) {
        showView(savedView);
    }

    // Filtre par formation
    const formationFilter = document.getElementById('formationFilter');
    formationFilter?.addEventListener('change', function() {
        const selectedFormation = this.value;
        const allEvents = document.querySelectorAll('[data-formation]');

        allEvents.forEach(event => {
            if (!selectedFormation || event.dataset.formation === selectedFormation) {
                event.style.display = '';
            } else {
                event.style.display = 'none';
            }
        });
    });

    // Navigation temporelle (simulation - à implémenter côté serveur)
    const prevBtn = document.getElementById('prevPeriod');
    const nextBtn = document.getElementById('nextPeriod');
    const todayBtn = document.getElementById('todayBtn');

    prevBtn?.addEventListener('click', () => {
        // Logique pour charger la période précédente
        console.log('Période précédente');
    });

    nextBtn?.addEventListener('click', () => {
        // Logique pour charger la période suivante
        console.log('Période suivante');
    });

    todayBtn?.addEventListener('click', () => {
        // Recharger avec la date du jour
        window.location.href = '{{ route("student.schedule.index") }}';
    });

    // Clic sur événement du calendrier
    const eventItems = document.querySelectorAll('.event-item, .week-event');
    eventItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (!e.target.closest('a')) {
                // Afficher détails de l'événement (modal ou redirection)
                alert('Afficher détails du cours');
            }
        });
    });
});
</script>
@endsection