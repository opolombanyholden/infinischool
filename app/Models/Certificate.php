<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'formation_id',
        'certificate_number',
        'title',
        'description',
        'issued_at',
        'expires_at',
        'grade',
        'mention',
        'file_path',
        'verification_code',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'grade' => 'decimal:2',
    ];

    // Boot method pour générer automatiquement les codes
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($certificate) {
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = self::generateCertificateNumber();
            }
            
            if (empty($certificate->verification_code)) {
                $certificate->verification_code = self::generateVerificationCode();
            }
        });
    }

    // Relations
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('issued_at', '>=', now()->subDays($days));
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByFormation($query, $formationId)
    {
        return $query->where('formation_id', $formationId);
    }

    // Helpers
    public function isVerified()
    {
        return $this->is_verified;
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive()
    {
        return !$this->isExpired();
    }

    public function verify($verifiedBy = null)
    {
        $this->is_verified = true;
        $this->verified_at = now();
        
        if ($verifiedBy) {
            $this->verified_by = $verifiedBy;
        }
        
        $this->save();
    }

    public function getFileUrl()
    {
        return $this->file_path 
            ? asset('storage/' . $this->file_path)
            : null;
    }

    public function getDownloadUrl()
    {
        return route('certificates.download', $this->id);
    }

    public function getVerificationUrl()
    {
        return route('certificates.verify', $this->verification_code);
    }

    public function getQRCode()
    {
        // Générer un QR code pour la vérification
        // Nécessite un package comme SimpleSoftwareIO/simple-qrcode
        return $this->getVerificationUrl();
    }

    public function getMentionBadgeClass()
    {
        return match($this->mention) {
            'Très Bien' => 'success',
            'Bien' => 'primary',
            'Assez Bien' => 'info',
            'Passable' => 'warning',
            default => 'secondary'
        };
    }

    // Static helpers
    public static function generateCertificateNumber()
    {
        $year = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        
        return sprintf('CERT-%d-%05d', $year, $count);
    }

    public static function generateVerificationCode()
    {
        return strtoupper(Str::random(12));
    }

    public static function verifyByCode($code)
    {
        return self::where('verification_code', $code)->first();
    }
}