<div>
    @section('title', 'Manajemen Peserta')

    <div class="container-xxl py-10">
        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center p-5 mb-10" role="alert">
                <i class="bi bi-check-circle-fill fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-success">Berhasil</h4>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center p-5 mb-10" role="alert">
                <i class="bi bi-exclamation-octagon-fill fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Gagal</h4>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filters & Search --}}
        <div class="card mb-7">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-5">
                    {{-- Search --}}
                    <div class="position-relative w-md-300px">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-4 fs-4"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-solid ps-12" placeholder="Cari nama, NIK, atau kontingen...">
                    </div>

                    {{-- Filter Type --}}
                    <div class="w-md-150px">
                        <select wire:model.live="typeFilter" class="form-select form-select-solid">
                            <option value="">Semua Jenis</option>
                            <option value="athlete">Atlet</option>
                            <option value="coach">Pelatih</option>
                            <option value="official">Official</option>
                        </select>
                    </div>

                    {{-- Filter Verification --}}
                    <div class="w-md-150px">
                        <select wire:model.live="verificationFilter" class="form-select form-select-solid">
                            <option value="">Semua Status</option>
                            <option value="verified">Verified</option>
                            <option value="unverified">Belum Verified</option>
                        </select>
                    </div>

                    {{-- Filter Province --}}
                    <div class="w-md-200px">
                        <select wire:model.live="provinceFilter" class="form-select form-select-solid">
                            <option value="">Semua Provinsi</option>
                            @foreach($this->provinces as $province)
                                <option value="{{ $province }}">{{ $province }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($search || $typeFilter || $verificationFilter || $provinceFilter)
                        <button wire:click="$set('search', ''); $set('typeFilter', ''); $set('verificationFilter', ''); $set('provinceFilter', '')" class="btn btn-light-primary">Reset</button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Daftar Peserta (Global)</span>
                    <span class="text-muted mt-1 fw-bold fs-7">Melihat dan memverifikasi seluruh peserta dari semua kontingen</span>
                </h3>
            </div>
            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-200 align-middle gs-3 gy-4">
                        <thead>
                            <tr class="fw-bolder text-muted fs-7 text-uppercase gs-0">
                                <th class="min-w-150px cursor-pointer" wire:click="sortBy('name')">
                                    Peserta @if($sortField === 'name') <i class="bi bi-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                                </th>
                                <th class="min-w-150px cursor-pointer" wire:click="sortBy('nik')">
                                    NIK @if($sortField === 'nik') <i class="bi bi-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                                </th>
                                <th class="min-w-100px cursor-pointer" wire:click="sortBy('type')">
                                    Jenis @if($sortField === 'type') <i class="bi bi-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                                </th>
                                <th class="min-w-150px cursor-pointer" wire:click="sortBy('contingent')">
                                    Kontingen @if($sortField === 'contingent') <i class="bi bi-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                                </th>
                                <th class="min-w-100px cursor-pointer" wire:click="sortBy('is_verified')">
                                    Status @if($sortField === 'is_verified') <i class="bi bi-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                                </th>
                                <th class="min-w-120px cursor-pointer" wire:click="sortBy('created_at')">
                                    Terdaftar @if($sortField === 'created_at') <i class="bi bi-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                                </th>
                                <th class="min-w-50px text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->participants as $participant)
                                <tr wire:key="participant-{{ $participant->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-45px me-5">
                                                <img src="{{ $participant->photo_url }}" alt="Photo" style="object-fit: cover; border-radius: 50%;">
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bolder mb-1">{{ $participant->name }}</span>
                                                <span class="text-muted fs-7">{{ $participant->gender?->value === 'M' ? 'Laki-laki' : 'Perempuan' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fw-bold">{{ $participant->nik ?? '-' }}</span>
                                        @if(in_array($participant->nik, $duplicateNiks))
                                            <span class="badge badge-light-danger ms-2" title="NIK ini digunakan oleh peserta lain">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Duplikat
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($participant->type === \App\Enums\ParticipantType::Athlete)
                                            <span class="badge badge-light-primary">Atlet</span>
                                        @elseif($participant->type === \App\Enums\ParticipantType::Coach)
                                            <span class="badge badge-light-success">Pelatih</span>
                                        @else
                                            <span class="badge badge-light-info">Official</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold">{{ $participant->contingent->name }}</span>
                                            <span class="text-muted fs-7">{{ $participant->contingent->province ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($participant->is_verified)
                                            <span class="badge badge-light-success">
                                                <i class="bi bi-patch-check-fill text-success me-1"></i> Verified
                                            </span>
                                        @else
                                            <span class="badge badge-light-warning">Menunggu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted fs-7">{{ $participant->created_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <button wire:click="selectParticipant({{ $participant->id }})" 
                                                class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#kt_modal_verify">
                                            <i class="bi bi-eye-fill fs-3"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-10">
                                        <span class="text-muted fs-6">Tidak ada data peserta ditemukan.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    <x-livewire-pagination :paginator="$this->participants" />
                </div>
            </div>
        </div>

        {{-- Verification Modal --}}
        <div wire:ignore.self class="modal fade" id="kt_modal_verify" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Verifikasi Dokumen Peserta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-light">
                        @if($selectedParticipantId)
                            @php $p = \App\Models\Participant::with('contingent')->find($selectedParticipantId); @endphp
                            @if($p)
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-body p-0 d-flex align-items-center justify-content-center bg-dark rounded" style="min-height: 500px;">
                                                @if($p->document)
                                                    @php $ext = pathinfo($p->document, PATHINFO_EXTENSION); @endphp
                                                    @if(strtolower($ext) === 'pdf')
                                                        <iframe src="{{ Storage::url($p->document) }}" style="width: 100%; height: 600px; border: none;"></iframe>
                                                    @else
                                                        <img src="{{ Storage::url($p->document) }}" class="img-fluid" style="max-height: 600px; object-fit: contain;">
                                                    @endif
                                                @else
                                                    <div class="text-center p-10">
                                                        <i class="bi bi-file-earmark-x fs-5x text-muted mb-5"></i>
                                                        <h3 class="text-gray-400">Belum Ada Dokumen</h3>
                                                        <p class="text-muted">Peserta ini belum mengunggah dokumen Akta/Ijazah.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-body">
                                                <h4 class="mb-5 text-gray-800">{{ $p->name }}</h4>
                                                
                                                <div class="d-flex flex-stack mb-5">
                                                    <span class="text-muted fw-bold">Kontingen</span>
                                                    <span class="text-gray-800 fw-bolder text-end">{{ $p->contingent->name }}</span>
                                                </div>

                                                <div class="d-flex flex-stack mb-5">
                                                    <span class="text-muted fw-bold">NIK</span>
                                                    <span class="text-gray-800 fw-bolder text-end">{{ $p->nik ?? '-' }}</span>
                                                </div>

                                                <div class="d-flex flex-stack mb-5">
                                                    <span class="text-muted fw-bold">Tgl Lahir</span>
                                                    <span class="text-gray-800 fw-bolder text-end">{{ $p->birth_date?->format('d/m/Y') ?? '-' }}</span>
                                                </div>

                                                <div class="separator separator-dashed my-5"></div>

                                                @if(!$p->is_verified)
                                                    <div class="mb-5">
                                                        <label class="form-label fw-bold">Tindakan</label>
                                                        <p class="text-muted fs-7">Verifikasi dokumen ini akan berlaku permanen untuk peserta ini di semua event.</p>
                                                    </div>

                                                    <div class="d-grid gap-3 mb-5">
                                                        <button wire:click="approve" wire:loading.attr="disabled" class="btn btn-success">
                                                            <i class="bi bi-check-lg me-1"></i> Approve Dokumen
                                                        </button>
                                                        
                                                        <div class="p-4 bg-light-danger rounded border border-danger border-dashed">
                                                            <label class="form-label fw-bold text-danger">Tolak Dokumen</label>
                                                            <textarea wire:model="rejectionReason" class="form-control form-control-solid mb-3" rows="3" placeholder="Alasan penolakan..."></textarea>
                                                            @error('rejectionReason') <div class="text-danger fs-7 mb-2">{{ $message }}</div> @enderror
                                                            <button wire:click="reject" wire:loading.attr="disabled" class="btn btn-danger btn-sm w-100">Submit Penolakan</button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                                                        <i class="bi bi-check-circle-fill fs-2x text-success me-3"></i>
                                                        <div class="d-flex flex-column">
                                                            <h4 class="mb-1 text-success">Verified</h4>
                                                            <span class="fs-7">Diverifikasi oleh {{ $p->verifiedBy?->name }} pada {{ $p->verified_at?->format('d/m/Y') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 bg-light-danger rounded border border-danger border-dashed mt-10">
                                                        <label class="form-label fw-bold text-danger">Revoke Verifikasi</label>
                                                        <textarea wire:model="rejectionReason" class="form-control form-control-solid mb-3" rows="3" placeholder="Alasan revoke..."></textarea>
                                                        @error('rejectionReason') <div class="text-danger fs-7 mb-2">{{ $message }}</div> @enderror
                                                        <button wire:click="revoke" wire:loading.attr="disabled" class="btn btn-outline btn-outline-danger btn-active-light-danger btn-sm w-100">Revoke Approval</button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('close-modal', () => {
            var modalEl = document.getElementById('kt_modal_verify');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        });

        Livewire.on('swal:error', (event) => {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: event.message,
            });
        });
    });
</script>
@endpush
