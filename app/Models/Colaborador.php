<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colaborador extends Model
{
    use HasFactory;

    protected $table = 'colaboradores';

    protected $fillable = [
        'company_id',
        'candidatura_id',
        'nome_completo',
        'email_pessoal',
        'numero_contribuinte',
        'numero_seguranca_social',
        'iban',
        'vencimento',
        'data_inicio_contrato',
        'data_fim_contrato',
        'posicao',
        'departamento',
        'data_nascimento',
        'foto_url',
    ];

    protected $casts = [
        'data_inicio_contrato' => 'date',
        'data_fim_contrato' => 'date',
        'data_nascimento' => 'date',
        'vencimento' => 'decimal:2',
    ];

    protected $appends = [
        'salario',
        'data_contratacao'
    ];

    // Relacionamentos
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function candidatura()
    {
        return $this->belongsTo(Candidatura::class);
    }

    public function diasNaoTrabalhados()
    {
        return $this->hasMany(DiaNaoTrabalhado::class);
    }

    // Verifica se o contrato tem termo
    public function isContratoSemTermo(): bool
    {
        return is_null($this->data_fim_contrato);
    }

    // Verifica se o contrato está ativo
    public function isContratoAtivo(): bool
    {
        $hoje = now()->toDateString();
        
        // Se ainda não começou
        if ($this->data_inicio_contrato > $hoje) {
            return false;
        }
        
        // Se tem data fim e já passou
        if ($this->data_fim_contrato && $this->data_fim_contrato < $hoje) {
            return false;
        }
        
        return true;
    }

    // Accessors para compatibilidade com frontend
    public function getSalarioAttribute()
    {
        return $this->vencimento;
    }

    public function getDataContratacaoAttribute()
    {
        return $this->data_inicio_contrato;
    }
}
