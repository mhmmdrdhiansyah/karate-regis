<div>
    @section('title', 'Template Sertifikat')

    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1"><i class="bi bi-award text-warning"></i> Template Sertifikat: {{ $event->name }}</span>
            </h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-primary" wire:click="create" data-bs-toggle="modal" data-bs-target="#templateFormModal">
                    <i class="bi bi-plus-lg"></i> Tambah Template
                </button>
            </div>
        </div>
        <div class="card-body py-3">
            @if (session()->has('certificate-templates-success'))
                <div class="alert alert-success p-3 fs-7 mb-4">{{ session('certificate-templates-success') }}</div>
            @endif

            @if ($templates->isEmpty())
                <div class="text-center text-muted py-10">
                    <i class="bi bi-image fs-1 d-block mb-3"></i>
                    Belum ada template. Upload minimal satu template scope <code>fallback</code> agar semua sertifikat bisa dicetak.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bolder text-muted">
                                <th class="min-w-100px">Thumbnail</th>
                                <th class="min-w-150px">Nama</th>
                                <th class="min-w-150px">Scope</th>
                                <th class="min-w-100px">Orientasi</th>
                                <th class="min-w-100px">Aktif</th>
                                <th class="w-125px text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($templates as $template)
                                <tr wire:key="tpl-{{ $template->id }}">
                                    <td>
                                        <img src="{{ $template->image_url }}" alt="{{ $template->name }}" class="rounded" style="max-height: 60px; max-width: 100px; object-fit: contain;">
                                    </td>
                                    <td class="fw-bold">{{ $template->name }}</td>
                                    <td>
                                        <span class="badge badge-light-dark">{{ str_replace('_', ' ', $template->scope->value) }}</span>
                                    </td>
                                    <td>{{ ucfirst($template->orientation) }}</td>
                                    <td>
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" {{ $template->is_active ? 'checked' : '' }}
                                                   wire:click="toggleActive({{ $template->id }})">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                                                wire:click="edit({{ $template->id }})" data-bs-toggle="modal" data-bs-target="#templateFormModal"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                                                wire:click="delete({{ $template->id }})"
                                                wire:confirm="Hapus template {{ $template->name }}?" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Form --}}
    <div class="modal fade" id="templateFormModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} Template Sertifikat</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row g-5">
                        <div class="col-lg-6">
                            <div class="fv-row mb-5">
                                <label class="form-label">Nama Template</label>
                                <input type="text" class="form-control" wire:model="name" placeholder="Mis. Sertifikat Juara Emas">
                                @error('name') <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="row g-3 mb-5">
                                <div class="col-6 fv-row">
                                    <label class="form-label">Scope</label>
                                    <select class="form-select" wire:model="scope">
                                        @foreach ($scopes::cases() as $case)
                                            <option value="{{ $case->value }}">{{ ucfirst(str_replace('_', ' ', $case->value)) }}</option>
                                        @endforeach
                                    </select>
                                    @error('scope') <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 fv-row">
                                    <label class="form-label">Orientasi</label>
                                    <select class="form-select" wire:model="orientation">
                                        <option value="landscape">Landscape</option>
                                        <option value="portrait">Portrait</option>
                                    </select>
                                    @error('orientation') <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="fv-row mb-5">
                                <label class="form-label">Gambar Template (PNG/JPG, max 5MB){{ $existingImagePath ? ' — biarkan kosong jika tidak diganti' : '' }}</label>
                                <input type="file" class="form-control" wire:model="image" accept="image/png,image/jpeg">
                                @error('image') <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                @if ($image)
                                    <div class="fs-8 text-muted mt-1">{{ $image->getFilename() }} — preview di kanan setelah diunggah</div>
                                @endif
                            </div>

                            <h6 class="fw-bolder mt-8 mb-4">Posisi & Ukuran Teks (%)</h6>
                            <div class="row g-3">
                                @php
                                    $fields = [
                                        ['name_x', 'Nama: X'], ['name_y', 'Nama: Y'], ['name_font_size', 'Nama: Ukuran'],
                                        ['category_x', 'Kategori: X'], ['category_y', 'Kategori: Y'], ['category_font_size', 'Kategori: Ukuran'],
                                        ['status_x', 'Status: X'], ['status_y', 'Status: Y'], ['status_font_size', 'Status: Ukuran'],
                                    ];
                                @endphp
                                @foreach ($fields as [$field, $label])
                                    <div class="col-4 fv-row">
                                        <label class="form-label fs-8">{{ $label }}</label>
                                        <input type="number" class="form-control form-control-sm" min="0" max="100" step="0.5" wire:model.live="{{ $field }}">
                                        @error($field) <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Preview live --}}
                        <div class="col-lg-6">
                            <label class="form-label fw-bolder">Preview Live</label>
                            <div class="position-relative border rounded overflow-hidden bg-light" style="aspect-ratio: {{ $orientation === 'portrait' ? '210/297' : '297/210' }};">
                                <img src="{{ $image ? $image->temporaryUrl() : ($existingImagePath ? Storage::disk('public')->url($existingImagePath) : '') }}"
                                     alt="" class="w-100 h-100" style="object-fit: contain;" @if (!$image && !$existingImagePath) hidden @endif>
                                @if ($image || $existingImagePath)
                                    <div class="position-absolute text-center text-dark fw-bold" style="font-size: {{ $name_font_size }}cqh; left: {{ $name_x }}%; top: {{ $name_y }}%; transform: translate(-50%, -50%); white-space: nowrap; container-type: size;">
                                        CONTOH NAMA PESERTA
                                    </div>
                                    <div class="position-absolute text-center text-dark" style="font-size: {{ $category_font_size }}cqh; left: {{ $category_x }}%; top: {{ $category_y }}%; transform: translate(-50%, -50%); white-space: nowrap;">
                                        Kelas — Sub Kategori
                                    </div>
                                    <div class="position-absolute text-center text-dark fw-bold" style="font-size: {{ $status_font_size }}cqh; left: {{ $status_x }}%; top: {{ $status_y }}%; transform: translate(-50%, -50%); white-space: nowrap;">
                                        JUARA 1
                                    </div>
                                @endif
                            </div>
                            <div class="fs-8 text-muted mt-2">Teks bergeser real-time saat angka posisi diubah.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" wire:click="save" wire:target="save">
                        <span wire:loading.remove wire:target="save">Simpan</span>
                        <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm"></span> Menyimpan…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
