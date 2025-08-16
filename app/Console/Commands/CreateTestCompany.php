<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class CreateTestCompany extends Command
{
    protected $signature = 'test:create-company';
    protected $description = 'Create a test company for login testing';

    public function handle()
    {
        $email = 'lucas4394@gmail.com';
        $password = 'password123';
        
        $company = Company::where('email', $email)->first();
        
        if (!$company) {
            $company = Company::create([
                'name' => 'Lucas Test Company',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now()
            ]);
            $this->info('Nova empresa criada!');
        } else {
            $company->password = Hash::make($password);
            $company->email_verified_at = now();
            $company->save();
            $this->info('Senha da empresa atualizada!');
        }
        
        $this->info("Email: {$email}");
        $this->info("Senha: {$password}");
        $this->info("ID: {$company->id}");
        
        return 0;
    }
}
