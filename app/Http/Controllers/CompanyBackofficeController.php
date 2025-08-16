<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Oportunidade;
use App\Models\Candidatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyBackofficeController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of company's opportunities.
     */
    public function index(Request $request)
    {
        try {
            $company = $request->user();
            
            $oportunidades = $company->oportunidades()
                ->withCount('candidaturas')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'oportunidade' => $oportunidades
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Company backoffice index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Store a newly created opportunity.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'titulo' => ['required', 'string', 'max:255'],
                'descricao' => ['required', 'string'],
                'skills_desejadas' => ['required', 'array'],
                'skills_desejadas.*' => ['string'],
                'localizacao' => ['nullable', 'string', 'max:255'],
                'ativa' => ['boolean'],
                'publica' => ['boolean'],
            ]);
            
            $company = $request->user();
            
            // Garantir que company_id vem sempre do token autenticado
            $oportunidade = $company->oportunidades()->create([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'skills_desejadas' => $request->skills_desejadas,
                'localizacao' => $request->localizacao,
                'slug' => Str::slug($request->titulo),
                'ativa' => $request->ativa ?? true,
                'publica' => $request->publica ?? true,
            ]);
            
            return response()->json([
                'message' => 'Oportunidade criada com sucesso',
                'oportunidade' => $oportunidade
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Company opportunity store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Display the specified opportunity.
     */
    public function show(Request $request, $id)
    {
        try {
            $company = $request->user();
            
            $oportunidade = $company->oportunidades()
                ->with(['candidaturas.user'])
                ->withCount('candidaturas')
                ->findOrFail($id);
            
            // Autorização por política
            $this->authorize('view', $oportunidade);
            
            return response()->json([
                'oportunidade' => $oportunidade
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Oportunidade não encontrada'
            ], 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        } catch (\Exception $e) {
            Log::error('Company opportunity show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Update the specified opportunity.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'titulo' => ['sometimes', 'string', 'max:255'],
                'descricao' => ['sometimes', 'string'],
                'skills_desejadas' => ['sometimes', 'array'],
                'skills_desejadas.*' => ['string'],
                'localizacao' => ['sometimes', 'nullable', 'string', 'max:255'],
                'ativa' => ['sometimes', 'boolean'],
                'publica' => ['sometimes', 'boolean'],
            ]);
            
            $company = $request->user();
            
            $oportunidade = $company->oportunidades()->findOrFail($id);
            
            // Autorização por política
            $this->authorize('update', $oportunidade);
            
            $updateData = $request->only(['titulo', 'descricao', 'skills_desejadas', 'localizacao', 'ativa', 'publica']);
            
            // Garantir que company_id nunca seja alterado (remover do payload se existir)
            unset($updateData['company_id']);
            
            if (isset($updateData['titulo'])) {
                $updateData['slug'] = Str::slug($updateData['titulo']);
            }
            
            $oportunidade->update($updateData);
            
            return response()->json([
                'message' => 'Oportunidade atualizada com sucesso',
                'oportunidade' => $oportunidade
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Oportunidade não encontrada'
            ], 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Company opportunity update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Remove the specified opportunity.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $company = $request->user();
            
            $oportunidade = $company->oportunidades()->findOrFail($id);
            
            // Autorização por política
            $this->authorize('delete', $oportunidade);
            
            $oportunidade->delete();
            
            return response()->json([
                'message' => 'Oportunidade eliminada com sucesso'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Oportunidade não encontrada'
            ], 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        } catch (\Exception $e) {
            Log::error('Company opportunity destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Get candidates for a specific opportunity.
     */
    public function getCandidates(Request $request, $id)
    {
        try {
            $company = $request->user();
            
            $oportunidade = $company->oportunidades()->findOrFail($id);
            
            $candidaturas = $oportunidade->candidaturas()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($candidatura) {
                    return [
                        'id' => $candidatura->id,
                        'nome' => $candidatura->nome,
                        'email' => $candidatura->email,
                        'telefone' => $candidatura->telefone ?? null,
                        'linkedin_url' => $candidatura->linkedin_url ?? null,
                        'cv_path' => $candidatura->cv_path,
                        'cv_url' => $candidatura->cv_path ? asset('storage/' . $candidatura->cv_path) : null,
                        'skills' => $candidatura->skills,
                        'analysis_result' => $candidatura->analysis_result,
                        'score' => $candidatura->score,
                        'ranking' => $candidatura->ranking ?? null,
                        'created_at' => $candidatura->created_at,
                        'slug' => $candidatura->slug,
                        'consentimento_rgpd' => $candidatura->consentimento_rgpd ?? true,
                        'company_rating' => $candidatura->company_rating,
                    ];
                });
            
            return response()->json([
                'oportunidade' => [
                    'id' => $oportunidade->id,
                    'titulo' => $oportunidade->titulo,
                ],
                'candidaturas' => $candidaturas
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Oportunidade não encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Company get candidates error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Get dashboard statistics.
     */
    public function dashboard(Request $request)
    {
        try {
            $company = $request->user();
            
            $totalOportunidades = $company->oportunidades()->count();
            $oportunidadesAtivas = $company->oportunidades()->where('ativa', true)->count();
            $totalCandidaturas = Candidatura::whereHas('oportunidade', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })->count();
            
            $recentCandidaturas = Candidatura::whereHas('oportunidade', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
                ->with(['oportunidade:id,titulo', 'colaborador'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($candidatura) {
                    return [
                        'id' => $candidatura->id,
                        'nome' => $candidatura->nome,
                        'email' => $candidatura->email,
                        'oportunidade_titulo' => $candidatura->oportunidade->titulo,
                        'is_contratado' => $candidatura->colaborador !== null,
                        'score' => $candidatura->score,
                        'created_at' => $candidatura->created_at,
                    ];
                });
            
            return response()->json([
                'stats' => [
                    'total_oportunidades' => $totalOportunidades,
                    'oportunidades_ativas' => $oportunidadesAtivas,
                    'total_candidaturas' => $totalCandidaturas,
                ],
                'recent_candidaturas' => $recentCandidaturas
            ]);
            
        } catch (\Exception $e) {
            Log::error('Company dashboard error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Get opportunity reports for a company.
     */
    public function getOpportunityReports(Request $request, $id)
    {
        try {
            $company = $request->user();
            
            $oportunidade = $company->oportunidades()->findOrFail($id);
            
            // Usar o AnalyticsService diretamente para obter os dados no formato correto
            $analyticsService = app(\App\Services\AnalyticsService::class);
            $days = $request->input('days', 30);
            $existingReports = $analyticsService->getReports($oportunidade, $days);
            
            // Adicionar dados específicos da empresa
            $candidaturas = $oportunidade->candidaturas()
                ->selectRaw('DATE(created_at) as data, COUNT(*) as total')
                ->groupBy('data')
                ->orderBy('data', 'desc')
                ->limit(30)
                ->get();
            
            $totalCandidaturas = $oportunidade->candidaturas()->count();
            $candidaturasEstesMes = $oportunidade->candidaturas()
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();
            
            // Adaptar formato dos browsers para o frontend
            $browsersFormatted = [];
            foreach ($existingReports['browsers_mais_usados'] as $browserData) {
                $browsersFormatted[$browserData['browser']] = $browserData['total'];
            }
            
            return response()->json([
                'oportunidade' => $oportunidade->only(['id', 'titulo']),
                'visitas_temporais' => $existingReports['visitas_por_dia'] ?? [],
                'browsers' => $browsersFormatted,
                'localizacoes' => $existingReports['visitas_por_cidade'] ?? [],
                'total_visitas' => $existingReports['total_visitas'] ?? 0,
                'total_candidaturas' => $totalCandidaturas,
                'candidaturas_mes' => $candidaturasEstesMes,
                'candidaturas_temporais' => $candidaturas
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Oportunidade não encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Company opportunity reports error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor',
                'visitas_temporais' => [],
                'browsers' => [],
                'localizacoes' => [],
                'total_visitas' => 0,
                'total_candidaturas' => 0,
                'candidaturas_mes' => 0,
                'candidaturas_temporais' => []
            ]);
        }
    }
    
    /**
     * Upload company logo.
     */
    public function uploadLogo(Request $request)
    {
        try {
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            ]);

            $company = $request->user();
            
            // Delete old logo if exists
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            
            // Store new logo
            $logoPath = $request->file('logo')->store('company_logos', 'public');
            $logoUrl = Storage::disk('public')->url($logoPath);
            
            // Update company with new logo info
            $company->update([
                'logo_path' => $logoPath,
                'logo_url' => $logoUrl,
            ]);
            
            return response()->json([
                'message' => 'Logo uploaded successfully',
                'logo_url' => $logoUrl,
                'logo_path' => $logoPath,
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Invalid file',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Company logo upload error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Remove company logo.
     */
    public function removeLogo(Request $request)
    {
        try {
            $company = $request->user();
            
            // Delete logo file if exists
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            
            // Update company to remove logo info
            $company->update([
                'logo_path' => null,
                'logo_url' => null,
            ]);
            
            return response()->json([
                'message' => 'Logo removed successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Company logo remove error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Show company profile.
     */
    public function showProfile(Request $request)
    {
        try {
            $company = $request->user();
            
            return response()->json([
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'website' => $company->website,
                    'sector' => $company->sector,
                    'logo_url' => $company->logo_url,
                    'logo_path' => $company->logo_path,
                    'slug' => $company->slug,
                    'created_at' => $company->created_at,
                    'email_verified_at' => $company->email_verified_at,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Company profile show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Update company profile.
     */
    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'website' => ['nullable', 'url', 'max:255'],
                'sector' => ['nullable', 'string', 'max:255'],
            ]);

            $company = $request->user();
            
            $company->update([
                'name' => $request->name,
                'website' => $request->website,
                'sector' => $request->sector,
            ]);
            
            return response()->json([
                'message' => 'Perfil atualizado com sucesso',
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'website' => $company->website,
                    'sector' => $company->sector,
                    'logo_url' => $company->logo_url,
                    'logo_path' => $company->logo_path,
                    'slug' => $company->slug,
                    'created_at' => $company->created_at,
                    'email_verified_at' => $company->email_verified_at,
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Company profile update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
