<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'subject_id',
        'teacher_id',
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'download_count',
        'is_public',
        'order',
        'thumbnail_path',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'order' => 'integer',
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

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDocuments($query)
    {
        return $query->whereIn('type', ['pdf', 'doc', 'docx', 'txt']);
    }

    public function scopeVideos($query)
    {
        return $query->whereIn('type', ['video', 'mp4', 'avi', 'mov']);
    }

    public function scopeImages($query)
    {
        return $query->whereIn('type', ['image', 'jpg', 'jpeg', 'png', 'gif']);
    }

    public function scopeAudios($query)
    {
        return $query->whereIn('type', ['audio', 'mp3', 'wav', 'ogg']);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePopular($query, $minDownloads = 10)
    {
        return $query->where('download_count', '>=', $minDownloads)
                     ->orderBy('download_count', 'desc');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    // Helpers
    public function isPublic()
    {
        return $this->is_public;
    }

    public function isDocument()
    {
        return in_array($this->type, ['pdf', 'doc', 'docx', 'txt', 'document']);
    }

    public function isVideo()
    {
        return in_array($this->type, ['video', 'mp4', 'avi', 'mov']);
    }

    public function isImage()
    {
        return in_array($this->type, ['image', 'jpg', 'jpeg', 'png', 'gif']);
    }

    public function isAudio()
    {
        return in_array($this->type, ['audio', 'mp3', 'wav', 'ogg']);
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

    public function getDownloadUrl()
    {
        return route('resources.download', $this->id);
    }

    public function getThumbnailUrl()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        
        // Retourner une image par défaut selon le type
        return match(true) {
            $this->isDocument() => asset('images/icons/document.png'),
            $this->isVideo() => asset('images/icons/video.png'),
            $this->isAudio() => asset('images/icons/audio.png'),
            default => asset('images/icons/file.png')
        };
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

    public function getIcon()
    {
        return match(true) {
            $this->isDocument() => 'fa-file-pdf',
            $this->isVideo() => 'fa-file-video',
            $this->isImage() => 'fa-file-image',
            $this->isAudio() => 'fa-file-audio',
            default => 'fa-file'
        };
    }

    public function getTypeLabel()
    {
        return match(true) {
            $this->isDocument() => 'Document',
            $this->isVideo() => 'Vidéo',
            $this->isImage() => 'Image',
            $this->isAudio() => 'Audio',
            default => 'Fichier'
        };
    }
}