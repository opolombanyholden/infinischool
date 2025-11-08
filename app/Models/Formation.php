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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getDiscountedPriceAttribute()
    {
        if ($this->discount_percentage > 0) {
            return $this->price * (1 - $this->discount_percentage / 100);
        }
        return $this->price;
    }

    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/' . $this->image) 
            : asset('images/default-formation.png');
    }

    public function getTotalHoursAttribute()
    {
        return $this->duration_weeks * $this->hours_per_week;
    }

    // ============================================
    // MUTATORS
    // ============================================

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // ============================================
    // HELPERS
    // ============================================

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isFeatured()
    {
        return $this->is_featured === true;
    }

    public function hasDiscount()
    {
        return $this->discount_percentage > 0;
    }

    public function incrementEnrolledCount()
    {
        $this->increment('enrolled_count');
    }

    public function updateRating($newRating)
    {
        $this->rating = (($this->rating * $this->reviews_count) + $newRating) / ($this->reviews_count + 1);
        $this->reviews_count++;
        $this->save();
    }
}