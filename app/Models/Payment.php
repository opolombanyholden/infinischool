<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'formation_id',
        'enrollment_id',
        'transaction_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'notes',
        'invoice_number',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Boot method pour générer automatiquement le numéro de facture
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (empty($payment->invoice_number)) {
                $payment->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    // Relations
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('paid_at', now()->month)
                     ->whereYear('paid_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('paid_at', now()->year);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helpers
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isRefunded()
    {
        return $this->status === 'refunded';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->paid_at = now();
        $this->save();
        
        // Mettre à jour le statut de paiement de l'inscription
        if ($this->enrollment) {
            $this->enrollment->payment_status = 'paid';
            $this->enrollment->save();
        }
    }

    public function markAsFailed($notes = null)
    {
        $this->status = 'failed';
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
    }

    public function cancel($notes = null)
    {
        $this->status = 'cancelled';
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
    }

    public function refund($amount = null, $notes = null)
    {
        $this->status = 'refunded';
        $this->refunded_at = now();
        $this->refund_amount = $amount ?? $this->amount;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
        
        // Mettre à jour le statut de paiement de l'inscription
        if ($this->enrollment) {
            $this->enrollment->payment_status = 'refunded';
            $this->enrollment->save();
        }
    }

    public function getFormattedAmount()
    {
        return number_format($this->amount, 2, ',', ' ') . ' ' . strtoupper($this->currency);
    }

    public function getFormattedRefundAmount()
    {
        if (!$this->refund_amount) return null;
        
        return number_format($this->refund_amount, 2, ',', ' ') . ' ' . strtoupper($this->currency);
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'info',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'completed' => 'Complété',
            'pending' => 'En attente',
            'failed' => 'Échoué',
            'refunded' => 'Remboursé',
            'cancelled' => 'Annulé',
            default => 'Non défini'
        };
    }

    public function getMethodLabel()
    {
        return match($this->payment_method) {
            'card' => 'Carte bancaire',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'bank_transfer' => 'Virement bancaire',
            'cash' => 'Espèces',
            'check' => 'Chèque',
            default => ucfirst($this->payment_method)
        };
    }

    public function getReceiptUrl()
    {
        return $this->receipt_path 
            ? asset('storage/' . $this->receipt_path)
            : null;
    }

    public function getDownloadReceiptUrl()
    {
        return route('payments.receipt', $this->id);
    }

    // Static helpers
    public static function generateInvoiceNumber()
    {
        $year = now()->year;
        $month = now()->format('m');
        $count = self::whereYear('created_at', $year)
                     ->whereMonth('created_at', $month)
                     ->count() + 1;
        
        return sprintf('INV-%d%s-%05d', $year, $month, $count);
    }

    public static function getTotalRevenue($startDate = null, $endDate = null)
    {
        $query = self::completed();
        
        if ($startDate) {
            $query->where('paid_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('paid_at', '<=', $endDate);
        }
        
        return $query->sum('amount');
    }

    public static function getMonthlyRevenue($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        
        return self::completed()
                   ->whereYear('paid_at', $year)
                   ->whereMonth('paid_at', $month)
                   ->sum('amount');
    }

    public static function getYearlyRevenue($year = null)
    {
        $year = $year ?? now()->year;
        
        return self::completed()
                   ->whereYear('paid_at', $year)
                   ->sum('amount');
    }
}