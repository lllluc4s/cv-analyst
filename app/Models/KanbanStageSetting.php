<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanStageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id',
        'color',
        'email_enabled',
        'email_subject',
        'email_body',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
    ];

    /**
     * Relação com o estágio
     */
    public function stage()
    {
        return $this->belongsTo(KanbanStage::class, 'stage_id');
    }
}
