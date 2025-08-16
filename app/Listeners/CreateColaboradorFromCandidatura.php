<?php

namespace App\Listeners;

use App\Events\CandidaturaMovedToContratado;
use App\Models\Colaborador;
use App\Models\Candidato;
use Illuminate\Support\Facades\Log;

class CreateColaboradorFromCandidatura
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CandidaturaMovedToContratado $event): void
    {
        try {
            $candidatura = $event->candidatura;
            
            Log::info("Listener chamado para candidatura {$candidatura->id}");
            Log::info("Estado atual: " . ($event->newState ? $event->newState->nome : 'null'));
            
            // Verificar se o estado é "Contratado"
            if (!$event->newState || strtolower($event->newState->nome) !== 'contratado') {
                Log::info("Estado não é 'contratado', saindo do listener");
                return;
            }
            
            // Verificar se já existe um colaborador para esta candidatura
            if ($candidatura->colaborador) {
                Log::info("Colaborador já existe para candidatura {$candidatura->id}");
                return;
            }
            
            Log::info("Criando colaborador para candidatura {$candidatura->id}");
            
            // Carregar relacionamentos necessários
            $candidatura->load('oportunidade.company');
            
            // Verificar se a oportunidade e empresa existem
            if (!$candidatura->oportunidade || !$candidatura->oportunidade->company) {
                Log::error("Oportunidade ou empresa não encontrada para candidatura {$candidatura->id}");
                return;
            }
            
            Log::info("Company ID: {$candidatura->oportunidade->company_id}");
            
            // Criar colaborador automaticamente
            $colaborador = Colaborador::create([
                'company_id' => $candidatura->oportunidade->company_id,
                'candidatura_id' => $candidatura->id,
                'nome_completo' => $candidatura->nome . ' ' . ($candidatura->apelido ?? ''),
                'email_pessoal' => $candidatura->email,
                'posicao' => $candidatura->oportunidade->titulo,
                'departamento' => 'Geral',
                'data_inicio_contrato' => now()->format('Y-m-d'),
                // Outros campos ficam null para serem preenchidos pela empresa
            ]);
            
            Log::info("Colaborador criado automaticamente", [
                'colaborador_id' => $colaborador->id,
                'candidatura_id' => $candidatura->id,
                'company_id' => $candidatura->oportunidade->company_id,
            ]);
            
            // Se a candidatura tem um candidato_id, criar/atualizar o perfil de candidato
            if ($candidatura->candidato_id) {
                $candidato = Candidato::find($candidatura->candidato_id);
                if ($candidato) {
                    Log::info("Candidato {$candidato->id} agora tem acesso à área de colaborador");
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar colaborador automaticamente: ' . $e->getMessage(), [
                'candidatura_id' => $event->candidatura->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
