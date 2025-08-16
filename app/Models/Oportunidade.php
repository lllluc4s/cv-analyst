<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Oportunidade extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
        'skills_desejadas',
        'localizacao',
        'slug',
        'ativa',
        'publica',
        'company_id'
    ];

    protected $casts = [
        'skills_desejadas' => 'array',
        'ativa' => 'boolean',
        'publica' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($oportunidade) {
            if (empty($oportunidade->slug)) {
                $oportunidade->slug = Str::slug($oportunidade->titulo);
            }
        });

        static::updating(function ($oportunidade) {
            if ($oportunidade->isDirty('titulo')) {
                $oportunidade->slug = Str::slug($oportunidade->titulo);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function socialShares()
    {
        return $this->hasMany(SocialShare::class);
    }

    public function convites()
    {
        return $this->hasMany(ConviteCandidato::class);
    }
}
