<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Criar tabela kanban_stages
        Schema::create('kanban_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('oportunidade_id')->nullable()->constrained('oportunidades')->onDelete('cascade');
            $table->timestamps();
            
            // Índices
            $table->index(['company_id', 'order']);
            $table->index(['oportunidade_id', 'order']);
        });
        
        // 2. Criar tabela kanban_stage_settings
        Schema::create('kanban_stage_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('kanban_stages')->onDelete('cascade');
            $table->string('color')->default('#6B7280');
            $table->boolean('email_enabled')->default(false);
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
            $table->timestamps();
        });
        
        // 3. Criar tabela kanban_transitions
        Schema::create('kanban_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidatura_id')->constrained('candidaturas')->onDelete('cascade');
            $table->foreignId('from_stage_id')->nullable()->constrained('kanban_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->constrained('kanban_stages')->onDelete('cascade');
            $table->text('note')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->longText('email_data')->nullable();
            $table->timestamps();
        });
        
        // 4. Criar tabela kanban_notes
        Schema::create('kanban_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidatura_id')->constrained('candidaturas')->onDelete('cascade');
            $table->foreignId('oportunidade_id')->constrained('oportunidades')->onDelete('cascade');
            $table->text('content');
            $table->foreignId('created_by')->constrained('companies')->onDelete('cascade');
            $table->timestamps();
        });
        
        // 5. Migrar dados de board_states para kanban_stages e kanban_stage_settings
        $boardStates = DB::table('board_states')->get();
        
        foreach ($boardStates as $state) {
            // Inserir na tabela kanban_stages
            $stageId = DB::table('kanban_stages')->insertGetId([
                'name' => $state->nome,
                'order' => $state->ordem,
                'is_default' => $state->is_default,
                'company_id' => $state->company_id,
                'oportunidade_id' => $state->oportunidade_id,
                'created_at' => $state->created_at,
                'updated_at' => $state->updated_at,
            ]);
            
            // Inserir na tabela kanban_stage_settings
            DB::table('kanban_stage_settings')->insert([
                'stage_id' => $stageId,
                'color' => $state->cor,
                'email_enabled' => $state->email_enabled,
                'email_subject' => $state->email_subject,
                'email_body' => $state->email_body,
                'created_at' => $state->created_at,
                'updated_at' => $state->updated_at,
            ]);
        }
        
        // 6. Migrar dados de candidatura_history para kanban_transitions
        $history = DB::table('candidatura_history')->get();
        
        foreach ($history as $entry) {
            // Mapear os ids de board_states para kanban_stages
            $fromStageId = null;
            if ($entry->previous_state_id) {
                $fromStageMapping = DB::table('board_states')
                    ->where('id', $entry->previous_state_id)
                    ->first();
                
                if ($fromStageMapping) {
                    $fromStage = DB::table('kanban_stages')
                        ->where('name', $fromStageMapping->nome)
                        ->where('order', $fromStageMapping->ordem)
                        ->where('is_default', $fromStageMapping->is_default)
                        ->first();
                    
                    if ($fromStage) {
                        $fromStageId = $fromStage->id;
                    }
                }
            }
            
            $toStageMapping = DB::table('board_states')
                ->where('id', $entry->board_state_id)
                ->first();
                
            if ($toStageMapping) {
                $toStage = DB::table('kanban_stages')
                    ->where('name', $toStageMapping->nome)
                    ->where('order', $toStageMapping->ordem)
                    ->where('is_default', $toStageMapping->is_default)
                    ->first();
                
                if ($toStage) {
                    DB::table('kanban_transitions')->insert([
                        'candidatura_id' => $entry->candidatura_id,
                        'from_stage_id' => $fromStageId,
                        'to_stage_id' => $toStage->id,
                        'note' => $entry->note,
                        'email_sent' => $entry->email_sent,
                        'email_data' => $entry->email_data,
                        'created_at' => $entry->created_at,
                        'updated_at' => $entry->updated_at,
                    ]);
                }
            }
        }
        
        // 7. Migrar notas privadas de candidaturas para kanban_notes
        $candidaturas = DB::table('candidaturas')
            ->whereNotNull('notas_privadas')
            ->select('id', 'oportunidade_id', 'notas_privadas')
            ->get();
        
        foreach ($candidaturas as $candidatura) {
            $oportunidade = DB::table('oportunidades')
                ->where('id', $candidatura->oportunidade_id)
                ->first();
                
            if ($oportunidade) {
                $companyId = $oportunidade->company_id ?: 1; // Use 1 como padrão se não houver company_id
                
                DB::table('kanban_notes')->insert([
                    'candidatura_id' => $candidatura->id,
                    'oportunidade_id' => $candidatura->oportunidade_id,
                    'content' => $candidatura->notas_privadas,
                    'created_by' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // 8. Adicionar campo stage_id na tabela candidaturas
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->foreignId('stage_id')->nullable()->after('board_state_id');
        });
        
        // 9. Migrar board_state_id para stage_id na tabela candidaturas
        $candidaturas = DB::table('candidaturas')
            ->whereNotNull('board_state_id')
            ->select('id', 'board_state_id')
            ->get();
            
        foreach ($candidaturas as $candidatura) {
            $boardState = DB::table('board_states')
                ->where('id', $candidatura->board_state_id)
                ->first();
                
            if ($boardState) {
                $stage = DB::table('kanban_stages')
                    ->where('name', $boardState->nome)
                    ->where('order', $boardState->ordem)
                    ->where('is_default', $boardState->is_default)
                    ->first();
                    
                if ($stage) {
                    DB::table('candidaturas')
                        ->where('id', $candidatura->id)
                        ->update(['stage_id' => $stage->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover campo stage_id da tabela candidaturas
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stage_id');
        });
        
        // Remover as novas tabelas na ordem correta
        Schema::dropIfExists('kanban_notes');
        Schema::dropIfExists('kanban_transitions');
        Schema::dropIfExists('kanban_stage_settings');
        Schema::dropIfExists('kanban_stages');
    }
};
