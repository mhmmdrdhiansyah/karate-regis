@php
    $isLocked = $event?->exists && $event->isLocked();
@endphp

<div class="row mb-7">
    <div class="col-md-4 fv-row">
        <label class="required form-label">Tanggal Event</label>
        <input type="text" name="event_date" class="form-control form-control-solid"
            value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" id="kt_event_date"
            {{ $isLocked ? 'disabled' : '' }}>
        @if ($isLocked)
            <input type="hidden" name="event_date" value="{{ $event->event_date?->format('Y-m-d') }}">
        @endif
        @error('event_date')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 fv-row">
        <label class="form-label">Registration Deadline</label>
        <input type="text" name="registration_deadline" class="form-control form-control-solid"
            value="{{ old('registration_deadline', $event->registration_deadline?->format('Y-m-d H:i')) }}"
            id="kt_deadline_date" placeholder="Opsional">
        @error('registration_deadline')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 fv-row">
        <label class="required form-label">Coach Fee</label>
        <input type="number" name="coach_fee" class="form-control form-control-solid"
            value="{{ old('coach_fee', $event->coach_fee) }}" min="0" step="0.01"
            {{ $isLocked ? 'disabled' : '' }}>
        @if ($isLocked)
            <input type="hidden" name="coach_fee" value="{{ $event->coach_fee }}">
        @endif
        @error('coach_fee')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="row mb-7">
    <div class="col-md-6 fv-row">
        <label class="required form-label">Nama Event</label>
        <input type="text" name="name" class="form-control form-control-solid"
            value="{{ old('name', $event->name) }}">
        @error('name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 fv-row">
        <label class="required form-label">Status</label>
        @if ($event->exists)
            <div class="form-control form-control-solid bg-light d-flex align-items-center justify-content-between">
                <span>{{ $event->statusLabel() }}</span>
                <span class="badge {{ $event->statusBadgeClass() }}">{{ $event->status->value }}</span>
            </div>
            <input type="hidden" name="status" value="{{ $event->status->value }}">
        @else
            <select name="status" class="form-select form-select-solid">
                @foreach (\App\Enums\EventStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $event->status?->value ?? 'draft') === $status->value)>
                        {{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                @endforeach
            </select>
        @endif
        @error('status')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
</div>
