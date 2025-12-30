<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Formation;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * AdminActivityController
 * 
 * Gère le journal d'activités de la plateforme
 * Agrège les activités depuis plusieurs sources
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminActivityController extends Controller
{
    /**
     * Affiche le journal d'activités
     */
    public function index(Request $request): View
    {
        // Filtres
        $type = $request->input('type', 'all');
        $period = $request->input('period', 'week');
        $startDate = $this->getStartDate($period);

        // Récupérer les activités de différentes sources
        $activities = $this->gatherActivities($startDate, $type);

        // Statistiques
        $stats = [
            'total' => $activities->count(),
            'today' => $activities->filter(fn($a) => $a['date']->isToday())->count(),
            'enrollments' => $activities->where('type', 'enrollment')->count(),
            'payments' => $activities->where('type', 'payment')->count(),
            'registrations' => $activities->where('type', 'registration')->count(),
            'reviews' => $activities->where('type', 'review')->count(),
        ];

        // Pagination manuelle
        $page = $request->input('page', 1);
        $perPage = 25;
        $paginatedActivities = $activities->forPage($page, $perPage);

        return view('admin.activity.index', [
            'activities' => $paginatedActivities,
            'stats' => $stats,
            'type' => $type,
            'period' => $period,
            'totalPages' => ceil($activities->count() / $perPage),
            'currentPage' => $page,
        ]);
    }

    /**
     * Export des activités
     */
    public function export(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period);

        $activities = $this->gatherActivities($startDate, 'all');

        $filename = "activites-" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($activities) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($file, ['Type', 'Utilisateur', 'Email', 'Action', 'Détails', 'Date']);

            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity['type'],
                    $activity['user_name'] ?? 'N/A',
                    $activity['user_email'] ?? 'N/A',
                    $activity['action'],
                    $activity['details'] ?? '',
                    $activity['date']->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Récupère les activités de différentes sources
     */
    private function gatherActivities(Carbon $startDate, string $type): Collection
    {
        $activities = collect();

        // Inscriptions aux formations
        if ($type === 'all' || $type === 'enrollment') {
            $enrollments = Enrollment::with(['user', 'formation'])
                ->where('created_at', '>=', $startDate)
                ->latest()
                ->limit(100)
                ->get();

            foreach ($enrollments as $enrollment) {
                $activities->push([
                    'type' => 'enrollment',
                    'icon' => 'user-plus',
                    'color' => 'primary',
                    'user_name' => $enrollment->user->name ?? 'Utilisateur',
                    'user_email' => $enrollment->user->email ?? '',
                    'user_avatar' => $enrollment->user->avatar ?? null,
                    'action' => 'Inscription à une formation',
                    'details' => $enrollment->formation->name ?? 'Formation',
                    'date' => $enrollment->created_at,
                ]);
            }
        }

        // Paiements
        if ($type === 'all' || $type === 'payment') {
            $payments = Payment::with(['student', 'formation'])
                ->where('status', 'completed')
                ->where('paid_at', '>=', $startDate)
                ->latest('paid_at')
                ->limit(100)
                ->get();

            foreach ($payments as $payment) {
                $activities->push([
                    'type' => 'payment',
                    'icon' => 'credit-card',
                    'color' => 'success',
                    'user_name' => $payment->student->name ?? 'Étudiant',
                    'user_email' => $payment->student->email ?? '',
                    'user_avatar' => $payment->student->avatar ?? null,
                    'action' => 'Paiement effectué',
                    'details' => number_format($payment->amount, 0, ',', ' ') . ' FCFA - ' . ($payment->formation->name ?? 'Formation'),
                    'date' => $payment->paid_at ?? $payment->created_at,
                ]);
            }
        }

        // Nouvelles inscriptions utilisateurs
        if ($type === 'all' || $type === 'registration') {
            $users = User::where('created_at', '>=', $startDate)
                ->latest()
                ->limit(100)
                ->get();

            foreach ($users as $user) {
                $activities->push([
                    'type' => 'registration',
                    'icon' => 'user-check',
                    'color' => 'info',
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_avatar' => $user->avatar ?? null,
                    'action' => 'Nouvel utilisateur inscrit',
                    'details' => 'Rôle: ' . ucfirst($user->role ?? 'student'),
                    'date' => $user->created_at,
                ]);
            }
        }

        // Avis
        if ($type === 'all' || $type === 'review') {
            $reviews = Review::with(['user', 'formation'])
                ->where('created_at', '>=', $startDate)
                ->latest()
                ->limit(100)
                ->get();

            foreach ($reviews as $review) {
                $activities->push([
                    'type' => 'review',
                    'icon' => 'star',
                    'color' => 'warning',
                    'user_name' => $review->user->name ?? 'Utilisateur',
                    'user_email' => $review->user->email ?? '',
                    'user_avatar' => $review->user->avatar ?? null,
                    'action' => 'Nouvel avis posté',
                    'details' => ($review->rating ?? 0) . '/5 - ' . ($review->formation->name ?? 'Formation'),
                    'date' => $review->created_at,
                ]);
            }
        }

        // Trier par date décroissante
        return $activities->sortByDesc('date')->values();
    }

    /**
     * Récupère la date de début selon la période
     */
    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subMonths(3),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subWeek(),
        };
    }
}
