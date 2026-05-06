<div>
    @section('title', 'Daftar Pembayaran')

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

        {{-- Header --}}
        <div class="card mb-5">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Daftar Pembayaran</span>
                    <span class="text-muted mt-1 fw-bold fs-7">Kelola pembayaran pendaftaran kontingen Anda</span>
                </h3>
            </div>
        </div>

        {{-- Daftar Payment Cards --}}
        <div class="row g-5">
            @forelse($this->payments as $payment)
                <div class="col-12" wire:key="payment-card-{{ $payment->id }}">
                    <div class="card card-flush shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="fw-bolder text-dark">{{ $payment->event->name }}</span>
                                <span class="text-muted mt-1 fw-bold fs-7">Invoice #{{ $payment->id }} - {{ $payment->created_at->translatedFormat('j F Y, H:i') }}</span>
                            </h3>
                            <div class="card-toolbar">
                                @switch($payment->status)
                                    @case(App\Enums\PaymentStatus::Pending)
                                        <span class="badge badge-light-warning fw-bolder px-4 py-3">Pending</span>
                                        @break
                                    @case(App\Enums\PaymentStatus::Verified)
                                        <span class="badge badge-light-success fw-bolder px-4 py-3">Verified</span>
                                        @break
                                    @case(App\Enums\PaymentStatus::Rejected)
                                        <span class="badge badge-light-danger fw-bolder px-4 py-3">Rejected</span>
                                        @break
                                    @case(App\Enums\PaymentStatus::Cancelled)
                                        <span class="badge badge-light-dark fw-bolder px-4 py-3">Cancelled</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="card-body py-5">
                            <div class="d-flex flex-wrap align-items-center mb-5">
                                <div class="me-10 mb-2">
                                    <span class="text-muted fw-bold d-block">Total Tagihan</span>
                                    <span class="text-gray-800 fw-bolder fs-3">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @if($payment->rejection_reason)
                                <div class="alert alert-danger d-flex align-items-center p-5 mt-3 mb-5">
                                    <i class="bi bi-exclamation-triangle-fill fs-2 text-danger me-4"></i>
                                    <div>
                                        <h4 class="mb-1 text-danger">Alasan Penolakan:</h4>
                                        <span>{{ $payment->rejection_reason }}</span>
                                    </div>
                                </div>
                            @endif

                            @if($payment->transfer_proof)
                                <div class="mt-4 mb-5">
                                    <h5 class="fw-bold mb-3 text-gray-700">Bukti Transfer:</h5>
                                    <div class="symbol symbol-150px symbol-lg-200px">
                                        <img src="{{ Storage::url($payment->transfer_proof) }}" 
                                             alt="Bukti Transfer" 
                                             class="rounded border" 
                                             style="cursor: pointer; object-fit: cover;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#proofModal{{ $payment->id }}">
                                    </div>
                                </div>
                                
                                {{-- Modal fullsize view --}}
                                <div class="modal fade" id="proofModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Bukti Transfer - Invoice #{{ $payment->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center p-0">
                                                <img src="{{ Storage::url($payment->transfer_proof) }}" 
                                                     alt="Bukti Transfer" class="img-fluid">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Tombol dan form upload --}}
                            @if($payment->canUploadProof())
                                @if($uploadingPaymentId === $payment->id)
                                    <div class="separator separator-dashed my-5"></div>
                                    {{-- Form Upload --}}
                                    <form wire:submit="uploadProof" class="mt-4 bg-light p-5 rounded" wire:key="upload-form-{{ $payment->id }}">
                                        <div class="mb-5">
                                            <label class="form-label fw-bold fs-6">Upload Bukti Transfer</label>
                                            <input type="file" 
                                                   wire:model.live="proofFile" 
                                                   class="form-control @error('proofFile') is-invalid @enderror" 
                                                   accept="image/*">
                                            @error('proofFile')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 5MB.</div>
                                            
                                            {{-- Indikator Loading khusus untuk proses upload file ke server --}}
                                            <div wire:loading wire:target="proofFile" class="mt-2 text-primary fw-bold fs-7">
                                                <span class="spinner-border spinner-border-sm me-1"></span> Memproses file...
                                            </div>
                                        </div>
                                        
                                        {{-- Preview sebelum upload --}}
                                        @if($proofFile)
                                            <div class="mb-5">
                                                <span class="text-muted d-block mb-2">Preview:</span>
                                                <img src="{{ $proofFile->temporaryUrl() }}" 
                                                     alt="Preview" 
                                                     class="img-fluid rounded border shadow-sm" 
                                                     style="max-height: 200px;">
                                            </div>
                                        @endif
                                        
                                        <div class="d-flex gap-3">
                                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="proofFile, uploadProof">
                                                <span wire:loading.remove wire:target="uploadProof">
                                                    <i class="bi bi-upload me-1"></i> Upload Sekarang
                                                </span>
                                                <span wire:loading wire:target="uploadProof">
                                                    <span class="spinner-border spinner-border-sm me-1"></span> Mengupload...
                                                </span>
                                            </button>
                                            <button type="button" wire:click="cancelUpload" class="btn btn-light" wire:loading.attr="disabled">Batal</button>
                                        </div>
                                    </form>
                                @else
                                    {{-- Tombol untuk mulai upload --}}
                                    <div class="d-flex gap-3 mt-3">
                                        <button wire:click="startUpload({{ $payment->id }})" class="btn btn-sm btn-primary">
                                            <i class="bi bi-upload me-1"></i>
                                            {{ $payment->transfer_proof ? 'Upload Ulang Bukti' : 'Upload Bukti Transfer' }}
                                        </button>

                                        @if($payment->canBeCancelledByUser())
                                            <button wire:click="cancelPayment({{ $payment->id }})" 
                                                    wire:confirm="Apakah Anda yakin? Semua pendaftaran atlet dan pelatih dalam invoice ini akan dibatalkan secara permanen."
                                                    class="btn btn-sm btn-light-danger">
                                                <i class="bi bi-trash me-1"></i>
                                                Batalkan Pendaftaran
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            <div class="separator separator-dashed my-5"></div>

                            {{-- Daftar registrasi --}}
                            <div class="mt-4">
                                <button class="btn btn-sm btn-light-primary" 
                                   data-bs-toggle="collapse" 
                                   data-bs-target="#registrations{{ $payment->id }}">
                                    <i class="bi bi-list-ul me-1"></i> Lihat Detail Pendaftaran ({{ $payment->registrations->count() }})
                                </button>
                                <div class="collapse mt-5" id="registrations{{ $payment->id }}">
                                    <div class="table-responsive">
                                        <table class="table table-row-bordered table-row-gray-200 align-middle gs-3 gy-3">
                                            <thead>
                                                <tr class="fw-bolder text-muted fs-7 text-uppercase">
                                                    <th>Nama</th>
                                                    <th>Tipe</th>
                                                    <th>Sub-Kategori</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($payment->registrations as $reg)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-gray-800 fw-bolder">{{ $reg->participant->name }}</span>
                                                                    <span class="text-muted fs-7">{{ $reg->participant->nik }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($reg->sub_category_id)
                                                                <span class="badge badge-light-info">Atlet</span>
                                                            @else
                                                                <span class="badge badge-light-primary">Pelatih</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $reg->subCategory?->name ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-20">
                            <i class="bi bi-credit-card fs-5x text-muted mb-5"></i>
                            <h3 class="fw-bolder text-gray-800">Belum Ada Pembayaran</h3>
                            <p class="text-muted fs-5">Anda belum memiliki riwayat pembayaran. Silakan buat pendaftaran terlebih dahulu untuk melihat invoice di sini.</p>
                            <a href="{{ route('registration.index') }}" class="btn btn-primary mt-5">
                                <i class="bi bi-plus-lg me-1"></i> Mulai Pendaftaran
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
