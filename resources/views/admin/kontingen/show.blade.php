<x-app-layout>
    @section('title', 'Detail Kontingen - ' . $kontingen->name)

    <div class="card mb-5 mb-xl-10">
        <div class="card-body border-0 pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                        <div class="symbol-label fs-1 fw-bolder bg-light-warning text-warning">
                            {{ substr($kontingen->name, 0, 1) }}
                        </div>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-gray-900 text-hover-primary fs-2 fw-bolder me-1">
                                    {{ $kontingen->name }}
                                </span>
                                <span class="badge badge-light-primary fw-bolder ms-2 fs-8">Kontingen</span>
                            </div>
                            <div class="d-flex flex-wrap fw-bold fs-6 mb-4 pe-2">
                                <span class="d-flex align-items-center text-gray-400 mb-2 me-4">
                                    <span class="svg-icon svg-icon-4 me-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path opacity="0.3" d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z" fill="black"/>
                                            <path d="M21 5H2.99999C2.69999 5 2.49999 5.10005 2.29999 5.30005L11.2 13.3C11.7 13.7 12.4 13.7 12.8 13.3L21.7 5.30005C21.5 5.10005 21.3 5 21 5Z" fill="black"/>
                                        </svg>
                                    </span>
                                    {{ $kontingen->user->email }}
                                </span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap">
                            <a href="{{ route('kontingen.edit', $kontingen) }}" class="btn btn-light-primary btn-sm me-2">Edit</a>
                            <a href="{{ route('kontingen.index') }}" class="btn btn-light btn-sm">Kembali</a>
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
                                    <td class="text-gray-800">{{ $kontingen->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Official</td>
                                    <td class="text-gray-800">{{ $kontingen->official_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Telepon</td>
                                    <td class="text-gray-800">{{ $kontingen->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Alamat</td>
                                    <td class="text-gray-800">{{ $kontingen->address ?? '-' }}</td>
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
                                    <td class="text-gray-600 fw-bold w-150px">Nama</td>
                                    <td class="text-gray-800">{{ $kontingen->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Username</td>
                                    <td class="text-gray-800">{{ $kontingen->user->username }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Email</td>
                                    <td class="text-gray-800">{{ $kontingen->user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Email Verified</td>
                                    <td>
                                        @if($kontingen->user->email_verified_at)
                                            <span class="badge badge-light-success">Ya</span>
                                        @else
                                            <span class="badge badge-light-danger">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-gray-600 fw-bold">Terdaftar</td>
                                    <td class="text-gray-800">{{ $kontingen->user->created_at->format('d M Y, H:i') }}</td>
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
