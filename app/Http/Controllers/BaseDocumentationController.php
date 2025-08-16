<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="CV Analyst API",
 *     version="1.0.0",
 *     description="
 * **CV Analyst API** - Solução completa para análise de CVs e gestão de oportunidades de emprego
 * 
 * ## 🚀 Funcionalidades Principais:
 * - **Análise Automática de CVs**: Utilize IA para analisar e pontuar currículos
 * - **Gestão de Oportunidades**: Publique e gira vagas de emprego
 * - **Sistema Kanban**: Gestão visual do processo de recrutamento
 * - **API Pública**: Endpoints públicos para listagem de vagas
 * - **Relatórios**: Estatísticas e relatórios detalhados
 * 
 * ## 🔐 Autenticação:
 * A API utiliza Laravel Sanctum com tokens Bearer. Primeiro registe-se ou faça login para obter um token, depois inclua-o no header: `Authorization: Bearer {seu-token}`
 * 
 * ## 📝 Exemplos:
 * - Para testar endpoints protegidos, clique em 'Authorize' e insira seu token
 * - Endpoints públicos não requerem autenticação
 * - Todos os uploads de arquivos devem ser PDFs (máximo 5MB)
 * 
 * ## 🌐 Ambientes:
 * - **Desenvolvimento**: {APP_URL}/api
 * - **Produção**: {PRODUCTION_API_URL}/api
 * 
 * As URLs são configuradas dinamicamente através das variáveis de ambiente.
 * ",
 *     @OA\Contact(
 *         email="lucas.rodrigues@team.inovcorp.com",
 *         name="Suporte CV Analyst",
 *         url="{PRODUCTION_FRONTEND_URL}"
 *     ),
 *     @OA\License(
 *         name="Proprietary",
 *         url="{PRODUCTION_FRONTEND_URL}/license"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8001/api",
 *     description="🔧 Servidor de Desenvolvimento"
 * )
 * 
 * @OA\Server(
 *     url="https://lucas-cv.iwork.pt/api",
 *     description="🌐 Servidor de Produção"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token de autenticação Bearer obtido através do login. Formato: Bearer {token}"
 * )
 * 
 * @OA\Tag(
 *     name="🔐 Autenticação",
 *     description="Operações de registo e login para empresas e candidatos"
 * )
 * 
 * @OA\Tag(
 *     name="💼 Oportunidades",
 *     description="Gestão completa de oportunidades de emprego (CRUD)"
 * )
 * 
 * @OA\Tag(
 *     name="📄 Candidaturas",
 *     description="Gestão de candidaturas a oportunidades"
 * )
 * 
 * @OA\Tag(
 *     name="🤖 Análise de CV",
 *     description="Análise automatizada de currículos usando inteligência artificial"
 * )
 * 
 * @OA\Tag(
 *     name="🏢 Empresas",
 *     description="Gestão de perfis empresariais e backoffice"
 * )
 * 
 * @OA\Tag(
 *     name="👤 Candidatos",
 *     description="Gestão de perfis de candidatos"
 * )
 * 
 * @OA\Tag(
 *     name="📋 Kanban",
 *     description="Sistema de gestão kanban para processos de recrutamento"
 * )
 * 
 * @OA\Tag(
 *     name="📊 Relatórios",
 *     description="Geração de relatórios e estatísticas detalhadas"
 * )
 * 
 * @OA\Tag(
 *     name="🌐 Público",
 *     description="Endpoints públicos sem necessidade de autenticação"
 * )
 * 
 * @OA\Tag(
 *     name="🛡️ GDPR",
 *     description="Operações relacionadas com proteção de dados pessoais"
 * )
 */

class BaseDocumentationController extends Controller
{
    /**
     * Get dynamic server configuration for Swagger
     */
    public static function getSwaggerServers(): array
    {
        return [
            [
                'url' => api_url(),
                'description' => '🔧 Servidor de Desenvolvimento'
            ],
            [
                'url' => env('PRODUCTION_API_URL', 'https://lucas-cv.iwork.pt') . '/api',
                'description' => '🌐 Servidor de Produção'
            ]
        ];
    }

    /**
     * Get dynamic contact info for Swagger
     */
    public static function getSwaggerContact(): array
    {
        return [
            'email' => 'lucas.rodrigues@team.inovcorp.com',
            'name' => 'Suporte CV Analyst',
            'url' => env('PRODUCTION_FRONTEND_URL', 'https://lucas-cv.iwork.pt')
        ];
    }
}
