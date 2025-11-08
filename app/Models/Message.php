<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'body',
        'is_read',
        'read_at',
        'replied_at',
        'parent_id',
        'attachment_path',
        'priority',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    // Relations
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
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

    public function scopeInbox($query, $userId)
    {
        return $query->where('receiver_id', $userId)
                     ->whereNull('parent_id')
                     ->orderBy('created_at', 'desc');
    }

    public function scopeSent($query, $userId)
    {
        return $query->where('sender_id', $userId)
                     ->whereNull('parent_id')
                     ->orderBy('created_at', 'desc');
    }

    public function scopeConversation($query, $user1Id, $user2Id)
    {
        return $query->where(function($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user1Id)
              ->where('receiver_id', $user2Id);
        })->orWhere(function($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user2Id)
              ->where('receiver_id', $user1Id);
        })->orderBy('created_at', 'asc');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
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

    public function reply($body, $subject = null)
    {
        return self::create([
            'sender_id' => $this->receiver_id,
            'receiver_id' => $this->sender_id,
            'subject' => $subject ?? 'Re: ' . $this->subject,
            'body' => $body,
            'parent_id' => $this->id,
            'priority' => $this->priority,
        ]);
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

    public function getExcerpt($length = 100)
    {
        return \Str::limit(strip_tags($this->body), $length);
    }

    public function isHighPriority()
    {
        return $this->priority === 'high';
    }
}