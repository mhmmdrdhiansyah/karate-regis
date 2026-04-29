<x-app-layout>
    @section('title', 'Tambah Event')

    <form action="{{ route('admin.events.store') }}" method="POST" class="form">
        @csrf
        @include('admin.events._form', ['event' => $event])

        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.events.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
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
