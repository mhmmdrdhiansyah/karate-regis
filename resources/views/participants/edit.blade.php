<x-app-layout>
    @section('title', 'Edit Peserta - ' . $participant->name)

    @if(count($lockedFields) > 0)
        <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex align-items-center p-5 mb-5">
            <span class="svg-icon svg-icon-2 me-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path opacity="0.3"
                        d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z"
                        fill="currentColor" />
                    <path d="M13 7H11V13H17V11H13V7Z" fill="currentColor" />
                </svg>
            </span>
            <div class="d-flex flex-column">
                <h5 class="mb-1 text-warning">Perhatian</h5>
                <span class="text-gray-600">
                    Beberapa field tidak dapat diubah karena peserta sudah terdaftar dalam pendaftaran aktif dan/atau sudah terverifikasi.
                </span>
            </div>
        </div>
    @endif

    <form action="{{ route('participants.update', $participant) }}" method="POST" id="kt_participant_form"
        class="form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="type" value="{{ $participant->type->value }}" />

        <div class="card mb-5">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-dark">Data Peserta</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Perbarui data peserta</span>
                </h3>
                <div class="card-toolbar">
                    @if($participant->type === \App\Enums\ParticipantType::Athlete)
                        <span class="badge badge-light-primary fs-7 fw-bolder px-4 py-2">Atlet</span>
                    @elseif($participant->type === \App\Enums\ParticipantType::Coach)
                        <span class="badge badge-light-success fs-7 fw-bolder px-4 py-2">Pelatih</span>
                    @else
                        <span class="badge badge-light-info fs-7 fw-bolder px-4 py-2">Official</span>
                    @endif
                </div>
            </div>

            <div class="card-body py-5">
                @php
                    $isNameLocked = in_array('name', $lockedFields);
                    $isNikLocked = in_array('nik', $lockedFields);
                    $isBirthDateLocked = in_array('birth_date', $lockedFields);
                    $isGenderLocked = in_array('gender', $lockedFields);
                    $isDocumentLocked = in_array('document', $lockedFields);
                @endphp

                <div class="row mb-7">
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="required form-label">
                                Nama Lengkap
                                @if($isNameLocked)
                                    <i class="bi bi-lock-fill text-warning ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="right" title="Field ini terkunci dan tidak dapat diubah"></i>
                                @endif
                            </label>
                            <input type="text" name="name" class="form-control form-control-solid"
                                value="{{ old('name', $participant->name) }}"
                                {{ $isNameLocked ? 'disabled' : '' }} />
                            @if($isNameLocked)
                                <input type="hidden" name="name" value="{{ $participant->name }}" />
                            @endif
                            @error('name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="form-label">Foto</label>
                            @if($participant->photo)
                                <div class="mb-3">
                                    <div class="symbol symbol-circle symbol-100px overflow-hidden">
                                        <img src="{{ Storage::url($participant->photo) }}" alt="{{ $participant->name }}"
                                            class="w-100 h-100 object-fit-cover" />
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="photo" class="form-control" accept="image/*" id="photo_input" />
                            <span class="text-muted fs-7">Kosongkan jika tidak ingin mengubah. Format: JPG, PNG. Maksimal 2MB</span>
                            <div id="photo_preview" class="mt-3" style="display: none;">
                                <div class="w-125px h-125px overflow-hidden rounded border">
                                    <img src="#" alt="Preview" class="w-100 h-100 object-fit-cover" />
                                </div>
                            </div>
                            @error('photo')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-7">
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="required form-label">
                                NIK
                                @if($isNikLocked)
                                    <i class="bi bi-lock-fill text-warning ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="right" title="Field ini terkunci dan tidak dapat diubah"></i>
                                @endif
                            </label>
                            <input type="text" name="nik" class="form-control form-control-solid"
                                value="{{ old('nik', $participant->nik) }}" maxlength="16" inputmode="numeric"
                                {{ $isNikLocked ? 'disabled' : '' }} />
                            @if($isNikLocked)
                                <input type="hidden" name="nik" value="{{ $participant->nik }}" />
                            @endif
                            <span class="text-muted fs-7">Masukkan 16 digit NIK</span>
                            @error('nik')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="required form-label">
                                Tanggal Lahir
                                @if($isBirthDateLocked)
                                    <i class="bi bi-lock-fill text-warning ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="right" title="Field ini terkunci dan tidak dapat diubah"></i>
                                @endif
                            </label>
                            <input type="text" name="birth_date" class="form-control form-control-solid"
                                value="{{ old('birth_date', $participant->birth_date?->format('Y-m-d')) }}"
                                data-flatpickr="true" inputmode="none"
                                {{ $isBirthDateLocked ? 'disabled' : '' }} />
                            @if($isBirthDateLocked)
                                <input type="hidden" name="birth_date" value="{{ $participant->birth_date?->format('Y-m-d') }}" />
                            @endif
                            @error('birth_date')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-7">
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="required form-label">
                                Jenis Kelamin
                                @if($isGenderLocked)
                                    <i class="bi bi-lock-fill text-warning ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="right" title="Field ini terkunci dan tidak dapat diubah"></i>
                                @endif
                            </label>
                            <div class="d-flex flex-wrap gap-5">
                                <label class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="gender" value="M"
                                        {{ old('gender', $participant->gender?->value) === 'M' ? 'checked' : '' }}
                                        id="gender_m" {{ $isGenderLocked ? 'disabled' : '' }} />
                                    <span class="form-check-label text-gray-600">Laki-laki</span>
                                </label>
                                <label class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="gender" value="F"
                                        {{ old('gender', $participant->gender?->value) === 'F' ? 'checked' : '' }}
                                        id="gender_f" {{ $isGenderLocked ? 'disabled' : '' }} />
                                    <span class="form-check-label text-gray-600">Perempuan</span>
                                </label>
                            </div>
                            @if($isGenderLocked)
                                <input type="hidden" name="gender" value="{{ $participant->gender?->value }}" />
                            @endif
                            @error('gender')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="form-label">
                                Provinsi
                                @if(in_array('provinsi', $lockedFields))
                                    <i class="bi bi-lock-fill text-warning ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="right" title="Field ini terkunci dan tidak dapat diubah"></i>
                                @endif
                            </label>
                            <input type="text" name="provinsi" class="form-control form-control-solid"
                                value="{{ old('provinsi', $participant->provinsi) }}"
                                {{ in_array('provinsi', $lockedFields) ? 'disabled' : '' }} />
                            @if(in_array('provinsi', $lockedFields))
                                <input type="hidden" name="provinsi" value="{{ $participant->provinsi }}" />
                            @endif
                            @error('provinsi')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-7">
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="form-label">
                                Institusi
                                @if(in_array('institusi', $lockedFields))
                                    <i class="bi bi-lock-fill text-warning ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="right" title="Field ini terkunci dan tidak dapat diubah"></i>
                                @endif
                            </label>
                            <input type="text" name="institusi" class="form-control form-control-solid"
                                value="{{ old('institusi', $participant->institusi) }}"
                                {{ in_array('institusi', $lockedFields) ? 'disabled' : '' }} />
                            @if(in_array('institusi', $lockedFields))
                                <input type="hidden" name="institusi" value="{{ $participant->institusi }}" />
                            @endif
                            @error('institusi')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fv-row">
                            <label class="form-label">
                                Dokumen
                                @if($isDocumentLocked)
                                    <i class="bi bi-lock-fill text-warning ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="right" title="Field ini terkunci dan tidak dapat diubah"></i>
                                @endif
                            </label>
                            @if($participant->document)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($participant->document) }}" target="_blank"
                                        class="btn btn-sm btn-light-primary">
                                        <i class="bi bi-download me-1"></i> Lihat Dokumen
                                    </a>
                                </div>
                            @endif
                            <input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                                id="document_input" {{ $isDocumentLocked ? 'disabled' : '' }} />
                            <span class="text-muted fs-7">Format: JPG, PNG, PDF. Maksimal 5MB</span>
                            <div id="document_info" class="mt-2" style="display: none;">
                                <span class="badge badge-light-info"></span>
                            </div>
                            @error('document')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end" id="form_actions">
            <a href="{{ route('participants.index') }}" class="btn btn-light btn-active-light-primary me-2">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" id="kt_btn_submit">
                <span class="indicator-label">Simpan Perubahan</span>
                <span class="indicator-progress">
                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });

            document.getElementById('photo_input').addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) {
                    document.getElementById('photo_preview').style.display = 'none';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    toastr.error('Ukuran foto maksimal 2MB');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (ev) {
                    document.getElementById('photo_preview').querySelector('img').src = ev.target.result;
                    document.getElementById('photo_preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            });

            @if(!$isDocumentLocked)
                document.getElementById('document_input')?.addEventListener('change', function (e) {
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
            @endif

            $('#kt_participant_form').on('submit', function () {
                var btn = $('#kt_btn_submit');
                btn.attr('data-kt-indicator', 'on');
                btn.attr('disabled', true);
            });
        </script>
    @endpush
</x-app-layout>
