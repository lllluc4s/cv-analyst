<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CvAnalysisController;
use App\Http\Controllers\CandidaturaController;
use App\Http\Controllers\CvOptimizerController;
use App\Http\Controllers\Api\OportunidadeController;
use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\CandidatoPrivacyController;
use App\Http\Controllers\CandidatoProfileController;
use App\Http\Controllers\DiaNaoTrabalhadoController;
use App\Http\Controllers\Api\PublicCompanyController;
use App\Http\Controllers\CompanyBackofficeController;
use App\Http\Controllers\Auth\CandidatoAuthController;
use App\Http\Controllers\Api\OportunidadeReportsController;
use App\Http\Controllers\Auth\CompanyEmailVerificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rota para verificar status de autenticação
Route::get('/auth/status', function (Request $request) {
    return response()->json([
        'authenticated' => $request->user() !== null,
        'user' => $request->user()
    ]);
})->middleware('auth:sanctum');

// Rota para obter perfil do usuário
Route::get('/user/profile', function (Request $request) {
    return response()->json([
        'authenticated' => $request->user() !== null,
        'user' => $request->user()
    ]);
})->middleware('auth:sanctum');

// Rota para logout
Route::post('/auth/logout', function (Request $request) {
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Logout realizado com sucesso']);
})->middleware('auth:sanctum');

// Rotas públicas (sem autenticação)
Route::post('/analyze-cvs', [CvAnalysisController::class, 'analyzeCvs']);

// Rota específica para buscar oportunidade por slug (deve vir antes do resource)
Route::get('/oportunidades/slug/{slug}', [OportunidadeController::class, 'showBySlug']);

// Rota para listar oportunidades públicas
Route::get('/oportunidades/publicas', [OportunidadeController::class, 'publicIndex']);

// Rotas públicas para empresas e suas oportunidades
Route::prefix('public')->group(function () {
    // Página da empresa com suas oportunidades
    Route::get('/empresa/{slug}', [PublicCompanyController::class, 'show']);

    // Listagem de todas as empresas
    Route::get('/empresas', [PublicCompanyController::class, 'companies']);

    // Listagem geral de oportunidades (todas as empresas)
    Route::get('/oportunidades', [PublicCompanyController::class, 'publicOpportunities']);

    // Rotas para filtros
    Route::get('/companies', [PublicCompanyController::class, 'companiesWithOpportunities']);
    Route::get('/skills', [PublicCompanyController::class, 'availableSkills']);
    Route::get('/locations', [PublicCompanyController::class, 'availableLocations']);
});

// Rota para submissão de candidaturas (opcional autenticação + recaptcha)
Route::post('/candidaturas', [CandidaturaController::class, 'store'])
    ->middleware([\App\Http\Middleware\VerifyRecaptcha::class]);

// Rotas para reports de oportunidades (públicos)
Route::get('/oportunidades/{slug}/reports', [OportunidadeReportsController::class, 'getReports']);

// Rotas para rastreamento de partilhas sociais
Route::post('/oportunidades/{slug}/share', [App\Http\Controllers\Api\SocialShareController::class, 'trackShare']);
Route::get('/oportunidades/{slug}/share-stats', [App\Http\Controllers\Api\SocialShareController::class, 'getShareStats']);

// Rota para rastreamento de convites
Route::get('/convites/{token}/track', [App\Http\Controllers\CandidatosPotencialController::class, 'trackConviteVisualizado']);

// Rota para meta dados de partilha social
Route::get('/oportunidades/{slug}/meta', [App\Http\Controllers\SocialMetaController::class, 'oportunidadeApi']);

// Rotas protegidas por autenticação (empresas)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Rotas de API para Oportunidades (protegidas)
    Route::apiResource('oportunidades', OportunidadeController::class)->except(['index', 'show']);
    Route::get('/oportunidades', [OportunidadeController::class, 'index']); // Apenas oportunidades da empresa
    Route::get('/oportunidades/{oportunidade}', [OportunidadeController::class, 'show']); // Apenas se pertencer à empresa

    // Rotas administrativas para candidaturas (protegidas)
    Route::get('/candidaturas', [CandidaturaController::class, 'index']); // Apenas candidaturas das oportunidades da empresa
    Route::get('/candidaturas/{candidatura}', [CandidaturaController::class, 'show']); // Apenas se pertencer à empresa
    Route::patch('/candidaturas/{candidatura}/skills', [CandidaturaController::class, 'atualizarSkills']); // Apenas se pertencer à empresa
    Route::patch('/candidaturas/{candidatura}/rating', [CandidaturaController::class, 'updateRating']); // Atualizar avaliação
    Route::delete('/candidaturas/{slug}', [CandidaturaController::class, 'destroyBySlug']); // Apenas se pertencer à empresa
    Route::get('/oportunidades/{oportunidadeId}/candidaturas', [CandidaturaController::class, 'candidaturasPorOportunidade']); // Apenas se pertencer à empresa

    // Rotas para colaboradores (sistema de contratação)
    Route::post('/candidaturas/{candidatura}/contratar', [App\Http\Controllers\ColaboradorController::class, 'contratarCandidato']);
    Route::get('/candidaturas/{candidatura}/status-contratacao', [App\Http\Controllers\ColaboradorController::class, 'statusCandidatura']);
    Route::get('/colaboradores', [App\Http\Controllers\ColaboradorController::class, 'index']);
    Route::get('/colaboradores/{colaborador}', [App\Http\Controllers\ColaboradorController::class, 'show']);
    Route::patch('/colaboradores/{colaborador}', [App\Http\Controllers\ColaboradorController::class, 'update']);
    Route::get('/colaboradores/{colaborador}/contrato', [App\Http\Controllers\ColaboradorController::class, 'gerarContrato']);

    // Rotas para dias não trabalhados
    Route::prefix('dias-nao-trabalhados')->name('dias-nao-trabalhados.')->group(function () {
        // Para colaboradores enviarem solicitações
        Route::get('/', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'show'])->name('show');
        Route::get('/{id}/documento', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'downloadDocumento'])->name('documento');

        // Para empresas gerenciarem solicitações
        Route::get('/empresa/listar', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'indexPorEmpresa'])->name('empresa.index');
        Route::post('/{id}/aprovar', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'aprovar'])->name('aprovar');
        Route::post('/{id}/recusar', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'recusar'])->name('recusar');
        Route::get('/empresa/estatisticas', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'estatisticas'])->name('empresa.estatisticas');

        // Rotas de exportação
        Route::get('/empresa/exportar/pdf', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'exportarPdf'])->name('empresa.exportar.pdf');
        Route::get('/empresa/exportar/excel', [App\Http\Controllers\DiaNaoTrabalhadoController::class, 'exportarExcel'])->name('empresa.exportar.excel');
    });

    // Rotas administrativas para reports (protegidas)
    Route::get('/admin/reports', [ReportsController::class, 'index']);
    Route::get('/admin/reports/all-opportunities', [ReportsController::class, 'allOpportunities']);
    Route::get('/admin/reports/opportunity/{slug}', [ReportsController::class, 'opportunityDetails']);
    Route::post('/admin/reports/opportunity/{slug}/export', [ReportsController::class, 'exportOpportunityData']);
});

// Company Authentication Routes
Route::prefix('companies')->name('companies.')->group(function () {
    Route::post('/register', [CompanyAuthController::class, 'register'])->name('register');
    Route::post('/login', [CompanyAuthController::class, 'login'])->name('login');
});

// Candidato Authentication Routes
Route::prefix('candidatos')->name('candidatos.')->group(function () {
    Route::post('/register', [CandidatoAuthController::class, 'register'])->name('register');
    Route::post('/login', [CandidatoAuthController::class, 'login'])->name('login');
});

// Company Email Verification Routes (públicas mas protegidas por middleware interno)
Route::prefix('companies')->name('companies.')->group(function () {
    Route::get('/email/verify/{id}/{hash}', [CompanyEmailVerificationController::class, 'verify'])
        ->name('verification.verify');
    Route::post('/email/resend', [CompanyEmailVerificationController::class, 'resend'])
        ->name('verification.resend');
});

// Rotas protegidas para empresas (exigem autenticação)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('companies')->name('companies.')->group(function () {
    Route::post('/logout', [CompanyAuthController::class, 'logout'])->name('logout');
    Route::get('/me', [CompanyAuthController::class, 'me'])->name('me');

    // Backoffice - Dashboard
    Route::get('/dashboard', [CompanyBackofficeController::class, 'dashboard'])->name('dashboard');

    // Backoffice - Gestão de oportunidades
    Route::get('/oportunidades', [CompanyBackofficeController::class, 'index'])->name('oportunidades.index');
    Route::post('/oportunidades', [CompanyBackofficeController::class, 'store'])->name('oportunidades.store');
    Route::get('/oportunidades/{id}', [CompanyBackofficeController::class, 'show'])->name('oportunidades.show');
    Route::put('/oportunidades/{id}', [CompanyBackofficeController::class, 'update'])->name('oportunidades.update');
    Route::delete('/oportunidades/{id}', [CompanyBackofficeController::class, 'destroy'])->name('oportunidades.destroy');

    // Backoffice - Gestão de candidatos
    Route::get('/oportunidades/{id}/candidates', [CompanyBackofficeController::class, 'getCandidates'])->name('oportunidades.candidates');

    // Kanban Board Routes - Protegidas por middleware de acesso
    Route::prefix('kanban')->name('kanban.')->middleware('company.candidates.access')->group(function () {
        Route::get('/oportunidades/{slugOrId}/board', [App\Http\Controllers\KanbanBoardController::class, 'getBoardByOportunidade'])->name('board');
        Route::post('/candidaturas/move', [App\Http\Controllers\KanbanBoardController::class, 'moveCandidatura'])->name('move');
        Route::get('/candidaturas/{id}/history', [App\Http\Controllers\KanbanBoardController::class, 'getCandidateHistory'])->name('history');
        Route::put('/candidaturas/{id}/notes', [App\Http\Controllers\KanbanBoardController::class, 'updateCandidateNotes'])->name('notes');

        // Estados globais (para compatibilidade)
        Route::get('/states', [App\Http\Controllers\KanbanBoardController::class, 'getStates'])->name('states');
        Route::post('/states', [App\Http\Controllers\KanbanBoardController::class, 'createState'])->name('states.create');
        Route::put('/states/{id}', [App\Http\Controllers\KanbanBoardController::class, 'updateState'])->name('states.update');
        Route::delete('/states/{id}', [App\Http\Controllers\KanbanBoardController::class, 'deleteState'])->name('states.delete');

        // Estados por oportunidade (novos)
        Route::get('/oportunidades/{slugOrId}/states', [App\Http\Controllers\KanbanBoardController::class, 'getStatesByOportunidade'])->name('oportunidade.states');
        Route::post('/oportunidades/{slugOrId}/states', [App\Http\Controllers\KanbanBoardController::class, 'createStateForOportunidade'])->name('oportunidade.states.create');
        Route::put('/oportunidades/{oportunidade_id}/states/{state_id}', [App\Http\Controllers\KanbanBoardController::class, 'updateStateForOportunidade'])->name('oportunidade.states.update');
        Route::post('/oportunidades/{slugOrId}/states/reorder', [App\Http\Controllers\KanbanBoardController::class, 'reorderStatesForOportunidade'])->name('oportunidade.states.reorder');
        Route::delete('/oportunidades/{oportunidade_id}/states/{state_id}', [App\Http\Controllers\KanbanBoardController::class, 'deleteStateForOportunidade'])->name('oportunidade.states.delete');

        // Email Configuration Routes
        Route::put('/states/{id}/email', [App\Http\Controllers\KanbanBoardController::class, 'updateEmailConfig'])->name('states.email.update');
        Route::post('/states/{id}/email/preview', [App\Http\Controllers\KanbanBoardController::class, 'previewEmailTemplate'])->name('states.email.preview');

        // Rotas adicionais para compatibilidade com frontend
        Route::put('/email/{id}', [App\Http\Controllers\KanbanBoardController::class, 'updateEmailConfig'])->name('email.update');
        Route::post('/email/{id}/preview', [App\Http\Controllers\KanbanBoardController::class, 'previewEmailTemplate'])->name('email.preview');
    });

    // Rotas debug específicas para o Kanban (compatibilidade com frontend)
    Route::prefix('debug/kanban')->middleware(['auth:sanctum', 'company.candidates.access'])->group(function () {
        Route::put('/email/{id}', [App\Http\Controllers\KanbanBoardController::class, 'updateEmailConfig']);
        Route::post('/email/{id}/preview', [App\Http\Controllers\KanbanBoardController::class, 'previewEmailTemplate']);
    });

    // Candidatos com Potencial
    Route::get('/oportunidades/{id}/candidatos-potencial', [App\Http\Controllers\CandidatosPotencialController::class, 'buscarCandidatosPotencial'])->name('oportunidades.candidatos-potencial');
    Route::post('/convites/candidatos', [App\Http\Controllers\CandidatosPotencialController::class, 'convidarCandidato'])->name('convites.enviar');
    Route::get('/oportunidades/{id}/convites', [App\Http\Controllers\CandidatosPotencialController::class, 'historicoConvites'])->name('oportunidades.convites');

    // Feedbacks de Recrutamento
    Route::get('/oportunidades/{id}/feedbacks', [App\Http\Controllers\FeedbackRecrutamentoController::class, 'listarFeedbacks'])->name('oportunidades.feedbacks');

    // Backoffice - Relatórios de oportunidades
    Route::get('/oportunidades/{id}/reports', [CompanyBackofficeController::class, 'getOpportunityReports'])->name('oportunidades.reports');

    // Company profile routes
    Route::get('/profile', [CompanyBackofficeController::class, 'showProfile']);
    Route::put('/profile', [CompanyBackofficeController::class, 'updateProfile']);

    // Company logo upload routes
    Route::post('/logo', [CompanyBackofficeController::class, 'uploadLogo']);
    Route::delete('/logo', [CompanyBackofficeController::class, 'removeLogo']);

    // Mapa de Candidatos Online - Funcionalidade administrativa
    Route::prefix('online-candidates')->name('online-candidates.')->group(function () {
        Route::get('/map', [App\Http\Controllers\OnlineCandidatesMapController::class, 'getOnlineCandidates'])->name('map');
        Route::get('/stats', [App\Http\Controllers\OnlineCandidatesMapController::class, 'getActivityStats'])->name('stats');
    });
});

// Alias para compatibilidade com frontend
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/company/dashboard', [CompanyBackofficeController::class, 'dashboard']);
});

// Rotas protegidas para candidatos (exigem autenticação)
Route::middleware(['auth:sanctum', 'track.candidato'])->prefix('candidatos')->name('candidatos.')->group(function () {
    Route::post('/logout', [CandidatoAuthController::class, 'logout'])->name('logout');
    Route::get('/me', [CandidatoAuthController::class, 'me'])->name('me');

    // Perfil do candidato
    Route::put('/profile', [CandidatoProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile', [CandidatoProfileController::class, 'update'])->name('profile.update.post');
    Route::get('/candidaturas', [CandidatoProfileController::class, 'candidaturas'])->name('candidaturas');

    // Profile photo upload
    Route::post('/profile-photo', [App\Http\Controllers\ProfilePhotoController::class, 'upload'])->name('profile-photo.upload');
    Route::delete('/profile-photo', [App\Http\Controllers\ProfilePhotoController::class, 'delete'])->name('profile-photo.delete');

    // Privacidade e GDPR
    Route::prefix('privacy')->name('privacy.')->group(function () {
        Route::get('/settings', [CandidatoPrivacyController::class, 'getPrivacySettings'])->name('settings');
        Route::put('/searchability', [CandidatoPrivacyController::class, 'updateSearchability'])->name('searchability');
        Route::post('/delete-account', [CandidatoPrivacyController::class, 'requestAccountDeletion'])->name('delete-account');
        Route::get('/export-data', [CandidatoPrivacyController::class, 'exportData'])->name('export-data');
    });

    // CV Optimizer - Novas rotas
    Route::prefix('cv-optimizer')->name('cv-optimizer.')->group(function () {
        Route::post('/upload', [CvOptimizerController::class, 'uploadCV'])->name('upload');
        Route::post('/{cvId}/otimizar', [CvOptimizerController::class, 'otimizarCV'])->name('otimizar');
        Route::put('/{cvId}', [CvOptimizerController::class, 'atualizarCV'])->name('atualizar');
        Route::get('/{cvId}', [CvOptimizerController::class, 'obterCV'])->name('obter');
        Route::get('/', [CvOptimizerController::class, 'listarCVs'])->name('listar');
        Route::delete('/{cvId}', [CvOptimizerController::class, 'excluirCV'])->name('excluir');
        Route::post('/{cvId}/pdf', [CvOptimizerController::class, 'gerarPDF'])->name('gerar-pdf');
        Route::get('/templates/listar', [CvOptimizerController::class, 'listarTemplates'])->name('templates.listar');
        Route::get('/ia/status', [CvOptimizerController::class, 'statusIA'])->name('ia.status');
    });

    // Rotas para candidaturas de oportunidades
    Route::prefix('oportunidades')->name('oportunidades.')->group(function () {
        Route::post('/{oportunidade}/candidatar', [CandidatoProfileController::class, 'candidatar'])->name('candidatar');
        Route::get('/{oportunidade}/can-apply', [CandidatoProfileController::class, 'canApply'])->name('can-apply');
    });
});

// Rotas para área de colaborador (candidatos contratados)
Route::middleware(['auth:sanctum', 'colaborador.access'])->prefix('colaborador')->name('colaborador.')->group(function () {
    Route::get('/me', [App\Http\Controllers\ColaboradorAreaController::class, 'me'])->name('me');
    Route::get('/dashboard', [App\Http\Controllers\ColaboradorAreaController::class, 'dashboard'])->name('dashboard');
    Route::get('/historico-contratos', [App\Http\Controllers\ColaboradorAreaController::class, 'historicoContratos'])->name('historico-contratos');

    // Rotas para dias não trabalhados (área do colaborador)
    Route::prefix('dias-nao-trabalhados')->name('dias-nao-trabalhados.')->group(function () {
        Route::get('/', [DiaNaoTrabalhadoController::class, 'index'])->name('index');
        Route::post('/', [DiaNaoTrabalhadoController::class, 'store'])->name('store');
        Route::get('/{id}', [DiaNaoTrabalhadoController::class, 'show'])->name('show');
        Route::get('/{id}/documento', [DiaNaoTrabalhadoController::class, 'downloadDocumento'])->name('documento');
    });
});

// Rota para verificar se candidato tem acesso à área de colaborador
Route::middleware(['auth:sanctum'])->get('/candidatos/verificar-acesso-colaborador', [App\Http\Controllers\ColaboradorAreaController::class, 'verificarAcesso'])->name('candidatos.verificar-acesso-colaborador');

// Rotas GDPR (Proteção de Dados)
Route::prefix('gdpr')->group(function () {
    Route::post('/export-data', [App\Http\Controllers\GdprController::class, 'exportPersonalData']);
    Route::post('/delete-data', [App\Http\Controllers\GdprController::class, 'deletePersonalData']);
    Route::get('/privacy-policy', [App\Http\Controllers\GdprController::class, 'privacyPolicy']);
    Route::post('/consent-status', [App\Http\Controllers\GdprController::class, 'consentStatus']);
});

// Rotas públicas para consentimento de candidatos
Route::prefix('candidate/privacy')->group(function () {
    Route::get('/{slug}/consent', [App\Http\Controllers\CandidatePrivacyController::class, 'showConsentForm'])->name('candidate.consent.form');
    Route::post('/{slug}/consent', [App\Http\Controllers\CandidatePrivacyController::class, 'updateConsent'])->name('candidate.consent.update');
    Route::get('/{slug}/status', [App\Http\Controllers\CandidatePrivacyController::class, 'getConsentStatus'])->name('candidate.consent.status');
    Route::post('/{slug}/revoke', [App\Http\Controllers\CandidatePrivacyController::class, 'revokeConsent'])->name('candidate.consent.revoke');
});

// Rota para servir o arquivo JSON da documentação Swagger
Route::get('/docs/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
})->name('custom.swagger.docs');

// Rota alternativa para documentação (caso tenha problemas)
Route::get('/swagger', function () {
    return view('l5-swagger::index', [
        'documentation' => 'default',
        'config' => [
            'operationsSorter' => null,
            'configUrl' => null,
            'validatorUrl' => null,
            'oauth2RedirectUrl' => route('l5-swagger.default.oauth2_callback'),
            'requestInterceptor' => null,
            'responseInterceptor' => null,
            'headers' => [],
            'additionalQueryStringParams' => [],
            'tryItOutEnabled' => true,
        ]
    ]);
});

// Rotas públicas para feedback de recrutamento
Route::prefix('feedback-recrutamento')->group(function () {
    Route::get('/{token}', [App\Http\Controllers\FeedbackRecrutamentoController::class, 'mostrarFormulario']);
    Route::post('/{token}', [App\Http\Controllers\FeedbackRecrutamentoController::class, 'submeterFeedback']);
});

// Messages Routes (Authenticated)
Route::middleware('auth:sanctum')->prefix('messages')->group(function () {
    Route::get('/conversations', [App\Http\Controllers\MessageController::class, 'conversations']);
    Route::get('/', [App\Http\Controllers\MessageController::class, 'messages']);
    Route::post('/send', [App\Http\Controllers\MessageController::class, 'send']);
    Route::get('/companies', [App\Http\Controllers\MessageController::class, 'companies']);
    Route::get('/candidatos', [App\Http\Controllers\MessageController::class, 'candidatos']);
});
