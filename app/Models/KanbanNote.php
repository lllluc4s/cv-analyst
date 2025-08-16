<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidatura_id',
        'oportunidade_id',
        'content',
        'created_by',
    ];

    /**
     * Relação com a candidatura
     */
    public function candidatura()
    {
        return $this->belongsTo(Candidatura::class);
    }

    /**
     * Relação com a oportunidade
     */
    public function oportunidade()
    {
        return $this->belongsTo(Oportunidade::class);
    }

    /**
     * Relação com a empresa que criou a nota
     */
    public function author()
    {
        return $this->belongsTo(Company::class, 'created_by');
    }
}
