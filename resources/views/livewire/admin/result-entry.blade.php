<div>
    @section('title', 'Input Hasil Pertandingan')

    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1"><i class="bi bi-trophy text-warning"></i> Input Hasil Pertandingan: {{ $event->name }}</span>
            </h3>
        </div>
        
        <div class="card-body py-3">
            {{-- Navigasi sidebar tree: tipe (collapsible, Open dulu) → kelas → konten kelas aktif --}}
            @php $activeCategory = $this->activeCategory; @endphp

            @if ($activeCategory)
                <div class="d-flex flex-column flex-xl-row gap-6">
                    {{-- Sidebar tree — sticky di desktop, collapse biasa di mobile --}}
                    <div class="flex-shrink-0 width-250px">
                        <div class="position-sticky top-20px">
                            {{-- Toggle mobile: tampilkan kelas aktif, klik untuk buka tree --}}
                            <button class="btn btn-sm btn-light-primary w-100 d-flex justify-content-between align-items-center d-xl-none mb-3"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#resultTree">
                                <span class="fw-bold">
                                    <i class="bi bi-diagram-3 me-1"></i>
                                    {{ $activeType }} · {{ $activeCategory->class_name }}
                                </span>
                                <i class="bi bi-chevron-down"></i>
                            </button>

                            <div class="collapse d-xl-block" id="resultTree">
                                <div class="border border-gray-300 rounded p-3">
                                    @foreach ($groupedCategories as $type => $categoriesInType)
                                        @php $isActiveType = $activeType === $type; @endphp
                                        {{-- Header tipe: toggle collapse Bootstrap, bukan navigasi --}}
                                        <a class="d-flex align-items-center justify-content-between py-2 px-2 text-dark fw-bolder text-hover-primary cursor-pointer
                                                  {{ ! $loop->first ? 'border-top border-gray-300' : '' }}"
                                           data-bs-toggle="collapse" data-bs-target="#treeType_{{ \Illuminate\Support\Str::slug($type) }}"
                                           aria-expanded="{{ $isActiveType }}">
                                            <span>
                                                @if ($isActiveType)
                                                    <i class="bi bi-folder2-open text-primary me-2"></i>
                                                @else
                                                    <i class="bi bi-folder2 text-muted me-2"></i>
                                                @endif
                                                {{ $type }}
                                            </span>
                                            <i class="bi bi-chevron-down fs-8 {{ $isActiveType ? '' : 'rotate-180' }}"></i>
                                        </a>

                                        {{-- Daftar kelas dalam tipe — tipe aktif terbuka --}}
                                        <div class="collapse {{ $isActiveType ? 'show' : '' }}" id="treeType_{{ \Illuminate\Support\Str::slug($type) }}">
                                            @foreach ($categoriesInType as $classCategory)
                                                <a class="d-block py-2 px-4 ms-4 rounded fs-7 cursor-pointer
                                                          {{ $activeClassId === $classCategory['id']
                                                                ? 'bg-primary text-white fw-bold'
                                                                : 'text-gray-700 text-hover-primary' }}"
                                                   wire:click="selectClass('{{ $type }}', {{ $classCategory['id'] }})">
                                                    <i class="bi bi-dot me-1"></i>{{ $classCategory['class_name'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Konten: sub-kategori kelas aktif --}}
                    <div class="flex-grow-1 min-w-100px">
                                @foreach ($activeCategory->subCategories as $subCategory)
                                    <div class="card card-bordered mb-7 shadow-sm">
                                        <div class="card-header bg-light-primary min-h-40px py-2">
                                            <h5 class="card-title text-primary m-0">
                                                {{ $subCategory->full_name }}
                                            </h5>
                                        </div>
                                        <div class="card-body py-4">
                                            @if (session()->has("success_{$subCategory->id}"))
                                                <div class="alert alert-success p-3 fs-7 mb-4">
                                                    {{ session("success_{$subCategory->id}") }}
                                                </div>
                                            @endif
                                            @if (session()->has("error_{$subCategory->id}"))
                                                <div class="alert alert-danger p-3 fs-7 mb-4">
                                                    {{ session("error_{$subCategory->id}") }}
                                                </div>
                                            @endif

                                            <div class="table-responsive">
                                                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bolder text-muted">
                                                            <th class="min-w-150px">Label Juara</th>
                                                            <th class="w-150px">Jenis Medali</th>
                                                            <th class="min-w-250px">Pilih Peserta/Tim</th>
                                                            <th class="w-50px text-end">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if (isset($slots[$subCategory->id]) && count($slots[$subCategory->id]) > 0)
                                                            @foreach ($slots[$subCategory->id] as $slotIndex => $slot)
                                                                <tr wire:key="slot-{{ $subCategory->id }}-{{ $slot['key'] ?? $slotIndex }}">
                                                                    <td>
                                                                        <input type="text" class="form-control form-control-sm" placeholder="Misal: Juara 4" wire:model="slots.{{ $subCategory->id }}.{{ $slotIndex }}.rank_name">
                                                                    </td>
                                                                    <td>
                                                                        <select class="form-select form-select-sm" wire:model="slots.{{ $subCategory->id }}.{{ $slotIndex }}.medal_type">
                                                                            <option value="Gold">Gold</option>
                                                                            <option value="Silver">Silver</option>
                                                                            <option value="Bronze">Bronze</option>
                                                                            <option value="">Tanpa Medali</option>
                                                                        </select>
                                                                    </td>
                                                                    <td wire:ignore.self>
                                                                        <div wire:ignore
                                                                             x-data="{
                                                                                 model: @entangle('slots.'.$subCategory->id.'.'.$slotIndex.'.registration_id')
                                                                             }"
                                                                             x-init="
                                                                                 const $select = $( $refs.select );
                                                                                 $select.select2({
                                                                                     placeholder: '-- Pilih Peserta --',
                                                                                     allowClear: true,
                                                                                     width: '100%'
                                                                                 });
                                                                                 $select.on('change', function () {
                                                                                     model = $select.val();
                                                                                 });
                                                                                 // Set nilai awal dari hasil yang sudah tersimpan —
                                                                                 // $watch tidak fire saat init, jadi tanpa ini
                                                                                 // Select2 selalu tampil kosong padahal data ada.
                                                                                 if (model) {
                                                                                     $select.val(model).trigger('change.select2');
                                                                                 }
                                                                                 $watch('model', value => {
                                                                                     if ($select.val() != value) {
                                                                                         $select.val(value).trigger('change.select2');
                                                                                     }
                                                                                 });
                                                                             "
                                                                             class="w-100"
                                                                        >
                                                                            <select x-ref="select" class="form-select form-select-sm" data-placeholder="-- Pilih Peserta --">
                                                                                <option value="">-- Pilih Peserta --</option>
                                                                                @foreach ($subCategory->registrations as $reg)
                                                                                    <option value="{{ $reg->id }}">
                                                                                        @if($reg->participant)
                                                                                            {{ $reg->participant->name }} ({{ optional($reg->participant->contingent)->name ?? '-' }})
                                                                                        @elseif($reg->teamGroup)
                                                                                            {{ $reg->teamGroup->name }} ({{ optional($reg->teamGroup->contingent)->name ?? '-' }})
                                                                                        @endif
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <button type="button" class="btn btn-icon btn-light-danger btn-sm" wire:click="removeSlot({{ $subCategory->id }}, {{ $slotIndex }})" title="Hapus Slot">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted">Belum ada slot juara. Silakan klik Tambah Juara.</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="d-flex justify-content-between mt-4">
                                                <button type="button" class="btn btn-light-primary btn-sm" wire:click="addSlot({{ $subCategory->id }})">
                                                    <i class="bi bi-plus-lg"></i> Tambah Juara
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm" wire:click="save({{ $subCategory->id }})">
                                                    Simpan Hasil
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                    </div>
                </div>
@endif
        </div>
    </div>
</div>
