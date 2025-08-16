<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dias_nao_trabalhados', function (Blueprint $table) {
            // Dropar a constraint existente
            $table->dropForeign(['aprovado_por']);
            
            // Recriar a constraint apontando para companies
            $table->foreign('aprovado_por')->references('id')->on('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dias_nao_trabalhados', function (Blueprint $table) {
            // Reverter para a constraint original
            $table->dropForeign(['aprovado_por']);
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
        });
    }
};
