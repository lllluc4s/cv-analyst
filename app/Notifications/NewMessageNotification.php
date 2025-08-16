<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $senderName = $this->message->sender_type === 'company' ? 
            $this->message->company->name : 
            $this->message->candidato->nome_completo;

        $appUrl = config('app.frontend_url', config('app.url'));
        $loginUrl = $appUrl . '/login';

        return (new MailMessage)
            ->subject('Nova mensagem no CV Analyst')
            ->greeting('Olá!')
            ->line("Você recebeu uma nova mensagem de {$senderName}.")
            ->line('Para ver e responder à mensagem, acesse sua área reservada no CV Analyst.')
            ->action('Ver Mensagens', $loginUrl)
            ->line('Este email é apenas uma notificação. O conteúdo da mensagem está disponível apenas na sua área reservada.')
            ->line('Obrigado por usar o CV Analyst!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $this->message->sender_type === 'company' ? 
                $this->message->company->name : 
                $this->message->candidato->nome_completo,
        ];
    }
}
