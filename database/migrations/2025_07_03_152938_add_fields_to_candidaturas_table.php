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
            $table->string('linkedin_url')->nullable()->after('cv_path');
            $table->boolean('consentimento_rgpd')->default(true)->after('rgpd_aceito');
            $table->json('skills')->nullable()->after('skills_extraidas');
            $table->json('analysis_result')->nullable()->after('skills');
            $table->decimal('score', 5, 2)->nullable()->after('analysis_result');
            $table->integer('ranking')->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropColumn([
                'linkedin_url',
                'consentimento_rgpd',
                'skills',
                'analysis_result',
                'score',
                'ranking'
            ]);
        });
    }
};
