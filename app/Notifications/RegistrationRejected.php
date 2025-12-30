<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRejected extends Notification
{
    use Queueable;

    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(?string $reason = null)
    {
        $this->reason = $reason;
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
        $mail = (new MailMessage)
            ->subject('Information concernant votre inscription - InfiniSchool')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous avons examiné votre demande d\'inscription sur InfiniSchool.');

        if ($this->reason) {
            $mail->line('Malheureusement, nous ne sommes pas en mesure de valider votre inscription pour le moment.')
                ->line('**Motif** : ' . $this->reason);
        } else {
            $mail->line('Malheureusement, votre inscription n\'a pas pu être validée.');
        }

        $mail->line('Si vous pensez qu\'il s\'agit d\'une erreur ou si vous souhaitez obtenir plus d\'informations, n\'hésitez pas à nous contacter.')
            ->action('Nous Contacter', url('/contact'))
            ->salutation('L\'équipe InfiniSchool');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'registration_rejected',
            'title' => 'Inscription non validée',
            'message' => $this->reason ?? 'Votre inscription n\'a pas été validée.',
            'action_url' => url('/contact'),
        ];
    }
}
