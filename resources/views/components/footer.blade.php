<!-- Footer -->
<footer class="bg-dark text-white mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <!-- Colonne 1 : À propos -->
            <div class="col-12 col-md-6 col-lg-3">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-graduation-cap me-2"></i>InfiniSchool
                </h5>
                <p class="text-white-50 small">
                    La plateforme e-learning qui révolutionne l'apprentissage en ligne avec des cours en direct et des formations de qualité.
                </p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            <!-- Colonne 2 : Liens rapides -->
            <div class="col-12 col-md-6 col-lg-3">
                <h6 class="fw-bold mb-3">Liens rapides</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('home') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>Accueil
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('about') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>À propos
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('formations.index') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>Formations
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('teachers') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>Enseignants
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('blog') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>Blog
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('contact') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>Contact
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Colonne 3 : Support -->
            <div class="col-12 col-md-6 col-lg-3">
                <h6 class="fw-bold mb-3">Support</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('faq') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>FAQ
                        </a>
                    </li>
                    <li class="mb-2">
                        @auth
                            <a href="{{ route('support.index') }}" class="text-white-50 text-decoration-none hover-link">
                                <i class="fas fa-chevron-right fa-xs me-2"></i>Centre d'aide
                            </a>
                        @else
                            <a href="{{ route('contact') }}" class="text-white-50 text-decoration-none hover-link">
                                <i class="fas fa-chevron-right fa-xs me-2"></i>Centre d'aide
                            </a>
                        @endauth
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('terms') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>Conditions d'utilisation
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('privacy') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>Politique de confidentialité
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('legal') }}" class="text-white-50 text-decoration-none hover-link">
                            <i class="fas fa-chevron-right fa-xs me-2"></i>Mentions légales
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Colonne 4 : Contact -->
            <div class="col-12 col-md-6 col-lg-3">
                <h6 class="fw-bold mb-3">Contactez-nous</h6>
                <ul class="list-unstyled">
                    <li class="mb-3 text-white-50 small">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Libreville, Gabon
                    </li>
                    <li class="mb-3 text-white-50 small">
                        <i class="fas fa-phone me-2"></i>
                        <a href="tel:+24100000000" class="text-white-50 text-decoration-none hover-link">
                            +241 00 00 00 00
                        </a>
                    </li>
                    <li class="mb-3 text-white-50 small">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:contact@infinischool.com" class="text-white-50 text-decoration-none hover-link">
                            contact@infinischool.com
                        </a>
                    </li>
                </ul>
                
                <!-- Newsletter -->
                <div class="mt-4">
                    <h6 class="fw-bold mb-3 small">Newsletter</h6>
                    <form action="{{ route('contact.send') }}" method="POST" class="input-group input-group-sm">
                        @csrf
                        <input type="hidden" name="type" value="newsletter">
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="Votre email"
                               required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="border-top border-secondary">
        <div class="container py-3">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <small class="text-white-50">
                        &copy; {{ date('Y') }} InfiniSchool. Tous droits réservés.
                    </small>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <small class="text-white-50">
                        Développé avec <i class="fas fa-heart text-danger"></i> au Gabon
                    </small>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* Effet hover sur les liens */
.hover-link {
    transition: all 0.3s ease;
}

.hover-link:hover {
    color: #fff !important;
    padding-left: 5px;
}

/* Style boutons sociaux */
.btn-outline-light:hover {
    background-color: #800020;
    border-color: #800020;
}
</style>