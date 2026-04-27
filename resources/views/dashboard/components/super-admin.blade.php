{{-- Stat Cards --}}
<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-people-fill text-primary"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Total Pengguna</span>
                        <span class="text-dark fw-bolder fs-2x">{{ number_format($totalUsers) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-building text-warning"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Total Kontingen</span>
                        <span class="text-dark fw-bolder fs-2x">{{ number_format($totalKontingen) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-shield-check text-success"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Sistem</span>
                        <span class="text-dark fw-bolder fs-2x">Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Users --}}
<div class="row g-5 g-xl-8">
    <div class="col-xl-12">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Pengguna Terbaru</span>
                    <span class="text-muted mt-1 fw-bold fs-7">5 akun terakhir yang terdaftar</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="min-w-150px">Nama</th>
                            <th class="min-w-125px">Username</th>
                            <th class="min-w-125px">Email</th>
                            <th class="min-w-125px">Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @forelse ($recentUsers as $u)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                            <div class="symbol-label fs-6 bg-light-primary text-primary">
                                                {{ substr($u->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <span class="text-gray-800">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $u->username ?? '-' }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-people fs-2x mb-3 text-gray-300"></i>
                                        <span class="fw-semibold">Belum ada pengguna terdaftar</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
