<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiUsageLoggingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Log apenas se o usuário estiver autenticado
        if ($request->user()) {
            try {
                $user = $request->user();
                Log::channel('daily')->info('API Usage', [
                    'company_id' => $user->company_id ?? $user->id, // Para Companies usa company_id, para Candidatos usa id
                    'user_id' => $user->id,
                    'user_type' => get_class($user),
                    'method' => $request->method(),
                    'url' => $request->url(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status_code' => $response->getStatusCode(),
                    'timestamp' => now()->toISOString(),
                ]);
            } catch (\Exception $e) {
                // Silenciar erros de logging para não quebrar a aplicação
                Log::error('Error in API Usage Logging: ' . $e->getMessage());
            }
        }
        
        return $response;
    }
}
