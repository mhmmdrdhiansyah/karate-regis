<x-app-layout>
    @section('title', isset($permission) ? 'Edit Permission' : 'Tambah Permission')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ isset($permission) ? 'Edit Permission' : 'Buat Permission Baru' }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('auth.permissions.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
        </div>

        <div class="card-body">
            <form
                action="{{ isset($permission) ? route('auth.permissions.update', $permission->id) : route('auth.permissions.store') }}"
                method="POST">
                @csrf
                @if (isset($permission))
                    @method('PUT')
                @endif

                <div class="mb-10">
                    <label class="form-label required">Nama Permission</label>
                    <input type="text" name="name" class="form-control form-control-solid"
                        value="{{ old('name', $permission->name ?? '') }}" placeholder="Contoh: view reports"
                        required />
                    <div class="form-text text-muted">Gunakan huruf kecil dan spasi (cth: create users, view dashboard).
                    </div>
                    @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit"
                        class="btn btn-primary">{{ isset($permission) ? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
