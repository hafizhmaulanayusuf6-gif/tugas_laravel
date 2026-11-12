import "./bootstrap";

/* SWEETALERT2_INTEGRATION_START */
import Swal from "sweetalert2";
import "sweetalert2/themes/bootstrap-5.css";

// Sediakan Swal jika suatu halaman memerlukan popup secara langsung.
window.Swal = Swal;

const toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    theme: "bootstrap-5",
});

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (character) => {
        const entities = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;",
        };

        return entities[character];
    });
}

// Baca pesan dari session Laravel setelah redirect (store, update, delete, dan validasi).
document.addEventListener("DOMContentLoaded", () => {
    const flash = window.AppFlash ?? {};
    const validationErrors = Array.isArray(flash.validationErrors)
        ? flash.validationErrors.filter(Boolean)
        : [];

    if (validationErrors.length > 0) {
        const errorItems = validationErrors
            .map((message) => `<li>${escapeHtml(message)}</li>`)
            .join("");

        Swal.fire({
            icon: "error",
            title: "Periksa kembali data",
            html: `<ul class="text-start mb-0 ps-3">${errorItems}</ul>`,
            confirmButtonText: "Mengerti",
            theme: "bootstrap-5",
        });
        return;
    }

    // Error menggunakan popup di tengah.
    if (flash.error) {
        Swal.fire({
            icon: "error",
            title: "Terjadi Kesalahan",
            text: String(flash.error),
            confirmButtonText: "Mengerti",
            theme: "bootstrap-5",
        });
        return;
    }

    // Checkout berhasil menggunakan popup di tengah.
    if (flash.successPopup) {
        Swal.fire({
            icon: "success",
            title: "Transaksi Berhasil!",
            text: String(flash.successPopup),
            confirmButtonText: "Selesai",
            theme: "bootstrap-5",
        });
        return;
    }

    // CRUD berhasil tetap menggunakan toast.
    if (flash.success) {
        toast.fire({
            icon: "success",
            title: String(flash.success),
        });
    }
});

// Konfirmasi hapus dipakai bersama oleh Products, Members, Categories, dan Users.
document.addEventListener("click", async (event) => {
    if (!(event.target instanceof Element)) return;

    const trigger = event.target.closest("[data-swal-delete]");
    if (!trigger) return;

    event.preventDefault();

    const form = document.getElementById(trigger.dataset.swalDelete);
    const recordId = trigger.dataset.id;
    const recordName = trigger.dataset.name || "data ini";

    if (!form || !form.dataset.actionTemplate || !recordId) {
        await Swal.fire({
            icon: "error",
            title: "Tidak dapat menghapus data",
            text: "Form atau informasi data yang diperlukan tidak ditemukan.",
            confirmButtonText: "Tutup",
            theme: "bootstrap-5",
        });
        return;
    }

    const result = await Swal.fire({
        icon: "warning",
        title: "Konfirmasi Hapus",
        text: `Apakah kamu yakin ingin menghapus "${recordName}"? Tindakan ini tidak dapat dibatalkan.`,
        showCancelButton: true,
        confirmButtonText: "Ya, hapus",
        cancelButtonText: "Batal",
        confirmButtonColor: "#dc3545",
        reverseButtons: true,
        focusCancel: true,
        theme: "bootstrap-5",
    });

    if (result.isConfirmed) {
        form.action = form.dataset.actionTemplate.replace(
            "__ID__",
            encodeURIComponent(recordId),
        );
        form.submit();
    }
});
/* SWEETALERT2_INTEGRATION_END */
