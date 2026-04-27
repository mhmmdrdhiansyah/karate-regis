<x-app-layout>
    @section('title', 'Tambah Kontingen Baru')

    <form action="{{ route('kontingen.store') }}" method="POST" id="kt_kontingen_form" class="form">
        @csrf

        <div class="card mb-5">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-dark">Data Akun</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Informasi login untuk akun kontingen</span>
                </h3>
            </div>

            <div class="card-body py-5">
                <div class="row mb-7">
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="required form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control form-control-solid"
                                placeholder="Masukkan nama lengkap" value="{{ old('name') }}" />
                            @error('name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="required form-label">Username</label>
                            <input type="text" name="username" class="form-control form-control-solid"
                                placeholder="Masukkan username" value="{{ old('username') }}" />
                            @error('username')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="fv-row mb-7">
                    <label class="required form-label">Email</label>
                    <input type="email" name="email" class="form-control form-control-solid"
                        placeholder="contoh@email.com" value="{{ old('email') }}" />
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-7">
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="required form-label">Password</label>
                            <input type="password" name="password" class="form-control form-control-solid"
                                placeholder="Minimal 8 karakter" />
                            @error('password')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="required form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                class="form-control form-control-solid" placeholder="Ulangi password" />
                            @error('password')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-dark">Data Kontingen</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Informasi kontingen / klub karate</span>
                </h3>
            </div>

            <div class="card-body py-5">
                <div class="fv-row mb-7">
                    <label class="required form-label">Nama Kontingen</label>
                    <input type="text" name="contingent_name" class="form-control form-control-solid"
                        placeholder="Contoh: Dojo Karate Nusantara" value="{{ old('contingent_name') }}" />
                    @error('contingent_name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="fv-row mb-7">
                    <label class="required form-label">Nama Official</label>
                    <input type="text" name="official_name" class="form-control form-control-solid"
                        placeholder="Nama official / manager" value="{{ old('official_name') }}" />
                    @error('official_name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row mb-7">
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="tel" name="phone" class="form-control form-control-solid"
                                placeholder="+62 xxx xxxx xxxx" value="{{ old('phone') }}" />
                            @error('phone')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control form-control-solid" rows="1"
                                placeholder="Alamat kontingen">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('kontingen.index') }}" class="btn btn-light btn-active-light-primary me-2">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" id="kt_btn_submit">
                <span class="indicator-label">Simpan</span>
                <span class="indicator-progress">
                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            $('#kt_kontingen_form').on('submit', function () {
                var btn = $('#kt_btn_submit');
                btn.attr('data-kt-indicator', 'on');
                btn.attr('disabled', true);
            });
        </script>
    @endpush
</x-app-layout>
