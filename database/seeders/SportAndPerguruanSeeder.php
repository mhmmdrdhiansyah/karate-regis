<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\Perguruan;
use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportAndPerguruanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cabor Karate
        $karate = Sport::firstOrCreate(
            ['code' => 'karate'],
            [
                'name' => 'Karate',
                'description' => 'Cabang Olahraga Beladiri Karate',
                'is_active' => true,
            ]
        );

        // 2. Cabor Renang
        $renang = Sport::firstOrCreate(
            ['code' => 'renang'],
            [
                'name' => 'Renang',
                'description' => 'Cabang Olahraga Renang',
                'is_active' => true,
            ]
        );

        // 3. Daftar Perguruan Karate
        $karatePerguruan = [
            'ASKI', 'BUDOKAI', 'BKC', 'BLACK PANTHER', 'FUNAKOSHI', 
            'GABDIKA', 'GOJUKAI', 'GOJU ASS', 'GOKASI', 'INKADO', 
            'INKAI', 'INKANAS', 'KALA HITAM', 'KEI SHIN KAN', 'KKNSI', 
            'KKI', 'KYOKUSHINKAI', 'LEMKARI', 'SHOKAIDO', 'SHOTOKAI', 
            'PORBIKAWA', 'SHINDOKA', 'SHIROITE', 'TAKO', 'WADOKAI',
        ];

        foreach ($karatePerguruan as $name) {
            Perguruan::firstOrCreate(
                ['sport_id' => $karate->id, 'name' => $name],
                ['code' => strtolower(str_replace(' ', '_', $name)), 'is_active' => true]
            );
        }

        // 4. Daftar Perguruan Renang
        $renangPerguruan = [
            'Akuatik Indonesia',
        ];

        foreach ($renangPerguruan as $name) {
            Perguruan::firstOrCreate(
                ['sport_id' => $renang->id, 'name' => $name],
                ['code' => strtolower(str_replace(' ', '_', $name)), 'is_active' => true]
            );
        }

        // 5. Otomatisasi data atlet eksisting: Set seluruh atlet yang belum punya sport_id ke Cabor Karate
        Participant::whereNull('sport_id')->update(['sport_id' => $karate->id]);
    }
}
