<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oportunidade;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OportunidadeController extends Controller
{
    use AuthorizesRequests;
    /**
     * @OA\Get(
     *     path="/oportunidades",
     *     tags={"💼 Oportunidades"},
     *     summary="Listar oportunidades da empresa",
     *     description="Retorna todas as oportunidades da empresa autenticada",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de oportunidades",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="titulo", type="string", example="Desenvolvedor Full Stack"),
     *                 @OA\Property(property="descricao", type="string", example="Procuramos um desenvolvedor experiente..."),
     *                 @OA\Property(property="localizacao", type="string", example="Lisboa"),
     *                 @OA\Property(property="salario_min", type="integer", example=30000),
     *                 @OA\Property(property="salario_max", type="integer", example=45000),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=5),
     *             @OA\Property(property="total", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário não autenticado")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            // Obter apenas oportunidades da empresa logada
            $company = $request->user(); // Assume que o user autenticado é uma Company
            
            if (!$company) {
                return response()->json([
                    'message' => 'Usuário não autenticado'
                ], 401);
            }
            
            if (!method_exists($company, 'oportunidades')) {
                return response()->json([
                    'message' => 'Tipo de usuário inválido'
                ], 403);
            }
            
            $oportunidades = $company->oportunidades()
                ->orderBy('created_at', 'desc')
                ->paginate(10);
                
            return response()->json($oportunidades, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/oportunidades",
     *     tags={"💼 Oportunidades"},
     *     summary="Criar nova oportunidade",
     *     description="Cria uma nova oportunidade de emprego para a empresa autenticada",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo","descricao","localizacao"},
     *             @OA\Property(property="titulo", type="string", example="Desenvolvedor Full Stack"),
     *             @OA\Property(property="descricao", type="string", example="Procuramos um desenvolvedor experiente em React e Laravel..."),
     *             @OA\Property(property="localizacao", type="string", example="Lisboa"),
     *             @OA\Property(property="tipo_contrato", type="string", enum={"full-time","part-time","freelance","estágio"}, example="full-time"),
     *             @OA\Property(property="salario_min", type="integer", example=30000),
     *             @OA\Property(property="salario_max", type="integer", example=45000),
     *             @OA\Property(property="remoto", type="boolean", example=true),
     *             @OA\Property(
     *                 property="skills",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"React", "Laravel", "JavaScript", "PHP"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Oportunidade criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Oportunidade criada com sucesso"),
     *             @OA\Property(
     *                 property="oportunidade",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="titulo", type="string", example="Desenvolvedor Full Stack"),
     *                 @OA\Property(property="slug", type="string", example="desenvolvedor-full-stack-1")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descricao' => 'required|string',
                'skills_desejadas' => 'required|array|min:1',
                'skills_desejadas.*.nome' => 'required|string|max:100',
                'skills_desejadas.*.peso' => 'required|numeric|min:1|max:100',
                'publica' => 'boolean',
                'localizacao' => 'nullable|string|max:255',
            ]);

            // Garantir que company_id vem sempre do token autenticado
            $validated['company_id'] = $request->user()->id;

            $oportunidade = Oportunidade::create($validated);

            return response()->json([
                'message' => 'Oportunidade criada com sucesso!',
                'data' => $oportunidade
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Oportunidade $oportunidade)
    {
        try {
            // Autorização por política
            $this->authorize('view', $oportunidade);

            return response()->json($oportunidade, 200);
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso negado. Esta oportunidade não pertence à sua empresa.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Oportunidade $oportunidade)
    {
        try {
            // Autorização por política
            $this->authorize('update', $oportunidade);

            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descricao' => 'required|string',
                'skills_desejadas' => 'required|array|min:1',
                'skills_desejadas.*.nome' => 'required|string|max:100',
                'skills_desejadas.*.peso' => 'required|numeric|min:1|max:100',
                'ativa' => 'boolean',
                'publica' => 'boolean',
                'localizacao' => 'nullable|string|max:255',
            ]);

            // Garantir que company_id nunca seja alterado (remover do payload se existir)
            unset($validated['company_id']);

            $oportunidade->update($validated);

            return response()->json([
                'message' => 'Oportunidade atualizada com sucesso!',
                'data' => $oportunidade
            ], 200);
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso negado. Esta oportunidade não pertence à sua empresa.'
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Oportunidade $oportunidade)
    {
        try {
            // Autorização por política
            $this->authorize('delete', $oportunidade);

            $oportunidade->delete();

            return response()->json([
                'message' => 'Oportunidade excluída com sucesso!'
            ], 200);
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'Acesso negado. Esta oportunidade não pertence à sua empresa.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource by slug.
     */
    public function showBySlug(string $slug)
    {
        // Buscar diretamente pelo slug
        $oportunidade = Oportunidade::where('slug', $slug)->first();

        if (!$oportunidade) {
            return response()->json([
                'message' => 'Oportunidade não encontrada.'
            ], 404);
        }

        return response()->json($oportunidade);
    }

    /**
     * Display a listing of public opportunities.
     */
    public function publicIndex()
    {
        $oportunidades = Oportunidade::where('publica', true)
            ->where('ativa', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json($oportunidades);
    }
}
