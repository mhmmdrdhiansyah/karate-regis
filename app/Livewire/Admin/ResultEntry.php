<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Result;
use App\Enums\MedalType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ResultEntry extends Component
{
    public Event $event;
    public array $slots = []; // [subCategoryId => [ ['medal_type' => 'Gold', 'registration_id' => 1], ... ]]

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->event->load([
            'categories.subCategories.registrations' => function ($q) {
                $q->whereNull('deleted_at');
            },
            'categories.subCategories.registrations.participant.contingent',
            'categories.subCategories.registrations.teamGroup.contingent',
        ]);

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
            unset($this->slots[$subCategoryId][$index]);
            // Re-index array so Livewire doesn't break
            $this->slots[$subCategoryId] = array_values($this->slots[$subCategoryId]);
        }
    }

    public function save($subCategoryId)
    {
        $slots = $this->slots[$subCategoryId] ?? [];
        
        // Validate duplicates
        $regIds = collect($slots)->pluck('registration_id')->filter(fn($id) => $id !== '');
        if ($regIds->duplicates()->isNotEmpty()) {
            session()->flash("error_{$subCategoryId}", 'Ada peserta yang sama dipilih lebih dari satu kali!');
            return;
        }

        $subCategoryRegIds = Registration::where('sub_category_id', $subCategoryId)->pluck('id');
        
        // Delete existing results for this subcategory
        Result::whereIn('registration_id', $subCategoryRegIds)->delete();
        
        // Save new results
        foreach ($slots as $slot) {
            if (!empty($slot['registration_id']) && (!empty($slot['medal_type']) || !empty($slot['rank_name']))) {
                Result::create([
                    'registration_id' => $slot['registration_id'],
                    'rank_name' => !empty($slot['rank_name']) ? $slot['rank_name'] : null,
                    'medal_type' => !empty($slot['medal_type']) ? $slot['medal_type'] : null,
                ]);
            }
        }
        
        session()->flash("success_{$subCategoryId}", 'Hasil berhasil disimpan!');
    }

    public function render()
    {
        return view('livewire.admin.result-entry');
    }
}
