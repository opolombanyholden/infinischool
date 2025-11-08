@extends('layouts.auth')

@section('title', 'Réinitialiser le mot de passe - InfiniSchool')
@section('form-title', 'Nouveau Mot de Passe')
@section('form-description', 'Créez un mot de passe sécurisé')

@section('brand-title', 'Dernière Étape !')
@section('brand-description', 'Choisissez un nouveau mot de passe fort et sécurisé. Assurez-vous qu\'il contient au moins 8 caractères avec des lettres et des chiffres.')

@section('content')

<!-- Success Message -->
@if (session('status'))
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('status') }}
    </div>
@endif

<!-- Reset Password Form -->
<form method="POST" action="{{ route('password.update') }}" class="needs-validation" novalidate>
    @csrf

    <!-- Hidden Token -->
    <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

    <!-- Email -->
    <div class="form-group">
        <label for="email" class="form-label">Adresse Email</label>
        <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" 
                   class="form-control with-icon @error('email') is-invalid @enderror" 
                   id="email" 
                   name="email" 
                   value="{{ $email ?? old('email') }}" 
                   placeholder="votre@email.com"
                   required 
                   autofocus
                   readonly>
        </div>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- New Password -->
    <div class="form-group">
        <label for="password" class="form-label">Nouveau Mot de Passe</label>
        <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" 
                   class="form-control with-icon @error('password') is-invalid @enderror" 
                   id="password" 
                   name="password" 
                   placeholder="Minimum 8 caractères"
                   required 
                   minlength="8">
            <span class="password-toggle" onclick="togglePassword('password')">
                <i class="fas fa-eye"></i>
            </span>
        </div>
        <!-- Password Strength Indicator -->
        <div class="password-strength mt-2">
            <div class="strength-bar">
                <div class="strength-fill" id="strengthBar"></div>
            </div>
            <small class="strength-text" id="strengthText">Force du mot de passe</small>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password Confirmation -->
    <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirmer le Mot de Passe</label>
        <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" 
                   class="form-control with-icon" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   placeholder="Confirmez votre mot de passe"
                   required 
                   minlength="8">
            <span class="password-toggle" onclick="togglePassword('password_confirmation')">
                <i class="fas fa-eye"></i>
            </span>
        </div>
        <small class="form-text text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Doit être identique au mot de passe ci-dessus
        </small>
    </div>

    <!-- Password Requirements -->
    <div class="password-requirements mb-3 p-3" style="background: #f8f9fa; border-radius: 10px;">
        <h6 class="mb-2" style="color: #212529; font-weight: 600; font-size: 0.9rem;">
            <i class="fas fa-shield-alt me-2" style="color: var(--primary-color);"></i>
            Critères de sécurité
        </h6>
        <ul class="requirements-list mb-0">
            <li id="req-length" class="requirement-item">
                <i class="fas fa-circle"></i>
                <span>Au moins 8 caractères</span>
            </li>
            <li id="req-letter" class="requirement-item">
                <i class="fas fa-circle"></i>
                <span>Au moins une lettre</span>
            </li>
            <li id="req-number" class="requirement-item">
                <i class="fas fa-circle"></i>
                <span>Au moins un chiffre</span>
            </li>
            <li id="req-match" class="requirement-item">
                <i class="fas fa-circle"></i>
                <span>Les mots de passe correspondent</span>
            </li>
        </ul>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-block" id="submitBtn" disabled>
        <i class="fas fa-check-circle me-2"></i>Réinitialiser le Mot de Passe
    </button>

    <!-- Back to Login -->
    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="auth-link">
            <i class="fas fa-arrow-left me-2"></i>Retour à la connexion
        </a>
    </div>
</form>

@endsection

@section('styles')
<style>
    /* Password Strength Indicator */
    .password-strength {
        margin-top: 10px;
    }

    .strength-bar {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
        border-radius: 3px;
    }

    .strength-fill.weak {
        width: 33%;
        background: #dc3545;
    }

    .strength-fill.medium {
        width: 66%;
        background: #ffc107;
    }

    .strength-fill.strong {
        width: 100%;
        background: #28a745;
    }

    .strength-text {
        display: block;
        margin-top: 5px;
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* Password Requirements */
    .requirements-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .requirement-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 5px 0;
        color: #6c757d;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    .requirement-item i {
        font-size: 0.5rem;
        transition: color 0.3s ease;
    }

    .requirement-item.valid {
        color: #28a745;
    }

    .requirement-item.valid i {
        color: #28a745;
    }

    .requirement-item.invalid {
        color: #dc3545;
    }

    .requirement-item.invalid i {
        color: #dc3545;
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

    // Password validation and strength
    const passwordField = document.getElementById('password');
    const confirmField = document.getElementById('password_confirmation');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const submitBtn = document.getElementById('submitBtn');

    // Requirements elements
    const reqLength = document.getElementById('req-length');
    const reqLetter = document.getElementById('req-letter');
    const reqNumber = document.getElementById('req-number');
    const reqMatch = document.getElementById('req-match');

    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        return strength;
    }

    function updatePasswordStrength() {
        const password = passwordField.value;
        const strength = checkPasswordStrength(password);

        // Update strength bar
        strengthBar.className = 'strength-fill';
        if (strength <= 2) {
            strengthBar.classList.add('weak');
            strengthText.textContent = 'Faible';
            strengthText.style.color = '#dc3545';
        } else if (strength <= 3) {
            strengthBar.classList.add('medium');
            strengthText.textContent = 'Moyen';
            strengthText.style.color = '#ffc107';
        } else {
            strengthBar.classList.add('strong');
            strengthText.textContent = 'Fort';
            strengthText.style.color = '#28a745';
        }
    }

    function validateRequirements() {
        const password = passwordField.value;
        const confirmation = confirmField.value;

        // Check length
        if (password.length >= 8) {
            reqLength.classList.add('valid');
            reqLength.classList.remove('invalid');
        } else {
            reqLength.classList.add('invalid');
            reqLength.classList.remove('valid');
        }

        // Check letter
        if (/[a-zA-Z]/.test(password)) {
            reqLetter.classList.add('valid');
            reqLetter.classList.remove('invalid');
        } else {
            reqLetter.classList.add('invalid');
            reqLetter.classList.remove('valid');
        }

        // Check number
        if (/[0-9]/.test(password)) {
            reqNumber.classList.add('valid');
            reqNumber.classList.remove('invalid');
        } else {
            reqNumber.classList.add('invalid');
            reqNumber.classList.remove('valid');
        }

        // Check match
        if (password.length > 0 && confirmation.length > 0 && password === confirmation) {
            reqMatch.classList.add('valid');
            reqMatch.classList.remove('invalid');
        } else if (confirmation.length > 0) {
            reqMatch.classList.add('invalid');
            reqMatch.classList.remove('valid');
        } else {
            reqMatch.classList.remove('valid', 'invalid');
        }

        // Enable/disable submit button
        const allValid = 
            password.length >= 8 &&
            /[a-zA-Z]/.test(password) &&
            /[0-9]/.test(password) &&
            password === confirmation &&
            confirmation.length > 0;

        submitBtn.disabled = !allValid;
    }

    passwordField.addEventListener('input', function() {
        updatePasswordStrength();
        validateRequirements();
    });

    confirmField.addEventListener('input', validateRequirements);
</script>
@endsection