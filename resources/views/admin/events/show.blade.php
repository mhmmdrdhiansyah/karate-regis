<x-app-layout>
    @section('title', 'Detail Event - ' . $event->name)

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
                <h3 class="card-label fw-bold text-dark">{{ $event->name }}</h3>
            </div>
            <div class="card-toolbar d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.events.index') }}" class="btn btn-light btn-sm">← Kembali ke Daftar Event</a>
                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-light-warning btn-sm">Edit Event</a>
                <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                    onsubmit="return confirm('Hapus event ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-light-danger btn-sm">Hapus</button>
                </form>
                @if ($event->canTransitionTo(\App\Enums\EventStatus::RegistrationOpen))
                    <form action="{{ route('admin.events.transition', $event) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="registration_open">
                        <button type="submit" class="btn btn-light-success btn-sm"
                            onclick="return confirm('Ubah status menjadi registration open?')">Open
                            Registration</button>
                    </form>
                @elseif($event->canTransitionTo(\App\Enums\EventStatus::RegistrationClosed))
                    <form action="{{ route('admin.events.transition', $event) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="registration_closed">
                        <button type="submit" class="btn btn-light-warning btn-sm"
                            onclick="return confirm('Tutup pendaftaran?')">Close Registration</button>
                    </form>
                @elseif($event->canTransitionTo(\App\Enums\EventStatus::Ongoing))
                    <form action="{{ route('admin.events.transition', $event) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="ongoing">
                        <button type="submit" class="btn btn-light-primary btn-sm"
                            onclick="return confirm('Ubah status menjadi ongoing?')">Start Event</button>
                    </form>
                @elseif($event->canTransitionTo(\App\Enums\EventStatus::Completed))
                    <form action="{{ route('admin.events.transition', $event) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-light-dark btn-sm"
                            onclick="return confirm('Tandai event selesai?')">Complete</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body py-5">
            <div class="row gy-4 mb-4">
                <div class="col-md-3">
                    <div class="text-muted fs-7">Tanggal Event</div>
                    <div class="fw-bold">{{ $event->event_date?->format('d M Y') ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted fs-7">Deadline</div>
                    <div class="fw-bold">{{ $event->registration_deadline?->format('d M Y H:i') ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted fs-7">Fee Event</div>
                    <div class="fw-bold">{{ number_format($event->event_fee, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted fs-7">Fee Coach</div>
                    <div class="fw-bold">{{ number_format($event->coach_fee, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted fs-7">Status</div>
                    <div class="fw-bold"><span
                            class="badge {{ $event->statusBadgeClass() }}">{{ $event->statusLabel() }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">Kategori Event</h3>
            </div>
        </div>
        <div class="card-body py-4">
            <form action="{{ route('admin.events.categories.store', $event) }}" method="POST"
                class="row g-4 mb-6 align-items-end">
                @csrf
                <div class="col-12 col-md-2">
                    <label class="required form-label">Type</label>
                    <select name="type" class="form-select form-select-solid">
                        <option value="Open" @selected(old('type') === 'Open')>Open</option>
                        <option value="Festival" @selected(old('type') === 'Festival')>Festival</option>
                    </select>
                    @error('type')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="required form-label">Class Name</label>
                    <input type="text" name="class_name" class="form-control form-control-solid"
                        value="{{ old('class_name') }}">
                    @error('class_name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="required form-label">Min Birth Date</label>
                    <input type="text" name="min_birth_date" class="form-control form-control-solid"
                        id="kt_min_birth_date" value="{{ old('min_birth_date') }}">
                    @error('min_birth_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="required form-label">Max Birth Date</label>
                    <input type="text" name="max_birth_date" class="form-control form-control-solid"
                        id="kt_max_birth_date" value="{{ old('max_birth_date') }}">
                    @error('max_birth_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end">
                    <button type="submit"
                        class="btn btn-primary w-100 d-flex align-items-center justify-content-center"
                        title="Tambah Kategori" aria-label="Tambah Kategori">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </form>

            <div class="table-responsive d-none d-lg-block">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th>Type</th>
                            <th>Class</th>
                            <th>Range Lahir</th>
                            <th>Sub Category</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($event->categories as $category)
                            <tr>
                                <td><span class="badge badge-light-info">{{ $category->type->value }}</span></td>
                                <td><a href="{{ route('admin.event-categories.show', $category) }}"
                                        class="text-dark text-hover-primary fw-bold">{{ $category->class_name }}</a>
                                </td>
                                <td>{{ $category->readableBirthRange() }}</td>
                                <td>{{ $category->subCategories->count() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.event-categories.edit', $category) }}"
                                        class="btn btn-light-warning btn-sm">Edit</a>
                                    <form action="{{ route('admin.event-categories.destroy', $category) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-light-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-10">Belum ada kategori</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-block d-lg-none">
                @forelse ($event->categories as $category)
                    <div class="k-card">
                        <div class="k-card-hd" onclick="this.parentElement.classList.toggle('open')">
                            <div class="k-card-av bg-light-info text-info">
                                {{ strtoupper(substr($category->class_name, 0, 1)) }}</div>
                            <div style="flex:1;min-width:0">
                                <div class="k-card-nm">{{ $category->class_name }}</div>
                                <div class="k-card-em">{{ $category->readableBirthRange() }}</div>
                            </div>
                            <div class="k-card-arr"><i class="bi bi-chevron-down"></i></div>
                        </div>
                        <div class="k-card-bd">
                            <div class="k-card-dt">
                                <div class="k-card-row"><span class="k-card-lbl">Type</span><span
                                        class="k-card-val">{{ $category->type->value }}</span></div>
                                <div class="k-card-row"><span class="k-card-lbl">Sub Category</span><span
                                        class="k-card-val">{{ $category->subCategories->count() }}</span></div>
                            </div>
                            <div class="k-card-acts">
                                <a href="{{ route('admin.event-categories.show', $category) }}"
                                    class="btn btn-light-primary">Detail</a>
                                <a href="{{ route('admin.event-categories.edit', $category) }}"
                                    class="btn btn-light-warning">Edit</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-10">Belum ada kategori</div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            flatpickr('#kt_min_birth_date', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });
            flatpickr('#kt_max_birth_date', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });
        </script>
    @endpush
</x-app-layout>
