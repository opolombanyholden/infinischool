@extends('layouts.dashboard')

@section('title', 'Créer un Utilisateur')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i>Retour aux utilisateurs
                </a>
                <h1 class="h3 mb-1 fw-bold mt-2">
                    <i class="fas fa-user-plus text-primary me-2"></i>
                    Créer un Utilisateur
                </h1>
                <p class="text-muted mb-0">Ajouter un nouvel utilisateur à la plateforme</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-4">
                        <!-- Informations de base -->
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3">Informations personnelles</h5>

                            <div class="mb-3">
                                <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                    name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Compte et rôle -->
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3">Paramètres du compte</h5>

                            <div class="mb-3">
                                <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role"
                                    required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Étudiant
                                    </option>
                                    <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Enseignant
                                    </option>
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrateur
                                    </option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                        Actif</option>
                                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>En attente
                                    </option>
                                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspendu
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Créer l'utilisateur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection