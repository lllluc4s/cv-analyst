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
        Schema::table('board_states', function (Blueprint $table) {
            $table->foreignId('oportunidade_id')->nullable()->after('company_id')->constrained('oportunidades')->onDelete('cascade');
            
            // Índice para buscar estados por oportunidade
            $table->index(['oportunidade_id', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_states', function (Blueprint $table) {
            $table->dropForeign(['oportunidade_id']);
            $table->dropIndex(['oportunidade_id', 'ordem']);
            $table->dropColumn('oportunidade_id');
        });
    }
};
