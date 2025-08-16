<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CompanyVerifyEmail;
use Illuminate\Support\Str;

class Company extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'password',
        'website',
        'sector',
        'logo_path',
        'logo_url',
        'provider',
        'provider_id',
        'email_verified_at',
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
        ];
    }

    /**
     * Boot method to automatically generate slugs
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
                
                // Garantir que o slug é único
                $originalSlug = $company->slug;
                $counter = 1;
                while (static::where('slug', $company->slug)->exists()) {
                    $company->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        static::updating(function ($company) {
            if ($company->isDirty('name') && empty($company->slug)) {
                $company->slug = Str::slug($company->name);
                
                // Garantir que o slug é único
                $originalSlug = $company->slug;
                $counter = 1;
                while (static::where('slug', $company->slug)->where('id', '!=', $company->id)->exists()) {
                    $company->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Relationship with opportunities
     */
    public function oportunidades()
    {
        return $this->hasMany(Oportunidade::class);
    }

    /**
     * Relationship with convites enviados
     */
    public function convitesEnviados()
    {
        return $this->hasMany(ConviteCandidato::class);
    }

    /**
     * Relationship with board states
     */
    public function boardStates()
    {
        return $this->hasMany(BoardState::class);
    }

    /**
     * Relationship with messages
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CompanyVerifyEmail);
    }
}
