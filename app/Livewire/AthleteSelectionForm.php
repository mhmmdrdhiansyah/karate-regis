<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\RegistrationDraft;
use App\Models\RegistrationDraftItem;
use App\Models\SubCategory;
use App\Enums\SubCategoryGender;
use App\Services\RegistrationService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class AthleteSelectionForm extends Component
{
    // ===== Properties =====

    // ID yang dikirim dari wizard — LOCK agar tidak bisa dimanipulasi dari frontend
    #[Locked]
    public int $eventId;

    #[Locked]
    public int $categoryId;

    #[Locked]
    public int $subCategoryId;

    // Array ID atlet yang dipilih user (dari checkbox)
    public array $selectedAthleteIds = [];

    // Pesan error jika ada
    public string $errorMessage = '';

    // Search filter untuk daftar atlet
    public string $search = '';

    // Toggle untuk menampilkan atlet tidak memenuhi syarat
    public bool $showIneligible = false;

    public bool $showSavedIndicator = false;

    // Untuk mode beregu
    public array $teams = [];           // Array of team data: [{id, name, number, memberIds}]
    public ?int $activeTeamId = null;   // Tim yang sedang diedit

    // ===== Lifecycle =====

    public function mount(int $event, int $category, int $sub_category): void
    {
        $this->eventId = $event;
        $this->categoryId = $category;
        $this->subCategoryId = $sub_category;

        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            abort(403, 'Anda belum memiliki data kontingen.');
        }

        // Validasi: pastikan sub_category milik category, dan category milik event
        $subCategory = SubCategory::findOrFail($sub_category);
        $eventCategory = EventCategory::findOrFail($category);

        if ($subCategory->event_category_id !== $eventCategory->id) {
            abort(403, 'Sub-kategori tidak valid untuk kategori ini.');
        }
        if ($eventCategory->event_id !== $event) {
            abort(403, 'Kategori tidak valid untuk event ini.');
        }

        $draft = RegistrationDraft::firstOrCreate([
            'contingent_id' => $contingent->id,
            'event_id' => $this->eventId,
            'status' => 'draft',
        ]);

        $this->selectedAthleteIds = RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->where('sub_category_id', $this->subCategoryId)
            ->pluck('participant_id')
            ->toArray();

        // Jika beregu, load tim-tim yang sudah dibuat
        if ($subCategory->isTeam()) {
            // Cleanup: Hapus item draf di kategori ini yang tidak punya team_group_id (data rusak)
            RegistrationDraftItem::where('registration_draft_id', $draft->id)
                ->where('sub_category_id', $this->subCategoryId)
                ->whereNull('team_group_id')
                ->delete();

            $this->loadTeams();
        }
    }

    // ===== Computed Properties =====

    #[Computed]
    public function subCategory(): SubCategory
    {
        return SubCategory::with('eventCategory.event')->findOrFail($this->subCategoryId);
    }

    #[Computed]
    public function eligibleAthletes()
    {
        return Participant::eligibleFor($this->subCategory)
            ->with(['registrations.subCategory', 'draftItems.subCategory'])
            ->when($this->search !== '', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function ineligibleAthletes()
    {
        // Ambil SEMUA atlet milik kontingen
        $allAthletes = Participant::athletes()
            ->where('contingent_id', auth()->user()->contingent->id)
            ->with(['registrations.subCategory', 'draftItems.subCategory'])
            ->get();

        // Filter yang TIDAK eligible + tentukan alasan
        $eligibleIds = $this->eligibleAthletes->pluck('id')->toArray();

        return $allAthletes->reject(fn($athlete) => in_array($athlete->id, $eligibleIds))
            ->map(function ($athlete) {
                $athlete->ineligible_reason = $this->getIneligibleReason($athlete);
                return $athlete;
            });
    }

    // ===== Actions =====

    public function toggleIneligible(): void
    {
        $this->showIneligible = !$this->showIneligible;
    }

    public function createTeam(): void
    {
        $sub = $this->subCategory();
        if (count($this->teams) >= $sub->max_teams) {
            $this->errorMessage = "Maksimal {$sub->max_teams} tim untuk sub-kategori ini.";
            return;
        }

        $contingent = auth()->user()->contingent;
        $teamNumber = count($this->teams) + 1;

        $team = \App\Models\TeamGroup::create([
            'contingent_id' => $contingent->id,
            'sub_category_id' => $this->subCategoryId,
            'team_name' => "Tim " . chr(64 + $teamNumber), // Tim A, Tim B, dst
            'team_number' => $teamNumber,
        ]);

        $this->loadTeams();
        $this->activeTeamId = $team->id;
        $this->showSavedIndicator = true;
    }

    public function deleteTeam(int $teamGroupId): void
    {
        $team = \App\Models\TeamGroup::where('id', $teamGroupId)
            ->where('contingent_id', auth()->user()->contingent->id)
            ->first();

        if ($team) {
            // Hapus anggota tim dari draf
            RegistrationDraftItem::where('team_group_id', $team->id)->delete();
            $team->delete();

            if ($this->activeTeamId === $teamGroupId) {
                $this->activeTeamId = null;
            }

            $this->loadTeams();
            $this->showSavedIndicator = true;
        }
    }

    public function selectTeam(int $teamGroupId): void
    {
        if ($this->activeTeamId === $teamGroupId) {
            $this->activeTeamId = null; // Toggle off if already selected
            return;
        }
        $this->activeTeamId = $teamGroupId;
    }

    public function updateTeamName(int $teamGroupId, string $name): void
    {
        $this->errorMessage = '';
        $name = trim($name);

        if (empty($name)) {
            // Jika kosong, kembalikan ke default (Tim A, Tim B, dst)
            $team = \App\Models\TeamGroup::find($teamGroupId);
            if ($team) {
                $name = "Tim " . chr(64 + $team->team_number);
                $team->update(['team_name' => $name]);
            }
        } else {
            \App\Models\TeamGroup::where('id', $teamGroupId)
                ->where('contingent_id', auth()->user()->contingent->id)
                ->update(['team_name' => $name]);
        }

        $this->loadTeams();
        $this->showSavedIndicator = true;
    }

    public function toggleTeamMember(int $athleteId): void
    {
        if (!$this->activeTeamId) {
            $this->errorMessage = 'Pilih tim terlebih dahulu.';
            return;
        }

        $this->errorMessage = '';
        $this->showSavedIndicator = false;

        $contingent = auth()->user()->contingent;
        $draft = RegistrationDraft::where('contingent_id', $contingent->id)
            ->where('event_id', $this->eventId)
            ->where('status', 'draft')
            ->first();

        if (!$draft) return;

        $registrationService = app(RegistrationService::class);
        if (!$registrationService->isRegistrationOpen($this->subCategory->eventCategory->event)) {
            $this->errorMessage = 'Pendaftaran event sudah ditutup.';
            return;
        }

        // Cek apakah sudah ada di tim ini
        $existing = RegistrationDraftItem::where('registration_draft_id', $draft->id)
            ->where('sub_category_id', $this->subCategoryId)
            ->where('participant_id', $athleteId)
            ->where('team_group_id', $this->activeTeamId)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            // Cek apakah sudah ada di tim LAIN pada sub-kategori yang sama
            $inOtherTeam = RegistrationDraftItem::where('registration_draft_id', $draft->id)
                ->where('sub_category_id', $this->subCategoryId)
                ->where('participant_id', $athleteId)
                ->exists();

            if ($inOtherTeam) {
                $this->errorMessage = 'Atlet sudah terdaftar di tim lain pada kategori ini.';
                return;
            }

            // Cek kuota anggota tim
            $currentMemberCount = RegistrationDraftItem::where('team_group_id', $this->activeTeamId)->count();
            if ($currentMemberCount >= $this->subCategory->max_participants) {
                $this->errorMessage = "Tim sudah penuh (maksimal {$this->subCategory->max_participants} atlet).";
                return;
            }

            // Cek eligibility
            $isEligible = Participant::eligibleFor($this->subCategory)->where('participants.id', $athleteId)->exists();
            if (!$isEligible) {
                $this->errorMessage = 'Atlet tidak memenuhi syarat untuk kategori ini.';
                return;
            }

            RegistrationDraftItem::create([
                'registration_draft_id' => $draft->id,
                'participant_id' => $athleteId,
                'sub_category_id' => $this->subCategoryId,
                'team_group_id' => $this->activeTeamId,
            ]);
        }

        $this->loadTeams();
        $this->showSavedIndicator = true;
    }

    public function loadTeams(): void
    {
        $contingent = auth()->user()->contingent;
        $teamGroups = \App\Models\TeamGroup::where('contingent_id', $contingent->id)
            ->where('sub_category_id', $this->subCategoryId)
            ->with('draftItems')
            ->orderBy('team_number')
            ->get();

        $this->teams = $teamGroups->map(fn($tg) => [
            'id' => $tg->id,
            'name' => $tg->team_name,
            'number' => $tg->team_number,
            'memberIds' => $tg->draftItems->pluck('participant_id')->toArray(),
        ])->toArray();
    }

    public function isAthleteInAnyTeam(int $athleteId): bool
    {
        foreach ($this->teams as $team) {
            if (in_array($athleteId, $team['memberIds'])) {
                return true;
            }
        }
        return false;
    }

    public function updatedSelectedAthleteIds(): void
    {
        $this->showSavedIndicator = false;
        $this->selectedAthleteIds = array_values(array_unique(array_map('intval', $this->selectedAthleteIds)));
        $draft = RegistrationDraft::where('contingent_id', auth()->user()->contingent->id)
            ->where('event_id', $this->eventId)
            ->where('status', 'draft')
            ->first();

        if (! $draft) {
            return;
        }

        $registrationService = app(RegistrationService::class);
        $event = $this->subCategory->eventCategory->event;
        if (! $registrationService->isRegistrationOpen($event)) {
            $this->errorMessage = 'Pendaftaran event sudah ditutup.';
            return;
        }

        // Keamanan: Jika kategori ini beregu, jangan proses via metode individu
        if ($this->subCategory->isTeam()) {
            return;
        }

        if (count($this->selectedAthleteIds) > $this->subCategory->max_participants) {
            $this->errorMessage = "{$this->subCategory->name} maksimal {$this->subCategory->max_participants} atlet.";
            $this->selectedAthleteIds = array_slice($this->selectedAthleteIds, 0, $this->subCategory->max_participants);
        }

        $existingIds = RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->where('sub_category_id', $this->subCategoryId)
            ->pluck('participant_id')
            ->toArray();

        $eligibleIds = $this->eligibleAthletes->pluck('id')->toArray();
        $toInsert = array_diff($this->selectedAthleteIds, $existingIds);
        $toInsert = array_values(array_intersect($toInsert, $eligibleIds));
        $toDelete = array_diff($existingIds, $this->selectedAthleteIds);

        if (count($toInsert) > 0) {
            $rows = collect($toInsert)->map(fn ($id) => [
                'registration_draft_id' => $draft->id,
                'participant_id' => $id,
                'sub_category_id' => $this->subCategoryId,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();
            RegistrationDraftItem::insert($rows);
        }

        if (count($toDelete) > 0) {
            RegistrationDraftItem::query()
                ->where('registration_draft_id', $draft->id)
                ->where('sub_category_id', $this->subCategoryId)
                ->whereIn('participant_id', $toDelete)
                ->delete();
        }

        $ineligibleSelection = array_diff($this->selectedAthleteIds, $eligibleIds);
        if (count($ineligibleSelection) > 0) {
            $this->errorMessage = 'Salah satu atlet yang dipilih tidak memenuhi syarat.';
            $this->selectedAthleteIds = array_values(array_intersect($this->selectedAthleteIds, $eligibleIds));

            RegistrationDraftItem::query()
                ->where('registration_draft_id', $draft->id)
                ->where('sub_category_id', $this->subCategoryId)
                ->whereNotIn('participant_id', $this->selectedAthleteIds)
                ->delete();
        }

        $this->showSavedIndicator = true;
    }

    public function clearDraft(): void
    {
        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            return;
        }

        $draft = RegistrationDraft::where('contingent_id', $contingent->id)
            ->where('event_id', $this->eventId)
            ->where('status', 'draft')
            ->first();

        if ($draft) {
            RegistrationDraftItem::where('registration_draft_id', $draft->id)->delete();
            $this->selectedAthleteIds = [];
            $this->showSavedIndicator = true;
        }
    }



    // ===== Helper Methods (Private) =====

    private function getIneligibleReason(Participant $athlete): string
    {
        $sub = $this->subCategory;
        $eventCategory = $sub->eventCategory;
        $reasons = [];

        // Cek gender
        if ($sub->gender !== SubCategoryGender::Mixed) {
            if ($athlete->gender?->value !== $sub->gender->value) {
                $genderLabel = $sub->gender->value === 'M' ? 'Putra' : 'Putri';
                $reasons[] = "Gender tidak sesuai (harus {$genderLabel})";
            }
        }

        // Cek rentang tanggal lahir
        if ($athlete->birth_date && $eventCategory->min_birth_date && $eventCategory->max_birth_date) {
            if ($athlete->birth_date->lt($eventCategory->min_birth_date)) {
                $reasons[] = 'Terlalu tua (lahir sebelum ' . $eventCategory->min_birth_date->format('d/m/Y') . ')';
            }
            if ($athlete->birth_date->gt($eventCategory->max_birth_date)) {
                $reasons[] = 'Terlalu muda (lahir setelah ' . $eventCategory->max_birth_date->format('d/m/Y') . ')';
            }
        }

        // Cek duplikasi registrasi (BR-09)
        $alreadyRegistered = $athlete->registrations()
            ->where('sub_category_id', $sub->id)
            ->exists();
        if ($alreadyRegistered) {
            $reasons[] = 'Sudah terdaftar di sub-kategori ini';
        }

        return implode(', ', $reasons) ?: 'Tidak memenuhi syarat';
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.athlete-selection-form');
    }
}
