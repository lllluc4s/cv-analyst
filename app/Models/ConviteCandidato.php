<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConviteCandidato extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidato_id',
        'oportunidade_id', 
        'company_id',
        'mensagem_personalizada',
        'token',
        'enviado_em',
        'visualizado_em',
        'candidatou_se'
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
        'visualizado_em' => 'datetime',
        'candidatou_se' => 'boolean'
    ];

    /**
     * Relacionamento com candidato
     */
    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }

    /**
     * Relacionamento com oportunidade
     */
    public function oportunidade()
    {
        return $this->belongsTo(Oportunidade::class);
    }

    /**
     * Relacionamento com empresa
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
