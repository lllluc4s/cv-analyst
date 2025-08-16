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
        Schema::create('candidaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oportunidade_id')->constrained('oportunidades')->onDelete('cascade');
            $table->string('nome');
            $table->string('apelido');
            $table->string('email');
            $table->string('telefone');
            $table->string('cv_path');
            $table->string('linkedin')->nullable();
            $table->boolean('rgpd_aceito')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidaturas');
    }
};
