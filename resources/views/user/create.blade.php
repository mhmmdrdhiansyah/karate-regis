<x-app-layout>
    @section('title', isset($user) ? 'Edit User' : 'Tambah User Baru')

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3>{{ isset($user) ? 'Edit Data User' : 'Form User Baru' }}</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('users.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
        </div>

        <div class="card-body py-4">
            <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST">
                @csrf

                @if (isset($user))
                    @method('PUT')
                @endif

                <div class="mb-10">
                    <label class="form-label required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control form-control-solid"
                        placeholder="Masukkan nama" value="{{ old('name', $user->name ?? '') }}" />
                    @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-10">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-control form-control-solid"
                        placeholder="user@email.com" value="{{ old('email', $user->email ?? '') }}" />
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-10">
                    <label class="form-label required">Role</label>
                    <select name="role" class="form-select form-select-solid" data-control="select2"
                        data-placeholder="Pilih Role">
                        <option></option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}"
                                {{ (isset($user) && in_array($role, $userRole ?? [])) || old('role') == $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                @if (isset($user))
                    <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed p-5 mb-5">
                        <div class="d-flex flex-column">
                            <h5 class="mb-1 text-warning">Password</h5>
                            <span class="text-gray-600">Kosongkan kolom password di bawah jika tidak ingin
                                menggantinya.</span>
                        </div>
                    </div>
                @endif

                <div class="row mb-10">
                    <div class="col-md-6">
                        <label class="form-label {{ isset($user) ? '' : 'required' }}">Password</label>
                        <input type="password" name="password" class="form-control form-control-solid"
                            placeholder="{{ isset($user) ? 'Biarkan kosong jika tetap' : 'Minimal 8 karakter' }}" />
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label {{ isset($user) ? '' : 'required' }}">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-solid"
                            placeholder="Ulangi password" />
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" id="kt_btn_submit">
                        <span class="indicator-label">Simpan</span>
                        <span class="indicator-progress">
                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            // Script sederhana untuk memunculkan loading saat form disubmit
            $('form').on('submit', function() {
                var btn = $('#kt_btn_submit');
                btn.attr('data-kt-indicator', 'on'); // Munculkan loading
                btn.attr('disabled', true); // Matikan tombol biar gak bisa diklik lagi
            });
        </script>
    @endpush
</x-app-layout>
