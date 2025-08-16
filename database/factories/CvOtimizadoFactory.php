<?php

namespace Database\Factories;

use App\Models\CvOtimizado;
use App\Models\Candidato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CvOtimizado>
 */
class CvOtimizadoFactory extends Factory
{
    protected $model = CvOtimizado::class;

    public function definition(): array
    {
        return [
            'candidato_id' => Candidato::factory(),
            'titulo' => $this->faker->jobTitle(),
            'resumo_pessoal' => $this->faker->paragraph(3),
            'experiencias' => [
                [
                    'empresa' => $this->faker->company(),
                    'cargo' => $this->faker->jobTitle(),
                    'inicio' => $this->faker->date('Y-m'),
                    'fim' => null,
                    'descricao' => $this->faker->paragraph(),
                ],
            ],
            'skills' => $this->faker->randomElements([
                'Laravel','PHP','Vue','MySQL','Docker','AWS','Git','Tailwind','TDD','REST'
            ], 5),
            'formacao' => [[
                'curso' => 'Bacharelado em Ciência da Computação',
                'instituicao' => $this->faker->company() . ' University',
                'conclusao' => $this->faker->year(),
            ]],
            'dados_pessoais' => [
                'linkedin' => $this->faker->url(),
                'email' => $this->faker->safeEmail(),
                'telefone' => $this->faker->phoneNumber(),
                'localizacao' => $this->faker->city(),
            ],
            'template_escolhido' => 'padrao-1',
            'cv_original_texto' => $this->faker->paragraphs(5, true),
            'cv_original_path' => null,
            'otimizado_por_ia' => true,
        ];
    }
}
