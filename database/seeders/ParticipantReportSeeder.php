<?php

namespace Database\Seeders;

use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\TeamGroup;
use App\Enums\ParticipantType;
use App\Enums\Gender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipantReportSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        Registration::truncate();
        Participant::truncate();
        TeamGroup::truncate();
        SubCategory::truncate();
        EventCategory::truncate();
        Event::truncate();
        Contingent::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Contingents
        $contingents = [
            ['name' => 'DKI Jakarta', 'code' => 'DKI'],
            ['name' => 'Jawa Barat', 'code' => 'JABAR'],
            ['name' => 'Jawa Tengah', 'code' => 'JATENG'],
            ['name' => 'Jawa Timur', 'code' => 'JATIM'],
            ['name' => 'Sumatera Utara', 'code' => 'SUMUT'],
        ];

        foreach ($contingents as $c) {
            Contingent::create($c);
        }

        // Create Event
        $event = Event::create([
            'name' => 'Kejuaraan Karate Piala Presiden 2024',
            'description' => 'Kejuaraan karate tingkat nasional',
            'location' => 'Jakarta',
            'start_date' => '2024-08-15',
            'end_date' => '2024-08-18',
            'is_active' => true,
        ]);

        // Create Event Categories
        $categories = [
            ['name' => 'Kumite', 'description' => 'Pertandingan perorangan'],
            ['name' => 'Kata', 'description' => 'Pertandingan perorangan'],
            ['name' => 'Kumite Beregu', 'description' => 'Pertandingan beregu'],
            ['name' => 'Kata Beregu', 'description' => 'Pertandingan beregu'],
        ];

        foreach ($categories as $cat) {
            EventCategory::create([
                'event_id' => $event->id,
                'name' => $cat['name'],
                'description' => $cat['description'],
                'min_birth_date' => '2005-01-01', // ~19 years old
            ]);
        }

        // Get all event categories
        $eventCategories = $event->categories;
        $kumiteCategory = $eventCategories->firstWhere('name', 'Kumite');
        $kataCategory = $eventCategories->firstWhere('name', 'Kata');
        $kumiteTeamCategory = $eventCategories->firstWhere('name', 'Kumite Beregu');
        $kataTeamCategory = $eventCategories->firstWhere('name', 'Kata Beregu');

        // Create Sub Categories for Kumite (Individual)
        $kumiteSubs = [
            ['event_category_id' => $kumiteCategory->id, 'name' => '-60kg', 'gender' => 'M', 'category_type' => 'individu'],
            ['event_category_id' => $kumiteCategory->id, 'name' => '-67kg', 'gender' => 'M', 'category_type' => 'individu'],
            ['event_category_id' => $kumiteCategory->id, 'name' => '-75kg', 'gender' => 'M', 'category_type' => 'individu'],
            ['event_category_id' => $kumiteCategory->id, 'name' => '-55kg', 'gender' => 'F', 'category_type' => 'individu'],
            ['event_category_id' => $kumiteCategory->id, 'name' => '-61kg', 'gender' => 'F', 'category_type' => 'individu'],
        ];

        foreach ($kumiteSubs as $sub) {
            SubCategory::create($sub);
        }

        // Create Sub Categories for Kata (Individual)
        $kataSubs = [
            ['event_category_id' => $kataCategory->id, 'name' => 'Individual', 'gender' => 'M', 'category_type' => 'individu'],
            ['event_category_id' => $kataCategory->id, 'name' => 'Individual', 'gender' => 'F', 'category_type' => 'individu'],
        ];

        foreach ($kataSubs as $sub) {
            SubCategory::create($sub);
        }

        // Create Sub Categories for Team Events
        $teamSubs = [
            ['event_category_id' => $kumiteTeamCategory->id, 'name' => 'Beregu', 'gender' => 'M', 'category_type' => 'beregu'],
            ['event_category_id' => $kumiteTeamCategory->id, 'name' => 'Beregu', 'gender' => 'F', 'category_type' => 'beregu'],
            ['event_category_id' => $kataTeamCategory->id, 'name' => 'Beregu', 'gender' => 'Mixed', 'category_type' => 'beregu'],
        ];

        foreach ($teamSubs as $sub) {
            SubCategory::create($sub);
        }

        // Get all contingents and sub categories
        $allContingents = Contingent::all();
        $kumiteSubCategories = SubCategory::where('category_type', 'individu')->get();
        $teamSubCategories = SubCategory::where('category_type', 'beregu')->get();

        // Create Participants and Registrations for Individual Events
        $participantId = 1;
        $registrationCount = 0;

        foreach ($allContingents as $contingent) {
            // Create 5 participants per contingent for individual events
            for ($i = 1; $i <= 5; $i++) {
                $gender = ($i % 2 == 0) ? Gender::Male : Gender::Female;
                $birthYear = rand(2003, 2008); // 16-21 years old

                $participant = Participant::create([
                    'contingent_id' => $contingent->id,
                    'nik' => '320' . str_pad($participantId, 12, '0', STR_PAD_LEFT),
                    'name' => 'Atlet ' . $contingent->code . ' ' . $i,
                    'institusi' => 'Sekolah Olahraga ' . $contingent->name,
                    'birth_date' => $birthYear . '-01-15',
                    'gender' => $gender,
                    'type' => ParticipantType::Athlete,
                    'is_verified' => ($i % 3 == 0), // Every 3rd athlete is verified
                ]);

                // Create 2 registrations per participant (different categories)
                foreach ($kumiteSubCategories->take(2) as $subCat) {
                    // Only register if gender matches
                    if ($subCat->gender == 'M' || $subCat->gender == 'Mixed' || ($subCat->gender == 'F' && $gender == Gender::Female)) {
                        Registration::create([
                            'participant_id' => $participant->id,
                            'sub_category_id' => $subCat->id,
                            'status_berkas' => ($i % 2 == 0) ? 'verified' : 'pending',
                        ]);
                        $registrationCount++;
                    }
                }

                $participantId++;
            }
        }

        // Create Team Groups and Registrations for Team Events
        $teamGroupId = 1;

        foreach ($allContingents as $contingent) {
            foreach ($teamSubCategories as $teamSub) {
                // Create a team group
                $teamGroup = TeamGroup::create([
                    'name' => $contingent->code . ' Team ' . $teamGroupId,
                    'contingent_id' => $contingent->id,
                ]);

                // Create 3 team members
                for ($i = 1; $i <= 3; $i++) {
                    $gender = ($teamSub->gender == 'M') ? Gender::Male :
                              ($teamSub->gender == 'F') ? Gender::Female : Gender::Male;

                    $participant = Participant::create([
                        'contingent_id' => $contingent->id,
                        'nik' => '320' . str_pad($participantId, 12, '0', STR_PAD_LEFT),
                        'name' => 'Team Member ' . $contingent->code . ' ' . $teamGroupId . '.' . $i,
                        'institusi' => 'Klub Karate ' . $contingent->name,
                        'birth_date' => rand(2004, 2007) . '-03-20',
                        'gender' => $gender,
                        'type' => ParticipantType::Athlete,
                        'is_verified' => true,
                    ]);

                    // Create team registration
                    Registration::create([
                        'participant_id' => $participant->id,
                        'sub_category_id' => $teamSub->id,
                        'team_group_id' => $teamGroup->id,
                        'status_berkas' => 'verified',
                    ]);

                    $participantId++;
                    $registrationCount++;
                }

                $teamGroupId++;
            }
        }

        $this->command->info('Participant Report Seeder completed successfully!');
        $this->command->info('Created:');
        $this->command->info('  - ' . Contingent::count() . ' Contingents');
        $this->command->info('  - ' . Event::count() . ' Events');
        $this->command->info('  - ' . EventCategory::count() . ' Event Categories');
        $this->command->info('  - ' . SubCategory::count() . ' Sub Categories');
        $this->command->info('  - ' . Participant::count() . ' Participants');
        $this->command->info('  - ' . TeamGroup::count() . ' Team Groups');
        $this->command->info('  - ' . Registration::count() . ' Registrations');
    }
}
