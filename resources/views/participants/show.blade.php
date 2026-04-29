<x-app-layout>
    @section('title', 'Detail Peserta - ' . $participant->name)

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
                                Edit
                            </a>
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
        @if (session('success'))
            <script>toastr.success("{{ session('success') }}");</script>
        @endif
    @endpush
</x-app-layout>
