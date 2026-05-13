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
                    @if ($canCreate)
                        <a href="{{ route('participants.create') }}" class="btn btn-primary d-flex align-items-center">
                            <x-icon name="plus" class="svg-icon-2 me-2" />
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
                                            <img src="{{ $participant->photo_url }}"
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
                                        @if ($participant->canBeEditedBy(auth()->user()))
                                            <a href="{{ route('participants.edit', $participant) }}"
                                                class="btn btn-icon btn-light-warning btn-sm me-1" title="Edit">
                                                <x-icon name="edit" class="svg-icon-3" />
                                            </a>
                                        @endif
                                        @if ($participant->canBeDeletedBy(auth()->user()))
                                            <form action="{{ route('participants.destroy', $participant) }}"
                                                method="POST" id="delete-form-{{ $participant->id }}"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a href="#" class="btn btn-icon btn-light-danger btn-sm"
                                                onclick="confirmDelete(event, {{ $participant->id }})" title="Hapus">
                                                <x-icon name="trash" class="svg-icon-3" />
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <x-icon name="folder" class="svg-icon-4x mb-3" />
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
                @include('partials.mobile-card-styles', ['prefix' => 'p'])

                @forelse ($participants as $participant)
                    <div class="p-card" onclick="this.classList.toggle('open')">
                        <div class="p-card-hd">
                            <div
                                class="p-card-av {{ $participant->type === \App\Enums\ParticipantType::Coach ? 'bg-light-success text-success' : ($participant->type === \App\Enums\ParticipantType::Official ? 'bg-light-info text-info' : 'bg-light-warning text-warning') }}">
                                @if ($participant->photo)
                                    <img src="{{ $participant->photo_url }}"
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
                                @if ($participant->canBeEditedBy(auth()->user()))
                                    <a href="{{ route('participants.edit', $participant) }}"
                                        class="btn btn-light-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                @endif
                                @if ($participant->canBeDeletedBy(auth()->user()))
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
                                <x-icon name="folder" class="svg-icon-4x mb-3" />
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
                toastr.success(@js(session('success')));
            @endif

            @if (session('error'))
                toastr.error(@js(session('error')));
            @endif
        </script>
    @endpush
</x-app-layout>
