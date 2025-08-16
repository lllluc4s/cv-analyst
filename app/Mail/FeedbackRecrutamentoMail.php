<?php

namespace App\Mail;

use App\Models\FeedbackRecrutamento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackRecrutamentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public FeedbackRecrutamento $feedback;

    /**
     * Create a new message instance.
     */
    public function __construct(FeedbackRecrutamento $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Feedback sobre o Processo de Recrutamento - ' . $this->feedback->oportunidade->titulo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback-recrutamento',
            with: [
                'feedback' => $this->feedback,
                'colaborador' => $this->feedback->colaborador,
                'oportunidade' => $this->feedback->oportunidade,
                'company' => $this->feedback->oportunidade->company,
                'linkFeedback' => config('app.frontend_url') . '/feedback-recrutamento/' . $this->feedback->token
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
