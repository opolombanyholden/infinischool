<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'course_id',
        'subject_id',
        'teacher_id',
        'grade',
        'max_grade',
        'type',
        'weight',
        'description',
        'graded_at',
        'comments',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'max_grade' => 'decimal:2',
        'weight' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    // Relations
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

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

    // Scopes
    public function scopePassed($query, $passingGrade = 10)
    {
        return $query->whereRaw('(grade / max_grade) * 20 >= ?', [$passingGrade]);
    }

    public function scopeFailed($query, $passingGrade = 10)
    {
        return $query->whereRaw('(grade / max_grade) * 20 < ?', [$passingGrade]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeExams($query)
    {
        return $query->where('type', 'exam');
    }

    public function scopeQuizzes($query)
    {
        return $query->where('type', 'quiz');
    }

    public function scopeAssignments($query)
    {
        return $query->where('type', 'assignment');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('graded_at', '>=', now()->subDays($days));
    }

    // Helpers
    public function getPercentage()
    {
        if ($this->max_grade == 0) return 0;
        return round(($this->grade / $this->max_grade) * 100, 2);
    }

    public function getGradeOn20()
    {
        if ($this->max_grade == 0) return 0;
        return round(($this->grade / $this->max_grade) * 20, 2);
    }

    public function isPassed($passingGrade = 10)
    {
        return $this->getGradeOn20() >= $passingGrade;
    }

    public function isFailed($passingGrade = 10)
    {
        return $this->getGradeOn20() < $passingGrade;
    }

    public function getLetterGrade()
    {
        $percentage = $this->getPercentage();
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    public function getMention()
    {
        $gradeOn20 = $this->getGradeOn20();
        
        if ($gradeOn20 >= 16) return 'Très Bien';
        if ($gradeOn20 >= 14) return 'Bien';
        if ($gradeOn20 >= 12) return 'Assez Bien';
        if ($gradeOn20 >= 10) return 'Passable';
        return 'Insuffisant';
    }

    public function getWeightedGrade()
    {
        return $this->grade * ($this->weight / 100);
    }
}