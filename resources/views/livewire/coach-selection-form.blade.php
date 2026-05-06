<div>
    {{-- Header Info --}}
    <div class="card shadow-sm mb-6">
        <div class="card-body p-6">
            <h2 class="fs-2 fw-bolder text-dark mb-1">
                Pendaftaran Pelatih
            </h2>
            <p class="text-muted fw-bold fs-6 mb-5">Pilih event dan daftarkan pelatih kontingen Anda</p>

            <div class="fv-row">
                <label class="required form-label fs-6 fw-bold mb-3">Pilih Event</label>
                <select class="form-select form-select-solid form-select-lg"
                        wire:model.live="selectedEventId">
                    <option value="">-- Pilih Event --</option>
                    @foreach ($this->events as $event)
                        <option value="{{ $event->id }}">
                            {{ $event->name }} ({{ $event->event_date->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Error Message --}}
    @if ($errorMessage)
        @include('partials.error-alert', ['message' => $errorMessage])
    @endif

    {{-- Saved Indicator --}}
    @if ($showSavedIndicator)
        <div class="alert alert-success d-flex align-items-center p-4 mb-6">
            <i class="fas fa-check-circle fs-2 text-success me-3"></i>
            <div class="text-success fw-bold">Tersimpan</div>
        </div>
    @endif

    @if ($selectedEventId)
        @php
            $event = $this->selectedEvent;
            $selectedCount = count($selectedCoachIds);
        @endphp

        <div class="row g-8">
            <div class="col-xl-8">
                {{-- Search Input --}}
                <div class="mb-4">
                    <div class="input-group input-group-solid">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" wire:model.live="search" class="form-control form-control-solid"
                            placeholder="Cari nama pelatih..." autofocus>
                    </div>
                </div>

                {{-- Daftar Pelatih --}}
                <div class="card shadow-sm mb-8">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 text-primary">
                                <i class="fas fa-user-tie text-primary me-2"></i> Daftar Pelatih
                            </span>
                            <span class="text-muted mt-1 fw-bold fs-7">{{ $this->coaches->count() }} pelatih tersedia</span>
                        </h3>
                    </div>
                    <div class="card-body pt-4">
                        @forelse($this->coaches as $coach)
                            @php
                                $isRegistered = in_array($coach->id, $this->registeredCoachIds);
                                $isConfirmed = in_array($coach->id, $this->confirmedCoachIds);
                            @endphp
                            <label wire:key="coach-{{ $coach->id }}"
                                class="d-flex flex-stack mb-4 cursor-pointer bg-hover-light p-3 rounded border border-dashed border-gray-300">

                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-5">
                                        <img src="{{ $coach->photo_url }}" alt="{{ $coach->name }}"
                                            class="border border-gray-200 object-fit-cover">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bolder text-gray-800 text-hover-primary fs-5">
                                            {{ $coach->name }}
                                        </span>
                                        <div class="d-flex align-items-center mt-1">
                                            @if ($coach->nik)
                                                <span class="text-muted fw-bold fs-7 me-3">NIK: {{ $coach->nik }}</span>
                                            @endif
                                            @if ($isRegistered && !$isConfirmed)
                                                <span class="badge badge-light-success fw-bolder">Terdaftar</span>
                                            @elseif ($isConfirmed)
                                                <span class="badge badge-light-primary fw-bolder">Invoice Confirmed</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="form-check form-check-solid form-check-custom form-check-success form-switch">
                                        <input class="form-check-input w-45px h-25px" type="checkbox"
                                            wire:model.live="selectedCoachIds" value="{{ $coach->id }}"
                                            id="coach_{{ $coach->id }}"
                                            @if ($isConfirmed) disabled @endif>
                                        <label class="form-check-label" for="coach_{{ $coach->id }}"></label>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="text-center py-10 bg-light-secondary rounded border border-dashed">
                                <i class="fas fa-box-open fs-3x text-muted mb-4"></i>
                                <div class="fs-5 fw-bold text-gray-600">
                                    {{ $search ? 'Tidak ditemukan pelatih yang sesuai pencarian.' : 'Belum ada data pelatih. Silakan tambahkan pelatih di Bank Peserta.' }}
                                </div>
                                @if (!$search)
                                    <div class="mt-4">
                                        <a href="{{ route('participants.index') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i> Tambah Pelatih
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card shadow-sm sticky-summary">
                    <div class="card-header">
                        <h3 class="card-title fw-bolder">Ringkasan</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-stack mb-5">
                            <span class="text-gray-600 fw-bold fs-5">Pelatih Terpilih:</span>
                            <span class="fw-bolder fs-3">
                                <span class="{{ $selectedCount === 0 ? 'text-muted' : 'text-success' }}">
                                    {{ $selectedCount }}
                                </span>
                            </span>
                        </div>

                        <div class="separator separator-dashed my-6"></div>

                        {{-- Selected coaches preview --}}
                        @if ($selectedCount > 0)
                            <div class="mb-5">
                                <div class="text-muted fw-bold fs-7 mb-2">PELATIH TERDAFTAR</div>
                                @foreach ($this->coaches->whereIn('id', $selectedCoachIds) as $coach)
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-user-check text-success fs-6 me-2"></i>
                                        <span class="text-gray-700 fs-7 fw-bold">{{ $coach->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="separator separator-dashed my-5"></div>

                            <div class="d-flex flex-stack mb-5">
                                <span class="text-gray-600 fw-bold fs-6">Biaya Pelatih:</span>
                                <span class="fw-bolder fs-4 text-dark">
                                    Rp {{ number_format($event->coach_fee * $selectedCount, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-muted fs-8 mb-5">
                                * Biaya per pelatih: Rp {{ number_format($event->coach_fee, 0, ',', '.') }}
                            </div>
                        @endif

                        <div class="d-flex flex-column gap-4">
                            <a href="{{ route('registration.index') }}" wire:navigate
                                class="btn btn-primary w-100 fw-bolder">
                                Kembali ke Pendaftaran
                            </a>
                            <a href="{{ route('registration.invoice', ['event' => $selectedEventId]) }}" wire:navigate
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
    @endif
</div>

