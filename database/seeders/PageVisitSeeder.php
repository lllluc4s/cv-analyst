<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oportunidades = \App\Models\Oportunidade::all();
        
        if ($oportunidades->isEmpty()) {
            $this->command->info('Nenhuma oportunidade encontrada. Execute o seeder de oportunidades primeiro.');
            return;
        }

        // Dados de cidades com coordenadas reais
        $cities = [
            ['city' => 'Barcelona', 'country' => 'Espanha', 'region' => 'Catalunha', 'lat' => 41.3851, 'lng' => 2.1734],
            ['city' => 'Sevilla', 'country' => 'Espanha', 'region' => 'Andaluzia', 'lat' => 37.3891, 'lng' => -5.9845],
            ['city' => 'Rome', 'country' => 'Itália', 'region' => 'Lazio', 'lat' => 41.9028, 'lng' => 12.4964],
            ['city' => 'Aveiro', 'country' => 'Portugal', 'region' => 'Aveiro', 'lat' => 40.6443, 'lng' => -8.6455],
            ['city' => 'Frankfurt', 'country' => 'Alemanha', 'region' => 'Hesse', 'lat' => 50.1109, 'lng' => 8.6821],
            ['city' => 'Lisboa', 'country' => 'Portugal', 'region' => 'Lisboa', 'lat' => 38.7223, 'lng' => -9.1393],
            ['city' => 'Porto', 'country' => 'Portugal', 'region' => 'Porto', 'lat' => 41.1579, 'lng' => -8.6291],
            ['city' => 'Madrid', 'country' => 'Espanha', 'region' => 'Madrid', 'lat' => 40.4168, 'lng' => -3.7038],
            ['city' => 'Milan', 'country' => 'Itália', 'region' => 'Lombardia', 'lat' => 45.4642, 'lng' => 9.1900],
            ['city' => 'Berlin', 'country' => 'Alemanha', 'region' => 'Berlin', 'lat' => 52.5200, 'lng' => 13.4050],
        ];

        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
        $platforms = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];

        foreach ($oportunidades as $oportunidade) {
            // Gerar entre 80-120 visitas para cada oportunidade
            $numVisitas = rand(80, 120);
            
            for ($i = 0; $i < $numVisitas; $i++) {
                $city = $cities[array_rand($cities)];
                
                \App\Models\PageVisit::create([
                    'oportunidade_id' => $oportunidade->id,
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (compatible; Bot)',
                    'browser' => $browsers[array_rand($browsers)],
                    'platform' => $platforms[array_rand($platforms)],
                    'country' => $city['country'],
                    'city' => $city['city'],
                    'region' => $city['region'],
                    'latitude' => $city['lat'],
                    'longitude' => $city['lng'],
                    'visited_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59))
                ]);
            }
        }
        
        $this->command->info('Page visits seedados com sucesso!');
    }
}
