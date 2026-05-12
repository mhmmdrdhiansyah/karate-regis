<x-app-layout>
    @section('title', 'Daftar Kontingen')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <form action="{{ route('kontingen.index') }}" method="GET"
                    class="d-flex align-items-center gap-3 position-relative my-1">

                    <div class="position-relative">
                        <x-icon name="search" class="svg-icon-1 position-absolute ms-6 top-50 translate-middle-y" />
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control form-control-solid w-200px ps-14" placeholder="Cari kontingen..." />
                    </div>

                    <select name="per_page" class="form-select form-select-solid w-100px" onchange="this.form.submit()">
                        @foreach ([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ request('per_page', 10) == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>

                    @if(request()->hasAny(['search', 'per_page']))
                        <a href="{{ route('kontingen.index') }}" class="btn btn-sm btn-light-danger" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end flex-wrap gap-2">
                    <div class="btn-group me-2">
                        <a href="{{ route('kontingen.index') }}"
                            class="btn btn-sm {{ !$showTrashed ? 'btn-primary active' : 'btn-light-primary' }}">
                            Aktif
                        </a>
                        <a href="{{ route('kontingen.index', ['trashed' => 'only']) }}"
                            class="btn btn-sm {{ $showTrashed ? 'btn-danger active' : 'btn-light-danger' }}">
                            Terhapus
                        </a>
                    </div>
                    <a href="{{ route('kontingen.create') }}" class="btn btn-sm btn-primary d-flex align-items-center">
                        <x-icon name="plus" class="svg-icon-2 me-2" />
                        Tambah Kontingen
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body py-4">

            {{-- Desktop: Table Layout --}}
            <div class="table-responsive d-none d-lg-block">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="w-50px">No</th>
                            <th class="min-w-150px">Kontingen</th>
                            <th class="min-w-125px">Official</th>
                            <th class="min-w-125px">Username</th>
                            <th class="min-w-125px">Email</th>
                            <th class="min-w-100px">Telepon</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @forelse ($contingents as $contingent)
                            <tr>
                                <td class="text-gray-600">{{ ($contingents->currentPage() - 1) * $contingents->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                            <a href="{{ route('kontingen.show', $contingent) }}">
                                                <div class="symbol-label fs-3 bg-light-warning text-warning">
                                                    {{ substr($contingent->name, 0, 1) }}
                                                </div>
                                            </a>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <a href="{{ route('kontingen.show', $contingent) }}"
                                                class="text-gray-800 text-hover-primary mb-1">{{ $contingent->name }}</a>
                                            <span class="text-muted fw-semibold text-muted d-block fs-7">
                                                {{ $contingent->user->name }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $contingent->official_name }}</td>
                                <td>{{ $contingent->user->username }}</td>
                                <td>{{ $contingent->user->email }}</td>
                                <td>{{ $contingent->phone ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-shrink-0">
                                        @if($showTrashed)
                                            <form action="{{ route('kontingen.restore', $contingent->id) }}" method="POST" id="restore-form-{{ $contingent->id }}" style="display: none;">
                                                @csrf
                                            </form>
                                            <a href="#" class="btn btn-icon btn-light-success btn-sm me-1" 
                                                onclick="event.preventDefault(); document.getElementById('restore-form-{{ $contingent->id }}').submit();" title="Pulihkan">
                                                <i class="bi bi-arrow-counterclockwise fs-3"></i>
                                            </a>

                                            <form action="{{ route('kontingen.force-delete', $contingent->id) }}" method="POST" id="force-delete-form-{{ $contingent->id }}" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a href="#" class="btn btn-icon btn-light-danger btn-sm" 
                                                onclick="confirmForceDelete(event, {{ $contingent->id }})" title="Hapus Permanen">
                                                <i class="bi bi-trash-fill fs-3"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('kontingen.show', $contingent) }}"
                                                class="btn btn-icon btn-light-primary btn-sm me-1" title="Lihat Detail">
                                                <x-icon name="eye" class="svg-icon-3" />
                                            </a>
                                            <a href="{{ route('kontingen.edit', $contingent) }}"
                                                class="btn btn-icon btn-light-warning btn-sm me-1" title="Edit">
                                                <x-icon name="edit" class="svg-icon-3" />
                                            </a>
                                            <form action="{{ route('kontingen.destroy', $contingent) }}" method="POST"
                                                id="delete-form-{{ $contingent->id }}" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a href="#" class="btn btn-icon btn-light-danger btn-sm"
                                                onclick="confirmDelete(event, {{ $contingent->id }})" title="Hapus">
                                                <x-icon name="trash" class="svg-icon-3" />
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="svg-icon svg-icon-4x mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path opacity="0.3"
                                                    d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z"
                                                    fill="black" />
                                                <path
                                                    d="M21 5H14.8L12 2H3C2.4 2 2 2.4 2 3V18C2 18.6 2.4 19 3 19H21C21.6 19 22 18.6 22 18V6C22 5.4 21.6 5 21 5Z"
                                                    fill="black" />
                                            </svg>
                                        </span>
                                        <span class="fw-semibold">Belum ada data kontingen</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile: Collapsible Card Layout --}}
            <div class="d-block d-lg-none">
                @include('partials.mobile-card-styles', ['prefix' => 'k'])

                @forelse ($contingents as $contingent)
                    <div class="k-card" onclick="this.classList.toggle('open')">
                        <div class="k-card-hd">
                            <div class="k-card-av bg-light-warning text-warning">
                                {{ substr($contingent->name, 0, 1) }}
                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="k-card-nm">{{ $contingent->name }}</div>
                                <div class="k-card-em">{{ $contingent->official_name }}</div>
                                <div class="k-card-bg">
                                    <span class="badge badge-light-warning">{{ $contingent->user->username }}</span>
                                </div>
                            </div>
                            <div class="k-card-arr"><i class="bi bi-chevron-down"></i></div>
                        </div>

                        <div class="k-card-bd" onclick="event.stopPropagation()">
                            <div class="k-card-dt">
                                <div class="k-card-row">
                                    <span class="k-card-lbl">Email</span>
                                    <span class="k-card-val">{{ $contingent->user->email }}</span>
                                </div>
                                <div class="k-card-row">
                                    <span class="k-card-lbl">Telepon</span>
                                    <span class="k-card-val">{{ $contingent->phone ?? '-' }}</span>
                                </div>
                                <div class="k-card-row">
                                    <span class="k-card-lbl">User</span>
                                    <span class="k-card-val">{{ $contingent->user->name }}</span>
                                </div>
                            </div>
                            <div class="k-card-acts">
                                <a href="{{ route('kontingen.show', $contingent) }}" class="btn btn-light-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <a href="{{ route('kontingen.edit', $contingent) }}" class="btn btn-light-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('kontingen.destroy', $contingent) }}" method="POST"
                                    id="m-delete-{{ $contingent->id }}" style="display:none">
                                    @csrf @method('DELETE')
                                </form>
                                <button class="btn btn-light-danger"
                                    onclick="event.stopPropagation();confirmDelete(event,{{ $contingent->id }})">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-10">
                        <div class="d-flex flex-column align-items-center">
                            <span class="svg-icon svg-icon-4x mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <path opacity="0.3"
                                        d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z"
                                        fill="black" />
                                    <path
                                        d="M21 5H14.8L12 2H3C2.4 2 2 2.4 2 3V18C2 18.6 2.4 19 3 19H21C21.6 19 22 18.6 22 18V6C22 5.4 21.6 5 21 5Z"
                                        fill="black" />
                                </svg>
                            </span>
                            <span class="fw-semibold">Belum ada data kontingen</span>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($contingents->count() > 0)
                <div class="row">
                    <div
                        class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="dataTables_info">
                            Menampilkan {{ $contingents->firstItem() }} sampai {{ $contingents->lastItem() }}
                            dari {{ $contingents->total() }} data
                        </div>
                    </div>
                    <div
                        class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                        {{ $contingents->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(e, id) {
                e.preventDefault();
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data kontingen beserta akun login-nya akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id)?.submit()
                            || document.getElementById('m-delete-' + id)?.submit();
                    }
                });
            }

            function confirmForceDelete(e, id) {
                e.preventDefault();

                Swal.fire({
                    title: 'Hapus Permanen?',
                    text: "Seluruh data kontingen dan akun login akan dihapus selamanya!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus Permanen!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('force-delete-form-' + id).submit();
                    }
                })
            }

            @if (session('success'))
                toastr.success(@js(session('success')));
            @endif

            @if (session('error'))
                toastr.error(@js(session('error')));
            @endif
        </script>
    @endpush
</x-app-layout>
