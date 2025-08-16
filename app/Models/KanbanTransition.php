<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidatura_id',
        'from_stage_id',
        'to_stage_id',
        'note',
        'email_sent',
        'email_data',
    ];

    protected $casts = [
        'email_sent' => 'boolean',
        'email_data' => 'array',
    ];

    /**
     * Relação com a candidatura
     */
    public function candidatura()
    {
        return $this->belongsTo(Candidatura::class);
    }

    /**
     * Relação com o estágio de origem
     */
    public function fromStage()
    {
        return $this->belongsTo(KanbanStage::class, 'from_stage_id');
    }

    /**
     * Relação com o estágio de destino
     */
    public function toStage()
    {
        return $this->belongsTo(KanbanStage::class, 'to_stage_id');
    }
}
