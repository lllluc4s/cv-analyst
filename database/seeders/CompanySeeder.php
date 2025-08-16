<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Empresa de exemplo 1
        Company::create([
            'name' => 'TechCorp Portugal',
            'email' => 'empresa@techcorp.pt',
            'password' => Hash::make('password123'),
            'website' => 'https://techcorp.pt',
            'sector' => 'Tecnologia',
            'email_verified_at' => now(),
        ]);
        
        // Empresa de exemplo 2
        Company::create([
            'name' => 'Inovação Digital Lda',
            'email' => 'rh@inovacaodigital.pt',
            'password' => Hash::make('password123'),
            'website' => 'https://inovacaodigital.pt',
            'sector' => 'Desenvolvimento de Software',
            'email_verified_at' => now(),
        ]);
        
        // Empresa de exemplo 3
        Company::create([
            'name' => 'StartupLisboa',
            'email' => 'jobs@startuplisboa.com',
            'password' => Hash::make('password123'),
            'website' => 'https://startuplisboa.com',
            'sector' => 'Startup / Fintech',
            'email_verified_at' => now(),
        ]);
    }
}
