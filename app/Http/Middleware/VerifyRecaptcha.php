<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip verification in development environment if no keys are set
        if (app()->environment('local')) {
            // Log para debug
            Log::debug('reCAPTCHA: Verificação em ambiente local', [
                'secret_key_set' => !empty(config('recaptcha.secret_key')),
                'token' => $request->input('recaptcha_token') ? 'Presente' : 'Ausente'
            ]);
            
            // Se estiver em ambiente local e as chaves parecem ser de teste, permite o acesso
            $secretKey = env('RECAPTCHA_SECRET_KEY', '');
            if (empty($secretKey) || str_starts_with($secretKey, '6LcPb3Qr')) {
                Log::info('reCAPTCHA: Pulando verificação em ambiente local com chaves de teste');
                return $next($request);
            }
        }

        $recaptchaToken = $request->input('recaptcha_token');
        
        if (!$recaptchaToken) {
            return response()->json([
                'message' => 'Verificação reCAPTCHA falhou. Por favor, tente novamente.',
                'errors' => ['recaptcha' => ['Token reCAPTCHA não fornecido.']]
            ], 400);
        }

        try {
            // Obter a chave secreta do config (que carrega do .env)
            $secretKey = config('recaptcha.secret_key');
            $minScore = config('recaptcha.min_score', 0.5);
            
            // Fallback para pegar diretamente do .env se config não funcionar
            if (empty($secretKey)) {
                $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';
            }
            
            if (empty($secretKey)) {
                Log::error('reCAPTCHA: Secret key não configurado');
                return response()->json([
                    'message' => 'Configuração de segurança inválida.',
                    'errors' => ['recaptcha' => ['Configuração do servidor incorreta.']]
                ], 500);
            }
            
            // Log para debug dos parâmetros
            Log::debug('reCAPTCHA: Enviando verificação', [
                'secret_key_length' => strlen($secretKey),
                'token_length' => strlen($recaptchaToken),
                'ip' => $request->ip(),
                'has_secret_key' => !empty($secretKey),
                'secret_key_first_chars' => substr($secretKey, 0, 10) . '...'
            ]);
            
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $recaptchaToken,
                'remoteip' => $request->ip()
            ]);

            $responseData = $response->json();

            // Log completo da resposta para debug
            Log::debug('reCAPTCHA: Resposta do Google', [
                'response_data' => $responseData,
                'http_status' => $response->status(),
                'token_sent' => substr($recaptchaToken, 0, 20) . '...'
            ]);

            if (!$responseData['success']) {
                Log::warning('reCAPTCHA verification failed', [
                    'response_data' => $responseData,
                    'error_codes' => $responseData['error-codes'] ?? []
                ]);
                return response()->json([
                    'message' => 'Verificação de segurança falhou. Por favor, tente novamente.',
                    'errors' => ['recaptcha' => ['A verificação de segurança falhou.']]
                ], 400);
            }

            if (isset($responseData['score']) && $responseData['score'] < $minScore) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $responseData['score'], 
                    'min_score' => $minScore,
                    'action' => $responseData['action'] ?? 'unknown'
                ]);
                return response()->json([
                    'message' => 'Sua solicitação foi identificada como potencialmente automatizada. Por favor, tente novamente mais tarde.',
                    'errors' => ['recaptcha' => ['Score de verificação muito baixo.']]
                ], 400);
            }

            // Log de sucesso
            Log::info('reCAPTCHA verification successful', [
                'score' => $responseData['score'] ?? 'N/A',
                'action' => $responseData['action'] ?? 'unknown'
            ]);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erro ao verificar reCAPTCHA. Por favor, tente novamente mais tarde.',
                'errors' => ['recaptcha' => ['Erro na verificação de segurança.']]
            ], 500);
        }
    }
}
