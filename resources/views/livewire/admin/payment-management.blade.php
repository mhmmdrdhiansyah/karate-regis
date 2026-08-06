<div>
    @section('title', 'Manajemen Pembayaran')

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
                <div class="row g-4">
                    {{-- Search --}}
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fs-7 fw-bolder text-gray-700 text-uppercase">Cari Invoice / Kontingen</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-4 fs-5 text-gray-500"></i>
                            <input type="text" wire:model.live="search" class="form-control form-control-solid ps-12" placeholder="Nama kontingen / ID #...">
                        </div>
                    </div>

                    {{-- Event Filter --}}
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fs-7 fw-bolder text-gray-700 text-uppercase">Event</label>
                        <select wire:model.live="eventId" class="form-select form-select-solid">
                            <option value="">Semua Event</option>
                            @foreach($this->events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label fs-7 fw-bolder text-gray-700 text-uppercase">Status</label>
                        <select wire:model.live="statusFilter" class="form-select form-select-solid">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    {{-- Type Filter (Open/Festival) --}}
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label fs-7 fw-bolder text-gray-700 text-uppercase">Tipe Kategori</label>
                        <select wire:model.live="typeFilter" class="form-select form-select-solid">
                            <option value="">Semua Tipe</option>
                            @foreach($this->availableTypes as $type)
                                <option value="{{ is_object($type) ? $type->value : $type }}">{{ is_object($type) ? $type->value : $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Class Filter (Usia Dini, Cadet, etc.) --}}
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label fs-7 fw-bolder text-gray-700 text-uppercase">Class / Kelas Umur</label>
                        <select wire:model.live="classFilter" class="form-select form-select-solid">
                            <option value="">Semua Class</option>
                            @foreach($this->availableClasses as $class)
                                <option value="{{ $class }}">{{ $class }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sub-Category Filter --}}
                    <div class="col-md-8 col-lg-6">
                        <label class="form-label fs-7 fw-bolder text-gray-700 text-uppercase">Sub-Kategori</label>
                        <select wire:model.live="subCategoryFilter" class="form-select form-select-solid">
                            <option value="">Semua Sub-Kategori</option>
                            @foreach($this->availableSubCategories as $subCat)
                                <option value="{{ $subCat->id }}">
                                    [{{ $subCat->eventCategory?->type?->value ?? 'Category' }} - {{ $subCat->eventCategory?->class_name }}] {{ $subCat->name }} ({{ $subCat->gender->value === 'M' ? 'Putra' : ($subCat->gender->value === 'F' ? 'Putri' : 'Campuran') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payments Table --}}
        <div class="card">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Daftar Pembayaran</span>
                    <span class="text-muted mt-1 fw-bold fs-7">Verifikasi bukti transfer dari kontingen</span>
                </h3>
            </div>
            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-200 align-middle gs-3 gy-4">
                        <thead>
                            <tr class="fw-bolder text-muted fs-7 text-uppercase gs-0">
                                <th class="min-w-50px">ID</th>
                                <th class="min-w-150px">Kontingen</th>
                                <th class="min-w-150px">Event</th>
                                <th class="min-w-100px">Total</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-120px">Tanggal</th>
                                <th class="min-w-100px text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->payments as $payment)
                                <tr wire:key="payment-row-{{ $payment->id }}">
                                    <td>
                                        <span class="text-gray-800 fw-bolder">#{{ $payment->id }}</span>
                                    </td>
                                    <td>
                                        @if($payment->contingent)
                                            @if($payment->contingent->trashed())
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bolder mb-1">{{ $payment->contingent->name }}</span>
                                                    <span class="badge badge-light-danger fs-7">Kontingen Dihapus</span>
                                                </div>
                                            @else
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bolder mb-1">{{ $payment->contingent->name }}</span>
                                                    <span class="text-muted fs-7">{{ $payment->contingent->official_name }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-danger fw-bolder">Kontingen Hilang (ID: {{ $payment->contingent_id }})</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($payment->event)
                                            <span class="text-gray-800 fw-bold">{{ $payment->event->name }}</span>
                                        @else
                                            <span class="text-danger fw-bolder">Event Terhapus (ID: {{ $payment->event_id }})</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fw-bolder">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        @switch($payment->status)
                                            @case(App\Enums\PaymentStatus::Pending)
                                                <span class="badge badge-light-warning">Pending</span>
                                                @break
                                            @case(App\Enums\PaymentStatus::Verified)
                                                <span class="badge badge-light-success">Verified</span>
                                                @break
                                            @case(App\Enums\PaymentStatus::Rejected)
                                                <span class="badge badge-light-danger">Rejected</span>
                                                @break
                                            @case(App\Enums\PaymentStatus::Cancelled)
                                                <span class="badge badge-light-dark">Cancelled</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <span class="text-muted fs-7">{{ $payment->created_at->translatedFormat('j F Y, H:i') }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($payment->transfer_proof)
                                            <button wire:click="selectPayment({{ $payment->id }})" 
                                                    class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#kt_modal_proof">
                                                <i class="bi bi-eye-fill fs-3"></i>
                                            </button>
                                        @else
                                            <span class="text-muted fs-8 italic">No proof</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-10">
                                        <span class="text-muted fs-6">Tidak ada data pembayaran ditemukan.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    <x-livewire-pagination :paginator="$this->payments" />
                </div>
            </div>
        </div>

        {{-- Modal Proof View & Actions --}}
        <div wire:ignore.self class="modal fade" id="kt_modal_proof" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Verifikasi Pembayaran #{{ $selectedPaymentId }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($selectedPaymentId)
                            @php $currentPayment = \App\Models\Payment::find($selectedPaymentId); @endphp
                            @if($currentPayment)
                                <div class="row">
                                    <div class="col-md-7">
                                        <h6 class="fw-bolder mb-3 text-uppercase fs-7 text-muted">Bukti Transfer:</h6>
                                        <div class="bg-light p-3 rounded text-center">
                                            <img src="{{ Storage::url($currentPayment->transfer_proof) }}" 
                                                 alt="Bukti Transfer" 
                                                 class="img-fluid rounded shadow-sm"
                                                 style="max-height: 500px;">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="card bg-light shadow-none border-0 mb-5">
                                            <div class="card-body p-5">
                                                <h6 class="fw-bolder mb-3">Detail Invoice:</h6>
                                                <div class="d-flex flex-stack mb-2">
                                                    <span class="text-muted fw-bold">Kontingen:</span>
                                                    @if($currentPayment->contingent)
                                                        @if($currentPayment->contingent->trashed())
                                                            <div class="text-end">
                                                                <span class="text-gray-800 fw-bolder">{{ $currentPayment->contingent->name }}</span>
                                                                <span class="badge badge-light-danger fs-7 ms-2">Dihapus</span>
                                                            </div>
                                                        @else
                                                            <span class="text-gray-800 fw-bolder">{{ $currentPayment->contingent->name }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-danger fw-bolder">Kontingen Hilang (ID: {{ $currentPayment->contingent_id }})</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-stack mb-2">
                                                    <span class="text-muted fw-bold">Total:</span>
                                                    <span class="text-gray-800 fw-bolder">Rp {{ number_format($currentPayment->total_amount, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="d-flex flex-stack mb-2">
                                                    <span class="text-muted fw-bold">Status:</span>
                                                    <span class="badge badge-light-{{ $currentPayment->status->value === 'pending' ? 'warning' : ($currentPayment->status->value === 'verified' ? 'success' : 'danger') }} fw-bolder">
                                                        {{ ucfirst($currentPayment->status->value) }}
                                                    </span>
                                                </div>

                                                <div class="separator separator-dashed my-4"></div>
                                                <h6 class="fw-bolder mb-2 text-uppercase fs-8 text-muted">Kategori Terdaftar:</h6>
                                                <div class="max-h-200px overflow-y-auto pe-2">
                                                    @php
                                                        $currentPayment->loadMissing(['registrations.subCategory.eventCategory', 'registrations.participant', 'registrations.teamGroup']);
                                                    @endphp
                                                    @forelse($currentPayment->registrations as $reg)
                                                        <div class="border-bottom border-gray-200 py-2 fs-7">
                                                            @if($reg->subCategory)
                                                                <div class="fw-bolder text-gray-800">
                                                                    [{{ $reg->subCategory->eventCategory?->type?->value ?? 'Cat' }}] {{ $reg->subCategory->eventCategory?->class_name }}
                                                                </div>
                                                                 <div class="text-muted fs-8">
                                                                     Sub: <strong class="text-primary">{{ $reg->subCategory->full_name }}</strong>
                                                                 </div>
                                                                <div class="text-gray-600 fs-8">
                                                                    Peserta: {{ $reg->teamGroup?->name ?? $reg->participant?->name ?? '-' }}
                                                                </div>
                                                            @else
                                                                <div class="fw-bolder text-info">
                                                                    [Biaya Pelatih / Offical]
                                                                </div>
                                                                <div class="text-gray-600 fs-8">
                                                                    Peserta: {{ $reg->participant?->name ?? '-' }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <div class="text-muted fs-8 italic">Tidak ada data rincian pendaftaran.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        @if($currentPayment->status === \App\Enums\PaymentStatus::Pending)
                                            <div class="mb-5">
                                                <label class="form-label fw-bold">Alasan Penolakan (Jika ditolak)</label>
                                                <textarea wire:model="rejectionReason" class="form-control form-control-solid @error('rejectionReason') is-invalid @enderror" rows="3" placeholder="Contoh: Bukti transfer tidak jelas atau nominal tidak sesuai..."></textarea>
                                                @error('rejectionReason')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="d-grid gap-3">
                                                <button wire:click="approve" wire:loading.attr="disabled" class="btn btn-success">
                                                    <span wire:loading.remove wire:target="approve">
                                                        <i class="bi bi-check-lg me-1"></i> Verifikasi & Approve
                                                    </span>
                                                    <span wire:loading wire:target="approve">
                                                        <span class="spinner-border spinner-border-sm me-1"></span> Memproses...
                                                    </span>
                                                </button>
                                                <button wire:click="reject" wire:loading.attr="disabled" class="btn btn-danger">
                                                    <span wire:loading.remove wire:target="reject">
                                                        <i class="bi bi-x-lg me-1"></i> Tolak Pembayaran
                                                    </span>
                                                    <span wire:loading wire:target="reject">
                                                        <span class="spinner-border spinner-border-sm me-1"></span> Memproses...
                                                    </span>
                                                </button>
                                            </div>
                                        @elseif($currentPayment->status === \App\Enums\PaymentStatus::Verified)
                                            <div class="separator separator-dashed my-5"></div>
                                            
                                            <div class="mb-5">
                                                <label class="form-label fw-bold text-danger">Alasan Revoke (Wajib)</label>
                                                <textarea wire:model="rejectionReason" class="form-control form-control-solid @error('rejectionReason') is-invalid @enderror" rows="3" placeholder="Alasan mencabut verifikasi..."></textarea>
                                                @error('rejectionReason')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="d-grid">
                                                <button wire:click="revoke" wire:loading.attr="disabled" class="btn btn-outline btn-outline-danger btn-active-light-danger">
                                                    <span wire:loading.remove wire:target="revoke">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Revoke Approval
                                                    </span>
                                                    <span wire:loading wire:target="revoke">
                                                        <span class="spinner-border spinner-border-sm me-1"></span> Memproses...
                                                    </span>
                                                </button>
                                                <div class="form-text text-danger mt-2 fs-8 italic">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Tindakan ini akan mengembalikan status ke Pending dan membatalkan status berkas atlet yang belum diverifikasi admin.
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info d-flex align-items-center p-5">
                                                <i class="bi bi-info-circle-fill fs-2x text-info me-3"></i>
                                                <span class="fs-7">Pembayaran ini sudah dalam status <strong>{{ ucfirst($currentPayment->status->value) }}</strong>. Tidak ada aksi yang diperlukan.</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('swal:error', event => {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: event.detail.message,
        });
    });

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('payment-processed', (event) => {
            var modalEl = document.getElementById('kt_modal_proof');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        });
    });
</script>
@endpush
