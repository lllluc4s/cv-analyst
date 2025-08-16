<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Oportunidade;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicCompanyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/public/empresa/{slug}",
     *     tags={"🌐 Público"},
     *     summary="Visualizar página de empresa",
     *     description="Retorna informações da empresa e suas oportunidades públicas",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug da empresa",
     *         @OA\Schema(type="string", example="tech-solutions")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informações da empresa",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="company",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tech Solutions"),
     *                 @OA\Property(property="website", type="string", example="https://techsolutions.pt"),
     *                 @OA\Property(property="sector", type="string", example="Tecnologia")
     *             ),
     *             @OA\Property(
     *                 property="oportunidades",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="titulo", type="string", example="Desenvolvedor Full Stack"),
     *                     @OA\Property(property="descricao", type="string", example="Procuramos um desenvolvedor..."),
     *                     @OA\Property(property="skills_desejadas", type="array", @OA\Items(type="string"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empresa não encontrada"
     *     )
     * )
     */
    public function show($slug)
    {
        // Tentar buscar empresa pelo slug primeiro
        $company = Company::where('slug', $slug)->first();
        
        // Se não encontrar, tentar buscar por slug gerado a partir do nome
        if (!$company) {
            $company = Company::whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [strtolower($slug)])
                ->orWhereRaw('LOWER(REPLACE(name, " ", "_")) = ?', [strtolower($slug)])
                ->first();
        }
        
        // Se ainda não encontrar, tentar buscar pelo nome com correspondência aproximada
        if (!$company) {
            $searchName = str_replace('-', ' ', $slug);
            $company = Company::where('name', 'like', '%' . $searchName . '%')->first();
        }
        
        if (!$company) {
            abort(404, 'Empresa não encontrada');
        }

        // Buscar oportunidades públicas e ativas da empresa
        $oportunidades = $company->oportunidades()
            ->where('ativa', true)
            ->where('publica', true)
            ->with(['company:id,name,logo_url,website,sector'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($oportunidade) {
                return [
                    'id' => $oportunidade->id,
                    'titulo' => $oportunidade->titulo,
                    'descricao' => substr($oportunidade->descricao, 0, 200) . '...',
                    'skills_desejadas' => array_slice($oportunidade->skills_desejadas, 0, 5),
                    'localizacao' => $oportunidade->localizacao,
                    'slug' => $oportunidade->slug,
                    'created_at' => $oportunidade->created_at,
                    'company' => [
                        'name' => $oportunidade->company->name,
                        'logo_url' => $oportunidade->company->logo_url,
                        'website' => $oportunidade->company->website,
                        'sector' => $oportunidade->company->sector,
                    ]
                ];
            });

        return response()->json([
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'website' => $company->website,
                'sector' => $company->sector,
                'logo_url' => $company->logo_url,
                'total_oportunidades' => $oportunidades->count()
            ],
            'oportunidades' => $oportunidades
        ]);
    }

    /**
     * @OA\Get(
     *     path="/public/oportunidades",
     *     tags={"🌐 Público"},
     *     summary="Listar todas as oportunidades públicas",
     *     description="Retorna uma lista paginada de todas as oportunidades públicas e ativas de todas as empresas",
     *     @OA\Parameter(
     *         name="skills",
     *         in="query",
     *         description="Filtrar por competências (separadas por vírgula)",
     *         required=false,
     *         @OA\Schema(type="string", example="React,JavaScript,PHP")
     *     ),
     *     @OA\Parameter(
     *         name="company_id",
     *         in="query",
     *         description="Filtrar por ID da empresa",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Filtrar por localização",
     *         required=false,
     *         @OA\Schema(type="string", example="Lisboa")
     *     ),
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
     *                 @OA\Property(property="slug", type="string", example="desenvolvedor-full-stack-1"),
     *                 @OA\Property(property="descricao", type="string", example="Procuramos um desenvolvedor..."),
     *                 @OA\Property(property="localizacao", type="string", example="Lisboa"),
     *                 @OA\Property(property="tipo_contrato", type="string", example="full-time"),
     *                 @OA\Property(property="remoto", type="boolean", example=true),
     *                 @OA\Property(
     *                     property="company",
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Tech Solutions"),
     *                     @OA\Property(property="sector", type="string", example="Tecnologia")
     *                 )
     *             )),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=5),
     *             @OA\Property(property="total", type="integer", example=50)
     *         )
     *     )
     * )
     */
    public function publicOpportunities(Request $request)
    {
        $query = Oportunidade::where('ativa', true)
            ->where('publica', true)
            ->with(['company:id,name,logo_url,website,sector']);

        // Filtrar por skills se especificado
        if ($request->has('skills') && !empty($request->skills)) {
            $skills = is_array($request->skills) ? $request->skills : explode(',', $request->skills);
            $query->where(function ($q) use ($skills) {
                foreach ($skills as $skill) {
                    $skill = trim($skill);
                    $q->orWhere('skills_desejadas', 'like', '%"nome":"' . $skill . '"%');
                }
            });
        }

        // Filtrar por empresa se especificado
        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }

        // Filtrar por localização se especificado
        if ($request->has('localizacao') && !empty($request->localizacao)) {
            $query->where('localizacao', 'like', '%' . $request->localizacao . '%');
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $oportunidades = $query->paginate(12);

        // Formatar dados para resposta
        $oportunidades->getCollection()->transform(function ($oportunidade) {
            return [
                'id' => $oportunidade->id,
                'titulo' => $oportunidade->titulo,
                'descricao' => substr($oportunidade->descricao, 0, 200) . '...',
                'skills_desejadas' => array_slice($oportunidade->skills_desejadas, 0, 5),
                'localizacao' => $oportunidade->localizacao,
                'slug' => $oportunidade->slug,
                'created_at' => $oportunidade->created_at,
                'company' => $oportunidade->company ? [
                    'id' => $oportunidade->company->id,
                    'name' => $oportunidade->company->name,
                    'logo_url' => $oportunidade->company->logo_url,
                    'website' => $oportunidade->company->website,
                    'sector' => $oportunidade->company->sector,
                ] : null
            ];
        });

        return response()->json($oportunidades);
    }

    /**
     * Get all companies with public opportunities for filters.
     */
    public function companiesWithOpportunities()
    {
        $companies = Company::whereHas('oportunidades', function ($query) {
            $query->where('ativa', true)->where('publica', true);
        })
        ->withCount(['oportunidades' => function ($query) {
            $query->where('ativa', true)->where('publica', true);
        }])
        ->select('id', 'name', 'logo_url', 'sector')
        ->orderBy('name')
        ->get();

        return response()->json($companies);
    }

    /**
     * Get all skills from public opportunities for filters.
     */
    public function availableSkills()
    {
        $oportunidades = Oportunidade::where('ativa', true)
            ->where('publica', true)
            ->select('skills_desejadas')
            ->get();

        $skills = collect();
        foreach ($oportunidades as $oportunidade) {
            foreach ($oportunidade->skills_desejadas as $skill) {
                if (isset($skill['nome'])) {
                    $skills->push($skill['nome']);
                }
            }
        }

        $uniqueSkills = $skills->unique()->sort()->values();

        return response()->json($uniqueSkills);
    }

    /**
     * Get all locations from public opportunities for filters.
     */
    public function availableLocations()
    {
        $locations = Oportunidade::where('ativa', true)
            ->where('publica', true)
            ->whereNotNull('localizacao')
            ->where('localizacao', '!=', '')
            ->select('localizacao')
            ->distinct()
            ->orderBy('localizacao')
            ->get()
            ->pluck('localizacao');

        return response()->json($locations);
    }

    /**
     * Display a listing of all companies with public opportunities.
     */
    public function companies()
    {
        $companies = Company::whereHas('oportunidades', function ($query) {
            $query->where('ativa', true)->where('publica', true);
        })
        ->withCount(['oportunidades' => function ($query) {
            $query->where('ativa', true)->where('publica', true);
        }])
        ->select('id', 'name', 'slug', 'logo_url', 'sector', 'website')
        ->orderBy('name')
        ->get()
        ->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'logo_url' => $company->logo_url,
                'sector' => $company->sector,
                'website' => $company->website,
                'oportunidades_count' => $company->oportunidades_count ?? 0,
                'page_url' => '/empresa/' . $company->slug
            ];
        });

        return response()->json($companies);
    }
}
