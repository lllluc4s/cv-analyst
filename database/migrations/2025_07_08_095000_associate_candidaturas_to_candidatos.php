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
        // Associar candidaturas aos candidatos com base no email
        $candidatos = DB::table('candidatos')->select('id', 'email')->get();
        
        foreach ($candidatos as $candidato) {
            DB::table('candidaturas')
                ->where('email', $candidato->email)
                ->whereNull('candidato_id')
                ->update(['candidato_id' => $candidato->id]);
        }
        
        // Adicionar índice para melhorar performance de consultas
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover associações
        DB::table('candidaturas')
            ->update(['candidato_id' => null]);
        
        // Remover índice
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropIndex(['email']);
        });
    }
};
