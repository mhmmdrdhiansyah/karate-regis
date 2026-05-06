@extends('layouts.app')

@section('title', 'Verifikasi Berkas')

@section('content')
<div class="container-xxl py-10">
    {{-- Header --}}
    <div class="mb-10">
        <h1 class="text-gray-900 fw-bolder fs-2">Verifikasi Berkas Atlet</h1>
        <div class="text-muted fw-bold fs-6">Periksa keabsahan dokumen Akta/Ijazah peserta sebelum bertanding. Verifikasi ini bersifat permanen.</div>
    </div>

    {{-- Filters & Search --}}
    <div class="card mb-7">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.documents.index') }}" class="d-flex flex-wrap align-items-center gap-5">
                <div class="position-relative w-md-400px">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-4 fs-4"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-solid ps-12" placeholder="Cari nama, NIK, atau kontingen...">
                </div>

                <div class="w-md-200px">
                    <select name="status" class="form-select form-select-solid" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Belum Terverifikasi</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    </select>
                </div>
                
                @if(request()->has('search') || request()->has('status'))
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-light-primary">Reset Filter</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-200 align-middle gs-3 gy-4">
                    <thead>
                        <tr class="fw-bolder text-muted fs-7 text-uppercase gs-0">
                            <th class="min-w-150px">Atlet</th>
                            <th class="min-w-150px">Kontingen</th>
                            <th class="min-w-150px">NIK</th>
                            <th class="min-w-100px">Tgl Lahir</th>
                            <th class="min-w-100px">Status Berkas</th>
                            <th class="min-w-100px text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-45px me-5">
                                            <img src="{{ $participant->photo_url }}" alt="Photo" style="object-fit: cover; border-radius: 50%;">
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bolder mb-1">{{ $participant->name }}</span>
                                            <span class="text-muted fs-7">
                                                {{ $participant->gender->value === 'M' ? 'Laki-laki' : 'Perempuan' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold">{{ $participant->contingent->name }}</span>
                                        <span class="text-muted fs-7">{{ $participant->contingent->official_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-bold">{{ $participant->nik }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-bold">{{ $participant->birth_date->format('d M Y') }}</span>
                                </td>
                                <td>
                                    @if($participant->is_verified)
                                        <span class="badge badge-light-success">
                                            <i class="bi bi-patch-check-fill text-success me-1"></i> Verified
                                        </span>
                                    @else
                                        <span class="badge badge-light-warning">Menunggu Review</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" 
                                            class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary view-doc-btn"
                                            data-id="{{ $participant->id }}"
                                            data-name="{{ $participant->name }}"
                                            data-contingent="{{ $participant->contingent->name }}"
                                            data-nik="{{ $participant->nik }}"
                                            data-birth-date="{{ $participant->birth_date->format('d M Y') }}"
                                            data-gender="{{ $participant->gender->value === 'M' ? 'Laki-laki' : 'Perempuan' }}"
                                            data-institusi="{{ $participant->institusi ?? '-' }}"
                                            data-doc-url="{{ $participant->document ? Storage::url($participant->document) : '' }}"
                                            data-is-verified="{{ $participant->is_verified ? '1' : '0' }}">
                                        <i class="bi bi-eye-fill fs-3"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <span class="text-muted fs-6">Tidak ada data atlet yang ditemukan.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $participants->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Modal Preview & Verification --}}
<div class="modal fade" id="kt_modal_verify" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verifikasi Dokumen Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row">
                    <div class="col-lg-8">
                        {{-- Document Preview Container --}}
                        <div class="card shadow-sm h-100">
                            <div class="card-body p-0 d-flex align-items-center justify-content-center bg-dark rounded" style="min-height: 500px; overflow: hidden;" id="docPreviewContainer">
                                <span class="spinner-border text-primary" role="status" id="docLoading">
                                    <span class="visually-hidden">Loading...</span>
                                </span>
                                {{-- Will be injected via JS --}}
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h4 class="mb-5 text-gray-800" id="modalAthleteName">Nama Atlet</h4>
                                
                                <div class="d-flex flex-stack mb-5">
                                    <span class="text-muted fw-bold">Kontingen</span>
                                    <span class="text-gray-800 fw-bolder text-end" id="modalContingent">Nama Kontingen</span>
                                </div>

                                <div class="d-flex flex-stack mb-5">
                                    <span class="text-muted fw-bold">NIK</span>
                                    <span class="text-gray-800 fw-bolder text-end" id="modalNIK">-</span>
                                </div>

                                <div class="d-flex flex-stack mb-5">
                                    <span class="text-muted fw-bold">Tgl Lahir</span>
                                    <span class="text-gray-800 fw-bolder text-end" id="modalBirthDate">-</span>
                                </div>

                                <div class="d-flex flex-stack mb-5">
                                    <span class="text-muted fw-bold">Gender</span>
                                    <span class="text-gray-800 fw-bolder text-end" id="modalGender">-</span>
                                </div>

                                <div class="d-flex flex-stack mb-5">
                                    <span class="text-muted fw-bold">Institusi</span>
                                    <span class="text-gray-800 fw-bolder text-end" id="modalInstitusi">-</span>
                                </div>
                                
                                <div class="separator separator-dashed my-5"></div>

                                <div id="actionArea" class="d-none">
                                    <div class="mb-5">
                                        <label class="form-label fw-bold">Tindakan</label>
                                        <p class="text-muted fs-7">Pilih apakah dokumen valid (Akta/Ijazah sesuai dengan identitas dan tanggal lahir) atau tidak. Jika diverifikasi, status ini akan permanen untuk atlet ini.</p>
                                    </div>

                                    <div class="d-grid gap-3 mb-5">
                                        <button type="button" class="btn btn-success" id="btnApprove">
                                            <i class="bi bi-check-lg me-1"></i> Dokumen Valid (Verify)
                                        </button>
                                        <button type="button" class="btn btn-danger" id="btnShowRejectForm">
                                            <i class="bi bi-x-lg me-1"></i> Tolak Dokumen
                                        </button>
                                    </div>

                                    {{-- Reject Form (Hidden by default) --}}
                                    <div id="rejectFormContainer" class="d-none mt-5 p-5 bg-light-danger rounded border border-danger border-dashed">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-danger">Alasan Penolakan (Wajib)</label>
                                            <textarea id="rejectionReason" class="form-control" rows="3" placeholder="Contoh: Dokumen buram, atau Akta palsu..."></textarea>
                                            <div class="invalid-feedback" id="rejectError"></div>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-light" id="btnCancelReject">Batal</button>
                                            <button type="button" class="btn btn-sm btn-danger" id="btnSubmitReject">Submit Penolakan</button>
                                        </div>
                                    </div>
                                </div>

                                <div id="infoArea" class="d-none">
                                    <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                                        <i class="bi bi-check-circle-fill fs-2x text-success me-3"></i>
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-success">Verified</h4>
                                            <span>Dokumen atlet ini telah diperiksa dan dinyatakan sah.</span>
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <button type="button" class="btn btn-light-danger btn-sm" id="btnShowRevokeForm">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Revoke Verifikasi
                                        </button>
                                    </div>

                                    {{-- Revoke Form (Hidden by default) --}}
                                    <div id="revokeFormContainer" class="d-none mt-5 p-5 bg-light-danger rounded border border-danger border-dashed">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-danger">Alasan Pencabutan (Wajib)</label>
                                            <textarea id="revokeReason" class="form-control" rows="3" placeholder="Jelaskan mengapa verifikasi dicabut..."></textarea>
                                            <div class="invalid-feedback" id="revokeError"></div>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-light" id="btnCancelRevoke">Batal</button>
                                            <button type="button" class="btn btn-sm btn-danger" id="btnSubmitRevoke">Konfirmasi Revoke</button>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('kt_modal_verify');
        const verifyModal = new bootstrap.Modal(modalEl);
        let currentParticipantId = null;

        // Elements
        const docPreviewContainer = document.getElementById('docPreviewContainer');
        const docLoading = document.getElementById('docLoading');
        const actionArea = document.getElementById('actionArea');
        const infoArea = document.getElementById('infoArea');
        const rejectFormContainer = document.getElementById('rejectFormContainer');
        const rejectionReasonInput = document.getElementById('rejectionReason');
        const rejectError = document.getElementById('rejectError');
        const revokeFormContainer = document.getElementById('revokeFormContainer');
        const revokeReasonInput = document.getElementById('revokeReason');
        const revokeError = document.getElementById('revokeError');

        // Buttons
        const btnApprove = document.getElementById('btnApprove');
        const btnShowRejectForm = document.getElementById('btnShowRejectForm');
        const btnCancelReject = document.getElementById('btnCancelReject');
        const btnSubmitReject = document.getElementById('btnSubmitReject');
        const btnShowRevokeForm = document.getElementById('btnShowRevokeForm');
        const btnCancelRevoke = document.getElementById('btnCancelRevoke');
        const btnSubmitRevoke = document.getElementById('btnSubmitRevoke');

        // Setup CSRF token for fetch
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Open Modal
        document.querySelectorAll('.view-doc-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const contingent = this.getAttribute('data-contingent');
                const nik = this.getAttribute('data-nik');
                const birthDate = this.getAttribute('data-birth-date');
                const gender = this.getAttribute('data-gender');
                const institusi = this.getAttribute('data-institusi');
                const docUrl = this.getAttribute('data-doc-url');
                const isVerified = this.getAttribute('data-is-verified') === '1';

                currentParticipantId = id;
                
                // Set details
                document.getElementById('modalAthleteName').textContent = name;
                document.getElementById('modalContingent').textContent = contingent;
                document.getElementById('modalNIK').textContent = nik;
                document.getElementById('modalBirthDate').textContent = birthDate;
                document.getElementById('modalGender').textContent = gender;
                document.getElementById('modalInstitusi').textContent = institusi;

                // Reset states
                docPreviewContainer.innerHTML = '';
                docPreviewContainer.appendChild(docLoading);
                docLoading.classList.remove('d-none');
                rejectFormContainer.classList.add('d-none');
                revokeFormContainer.classList.add('d-none');
                rejectionReasonInput.value = '';
                rejectionReasonInput.classList.remove('is-invalid');
                revokeReasonInput.value = '';
                revokeReasonInput.classList.remove('is-invalid');
                btnApprove.classList.remove('d-none');
                btnShowRejectForm.classList.remove('d-none');
                btnShowRevokeForm.classList.remove('d-none');

                // Toggle Action vs Info area
                if (!isVerified) {
                    actionArea.classList.remove('d-none');
                    infoArea.classList.add('d-none');
                } else {
                    actionArea.classList.add('d-none');
                    infoArea.classList.remove('d-none');
                }

                // Render Document (PDF vs Image)
                setTimeout(() => {
                    docLoading.classList.add('d-none');
                    if (!docUrl) {
                        docPreviewContainer.innerHTML = `
                            <div class="text-center p-10">
                                <i class="bi bi-file-earmark-x fs-5x text-muted mb-5"></i>
                                <h3 class="text-gray-400">Belum Ada Dokumen</h3>
                                <p class="text-muted">Atlet ini belum mengunggah dokumen Akta/Ijazah.</p>
                            </div>
                        `;
                        // Sembunyikan tombol approve jika dokumen kosong
                        if (!isVerified) {
                            btnApprove.classList.add('d-none');
                        }
                    } else {
                        const extension = docUrl.split('.').pop().toLowerCase();
                        if (extension === 'pdf') {
                            docPreviewContainer.innerHTML = `<iframe src="${docUrl}" style="width: 100%; height: 75vh; border: none;"></iframe>`;
                        } else {
                            docPreviewContainer.innerHTML = `<img src="${docUrl}" class="img-fluid" style="max-height: 75vh; object-fit: contain;">`;
                        }
                    }
                }, 300);

                verifyModal.show();
            });
        });

        // Toggle Reject Form
        btnShowRejectForm.addEventListener('click', () => {
            rejectFormContainer.classList.remove('d-none');
            btnApprove.classList.add('d-none');
            btnShowRejectForm.classList.add('d-none');
        });

        btnCancelReject.addEventListener('click', () => {
            rejectFormContainer.classList.add('d-none');
            btnApprove.classList.remove('d-none');
            btnShowRejectForm.classList.remove('d-none');
        });

        // Toggle Revoke Form
        btnShowRevokeForm.addEventListener('click', () => {
            revokeFormContainer.classList.remove('d-none');
            btnShowRevokeForm.classList.add('d-none');
        });

        btnCancelRevoke.addEventListener('click', () => {
            revokeFormContainer.classList.add('d-none');
            btnShowRevokeForm.classList.remove('d-none');
        });

        // Approve Action
        btnApprove.addEventListener('click', function() {
            // Confirm Dialog
            Swal.fire({
                title: 'Verifikasi Dokumen?',
                text: "Anda menyatakan dokumen ini valid. Status Verified akan berlaku permanen untuk atlet ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Verifikasi!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-light"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    processAction('approve');
                }
            });
        });

        // Reject Action
        btnSubmitReject.addEventListener('click', function() {
            const reason = rejectionReasonInput.value.trim();
            if (reason.length < 5) {
                rejectionReasonInput.classList.add('is-invalid');
                rejectError.textContent = 'Alasan penolakan wajib diisi (min. 5 karakter).';
                return;
            }
            rejectionReasonInput.classList.remove('is-invalid');
            processAction('reject', reason);
        });

        // Revoke Action
        btnSubmitRevoke.addEventListener('click', function() {
            const reason = revokeReasonInput.value.trim();
            if (reason.length < 5) {
                revokeReasonInput.classList.add('is-invalid');
                revokeError.textContent = 'Alasan pencabutan wajib diisi (min. 5 karakter).';
                return;
            }
            revokeReasonInput.classList.remove('is-invalid');
            processAction('revoke', reason);
        });

        // AJAX Process
        function processAction(action, reason = '') {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const url = `/admin/documents/${currentParticipantId}/${action}`;
            const data = (action === 'reject' || action === 'revoke') ? { rejection_reason: reason } : {};

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(result => {
                if (result.status >= 200 && result.status < 300) {
                    verifyModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: result.body.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload(); // Reload to update table
                    });
                } else {
                    Swal.fire('Error', result.body.message || 'Terjadi kesalahan pada server.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Gagal menghubungi server.', 'error');
                console.error(error);
            });
        }
    });
</script>
@endpush
