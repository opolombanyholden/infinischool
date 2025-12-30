<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si pas connecté, laisser passer (géré par auth middleware)
        if (!$user) {
            return $next($request);
        }

        // Admin passe toujours
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Vérifier le statut d'inscription
        if ($user->registration_status === 'pending') {
            return redirect()->route('registration.pending')
                ->with('warning', 'Votre inscription est en attente de validation.');
        }

        // Vérifier si l'inscription a été refusée
        if ($user->registration_status === 'rejected') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Votre inscription a été refusée. Contactez-nous pour plus d\'informations.');
        }

        // Vérifier que le compte est actif
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte n\'est pas actif.');
        }

        return $next($request);
    }
}
