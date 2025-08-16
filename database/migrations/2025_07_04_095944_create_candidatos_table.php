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
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('apelido');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telefone')->nullable();
            $table->string('foto_path')->nullable();
            $table->string('foto_url')->nullable();
            $table->json('skills')->nullable();
            $table->json('experiencia_profissional')->nullable();
            $table->json('formacao_academica')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatos');
    }
};
