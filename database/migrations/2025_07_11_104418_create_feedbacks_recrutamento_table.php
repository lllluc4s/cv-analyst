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
        Schema::create('feedbacks_recrutamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('colaborador_id');
            $table->unsignedBigInteger('candidatura_id');
            $table->unsignedBigInteger('oportunidade_id');
            $table->string('token', 64)->unique(); // Token para acesso ao formulário
            $table->integer('avaliacao_processo')->nullable(); // 1 a 5 estrelas
            $table->text('gostou_mais')->nullable();
            $table->text('poderia_melhorar')->nullable();
            $table->timestamp('enviado_em')->nullable(); // Quando foi enviado o email
            $table->timestamp('respondido_em')->nullable(); // Quando o candidato respondeu
            $table->timestamps();

            $table->foreign('colaborador_id')->references('id')->on('colaboradores')->onDelete('cascade');
            $table->foreign('candidatura_id')->references('id')->on('candidaturas')->onDelete('cascade');
            $table->foreign('oportunidade_id')->references('id')->on('oportunidades')->onDelete('cascade');
            
            $table->index(['oportunidade_id', 'enviado_em']);
            $table->index(['token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks_recrutamento');
    }
};
