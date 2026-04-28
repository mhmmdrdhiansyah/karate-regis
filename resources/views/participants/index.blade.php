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
                    <a href="{{ route('participants.create') }}" class="btn btn-primary d-flex align-items-center">
                        <span class="svg-icon svg-icon-2 me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                    transform="rotate(-90 11.364 20.364)" fill="currentColor" />
                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                    fill="currentColor" />
                            </svg>
                        </span>
                        Tambah Peserta
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
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
                            <td>
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    @if ($participant->photo)
                                        <img src="{{ Storage::url($participant->photo) }}"
                                            alt="{{ $participant->name }}" class="w-100 h-100 object-fit-cover" />
                                    @else
                                        <div class="symbol-label fs-3 {{ $participant->type === \App\Enums\ParticipantType::Coach ? 'bg-light-success text-success' : ($participant->type === \App\Enums\ParticipantType::Official ? 'bg-light-info text-info' : 'bg-light-warning text-warning') }}">
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
                                    {{ $participant->gender === \App\Enums\ParticipantGender::M ? 'Laki-laki' : 'Perempuan' }}
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
                                    <a href="{{ route('participants.edit', $participant) }}"
                                        class="btn btn-icon btn-light-warning btn-sm me-1" title="Edit">
                                        <span class="svg-icon svg-icon-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path opacity="0.3"
                                                    d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.37449 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                                    fill="currentColor" />
                                            </svg>
                                        </span>
                                    </a>
                                    <form action="{{ route('participants.destroy', $participant) }}" method="POST"
                                        id="delete-form-{{ $participant->id }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <a href="#" class="btn btn-icon btn-light-danger btn-sm"
                                        onclick="confirmDelete(event, {{ $participant->id }})" title="Hapus">
                                        <span class="svg-icon svg-icon-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-10">
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
            document.addEventListener('DOMContentLoaded', function () {
                const filterSelect = document.getElementById('kt_filter_type');
                if (filterSelect) {
                    filterSelect.addEventListener('change', function () {
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
                        document.getElementById('delete-form-' + id).submit();
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
