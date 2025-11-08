<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Formation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'objectives', 'prerequisites',
        'level', 'duration_weeks', 'hours_per_week', 'price', 'discount_percentage',
        'image', 'video_preview', 'enrolled_count', 'rating', 'reviews_count',
        'status', 'is_featured', 'certificate_available', 'certificate_type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_featured' => 'boolean',
        'certificate_available' => 'boolean',
    ];

    // ============================================
    // RELATIONS
    // ============================================

    /**
     * Classes de cette formation
     */
    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    /**
     * Matières de cette formation
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Inscriptions à cette formation
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Étudiants inscrits à cette formation
     */
    public function students()
    {
        return $this->hasManyThrough(User::class, Enrollment::class, 'formation_id', 'id', 'id', 'student_id');
    }

    /**
     * Certificats délivrés pour cette formation
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Paiements pour cette formation
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * ✅ SCOPE AJOUTÉ - Formations actives
     * 
     * Retourne uniquement les formations avec status = 'active' ou 'published'
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'published']);
    }

    /**
     * Formations publiées
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Formations en vedette
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Filtrer par niveau
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Formations avec certificat
     */
    public function scopeWithCertificate($query)
    {
        return $query->where('certificate_available', true);
    }

    /**
     * Formations par catégorie
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Formations avec réduction
     */
    public function scopeWithDiscount($query)
    {
        return $query->where('discount_percentage', '>', 0);
    }

    /**
     * Trier par popularité
     */
    public function scopePopular($query)
    {
        return $query->orderBy('enrolled_count', 'desc');
    }

    /**
     * Trier par note
     */
    public function scopeTopRated($query, $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating)
                     ->orderBy('rating', 'desc');
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Prix après réduction
     */
    public function getDiscountedPriceAttribute()
    {
        if ($this->discount_percentage > 0) {
            return $this->price * (1 - $this->discount_percentage / 100);
        }
        return $this->price;
    }

    /**
     * URL de l'image
     */
    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/' . $this->image) 
            : asset('images/default-formation.png');
    }

    /**
     * Total d'heures de la formation
     */
    public function getTotalHoursAttribute()
    {
        return $this->duration_weeks * $this->hours_per_week;
    }

    /**
     * Prix formaté
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * Prix réduit formaté
     */
    public function getFormattedDiscountedPriceAttribute()
    {
        return number_format($this->discounted_price, 2, ',', ' ') . ' €';
    }

    // ============================================
    // MUTATORS
    // ============================================

    /**
     * Générer automatiquement le slug depuis le titre
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // ============================================
    // HELPERS
    // ============================================

    /**
     * Vérifier si la formation est publiée
     */
    public function isPublished()
    {
        return $this->status === 'published';
    }

    /**
     * Vérifier si la formation est active
     */
    public function isActive()
    {
        return in_array($this->status, ['active', 'published']);
    }

    /**
     * Vérifier si la formation est en vedette
     */
    public function isFeatured()
    {
        return $this->is_featured === true;
    }

    /**
     * Vérifier si la formation a une réduction
     */
    public function hasDiscount()
    {
        return $this->discount_percentage > 0;
    }

    /**
     * Incrémenter le nombre d'inscrits
     */
    public function incrementEnrolledCount()
    {
        $this->increment('enrolled_count');
    }

    /**
     * Décrémenter le nombre d'inscrits
     */
    public function decrementEnrolledCount()
    {
        $this->decrement('enrolled_count');
    }

    /**
     * Mettre à jour la note moyenne
     */
    public function updateRating($newRating)
    {
        $currentTotal = $this->rating * $this->reviews_count;
        $this->reviews_count++;
        $this->rating = ($currentTotal + $newRating) / $this->reviews_count;
        $this->save();
    }

    /**
     * Calculer le pourcentage de réduction
     */
    public function getDiscountPercentage()
    {
        return $this->discount_percentage;
    }

    /**
     * Obtenir les statistiques de la formation
     */
    public function getStats()
    {
        return [
            'enrolled' => $this->enrolled_count,
            'rating' => $this->rating,
            'reviews' => $this->reviews_count,
            'classes' => $this->classes()->count(),
            'subjects' => $this->subjects()->count(),
            'certificates_issued' => $this->certificates()->count(),
        ];
    }
}