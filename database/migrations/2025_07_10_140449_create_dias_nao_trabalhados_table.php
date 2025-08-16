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
        Schema::create('dias_nao_trabalhados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores')->onDelete('cascade');
            $table->date('data_ausencia');
            $table->text('motivo');
            $table->string('documento_path')->nullable();
            $table->enum('status', ['pendente', 'aprovado', 'recusado'])->default('pendente');
            $table->text('observacoes_empresa')->nullable(); // Para feedback da empresa
            $table->timestamp('aprovado_em')->nullable();
            $table->foreignId('aprovado_por')->nullable()->constrained('users')->onDelete('set null'); // User da empresa que aprovou
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index(['colaborador_id', 'status']);
            $table->index('data_ausencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias_nao_trabalhados');
    }
};
