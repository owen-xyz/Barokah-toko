/**
 * POS System — Halaman Laporan
 * Menampilkan summary cards dan grafik Chart.js
 */

'use strict';

// Referensi chart agar bisa di-destroy saat refresh
let barChartInstance      = null;
let doughnutChartInstance = null;

/** Format angka ke Rupiah singkat */
function formatRpShort(value) {
    if (value >= 1_000_000_000) return 'Rp ' + (value / 1_000_000_000).toFixed(1) + ' M';
    if (value >= 1_000_000)     return 'Rp ' + (value / 1_000_000).toFixed(1) + ' Jt';
    if (value >= 1_000)         return 'Rp ' + (value / 1_000).toFixed(0) + ' Rb';
    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

function formatRp(value) {
    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

/** Ambil data laporan dari server */
async function loadReportData() {
    const btn = document.getElementById('refreshBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...';
    btn.disabled  = true;

    try {
        const res  = await fetch('/reports/data');
        const json = await res.json();
        

        if (!json.success) throw new Error('Gagal memuat data');

        const { summary, daily_sales, doughnut_chart } = json.data;

        updateSummaryCards(summary);
        renderBarChart(daily_sales);
        renderDoughnutChart(doughnut_chart);

    } catch (err) {
        console.error(err);
        Swal.fire({
            icon:  'error',
            title: 'Gagal Memuat Data',
            text:  'Tidak dapat mengambil data laporan dari server.',
            confirmButtonColor: '#2563EB',
        });
    } finally {
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Refresh';
        btn.disabled  = false;
    }
}

/** Update 4 summary cards */
function updateSummaryCards(summary) {
    document.getElementById('cardPenjualan').textContent  = formatRpShort(summary.total_penjualan);
    document.getElementById('cardModal').textContent      = formatRpShort(summary.total_modal);
    document.getElementById('cardKeuntungan').textContent = formatRpShort(summary.total_keuntungan);
    document.getElementById('cardTransaksi').textContent  = summary.jumlah_transaksi.toLocaleString('id-ID') + ' Transaksi';
}

/** Render Bar Chart — produk terjual per hari */
function renderBarChart(dailySales) {
    const ctx = document.getElementById('barChart').getContext('2d');

    if (barChartInstance) barChartInstance.destroy();

    const labels = dailySales.map(d => d.label);
    const values = dailySales.map(d => d.qty);

    barChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label:           'Produk Terjual (pcs)',
                data:            values,
                backgroundColor: 'rgba(37, 99, 235, 0.15)',
                borderColor:     '#2563EB',
                borderWidth:     2,
                borderRadius:    6,
                borderSkipped:   false,
                hoverBackgroundColor: 'rgba(37, 99, 235, 0.3)',
            }],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} pcs`,
                    },
                },
            },
            scales: {
                x: {
                    grid:      { display: false },
                    ticks:     { font: { size: 11 }, color: '#6B7280' },
                    border:    { display: false },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font:      { size: 11 },
                        color:     '#6B7280',
                        stepSize:  1,
                        callback:  v => v + ' pcs',
                    },
                    grid:   { color: '#F3F4F6' },
                    border: { display: false },
                },
            },
        },
    });
}

/** Render Doughnut Chart — komposisi keuangan */
function renderDoughnutChart(data) {
    const ctx = document.getElementById('doughnutChart').getContext('2d');

    if (doughnutChartInstance) doughnutChartInstance.destroy();

    const labels = ['Modal', 'Penjualan', 'Keuntungan'];
    const values = [data.modal, data.penjualan, data.keuntungan];
    const colors = ['#EA580C', '#2563EB', '#16A34A'];
    const total  = values.reduce((a, b) => a + b, 0);

    doughnutChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors,
                borderWidth:     3,
                borderColor:     '#FFFFFF',
                hoverOffset:     6,
            }],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            cutout:              '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const val  = ctx.parsed;
                            const pct  = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                            return ` ${ctx.label}: ${formatRp(val)} (${pct}%)`;
                        },
                    },
                },
            },
        },
    });

    // Custom legend
    renderDonutLegend(labels, values, colors, total);
}

function renderDonutLegend(labels, values, colors, total) {
    const el = document.getElementById('doughnutLegend');

    el.innerHTML = labels.map((label, i) => {
        const pct = total > 0 ? ((values[i] / total) * 100).toFixed(1) : '0.0';
        return `
        <div class="donut-legend-item">
            <span class="donut-legend-label">
                <span class="donut-legend-dot" style="background:${colors[i]}"></span>
                ${label} (${pct}%)
            </span>
            <span class="donut-legend-value">${formatRpShort(values[i])}</span>
        </div>`;
    }).join('');
}

// ─── Init ───────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadReportData();

    document.getElementById('refreshBtn').addEventListener('click', loadReportData);
});
