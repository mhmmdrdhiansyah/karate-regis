{{-- Welcome --}}
<div class="row g-5 g-xl-8">
    <div class="col-xl-12">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Selamat Datang, Panitia!</span>
                    <span class="text-muted mt-1 fw-bold fs-7">{{ Auth::user()->name }} &mdash; Panel Panitia</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="alert alert-warning d-flex align-items-center p-5 mb-0">
                    <i class="bi bi-info-circle-fill text-warning fs-3 me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold">Fitur dashboard panitia akan segera tersedia.</span>
                        <span class="text-muted fs-7">Data event dan peserta akan ditampilkan di sini.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Stat --}}
<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-building text-warning"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Total Kontingen</span>
                        <span class="text-dark fw-bolder fs-2x">{{ number_format($totalKontingen) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
