<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidaturaHistory extends Model
{
    protected $table = 'candidatura_history';

    protected $fillable = [
        'candidatura_id',
        'board_state_id',
        'previous_state_id',
        'note',
        'email_sent',
        'email_data'
    ];

    protected $casts = [
        'email_sent' => 'boolean',
        'email_data' => 'array'
    ];

    public function candidatura()
    {
        return $this->belongsTo(Candidatura::class);
    }

    public function boardState()
    {
        return $this->belongsTo(BoardState::class);
    }

    public function previousState()
    {
        return $this->belongsTo(BoardState::class, 'previous_state_id');
    }
}
