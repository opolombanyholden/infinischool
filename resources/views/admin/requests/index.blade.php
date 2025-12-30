@extends('layouts.dashboard')

@section('title', 'Demandes en Attente')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-tasks text-warning me-2"></i>
                    Demandes en Attente
                </h1>
                <p class="text-muted mb-0">Validations en attente de traitement</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                    <div class="card-body text-center py-4">
                        <h2 class="fw-bold text-warning mb-0">{{ $stats['total'] }}</h2>
                        <p class="text-muted mb-0">Total en attente</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <h2 class="fw-bold text-info mb-0">{{ $stats['teachers'] }}</h2>
                        <p class="text-muted mb-0">Inscriptions enseignants</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <h2 class="fw-bold text-primary mb-0">{{ $stats['formations'] }}</h2>
                        <p class="text-muted mb-0">Formations à valider</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list text-secondary me-2"></i>
                    Liste des demandes
                </h5>
            </div>
            <div class="card-body p-0">
                @if($requests->count() > 0)
                    @foreach($requests as $request)
                        <div class="border-bottom p-4">
                            <div class="row align-items-center">
                                <div class="col-md-1">
                                    <div class="rounded-circle bg-{{ $request['type'] === 'teacher_registration' ? 'info' : 'primary' }} bg-opacity-10 
                                                            d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 50px; height: 50px;">
                                        <i
                                            class="fas fa-{{ $request['type'] === 'teacher_registration' ? 'user-tie' : 'book' }} 
                                                               text-{{ $request['type'] === 'teacher_registration' ? 'info' : 'primary' }}"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-1">{{ $request['title'] }}</h6>
                                    <p class="text-muted mb-1 small">{{ $request['description'] }}</p>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($request['user'])
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $request['user']->name ?? 'N/A' }}
                                            </span>
                                        @endif
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $request['date']->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    @if($request['type'] === 'teacher_registration')
                                        <span class="badge bg-info-subtle text-info">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>Enseignant
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="fas fa-graduation-cap me-1"></i>Formation
                                        </span>
                                    @endif
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.requests.show', $request['id']) }}"
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                        <form action="{{ route('admin.requests.approve', $request['id']) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check me-1"></i>Approuver
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $loop->index }}">
                                            <i class="fas fa-times me-1"></i>Rejeter
                                        </button>
                                    </div>

                                    <!-- Modal de rejet -->
                                    <div class="modal fade" id="rejectModal{{ $loop->index }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.requests.reject', $request['id']) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Rejeter la demande</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p>Êtes-vous sûr de vouloir rejeter cette demande ?</p>
                                                        <div class="mb-3">
                                                            <label for="reason" class="form-label">Raison (optionnel)</label>
                                                            <textarea class="form-control" name="reason" rows="3"
                                                                placeholder="Expliquez la raison du rejet..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-double fa-3x mb-3 text-success"></i>
                        <h5>Aucune demande en attente</h5>
                        <p class="mb-0">Toutes les demandes ont été traitées</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection