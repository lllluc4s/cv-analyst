<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptableFields;

class Candidatura extends Model
{
    use EncryptableFields;

    /**
     * Campos específicos para criptografia em candidaturas
     * NOTA: email é excluído para manter compatibilidade com busca/autenticação
     */
    protected $encryptable = [
        'nome',
        'apelido',
        'telefone',
        'cv_path',
        'linkedin_url',
        'skills_extraidas',
        'skills',
        'analysis_result',
    ];
    protected $fillable = [
        'oportunidade_id',
        'candidato_id',
        'user_id',
        'nome',
        'apelido',
        'email',
        'telefone',
        'cv_path',
        'linkedin_url',
        'rgpd_aceito',
        'consentimento_rgpd',
        'consentimento_emails',
        'consentimento_emails_data',
        'pode_ser_contactado',
        'skills_extraidas',
        'pontuacao_skills',
        'skills',
        'analysis_result',
        'score',
        'ranking',
        'slug',
        'board_state_id',
        'moved_to_state_at',
        'notas_privadas',
        'company_rating'
    ];

    protected $casts = [
        'rgpd_aceito' => 'boolean',
        'consentimento_rgpd' => 'boolean',
        'consentimento_emails' => 'boolean',
        'consentimento_emails_data' => 'datetime',
        'pode_ser_contactado' => 'boolean',
        'skills_extraidas' => 'array',
        'skills' => 'array',
        'analysis_result' => 'array',
        'moved_to_state_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($candidatura) {
            // Quando uma nova candidatura é criada, automaticamente coloca no primeiro estado
            if (!$candidatura->board_state_id) {
                $firstState = BoardState::default()->ordered()->first();
                if ($firstState) {
                    $candidatura->board_state_id = $firstState->id;
                    $candidatura->moved_to_state_at = now();
                }
            }
            
            // Gerar slug único
            if (empty($candidatura->slug)) {
                $slugBase = \Illuminate\Support\Str::slug($candidatura->nome.'-'.$candidatura->apelido.'-'.uniqid());
                $candidatura->slug = $slugBase;
            }
        });
    }

    public function oportunidade()
    {
        return $this->belongsTo(Oportunidade::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }
    
    /**
     * Relação com o estágio atual do Kanban
     */
    public function stage()
    {
        return $this->belongsTo(KanbanStage::class, 'stage_id');
    }
    
    /**
     * Histórico de transições no Kanban
     */
    public function transitions()
    {
        return $this->hasMany(KanbanTransition::class);
    }
    
    /**
     * Notas no Kanban
     */
    public function kanbanNotes()
    {
        return $this->hasMany(KanbanNote::class);
    }

    public function boardState()
    {
        return $this->belongsTo(BoardState::class);
    }

    public function history()
    {
        return $this->hasMany(CandidaturaHistory::class)->orderBy('created_at', 'desc');
    }

    public function colaborador()
    {
        return $this->hasOne(Colaborador::class);
    }

    public function isContratado(): bool
    {
        return $this->colaborador()->exists();
    }

    public function calcularPontuacaoSkills()
    {
        if (!$this->skills_extraidas || !$this->oportunidade || !$this->oportunidade->skills_desejadas) {
            return 0;
        }

        // Tratar skills_desejadas como array de objetos ou strings
        $skillsDesejadas = array_map(function($skill) {
            return strtolower(is_string($skill) ? $skill : $skill['nome']);
        }, $this->oportunidade->skills_desejadas);
        
        $skillsExtraidas = array_map('strtolower', $this->skills_extraidas);
        
        $matches = array_intersect($skillsDesejadas, $skillsExtraidas);
        
        return count($matches);
    }

    public function atualizarPontuacao()
    {
        $this->pontuacao_skills = $this->calcularPontuacaoSkills();
        $this->save();
    }
}
