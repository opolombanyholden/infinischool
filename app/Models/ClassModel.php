<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'formation_id', 'name', 'code', 'max_students', 'current_students',
        'start_date', 'end_date', 'schedule', 'status', 'teacher_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'schedule' => 'array',
    ];

    // ============================================
    // RELATIONS
    // ============================================

    /**
     * Formation à laquelle appartient cette classe
     */
    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    /**
     * Enseignant principal de la classe
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Inscriptions dans cette classe
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    /**
     * Étudiants de cette classe
     */
    public function students()
    {
        return $this->hasManyThrough(User::class, Enrollment::class, 'class_id', 'id', 'id', 'student_id');
    }

    /**
     * Cours de cette classe
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'class_id');
    }

    /**
     * Emploi du temps de cette classe
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeAvailable($query)
    {
        return $query->whereColumn('current_students', '<', 'max_students');
    }

    public function scopeFull($query)
    {
        return $query->whereColumn('current_students', '>=', 'max_students');
    }

    // ============================================
    // HELPERS
    // ============================================

    public function isFull()
    {
        return $this->current_students >= $this->max_students;
    }

    public function hasSpace()
    {
        return $this->current_students < $this->max_students;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function getAvailableSeatsAttribute()
    {
        return $this->max_students - $this->current_students;
    }

    public function getOccupancyRateAttribute()
    {
        return ($this->current_students / $this->max_students) * 100;
    }

    public function addStudent()
    {
        if (!$this->isFull()) {
            $this->increment('current_students');
            return true;
        }
        return false;
    }

    public function removeStudent()
    {
        if ($this->current_students > 0) {
            $this->decrement('current_students');
            return true;
        }
        return false;
    }
}