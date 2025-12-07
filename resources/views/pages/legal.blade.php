@extends('layouts.app')

@section('title', 'Mentions Légales - InfiniSchool')
@section('description', 'Mentions légales d\'InfiniSchool - Informations sur l\'éditeur et l\'hébergeur du site.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Mentions Légales</h1>
                <p class="page-header-subtitle">
                    Informations légales obligatoires
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Mentions légales</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="legal-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="legal-content">
                    
                    <div class="legal-block">
                        <h2>1. Éditeur du site</h2>
                        <p>Le site InfiniSchool est édité par :</p>
                        <ul>
                            <li><strong>Raison sociale :</strong> InfiniSchool SARL</li>
                            <li><strong>Siège social :</strong> Boulevard Triomphal, Libreville, Gabon</li>
                            <li><strong>Capital social :</strong> 5 000 000 FCFA</li>
                            <li><strong>RCCM :</strong> GA-LBV-01-2020-B12-00XXX</li>
                            <li><strong>NIF :</strong> XXXXX</li>
                            <li><strong>Téléphone :</strong> +241 01 12 34 56 7</li>
                            <li><strong>Email :</strong> contact@infinischool.com</li>
                        </ul>
                    </div>

                    <div class="legal-block">
                        <h2>2. Directeur de la publication</h2>
                        <p>
                            Le directeur de la publication est M./Mme [Nom du Directeur], en qualité de Gérant 
                            de la société InfiniSchool SARL.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>3. Hébergement</h2>
                        <p>Le site est hébergé par :</p>
                        <ul>
                            <li><strong>Société :</strong> [Nom de l'hébergeur]</li>
                            <li><strong>Adresse :</strong> [Adresse de l'hébergeur]</li>
                            <li><strong>Téléphone :</strong> [Téléphone de l'hébergeur]</li>
                        </ul>
                    </div>

                    <div class="legal-block">
                        <h2>4. Propriété intellectuelle</h2>
                        <p>
                            L'ensemble du contenu du site InfiniSchool (textes, images, vidéos, logos, graphismes, 
                            icônes, etc.) est protégé par les lois relatives à la propriété intellectuelle.
                        </p>
                        <p>
                            Toute reproduction, représentation, modification, publication, transmission ou dénaturation 
                            totale ou partielle du site ou de son contenu, par quelque procédé que ce soit, et sur 
                            quelque support que ce soit, est interdite sans l'autorisation écrite préalable d'InfiniSchool.
                        </p>
                        <p>
                            Les marques et logos figurant sur le site sont des marques déposées. Toute reproduction 
                            totale ou partielle de ces marques sans autorisation est prohibée.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>5. Liens hypertextes</h2>
                        <p>
                            Le site InfiniSchool peut contenir des liens hypertextes vers d'autres sites. 
                            InfiniSchool n'exerce aucun contrôle sur ces sites et décline toute responsabilité 
                            quant à leur contenu.
                        </p>
                        <p>
                            La création de liens vers le site InfiniSchool est soumise à autorisation préalable 
                            de l'éditeur.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>6. Limitation de responsabilité</h2>
                        <p>
                            InfiniSchool s'efforce d'assurer l'exactitude et la mise à jour des informations 
                            diffusées sur son site. Toutefois, elle ne peut garantir l'exactitude, la précision 
                            ou l'exhaustivité des informations mises à disposition.
                        </p>
                        <p>
                            InfiniSchool décline toute responsabilité pour toute imprécision, inexactitude ou 
                            omission portant sur des informations disponibles sur le site.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>7. Données personnelles</h2>
                        <p>
                            La collecte et le traitement des données personnelles sont effectués conformément à 
                            notre <a href="{{ route('privacy') }}" class="text-primary">Politique de Confidentialité</a>.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>8. Droit applicable</h2>
                        <p>
                            Les présentes mentions légales sont régies par le droit gabonais. En cas de litige, 
                            les tribunaux de Libreville seront seuls compétents.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>9. Crédits</h2>
                        <p>
                            <strong>Conception et développement :</strong> Équipe technique InfiniSchool<br>
                            <strong>Iconographie :</strong> Font Awesome, Unsplash<br>
                            <strong>Polices :</strong> Google Fonts
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>10. Contact</h2>
                        <p>
                            Pour toute question relative aux présentes mentions légales :
                        </p>
                        <div class="contact-info mt-3">
                            <p>
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:legal@infinischool.com">legal@infinischool.com</a>
                            </p>
                            <p>
                                <i class="fas fa-phone text-primary me-2"></i>
                                +241 01 12 34 56 7
                            </p>
                            <p>
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                Boulevard Triomphal, Libreville, Gabon
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('styles')
<style>
    :root {
        --primary-color: #800020;
        --primary-dark: #5c0017;
        --text-dark: #1a1a2e;
        --text-light: #6c757d;
    }

    .page-header-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        padding: 80px 0;
    }

    .page-header-title {
        font-size: 3rem;
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
    }

    .page-header-subtitle {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .breadcrumb {
        background: transparent;
        margin: 0;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: white;
    }

    .legal-content {
        background: white;
        padding: 50px;
        border-radius: 20px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
    }

    .legal-block {
        margin-bottom: 40px;
    }

    .legal-block h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .legal-block p {
        color: var(--text-light);
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .legal-block ul {
        color: var(--text-light);
        line-height: 2;
        padding-left: 20px;
    }

    .legal-block ul li {
        margin-bottom: 8px;
    }

    .contact-info p {
        margin-bottom: 10px;
    }

    .contact-info a {
        color: var(--text-light);
        text-decoration: none;
    }

    .contact-info a:hover {
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .page-header-title {
            font-size: 2rem;
        }

        .legal-content {
            padding: 30px 20px;
        }
    }
</style>
@endsection
