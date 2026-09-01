<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Registration;
use App\Models\Result;
use App\Models\SubCategory;
use App\Enums\MedalType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ResultEntry extends Component
{
    public Event $event;
    public array $slots = []; // [subCategoryId => [ ['medal_type' => 'Gold', 'registration_id' => 1], ... ]]

    /** Tab aktif: tipe kategori (mis. 'Open'/'Festival') — Open default. */
    public string $activeType = '';

    /** Tab aktif: EventCategory (kelas) dalam tipe terpilih. */
    public int|null $activeClassId = null;

    /**
     * Kategori dikelompokkan per tipe untuk navigasi 2 level (tipe → kelas).
     * Open selalu pertama, sisanya alphabet; kelas dalam tiap tipe alphabet.
     *
     * @var array<string, \Illuminate\Support\Collection<int, EventCategory>>
     */
    public array $groupedCategories = [];

    public function mount(Event $event)
    {
        // Panitia hanya bisa input hasil event yang dipegangnya & belum completed
        abort_unless(auth()->user()->can('manage', $event), 403, 'Bukan event yang ditugaskan.');

        $this->event = $event;
        $this->event->load([
            'categories.subCategories.registrations' => function ($q) {
                $q->whereNull('deleted_at');
            },
            'categories.subCategories.registrations.participant.contingent',
            'categories.subCategories.registrations.teamGroup.contingent',
            'categories.subCategories.teamGroups', // opsi tim untuk sub beregu
        ]);

        // Grup tipe → kelas (Open dulu, kelas alphabet) untuk tab navigasi
        $this->groupedCategories = $this->event->categories
            ->sortBy([['type', 'asc'], ['class_name', 'asc']])
            ->groupBy(fn ($c) => $c->type->value)
            ->map(fn ($group) => $group->values())
            ->toArray();

        // Letakkan grup Open di depan
        uksort($this->groupedCategories, function ($a, $b) {
            if ($a === 'Open') return -1;
            if ($b === 'Open') return 1;
            return strcmp($a, $b);
        });

        $this->activeType = array_key_first($this->groupedCategories) ?? '';
        $this->activeClassId = $this->groupedCategories[$this->activeType][0]['id'] ?? null;

        foreach ($this->event->categories as $category) {
            foreach ($category->subCategories as $subCategory) {
                $subCategoryRegIds = $subCategory->registrations->pluck('id');
                $results = Result::whereIn('registration_id', $subCategoryRegIds)->get();

                if ($results->isEmpty()) {
                    // Default 4 slots
                    $this->slots[$subCategory->id] = [
                        ['key' => uniqid('slot_'), 'rank_name' => 'Juara 1', 'medal_type' => 'Gold', 'registration_id' => ''],
                        ['key' => uniqid('slot_'), 'rank_name' => 'Juara 2', 'medal_type' => 'Silver', 'registration_id' => ''],
                        ['key' => uniqid('slot_'), 'rank_name' => 'Juara 3', 'medal_type' => 'Bronze', 'registration_id' => ''],
                        ['key' => uniqid('slot_'), 'rank_name' => 'Juara 3 Bersama', 'medal_type' => 'Bronze', 'registration_id' => ''],
                    ];
                } else {
                    $this->slots[$subCategory->id] = [];
                    foreach ($results as $res) {
                        // Beregu: kembalikan per TIM — Result anggota tim yang sama
                        // (rank+medali identik) diwakilkan satu slot bertim tersebut.
                        $teamId = $res->registration?->team_group_id;
                        if ($subCategory->isTeam() && $teamId) {
                            $alreadySlotted = collect($this->slots[$subCategory->id])
                                ->contains(fn ($s) => $s['registration_id'] === 'team:' . $teamId);
                            if (! $alreadySlotted) {
                                $this->slots[$subCategory->id][] = [
                                    'key' => uniqid('slot_'),
                                    'rank_name' => $res->rank_name ?? '',
                                    'medal_type' => $res->medal_type ? $res->medal_type->value : '',
                                    'registration_id' => 'team:' . $teamId,
                                ];
                            }
                            continue;
                        }
                        // Individu (atau Result lama tanpa tim): slot perorangan seperti biasa
                        $this->slots[$subCategory->id][] = [
                            'key' => uniqid('slot_'),
                            'rank_name' => $res->rank_name ?? '',
                            'medal_type' => $res->medal_type ? $res->medal_type->value : '',
                            'registration_id' => $res->registration_id,
                        ];
                    }
                }
            }
        }
    }

    /**
     * Ganti tab tipe — kelas aktif direset ke kelas pertama tipe tersebut.
     */
    public function selectType(string $type): void
    {
        if (! isset($this->groupedCategories[$type])) {
            return;
        }

        $this->activeType = $type;
        $this->activeClassId = $this->groupedCategories[$type][0]['id'] ?? null;
    }

    /**
     * Pilih kelas langsung dari sidebar tree — set tipe + kelas sekaligus
     * (satu action, tanpa bergantung urutan dua update terpisah).
     */
    public function selectClass(string $type, int $classId): void
    {
        if (! isset($this->groupedCategories[$type])) {
            return;
        }

        $valid = collect($this->groupedCategories[$type])->contains(fn ($c) => $c['id'] === $classId);
        if (! $valid) {
            return;
        }

        $this->activeType = $type;
        $this->activeClassId = $classId;
    }

    /**
     * Kategori (kelas) yang sedang aktif — konten tab.
     */
    public function getActiveCategoryProperty(): ?EventCategory
    {
        foreach ($this->groupedCategories[$this->activeType] ?? [] as $category) {
            if ($category['id'] === $this->activeClassId) {
                return $this->event->categories->firstWhere('id', $category['id']);
            }
        }

        return null;
    }

    public function addSlot($subCategoryId)
    {
        if (!isset($this->slots[$subCategoryId])) {
            $this->slots[$subCategoryId] = [];
        }
        
        $this->slots[$subCategoryId][] = [
            'key' => uniqid('slot_'),
            'rank_name' => '',
            'medal_type' => '', // Empty means No Medal
            'registration_id' => '',
        ];
    }

    public function removeSlot($subCategoryId, $index)
    {
        if (isset($this->slots[$subCategoryId][$index])) {
            $slot = $this->slots[$subCategoryId][$index];

            // Slot tim: hapus Result SEMUA anggota tim tersebut
            if (str_starts_with((string) $slot['registration_id'], 'team:')) {
                $teamId = (int) substr($slot['registration_id'], 5);
                $regIds = Registration::where('team_group_id', $teamId)->pluck('id');
                Result::whereIn('registration_id', $regIds)->delete();
            } elseif (!empty($slot['registration_id'])) {
                Result::where('registration_id', $slot['registration_id'])->delete();
            }

            unset($this->slots[$subCategoryId][$index]);
            // Re-index array so Livewire doesn't break
            $this->slots[$subCategoryId] = array_values($this->slots[$subCategoryId]);
        }
    }

    public function save($subCategoryId)
    {
        abort_unless(auth()->user()->can('manage', $this->event), 403, 'Event selesai, data read-only.');

        $slots = $this->slots[$subCategoryId] ?? [];
        $subCategory = $this->findSubCategory((int) $subCategoryId);
        $isTeamCategory = $subCategory && $subCategory->isTeam();

        // Validate duplicates
        $regIds = collect($slots)->pluck('registration_id')->filter(fn($id) => $id !== '');
        if ($regIds->duplicates()->isNotEmpty()) {
            session()->flash("error_{$subCategoryId}", 'Ada peserta yang sama dipilih lebih dari satu kali!');
            return;
        }

        $subCategoryRegIds = Registration::forManagedEvents()->where('sub_category_id', $subCategoryId)->pluck('id');

        // Delete existing results for this subcategory
        Result::whereIn('registration_id', $subCategoryRegIds)->delete();

        // Save new results
        foreach ($slots as $slot) {
            if (!empty($slot['registration_id']) && (!empty($slot['medal_type']) || !empty($slot['rank_name']))) {
                // Slot TIM beregu: buat Result untuk semua anggota tim
                if ($isTeamCategory && str_starts_with((string) $slot['registration_id'], 'team:')) {
                    $teamId = (int) substr($slot['registration_id'], 5);
                    $memberRegIds = Registration::where('team_group_id', $teamId)
                        ->whereNull('deleted_at')
                        ->pluck('id');
                    foreach ($memberRegIds as $memberRegId) {
                        Result::create([
                            'registration_id' => $memberRegId,
                            'rank_name' => !empty($slot['rank_name']) ? $slot['rank_name'] : null,
                            'medal_type' => !empty($slot['medal_type']) ? $slot['medal_type'] : null,
                        ]);
                    }
                    continue;
                }
                // Slot individu (kategori individu, atau fallback data lama)
                Result::create([
                    'registration_id' => $slot['registration_id'],
                    'rank_name' => !empty($slot['rank_name']) ? $slot['rank_name'] : null,
                    'medal_type' => !empty($slot['medal_type']) ? $slot['medal_type'] : null,
                ]);
            }
        }

        session()->flash("success_{$subCategoryId}", 'Hasil berhasil disimpan!');
    }

    /**
     * Cari SubCategory dari id di dalam event yang sedang dikelola.
     */
    private function findSubCategory(int $subCategoryId): ?SubCategory
    {
        foreach ($this->event->categories as $category) {
            foreach ($category->subCategories as $subCategory) {
                if ($subCategory->id === $subCategoryId) {
                    return $subCategory;
                }
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.admin.result-entry');
    }
}
