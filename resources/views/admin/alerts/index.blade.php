@extends('layouts.dashboard')

@section('title', 'Alertes Système')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-bell text-danger me-2"></i>
                    Alertes Système
                </h1>
                <p class="text-muted mb-0">Gérer et envoyer des alertes aux utilisateurs</p>
            </div>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#newAlertModal">
                <i class="fas fa-plus me-2"></i>Nouvelle alerte
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-secondary mb-0">{{ $stats['total'] }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-success mb-0">{{ $stats['active'] }}</h4>
                        <small class="text-muted">Actives</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-info mb-0">{{ $stats['info'] }}</h4>
                        <small class="text-muted">Info</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-warning mb-0">{{ $stats['warning'] }}</h4>
                        <small class="text-muted">Avertissement</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="fw-bold text-danger mb-0">{{ $stats['danger'] }}</h4>
                        <small class="text-muted">Urgent</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="status" class="form-label small text-muted">Statut</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tous</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actives</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label small text-muted">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">Tous les types</option>
                            <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>Information</option>
                            <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>Avertissement
                            </option>
                            <option value="danger" {{ request('type') === 'danger' ? 'selected' : '' }}>Urgent</option>
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

        <!-- Alerts List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list text-secondary me-2"></i>
                    Liste des alertes
                </h5>
            </div>
            <div class="card-body p-0">
                @if($alerts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Type</th>
                                    <th>Titre</th>
                                    <th>Message</th>
                                    <th>Destinataires</th>
                                    <th style="width: 100px;">Statut</th>
                                    <th style="width: 150px;">Date</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alerts as $alert)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $alert->type }}-subtle text-{{ $alert->type }}">
                                                <i
                                                    class="fas fa-{{ $alert->type === 'danger' ? 'exclamation-triangle' : ($alert->type === 'warning' ? 'exclamation-circle' : 'info-circle') }} me-1"></i>
                                                {{ ucfirst($alert->type) }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold">{{ $alert->title }}</td>
                                        <td class="text-muted small">{{ Str::limit($alert->message, 60) }}</td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ $alert->target_role ? ucfirst($alert->target_role) . 's' : 'Tous' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($alert->is_active)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">
                                                    <i class="fas fa-times-circle me-1"></i>Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $alert->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form action="{{ route('admin.alerts.toggle', $alert) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-outline-{{ $alert->is_active ? 'warning' : 'success' }}"
                                                        title="{{ $alert->is_active ? 'Désactiver' : 'Activer' }}">
                                                        <i class="fas fa-{{ $alert->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.alerts.destroy', $alert) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Supprimer cette alerte ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($alerts->hasPages())
                        <div class="card-footer bg-white">
                            {{ $alerts->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-bell-slash fa-3x mb-3"></i>
                        <p class="mb-0">Aucune alerte trouvée</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Nouvelle alerte -->
    <div class="modal fade" id="newAlertModal" tabindex="-1" aria-labelledby="newAlertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newAlertModalLabel">
                        <i class="fas fa-bell text-danger me-2"></i>
                        Envoyer une alerte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.alerts.send') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="alertType" class="form-label">Type d'alerte <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="alertType" name="type" required>
                                <option value="">Sélectionner...</option>
                                <option value="info">Information</option>
                                <option value="warning">Avertissement</option>
                                <option value="danger">Urgent</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="alertTitle" class="form-label">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="alertTitle" name="title" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="alertMessage" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alertMessage" name="message" rows="4" required
                                maxlength="2000"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Destinataires <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allUsers" name="recipients[]"
                                    value="all">
                                <label class="form-check-label" for="allUsers">
                                    <i class="fas fa-users me-1"></i>Tous les utilisateurs
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="studentsOnly" name="recipients[]"
                                    value="students">
                                <label class="form-check-label" for="studentsOnly">
                                    <i class="fas fa-user-graduate me-1"></i>Étudiants uniquement
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="teachersOnly" name="recipients[]"
                                    value="teachers">
                                <label class="form-check-label" for="teachersOnly">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>Enseignants uniquement
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer l'alerte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection