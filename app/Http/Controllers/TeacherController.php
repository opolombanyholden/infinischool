<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * TeacherController
 * 
 * Gère l'affichage des profils enseignants publics et les candidatures
 * 
 * @package App\Http\Controllers
 */
class TeacherController extends Controller
{
    /**
     * Afficher le profil public d'un enseignant
     *
     * @param User $teacher
     * @return \Illuminate\View\View
     */
    public function show(User $teacher)
    {
        // Vérifier que c'est bien un enseignant actif et approuvé
        if ($teacher->role !== 'teacher' || !$teacher->is_approved) {
            abort(404);
        }

        // Charger les relations nécessaires
        $teacher->load([
            'teachingClasses' => function($query) {
                $query->where('status', 'active')
                    ->with('formation')
                    ->latest();
            }
        ]);

        // Statistiques de l'enseignant
        $stats = [
            'total_courses' => $teacher->teachingClasses()->count(),
            'total_students' => $this->getTotalStudents($teacher),
            'rating' => $teacher->rating ?? 4.8,
            'reviews_count' => $teacher->reviews_count ?? 0,
            'years_experience' => $teacher->experience_years ?? 5,
        ];

        // Formations enseignées (uniques)
        $formations = $teacher->teachingClasses()
            ->with('formation')
            ->get()
            ->pluck('formation')
            ->filter()
            ->unique('id')
            ->take(6);

        // Avis récents
        $reviews = collect([]); // TODO: Charger depuis la table reviews

        return view('teachers.show', compact('teacher', 'stats', 'formations', 'reviews'));
    }

    /**
     * Afficher le formulaire de candidature enseignant
     *
     * @return \Illuminate\View\View
     */
    public function apply()
    {
        // Si l'utilisateur est déjà connecté et est enseignant, rediriger
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->role === 'teacher') {
                return redirect()->route('teacher.dashboard')
                    ->with('info', 'Vous êtes déjà inscrit en tant qu\'enseignant.');
            }
        }

        // Liste des spécialisations disponibles
        $specializations = [
            'development' => 'Développement Web & Mobile',
            'design' => 'Design & UX/UI',
            'marketing' => 'Marketing Digital',
            'data' => 'Data Science & Analytics',
            'business' => 'Business & Management',
            'languages' => 'Langues',
            'finance' => 'Finance & Comptabilité',
            'health' => 'Santé & Bien-être',
            'other' => 'Autre',
        ];

        return view('teachers.apply', compact('specializations'));
    }

    /**
     * Traiter la soumission d'une candidature enseignant
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitApplication(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'qualifications' => 'required|string|min:20',
            'experience_years' => 'required|integer|min:0|max:50',
            'bio' => 'required|string|min:100|max:2000',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5 Mo max
            'linkedin' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'motivation' => 'required|string|min:50|max:1000',
            'terms_accepted' => 'required|accepted',
        ], [
            'first_name.required' => 'Veuillez entrer votre prénom.',
            'last_name.required' => 'Veuillez entrer votre nom.',
            'email.required' => 'Veuillez entrer votre adresse email.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.required' => 'Veuillez entrer votre numéro de téléphone.',
            'specialization.required' => 'Veuillez sélectionner votre domaine d\'expertise.',
            'qualifications.required' => 'Veuillez décrire vos qualifications.',
            'qualifications.min' => 'Les qualifications doivent contenir au moins 20 caractères.',
            'experience_years.required' => 'Veuillez indiquer vos années d\'expérience.',
            'experience_years.integer' => 'Les années d\'expérience doivent être un nombre.',
            'bio.required' => 'Veuillez rédiger une biographie.',
            'bio.min' => 'La biographie doit contenir au moins 100 caractères.',
            'bio.max' => 'La biographie ne doit pas dépasser 2000 caractères.',
            'cv.required' => 'Veuillez télécharger votre CV.',
            'cv.mimes' => 'Le CV doit être au format PDF, DOC ou DOCX.',
            'cv.max' => 'Le CV ne doit pas dépasser 5 Mo.',
            'motivation.required' => 'Veuillez expliquer vos motivations.',
            'motivation.min' => 'La motivation doit contenir au moins 50 caractères.',
            'terms_accepted.accepted' => 'Vous devez accepter les conditions générales.',
        ]);

        // Sauvegarder le CV
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('teacher-applications', 'public');
        }

        // Créer l'utilisateur avec le rôle teacher en attente d'approbation
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'specialization' => $validated['specialization'],
            'qualifications' => $validated['qualifications'],
            'experience_years' => $validated['experience_years'],
            'bio' => $validated['bio'],
            'cv_path' => $cvPath,
            'linkedin' => $validated['linkedin'] ?? null,
            'website' => $validated['website'] ?? null,
            'motivation' => $validated['motivation'],
            'role' => 'teacher',
            'status' => 'pending', // En attente d'approbation
            'is_approved' => false,
            'password' => Hash::make(Str::random(16)), // Mot de passe temporaire
        ]);

        // TODO: Envoyer un email de confirmation au candidat
        // Mail::to($user->email)->send(new TeacherApplicationReceived($user));

        // TODO: Notifier les admins de la nouvelle candidature
        // Notification::send($admins, new NewTeacherApplication($user));

        // Log de l'activité
        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->log('Nouvelle candidature enseignant soumise');

        return redirect()->route('teachers')
            ->with('success', 'Votre candidature a été soumise avec succès ! Nous examinerons votre profil et vous contacterons par email dans les plus brefs délais.');
    }

    /**
     * Obtenir le nombre total d'étudiants d'un enseignant
     *
     * @param User $teacher
     * @return int
     */
    protected function getTotalStudents(User $teacher): int
    {
        try {
            return $teacher->teachingClasses()
                ->withCount('enrollments')
                ->get()
                ->sum('enrollments_count');
        } catch (\Exception $e) {
            return 0;
        }
    }
}
