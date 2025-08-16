<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oportunidade;
use App\Models\SocialShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialShareController extends Controller
{
    /**
     * Registra uma partilha social
     */
    public function trackShare(Request $request, $slug)
    {
        try {
            $validated = $request->validate([
                'platform' => 'required|string|in:facebook,twitter,linkedin,whatsapp,copy_link',
                'utm_source' => 'nullable|string',
                'utm_medium' => 'nullable|string', 
                'utm_campaign' => 'nullable|string',
                'utm_content' => 'nullable|string'
            ]);

            $oportunidade = Oportunidade::where('slug', $slug)->firstOrFail();

            $socialShare = SocialShare::create([
                'oportunidade_id' => $oportunidade->id,
                'platform' => $validated['platform'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'utm_source' => $validated['utm_source'] ?? $validated['platform'],
                'utm_medium' => $validated['utm_medium'] ?? 'social',
                'utm_campaign' => $validated['utm_campaign'] ?? 'job_share',
                'utm_content' => $validated['utm_content'] ?? $oportunidade->titulo,
                'shared_at' => now()
            ]);

            Log::info('Social share tracked', [
                'oportunidade_id' => $oportunidade->id,
                'platform' => $validated['platform'],
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Partilha registrada com sucesso',
                'data' => $socialShare
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Oportunidade não encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error tracking social share: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Obtém estatísticas de partilhas para uma oportunidade
     */
    public function getShareStats($slug)
    {
        try {
            $oportunidade = Oportunidade::where('slug', $slug)->firstOrFail();

            $stats = SocialShare::where('oportunidade_id', $oportunidade->id)
                ->selectRaw('platform, COUNT(*) as total')
                ->groupBy('platform')
                ->get()
                ->pluck('total', 'platform');

            $totalShares = SocialShare::where('oportunidade_id', $oportunidade->id)->count();

            return response()->json([
                'total_shares' => $totalShares,
                'shares_by_platform' => $stats,
                'oportunidade' => [
                    'id' => $oportunidade->id,
                    'titulo' => $oportunidade->titulo,
                    'slug' => $oportunidade->slug
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Oportunidade não encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting share stats: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
