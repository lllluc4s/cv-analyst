<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DataPrivacyMiddleware
{
    /**
     * Campos que contêm dados pessoais e devem ser ocultados em logs
     */
    private const SENSITIVE_FIELDS = [
        'nome',
        'apelido',
        'email',
        'telefone',
        'password',
        'linkedin_url',
        'cv_path',
        'foto_path',
        'skills',
        'experiencia_profissional',
        'formacao_academica',
        'ip_address',
        'user_agent',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Intercepta e mascara dados sensíveis nos logs
        $this->sanitizeRequestForLogging($request);

        $response = $next($request);

        // Log da resposta de forma segura (sem dados sensíveis)
        $this->logResponseSafely($request, $response);

        return $response;
    }

    /**
     * Sanitiza dados da requisição para logs seguros
     */
    private function sanitizeRequestForLogging(Request $request): void
    {
        $sanitizedData = $this->sanitizeArray($request->all());
        
        // Log apenas se houver dados e não for uma rota de health check
        if (!empty($sanitizedData) && !$this->isHealthCheckRoute($request)) {
            Log::info('Request processada', [
                'url' => $request->url(),
                'method' => $request->method(),
                'sanitized_data' => $sanitizedData,
                'user_id' => auth('sanctum')->id() ?? 'guest',
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Log da resposta de forma segura
     */
    private function logResponseSafely(Request $request, Response $response): void
    {
        // Log apenas erros ou operações importantes
        if ($response->getStatusCode() >= 400 || $this->isImportantOperation($request)) {
            Log::info('Response gerada', [
                'url' => $request->url(),
                'status' => $response->getStatusCode(),
                'user_id' => auth('sanctum')->id() ?? 'guest',
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Sanitiza array removendo/mascarando dados sensíveis
     */
    private function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (in_array($key, self::SENSITIVE_FIELDS)) {
                $sanitized[$key] = $this->maskSensitiveData($key, $value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Mascara dados sensíveis para logs
     */
    private function maskSensitiveData(string $field, $value): string
    {
        if (empty($value)) {
            return '[vazio]';
        }

        switch ($field) {
            case 'email':
                return $this->maskEmail($value);
            case 'telefone':
                return $this->maskPhone($value);
            case 'nome':
            case 'apelido':
                return $this->maskName($value);
            case 'password':
                return '[SENHA_OCULTA]';
            case 'cv_path':
            case 'foto_path':
                return '[ARQUIVO_UPLOAD]';
            case 'ip_address':
                return $this->maskIpAddress($value);
            default:
                return '[DADOS_PESSOAIS]';
        }
    }

    /**
     * Mascara email preservando estrutura básica
     */
    private function maskEmail(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '[EMAIL_INVÁLIDO]';
        }

        list($local, $domain) = explode('@', $email);
        $maskedLocal = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 2));
        
        return $maskedLocal . '@' . $domain;
    }

    /**
     * Mascara telefone preservando primeiros dígitos
     */
    private function maskPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleaned) < 4) {
            return '[TELEFONE]';
        }
        
        return substr($cleaned, 0, 3) . str_repeat('*', strlen($cleaned) - 3);
    }

    /**
     * Mascara nome preservando inicial
     */
    private function maskName(string $name): string
    {
        if (strlen($name) <= 1) {
            return '[NOME]';
        }
        
        return substr($name, 0, 1) . str_repeat('*', max(1, strlen($name) - 1));
    }

    /**
     * Mascara IP preservando primeiros octetos
     */
    private function maskIpAddress(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.***.***.';
        }
        
        return '[IP_MASCARADO]';
    }

    /**
     * Verifica se é uma rota de health check
     */
    private function isHealthCheckRoute(Request $request): bool
    {
        $healthRoutes = ['/health', '/status', '/ping', '/api/health'];
        
        foreach ($healthRoutes as $route) {
            if (str_contains($request->getPathInfo(), $route)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verifica se é uma operação importante que deve ser logada
     */
    private function isImportantOperation(Request $request): bool
    {
        $importantRoutes = [
            '/api/candidatos',
            '/api/candidaturas',
            '/api/auth',
            '/api/privacy',
        ];

        foreach ($importantRoutes as $route) {
            if (str_contains($request->getPathInfo(), $route)) {
                return true;
            }
        }

        return false;
    }
}
