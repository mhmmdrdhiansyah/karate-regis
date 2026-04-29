<x-app-layout>
    @section('title', 'Detail Peserta - ' . $participant->name)

    @if($hasActiveRegistration && !$participant->is_verified)
        <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex align-items-center p-5 mb-5">
            <span class="svg-icon svg-icon-2 me-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path opacity="0.3"
                        d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z"
                        fill="currentColor" />
                    <path d="M13 7H11V13H17V11H13V7Z" fill="currentColor" />
                </svg>
            </span>
            <div class="d-flex flex-column">
                <h5 class="mb-1 text-warning">Peserta terdaftar di event</h5>
                <span class="text-gray-600">
                    NIK, tanggal lahir, dan jenis kelamin tidak dapat diubah. Peserta tidak dapat dihapus.
                </span>
            </div>
        </div>
    @elseif($participant->is_verified)
        <div class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex align-items-center p-5 mb-5">
            <span class="svg-icon svg-icon-2 me-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path opacity="0.3"
                        d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z"
                        fill="currentColor" />
                    <path d="M13 7H11V13H17V11H13V7Z" fill="currentColor" />
                </svg>
            </span>
            <div class="d-flex flex-column">
                <h5 class="mb-1 text-danger">Data sudah terverifikasi</h5>
                <span class="text-gray-600">
                    Semua field terkunci kecuali foto. Peserta tidak dapat dihapus.
                </span>
            </div>
        </div>
    @endif

    <div class="card mb-5 mb-xl-10">
        <div class="card-body border-0 pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                        @if($participant->photo)
                            <img src="{{ Storage::url($participant->photo) }}" alt="{{ $participant->name }}"
                                class="w-100 h-100 object-fit-cover" />
                        @else
                            <div class="symbol-label fs-1 fw-bolder {{ $participant->type === \App\Enums\ParticipantType::Coach ? 'bg-light-success text-success' : ($participant->type === \App\Enums\ParticipantType::Official ? 'bg-light-info text-info' : 'bg-light-warning text-warning') }}">
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
                                @if($participant->type === \App\Enums\ParticipantType::Athlete)
                                    <span class="badge badge-light-primary fw-bolder ms-2 fs-8">Atlet</span>
                                @elseif($participant->type === \App\Enums\ParticipantType::Coach)
                                    <span class="badge badge-light-success fw-bolder ms-2 fs-8">Pelatih</span>
                                @else
                                    <span class="badge badge-light-info fw-bolder ms-2 fs-8">Official</span>
                                @endif
                                @if($participant->is_verified)
                                    <span class="badge badge-light-success fw-bolder ms-2 fs-8">
                                        <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                    </span>
                                @else
                                    <span class="badge badge-light-warning fw-bolder ms-2 fs-8">Belum</span>
                                @endif
                                @if($hasActiveRegistration)
                                    <span class="badge badge-light-info fw-bolder ms-2 fs-8">
                                        <i class="bi bi-clipboard-check me-1"></i>Terdaftar Event
                                    </span>
                                @endif
                            </div>
                            <span class="text-muted fw-semibold fs-6">
                                @if($participant->nik)
                                    NIK: {{ $participant->nik }}
                                @else
                                    {{ $participant->type === \App\Enums\ParticipantType::Coach ? 'Pelatih' : ($participant->type === \App\Enums\ParticipantType::Official ? 'Official' : 'Peserta') }}
                                @endif
                            </span>
                        </div>
                        <div class="d-flex flex-wrap">
                            <a href="{{ route('participants.edit', $participant) }}"
                                class="btn btn-light-primary btn-sm me-2">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            @if($canDelete)
                                <form action="{{ route('participants.destroy', $participant) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus peserta ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light-danger btn-sm me-2">
                                        <i class="bi bi-trash me-1"></i> Hapus
                                    </button>
                                </form>
                            @else
                                <span class="btn btn-light-danger btn-sm me-2 opacity-50 cursor-default" data-bs-toggle="tooltip"
                                    data-bs-placement="bottom" title="{{ $deleteReason ?? 'Peserta tidak dapat dihapus' }}"
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
                                        @if($participant->type === \App\Enums\ParticipantType::Athlete)
                                            <span class="badge badge-light-primary">Atlet</span>
                                        @elseif($participant->type === \App\Enums\ParticipantType::Coach)
                                            <span class="badge badge-light-success">Pelatih</span>
                                        @else
                                            <span class="badge badge-light-info">Official</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($participant->type === \App\Enums\ParticipantType::Athlete)
                                    <tr>
                                        <td class="text-gray-600 fw-bold">NIK</td>
                                        <td class="text-gray-800">{{ $participant->nik ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-600 fw-bold">Tanggal Lahir</td>
                                        <td class="text-gray-800">{{ $participant->birth_date?->format('d M Y') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-600 fw-bold">Jenis Kelamin</td>
                                        <td class="text-gray-800">
                                            {{ $participant->gender === \App\Enums\ParticipantGender::Male ? 'Laki-laki' : 'Perempuan' }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-gray-600 fw-bold">Provinsi</td>
                                    <td class="text-gray-800">{{ $participant->provinsi ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Institusi</td>
                                    <td class="text-gray-800">{{ $participant->institusi ?? '-' }}</td>
                                </tr>
                                @endif
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
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-2 gy-3">
                            <tbody>
                                <tr>
                                    <td class="text-gray-600 fw-bold w-150px">Status Verifikasi</td>
                                    <td>
                                        @if($participant->is_verified)
                                            <span class="badge badge-light-success">
                                                <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                            </span>
                                        @else
                                            <span class="badge badge-light-warning">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($participant->document)
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
                                @if($participant->is_verified)
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
                                    <td class="text-gray-800">{{ $participant->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });

        @if (session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if ($errors->has('delete'))
            toastr.error("{{ $errors->first('delete') }}");
        @endif
    @endpush
</x-app-layout>
