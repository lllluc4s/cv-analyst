<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CvOtimizado extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidato_id',
        'titulo',
        'resumo_pessoal',
        'experiencias',
        'skills',
        'formacao',
        'dados_pessoais',
        'template_escolhido',
        'cv_original_texto',
        'cv_original_path',
        'otimizado_por_ia'
    ];

    protected $casts = [
        'experiencias' => 'array',
        'skills' => 'array',
        'formacao' => 'array',
        'dados_pessoais' => 'array',
        'otimizado_por_ia' => 'boolean'
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }
}
