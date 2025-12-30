@extends('layouts.dashboard')

@section('title', 'Détail de la demande')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <a href="{{ route('admin.requests.index') }}" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i>Retour aux demandes
            </a>
            <h1 class="h3 mb-1 fw-bold mt-2">
                <i
                    class="fas fa-{{ $entityType === 'teacher' ? 'user-tie' : 'book' }} text-{{ $entityType === 'teacher' ? 'info' : 'primary' }} me-2"></i>
                {{ $entityType === 'teacher' ? 'Demande d\'inscription enseignant' : 'Validation de formation' }}
            </h1>
        </div>

        @if($entityType === 'teacher')
            <!-- Teacher Request Details -->
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Informations du candidat</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-3 text-center">
                                    <div class="rounded-circle bg-info bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                                        style="width: 100px; height: 100px;">
                                        @if($entity->avatar)
                                            <img src="{{ asset('storage/' . $entity->avatar) }}" class="rounded-circle"
                                                style="width: 100px; height: 100px; object-fit: cover;">
                                        @else
                                            <i class="fas fa-user fa-3x text-info"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <h4 class="fw-bold">{{ $entity->name }}</h4>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-envelope me-2"></i>{{ $entity->email }}
                                    </p>
                                    @if($entity->phone)
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-phone me-2"></i>{{ $entity->phone }}
                                        </p>
                                    @endif
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-calendar me-2"></i>Inscrit le
                                        {{ $entity->created_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>
                            </div>

                            @if($entity->bio)
                                <div class="mb-4">
                                    <h6 class="fw-bold">Biographie</h6>
                                    <p class="text-muted">{{ $entity->bio }}</p>
                                </div>
                            @endif

                            @if($entity->qualifications)
                                <div class="mb-4">
                                    <h6 class="fw-bold">Qualifications</h6>
                                    <p class="text-muted">{{ $entity->qualifications }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">Actions</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.requests.approve', 'teacher_' . $entity->id) }}" method="POST"
                                class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-2"></i>Approuver l'inscription
                                </button>
                            </form>
                            <form action="{{ route('admin.requests.reject', 'teacher_' . $entity->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control" name="reason" rows="3"
                                        placeholder="Raison du rejet (optionnel)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-times me-2"></i>Rejeter l'inscription
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Formation Request Details -->
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Détails de la formation</h5>
                        </div>
                        <div class="card-body">
                            @if($entity->image)
                                <img src="{{ asset('storage/' . $entity->image) }}" class="img-fluid rounded mb-4"
                                    style="max-height: 300px; width: 100%; object-fit: cover;">
                            @endif

                            <h4 class="fw-bold">{{ $entity->name }}</h4>

                            @if($entity->teacher)
                                <p class="text-muted mb-3">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>
                                    Par {{ $entity->teacher->name }}
                                </p>
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 text-center">
                                        <h5 class="fw-bold text-success mb-0">
                                            {{ number_format($entity->price ?? 0, 0, ',', ' ') }} FCFA</h5>
                                        <small class="text-muted">Prix</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 text-center">
                                        <h5 class="fw-bold text-primary mb-0">{{ $entity->duration ?? 'N/A' }}</h5>
                                        <small class="text-muted">Durée</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 text-center">
                                        <h5 class="fw-bold text-info mb-0">{{ ucfirst($entity->level ?? 'Tous') }}</h5>
                                        <small class="text-muted">Niveau</small>
                                    </div>
                                </div>
                            </div>

                            @if($entity->description)
                                <div class="mb-4">
                                    <h6 class="fw-bold">Description</h6>
                                    <p class="text-muted">{{ $entity->description }}</p>
                                </div>
                            @endif

                            @if($entity->objectives)
                                <div class="mb-4">
                                    <h6 class="fw-bold">Objectifs</h6>
                                    <p class="text-muted">{{ $entity->objectives }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">Enseignant</h6>
                        </div>
                        <div class="card-body text-center">
                            @if($entity->teacher)
                                <div class="rounded-circle bg-info bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                                    style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x text-info"></i>
                                </div>
                                <h6 class="fw-bold">{{ $entity->teacher->name }}</h6>
                                <p class="text-muted small mb-0">{{ $entity->teacher->email }}</p>
                            @else
                                <p class="text-muted">Aucun enseignant assigné</p>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">Actions</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.requests.approve', 'formation_' . $entity->id) }}" method="POST"
                                class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-2"></i>Valider et publier
                                </button>
                            </form>
                            <form action="{{ route('admin.requests.reject', 'formation_' . $entity->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control" name="reason" rows="3"
                                        placeholder="Raison du rejet (optionnel)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-times me-2"></i>Rejeter la formation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection