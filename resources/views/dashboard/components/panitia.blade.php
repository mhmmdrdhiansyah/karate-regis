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
                <div class="alert alert-light d-flex align-items-center p-5 mb-0">
                    <i class="bi bi-info-circle-fill text-primary fs-3 me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold">Ringkasan Data Event</span>
                        <span class="text-muted fs-7">Berikut adalah statistik terkini peserta dan kontingen.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-5 g-xl-8">
    <div class="col-xl">
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

    <div class="col-xl">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-person-fill text-primary"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Total Atlet</span>
                        <span class="text-dark fw-bolder fs-2x">{{ number_format($totalAthletes) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-whistle text-warning"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Total Pelatih</span>
                        <span class="text-dark fw-bolder fs-2x">{{ number_format($totalCoaches) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-person-badge text-info"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Total Official</span>
                        <span class="text-dark fw-bolder fs-2x">{{ number_format($totalOfficials) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-shield-check text-success"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Terverifikasi</span>
                        <span class="text-dark fw-bolder fs-2x">{{ number_format($totalVerified) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-body d-flex flex-column p-6">
                <div class="d-flex align-items-center mb-5">
                    <span class="svg-icon svg-icon-3x me-4">
                        <i class="bi bi-clock-history text-danger"></i>
                    </span>
                    <div class="d-flex flex-column">
                        <span class="text-gray-400 fw-bold fs-7">Pending Verifikasi</span>
                        <span class="text-dark fw-bolder fs-2x">{{ number_format($totalPending) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top 10 Kontingen --}}
<div class="row g-5 g-xl-8">
    <div class="col-xl-12">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Top 10 Kontingen</span>
                    <span class="text-muted mt-1 fw-bold fs-7">Berdasarkan jumlah peserta</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div id="kt_top_kontingen_chart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .event-header:hover { background-color: rgba(0,0,0,.02); }
    .event-header .chevron-icon { transition: transform .3s cubic-bezier(.4,0,.2,1); }
    .event-header .chevron-icon.rotated { transform: rotate(180deg); }
    #event_collapse_0, #event_collapse_1, #event_collapse_2,
    #event_collapse_3, #event_collapse_4, #event_collapse_5,
    #event_collapse_6, #event_collapse_7, #event_collapse_8,
    #event_collapse_9 {
        transition: height .35s cubic-bezier(.4,0,.2,1), opacity .3s ease;
    }
</style>
@endpush

{{-- Pendaftaran per Event --}}
@foreach ($eventCharts as $eventIndex => $event)
<div class="row g-5 g-xl-8 mb-4">
    <div class="col-xl-12">
        <div class="card card-xl-stretch mb-xl-8">
            <div class="card-header border-0 pt-5 cursor-pointer event-header" data-bs-toggle="collapse" data-bs-target="#event_collapse_{{ $eventIndex }}">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="card-title align-items-start flex-column mb-0">
                        <span class="card-label fw-bolder fs-4 mb-1">
                            <i class="bi bi-trophy text-warning me-2"></i>{{ $event->name }}
                        </span>
                    </h3>
                    <i class="bi bi-chevron-down text-gray-400 fs-4 chevron-icon rotated" id="event_icon_{{ $eventIndex }}"></i>
                </div>
            </div>
            <div class="collapse show" id="event_collapse_{{ $eventIndex }}">
                <div class="card-body py-3">
                    <div class="row g-5 g-xl-8">
                        @foreach ($event->categories as $catIndex => $cat)
                        <div class="col-xl-6">
                            <div class="card card-flush mb-0">
                                <div class="card-header border-0 pt-4">
                                    <h4 class="card-title fw-bolder fs-6 mb-0">{{ $cat->name }}</h4>
                                </div>
                                <div class="card-body py-2">
                                    <div id="kt_event_chart_{{ $eventIndex }}_{{ $catIndex }}" style="min-height: auto;"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var options = {
        series: [{
            name: 'Peserta',
            data: @json($topKontingen->pluck('participants_count'))
        }],
        chart: {
            type: 'bar',
            height: 350,
            fontFamily: 'inherit',
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
                barHeight: '65%',
                distributed: true
            }
        },
        colors: [
            '#3699FF', '#FBC02D', '#1BC5BD', '#8950FC',
            '#EA5455', '#28C76F', '#FF9F43', '#00CFE8',
            '#6C5CE7', '#B8C2CC'
        ],
        dataLabels: {
            enabled: true,
            style: { fontSize: '12px', colors: ['#333'] }
        },
        xaxis: {
            categories: @json($topKontingen->pluck('name')),
            labels: { style: { fontSize: '12px' } }
        },
        yaxis: {
            labels: { style: { fontSize: '12px' } }
        },
        legend: { show: false },
        grid: {
            borderColor: '#E5E5E5',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } }
        },
        tooltip: {
            style: { fontSize: '12px' },
            y: { formatter: function(val) { return val + ' peserta'; } }
        }
    };

    new ApexCharts(document.querySelector('#kt_top_kontingen_chart'), options).render();

    // Event Registration Charts
    var eventCharts = @json($eventCharts);
    var barColors = ['#3699FF','#FBC02D','#1BC5BD','#8950FC','#EA5455','#28C76F','#FF9F43','#00CFE8','#6C5CE7','#B8C2CC'];
    var chartInstances = {};

    function renderEventCharts(ei) {
        eventCharts[ei].categories.forEach(function(cat, ci) {
            var id = 'kt_event_chart_' + ei + '_' + ci;
            if (chartInstances[id]) {
                chartInstances[id].destroy();
            }
            var chart = new ApexCharts(document.querySelector('#' + id), {
                series: [{ name: 'Terdaftar', data: cat.series }],
                chart: { type: 'bar', height: Math.max(cat.labels.length * 32, 120), fontFamily: 'inherit', toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '70%', distributed: true } },
                colors: barColors,
                dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#333'] } },
                xaxis: { categories: cat.labels, labels: { style: { fontSize: '11px' } } },
                yaxis: { labels: { style: { fontSize: '11px' } } },
                legend: { show: false },
                grid: { borderColor: '#E5E5E5', strokeDashArray: 4 },
                tooltip: { y: { formatter: function(val) { return val + ' peserta (pembayaran terkonfirmasi)'; } } }
            });
            chart.render();
            chartInstances[id] = chart;
        });
    }

    // Initial render
    eventCharts.forEach(function(event, ei) {
        renderEventCharts(ei);
    });

    // Toggle icon on collapse
    document.querySelectorAll('.event-header').forEach(function(el) {
        el.addEventListener('click', function() {
            var target = this.getAttribute('data-bs-target');
            var icon = document.querySelector(target.replace('collapse', 'icon'));
            var collapseEl = document.querySelector(target);
            collapseEl.addEventListener('shown.bs.collapse', function() { icon.classList.add('rotated'); renderEventCharts(target.split('_').pop()); }, { once: true });
            collapseEl.addEventListener('hidden.bs.collapse', function() { icon.classList.remove('rotated'); }, { once: true });
        });
    });
});
</script>
@endpush
