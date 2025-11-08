<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'subject_id',
        'teacher_id',
        'class_id',
        'title',
        'description',
        'instructions',
        'type',
        'total_points',
        'due_date',
        'published_at',
        'attachment_path',
        'allow_late_submission',
        'late_penalty_percentage',
        'status',
    ];

    protected $casts = [
        'total_points' => 'decimal:2',
        'due_date' => 'datetime',
        'published_at' => 'datetime',
        'allow_late_submission' => 'boolean',
        'late_penalty_percentage' => 'decimal:2',
    ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeUpcoming($query)
    {
        return $query->published()
                     ->where('due_date', '>', now())
                     ->orderBy('due_date', 'asc');
    }

    public function scopeOverdue($query)
    {
        return $query->published()
                     ->where('due_date', '<', now())
                     ->where('status', '!=', 'closed');
    }

    public function scopeDueToday($query)
    {
        return $query->published()
                     ->whereDate('due_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->published()
                     ->whereBetween('due_date', [
                         now()->startOfWeek(),
                         now()->endOfWeek()
                     ]);
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

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isOverdue()
    {
        return $this->due_date->isPast() && !$this->isClosed();
    }

    public function isDueToday()
    {
        return $this->due_date->isToday();
    }

    public function isDueSoon($hours = 24)
    {
        return $this->due_date->isFuture() && 
               $this->due_date->diffInHours(now()) <= $hours;
    }

    public function publish()
    {
        $this->status = 'published';
        $this->published_at = now();
        $this->save();
    }

    public function close()
    {
        $this->status = 'closed';
        $this->save();
    }

    public function reopen()
    {
        $this->status = 'published';
        $this->save();
    }

    public function getTimeRemaining()
    {
        if ($this->due_date->isPast()) {
            return 'Échéance dépassée';
        }
        
        return $this->due_date->diffForHumans();
    }

    public function getDaysRemaining()
    {
        if ($this->due_date->isPast()) {
            return 0;
        }
        
        return $this->due_date->diffInDays(now());
    }

    public function getHoursRemaining()
    {
        if ($this->due_date->isPast()) {
            return 0;
        }
        
        return $this->due_date->diffInHours(now());
    }

    public function hasAttachment()
    {
        return !empty($this->attachment_path);
    }

    public function getAttachmentUrl()
    {
        return $this->attachment_path 
            ? asset('storage/' . $this->attachment_path)
            : null;
    }

    public function getTotalSubmissions()
    {
        return $this->submissions()->count();
    }

    public function getSubmittedCount()
    {
        return $this->submissions()->where('status', 'submitted')->count();
    }

    public function getGradedCount()
    {
        return $this->submissions()->where('status', 'graded')->count();
    }

    public function getPendingCount()
    {
        return $this->submissions()->where('status', 'pending')->count();
    }

    public function getLateSubmissionsCount()
    {
        return $this->submissions()
                    ->where('submitted_at', '>', $this->due_date)
                    ->count();
    }

    public function getSubmissionRate()
    {
        $totalStudents = $this->class ? $this->class->students()->count() : 0;
        if ($totalStudents === 0) return 0;
        
        $submissions = $this->getSubmittedCount();
        return round(($submissions / $totalStudents) * 100, 2);
    }

    public function getAverageScore()
    {
        return $this->submissions()
                    ->where('status', 'graded')
                    ->avg('points_earned');
    }

    public function getAveragePercentage()
    {
        if ($this->total_points == 0) return 0;
        
        $avgScore = $this->getAverageScore();
        if (!$avgScore) return 0;
        
        return round(($avgScore / $this->total_points) * 100, 2);
    }

    public function getStatusBadgeClass()
    {
        if ($this->isClosed()) return 'secondary';
        if ($this->isDraft()) return 'warning';
        if ($this->isOverdue()) return 'danger';
        if ($this->isDueSoon(24)) return 'warning';
        return 'success';
    }

    public function getStatusLabel()
    {
        if ($this->isClosed()) return 'Fermé';
        if ($this->isDraft()) return 'Brouillon';
        if ($this->isOverdue()) return 'Échéance dépassée';
        if ($this->isDueSoon(24)) return 'À rendre bientôt';
        return 'Ouvert';
    }

    public function getTypeBadgeClass()
    {
        return match($this->type) {
            'homework' => 'primary',
            'quiz' => 'info',
            'project' => 'warning',
            'exam' => 'danger',
            'exercise' => 'success',
            default => 'secondary'
        };
    }

    public function getTypeLabel()
    {
        return match($this->type) {
            'homework' => 'Devoir',
            'quiz' => 'Quiz',
            'project' => 'Projet',
            'exam' => 'Examen',
            'exercise' => 'Exercice',
            default => ucfirst($this->type)
        };
    }

    public function studentHasSubmitted($studentId)
    {
        return $this->submissions()
                    ->where('student_id', $studentId)
                    ->where('status', '!=', 'draft')
                    ->exists();
    }

    public function getStudentSubmission($studentId)
    {
        return $this->submissions()
                    ->where('student_id', $studentId)
                    ->first();
    }

    public function calculateLatePenalty($submittedAt)
    {
        if (!$this->allow_late_submission) return 0;
        if ($submittedAt <= $this->due_date) return 0;
        
        return $this->late_penalty_percentage;
    }
}

// Modèle AssignmentSubmission (à créer séparément si nécessaire)
class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'attachment_path',
        'submitted_at',
        'points_earned',
        'feedback',
        'status',
        'is_late',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'points_earned' => 'decimal:2',
        'is_late' => 'boolean',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isLate()
    {
        return $this->is_late;
    }

    public function isGraded()
    {
        return $this->status === 'graded';
    }

    public function getPercentage()
    {
        if (!$this->assignment || $this->assignment->total_points == 0) return 0;
        
        return round(($this->points_earned / $this->assignment->total_points) * 100, 2);
    }

    public function getGrade()
    {
        if (!$this->assignment || $this->assignment->total_points == 0) return 0;
        
        return round(($this->points_earned / $this->assignment->total_points) * 20, 2);
    }
}