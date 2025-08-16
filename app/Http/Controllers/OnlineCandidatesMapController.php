<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnlineCandidatesMapController extends Controller
{
    /**
     * Obter candidatos online para o mapa 3D
     * Esta funcionalidade é apenas para administração
     */
    public function getOnlineCandidates(Request $request)
    {
        try {
            // Verificar se o usuário é uma empresa (autenticado)
            $company = $request->user();
            if (!$company || !$company instanceof Company) {
                return response()->json([
                    'message' => 'Acesso não autorizado'
                ], 403);
            }

            // Obter candidatos que estiveram online nos últimos 5 minutos
            $onlineCandidates = Candidato::online()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->select([
                    'id',
                    'nome',
                    'apelido', 
                    'email',
                    'avatar_url',
                    'foto_url',
                    'profile_photo',
                    'country',
                    'city',
                    'region',
                    'latitude',
                    'longitude',
                    'last_seen_at',
                    'is_online'
                ])
                ->get()
                ->map(function ($candidato) {
                    return [
                        'id' => $candidato->id,
                        'name' => $candidato->nome . ' ' . $candidato->apelido,
                        'email' => $candidato->email,
                        'avatar' => $candidato->avatar_url,
                        'profile_photo' => $candidato->profile_photo,
                        'location' => [
                            'country' => $candidato->country,
                            'city' => $candidato->city,
                            'region' => $candidato->region,
                            'latitude' => (float) $candidato->latitude,
                            'longitude' => (float) $candidato->longitude,
                        ],
                        'last_seen' => $candidato->last_seen_at,
                        'is_online' => $candidato->isOnline(),
                        'status' => $candidato->isOnline() ? 'online' : 'recently_active',
                        'session_duration' => $this->calculateSessionDuration($candidato->last_seen_at)
                    ];
                });

            // Estatísticas adicionais
            $totalOnline = $onlineCandidates->where('is_online', true)->count();
            $totalRecentlyActive = $onlineCandidates->count();
            
            // Agrupar por país para mostrar densidade
            $byCountry = $onlineCandidates->groupBy('location.country')->map(function ($candidates, $country) {
                return [
                    'country' => $country,
                    'count' => $candidates->count(),
                    'candidates' => $candidates->values()
                ];
            })->values();

            return response()->json([
                'candidates' => $onlineCandidates,
                'stats' => [
                    'total_online' => $totalOnline,
                    'total_recently_active' => $totalRecentlyActive,
                    'countries' => $byCountry->count()
                ],
                'by_country' => $byCountry,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter candidatos online: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Calcular duração da sessão baseada no last_seen_at
     */
    private function calculateSessionDuration($lastSeenAt)
    {
        if (!$lastSeenAt) {
            return 'Tempo desconhecido';
        }
        
        $diffInMinutes = now()->diffInMinutes($lastSeenAt);
        
        if ($diffInMinutes < 1) {
            return 'Online agora';
        } elseif ($diffInMinutes < 60) {
            return "Online há {$diffInMinutes} min";
        } elseif ($diffInMinutes < 1440) { // menos de 24 horas
            $hours = floor($diffInMinutes / 60);
            return "Online há {$hours}h";
        } else {
            $days = floor($diffInMinutes / 1440);
            return "Ativo há {$days}d";
        }
    }

    /**
     * Obter estatísticas gerais de atividade
     */
    public function getActivityStats(Request $request)
    {
        try {
            $company = $request->user();
            if (!$company || !$company instanceof Company) {
                return response()->json([
                    'message' => 'Acesso não autorizado'
                ], 403);
            }

            // Candidatos online agora
            $onlineNow = Candidato::online()->count();
            
            // Candidatos online na última hora
            $onlineLastHour = Candidato::where('last_seen_at', '>=', now()->subHour())->count();
            
            // Candidatos online hoje
            $onlineToday = Candidato::whereDate('last_seen_at', today())->count();
            
            // Top países por atividade
            $topCountries = Candidato::whereNotNull('country')
                ->where('last_seen_at', '>=', now()->subDay())
                ->select('country', DB::raw('count(*) as total'))
                ->groupBy('country')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            // Atividade por hora nas últimas 24h
            $hourlyActivity = [];
            for ($i = 23; $i >= 0; $i--) {
                $hour = now()->subHours($i);
                $count = Candidato::whereBetween('last_seen_at', [
                    $hour->startOfHour(),
                    $hour->endOfHour()
                ])->count();
                
                $hourlyActivity[] = [
                    'hour' => $hour->format('H:00'),
                    'count' => $count
                ];
            }

            return response()->json([
                'current_stats' => [
                    'online_now' => $onlineNow,
                    'online_last_hour' => $onlineLastHour,
                    'online_today' => $onlineToday
                ],
                'top_countries' => $topCountries,
                'hourly_activity' => $hourlyActivity,
                'generated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas de atividade: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
