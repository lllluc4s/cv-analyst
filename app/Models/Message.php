<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'candidato_id',
        'sender_type',
        'sender_id',
        'content',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }

    public function sender()
    {
        if ($this->sender_type === 'company') {
            return $this->belongsTo(Company::class, 'sender_id');
        }
        
        return $this->belongsTo(Candidato::class, 'sender_id');
    }

    public function scopeConversation($query, $companyId, $candidatoId)
    {
        return $query->where('company_id', $companyId)
                     ->where('candidato_id', $candidatoId);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
}
