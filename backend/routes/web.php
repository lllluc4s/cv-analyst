<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OportunidadeController;
use App\Http\Controllers\SocialMetaController;

// Rota principal - serve a aplicação Vue para todas as rotas da SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*')->name('spa');

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
