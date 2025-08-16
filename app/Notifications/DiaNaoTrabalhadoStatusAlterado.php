<?php

namespace App\Notifications;

use App\Models\DiaNaoTrabalhado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiaNaoTrabalhadoStatusAlterado extends Notification implements ShouldQueue
{
    use Queueable;

    protected $diaNaoTrabalhado;
    protected $statusAnterior;

    public function __construct(DiaNaoTrabalhado $diaNaoTrabalhado, $statusAnterior = null)
    {
        $this->diaNaoTrabalhado = $diaNaoTrabalhado;
        $this->statusAnterior = $statusAnterior;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->diaNaoTrabalhado->status_label;
        $dataAusencia = $this->diaNaoTrabalhado->data_ausencia->format('d/m/Y');
        $nomeEmpresa = $this->diaNaoTrabalhado->colaborador->company->name ?? 'Empresa';

        $subject = "Solicitação de Ausência - Status: {$statusLabel}";
        
        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Olá, ' . $this->diaNaoTrabalhado->colaborador->nome_completo . '!')
            ->line("Sua solicitação de ausência para o dia **{$dataAusencia}** foi **{$statusLabel}**.")
            ->line("**Motivo:** {$this->diaNaoTrabalhado->motivo}");

        if ($this->diaNaoTrabalhado->isAprovado()) {
            $message->line('✅ Sua solicitação foi aprovada! Você pode se ausentar na data solicitada.');
        } elseif ($this->diaNaoTrabalhado->isRecusado()) {
            $message->line('❌ Sua solicitação foi recusada.');
        }

        if ($this->diaNaoTrabalhado->observacoes_empresa) {
            $message->line("**Observações da empresa:** {$this->diaNaoTrabalhado->observacoes_empresa}");
        }

        if ($this->diaNaoTrabalhado->aprovado_por && $this->diaNaoTrabalhado->aprovado_em) {
            $aprovadoPor = $this->diaNaoTrabalhado->aprovadoPor->name ?? 'Gestor';
            $dataAprovacao = $this->diaNaoTrabalhado->aprovado_em->format('d/m/Y H:i');
            $message->line("**Processado por:** {$aprovadoPor} em {$dataAprovacao}");
        }

        $message->line("Para mais informações, acesse sua área de colaborador.")
            ->salutation("Atenciosamente,\n{$nomeEmpresa}");

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'dia_nao_trabalhado_id' => $this->diaNaoTrabalhado->id,
            'status' => $this->diaNaoTrabalhado->status,
            'status_anterior' => $this->statusAnterior,
            'data_ausencia' => $this->diaNaoTrabalhado->data_ausencia,
            'motivo' => $this->diaNaoTrabalhado->motivo,
        ];
    }
}
