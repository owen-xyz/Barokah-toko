/**
 * POS System — Halaman Transaksi
 * Mengelola: produk grid, search realtime, keranjang, perhitungan, simpan transaksi
 */

"use strict";

// ─── State ─────────────────────────────────────────────────────────────────────
const Cart = {
    items: [], // { product, qty, hargaJual, jenisHarga }
};

let currentPage = 1;
let searchKeyword = "";
let searchTimer = null;

// ─── Utility ───────────────────────────────────────────────────────────────────

/** Format angka ke Rupiah */
function formatRp(value) {
    return "Rp " + Math.round(value).toLocaleString("id-ID");
}

/** Ambil CSRF token dari meta tag */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

// ─── Fetch Produk ──────────────────────────────────────────────────────────────

async function loadProducts(page = 1, keyword = "") {
    const grid = document.getElementById("productGrid");
    const spinner = document.getElementById("searchSpinner");
    const countEl = document.getElementById("productCount");

    spinner.classList.remove("d-none");

    const url = keyword
        ? `/api/products/search?q=${encodeURIComponent(keyword)}&page=${page}`
        : `/api/products?page=${page}`;

    try {
        const res = await fetch(url);
        const json = await res.json();

        if (!json.success) throw new Error("Gagal memuat produk");

        const { data } = json;
        renderProducts(data.data);
        renderPagination(data);

        const total = data.total;
        countEl.textContent = keyword
            ? `${total} produk ditemukan untuk "${keyword}"`
            : `${total} produk tersedia`;
    } catch (err) {
        console.error(err);
        grid.innerHTML = `
            <div class="products-empty">
                <i class="bi bi-wifi-off"></i>
                <p>Gagal memuat produk</p>
            </div>`;
    } finally {
        spinner.classList.add("d-none");
    }
}

/** Render kartu produk ke grid */
function renderProducts(products) {
    const grid = document.getElementById("productGrid");

    if (!products.length) {
        grid.innerHTML = `
            <div class="products-empty">
                <i class="bi bi-search"></i>
                <p>Produk tidak ditemukan</p>
            </div>`;
        return;
    }

    grid.innerHTML = products
        .map(
            (p) => `
        <div class="product-card ${p.stok <= 0 ? "out-of-stock" : ""}"
             data-id="${p.id}"
             data-nama="${escapeHtml(p.nama_produk)}"
             data-modal="${p.harga_modal}"
             data-ecer="${p.harga_ecer}"
             data-dropship="${p.harga_dropship}"
             data-stok="${p.stok}"
             onclick="addToCart(this)"
             title="${escapeHtml(p.nama_produk)}">
            ${p.stok <= 0 ? '<span class="out-of-stock-badge">Habis</span>' : ""}
            <img class="product-card-img"
                 src="${p.gambar}"
                 alt="${escapeHtml(p.nama_produk)}"
                 loading="lazy"
                 onerror="this.src='https://placehold.co/80x64/e8f4f8/2563eb?text=IMG'">
            <p class="product-card-name">${escapeHtml(p.nama_produk)}</p>
            <span class="product-card-price">Ecer : ${Math.round(p.harga_ecer).toLocaleString("id-ID")}</span>
            <span class="product-card-price-over">over : ${Math.round(p.harga_dropship).toLocaleString("id-ID")}</span>
            <span class="product-card-stock">Stok: ${p.stok}</span>
        </div>
    `,
        )
        .join("");
}

/** Render pagination */
function renderPagination(meta) {
    const wrap = document.getElementById("paginationWrapper");
    const { last_page, current_page } = meta;

    if (last_page <= 1) {
        wrap.innerHTML = "";
        return;
    }

    let html = `<button class="page-btn" onclick="changePage(${current_page - 1})"
        ${current_page === 1 ? "disabled" : ""}><i class="bi bi-chevron-left"></i></button>`;

    // Tampilkan max 5 halaman di sekitar halaman aktif
    const start = Math.max(1, current_page - 2);
    const end = Math.min(last_page, start + 4);

    for (let i = start; i <= end; i++) {
        html += `<button class="page-btn ${i === current_page ? "active" : ""}"
            onclick="changePage(${i})">${i}</button>`;
    }

    html += `<button class="page-btn" onclick="changePage(${current_page + 1})"
        ${current_page === last_page ? "disabled" : ""}><i class="bi bi-chevron-right"></i></button>`;

    wrap.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    loadProducts(page, searchKeyword);
}

// ─── Keranjang ─────────────────────────────────────────────────────────────────

/** Tambah produk ke keranjang (dari klik card) */
function addToCart(el) {
    const product = {
        id: parseInt(el.dataset.id),
        nama: el.dataset.nama,
        modal: parseFloat(el.dataset.modal),
        ecer: parseFloat(el.dataset.ecer),
        dropship: parseFloat(el.dataset.dropship),
        stok: parseInt(el.dataset.stok),
    };

    // Cek stok
    if (product.stok <= 0) return;

    const existing = Cart.items.find((i) => i.product.id === product.id);

    if (existing) {
        // Cek apakah qty sudah maksimum stok
        if (existing.qty >= product.stok) {
            Swal.fire({
                icon: "warning",
                title: "Stok Tidak Mencukupi",
                text: `Stok ${product.nama} hanya tersisa ${product.stok}.`,
                confirmButtonColor: "#2563EB",
                timer: 2000,
            });
            return;
        }
        existing.qty += 1;
    } else {
        Cart.items.push({
            product,
            qty: 1,
            jenisHarga: "ecer",
            hargaJual: product.ecer,
        });
    }

    renderCart();

    // Animasi feedback pada card
    el.style.transform = "scale(0.95)";
    setTimeout(() => (el.style.transform = ""), 150);
}

/** Render seluruh isi keranjang */
function renderCart() {
    const container = document.getElementById("cartItems");
    const emptyEl = document.getElementById("cartEmpty");

    if (Cart.items.length === 0) {
        container.innerHTML = "";
        container.appendChild(emptyEl);
        emptyEl.style.display = "";
        updateSummary();
        toggleSaveBtn(false);
        return;
    }

    // Sembunyikan empty state
    emptyEl.style.display = "none";

    // Build cart items HTML
    const html = Cart.items
        .map((item, idx) => {
            const subtotal = item.hargaJual * item.qty;
            const modal = item.product.modal * item.qty;
            const keuntungan = subtotal - modal;
            const isManual = item.jenisHarga === "manual";

            return `
<div class="cart-item" id="cart-item-${idx}">
    <div class="cart-item-top">
        <span class="cart-item-name">${escapeHtml(item.product.nama)}</span>
        <button class="btn-remove-item" onclick="removeItem(${idx})" title="Hapus">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="cart-item-controls">
        <select class="price-type-select" onchange="changeHarga(${idx}, this.value)">
            <option value="ecer"     ${item.jenisHarga === "ecer" ? "selected" : ""}>Harga Ecer</option>
            <option value="dropship" ${item.jenisHarga === "dropship" ? "selected" : ""}>Harga Dropship</option>
            <option value="manual"   ${item.jenisHarga === "manual" ? "selected" : ""}>Manual</option>
        </select>

        <div class="qty-controls">
            <button class="btn-qty" onclick="changeQty(${idx}, -1)"
                ${item.qty <= 1 ? "disabled" : ""}>−</button>
            <span class="qty-display">${item.qty}</span>
            <button class="btn-qty" onclick="changeQty(${idx}, 1)"
                ${item.qty >= item.product.stok ? "disabled" : ""}>+</button>
        </div>
    </div>

            ${
                isManual
                    ? `
            <input type="number"
                   class="manual-price-input"
                   placeholder="Masukkan harga jual..."
                   value="${item.hargaJual}"
                   min="0"
                   oninput="setManualHarga(${idx}, this.value)">
            `
                    : ""
            }

            <div class="cart-item-price-row">
                <div>
                    <div class="cart-item-price-label">${formatRp(item.hargaJual)} × ${item.qty}</div>
                    <div class="cart-item-profit">Untung: ${formatRp(keuntungan)}</div>
                </div>
                <span class="cart-item-subtotal">${formatRp(subtotal)}</span>
            </div>
        </div>`;
        })
        .join("");

    container.innerHTML = html;
    container.insertBefore(emptyEl, container.firstChild);

    updateSummary();
    toggleSaveBtn(true);
}

/** Update ringkasan di bawah keranjang */
function updateSummary() {
    let totalHarga = 0;
    let totalModal = 0;
    let totalKeuntungan = 0;

    Cart.items.forEach((item) => {
        const subtotal = item.hargaJual * item.qty;
        const modal = item.product.modal * item.qty;
        const keuntungan = subtotal - modal;

        totalHarga += subtotal;
        totalModal += modal;
        totalKeuntungan += keuntungan;
    });

    document.getElementById("summarySubtotal").textContent =
        formatRp(totalHarga);
    document.getElementById("summaryModal").textContent = formatRp(totalModal);
    document.getElementById("summaryProfit").textContent =
        formatRp(totalKeuntungan);
    document.getElementById("summaryTotal").textContent = formatRp(totalHarga);
}

/** Aktif/non-aktifkan tombol simpan */
function toggleSaveBtn(hasItems) {
    const btn = document.getElementById("saveTransactionBtn");
    btn.disabled = !hasItems;
}

// ─── Cart Actions ──────────────────────────────────────────────────────────────

function changeQty(idx, delta) {
    const item = Cart.items[idx];
    if (!item) return;

    const newQty = item.qty + delta;

    if (newQty < 1) return;
    if (newQty > item.product.stok) {
        Swal.fire({
            icon: "warning",
            title: "Stok Tidak Mencukupi",
            text: `Stok ${item.product.nama} hanya ${item.product.stok}.`,
            confirmButtonColor: "#2563EB",
            timer: 2000,
        });
        return;
    }

    item.qty = newQty;
    renderCart();
}

function changeHarga(idx, jenis) {
    const item = Cart.items[idx];
    if (!item) return;

    item.jenisHarga = jenis;

    if (jenis === "ecer") item.hargaJual = item.product.ecer;
    if (jenis === "dropship") item.hargaJual = item.product.dropship;
    if (jenis === "manual") item.hargaJual = item.product.ecer; // default ke ecer dulu

    renderCart();
}

function setManualHarga(idx, value) {
    const item = Cart.items[idx];
    if (!item) return;

    const harga = parseFloat(value) || 0;
    item.hargaJual = harga < 0 ? 0 : harga;
    updateSummary(); // hanya update summary, tidak re-render supaya input tetap fokus
}

function removeItem(idx) {
    Cart.items.splice(idx, 1);
    renderCart();
}

function clearCart() {
    Cart.items = [];
    renderCart();
}

// ─── Simpan Transaksi ──────────────────────────────────────────────────────────

async function saveTransaction() {
    if (Cart.items.length === 0) return;

    // Validasi harga manual
    for (const item of Cart.items) {
        if (item.jenisHarga === "manual" && item.hargaJual < 0) {
            Swal.fire({
                icon: "error",
                title: "Harga Tidak Valid",
                text: `Harga manual untuk ${item.product.nama} tidak boleh negatif.`,
                confirmButtonColor: "#2563EB",
            });
            return;
        }
    }

    // Konfirmasi
    const totalHarga = Cart.items.reduce(
        (sum, i) => sum + i.hargaJual * i.qty,
        0,
    );
    const result = await Swal.fire({
        title: "Simpan Transaksi?",
        html: `Total pembayaran: <strong>${formatRp(totalHarga)}</strong><br><small>${Cart.items.length} item</small>`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, Simpan",
        cancelButtonText: "Batal",
        confirmButtonColor: "#16A34A",
        cancelButtonColor: "#6B7280",
    });

    if (!result.isConfirmed) return;

    // Loading state
    const btn = document.getElementById("saveTransactionBtn");
    const btnText = document.getElementById("saveTransactionText");
    btn.disabled = true;
    btnText.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    // Payload
    const payload = {
        items: Cart.items.map((item) => ({
            product_id: item.product.id,
            qty: item.qty,
            harga_jual: item.hargaJual,
            jenis_harga: item.jenisHarga,
        })),
    };

    try {
        const res = await fetch("/api/transactions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken(),
                Accept: "application/json",
            },
            body: JSON.stringify(payload),
        });

        const json = await res.json();

        if (json.success) {
            await Swal.fire({
                icon: "success",
                title: "Transaksi Berhasil!",
                html: `Kode: <strong>${json.data.kode_transaksi}</strong><br>Total: <strong>${formatRp(json.data.total_harga)}</strong>`,
                confirmButtonColor: "#2563EB",
                timer: 3000,
                timerProgressBar: true,
            });

            clearCart();
            // Reload produk untuk update stok
            loadProducts(currentPage, searchKeyword);
        } else {
            Swal.fire({
                icon: "error",
                title: "Gagal Menyimpan",
                text: json.message || "Terjadi kesalahan.",
                confirmButtonColor: "#2563EB",
            });
        }
    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: "error",
            title: "Koneksi Bermasalah",
            text: "Tidak dapat terhubung ke server.",
            confirmButtonColor: "#2563EB",
        });
    } finally {
        btn.disabled = false;
        btnText.innerHTML =
            '<i class="bi bi-check2-circle me-2"></i>Simpan Transaksi';
    }
}

// ─── XSS Guard ─────────────────────────────────────────────────────────────────
function escapeHtml(str) {
    const div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ─── Init ───────────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
    // Load produk awal
    loadProducts();

    // Search dengan debounce 300ms
    document.getElementById("searchInput").addEventListener("input", (e) => {
        clearTimeout(searchTimer);
        searchKeyword = e.target.value.trim();
        currentPage = 1;
        searchTimer = setTimeout(() => loadProducts(1, searchKeyword), 300);
    });

    // Clear cart button
    document.getElementById("clearCartBtn").addEventListener("click", () => {
        if (Cart.items.length === 0) return;
        Swal.fire({
            title: "Kosongkan Keranjang?",
            text: "Semua item akan dihapus dari keranjang.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Kosongkan",
            cancelButtonText: "Batal",
            confirmButtonColor: "#DC2626",
        }).then((r) => {
            if (r.isConfirmed) clearCart();
        });
    });

    // Save transaction button
    document
        .getElementById("saveTransactionBtn")
        .addEventListener("click", saveTransaction);
});
