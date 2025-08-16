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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telefone',
                'foto',
                'skills',
                'experiencia_profissional',
                'formacao_academica',
                'cv_original_path',
                'perfil_completo_em'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefone')->nullable();
            $table->string('foto')->nullable();
            $table->json('skills')->nullable();
            $table->json('experiencia_profissional')->nullable();
            $table->json('formacao_academica')->nullable();
            $table->string('cv_original_path')->nullable();
            $table->timestamp('perfil_completo_em')->nullable();
        });
    }
};
