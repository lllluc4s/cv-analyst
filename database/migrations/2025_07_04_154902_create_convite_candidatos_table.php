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
        Schema::create('convite_candidatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos')->onDelete('cascade');
            $table->foreignId('oportunidade_id')->constrained('oportunidades')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->text('mensagem_personalizada')->nullable();
            $table->string('token')->unique();
            $table->timestamp('enviado_em');
            $table->timestamp('visualizado_em')->nullable();
            $table->boolean('candidatou_se')->default(false);
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index(['candidato_id', 'oportunidade_id']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convite_candidatos');
    }
};
