<x-app-layout>
    @section('title', 'Detail Kategori - ' . $eventCategory->class_name)

    <style>
        .k-card {
            background: #fff;
            border: 1px dashed #e4e6ef;
            border-radius: 8px;
            margin-bottom: 10px;
            overflow: hidden
        }

        .k-card-hd {
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            user-select: none
        }

        .k-card-av {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .95rem
        }

        .k-card-nm {
            flex: 1;
            min-width: 0;
            font-weight: 700;
            font-size: .88rem;
            color: #3f4254;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .k-card-em {
            font-size: .72rem;
            color: #b5b5c3;
            font-weight: 600;
            margin-top: 2px
        }

        .k-card-arr {
            flex-shrink: 0;
            color: #b5b5c3;
            transition: transform .25s ease;
            font-size: .7rem
        }

        .k-card.open .k-card-arr {
            transform: rotate(180deg)
        }

        .k-card-bd {
            max-height: 0;
            overflow: hidden;
            transition: max-height .3s cubic-bezier(.4, 0, .2, 1)
        }

        .k-card.open .k-card-bd {
            max-height: 500px
        }

        .k-card-dt {
            padding: 0 14px 12px
        }

        .k-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid #f3f6f9
        }

        .k-card-row:last-child {
            border-bottom: none
        }

        .k-card-lbl {
            font-size: .72rem;
            color: #b5b5c3;
            font-weight: 600
        }

        .k-card-val {
            font-size: .78rem;
            color: #3f4254;
            font-weight: 600;
            text-align: right;
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .k-card-acts {
            display: flex;
            gap: 6px;
            padding: 6px 14px 12px;
            border-top: 1px dashed #e4e6ef
        }

        .k-card-acts .btn {
            flex: 1;
            font-size: .72rem;
            padding: 6px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px
        }
    </style>

    <div class="card mb-5">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">{{ $eventCategory->class_name }}</h3>
            </div>
            <div class="card-toolbar d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.events.show', $eventCategory->event) }}" class="btn btn-light btn-sm">← Kembali
                    ke Event</a>
                <a href="{{ route('admin.event-categories.edit', $eventCategory) }}"
                    class="btn btn-light-warning btn-sm">Edit Kategori</a>
            </div>
        </div>
        <div class="card-body py-5">
            <div class="row gy-4 mb-4">
                <div class="col-md-4">
                    <div class="text-muted fs-7">Event</div>
                    <div class="fw-bold">{{ $eventCategory->event->name }}</div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted fs-7">Type</div>
                    <div class="fw-bold">{{ $eventCategory->type->value }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-7">Rentang Lahir</div>
                    <div class="fw-bold">{{ $eventCategory->readableBirthRange() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">Sub-Kategori</h3>
            </div>
        </div>
        <div class="card-body py-4">
            <form action="{{ route('admin.event-categories.sub-categories.store', $eventCategory) }}" method="POST"
                class="row g-4 mb-6">
                @csrf
                <div class="col-md-3">
                    <label class="required form-label">Name</label>
                    <input type="text" name="name" class="form-control form-control-solid"
                        value="{{ old('name') }}" placeholder="Kumite -55kg">
                    @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="required form-label">Gender</label>
                    <select name="gender" class="form-select form-select-solid">
                        <option value="M">M</option>
                        <option value="F">F</option>
                        <option value="Mixed">Mixed</option>
                    </select>
                    @error('gender')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="required form-label">Price</label>
                    <input type="number" name="price" class="form-control form-control-solid"
                        value="{{ old('price', 0) }}" min="0" step="0.01">
                    @error('price')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="required form-label">Min Participants</label>
                    <input type="number" name="min_participants" class="form-control form-control-solid"
                        value="{{ old('min_participants', 1) }}" min="1">
                    @error('min_participants')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label class="required form-label">Max Participants</label>
                    <input type="number" name="max_participants" class="form-control form-control-solid"
                        value="{{ old('max_participants', 1) }}" min="1">
                    @error('max_participants')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Tambah</button>
                </div>
            </form>

            <div class="table-responsive d-none d-lg-block">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Price</th>
                            <th>Peserta</th>
                            <th>Label</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($eventCategory->subCategories as $subCategory)
                            <tr>
                                <td>{{ $subCategory->name }}</td>
                                <td>{{ $subCategory->gender->value }}</td>
                                <td>{{ number_format($subCategory->price, 2, ',', '.') }}</td>
                                <td>{{ $subCategory->min_participants }} - {{ $subCategory->max_participants }}</td>
                                <td><span
                                        class="badge {{ $subCategory->isTeam() ? 'badge-light-primary' : 'badge-light-secondary' }}">{{ $subCategory->labelType() }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.sub-categories.edit', $subCategory) }}"
                                        class="btn btn-light-warning btn-sm">Edit</a>
                                    <form action="{{ route('admin.sub-categories.destroy', $subCategory) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus sub-kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-light-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-10">Belum ada sub-kategori</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-block d-lg-none">
                @forelse ($eventCategory->subCategories as $subCategory)
                    <div class="k-card">
                        <div class="k-card-hd" onclick="this.parentElement.classList.toggle('open')">
                            <div class="k-card-av bg-light-success text-success">
                                {{ strtoupper(substr($subCategory->name, 0, 1)) }}</div>
                            <div style="flex:1;min-width:0">
                                <div class="k-card-nm">{{ $subCategory->name }}</div>
                                <div class="k-card-em">{{ $subCategory->labelType() }}</div>
                            </div>
                            <div class="k-card-arr"><i class="bi bi-chevron-down"></i></div>
                        </div>
                        <div class="k-card-bd">
                            <div class="k-card-dt">
                                <div class="k-card-row"><span class="k-card-lbl">Gender</span><span
                                        class="k-card-val">{{ $subCategory->gender->value }}</span></div>
                                <div class="k-card-row"><span class="k-card-lbl">Price</span><span
                                        class="k-card-val">{{ number_format($subCategory->price, 2, ',', '.') }}</span>
                                </div>
                                <div class="k-card-row"><span class="k-card-lbl">Peserta</span><span
                                        class="k-card-val">{{ $subCategory->min_participants }} -
                                        {{ $subCategory->max_participants }}</span></div>
                            </div>
                            <div class="k-card-acts">
                                <a href="{{ route('admin.sub-categories.edit', $subCategory) }}"
                                    class="btn btn-light-warning">Edit</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-10">Belum ada sub-kategori</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
