<?php

namespace App\Http\Controllers;

use App\Models\DiaNaoTrabalhado;
use App\Models\Colaborador;
use App\Services\DiasNaoTrabalhadosExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DiaNaoTrabalhadoController extends Controller
{
    protected $exportService;

    public function __construct(DiasNaoTrabalhadosExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    // Métodos para colaboradores
    public function index(Request $request)
    {
        $colaboradorId = $request->input('colaborador_id');
        
        $query = DiaNaoTrabalhado::with(['colaborador', 'aprovadoPor']);
        
        if ($colaboradorId) {
            $query->where('colaborador_id', $colaboradorId);
        }
        
        $diasNaoTrabalhados = $query->orderBy('data_ausencia', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $diasNaoTrabalhados
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'colaborador_id' => 'required|exists:colaboradores,id',
            'data_ausencia' => 'required|date|after_or_equal:today',
            'motivo' => 'required|string|max:500',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['colaborador_id', 'data_ausencia', 'motivo']);
        $data['status'] = DiaNaoTrabalhado::STATUS_PENDENTE;

        // Upload do documento se fornecido
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('dias_nao_trabalhados', $filename, 'public');
            $data['documento_path'] = $path;
        }

        $diaNaoTrabalhado = DiaNaoTrabalhado::create($data);

        return response()->json([
            'success' => true,
            'data' => $diaNaoTrabalhado->load(['colaborador', 'aprovadoPor']),
            'message' => 'Solicitação de ausência enviada com sucesso!'
        ], 201);
    }

    public function show($id)
    {
        $diaNaoTrabalhado = DiaNaoTrabalhado::with(['colaborador', 'aprovadoPor'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $diaNaoTrabalhado
        ]);
    }

    // Métodos para empresas
    public function indexPorEmpresa(Request $request)
    {
        // Obter a empresa autenticada
        $company = $request->user();
        
        if (!$company || !($company instanceof \App\Models\Company)) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Apenas empresas podem acessar esta funcionalidade.'
            ], 403);
        }

        $companyId = $company->id;

        $query = DiaNaoTrabalhado::with(['colaborador', 'aprovadoPor'])
            ->whereHas('colaborador', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        $status = $request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        $diasNaoTrabalhados = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $diasNaoTrabalhados
        ]);
    }

    public function aprovar(Request $request, $id)
    {
        $company = $request->user();
        $diaNaoTrabalhado = DiaNaoTrabalhado::with('colaborador')
            ->whereHas('colaborador', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->findOrFail($id);

        if (!$diaNaoTrabalhado->isPendente()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas solicitações pendentes podem ser aprovadas'
            ], 400);
        }

        $observacoes = $request->input('observacoes_empresa');

        $diaNaoTrabalhado->aprovar($company->id, $observacoes);

        return response()->json([
            'success' => true,
            'data' => $diaNaoTrabalhado->load(['colaborador', 'aprovadoPor']),
            'message' => 'Solicitação aprovada com sucesso!'
        ]);
    }

    public function recusar(Request $request, $id)
    {
        $company = $request->user();
        $diaNaoTrabalhado = DiaNaoTrabalhado::with('colaborador')
            ->whereHas('colaborador', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->findOrFail($id);

        if (!$diaNaoTrabalhado->isPendente()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas solicitações pendentes podem ser recusadas'
            ], 400);
        }

        $observacoes = $request->input('observacoes_empresa');

        $diaNaoTrabalhado->recusar($company->id, $observacoes);

        return response()->json([
            'success' => true,
            'data' => $diaNaoTrabalhado->load(['colaborador', 'aprovadoPor']),
            'message' => 'Solicitação recusada com sucesso!'
        ]);
    }

    public function downloadDocumento($id)
    {
        $diaNaoTrabalhado = DiaNaoTrabalhado::findOrFail($id);

        if (!$diaNaoTrabalhado->documento_path) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum documento encontrado'
            ], 404);
        }

        $filePath = storage_path('app/public/' . $diaNaoTrabalhado->documento_path);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo não encontrado'
            ], 404);
        }

        return response()->download($filePath);
    }

    public function estatisticas(Request $request)
    {
        $companyId = $request->input('company_id');
        
        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'ID da empresa é obrigatório'
            ], 400);
        }

        $query = DiaNaoTrabalhado::whereHas('colaborador', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });

        $estatisticas = [
            'total' => $query->count(),
            'pendentes' => $query->where('status', DiaNaoTrabalhado::STATUS_PENDENTE)->count(),
            'aprovadas' => $query->where('status', DiaNaoTrabalhado::STATUS_APROVADO)->count(),
            'recusadas' => $query->where('status', DiaNaoTrabalhado::STATUS_RECUSADO)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $estatisticas
        ]);
    }

    /**
     * Exportar dados para PDF
     */
    public function exportarPdf(Request $request)
    {
        $companyId = $request->input('company_id');
        
        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'ID da empresa é obrigatório'
            ], 400);
        }

        try {
            $filtros = $request->only(['status', 'data_inicio', 'data_fim', 'colaborador_id']);
            $filepath = $this->exportService->exportarPdf($companyId, $filtros);
            
            return response()->download($filepath, basename($filepath), [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar dados para Excel
     */
    public function exportarExcel(Request $request)
    {
        $companyId = $request->input('company_id');
        
        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'ID da empresa é obrigatório'
            ], 400);
        }

        try {
            $filtros = $request->only(['status', 'data_inicio', 'data_fim', 'colaborador_id']);
            $filepath = $this->exportService->exportarExcel($companyId, $filtros);
            
            return response()->download($filepath, basename($filepath), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}
