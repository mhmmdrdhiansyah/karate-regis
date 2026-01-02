<x-app-layout>
    @section('title', 'Dashboard Utama')

    <div class="row g-5 g-xl-8">
        <div class="col-xl-12">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Selamat Datang!</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Anda login sebagai {{ Auth::user()->name }}</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="alert alert-primary">
                        Template Starter Laravel 11 + Metronic + Spatie Permissions berhasil diinstall!
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
