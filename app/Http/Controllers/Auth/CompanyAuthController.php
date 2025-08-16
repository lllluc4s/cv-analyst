<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class CompanyAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/companies/register",
     *     tags={"🔐 Autenticação"},
     *     summary="Registar nova empresa",
     *     description="Cria uma nova conta de empresa no sistema",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Tech Solutions Lda"),
     *             @OA\Property(property="email", type="string", format="email", example="admin@techsolutions.pt"),
     *             @OA\Property(property="password", type="string", format="password", example="senha123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="senha123"),
     *             @OA\Property(property="website", type="string", format="url", example="https://techsolutions.pt"),
     *             @OA\Property(property="sector", type="string", example="Tecnologia")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Empresa registada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa registada com sucesso! Verifique o seu email para ativar a conta."),
     *             @OA\Property(
     *                 property="company",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tech Solutions Lda"),
     *                 @OA\Property(property="email", type="string", example="admin@techsolutions.pt"),
     *                 @OA\Property(property="website", type="string", example="https://techsolutions.pt"),
     *                 @OA\Property(property="sector", type="string", example="Tecnologia")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:companies'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'website' => ['nullable', 'url', 'max:255'],
                'sector' => ['nullable', 'string', 'max:255'],
            ]);
            
            $company = Company::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'website' => $request->website,
                'sector' => $request->sector,
            ]);
            
            // Trigger email verification
            event(new Registered($company));
            
            return response()->json([
                'message' => 'Empresa registada com sucesso! Verifique o seu email para ativar a conta.',
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'website' => $company->website,
                    'sector' => $company->sector,
                ]
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Company registration error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * @OA\Post(
     *     path="/companies/login",
     *     tags={"🔐 Autenticação"},
     *     summary="Login de empresa",
     *     description="Autentica uma empresa e retorna o token de acesso",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@techsolutions.pt"),
     *             @OA\Property(property="password", type="string", format="password", example="senha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso"),
     *             @OA\Property(property="token", type="string", example="1|abc123def456..."),
     *             @OA\Property(
     *                 property="company",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tech Solutions Lda"),
     *                 @OA\Property(property="email", type="string", example="admin@techsolutions.pt"),
     *                 @OA\Property(property="website", type="string", example="https://techsolutions.pt")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciais inválidas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Email não verificado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email não verificado. Verifique o seu email para ativar a conta.")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);
            
            $company = Company::where('email', $request->email)->first();
            
            if (!$company || !Hash::check($request->password, $company->password)) {
                return response()->json([
                    'message' => 'Credenciais inválidas'
                ], 401);
            }
            
            if (!$company->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email não verificado. Verifique o seu email para ativar a conta.'
                ], 403);
            }
            
            // Create API token
            $token = $company->createToken('company-auth-token')->plainTextToken;
            
            return response()->json([
                'message' => 'Login realizado com sucesso',
                'token' => $token,
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'website' => $company->website,
                    'sector' => $company->sector,
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Company login error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Handle logout request for companies.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }
    
    /**
     * Get authenticated company info.
     */
    public function me(Request $request)
    {
        $company = $request->user();
        
        return response()->json([
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'website' => $company->website,
                'sector' => $company->sector,
                'logo_path' => $company->logo_path,
                'logo_url' => $company->logo_url,
                'email_verified_at' => $company->email_verified_at,
            ]
        ]);
    }
    
    /**
     * Resend email verification.
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);
        
        $company = Company::where('email', $request->email)->first();
        
        if (!$company) {
            return response()->json([
                'message' => 'Empresa não encontrada'
            ], 404);
        }
        
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
