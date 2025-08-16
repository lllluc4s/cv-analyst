<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageVisit extends Model
{
    protected $fillable = [
        'oportunidade_id',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'region',
        'latitude',
        'longitude',
        'browser',
        'platform',
        'visited_at'
    ];

    protected $casts = [
        'visited_at' => 'datetime'
    ];

    public function oportunidade(): BelongsTo
    {
        return $this->belongsTo(Oportunidade::class);
    }
}
