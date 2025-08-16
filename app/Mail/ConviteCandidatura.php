<?php

namespace App\Mail;

use App\Models\Candidato;
use App\Models\Oportunidade;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConviteCandidatura extends Mailable
{
    use Queueable, SerializesModels;

    public $candidato;
    public $oportunidade;
    public $company;
    public $linkCandidatura;
    public $mensagemPersonalizada;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Candidato $candidato,
        Oportunidade $oportunidade,
        Company $company,
        string $linkCandidatura,
        ?string $mensagemPersonalizada = null
    ) {
        $this->candidato = $candidato;
        $this->oportunidade = $oportunidade;
        $this->company = $company;
        $this->linkCandidatura = $linkCandidatura;
        $this->mensagemPersonalizada = $mensagemPersonalizada;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Convite especial: {$this->oportunidade->titulo} - {$this->company->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.convite-candidatura',
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
