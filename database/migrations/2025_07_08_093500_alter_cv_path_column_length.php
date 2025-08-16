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
        Schema::table('candidatos', function (Blueprint $table) {
            // Alterando o campo cv_path para TEXT para permitir strings maiores
            $table->text('cv_path')->nullable()->change();
            
            // Se houver outros campos que possam conter dados criptografados, também podemos alterá-los
            $table->text('linkedin_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatos', function (Blueprint $table) {
            // Voltar para o tipo string original
            $table->string('cv_path')->nullable()->change();
            $table->string('linkedin_url')->nullable()->change();
        });
    }
};
