<x-app-layout>
    @section('title', 'Profil Saya')

    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                        @if($user->avatar_url)
                            <div class="symbol-label" style="background-image: url('{{ $user->avatar_url }}'); background-size: cover; background-position: center; width: 100%; height: 100%; border-radius: 0.475rem;"></div>
                        @else
                            <div class="symbol-label fs-1 fw-bolder bg-light-primary text-primary">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        <div
                            class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-white h-20px w-20px">
                        </div>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <a href="#"
                                    class="text-gray-900 text-hover-primary fs-2 fw-bolder me-1">{{ $user->name }}</a>
                                <span
                                    class="badge badge-light-success fw-bolder ms-2 fs-8">{{ $user->getRoleNames()->first() }}</span>
                            </div>
                            <div class="d-flex flex-wrap fw-bold fs-6 mb-4 pe-2">
                                <a href="#"
                                    class="d-flex align-items-center text-gray-400 text-hover-primary mb-2">
                                    <span class="svg-icon svg-icon-4 me-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none">
                                            <path opacity="0.3"
                                                d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z"
                                                fill="black"></path>
                                            <path
                                                d="M21 5H2.99999C2.69999 5 2.49999 5.10005 2.29999 5.30005L11.2 13.3C11.7 13.7 12.4 13.7 12.8 13.3L21.7 5.30005C21.5 5.10005 21.3 5 21 5Z"
                                                fill="black"></path>
                                        </svg>
                                    </span>
                                    {{ $user->email }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kontingen Profile Section - Hanya untuk role kontingen --}}
    @if($user->isKontingen() && $contingent)
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_kontingen_details" aria-expanded="true"
            aria-controls="kt_account_kontingen_details">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">Profil Kontingen</h3>
            </div>
            <div class="card-toolbar">
                <span class="svg-icon svg-icon-1 toggle-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M11 4.5C11 4.22386 11.2239 4 11.5 4H12.5C12.7761 4 13 4.22386 13 4.5V19.5C13 19.7761 12.7761 20 12.5 20H11.5C11.2239 20 11 19.7761 11 19.5V4.5Z" fill="currentColor"/>
                        <path d="M4.5 11C4.22386 11 4 11.2239 4 11.5V12.5C4 12.7761 4.22386 13 4.5 13H19.5C19.7761 13 20 12.7761 20 12.5V11.5C20 11.2239 19.7761 11 19.5 11H4.5Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="svg-icon svg-icon-1 toggle-off d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M4.5 11C4.22386 11 4 11.2239 4 11.5V12.5C4 12.7761 4.22386 13 4.5 13H19.5C19.7761 13 20 12.7761 20 12.5V11.5C20 11.2239 19.7761 11 19.5 11H4.5Z" fill="currentColor"/>
                    </svg>
                </span>
            </div>
        </div>
        <div id="kt_account_kontingen_details" class="collapse show">
            <form class="form" method="POST" action="{{ route('profile.update.kontingen') }}" enctype="multipart/form-data">
                @csrf
                @method('patch')

                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Foto Profil / Logo Kontingen</label>
                        <div class="col-lg-8 fv-row">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-100px symbol-lg-120px me-5 position-relative">
                                    @if($contingent->photo_url)
                                        <img id="photo-preview-img" src="{{ $contingent->photo_url }}" alt="Foto Kontingen" style="width:100%; height:100%; object-fit:cover; border-radius: 0.475rem;" />
                                        <div id="photo-preview-placeholder" class="symbol-label fs-2x fw-bolder bg-light-primary text-primary" style="display:none; border-radius: 0.475rem;">
                                            {{ substr($contingent->name, 0, 1) }}
                                        </div>
                                    @else
                                        <div id="photo-preview-placeholder" class="symbol-label fs-2x fw-bolder bg-light-primary text-primary" style="border-radius: 0.475rem;">
                                            {{ substr($contingent->name, 0, 1) }}
                                        </div>
                                        <img id="photo-preview-img" src="" alt="Foto Kontingen" style="width:100%; height:100%; object-fit:cover; display:none; border-radius: 0.475rem;" />
                                    @endif
                                </div>
                                <div class="d-flex flex-column align-items-start gap-2">
                                    <label class="btn btn-sm btn-primary text-white mb-0">
                                        <i class="bi bi-upload me-1"></i> Pilih Foto
                                        <input type="file" name="photo" id="kontingen-photo-input" accept="image/png, image/jpeg, image/jpg, image/webp" class="d-none" />
                                    </label>
                                    <input type="hidden" name="remove_photo" id="remove-photo-input" value="0" />
                                    @if($contingent->photo)
                                        <button type="button" class="btn btn-sm btn-light-danger" id="btn-remove-photo">
                                            <i class="bi bi-trash me-1"></i> Hapus Foto
                                        </button>
                                    @endif
                                    <div class="form-text text-muted">Format: PNG, JPG, JPEG, WEBP. Maksimal 2MB.</div>
                                </div>
                            </div>
                            @error('photo')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Nama Kontingen</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="name" class="form-control form-control-lg form-control-solid"
                                placeholder="Nama Kontingen" value="{{ old('name', $contingent->name) }}" />
                            @error('name')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Nama Official</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="official_name" class="form-control form-control-lg form-control-solid"
                                placeholder="Nama official / manager" value="{{ old('official_name', $contingent->official_name) }}" />
                            @error('official_name')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Provinsi</label>
                        <div class="col-lg-8 fv-row">
                            <select id="profile-province-select" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih provinsi...">
                                <option></option>
                            </select>
                            <input type="hidden" name="province" id="profile-province-hidden" value="{{ old('province', $contingent->province) }}" />
                            @error('province')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Kabupaten/Kota</label>
                        <div class="col-lg-8 fv-row">
                            <select id="profile-regency-select" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih kabupaten/kota..." disabled>
                                <option></option>
                            </select>
                            <input type="hidden" name="regency" id="profile-regency-hidden" value="{{ old('regency', $contingent->regency) }}" />
                            @error('regency')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Nomor Telepon</label>
                        <div class="col-lg-8 fv-row">
                            <input type="tel" name="phone" class="form-control form-control-lg form-control-solid"
                                placeholder="+62 xxx xxxx xxxx" value="{{ old('phone', $contingent->phone) }}" />
                            @error('phone')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Alamat</label>
                        <div class="col-lg-8 fv-row">
                            <textarea name="address" class="form-control form-control-lg form-control-solid" rows="3"
                                placeholder="Alamat kontingen">{{ old('address', $contingent->address) }}</textarea>
                            @error('address')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-light btn-active-light-primary me-2">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_profile_details" aria-expanded="true"
            aria-controls="kt_account_profile_details">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">Detail Profil</h3>
            </div>
            <div class="card-toolbar">
                <span class="svg-icon svg-icon-1 toggle-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M11 4.5C11 4.22386 11.2239 4 11.5 4H12.5C12.7761 4 13 4.22386 13 4.5V19.5C13 19.7761 12.7761 20 12.5 20H11.5C11.2239 20 11 19.7761 11 19.5V4.5Z" fill="currentColor"/>
                        <path d="M4.5 11C4.22386 11 4 11.2239 4 11.5V12.5C4 12.7761 4.22386 13 4.5 13H19.5C19.7761 13 20 12.7761 20 12.5V11.5C20 11.2239 19.7761 11 19.5 11H4.5Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="svg-icon svg-icon-1 toggle-off d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M4.5 11C4.22386 11 4 11.2239 4 11.5V12.5C4 12.7761 4.22386 13 4.5 13H19.5C19.7761 13 20 12.7761 20 12.5V11.5C20 11.2239 19.7761 11 19.5 11H4.5Z" fill="currentColor"/>
                    </svg>
                </span>
            </div>
        </div>
        <div id="kt_account_profile_details" class="collapse show">
            <form class="form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('patch')

                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Foto Profil / Avatar</label>
                        <div class="col-lg-8 fv-row">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-100px symbol-lg-120px me-5 position-relative">
                                    @if($user->avatar_url)
                                        <img id="user-avatar-preview-img" src="{{ $user->avatar_url }}" alt="Foto Profile" style="width:100%; height:100%; object-fit:cover; border-radius: 0.475rem;" />
                                        <div id="user-avatar-preview-placeholder" class="symbol-label fs-2x fw-bolder bg-light-primary text-primary" style="display:none; border-radius: 0.475rem;">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    @else
                                        <div id="user-avatar-preview-placeholder" class="symbol-label fs-2x fw-bolder bg-light-primary text-primary" style="border-radius: 0.475rem;">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <img id="user-avatar-preview-img" src="" alt="Foto Profile" style="width:100%; height:100%; object-fit:cover; display:none; border-radius: 0.475rem;" />
                                    @endif
                                </div>
                                <div class="d-flex flex-column align-items-start gap-2">
                                    <label class="btn btn-sm btn-primary text-white mb-0">
                                        <i class="bi bi-upload me-1"></i> Pilih Foto
                                        <input type="file" name="avatar" id="user-avatar-input" accept="image/png, image/jpeg, image/jpg, image/webp" class="d-none" />
                                    </label>
                                    <input type="hidden" name="remove_avatar" id="remove-user-avatar-input" value="0" />
                                    @if($user->avatar)
                                        <button type="button" class="btn btn-sm btn-light-danger" id="btn-remove-user-avatar">
                                            <i class="bi bi-trash me-1"></i> Hapus Foto
                                        </button>
                                    @endif
                                    <div class="form-text text-muted">Format: PNG, JPG, JPEG, WEBP. Maksimal 2MB.</div>
                                </div>
                            </div>
                            @error('avatar')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @if($user->isKontingen())
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Username</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" class="form-control form-control-lg form-control-solid"
                                value="{{ $user->username }}" readonly />
                            <span class="form-text text-muted mt-2">Username tidak dapat diubah.</span>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Nama Lengkap</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="name" class="form-control form-control-lg form-control-solid"
                                placeholder="Nama Lengkap" value="{{ old('name', $user->name) }}" />
                            @error('name')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Email</label>
                        <div class="col-lg-8 fv-row">
                            <input type="email" name="email" class="form-control form-control-lg form-control-solid"
                                placeholder="Email Address" value="{{ old('email', $user->email) }}" />
                            @error('email')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-light btn-active-light-primary me-2">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_signin_method" aria-expanded="true"
            aria-controls="kt_account_signin_method">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">Ganti Password</h3>
            </div>
            <div class="card-toolbar">
                <span class="svg-icon svg-icon-1 toggle-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M11 4.5C11 4.22386 11.2239 4 11.5 4H12.5C12.7761 4 13 4.22386 13 4.5V19.5C13 19.7761 12.7761 20 12.5 20H11.5C11.2239 20 11 19.7761 11 19.5V4.5Z" fill="currentColor"/>
                        <path d="M4.5 11C4.22386 11 4 11.2239 4 11.5V12.5C4 12.7761 4.22386 13 4.5 13H19.5C19.7761 13 20 12.7761 20 12.5V11.5C20 11.2239 19.7761 11 19.5 11H4.5Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="svg-icon svg-icon-1 toggle-off d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M4.5 11C4.22386 11 4 11.2239 4 11.5V12.5C4 12.7761 4.22386 13 4.5 13H19.5C19.7761 13 20 12.7761 20 12.5V11.5C20 11.2239 19.7761 11 19.5 11H4.5Z" fill="currentColor"/>
                    </svg>
                </span>
            </div>
        </div>

        <div id="kt_account_signin_method" class="collapse show">
            <div class="card-body border-top p-9">
                <form method="post" action="{{ route('password.update') }}" class="form">
                    @csrf
                    @method('put')

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Password Saat Ini</label>
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="current_password"
                                class="form-control form-control-lg form-control-solid"
                                autocomplete="current-password" />
                            @error('current_password', 'updatePassword')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Password Baru</label>
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="password"
                                class="form-control form-control-lg form-control-solid" autocomplete="new-password" />
                            @error('password', 'updatePassword')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Konfirmasi Password Baru</label>
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="password_confirmation"
                                class="form-control form-control-lg form-control-solid" autocomplete="new-password" />
                            @error('password_confirmation', 'updatePassword')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if (session('status') === 'profile-updated')
        @push('scripts')
            <script>
                toastr.success("Profil berhasil diperbarui!");
            </script>
        @endpush
    @elseif (session('status') === 'kontingen-updated')
        @push('scripts')
            <script>
                toastr.success("Profil kontingen berhasil diperbarui!");
            </script>
        @endpush
    @elseif (session('status') === 'password-updated')
        @push('scripts')
            <script>
                toastr.success("Password berhasil diubah!");
            </script>
        @endpush
    @elseif (session('error'))
        @push('scripts')
            <script>
                toastr.error(@js(session('error')));
            </script>
        @endpush
    @endif

    @push('scripts')
        <script>
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (trigger) {
                var target = document.querySelector(trigger.getAttribute('data-bs-target'));
                if (!target) return;

                target.addEventListener('shown.bs.collapse', function () {
                    trigger.querySelector('.toggle-icon').classList.remove('d-none');
                    trigger.querySelector('.toggle-off').classList.add('d-none');
                    trigger.setAttribute('aria-expanded', 'true');
                });
                target.addEventListener('hidden.bs.collapse', function () {
                    trigger.querySelector('.toggle-icon').classList.add('d-none');
                    trigger.querySelector('.toggle-off').classList.remove('d-none');
                    trigger.setAttribute('aria-expanded', 'false');
                });
            });

            $('#user-avatar-input').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#user-avatar-preview-img').attr('src', e.target.result).show();
                        $('#user-avatar-preview-placeholder').hide();
                        $('#remove-user-avatar-input').val('0');
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#btn-remove-user-avatar').on('click', function() {
                $('#user-avatar-preview-img').attr('src', '').hide();
                $('#user-avatar-preview-placeholder').show();
                $('#user-avatar-input').val('');
                $('#remove-user-avatar-input').val('1');
                $(this).hide();
            });

            @if($user->isKontingen() && $contingent)
            $('#kontingen-photo-input').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photo-preview-img').attr('src', e.target.result).show();
                        $('#photo-preview-placeholder').hide();
                        $('#remove-photo-input').val('0');
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#btn-remove-photo').on('click', function() {
                $('#photo-preview-img').attr('src', '').hide();
                $('#photo-preview-placeholder').show();
                $('#kontingen-photo-input').val('');
                $('#remove-photo-input').val('1');
                $(this).hide();
            });

            var pSelect = $('#profile-province-select');
            var rSelect = $('#profile-regency-select');
            var pH = $('#profile-province-hidden');
            var rH = $('#profile-regency-hidden');
            var sP = pH.val();
            var sR = rH.val();

            pSelect.on('select2:select', function (e) {
                pH.val(e.params.data.text);
                rSelect.prop('disabled', false);
                rSelect.val(null).trigger('change');
                rH.val('');
                $.get('/api/wilayah/regencies/' + e.params.data.id, function (res) {
                    rSelect.empty().append('<option></option>');
                    (res.data || []).forEach(function (item) {
                        rSelect.append(new Option(item.name, item.code, false, false));
                    });
                    rSelect.trigger('change');
                });
            });

            rSelect.on('select2:select', function (e) {
                rH.val(e.params.data.text);
            });

            $.get('/api/wilayah/provinces', function (res) {
                (res.data || []).forEach(function (item) {
                    pSelect.append(new Option(item.name, item.code, false, false));
                });
                pSelect.trigger('change');
                if (sP) {
                    pSelect.find('option').each(function () {
                        if ($(this).text() === sP) {
                            pSelect.val($(this).val()).trigger('change');
                            pH.val(sP);
                            return false;
                        }
                    });
                }
            });

            if (sP) {
                $(document).ajaxComplete(function handler(e, xhr, settings) {
                    if (settings.url && settings.url.indexOf('/provinces') !== -1 && !rSelect.data('loaded-for')) {
                        pSelect.find('option').each(function () {
                            if ($(this).text() === sP) {
                                var code = $(this).val();
                                $.get('/api/wilayah/regencies/' + code, function (res) {
                                    rSelect.prop('disabled', false);
                                    rSelect.empty().append('<option></option>');
                                    (res.data || []).forEach(function (item) {
                                        rSelect.append(new Option(item.name, item.code, false, false));
                                    });
                                    rSelect.trigger('change');
                                    if (sR) {
                                        rSelect.find('option').each(function () {
                                            if ($(this).text() === sR) {
                                                rSelect.val($(this).val()).trigger('change');
                                                rH.val(sR);
                                                return false;
                                            }
                                        });
                                    }
                                    rSelect.data('loaded-for', code);
                                });
                                $(document).off('ajaxComplete', handler);
                                return false;
                            }
                        });
                    }
                });
            }
            @endif
        </script>
    @endpush

</x-app-layout>
