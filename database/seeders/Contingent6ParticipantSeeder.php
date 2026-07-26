<?php

namespace Database\Seeders;

use App\Models\Contingent;
use App\Models\Participant;
use App\Enums\ParticipantType;
use App\Enums\ParticipantGender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Contingent6ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        // Cari kontingen ke-6 (Medan Karate Institute) berdasarkan NAMA, bukan
        // ID. ContingentSeeder membuat kontingen via updateOrCreate kunci nama,
        // jadi ID bisa bergeser (mis. 19-24) bila tabel sudah berisi data lama.
        // Lookup via nama membuat seeder ini tahan terhadap pergeseran ID.
        $contingent = Contingent::where('name', 'Medan Karate Institute')->first();

        if (!$contingent) {
            $this->command->warn('Contingent "Medan Karate Institute" not found.');
            return;
        }

        $athletes = [
            // JUNIOR (2009-2010)
            ['name' => 'Aditya Kusuma', 'gender' => 'M', 'birth_date' => '2009-05-15', 'nik' => '3206060102010001', 'photo' => 'assets/media/avatars/150-26.jpg'],
            ['name' => 'Bima Satria', 'gender' => 'M', 'birth_date' => '2010-08-22', 'nik' => '3206060102010002', 'photo' => 'assets/media/avatars/150-27.jpg'],
            ['name' => 'Chandra Wijaya', 'gender' => 'M', 'birth_date' => '2009-12-10', 'nik' => '3206060102010003', 'photo' => 'assets/media/avatars/150-28.jpg'],
            ['name' => 'Dian Sastro', 'gender' => 'F', 'birth_date' => '2009-03-18', 'nik' => '3206060102010004', 'photo' => 'assets/media/avatars/150-29.jpg'],
            ['name' => 'Eka Pertiwi', 'gender' => 'F', 'birth_date' => '2010-11-25', 'nik' => '3206060102010005', 'photo' => 'assets/media/avatars/150-30.jpg'],
            ['name' => 'Fajar Utami', 'gender' => 'F', 'birth_date' => '2009-07-08', 'nik' => '3206060102010006', 'photo' => 'assets/media/avatars/150-31.jpg'],

            // U21 (2006-2008)
            ['name' => 'Gilang Ramadhan', 'gender' => 'M', 'birth_date' => '2006-02-14', 'nik' => '3206060102010007', 'photo' => 'assets/media/avatars/150-32.jpg'],
            ['name' => 'Hans Fernando', 'gender' => 'M', 'birth_date' => '2007-06-19', 'nik' => '3206060102010008', 'photo' => 'assets/media/avatars/150-33.jpg'],
            ['name' => 'Indra Lesmana', 'gender' => 'M', 'birth_date' => '2008-09-28', 'nik' => '3206060102010009', 'photo' => 'assets/media/avatars/150-34.jpg'],
            ['name' => 'Jesica Putri', 'gender' => 'F', 'birth_date' => '2006-04-12', 'nik' => '3206060102010010', 'photo' => 'assets/media/avatars/150-35.jpg'],
            ['name' => 'Kirana Dewi', 'gender' => 'F', 'birth_date' => '2007-01-30', 'nik' => '3206060102010011', 'photo' => 'assets/media/avatars/150-36.jpg'],
            ['name' => 'Lina Marlina', 'gender' => 'F', 'birth_date' => '2008-10-07', 'nik' => '3206060102010012', 'photo' => 'assets/media/avatars/150-37.jpg'],

            // DEWASA (1996-2005)
            ['name' => 'Mahendra Pratama', 'gender' => 'M', 'birth_date' => '1999-05-20', 'nik' => '3206060102010013', 'photo' => 'assets/media/avatars/150-38.jpg'],
            ['name' => 'Noviandi Saputra', 'gender' => 'M', 'birth_date' => '2001-08-15', 'nik' => '3206060102010014', 'photo' => 'assets/media/avatars/150-39.jpg'],
            ['name' => 'Oscar Permana', 'gender' => 'M', 'birth_date' => '2004-11-08', 'nik' => '3206060102010015', 'photo' => 'assets/media/avatars/150-40.jpg'],
            ['name' => 'Puspa Indah', 'gender' => 'F', 'birth_date' => '1998-07-12', 'nik' => '3206060102010016', 'photo' => 'assets/media/avatars/150-41.jpg'],
            ['name' => 'Qonita Sari', 'gender' => 'F', 'birth_date' => '2002-03-25', 'nik' => '3206060102010017', 'photo' => 'assets/media/avatars/150-42.jpg'],
            ['name' => 'Ratna Wijaya', 'gender' => 'F', 'birth_date' => '2005-12-18', 'nik' => '3206060102010018', 'photo' => 'assets/media/avatars/150-43.jpg'],
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
                    'institusi' => $contingent->name,
                    'photo' => $athlete['photo'],
                    'is_verified' => true,
                    'verified_at' => now(),
                ]
            );
        }

        $coaches = [
            ['nik' => '3206060102090001', 'name' => 'Sensei Bambang', 'gender' => 'M', 'photo' => 'assets/media/avatars/150-44.jpg'],
            ['nik' => '3206060102090002', 'name' => 'Sensei Citra', 'gender' => 'F', 'photo' => 'assets/media/avatars/150-45.jpg'],
            ['nik' => '3206060102090003', 'name' => 'Sensei Doni', 'gender' => 'M', 'photo' => 'assets/media/avatars/150-46.jpg'],
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
            ['nik' => '3206060102091001', 'name' => 'Official Eko', 'gender' => 'M', 'photo' => 'assets/media/avatars/150-47.jpg'],
            ['nik' => '3206060102091002', 'name' => 'Official Fitri', 'gender' => 'F', 'photo' => 'assets/media/avatars/150-48.jpg'],
            ['nik' => '3206060102091003', 'name' => 'Official Gunawan', 'gender' => 'M', 'photo' => 'assets/media/avatars/150-49.jpg'],
            ['nik' => '3206060102091004', 'name' => 'Official Hartini', 'gender' => 'F', 'photo' => 'assets/media/avatars/150-50.jpg'],
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

        $this->command->info('Contingent6ParticipantSeeder: 18 athletes, 3 coaches, and 4 officials created for contingent ' . $contingent->name . ' (ID: ' . $contingent->id . ').');
    }
}
