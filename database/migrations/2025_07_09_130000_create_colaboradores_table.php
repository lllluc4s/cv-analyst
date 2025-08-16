<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('candidatura_id')->constrained('candidaturas')->onDelete('cascade');
            
            // Dados do candidato (copiados)
            $table->string('nome_completo');
            $table->string('email_pessoal');
            
            // Dados do colaborador (editáveis)
            $table->string('numero_contribuinte')->nullable();
            $table->string('numero_seguranca_social')->nullable();
            $table->string('iban')->nullable();
            $table->decimal('vencimento', 8, 2)->nullable();
            
            // Datas do contrato
            $table->date('data_inicio_contrato');
            $table->date('data_fim_contrato')->nullable(); // null = sem termo
            
            $table->timestamps();
            
            // Índices
            $table->index(['company_id', 'data_inicio_contrato']);
            $table->unique(['company_id', 'candidatura_id']); // Um candidato só pode ser contratado uma vez por empresa
        });
    }

    public function down()
    {
        Schema::dropIfExists('colaboradores');
    }
};
