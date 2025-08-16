<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="CV Analyst API",
 *     version="1.0.0",
 *     description="API para análise de CVs e gestão de oportunidades de emprego",
 *     @OA\Contact(
 *         email="lucas.rodrigues@team.inovcorp.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8001/api",
 *     description="Servidor de Desenvolvimento"
 * )
 * 
 * @OA\Server(
 *     url="https://lucas-cv.iwork.pt/api",
 *     description="Servidor de Produção"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use o token de autenticação Bearer"
 * )
 * 
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Operações de autenticação para empresas e candidatos"
 * )
 * 
 * @OA\Tag(
 *     name="Oportunidades",
 *     description="Gestão de oportunidades de emprego"
 * )
 * 
 * @OA\Tag(
 *     name="Candidaturas",
 *     description="Gestão de candidaturas"
 * )
 * 
 * @OA\Tag(
 *     name="Análise de CV",
 *     description="Análise automatizada de currículos"
 * )
 * 
 * @OA\Tag(
 *     name="Empresas",
 *     description="Gestão de perfis empresariais"
 * )
 * 
 * @OA\Tag(
 *     name="Candidatos",
 *     description="Gestão de perfis de candidatos"
 * )
 * 
 * @OA\Tag(
 *     name="Kanban",
 *     description="Sistema de gestão kanban para candidaturas"
 * )
 * 
 * @OA\Tag(
 *     name="Relatórios",
 *     description="Geração de relatórios e estatísticas"
 * )
 */
class SwaggerController extends Controller
{
    //
}
