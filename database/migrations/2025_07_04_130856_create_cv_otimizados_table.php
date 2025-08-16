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
        Schema::create('cv_otimizados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos')->onDelete('cascade');
            $table->string('titulo')->nullable();
            $table->text('resumo_pessoal')->nullable();
            $table->json('experiencias')->nullable(); // Array de experiências otimizadas
            $table->json('skills')->nullable(); // Skills otimizadas
            $table->json('formacao')->nullable(); // Formação otimizada
            $table->json('dados_pessoais')->nullable(); // Nome, contato, etc.
            $table->string('template_escolhido')->default('moderno'); // Template selecionado
            $table->text('cv_original_texto')->nullable(); // Texto extraído do CV original
            $table->string('cv_original_path')->nullable(); // Caminho do CV original
            $table->boolean('otimizado_por_ia')->default(false); // Se foi otimizado pela IA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_otimizados');
    }
};
