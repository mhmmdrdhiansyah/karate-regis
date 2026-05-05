<x-app-layout>
    @section('title', 'Pendaftaran Pelatih')

    <div class="card mb-5">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-dark">Pendaftaran Pelatih</span>
                <span class="text-muted mt-1 fw-semibold fs-7">Pilih event dan daftarkan pelatih kontingen Anda</span>
            </h3>
        </div>

        <div class="card-body py-5">
            {{-- Error Message --}}
            @if ($errorMessage)
                <div class="alert alert-danger d-flex align-items-center mb-7">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>{{ $errorMessage }}</div>
                </div>
            @endif

            {{-- Event Selection --}}
            <div class="fv-row mb-7">
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

            {{-- Coach List --}}
            @if ($selectedEventId)
                <div class="fv-row">
                    <label class="form-label fs-6 fw-bold mb-3">Daftar Pelatih</label>

                    @if ($this->coaches->count() > 0)
                        @php
                            $hasConfirmedCoaches = collect($this->coaches)->contains(fn($coach) => in_array($coach->id, $this->confirmedCoachIds));
                        @endphp

                        <div class="border rounded p-4 bg-light">
                            @foreach ($this->coaches as $coach)
                                @php
                                    $isRegistered = in_array($coach->id, $this->registeredCoachIds);
                                    $isConfirmed = in_array($coach->id, $this->confirmedCoachIds);
                                @endphp

                                <div class="d-flex align-items-center py-3 @if(!$loop->last) border-bottom @endif">
                                    <div class="form-check form-check-solid form-check-custom me-4">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               value="{{ $coach->id }}"
                                               wire:model.live="selectedCoachIds"
                                               @if ($isConfirmed) disabled @endif
                                               id="coach_{{ $coach->id }}" />
                                    </div>

                                    <div class="symbol symbol-50px symbol-circle me-4">
                                        @if ($coach->photo)
                                            <img src="{{ $coach->photo_url }}"
                                                 alt="{{ $coach->name }}"
                                                 class="object-fit-cover" />
                                        @else
                                            <div class="symbol-label bg-light-success text-success fs-3">
                                                {{ strtoupper(substr($coach->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="fs-6 fw-bold text-gray-800 me-2">{{ $coach->name }}</span>
                                            @if ($isRegistered && !$isConfirmed)
                                                <span class="badge badge-light-success">Terdaftar</span>
                                            @elseif ($isConfirmed)
                                                <span class="badge badge-light-primary">Invoice Confirmed</span>
                                            @endif
                                        </div>
                                        @if ($coach->nik)
                                            <span class="text-muted fs-7">NIK: {{ $coach->nik }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($hasConfirmedCoaches)
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle"></i>
                                Pelatih yang sudah masuk invoice confirmed tidak dapat diubah.
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-10">
                            <i class="bi bi-people fs-1 mb-3 d-block"></i>
                            <span class="fw-semibold">Belum ada data pelatih</span>
                            <p class="text-muted fs-7 mt-2">
                                Silakan tambahkan pelatih terlebih dahulu di menu Bank Peserta.
                            </p>
                            <a href="{{ route('participants.create') }}" class="btn btn-light-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Pelatih
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
