<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'course_id',
        'teacher_id',
        'subject_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'is_recurring',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    // Relations
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeToday($query)
    {
        return $query->whereRaw('DAYOFWEEK(start_time) = ?', [Carbon::today()->dayOfWeekIso + 1]);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_time', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Helpers
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDuring($datetime)
    {
        return $this->start_time <= $datetime && $this->end_time >= $datetime;
    }

    public function hasConflict($startTime, $endTime)
    {
        return $this->start_time < $endTime && $this->end_time > $startTime;
    }

    public function getDuration()
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getFormattedTime()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    public function getDayName()
    {
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        return $days[$this->day_of_week - 1] ?? '';
    }
}