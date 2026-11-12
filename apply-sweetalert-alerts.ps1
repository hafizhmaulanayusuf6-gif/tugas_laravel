$ErrorActionPreference = 'Stop'
$projectRoot = (Get-Location).Path

# Run this script from the Laravel project root (the folder containing artisan).
if (-not (Test-Path -LiteralPath '.\artisan')) {
    throw 'artisan was not found. Open PowerShell in the Laravel project root, then run this script again.'
}

$viewFiles = @(
    'resources/views/products/index.blade.php',
    'resources/views/members/index.blade.php',
    'resources/views/users/index.blade.php',
    'resources/views/categories/index.blade.php'
)
$allFiles = @(
    'resources/js/app.js',
    'resources/views/templates/footer.blade.php',
    'app/Http/Controllers/OrderController.php',
    'resources/views/orders/index.blade.php'
) + $viewFiles

foreach ($file in $allFiles) {
    if (-not (Test-Path -LiteralPath $file)) {
        throw "Required file not found: $file. No files have been changed."
    }
}

function Replace-One {
    param(
        [string]$Content,
        [string]$Pattern,
        [string]$Replacement,
        [string]$Label
    )

    $regex = [regex]::new($Pattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)
    if (-not $regex.IsMatch($Content)) {
        throw "Expected code pattern was not found: $Label. No files have been changed."
    }

    return $regex.Replace($Content, $Replacement, 1)
}

# Read and prepare every change in memory first. This prevents partial edits
# when the local files differ from the expected project structure.
$updated = @{}
foreach ($file in $allFiles) {
    $updated[$file] = [System.IO.File]::ReadAllText((Resolve-Path -LiteralPath $file))
}

$notificationPattern = '\{\{--\s*Notifikasi\s*--\}\}\s*@if\s*\(session\(''success''\)\).*?@endif\s*@if\s*\(session\(''error''\)\).*?@endif'

$deleteTargets = @{
    'resources/views/products/index.blade.php' = @{
        Pattern = 'data-bs-toggle="modal"\s+data-bs-target="#deleteProductModal"'
        Replacement = 'data-swal-delete="deleteProductForm"'
    }
    'resources/views/members/index.blade.php' = @{
        Pattern = 'data-bs-toggle="modal"\s+data-bs-target="#deleteMemberModal"'
        Replacement = 'data-swal-delete="deleteMemberForm"'
    }
    'resources/views/users/index.blade.php' = @{
        Pattern = 'data-bs-toggle="modal"\s+data-bs-target="#deleteUserModal"'
        Replacement = 'data-swal-delete="deleteUserForm"'
    }
    'resources/views/categories/index.blade.php' = @{
        Pattern = 'data-bs-toggle="modal"\s+data-bs-target="#deleteCategoryModal"'
        Replacement = 'data-swal-delete="deleteCategoryForm"'
    }
}

foreach ($file in $viewFiles) {
    $content = $updated[$file]
    $content = Replace-One $content $notificationPattern '{{-- Flash notification ditangani oleh SweetAlert2 --}}' "${file}: remove Bootstrap flash alerts"
    $target = $deleteTargets[$file]
    $content = Replace-One $content $target.Pattern $target.Replacement "${file}: switch delete button to SweetAlert2"
    $updated[$file] = $content
}

# Orders page: validation errors are now shown by SweetAlert2. Keep the cart
# cleanup behavior, but key it off the dedicated checkout-success flash key.
$orders = $updated['resources/views/orders/index.blade.php']
$orders = Replace-One $orders '\s*@if\s*\(\$errors->any\(\)\)\s*<div class="alert alert-danger" role="alert">.*?</div>\s*@endif' "`r`n" 'orders page: remove inline validation alert'
$orders = Replace-One $orders '@if\s*\(session\(\x27success\x27\)\)' "@if (session('success_popup'))" 'orders page: preserve cart cleanup after checkout'
$updated['resources/views/orders/index.blade.php'] = $orders

# Pass the dedicated checkout-success message to the JavaScript bundle.
$footer = $updated['resources/views/templates/footer.blade.php']
$footer = Replace-One $footer '\x27success\x27\s*=>\s*session\(\x27success\x27\),' "'success' => session('success'),`r`n  'successPopup' => session('success_popup')," 'footer: add successPopup to AppFlash'
$updated['resources/views/templates/footer.blade.php'] = $footer

# Keep the original success flash key for CRUD pages, but give checkout its own
# key so its message can use a centered success dialog instead of a toast.
$controller = $updated['app/Http/Controllers/OrderController.php']
$controller = Replace-One $controller '->with\(\x27success\x27,\s*sprintf\(' '->with(''success_popup'', sprintf(' 'OrderController: use centered checkout success popup'
$updated['app/Http/Controllers/OrderController.php'] = $controller

# Replace app.js with one centralized SweetAlert2 handler.
$updated['resources/js/app.js'] = @'
import './bootstrap';

import Swal from 'sweetalert2';
import 'sweetalert2/themes/bootstrap-5.css';

// Expose Swal for pages that need to open a dialog directly.
window.Swal = Swal;

const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    theme: 'bootstrap-5',
});

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);
}

// Display Laravel flash messages once, with a different style per situation.
document.addEventListener('DOMContentLoaded', () => {
    const flash = window.AppFlash ?? {};
    const validationErrors = Array.isArray(flash.validationErrors)
        ? flash.validationErrors
        : [];

    if (validationErrors.length > 0) {
        const errorItems = validationErrors
            .map((message) => `<li>${escapeHtml(message)}</li>`)
            .join('');

        Swal.fire({
            icon: 'error',
            title: 'Periksa kembali data',
            html: `<ul class="text-start mb-0">${errorItems}</ul>`,
            confirmButtonText: 'Mengerti',
            theme: 'bootstrap-5',
        });
        return;
    }

    if (flash.error) {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi kesalahan',
            text: String(flash.error),
            confirmButtonText: 'Mengerti',
            theme: 'bootstrap-5',
        });
        return;
    }

    if (flash.successPopup) {
        Swal.fire({
            icon: 'success',
            title: 'Transaksi berhasil!',
            text: String(flash.successPopup),
            confirmButtonText: 'Selesai',
            theme: 'bootstrap-5',
        });
        return;
    }

    if (flash.success) {
        toast.fire({
            icon: 'success',
            title: String(flash.success),
        });
    }
});

// Replace the old Bootstrap delete modal with a SweetAlert2 confirmation.
document.addEventListener('click', async (event) => {
    const target = event.target instanceof Element ? event.target : null;
    const trigger = target?.closest('[data-swal-delete]');

    if (!trigger) {
        return;
    }

    event.preventDefault();

    const formId = trigger.dataset.swalDelete;
    const form = formId ? document.getElementById(formId) : null;
    const actionTemplate = form?.dataset.actionTemplate;
    const recordId = trigger.dataset.id;
    const recordName = trigger.dataset.name || 'data ini';

    if (!form || !actionTemplate || !recordId) {
        await Swal.fire({
            icon: 'error',
            title: 'Tidak dapat menghapus data',
            text: 'Form penghapusan tidak ditemukan atau belum dikonfigurasi dengan benar.',
            confirmButtonText: 'Mengerti',
            theme: 'bootstrap-5',
        });
        return;
    }

    const confirmation = await Swal.fire({
        icon: 'warning',
        title: 'Hapus data?',
        text: `Apakah kamu yakin ingin menghapus "${recordName}"? Tindakan ini tidak dapat dibatalkan.`,
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true,
        theme: 'bootstrap-5',
    });

    if (!confirmation.isConfirmed) {
        return;
    }

    form.action = actionTemplate.replace('__ID__', recordId);
    trigger.disabled = true;
    form.submit();
});
'@

# Create backups outside the repository, then write the prepared files.
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupDirectory = Join-Path $env:TEMP "tugas_laravel-sweetalert2-backup-$timestamp"
New-Item -ItemType Directory -Path $backupDirectory -Force | Out-Null

$utf8NoBom = [System.Text.UTF8Encoding]::new($false)
foreach ($file in $allFiles) {
    $backupPath = Join-Path $backupDirectory $file
    $backupParent = Split-Path -Parent $backupPath
    New-Item -ItemType Directory -Path $backupParent -Force | Out-Null
    [System.IO.File]::Copy((Resolve-Path -LiteralPath $file), $backupPath, $true)
}

foreach ($file in $allFiles) {
    [System.IO.File]::WriteAllText((Join-Path $projectRoot $file), $updated[$file], $utf8NoBom)
}

Write-Host ''
Write-Host 'SweetAlert2 alert replacement applied.' -ForegroundColor Green
Write-Host 'CRUD success: top-right toast'
Write-Host 'Delete action: centered confirmation dialog'
Write-Host 'Checkout success: centered success popup'
Write-Host 'Operation/validation errors: centered error popup'
Write-Host "Backup created at: $backupDirectory" -ForegroundColor Cyan
Write-Host ''
Write-Host 'Next run: npm run build'
