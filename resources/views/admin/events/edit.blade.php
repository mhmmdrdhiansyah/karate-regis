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
