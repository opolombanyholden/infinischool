@extends('layouts.dashboard')

@section('title', 'Gestion des Utilisateurs')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-users text-primary me-2"></i>
                    Gestion des Utilisateurs
                </h1>
                <p class="text-muted mb-0">Gérez tous les utilisateurs de la plateforme</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Ajouter un utilisateur
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-primary mb-0">{{ $stats['total'] ?? 0 }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-info mb-0">{{ $stats['students'] ?? 0 }}</h4>
                        <small class="text-muted">Étudiants</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-success mb-0">{{ $stats['teachers'] ?? 0 }}</h4>
                        <small class="text-muted">Enseignants</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-danger mb-0">{{ $stats['admins'] ?? 0 }}</h4>
                        <small class="text-muted">Admins</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Rechercher</label>
                        <input type="text" name="search" class="form-control" placeholder="Nom, email..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Rôle</label>
                        <select name="role" class="form-select">
                            <option value="">Tous les rôles</option>
                            <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Étudiants</option>
                            <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Enseignants</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Statut</label>
                        <select name="status" class="form-select">
                            <option value="">Tous</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Inscrit le</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'teacher' ? 'success' : 'primary') }} bg-opacity-10 
                                                            me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    @if($user->avatar)
                                                        <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle" width="40" height="40">
                                                    @else
                                                        <span class="fw-bold text-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'teacher' ? 'success' : 'primary') }}">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $user->name }}</div>
                                                    @if($user->phone)
                                                        <small class="text-muted">{{ $user->phone }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted">{{ $user->email }}</td>
                                        <td>
                                            @if($user->role === 'admin')
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fas fa-shield-alt me-1"></i>Admin
                                                </span>
                                            @elseif($user->role === 'teacher')
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-chalkboard-teacher me-1"></i>Enseignant
                                                </span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <i class="fas fa-user-graduate me-1"></i>Étudiant
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->status === 'active' || !$user->status)
                                                <span class="badge bg-success">Actif</span>
                                            @elseif($user->status === 'pending')
                                                <span class="badge bg-warning">En attente</span>
                                            @elseif($user->status === 'suspended')
                                                <span class="badge bg-danger">Suspendu</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $user->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" title="Voir (à venir)" disabled>
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" title="Modifier (à venir)" disabled>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="card-footer bg-white">
                            {{ $users->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p class="mb-0">Aucun utilisateur trouvé</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
