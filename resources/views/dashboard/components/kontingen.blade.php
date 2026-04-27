{{-- Profile Header --}}
<div class="card mb-5 mb-xl-10">
    <div class="card-body border-0 pt-9 pb-0">
        <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
            <div class="me-7 mb-4">
                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                    <div class="symbol-label fs-1 fw-bolder bg-light-warning text-warning">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-900 fs-2 fw-bolder me-1">
                                {{ $user->name }}
                            </span>
                            <span class="badge badge-light-warning fw-bolder ms-2 fs-8">Kontingen</span>
                        </div>
                        <span class="text-muted fw-semibold fs-6">{{ $user->email }}</span>
                    </div>
                    <div class="d-flex flex-wrap">
                        <a href="{{ route('profile.edit') }}" class="btn btn-light-primary btn-sm">
                            Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($contingent)
    {{-- Contingent Data --}}
    <div class="row g-5 g-xl-10">
        <div class="col-xl-6">
            <div class="card card-flush h-lg-100">
                <div class="card-header pt-7">
                    <h3 class="card-label fw-bold text-dark">Data Kontingen</h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-primary">Info</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-2 gy-3">
                            <tbody>
                                <tr>
                                    <td class="text-gray-600 fw-bold w-150px">Nama Kontingen</td>
                                    <td class="text-gray-800">{{ $contingent->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Official</td>
                                    <td class="text-gray-800">{{ $contingent->official_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Telepon</td>
                                    <td class="text-gray-800">{{ $contingent->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Alamat</td>
                                    <td class="text-gray-800">{{ $contingent->address ?? '-' }}</td>
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
                    <h3 class="card-label fw-bold text-dark">Data Akun</h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-info">Akun</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-2 gy-3">
                            <tbody>
                                <tr>
                                    <td class="text-gray-600 fw-bold w-150px">Username</td>
                                    <td class="text-gray-800">{{ $user->username }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Email</td>
                                    <td class="text-gray-800">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Email Verified</td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge badge-light-success">Terverifikasi</span>
                                        @else
                                            <span class="badge badge-light-danger">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- No Contingent Data --}}
    <div class="row g-5 g-xl-8">
        <div class="col-xl-12">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-body py-10">
                    <div class="d-flex flex-column align-items-center text-center">
                        <i class="bi bi-exclamation-triangle text-warning fs-2x mb-3"></i>
                        <span class="fw-bolder fs-4 text-dark mb-2">Data Kontingen Belum Lengkap</span>
                        <span class="text-muted fw-semibold fs-6 mb-5">
                            Hubungi administrator untuk melengkapi data kontingen Anda.
                        </span>
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">Lihat Profil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
