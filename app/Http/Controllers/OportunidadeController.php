<?php

namespace App\Http\Controllers;

use App\Models\Oportunidade;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class OportunidadeController extends Controller
{
    /**
     * Serviço de Analytics
     */
    protected $analyticsService;
    
    /**
     * Constructor
     */
    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $oportunidades = Oportunidade::orderBy('created_at', 'desc')->paginate(10);
        return view('oportunidades.index', compact('oportunidades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('oportunidades.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'skills_desejadas' => 'required|array|min:1',
            'skills_desejadas.*' => 'required|string|max:100',
            'publica' => 'boolean'
        ]);

        Oportunidade::create($validated);

        return redirect()->route('oportunidades.index')
            ->with('success', 'Oportunidade criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Oportunidade $oportunidade)
    {
        return view('oportunidades.show', compact('oportunidade'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Oportunidade $oportunidade)
    {
        return view('oportunidades.edit', compact('oportunidade'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Oportunidade $oportunidade)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'skills_desejadas' => 'required|array|min:1',
            'skills_desejadas.*' => 'required|string|max:100',
            'ativa' => 'boolean',
            'publica' => 'boolean'
        ]);

        $oportunidade->update($validated);

        return redirect()->route('oportunidades.index')
            ->with('success', 'Oportunidade atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Oportunidade $oportunidade)
    {
        $oportunidade->delete();

        return redirect()->route('oportunidades.index')
            ->with('success', 'Oportunidade removida com sucesso!');
    }

    /**
     * Display the public page for a job opportunity.
     */
    public function showPublic(Oportunidade $oportunidade)
    {
        // Verificar se a oportunidade está ativa
        if (!$oportunidade->ativa) {
            abort(404);
        }

        // Redirecionar para a página pública no frontend Vue.js em vez de renderizar uma view
        // Adicionar parâmetro para indicar que a visita já foi contabilizada
        $frontendUrl = "http://localhost:5174/oportunidade/{$oportunidade->slug}?tracked=1";
        return redirect()->away($frontendUrl);
    }

    /**
     * Display a listing of public opportunities.
     */
    public function publicIndex()
    {
        $oportunidades = Oportunidade::where('publica', true)
            ->where('ativa', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('oportunidades.public-list', compact('oportunidades'));
    }
    
    /**
     * Get analytics reports for an opportunity
     */
    public function getReports(Oportunidade $oportunidade, Request $request)
    {
        // Verificar se o usuário tem permissão para acessar os relatórios
        // $this->authorize('view', $oportunidade);
        
        // Obter o número de dias para o relatório (padrão: 30)
        $days = $request->input('days', 30);
        
        // Gerar relatórios usando o serviço de analytics
        $reports = $this->analyticsService->getReports($oportunidade, $days);
        
        return response()->json($reports);
    }
    
    /**
     * Get candidaturas for an opportunity
     */
    public function getCandidaturas(Oportunidade $oportunidade)
    {
        // Verificar se o usuário tem permissão para acessar as candidaturas
        // $this->authorize('view', $oportunidade);
        
        $candidaturas = $oportunidade->candidaturas()->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'candidaturas' => $candidaturas
        ]);
    }
}
