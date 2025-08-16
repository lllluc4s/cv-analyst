<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColaboradorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'nome_completo' => $this->faker->name,
            'email_pessoal' => $this->faker->unique()->safeEmail,
            'numero_contribuinte' => $this->faker->numerify('#########'),
            'numero_seguranca_social' => $this->faker->numerify('###########'),
            'iban' => $this->faker->iban('PT'),
            'vencimento' => $this->faker->randomFloat(2, 600, 3000),
            'data_inicio_contrato' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'data_fim_contrato' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'posicao' => $this->faker->jobTitle,
            'departamento' => $this->faker->randomElement(['TI', 'RH', 'Vendas', 'Marketing', 'Financeiro']),
        ];
    }
}
