<?php

namespace App\Http\Controllers;

use App\Models\Colaborador;
use App\Models\Candidatura;
use App\Models\Company;
use App\Models\FeedbackRecrutamento;
use App\Mail\FeedbackRecrutamentoMail;
use App\Services\ContratoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ColaboradorController extends Controller
{
    /**
     * Contratar candidato - criar colaborador
     */
    public function contratarCandidato(Request $request, $candidaturaId)
    {
        $request->validate([
            'data_inicio_contrato' => 'required|date',
            'data_fim_contrato' => 'nullable|date|after:data_inicio_contrato',
            'vencimento' => 'nullable|numeric|min:0',
            'numero_contribuinte' => 'nullable|string|max:20',
            'numero_seguranca_social' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:50',
            'posicao' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
        ]);

        // Verificar se a candidatura existe e pertence à empresa
        $company = $request->user(); // Usar user() em vez de Auth::guard('company')
        
        $candidatura = Candidatura::with('candidato')
            ->whereHas('oportunidade', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })->findOrFail($candidaturaId);

        // Verificar se já não foi contratado
        if ($candidatura->isContratado()) {
            return response()->json([
                'message' => 'Este candidato já foi contratado.'
            ], 400);
        }

        // Criar colaborador
        $colaborador = Colaborador::create([
            'company_id' => $company->id,
            'candidatura_id' => $candidatura->id,
            'nome_completo' => $candidatura->nome . ' ' . ($candidatura->apelido ?? ''),
            'email_pessoal' => $candidatura->email,
            'numero_contribuinte' => $request->numero_contribuinte,
            'numero_seguranca_social' => $request->numero_seguranca_social,
            'iban' => $request->iban,
            'vencimento' => $request->vencimento,
            'data_inicio_contrato' => $request->data_inicio_contrato,
            'data_fim_contrato' => $request->data_fim_contrato,
            'posicao' => $request->posicao ?? $candidatura->oportunidade->titulo,
            'departamento' => $request->departamento ?? 'Geral',
            'data_nascimento' => $candidatura->candidato->data_nascimento ?? null,
            'foto_url' => $candidatura->candidato->foto_url ?? null,
        ]);

        // Enviar email de feedback automaticamente
        $this->enviarEmailFeedback($colaborador);

        // Criar mensagem automática de boas-vindas para iniciar conversa
        $this->criarMensagemBoasVindas($colaborador, $company);

        return response()->json([
            'message' => 'Candidato contratado com sucesso!',
            'colaborador' => $colaborador->load('candidatura')
        ]);
    }

    /**
     * Listar colaboradores da empresa
     */
    public function index(Request $request)
    {
        $company = $request->user();
        
        $query = Colaborador::where('company_id', $company->id)
            ->with('candidatura.oportunidade');

        // Filtrar por oportunidade se especificado
        if ($request->has('oportunidade_id')) {
            $query->whereHas('candidatura', function($q) use ($request) {
                $q->where('oportunidade_id', $request->oportunidade_id);
            });
        }

        $colaboradores = $query->orderBy('data_inicio_contrato', 'desc')->get();

        return response()->json([
            'data' => $colaboradores
        ]);
    }

    /**
     * Mostrar colaborador específico
     */
    public function show(Request $request, $id)
    {
        $company = $request->user();
        
        $colaborador = Colaborador::where('company_id', $company->id)
            ->with('candidatura.oportunidade')
            ->findOrFail($id);

        return response()->json($colaborador);
    }

    /**
     * Atualizar dados do colaborador
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'numero_contribuinte' => 'nullable|string|max:20',
            'numero_seguranca_social' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:50',
            'vencimento' => 'nullable|numeric|min:0',
            'data_inicio_contrato' => 'required|date',
            'data_fim_contrato' => 'nullable|date|after:data_inicio_contrato',
        ]);

        $company = $request->user();
        
        $colaborador = Colaborador::where('company_id', $company->id)
            ->findOrFail($id);

        $colaborador->update($request->only([
            'numero_contribuinte',
            'numero_seguranca_social',
            'iban',
            'vencimento',
            'data_inicio_contrato',
            'data_fim_contrato',
        ]));

        return response()->json([
            'message' => 'Colaborador atualizado com sucesso!',
            'colaborador' => $colaborador->load('candidatura')
        ]);
    }

    /**
     * Status da candidatura (se foi contratada)
     */
    public function statusCandidatura(Request $request, $candidaturaId)
    {
        $company = $request->user();
        
        $candidatura = Candidatura::whereHas('oportunidade', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->with('colaborador')->findOrFail($candidaturaId);

        return response()->json([
            'is_contratado' => $candidatura->isContratado(),
            'colaborador' => $candidatura->colaborador
        ]);
    }

    /**
     * Gerar contrato de trabalho em DOCX
     */
    public function gerarContrato(Request $request, $id)
    {
        $company = $request->user();
        
        $colaborador = Colaborador::where('company_id', $company->id)
            ->with(['candidatura.oportunidade', 'company'])
            ->findOrFail($id);

        try {
            $contratoService = new ContratoService();
            $filename = $contratoService->gerarContrato($colaborador);
            $filepath = $contratoService->getCaminhoContrato($filename);

            // Verificar se o arquivo foi criado
            if (!file_exists($filepath)) {
                throw new \Exception('Falha ao gerar o arquivo de contrato');
            }

            return response()->download($filepath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar contrato: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao gerar contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar email de feedback de recrutamento
     */
    private function enviarEmailFeedback(Colaborador $colaborador)
    {
        try {
            // Criar registro de feedback
            $feedback = FeedbackRecrutamento::create([
                'colaborador_id' => $colaborador->id,
                'candidatura_id' => $colaborador->candidatura_id,
                'oportunidade_id' => $colaborador->candidatura->oportunidade_id,
                'token' => FeedbackRecrutamento::gerarToken(),
                'enviado_em' => now()
            ]);

            // Carregar relacionamentos
            $feedback->load(['colaborador', 'candidatura', 'oportunidade.company']);

            // Enviar email
            Mail::to($colaborador->email_pessoal)->send(new FeedbackRecrutamentoMail($feedback));

            Log::info('Email de feedback enviado para colaborador contratado', [
                'colaborador_id' => $colaborador->id,
                'email' => $colaborador->email_pessoal,
                'feedback_id' => $feedback->id
            ]);

        } catch (\Exception $e) {
            // Log do erro mas não interrompe o processo de contratação
            Log::error('Erro ao enviar email de feedback: ' . $e->getMessage(), [
                'colaborador_id' => $colaborador->id,
                'email' => $colaborador->email_pessoal
            ]);
        }
    }

    /**
     * Criar mensagem automática de boas-vindas para candidato contratado
     */
    private function criarMensagemBoasVindas(Colaborador $colaborador, Company $company)
    {
        try {
            // Verificar se o candidato tem um registro no sistema de candidatos
            $candidato = \App\Models\Candidato::where('email', $colaborador->email_pessoal)->first();
            
            if ($candidato) {
                // Criar mensagem de boas-vindas
                \App\Models\Message::create([
                    'company_id' => $company->id,
                    'candidato_id' => $candidato->id,
                    'sender_type' => 'company',
                    'sender_id' => $company->id,
                    'content' => "🎉 Parabéns! Bem-vindo à equipa {$company->name}!\n\nEstamos muito felizes em tê-lo connosco para a posição de {$colaborador->posicao}. A partir de {$colaborador->data_inicio_contrato->format('d/m/Y')}, farás oficialmente parte da nossa equipa.\n\nSe tiveres alguma dúvida ou precisares de alguma informação, não hesites em contactar-nos através deste chat.\n\nMais uma vez, bem-vindo! 🚀",
                    'read_at' => null, // Mensagem não lida
                ]);

                Log::info('Mensagem de boas-vindas criada para colaborador contratado', [
                    'colaborador_id' => $colaborador->id,
                    'candidato_id' => $candidato->id,
                    'company_id' => $company->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar mensagem de boas-vindas: ' . $e->getMessage(), [
                'colaborador_id' => $colaborador->id,
                'company_id' => $company->id
            ]);
        }
    }
}
