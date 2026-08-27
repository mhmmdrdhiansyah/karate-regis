<x-app-layout>
    @section('title', 'Daftar Event untuk Input Hasil')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">Daftar Event untuk Input Hasil</h3>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="min-w-70px" style="width: 3ch;">No</th>
                            <th class="min-w-200px">Nama Event</th>
                            <th class="min-w-125px">Tanggal</th>
                            <th class="text-end min-w-150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @forelse ($events as $event)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="text-gray-800 fw-bold">{{ $event->name }}</span>
                                </td>
                                <td>{{ $event->formatted_date }}</td>
                                <td class="text-end">
                                    @can('manage', $event)
                                        <a href="{{ route('admin.events.results.entry', $event) }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-pencil-square"></i> Input Hasil
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-light btn-sm" disabled>
                                            <i class="bi bi-lock"></i> Event Selesai / Bukan Tugas Anda
                                        </button>
                                    @endcan
                                    @can('manage certificate templates')
                                        <a href="{{ route('admin.events.certificate-templates.index', $event) }}" class="btn btn-light-primary btn-sm">
                                            <i class="bi bi-award"></i> Template Sertifikat
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-10">Belum ada event</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
