<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Candidato;
use App\Models\Oportunidade;
use Illuminate\Support\Facades\Hash;

class CandidaturaOneClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidato_can_apply_to_opportunity_with_one_click()
    {
        // Criar oportunidade ativa
        $oportunidade = Oportunidade::factory()->create([
            'ativa' => true,
            'titulo' => 'Vaga de Teste'
        ]);
        
        // Criar candidato com CV
        $candidato = Candidato::factory()->create([
            'password' => Hash::make('password123'),
            'cv_path' => 'candidatos/cvs/cv_teste.pdf'
        ]);
        
        // Testar candidatura com 1 clique
        $response = $this->actingAs($candidato)
            ->postJson("/api/candidatos/oportunidades/{$oportunidade->id}/candidatar");
        
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Candidatura realizada com sucesso!'
            ]);
        
        // Verificar que a candidatura foi criada na base de dados
        $this->assertDatabaseHas('candidaturas', [
            'candidato_id' => $candidato->id,
            'oportunidade_id' => $oportunidade->id,
            'email' => $candidato->email
        ]);
    }

    public function test_candidato_can_check_if_can_apply()
    {
        // Criar oportunidade ativa
        $oportunidade = Oportunidade::factory()->create([
            'ativa' => true,
            'titulo' => 'Vaga de Teste'
        ]);
        
        // Criar candidato com CV
        $candidato = Candidato::factory()->create([
            'cv_path' => 'candidatos/cvs/cv_teste.pdf'
        ]);
        
        // Testar verificação se pode candidatar-se
        $response = $this->actingAs($candidato)
            ->getJson("/api/candidatos/oportunidades/{$oportunidade->id}/can-apply");
        
        $response->assertStatus(200)
            ->assertJson([
                'can_apply' => true
            ]);
    }

    public function test_candidato_cannot_apply_without_cv()
    {
        // Criar oportunidade ativa
        $oportunidade = Oportunidade::factory()->create([
            'ativa' => true
        ]);
        
        // Criar candidato sem CV
        $candidato = Candidato::factory()->create([
            'cv_path' => null
        ]);
        
        // Testar candidatura sem CV
        $response = $this->actingAs($candidato)
            ->postJson("/api/candidatos/oportunidades/{$oportunidade->id}/candidatar");
        
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'É necessário ter um CV no seu perfil para se candidatar'
            ]);
    }

    public function test_candidato_cannot_apply_twice()
    {
        // Criar oportunidade ativa
        $oportunidade = Oportunidade::factory()->create([
            'ativa' => true
        ]);
        
        // Criar candidato com CV
        $candidato = Candidato::factory()->create([
            'cv_path' => 'candidatos/cvs/cv_teste.pdf'
        ]);
        
        // Primeira candidatura
        $this->actingAs($candidato)
            ->postJson("/api/candidatos/oportunidades/{$oportunidade->id}/candidatar")
            ->assertStatus(201);
        
        // Segunda candidatura (deve falhar)
        $response = $this->actingAs($candidato)
            ->postJson("/api/candidatos/oportunidades/{$oportunidade->id}/candidatar");
        
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Você já se candidatou a esta oportunidade'
            ]);
    }
}
