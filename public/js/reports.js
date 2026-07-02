/**
 * Reports — Dashboard + Riwayat Transaksi
 * Fix doughnut chart, tambah history table, detail modal,
 * export: cetak struk, WhatsApp, Excel, PDF
 */
"use strict";

let barChartInst = null;
let doughnutChartInst = null;
let historyPage = 1;
let filterStart = "";
let filterEnd = "";
let activeTransaction = null; // data detail transaksi yang sedang dibuka

// ─── Format helpers ────────────────────────────────────────────────
const fmtRp = (v) => "Rp " + Math.round(v || 0).toLocaleString("id-ID");
const fmtRpShort = (v) => {
    if (v >= 1e9) return "Rp " + (v / 1e9).toFixed(1) + " M";
    if (v >= 1e6) return "Rp " + (v / 1e6).toFixed(1) + " Jt";
    if (v >= 1e3) return "Rp " + (v / 1e3).toFixed(0) + " Rb";
    return "Rp " + Math.round(v).toLocaleString("id-ID");
};

// ─── Load semua data dashboard ──────────────────────────────────────
async function loadDashboard() {
    const btn = document.getElementById("refreshBtn");
    btn.disabled = true;
    btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span>Memuat...';

    try {
        const res = await fetch("/reports/data");
        const json = await res.json();
        if (!json.success) throw new Error("Gagal memuat data");

        updateCards(json.data.summary);
        renderBarChart(json.data.daily_sales);
        renderDoughnutChart(json.data.doughnut);
    } catch (e) {
        console.error(e);
        Swal.fire({
            icon: "error",
            title: "Gagal",
            text: e.message,
            confirmButtonColor: "#2563EB",
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Refresh';
    }
}

function updateCards(s) {
    document.getElementById("cardPenjualan").textContent = fmtRpShort(
        s.total_penjualan,
    );
    document.getElementById("cardModal").textContent = fmtRpShort(
        s.total_modal,
    );
    document.getElementById("cardKeuntungan").textContent = fmtRpShort(
        s.total_keuntungan,
    );
    document.getElementById("cardTransaksi").textContent =
        s.jumlah_transaksi.toLocaleString("id-ID") + " Transaksi";
}

// ─── Bar Chart ──────────────────────────────────────────────────────
function renderBarChart(data) {
    const ctx = document.getElementById("barChart").getContext("2d");
    if (barChartInst) barChartInst.destroy();

    barChartInst = new Chart(ctx, {
        type: "bar",
        data: {
            labels: data.map((d) => d.label),
            datasets: [
                {
                    label: "Penjualan",
                    data: data.map((d) => d.total),
                    backgroundColor: "rgba(37,99,235,0.12)",
                    borderColor: "#2563EB",
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                    hoverBackgroundColor: "rgba(37,99,235,0.28)",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: (c) => " " + fmtRp(c.parsed.y) },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: "#6B7280" },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 11 },
                        color: "#6B7280",
                        callback: (v) => fmtRpShort(v),
                    },
                    grid: { color: "#F3F4F6" },
                    border: { display: false },
                },
            },
        },
    });
}

// ─── Doughnut Chart (FIXED) ─────────────────────────────────────────
function renderDoughnutChart(data) {
    const ctx = document.getElementById("doughnutChart").getContext("2d");
    if (doughnutChartInst) doughnutChartInst.destroy();

    const { labels, values, colors } = data;
    const total = values.reduce((a, b) => a + b, 0);

    // Jika semua nilai 0 tampilkan placeholder agar tidak crash
    const chartValues = total === 0 ? [1, 1] : values;
    const chartColors = total === 0 ? ["#E5E7EB", "#F3F4F6"] : colors;

    doughnutChartInst = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels,
            datasets: [
                {
                    data: chartValues,
                    backgroundColor: chartColors,
                    borderWidth: total === 0 ? 0 : 3,
                    borderColor: "#FFFFFF",
                    hoverOffset: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "68%",
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: total > 0,
                    callbacks: {
                        label: (c) => {
                            const pct = ((c.parsed / total) * 100).toFixed(1);
                            return ` ${c.label}: ${fmtRp(c.parsed)} (${pct}%)`;
                        },
                    },
                },
            },
        },
    });

    // Legend bawah
    const legendEl = document.getElementById("doughnutLegend");
    if (total === 0) {
        legendEl.innerHTML =
            '<p class="text-center text-muted small mt-2">Belum ada data transaksi</p>';
        return;
    }
    legendEl.innerHTML = labels
        .map((lbl, i) => {
            const pct =
                total > 0 ? ((values[i] / total) * 100).toFixed(1) : "0.0";
            return `<div class="donut-legend-item">
            <span class="donut-legend-label">
                <span class="donut-legend-dot" style="background:${colors[i]}"></span>${lbl} (${pct}%)
            </span>
            <span class="donut-legend-value">${fmtRpShort(values[i])}</span>
        </div>`;
        })
        .join("");
}

// ─── History Table ──────────────────────────────────────────────────
async function loadHistory(page = 1) {
    historyPage = page;

    const loadEl = document.getElementById("historyLoading");
    const tableEl = document.getElementById("historyTable");
    const emptyEl = document.getElementById("historyEmpty");
    const bodyEl = document.getElementById("historyBody");

    loadEl.style.display = "flex";
    tableEl.style.display = "none";
    emptyEl.classList.add("d-none");

    const params = new URLSearchParams({ page });
    if (filterStart) params.append("start", filterStart);
    if (filterEnd) params.append("end", filterEnd);

    try {
        const res = await fetch("/reports/history?" + params);
        const json = await res.json();
        if (!json.success) throw new Error();

        loadEl.style.display = "none";

        if (json.data.length === 0) {
            emptyEl.classList.remove("d-none");
            document.getElementById("historyPagination").innerHTML = "";
            return;
        }

        bodyEl.innerHTML = json.data
            .map(
                (t, i) => `
            <tr>
                <td class="text-muted" style="font-size:.8rem">${(page - 1) * 15 + i + 1}</td>
                <td><span class="trx-code">${t.kode_transaksi}</span></td>
                <td><span class="trx-date">${t.created_at}</span></td>
                <td class="text-end text-muted">${t.jumlah_item} item</td>
                <td class="text-end trx-total">${fmtRp(t.total_harga)}</td>
                <td class="text-end trx-profit">${fmtRp(t.total_keuntungan)}</td>
                <td class="text-center">
                    <button class="btn-trx-detail" onclick="openDetail(${t.id})">
                        <i class="bi bi-eye me-1"></i>Detail
                    </button>
                </td>
            </tr>`,
            )
            .join("");

        tableEl.style.display = "table";
        renderHistoryPagination(json.current_page, json.last_page);
    } catch {
        loadEl.style.display = "none";
        emptyEl.classList.remove("d-none");
    }
}

function renderHistoryPagination(current, last) {
    const el = document.getElementById("historyPagination");
    if (last <= 1) {
        el.innerHTML = "";
        return;
    }

    let html = `<button class="page-btn" onclick="loadHistory(${current - 1})" ${current === 1 ? "disabled" : ""}><i class="bi bi-chevron-left"></i></button>`;

    const start = Math.max(1, current - 2);
    const end = Math.min(last, start + 4);
    for (let p = start; p <= end; p++) {
        html += `<button class="page-btn ${p === current ? "active" : ""}" onclick="loadHistory(${p})">${p}</button>`;
    }

    html += `<button class="page-btn" onclick="loadHistory(${current + 1})" ${current === last ? "disabled" : ""}><i class="bi bi-chevron-right"></i></button>`;
    el.innerHTML = html;
}

// ─── Detail Modal ───────────────────────────────────────────────────
async function openDetail(id) {
    activeTransaction = null;

    document.getElementById("detailOverlay").classList.add("show");
    document.getElementById("detailModal").classList.add("show");
    document.getElementById("detailLoading").style.display = "flex";
    document.getElementById("detailContent").style.display = "none";
    document.getElementById("detailKode").textContent = "Memuat...";
    document.getElementById("detailDate").textContent = "";
    document.body.style.overflow = "hidden";

    try {
        const res = await fetch(`/reports/detail/${id}`);
        const json = await res.json();
        if (!json.success) throw new Error("Gagal memuat detail");

        const t = json.data;
        activeTransaction = t;

        document.getElementById("detailKode").textContent = t.kode_transaksi;
        document.getElementById("detailDate").textContent = t.created_at;
        document.getElementById("sumModal").textContent = fmtRp(t.total_modal);
        document.getElementById("sumTotal").textContent = fmtRp(t.total_harga);
        document.getElementById("sumProfit").textContent = fmtRp(
            t.total_keuntungan,
        );

        document.getElementById("detailBody").innerHTML = t.items
            .map(
                (item) => `
            <tr>
                <td>
                    <div class="prod-name">${escHtml(item.nama_produk)}</div>
                    <div class="prod-code">${escHtml(item.kode_produk)}</div>
                </td>
                <td class="text-center">${item.qty}</td>
                <td class="text-end">${fmtRp(item.harga_jual)}</td>
                <td class="text-end fw-bold">${fmtRp(item.subtotal)}</td>
                <td class="text-end" style="color:var(--green)">${fmtRp(item.keuntungan)}</td>
            </tr>`,
            )
            .join("");

        document.getElementById("detailLoading").style.display = "none";
        document.getElementById("detailContent").style.display = "block";
    } catch (e) {
        closeDetailModal();
        Swal.fire({
            icon: "error",
            title: "Gagal",
            text: e.message,
            confirmButtonColor: "#2563EB",
        });
    }
}

function closeDetailModal() {
    document.getElementById("detailOverlay").classList.remove("show");
    document.getElementById("detailModal").classList.remove("show");
    document.body.style.overflow = "";
}

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeDetailModal();
});

// ─── Ekspor Cetak Struk ─────────────────────────────────────────────
function printReceipt() {
    if (!activeTransaction) return;
    window.open(`/pos/receipt/${activeTransaction.id}`, "_blank");
}

// ─── Ekspor WhatsApp ────────────────────────────────────────────────
function shareWA() {
    if (!activeTransaction) return;
    const t = activeTransaction;

    let msg = `*STRUK BELANJA*\n`;
    msg += `━━━━━━━━━━━━━━━━━━━━\n`;
    msg += `Kode   : ${t.kode_transaksi}\n`;
    msg += `Tanggal: ${t.created_at}\n`;
    msg += `━━━━━━━━━━━━━━━━━━━━\n`;

    t.items.forEach((item) => {
        msg += `${item.nama_produk}\n`;
        msg += `  ${item.qty} x ${fmtRp(item.harga_jual)} = ${fmtRp(item.subtotal)}\n`;
    });

    msg += `━━━━━━━━━━━━━━━━━━━━\n`;
    msg += `TOTAL  : ${fmtRp(t.total_harga)}\n`;
    msg += `━━━━━━━━━━━━━━━━━━━━\n`;
    msg += `Terima kasih sudah berbelanja!`;

    window.open("https://wa.me/?text=" + encodeURIComponent(msg), "_blank");
}

// ─── Ekspor Excel ───────────────────────────────────────────────────
function exportExcel() {
    if (!activeTransaction) return;
    const t = activeTransaction;

    // Buat CSV yang bisa dibuka Excel
    const rows = [
        ["LAPORAN TRANSAKSI"],
        ["Kode Transaksi", t.kode_transaksi],
        ["Tanggal", t.created_at],
        [],
        ["No", "Produk", "Kode", "Qty", "Harga Jual", "Subtotal", "Keuntungan"],
    ];

    t.items.forEach((item, i) => {
        rows.push([
            i + 1,
            item.nama_produk,
            item.kode_produk,
            item.qty,
            item.harga_jual,
            item.subtotal,
            item.keuntungan,
        ]);
    });

    rows.push([]);
    rows.push(["", "", "", "", "Total Modal", t.total_modal, ""]);
    rows.push(["", "", "", "", "Total Penjualan", t.total_harga, ""]);
    rows.push(["", "", "", "", "Total Keuntungan", "", t.total_keuntungan]);

    const csv = rows
        .map((r) =>
            r.map((c) => `"${String(c ?? "").replace(/"/g, '""')}"`).join(","),
        )
        .join("\n");
    const bom = "\uFEFF"; // BOM agar Excel baca UTF-8 dengan benar
    downloadFile(
        bom + csv,
        `TRX-${t.kode_transaksi}.csv`,
        "text/csv;charset=utf-8;",
    );
}

// ─── Ekspor PDF ─────────────────────────────────────────────────────
function exportPDF() {
    if (!activeTransaction) return;
    const t = activeTransaction;

    const html = `<!DOCTYPE html><html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice ${t.kode_transaksi}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #111; padding: 32px; }
  h2 { font-size: 18px; font-weight: 800; color: #2563EB; margin-bottom: 4px; }
  .sub { color: #6B7280; font-size: 11px; margin-bottom: 20px; }
  .meta { display: flex; justify-content: space-between; margin-bottom: 20px; background: #F9FAFB; padding: 12px 16px; border-radius: 6px; }
  .meta-item label { font-size: 10px; color: #6B7280; text-transform: uppercase; display: block; }
  .meta-item span { font-weight: 700; font-size: 13px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  th { background: #2563EB; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; }
  th.r, td.r { text-align: right; }
  td { padding: 8px 10px; border-bottom: 1px solid #E5E7EB; font-size: 12px; }
  tr:nth-child(even) td { background: #F9FAFB; }
  .summary { margin-left: auto; width: 280px; }
  .sum-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px; border-bottom: 1px solid #E5E7EB; }
  .sum-row.total { font-size: 14px; font-weight: 800; color: #2563EB; border-bottom: none; margin-top: 4px; }
  .footer { margin-top: 32px; text-align: center; color: #9CA3AF; font-size: 11px; }
</style>
</head>
<body>
<h2>INVOICE</h2>
<p class="sub">KasirPOS — Sistem Kasir Modern</p>
<div class="meta">
  <div class="meta-item"><label>No Transaksi</label><span>${t.kode_transaksi}</span></div>
  <div class="meta-item"><label>Tanggal</label><span>${t.created_at}</span></div>
  <div class="meta-item"><label>Total Item</label><span>${t.items.length} produk</span></div>
</div>
<table>
  <thead><tr><th>No</th><th>Produk</th><th>Kode</th><th class="r">Qty</th><th class="r">Harga</th><th class="r">Subtotal</th></tr></thead>
  <tbody>
    ${t.items
        .map(
            (item, i) => `
    <tr>
      <td>${i + 1}</td>
      <td>${escHtml(item.nama_produk)}</td>
      <td>${escHtml(item.kode_produk)}</td>
      <td class="r">${item.qty}</td>
      <td class="r">${fmtRp(item.harga_jual)}</td>
      <td class="r">${fmtRp(item.subtotal)}</td>
    </tr>`,
        )
        .join("")}
  </tbody>
</table>
<div class="summary">
  <div class="sum-row"><span>Total Modal</span><span>${fmtRp(t.total_modal)}</span></div>
  <div class="sum-row"><span>Total Keuntungan</span><span style="color:#16A34A">${fmtRp(t.total_keuntungan)}</span></div>
  <div class="sum-row total"><span>TOTAL BAYAR</span><span>${fmtRp(t.total_harga)}</span></div>
</div>
<div class="footer">Dicetak oleh KasirPOS • ${new Date().toLocaleString("id-ID")}</div>
<script>window.onload=()=>window.print()<\/script>
</body></html>`;

    const win = window.open("", "_blank");
    win.document.write(html);
    win.document.close();
}

// ─── Utility ────────────────────────────────────────────────────────
function downloadFile(content, filename, type) {
    const blob = new Blob([content], { type });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

function escHtml(str) {
    const d = document.createElement("div");
    d.appendChild(document.createTextNode(str || ""));
    return d.innerHTML;
}

// ─── Init ────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
    loadHistory(1);

    document.getElementById("refreshBtn").addEventListener("click", () => {
        loadDashboard();
        loadHistory(1);
    });

    document.getElementById("filterBtn").addEventListener("click", () => {
        filterStart = document.getElementById("filterStart").value;
        filterEnd = document.getElementById("filterEnd").value;
        loadHistory(1);
    });

    document.getElementById("resetFilterBtn").addEventListener("click", () => {
        filterStart = "";
        filterEnd = "";
        document.getElementById("filterStart").value = "";
        document.getElementById("filterEnd").value = "";
        loadHistory(1);
    });
});
