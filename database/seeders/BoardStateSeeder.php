<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BoardState;

class BoardStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultStates = [
            [
                'nome' => 'Recebido',
                'cor' => '#3B82F6', // Azul
                'ordem' => 1,
                'is_default' => true,
                'company_id' => null
            ],
            [
                'nome' => 'Em análise',
                'cor' => '#F59E0B', // Amarelo
                'ordem' => 2,
                'is_default' => true,
                'company_id' => null
            ],
            [
                'nome' => 'Entrevista',
                'cor' => '#8B5CF6', // Roxo
                'ordem' => 3,
                'is_default' => true,
                'company_id' => null
            ],
            [
                'nome' => 'Contratado',
                'cor' => '#10B981', // Verde
                'ordem' => 4,
                'is_default' => true,
                'company_id' => null
            ],
            [
                'nome' => 'Rejeitado',
                'cor' => '#EF4444', // Vermelho
                'ordem' => 5,
                'is_default' => true,
                'company_id' => null
            ]
        ];

        foreach ($defaultStates as $state) {
            BoardState::updateOrCreate(
                ['nome' => $state['nome'], 'is_default' => true],
                $state
            );
        }
    }
}
