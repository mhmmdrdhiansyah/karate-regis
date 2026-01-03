<x-app-layout>
    @section('title', isset($role) ? 'Edit Role' : 'Tambah Role')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ isset($role) ? 'Edit Role' : 'Buat Role Baru' }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('auth.roles.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ isset($role) ? route('auth.roles.update', $role->id) : route('auth.roles.store') }}"
                method="POST">
                @csrf
                @if (isset($role))
                    @method('PUT')
                @endif

                <div class="mb-10">
                    <label class="form-label required">Nama Role</label>
                    <input type="text" name="name" class="form-control form-control-solid"
                        value="{{ old('name', $role->name ?? '') }}" placeholder="Contoh: Manager" required />
                    @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-10">
                    <label class="form-label fw-bolder mb-5">Pilih Hak Akses (Permissions)</label>

                    <div class="row">
                        @foreach ($permissions as $perm)
                            <div class="col-md-3 mb-4">
                                <label class="form-check form-check-custom form-check-solid align-items-start">
                                    <input class="form-check-input me-3" type="checkbox" name="permissions[]"
                                        value="{{ $perm->name }}"
                                        {{ isset($rolePermissions) && in_array($perm->name, $rolePermissions) ? 'checked' : '' }} />
                                    <span class="form-check-label d-flex flex-column align-items-start">
                                        <span class="fw-bolder fs-6 mb-0">{{ $perm->name }}</span>
                                        <span class="text-muted fs-7">Izinkan role ini melakukan
                                            {{ $perm->name }}</span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit"
                        class="btn btn-primary">{{ isset($role) ? 'Update Role' : 'Simpan Role' }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
