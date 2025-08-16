<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Candidatura;

class NovaCandidaturaNotification extends Notification
{
    use Queueable;

    protected $candidatura;

    /**
     * Create a new notification instance.
     */
    public function __construct(Candidatura $candidatura)
    {
        $this->candidatura = $candidatura;
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
        $oportunidade = $this->candidatura->oportunidade;
        
        return (new MailMessage)
                    ->subject('Nova Candidatura - ' . $oportunidade->titulo)
                    ->greeting('Olá ' . $notifiable->name . '!')
                    ->line('Recebeu uma nova candidatura para a oportunidade:')
                    ->line('**' . $oportunidade->titulo . '**')
                    ->line('Candidato: ' . $this->candidatura->nome)
                    ->line('Email: ' . $this->candidatura->email)
                    ->line('Data da candidatura: ' . $this->candidatura->created_at->format('d/m/Y H:i'))
                    ->action('Ver Candidatura', url('/empresas/dashboard'))
                    ->line('Obrigado por usar a nossa plataforma!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'candidatura_id' => $this->candidatura->id,
            'oportunidade_titulo' => $this->candidatura->oportunidade->titulo,
            'candidato_nome' => $this->candidatura->nome,
        ];
    }
}
