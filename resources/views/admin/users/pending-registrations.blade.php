@extends('layouts.admin')

@section('title', 'Inscriptions En Attente')
@section('page-title', 'Inscriptions En Attente')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">Inscriptions en attente</li>
@endsection

@section('content')
    <div class="container-fluid">

        {{-- Messages Flash --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Statistiques --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Total En Attente</h6>
                                <h2 class="mb-0">{{ $stats['total_pending'] }}</h2>
                            </div>
                            <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Email Vérifié</h6>
                                <h2 class="mb-0">{{ $stats['email_verified'] }}</h2>
                            </div>
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Email Non Vérifié</h6>
                                <h2 class="mb-0">{{ $stats['email_not_verified'] }}</h2>
                            </div>
                            <i class="fas fa-times-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Aujourd'hui</h6>
                                <h2 class="mb-0">{{ $stats['today'] }}</h2>
                            </div>
                            <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.users.pending') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Formation</label>
                        <select name="formation_id" class="form-select">
                            <option value="">Toutes les formations</option>
                            @foreach($formations as $formation)
                                <option value="{{ $formation->id }}"
                                    {{ request('formation_id') == $formation->id ? 'selected' : '' }}>
                                    {{ $formation->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" placeholder="Nom, email, numéro étudiant..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.users.pending') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tableau des inscriptions --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-clock me-2"></i>
                    Inscriptions en attente ({{ $pendingUsers->total() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if($pendingUsers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Contact</th>
                                    <th>Formation Souhaitée</th>
                                    <th>Niveau</th>
                                    <th>Email</th>
                                    <th>Date Inscription</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingUsers as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $user->name }}</div>
                                                    <small class="text-muted">{{ $user->student_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div><i class="fas fa-envelope text-muted me-1"></i>{{ $user->email }}</div>
                                            <div><i class="fas fa-phone text-muted me-1"></i>{{ $user->phone ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @if($user->desiredFormation)
                                                <span class="badge bg-primary">{{ $user->desiredFormation->title }}</span>
                                            @else
                                                <span class="text-muted">Non spécifié</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $levels = [
                                                    'bac' => 'Bac',
                                                    'licence' => 'Licence',
                                                    'master' => 'Master',
                                                    'doctorat' => 'Doctorat',
                                                    'autre' => 'Autre'
                                                ];
                                            @endphp
                                            <span class="badge bg-secondary">{{ $levels[$user->desired_level] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($user->email_verified_at)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Vérifié
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Non vérifié
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                {{-- Bouton Voir --}}
                                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info"
                                                    title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Bouton Approuver --}}
                                                <form action="{{ route('admin.users.approve', $user) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Valider l\'inscription de {{ $user->name }} ?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approuver">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>

                                                {{-- Bouton Refuser (ouvre modal) --}}
                                                <button type="button" class="btn btn-sm btn-danger" title="Refuser"
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $user->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Modal de refus --}}
                                    <div class="modal fade" id="rejectModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.users.reject', $user) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-times-circle text-danger me-2"></i>
                                                            Refuser l'inscription
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Vous êtes sur le point de refuser l'inscription de :</p>
                                                        <div class="alert alert-secondary">
                                                            <strong>{{ $user->name }}</strong><br>
                                                            <small>{{ $user->email }}</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Raison du refus <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" required
                                                                placeholder="Indiquez la raison du refus..."></textarea>
                                                            <small class="text-muted">Cette raison sera communiquée à l'utilisateur
                                                                par email.</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-times me-1"></i>Confirmer le refus
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="card-footer">
                        {{ $pendingUsers->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h5>Aucune inscription en attente</h5>
                        <p class="text-muted">Toutes les inscriptions ont été traitées.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-hide alerts après 5 secondes
        setTimeout(function () {
            document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
@endpush