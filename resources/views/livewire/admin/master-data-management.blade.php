<div>
    {{-- Flash messages (pola payment-management) --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center p-5 mb-10" role="alert">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="fs-6">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap flex-stack mb-6">
        <h1 class="page-title text-gray-900 fw-bold">Master Data — Cabor & Perguruan</h1>
        <div class="d-flex align-items-center gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-solid" placeholder="Cari nama...">
        </div>
    </div>

    <div class="row g-5 g-xl-8">
        {{-- PANEL KIRI: SPORT --}}
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title"><span class="card-label fw-bolder fs-3 mb-1">Cabang Olahraga</span></h3>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_sport">
                        {{ __('Tambah Cabor') }}
                    </button>
                </div>
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-200 align-middle gs-3 gy-4">
                            <thead>
                                <tr class="fw-bolder text-muted">
                                    <th>Nama</th>
                                    <th>Perguruan</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->sports as $sport)
                                    <tr class="{{ $selectedSportId === $sport->id ? 'table-active' : '' }}">
                                        <td>
                                            <a href="#" wire:click="selectSport({{ $sport->id }})" class="text-gray-800 fw-bold text-hover-primary">
                                                {{ $sport->name }}
                                            </a>
                                            @if ($sport->code)<div class="text-muted fs-7">{{ $sport->code }}</div>@endif
                                        </td>
                                        <td>{{ $sport->perguruan_count }}</td>
                                        <td>
                                            <span class="badge badge-light-{{ $sport->is_active ? 'success' : 'danger' }} fs-7">
                                                {{ $sport->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button wire:click="toggleSportActive({{ $sport->id }})"
                                                        class="btn btn-icon btn-light btn-sm"
                                                        title="{{ $sport->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="bi bi-{{ $sport->is_active ? 'toggle-on' : 'toggle-off' }} fs-4"></i>
                                                </button>
                                                <button wire:click="editSport({{ $sport->id }})"
                                                        data-bs-toggle="modal" data-bs-target="#kt_modal_sport"
                                                        class="btn btn-icon btn-light btn-sm" title="Edit">
                                                    <i class="bi bi-pencil fs-4"></i>
                                                </button>
                                                <button wire:click="deleteSport({{ $sport->id }})"
                                                        wire:confirm="Hapus cabor {{ $sport->name }}?"
                                                        class="btn btn-icon btn-light-danger btn-sm" title="Hapus">
                                                    <i class="bi bi-trash fs-4"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($this->sports->isEmpty())
                                    <tr><td colspan="4" class="text-center text-muted">Tidak ada cabor ditemukan.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL KANAN: PERGURUAN --}}
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title"><span class="card-label fw-bolder fs-3 mb-1">Perguruan</span></h3>
                    @if ($selectedSportId)
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_perguruan">
                            {{ __('Tambah Perguruan') }}
                        </button>
                    @endif
                </div>
                <div class="card-body py-4">
                    @unless ($selectedSportId)
                        <div class="text-center text-muted py-10">
                            <i class="bi bi-arrow-left fs-2x"></i>
                            <p class="mt-3">Pilih cabor di panel kiri untuk melihat daftar perguruan.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-200 align-middle gs-3 gy-4">
                                <thead>
                                    <tr class="fw-bolder text-muted">
                                        <th>Nama</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->perguruan as $p)
                                        <tr>
                                            <td>
                                                <span class="text-gray-800 fw-bold">{{ $p->name }}</span>
                                                @if ($p->code)
                                                    <div class="text-muted fs-7">{{ $p->code }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-light-{{ $p->is_active ? 'success' : 'danger' }} fs-7">
                                                    {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button wire:click="togglePerguruanActive({{ $p->id }})"
                                                            class="btn btn-icon btn-light btn-sm"
                                                            title="{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <i class="bi bi-{{ $p->is_active ? 'toggle-on' : 'toggle-off' }} fs-4"></i>
                                                    </button>
                                                    <button wire:click="editPerguruan({{ $p->id }})"
                                                            data-bs-toggle="modal" data-bs-target="#kt_modal_perguruan"
                                                            class="btn btn-icon btn-light btn-sm" title="Edit">
                                                        <i class="bi bi-pencil fs-4"></i>
                                                    </button>
                                                    <button wire:click="deletePerguruan({{ $p->id }})"
                                                            wire:confirm="Hapus perguruan {{ $p->name }}?"
                                                            class="btn btn-icon btn-light-danger btn-sm" title="Hapus">
                                                        <i class="bi bi-trash fs-4"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($this->perguruan->isEmpty())
                                        <tr><td colspan="3" class="text-center text-muted">Tidak ada perguruan ditemukan.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL FORM SPORT --}}
    <div wire:ignore.self class="modal fade" id="kt_modal_sport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingSportId ? 'Edit Cabor' : 'Tambah Cabor' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="form-label">Nama Cabor <span class="text-danger">*</span></label>
                        <input type="text" wire:model="sportName" class="form-control form-control-solid {{ $errors->has('sportName') ? 'is-invalid' : '' }}">
                        @error('sportName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Kode</label>
                        <input type="text" wire:model="sportCode" class="form-control form-control-solid">
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Deskripsi</label>
                        <textarea wire:model="sportDescription" class="form-control form-control-solid" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button wire:click="saveSport" data-bs-dismiss="modal" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL FORM PERGURUAN --}}
    <div wire:ignore.self class="modal fade" id="kt_modal_perguruan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingPerguruanId ? 'Edit Perguruan' : 'Tambah Perguruan' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Hidden sport context: selectedSportId sudah divalidasi di savePerguruan() --}}
                    <div class="mb-5">
                        <label class="form-label">Nama Perguruan <span class="text-danger">*</span></label>
                        <input type="text" wire:model="perguruanName" class="form-control form-control-solid {{ $errors->has('perguruanName') ? 'is-invalid' : '' }}">
                        @error('perguruanName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Kode</label>
                        <input type="text" wire:model="perguruanCode" class="form-control form-control-solid">
                    </div>
                    @error('selectedSportId') <div class="text-danger fs-7">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button wire:click="savePerguruan" data-bs-dismiss="modal" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
