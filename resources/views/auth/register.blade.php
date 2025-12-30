@extends('layouts.auth')

@section('title', 'Inscription Étudiant - InfiniSchool')
@section('form-title', 'Inscription Étudiant')
@section('form-description', 'Commencez votre parcours d\'apprentissage')

@section('brand-title', 'Rejoignez InfiniSchool')
@section('brand-description', 'Inscrivez-vous et accédez à nos formations de qualité. Votre dossier sera examiné par notre équipe.')

@section('content')

    <!-- Social Register -->
    <div class="social-login">
        <a href="{{ route('auth.social', 'google') }}" class="btn-social btn-google">
            <i class="fab fa-google"></i>
            <span>Google</span>
        </a>
        <a href="{{ route('auth.social', 'linkedin') }}" class="btn-social btn-linkedin">
            <i class="fab fa-linkedin"></i>
            <span>LinkedIn</span>
        </a>
    </div>

    <!-- Divider -->
    <div class="auth-divider">
        <span>Ou avec votre email</span>
    </div>

    <!-- Register Form -->
    <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
        @csrf

        <input type="hidden" name="role" value="student">

        <!-- Section: Informations Personnelles -->
        <div class="form-section-title">
            <i class="fas fa-user me-2"></i>Informations Personnelles
        </div>

        <!-- Full Name -->
        <div class="form-group">
            <label for="name" class="form-label">Nom Complet <span class="text-danger">*</span></label>
            <div class="input-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" class="form-control with-icon @error('name') is-invalid @enderror" id="name" name="name"
                    value="{{ old('name') }}" placeholder="Prénom Nom" required autofocus>
            </div>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email" class="form-label">Adresse Email <span class="text-danger">*</span></label>
            <div class="input-group">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" class="form-control with-icon @error('email') is-invalid @enderror" id="email"
                    name="email" value="{{ old('email') }}" placeholder="votre@email.com" required>
            </div>
            <small class="form-text text-muted">Utilisé pour la connexion et les notifications</small>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Phone -->
        <div class="form-group">
            <label for="phone" class="form-label">Numéro de Téléphone <span class="text-danger">*</span></label>
            <div class="input-group">
                <i class="fas fa-phone input-icon"></i>
                <input type="tel" class="form-control with-icon @error('phone') is-invalid @enderror" id="phone"
                    name="phone" value="{{ old('phone') }}" placeholder="+241 XX XX XX XX" required>
            </div>
            @error('phone')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Section: Adresse -->
        <div class="form-section-title mt-4">
            <i class="fas fa-map-marker-alt me-2"></i>Adresse
        </div>

        <!-- Address -->
        <div class="form-group">
            <label for="address" class="form-label">Adresse Complète <span class="text-danger">*</span></label>
            <div class="input-group">
                <i class="fas fa-home input-icon"></i>
                <input type="text" class="form-control with-icon @error('address') is-invalid @enderror" id="address"
                    name="address" value="{{ old('address') }}" placeholder="Numéro, rue, quartier" required>
            </div>
            @error('address')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <!-- City -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="city" class="form-label">Ville <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city"
                        value="{{ old('city') }}" placeholder="Libreville" required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Country -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="country" class="form-label">Pays <span class="text-danger">*</span></label>
                    <select class="form-control @error('country') is-invalid @enderror" id="country" name="country"
                        required>
                        <option value="Gabon" {{ old('country', 'Gabon') === 'Gabon' ? 'selected' : '' }}>Gabon</option>
                        <option value="France" {{ old('country') === 'France' ? 'selected' : '' }}>France</option>
                        <option value="Côte d'Ivoire" {{ old('country') === "Côte d'Ivoire" ? 'selected' : '' }}>Côte
                            d'Ivoire</option>
                        <option value="Sénégal" {{ old('country') === 'Sénégal' ? 'selected' : '' }}>Sénégal</option>
                        <option value="Cameroun" {{ old('country') === 'Cameroun' ? 'selected' : '' }}>Cameroun</option>
                        <option value="Autre">Autre</option>
                    </select>
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Formation -->
        <div class="form-section-title mt-4">
            <i class="fas fa-graduation-cap me-2"></i>Formation Souhaitée
        </div>

        <!-- Formation -->
        <div class="form-group">
            <label for="desired_formation_id" class="form-label">
                Choisissez Votre Formation <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <i class="fas fa-book input-icon"></i>
                <select class="form-control with-icon @error('desired_formation_id') is-invalid @enderror"
                    id="desired_formation_id" name="desired_formation_id" required>
                    <option value="">Sélectionnez une formation...</option>
                    @foreach($formations as $formation)
                        <option value="{{ $formation->id }}"
                            {{ old('desired_formation_id') == $formation->id ? 'selected' : '' }}>
                            {{ $formation->title }} - {{ ucfirst($formation->level) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('desired_formation_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Desired Level -->
        <div class="form-group">
            <label for="desired_level" class="form-label">
                Diplôme/Niveau Visé <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <i class="fas fa-award input-icon"></i>
                <select class="form-control with-icon @error('desired_level') is-invalid @enderror" id="desired_level"
                    name="desired_level" required>
                    <option value="">Sélectionnez un niveau...</option>
                    <option value="bac" {{ old('desired_level') == 'bac' ? 'selected' : '' }}>Baccalauréat</option>
                    <option value="licence" {{ old('desired_level') == 'licence' ? 'selected' : '' }}>Licence / Bachelor
                    </option>
                    <option value="master" {{ old('desired_level') == 'master' ? 'selected' : '' }}>Master</option>
                    <option value="doctorat" {{ old('desired_level') == 'doctorat' ? 'selected' : '' }}>Doctorat</option>
                    <option value="autre" {{ old('desired_level') == 'autre' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>
            @error('desired_level')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Section: Sécurité -->
        <div class="form-section-title mt-4">
            <i class="fas fa-lock me-2"></i>Sécurité
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">Mot de Passe <span class="text-danger">*</span></label>
            <div class="input-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" class="form-control with-icon @error('password') is-invalid @enderror" id="password"
                    name="password" placeholder="Minimum 8 caractères" required minlength="8">
                <span class="password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <small class="form-text text-muted">
                Au moins 8 caractères avec lettres et chiffres
            </small>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password Confirmation -->
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirmer le Mot de Passe <span
                    class="text-danger">*</span></label>
            <div class="input-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" class="form-control with-icon" id="password_confirmation"
                    name="password_confirmation" placeholder="Confirmez votre mot de passe" required minlength="8">
                <span class="password-toggle" onclick="togglePassword('password_confirmation')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>

        <!-- Terms Acceptance -->
        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms" name="terms"
                    required>
                <label class="form-check-label" for="terms">
                    J'accepte les
                    <a href="{{ route('terms') }}" target="_blank" class="auth-link">Conditions Générales</a>
                    et la
                    <a href="{{ route('privacy') }}" target="_blank" class="auth-link">Politique de Confidentialité</a>
                </label>
            </div>
            @error('terms')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Newsletter -->
        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter"
                    {{ old('newsletter') ? 'checked' : '' }}>
                <label class="form-check-label" for="newsletter">
                    Je souhaite recevoir les actualités et offres d'InfiniSchool
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-user-plus me-2"></i>Soumettre Mon Inscription
        </button>

        <!-- Login Link -->
        <div class="text-center mt-4">
            <p class="mb-0" style="color: #6c757d;">
                Vous avez déjà un compte ?
                <a href="{{ route('login') }}" class="auth-link">
                    Se connecter
                </a>
            </p>
        </div>

        <!-- Teacher Link -->
        <div class="text-center mt-2">
            <p class="mb-0 small" style="color: #6c757d;">
                Vous êtes enseignant ?
                <a href="{{ route('teacher.apply') }}" class="auth-link">
                    Postuler ici
                </a>
            </p>
        </div>
    </form>

@endsection

@section('styles')
    <style>
        /* Form Section Title */
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(128, 0, 32, 0.1);
        }

        /* Styling amélioré */
        .row {
            margin-left: -7.5px;
            margin-right: -7.5px;
        }

        .row>[class*="col-"] {
            padding-left: 7.5px;
            padding-right: 7.5px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.nextElementSibling;
            const icon = toggle.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function () {
            const password = this.value;
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasMinLength = password.length >= 8;

            if (hasLetter && hasNumber && hasMinLength) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
        });

        // Password confirmation validation
        document.getElementById('password_confirmation').addEventListener('input', function () {
            const password = document.getElementById('password').value;
            const confirmation = this.value;

            if (password === confirmation && confirmation.length > 0) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                if (confirmation.length > 0) {
                    this.classList.add('is-invalid');
                }
            }
        });
    </script>
@endsection