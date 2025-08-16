<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // Verificar se é uma requisição de API ou espera JSON
        $wantsJson = $request->expectsJson() || 
                    $request->is('api/*') ||
                    $request->wantsJson() || 
                    $request->ajax();

        // Para requisições de API, sempre retornar JSON
        if ($wantsJson) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Trata exceções para respostas de API
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleApiException($request, Throwable $e): JsonResponse
    {
        $statusCode = 500;
        $response = [
            'message' => 'Ocorreu um erro no servidor.',
            'success' => false
        ];

        // Se for uma exceção HTTP, usar o código de status dela
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $response['message'] = $e->getMessage() ?: 'Ocorreu um erro HTTP.';
        } 
        // Tratamento específico para falhas de autenticação
        else if ($e instanceof AuthenticationException) {
            $statusCode = 401;
            $response['message'] = 'Não autenticado';
            $response['authenticated'] = false;
            $response['login_url'] = env('APP_URL') . '/auth/google/redirect';
        } 
        // Tratamento específico para falhas de validação
        else if ($e instanceof ValidationException) {
            $statusCode = 422;
            $response['message'] = $e->getMessage();
            $response['errors'] = $e->errors();
        } 
        // Para outras exceções em ambiente de produção, ocultar detalhes
        else if (!config('app.debug')) {
            $response['message'] = 'Ocorreu um erro no servidor.';
        } 
        // Em ambiente de desenvolvimento, mostrar detalhes
        else {
            $response['message'] = $e->getMessage();
            $response['exception'] = get_class($e);
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
            $response['trace'] = $e->getTrace();
        }

        return new JsonResponse($response, $statusCode);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Não autenticado.',
                'authenticated' => false,
                'login_url' => env('APP_URL') . '/auth/google/redirect'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
