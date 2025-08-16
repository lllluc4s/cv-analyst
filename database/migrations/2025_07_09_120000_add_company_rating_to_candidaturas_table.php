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
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->tinyInteger('company_rating')->nullable()->after('notas_privadas')
                  ->comment('Avaliação da empresa de 1 a 5 estrelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropColumn('company_rating');
        });
    }
};
