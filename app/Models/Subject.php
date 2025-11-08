<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id',
        'name',
        'code',
        'description',
        'credits',
        'hours',
        'coefficient',
        'color',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'credits' => 'integer',
        'hours' => 'integer',
        'coefficient' => 'decimal:2',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relations
    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_teacher', 'subject_id', 'teacher_id')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByFormation($query, $formationId)
    {
        return $query->where('formation_id', $formationId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeWithHours($query, $minHours = null, $maxHours = null)
    {
        if ($minHours) {
            $query->where('hours', '>=', $minHours);
        }
        if ($maxHours) {
            $query->where('hours', '<=', $maxHours);
        }
        return $query;
    }

    // Helpers
    public function isActive()
    {
        return $this->is_active;
    }

    public function activate()
    {
        $this->is_active = true;
        $this->save();
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();
    }

    public function getTotalCourses()
    {
        return $this->courses()->count();
    }

    public function getActiveCourses()
    {
        return $this->courses()->where('status', 'scheduled')->count();
    }

    public function getCompletedCourses()
    {
        return $this->courses()->where('status', 'completed')->count();
    }

    public function getAverageGrade()
    {
        return $this->grades()->avg('grade');
    }

    public function getSuccessRate()
    {
        $total = $this->grades()->count();
        if ($total === 0) return 0;
        
        $passed = $this->grades()->whereRaw('(grade / max_grade) * 20 >= 10')->count();
        return round(($passed / $total) * 100, 2);
    }

    public function getTotalStudents()
    {
        return $this->courses()
                    ->with('class.students')
                    ->get()
                    ->pluck('class.students')
                    ->flatten()
                    ->unique('id')
                    ->count();
    }

    public function getFormattedHours()
    {
        if (!$this->hours) return '0h';
        
        if ($this->hours < 60) {
            return $this->hours . 'h';
        }
        
        $days = floor($this->hours / 24);
        $remainingHours = $this->hours % 24;
        
        return $days . 'j ' . $remainingHours . 'h';
    }

    public function getColorClass()
    {
        return $this->color ?? 'primary';
    }

    public function getIcon()
    {
        return $this->icon ?? 'fa-book';
    }

    public function getShortCode()
    {
        return $this->code ?? strtoupper(substr($this->name, 0, 3));
    }
}