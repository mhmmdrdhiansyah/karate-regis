<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\SubCategory;
use App\Enums\SubCategoryGender;
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

    // Confirmation modal state
    public bool $showConfirmation = false;

    // ===== Lifecycle =====

    public function mount(int $event, int $category, int $sub_category): void
    {
        $this->eventId = $event;
        $this->categoryId = $category;
        $this->subCategoryId = $sub_category;

        // Validasi: pastikan sub_category milik category, dan category milik event
        $subCategory = SubCategory::findOrFail($sub_category);
        $eventCategory = EventCategory::findOrFail($category);

        if ($subCategory->event_category_id !== $eventCategory->id) {
            abort(403, 'Sub-kategori tidak valid untuk kategori ini.');
        }
        if ($eventCategory->event_id !== $event) {
            abort(403, 'Kategori tidak valid untuk event ini.');
        }

        // Pastikan user punya contingent
        if (!auth()->user()->contingent) {
            abort(403, 'Anda belum memiliki data kontingen.');
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

    public function confirmSubmit(): void
    {
        $this->errorMessage = '';
        $this->showConfirmation = true;
    }

    public function cancelConfirmation(): void
    {
        $this->showConfirmation = false;
    }

    public function toggleIneligible(): void
    {
        $this->showIneligible = !$this->showIneligible;
    }

    public function submit(): void
    {
        // Validasi jumlah atlet yang dipilih (BR-15)
        $sub = $this->subCategory;
        $count = count($this->selectedAthleteIds);

        if ($count < $sub->min_participants || $count > $sub->max_participants) {
            $this->errorMessage = "{$sub->name} membutuhkan minimal {$sub->min_participants} dan maksimal {$sub->max_participants} atlet.";
            return;
        }

        // Validasi ulang: semua atlet yang dipilih harus eligible
        $eligibleIds = $this->eligibleAthletes->pluck('id')->toArray();
        foreach ($this->selectedAthleteIds as $id) {
            if (!in_array($id, $eligibleIds)) {
                $this->errorMessage = 'Salah satu atlet yang dipilih tidak memenuhi syarat.';
                return;
            }
        }

        // Simpan ke session atau dispatch event untuk diproses di tahap berikutnya
        session()->put('registration_draft.event_id', $this->eventId);
        session()->put('registration_draft.sub_category_id', $this->subCategoryId);
        session()->put('registration_draft.athlete_ids', $this->selectedAthleteIds);

        // Redirect ke halaman konfirmasi/invoice (akan dibuat di Step 4.5)
        session()->flash('success', 'Atlet berhasil dipilih. Lanjutkan ke konfirmasi.');
        $this->redirect(route('registration.index'), navigate: true);
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
