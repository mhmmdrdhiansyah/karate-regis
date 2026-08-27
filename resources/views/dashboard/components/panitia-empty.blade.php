<div class="card">
    <div class="card-body p-0">
        <div class="d-flex flex-column flex-center text-center py-20">
            <img src="{{ asset('assets/media/logos/logo3.png') }}" alt="" class="mw-100px mb-8" style="filter: grayscale(1); opacity: .5">
            <h3 class="fs-2 fw-bold text-gray-800 mb-2">Belum Ada Event yang Ditugaskan</h3>
            <p class="text-muted fs-6 mb-8">
                Anda belum ditugaskan sebagai panitia pada event apa pun.<br>
                Hubungi <strong>super-admin</strong> untuk mendapatkan penugasan event, atau buat event baru
                — event yang Anda buat otomatis menjadi tanggungan Anda.
            </p>
            @can('create', \App\Models\Event::class)
                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Buat Event Baru
                </a>
            @endcan
        </div>
    </div>
</div>
