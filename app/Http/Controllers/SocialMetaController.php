<?php

namespace App\Http\Controllers;

use App\Models\Oportunidade;
use Illuminate\Http\Request;

class SocialMetaController extends Controller
{
    /**
     * Gera meta tags para partilha social de uma oportunidade
     */
    public function oportunidade($slug)
    {
        $oportunidade = Oportunidade::with('company')
            ->where('slug', $slug)
            ->where('ativa', true)
            ->where('publica', true)
            ->firstOrFail();

        $title = $oportunidade->titulo . ' - ' . ($oportunidade->company->name ?? 'Empresa');
        
        // Descrição breve da oportunidade (máximo 160 caracteres para SEO)
        $baseDescription = substr(strip_tags($oportunidade->descricao), 0, 120);
        $description = $baseDescription;
        
        // Adicionar localização se disponível
        if ($oportunidade->localizacao) {
            $description .= " | Localização: " . $oportunidade->localizacao;
        }
        
        // Adicionar principais skills
        $skills = collect($oportunidade->skills_desejadas)->take(3)->pluck('nome')->implode(', ');
        if ($skills) {
            $description .= " | Skills: " . $skills;
        }
        
        $url = request()->url();
        
        // Priorizar logo da empresa, senão usar imagem padrão
        $image = $oportunidade->company->logo_url ?? 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="400" height="400" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="400" fill="#667eea"/>
                <circle cx="200" cy="150" r="60" fill="white"/>
                <rect x="140" y="220" width="120" height="80" rx="8" fill="white"/>
                <text x="200" y="340" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="24" font-weight="bold">CV Analyst</text>
            </svg>
        ');
        
        $siteName = config('app.name', 'CV Analyst');
        
        $metaTags = [
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'image' => $image,
            'siteName' => $siteName,
            'type' => 'article',
            'companyName' => $oportunidade->company->name ?? 'Empresa',
        ];

        return view('social-meta.oportunidade', compact('metaTags', 'oportunidade'));
    }
    
    /**
     * API endpoint para obter meta dados de uma oportunidade
     */
    public function oportunidadeApi($slug)
    {
        $oportunidade = Oportunidade::with('company')
            ->where('slug', $slug)
            ->where('ativa', true)
            ->where('publica', true)
            ->firstOrFail();

        $title = $oportunidade->titulo . ' - ' . ($oportunidade->company->name ?? 'Empresa');
        $description = substr(strip_tags($oportunidade->descricao), 0, 200);
        $url = config('app.frontend_url') . '/oportunidade/' . $slug;
        
        // Priorizar logo da empresa
        $image = $oportunidade->company->logo_url ?? 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="400" height="400" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="400" fill="#667eea"/>
                <circle cx="200" cy="150" r="60" fill="white"/>
                <rect x="140" y="220" width="120" height="80" rx="8" fill="white"/>
                <text x="200" y="340" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="24" font-weight="bold">CV Analyst</text>
            </svg>
        ');
        
        // Preparar skills para exibição (top 3)
        $skills = collect($oportunidade->skills_desejadas)->take(3)->pluck('nome')->implode(', ');
        
        return response()->json([
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'image' => $image,
            'skills' => $skills,
            'location' => $oportunidade->localizacao,
            'company' => [
                'name' => $oportunidade->company->name ?? 'Empresa',
                'logo' => $oportunidade->company->logo_url
            ],
            'oportunidade' => [
                'titulo' => $oportunidade->titulo,
                'slug' => $oportunidade->slug,
                'created_at' => $oportunidade->created_at->format('d/m/Y')
            ]
        ]);
    }
}
