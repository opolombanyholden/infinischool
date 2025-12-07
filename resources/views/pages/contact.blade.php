@extends('layouts.app')

@section('title', 'Contact - InfiniSchool')
@section('description', 'Contactez-nous pour toute question ou information. Notre équipe est disponible pour vous accompagner dans votre parcours d\'apprentissage.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Contactez-Nous</h1>
                <p class="page-header-subtitle">
                    Notre équipe est là pour répondre à toutes vos questions
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contact</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Information -->
            <div class="col-lg-4">
                <div class="contact-info-wrapper">
                    <h3 class="contact-info-title mb-4">Informations de Contact</h3>
                    
                    <!-- Address -->
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-info-content">
                            <h5>Adresse</h5>
                            <p>Boulevard Triomphal<br>Libreville, Gabon</p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-info-content">
                            <h5>Téléphone</h5>
                            <p>
                                <a href="tel:+241011234567">+241 01 12 34 56 7</a><br>
                                <a href="tel:+241077123456">+241 07 71 23 45 6</a>
                            </p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-info-content">
                            <h5>Email</h5>
                            <p>
                                <a href="mailto:contact@infinischool.com">contact@infinischool.com</a><br>
                                <a href="mailto:support@infinischool.com">support@infinischool.com</a>
                            </p>
                        </div>
                    </div>

                    <!-- Working Hours -->
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-info-content">
                            <h5>Horaires d'Ouverture</h5>
                            <p>
                                Lundi - Vendredi: 8h - 18h<br>
                                Samedi: 9h - 14h<br>
                                Dimanche: Fermé
                            </p>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="contact-social mt-4">
                        <h5 class="mb-3">Suivez-Nous</h5>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form-wrapper">
                    <h3 class="contact-form-title mb-4">Envoyez-nous un Message</h3>
                    
                    <form action="{{ route('contact.send') }}" method="POST" class="contact-form needs-validation" novalidate>
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nom Complet <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control with-icon" id="name" name="name" 
                                               value="{{ old('name') }}" required>
                                        <div class="invalid-feedback">
                                            Veuillez entrer votre nom.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Adresse Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control with-icon" id="email" name="email" 
                                               value="{{ old('email') }}" required>
                                        <div class="invalid-feedback">
                                            Veuillez entrer une adresse email valide.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="tel" class="form-control with-icon" id="phone" name="phone" 
                                               value="{{ old('phone') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subject" class="form-label">Sujet <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-tag"></i>
                                        </span>
                                        <select class="form-control with-icon" id="subject" name="subject" required>
                                            <option value="">Sélectionnez un sujet</option>
                                            <option value="information" {{ old('subject') == 'information' ? 'selected' : '' }}>Information générale</option>
                                            <option value="inscription" {{ old('subject') == 'inscription' ? 'selected' : '' }}>Inscription</option>
                                            <option value="formation" {{ old('subject') == 'formation' ? 'selected' : '' }}>Formations</option>
                                            <option value="technique" {{ old('subject') == 'technique' ? 'selected' : '' }}>Support technique</option>
                                            <option value="partenariat" {{ old('subject') == 'partenariat' ? 'selected' : '' }}>Partenariat</option>
                                            <option value="autre" {{ old('subject') == 'autre' ? 'selected' : '' }}>Autre</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Veuillez sélectionner un sujet.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="6" 
                                              required>{{ old('message') }}</textarea>
                                    <div class="invalid-feedback">
                                        Veuillez entrer votre message.
                                    </div>
                                    <small class="form-text text-muted">
                                        Minimum 10 caractères
                                    </small>
                                </div>
                            </div>

                            <!-- Privacy Policy -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="privacy" name="privacy" required>
                                    <label class="form-check-label" for="privacy">
                                        J'accepte la <a href="{{ route('privacy') }}" target="_blank">politique de confidentialité</a> 
                                        et le traitement de mes données personnelles. <span class="text-danger">*</span>
                                    </label>
                                    <div class="invalid-feedback">
                                        Vous devez accepter la politique de confidentialité.
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Envoyer le Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="map-container">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15948.234567890123!2d9.4519!3d0.3921!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x107e7e6e1a1a1a1a%3A0x1a1a1a1a1a1a1a1a!2sLibreville%2C%20Gabon!5e0!3m2!1sfr!2sga!4v1234567890123!5m2!1sfr!2sga" 
            width="100%" 
            height="450" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <span class="section-badge">FAQ</span>
                <h2 class="section-title">Questions Fréquentes</h2>
                <p class="section-description">Trouvez rapidement des réponses à vos questions</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <!-- FAQ 1 -->
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                                <i class="fas fa-question-circle me-2"></i>
                                Comment puis-je m'inscrire sur InfiniSchool ?
                            </button>
                        </h3>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                L'inscription sur InfiniSchool est simple et rapide. Cliquez sur le bouton "S'inscrire" 
                                en haut de la page, remplissez le formulaire avec vos informations personnelles, 
                                et validez votre compte via l'email de confirmation. Vous pourrez ensuite accéder 
                                immédiatement à toutes nos formations.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                                <i class="fas fa-question-circle me-2"></i>
                                Les cours sont-ils certifiants ?
                            </button>
                        </h3>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Oui, toutes nos formations délivrent un certificat de réussite une fois que vous avez 
                                complété le cursus et validé les évaluations. Ces certificats sont reconnus et peuvent 
                                être ajoutés à votre CV ou profil LinkedIn pour valoriser vos compétences.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                                <i class="fas fa-question-circle me-2"></i>
                                Quels sont les moyens de paiement acceptés ?
                            </button>
                        </h3>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Nous acceptons les cartes bancaires (Visa, Mastercard), les virements bancaires, 
                                et le paiement mobile (Mobile Money). Des facilités de paiement en plusieurs fois 
                                sont également disponibles pour certaines formations.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                                <i class="fas fa-question-circle me-2"></i>
                                Puis-je accéder aux cours à tout moment ?
                            </button>
                        </h3>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolument ! Une fois inscrit à une formation, vous avez un accès illimité 24h/24 et 7j/7 
                                aux contenus de cours. Les sessions en direct sont programmées, mais les enregistrements 
                                restent disponibles pour que vous puissiez réviser à votre rythme.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                                <i class="fas fa-question-circle me-2"></i>
                                Comment puis-je contacter un formateur ?
                            </button>
                        </h3>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Vous pouvez contacter vos formateurs directement via la messagerie intégrée à la plateforme, 
                                poser vos questions pendant les sessions en direct, ou utiliser le forum de discussion 
                                dédié à chaque formation. Nos formateurs sont engagés à répondre dans les 24-48 heures.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- More Questions CTA -->
                <div class="text-center mt-4">
                    <p class="text-muted mb-3">Vous ne trouvez pas la réponse à votre question ?</p>
                    <a href="{{ route('help') }}" class="btn btn-outline-primary">
                        <i class="fas fa-life-ring me-2"></i>Consulter le Centre d'Aide
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('styles')
<style>
    /* ========================================
       PAGE HEADER
    ======================================== */
    .page-header-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        padding: 80px 0 60px;
        position: relative;
        overflow: hidden;
    }

    .page-header-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
    }

    .page-header-title {
        color: white;
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 15px;
        position: relative;
    }

    .page-header-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.3rem;
        margin-bottom: 25px;
        position: relative;
    }

    .page-header-section .breadcrumb {
        background: transparent;
        position: relative;
    }

    .page-header-section .breadcrumb-item,
    .page-header-section .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
    }

    .page-header-section .breadcrumb-item.active {
        color: white;
    }

    /* ========================================
       CONTACT INFO
    ======================================== */
    .contact-info-wrapper {
        background: white;
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        height: 100%;
    }

    .contact-info-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .contact-info-item {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #e9ecef;
    }

    .contact-info-item:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .contact-info-icon {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        background: rgba(128, 0, 32, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: var(--primary-color);
    }

    .contact-info-content h5 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-dark);
    }

    .contact-info-content p {
        color: var(--text-light);
        margin: 0;
        line-height: 1.6;
    }

    .contact-info-content a {
        color: var(--text-light);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .contact-info-content a:hover {
        color: var(--primary-color);
    }

    /* Social Links */
    .contact-social h5 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .social-links {
        display: flex;
        gap: 10px;
    }

    .social-link {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .social-link:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-3px);
    }

    /* ========================================
       CONTACT FORM
    ======================================== */
    .contact-form-wrapper {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .contact-form-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .input-group {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        z-index: 5;
    }

    .form-control.with-icon {
        padding-left: 45px;
    }

    .form-control,
    .form-select {
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 32, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    /* Form Validation */
    .was-validated .form-control:invalid,
    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .was-validated .form-control:valid,
    .form-control.is-valid {
        border-color: #28a745;
    }

    .invalid-feedback {
        font-size: 0.875rem;
        margin-top: 5px;
    }

    /* ========================================
       MAP SECTION
    ======================================== */
    .map-section {
        margin-top: -30px;
    }

    .map-container {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .map-container iframe {
        display: block;
    }

    /* ========================================
       FAQ SECTION
    ======================================== */
    .section-badge {
        display: inline-block;
        background: rgba(128, 0, 32, 0.1);
        color: var(--primary-color);
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 15px;
        color: var(--text-dark);
    }

    .section-description {
        font-size: 1.1rem;
        color: var(--text-light);
    }

    .accordion-item {
        border: none;
        background: white;
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .accordion-button {
        background: white;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 1.05rem;
        padding: 20px 25px;
        border: none;
        box-shadow: none;
    }

    .accordion-button:not(.collapsed) {
        background: var(--primary-color);
        color: white;
    }

    .accordion-button:focus {
        box-shadow: none;
        border: none;
    }

    .accordion-button::after {
        background-image: none;
        content: '\f078';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 0.9rem;
    }

    .accordion-button:not(.collapsed)::after {
        content: '\f077';
    }

    .accordion-body {
        padding: 25px;
        color: var(--text-light);
        line-height: 1.8;
        font-size: 1rem;
    }

    /* ========================================
       RESPONSIVE
    ======================================== */
    @media (max-width: 992px) {
        .page-header-title {
            font-size: 2.5rem;
        }

        .contact-form-wrapper,
        .contact-info-wrapper {
            padding: 30px 25px;
        }

        .section-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 576px) {
        .page-header-title {
            font-size: 2rem;
        }

        .contact-form-wrapper,
        .contact-info-wrapper {
            padding: 25px 20px;
        }

        .contact-info-item {
            flex-direction: column;
            text-align: center;
        }

        .social-links {
            justify-content: center;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });

        // Message length validation
        const messageField = document.getElementById('message');
        if (messageField) {
            messageField.addEventListener('input', function() {
                if (this.value.length < 10) {
                    this.setCustomValidity('Le message doit contenir au moins 10 caractères.');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    });
</script>
@endsection