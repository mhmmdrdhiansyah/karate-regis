<x-app-layout>
    @section('title', 'Bank Peserta')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">Bank Peserta</h3>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <div class="w-150px">
                        <select class="form-select form-select-solid form-select-sm" id="kt_filter_type"
                            data-kt-table-filter="type">
                            <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>
                                Semua
                            </option>
                            <option value="athlete" {{ request('type') === 'athlete' ? 'selected' : '' }}>
                                Atlet
                            </option>
                            <option value="coach" {{ request('type') === 'coach' ? 'selected' : '' }}>
                                Pelatih
                            </option>
                            <option value="official" {{ request('type') === 'official' ? 'selected' : '' }}>
                                Official
                            </option>
                        </select>
                    </div>
                    @php $__puser = auth()->user(); @endphp
                    @if (
                        $__puser &&
                            ($__puser->can('create participants') ||
                                $__puser->can('manage participants') ||
                                $__puser->can('manage own participants')))
                        <a href="{{ route('participants.create') }}" class="btn btn-primary d-flex align-items-center">
                            <span class="svg-icon svg-icon-2 me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                                        rx="1" transform="rotate(-90 11.364 20.364)" fill="currentColor" />
                                    <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                        fill="currentColor" />
                                </svg>
                            </span>
                            Tambah Peserta
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body py-4">

            {{-- Desktop/Tablet: Table Layout --}}
            <div class="table-responsive d-none d-lg-block">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="min-w-50px">No</th>
                            <th class="min-w-50px">Foto</th>
                            <th class="min-w-200px">Nama</th>
                            <th class="min-w-150px">NIK</th>
                            <th class="min-w-125px">Tgl Lahir</th>
                            <th class="min-w-125px">Gender</th>
                            <th class="min-w-100px">Jenis</th>
                            <th class="min-w-125px">Status</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @forelse ($participants as $participant)
                            <tr>
                                <td class="text-gray-500 text-center">{{ $participants->firstItem() + $loop->index }}
                                </td>
                                <td>
                                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3"
                                        style="width:50px;height:50px">
                                        @if ($participant->photo)
                                            <img src="{{ Storage::url($participant->photo) }}"
                                                alt="{{ $participant->name }}"
                                                style="width:50px;height:50px;object-fit:cover" />
                                        @else
                                            <div
                                                class="symbol-label fs-3 {{ $participant->type === \App\Enums\ParticipantType::Coach ? 'bg-light-success text-success' : ($participant->type === \App\Enums\ParticipantType::Official ? 'bg-light-info text-info' : 'bg-light-warning text-warning') }}">
                                                {{ strtoupper(substr($participant->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('participants.show', $participant) }}"
                                        class="text-gray-800 text-hover-primary fw-bold">
                                        {{ $participant->name }}
                                    </a>
                                </td>
                                <td>{{ $participant->nik ?? '-' }}</td>
                                <td>{{ $participant->birth_date?->format('d M Y') ?? '-' }}</td>
                                <td>
                                    @if ($participant->gender)
                                        {{ $participant->gender === \App\Enums\ParticipantGender::Male ? 'Laki-laki' : 'Perempuan' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($participant->type === \App\Enums\ParticipantType::Athlete)
                                        <span class="badge badge-light-primary">Atlet</span>
                                    @elseif ($participant->type === \App\Enums\ParticipantType::Coach)
                                        <span class="badge badge-light-success">Pelatih</span>
                                    @else
                                        <span class="badge badge-light-info">Official</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($participant->is_verified)
                                        <span class="badge badge-light-success d-flex align-items-center">
                                            <i class="bi bi-check-circle me-1"></i> Terverifikasi
                                        </span>
                                    @else
                                        <span class="badge badge-light-warning">Belum</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-shrink-0">
                                        @php
                                            $canEdit =
                                                $__puser &&
                                                ($__puser->can('edit participants') ||
                                                    $__puser->can('manage participants') ||
                                                    ($__puser->can('manage own participants') &&
                                                        $participant->contingent_id === $__puser->contingent?->id));
                                            $canDelete =
                                                $__puser &&
                                                ($__puser->can('delete participants') ||
                                                    $__puser->can('manage participants') ||
                                                    ($__puser->can('manage own participants') &&
                                                        $participant->contingent_id === $__puser->contingent?->id));
                                        @endphp
                                        @if ($canEdit)
                                            <a href="{{ route('participants.edit', $participant) }}"
                                                class="btn btn-icon btn-light-warning btn-sm me-1" title="Edit">
                                                <span class="svg-icon svg-icon-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                        height="24" viewBox="0 0 24 24" fill="none">
                                                        <path opacity="0.3"
                                                            d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                                            fill="currentColor" />
                                                        <path
                                                            d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.37449 21.6179C2.21036 21.4495 2.09202 1.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                                            fill="currentColor" />
                                                    </svg>
                                                </span>
                                            </a>
                                        @endif
                                        @if ($canDelete)
                                            <form action="{{ route('participants.destroy', $participant) }}"
                                                method="POST" id="delete-form-{{ $participant->id }}"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a href="#" class="btn btn-icon btn-light-danger btn-sm"
                                                onclick="confirmDelete(event, {{ $participant->id }})" title="Hapus">
                                                <span class="svg-icon svg-icon-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                        height="24" viewBox="0 0 24 24" fill="none">
                                                        <path
                                                            d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z"
                                                            fill="currentColor" />
                                                        <path opacity="0.5"
                                                            d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z"
                                                            fill="currentColor" />
                                                        <path opacity="0.5"
                                                            d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z"
                                                            fill="currentColor" />
                                                    </svg>
                                                </span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="svg-icon svg-icon-4x mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path opacity="0.3"
                                                    d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z"
                                                    fill="black" />
                                                <path
                                                    d="M21 5H14.8L12 2H3C2.4 2 2 2.4 2 3V18C2 18.6 2.4 19 3 19H21C21.6 19 22 18.6 22 18V6C22 5.4 21.6 5 21 5Z"
                                                    fill="black" />
                                            </svg>
                                        </span>
                                        <span class="fw-semibold">Belum ada data peserta</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile: Collapsible Card Layout --}}
            <div class="d-block d-lg-none">
                <style>
                    .p-card {
                        background: #fff;
                        border: 1px dashed #e4e6ef;
                        border-radius: 8px;
                        margin-bottom: 10px;
                        overflow: hidden
                    }

                    .p-card-hd {
                        padding: 12px 14px;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        cursor: pointer;
                        -webkit-tap-highlight-color: transparent;
                        user-select: none
                    }

                    .p-card-hd:active {
                        background: #f9fafb
                    }

                    .p-card-av {
                        width: 42px;
                        height: 42px;
                        border-radius: 50%;
                        overflow: hidden;
                        flex-shrink-0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: 700;
                        font-size: .95rem
                    }

                    .p-card-av img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        border-radius: 50%
                    }

                    .p-card-nm {
                        flex: 1;
                        min-width: 0;
                        font-weight: 700;
                        font-size: .88rem;
                        color: #3f4254;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis
                    }

                    .p-card-bg {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 4px;
                        margin-top: 2px
                    }

                    .p-card-bg .badge {
                        font-size: .6rem;
                        padding: 2px 6px;
                        border-radius: 4px
                    }

                    .p-card-arr {
                        flex-shrink: 0;
                        color: #b5b5c3;
                        transition: transform .25s ease;
                        font-size: .7rem
                    }

                    .p-card.open .p-card-arr {
                        transform: rotate(180deg)
                    }

                    .p-card-bd {
                        max-height: 0;
                        overflow: hidden;
                        transition: max-height .3s cubic-bezier(.4, 0, .2, 1)
                    }

                    .p-card.open .p-card-bd {
                        max-height: 500px
                    }

                    .p-card-dt {
                        padding: 0 14px 12px
                    }

                    .p-card-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 7px 0;
                        border-bottom: 1px solid #f3f6f9
                    }

                    .p-card-row:last-child {
                        border-bottom: none
                    }

                    .p-card-lbl {
                        font-size: .72rem;
                        color: #b5b5c3;
                        font-weight: 600
                    }

                    .p-card-val {
                        font-size: .78rem;
                        color: #3f4254;
                        font-weight: 600;
                        text-align: right;
                        max-width: 60%;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap
                    }

                    .p-card-acts {
                        display: flex;
                        gap: 6px;
                        padding: 6px 14px 12px;
                        border-top: 1px dashed #e4e6ef
                    }

                    .p-card-acts .btn {
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

                @forelse ($participants as $participant)
                    <div class="p-card" onclick="this.classList.toggle('open')">
                        <div class="p-card-hd">
                            <div
                                class="p-card-av {{ $participant->type === \App\Enums\ParticipantType::Coach ? 'bg-light-success text-success' : ($participant->type === \App\Enums\ParticipantType::Official ? 'bg-light-info text-info' : 'bg-light-warning text-warning') }}">
                                @if ($participant->photo)
                                    <img src="{{ Storage::url($participant->photo) }}"
                                        alt="{{ $participant->name }}" />
                                @else
                                    {{ strtoupper(substr($participant->name, 0, 1)) }}
                                @endif
                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="p-card-nm">{{ $participant->name }}</div>
                                <div class="p-card-bg">
                                    @if ($participant->type === \App\Enums\ParticipantType::Athlete)
                                        <span class="badge badge-light-primary">Atlet</span>
                                    @elseif ($participant->type === \App\Enums\ParticipantType::Coach)
                                        <span class="badge badge-light-success">Pelatih</span>
                                    @else
                                        <span class="badge badge-light-info">Official</span>
                                    @endif
                                    @if ($participant->is_verified)
                                        <span class="badge badge-light-success"><i
                                                class="bi bi-check-circle-fill"></i></span>
                                    @else
                                        <span class="badge badge-light-warning">Belum</span>
                                    @endif
                                </div>
                            </div>
                            <div class="p-card-arr"><i class="bi bi-chevron-down"></i></div>
                        </div>

                        <div class="p-card-bd" onclick="event.stopPropagation()">
                            <div class="p-card-dt">
                                <div class="p-card-row">
                                    <span class="p-card-lbl">NIK</span>
                                    <span class="p-card-val">{{ $participant->nik ?? '-' }}</span>
                                </div>
                                <div class="p-card-row">
                                    <span class="p-card-lbl">Tgl Lahir</span>
                                    <span
                                        class="p-card-val">{{ $participant->birth_date?->format('d M Y') ?? '-' }}</span>
                                </div>
                                <div class="p-card-row">
                                    <span class="p-card-lbl">Gender</span>
                                    <span class="p-card-val">
                                        {{ $participant->gender === \App\Enums\ParticipantGender::Male ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                </div>
                                <div class="p-card-row">
                                    <span class="p-card-lbl">No</span>
                                    <span class="p-card-val">{{ $participants->firstItem() + $loop->index }}</span>
                                </div>
                            </div>
                            <div class="p-card-acts">
                                <a href="{{ route('participants.show', $participant) }}"
                                    class="btn btn-light-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                @if ($canEdit)
                                    <a href="{{ route('participants.edit', $participant) }}"
                                        class="btn btn-light-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                @endif
                                @if ($canDelete)
                                    <form action="{{ route('participants.destroy', $participant) }}" method="POST"
                                        id="m-delete-{{ $participant->id }}" style="display:none">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button class="btn btn-light-danger"
                                        onclick="event.stopPropagation();confirmDelete(event,{{ $participant->id }})">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-10">
                        <div class="d-flex flex-column align-items-center">
                            <span class="svg-icon svg-icon-4x mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <path opacity="0.3"
                                        d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z"
                                        fill="black" />
                                    <path
                                        d="M21 5H14.8L12 2H3C2.4 2 2 2.4 2 3V18C2 18.6 2.4 19 3 19H21C21.6 19 22 18.6 22 18V6C22 5.4 21.6 5 21 5Z"
                                        fill="black" />
                                </svg>
                            </span>
                            <span class="fw-semibold">Belum ada data peserta</span>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($participants->count() > 0)
                <div class="row">
                    <div
                        class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="dataTables_info">
                            Menampilkan {{ $participants->firstItem() }} sampai {{ $participants->lastItem() }}
                            dari {{ $participants->total() }} data
                        </div>
                    </div>
                    <div
                        class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                        {{ $participants->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterSelect = document.getElementById('kt_filter_type');
                if (filterSelect) {
                    filterSelect.addEventListener('change', function() {
                        const url = new URL(window.location.href);
                        const type = this.value;
                        if (type === 'all') {
                            url.searchParams.delete('type');
                        } else {
                            url.searchParams.set('type', type);
                        }
                        window.location.href = url.toString();
                    });
                }
            });

            function confirmDelete(e, id) {
                e.preventDefault();
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data peserta ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id)?.submit() ||
                            document.getElementById('m-delete-' + id)?.submit();
                    }
                });
            }

            @if (session('success'))
                toastr.success("{{ session('success') }}");
            @endif

            @if (session('error'))
                toastr.error("{{ session('error') }}");
            @endif
        </script>
    @endpush
</x-app-layout>
