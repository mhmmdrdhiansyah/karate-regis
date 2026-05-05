<?php

namespace Database\Seeders;

use App\Models\Contingent;
use App\Models\Participant;
use App\Enums\ParticipantType;
use App\Enums\ParticipantGender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $contingent = Contingent::first();

        if (!$contingent) {
            $this->command->warn('No contingent found. Please run RolesAndPermissionsSeeder first.');
            return;
        }

        $athletes = [
            // JUNIOR (2009-2010)
            ['name' => 'Ari Pratama', 'gender' => 'M', 'birth_date' => '2009-03-12', 'nik' => '3201010102010001', 'photo' => 'assets/media/avatars/150-1.jpg'],
            ['name' => 'Bagas Saputra', 'gender' => 'M', 'birth_date' => '2010-07-21', 'nik' => '3201010102010002', 'photo' => 'assets/media/avatars/150-2.jpg'],
            ['name' => 'Cahya Nugroho', 'gender' => 'M', 'birth_date' => '2009-11-08', 'nik' => '3201010102010003', 'photo' => 'assets/media/avatars/150-3.jpg'],
            ['name' => 'Dinda Permata', 'gender' => 'F', 'birth_date' => '2009-02-14', 'nik' => '3201010102010004', 'photo' => 'assets/media/avatars/150-4.jpg'],
            ['name' => 'Eka Lestari', 'gender' => 'F', 'birth_date' => '2010-09-05', 'nik' => '3201010102010005', 'photo' => 'assets/media/avatars/150-5.jpg'],
            ['name' => 'Fina Putri', 'gender' => 'F', 'birth_date' => '2009-06-30', 'nik' => '3201010102010006', 'photo' => 'assets/media/avatars/150-6.jpg'],

            // U21 (2006-2008)
            ['name' => 'Galih Santosa', 'gender' => 'M', 'birth_date' => '2006-01-19', 'nik' => '3201010102010007', 'photo' => 'assets/media/avatars/150-7.jpg'],
            ['name' => 'Hadi Setiawan', 'gender' => 'M', 'birth_date' => '2007-05-24', 'nik' => '3201010102010008', 'photo' => 'assets/media/avatars/150-8.jpg'],
            ['name' => 'Irfan Maulana', 'gender' => 'M', 'birth_date' => '2008-10-03', 'nik' => '3201010102010009', 'photo' => 'assets/media/avatars/150-9.jpg'],
            ['name' => 'Jihan Aulia', 'gender' => 'F', 'birth_date' => '2006-03-10', 'nik' => '3201010102010010', 'photo' => 'assets/media/avatars/150-10.jpg'],
            ['name' => 'Kartika Sari', 'gender' => 'F', 'birth_date' => '2007-12-28', 'nik' => '3201010102010011', 'photo' => 'assets/media/avatars/150-11.jpg'],
            ['name' => 'Laras Wulandari', 'gender' => 'F', 'birth_date' => '2008-08-16', 'nik' => '3201010102010012', 'photo' => 'assets/media/avatars/150-12.jpg'],

            // DEWASA (1996-2005)
            ['name' => 'Miko Ardiansyah', 'gender' => 'M', 'birth_date' => '1999-04-17', 'nik' => '3201010102010013', 'photo' => 'assets/media/avatars/150-13.jpg'],
            ['name' => 'Nanda Prakoso', 'gender' => 'M', 'birth_date' => '2001-09-09', 'nik' => '3201010102010014', 'photo' => 'assets/media/avatars/150-14.jpg'],
            ['name' => 'Oki Ramadhan', 'gender' => 'M', 'birth_date' => '2004-12-02', 'nik' => '3201010102010015', 'photo' => 'assets/media/avatars/150-15.jpg'],
            ['name' => 'Putri Larasati', 'gender' => 'F', 'birth_date' => '1998-06-06', 'nik' => '3201010102010016', 'photo' => 'assets/media/avatars/150-16.jpg'],
            ['name' => 'Qori Azzahra', 'gender' => 'F', 'birth_date' => '2002-02-27', 'nik' => '3201010102010017', 'photo' => 'assets/media/avatars/150-17.jpg'],
            ['name' => 'Rina Maharani', 'gender' => 'F', 'birth_date' => '2005-11-19', 'nik' => '3201010102010018', 'photo' => 'assets/media/avatars/150-18.jpg'],
        ];

        foreach ($athletes as $athlete) {
            Participant::updateOrCreate(
                ['nik' => $athlete['nik']],
                [
                    'contingent_id' => $contingent->id,
                    'type' => 'athlete',
                    'name' => $athlete['name'],
                    'birth_date' => $athlete['birth_date'],
                    'gender' => $athlete['gender'],
                    'provinsi' => 'Jawa Barat',
                    'institusi' => 'Dojo Karate Hebat',
                    'photo' => $athlete['photo'],
                    'is_verified' => true,
                    'verified_at' => now(),
                ]
            );
        }

        $coaches = [
            ['nik' => '3201010102090001', 'name' => 'Sensei Arman', 'gender' => 'M', 'photo' => 'assets/media/avatars/150-19.jpg'],
            ['nik' => '3201010102090002', 'name' => 'Sensei Rahayu', 'gender' => 'F', 'photo' => 'assets/media/avatars/150-20.jpg'],
            ['nik' => '3201010102090003', 'name' => 'Sensei Fauzan', 'gender' => 'M', 'photo' => 'assets/media/avatars/150-21.jpg'],
        ];

        foreach ($coaches as $coach) {
            Participant::updateOrCreate(
                ['nik' => $coach['nik']],
                [
                    'contingent_id' => $contingent->id,
                    'type' => 'coach',
                    'name' => $coach['name'],
                    'gender' => $coach['gender'],
                    'photo' => $coach['photo'],
                    'is_verified' => true,
                ]
            );
        }

        $officials = [
            ['nik' => '3201010102091001', 'name' => 'Official Yudha', 'gender' => 'M', 'photo' => 'assets/media/avatars/150-22.jpg'],
            ['nik' => '3201010102091002', 'name' => 'Official Melati', 'gender' => 'F', 'photo' => 'assets/media/avatars/150-23.jpg'],
            ['nik' => '3201010102091003', 'name' => 'Official Raka', 'gender' => 'M', 'photo' => 'assets/media/avatars/150-24.jpg'],
            ['nik' => '3201010102091004', 'name' => 'Official Sinta', 'gender' => 'F', 'photo' => 'assets/media/avatars/150-25.jpg'],
        ];

        foreach ($officials as $official) {
            Participant::updateOrCreate(
                ['nik' => $official['nik']],
                [
                    'contingent_id' => $contingent->id,
                    'type' => 'official',
                    'name' => $official['name'],
                    'gender' => $official['gender'],
                    'photo' => $official['photo'],
                    'is_verified' => true,
                ]
            );
        }

        $this->command->info('ParticipantSeeder: 18 athletes, 3 coaches, and 4 officials created.');
    }
}
