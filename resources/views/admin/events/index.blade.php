<x-app-layout>
    @section('title', 'Manajemen Event')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">Manajemen Event</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm d-flex align-items-center"
                    title="Tambah Event" aria-label="Tambah Event">
                    <span class="svg-icon svg-icon-2 me-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none">
                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                transform="rotate(-90 11.364 20.364)" fill="currentColor" />
                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                fill="currentColor" />
                        </svg>
                    </span>
                    Tambah Event
                </a>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive event-table-responsive d-none d-lg-block">
                <table class="table align-middle table-row-dashed fs-6 gy-5 event-table">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="min-w-70px" style="width: 3ch;">No</th>
                            <th class="min-w-125px">Poster</th>
                            <th class="min-w-200px">Nama Event</th>
                            <th class="min-w-125px">Fee Event</th>
                            <th class="min-w-125px">Status</th>
                            <th class="text-end min-w-150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @forelse ($events as $event)
                            <tr>
                                <td>{{ ($events->firstItem() ?? 0) + $loop->index }}</td>
                                <td>
                                    <div class="rounded-3 overflow-hidden border bg-light"
                                        style="width: 108px; height: 144px;">
                                        <img src="{{ $event->poster ? asset('storage/' . $event->poster) : asset('assets/media/avatars/blank.png') }}"
                                            alt="Poster {{ $event->name }}" class="w-100 h-100 object-fit-cover">
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.events.show', $event) }}"
                                        class="text-gray-800 text-hover-primary fw-bold">{{ $event->name }}</a>
                                </td>
                                <td>{{ number_format($event->event_fee, 2, ',', '.') }}</td>
                                <td>
                                    <span
                                        class="badge {{ $event->statusBadgeClass() }}">{{ $event->statusLabel() }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-shrink-0 gap-2">
                                        <a href="{{ route('admin.events.show', $event) }}"
                                            class="btn btn-icon btn-light-primary btn-sm" title="Detail"
                                            aria-label="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.events.edit', $event) }}"
                                            class="btn btn-icon btn-light-warning btn-sm" title="Edit"
                                            aria-label="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                                            onsubmit="return confirm('Hapus event ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-light-danger btn-sm"
                                                title="Hapus" aria-label="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-10">Belum ada event</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-block d-lg-none">
                @forelse ($events as $event)
                    <div class="k-card">
                        <div class="k-card-hd" onclick="this.parentElement.classList.toggle('open')">
                            <div class="k-card-av bg-light overflow-hidden border">
                                <img src="{{ $event->poster ? asset('storage/' . $event->poster) : asset('assets/media/avatars/blank.png') }}"
                                    alt="Poster {{ $event->name }}" class="w-100 h-100 object-fit-cover">
                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="k-card-nm">{{ $event->name }}</div>
                                <div class="k-card-em">{{ number_format($event->event_fee, 2, ',', '.') }}</div>
                            </div>
                            <div class="badge {{ $event->statusBadgeClass() }}">{{ $event->statusLabel() }}</div>
                            <div class="k-card-arr"><i class="bi bi-chevron-down"></i></div>
                        </div>
                        <div class="k-card-bd">
                            <div class="k-card-dt">
                                <div class="k-card-row"><span class="k-card-lbl">Nama Event</span><span
                                        class="k-card-val">{{ $event->name }}</span>
                                </div>
                            </div>
                            <div class="k-card-acts">
                                <a href="{{ route('admin.events.show', $event) }}"
                                    class="btn btn-icon btn-light-primary" title="Detail" aria-label="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.events.edit', $event) }}"
                                    class="btn btn-icon btn-light-warning" title="Edit" aria-label="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                                    onsubmit="return confirm('Hapus event ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-light-danger" title="Hapus"
                                        aria-label="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-10">Belum ada event</div>
                @endforelse
            </div>

            @if ($events->count() > 0)
                <div class="row">
                    <div
                        class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="dataTables_info">Menampilkan {{ $events->firstItem() }} sampai
                            {{ $events->lastItem() }} dari {{ $events->total() }} data</div>
                    </div>
                    <div
                        class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                        {{ $events->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <style>
            .event-table-responsive {
                overflow-x: auto;
                overflow-y: hidden;
                scrollbar-width: thin;
                scrollbar-color: #4b5563 #e5e7eb;
                -ms-overflow-style: auto;
                padding-bottom: 6px;
            }

            .event-table-responsive::-webkit-scrollbar {
                height: 10px;
            }

            .event-table-responsive::-webkit-scrollbar-track {
                background: #e5e7eb;
                border-radius: 999px;
            }

            .event-table-responsive::-webkit-scrollbar-thumb {
                background: #4b5563;
                border-radius: 999px;
                border: 2px solid #e5e7eb;
            }

            .event-table-responsive::-webkit-scrollbar-thumb:hover {
                background: #374151;
            }

            .event-table {
                min-width: 1200px;
            }

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
                width: 63px;
                height: 63px;
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
    @endpush
</x-app-layout>
