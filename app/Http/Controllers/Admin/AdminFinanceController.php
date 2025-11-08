<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * AdminFinanceController
 * 
 * Gère le suivi financier et les analytics de la plateforme
 * CA, paiements, revenus, remboursements, rapports
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminFinanceController extends Controller
{
    /**
     * Affiche le dashboard financier
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Période sélectionnée (par défaut : mois en cours)
        $period = $request->input('period', 'month');
        $startDate = $this->getPeriodStartDate($period);
        $endDate = Carbon::now();
        
        // KPIs financiers
        $kpis = $this->getFinancialKPIs($startDate, $endDate);
        
        // Évolution du CA (graphique)
        $revenueEvolution = $this->getRevenueEvolution($startDate, $endDate);
        
        // Top formations par revenus
        $topFormations = $this->getTopFormationsByRevenue(5);
        
        // Paiements récents
        $recentPayments = Payment::with(['student', 'formation'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Paiements en attente
        $pendingPayments = Payment::where('status', 'pending')
            ->with(['student', 'formation'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Paiements échoués (aujourd'hui)
        $failedPaymentsToday = Payment::where('status', 'failed')
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Statistiques par méthode de paiement
        $paymentMethods = $this->getPaymentMethodsStats();
        
        return view('admin.finance.index', compact(
            'kpis',
            'revenueEvolution',
            'topFormations',
            'recentPayments',
            'pendingPayments',
            'failedPaymentsToday',
            'paymentMethods',
            'period'
        ));
    }
    
    /**
     * Affiche la liste des paiements avec filtres
     * 
     * @param Request $request
     * @return View
     */
    public function payments(Request $request): View
    {
        $query = Payment::with(['student', 'formation', 'enrollment']);
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par méthode de paiement
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filtre par formation
        if ($request->filled('formation_id')) {
            $query->where('formation_id', $request->formation_id);
        }
        
        // Filtre par période
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filtre par montant
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }
        
        // Recherche par transaction ID ou nom étudiant
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $perPage = $request->input('per_page', 25);
        $payments = $query->paginate($perPage)->withQueryString();
        
        // Statistiques pour la page
        $stats = [
            'total' => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'refunded' => Payment::where('status', 'refunded')->count(),
            'total_amount' => Payment::where('status', 'completed')->sum('amount'),
            'pending_amount' => Payment::where('status', 'pending')->sum('amount'),
        ];
        
        // Formations pour le filtre
        $formations = Formation::orderBy('name')->get();
        
        return view('admin.finance.payments', compact('payments', 'stats', 'formations'));
    }
    
    /**
     * Affiche les détails d'un paiement
     * 
     * @param Payment $payment
     * @return View
     */
    public function showPayment(Payment $payment): View
    {
        $payment->load(['student', 'formation', 'enrollment']);
        
        // Historique des tentatives (si table exists)
        // $paymentAttempts = PaymentAttempt::where('payment_id', $payment->id)->get();
        
        return view('admin.finance.payment-details', compact('payment'));
    }
    
    /**
     * Marque un paiement comme complété manuellement
     * 
     * @param Payment $payment
     * @return RedirectResponse
     */
    public function markAsCompleted(Payment $payment): RedirectResponse
    {
        if ($payment->status === 'completed') {
            return redirect()
                ->back()
                ->with('warning', 'Ce paiement est déjà marqué comme complété.');
        }
        
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);
        
        // Activer l'inscription
        if ($payment->enrollment_id) {
            Enrollment::where('id', $payment->enrollment_id)
                ->update(['status' => 'active']);
        }
        
        // TODO: Envoyer email de confirmation
        
        return redirect()
            ->back()
            ->with('success', 'Paiement marqué comme complété avec succès !');
    }
    
    /**
     * Traite un remboursement
     * 
     * @param Request $request
     * @param Payment $payment
     * @return RedirectResponse
     */
    public function refund(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'refund_amount' => 'nullable|numeric|min:0|max:' . $payment->amount,
        ]);
        
        if ($payment->status !== 'completed') {
            return redirect()
                ->back()
                ->with('error', 'Seuls les paiements complétés peuvent être remboursés.');
        }
        
        $refundAmount = $validated['refund_amount'] ?? $payment->amount;
        
        DB::beginTransaction();
        
        try {
            // Mettre à jour le paiement
            $payment->update([
                'status' => 'refunded',
                'refund_reason' => $validated['reason'],
                'refund_amount' => $refundAmount,
                'refunded_at' => now(),
            ]);
            
            // Suspendre l'inscription si remboursement total
            if ($refundAmount == $payment->amount && $payment->enrollment_id) {
                Enrollment::where('id', $payment->enrollment_id)
                    ->update(['status' => 'cancelled']);
            }
            
            // TODO: Traiter le remboursement via Stripe/PayPal
            // Stripe::refund($payment->transaction_id, $refundAmount);
            
            // TODO: Envoyer email de confirmation de remboursement
            
            DB::commit();
            
            return redirect()
                ->back()
                ->with('success', "Remboursement de {$refundAmount}€ effectué avec succès !");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors du remboursement : ' . $e->getMessage());
        }
    }
    
    /**
     * Affiche les paiements échoués
     * 
     * @return View
     */
    public function failedPayments(): View
    {
        $failedPayments = Payment::where('status', 'failed')
            ->with(['student', 'formation'])
            ->latest()
            ->paginate(25);
        
        $stats = [
            'total_failed' => Payment::where('status', 'failed')->count(),
            'failed_today' => Payment::where('status', 'failed')
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'lost_revenue' => Payment::where('status', 'failed')->sum('amount'),
        ];
        
        return view('admin.finance.failed-payments', compact('failedPayments', 'stats'));
    }
    
    /**
     * Réessaie un paiement échoué
     * 
     * @param Payment $payment
     * @return RedirectResponse
     */
    public function retryPayment(Payment $payment): RedirectResponse
    {
        if ($payment->status !== 'failed') {
            return redirect()
                ->back()
                ->with('error', 'Ce paiement n\'est pas en échec.');
        }
        
        // TODO: Implémenter la logique de retry avec Stripe/PayPal
        // $result = Stripe::retryPayment($payment->transaction_id);
        
        $payment->update([
            'status' => 'pending',
            'notes' => ($payment->notes ?? '') . "\n[" . now() . "] Tentative de paiement relancée",
        ]);
        
        // TODO: Envoyer email à l'étudiant pour compléter le paiement
        
        return redirect()
            ->back()
            ->with('success', 'Demande de paiement relancée !');
    }
    
    /**
     * Affiche les revenus par formation
     * 
     * @param Request $request
     * @return View
     */
    public function revenueByFormation(Request $request): View
    {
        // Période
        $period = $request->input('period', 'all');
        $startDate = $period !== 'all' ? $this->getPeriodStartDate($period) : null;
        
        // Requête
        $query = DB::table('payments')
            ->join('formations', 'payments.formation_id', '=', 'formations.id')
            ->where('payments.status', 'completed')
            ->select(
                'formations.id',
                'formations.name',
                'formations.code',
                DB::raw('COUNT(payments.id) as payments_count'),
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('AVG(payments.amount) as avg_payment'),
                DB::raw('MIN(payments.amount) as min_payment'),
                DB::raw('MAX(payments.amount) as max_payment')
            )
            ->groupBy('formations.id', 'formations.name', 'formations.code')
            ->orderByDesc('total_revenue');
        
        if ($startDate) {
            $query->where('payments.paid_at', '>=', $startDate);
        }
        
        $revenueByFormation = $query->get();
        
        // Total global
        $totalRevenue = $revenueByFormation->sum('total_revenue');
        
        return view('admin.finance.revenue-by-formation', compact(
            'revenueByFormation',
            'totalRevenue',
            'period'
        ));
    }
    
    /**
     * Génère un rapport financier
     * 
     * @param Request $request
     * @return View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'format' => 'required|in:view,csv,pdf',
        ]);
        
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        
        // Données du rapport
        $reportData = $this->generateReportData($startDate, $endDate);
        
        // Format de sortie
        switch ($validated['format']) {
            case 'csv':
                return $this->exportReportAsCSV($reportData, $startDate, $endDate);
                
            case 'pdf':
                // TODO: Implémenter avec DomPDF
                return redirect()
                    ->back()
                    ->with('info', 'Export PDF en développement.');
                
            default: // view
                return view('admin.finance.report', compact('reportData', 'startDate', 'endDate'));
        }
    }
    
    /**
     * Affiche les statistiques de conversion
     * 
     * @param Request $request
     * @return View
     */
    public function conversionStats(Request $request): View
    {
        $period = $request->input('period', 'month');
        $startDate = $this->getPeriodStartDate($period);
        
        // Visiteurs (TODO: intégrer avec Google Analytics)
        $totalVisitors = 10000; // Placeholder
        
        // Inscriptions
        $totalEnrollments = Enrollment::where('created_at', '>=', $startDate)->count();
        
        // Paiements
        $completedPayments = Payment::where('status', 'completed')
            ->where('paid_at', '>=', $startDate)
            ->count();
        
        $pendingPayments = Payment::where('status', 'pending')
            ->where('created_at', '>=', $startDate)
            ->count();
        
        $failedPayments = Payment::where('status', 'failed')
            ->where('created_at', '>=', $startDate)
            ->count();
        
        // Calculs
        $enrollmentRate = $totalVisitors > 0 ? ($totalEnrollments / $totalVisitors) * 100 : 0;
        $paymentRate = $totalEnrollments > 0 ? ($completedPayments / $totalEnrollments) * 100 : 0;
        $failureRate = ($completedPayments + $failedPayments) > 0 
            ? ($failedPayments / ($completedPayments + $failedPayments)) * 100 
            : 0;
        
        // Évolution du taux de conversion
        $conversionEvolution = $this->getConversionEvolution($startDate);
        
        $stats = [
            'visitors' => $totalVisitors,
            'enrollments' => $totalEnrollments,
            'completed_payments' => $completedPayments,
            'pending_payments' => $pendingPayments,
            'failed_payments' => $failedPayments,
            'enrollment_rate' => round($enrollmentRate, 2),
            'payment_rate' => round($paymentRate, 2),
            'failure_rate' => round($failureRate, 2),
        ];
        
        return view('admin.finance.conversion', compact('stats', 'conversionEvolution', 'period'));
    }
    
    /**
     * Récupère les KPIs financiers
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getFinancialKPIs(Carbon $startDate, Carbon $endDate): array
    {
        // Revenus complétés
        $completedRevenue = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');
        
        // Revenus en attente
        $pendingRevenue = Payment::where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
        
        // Revenus remboursés
        $refundedRevenue = Payment::where('status', 'refunded')
            ->whereBetween('refunded_at', [$startDate, $endDate])
            ->sum('refund_amount');
        
        // Nombre de transactions
        $transactionsCount = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->count();
        
        // Panier moyen
        $avgBasket = $transactionsCount > 0 ? $completedRevenue / $transactionsCount : 0;
        
        // Croissance vs période précédente
        $previousPeriodStart = $startDate->copy()->sub($endDate->diff($startDate));
        $previousRevenue = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$previousPeriodStart, $startDate])
            ->sum('amount');
        
        $growth = $previousRevenue > 0 
            ? (($completedRevenue - $previousRevenue) / $previousRevenue) * 100 
            : 0;
        
        // CA total (all time)
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        
        return [
            'completed_revenue' => round($completedRevenue, 2),
            'pending_revenue' => round($pendingRevenue, 2),
            'refunded_revenue' => round($refundedRevenue, 2),
            'net_revenue' => round($completedRevenue - $refundedRevenue, 2),
            'transactions_count' => $transactionsCount,
            'avg_basket' => round($avgBasket, 2),
            'growth' => round($growth, 1),
            'total_revenue_all_time' => round($totalRevenue, 2),
            'currency' => 'EUR',
        ];
    }
    
    /**
     * Récupère l'évolution des revenus
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getRevenueEvolution(Carbon $startDate, Carbon $endDate): array
    {
        $data = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as revenue, COUNT(*) as transactions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'labels' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'revenue' => $data->pluck('revenue')->map(fn($r) => round($r, 2))->toArray(),
            'transactions' => $data->pluck('transactions')->toArray(),
        ];
    }
    
    /**
     * Récupère le top formations par revenus
     * 
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getTopFormationsByRevenue(int $limit = 5)
    {
        return DB::table('payments')
            ->join('formations', 'payments.formation_id', '=', 'formations.id')
            ->where('payments.status', 'completed')
            ->select(
                'formations.id',
                'formations.name',
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('COUNT(payments.id) as payments_count')
            )
            ->groupBy('formations.id', 'formations.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Récupère les stats par méthode de paiement
     * 
     * @return array
     */
    private function getPaymentMethodsStats(): array
    {
        return Payment::where('status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as revenue')
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->payment_method => [
                        'count' => $item->count,
                        'revenue' => round($item->revenue, 2),
                    ]
                ];
            })
            ->toArray();
    }
    
    /**
     * Récupère la date de début selon la période
     * 
     * @param string $period
     * @return Carbon
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        switch ($period) {
            case 'today':
                return Carbon::today();
            case 'week':
                return Carbon::now()->startOfWeek();
            case 'month':
                return Carbon::now()->startOfMonth();
            case 'quarter':
                return Carbon::now()->startOfQuarter();
            case 'year':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->startOfMonth();
        }
    }
    
    /**
     * Génère les données du rapport
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function generateReportData(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'summary' => $this->getFinancialKPIs($startDate, $endDate),
            'by_formation' => $this->getRevenueByFormationData($startDate, $endDate),
            'by_day' => $this->getRevenueEvolution($startDate, $endDate),
            'by_method' => $this->getPaymentMethodsStats(),
            'refunds' => $this->getRefundsData($startDate, $endDate),
        ];
    }
    
    /**
     * Récupère les revenus par formation pour une période
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getRevenueByFormationData(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('payments')
            ->join('formations', 'payments.formation_id', '=', 'formations.id')
            ->where('payments.status', 'completed')
            ->whereBetween('payments.paid_at', [$startDate, $endDate])
            ->select(
                'formations.name',
                DB::raw('SUM(payments.amount) as revenue'),
                DB::raw('COUNT(payments.id) as transactions')
            )
            ->groupBy('formations.id', 'formations.name')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }
    
    /**
     * Récupère les données de remboursement
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getRefundsData(Carbon $startDate, Carbon $endDate): array
    {
        $refunds = Payment::where('status', 'refunded')
            ->whereBetween('refunded_at', [$startDate, $endDate])
            ->get();
        
        return [
            'count' => $refunds->count(),
            'total_amount' => round($refunds->sum('refund_amount'), 2),
            'avg_amount' => round($refunds->avg('refund_amount'), 2),
        ];
    }
    
    /**
     * Exporte le rapport en CSV
     * 
     * @param array $data
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportReportAsCSV(array $data, Carbon $startDate, Carbon $endDate)
    {
        $filename = "rapport-financier-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($data, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // En-tête du rapport
            fputcsv($file, ['Rapport Financier InfiniSchool']);
            fputcsv($file, ['Période', $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y')]);
            fputcsv($file, []);
            
            // Résumé
            fputcsv($file, ['RÉSUMÉ']);
            fputcsv($file, ['Revenus Complétés', $data['summary']['completed_revenue'] . ' €']);
            fputcsv($file, ['Revenus en Attente', $data['summary']['pending_revenue'] . ' €']);
            fputcsv($file, ['Remboursements', $data['summary']['refunded_revenue'] . ' €']);
            fputcsv($file, ['Revenus Net', $data['summary']['net_revenue'] . ' €']);
            fputcsv($file, ['Transactions', $data['summary']['transactions_count']]);
            fputcsv($file, ['Panier Moyen', $data['summary']['avg_basket'] . ' €']);
            fputcsv($file, []);
            
            // Par formation
            fputcsv($file, ['REVENUS PAR FORMATION']);
            fputcsv($file, ['Formation', 'Revenus (€)', 'Transactions']);
            foreach ($data['by_formation'] as $item) {
                fputcsv($file, [$item->name, $item->revenue, $item->transactions]);
            }
            fputcsv($file, []);
            
            // Remboursements
            fputcsv($file, ['REMBOURSEMENTS']);
            fputcsv($file, ['Nombre', $data['refunds']['count']]);
            fputcsv($file, ['Montant Total', $data['refunds']['total_amount'] . ' €']);
            fputcsv($file, ['Montant Moyen', $data['refunds']['avg_amount'] . ' €']);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Récupère l'évolution du taux de conversion
     * 
     * @param Carbon $startDate
     * @return array
     */
    private function getConversionEvolution(Carbon $startDate): array
    {
        // TODO: Implémenter avec données réelles
        return [];
    }
}