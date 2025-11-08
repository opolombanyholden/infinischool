<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'bio',
        'birth_date', 'address', 'city', 'country', 'role', 'status',
        'specialization', 'qualifications', 'hourly_rate', 'is_approved',
        'student_number', 'level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'is_approved' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    // ============================================
    // RELATIONS
    // ============================================

    /**
     * Inscriptions de l'étudiant
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    /**
     * Matières enseignées (pour enseignants)
     */
    public function teachingSubjects()
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }

    /**
     * Classes enseignées (pour enseignants)
     */
    public function teachingClasses()
    {
        return $this->hasMany(ClassModel::class, 'teacher_id');
    }

    /**
     * Cours donnés (pour enseignants)
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /**
     * Messages envoyés
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages reçus
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Notes de l'étudiant
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    /**
     * Notes données par l'enseignant
     */
    public function givenGrades()
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }

    /**
     * Certificats de l'étudiant
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'student_id');
    }

    /**
     * Paiements de l'étudiant
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    /**
     * Devoirs créés par l'enseignant
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    /**
     * Soumissions de devoirs de l'étudiant
     */
    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    /**
     * Ressources créées par l'enseignant
     */
    public function resources()
    {
        return $this->hasMany(Resource::class, 'teacher_id');
    }

    /**
     * Notifications de l'utilisateur
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeTeachers($query)
    {
        return $query->where('role', 'teacher');
    }

    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    // ============================================
    // HELPERS
    // ============================================

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isApproved()
    {
        return $this->is_approved === true;
    }

    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->country}";
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar 
            ? asset('storage/' . $this->avatar) 
            : asset('images/default-avatar.png');
    }
}