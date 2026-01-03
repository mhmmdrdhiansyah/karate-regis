<x-app-layout>
    @section('title', 'Master Permission')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <form action="{{ route('auth.permissions.index') }}" method="GET"
                    class="d-flex align-items-center position-relative my-1">
                    <span class="svg-icon svg-icon-1 position-absolute ms-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none">
                            <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                                transform="rotate(45 17.0365 15.1223)" fill="black" />
                            <path
                                d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                fill="black" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control form-control-solid w-250px ps-14" placeholder="Cari permission..." />
                </form>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('auth.permissions.create') }}" class="btn btn-primary">Tambah Permission</a>
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th class="min-w-125px">Nama Permission</th>
                        <th class="min-w-125px">Guard</th>
                        <th class="text-end min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-bold">
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td><span class="badge badge-light">{{ $permission->guard_name }}</span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end flex-shrink-0">
                                    <a href="{{ route('auth.permissions.edit', $permission->id) }}"
                                        class="btn btn-icon btn-light-warning btn-sm me-1"><i
                                            class="bi bi-pencil"></i></a>

                                    <form action="{{ route('auth.permissions.destroy', $permission->id) }}"
                                        method="POST" id="delete-perm-{{ $permission->id }}" style="display:none">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button class="btn btn-icon btn-light-danger btn-sm"
                                        onclick="confirmDelete(event, {{ $permission->id }})"><i
                                            class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row">
                <div
                    class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                    <div class="dataTables_info">
                        Menampilkan {{ $permissions->firstItem() }} sampai {{ $permissions->lastItem() }} dari
                        {{ $permissions->total() }} data
                    </div>
                </div>
                <div class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                    <div class="dataTables_paginate paging_simple_numbers">
                        {{ $permissions->links() }}
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
                    title: 'Hapus Permission?',
                    text: "Permission ini akan hilang dari semua Role yang memilikinya!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('delete-perm-' + id).submit();
                })
            }
        </script>
        @if (session('success'))
            <script>
                toastr.success("{{ session('success') }}");
            </script>
        @endif

        @if (session('error'))
            <script>
                toastr.error("{{ session('error') }}");
            </script>
        @endif
    @endpush
</x-app-layout>
