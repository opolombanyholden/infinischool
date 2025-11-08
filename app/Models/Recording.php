<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'file_path',
        'file_size',
        'duration',
        'format',
        'quality',
        'recorded_at',
        'processed_at',
        'is_public',
        'views_count',
        'download_count',
        'status',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'processed_at' => 'datetime',
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'duration' => 'integer',
        'views_count' => 'integer',
        'download_count' => 'integer',
    ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }

    public function scopePopular($query, $minViews = 100)
    {
        return $query->where('views_count', '>=', $minViews)
                     ->orderBy('views_count', 'desc');
    }

    // Helpers
    public function isPublic()
    {
        return $this->is_public;
    }

    public function isProcessed()
    {
        return $this->status === 'processed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementDownloads()
    {
        $this->increment('download_count');
    }

    public function getFileUrl()
    {
        return $this->file_path 
            ? asset('storage/' . $this->file_path)
            : null;
    }

    public function getFormattedDuration()
    {
        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getFormattedSize()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->file_size;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function markAsProcessed()
    {
        $this->status = 'processed';
        $this->processed_at = now();
        $this->save();
    }

    public function markAsFailed()
    {
        $this->status = 'failed';
        $this->save();
    }
}