<div>
    @section('title', 'Input Hasil Pertandingan')

    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1"><i class="bi bi-trophy text-warning"></i> Input Hasil Pertandingan: {{ $event->name }}</span>
            </h3>
        </div>
        
        <div class="card-body py-3">
            <div class="accordion accordion-icon-toggle" id="categoriesAccordion">
                @foreach ($event->categories as $index => $category)
                    <div class="mb-5 border-bottom border-light">
                        <div class="accordion-header py-3 d-flex cursor-pointer" data-bs-toggle="collapse" data-bs-target="#category_{{ $category->id }}">
                            <span class="accordion-icon"><i class="bi bi-chevron-down fs-4"></i></span>
                            <h4 class="text-dark fw-bolder mb-0 ms-3">{{ $category->class_name }}</h4>
                        </div>
                        <div id="category_{{ $category->id }}" class="collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#categoriesAccordion">
                            <div class="pt-5 pb-5">
                                @foreach ($category->subCategories as $subCategory)
                                    <div class="card card-bordered mb-7 shadow-sm">
                                        <div class="card-header bg-light-primary min-h-40px py-2">
                                            <h5 class="card-title text-primary m-0">{{ $subCategory->name }}</h5>
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
                                                            <th class="min-w-200px">Pilih Peserta/Tim</th>
                                                            <th class="w-50px text-end">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if (isset($slots[$subCategory->id]) && count($slots[$subCategory->id]) > 0)
                                                            @foreach ($slots[$subCategory->id] as $slotIndex => $slot)
                                                                <tr>
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
                                                                    <td>
                                                                        <select class="form-select form-select-sm" wire:model="slots.{{ $subCategory->id }}.{{ $slotIndex }}.registration_id">
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
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
