<div>
    @section('title', 'Template Sertifikat')
    @once
        <style>
            @font-face { font-family: 'GreatVibes'; src: url('{{ asset('fonts/certificate/GreatVibes-Regular.ttf') }}'); }
            @font-face { font-family: 'DancingScript'; src: url('{{ asset('fonts/certificate/DancingScript.ttf') }}'); }
            @font-face { font-family: 'Caveat'; src: url('{{ asset('fonts/certificate/Caveat.ttf') }}'); }
        </style>
    @endonce

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
    <div wire:ignore.self class="modal fade" id="templateFormModal" tabindex="-1" aria-hidden="true">
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

                            <div class="d-flex justify-content-between align-items-center mt-8 mb-4">
                                <h6 class="fw-bolder mb-0">Teks Sertifikat</h6>
                                <button type="button" class="btn btn-sm btn-light-primary" wire:click="addText" wire:target="addText">
                                    <i class="bi bi-plus-lg"></i> Tambah Teks
                                </button>
                            </div>
                            <div class="fs-8 text-muted mb-3">
                                Placeholder: <code>{nama}</code> <code>{kategori}</code> <code>{kelas}</code> <code>{subkategori}</code> <code>{status}</code> <code>{event}</code> <code>{kontingen}</code> — dicampur teks bebas. Nomor sertifikat: tulis format bebas lalu sisipkan <code>{xxx}</code> untuk nomor urut, mis. <code>apr/7yh652/260829/{xxx}</code>.
                            </div>
                            @foreach ($texts as $i => $t)
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold fs-7">Teks #{{ $i + 1 }}</span>
                                        <button type="button" class="btn btn-sm btn-light-danger" wire:click="removeText({{ $i }})" wire:target="removeText({{ $i }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="fv-row mb-2">
                                        <label class="form-label fs-8">Konten</label>
                                        <input type="text" class="form-control form-control-sm" wire:model.live="texts.{{ $i }}.content" placeholder="Mis. {nama} — Festival Karate">
                                        @error("texts.{$i}.content") <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-3 fv-row">
                                            <label class="form-label fs-8">X (%)</label>
                                            <input type="number" class="form-control form-control-sm" min="0" max="100" step="0.5" wire:model.live="texts.{{ $i }}.x">
                                            @error("texts.{$i}.x") <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-3 fv-row">
                                            <label class="form-label fs-8">Y (%)</label>
                                            <input type="number" class="form-control form-control-sm" min="0" max="100" step="0.5" wire:model.live="texts.{{ $i }}.y">
                                            @error("texts.{$i}.y") <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-3 fv-row">
                                            <label class="form-label fs-8">Ukuran (%)</label>
                                            <input type="number" class="form-control form-control-sm" min="0" max="100" step="0.1" wire:model.live="texts.{{ $i }}.font_size">
                                            @error("texts.{$i}.font_size") <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-3 d-flex align-items-end">
                                            <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                <input class="form-check-input" type="checkbox" id="bold-{{ $i }}" wire:model.live="texts.{{ $i }}.bold">
                                                <label class="form-check-label fs-8" for="bold-{{ $i }}">Tebal</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-1">
                                        <div class="col-8 fv-row">
                                            <label class="form-label fs-8">Font</label>
                                            <select class="form-select form-select-sm" wire:model.live="texts.{{ $i }}.font_family">
                                                <option value="times">Times New Roman</option>
                                                <option value="helvetica">Helvetica</option>
                                                <option value="arial">Arial</option>
                                                <option value="courier">Courier</option>
                                                <option value="greatvibes">Great Vibes (tulisan tangan)</option>
                                                <option value="dancingscript">Dancing Script (tulisan tangan)</option>
                                                <option value="caveat">Caveat (tulisan tangan)</option>
                                            </select>
                                            @error("texts.{$i}.font_family") <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-4 fv-row">
                                            <label class="form-label fs-8">Warna</label>
                                            <input type="color" class="form-control form-control-color form-control-sm w-100" wire:model.live="texts.{{ $i }}.color">
                                            @error("texts.{$i}.color") <div class="text-danger fs-8 mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Preview live — sticky: diam saat form kiri di-scroll --}}
                        <div class="col-lg-6">
                            <div class="position-sticky top-0" style="z-index: 10;">
                            <label class="form-label fw-bolder">Preview Live</label>
                            <div class="position-relative border rounded overflow-hidden bg-light" style="aspect-ratio: {{ $orientation === 'portrait' ? '210/297' : '297/210' }}; container-type: size;">
                                <img src="{{ $image ? $image->temporaryUrl() : ($existingImagePath ? asset('storage/' . ltrim($existingImagePath, '/')) : '') }}"
                                     alt="" class="w-100 h-100" style="object-fit: contain;" @if (!$image && !$existingImagePath) hidden @endif>
                                @if ($image || $existingImagePath)
                                    @foreach ($texts as $t)
                                        @php
                                            $sample = strtr($t['content'], [
                                                '{nama}' => 'CONTOH NAMA PESERTA',
                                                '{kategori}' => 'Open',
                                        '{kelas}' => 'DEWASA',
                                        '{subkategori}' => 'KATA Individu Putra',
                                        '{xxx}' => '001',
                                                '{status}' => 'JUARA 1',
                                                '{event}' => $event->name,
                                                '{kontingen}' => 'Nama Kontingen',
                                            ]);
                                        @endphp
                                        @php
                                            $fontStacks = [
                                                'times' => "'Times New Roman', Times, serif",
                                                'helvetica' => 'Helvetica, Arial, sans-serif',
                                                'arial' => 'Arial, Helvetica, sans-serif',
                                                'courier' => "'Courier New', Courier, monospace",
                                                'greatvibes' => "'GreatVibes', cursive",
                                                'dancingscript' => "'DancingScript', cursive",
                                                'caveat' => "'Caveat', cursive",
                                            ];
                                            $font = $fontStacks[$t['font_family'] ?? 'times'] ?? $fontStacks['times'];
                                        @endphp
                                        <div class="position-absolute text-center" style="font-size: {{ $t['font_size'] }}cqh; left: {{ $t['x'] }}%; top: {{ $t['y'] }}%; transform: translate(-50%, -50%); white-space: nowrap; font-family: {{ $font }}; color: {{ $t['color'] ?? '#000000' }}; {{ !empty($t['bold']) ? 'font-weight: bold;' : '' }}">
                                            {{ $sample }}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="fs-8 text-muted mt-2">Teks bergeser real-time saat angka posisi diubah.</div>
                            </div>{{-- /sticky --}}
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

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', () => {
                var modalEl = document.getElementById('templateFormModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
        });
    </script>
@endpush
