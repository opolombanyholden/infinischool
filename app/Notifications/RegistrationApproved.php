<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Inscription Validée - InfiniSchool')
            ->greeting('Félicitations ' . $notifiable->name . ' !')
            ->line('Nous avons le plaisir de vous informer que votre inscription sur InfiniSchool a été validée.')
            ->line('Vous pouvez désormais accéder à votre espace e-learning et commencer votre formation.')
            ->line('**Votre numéro étudiant** : ' . $notifiable->student_number)
            ->action('Accéder à Mon Espace', url('/login'))
            ->line('Nous vous souhaitons un excellent parcours d\'apprentissage !')
            ->salutation('L\'équipe InfiniSchool');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'registration_approved',
            'title' => 'Inscription validée',
            'message' => 'Votre inscription a été validée. Bienvenue sur InfiniSchool !',
            'action_url' => url('/student/dashboard'),
        ];
    }
}
