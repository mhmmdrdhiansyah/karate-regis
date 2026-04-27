<x-app-layout>
    @section('title', 'Profil Saya')

    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                        <div class="symbol-label fs-1 fw-bolder bg-light-primary text-primary">
                            {{ substr($user->name, 0, 1) }}
                        </div>
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
            <form class="form" method="POST" action="{{ route('profile.update.kontingen') }}">
                @csrf
                @method('patch')

                <div class="card-body border-top p-9">
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
            <form class="form" method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="card-body border-top p-9">
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
                toastr.error("{{ session('error') }}");
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
        </script>
    @endpush

</x-app-layout>
