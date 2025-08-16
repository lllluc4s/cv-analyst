<?php

namespace App\Http\Controllers;

use App\Models\Candidatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function getTendencias(Request $request)
    {
        // Validar parâmetros de filtro
        $request->validate([
            'data_inicio' => 'sometimes|date',
            'data_fim' => 'sometimes|date|after_or_equal:data_inicio'
        ]);

        // Obter apenas candidaturas das oportunidades da empresa logada
        $company = $request->user();
        $oportunidadeIds = $company->oportunidades()->pluck('id');

        $query = Candidatura::whereIn('oportunidade_id', $oportunidadeIds);

        // Aplicar filtro por período se fornecido
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->where('created_at', '>=', $request->data_inicio . ' 00:00:00');
        }
        
        if ($request->has('data_fim') && $request->data_fim) {
            $query->where('created_at', '<=', $request->data_fim . ' 23:59:59');
        }

        // Análise temporal de candidaturas por mês (compatível com SQLite)
        $candidaturasPorMes = $query->selectRaw('
                strftime("%Y-%m", created_at) as mes,
                COUNT(*) as total_candidaturas
            ')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Top skills mais encontrados
        $skillsQuery = Candidatura::whereIn('oportunidade_id', $oportunidadeIds)
            ->whereNotNull('skills_extraidas')
            ->where('skills_extraidas', '!=', '[]');

        // Aplicar mesmo filtro por período para skills
        if ($request->has('data_inicio') && $request->data_inicio) {
            $skillsQuery->where('created_at', '>=', $request->data_inicio . ' 00:00:00');
        }
        
        if ($request->has('data_fim') && $request->data_fim) {
            $skillsQuery->where('created_at', '<=', $request->data_fim . ' 23:59:59');
        }

        $skillsData = $skillsQuery->get();

        $skillsCount = [];
        foreach ($skillsData as $candidatura) {
            if (is_array($candidatura->skills_extraidas)) {
                foreach ($candidatura->skills_extraidas as $skill) {
                    $skillLower = strtolower(trim($skill));
                    if (!empty($skillLower)) {
                        $skillsCount[$skillLower] = ($skillsCount[$skillLower] ?? 0) + 1;
                    }
                }
            }
        }

        // Ordenar skills por frequência
        arsort($skillsCount);
        $topSkills = array_slice($skillsCount, 0, 20, true);

        // Skills por mês (tendências temporais)
        $skillsPorMes = [];
        $candidaturasPorMesQuery = Candidatura::whereIn('oportunidade_id', $oportunidadeIds)
            ->whereNotNull('skills_extraidas')
            ->where('skills_extraidas', '!=', '[]');

        // Aplicar mesmo filtro por período para skills por mês
        if ($request->has('data_inicio') && $request->data_inicio) {
            $candidaturasPorMesQuery->where('created_at', '>=', $request->data_inicio . ' 00:00:00');
        }
        
        if ($request->has('data_fim') && $request->data_fim) {
            $candidaturasPorMesQuery->where('created_at', '<=', $request->data_fim . ' 23:59:59');
        }

        $candidaturasPorMesData = $candidaturasPorMesQuery
            ->selectRaw('strftime("%Y-%m", created_at) as mes, skills_extraidas')
            ->orderBy('mes')
            ->get()
            ->groupBy('mes');

        foreach ($candidaturasPorMesData as $mes => $candidaturas) {
            $skillsDoMes = [];
            foreach ($candidaturas as $candidatura) {
                if (is_array($candidatura->skills_extraidas)) {
                    foreach ($candidatura->skills_extraidas as $skill) {
                        $skillLower = strtolower(trim($skill));
                        if (!empty($skillLower)) {
                            $skillsDoMes[$skillLower] = ($skillsDoMes[$skillLower] ?? 0) + 1;
                        }
                    }
                }
            }
            $skillsPorMes[$mes] = $skillsDoMes;
        }

        // Calcular totais com base no filtro aplicado
        $totalCandidaturas = $query->count();
        $periodoInicio = $query->min('created_at');
        $periodoFim = $query->max('created_at');

        return response()->json([
            'candidaturas_por_mes' => $candidaturasPorMes,
            'top_skills' => $topSkills,
            'skills_por_mes' => $skillsPorMes,
            'total_candidaturas' => $totalCandidaturas,
            'periodo' => [
                'inicio' => $periodoInicio,
                'fim' => $periodoFim
            ]
        ]);
    }
}
