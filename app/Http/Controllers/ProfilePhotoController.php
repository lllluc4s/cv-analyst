<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfilePhotoController extends Controller
{
    /**
     * Upload profile photo for candidato
     */
    public function upload(Request $request)
    {
        try {
            $candidato = $request->user();
            
            $request->validate([
                'profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
            ]);
            
            // Delete old profile photo if exists
            if ($candidato->profile_photo && Storage::exists('public/' . $candidato->profile_photo)) {
                Storage::delete('public/' . $candidato->profile_photo);
            }
            
            // Upload new photo
            $path = $request->file('profile_photo')->store('candidatos/profile-photos', 'public');
            
            // Update candidato
            $candidato->update([
                'profile_photo' => $path
            ]);
            
            return response()->json([
                'message' => 'Foto de perfil atualizada com sucesso',
                'profile_photo_url' => asset('storage/' . $path),
                'candidato' => [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'apelido' => $candidato->apelido,
                    'email' => $candidato->email,
                    'profile_photo' => $path,
                    'avatar_url' => $candidato->avatar_url, // This will now use the new profile_photo
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Profile photo upload error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Delete profile photo for candidato
     */
    public function delete(Request $request)
    {
        try {
            $candidato = $request->user();
            
            // Delete photo file if exists
            if ($candidato->profile_photo && Storage::exists('public/' . $candidato->profile_photo)) {
                Storage::delete('public/' . $candidato->profile_photo);
            }
            
            // Update candidato
            $candidato->update([
                'profile_photo' => null
            ]);
            
            return response()->json([
                'message' => 'Foto de perfil removida com sucesso',
                'candidato' => [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'apelido' => $candidato->apelido,
                    'email' => $candidato->email,
                    'profile_photo' => null,
                    'avatar_url' => $candidato->avatar_url, // This will now fall back to initials
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Profile photo delete error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
