<x-app-layout>
    @section('title', 'Edit Event - ' . $event->name)

    @if ($event->isLocked())
        <div
            class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex align-items-center p-5 mb-5">
            <div class="d-flex flex-column">
                <h5 class="mb-1 text-warning">Event terkunci</h5>
                <span class="text-gray-600">Tanggal event dan coach fee tidak dapat diubah karena event sudah ongoing
                    atau completed.</span>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.events.update', $event) }}" method="POST" class="form">
        @csrf
        @method('PUT')
        @include('admin.events._form', ['event' => $event])

        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.events.show', $event) }}"
                class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>

    @push('scripts')
        <script>
            flatpickr('#kt_event_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today'
            });
            flatpickr('#kt_deadline_date', {
                enableTime: true,
                dateFormat: 'Y-m-d H:i'
            });
        </script>
    @endpush
</x-app-layout>
