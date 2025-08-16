<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Candidato;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CandidatoPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidato_can_view_privacy_settings()
    {
        $candidato = Candidato::factory()->create();
        
        $response = $this->actingAs($candidato)
            ->getJson('/api/candidatos/privacy/settings');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'is_searchable',
                'email',
                'created_at'
            ]);
    }

    public function test_candidato_can_update_searchability()
    {
        $candidato = Candidato::factory()->create(['is_searchable' => true]);
        
        $response = $this->actingAs($candidato)
            ->putJson('/api/candidatos/privacy/searchability', [
                'is_searchable' => false
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'is_searchable' => false
            ]);
        
        $this->assertDatabaseHas('candidatos', [
            'id' => $candidato->id,
            'is_searchable' => false
        ]);
    }

    public function test_candidato_can_export_data()
    {
        $candidato = Candidato::factory()->create();
        
        $response = $this->actingAs($candidato)
            ->getJson('/api/candidatos/privacy/export-data');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'dados_pessoais',
                    'dados_profissionais',
                    'configuracoes_privacidade',
                    'metadados'
                ],
                'exported_at'
            ]);
    }

    public function test_data_encryption_works()
    {
        $candidato = Candidato::create([
            'nome' => 'Test',
            'apelido' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'telefone' => '911234567',
            'linkedin_url' => 'https://linkedin.com/in/test',
            'is_searchable' => true,
        ]);

        // Verificar que os dados são armazenados criptografados na base de dados
        $rawData = DB::table('candidatos')->where('id', $candidato->id)->first();
        
        // Os dados criptografados não devem ser iguais aos dados originais
        $this->assertNotEquals('911234567', $rawData->telefone);
        $this->assertNotEquals('https://linkedin.com/in/test', $rawData->linkedin_url);
        
        // Mas ao acessar via model, devem ser descriptografados
        $this->assertEquals('911234567', $candidato->telefone);
        $this->assertEquals('https://linkedin.com/in/test', $candidato->linkedin_url);
    }

    public function test_searchable_scope_works()
    {
        Candidato::factory()->create(['is_searchable' => true]);
        Candidato::factory()->create(['is_searchable' => false]);
        
        $searchableCandidatos = Candidato::searchable()->count();
        $this->assertEquals(1, $searchableCandidatos);
    }

    public function test_candidato_can_delete_account_permanently()
    {
        $candidato = Candidato::factory()->create([
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->actingAs($candidato)
            ->postJson('/api/candidatos/privacy/delete-account', [
                'password' => 'password123',
                'confirmation' => 'DELETE_MY_ACCOUNT'
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Conta excluída permanentemente com sucesso'
            ]);
        
        // Verificar que o candidato foi removido permanentemente da base de dados
        $this->assertDatabaseMissing('candidatos', [
            'id' => $candidato->id
        ]);
    }

    public function test_delete_account_fails_with_wrong_password()
    {
        $candidato = Candidato::factory()->create([
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->actingAs($candidato)
            ->postJson('/api/candidatos/privacy/delete-account', [
                'password' => 'wrongpassword',
                'confirmation' => 'DELETE_MY_ACCOUNT'
            ]);
        
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Senha incorreta'
            ]);
        
        // Verificar que o candidato ainda existe na base de dados
        $this->assertDatabaseHas('candidatos', [
            'id' => $candidato->id
        ]);
    }

    public function test_delete_account_fails_with_wrong_confirmation()
    {
        $candidato = Candidato::factory()->create([
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->actingAs($candidato)
            ->postJson('/api/candidatos/privacy/delete-account', [
                'password' => 'password123',
                'confirmation' => 'WRONG_CONFIRMATION'
            ]);
        
        $response->assertStatus(422);
        
        // Verificar que o candidato ainda existe na base de dados
        $this->assertDatabaseHas('candidatos', [
            'id' => $candidato->id
        ]);
    }
}
