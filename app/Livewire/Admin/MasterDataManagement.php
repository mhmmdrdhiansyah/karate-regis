<?php

namespace App\Livewire\Admin;

use App\Models\Perguruan;
use App\Models\Sport;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MasterDataManagement extends Component
{
    use WithPagination;

    // Filter
    public string $search = '';
    public ?int $selectedSportId = null;

    // Form Sport
    public string $sportName = '';
    public string $sportCode = '';
    public string $sportDescription = '';
    public ?int $editingSportId = null;

    // Form Perguruan
    public string $perguruanName = '';
    public string $perguruanCode = '';
    public ?int $editingPerguruanId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedSportId' => ['except' => null],
    ];

    #[Computed]
    public function sports()
    {
        return Sport::query()
            ->withCount('perguruan')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function perguruan()
    {
        if (! $this->selectedSportId) {
            return collect();
        }

        return Perguruan::query()
            ->where('sport_id', $this->selectedSportId)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    public function selectSport(int $sportId): void
    {
        $this->selectedSportId = $sportId;
        $this->resetPage();
    }

    public function saveSport(): void
    {
        $validated = $this->validate([
            'sportName' => 'required|string|max:255',
            'sportCode' => 'nullable|string|max:255',
            'sportDescription' => 'nullable|string',
        ]);

        $data = [
            'name' => $validated['sportName'],
            'code' => $validated['sportCode'] ?: null,
            'description' => $validated['sportDescription'] ?: null,
            'is_active' => true,
        ];

        if ($this->editingSportId) {
            Sport::findOrFail($this->editingSportId)->update($data);
            session()->flash('success', 'Cabor berhasil diperbarui.');
        } else {
            Sport::create($data);
            session()->flash('success', 'Cabor berhasil ditambahkan.');
        }

        $this->reset(['sportName', 'sportCode', 'sportDescription', 'editingSportId']);
    }

    public function editSport(int $sportId): void
    {
        $sport = Sport::findOrFail($sportId);
        $this->editingSportId = $sport->id;
        $this->sportName = $sport->name;
        $this->sportCode = $sport->code ?? '';
        $this->sportDescription = $sport->description ?? '';
    }

    public function toggleSportActive(int $sportId): void
    {
        $sport = Sport::findOrFail($sportId);
        $sport->update(['is_active' => ! $sport->is_active]);
    }

    public function deleteSport(int $sportId): void
    {
        $sport = Sport::findOrFail($sportId);

        // Aturan B2: jangan hapus sport yang masih punya perguruan
        // (FK cascadeOnDelete akan ikut menghapus perguruannya)
        if ($sport->perguruan()->exists()) {
            $this->dispatch('swal:error', message: 'Cabor tidak dapat dihapus karena masih memiliki '.$sport->perguruan()->count().' perguruan. Hapus/nonaktifkan perguruannya terlebih dahulu.');
            return;
        }

        $sport->delete();
        if ($this->selectedSportId === $sportId) {
            $this->reset('selectedSportId');
        }
        session()->flash('success', 'Cabor berhasil dihapus.');
    }

    public function savePerguruan(): void
    {
        $validated = $this->validate([
            'selectedSportId' => 'required|integer|exists:sports,id',
            'perguruanName' => 'required|string|max:255',
            'perguruanCode' => 'nullable|string|max:255',
        ]);

        $data = [
            'sport_id' => $validated['selectedSportId'],
            'name' => $validated['perguruanName'],
            'code' => $validated['perguruanCode'] ?: null,
            'is_active' => true,
        ];

        if ($this->editingPerguruanId) {
            Perguruan::findOrFail($this->editingPerguruanId)->update($data);
            session()->flash('success', 'Perguruan berhasil diperbarui.');
        } else {
            Perguruan::create($data);
            session()->flash('success', 'Perguruan berhasil ditambahkan.');
        }

        $this->reset(['perguruanName', 'perguruanCode', 'editingPerguruanId']);
    }

    public function editPerguruan(int $perguruanId): void
    {
        $perguruan = Perguruan::findOrFail($perguruanId);
        $this->editingPerguruanId = $perguruan->id;
        $this->perguruanName = $perguruan->name;
        $this->perguruanCode = $perguruan->code ?? '';
    }

    public function togglePerguruanActive(int $perguruanId): void
    {
        $perguruan = Perguruan::findOrFail($perguruanId);
        $perguruan->update(['is_active' => ! $perguruan->is_active]);
    }

    public function deletePerguruan(int $perguruanId): void
    {
        // Aturan B3: aman langsung hapus — participant menyimpan institusi sebagai string
        $perguruan = Perguruan::findOrFail($perguruanId);
        $perguruan->delete();
        session()->flash('success', 'Perguruan berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.master-data-management');
    }
}
