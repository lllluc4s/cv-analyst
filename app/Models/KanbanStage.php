<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'is_default',
        'company_id',
        'oportunidade_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relação com a empresa (caso seja um estado personalizado da empresa)
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relação com a oportunidade (caso seja um estado personalizado da oportunidade)
     */
    public function oportunidade()
    {
        return $this->belongsTo(Oportunidade::class);
    }

    /**
     * Candidaturas neste estágio
     */
    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class, 'stage_id');
    }

    /**
     * Configurações do estágio
     */
    public function settings()
    {
        return $this->hasOne(KanbanStageSetting::class, 'stage_id');
    }

    /**
     * Transições de candidaturas que vieram para este estágio
     */
    public function incomingTransitions()
    {
        return $this->hasMany(KanbanTransition::class, 'to_stage_id');
    }

    /**
     * Transições de candidaturas que saíram deste estágio
     */
    public function outgoingTransitions()
    {
        return $this->hasMany(KanbanTransition::class, 'from_stage_id');
    }

    /**
     * Scope para ordenar por ordem
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope para obter apenas estados padrão
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope para obter estados de uma oportunidade específica
     */
    public function scopeForOportunidade($query, $oportunidadeId)
    {
        return $query->where('oportunidade_id', $oportunidadeId);
    }

    /**
     * Scope para obter estados de uma empresa específica
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
