<x-app-layout>
    @section('title', 'Detail Peserta - ' . $participant->name)

    @if ($hasActiveRegistration && !$participant->is_verified)
        <div
            class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex align-items-center p-5 mb-5">
            <span class="svg-icon svg-icon-2 me-4">
                <x-icon name="info" class="svg-icon-2 me-4" />
            </span>
            <div class="d-flex flex-column">
                <h5 class="mb-1 text-warning">Peserta terdaftar di event</h5>
                <span class="text-gray-600">
                    NIK, tanggal lahir, dan jenis kelamin tidak dapat diubah.
                </span>
            </div>
        </div>
    @elseif($participant->is_verified)
        <div
            class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex align-items-center p-5 mb-5">
            <span class="svg-icon svg-icon-2 me-4">
                <x-icon name="info" class="svg-icon-2 me-4" />
            </span>
            <div class="d-flex flex-column">
                <h5 class="mb-1 text-danger">Data sudah terverifikasi</h5>
                <span class="text-gray-600">
                    Semua field terkunci kecuali foto.
                </span>
            </div>
        </div>
    @endif

    <div class="card mb-5 mb-xl-10">
        <div class="card-body border-0 pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                        @if ($participant->photo)
                            <img src="{{ $participant->photo_url }}" alt="{{ $participant->name }}"
                                class="w-100 h-100 object-fit-cover" />
                        @else
                            <div
                                class="symbol-label fs-1 fw-bolder {{ $participant->type === \App\Enums\ParticipantType::Coach ? 'bg-light-success text-success' : ($participant->type === \App\Enums\ParticipantType::Official ? 'bg-light-info text-info' : 'bg-light-warning text-warning') }}">
                                {{ strtoupper(substr($participant->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-gray-900 fs-2 fw-bolder me-1">
                                    {{ $participant->name }}
                                </span>
                                @if ($participant->type === \App\Enums\ParticipantType::Athlete)
                                    <span class="badge badge-light-primary fw-bolder ms-2 fs-8">Atlet</span>
                                @elseif($participant->type === \App\Enums\ParticipantType::Coach)
                                    <span class="badge badge-light-success fw-bolder ms-2 fs-8">Pelatih</span>
                                @else
                                    <span class="badge badge-light-info fw-bolder ms-2 fs-8">Official</span>
                                @endif
                                @if ($participant->is_verified)
                                    <span class="badge badge-light-success fw-bolder ms-2 fs-8">
                                        <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                    </span>
                                @else
                                    <span class="badge badge-light-warning fw-bolder ms-2 fs-8">Belum</span>
                                @endif
                                @if ($hasActiveRegistration)
                                    <span class="badge badge-light-info fw-bolder ms-2 fs-8">
                                        <i class="bi bi-clipboard-check me-1"></i>Terdaftar Event
                                    </span>
                                @endif
                            </div>
                            <span class="text-muted fw-semibold fs-6">
                                @if ($participant->nik)
                                    NIK: {{ $participant->nik }}
                                @else
                                    {{ $participant->type === \App\Enums\ParticipantType::Coach ? 'Pelatih' : ($participant->type === \App\Enums\ParticipantType::Official ? 'Official' : 'Peserta') }}
                                @endif
                            </span>
                        </div>
                        <div class="d-flex flex-wrap">


                            @if ($hasEditPermission)
                                <a href="{{ route('participants.edit', $participant) }}"
                                    class="btn btn-light-primary btn-sm me-2">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            @endif

                            @if ($hasDeletePermission)
                                <button type="button" class="btn btn-light-danger btn-sm me-2" onclick="confirmDeleteParticipant()">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                                <form action="{{ route('participants.destroy', $participant) }}" method="POST"
                                    id="participant-delete-form" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @elseif(!$hasDeletePermission)
                                <span class="btn btn-light-danger btn-sm me-2 opacity-50 cursor-default"
                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                    title="Anda tidak memiliki izin menghapus peserta ini"
                                    style="cursor: not-allowed;">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </span>
                            @endif

                            <a href="{{ route('participants.index') }}" class="btn btn-light btn-sm">
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-10">
        <div class="col-xl-6">
            <div class="card card-flush h-lg-100">
                <div class="card-header pt-7">
                    <h3 class="card-label fw-bold text-dark">Data Pribadi</h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-primary">Info</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-2 gy-3">
                            <tbody>
                                <tr>
                                    <td class="text-gray-600 fw-bold w-150px">Nama Lengkap</td>
                                    <td class="text-gray-800">{{ $participant->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Jenis</td>
                                    <td>
                                        @if ($participant->type === \App\Enums\ParticipantType::Athlete)
                                            <span class="badge badge-light-primary">Atlet</span>
                                        @elseif($participant->type === \App\Enums\ParticipantType::Coach)
                                            <span class="badge badge-light-success">Pelatih</span>
                                        @else
                                            <span class="badge badge-light-info">Official</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($participant->type === \App\Enums\ParticipantType::Athlete)
                                    <tr>
                                        <td class="text-gray-600 fw-bold">NIK</td>
                                        <td class="text-gray-800">{{ $participant->nik ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-600 fw-bold">Tanggal Lahir</td>
                                        <td class="text-gray-800">
                                            {{ $participant->birth_date?->format('d M Y') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-600 fw-bold">Jenis Kelamin</td>
                                        <td class="text-gray-800">
                                            {{ $participant->gender === \App\Enums\ParticipantGender::Male ? 'Laki-laki' : 'Perempuan' }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-gray-600 fw-bold">Perguruan</td>
                                    <td class="text-gray-800">{{ $participant->institusi ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card card-flush h-lg-100">
                <div class="card-header pt-7">
                    <h3 class="card-label fw-bold text-dark">Dokumen & Verifikasi</h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-info">Status</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @if($participant->rejection_reason)
                        <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                            <i class="bi bi-exclamation-triangle-fill fs-2 text-danger me-4"></i>
                            <div class="d-flex flex-column flex-grow-1">
                                <h4 class="mb-1 text-danger">Dokumen/Verifikasi Ditolak</h4>
                                <span><b>Alasan:</b> {{ $participant->rejection_reason }}</span>
                                <div class="mt-3">
                                    <a href="{{ route('participants.edit', $participant) }}" class="btn btn-sm btn-danger">
                                        <i class="bi bi-pencil-square me-1"></i> Perbarui Data / Upload Berkas
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-2 gy-3">
                            <tbody>
                                <tr>
                                    <td class="text-gray-600 fw-bold w-150px">Status Verifikasi</td>
                                    <td>
                                        @if ($participant->is_verified)
                                            <span class="badge badge-light-success">
                                                <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                            </span>
                                        @elseif($participant->rejection_reason)
                                            <span class="badge badge-light-danger">
                                                <i class="bi bi-x-circle me-1"></i>Ditolak
                                            </span>
                                        @else
                                            <span class="badge badge-light-warning">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($participant->document)
                                    <tr>
                                        <td class="text-gray-600 fw-bold">Dokumen</td>
                                        <td>
                                            <a href="{{ Storage::url($participant->document) }}" target="_blank"
                                                class="btn btn-sm btn-light-primary">
                                                <i class="bi bi-download me-1"></i> Unduh Dokumen
                                            </a>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="text-gray-600 fw-bold">Dokumen</td>
                                        <td class="text-gray-800">-</td>
                                    </tr>
                                @endif
                                @if ($participant->is_verified)
                                    <tr>
                                        <td class="text-gray-600 fw-bold">Diverifikasi oleh</td>
                                        <td class="text-gray-800">{{ $participant->verifiedBy->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-600 fw-bold">Tanggal Verifikasi</td>
                                        <td class="text-gray-800">
                                            {{ $participant->verified_at?->format('d M Y, H:i') ?? '-' }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-gray-600 fw-bold">Terdaftar</td>
                                    <td class="text-gray-800">{{ $participant->created_at->format('d M Y, H:i') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION: Riwayat Pendaftaran Event                           --}}
    {{-- Menampilkan semua event yang pernah diikuti peserta ini.     --}}
    {{-- ============================================================ --}}
    <div class="card mt-5">
        <div class="card-header pt-7">
            <h3 class="card-label fw-bold text-dark">
                <i class="bi bi-clipboard-check me-2"></i>Riwayat Pendaftaran Event
            </h3>
            <div class="card-toolbar">
                <span class="badge badge-light-primary fw-bold">
                    {{ $registrations->count() }} pendaftaran
                </span>
            </div>
        </div>
        <div class="card-body pt-3">

            @if ($registrations->isEmpty())
                <div class="text-center text-muted py-10">
                    <i class="bi bi-clipboard-x fs-2x d-block mb-3 text-gray-400"></i>
                    <p class="fw-semibold text-gray-500 mb-0">
                        Peserta ini belum pernah terdaftar di event manapun.
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-2 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted fs-7 text-uppercase">
                                <th class="min-w-150px">Nama Event</th>
                                <th class="min-w-120px">Tanggal Event</th>
                                <th class="min-w-130px">Kategori / Kelas</th>
                                <th class="min-w-130px">Sub-Kategori</th>
                                <th class="min-w-120px">Status Berkas</th>
                                <th class="min-w-120px">Status Pembayaran</th>
                                <th class="min-w-110px">Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($registrations as $registration)
                                <tr>
                                    <td class="text-gray-800 fw-bold">
                                        {{ $registration->payment->event->name ?? '-' }}
                                    </td>
                                    <td class="text-gray-600">
                                        {{ $registration->payment->event->event_date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="text-gray-600">
                                        {{ $registration->subCategory->eventCategory->class_name ?? '-' }}
                                    </td>
                                    <td class="text-gray-600">
                                        @if ($registration->subCategory)
                                            {{ $registration->subCategory->full_name }}
                                        @else
                                            <span class="text-muted fst-italic">Pelatih</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($registration->status_berkas->value)
                                            @case('verified')
                                                <span class="badge badge-light-success">
                                                    <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                                </span>
                                                @break
                                            @case('pending_review')
                                                <span class="badge badge-light-warning">
                                                    <i class="bi bi-hourglass-split me-1"></i>Menunggu Review
                                                </span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-light-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Ditolak
                                                </span>
                                                @break
                                            @default
                                                <span class="badge badge-light-secondary">
                                                    Belum Disubmit
                                                </span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if ($registration->payment)
                                            @switch($registration->payment->status->value)
                                                @case('verified')
                                                    <span class="badge badge-light-success">
                                                        <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                                    </span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge badge-light-warning">
                                                        <i class="bi bi-clock me-1"></i>Menunggu
                                                    </span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge badge-light-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Ditolak
                                                    </span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-light-secondary">
                                                        <i class="bi bi-slash-circle me-1"></i>Dibatalkan
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge badge-light-secondary">-</span>
                                            @endswitch
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-gray-600">
                                        {{ $registration->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

    {{-- Sertifikat: panitia/super-admin (manage participants) + kontingen (manage own
       participants, scoped ke peserta sendiri oleh authorizeParticipant di controller) --}}
    @if (auth()->user()->can('manage participants') || auth()->user()->can('manage own participants'))
        <div class="card mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Sertifikat</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Satu sertifikat per kategori yang diikuti (pendaftaran terverifikasi & pembayaran lunas)</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                @if ($certificates->isEmpty())
                    <div class="text-center text-muted py-10">
                        <i class="bi bi-award fs-2x d-block mb-3 text-gray-400"></i>
                        <p class="fw-semibold text-gray-500 mb-0">Belum ada sertifikat.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-2 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted fs-7 text-uppercase">
                                    <th class="min-w-150px">Event</th>
                                    <th class="min-w-180px">Kategori</th>
                                    <th class="min-w-120px">Status</th>
                                    <th class="min-w-110px text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($certificates as $cert)
                                    <tr>
                                        <td class="text-gray-800 fw-bold">{{ $cert['event']->name }}</td>
                                        <td class="text-gray-600">{{ $cert['class'] }} — {{ $cert['sub_category'] }}</td>
                                        <td>
                                            @if ($cert['scope']->value === 'champion_gold')
                                                <span class="badge badge-light-warning"><i class="bi bi-medal-fill me-1"></i>{{ $cert['status'] }}</span>
                                            @elseif($cert['scope']->value === 'champion_silver')
                                                <span class="badge badge-light-info"><i class="bi bi-medal-fill me-1"></i>{{ $cert['status'] }}</span>
                                            @elseif($cert['scope']->value === 'champion_bronze' || $cert['scope']->value === 'champion_other')
                                                <span class="badge badge-light-danger"><i class="bi bi-medal-fill me-1"></i>{{ $cert['status'] }}</span>
                                            @else
                                                <span class="badge badge-light-success">{{ $cert['status'] }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('certificates.public.pdf', $cert['registration']) }}"
                                               class="btn btn-sm btn-light-primary" target="_blank">
                                                <i class="bi bi-download"></i> Unduh PDF
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                new bootstrap.Tooltip(el);
            });

            @if (session('success'))
                toastr.success(@js(session('success')));
            @endif
            @if ($errors->has('delete'))
                toastr.error(@js($errors->first('delete')));
            @endif

            window.participantDeletePreviewUrl = "{{ route('participants.delete-preview', $participant) }}";

            function renderDeleteImpactHtml(impact) {
                const c = impact.counts || {};
                const regs = (impact.details && impact.details.registrations) || [];
                const medals = (impact.details && impact.details.results) || [];

                let html = '<div class="text-start">';
                html += '<p class="fw-bold text-danger mb-2">Data berikut akan dihapus permanen:</p>';
                html += '<ul class="small text-start mb-2">'
                    + '<li><b>' + c.registrations + '</b> registrasi pendaftaran</li>'
                    + '<li><b>' + c.results + '</b> hasil/medal pertandingan</li>'
                    + '<li><b>' + c.draft_items + '</b> item keranjang draft</li>'
                    + '</ul>';

                if (medals.length) {
                    html += '<p class="small text-danger mb-1"><b>History pertandingan (medal):</b></p><ul class="small text-start">';
                    medals.forEach(m => html += '<li>' + (m.event || '-') + ' — ' + (m.medal || '-') + '</li>');
                    html += '</ul>';
                }
                if (regs.length) {
                    html += '<p class="small text-muted mb-1"><b>Pendaftaran:</b></p><ul class="small text-start">';
                    regs.forEach(r => html += '<li>' + (r.event || '-') + ' — ' + (r.category || '-') + '</li>');
                    html += '</ul>';
                }

                html += '<p class="small text-muted mt-2 mb-0">Pembayaran &amp; data kontingen lainnya <b>tidak</b> ikut dihapus.</p>';
                html += '</div>';
                return html;
            }

            function confirmDeleteParticipant() {
                Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch(window.participantDeletePreviewUrl, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(impact => {
                        Swal.fire({
                            title: 'Hapus peserta ini?',
                            html: renderDeleteImpactHtml(impact),
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, hapus permanen',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) {
                                document.getElementById('participant-delete-form')?.submit();
                            }
                        });
                    })
                    .catch(() => Swal.fire('Gagal', 'Tidak dapat memuat data. Coba lagi.', 'error'));
            }
        </script>
    @endpush
</x-app-layout>
