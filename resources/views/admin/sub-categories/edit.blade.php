<x-app-layout>
    @section('title', 'Edit Sub-Kategori - ' . $subCategory->name)

    <form action="{{ route('admin.sub-categories.update', $subCategory) }}" method="POST" class="form">
        @csrf
        @method('PUT')
        <div class="card mb-5">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-dark">Edit Sub-Kategori</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">{{ $subCategory->eventCategory->class_name }}</span>
                </h3>
            </div>
            <div class="card-body py-5">
                <div class="row g-4">
                    <div class="col-md-3">
                        <label class="required form-label">Name</label>
                        <input type="text" name="name" class="form-control form-control-solid"
                            value="{{ old('name', $subCategory->name) }}">
                        @error('name')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="required form-label">Type</label>
                        <select name="category_type" class="form-select form-select-solid">
                            <option value="individu" @selected(old('category_type', $subCategory->category_type) === 'individu')>Individu</option>
                            <option value="beregu" @selected(old('category_type', $subCategory->category_type) === 'beregu')>Beregu</option>
                        </select>
                        @error('category_type')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-1">
                        <label class="required form-label">Gender</label>
                        <select name="gender" class="form-select form-select-solid">
                            <option value="M" @selected(old('gender', $subCategory->gender->value) === 'M')>M</option>
                            <option value="F" @selected(old('gender', $subCategory->gender->value) === 'F')>F</option>
                            <option value="Mixed" @selected(old('gender', $subCategory->gender->value) === 'Mixed')>Mixed</option>
                        </select>
                        @error('gender')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="required form-label">Price</label>
                        <input type="number" name="price" class="form-control form-control-solid"
                            value="{{ old('price', $subCategory->price) }}" min="0" step="0.01"
                            {{ $subCategory->canEditPrice() ? '' : 'disabled' }}>
                        @if (!$subCategory->canEditPrice())
                            <input type="hidden" name="price" value="{{ $subCategory->price }}">
                        @endif
                        @error('price')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-1">
                        <label class="required form-label">Min</label>
                        <input type="number" name="min_participants" class="form-control form-control-solid"
                            value="{{ old('min_participants', $subCategory->min_participants) }}" min="1">
                        @error('min_participants')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-1">
                        <label class="required form-label">Max</label>
                        <input type="number" name="max_participants" class="form-control form-control-solid"
                            value="{{ old('max_participants', $subCategory->max_participants) }}" min="1">
                        @error('max_participants')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-1">
                        <label class="required form-label">Max Tim</label>
                        <input type="number" name="max_teams" class="form-control form-control-solid"
                            value="{{ old('max_teams', $subCategory->max_teams) }}" min="1">
                        @error('max_teams')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <div>
                <a href="{{ route('admin.events.show', $subCategory->eventCategory->event) }}"
                    class="btn btn-light btn-sm me-2">← Kembali ke Event</a>
            </div>
            <div>
                <a href="{{ route('admin.event-categories.show', $subCategory->eventCategory) }}"
                    class="btn btn-light btn-active-light-primary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</x-app-layout>
