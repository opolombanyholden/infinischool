<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialController extends Controller
{
    /**
     * Les providers OAuth supportés
     */
    protected array $providers = ['google', 'linkedin', 'facebook', 'github'];

    /**
     * Rediriger l'utilisateur vers le provider OAuth
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect(string $provider)
    {
        // Vérifier que le provider est supporté
        if (!in_array($provider, $this->providers)) {
            return redirect()->route('login')
                ->with('error', 'Ce fournisseur d\'authentification n\'est pas supporté.');
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Erreur lors de la connexion avec ' . ucfirst($provider) . '. Veuillez réessayer.');
        }
    }

    /**
     * Gérer le callback du provider OAuth
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(string $provider)
    {
        // Vérifier que le provider est supporté
        if (!in_array($provider, $this->providers)) {
            return redirect()->route('login')
                ->with('error', 'Ce fournisseur d\'authentification n\'est pas supporté.');
        }

        try {
            // Récupérer les informations de l'utilisateur depuis le provider
            $socialUser = Socialite::driver($provider)->user();

            // Chercher ou créer l'utilisateur
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Vérifier si l'utilisateur est actif
            if (!$user->is_active) {
                return redirect()->route('login')
                    ->with('error', 'Votre compte a été désactivé. Veuillez contacter le support.');
            }

            // Connecter l'utilisateur
            Auth::login($user, true);

            // Mettre à jour la dernière connexion
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            // Rediriger vers le dashboard approprié selon le rôle
            return $this->redirectToDashboard($user);

        } catch (Exception $e) {
            \Log::error('Social Auth Error: ' . $e->getMessage(), [
                'provider' => $provider,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with('error', 'Une erreur est survenue lors de la connexion. Veuillez réessayer.');
        }
    }

    /**
     * Trouver ou créer un utilisateur à partir des données du provider
     *
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @param string $provider
     * @return \App\Models\User
     */
    protected function findOrCreateUser($socialUser, string $provider): User
    {
        // Chercher par provider_id
        $user = User::where('provider', $provider)
                    ->where('provider_id', $socialUser->getId())
                    ->first();

        if ($user) {
            // Mettre à jour les informations si nécessaire
            $user->update([
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
            ]);
            return $user;
        }

        // Chercher par email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Lier le compte social au compte existant
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
            return $user;
        }

        // Créer un nouvel utilisateur
        return $this->createUser($socialUser, $provider);
    }

    /**
     * Créer un nouvel utilisateur
     *
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @param string $provider
     * @return \App\Models\User
     */
    protected function createUser($socialUser, string $provider): User
    {
        // Extraire le prénom et le nom
        $name = $socialUser->getName() ?? '';
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        // Si pas de nom séparé, essayer avec getNickname
        if (empty($firstName)) {
            $firstName = $socialUser->getNickname() ?? 'Utilisateur';
        }

        return User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $name ?: $firstName,
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(24)),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'role' => 'student', // Par défaut, les nouveaux utilisateurs sont étudiants
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(), // Email vérifié via OAuth
        ]);
    }

    /**
     * Rediriger vers le dashboard approprié selon le rôle
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToDashboard(User $user)
    {
        $message = 'Bienvenue, ' . $user->first_name . ' ! Vous êtes connecté.';

        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard')
                    ->with('success', $message);

            case 'teacher':
                // Vérifier si l'enseignant est approuvé
                if (!$user->is_approved) {
                    return redirect()->route('teacher.dashboard')
                        ->with('warning', 'Votre compte enseignant est en attente d\'approbation.');
                }
                return redirect()->route('teacher.dashboard')
                    ->with('success', $message);

            case 'student':
            default:
                return redirect()->route('student.dashboard')
                    ->with('success', $message);
        }
    }
}