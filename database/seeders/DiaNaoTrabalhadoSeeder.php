<?php

namespace Database\Seeders;

use App\Models\DiaNaoTrabalhado;
use App\Models\Colaborador;
use App\Models\Company;
use Illuminate\Database\Seeder;

class DiaNaoTrabalhadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se há colaboradores na base de dados
        if (Colaborador::count() === 0) {
            // Criar uma empresa de exemplo
            $company = Company::factory()->create([
                'name' => 'Empresa de Exemplo',
                'email' => 'empresa@exemplo.com',
                'slug' => 'empresa-exemplo',
            ]);

            // Criar alguns colaboradores
            $colaboradores = Colaborador::factory(3)->create([
                'company_id' => $company->id,
            ]);
        } else {
            // Usar colaboradores existentes
            $colaboradores = Colaborador::limit(3)->get();
        }

        // Criar solicitações de dias não trabalhados para cada colaborador
        foreach ($colaboradores as $colaborador) {
            // Criar uma solicitação pendente
            DiaNaoTrabalhado::factory()->create([
                'colaborador_id' => $colaborador->id,
                'data_ausencia' => now()->addDays(7),
                'motivo' => 'Consulta médica de rotina',
            ]);

            // Criar uma solicitação aprovada
            DiaNaoTrabalhado::factory()->aprovado()->create([
                'colaborador_id' => $colaborador->id,
                'data_ausencia' => now()->addDays(14),
                'motivo' => 'Assuntos pessoais',
            ]);

            // Criar uma solicitação recusada
            DiaNaoTrabalhado::factory()->recusado()->create([
                'colaborador_id' => $colaborador->id,
                'data_ausencia' => now()->addDays(21),
                'motivo' => 'Férias não autorizadas',
            ]);
        }

        $this->command->info('Dados de exemplo para Dias Não Trabalhados criados com sucesso!');
    }
}
