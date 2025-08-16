<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialMetaController;

// Rota para documentação da API
Route::get('/docs', function () {
    return redirect('/api/documentation');
})->name('api.docs');

// Rota para meta tags sociais
Route::get('/social/oportunidade/{slug}', [SocialMetaController::class, 'oportunidade'])
    ->name('social.oportunidade');

// Rotas para autenticação social (redirect e callback)
Route::middleware(['web'])->prefix('auth')->group(function () {
    Route::get('/{provider}/redirect', [App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])
        ->name('social.redirect')
        ->where('provider', 'google|github|linkedin');
        
    Route::get('/{provider}/callback', [App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])
        ->name('social.callback')
        ->where('provider', 'google|github|linkedin');
});

// Rota para servir o frontend em produção (apenas se necessário)
Route::get('/{any}', function () {
    // Em desenvolvimento, o Vue.js roda em porta separada (5174)
    // Em produção, serve os arquivos do build
    $frontendPath = public_path('frontend/index.html');
    if (file_exists($frontendPath)) {
        return file_get_contents($frontendPath);
    }
    
    // Se não existir build, retorna informação sobre desenvolvimento
    return response()->json([
        'message' => 'Frontend em desenvolvimento. Acesse http://localhost:5174',
        'api' => 'API disponível em /api/*'
    ]);
})->where('any', '^(?!api|images|storage).*$')->name('spa');

// Rota de redirecionamento temporário para manter compatibilidade com Google Console
Route::get('/auth/{provider}/callback', [App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])
    ->middleware(['web'])
    ->name('social.callback.legacy')
    ->where('provider', 'google|github|linkedin');

// Rotas públicas para consentimento de candidatos (páginas web)
Route::prefix('candidate/privacy')->name('candidate.privacy.')->group(function () {
    Route::get('/{slug}/consent', [App\Http\Controllers\CandidatePrivacyController::class, 'showConsentForm'])
        ->name('consent.form');
});

// Rota para servir PDFs com headers corretos
Route::get('/files/pdf/{filename}', [App\Http\Controllers\FileController::class, 'servePdf'])
    ->name('files.pdf')
    ->middleware(['cors'])
    ->where('filename', '.*\.pdf$');

// Rota de login que sempre retorna JSON
Route::get('/login', function() {
    return response()->json([
        'message' => 'Não autenticado',
        'authenticated' => false,
        'login_url' => env('APP_URL') . '/auth/google/redirect'
    ], 401);
})->name('login');

// Rotas para servir imagens default
Route::get('/images/default-avatar.png', function() {
    $svgPath = public_path('images/default-avatar.svg');
    if (file_exists($svgPath)) {
        return response()->file($svgPath, [
            'Content-Type' => 'image/svg+xml'
        ]);
    }
    
    // Se não existir, criar uma resposta simples
    $svg = '<svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
        <rect width="150" height="150" fill="#e2e8f0" rx="75"/>
        <circle cx="75" cy="60" r="25" fill="#94a3b8"/>
        <circle cx="75" cy="120" r="35" fill="#94a3b8"/>
    </svg>';
    
    return response($svg, 200, [
        'Content-Type' => 'image/svg+xml',
        'Cache-Control' => 'public, max-age=86400'
    ]);
});

Route::get('/images/default-company.png', function() {
    $svgPath = public_path('images/default-company.svg');
    if (file_exists($svgPath)) {
        return response()->file($svgPath, [
            'Content-Type' => 'image/svg+xml'
        ]);
    }
    
    // Se não existir, criar uma resposta simples
    $svg = '<svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
        <rect width="150" height="150" fill="#f1f5f9"/>
        <rect x="30" y="30" width="90" height="90" fill="#cbd5e1" stroke="#94a3b8" stroke-width="2" rx="5"/>
        <rect x="50" y="50" width="50" height="30" fill="#94a3b8" rx="3"/>
        <rect x="40" y="90" width="20" height="20" fill="#94a3b8" rx="2"/>
        <rect x="70" y="90" width="20" height="20" fill="#94a3b8" rx="2"/>
        <rect x="100" y="90" width="20" height="20" fill="#94a3b8" rx="2"/>
    </svg>';
    
    return response($svg, 200, [
        'Content-Type' => 'image/svg+xml',
        'Cache-Control' => 'public, max-age=86400'
    ]);
});
