<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'assessment_type',
        'assessment_title',
        'score',
        'max_score',
        'percentage',
        'feedback',
        'assessment_date',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'assessment_date' => 'date',
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
        return $query->whereRaw('(score / max_score) * 20 >= ?', [$passingGrade]);
    }

    public function scopeFailed($query, $passingGrade = 10)
    {
        return $query->whereRaw('(score / max_score) * 20 < ?', [$passingGrade]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('assessment_type', $type);
    }

    public function scopeExams($query)
    {
        return $query->where('assessment_type', 'Examen');
    }

    public function scopeQuizzes($query)
    {
        return $query->where('assessment_type', 'Quiz');
    }

    public function scopeAssignments($query)
    {
        return $query->where('assessment_type', 'Devoir');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('assessment_date', '>=', now()->subDays($days));
    }

    // Helpers
    public function getPercentage()
    {
        if ($this->max_grade == 0)
            return 0;
        return round(($this->grade / $this->max_grade) * 100, 2);
    }

    public function getGradeOn20()
    {
        if ($this->max_grade == 0)
            return 0;
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

        if ($percentage >= 90)
            return 'A';
        if ($percentage >= 80)
            return 'B';
        if ($percentage >= 70)
            return 'C';
        if ($percentage >= 60)
            return 'D';
        return 'F';
    }

    public function getMention()
    {
        $gradeOn20 = $this->getGradeOn20();

        if ($gradeOn20 >= 16)
            return 'Très Bien';
        if ($gradeOn20 >= 14)
            return 'Bien';
        if ($gradeOn20 >= 12)
            return 'Assez Bien';
        if ($gradeOn20 >= 10)
            return 'Passable';
        return 'Insuffisant';
    }

    public function getWeightedGrade()
    {
        return $this->grade * ($this->weight / 100);
    }
}