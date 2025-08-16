<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResendService;
use Illuminate\Support\Facades\Log;

class ResendConfigController extends Controller
{
    protected $resendService;

    public function __construct(ResendService $resendService)
    {
        $this->resendService = $resendService;
    }

    /**
     * Exibe informações de diagnóstico sobre a configuração do Resend
     */
    public function diagnostico()
    {
        $config = $this->resendService->debugResendEnvironment();
        
        // Registra as informações em log
        Log::info('Diagnóstico de configuração do Resend', $config);
        
        return response()->json([
            'message' => 'Diagnóstico de configuração do Resend',
            'config' => $config
        ]);
    }
    
    /**
     * Simula um fluxo completo para uma oportunidade específica
     */
    public function simularFluxo(Request $request)
    {
        $request->validate([
            'oportunidade_id' => 'required|exists:oportunidades,id',
            'force_enable' => 'boolean'
        ]);
        
        // Opcionalmente força habilitar o Resend
        if ($request->has('force_enable') && $request->force_enable) {
            $this->resendService->forcarHabilitar(true);
        }
        
        $resultado = $this->resendService->simularFluxoCompleto($request->oportunidade_id);
        
        return response()->json([
            'message' => $resultado['sucesso'] ? 'Simulação concluída com sucesso' : 'Falha na simulação',
            'resultado' => $resultado
        ]);
    }
}
