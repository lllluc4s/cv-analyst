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
        Schema::create('social_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oportunidade_id')->constrained('oportunidades')->onDelete('cascade');
            $table->string('platform'); // facebook, twitter, linkedin, whatsapp, copy_link
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->timestamp('shared_at');
            $table->timestamps();
            
            $table->index(['oportunidade_id', 'platform']);
            $table->index('shared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_shares');
    }
};
