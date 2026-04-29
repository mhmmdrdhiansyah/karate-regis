<x-app-layout>
    @section('title', 'Tambah Peserta')
    @php $__puser = auth()->user(); @endphp

    @if (
        !(
            $__puser &&
            ($__puser->can('create participants') ||
                $__puser->can('manage participants') ||
                $__puser->can('manage own participants'))
        ))
        <div class="card">
            <div class="card-body">
                <div class="alert alert-danger">Akses ditolak: Anda tidak memiliki izin untuk menambah peserta.</div>
                <a href="{{ route('participants.index') }}" class="btn btn-light">Kembali</a>
            </div>
        </div>
    @else
        <form action="{{ route('participants.store') }}" method="POST" id="kt_participant_form" class="form"
            enctype="multipart/form-data">
            @csrf

            <div class="card mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Data Peserta</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Pilih jenis peserta dan isi form sesuai
                            data</span>
                    </h3>
                </div>

                <div class="card-body py-5">
                    <div class="row">
                        <!--begin::Col Foto (Kiri)-->
                        <div class="col-md-4">
                            <div class="fv-row mb-7">
                                <label class="required form-label">Foto</label>
                                <div class="border rounded p-4 text-center">
                                    <div
                                        class="w-175px h-225px mx-auto overflow-hidden rounded border d-flex align-items-center justify-content-center bg-light">
                                        <img id="photo_preview" src="{{ asset('assets/media/avatars/blank.png') }}"
                                            alt="Preview" class="w-100 h-100 object-fit-cover" />
                                    </div>
                                    <input type="file" name="photo" class="form-control mt-4" accept="image/*"
                                        id="photo_input" />
                                    <span class="text-muted fs-7">Format: JPG, PNG. Maksimal 2MB</span>
                                    @error('photo')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="fv-row">
                                <label class="form-label">Contoh Foto</label>
                                <div class="border rounded p-3">
                                    <div class="w-100 h-auto overflow-hidden rounded">
                                        <img src="{{ asset('assets/media/contoh-foto/contoh1.png') }}" alt="Contoh Foto"
                                            class="w-100 h-auto" />
                                    </div>
                                    <span class="text-muted fs-7 mt-2 d-block">Pastikan foto sesuai format di
                                        atas</span>
                                </div>
                            </div>
                        </div>
                        <!--end::Col Foto-->

                        <!--begin::Col Form (Kanan)-->
                        <div class="col-md-8">
                            <div class="fv-row mb-7">
                                <label class="required form-label">Jenis Peserta</label>
                                <div class="d-flex flex-wrap gap-5">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="type" value="athlete"
                                            {{ old('type', 'athlete') === 'athlete' ? 'checked' : '' }}
                                            id="type_athlete" />
                                        <span class="form-check-label text-gray-600">Atlet</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="type" value="coach"
                                            {{ old('type') === 'coach' ? 'checked' : '' }} id="type_coach" />
                                        <span class="form-check-label text-gray-600">Pelatih</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="type" value="official"
                                            {{ old('type') === 'official' ? 'checked' : '' }} id="type_official" />
                                        <span class="form-check-label text-gray-600">Official</span>
                                    </label>
                                </div>
                                @error('type')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="fv-row mb-7">
                                <label class="required form-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control form-control-solid"
                                    placeholder="Masukkan nama lengkap" value="{{ old('name') }}" />
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="fv-row mb-7" id="field_nik">
                                <label class="required form-label">NIK</label>
                                <input type="text" name="nik" class="form-control form-control-solid"
                                    placeholder="Masukkan 16 digit NIK" value="{{ old('nik') }}" maxlength="16"
                                    inputmode="numeric" />
                                <span class="text-muted fs-7">Masukkan 16 digit NIK</span>
                                @error('nik')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="fv-row mb-7" id="field_birth_date">
                                <label class="required form-label">Tanggal Lahir</label>
                                <input type="text" name="birth_date" class="form-control form-control-solid"
                                    placeholder="Pilih tanggal lahir" value="{{ old('birth_date') }}"
                                    id="kt_datepicker_birth_date" inputmode="none" readonly />
                                @error('birth_date')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="fv-row mb-7" id="field_gender">
                                <label class="required form-label">Jenis Kelamin</label>
                                <div class="d-flex flex-wrap gap-5">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="gender" value="M"
                                            {{ old('gender') === 'M' ? 'checked' : '' }} id="gender_m" />
                                        <span class="form-check-label text-gray-600">Laki-laki</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="gender" value="F"
                                            {{ old('gender') === 'F' ? 'checked' : '' }} id="gender_f" />
                                        <span class="form-check-label text-gray-600">Perempuan</span>
                                    </label>
                                </div>
                                @error('gender')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="fv-row mb-7">
                                <label class="form-label">Provinsi</label>
                                <input type="text" name="provinsi" class="form-control form-control-solid"
                                    placeholder="Masukkan provinsi" value="{{ old('provinsi') }}" />
                                @error('provinsi')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="fv-row mb-7">
                                <label class="form-label">Institusi</label>
                                <input type="text" name="institusi" class="form-control form-control-solid"
                                    placeholder="Masukkan institusi" value="{{ old('institusi') }}" />
                                @error('institusi')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="fv-row mb-7" id="field_document">
                                <label class="required form-label">Dokumen</label>
                                <input type="file" name="document" class="form-control"
                                    accept=".jpg,.jpeg,.png,.pdf" id="document_input" />
                                <span class="text-muted fs-7">Format: JPG, PNG, PDF. Maksimal 5MB</span>
                                <div id="document_info" class="mt-2" style="display: none;">
                                    <span class="badge badge-light-info">{{ old('document_name', '') }}</span>
                                </div>
                                @error('document')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <!--end::Col Form-->
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('participants.index') }}" class="btn btn-light btn-active-light-primary me-2">
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
    @endif

    @push('scripts')
        <script>
            flatpickr("#kt_datepicker_birth_date", {
                dateFormat: "Y-m-d",
                maxDate: "today",
                allowInput: false
            });

            document.getElementById('photo_input').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) {
                    document.getElementById('photo_preview').src = '{{ asset('assets/media/avatars/blank.png') }}';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    toastr.error('Ukuran foto maksimal 2MB');
                    e.target.value = '';
                    document.getElementById('photo_preview').src = '{{ asset('assets/media/avatars/blank.png') }}';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('photo_preview').src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });

            document.getElementById('document_input').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) {
                    document.getElementById('document_info').style.display = 'none';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    toastr.error('Ukuran dokumen maksimal 5MB');
                    e.target.value = '';
                    return;
                }
                const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    toastr.error('Format dokumen harus JPG, PNG, atau PDF');
                    e.target.value = '';
                    return;
                }
                document.getElementById('document_info').querySelector('.badge').textContent = file.name;
                document.getElementById('document_info').style.display = 'block';
            });

            $('#kt_participant_form').on('submit', function() {
                var btn = $('#kt_btn_submit');
                btn.attr('data-kt-indicator', 'on');
                btn.attr('disabled', true);
            });
        </script>
    @endpush
</x-app-layout>
