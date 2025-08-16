<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\EncryptableFields;
use Illuminate\Support\Facades\Storage;

class Candidato extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, EncryptableFields;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'apelido',
        'slug',
        'email',
        'password',
        'telefone',
        'data_nascimento',
        'foto_path',
        'foto_url',
        'profile_photo',
        'skills',
        'experiencia_profissional',
        'formacao_academica',
        'cv_path',
        'linkedin_url',
        'provider',
        'provider_id',
        'email_verified_at',
        'is_searchable',
        'consentimento_emails',
        'consentimento_emails_data',
        'pode_ser_contactado',
        'last_seen_at',
        'country',
        'city',
        'region',
        'latitude',
        'longitude',
        'is_online',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data_nascimento' => 'date',
            'skills' => 'array',
            'experiencia_profissional' => 'array',
            'formacao_academica' => 'array',
            'is_searchable' => 'boolean',
            'consentimento_emails' => 'boolean',
            'consentimento_emails_data' => 'datetime',
            'pode_ser_contactado' => 'boolean',
            'last_seen_at' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_online' => 'boolean',
        ];
    }

    /**
     * Generate slug when creating a new candidato
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($candidato) {
            if (empty($candidato->slug)) {
                $baseSlug = Str::slug($candidato->nome . '-' . $candidato->apelido);
                $slug = $baseSlug;
                $counter = 1;
                
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $candidato->slug = $slug;
            }
        });
    }

    /**
     * Get the candidaturas for the candidato.
     */
    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }

    /**
     * Get the CVs otimizados for the candidato.
     */
    public function cvsOtimizados()
    {
        return $this->hasMany(CvOtimizado::class);
    }

    /**
     * Relationship with messages
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest CV otimizado for the candidato.
     */
    public function cvOtimizadoAtual()
    {
        return $this->hasOne(CvOtimizado::class)->latest();
    }

    /**
     * Get the full name attribute.
     */
    public function getNomeCompletoAttribute()
    {
        return $this->nome . ' ' . $this->apelido;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the convites recebidos for the candidato.
     */
    public function convitesRecebidos()
    {
        return $this->hasMany(ConviteCandidato::class);
    }

    /**
     * Scope para candidatos pesquisáveis
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Marcar candidato como não pesquisável
     */
    public function markAsNotSearchable()
    {
        $this->update(['is_searchable' => false]);
    }

    /**
     * Marcar candidato como pesquisável
     */
    public function markAsSearchable()
    {
        $this->update(['is_searchable' => true]);
    }

    /**
     * Verificar se o candidato é colaborador (foi contratado por alguma empresa)
     */
    public function isColaborador()
    {
        return $this->candidaturas()->whereHas('colaborador')->exists();
    }

    /**
     * Obter todos os vínculos de colaborador deste candidato
     */
    public function colaboradores()
    {
        return $this->hasManyThrough(
            Colaborador::class,
            Candidatura::class,
            'candidato_id', // Foreign key na tabela candidaturas
            'candidatura_id', // Foreign key na tabela colaboradores
            'id', // Local key na tabela candidatos
            'id' // Local key na tabela candidaturas
        );
    }

    /**
     * Obter colaboradores ativos (contratos em vigor)
     */
    public function colaboradoresAtivos()
    {
        return $this->colaboradores()->whereDate('data_inicio_contrato', '<=', now())
            ->where(function($query) {
                $query->whereNull('data_fim_contrato')
                    ->orWhereDate('data_fim_contrato', '>=', now());
            });
    }

    /**
     * Excluir permanentemente a conta e todos os dados relacionados
     */
    public function deleteAccountPermanently()
    {
        // Excluir arquivo CV se existir
        if ($this->cv_path && Storage::exists($this->cv_path)) {
            Storage::delete($this->cv_path);
        }

        // Excluir foto se existir
        if ($this->foto_path && Storage::exists($this->foto_path)) {
            Storage::delete($this->foto_path);
        }

        // Excluir candidaturas relacionadas
        $this->candidaturas()->delete();

        // Excluir CVs otimizados relacionados
        $this->cvsOtimizados()->each(function ($cv) {
            if ($cv->path && Storage::exists($cv->path)) {
                Storage::delete($cv->path);
            }
            $cv->delete();
        });

        // Excluir convites relacionados (se existir a relação)
        if (method_exists($this, 'convitesRecebidos')) {
            $this->convitesRecebidos()->delete();
        }

        // Excluir o candidato permanentemente
        $this->forceDelete();
    }

    /**
     * Marcar candidato como online com localização
     */
    public function markOnline($latitude = null, $longitude = null, $country = null, $city = null, $region = null)
    {
        $data = [
            'is_online' => true,
            'last_seen_at' => now(),
        ];

        if ($latitude && $longitude) {
            $data['latitude'] = $latitude;
            $data['longitude'] = $longitude;
        }

        if ($country) $data['country'] = $country;
        if ($city) $data['city'] = $city;
        if ($region) $data['region'] = $region;

        $this->update($data);
    }

    /**
     * Marcar candidato como offline
     */
    public function markOffline()
    {
        $this->update([
            'is_online' => false,
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Verificar se candidato está online (últimos 5 minutos)
     */
    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }

        return $this->last_seen_at->diffInMinutes(now()) <= 5;
    }

    /**
     * Scope para candidatos online
     */
    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(5));
    }

    /**
     * Obter avatar URL (profile_photo tem prioridade sobre outros avatares)
     */
    public function getAvatarUrlAttribute()
    {
        // Prioridade 1: Profile photo (upload do usuário)
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }

        // Prioridade 2: Avatar URL personalizado
        if ($this->attributes['avatar_url']) {
            return $this->attributes['avatar_url'];
        }

        // Prioridade 3: Foto URL (legacy)
        if ($this->foto_url) {
            return $this->foto_url;
        }

        // Fallback: Gerar iniciais do nome
        $initials = strtoupper(substr($this->nome, 0, 1) . substr($this->apelido, 0, 1));
        return "https://ui-avatars.com/api/?name={$initials}&background=0D8ABC&color=fff&size=100&rounded=true";
    }
}
