@extends('layouts.dashboard')

@section('title', 'Ticket #' . $ticket->id)

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.support.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i>Retour aux tickets
                </a>
                <h1 class="h3 mb-1 fw-bold mt-2">
                    <i class="fas fa-ticket-alt text-primary me-2"></i>
                    Ticket #{{ $ticket->id }}
                </h1>
                <p class="text-muted mb-0">{{ $ticket->subject }}</p>
            </div>
            <div class="d-flex gap-2">
                @if($ticket->status !== 'closed')
                    <form action="{{ route('admin.support.resolve', $ticket) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Marquer comme résolu
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Ticket Details -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Détails du ticket</h5>
                            <div>
                                @if($ticket->priority === 'high')
                                    <span class="badge bg-danger me-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Priorité Haute
                                    </span>
                                @elseif($ticket->priority === 'medium')
                                    <span class="badge bg-warning text-dark me-2">Priorité Moyenne</span>
                                @else
                                    <span class="badge bg-secondary me-2">Priorité Basse</span>
                                @endif

                                @if($ticket->status === 'open')
                                    <span class="badge bg-danger">Ouvert</span>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="badge bg-warning text-dark">En cours</span>
                                @else
                                    <span class="badge bg-success">Résolu</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 me-3 d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px;">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold mb-0">{{ $ticket->user->name ?? 'Utilisateur' }}</h6>
                                        <small class="text-muted">{{ $ticket->user->email ?? '' }}</small>
                                    </div>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $ticket->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light rounded p-4">
                            <h6 class="fw-bold mb-3">{{ $ticket->subject }}</h6>
                            <div class="text-dark" style="white-space: pre-wrap;">{{ $ticket->message }}</div>
                        </div>
                    </div>
                </div>

                <!-- Reply Form -->
                @if($ticket->status !== 'closed')
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-reply text-primary me-2"></i>
                                Répondre
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.support.reply', $ticket) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control" name="reply" rows="4" placeholder="Écrivez votre réponse..."
                                        required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Envoyer la réponse
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Assignment -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-user-cog text-secondary me-2"></i>
                            Assignation
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($ticket->assignedTo)
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-info bg-opacity-10 me-2 d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-shield text-info"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $ticket->assignedTo->name }}</div>
                                    <small class="text-muted">{{ $ticket->assignedTo->email }}</small>
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-3">Aucun admin assigné</p>
                        @endif

                        @if($ticket->status !== 'closed')
                            <form action="{{ route('admin.support.assign', $ticket) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <select name="admin_id" class="form-select" required>
                                        <option value="">Sélectionner un admin...</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}"
                                                {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
                                                {{ $admin->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-user-plus me-2"></i>Assigner
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Ticket Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-info-circle text-secondary me-2"></i>
                            Informations
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <small class="text-muted d-block">ID du ticket</small>
                                <span class="fw-semibold">#{{ $ticket->id }}</span>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Créé le</small>
                                <span>{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Dernière mise à jour</small>
                                <span>{{ $ticket->updated_at->format('d/m/Y à H:i') }}</span>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Priorité</small>
                                @if($ticket->priority === 'high')
                                    <span class="badge bg-danger">Haute</span>
                                @elseif($ticket->priority === 'medium')
                                    <span class="badge bg-warning text-dark">Moyenne</span>
                                @else
                                    <span class="badge bg-secondary">Basse</span>
                                @endif
                            </li>
                            <li>
                                <small class="text-muted d-block">Statut</small>
                                @if($ticket->status === 'open')
                                    <span class="badge bg-danger">Ouvert</span>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="badge bg-warning text-dark">En cours</span>
                                @else
                                    <span class="badge bg-success">Résolu</span>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection