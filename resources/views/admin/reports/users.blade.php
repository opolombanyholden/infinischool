@extends('layouts.dashboard')

@section('title', 'Rapport Utilisateurs')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.reports.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i>Retour aux rapports
                </a>
                <h1 class="h3 mb-1 fw-bold mt-2">
                    <i class="fas fa-users text-primary me-2"></i>
                    Rapport Utilisateurs
                </h1>
                <p class="text-muted mb-0">Statistiques des inscriptions et utilisateurs</p>
            </div>
            <form method="GET" class="d-flex gap-2">
                <select name="period" class="form-select" onchange="this.form.submit()">
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>7 derniers jours</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>30 derniers jours</option>
                    <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>3 derniers mois</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Cette année</option>
                </select>
            </form>
        </div>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-primary">{{ $stats['total'] }}</h2>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-success">{{ $stats['new'] }}</h2>
                        <small class="text-muted">Nouveaux</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-info">{{ $stats['students'] }}</h2>
                        <small class="text-muted">Étudiants</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-warning">{{ $stats['teachers'] }}</h2>
                        <small class="text-muted">Enseignants</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-danger">{{ $stats['admins'] }}</h2>
                        <small class="text-muted">Admins</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Daily Signups -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Inscriptions quotidiennes
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($dailySignups->count() > 0)
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-center">Inscriptions</th>
                                            <th style="width: 60%;">Progression</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $maxDaily = $dailySignups->max('count'); @endphp
                                        @foreach($dailySignups as $day)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</td>
                                                <td class="text-center fw-bold">{{ $day->count }}</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-primary"
                                                            style="width: {{ $maxDaily > 0 ? ($day->count / $maxDaily) * 100 : 0 }}%">
                                                            {{ $day->count }}
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                <p>Aucune inscription sur cette période</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- By Role -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-pie-chart text-info me-2"></i>
                            Répartition par rôle
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($byRole as $role)
                            @php
                                $color = match ($role->role) {
                                    'student' => 'primary',
                                    'teacher' => 'success',
                                    'admin' => 'danger',
                                    default => 'secondary'
                                };
                                $icon = match ($role->role) {
                                    'student' => 'user-graduate',
                                    'teacher' => 'chalkboard-teacher',
                                    'admin' => 'user-shield',
                                    default => 'user'
                                };
                            @endphp
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="rounded-circle bg-{{ $color }} bg-opacity-10 me-3 d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-{{ $icon }} text-{{ $color }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold">{{ ucfirst($role->role ?? 'Autre') }}</h6>
                                    <small class="text-muted">{{ $role->count }} utilisateurs</small>
                                </div>
                                <span class="badge bg-{{ $color }}">
                                    {{ $stats['total'] > 0 ? round(($role->count / $stats['total']) * 100) : 0 }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-user-plus text-success me-2"></i>
                    Derniers inscrits
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date d'inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;">
                                                <i class="fas fa-user text-secondary small"></i>
                                            </div>
                                            <span class="fw-semibold">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $user->email }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'teacher' ? 'success' : 'primary') }}-subtle 
                                                              text-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'teacher' ? 'success' : 'primary') }}">
                                            {{ ucfirst($user->role ?? 'student') }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection