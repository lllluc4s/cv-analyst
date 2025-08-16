<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoardState extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cor',
        'ordem',
        'is_default',
        'company_id',
        'oportunidade_id',
        'email_enabled',
        'email_subject',
        'email_body'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'email_enabled' => 'boolean'
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
     * Candidaturas neste estado
     */
    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }

    /**
     * Escopo para estados padrão do sistema
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Escopo para estados de uma oportunidade específica
     */
    public function scopeForOportunidade($query, $oportunidadeId)
    {
        return $query->where(function($q) use ($oportunidadeId) {
            $q->where('oportunidade_id', $oportunidadeId)
              ->orWhere('is_default', true);
        });
    }

    /**
     * Escopo para estados de uma empresa específica
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhere('is_default', true);
        });
    }

    /**
     * Ordenação padrão por ordem
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordem');
    }
}
