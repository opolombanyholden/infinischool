@extends('layouts.app')

@section('title', 'Politique de Confidentialité - InfiniSchool')
@section('description', 'Politique de confidentialité et protection des données personnelles d\'InfiniSchool.')

@section('content')

<!-- Page Header -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-header-title">Politique de Confidentialité</h1>
                <p class="page-header-subtitle">
                    Protection de vos données personnelles
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Confidentialité</li>
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
                        <h2>1. Introduction</h2>
                        <p>
                            InfiniSchool s'engage à protéger la vie privée de ses utilisateurs. Cette politique de 
                            confidentialité explique comment nous collectons, utilisons et protégeons vos données personnelles.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>2. Données collectées</h2>
                        <p>Nous collectons les données suivantes :</p>
                        <ul>
                            <li><strong>Données d'identification :</strong> nom, prénom, adresse email, numéro de téléphone</li>
                            <li><strong>Données de connexion :</strong> adresse IP, type de navigateur, pages visitées</li>
                            <li><strong>Données de formation :</strong> formations suivies, progression, notes obtenues</li>
                            <li><strong>Données de paiement :</strong> informations de facturation (les données bancaires sont traitées par nos prestataires de paiement sécurisés)</li>
                        </ul>
                    </div>

                    <div class="legal-block">
                        <h2>3. Finalités du traitement</h2>
                        <p>Vos données sont utilisées pour :</p>
                        <ul>
                            <li>Gérer votre compte utilisateur</li>
                            <li>Fournir les services de formation</li>
                            <li>Traiter vos paiements</li>
                            <li>Vous envoyer des informations sur nos formations</li>
                            <li>Améliorer nos services</li>
                            <li>Respecter nos obligations légales</li>
                        </ul>
                    </div>

                    <div class="legal-block">
                        <h2>4. Base légale</h2>
                        <p>Le traitement de vos données repose sur :</p>
                        <ul>
                            <li>L'exécution du contrat de formation</li>
                            <li>Votre consentement (pour les communications marketing)</li>
                            <li>Nos intérêts légitimes (amélioration des services)</li>
                            <li>Le respect de nos obligations légales</li>
                        </ul>
                    </div>

                    <div class="legal-block">
                        <h2>5. Durée de conservation</h2>
                        <p>
                            Nous conservons vos données pendant la durée de votre inscription et pendant une période 
                            de 3 ans après votre dernière activité sur la plateforme. Les données de facturation 
                            sont conservées pendant 10 ans conformément aux obligations comptables.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>6. Partage des données</h2>
                        <p>Vos données peuvent être partagées avec :</p>
                        <ul>
                            <li>Nos formateurs (uniquement les données nécessaires au suivi pédagogique)</li>
                            <li>Nos prestataires de paiement</li>
                            <li>Nos hébergeurs et prestataires techniques</li>
                            <li>Les autorités compétentes en cas d'obligation légale</li>
                        </ul>
                        <p>Nous ne vendons jamais vos données à des tiers.</p>
                    </div>

                    <div class="legal-block">
                        <h2>7. Sécurité</h2>
                        <p>
                            Nous mettons en œuvre des mesures de sécurité appropriées pour protéger vos données :
                        </p>
                        <ul>
                            <li>Chiffrement SSL/TLS pour toutes les communications</li>
                            <li>Stockage sécurisé des mots de passe (hashage)</li>
                            <li>Accès restreint aux données personnelles</li>
                            <li>Surveillance et mise à jour régulière de nos systèmes</li>
                        </ul>
                    </div>

                    <div class="legal-block">
                        <h2>8. Vos droits</h2>
                        <p>Conformément à la réglementation applicable, vous disposez des droits suivants :</p>
                        <ul>
                            <li><strong>Droit d'accès :</strong> obtenir une copie de vos données</li>
                            <li><strong>Droit de rectification :</strong> corriger vos données inexactes</li>
                            <li><strong>Droit à l'effacement :</strong> supprimer vos données</li>
                            <li><strong>Droit à la portabilité :</strong> récupérer vos données dans un format structuré</li>
                            <li><strong>Droit d'opposition :</strong> vous opposer au traitement de vos données</li>
                            <li><strong>Droit de retrait du consentement :</strong> retirer votre consentement à tout moment</li>
                        </ul>
                        <p>
                            Pour exercer ces droits, contactez-nous à : privacy@infinischool.com
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>9. Cookies</h2>
                        <p>
                            Notre site utilise des cookies pour améliorer votre expérience. Vous pouvez gérer vos 
                            préférences de cookies via les paramètres de votre navigateur.
                        </p>
                    </div>

                    <div class="legal-block">
                        <h2>10. Contact</h2>
                        <p>
                            Pour toute question concernant cette politique ou vos données personnelles :
                        </p>
                        <ul>
                            <li>Email : privacy@infinischool.com</li>
                            <li>Adresse : Boulevard Triomphal, Libreville, Gabon</li>
                        </ul>
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
