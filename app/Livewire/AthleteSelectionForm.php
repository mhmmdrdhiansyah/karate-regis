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
