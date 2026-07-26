<?php

namespace Database\Seeders;

use App\Models\Contingent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ContingentSeeder extends Seeder
{
    public function run(): void
    {
        $kontingenRole = Role::where('name', 'kontingen')->first();

        $contingents = [
            [
                'name' => 'Dojo Karate Jakarta',
                'official_name' => 'Perguruan Karate Jakarta',
                'phone' => '081234567890',
                'address' => 'Jakarta Selatan',
            ],
            [
                'name' => 'Bandung Karate Club',
                'official_name' => 'Club Karate Bandung',
                'phone' => '081234567891',
                'address' => 'Bandung',
            ],
            [
                'name' => 'Surabaya Karate Association',
                'official_name' => 'Asosiasi Karate Surabaya',
                'phone' => '081234567892',
                'address' => 'Surabaya',
            ],
            [
                'name' => 'Semarang Karate Academy',
                'official_name' => 'Akademi Karate Semarang',
                'phone' => '081234567893',
                'address' => 'Semarang',
            ],
            [
                'name' => 'Yogyakarta Karate Federation',
                'official_name' => 'Federasi Karate Yogyakarta',
                'phone' => '081234567894',
                'address' => 'Yogyakarta',
            ],
            [
                'name' => 'Medan Karate Institute',
                'official_name' => 'Institut Karate Medan',
                'phone' => '081234567895',
                'address' => 'Medan',
            ],
        ];

        foreach ($contingents as $index => $data) {
            $userNumber = $index + 1;

            // Contingent #1 reuses the existing 'kontingen' demo account so the
            // well-known login (kontingen / password) stays tied to a real
            // contingent. The rest get their own kontingen2..kontingen6 accounts.
            $username = $userNumber === 1 ? 'kontingen' : 'kontingen' . $userNumber;
            $email = $userNumber === 1 ? 'kontingen@admin.com' : 'kontingen' . $userNumber . '@admin.com';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $data['official_name'],
                    'username' => $username,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if ($kontingenRole && !$user->hasRole('kontingen')) {
                $user->assignRole($kontingenRole);
            }

            Contingent::updateOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['user_id' => $user->id])
            );
        }

        $this->command->info('ContingentSeeder: 6 contingents created, each with its own kontingen user.');
    }
}
