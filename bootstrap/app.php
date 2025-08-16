<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Excluir rotas da API da verificação CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        
        // Registrar middleware de tracking e privacy
        $middleware->alias([
            'track.visit' => \App\Http\Middleware\TrackPageVisit::class,
            'track.candidato' => \App\Http\Middleware\TrackCandidatoActivity::class,
            'data.privacy' => \App\Http\Middleware\DataPrivacyMiddleware::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
            'company.candidates.access' => \App\Http\Middleware\EnsureCompanyAccessToCandidates::class,
            'colaborador.access' => \App\Http\Middleware\EnsureColaboradorAccess::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\ContentSecurityPolicy::class,
            \App\Http\Middleware\DataPrivacyMiddleware::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\DataPrivacyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
