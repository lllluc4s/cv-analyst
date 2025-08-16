<?php

namespace App\Jobs;

use App\Models\Candidatura;
use App\Models\BoardState;
use App\Models\KanbanTransition;
use App\Models\Oportunidade;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCandidateStateEmail implements ShouldQueue
{
    use Queueable;

    protected $candidatura;
    protected $emailData;
    protected $transitionId;

    /**
     * Create a new job instance.
     */
    public function __construct(Candidatura $candidatura, array $emailData, $transitionId = null)
    {
        $this->candidatura = $candidatura;
        $this->emailData = $emailData;
        $this->transitionId = $transitionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // PRIVACIDADE: Verificar se candidato deu consentimento para emails
            if (!$this->candidatura->consentimento_emails || !$this->candidatura->pode_ser_contactado) {
                Log::info('Email não enviado - candidato não deu consentimento', [
                    'candidatura_id' => $this->candidatura->id,
                    'consentimento_emails' => $this->candidatura->consentimento_emails,
                    'pode_ser_contactado' => $this->candidatura->pode_ser_contactado
                ]);
                return;
            }

            // Obter dados da transição se disponível
            $transition = null;
            if ($this->transitionId) {
                $transition = KanbanTransition::find($this->transitionId);
            }
            
            // Buscar a oportunidade associada à candidatura
            $oportunidade = Oportunidade::find($this->candidatura->oportunidade_id);
            if (!$oportunidade) {
                Log::error('Email não enviado - oportunidade não encontrada', [
                    'candidatura_id' => $this->candidatura->id,
                    'oportunidade_id' => $this->candidatura->oportunidade_id
                ]);
                return;
            }

            // Processar template do assunto
            $subject = $this->processTemplate($this->emailData['subject']);
            
            // Processar template do corpo
            $body = $this->processTemplate($this->emailData['body']);

            // Enviar email
            Mail::html($body, function ($message) use ($subject) {
                $message->to($this->candidatura->email)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info('Email enviado para candidato', [
                'candidatura_id' => $this->candidatura->id,
                'email' => $this->candidatura->email,
                'transitionId' => $this->transitionId
            ]);
            
            // Atualizar a transição para marcar que o email foi enviado
            if ($transition) {
                $transition->email_sent = true;
                $transition->save();
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar email para candidato', [
                'candidatura_id' => $this->candidatura->id,
                'email' => $this->candidatura->email,
                'erro' => $e->getMessage()
            ]);
        }
    }

    /**
     * Processar template substituindo placeholders
     */
    private function processTemplate(string $template): string
    {
        // Obter a oportunidade associada à candidatura
        $oportunidade = Oportunidade::find($this->candidatura->oportunidade_id);
        
        $replacements = [
            '{nome}' => $this->candidatura->nome,
            '{oportunidade}' => $oportunidade ? $oportunidade->titulo : 'N/A',
            '{empresa}' => $oportunidade && $oportunidade->company ? $oportunidade->company->nome : 'N/A',
            '{link}' => $oportunidade ? route('oportunidade.public', $oportunidade->slug) : '#'
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
