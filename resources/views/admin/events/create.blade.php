<x-app-layout>
    @section('title', 'Tambah Event')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">Buat Event Baru</h3>
            </div>
        </div>

        <div class="card-body py-5">
            <div
                class="alert alert-light-primary border border-primary border-dashed d-flex align-items-center p-5 mb-6">
                <div class="d-flex flex-column">
                    <h5 class="mb-1 text-primary">Isi informasi inti event terlebih dahulu</h5>
                    <span class="text-gray-600">Isi <strong>fee event</strong> dan <strong>fee coach</strong> sebagai dua
                        biaya terpisah, lalu ubah status lewat tombol transition di detail event.</span>
                </div>
            </div>

            <form action="{{ route('admin.events.store') }}" method="POST" class="form" enctype="multipart/form-data">
                @csrf
                @include('admin.events._form', ['event' => $event])

                <div class="d-flex justify-content-end mt-8">
                    <a href="{{ route('admin.events.index') }}"
                        class="btn btn-light btn-active-light-primary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
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
