<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:student,teacher,admin',
        ]);

        // Extraire le rôle pour la vérification
        $role = $credentials['role'];
        unset($credentials['role']);

        // Tentative de connexion
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Vérifier le rôle
            if ($user->role !== $role) {
                Auth::logout();
                return back()->withErrors([
                    'role' => 'Le rôle sélectionné ne correspond pas à votre compte.',
                ])->withInput($request->only('email'));
            }

            // Vérifier si le compte est actif
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Votre compte est désactivé. Contactez l\'administration.',
                ])->withInput($request->only('email'));
            }

            // Régénérer la session
            $request->session()->regenerate();

            // Redirection selon le rôle
            return $this->redirectToDashboard($user->role);
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->withInput($request->only('email'));
    }

    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Traiter l'inscription (étudiants uniquement)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
            'accept_terms' => 'required|accepted',
        ]);

        // Créer l'utilisateur étudiant
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'is_active' => true,
        ]);

        // Connexion automatique
        Auth::login($user);

        return redirect()->route('student.dashboard')
            ->with('success', 'Bienvenue sur InfiniSchool ! Votre compte a été créé avec succès.');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Afficher le formulaire de mot de passe oublié
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Envoyer le lien de réinitialisation
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // TODO: Implémenter l'envoi d'email avec token
        // Password::sendResetLink($request->only('email'));

        return back()->with('success', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
    }

    /**
     * Afficher le formulaire de réinitialisation
     */
    public function showResetPassword($token)
    {
        return view('auth.reset-password', compact('token'));
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // TODO: Implémenter la logique de réinitialisation avec token
        // Password::reset($validated, function ($user, $password) {
        //     $user->password = Hash::make($password);
        //     $user->save();
        // });

        return redirect()->route('login')
            ->with('success', 'Votre mot de passe a été réinitialisé avec succès.');
    }

    /**
     * Afficher le formulaire d'inscription enseignant
     */
    public function showTeacherRegister()
    {
        return view('auth.teacher-register');
    }

    /**
     * Traiter l'inscription enseignant (validation admin requise)
     */
    public function registerTeacher(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'specialization' => 'required|string|max:255',
            'experience_years' => 'required|integer|min:0',
            'bio' => 'required|string|max:1000',
            'cv' => 'required|file|mimes:pdf|max:2048',
            'certifications' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // Upload des fichiers
        $cvPath = $request->file('cv')->store('teacher-applications/cv', 'public');
        $certificationsPath = $request->hasFile('certifications')
            ? $request->file('certifications')->store('teacher-applications/certifications', 'public')
            : null;

        // Créer l'utilisateur enseignant (désactivé par défaut)
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
            'is_active' => false, // Nécessite validation admin
            'bio' => $validated['bio'],
            'specialization' => $validated['specialization'],
            'experience_years' => $validated['experience_years'],
            'cv_path' => $cvPath,
            'certifications_path' => $certificationsPath,
        ]);

        // TODO: Notifier les admins de la nouvelle candidature

        return redirect()->route('login')
            ->with('success', 'Votre candidature a été soumise avec succès. Un administrateur examinera votre dossier sous 48h.');
    }

    /**
     * Rediriger vers le dashboard approprié selon le rôle
     */
    protected function redirectToDashboard($role)
    {
        return match($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('home'),
        };
    }

    /**
     * Vérifier l'email (si activation par email)
     */
    public function verifyEmail($token)
    {
        // TODO: Implémenter la vérification d'email
        $user = User::where('verification_token', $token)->firstOrFail();
        
        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();

        return redirect()->route('login')
            ->with('success', 'Votre email a été vérifié avec succès !');
    }
}