<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Enums\ParticipantType;
use Livewire\Component;
use Livewire\WithPagination;

class ParticipantList extends Component
{
    use WithPagination;

    public $search = '';
    public $type = 'all';
    public $perPage = 15;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => 'all'],
        'perPage' => ['except' => 15],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $contingent = auth()->user()->contingent;

        if (!$contingent) {
            abort(403, 'Akun Anda tidak terkait dengan kontingen.');
        }

        $query = $contingent->participants()
            ->withCount(['registrations' => fn($q) => $q->whereNull('deleted_at')])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('nik', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type !== 'all', function ($q) {
                $q->where('type', $this->type);
            });

        // Handle sorting
        if ($this->sortField === 'type') {
            $query->orderBy('type', $this->sortDirection);
        } elseif ($this->sortField === 'is_verified') {
            $query->orderBy('is_verified', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $participants = $query->paginate($this->perPage);

        return view('livewire.participant-list', [
            'participants' => $participants,
            'canCreate' => auth()->user()->can('create participants')
        ]);
    }
}
