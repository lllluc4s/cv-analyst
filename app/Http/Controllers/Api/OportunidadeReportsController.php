<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oportunidade;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class OportunidadeReportsController extends Controller
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
     * Obtém relatórios de uma oportunidade
     */
    public function getReports(Request $request, $slug)
    {
        $oportunidade = Oportunidade::where('slug', $slug)->firstOrFail();
        
        // Obter período para o relatório (padrão: 30 dias)
        $days = $request->input('days', 30);
        
        // Usar o serviço de analytics para gerar relatórios
        $reports = $this->analyticsService->getReports($oportunidade, $days);
        
        // Adicionar informações da oportunidade
        $reports['oportunidade'] = $oportunidade;
        
        return response()->json($reports);
    }
}
