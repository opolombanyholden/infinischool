<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * AdminSupportController
 * 
 * Gère les tickets de support utilisateurs
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminSupportController extends Controller
{
    /**
     * Affiche la liste des tickets de support
     */
    public function index(Request $request): View
    {
        $query = Ticket::with(['user', 'assignedTo']);

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par priorité
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filtre par assignation
        if ($request->filled('assigned')) {
            if ($request->assigned === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned);
            }
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->latest()->paginate(20);

        // Stats
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'high_priority' => Ticket::where('priority', 'high')->where('status', '!=', 'closed')->count(),
            'unassigned' => Ticket::whereNull('assigned_to')->where('status', '!=', 'closed')->count(),
        ];

        // Admins pour l'assignation
        $admins = User::where('role', 'admin')->get();

        return view('admin.support.index', compact('tickets', 'stats', 'admins'));
    }

    /**
     * Affiche les détails d'un ticket
     */
    public function show(Ticket $ticket): View
    {
        $ticket->load(['user', 'assignedTo']);

        // Admins pour l'assignation
        $admins = User::where('role', 'admin')->get();

        return view('admin.support.show', compact('ticket', 'admins'));
    }

    /**
     * Assigne un ticket à un admin
     */
    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:users,id',
        ]);

        $ticket->update([
            'assigned_to' => $validated['admin_id'],
            'status' => 'in_progress',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Ticket assigné avec succès !');
    }

    /**
     * Marque un ticket comme résolu
     */
    public function resolve(Request $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update([
            'status' => 'closed',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Ticket résolu avec succès !');
    }

    /**
     * Répond à un ticket
     */
    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'reply' => 'required|string|max:2000',
        ]);

        // TODO: Créer un modèle TicketReply pour gérer les réponses
        // Pour l'instant, on ajoute la réponse au message
        $existingMessage = $ticket->message;
        $replyMessage = "\n\n---\n**Réponse Admin** (" . now()->format('d/m/Y H:i') . "):\n" . $validated['reply'];

        $ticket->update([
            'message' => $existingMessage . $replyMessage,
            'status' => 'in_progress',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Réponse envoyée avec succès !');
    }
}
