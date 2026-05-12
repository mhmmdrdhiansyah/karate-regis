<x-app-layout>
    @section('title', 'User List')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <form action="{{ route('auth.users.index') }}" method="GET"
                    class="d-flex align-items-center gap-3 position-relative my-1">

                    <div class="position-relative">
                        <x-icon name="search" class="svg-icon-1 position-absolute ms-6 top-50 translate-middle-y" />
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control form-control-solid w-200px ps-14" placeholder="Cari user..." />
                    </div>

                    <select name="role" class="form-select form-select-solid w-150px" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="per_page" class="form-select form-select-solid w-100px" onchange="this.form.submit()">
                        @foreach ([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ request('per_page', 10) == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>

                    @if(request()->hasAny(['search', 'role', 'per_page']))
                        <a href="{{ route('auth.users.index') }}" class="btn btn-sm btn-light-danger" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end flex-wrap gap-2" data-kt-user-table-toolbar="base">
                    <div class="btn-group me-2">
                        <a href="{{ route('auth.users.index') }}"
                            class="btn btn-sm {{ !$showTrashed ? 'btn-primary active' : 'btn-light-primary' }}">
                            Aktif
                        </a>
                        <a href="{{ route('auth.users.index', ['trashed' => 'only']) }}"
                            class="btn btn-sm {{ $showTrashed ? 'btn-danger active' : 'btn-light-danger' }}">
                            Terhapus
                        </a>
                    </div>
                    <a href="{{ route('auth.users.create') }}" class="btn btn-sm btn-primary d-flex align-items-center">
                        <x-icon name="plus" class="svg-icon-2 me-2" />
                        Add User
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body py-4">

            {{-- Desktop: Table Layout --}}
            <div class="table-responsive d-none d-lg-block">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="w-50px">No</th>
                            <th class="min-w-125px">User</th>
                            <th class="min-w-125px">Role</th>
                            <th class="min-w-125px">Joined Date</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @foreach ($users as $user)
                            <tr>
                                <td class="text-gray-600">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                <td class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                        <a href="{{ route('auth.users.show', $user->id) }}">
                                            <div class="symbol-label fs-3 bg-light-primary text-primary">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('auth.users.show', $user->id) }}"
                                            class="text-gray-800 text-hover-primary mb-1">{{ $user->name }}</a>
                                        <span class="text-muted fs-7">{{ $user->username }}</span>
                                        <span>{{ $user->email }}</span>
                                    </div>
                                </td>

                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-light-primary fw-bolder">{{ $role->name }}</span>
                                    @endforeach
                                </td>


                                <td>{{ $user->created_at->format('d M Y, h:i a') }}</td>

                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-shrink-0">

                                        @if($showTrashed)
                                            <form action="{{ route('auth.users.restore', $user->id) }}" method="POST" id="restore-form-{{ $user->id }}" style="display: none;">
                                                @csrf
                                            </form>
                                            <a href="#" class="btn btn-icon btn-light-success btn-sm me-1" 
                                                onclick="event.preventDefault(); document.getElementById('restore-form-{{ $user->id }}').submit();" title="Pulihkan User">
                                                <i class="bi bi-arrow-counterclockwise fs-3"></i>
                                            </a>

                                            <form action="{{ route('auth.users.forceDelete', $user->id) }}" method="POST" id="force-delete-form-{{ $user->id }}" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a href="#" class="btn btn-icon btn-light-danger btn-sm" 
                                                onclick="confirmForceDelete(event, {{ $user->id }})" title="Hapus Permanen">
                                                <i class="bi bi-trash-fill fs-3"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('auth.users.show', $user->id) }}"
                                                class="btn btn-icon btn-light-primary btn-sm me-1" title="Lihat Detail">
                                                <x-icon name="eye" class="svg-icon-3" />
                                            </a>
                                            @can('edit user')
                                                <a href="{{ route('auth.users.edit', $user->id) }}"
                                                    class="btn btn-icon btn-light-warning btn-sm me-1" title="Edit User">
                                                    <x-icon name="edit" class="svg-icon-3" />
                                                </a>
                                            @endcan

                                            @can('delete user')
                                                <form action="{{ route('auth.users.destroy', $user->id) }}" method="POST"
                                                    id="delete-form-{{ $user->id }}" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                                <a href="#" class="btn btn-icon btn-light-danger btn-sm"
                                                    onclick="confirmDelete(event, {{ $user->id }})" title="Hapus User">
                                                    <x-icon name="trash" class="svg-icon-3" />
                                                </a>
                                            @endcan
                                        @endif


                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: Collapsible Card Layout --}}
            <div class="d-block d-lg-none">
                @include('partials.mobile-card-styles', ['prefix' => 'u'])

                @forelse ($users as $user)
                    <div class="u-card" onclick="this.classList.toggle('open')">
                        <div class="u-card-hd">
                            <div class="u-card-av bg-light-primary text-primary">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="u-card-nm">{{ $user->name }}</div>
                                <div class="u-card-em">{{ $user->email }}</div>
                                <div class="u-card-bg">
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-light-primary">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="u-card-arr"><i class="bi bi-chevron-down"></i></div>
                        </div>

                        <div class="u-card-bd" onclick="event.stopPropagation()">
                            <div class="u-card-dt">
                                <div class="u-card-row">
                                    <span class="u-card-lbl">Username</span>
                                    <span class="u-card-val">{{ $user->username }}</span>
                                </div>
                                <div class="u-card-row">
                                    <span class="u-card-lbl">Bergabung</span>
                                    <span class="u-card-val">{{ $user->created_at->format('d M Y, h:i a') }}</span>
                                </div>
                            </div>
                            <div class="u-card-acts">
                                <a href="{{ route('auth.users.show', $user->id) }}" class="btn btn-light-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                @can('edit user')
                                    <a href="{{ route('auth.users.edit', $user->id) }}" class="btn btn-light-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                @endcan
                                @can('delete user')
                                    <form action="{{ route('auth.users.destroy', $user->id) }}" method="POST"
                                        id="m-delete-{{ $user->id }}" style="display:none">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button class="btn btn-light-danger"
                                        onclick="event.stopPropagation();confirmDelete(event,{{ $user->id }})">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                @endcan
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
                            <span class="fw-semibold">Belum ada data user</span>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="row">
                <div
                    class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                    <div class="dataTables_info">
                        Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari
                        {{ $users->total() }} data
                    </div>
                </div>
                <div
                    class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                    <div class="dataTables_paginate paging_simple_numbers">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(e, id) {
                e.preventDefault();

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data user ini akan dihapus permanen!",
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
                })
            }

            function confirmForceDelete(e, id) {
                e.preventDefault();

                Swal.fire({
                    title: 'Hapus Permanen?',
                    text: "Data ini tidak bisa dipulihkan kembali!",
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

            @if(session('success'))
                toastr.success(@js(session('success')));
            @endif

            @if(session('error'))
                toastr.error(@js(session('error')));
            @endif
        </script>
    @endpush

</x-app-layout>
