<?php

namespace Database\Seeders;

use App\Models\BoardState;
use Illuminate\Database\Seeder;

class ContratadoStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar estado "Contratado" se não existir
        BoardState::firstOrCreate([
            'nome' => 'Contratado'
        ], [
            'nome' => 'Contratado',
            'cor' => '#10B981', // Verde
            'ordem' => 999,
            'is_default' => false,
            'email_enabled' => true,
            'email_subject' => 'Parabéns! Você foi contratado!',
            'email_body' => 'Temos o prazer de informar que você foi selecionado para a posição. Em breve entraremos em contato para os próximos passos.'
        ]);

        $this->command->info('Estado "Contratado" criado/atualizado com sucesso!');
    }
}
