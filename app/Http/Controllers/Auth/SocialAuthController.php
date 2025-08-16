<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redireciona o usuário para a página de autenticação do provedor OAuth.
     *
     * @param string $provider O provedor de autenticação (google, github, linkedin)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($provider)
    {
        // Log para debug
        Log::info("Redirecionando para autenticação social: {$provider}");
        Log::info("URL atual: " . request()->fullUrl());
        
        // Verificar se o provedor existe
        if (!in_array($provider, ['google', 'github', 'linkedin'])) {
            Log::error("Provedor inválido: {$provider}");
            return response()->json(['error' => 'Provedor inválido'], 400);
        }
        
        // Obter URL de redirecionamento do Socialite
        try {
            $redirectResponse = Socialite::driver($provider)->redirect();
            Log::info("URL de redirecionamento gerado com sucesso");
            return $redirectResponse;
        } catch (\Exception $e) {
            Log::error("Erro ao gerar URL de redirecionamento: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Erro ao redirecionar para autenticação'], 500);
        }
    }

    /**
     * Obtém as informações do usuário após autenticação no provedor OAuth.
     *
     * @param string $provider O provedor de autenticação (google, github, linkedin)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback($provider)
    {
        Log::info("=== INÍCIO DO CALLBACK ===");
        Log::info("Provider: {$provider}");
        Log::info("URL atual: " . request()->fullUrl());
        
        try {
            Log::info("Tentando obter usuário do Socialite...");
            
            // Verificar se temos o code na URL
            if (!request()->has('code')) {
                throw new Exception('Código de autorização não encontrado na URL');
            }
            
            Log::info("Código da URL: " . request()->get('code'));
            Log::info("State da URL: " . request()->get('state', 'não encontrado'));
            
            // Verificar se há algum erro na URL de callback
            if (request()->has('error')) {
                $error = request()->get('error');
                $errorDescription = request()->get('error_description', 'Sem descrição');
                throw new Exception("Erro OAuth: {$error} - {$errorDescription}");
            }
            
            // Verificar se estamos em modo de desenvolvimento
            $devMode = env('OAUTH_DEV_MODE', false);
            if ($devMode) {
                Log::info("OAUTH_DEV_MODE está ativado - O bypass de state está funcionando");
                Log::warning("⚠️ AVISO: Não use OAUTH_DEV_MODE em produção! ⚠️");
            }
            
            $socialUser = $this->getSocialUser($provider);
            
            if (!$socialUser) {
                throw new Exception('Não foi possível obter dados do usuário social');
            }
            
            Log::info("Usuário social obtido com sucesso", [
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
                'id' => $socialUser->getId(),
                'dev_mode' => $devMode ? 'SIM' : 'NÃO'
            ]);
            
            if (!$socialUser->getEmail()) {
                throw new Exception('Email do usuário não disponível');
            }
            
            // Verifica se o usuário já existe no banco de dados
            $user = User::where('email', $socialUser->getEmail())->first();
            
            // Se o usuário não existir, cria um novo
            if (!$user) {
                Log::info("Criando novo usuário...");
                $user = User::create([
                    'name' => $socialUser->getName() ?: 'Usuário',
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => (string) $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'profile_url' => $this->getProfileUrl($provider, $socialUser),
                    'password' => null,
                ]);
                Log::info("Usuário criado com ID: {$user->id}");
            } else {
                Log::info("Atualizando usuário existente: {$user->id}");
                $user->update([
                    'provider' => $provider,
                    'provider_id' => (string) $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'profile_url' => $this->getProfileUrl($provider, $socialUser),
                ]);
            }
            
            // NÃO fazer Auth::login - apenas criar o token Sanctum
            Log::info("Usuário autenticado, criando token Sanctum");
            
            // Remover tokens antigos (opcional)
            $user->tokens()->delete();
            
            // Criar token Sanctum para o usuário
            $token = $user->createToken('spa-auth-token')->plainTextToken;
            Log::info("Token Sanctum criado: " . substr($token, 0, 10) . "...");
            
            // Redirecionar para o frontend com o token
            $frontendUrl = env('APP_FRONTEND_URL') ?: env('FRONTEND_URL', 'http://localhost:5174');
            
            // Obter a URL de retorno que foi especificada no início do fluxo
            // Se não houver, usar a página padrão de oportunidades
            $returnUrl = session('oauth_return_url', '/oportunidade/desenvolvedor-backend-php');
            
            // Garantir que não contém query string
            $returnUrl = parse_url($returnUrl, PHP_URL_PATH) ?: '/oportunidade/desenvolvedor-backend-php';
            
            // Construir URL de redirecionamento com token bem visível para debug
            // Adicionando _auth_token para garantir que seja único e mais fácil de capturar
            $redirectUrl = $frontendUrl . $returnUrl . '?_auth_token=' . urlencode($token);
            
            Log::info("Redirecionando para: {$redirectUrl}");
            Log::info("Token: " . substr($token, 0, 15) . "...");
            
            return redirect($redirectUrl);
            
        } catch (Exception $e) {
            Log::error("=== ERRO NO CALLBACK ===");
            Log::error('Erro: ' . $e->getMessage());
            Log::error('Arquivo: ' . $e->getFile());
            Log::error('Linha: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Redirecionar para o frontend com erro detalhado
            $frontendUrl = env('APP_FRONTEND_URL') ?: env('FRONTEND_URL', 'http://localhost:5174');
            $errorMessage = urlencode($e->getMessage());
            
            return redirect($frontendUrl . "/oportunidade/desenvolvedor-backend-php?auth=error&message={$errorMessage}");
        }
    }
    
    /**
     * Obtém a URL do perfil do usuário com base no provedor.
     *
     * @param string $provider O provedor de autenticação
     * @param \Laravel\Socialite\Contracts\User $socialUser O usuário do provedor social
     * @return string|null
     */
    protected function getProfileUrl($provider, $socialUser)
    {
        switch ($provider) {
            case 'github':
                return 'https://github.com/' . $socialUser->getNickname();
            case 'linkedin':
                // LinkedIn pode não fornecer a URL do perfil diretamente
                // Esta é uma tentativa de obter via campos adicionais
                return $socialUser->user['publicProfileUrl'] ?? null;
            default:
                return null;
        }
    }
    
    /**
     * Método auxiliar para obter usuário do OAuth com fallback
     * Ignora a validação de state em ambiente local quando OAUTH_DEV_MODE=true
     * 
     * IMPORTANTE: Este bypass de state é APENAS para desenvolvimento local!
     * Em produção, a validação de state é essencial para segurança contra CSRF.
     */
    private function getSocialUser($provider)
    {
        // Ambiente de desenvolvimento - implementar uma solução simplificada que não depende de state
        try {
            Log::info("Tentando obter usuário do OAuth com verificação de state...");
            return Socialite::driver($provider)->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning("State inválido detectado. Em ambiente local, vamos ignorar a validação de state.");
            
            // Verificar se o modo de desenvolvimento está ativado
            $devMode = env('OAUTH_DEV_MODE', false);
            if (!$devMode) {
                Log::error("OAUTH_DEV_MODE não está ativado no .env - O bypass de state não funcionará!");
                throw $e;
            }
            
            // Modo de desenvolvimento - ignorar o problema de state e usar uma abordagem direta
            // Isso é apenas para ambiente de desenvolvimento local!
            try {
                $code = request()->get('code');
                if (!$code) {
                    throw new Exception('Código de autorização não encontrado na URL');
                }
                
                // Usar diretamente as credenciais do .env (apenas para desenvolvimento)
                Log::info("Simulando login para ambiente de desenvolvimento com código: " . $code);

                // Usuário mockado para desenvolvimento
                // Isso simula um login bem-sucedido do Google sem validação de state
                $mockUser = new \Laravel\Socialite\Two\User();
                $mockUser->id = '123456789';
                $mockUser->name = 'Usuário de Teste Local';
                $mockUser->email = 'teste@exemplo.com.br';
                $mockUser->avatar = 'https://www.gravatar.com/avatar/'.md5('teste@exemplo.com.br');
                
                Log::info("Login simulado com sucesso para ambiente de desenvolvimento");
                return $mockUser;
            } catch (\Exception $innerException) {
                Log::error("Erro ao simular login: " . $innerException->getMessage());
                throw $innerException;
            }
        } catch (\Exception $e) {
            Log::error("Erro ao obter usuário social: " . $e->getMessage());
            throw $e;
        }
    }
}
