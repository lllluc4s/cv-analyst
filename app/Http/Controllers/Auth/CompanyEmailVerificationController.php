<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class CompanyEmailVerificationController extends Controller
{
    /**
     * Mark the authenticated company's email address as verified.
     */
    public function verify(Request $request, $id, $hash)
    {
        $company = Company::findOrFail($id);
        
        if (!hash_equals((string) $hash, sha1($company->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Link de verificação inválido'
            ], 403);
        }
        
        if ($company->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email já verificado',
                'verified' => true
            ]);
        }
        
        if ($company->markEmailAsVerified()) {
            event(new Verified($company));
        }
        
        return response()->json([
            'message' => 'Email verificado com sucesso',
            'verified' => true
        ]);
    }
    
    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:companies,email'
        ]);
        
        $company = Company::where('email', $request->email)->first();
        
        if ($company->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email já verificado'
            ], 400);
        }
        
        $company->sendEmailVerificationNotification();
        
        return response()->json([
            'message' => 'Email de verificação reenviado'
        ]);
    }
}
