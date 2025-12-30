@extends('layouts.dashboard')

@section('title', 'Journal d\'activités')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-stream text-info me-2"></i>
                    Journal d'activités
                </h1>
                <p class="text-muted mb-0">Suivi en temps réel des activités de la plateforme</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.activity.export', ['period' => $period]) }}" class="btn btn-outline-success">
                    <i class="fas fa-download me-2"></i>Exporter CSV
                </a>
                <button class="btn btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-2"></i>Actualiser
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-primary mb-0">{{ $stats['total'] }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-success mb-0">{{ $stats['today'] }}</h4>
                        <small class="text-muted">Aujourd'hui</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-info mb-0">{{ $stats['enrollments'] }}</h4>
                        <small class="text-muted">Inscriptions</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-success mb-0">{{ $stats['payments'] }}</h4>
                        <small class="text-muted">Paiements</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-primary mb-0">{{ $stats['registrations'] }}</h4>
                        <small class="text-muted">Inscrits</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-warning mb-0">{{ $stats['reviews'] }}</h4>
                        <small class="text-muted">Avis</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.activity.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="type" class="form-label small text-muted">Type d'activité</label>
                        <select name="type" id="type" class="form-select">
                            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Toutes les activités</option>
                            <option value="enrollment" {{ $type === 'enrollment' ? 'selected' : '' }}>Inscriptions
                                formations</option>
                            <option value="payment" {{ $type === 'payment' ? 'selected' : '' }}>Paiements</option>
                            <option value="registration" {{ $type === 'registration' ? 'selected' : '' }}>Nouveaux
                                utilisateurs</option>
                            <option value="review" {{ $type === 'review' ? 'selected' : '' }}>Avis</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="period" class="form-label small text-muted">Période</label>
                        <select name="period" id="period" class="form-select">
                            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                            <option value="week" {{ $period === 'week' ? 'selected' : '' }}>7 derniers jours</option>
                            <option value="month" {{ $period === 'month' ? 'selected' : '' }}>30 derniers jours</option>
                            <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>3 derniers mois</option>
                            <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Cette année</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-history text-secondary me-2"></i>
                    Activités récentes
                </h5>
            </div>
            <div class="card-body p-0">
                @if($activities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Type</th>
                                    <th>Utilisateur</th>
                                    <th>Action</th>
                                    <th>Détails</th>
                                    <th style="width: 180px;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $activity['color'] }}-subtle text-{{ $activity['color'] }}">
                                                <i class="fas fa-{{ $activity['icon'] }} me-1"></i>
                                                {{ ucfirst($activity['type']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($activity['user_avatar'])
                                                    <img src="{{ asset('storage/' . $activity['user_avatar']) }}" alt="Avatar"
                                                        class="rounded-circle me-2" width="32" height="32">
                                                @else
                                                    <div class="rounded-circle bg-secondary bg-opacity-25 me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px;">
                                                        <i class="fas fa-user text-secondary small"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold small">{{ $activity['user_name'] }}</div>
                                                    <small class="text-muted">{{ $activity['user_email'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">{{ $activity['action'] }}</td>
                                        <td class="align-middle text-muted small">{{ Str::limit($activity['details'] ?? '', 50) }}
                                        </td>
                                        <td class="align-middle">
                                            <div class="small">
                                                <i class="far fa-clock me-1 text-muted"></i>
                                                {{ $activity['date']->format('d/m/Y H:i') }}
                                            </div>
                                            <small class="text-muted">{{ $activity['date']->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($totalPages > 1)
                        <div class="card-footer bg-white border-0">
                            <nav aria-label="Pagination">
                                <ul class="pagination pagination-sm justify-content-center mb-0">
                                    @for($i = 1; $i <= $totalPages; $i++)
                                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                            <a class="page-link"
                                                href="{{ route('admin.activity.index', ['page' => $i, 'type' => $type, 'period' => $period]) }}">
                                                {{ $i }}
                                            </a>
                                        </li>
                                    @endfor
                                </ul>
                            </nav>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p class="mb-0">Aucune activité trouvée pour cette période</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .badge {
            font-weight: 500;
        }
    </style>
@endsection