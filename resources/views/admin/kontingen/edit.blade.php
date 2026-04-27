<x-app-layout>
    @section('title', 'Edit Kontingen - ' . $kontingen->name)

    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_kontingen_data" aria-expanded="true" aria-controls="kt_kontingen_data">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">Data Kontingen</h3>
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
        <div id="kt_kontingen_data" class="collapse show">
            <form class="form" method="POST" action="{{ route('kontingen.update', $kontingen) }}">
                @csrf
                @method('put')

                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Nama Kontingen</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="name" class="form-control form-control-lg form-control-solid"
                                value="{{ old('name', $kontingen->name) }}" />
                            @error('name')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Nama Official</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="official_name" class="form-control form-control-lg form-control-solid"
                                value="{{ old('official_name', $kontingen->official_name) }}" />
                            @error('official_name')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Nomor Telepon</label>
                        <div class="col-lg-8 fv-row">
                            <input type="tel" name="phone" class="form-control form-control-lg form-control-solid"
                                value="{{ old('phone', $kontingen->phone) }}" />
                            @error('phone')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">Alamat</label>
                        <div class="col-lg-8 fv-row">
                            <textarea name="address" class="form-control form-control-lg form-control-solid" rows="2">{{ old('address', $kontingen->address) }}</textarea>
                            @error('address')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('kontingen.show', $kontingen) }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
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

    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_data" aria-expanded="true" aria-controls="kt_account_data">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">Data Akun</h3>
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
        <div id="kt_account_data" class="collapse show">
            <form class="form" method="POST" action="{{ route('kontingen.update', $kontingen) }}">
                @csrf
                @method('put')

                <div class="card-body border-top p-9">
                    <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed p-5 mb-5">
                        <div class="d-flex flex-column">
                            <h5 class="mb-1 text-warning">Perhatian</h5>
                            <span class="text-gray-600">Perubahan username dan email akan mempengaruhi login akun kontingen ini.</span>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Nama Lengkap</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="name" class="form-control form-control-lg form-control-solid"
                                value="{{ old('name', $kontingen->user->name) }}" />
                            @error('name')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Username</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="username" class="form-control form-control-lg form-control-solid"
                                value="{{ old('username', $kontingen->user->username) }}" />
                            @error('username')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-bold fs-6">Email</label>
                        <div class="col-lg-8 fv-row">
                            <input type="email" name="email" class="form-control form-control-lg form-control-solid"
                                value="{{ old('email', $kontingen->user->email) }}" />
                            @error('email')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('kontingen.show', $kontingen) }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary" id="kt_btn_submit_account">
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
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (trigger) {
                var target = document.querySelector(trigger.getAttribute('data-bs-target'));
                if (!target) return;
                target.addEventListener('shown.bs.collapse', function () {
                    trigger.querySelector('.toggle-icon').classList.remove('d-none');
                    trigger.querySelector('.toggle-off').classList.add('d-none');
                });
                target.addEventListener('hidden.bs.collapse', function () {
                    trigger.querySelector('.toggle-icon').classList.add('d-none');
                    trigger.querySelector('.toggle-off').classList.remove('d-none');
                });
            });
            $('#kt_btn_submit, #kt_btn_submit_account').on('click', function () {
                $(this).attr('data-kt-indicator', 'on').attr('disabled', true);
            });
            @if (session('success'))
                toastr.success("{{ session('success') }}");
            @endif
        </script>
    @endpush
</x-app-layout>
