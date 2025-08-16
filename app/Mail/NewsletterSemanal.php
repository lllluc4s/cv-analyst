<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterSemanal extends Mailable
{
    use Queueable, SerializesModels;

    public $dadosNewsletter;
    public $nomeCandidato;

    /**
     * Create a new message instance.
     */
    public function __construct($dadosNewsletter, $nomeCandidato)
    {
        $this->dadosNewsletter = $dadosNewsletter;
        $this->nomeCandidato = $nomeCandidato;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Newsletter CV Analyst - ' . $this->dadosNewsletter['total_oportunidades'] . ' Novas Oportunidades',
            from: config('mail.from.address', 'noreply@cvanalyst.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-semanal',
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
