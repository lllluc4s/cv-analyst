<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CandidatoAuthController extends Controller
{
    /**
     * Handle a registration request for candidatos.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'nome' => ['required', 'string', 'max:255'],
                'apelido' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:candidatos'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'telefone' => ['required', 'string', 'max:20'],
                'data_nascimento' => ['required', 'date', 'before:today'],
                'cv_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB máx
                'linkedin_url' => ['nullable', 'string', 'url', 'max:500'],
            ]);
            
            // Upload do arquivo CV
            $cvPath = null;
            if ($request->hasFile('cv_file')) {
                $file = $request->file('cv_file');
                $fileName = time() . '_' . Str::slug($request->nome . '-' . $request->apelido) . '.' . $file->getClientOriginalExtension();
                $cvPath = $file->storeAs('cvs', $fileName, 'public');
            }
            
            $candidato = Candidato::create([
                'nome' => $request->nome,
                'apelido' => $request->apelido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefone' => $request->telefone,
                'data_nascimento' => $request->data_nascimento,
                'cv_path' => $cvPath,
                'linkedin_url' => $request->linkedin_url,
                'email_verified_at' => now(), // Auto-verify email
            ]);
            
            return response()->json([
                'message' => 'Candidato registado com sucesso! Pode fazer login imediatamente.',
                'candidato' => [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'apelido' => $candidato->apelido,
                    'email' => $candidato->email,
                    'telefone' => $candidato->telefone,
                ]
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Candidato registration error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Handle a login request for candidatos.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);
            
            $candidato = Candidato::where('email', $request->email)->first();
            
            if (!$candidato || !Hash::check($request->password, $candidato->password)) {
                return response()->json([
                    'message' => 'Credenciais inválidas'
                ], 401);
            }
            
            // Create API token
            $token = $candidato->createToken('candidato-auth-token')->plainTextToken;
            
            return response()->json([
                'message' => 'Login realizado com sucesso',
                'token' => $token,
                'candidato' => [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'apelido' => $candidato->apelido,
                    'email' => $candidato->email,
                    'telefone' => $candidato->telefone,
                    'foto_url' => $candidato->foto_url,
                    'skills' => $candidato->skills,
                    'linkedin_url' => $candidato->linkedin_url,
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Candidato login error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'message' => 'Logout realizado com sucesso'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Candidato logout error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Get the authenticated candidato.
     */
    public function me(Request $request)
    {
        try {
            $candidato = $request->user();
            
            return response()->json([
                'candidato' => [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'apelido' => $candidato->apelido,
                    'email' => $candidato->email,
                    'telefone' => $candidato->telefone,
                    'foto_url' => $candidato->foto_url,
                    'skills' => $candidato->skills,
                    'experiencia_profissional' => $candidato->experiencia_profissional,
                    'formacao_academica' => $candidato->formacao_academica,
                    'cv_path' => $candidato->cv_path,
                    'linkedin_url' => $candidato->linkedin_url,
                    'is_searchable' => $candidato->is_searchable,
                    'consentimento_emails' => $candidato->consentimento_emails,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Get candidato profile error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
