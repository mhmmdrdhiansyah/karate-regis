<div>
    @if (session('error'))
        @include('partials.error-alert', ['message' => session('error')])
    @endif

    @if ($errorMessage)
        @include('partials.error-alert', ['message' => $errorMessage, 'title' => 'Terjadi Kesalahan'])
    @endif

    <div class="card shadow-sm mb-6">
        <div class="card-body p-6">
            <h2 class="fw-bolder text-dark mb-2">Invoice Pendaftaran Event</h2>
            <div class="text-muted fw-bold fs-6">
                {{ $this->event->name }}
                <span class="mx-2 text-gray-300">|</span>
                {{ $this->event->event_date->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    <div class="row g-6">
        <div class="col-xl-8">
            <div class="card shadow-sm mb-6">
                <div class="card-header">
                    <h3 class="card-title fw-bolder">Ringkasan Biaya</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-stack mb-4">
                        <div>
                            <div class="fw-bolder">Biaya Event</div>
                            <div class="text-muted fs-7">Sekali per kontingen</div>
                        </div>
                        <div class="fw-bolder">Rp {{ number_format($this->event->event_fee, 0, ',', '.') }}</div>
                    </div>

                    <div class="separator separator-dashed my-6"></div>

                    <div class="mb-4">
                        <div class="fw-bolder mb-2">Atlet</div>
                        @if($this->athleteSelections->count() > 0)
                            <div class="d-flex flex-column gap-4">
                                @foreach ($this->athleteSelections as $selection)
                                    <div>
                                        <div class="text-muted fs-7 mb-2">Sub-kategori: {{ $selection['subCategory']->name }}</div>
                                        @if ($selection['subCategory']->isTeam())
                                            @php
                                                $teams = collect($selection['athletes'])->groupBy('team_group_id');
                                            @endphp
                                            @foreach ($teams as $teamId => $teamAthletes)
                                                @php
                                                    $teamName = \App\Models\TeamGroup::find($teamId)?->team_name ?? 'Tim';
                                                @endphp
                                                <div class="mb-3 ps-4 border-start border-2 border-primary">
                                                    <div class="fw-bolder fs-7 text-dark mb-1">{{ $teamName }}</div>
                                                    <div class="d-flex flex-column gap-1">
                                                        @foreach ($teamAthletes as $athlete)
                                                            <div class="d-flex flex-stack fs-8">
                                                                <span class="text-gray-600">
                                                                    <i class="fas fa-check text-success me-2 fs-9"></i>
                                                                    {{ $athlete['participant']->name }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="d-flex flex-stack mt-2">
                                                        <span class="text-muted fs-9 italic">Biaya Flat (Tim)</span>
                                                        <span class="fw-bold fs-8 text-gray-800">Rp {{ number_format($selection['subCategory']->price, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="d-flex flex-column gap-2">
                                                @foreach ($selection['athletes'] as $athlete)
                                                    <div class="d-flex flex-stack">
                                                        <span class="text-gray-700">
                                                            <i class="fas fa-user-check me-2 text-primary"></i>
                                                            {{ $athlete['participant']->name }}
                                                        </span>
                                                        <span class="text-gray-600">
                                                            Rp {{ number_format($selection['subCategory']->price, 0, ',', '.') }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="d-flex flex-stack mt-3 pt-3 border-top border-gray-200 border-dashed">
                                            <span class="fw-bold text-gray-700">Subtotal {{ $selection['subCategory']->isTeam() ? 'Beregu' : 'Individu' }}</span>
                                            <span class="fw-bolder">
                                                @php
                                                    if ($selection['subCategory']->isTeam()) {
                                                        $teamCount = collect($selection['athletes'])->pluck('team_group_id')->filter()->unique()->count();
                                                        $teamCount = max($teamCount, 1);
                                                        $subtotal = $selection['subCategory']->price * $teamCount;
                                                    } else {
                                                        $subtotal = $selection['subCategory']->price * count($selection['athletes']);
                                                    }
                                                @endphp
                                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex flex-stack mt-4">
                                <span class="fw-bold text-gray-700">Total Atlet</span>
                                <span class="fw-bolder">Rp {{ number_format($this->totalAthleteFee, 0, ',', '.') }}</span>
                            </div>
                        @else
                            <div class="text-muted">Belum ada atlet dipilih.</div>
                        @endif
                    </div>

                    <div class="separator separator-dashed my-6"></div>

                    <div class="mb-2">
                        <div class="fw-bolder mb-2">Pelatih</div>
                        @if($this->coaches->count() > 0)
                            <div class="d-flex flex-column gap-2">
                                @foreach($this->coaches as $coach)
                                    <div class="d-flex flex-stack">
                                        <span class="text-gray-700">{{ $coach->name }}</span>
                                        <span class="text-gray-600">Rp {{ number_format($this->event->coach_fee, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex flex-stack mt-4">
                                <span class="fw-bold text-gray-700">Subtotal Pelatih</span>
                                <span class="fw-bolder">Rp {{ number_format($this->totalCoachFee, 0, ',', '.') }}</span>
                            </div>
                        @else
                            <div class="text-muted">Belum ada pelatih dipilih.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-sm sticky-summary">
                <div class="card-header">
                    <h3 class="card-title fw-bolder">Total Invoice</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-stack mb-5">
                        <span class="text-gray-600 fw-bold fs-6">Biaya Event</span>
                        <span class="fw-bolder">Rp {{ number_format($this->event->event_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex flex-stack mb-5">
                        <span class="text-gray-600 fw-bold fs-6">Subtotal Atlet</span>
                        <span class="fw-bolder">Rp {{ number_format($this->totalAthleteFee, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex flex-stack mb-5">
                        <span class="text-gray-600 fw-bold fs-6">Subtotal Pelatih</span>
                        <span class="fw-bolder">Rp {{ number_format($this->totalCoachFee, 0, ',', '.') }}</span>
                    </div>
                    <div class="separator separator-dashed my-5"></div>
                    <div class="d-flex flex-stack">
                        <span class="fw-bolder fs-5">Total</span>
                        <span class="fw-bolder fs-3 text-primary">Rp {{ number_format($this->totalAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex flex-column gap-4 mt-6">
                        <button wire:click="confirmSubmit" class="btn btn-primary fw-bolder">
                            <i class="fas fa-check me-2"></i> Konfirmasi Invoice
                        </button>
                        <a href="{{ route('registration.index') }}" wire:navigate class="btn btn-light-danger fw-bold">
                            Batalkan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showConfirmation ? 'show d-block' : '' }}" tabindex="-1" aria-hidden="{{ !$showConfirmation }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bolder">Konfirmasi Invoice</h3>
                    <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" wire:click="cancelConfirmation">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @if ($errorMessage)
                        <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                            <i class="fas fa-exclamation-triangle text-danger fs-2hx me-4"></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Gagal Membuat Invoice</h4>
                                <span>{{ $errorMessage }}</span>
                            </div>
                        </div>
                    @endif

                    <p class="text-muted mb-4">Pastikan data atlet dan pelatih sudah sesuai sebelum membuat invoice.</p>
                    <div class="d-flex flex-stack">
                        <span class="fw-bold">Total yang harus dibayar</span>
                        <span class="fw-bolder text-primary">Rp {{ number_format($this->totalAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-bold" wire:click="cancelConfirmation">Kembali</button>
                    <button type="button" class="btn btn-primary fw-bolder" 
                        wire:click="submit" 
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">
                            <i class="fas fa-check me-2"></i> Buat Invoice
                        </span>
                        <span wire:loading wire:target="submit">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($showConfirmation)
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
