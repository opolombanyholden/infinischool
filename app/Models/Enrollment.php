<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'formation_id',
        'class_id',
        'enrollment_date',
        'status',
        'progress_percentage',
        'completion_date',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'completion_date' => 'date',
        'progress_percentage' => 'integer',
    ];

    // Relations
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    // Helpers
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function updateProgress($percentage)
    {
        $this->progress_percentage = min(100, max(0, $percentage));
        
        if ($this->progress_percentage >= 100) {
            $this->status = 'completed';
            $this->completion_date = now();
        }
        
        $this->save();
    }

    public function complete()
    {
        $this->status = 'completed';
        $this->progress_percentage = 100;
        $this->completion_date = now();
        $this->save();
    }

    public function suspend($reason = null)
    {
        $this->status = 'suspended';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
    }

    public function activate()
    {
        $this->status = 'active';
        $this->save();
    }

    public function getAverageGrade()
    {
        return $this->grades()->avg('grade');
    }

    public function getAttendanceRate()
    {
        $total = $this->attendances()->count();
        if ($total === 0) return 0;
        
        $present = $this->attendances()->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }
}