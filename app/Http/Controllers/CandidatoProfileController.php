<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CandidatoProfileController extends Controller
{
    /**
     * Update the candidato's profile.
     */
    public function update(Request $request)
    {
        try {
            $candidato = $request->user();
            
            Log::info('Update profile request received', [
                'user_id' => $candidato->id,
                'request_data' => $request->all()
            ]);
            
            $request->validate([
                'nome' => ['sometimes', 'string', 'max:255'],
                'apelido' => ['sometimes', 'string', 'max:255'],
                'telefone' => ['sometimes', 'nullable', 'string', 'max:20'],
                'data_nascimento' => ['sometimes', 'nullable', 'date', 'before:today'],
                'skills' => ['sometimes', 'array'],
                'experiencia_profissional' => ['sometimes', 'array'],
                'formacao_academica' => ['sometimes', 'array'],
                'linkedin_url' => ['sometimes', 'nullable', 'url'],
                'foto' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'profile_photo' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'cv' => ['sometimes', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            ]);
            
            // Handle profile photo upload (new field)
            if ($request->hasFile('profile_photo')) {
                // Delete old profile photo if exists
                if ($candidato->profile_photo && Storage::exists('public/' . $candidato->profile_photo)) {
                    Storage::delete('public/' . $candidato->profile_photo);
                }
                
                $path = $request->file('profile_photo')->store('candidatos/profile-photos', 'public');
                $candidato->profile_photo = $path;
            }

            // Handle photo upload (legacy field)
            if ($request->hasFile('foto')) {
                // Delete old photo if exists
                if ($candidato->foto_path && Storage::exists($candidato->foto_path)) {
                    Storage::delete($candidato->foto_path);
                }
                
                $path = $request->file('foto')->store('candidatos/fotos', 'public');
                $candidato->foto_path = $path;
                $candidato->foto_url = Storage::url($path);
            }
            
            // Handle CV upload
            if ($request->hasFile('cv')) {
                // Delete old CV if exists
                if ($candidato->cv_path && Storage::exists($candidato->cv_path)) {
                    Storage::delete($candidato->cv_path);
                }
                
                $path = $request->file('cv')->store('candidatos/cvs', 'public');
                $candidato->cv_path = $path;
            }
            
            // Update other fields
            $candidato->fill($request->only([
                'nome', 'apelido', 'telefone', 'data_nascimento', 'skills', 
                'experiencia_profissional', 'formacao_academica', 'linkedin_url'
            ]));
            
            $candidato->save();
            
            Log::info('Profile updated successfully', [
                'user_id' => $candidato->id,
                'updated_fields' => $candidato->getChanges()
            ]);
            
            return response()->json([
                'message' => 'Perfil atualizado com sucesso',
                'candidato' => [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'apelido' => $candidato->apelido,
                    'email' => $candidato->email,
                    'telefone' => $candidato->telefone,
                    'data_nascimento' => $candidato->data_nascimento,
                    'foto_url' => $candidato->foto_url,
                    'skills' => $candidato->skills,
                    'experiencia_profissional' => $candidato->experiencia_profissional,
                    'formacao_academica' => $candidato->formacao_academica,
                    'cv_path' => $candidato->cv_path,
                    'linkedin_url' => $candidato->linkedin_url,
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update candidato profile error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Get candidato's candidaturas history.
     */
    public function candidaturas(Request $request)
    {
        try {
            $candidato = $request->user();
            
            // Buscar candidaturas pelo email do candidato, já que o relacionamento pode não estar funcionando
            $candidaturas = \App\Models\Candidatura::where('email', $candidato->email)
                ->with(['oportunidade' => function($query) {
                    $query->select('id', 'titulo', 'company_id', 'localizacao', 'slug', 'descricao')
                          ->with('company:id,name,logo_url'); // Carregar a empresa associada
                }])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            return response()->json([
                'data' => $candidaturas->items(),
                'meta' => [
                    'current_page' => $candidaturas->currentPage(),
                    'from' => $candidaturas->firstItem(),
                    'last_page' => $candidaturas->lastPage(),
                    'per_page' => $candidaturas->perPage(),
                    'to' => $candidaturas->lastItem(),
                    'total' => $candidaturas->total(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Get candidato candidaturas error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Apply to an opportunity (1-click application)
     */
    public function candidatar(Request $request, $oportunidade)
    {
        try {
            $candidato = $request->user();
            
            // Verificar se a oportunidade existe e está ativa
            $oportunidadeModel = \App\Models\Oportunidade::where('id', $oportunidade)
                ->orWhere('slug', $oportunidade)
                ->first();
                
            if (!$oportunidadeModel) {
                return response()->json([
                    'message' => 'Oportunidade não encontrada'
                ], 404);
            }
            
            if (!$oportunidadeModel->ativa) {
                return response()->json([
                    'message' => 'Esta oportunidade não está mais ativa'
                ], 422);
            }
            
            // Verificar se já se candidatou
            $candidaturaExistente = \App\Models\Candidatura::where('candidato_id', $candidato->id)
                ->where('oportunidade_id', $oportunidadeModel->id)
                ->first();
                
            if ($candidaturaExistente) {
                return response()->json([
                    'message' => 'Você já se candidatou a esta oportunidade'
                ], 422);
            }
            
            // Verificar se o candidato tem CV
            if (!$candidato->cv_path) {
                return response()->json([
                    'message' => 'É necessário ter um CV no seu perfil para se candidatar'
                ], 422);
            }
            
            // Criar candidatura automática
            $candidatura = \App\Models\Candidatura::create([
                'candidato_id' => $candidato->id,
                'oportunidade_id' => $oportunidadeModel->id,
                'nome' => $candidato->nome,
                'apelido' => $candidato->apelido,
                'email' => $candidato->email,
                'telefone' => $candidato->telefone,
                'cv_path' => $candidato->cv_path,
                'linkedin_url' => $candidato->linkedin_url,
                'skills' => $candidato->skills ?? [],
                'rgpd_aceito' => true,
                'consentimento_rgpd' => true,
            ]);
            
            return response()->json([
                'message' => 'Candidatura realizada com sucesso!',
                'candidatura' => [
                    'id' => $candidatura->id,
                    'oportunidade' => $oportunidadeModel->titulo,
                    'data' => $candidatura->created_at->format('d/m/Y H:i')
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Candidatura error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Check if candidate can apply to an opportunity
     */
    public function canApply(Request $request, $oportunidade)
    {
        try {
            $candidato = $request->user();
            
            // Verificar se a oportunidade existe e está ativa
            $oportunidadeModel = \App\Models\Oportunidade::where('id', $oportunidade)
                ->orWhere('slug', $oportunidade)
                ->first();
                
            if (!$oportunidadeModel) {
                return response()->json([
                    'can_apply' => false,
                    'reason' => 'Oportunidade não encontrada'
                ]);
            }
            
            if (!$oportunidadeModel->ativa) {
                return response()->json([
                    'can_apply' => false,
                    'reason' => 'Esta oportunidade não está mais ativa'
                ]);
            }
            
            // Verificar se já se candidatou
            $candidaturaExistente = \App\Models\Candidatura::where('candidato_id', $candidato->id)
                ->where('oportunidade_id', $oportunidadeModel->id)
                ->exists();
                
            if ($candidaturaExistente) {
                return response()->json([
                    'can_apply' => false,
                    'reason' => 'Você já se candidatou a esta oportunidade'
                ]);
            }
            
            // Verificar se o candidato tem CV
            if (!$candidato->cv_path) {
                return response()->json([
                    'can_apply' => false,
                    'reason' => 'É necessário ter um CV no seu perfil para se candidatar'
                ]);
            }
            
            return response()->json([
                'can_apply' => true,
                'oportunidade' => [
                    'id' => $oportunidadeModel->id,
                    'titulo' => $oportunidadeModel->titulo,
                    'empresa' => $oportunidadeModel->empresa
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Can apply check error: ' . $e->getMessage());
            return response()->json([
                'can_apply' => false,
                'reason' => 'Erro interno do servidor'
            ]);
        }
    }
}
