<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'class_id',
        'teacher_id',
        'title',
        'description',
        'scheduled_at',
        'duration_minutes',
        'meeting_url',
        'meeting_id',
        'meeting_password',
        'status',
        'max_students',
        'room',
        'type',
        'is_recorded',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'max_students' => 'integer',
        'is_recorded' => 'boolean',
    ];

    // Relations
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function recordings()
    {
        return $this->hasMany(Recording::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '>', now())
                     ->orderBy('scheduled_at', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now())
                     ->orderBy('scheduled_at', 'desc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('scheduled_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('scheduled_at', now()->month)
                     ->whereYear('scheduled_at', now()->year);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecorded($query)
    {
        return $query->where('is_recorded', true);
    }

    // Helpers
    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    public function isLive()
    {
        return $this->status === 'live';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isUpcoming()
    {
        return $this->isScheduled() && $this->scheduled_at->isFuture();
    }

    public function isPast()
    {
        return $this->scheduled_at->isPast();
    }

    public function isToday()
    {
        return $this->scheduled_at->isToday();
    }

    public function isHappeningNow()
    {
        if (!$this->isLive()) return false;
        
        $endTime = $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
        return now()->between($this->scheduled_at, $endTime);
    }

    public function canJoin()
    {
        // Peut rejoindre si le cours est live ou commence dans 10 minutes
        if ($this->isLive()) return true;
        
        $startTime = $this->scheduled_at->copy()->subMinutes(10);
        return now()->greaterThanOrEqualTo($startTime) && now()->lessThan($this->scheduled_at->copy()->addMinutes($this->duration_minutes));
    }

    public function startLive()
    {
        $this->status = 'live';
        $this->save();
    }

    public function complete()
    {
        $this->status = 'completed';
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

    public function reschedule($newDateTime)
    {
        $this->scheduled_at = $newDateTime;
        $this->status = 'scheduled';
        $this->save();
    }

    public function getEndTime()
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    public function getFormattedDuration()
    {
        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . ' min';
        }
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($minutes > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }
        
        return $hours . 'h';
    }

    public function getFormattedSchedule()
    {
        return $this->scheduled_at->format('d/m/Y à H:i') . ' - ' . $this->getEndTime()->format('H:i');
    }

    public function getTimeUntilStart()
    {
        if ($this->scheduled_at->isPast()) {
            return 'Commencé';
        }
        
        return $this->scheduled_at->diffForHumans();
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'scheduled' => 'primary',
            'live' => 'success',
            'completed' => 'secondary',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'scheduled' => 'Programmé',
            'live' => 'En direct',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => 'Non défini'
        };
    }

    public function getTypeBadgeClass()
    {
        return match($this->type) {
            'lecture' => 'info',
            'practical' => 'warning',
            'exam' => 'danger',
            'tutorial' => 'success',
            default => 'secondary'
        };
    }

    public function getTypeLabel()
    {
        return match($this->type) {
            'lecture' => 'Cours magistral',
            'practical' => 'Travaux pratiques',
            'exam' => 'Examen',
            'tutorial' => 'Tutoriel',
            default => ucfirst($this->type)
        };
    }

    public function getAttendanceCount()
    {
        return $this->attendances()->where('status', 'present')->count();
    }

    public function getAttendanceRate()
    {
        $total = $this->attendances()->count();
        if ($total === 0) return 0;
        
        $present = $this->attendances()->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }

    public function hasRecording()
    {
        return $this->recordings()->exists();
    }

    public function getLatestRecording()
    {
        return $this->recordings()->latest()->first();
    }
}