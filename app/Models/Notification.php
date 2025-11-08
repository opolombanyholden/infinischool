<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'is_read',
        'read_at',
        'priority',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeMediumPriority($query)
    {
        return $query->where('priority', 'medium');
    }

    public function scopeLowPriority($query)
    {
        return $query->where('priority', 'low');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helpers
    public function isRead()
    {
        return $this->is_read;
    }

    public function isUnread()
    {
        return !$this->is_read;
    }

    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->is_read = true;
            $this->read_at = now();
            $this->save();
        }
    }

    public function markAsUnread()
    {
        $this->is_read = false;
        $this->read_at = null;
        $this->save();
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isHighPriority()
    {
        return $this->priority === 'high';
    }

    public function isMediumPriority()
    {
        return $this->priority === 'medium';
    }

    public function isLowPriority()
    {
        return $this->priority === 'low';
    }

    public function getIcon()
    {
        return match($this->type) {
            'course' => 'fa-chalkboard-teacher',
            'grade' => 'fa-graduation-cap',
            'message' => 'fa-envelope',
            'payment' => 'fa-credit-card',
            'attendance' => 'fa-calendar-check',
            'certificate' => 'fa-certificate',
            'announcement' => 'fa-bullhorn',
            'reminder' => 'fa-bell',
            'warning' => 'fa-exclamation-triangle',
            'success' => 'fa-check-circle',
            'info' => 'fa-info-circle',
            default => 'fa-bell'
        };
    }

    public function getColor()
    {
        if ($this->priority === 'high') {
            return 'danger';
        }
        
        return match($this->type) {
            'success' => 'success',
            'warning' => 'warning',
            'info' => 'info',
            'grade' => 'primary',
            'payment' => 'success',
            'message' => 'info',
            default => 'secondary'
        };
    }

    public function getTypeLabel()
    {
        return match($this->type) {
            'course' => 'Cours',
            'grade' => 'Note',
            'message' => 'Message',
            'payment' => 'Paiement',
            'attendance' => 'Présence',
            'certificate' => 'Certificat',
            'announcement' => 'Annonce',
            'reminder' => 'Rappel',
            'warning' => 'Avertissement',
            'success' => 'Succès',
            'info' => 'Information',
            default => 'Notification'
        };
    }

    public function getPriorityLabel()
    {
        return match($this->priority) {
            'high' => 'Haute',
            'medium' => 'Moyenne',
            'low' => 'Basse',
            default => 'Normale'
        };
    }

    // Static helpers
    public static function sendToUser($userId, $type, $title, $message, $data = [], $actionUrl = null, $priority = 'medium')
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'priority' => $priority,
        ]);
    }

    public static function sendToMultipleUsers($userIds, $type, $title, $message, $data = [], $actionUrl = null, $priority = 'medium')
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'action_url' => $actionUrl,
                'priority' => $priority,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return self::insert($notifications);
    }
}