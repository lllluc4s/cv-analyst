<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackRecrutamento extends Model
{
    use HasFactory;

    protected $table = 'feedbacks_recrutamento';

    protected $fillable = [
        'colaborador_id',
        'candidatura_id',
        'oportunidade_id',
        'token',
        'avaliacao_processo',
        'gostou_mais',
        'poderia_melhorar',
        'enviado_em',
        'respondido_em'
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
        'respondido_em' => 'datetime',
        'avaliacao_processo' => 'integer'
    ];

    /**
     * Relação com colaborador
     */
    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class);
    }

    /**
     * Relação com candidatura
     */
    public function candidatura()
    {
        return $this->belongsTo(Candidatura::class);
    }

    /**
     * Relação com oportunidade
     */
    public function oportunidade()
    {
        return $this->belongsTo(Oportunidade::class);
    }

    /**
     * Verifica se o feedback já foi respondido
     */
    public function isRespondido(): bool
    {
        return !is_null($this->respondido_em);
    }

    /**
     * Gerar token único para o feedback
     */
    public static function gerarToken(): string
    {
        do {
            $token = \Illuminate\Support\Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }
}
