<div>
    <!-- Header Info -->
    <div class="card shadow-sm mb-6">
        <div class="card-body p-6">
            <h2 class="fs-2 fw-bolder text-dark mb-1">
                Pilih Atlet — <span class="text-primary">{{ $this->subCategory->name }}</span>
            </h2>
            <div class="text-muted fw-bold fs-6 mb-5">
                {{ $this->subCategory->eventCategory->event->name }}
                <span class="mx-2 text-gray-300">|</span>
                {{ $this->subCategory->eventCategory->type->value }}
                <span class="mx-2 text-gray-300">|</span>
                {{ $this->subCategory->eventCategory->class_name }}
            </div>

            {{-- Info jumlah peserta yang dibutuhkan --}}
            @if ($this->subCategory->isTeam())
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4">
                    <i class="fas fa-users fs-2tx text-warning me-4 mt-1"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold mb-1">Kategori Beregu</h4>
                            <div class="fs-6 text-gray-700">Pilih minimal {{ $this->subCategory->min_participants }} dan
                                maksimal {{ $this->subCategory->max_participants }} atlet.</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-4">
                    <i class="fas fa-user fs-2tx text-info me-4 mt-1"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold mb-1">Kategori Individu</h4>
                            <div class="fs-6 text-gray-700">Pilih tepat 1 atlet.</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Error Message --}}
    @if ($errorMessage)
        @include('partials.error-alert', ['message' => $errorMessage])
    @endif

    @if ($showSavedIndicator)
        <div class="alert alert-success d-flex align-items-center p-4 mb-6">
            <i class="fas fa-check-circle fs-2 text-success me-3"></i>
            <div class="text-success fw-bold">Tersimpan</div>
        </div>
    @endif

    @php
        $selectedCount = count($selectedAthleteIds);
        $maxParticipants = $this->subCategory->max_participants;
    @endphp

    <div class="row g-8">
        <div class="col-xl-8">
            @if ($this->subCategory->isTeam())
                {{-- === MODE BEREGU === --}}
                <div class="mb-6 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="fw-bolder text-dark mb-0">Manajemen Tim</h3>
                        <span class="text-muted fs-7">Buat tim dan masukkan atlet ke dalam tim.</span>
                    </div>
                    @php
                        $canAddTeam = count($teams) < $this->subCategory->max_teams;
                    @endphp
                    <button wire:click="createTeam" class="btn btn-primary fw-bolder"
                        {{ !$canAddTeam ? 'disabled' : '' }}>
                        <i class="fas fa-plus me-2"></i>
                        Tambah Tim ({{ count($teams) }}/{{ $this->subCategory->max_teams }})
                    </button>
                </div>

                @forelse($teams as $team)
                    <div wire:key="team-{{ $team['id'] }}"
                        class="card shadow-sm mb-4 border {{ $activeTeamId === $team['id'] ? 'border-primary border-dashed' : 'border-gray-300' }}">
                        <div class="card-header cursor-pointer py-4" wire:click="selectTeam({{ $team['id'] }})">
                            <div class="card-title">
                                <span class="symbol symbol-35px me-3">
                                    <span class="symbol-label bg-light-primary text-primary fw-bold">
                                        {{ $team['number'] }}
                                    </span>
                                </span>
                                <div class="d-flex align-items-center" onclick="event.stopPropagation();">
                                    <input type="text" 
                                        class="form-control form-control-flush fw-bolder fs-4 p-0 w-auto min-w-150px" 
                                        value="{{ $team['name'] }}"
                                        onkeydown="if(event.key === 'Enter') { this.blur(); }"
                                        onblur="@this.updateTeamName({{ $team['id'] }}, this.value)"
                                        placeholder="Nama Tim..."
                                    >
                                    <i class="fas fa-edit ms-2 text-gray-400 fs-9"></i>
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <span
                                    class="badge {{ count($team['memberIds']) >= $this->subCategory->min_participants ? 'badge-light-success' : 'badge-light-warning' }} fw-bolder fs-7 px-4 py-3">
                                    {{ count($team['memberIds']) }}/{{ $this->subCategory->max_participants }} Atlet
                                </span>
                                <i
                                    class="fas fa-chevron-{{ $activeTeamId === $team['id'] ? 'up' : 'down' }} ms-4 text-gray-400"></i>
                            </div>
                        </div>

                        @if ($activeTeamId === $team['id'])
                            <div class="card-body py-5">
                                {{-- Search & Filter for Team Mode --}}
                                <div class="mb-5">
                                    <div class="input-group input-group-sm input-group-solid">
                                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" wire:model.live="search" class="form-control"
                                            placeholder="Cari nama atlet...">
                                    </div>
                                </div>

                                <div class="row g-4">
                                    @forelse($this->eligibleAthletes as $athlete)
                                        @php
                                            $isInThisTeam = in_array($athlete->id, $team['memberIds']);
                                            $isInOtherTeam = !$isInThisTeam && $this->isAthleteInAnyTeam($athlete->id);
                                            $isFull = count($team['memberIds']) >= $this->subCategory->max_participants;
                                        @endphp
                                        <div class="col-md-6">
                                            <label
                                                class="d-flex flex-stack p-4 rounded border border-dashed {{ $isInOtherTeam ? 'bg-light opacity-50' : 'bg-hover-light cursor-pointer' }} {{ $isInThisTeam ? 'border-primary bg-light-primary' : 'border-gray-300' }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px me-3">
                                                        <img src="{{ $athlete->photo_url }}" alt="{{ $athlete->name }}">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span
                                                            class="fw-bold text-gray-800 fs-7">{{ $athlete->name }}</span>
                                                        @if ($isInOtherTeam)
                                                            <span class="badge badge-light-danger fs-9 px-1">Sudah di tim
                                                                lain</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="form-check form-check-solid form-switch">
                                                    <input type="checkbox" class="form-check-input h-20px w-35px"
                                                        wire:click="toggleTeamMember({{ $athlete->id }})"
                                                        {{ $isInThisTeam ? 'checked' : '' }}
                                                        {{ $isInOtherTeam || (!$isInThisTeam && $isFull) ? 'disabled' : '' }}>
                                                </div>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-5 text-muted">Tidak ada atlet tersedia</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="card-footer py-4 d-flex justify-content-end">
                                <button wire:click="deleteTeam({{ $team['id'] }})"
                                    wire:confirm="Hapus tim ini beserta semua anggotanya?"
                                    class="btn btn-sm btn-light-danger fw-bolder">
                                    <i class="fas fa-trash me-2"></i> Hapus Tim
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="card shadow-sm border-dashed border-gray-400 bg-light-secondary">
                        <div class="card-body text-center py-15">
                            <i class="fas fa-users-cog fs-3x text-muted mb-5"></i>
                            <h3 class="fw-bolder text-gray-700">Belum Ada Tim</h3>
                            <p class="text-muted mb-6">Klik tombol "Tambah Tim" untuk mulai mendaftarkan atlet ke kategori
                                beregu ini.</p>
                            <button wire:click="createTeam" class="btn btn-primary fw-bolder">
                                <i class="fas fa-plus me-2"></i> Buat Tim Pertama
                            </button>
                        </div>
                    </div>
                @endforelse
            @else
                {{-- === MODE INDIVIDU === --}}
                {{-- Search Input --}}
                <div class="mb-4">
                    <div class="input-group input-group-solid">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" wire:model.live="search" class="form-control form-control-solid"
                            placeholder="Cari nama atlet..." autofocus>
                    </div>
                </div>

                {{-- Daftar Atlet Eligible (Checkbox) --}}
                <div class="card shadow-sm mb-8">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 text-success">
                                <i class="fas fa-check-circle text-success me-2"></i> Atlet Memenuhi Syarat
                            </span>
                            <span class="text-muted mt-1 fw-bold fs-7">{{ $this->eligibleAthletes->count() }} atlet
                                tersedia</span>
                        </h3>
                    </div>
                    <div class="card-body pt-4">
                        @forelse($this->eligibleAthletes as $athlete)
                            @php
                                $isSelected = in_array($athlete->id, $selectedAthleteIds, true);
                                $isLimitReached = $selectedCount >= $maxParticipants;
                            @endphp
                            <label wire:key="athlete-{{ $athlete->id }}"
                                class="d-flex flex-stack mb-4 cursor-pointer bg-hover-light p-3 rounded border border-dashed border-gray-300">

                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-5">
                                        <img src="{{ $athlete->photo_url }}" alt="{{ $athlete->name }}"
                                            class="border border-gray-200 athlete-photo">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span
                                            class="fw-bolder text-gray-800 text-hover-primary fs-5">{{ $athlete->name }}</span>

                                        {{-- Registration Preview Badges --}}
                                        <div class="d-flex flex-wrap gap-1 mt-1 mb-1">
                                            @php
                                                $otherRegs = collect();
                                                foreach ($athlete->draftItems as $item) {
                                                    if ($item->sub_category_id && $item->sub_category_id !== $this->subCategoryId) {
                                                        $otherRegs->push(['name' => $item->subCategory->name, 'type' => 'draft']);
                                                    }
                                                }
                                                foreach ($athlete->registrations as $reg) {
                                                    if ($reg->sub_category_id && $reg->sub_category_id !== $this->subCategoryId) {
                                                        $otherRegs->push(['name' => $reg->subCategory->name, 'type' => 'active']);
                                                    }
                                                }
                                            @endphp

                                            @forelse($otherRegs as $reg)
                                                <span class="badge badge-light-{{ $reg['type'] === 'active' ? 'success' : 'info' }} fw-bold fs-9 py-1 px-2"
                                                    title="{{ $reg['type'] === 'active' ? 'Sudah Terdaftar (Invoice)' : 'Dalam Draf' }}">
                                                    {{ $reg['name'] }}
                                                </span>
                                            @empty
                                                <span class="text-muted fs-8 fst-italic">Belum terdaftar di kategori
                                                    lain</span>
                                            @endforelse
                                        </div>

                                        <span class="text-muted fw-bold fs-7">
                                            {{ $athlete->gender->value === 'M' ? 'Putra' : 'Putri' }}
                                            <span class="mx-1">&bull;</span> Lahir:
                                            {{ $athlete->birth_date->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end align-items-center">
                                    <div
                                        class="form-check form-check-solid form-check-custom form-check-success form-switch">
                                        <input class="form-check-input w-45px h-25px" type="checkbox"
                                            wire:model.live="selectedAthleteIds" value="{{ $athlete->id }}"
                                            id="athlete_{{ $athlete->id }}"
                                            {{ !$isSelected && $isLimitReached ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="athlete_{{ $athlete->id }}"></label>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="text-center py-10 bg-light-secondary rounded border border-dashed">
                                <i class="fas fa-box-open fs-3x text-muted mb-4"></i>
                                <div class="fs-5 fw-bold text-gray-600">
                                    {{ $search ? 'Tidak ditemukan atlet yang sesuai pencarian.' : 'Tidak ada atlet yang memenuhi syarat untuk sub-kategori ini.' }}
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Toggle & Daftar Atlet TIDAK Eligible --}}
            @if ($this->ineligibleAthletes->count() > 0)
                <button wire:click="toggleIneligible"
                    class="btn btn-flex btn-text btn-color-muted btn-active-color-primary w-auto mb-4">
                    <i class="fas fa-chevron-{{ $showIneligible ? 'up' : 'down' }} me-2 fs-6"></i>
                    <span class="fs-6 fw-bold">Tampilkan atlet tidak memenuhi syarat
                        ({{ $this->ineligibleAthletes->count() }})</span>
                </button>

                @if ($showIneligible)
                    <div class="card shadow-sm border border-dashed mb-6">
                        <div class="card-header border-0 pt-5 pb-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-4 text-danger">
                                    <i class="fas fa-times-circle text-danger me-2"></i> Atlet Tidak Memenuhi Syarat
                                </span>
                                <span class="text-muted mt-1 fw-bold fs-7">{{ $this->ineligibleAthletes->count() }}
                                    atlet</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                @foreach ($this->ineligibleAthletes as $athlete)
                                    <div class="col-md-6" wire:key="ineligible-{{ $athlete->id }}">
                                        <div class="d-flex align-items-center p-3 rounded bg-light opacity-75">
                                            <div class="symbol symbol-40px me-4">
                                                <img src="{{ $athlete->photo_url }}" alt="{{ $athlete->name }}"
                                                    class="athlete-photo-ineligible">
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bolder text-gray-600 fs-6">{{ $athlete->name }}</span>

                                                {{-- Registration Preview Badges --}}
                                                <div class="d-flex flex-wrap gap-1 mt-1 mb-1">
                                                    @php
                                                        $otherRegs = collect();
                                                        foreach ($athlete->draftItems as $item) {
                                                            if ($item->sub_category_id && $item->sub_category_id !== $this->subCategoryId) {
                                                                $otherRegs->push(['name' => $item->subCategory->name, 'type' => 'draft']);
                                                            }
                                                        }
                                                        foreach ($athlete->registrations as $reg) {
                                                            if ($reg->sub_category_id && $reg->sub_category_id !== $this->subCategoryId) {
                                                                $otherRegs->push(['name' => $reg->subCategory->name, 'type' => 'active']);
                                                            }
                                                        }
                                                    @endphp

                                                    @forelse($otherRegs as $reg)
                                                        <span class="badge badge-light-{{ $reg['type'] === 'active' ? 'success' : 'info' }} fw-bold fs-9 py-1 px-2"
                                                            title="{{ $reg['type'] === 'active' ? 'Sudah Terdaftar (Invoice)' : 'Dalam Draf' }}">
                                                            {{ $reg['name'] }}
                                                        </span>
                                                    @empty
                                                        <span class="text-muted fs-8 fst-italic">Belum terdaftar</span>
                                                    @endforelse
                                                </div>

                                                <span
                                                    class="text-danger fw-bold fs-8">{{ $athlete->ineligible_reason }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <div class="col-xl-4">
            <div class="card shadow-sm sticky-summary">
                <div class="card-header">
                    <h3 class="card-title fw-bolder">Ringkasan</h3>
                </div>
                <div class="card-body">
                    @if ($this->subCategory->isTeam())
                        {{-- Ringkasan Mode Beregu --}}
                        <div class="d-flex flex-stack mb-5">
                            <span class="text-gray-600 fw-bold fs-5">Total Tim:</span>
                            <span class="fw-bolder fs-3">
                                <span class="{{ count($teams) === 0 ? 'text-danger' : 'text-success' }}">
                                    {{ count($teams) }}
                                </span>
                                <span class="text-muted fs-6">/ {{ $this->subCategory->max_teams }}</span>
                            </span>
                        </div>

                        @foreach ($teams as $team)
                            <div class="mb-4 p-3 bg-light rounded border border-dashed {{ $activeTeamId === $team['id'] ? 'border-primary' : 'border-gray-300' }}">
                                <div class="d-flex flex-stack mb-1">
                                    <span class="fw-bolder text-gray-800">{{ $team['name'] }}</span>
                                    <span class="badge {{ count($team['memberIds']) >= $this->subCategory->min_participants ? 'badge-light-success' : 'badge-light-warning' }} fs-9">
                                        {{ count($team['memberIds']) }}/{{ $this->subCategory->max_participants }}
                                    </span>
                                </div>
                                <div class="text-muted fs-8">
                                    @php
                                        $members = $this->eligibleAthletes->whereIn('id', $team['memberIds'])->pluck('name');
                                    @endphp
                                    {{ $members->isNotEmpty() ? $members->join(', ') : 'Belum ada anggota' }}
                                </div>
                            </div>
                        @endforeach

                        <div class="separator separator-dashed my-6"></div>

                        <div class="d-flex flex-stack mb-5">
                            <span class="text-gray-600 fw-bold fs-6">Biaya (Flat per Tim):</span>
                            <span class="fw-bolder fs-4 text-dark text-end">
                                {{ count($teams) }} × Rp {{ number_format($this->subCategory->price, 0, ',', '.') }}<br>
                                <span class="fs-3 text-primary">= Rp {{ number_format($this->subCategory->price * count($teams), 0, ',', '.') }}</span>
                            </span>
                        </div>
                    @else
                        {{-- Ringkasan Mode Individu --}}
                        <div class="d-flex flex-stack mb-5">
                            <span class="text-gray-600 fw-bold fs-5">Total Terpilih:</span>
                            <span class="fw-bolder fs-3">
                                <span
                                    class="{{ count($selectedAthleteIds) === 0 ? 'text-danger' : (count($selectedAthleteIds) < $this->subCategory->min_participants ? 'text-warning' : 'text-success') }}">
                                    {{ count($selectedAthleteIds) }}
                                </span>
                                <span class="text-muted fs-6">/ {{ $this->subCategory->max_participants }}</span>
                            </span>
                        </div>

                        @if (count($selectedAthleteIds) > 0 && count($selectedAthleteIds) < $this->subCategory->min_participants)
                            <div class="notice d-flex bg-light-warning rounded p-3 mb-5">
                                <i class="fas fa-info-circle text-warning me-3 mt-1"></i>
                                <span class="fs-7 text-gray-700">Pilih minimal
                                    {{ $this->subCategory->min_participants - count($selectedAthleteIds) }} atlet
                                    lagi</span>
                            </div>
                        @endif

                        <div class="separator separator-dashed my-6"></div>

                        {{-- Selected athletes preview --}}
                        @if (count($selectedAthleteIds) > 0)
                            <div class="mb-5">
                                <div class="text-muted fw-bold fs-7 mb-2">ATLET TERPILIH</div>
                                @foreach ($this->eligibleAthletes->whereIn('id', $selectedAthleteIds) as $athlete)
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-user-check text-success fs-6 me-2"></i>
                                        <span class="text-gray-700 fs-7">{{ $athlete->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="separator separator-dashed my-5"></div>

                            <div class="d-flex flex-stack mb-5">
                                <span class="text-gray-600 fw-bold fs-6">Biaya:</span>
                                <span class="fw-bolder fs-4 text-dark text-end">
                                    Rp {{ number_format($this->subCategory->price * count($selectedAthleteIds), 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    @endif

                    <div class="d-flex flex-column gap-4">
                        <a href="{{ route('registration.index') }}" wire:navigate
                            class="btn btn-primary w-100 fw-bolder">
                            Kembali ke Pendaftaran
                        </a>
                        <a href="{{ route('registration.invoice', ['event' => $eventId]) }}" wire:navigate
                            class="btn btn-light-primary w-100 fw-bolder">
                            Lanjut ke Invoice
                        </a>
                        
                        <button type="button" wire:click="clearDraft" 
                            wire:confirm="Apakah Anda yakin ingin menghapus semua pilihan (atlet & pelatih) di draf untuk event ini?"
                            class="btn btn-light-danger w-100 fw-bolder mt-2">
                            <i class="bi bi-trash3 me-2"></i> Kosongkan Draf
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
