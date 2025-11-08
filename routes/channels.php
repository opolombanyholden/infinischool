<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Channels pour les événements en temps réel (Laravel Echo)
| Utilisés pour : chat live, notifications, cours en direct, etc.
|
*/

/*
|--------------------------------------------------------------------------
| Channels Privés Utilisateur
|--------------------------------------------------------------------------
*/

// Canal privé pour l'utilisateur (notifications personnelles)
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Canal pour les notifications de l'utilisateur
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Canal pour les messages privés entre deux utilisateurs
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

/*
|--------------------------------------------------------------------------
| Channels Cours Live
|--------------------------------------------------------------------------
*/

// Canal public pour un cours en direct (participants peuvent écouter)
Broadcast::channel('course.{courseId}', function ($user, $courseId) {
    // Vérifier que l'utilisateur est inscrit au cours ou est l'enseignant
    $course = \App\Models\Course::find($courseId);
    
    if (!$course) {
        return false;
    }
    
    // Si c'est l'enseignant du cours
    if ($user->id === $course->teacher_id) {
        return [
            'id' => $user->id,
            'name' => $user->getFullNameAttribute(),
            'role' => 'teacher',
            'avatar' => $user->avatar,
        ];
    }
    
    // Si c'est un étudiant inscrit
    if ($user->role === 'student') {
        $enrollment = $user->enrollments()
            ->whereHas('class.courses', function ($query) use ($courseId) {
                $query->where('id', $courseId);
            })
            ->where('status', 'active')
            ->exists();
            
        if ($enrollment) {
            return [
                'id' => $user->id,
                'name' => $user->getFullNameAttribute(),
                'role' => 'student',
                'avatar' => $user->avatar,
            ];
        }
    }
    
    return false;
});

// Canal pour le chat d'un cours en direct
Broadcast::channel('course.{courseId}.chat', function ($user, $courseId) {
    // Même logique de vérification que le canal principal
    $course = \App\Models\Course::find($courseId);
    
    if (!$course) {
        return false;
    }
    
    if ($user->id === $course->teacher_id) {
        return [
            'id' => $user->id,
            'name' => $user->getFullNameAttribute(),
            'role' => 'teacher',
            'avatar' => $user->avatar,
        ];
    }
    
    if ($user->role === 'student') {
        $enrollment = $user->enrollments()
            ->whereHas('class.courses', function ($query) use ($courseId) {
                $query->where('id', $courseId);
            })
            ->where('status', 'active')
            ->exists();
            
        if ($enrollment) {
            return [
                'id' => $user->id,
                'name' => $user->getFullNameAttribute(),
                'role' => 'student',
                'avatar' => $user->avatar,
            ];
        }
    }
    
    return false;
});

// Canal pour les contrôles enseignant d'un cours
Broadcast::channel('course.{courseId}.controls', function ($user, $courseId) {
    $course = \App\Models\Course::find($courseId);
    
    if (!$course) {
        return false;
    }
    
    // Seul l'enseignant du cours peut accéder aux contrôles
    return $user->id === $course->teacher_id;
});

/*
|--------------------------------------------------------------------------
| Channels Classe
|--------------------------------------------------------------------------
*/

// Canal pour une classe (annonces, événements)
Broadcast::channel('class.{classId}', function ($user, $classId) {
    $class = \App\Models\ClassModel::find($classId);
    
    if (!$class) {
        return false;
    }
    
    // Si l'utilisateur est un étudiant de la classe
    if ($user->role === 'student') {
        $enrolled = $user->enrollments()
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->exists();
            
        if ($enrolled) {
            return [
                'id' => $user->id,
                'name' => $user->getFullNameAttribute(),
                'role' => 'student',
            ];
        }
    }
    
    // Si l'utilisateur est un enseignant de la classe
    if ($user->role === 'teacher') {
        $teaching = $class->courses()
            ->where('teacher_id', $user->id)
            ->exists();
            
        if ($teaching) {
            return [
                'id' => $user->id,
                'name' => $user->getFullNameAttribute(),
                'role' => 'teacher',
            ];
        }
    }
    
    // Les admins ont accès à toutes les classes
    if ($user->role === 'admin') {
        return [
            'id' => $user->id,
            'name' => $user->getFullNameAttribute(),
            'role' => 'admin',
        ];
    }
    
    return false;
});

/*
|--------------------------------------------------------------------------
| Channels Formation
|--------------------------------------------------------------------------
*/

// Canal pour une formation (notifications aux étudiants inscrits)
Broadcast::channel('formation.{formationId}', function ($user, $formationId) {
    if ($user->role === 'student') {
        $enrolled = $user->enrollments()
            ->where('formation_id', $formationId)
            ->where('status', 'active')
            ->exists();
            
        return $enrolled ? ['id' => $user->id] : false;
    }
    
    // Les admins et enseignants ont accès
    if (in_array($user->role, ['admin', 'teacher'])) {
        return ['id' => $user->id];
    }
    
    return false;
});

/*
|--------------------------------------------------------------------------
| Channels Admin
|--------------------------------------------------------------------------
*/

// Canal pour les admins (alertes système, monitoring)
Broadcast::channel('admin', function ($user) {
    return $user->role === 'admin' ? [
        'id' => $user->id,
        'name' => $user->getFullNameAttribute(),
    ] : false;
});

// Canal pour les alertes système
Broadcast::channel('system.alerts', function ($user) {
    return $user->role === 'admin';
});

// Canal pour le monitoring en temps réel
Broadcast::channel('system.monitoring', function ($user) {
    return $user->role === 'admin';
});

/*
|--------------------------------------------------------------------------
| Channels Présence
|--------------------------------------------------------------------------
*/

// Canal de présence pour voir qui est en ligne
Broadcast::channel('online', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->getFullNameAttribute(),
        'role' => $user->role,
        'avatar' => $user->avatar,
    ];
});

// Canal de présence pour les étudiants en ligne
Broadcast::channel('online.students', function ($user) {
    if ($user->role === 'student') {
        return [
            'id' => $user->id,
            'name' => $user->getFullNameAttribute(),
            'avatar' => $user->avatar,
        ];
    }
    
    return false;
});

// Canal de présence pour les enseignants en ligne
Broadcast::channel('online.teachers', function ($user) {
    if ($user->role === 'teacher') {
        return [
            'id' => $user->id,
            'name' => $user->getFullNameAttribute(),
            'avatar' => $user->avatar,
        ];
    }
    
    return false;
});

/*
|--------------------------------------------------------------------------
| Channels Support
|--------------------------------------------------------------------------
*/

// Canal pour un ticket de support
Broadcast::channel('support.ticket.{ticketId}', function ($user, $ticketId) {
    $ticket = \App\Models\SupportTicket::find($ticketId);
    
    if (!$ticket) {
        return false;
    }
    
    // Le créateur du ticket peut y accéder
    if ($user->id === $ticket->user_id) {
        return ['id' => $user->id, 'role' => 'user'];
    }
    
    // Les admins peuvent accéder à tous les tickets
    if ($user->role === 'admin') {
        return ['id' => $user->id, 'role' => 'admin'];
    }
    
    return false;
});

/*
|--------------------------------------------------------------------------
| Channels Analytics
|--------------------------------------------------------------------------
*/

// Canal pour les analytics d'un cours (enseignant uniquement)
Broadcast::channel('analytics.course.{courseId}', function ($user, $courseId) {
    $course = \App\Models\Course::find($courseId);
    
    if (!$course) {
        return false;
    }
    
    // Seul l'enseignant ou admin peut voir les analytics
    if ($user->id === $course->teacher_id || $user->role === 'admin') {
        return ['id' => $user->id];
    }
    
    return false;
});

// Canal pour les analytics d'une classe
Broadcast::channel('analytics.class.{classId}', function ($user, $classId) {
    $class = \App\Models\ClassModel::find($classId);
    
    if (!$class) {
        return false;
    }
    
    // Enseignants de la classe ou admins
    if ($user->role === 'admin') {
        return ['id' => $user->id];
    }
    
    if ($user->role === 'teacher') {
        $teaching = $class->courses()
            ->where('teacher_id', $user->id)
            ->exists();
            
        return $teaching ? ['id' => $user->id] : false;
    }
    
    return false;
});

/*
|--------------------------------------------------------------------------
| Channels Communauté
|--------------------------------------------------------------------------
*/

// Canal pour le forum de la communauté
Broadcast::channel('community', function ($user) {
    // Tous les utilisateurs authentifiés ont accès
    return [
        'id' => $user->id,
        'name' => $user->getFullNameAttribute(),
        'role' => $user->role,
        'avatar' => $user->avatar,
    ];
});

// Canal pour un topic spécifique du forum
Broadcast::channel('community.topic.{topicId}', function ($user, $topicId) {
    // Tous les utilisateurs authentifiés peuvent voir les topics
    return ['id' => $user->id];
});

/*
|--------------------------------------------------------------------------
| Channels Typing Indicator
|--------------------------------------------------------------------------
*/

// Canal pour l'indicateur de saisie dans les conversations privées
Broadcast::channel('typing.user.{userId}', function ($user, $userId) {
    // L'utilisateur peut voir quand quelqu'un lui écrit
    return (int) $user->id === (int) $userId;
});

// Canal pour l'indicateur de saisie dans les cours
Broadcast::channel('typing.course.{courseId}', function ($user, $courseId) {
    $course = \App\Models\Course::find($courseId);
    
    if (!$course) {
        return false;
    }
    
    // Vérifier l'accès au cours
    if ($user->id === $course->teacher_id) {
        return true;
    }
    
    if ($user->role === 'student') {
        return $user->enrollments()
            ->whereHas('class.courses', function ($query) use ($courseId) {
                $query->where('id', $courseId);
            })
            ->where('status', 'active')
            ->exists();
    }
    
    return false;
});