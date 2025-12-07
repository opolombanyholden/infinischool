<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Formation;
use App\Models\Enrollment;
use App\Models\Certificate;
use Illuminate\Http\Request;

/**
 * PageController
 * 
 * Gère les pages statiques et informatives du site
 * About, Teachers, Contact, FAQ, Legal pages, etc.
 * 
 * @package App\Http\Controllers
 */
class PageController extends Controller
{
    /**
     * Page À propos
     * 
     * @return \Illuminate\View\View
     */
    public function about()
    {
        // Statistiques pour la page À propos
        $stats = [
            'total_students' => User::where('role', 'student')->count() ?: 1250,
            'total_teachers' => User::where('role', 'teacher')->count() ?: 45,
            'total_formations' => Formation::where('status', 'published')->count() ?: 50,
            'total_certificates' => Certificate::count() ?: 890,
            'success_rate' => 95,
        ];

        return view('pages.about', compact('stats'));
    }

    /**
     * Page Enseignants / Formateurs
     * 
     * @return \Illuminate\View\View
     */
    public function teachers()
    {
        // Récupérer les enseignants actifs avec leurs statistiques
        $teachers = User::where('role', 'teacher')
            ->where('status', 'active')
            ->withCount([
                'teachingClasses as courses_count',
                'teachingClasses as students_count' => function($query) {
                    $query->withCount('enrollments');
                }
            ])
            ->orderBy('name')
            ->get();

        // Si aucun enseignant, créer une collection vide
        if ($teachers->isEmpty()) {
            $teachers = collect();
        }

        return view('pages.teachers', compact('teachers'));
    }

    /**
     * Page Contact
     * 
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Page FAQ
     * 
     * @return \Illuminate\View\View
     */
    public function faq()
    {
        // FAQ organisées par catégories
        $faqs = [
            'general' => [
                [
                    'question' => 'Qu\'est-ce qu\'InfiniSchool ?',
                    'answer' => 'InfiniSchool est une plateforme d\'e-learning innovante qui propose des formations en direct avec des formateurs experts. Nous offrons une expérience d\'apprentissage interactive et personnalisée.'
                ],
                [
                    'question' => 'Comment fonctionnent les cours en direct ?',
                    'answer' => 'Nos cours en direct sont dispensés via Zoom. Une fois inscrit à une formation, vous recevrez les liens de connexion pour chaque session. Vous pouvez interagir en temps réel avec votre formateur et les autres participants.'
                ],
                [
                    'question' => 'Les cours sont-ils enregistrés ?',
                    'answer' => 'Oui, tous nos cours en direct sont enregistrés et mis à disposition dans votre espace étudiant pour que vous puissiez les revoir à tout moment.'
                ],
            ],
            'inscription' => [
                [
                    'question' => 'Comment m\'inscrire à une formation ?',
                    'answer' => 'Créez un compte gratuit, parcourez notre catalogue de formations, choisissez celle qui vous intéresse et procédez au paiement. Vous serez automatiquement inscrit dans la prochaine session disponible.'
                ],
                [
                    'question' => 'Quels sont les modes de paiement acceptés ?',
                    'answer' => 'Nous acceptons les paiements par carte bancaire, mobile money (Airtel Money, Moov Money), et virement bancaire.'
                ],
                [
                    'question' => 'Puis-je annuler mon inscription ?',
                    'answer' => 'Vous pouvez demander un remboursement dans les 7 jours suivant votre inscription si vous n\'avez pas encore commencé la formation. Contactez notre support pour plus d\'informations.'
                ],
            ],
            'certificats' => [
                [
                    'question' => 'Comment obtenir mon certificat ?',
                    'answer' => 'Une fois que vous avez terminé tous les modules de votre formation et réussi l\'évaluation finale avec un minimum de 70%, votre certificat sera automatiquement généré et disponible en téléchargement.'
                ],
                [
                    'question' => 'Les certificats sont-ils reconnus ?',
                    'answer' => 'Nos certificats attestent de vos compétences et sont reconnus par de nombreuses entreprises partenaires. Chaque certificat dispose d\'un code de vérification unique.'
                ],
            ],
            'technique' => [
                [
                    'question' => 'Quels équipements sont nécessaires ?',
                    'answer' => 'Vous avez besoin d\'un ordinateur ou tablette avec une connexion internet stable, un navigateur web récent, et idéalement un casque avec micro pour les sessions en direct.'
                ],
                [
                    'question' => 'J\'ai un problème technique, que faire ?',
                    'answer' => 'Contactez notre support technique via le formulaire de contact ou par email à support@infinischool.com. Notre équipe répond généralement sous 24h.'
                ],
            ],
        ];

        return view('pages.faq', compact('faqs'));
    }

    /**
     * Page Blog (liste des articles)
     * 
     * @return \Illuminate\View\View
     */
    public function blog()
    {
        // Pour l'instant, page statique
        // TODO: Implémenter le système de blog avec modèle Article
        $posts = collect(); // Collection vide pour l'instant

        return view('pages.blog', compact('posts'));
    }

    /**
     * Page Conditions d'utilisation
     * 
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Page Politique de confidentialité
     * 
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Page Mentions légales
     * 
     * @return \Illuminate\View\View
     */
    public function legal()
    {
        return view('pages.legal');
    }
}
