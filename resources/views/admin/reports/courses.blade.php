@extends('layouts.dashboard')

@section('title', 'Rapport Formations')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.reports.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i>Retour aux rapports
                </a>
                <h1 class="h3 mb-1 fw-bold mt-2">
                    <i class="fas fa-book-open text-info me-2"></i>
                    Rapport Formations
                </h1>
                <p class="text-muted mb-0">Performance et statistiques des formations</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-info">{{ $stats['total'] }}</h2>
                        <small class="text-muted">Total formations</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-success">{{ $stats['published'] }}</h2>
                        <small class="text-muted">Publiées</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-warning">{{ $stats['pending'] }}</h2>
                        <small class="text-muted">En attente</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-secondary">{{ $stats['draft'] }}</h2>
                        <small class="text-muted">Brouillons</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Popular Formations -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-fire text-danger me-2"></i>
                            Formations les plus populaires
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($popularFormations->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Formation</th>
                                            <th class="text-center">Inscrits</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($popularFormations as $index => $formation)
                                            <tr>
                                                <td>
                                                    @if($index < 3)
                                                        <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'info') }}">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    @else
                                                        {{ $index + 1 }}
                                                    @endif
                                                </td>
                                                <td class="fw-semibold">{{ Str::limit($formation->name, 35) }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        {{ $formation->enrollments_count }} inscrits
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <p>Aucune formation</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Revenue by Formation -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-money-bill-wave text-success me-2"></i>
                            Revenus par formation
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($formationRevenue->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Formation</th>
                                            <th class="text-end">Revenus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($formationRevenue as $index => $formation)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="fw-semibold">{{ Str::limit($formation->name, 35) }}</td>
                                                <td class="text-end">
                                                    <span class="fw-bold text-success">
                                                        {{ number_format($formation->revenue ?? 0, 0, ',', ' ') }} FCFA
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <p>Aucune donnée</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- By Category -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-tags text-primary me-2"></i>
                    Formations par catégorie
                </h5>
            </div>
            <div class="card-body">
                @if($byCategory->count() > 0)
                    <div class="row">
                        @foreach($byCategory as $cat)
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="bg-light rounded p-3 text-center">
                                    <h4 class="fw-bold text-primary mb-0">{{ $cat->count }}</h4>
                                    <small class="text-muted">{{ $cat->category->name ?? 'Sans catégorie' }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <p>Aucune catégorie définie</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
