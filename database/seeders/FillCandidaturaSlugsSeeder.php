<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidatura;
use Illuminate\Support\Str;

class FillCandidaturaSlugsSeeder extends Seeder
{
    public function run()
    {
        foreach (Candidatura::whereNull('slug')->get() as $c) {
            $c->slug = Str::slug($c->nome.'-'.$c->apelido.'-'.uniqid());
            $c->save();
        }
    }
}
