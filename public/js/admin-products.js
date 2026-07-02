/**
 * Admin Products — CRUD Logic
 * Tambah, Edit, Hapus produk via AJAX + preview gambar + drag & drop
 */

"use strict";

let isEditMode = false;
let currentId = null;
let imageRemoved = false;

// ─── Modal ─────────────────────────────────────────────────────────────────────

function openAddModal() {
    isEditMode = false;
    currentId = null;
    imageRemoved = false;
    resetForm();
    document.getElementById("modalTitleText").textContent = "Tambah Produk";
    document.getElementById("modalIcon").className = "bi bi-plus-circle me-2";
    document.getElementById("btnSaveText").textContent = "Simpan Produk";
    showModal();
}

async function openEditModal(id) {
    isEditMode = true;
    currentId = id;
    imageRemoved = false;
    resetForm();
    document.getElementById("modalTitleText").textContent = "Edit Produk";
    document.getElementById("modalIcon").className = "bi bi-pencil-circle me-2";
    document.getElementById("btnSaveText").textContent = "Perbarui Produk";

    const btn = document.getElementById("btnSave");
    btn.disabled = true;
    document.getElementById("btnSaveText").innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span>Memuat...';

    showModal();

    try {
        const res = await fetch(ROUTES.show + id, {
            headers: { Accept: "application/json", "X-CSRF-TOKEN": CSRF },
        });
        const json = await res.json();
        if (!json.success) throw new Error("Gagal memuat data produk");
        fillForm(json.data);
    } catch (err) {
        closeModal();
        Swal.fire({
            icon: "error",
            title: "Gagal",
            text: err.message,
            confirmButtonColor: "#2563EB",
        });
    } finally {
        btn.disabled = false;
        document.getElementById("btnSaveText").textContent = "Perbarui Produk";
    }
}

function showModal() {
    document.getElementById("modalOverlay").classList.add("show");
    document.getElementById("productModal").classList.add("show");
    document.body.style.overflow = "hidden";
}

function closeModal() {
    document.getElementById("modalOverlay").classList.remove("show");
    document.getElementById("productModal").classList.remove("show");
    document.body.style.overflow = "";
    resetForm();
}

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal();
});

// ─── Form ───────────────────────────────────────────────────────────────────────

function resetForm() {
    const form = document.getElementById("productForm");
    if (form) form.reset();
    document.getElementById("productId").value = "";
    document.getElementById("imgPreview").src =
        "https://placehold.co/200x140/e8f4f8/2563eb?text=Preview";
    document.getElementById("imgRemoveBtn").classList.add("d-none");
    resetDropZoneText();
    document
        .querySelectorAll(".form-error")
        .forEach((el) => (el.textContent = ""));
    document
        .querySelectorAll(".form-input")
        .forEach((el) => el.classList.remove("is-invalid"));
}

function fillForm(data) {
    document.getElementById("productId").value = data.id;
    document.getElementById("kode_produk").value = data.kode_produk;
    document.getElementById("barcode").value = data.barcode || "";
    document.getElementById("nama_produk").value = data.nama_produk;
    document.getElementById("kategori").value = data.kategori;
    document.getElementById("stok").value = data.stok;
    document.getElementById("harga_modal").value = data.harga_modal;
    document.getElementById("harga_ecer").value = data.harga_ecer;
    document.getElementById("harga_dropship").value = data.harga_dropship;
    if (data.gambar) {
        document.getElementById("imgPreview").src = data.gambar;
        document.getElementById("imgRemoveBtn").classList.remove("d-none");
    }
}

// ─── Submit ─────────────────────────────────────────────────────────────────────

async function submitProduct() {
    clearErrors();

    const btn = document.getElementById("btnSave");
    const btnText = document.getElementById("btnSaveText");
    btn.disabled = true;
    btnText.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

    try {
        const formData = buildFormData();
        const url = isEditMode ? ROUTES.update + currentId : ROUTES.store;
        if (isEditMode) formData.append("_method", "PUT");

        const res = await fetch(url, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": CSRF, Accept: "application/json" },
            body: formData,
        });

        const json = await res.json();

        if (res.status === 422) {
            showErrors(json.errors || {});
            return;
        }

        if (!json.success)
            throw new Error(json.message || "Terjadi kesalahan.");

        closeModal();

        await Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: json.message,
            confirmButtonColor: "#2563EB",
            timer: 2000,
            timerProgressBar: true,
        });

        window.location.reload();
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Gagal",
            text: err.message,
            confirmButtonColor: "#2563EB",
        });
    } finally {
        btn.disabled = false;
        btnText.textContent = isEditMode ? "Perbarui Produk" : "Simpan Produk";
    }
}

function buildFormData() {
    const fd = new FormData();
    fd.append(
        "kode_produk",
        document.getElementById("kode_produk").value.trim(),
    );
    fd.append("barcode", document.getElementById("barcode").value.trim());
    fd.append(
        "nama_produk",
        document.getElementById("nama_produk").value.trim(),
    );
    fd.append("kategori", document.getElementById("kategori").value.trim());
    fd.append("stok", document.getElementById("stok").value);
    fd.append("harga_modal", document.getElementById("harga_modal").value);
    fd.append("harga_ecer", document.getElementById("harga_ecer").value);
    fd.append(
        "harga_dropship",
        document.getElementById("harga_dropship").value,
    );
    const fileInput = document.getElementById("gambar");
    if (fileInput.files[0]) fd.append("gambar", fileInput.files[0]);
    if (imageRemoved) fd.append("remove_gambar", "1");
    return fd;
}

// ─── Delete ─────────────────────────────────────────────────────────────────────

async function deleteProduct(id, nama) {
    const result = await Swal.fire({
        title: 'Hapus "' + nama + '"?',
        text: "Produk yang dihapus tidak dapat dikembalikan.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal",
        confirmButtonColor: "#DC2626",
        cancelButtonColor: "#6B7280",
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(ROUTES.destroy + id, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": CSRF,
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ _method: "DELETE" }),
        });

        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        const card = document.getElementById("card-" + id);
        if (card) {
            card.style.transition = "all .3s ease";
            card.style.opacity = "0";
            card.style.transform = "scale(0.85)";
            setTimeout(() => {
                card.remove();
                updateProductCount();
            }, 300);
        }

        Swal.fire({
            icon: "success",
            title: "Berhasil Dihapus",
            text: json.message,
            confirmButtonColor: "#2563EB",
            timer: 1800,
        });
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Gagal Menghapus",
            text: err.message,
            confirmButtonColor: "#2563EB",
        });
    }
}

function updateProductCount() {
    const remaining = document.querySelectorAll(".admin-product-card").length;
    const badge = document.querySelector(".product-total-badge");
    if (badge) badge.textContent = remaining + " produk";
}

// ─── Error Handling ─────────────────────────────────────────────────────────────

function showErrors(errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const key = field.replace(/\./g, "_");
        const errEl = document.getElementById("err_" + key);
        const input = document.getElementById(key);
        if (errEl) errEl.textContent = messages[0];
        if (input) input.classList.add("is-invalid");
    });
}

function clearErrors() {
    document
        .querySelectorAll(".form-error")
        .forEach((el) => (el.textContent = ""));
    document
        .querySelectorAll(".form-input")
        .forEach((el) => el.classList.remove("is-invalid"));
}

// ─── Image Preview & Drag Drop ──────────────────────────────────────────────────

function previewImage(input) {
    const file = input.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        document.getElementById("err_gambar").textContent =
            "Ukuran gambar maksimal 2MB.";
        input.value = "";
        return;
    }

    document.getElementById("err_gambar").textContent = "";
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById("imgPreview").src = e.target.result;
        document.getElementById("imgRemoveBtn").classList.remove("d-none");
        updateDropZoneText(file.name);
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    document.getElementById("gambar").value = "";
    document.getElementById("imgPreview").src =
        "https://placehold.co/200x140/e8f4f8/2563eb?text=Preview";
    document.getElementById("imgRemoveBtn").classList.add("d-none");
    imageRemoved = true;
    resetDropZoneText();
}

function updateDropZoneText(filename) {
    document
        .getElementById("dropZone")
        .querySelector(".drop-zone-text").textContent = filename;
    document
        .getElementById("dropZone")
        .querySelector(".drop-zone-sub").textContent =
        "Klik untuk ganti gambar";
}

function resetDropZoneText() {
    const dz = document.getElementById("dropZone");
    if (!dz) return;
    dz.querySelector(".drop-zone-text").textContent =
        "Klik atau drag & drop gambar di sini";
    dz.querySelector(".drop-zone-sub").textContent =
        "JPG, PNG, WEBP — Maks. 2MB";
}

document.addEventListener("DOMContentLoaded", () => {
    const dropZone = document.getElementById("dropZone");
    if (!dropZone) return;

    dropZone.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropZone.classList.add("drag-over");
    });

    dropZone.addEventListener("dragleave", () =>
        dropZone.classList.remove("drag-over"),
    );

    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();
        dropZone.classList.remove("drag-over");
        const file = e.dataTransfer.files[0];
        if (!file || !file.type.startsWith("image/")) {
            document.getElementById("err_gambar").textContent =
                "File harus berupa gambar.";
            return;
        }
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById("gambar").files = dt.files;
        previewImage(document.getElementById("gambar"));
    });
});
