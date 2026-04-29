<x-app-layout>
    @section('title', 'Laporan - Ringkasan')

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-label fw-bold text-dark">Laporan Transaksi</h3>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <button id="btn-export-pdf" class="btn btn-light-primary btn-sm">Export PDF</button>
                    <button id="btn-export-excel" class="btn btn-light-success btn-sm">Export Excel</button>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive d-none d-lg-block">
                <table id="report-table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="min-w-50px">No</th>
                            <th class="min-w-125px sortable" data-type="date">Tanggal Order <span
                                    class="sort-indicator"></span></th>
                            <th class="min-w-200px sortable" data-type="string">Kontingen <span
                                    class="sort-indicator"></span></th>
                            <th class="min-w-200px sortable" data-type="string">Event <span
                                    class="sort-indicator"></span></th>
                            <th class="min-w-150px text-end sortable" data-type="number">Total Tagihan <span
                                    class="sort-indicator"></span></th>
                            <th class="min-w-125px">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @foreach ($payments as $idx => $payment)
                            <tr>
                                <td class="text-gray-500 text-center">{{ $payments->firstItem() + $idx }}</td>
                                <td>{{ $payment->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td>{{ $payment->contingent?->name ?? '-' }}</td>
                                <td>{{ $payment->event?->name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($payment->total_amount, 2, ',', '.') }}</td>
                                <td>{{ $payment->status?->value ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: reuse participant card style for responsiveness --}}
            <div class="d-block d-lg-none">
                @foreach ($payments as $idx => $payment)
                    <div class="p-card"
                        style="background:#fff;border:1px dashed #e4e6ef;border-radius:8px;margin-bottom:10px;overflow:hidden">
                        <div class="p-card-hd" style="padding:12px 14px;display:flex;align-items:center;gap:10px;">
                            <div style="flex:1;min-width:0;font-weight:700;font-size:.88rem;color:#3f4254">
                                {{ $payment->contingent?->name ?? '-' }}</div>
                            <div style="text-align:right;font-weight:600">
                                {{ number_format($payment->total_amount, 2, ',', '.') }}</div>
                        </div>
                        <div class="p-card-bd" style="padding:0 14px 12px">
                            <div style="padding:7px 0;border-bottom:1px solid #f3f6f9">
                                <span class="p-card-lbl"
                                    style="font-size:.72rem;color:#b5b5c3;font-weight:600">Tanggal</span>
                                <div class="p-card-val"
                                    style="font-size:.78rem;color:#3f4254;font-weight:600;text-align:right">
                                    {{ $payment->created_at?->format('d M Y H:i') ?? '-' }}</div>
                            </div>
                            <div style="padding:7px 0;border-bottom:1px solid #f3f6f9">
                                <span class="p-card-lbl"
                                    style="font-size:.72rem;color:#b5b5c3;font-weight:600">Event</span>
                                <div class="p-card-val"
                                    style="font-size:.78rem;color:#3f4254;font-weight:600;text-align:right">
                                    {{ $payment->event?->name ?? '-' }}</div>
                            </div>
                            <div style="padding:7px 0;">
                                <span class="p-card-lbl"
                                    style="font-size:.72rem;color:#b5b5c3;font-weight:600">Status</span>
                                <div class="p-card-val"
                                    style="font-size:.78rem;color:#3f4254;font-weight:600;text-align:right">
                                    {{ $payment->status?->value ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($payments->count() > 0)
                <div class="row">
                    <div
                        class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="dataTables_info">
                            Menampilkan {{ $payments->firstItem() }} sampai {{ $payments->lastItem() }} dari
                            {{ $payments->total() }} data
                        </div>
                    </div>
                    <div
                        class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                        {{ $payments->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Sorting state: none / asc / desc
                const headers = document.querySelectorAll('#report-table thead th.sortable');
                headers.forEach((th, idx) => {
                    th.dataset.sort = 'none';
                    th.style.cursor = 'pointer';
                    th.addEventListener('click', function() {
                        // cycle state
                        const states = ['none', 'asc', 'desc'];
                        const cur = this.dataset.sort;
                        const next = states[(states.indexOf(cur) + 1) % states.length];
                        // reset all
                        headers.forEach(h => {
                            h.dataset.sort = 'none';
                            h.querySelector('.sort-indicator').innerText = ''
                        });
                        this.dataset.sort = next;
                        this.querySelector('.sort-indicator').innerText = next === 'asc' ? '↑' : (
                            next === 'desc' ? '↓' : '');
                        if (next === 'none') {
                            // restore original order (by reloading page to server-side order)
                            location.reload();
                            return;
                        }
                        sortTableByColumn(this, idx, this.dataset.type, next);
                    });
                });

                function sortTableByColumn(th, headerIndex, type, dir) {
                    const table = document.getElementById('report-table');
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    const colIndex = Array.from(th.parentElement.children).indexOf(th);

                    rows.sort((a, b) => {
                        const aText = a.children[colIndex]?.innerText.trim() ?? '';
                        const bText = b.children[colIndex]?.innerText.trim() ?? '';
                        let av = aText,
                            bv = bText;
                        if (type === 'number') {
                            av = parseFloat(aText.replace(/[^0-9,.-]/g, '').replace(',', '.')) || 0;
                            bv = parseFloat(bText.replace(/[^0-9,.-]/g, '').replace(',', '.')) || 0;
                        } else if (type === 'date') {
                            av = Date.parse(aText) || 0;
                            bv = Date.parse(bText) || 0;
                        } else {
                            av = aText.toLowerCase();
                            bv = bText.toLowerCase();
                        }
                        if (av < bv) return dir === 'asc' ? -1 : 1;
                        if (av > bv) return dir === 'asc' ? 1 : -1;
                        return 0;
                    });

                    // re-append rows
                    rows.forEach(r => tbody.appendChild(r));
                }

                // Export CSV (Excel)
                document.getElementById('btn-export-excel').addEventListener('click', function() {
                    const table = document.getElementById('report-table');
                    const rows = Array.from(table.querySelectorAll('thead tr, tbody tr'));
                    const csv = [];
                    rows.forEach((row, idx) => {
                        const cols = Array.from(row.querySelectorAll('th, td'));
                        const rowData = cols.map(col => '"' + col.innerText.replace(/"/g, '""') + '"');
                        csv.push(rowData.join(','));
                    });
                    const blob = new Blob([csv.join('\n')], {
                        type: 'text/csv;charset=utf-8;'
                    });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'laporan.csv';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(url);
                });

                // Export PDF using jsPDF + autoTable
                document.getElementById('btn-export-pdf').addEventListener('click', function() {
                    const {
                        jsPDF
                    } = window.jspdf || {};
                    if (!jsPDF) {
                        alert('Library jsPDF belum tersedia.');
                        return;
                    }
                    const doc = new jsPDF('landscape');
                    const table = document.getElementById('report-table');
                    const headers = Array.from(table.querySelectorAll('thead th')).map(h => h.innerText.trim());
                    const body = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
                        return Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim());
                    });

                    doc.autoTable({
                        head: [headers],
                        body: body,
                        styles: {
                            fontSize: 8
                        }
                    });
                    doc.save('laporan.pdf');
                });
            });
        </script>
    @endpush
</x-app-layout>
