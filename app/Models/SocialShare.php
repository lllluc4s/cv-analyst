<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialShare extends Model
{
    protected $fillable = [
        'oportunidade_id',
        'platform',
        'ip_address',
        'user_agent',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'shared_at'
    ];

    protected $casts = [
        'shared_at' => 'datetime'
    ];

    public function oportunidade(): BelongsTo
    {
        return $this->belongsTo(Oportunidade::class);
    }
}
