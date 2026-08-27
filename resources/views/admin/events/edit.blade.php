<x-app-layout>
    @section('title', 'Edit Event - ' . $event->name)

    @if ($event->isLocked())
        <div
            class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex align-items-center p-5 mb-5">
            <div class="d-flex flex-column">
                <h5 class="mb-1 text-warning">Event terkunci</h5>
                <span class="text-gray-600">Tanggal event, fee event, dan fee coach tidak dapat diubah karena event sudah
                    ongoing
                    atau completed.</span>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body py-5">
            <form action="{{ route('admin.events.update', $event) }}" method="POST" class="form"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.events._form', ['event' => $event])

                <div class="d-flex justify-content-end mt-8">
                    <a href="{{ route('admin.events.show', $event) }}"
                        class="btn btn-light btn-active-light-primary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>

            @can('assign event panitia')
                <div class="separator separator-dashed my-8" id="panitia"></div>

                <form action="{{ route('admin.events.panitia.assign', $event) }}" method="POST"
                    class="form">
                    @csrf
                    @method('PUT')
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold">Panitia Event</label>
                        <div class="col-lg-9">
                            <p class="text-muted fs-7 mb-3">Panitia yang ditugaskan bisa mengelola event ini
                                (verifikasi pembayaran &amp; berkas, input hasil). Panitia lain hanya bisa melihat.</p>

                            @if ($event->panitia->isNotEmpty())
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                                    <span class="text-muted fs-7">Sudah ditugaskan ({{ $event->panitia->count() }}):</span>
                                    @foreach ($event->panitia as $p)
                                        <span class="badge badge-light-primary fs-7">
                                            <i class="bi bi-person-check fs-8 me-1"></i>{{ $p->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted fs-7 mb-4">
                                    <i class="bi bi-info-circle me-1"></i>Belum ada panitia yang ditugaskan.
                                </div>
                            @endif

                            <div class="border rounded p-4" style="max-height: 260px; overflow-y: auto">
                                @foreach ($panitiaUsers as $id => $name)
                                    <label class="d-flex align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }} cursor-pointer">
                                        <input type="checkbox" name="panitia_ids[]" value="{{ $id }}"
                                            class="form-check-input me-3"
                                            {{ $event->panitia->contains('id', $id) ? 'checked' : '' }}>
                                        <span class="fs-6">{{ $name }}</span>
                                        @if ($event->panitia->contains('id', $id))
                                            <span class="badge badge-light-success ms-2">Aktif</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                            <div class="form-text fs-8 mt-2">Centang = ditugaskan. Simpan untuk menerapkan perubahan.</div>

                            <div class="mt-6">
                                <button type="submit" class="btn btn-light-primary">
                                    <i class="bi bi-people"></i> Simpan Penugasan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const posterInput = document.getElementById('kt_event_poster');
                const posterPreview = document.getElementById('kt_event_poster_preview');
                const eventDateInput = document.getElementById('kt_event_date');
                const deadlineInput = document.getElementById('kt_deadline_date');

                if (posterInput && posterPreview) {
                    posterInput.addEventListener('change', function() {
                        const file = this.files && this.files[0];

                        if (!file) return;

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            posterPreview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    });
                }

                const eventDatePicker = flatpickr(eventDateInput, {
                    dateFormat: 'Y-m-d',
                    minDate: 'today',
                    onChange(selectedDates) {
                        if (!deadlinePicker) return;

                        if (!selectedDates.length) {
                            deadlinePicker.set('maxDate', null);
                            return;
                        }

                        const maxDeadline = new Date(selectedDates[0]);
                        maxDeadline.setDate(maxDeadline.getDate() - 1);
                        deadlinePicker.set('maxDate', maxDeadline);
                    }
                });

                const deadlinePicker = flatpickr(deadlineInput, {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    onChange(selectedDates) {
                        if (!eventDatePicker) return;

                        if (!selectedDates.length) {
                            eventDatePicker.set('minDate', 'today');
                            return;
                        }

                        const minEventDate = new Date(selectedDates[0]);
                        minEventDate.setDate(minEventDate.getDate() + 1);
                        eventDatePicker.set('minDate', minEventDate);
                    }
                });

                if (eventDateInput.value) {
                    eventDatePicker.setDate(eventDateInput.value, false, 'Y-m-d');
                }

                if (deadlineInput.value) {
                    deadlinePicker.setDate(deadlineInput.value, false, 'Y-m-d H:i');
                }

                if (eventDateInput.value) {
                    const selectedEventDate = eventDatePicker.selectedDates[0];
                    if (selectedEventDate) {
                        const maxDeadline = new Date(selectedEventDate);
                        maxDeadline.setDate(maxDeadline.getDate() - 1);
                        deadlinePicker.set('maxDate', maxDeadline);
                    }
                }

                if (deadlinePicker.selectedDates[0]) {
                    const selectedDeadline = deadlinePicker.selectedDates[0];
                    const minEventDate = new Date(selectedDeadline);
                    minEventDate.setDate(minEventDate.getDate() + 1);
                    eventDatePicker.set('minDate', minEventDate);
                }
            });
        </script>
    @endpush
</x-app-layout>
