<?php

namespace App\Http\Controllers;

use App\Models\Candidatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CandidatePrivacyController extends Controller
{
    /**
     * Página de consentimento para emails (público)
     */
    public function showConsentForm($slug)
    {
        $candidatura = Candidatura::where('slug', $slug)->firstOrFail();
        
        return view('candidate.consent', compact('candidatura'));
    }

    /**
     * Processar consentimento de emails
     */
    public function updateConsent(Request $request, $slug)
    {
        $candidatura = Candidatura::where('slug', $slug)->firstOrFail();
        
        $validated = $request->validate([
            'consentimento_emails' => 'required|boolean',
            'pode_ser_contactado' => 'required|boolean'
        ]);

        $candidatura->update([
            'consentimento_emails' => $validated['consentimento_emails'],
            'consentimento_emails_data' => $validated['consentimento_emails'] ? now() : null,
            'pode_ser_contactado' => $validated['pode_ser_contactado']
        ]);

        return response()->json([
            'message' => 'Preferências de privacidade atualizadas com sucesso!',
            'candidatura' => [
                'id' => $candidatura->id,
                'consentimento_emails' => $candidatura->consentimento_emails,
                'pode_ser_contactado' => $candidatura->pode_ser_contactado
            ]
        ]);
    }

    /**
     * Verificar status do consentimento (API)
     */
    public function getConsentStatus($slug)
    {
        $candidatura = Candidatura::where('slug', $slug)->firstOrFail();
        
        return response()->json([
            'candidatura' => [
                'nome' => $candidatura->nome,
                'email' => $candidatura->email,
                'consentimento_emails' => $candidatura->consentimento_emails,
                'consentimento_emails_data' => $candidatura->consentimento_emails_data,
                'pode_ser_contactado' => $candidatura->pode_ser_contactado
            ]
        ]);
    }

    /**
     * Revogar consentimento
     */
    public function revokeConsent(Request $request, $slug)
    {
        $candidatura = Candidatura::where('slug', $slug)->firstOrFail();
        
        $candidatura->update([
            'consentimento_emails' => false,
            'consentimento_emails_data' => null,
            'pode_ser_contactado' => false
        ]);

        return response()->json([
            'message' => 'Consentimento revogado com sucesso. Não receberá mais emails automáticos.'
        ]);
    }
}
