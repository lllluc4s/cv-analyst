<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiaNaoTrabalhado extends Model
{
    use HasFactory;

    protected $table = 'dias_nao_trabalhados';

    protected $fillable = [
        'colaborador_id',
        'data_ausencia',
        'motivo',
        'documento_path',
        'status',
        'observacoes_empresa',
        'aprovado_em',
        'aprovado_por'
    ];

    protected $casts = [
        'data_ausencia' => 'date',
        'aprovado_em' => 'datetime',
    ];

    const STATUS_PENDENTE = 'pendente';
    const STATUS_APROVADO = 'aprovado';
    const STATUS_RECUSADO = 'recusado';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_APROVADO => 'Aprovado',
            self::STATUS_RECUSADO => 'Recusado',
        ];
    }

    // Relacionamentos
    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function aprovadoPor()
    {
        return $this->belongsTo(Company::class, 'aprovado_por');
    }

    // Scopes
    public function scopePendentes($query)
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    public function scopeAprovados($query)
    {
        return $query->where('status', self::STATUS_APROVADO);
    }

    public function scopeRecusados($query)
    {
        return $query->where('status', self::STATUS_RECUSADO);
    }

    // Helpers
    public function isPendente()
    {
        return $this->status === self::STATUS_PENDENTE;
    }

    public function isAprovado()
    {
        return $this->status === self::STATUS_APROVADO;
    }

    public function isRecusado()
    {
        return $this->status === self::STATUS_RECUSADO;
    }

    public function getStatusLabelAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? 'Desconhecido';
    }

    public function aprovar($userId, $observacoes = null)
    {
        $this->update([
            'status' => self::STATUS_APROVADO,
            'aprovado_por' => $userId,
            'aprovado_em' => now(),
            'observacoes_empresa' => $observacoes,
        ]);
    }

    public function recusar($userId, $observacoes = null)
    {
        $this->update([
            'status' => self::STATUS_RECUSADO,
            'aprovado_por' => $userId,
            'aprovado_em' => now(),
            'observacoes_empresa' => $observacoes,
        ]);
    }
}
