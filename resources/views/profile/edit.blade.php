@extends('layouts.dashboard')

@section('title', 'Mon profil - InfiniSchool')
@section('description', 'Gérez vos informations personnelles et paramètres de compte')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route(Auth::user()->role . '.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Mon profil</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-2 fw-bold">
                        <i class="fas fa-user-edit text-primary me-2"></i>
                        Mon profil
                    </h1>
                    <p class="text-muted mb-0">
                        Gérez vos informations personnelles et paramètres de compte
                    </p>
                </div>
                <a href="{{ route(Auth::user()->role . '.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au dashboard
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Erreur :</strong> Veuillez corriger les erreurs ci-dessous.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Colonne principale -->
        <div class="col-12 col-xl-8">
            <!-- Navigation par onglets -->
            <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                        <i class="fas fa-user me-2"></i>Informations
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                        <i class="fas fa-lock me-2"></i>Sécurité
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                        <i class="fas fa-cog me-2"></i>Préférences
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="profileTabsContent">
                <!-- Onglet Informations personnelles -->
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">Informations personnelles</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    <!-- Prénom -->
                                    <div class="col-12 col-md-6">
                                        <label for="first_name" class="form-label">
                                            Prénom <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('first_name') is-invalid @enderror" 
                                               id="first_name" 
                                               name="first_name" 
                                               value="{{ old('first_name', $user->first_name) }}" 
                                               required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Nom -->
                                    <div class="col-12 col-md-6">
                                        <label for="last_name" class="form-label">
                                            Nom <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" 
                                               name="last_name" 
                                               value="{{ old('last_name', $user->last_name) }}" 
                                               required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="col-12">
                                        <label for="email" class="form-label">
                                            Adresse email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $user->email) }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            Votre email sera utilisé pour la connexion et les notifications
                                        </small>
                                    </div>

                                    <!-- Téléphone -->
                                    <div class="col-12 col-md-6">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone', $user->phone) }}"
                                               placeholder="+241 XX XX XX XX">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Date de naissance -->
                                    <div class="col-12 col-md-6">
                                        <label for="birth_date" class="form-label">Date de naissance</label>
                                        <input type="date" 
                                               class="form-control @error('birth_date') is-invalid @enderror" 
                                               id="birth_date" 
                                               name="birth_date" 
                                               value="{{ old('birth_date', $user->birth_date) }}">
                                        @error('birth_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Pays -->
                                    <div class="col-12 col-md-6">
                                        <label for="country" class="form-label">Pays</label>
                                        <select class="form-select @error('country') is-invalid @enderror" 
                                                id="country" 
                                                name="country">
                                            <option value="">Sélectionner...</option>
                                            <option value="GA" {{ old('country', $user->country) === 'GA' ? 'selected' : '' }}>Gabon</option>
                                            <option value="FR" {{ old('country', $user->country) === 'FR' ? 'selected' : '' }}>France</option>
                                            <option value="CI" {{ old('country', $user->country) === 'CI' ? 'selected' : '' }}>Côte d'Ivoire</option>
                                            <option value="SN" {{ old('country', $user->country) === 'SN' ? 'selected' : '' }}>Sénégal</option>
                                            <option value="CM" {{ old('country', $user->country) === 'CM' ? 'selected' : '' }}>Cameroun</option>
                                            <option value="other">Autre</option>
                                        </select>
                                        @error('country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Ville -->
                                    <div class="col-12 col-md-6">
                                        <label for="city" class="form-label">Ville</label>
                                        <input type="text" 
                                               class="form-control @error('city') is-invalid @enderror" 
                                               id="city" 
                                               name="city" 
                                               value="{{ old('city', $user->city) }}"
                                               placeholder="Ex: Libreville">
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Bio (pour enseignants) -->
                                    @if(Auth::user()->role === 'teacher')
                                    <div class="col-12">
                                        <label for="bio" class="form-label">Biographie</label>
                                        <textarea class="form-control @error('bio') is-invalid @enderror" 
                                                  id="bio" 
                                                  name="bio" 
                                                  rows="4"
                                                  placeholder="Présentez-vous à vos futurs étudiants...">{{ old('bio', $user->bio) }}</textarea>
                                        @error('bio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">{{ strlen($user->bio ?? '') }}/500 caractères</small>
                                    </div>

                                    <!-- Spécialités -->
                                    <div class="col-12">
                                        <label for="specialization" class="form-label">Spécialités</label>
                                        <input type="text" 
                                               class="form-control @error('specialization') is-invalid @enderror" 
                                               id="specialization" 
                                               name="specialization" 
                                               value="{{ old('specialization', $user->specialization) }}"
                                               placeholder="Ex: Développement Web, Marketing Digital">
                                        @error('specialization')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif
                                </div>

                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-2"></i>Annuler
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Onglet Sécurité -->
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <!-- Changement mot de passe -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">Changer le mot de passe</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.password.update') }}" method="POST" id="passwordForm">
                                @csrf
                                @method('PUT')

                                <!-- Mot de passe actuel -->
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">
                                        Mot de passe actuel <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('current_password') is-invalid @enderror" 
                                               id="current_password" 
                                               name="current_password" 
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Nouveau mot de passe -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        Nouveau mot de passe <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Indicateur force mot de passe -->
                                    <div class="mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted" id="passwordStrengthText">Force du mot de passe</small>
                                    </div>
                                </div>

                                <!-- Confirmation mot de passe -->
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        Confirmer le mot de passe <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required>
                                </div>

                                <!-- Exigences mot de passe -->
                                <div class="alert alert-info border-0 mb-3">
                                    <strong>Exigences du mot de passe :</strong>
                                    <ul class="mb-0 mt-2" id="passwordRequirements">
                                        <li id="req-length">Au moins 8 caractères</li>
                                        <li id="req-uppercase">Une lettre majuscule</li>
                                        <li id="req-lowercase">Une lettre minuscule</li>
                                        <li id="req-number">Un chiffre</li>
                                        <li id="req-special">Un caractère spécial (@$!%*?&)</li>
                                    </ul>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Mettre à jour le mot de passe
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Authentification à deux facteurs -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">Authentification à deux facteurs (2FA)</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Ajoutez une couche de sécurité supplémentaire à votre compte en activant l'authentification à deux facteurs.
                            </p>
                            
                            @if($user->two_factor_enabled ?? false)
                                <div class="alert alert-success border-0 mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    L'authentification à deux facteurs est <strong>activée</strong>
                                </div>
                                <form action="{{ route('profile.2fa.disable') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times me-2"></i>Désactiver 2FA
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-warning border-0 mb-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    L'authentification à deux facteurs est <strong>désactivée</strong>
                                </div>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#enable2FAModal">
                                    <i class="fas fa-shield-alt me-2"></i>Activer 2FA
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Onglet Préférences -->
                <div class="tab-pane fade" id="preferences" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">Préférences de notification</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.preferences.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-3">Notifications par email</h6>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notify_new_course" 
                                               name="notify_new_course"
                                               {{ old('notify_new_course', $user->preferences->notify_new_course ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_new_course">
                                            <strong>Nouveaux cours disponibles</strong>
                                            <br><small class="text-muted">Recevoir des alertes quand de nouveaux cours sont publiés</small>
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notify_class_reminder" 
                                               name="notify_class_reminder"
                                               {{ old('notify_class_reminder', $user->preferences->notify_class_reminder ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_class_reminder">
                                            <strong>Rappels de cours</strong>
                                            <br><small class="text-muted">Recevoir un rappel 1h avant chaque cours en direct</small>
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notify_certificate" 
                                               name="notify_certificate"
                                               {{ old('notify_certificate', $user->preferences->notify_certificate ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_certificate">
                                            <strong>Certificats disponibles</strong>
                                            <br><small class="text-muted">M'avertir quand un certificat est prêt</small>
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notify_messages" 
                                               name="notify_messages"
                                               {{ old('notify_messages', $user->preferences->notify_messages ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_messages">
                                            <strong>Messages reçus</strong>
                                            <br><small class="text-muted">Recevoir une notification pour les nouveaux messages</small>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notify_newsletter" 
                                               name="notify_newsletter"
                                               {{ old('notify_newsletter', $user->preferences->notify_newsletter ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_newsletter">
                                            <strong>Newsletter et promotions</strong>
                                            <br><small class="text-muted">Recevoir nos actualités et offres spéciales</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-3">Langue</h6>
                                    <select class="form-select" name="language" style="max-width: 300px;">
                                        <option value="fr" {{ old('language', $user->preferences->language ?? 'fr') === 'fr' ? 'selected' : '' }}>Français</option>
                                        <option value="en" {{ old('language', $user->preferences->language ?? 'fr') === 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-3">Fuseau horaire</h6>
                                    <select class="form-select" name="timezone" style="max-width: 300px;">
                                        <option value="Africa/Libreville" {{ old('timezone', $user->preferences->timezone ?? 'Africa/Libreville') === 'Africa/Libreville' ? 'selected' : '' }}>
                                            Libreville (WAT)
                                        </option>
                                        <option value="Europe/Paris" {{ old('timezone', $user->preferences->timezone ?? 'Africa/Libreville') === 'Europe/Paris' ? 'selected' : '' }}>
                                            Paris (CET)
                                        </option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les préférences
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Supprimer le compte -->
                    <div class="card border-danger shadow-sm mt-4">
                        <div class="card-header bg-danger bg-opacity-10 border-danger py-3">
                            <h5 class="mb-0 fw-bold text-danger">Zone dangereuse</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Une fois votre compte supprimé, toutes vos données seront définitivement effacées. 
                                Cette action est irréversible.
                            </p>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash-alt me-2"></i>Supprimer mon compte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-xl-4">
            <!-- Photo de profil -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&size=150&background=800020&color=fff' }}" 
                             alt="Photo de profil" 
                             class="rounded-circle"
                             id="avatarPreview"
                             style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #800020;">
                        <button type="button" 
                                class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0"
                                style="width: 40px; height: 40px;"
                                onclick="document.getElementById('avatarInput').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>

                    <h5 class="fw-bold mb-1">{{ $user->full_name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }}</p>
                    
                    <span class="badge bg-primary-subtle text-primary">
                        <i class="fas fa-{{ $user->role === 'student' ? 'graduation-cap' : ($user->role === 'teacher' ? 'chalkboard-teacher' : 'shield-alt') }} me-1"></i>
                        {{ ucfirst($user->role) }}
                    </span>

                    <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                        @csrf
                        <input type="file" 
                               id="avatarInput" 
                               name="avatar" 
                               accept="image/*" 
                               class="d-none"
                               onchange="previewAndUploadAvatar(this)">
                    </form>

                    @if($user->avatar)
                    <form action="{{ route('profile.avatar.delete') }}" method="POST" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash me-1"></i>Supprimer la photo
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Statistiques compte -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold">Statistiques du compte</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Membre depuis</span>
                        <span class="fw-semibold">{{ $user->created_at->locale('fr')->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Dernière connexion</span>
                        <span class="fw-semibold">{{ $user->last_login_at?->locale('fr')->diffForHumans() ?? 'Jamais' }}</span>
                    </div>
                    @if($user->role === 'student')
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Formations suivies</span>
                        <span class="fw-semibold">{{ $user->enrollments_count ?? 0 }}</span>
                    </div>
                    @elseif($user->role === 'teacher')
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Cours créés</span>
                        <span class="fw-semibold">{{ $user->courses_count ?? 0 }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Aide et support -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold">Besoin d'aide ?</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('support.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-life-ring me-2"></i>Centre d'aide
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-envelope me-2"></i>Contacter le support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Supprimer compte -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Supprimer mon compte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger border-0">
                    <strong>Attention !</strong> Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir supprimer votre compte ? Toutes vos données seront définitivement effacées :</p>
                <ul>
                    <li>Vos informations personnelles</li>
                    <li>Votre historique de formations</li>
                    <li>Vos certificats</li>
                    <li>Vos messages</li>
                </ul>
                <p class="mb-0">Pour confirmer, veuillez saisir votre mot de passe :</p>
                <form action="{{ route('profile.delete') }}" method="POST" id="deleteAccountForm">
                    @csrf
                    @method('DELETE')
                    <input type="password" 
                           class="form-control mt-2" 
                           name="password" 
                           placeholder="Mot de passe"
                           required>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="deleteAccountForm" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-2"></i>Supprimer définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Activer 2FA -->
<div class="modal fade" id="enable2FAModal" tabindex="-1" aria-labelledby="enable2FAModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="enable2FAModalLabel">
                    <i class="fas fa-shield-alt me-2"></i>Activer l'authentification à deux facteurs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Scannez ce QR code avec votre application d'authentification (Google Authenticator, Authy, etc.)</p>
                <div class="bg-light p-3 rounded mb-3">
                    <img src="https://via.placeholder.com/200x200/800020/ffffff?text=QR+CODE" 
                         alt="QR Code 2FA" 
                         class="img-fluid">
                </div>
                <p class="small text-muted">Ou entrez cette clé manuellement : <code>ABCD-EFGH-IJKL</code></p>
                <form action="{{ route('profile.2fa.enable') }}" method="POST">
                    @csrf
                    <input type="text" 
                           class="form-control text-center mb-3" 
                           name="code" 
                           placeholder="Code à 6 chiffres"
                           maxlength="6"
                           required>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check me-2"></i>Vérifier et activer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Onglets personnalisés */
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.nav-tabs .nav-link:hover {
    color: #800020;
    border-bottom-color: rgba(128, 0, 32, 0.3);
}

.nav-tabs .nav-link.active {
    color: #800020;
    background: none;
    border-bottom-color: #800020;
    font-weight: 600;
}

/* Liste exigences mot de passe */
#passwordRequirements li {
    position: relative;
    padding-left: 25px;
    margin-bottom: 5px;
}

#passwordRequirements li::before {
    content: '\f00d';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    left: 0;
    color: #dc3545;
}

#passwordRequirements li.valid::before {
    content: '\f00c';
    color: #198754;
}

/* Avatar hover effect */
#avatarPreview {
    transition: opacity 0.3s;
}

.position-relative:hover #avatarPreview {
    opacity: 0.8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    ['toggleCurrentPassword', 'togglePassword'].forEach(id => {
        const btn = document.getElementById(id);
        btn?.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Validation force mot de passe
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    const requirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number'),
        special: document.getElementById('req-special')
    };

    passwordInput?.addEventListener('input', function() {
        const value = this.value;
        let strength = 0;

        // Vérifier chaque exigence
        const checks = {
            length: value.length >= 8,
            uppercase: /[A-Z]/.test(value),
            lowercase: /[a-z]/.test(value),
            number: /[0-9]/.test(value),
            special: /[@$!%*?&]/.test(value)
        };

        // Mettre à jour visuellement chaque exigence
        Object.keys(checks).forEach(key => {
            if (checks[key]) {
                requirements[key]?.classList.add('valid');
                strength += 20;
            } else {
                requirements[key]?.classList.remove('valid');
            }
        });

        // Mettre à jour la barre de progression
        strengthBar.style.width = strength + '%';
        strengthBar.className = 'progress-bar';
        
        if (strength <= 40) {
            strengthBar.classList.add('bg-danger');
            strengthText.textContent = 'Mot de passe faible';
        } else if (strength <= 60) {
            strengthBar.classList.add('bg-warning');
            strengthText.textContent = 'Mot de passe moyen';
        } else if (strength <= 80) {
            strengthBar.classList.add('bg-info');
            strengthText.textContent = 'Mot de passe bon';
        } else {
            strengthBar.classList.add('bg-success');
            strengthText.textContent = 'Mot de passe fort';
        }
    });

    // Compteur caractères bio
    const bioTextarea = document.getElementById('bio');
    if (bioTextarea) {
        bioTextarea.addEventListener('input', function() {
            const count = this.value.length;
            const counter = this.nextElementSibling.nextElementSibling;
            if (counter) {
                counter.textContent = `${count}/500 caractères`;
                if (count > 500) {
                    counter.classList.add('text-danger');
                } else {
                    counter.classList.remove('text-danger');
                }
            }
        });
    }
});

// Preview et upload avatar
function previewAndUploadAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
        
        // Auto-submit le formulaire
        document.getElementById('avatarForm').submit();
    }
}
</script>
@endsection