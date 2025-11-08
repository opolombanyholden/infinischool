<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_id',
        'class_id',
        'date',
        'status',
        'check_in_time',
        'check_out_time',
        'duration_minutes',
        'ip_address',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // Relations
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // Scopes
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeExcused($query)
    {
        return $query->where('status', 'excused');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    // Helpers
    public function isPresent()
    {
        return $this->status === 'present';
    }

    public function isAbsent()
    {
        return $this->status === 'absent';
    }

    public function isLate()
    {
        return $this->status === 'late';
    }

    public function isExcused()
    {
        return $this->status === 'excused';
    }

    public function markPresent()
    {
        $this->status = 'present';
        $this->check_in_time = now();
        $this->save();
    }

    public function markAbsent($notes = null)
    {
        $this->status = 'absent';
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();
    }

    public function markLate()
    {
        $this->status = 'late';
        $this->check_in_time = now();
        $this->save();
    }

    public function markExcused($notes = null)
    {
        $this->status = 'excused';
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();
    }

    public function checkOut()
    {
        $this->check_out_time = now();
        
        if ($this->check_in_time) {
            $this->duration_minutes = $this->check_in_time->diffInMinutes($this->check_out_time);
        }
        
        $this->save();
    }

    public function getFormattedDuration()
    {
        if (!$this->duration_minutes) return '0min';
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }
        
        return "{$minutes}min";
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'excused' => 'info',
            default => 'secondary'
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'present' => 'Présent',
            'absent' => 'Absent',
            'late' => 'Retard',
            'excused' => 'Excusé',
            default => 'Non défini'
        };
    }
}