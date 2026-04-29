<x-app-layout>
    @section('title', 'Edit Kategori - ' . $eventCategory->class_name)

    <form action="{{ route('admin.event-categories.update', $eventCategory) }}" method="POST" class="form">
        @csrf
        @method('PUT')
        <div class="card mb-5">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-dark">Edit Kategori</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">{{ $eventCategory->event->name }}</span>
                </h3>
            </div>
            <div class="card-body py-5">
                <div class="row g-4">
                    <div class="col-md-2">
                        <label class="required form-label">Type</label>
                        <select name="type" class="form-select form-select-solid">
                            <option value="Open" @selected(old('type', $eventCategory->type->value) === 'Open')>Open</option>
                            <option value="Festival" @selected(old('type', $eventCategory->type->value) === 'Festival')>Festival</option>
                        </select>
                        @error('type')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="required form-label">Class Name</label>
                        <input type="text" name="class_name" class="form-control form-control-solid"
                            value="{{ old('class_name', $eventCategory->class_name) }}">
                        @error('class_name')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="required form-label">Min Birth Date</label>
                        <input type="text" name="min_birth_date" class="form-control form-control-solid"
                            id="kt_min_birth_date"
                            value="{{ old('min_birth_date', $eventCategory->min_birth_date?->format('Y-m-d')) }}">
                        @error('min_birth_date')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="required form-label">Max Birth Date</label>
                        <input type="text" name="max_birth_date" class="form-control form-control-solid"
                            id="kt_max_birth_date"
                            value="{{ old('max_birth_date', $eventCategory->max_birth_date?->format('Y-m-d')) }}">
                        @error('max_birth_date')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-3 mt-6">
                    <a href="{{ route('admin.events.show', $eventCategory->event) }}"
                        class="btn btn-light btn-active-light-primary w-100 w-sm-auto">Batal</a>
                    <button type="submit" class="btn btn-primary w-100 w-sm-auto">Simpan</button>
                </div>
            </div>
        </div>
    </form>

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
