<footer class="footer bg-dark text-light pt-5 pb-3 mt-5">
    <div class="container">
        <!-- Main Footer Content -->
        <div class="row g-4">
            <!-- Column 1: About -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-section">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-graduation-cap me-2" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <h4 class="mb-0">
                            <span style="color: var(--primary-color); font-weight: 700;">Infini</span>
                            <span style="color: white; font-weight: 700;">School</span>
                        </h4>
                    </div>
                    <p class="text-light-gray mb-3">
                        Plateforme d'apprentissage en ligne moderne avec cours en direct et classes virtuelles. 
                        Apprenez à votre rythme avec nos enseignants qualifiés.
                    </p>
                    
                    <!-- Social Media Links -->
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

            <!-- Column 2: Quick Links -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-section">
                    <h5 class="footer-title mb-3">Liens Rapides</h5>
                    <ul class="footer-links list-unstyled">
                        <li><a href="{{ route('home') }}"><i class="fas fa-chevron-right me-2"></i>Accueil</a></li>
                        <li><a href="{{ route('formations.index') }}"><i class="fas fa-chevron-right me-2"></i>Formations</a></li>
                        <li><a href="{{ route('teachers') }}"><i class="fas fa-chevron-right me-2"></i>Enseignants</a></li>
                        <li><a href="{{ route('about') }}"><i class="fas fa-chevron-right me-2"></i>À propos</a></li>
                        <li><a href="{{ route('contact') }}"><i class="fas fa-chevron-right me-2"></i>Contact</a></li>
                    </ul>
                </div>
            </div>

            <!-- Column 3: Support & Legal -->
            <div class="col-lg-3 col-md-6">
                <div class="footer-section">
                    <h5 class="footer-title mb-3">Support & Légal</h5>
                    <ul class="footer-links list-unstyled">
                        <li><a href="{{ route('faq') }}"><i class="fas fa-chevron-right me-2"></i>FAQ</a></li>
                        <li><a href="{{ route('help') }}"><i class="fas fa-chevron-right me-2"></i>Centre d'aide</a></li>
                        <li><a href="{{ route('terms') }}"><i class="fas fa-chevron-right me-2"></i>Conditions Générales</a></li>
                        <li><a href="{{ route('privacy') }}"><i class="fas fa-chevron-right me-2"></i>Politique de Confidentialité</a></li>
                        <li><a href="{{ route('legal') }}"><i class="fas fa-chevron-right me-2"></i>Mentions Légales</a></li>
                    </ul>
                </div>
            </div>

            <!-- Column 4: Newsletter -->
            <div class="col-lg-3 col-md-6">
                <div class="footer-section">
                    <h5 class="footer-title mb-3">Newsletter</h5>
                    <p class="text-light-gray mb-3">
                        Recevez nos dernières actualités et offres exclusives.
                    </p>
                    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Votre email" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Contact Info -->
                    <div class="contact-info mt-4">
                        <div class="d-flex align-items-start mb-2">
                            <i class="fas fa-envelope me-2 mt-1 text-primary"></i>
                            <a href="mailto:contact@infinischool.com" class="text-light-gray">contact@infinischool.com</a>
                        </div>
                        <div class="d-flex align-items-start mb-2">
                            <i class="fas fa-phone me-2 mt-1 text-primary"></i>
                            <a href="tel:+241011234567" class="text-light-gray">+241 01 12 34 56 7</a>
                        </div>
                        <div class="d-flex align-items-start">
                            <i class="fas fa-map-marker-alt me-2 mt-1 text-primary"></i>
                            <span class="text-light-gray">Libreville, Gabon</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4 border-secondary opacity-25">

        <!-- Bottom Footer -->
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-light-gray">
                    &copy; {{ date('Y') }} <strong class="text-primary">InfiniSchool</strong>. Tous droits réservés.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <p class="mb-0 text-light-gray">
                    Développé avec <i class="fas fa-heart text-danger"></i> par 
                    <strong class="text-primary">Yubile</strong>
                </p>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="btn btn-primary back-to-top" aria-label="Retour en haut">
        <i class="fas fa-arrow-up"></i>
    </button>
</footer>

<style>
    /* Footer Styling */
    .footer {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        position: relative;
        overflow: hidden;
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-light) 100%);
    }

    .text-light-gray {
        color: #b8b8b8;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* Footer Sections */
    .footer-section {
        animation: fadeInUp 0.6s ease-out;
    }

    .footer-title {
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
        position: relative;
        padding-bottom: 10px;
        margin-bottom: 20px !important;
    }

    .footer-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary-color);
        border-radius: 2px;
    }

    /* Footer Links */
    .footer-links li {
        margin-bottom: 10px;
        transition: transform 0.2s ease;
    }

    .footer-links li:hover {
        transform: translateX(5px);
    }

    .footer-links a {
        color: #b8b8b8;
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.3s ease;
        display: flex;
        align-items: center;
    }

    .footer-links a:hover {
        color: var(--primary-color);
    }

    .footer-links a i {
        font-size: 0.7rem;
        transition: transform 0.3s ease;
    }

    .footer-links a:hover i {
        transform: translateX(3px);
    }

    /* Social Links */
    .social-links {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .social-link {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .social-link:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(128, 0, 32, 0.4);
    }

    /* Newsletter Form */
    .newsletter-form .form-control {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.7rem 1rem;
        border-radius: 8px 0 0 8px;
    }

    .newsletter-form .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .newsletter-form .form-control:focus {
        background-color: rgba(255, 255, 255, 0.15);
        border-color: var(--primary-color);
        color: white;
        box-shadow: none;
    }

    .newsletter-form .btn {
        border-radius: 0 8px 8px 0;
        padding: 0.7rem 1.2rem;
    }

    /* Contact Info */
    .contact-info a {
        color: #b8b8b8;
        text-decoration: none;
        transition: color 0.3s ease;
        font-size: 0.95rem;
    }

    .contact-info a:hover {
        color: var(--primary-color);
    }

    /* Back to Top Button */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 999;
        box-shadow: 0 5px 20px rgba(128, 0, 32, 0.3);
    }

    .back-to-top.show {
        display: flex;
        opacity: 1;
        transform: translateY(0);
    }

    .back-to-top:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(128, 0, 32, 0.5);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .footer {
            text-align: center;
        }

        .footer-title::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .social-links {
            justify-content: center;
        }

        .footer-links a {
            justify-content: center;
        }

        .contact-info > div {
            justify-content: center;
        }

        .back-to-top {
            bottom: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
        }
    }

    /* HR Divider */
    .footer hr {
        margin: 2rem 0;
        opacity: 0.1;
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Back to Top Button
        const backToTop = document.getElementById('back-to-top');
        
        if (backToTop) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            });

            backToTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Newsletter form handling
        const newsletterForm = document.querySelector('.newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                // Form will be handled by Laravel
                // You can add additional client-side validation here if needed
            });
        }
    });
</script>