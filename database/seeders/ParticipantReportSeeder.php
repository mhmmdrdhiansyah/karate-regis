<?php

namespace Database\Seeders;

use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\TeamGroup;
use App\Models\User;
use App\Enums\ParticipantType;
use App\Enums\ParticipantGender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ParticipantReportSeeder extends Seeder
{
    /**
     * Membangun dataset lengkap untuk laporan peserta:
     * kontingen -> peserta -> pendaftaran (individu & beregu) + pembayaran + tim.
     *
     * Idempoten: seluruh data dibuat via updateOrCreate berdasarkan key natural,
     * sehingga aman dijalankan berulang (menimpa data lama) tanpa migrate:fresh
     * dan tanpa menghapus data dari seeder lain (roles, ParticipantSeeder, EventSeeder).
     */
    public function run(): void
    {
        // -----------------------------------------------------------------
        // 1. EVENT
        // -----------------------------------------------------------------
        $event = Event::updateOrCreate(
            ['name' => 'Kejuaraan Karate Piala Presiden 2024'],
            [
                'event_date' => '2024-08-15',
                'registration_deadline' => '2024-08-10 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'registration_open',
            ]
        );

        // -----------------------------------------------------------------
        // 2. KATEGORI (Open / DEWASA, lahir 1996-2005)
        // -----------------------------------------------------------------
        $category = EventCategory::updateOrCreate(
            [
                'event_id' => $event->id,
                'type' => 'Open',
                'class_name' => 'DEWASA',
            ],
            [
                'min_birth_date' => '1996-01-01',
                'max_birth_date' => '2005-12-31',
            ]
        );

        // -----------------------------------------------------------------
        // 3. SUB-KATEGORI (individu & beregu)
        // -----------------------------------------------------------------
        $subDefs = [
            ['name' => 'KUMITE Individu Putra', 'category_type' => 'individu', 'gender' => 'M',     'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KUMITE Individu Putri', 'category_type' => 'individu', 'gender' => 'F',     'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KATA Individu Putra',   'category_type' => 'individu', 'gender' => 'M',     'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KATA Individu Putri',   'category_type' => 'individu', 'gender' => 'F',     'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KUMITE Beregu Putra',   'category_type' => 'beregu',   'gender' => 'M',     'price' => 500000, 'min' => 3, 'max' => 3, 'max_teams' => 2],
            ['name' => 'KUMITE Beregu Putri',   'category_type' => 'beregu',   'gender' => 'F',     'price' => 500000, 'min' => 3, 'max' => 3, 'max_teams' => 2],
            ['name' => 'KATA Beregu',           'category_type' => 'beregu',   'gender' => 'Mixed', 'price' => 500000, 'min' => 3, 'max' => 3, 'max_teams' => 2],
        ];

        $subCategories = [];
        foreach ($subDefs as $def) {
            $subCategories[$def['name']] = SubCategory::updateOrCreate(
                ['event_category_id' => $category->id, 'name' => $def['name']],
                [
                    'category_type' => $def['category_type'],
                    'gender' => $def['gender'],
                    'price' => $def['price'],
                    'min_participants' => $def['min'],
                    'max_participants' => $def['max'],
                    'max_teams' => $def['max_teams'],
                ]
            );
        }

        $individuSubs = collect($subCategories)->filter(fn ($s) => $s->category_type === 'individu')->values();
        $bereguSubs = collect($subCategories)->filter(fn ($s) => $s->category_type === 'beregu')->values();

        $kontingenRoleExists = Role::where('name', 'kontingen')->exists();

        $contingentDefs = [
            ['name' => 'DKI Jakarta',     'official' => 'Kontingen DKI Jakarta'],
            ['name' => 'Jawa Barat',      'official' => 'Kontingen Jawa Barat'],
            ['name' => 'Jawa Tengah',     'official' => 'Kontingen Jawa Tengah'],
            ['name' => 'Jawa Timur',      'official' => 'Kontingen Jawa Timur'],
            ['name' => 'Sumatera Utara',  'official' => 'Kontingen Sumatera Utara'],
        ];

        $nikSeq = 1;
        $avatarStock = 25;

        // -----------------------------------------------------------------
        // 4. PER KONTINGEN: user, kontingen, pembayaran, peserta, pendaftaran
        // -----------------------------------------------------------------
        foreach ($contingentDefs as $idx => $c) {
            $user = User::updateOrCreate(
                ['email' => 'report.kontingen.' . ($idx + 1) . '@karatae.test'],
                [
                    'name' => 'Manajer ' . $c['name'],
                    'username' => 'report_kontingen_' . ($idx + 1),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            if ($kontingenRoleExists) {
                $user->assignRole('kontingen');
            }

            $contingent = Contingent::updateOrCreate(
                ['name' => $c['name']],
                [
                    'user_id' => $user->id,
                    'official_name' => $c['official'],
                    'phone' => '08' . str_pad((string) (1000000000 + $idx), 12, '0', STR_PAD_LEFT),
                    'address' => $c['name'],
                ]
            );

            // Satu pembayaran per kontingen untuk event ini
            $payment = Payment::updateOrCreate(
                ['contingent_id' => $contingent->id, 'event_id' => $event->id],
                [
                    'total_amount' => 0,
                    'status' => ($idx % 2 === 0) ? 'verified' : 'pending',
                    'transfer_proof' => ($idx % 2 === 0) ? 'payments/proof-' . ($idx + 1) . '.jpg' : null,
                ]
            );

            $regTotal = 0.0;

            // --- Peserta individu (5 per kontingen) ---
            for ($i = 1; $i <= 5; $i++) {
                $gender = ($i % 2 === 1) ? 'M' : 'F';

                $participant = $this->upsertParticipant($nikSeq, $contingent->id, $avatarStock, [
                    'name' => 'Atlet ' . $c['name'] . ' ' . $i,
                    'gender' => $gender,
                    'institusi' => 'Dojo ' . $c['name'],
                    'day' => '15',
                    'verified' => ($i % 3 === 0),
                ]);
                $nikSeq++;

                // Daftar ke 1-2 sub-kategori individu yang cocok gendernya
                $matched = $individuSubs->filter(fn ($s) => $s->gender->value === $gender);
                foreach ($matched->take(2) as $sub) {
                    Registration::updateOrCreate(
                        ['participant_id' => $participant->id, 'sub_category_id' => $sub->id],
                        [
                            'payment_id' => $payment->id,
                            'status_berkas' => ($i % 2 === 0) ? 'verified' : 'pending_review',
                        ]
                    );
                    $regTotal += (float) $sub->price;
                }
            }

            // --- Peserta beregu (1 tim per sub-kategori beregu, 3 anggota) ---
            foreach ($bereguSubs as $sub) {
                $team = TeamGroup::updateOrCreate(
                    [
                        'contingent_id' => $contingent->id,
                        'sub_category_id' => $sub->id,
                        'team_number' => 1,
                    ],
                    ['team_name' => 'Tim 1']
                );

                for ($m = 1; $m <= 3; $m++) {
                    $gender = ($sub->gender->value === 'Mixed')
                        ? (($m % 2 === 1) ? 'M' : 'F')
                        : $sub->gender->value;

                    $participant = $this->upsertParticipant($nikSeq, $contingent->id, $avatarStock, [
                        'name' => 'Tim ' . $c['name'] . ' ' . $sub->name . ' Anggota ' . $m,
                        'gender' => $gender,
                        'institusi' => 'Klub Karate ' . $c['name'],
                        'day' => '20',
                        'verified' => true,
                    ]);
                    $nikSeq++;

                    Registration::updateOrCreate(
                        ['participant_id' => $participant->id, 'sub_category_id' => $sub->id],
                        [
                            'payment_id' => $payment->id,
                            'team_group_id' => $team->id,
                            'status_berkas' => 'verified',
                        ]
                    );
                    $regTotal += (float) $sub->price;
                }
            }

            $payment->update(['total_amount' => $regTotal]);
        }

        // -----------------------------------------------------------------
        // 5. RINGKASAN
        // -----------------------------------------------------------------
        $this->command->info('Participant Report Seeder completed successfully!');
        $this->command->info('Created/updated:');
        $this->command->info('  - ' . Event::count() . ' Events');
        $this->command->info('  - ' . EventCategory::count() . ' Event Categories');
        $this->command->info('  - ' . SubCategory::count() . ' Sub Categories');
        $this->command->info('  - ' . User::count() . ' Users');
        $this->command->info('  - ' . Contingent::count() . ' Contingents');
        $this->command->info('  - ' . Participant::count() . ' Participants');
        $this->command->info('  - ' . Payment::count() . ' Payments');
        $this->command->info('  - ' . TeamGroup::count() . ' Team Groups');
        $this->command->info('  - ' . Registration::count() . ' Registrations');
    }

    /**
     * Buat/perbarui peserta berdasarkan NIK (unik) dengan tanggal lahir yang
     * konsisten dan selalu di dalam rentang kategori (1996-2005).
     */
    private function upsertParticipant(int $nikSeq, int $contingentId, int $avatarStock, array $attrs): Participant
    {
        $year = 1996 + ($nikSeq % 10);
        $month = str_pad((string) (($nikSeq % 9) + 1), 2, '0', STR_PAD_LEFT);

        return Participant::updateOrCreate(
            ['nik' => '321' . str_pad((string) $nikSeq, 13, '0', STR_PAD_LEFT)],
            [
                'contingent_id' => $contingentId,
                'type' => ParticipantType::Athlete,
                'name' => $attrs['name'],
                'birth_date' => "{$year}-{$month}-{$attrs['day']}",
                'gender' => $attrs['gender'],
                'institusi' => $attrs['institusi'],
                'photo' => 'assets/media/avatars/150-' . ((($nikSeq - 1) % $avatarStock) + 1) . '.jpg',
                'is_verified' => $attrs['verified'],
            ]
        );
    }
}
