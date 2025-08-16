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
        if (!Schema::hasTable('kanban_stage_settings')) {
            Schema::create('kanban_stage_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stage_id')->constrained('kanban_stages')->onDelete('cascade');
                $table->string('color')->default('#6B7280');
                $table->boolean('email_enabled')->default(false);
                $table->string('email_subject')->nullable();
                $table->text('email_body')->nullable();
                $table->timestamps();
            });
        } else {
            // Garantir que as colunas existam
            Schema::table('kanban_stage_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('kanban_stage_settings', 'email_enabled')) {
                    $table->boolean('email_enabled')->default(false);
                }
                if (!Schema::hasColumn('kanban_stage_settings', 'email_subject')) {
                    $table->string('email_subject')->nullable();
                }
                if (!Schema::hasColumn('kanban_stage_settings', 'email_body')) {
                    $table->text('email_body')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
