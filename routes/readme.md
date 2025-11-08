# 🛤️ ROUTES INFINISCHOOL - DOCUMENTATION

**Date de création** : 30 octobre 2025  
**Projet** : InfiniSchool.com  
**Version** : 1.0

---

## 📋 FICHIERS DE ROUTES CRÉÉS

### 1. **routes/web.php** (Routes Web)
Fichier principal contenant toutes les routes web de l'application.

**Contenu** :
- ✅ Routes publiques (home, formations, contact)
- ✅ Routes d'authentification (login, register, password reset)
- ✅ Routes communes (messages, notifications)
- ✅ Routes Espace Étudiant (10+ sections)
- ✅ Routes Espace Enseignant (12+ sections)
- ✅ Routes Espace Administrateur (8+ sections)

**Total** : ~150+ routes web

---

### 2. **routes/api.php** (Routes API)
Routes API pour interactions AJAX et applications mobiles.

**Contenu** :
- ✅ API Cours (join, status, participants)
- ✅ API Notifications (unread, mark-read)
- ✅ API Chat temps réel (messages, typing)
- ✅ API Présence (check-in, check-out)
- ✅ API Planning (today, week, month)
- ✅ API Ressources (download, track)
- ✅ API Notes (student, subject, class)
- ✅ API Dashboard (stats par rôle)
- ✅ API Recherche (global search)
- ✅ API Upload (avatar, files)
- ✅ Webhooks (Zoom, Stripe)

**Total** : ~80+ routes API

---

### 3. **routes/channels.php** (Broadcasting)
Channels pour événements temps réel (Laravel Echo + Pusher).

**Contenu** :
- ✅ Channels utilisateur privés
- ✅ Channels cours live
- ✅ Channels classe
- ✅ Channels formation
- ✅ Channels admin
- ✅ Channels présence (online users)
- ✅ Channels support
- ✅ Channels analytics
- ✅ Channels communauté
- ✅ Channels typing indicator

**Total** : 25+ channels

---

### 4. **routes/console.php** (Commandes Artisan)
Commandes Artisan personnalisées pour maintenance et automatisation.

**Contenu** :
- ✅ Nettoyage (notifications, sessions, recordings)
- ✅ Notifications automatiques (rappels cours)
- ✅ Génération certificats
- ✅ Statistiques et rapports
- ✅ Sauvegardes
- ✅ Optimisation BDD
- ✅ Synchronisation Zoom
- ✅ Tests intégrations

**Total** : 15+ commandes

---

## 🔐 MIDDLEWARE UTILISÉS

### Middleware Laravel Standard
- ✅ `auth` - Utilisateur authentifié
- ✅ `verified` - Email vérifié
- ✅ `guest` - Utilisateur non authentifié

### Middleware Personnalisés à Créer
- ❌ `role:admin` - Vérifier rôle administrateur
- ❌ `role:teacher` - Vérifier rôle enseignant
- ❌ `role:student` - Vérifier rôle étudiant

**Fichier à créer** : `app/Http/Middleware/CheckRole.php`

---

## 📊 STATISTIQUES DES ROUTES

| Fichier | Routes | Sections | Complexité |
|---------|--------|----------|------------|
| **web.php** | ~150 | 7 | ⭐⭐⭐⭐⭐ |
| **api.php** | ~80 | 12 | ⭐⭐⭐⭐☆ |
| **channels.php** | ~25 | 10 | ⭐⭐⭐☆☆ |
| **console.php** | ~15 | 3 | ⭐⭐⭐☆☆ |
| **TOTAL** | **~270** | **32** | **Élevée** |

---

## 🎯 ROUTES PAR ESPACE UTILISATEUR

### 🏠 PORTAIL PUBLIC (Routes non authentifiées)

#### Pages d'Information
```php
GET  /                          → home
GET  /a-propos                  → about
GET  /enseignants              → teachers
GET  /contact                  → contact
POST /contact                  → contact.send
GET  /formations               → formations.index
GET  /formations/{slug}        → formations.show
```

#### Authentification
```php
GET  /login                    → login (form)
POST /login                    → login (submit)
POST /logout                   → logout
GET  /register                 → register (form)
POST /register                 → register (submit)
GET  /password/reset           → password.request
POST /password/email           → password.email
GET  /password/reset/{token}   → password.reset
POST /password/reset           → password.update
```

---

### 👨‍🎓 ESPACE ÉTUDIANT (Middleware: auth, verified, role:student)

#### Dashboard
```php
GET /student/dashboard → student.dashboard
```

#### Mes Cours
```php
GET  /student/courses              → student.courses.index
GET  /student/courses/{course}     → student.courses.show
POST /student/courses/{course}/join → student.courses.join
GET  /student/courses/{course}/live → student.courses.live
```

#### Planning
```php
GET /student/schedule        → student.schedule
GET /student/schedule/export → student.schedule.export
```

#### Progression
```php
GET /student/progress            → student.progress
GET /student/progress/{subject}  → student.progress.show
```

#### Devoirs
```php
GET  /student/assignments                    → student.assignments.index
GET  /student/assignments/{assignment}       → student.assignments.show
POST /student/assignments/{assignment}/submit → student.assignments.submit
GET  /student/assignments/{assignment}/download → student.assignments.download
```

#### Ressources
```php
GET /student/resources                    → student.resources.index
GET /student/resources/{resource}/download → student.resources.download
GET /student/resources/{resource}/view    → student.resources.view
```

#### Replay / Enregistrements
```php
GET /student/replay                   → student.replay.index
GET /student/replay/{recording}       → student.replay.show
GET /student/replay/{recording}/watch → student.replay.watch
```

#### Notes
```php
GET /student/grades                     → student.grades.index
GET /student/grades/subject/{subject}   → student.grades.by-subject
GET /student/grades/export             → student.grades.export
```

#### Certificats
```php
GET /student/certificates                     → student.certificates.index
GET /student/certificates/{certificate}/download → student.certificates.download
GET /student/certificates/{certificate}/view  → student.certificates.view
```

#### Communauté
```php
GET  /student/community                  → student.community.index
GET  /student/community/topic/{topic}    → student.community.show
POST /student/community/topic            → student.community.create-topic
POST /student/community/topic/{topic}/reply → student.community.reply
```

#### Support
```php
GET  /student/support              → student.support.index
GET  /student/support/create       → student.support.create
POST /student/support              → student.support.store
GET  /student/support/{ticket}     → student.support.show
POST /student/support/{ticket}/reply → student.support.reply
```

#### Profil
```php
GET  /student/profile          → student.profile.index
GET  /student/profile/edit     → student.profile.edit
PUT  /student/profile          → student.profile.update
PUT  /student/profile/password → student.profile.update-password
POST /student/profile/avatar   → student.profile.update-avatar
```

---

### 👨‍🏫 ESPACE ENSEIGNANT (Middleware: auth, verified, role:teacher)

#### Dashboard
```php
GET /teacher/dashboard → teacher.dashboard
```

#### Planning
```php
GET /teacher/schedule        → teacher.schedule
GET /teacher/schedule/export → teacher.schedule.export
```

#### Gestion des Cours
```php
GET    /teacher/courses                          → teacher.courses.index
GET    /teacher/courses/create                   → teacher.courses.create
POST   /teacher/courses                          → teacher.courses.store
GET    /teacher/courses/{course}                 → teacher.courses.show
GET    /teacher/courses/{course}/edit            → teacher.courses.edit
PUT    /teacher/courses/{course}                 → teacher.courses.update
DELETE /teacher/courses/{course}                 → teacher.courses.destroy
POST   /teacher/courses/{course}/start           → teacher.courses.start
POST   /teacher/courses/{course}/end             → teacher.courses.end
GET    /teacher/courses/{course}/live            → teacher.courses.live
POST   /teacher/courses/{course}/generate-zoom   → teacher.courses.generate-zoom
POST   /teacher/courses/{course}/start-recording → teacher.courses.start-recording
POST   /teacher/courses/{course}/stop-recording  → teacher.courses.stop-recording
GET    /teacher/courses/{course}/attendance      → teacher.courses.attendance
POST   /teacher/courses/{course}/attendance      → teacher.courses.save-attendance
```

#### Mes Classes
```php
GET /teacher/classes                  → teacher.classes.index
GET /teacher/classes/{class}          → teacher.classes.show
GET /teacher/classes/{class}/students → teacher.classes.students
GET /teacher/classes/{class}/export   → teacher.classes.export-students
```

#### Ressources
```php
GET    /teacher/resources                  → teacher.resources.index
GET    /teacher/resources/create           → teacher.resources.create
POST   /teacher/resources                  → teacher.resources.store
GET    /teacher/resources/{resource}       → teacher.resources.show
DELETE /teacher/resources/{resource}       → teacher.resources.destroy
GET    /teacher/resources/{resource}/download → teacher.resources.download
```

#### Devoirs
```php
GET    /teacher/assignments                       → teacher.assignments.index
GET    /teacher/assignments/create                → teacher.assignments.create
POST   /teacher/assignments                       → teacher.assignments.store
GET    /teacher/assignments/{assignment}          → teacher.assignments.show
GET    /teacher/assignments/{assignment}/edit     → teacher.assignments.edit
PUT    /teacher/assignments/{assignment}          → teacher.assignments.update
DELETE /teacher/assignments/{assignment}          → teacher.assignments.destroy
GET    /teacher/assignments/{assignment}/submissions → teacher.assignments.submissions
POST   /teacher/assignments/{submission}/grade    → teacher.assignments.grade
```

#### Notes
```php
GET  /teacher/grades                  → teacher.grades.index
GET  /teacher/grades/class/{class}    → teacher.grades.by-class
GET  /teacher/grades/subject/{subject} → teacher.grades.by-subject
POST /teacher/grades                  → teacher.grades.store
PUT  /teacher/grades/{grade}          → teacher.grades.update
DELETE /teacher/grades/{grade}        → teacher.grades.destroy
POST /teacher/grades/bulk-update      → teacher.grades.bulk-update
GET  /teacher/grades/export           → teacher.grades.export
```

#### Analytics
```php
GET /teacher/analytics                → teacher.analytics.index
GET /teacher/analytics/engagement     → teacher.analytics.engagement
GET /teacher/analytics/attendance     → teacher.analytics.attendance
GET /teacher/analytics/performance    → teacher.analytics.performance
GET /teacher/analytics/class/{class}  → teacher.analytics.by-class
```

#### Enregistrements
```php
GET    /teacher/recordings                   → teacher.recordings.index
GET    /teacher/recordings/{recording}       → teacher.recordings.show
DELETE /teacher/recordings/{recording}       → teacher.recordings.destroy
POST   /teacher/recordings/{recording}/publish → teacher.recordings.publish
POST   /teacher/recordings/{recording}/unpublish → teacher.recordings.unpublish
```

---

### 👨‍💼 ESPACE ADMINISTRATEUR (Middleware: auth, verified, role:admin)

#### Dashboard
```php
GET /admin/dashboard → admin.dashboard
```

#### Gestion Utilisateurs
```php
GET    /admin/users                      → admin.users.index
GET    /admin/users/create               → admin.users.create
POST   /admin/users                      → admin.users.store
GET    /admin/users/{user}               → admin.users.show
GET    /admin/users/{user}/edit          → admin.users.edit
PUT    /admin/users/{user}               → admin.users.update
DELETE /admin/users/{user}               → admin.users.destroy
POST   /admin/users/{user}/change-status → admin.users.change-status
POST   /admin/users/{user}/reset-password → admin.users.reset-password
POST   /admin/users/{user}/impersonate  → admin.users.impersonate
GET    /admin/users/export               → admin.users.export
```

#### Gestion Formations
```php
GET    /admin/formations                          → admin.formations.index
GET    /admin/formations/create                   → admin.formations.create
POST   /admin/formations                          → admin.formations.store
GET    /admin/formations/{formation}              → admin.formations.show
GET    /admin/formations/{formation}/edit         → admin.formations.edit
PUT    /admin/formations/{formation}              → admin.formations.update
DELETE /admin/formations/{formation}              → admin.formations.destroy
POST   /admin/formations/{formation}/publish      → admin.formations.publish
POST   /admin/formations/{formation}/unpublish    → admin.formations.unpublish
POST   /admin/formations/{formation}/duplicate    → admin.formations.duplicate
```

#### Gestion Classes
```php
GET  /admin/classes                             → admin.classes.index
GET  /admin/classes/create                      → admin.classes.create
POST /admin/classes                             → admin.classes.store
GET  /admin/classes/{class}                     → admin.classes.show
GET  /admin/classes/{class}/edit                → admin.classes.edit
PUT  /admin/classes/{class}                     → admin.classes.update
DELETE /admin/classes/{class}                   → admin.classes.destroy
GET  /admin/classes/{class}/students            → admin.classes.students
POST /admin/classes/{class}/assign-students     → admin.classes.assign-students
POST /admin/classes/{class}/auto-assign         → admin.classes.auto-assign
POST /admin/classes/{class}/assign-teacher      → admin.classes.assign-teacher
GET  /admin/classes/{class}/schedule            → admin.classes.schedule
POST /admin/classes/{class}/schedule            → admin.classes.update-schedule
```

#### Gestion Enseignants
```php
GET  /admin/teachers                  → admin.teachers.index
GET  /admin/teachers/pending          → admin.teachers.pending
GET  /admin/teachers/{teacher}        → admin.teachers.show
POST /admin/teachers/{teacher}/approve → admin.teachers.approve
POST /admin/teachers/{teacher}/reject  → admin.teachers.reject
POST /admin/teachers/{teacher}/suspend → admin.teachers.suspend
GET  /admin/teachers/{teacher}/classes → admin.teachers.classes
POST /admin/teachers/{teacher}/assign-class → admin.teachers.assign-class
```

#### Finances
```php
GET  /admin/finances                         → admin.finances.index
GET  /admin/finances/payments                → admin.finances.payments
GET  /admin/finances/payment/{payment}       → admin.finances.show-payment
POST /admin/finances/payment/{payment}/refund → admin.finances.refund
GET  /admin/finances/reports                 → admin.finances.reports
GET  /admin/finances/reports/revenue         → admin.finances.revenue-report
GET  /admin/finances/reports/enrollments     → admin.finances.enrollments-report
GET  /admin/finances/export-transactions     → admin.finances.export-transactions
```

#### Système
```php
GET    /admin/system                          → admin.system.index
GET    /admin/system/logs                     → admin.system.logs
GET    /admin/system/logs/{file}              → admin.system.view-log
POST   /admin/system/cache/clear              → admin.system.clear-cache
POST   /admin/system/optimize                 → admin.system.optimize
GET    /admin/system/backups                  → admin.system.backups
POST   /admin/system/backup/create            → admin.system.create-backup
GET    /admin/system/backup/{backup}/download → admin.system.download-backup
DELETE /admin/system/backup/{backup}          → admin.system.delete-backup
GET    /admin/system/maintenance              → admin.system.maintenance
POST   /admin/system/maintenance/enable       → admin.system.enable-maintenance
POST   /admin/system/maintenance/disable      → admin.system.disable-maintenance
```

#### Paramètres
```php
GET  /admin/settings                             → admin.settings.index
GET  /admin/settings/general                     → admin.settings.general
POST /admin/settings/general                     → admin.settings.update-general
GET  /admin/settings/email                       → admin.settings.email
POST /admin/settings/email                       → admin.settings.update-email
POST /admin/settings/email/test                  → admin.settings.test-email
GET  /admin/settings/integrations                → admin.settings.integrations
POST /admin/settings/integrations                → admin.settings.update-integrations
POST /admin/settings/integrations/zoom/test      → admin.settings.test-zoom
POST /admin/settings/integrations/stripe/test    → admin.settings.test-stripe
GET  /admin/settings/security                    → admin.settings.security
POST /admin/settings/security                    → admin.settings.update-security
GET  /admin/settings/notifications               → admin.settings.notifications
POST /admin/settings/notifications               → admin.settings.update-notifications
GET  /admin/settings/enrollments                 → admin.settings.enrollments
POST /admin/settings/enrollments                 → admin.settings.update-enrollments
GET  /admin/settings/payments                    → admin.settings.payments
POST /admin/settings/payments                    → admin.settings.update-payments
```

---

## 🔌 ROUTES API PRINCIPALES

### Authentification
```php
POST /api/login
POST /api/register
POST /api/logout
GET  /api/user  // Utilisateur connecté
```

### Cours
```php
GET  /api/courses                  // Liste des cours
GET  /api/courses/upcoming         // Cours à venir
GET  /api/courses/today           // Cours aujourd'hui
GET  /api/courses/{course}        // Détails d'un cours
POST /api/courses/{course}/join   // Rejoindre un cours
GET  /api/courses/{course}/status // Statut cours (live/scheduled)
GET  /api/courses/{course}/participants // Liste participants
```

### Notifications
```php
GET    /api/notifications              // Toutes les notifications
GET    /api/notifications/unread       // Non lues
GET    /api/notifications/unread-count // Compteur
POST   /api/notifications/{id}/mark-read // Marquer lue
POST   /api/notifications/mark-all-read  // Tout marquer lu
DELETE /api/notifications/{id}           // Supprimer
```

### Chat Temps Réel
```php
GET  /api/chat/conversations          // Liste conversations
GET  /api/chat/messages              // Messages
POST /api/chat/send                  // Envoyer message
POST /api/chat/typing                // Indicateur de saisie
GET  /api/chat/course/{course}/messages // Messages cours
POST /api/chat/course/{course}/send     // Envoyer dans cours
```

### Dashboard
```php
GET /api/dashboard/student/stats           // Stats étudiant
GET /api/dashboard/teacher/stats           // Stats enseignant
GET /api/dashboard/admin/stats             // Stats admin
GET /api/dashboard/student/upcoming-courses // Prochains cours
GET /api/dashboard/student/progress        // Progression
```

---

## 📡 CHANNELS BROADCASTING

### Channels Utilisateur
```php
user.{userId}                // Canal privé utilisateur
notifications.{userId}       // Notifications
chat.{userId}               // Messages privés
```

### Channels Cours
```php
course.{courseId}           // Cours en direct
course.{courseId}.chat      // Chat du cours
course.{courseId}.controls  // Contrôles enseignant
```

### Channels Classe
```php
class.{classId}  // Annonces et événements classe
```

### Channels Présence
```php
online              // Tous les utilisateurs en ligne
online.students     // Étudiants en ligne
online.teachers     // Enseignants en ligne
```

### Channels Admin
```php
admin               // Canal admin général
system.alerts       // Alertes système
system.monitoring   // Monitoring temps réel
```

---

## 🔧 MIDDLEWARE À CRÉER

### 1. CheckRole Middleware

**Fichier** : `app/Http/Middleware/CheckRole.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user() || $request->user()->role !== $role) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}
```

**Enregistrement dans** : `app/Http/Kernel.php`

```php
protected $middlewareAliases = [
    // ...
    'role' => \App\Http\Middleware\CheckRole::class,
];
```

---

## 📝 UTILISATION

### Tester les routes
```bash
# Lister toutes les routes
php artisan route:list

# Lister les routes d'un nom spécifique
php artisan route:list --name=student

# Lister les routes avec un middleware
php artisan route:list --middleware=auth
```

### Générer un cache des routes (production)
```bash
php artisan route:cache
```

### Nettoyer le cache des routes
```bash
php artisan route:clear
```

---

## ✅ CHECKLIST D'INTÉGRATION

### Étapes pour activer les routes

1. **Copier les fichiers de routes**
   ```bash
   cp routes/*.php /path/to/laravel/routes/
   ```

2. **Créer le middleware CheckRole**
   ```bash
   php artisan make:middleware CheckRole
   # Copier le contenu fourni ci-dessus
   ```

3. **Enregistrer le middleware dans Kernel.php**
   ```php
   'role' => \App\Http\Middleware\CheckRole::class,
   ```

4. **Tester les routes**
   ```bash
   php artisan route:list
   ```

5. **Vérifier les conflits**
   ```bash
   php artisan route:list | grep "DUPLICATE"
   ```

6. **Créer les controllers manquants si nécessaire**
   - Les controllers doivent correspondre aux routes
   - Vérifier que tous les controllers importés existent

---

## 🎯 PROCHAINES ÉTAPES

### Priorité Haute 🔴
1. ✅ Routes créées
2. ❌ Créer middleware `CheckRole`
3. ❌ Vérifier que tous les controllers existent
4. ❌ Créer les vues Blade correspondantes
5. ❌ Tester l'authentification multi-rôles

### Priorité Moyenne 🟡
6. ❌ Configurer Laravel Echo pour broadcasting
7. ❌ Tester les webhooks Zoom et Stripe
8. ❌ Implémenter les Form Requests
9. ❌ Ajouter rate limiting sur API

### Priorité Basse 🟢
10. ❌ Créer tests pour routes critiques
11. ❌ Optimiser les requêtes avec eager loading
12. ❌ Documenter l'API avec Swagger/OpenAPI

---

## 📚 RESSOURCES

### Documentation Laravel
- **Routing** : https://laravel.com/docs/10.x/routing
- **Middleware** : https://laravel.com/docs/10.x/middleware
- **Broadcasting** : https://laravel.com/docs/10.x/broadcasting
- **Sanctum** : https://laravel.com/docs/10.x/sanctum

### Outils
- **Laravel Debugbar** : Debug des routes
- **Telescope** : Monitoring des requêtes
- **Postman** : Test des API

---

**Document créé le** : 30 octobre 2025  
**Dernière mise à jour** : 30 octobre 2025  
**Projet** : InfiniSchool.com  
**Routes totales** : ~270 routes  
**Statut** : ✅ Complet

---

**InfiniSchool.com - Routes Backend**  
*"L'architecture qui connecte tout"* 🛤️✨