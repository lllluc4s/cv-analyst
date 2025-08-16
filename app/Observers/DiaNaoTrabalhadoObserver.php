<?php

namespace App\Observers;

use App\Models\DiaNaoTrabalhado;
use App\Notifications\DiaNaoTrabalhadoStatusAlterado;
use Illuminate\Support\Facades\Notification;

class DiaNaoTrabalhadoObserver
{
    /**
     * Handle the DiaNaoTrabalhado "updated" event.
     */
    public function updated(DiaNaoTrabalhado $diaNaoTrabalhado): void
    {
        // Verifica se o status foi alterado
        if ($diaNaoTrabalhado->wasChanged('status')) {
            $statusAnterior = $diaNaoTrabalhado->getOriginal('status');
            $statusAtual = $diaNaoTrabalhado->status;
            
            // Só envia notificação se o status mudou para aprovado ou recusado
            if (in_array($statusAtual, [DiaNaoTrabalhado::STATUS_APROVADO, DiaNaoTrabalhado::STATUS_RECUSADO])) {
                // Enviar notificação por email para o colaborador
                if ($diaNaoTrabalhado->colaborador && $diaNaoTrabalhado->colaborador->email_pessoal) {
                    try {
                        Notification::route('mail', $diaNaoTrabalhado->colaborador->email_pessoal)
                            ->notify(new DiaNaoTrabalhadoStatusAlterado($diaNaoTrabalhado, $statusAnterior));
                    } catch (\Exception $e) {
                        // Log do erro, mas não interrompe o processo
                        \Log::error('Erro ao enviar notificação de dia não trabalhado: ' . $e->getMessage());
                    }
                }
            }
        }
    }
}
