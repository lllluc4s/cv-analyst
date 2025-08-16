<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidato>
 */
class CandidatoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->firstName(),
            'apelido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'telefone' => fake()->phoneNumber(),
            'skills' => ['PHP', 'Laravel', 'Vue.js'],
            'experiencia_profissional' => [
                [
                    'empresa' => fake()->company(),
                    'cargo' => 'Desenvolvedor',
                    'periodo' => '2020-2024',
                    'descricao' => fake()->text(100)
                ]
            ],
            'formacao_academica' => [
                [
                    'instituicao' => fake()->company(),
                    'curso' => 'Informática',
                    'periodo' => '2016-2020',
                    'tipo' => 'Licenciatura'
                ]
            ],
            'linkedin_url' => 'https://linkedin.com/in/' . fake()->userName(),
            'is_searchable' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the candidato should not be searchable.
     */
    public function notSearchable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_searchable' => false,
        ]);
    }
}
