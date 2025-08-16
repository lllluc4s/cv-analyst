<?php

namespace Database\Factories;

use App\Models\Colaborador;
use App\Models\DiaNaoTrabalhado;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiaNaoTrabalhadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'colaborador_id' => Colaborador::factory(),
            'data_ausencia' => $this->faker->dateTimeBetween('now', '+30 days'),
            'motivo' => $this->faker->randomElement([
                'Consulta médica',
                'Assuntos pessoais',
                'Licença de paternidade',
                'Licença de maternidade',
                'Formação profissional',
                'Doença',
                'Assuntos familiares'
            ]),
            'status' => DiaNaoTrabalhado::STATUS_PENDENTE,
            'documento_path' => $this->faker->optional()->filePath(),
            'observacoes_empresa' => $this->faker->optional()->sentence,
        ];
    }

    public function aprovado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DiaNaoTrabalhado::STATUS_APROVADO,
            'aprovado_em' => now(),
            'observacoes_empresa' => 'Aprovado pela gestão',
        ]);
    }

    public function recusado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DiaNaoTrabalhado::STATUS_RECUSADO,
            'aprovado_em' => now(),
            'observacoes_empresa' => 'Recusado pela gestão',
        ]);
    }
}
