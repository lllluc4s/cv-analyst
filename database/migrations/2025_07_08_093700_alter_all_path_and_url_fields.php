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
        // Candidatos - já alteramos cv_path e linkedin_url
        Schema::table('candidatos', function (Blueprint $table) {
            $table->text('foto_path')->nullable()->change();
            $table->text('foto_url')->nullable()->change();
        });

        // CV Otimizados
        Schema::table('cv_otimizados', function (Blueprint $table) {
            $table->text('cv_original_path')->nullable()->change();
        });

        // Empresas
        Schema::table('companies', function (Blueprint $table) {
            $table->text('logo_path')->nullable()->change();
            $table->text('logo_url')->nullable()->change();
        });

        // Usuários
        Schema::table('users', function (Blueprint $table) {
            $table->text('profile_url')->nullable()->change();
        });

        // Alterando o campo linkedin_url na tabela candidaturas (se existir)
        if (Schema::hasColumn('candidaturas', 'linkedin_url')) {
            Schema::table('candidaturas', function (Blueprint $table) {
                $table->text('linkedin_url')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Candidatos
        Schema::table('candidatos', function (Blueprint $table) {
            $table->string('foto_path')->nullable()->change();
            $table->string('foto_url')->nullable()->change();
        });

        // CV Otimizados
        Schema::table('cv_otimizados', function (Blueprint $table) {
            $table->string('cv_original_path')->nullable()->change();
        });

        // Empresas
        Schema::table('companies', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->change();
            $table->string('logo_url')->nullable()->change();
        });

        // Usuários
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_url')->nullable()->change();
        });

        // Alterando o campo linkedin_url na tabela candidaturas (se existir)
        if (Schema::hasColumn('candidaturas', 'linkedin_url')) {
            Schema::table('candidaturas', function (Blueprint $table) {
                $table->string('linkedin_url')->nullable()->change();
            });
        }
    }
};
