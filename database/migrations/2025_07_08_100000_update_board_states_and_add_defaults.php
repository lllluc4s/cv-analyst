<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar se já existem estados padrão
        $existingStates = DB::table('board_states')->where('is_default', true)->count();
        if ($existingStates > 0) {
            return; // Não adicionar estados se já existirem
        }
        
        // Criar estados padrão do Kanban
        $defaultStates = [
            [
                'nome' => 'Novo',
                'cor' => '#3B82F6', // blue-500
                'ordem' => 1,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Em Análise',
                'cor' => '#8B5CF6', // purple-500
                'ordem' => 2,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Entrevista',
                'cor' => '#10B981', // emerald-500
                'ordem' => 3,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Aprovado',
                'cor' => '#059669', // green-600
                'ordem' => 4,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Rejeitado',
                'cor' => '#EF4444', // red-500
                'ordem' => 5,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        DB::table('board_states')->insert($defaultStates);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover os estados padrão
        DB::table('board_states')->where('is_default', true)->delete();
    }
};
