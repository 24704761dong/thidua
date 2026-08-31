<?php
if (!function_exists('get_first_name')) {
    function get_first_name($fullName) {
        if (!$fullName) return '';
        $parts = explode(' ', trim($fullName));
        return end($parts);
    }
}
if (!function_exists('get_short_class_name')) {
    function get_short_class_name($className) {
        if (!$className) return '';
        $c = strtoupper(preg_replace('/\s+/', '', $className));
        $c = str_replace('.', '', $c);
        if (preg_match('/^([ABC])(\d+)$/', $c, $m)) {
            $k = ['A'=>'10', 'B'=>'11', 'C'=>'12'];
            return $k[$m[1]] . 'A' . $m[2];
        }
        return $c;
    }
}
?>
<?php
$page_title = 'Nh?p Vi Ph?m - ' . htmlspecialchars($tuan_hoc['ten_tuan'] ?? '', ENT_QUOTES, 'UTF-8');
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
/* ===== DESIGN SYSTEM ===== */
:root {
    --primary: #224397;
    --accent: #FAB723;
    --border: rgba(34,67,151,0.25);
    --bg-header: rgba(34,67,151,0.08);
    --bg-page: #f4f7f9;
}

body { background-color: var(--bg-page); }

/* ===== TABLE ===== */
#violation-table {
    border: 1px solid var(--border);
    border-collapse: collapse;
    width: 100%;
}
#violation-table thead { background-color: var(--bg-header); }
#violation-table th {
    color: var(--primary);
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.5px;
    padding: 0.65rem 0.5rem;
    border: 1px solid var(--border);
    text-align: center;
    white-space: nowrap;
}
#violation-table td {
    padding: 0;
    font-size: 0.82rem;
    border: 1px solid var(--border);
    vertical-align: middle;
    text-align: center;
    position: relative;
    font-weight: 600;
    color: #1e293b;
}

/* Fix padding and sizing */
#violation-table td.row-number,
#violation-table td.student-code,
#violation-table td.delete-col,
#violation-table td.notification-status,
#violation-table td.user-name,
#violation-table td.text-center { padding: 0.5rem 0.4rem; }

#violation-table td.p-0 { padding: 0 !important; height: 1px; }

#violation-table .cell-edit,
#violation-table input.violation-name {
    width: 100%;
    height: 100%;
    min-height: 44px;
    padding: 0.5rem 0.4rem;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
    outline: none !important;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    font-size: 0.82rem;
    font-weight: 600;
    text-align: center;
    color: #1e293b;
}
#violation-table input.violation-name { text-align: center; }

#violation-table tbody tr:hover { background-color: rgba(34,67,151,0.03); }

/* Cell focus */
#violation-table .cell-edit:focus,
#violation-table input.violation-name:focus {
    background: rgba(34,67,151,0.06) !important;
    box-shadow: inset 0 0 0 2px rgba(34,67,151,0.3) !important;
}

/* Status dot */
.status-dot {
    position: absolute;
    top: 3px; right: 3px;
    width: 7px; height: 7px;
    border-radius: 50%;
    pointer-events: none;
}
tr[data-status="saved"] .status-dot { background-color: #22c55e; }
tr[data-status="new"] .status-dot { background-color: #f59e0b; }
tr[data-status="modified"] .status-dot { background-color: #ef4444; }

/* Row status colors */
tr[data-status="modified"] { background-color: rgba(250,183,35,0.06) !important; }
tr[data-kxd="1"] td.student-code { color: #dc2626; font-weight: 700; }

/* Cell error */
.input-error { box-shadow: inset 0 0 0 2px #ef4444 !important; background: #fef2f2 !important; }

/* Delete mode */
#violation-table.deletion-mode .delete-col { display: table-cell !important; }
#violation-table:not(.deletion-mode) .delete-col { display: none; }
#violation-table.deletion-mode tbody tr:hover { background-color: rgba(239,68,68,0.06); }

/* Range selection */
#violation-table.range-selecting { user-select: none; }
#violation-table td.cell-selected { background: rgba(34,67,151,0.12) !important; outline: 1px solid rgba(34,67,151,0.5); }

/* Scrollbar */
body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
body::-webkit-scrollbar-track { background: transparent; }

/* Table scroll */
.table-scroll-wrapper::-webkit-scrollbar { display: block !important; height: 6px; }
.table-scroll-wrapper::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.25); border-radius: 3px; }

/* Utility */
.hidden { display: none !important; }

/* ===== CUSTOM MODAL ===== */
.custom-modal-backdrop {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(3px);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; visibility: hidden;
    transition: all 0.25s ease;
}
.custom-modal-backdrop.show { opacity: 1; visibility: visible; }
.custom-modal-box {
    background: white;
    border-radius: 16px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    border: 1px solid rgba(34,67,151,0.15);
    max-width: 480px; width: 90%;
    padding: 2rem;
    text-align: center;
    transform: scale(0.9) translateY(-10px);
    transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
    max-height: 90vh;
    overflow-y: auto;
}
.custom-modal-backdrop.show .custom-modal-box { transform: scale(1) translateY(0); }
.custom-modal-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
}
.custom-modal-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; }
.custom-modal-msg { color: #64748b; font-size: 0.92rem; margin-bottom: 1.5rem; }
.custom-modal-btns { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }

/* Modal Header/Body cho cac form (layout tuong tu modal cu) */
.custom-modal-box.has-header { padding: 0; text-align: left; }
.custom-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(34,67,151,0.15);
    background: rgba(34,67,151,0.05);
    display: flex; justify-content: space-between; align-items: center;
    border-radius: 16px 16px 0 0;
}
.custom-modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; color: #224397; text-transform: uppercase; }
.custom-modal-body { padding: 1.5rem; }
.custom-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(34,67,151,0.15);
    display: flex; gap: 0.5rem; justify-content: flex-end;
}
.close-btn { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; }
.close-btn:hover { color: #dc2626; }

/* ===== TOAST ===== */
#toast-container {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    z-index: 10000; display: flex; flex-direction: column; gap: 0.5rem;
}
.toast-item {
    padding: 0.75rem 1.25rem;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 600;
    display: flex; align-items: center; gap: 0.6rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    animation: toastIn 0.35s ease;
    max-width: 380px;
    border: 1px solid;
}
.toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
.toast-error { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
.toast-warning { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
.toast-info { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }
@keyframes toastIn { from { opacity:0; transform: translateX(30px); } to { opacity:1; transform: translateX(0); } }
@keyframes toastOut { to { opacity:0; transform: translateX(30px); } }

/* ===== BTN CHUAN ===== */
.btn-std {
    padding: 0.3rem 0.65rem;
    background: white;
    border: 1px solid var(--border);
    border-radius: 4px;
    color: var(--primary);
    font-size: 0.6875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(34,67,151,0.08);
}
.btn-std:hover { background: var(--accent); color: white; border-color: var(--accent); }
.btn-std.active, .btn-std-primary { background: var(--primary); color: white; border-color: var(--primary); }
.btn-std.active:hover, .btn-std-primary:hover { background: var(--accent); border-color: var(--accent); }
.btn-std-danger { background: white; color: #dc2626; border-color: rgba(220,38,38,0.3); }
.btn-std-danger:hover { background: #dc2626; color: white; border-color: #dc2626; }
.btn-std-green { background: white; color: #16a34a; border-color: rgba(22,163,74,0.3); }
.btn-std-green:hover { background: #16a34a; color: white; border-color: #16a34a; }

/* Dropdown */
.dropdown-wrapper { position: relative; }
.dropdown-menu-std {
    position: absolute;
    top: calc(100% + 4px);
    right: 0;
    z-index: 999;
    min-width: 180px;
    background: white;
    border: 1px solid rgba(34,67,151,0.2);
    border-radius: 10px;
    box-shadow: 0 12px 30px rgba(34,67,151,0.15);
    padding: 0.4rem 0;
    opacity: 0; visibility: hidden;
    transform: scale(0.95) translateY(-4px);
    transition: all 0.15s ease;
    transform-origin: top right;
}
.dropdown-menu-std.open { opacity: 1; visibility: visible; transform: scale(1) translateY(0); }
.dropdown-menu-std a {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.82rem; font-weight: 600;
    color: #334155;
    text-decoration: none;
    transition: all 0.15s;
}
.dropdown-menu-std a:hover { background: rgba(34,67,151,0.06); color: var(--primary); }
.dropdown-menu-std hr { margin: 0.3rem 0; border-color: rgba(34,67,151,0.1); }

/* Loading overlay */
#loading-overlay {
    position: fixed; inset: 0;
    background: rgba(17,24,39,.55);
    backdrop-filter: saturate(160%) blur(2px);
    justify-content: center; align-items: center;
    z-index: 10055; display: none;
}
.spinner-ring {
    width: 52px; height: 52px;
    border: 5px solid rgba(255,255,255,.3);
    border-top: 5px solid white;
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="w-full px-2 lg:px-4">

  <!-- ===== TOOLBAR ===== -->
  <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">

    <!-- TUAN INFO (trai) -->
    <div class="flex items-center gap-2 flex-1">
      <div class="bg-[#224397]/8 border border-[#224397]/20 rounded px-3 py-1.5 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#224397" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
        <span class="font-bold text-[#224397] text-[12px]">NH?P VI PH?M:</span>
        <span class="font-bold text-[#224397] text-[12px]"><?= htmlspecialchars($tuan_hoc['ten_tuan'] ?? '') ?></span>
        <span class="text-slate-400 text-[11px]">(<?= date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau'])) ?> &ndash; <?= date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc'])) ?>)</span>
      </div>
      <span id="status-indicator" class="text-slate-400 text-[11px] italic hidden sm:block"></span>
    </div>

    <!-- BUTTONS (phai) -->
    <div class="flex items-center gap-1.5 flex-wrap" id="toolbar-normal">

      <!-- Excel dropdown -->
      <div class="dropdown-wrapper">
        <button class="btn-std" onclick="toggleDropdown('dropdownExcel')">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h3l-4-4zm-3.396 8.56h-.832l-.983-1.769-.976 1.77H3.4l1.455-2.184L3.5 7.573h.794l.903 1.786.9-1.786h.79l-1.335 2.112 1.552 2.675zm1.41 0V7.573h.718v3.706h2.259v.607H7.514z"/></svg>
          Excel
        </button>
        <div class="dropdown-menu-std" id="dropdownExcel">
          <a href="#" onclick="openModal('importModal'); closeAllDropdowns(); return false;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>
            Nh?p Excel
          </a>
          <a href="/thidua/admin/vi-pham/tai-file-mau">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 1-.708.708z"/></svg>
            T?i File M?u
          </a>
        </div>
      </div>

      <!-- Nhap tay -->
      <button class="btn-std" onclick="openModal('adminManualAddModal')">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-keyboard" viewBox="0 0 16 16"><path d="M14 5a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zM2 4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><path d="M13 10.25a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm0-2a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm-5 0A.25.25 0 0 1 8.25 8h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 8 8.75zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm-6 2A.25.25 0 0 1 6.25 10h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 6 10.75zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 8 10.75zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 10 10.75zm2 0a.25.25 0 0 1 .25-.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25v-.5A.25.25 0 0 1 12.25 10zM2.25 8h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 2 8.75v-.5A.25.25 0 0 1 2.25 8zm2 0h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 4 8.75v-.5A.25.25 0 0 1 4.25 8zm2 0h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 6 8.75v-.5A.25.25 0 0 1 6.25 8zM2.25 10h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 2 10.75v-.5A.25.25 0 0 1 2.25 10zm4.5 0h4.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-4.5A.25.25 0 0 1 6.5 10.75v-.5A.25.25 0 0 1 6.75 10z"/></svg>
        Nh?p tay
      </button>

      <!-- QR -->
      <button class="btn-std" id="start-scan-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16"><path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z"/><path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z"/><path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z"/><path d="M9 2h5v5H9zm1 1v3h3V3zM8 8h1v1H8zm2 0h1v1h-1zm-1 2h1v1H9zm2 0h1v1h-1zm-3 1h1v1H8zm4 1h1v1h-1zm-3 0h1v1h-1zm3-3h1v1h-1z"/></svg>
        QR
      </button>

      <!-- Separator -->
      <div class="w-px h-5 bg-slate-200"></div>

      <!-- Gui thong bao dropdown -->
      <div class="dropdown-wrapper">
        <button class="btn-std btn-std-primary" onclick="toggleDropdown('dropdownNotify')">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-envelope-paper" viewBox="0 0 16 16"><path d="M4 0a2 2 0 0 0-2 2v1.133l-.941.502A2 2 0 0 0 0 5.4V14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5.4a2 2 0 0 0-1.059-1.765L14 3.133V2a2 2 0 0 0-2-2zm10 4.271-4.705 2.509a2 2 0 0 1-2.59 0L2 4.271V2a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1zm-8.5 3.55 3.205 1.709a1 1 0 0 0 1.59 0l3.205-1.709V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/></svg>
          G?i thÙng b·o
        </button>
        <div class="dropdown-menu-std" id="dropdownNotify">
          <a href="#" class="send-notification-btn" data-send-mode="all">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-check-all" viewBox="0 0 16 16"><path d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"/></svg>
            G?i t?t c?
          </a>
          <a href="#" class="send-notification-btn" data-send-mode="unsent">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-envelope-exclamation" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/><path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1.5a.5.5 0 0 1-1 0V11a.5.5 0 0 1 1 0m0 3a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/></svg>
            G?i chua g?i
          </a>
          <a href="#" class="send-notification-btn" data-send-mode="selected">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-ui-checks" viewBox="0 0 16 16"><path d="M7 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zM2 1a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 8a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2zm.854-3.646a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zm0 8a.5.5 0 0 1-.708 0l-1-1a.5.5 0 0 1 .708-.708l.646.647 1.646-1.647a.5.5 0 0 1 .708.708zM7 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 8a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/></svg>
            G?i d„ ch·ªçn
          </a>
        </div>
      </div>

      <!-- Them hang -->
      <button id="add-row-btn" class="btn-std">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
        ThÍm h‡ng
      </button>

      <!-- Xoa (mode) -->
      <button id="delete-mode-btn" class="btn-std btn-std-danger">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16"><path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/></svg>
        XÛa
      </button>

      <!-- Confirm/cancel delete (an theo mac dinh) -->
      <button id="confirm-delete-btn" class="btn-std btn-std-danger hidden">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg>
        X·c nh?n XÛa
      </button>
      <button id="cancel-delete-btn" class="btn-std hidden">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708z"/></svg>
        H?y
      </button>

      <!-- Separator -->
      <div class="w-px h-5 bg-slate-200"></div>

      <!-- Xu ly diem danh -->
      <a href="/thidua/admin/diem-danh-nang-cao/nhap?tuan_id=<?= $tuan_hoc['id']; ?>" class="btn-std">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16"><path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/><path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/></svg>
        X? l˝ di?m danh
      </a>

      <!-- Xu ly tre hoc -->
      <a href="/thidua/admin/xu-ly-tre-hoc?tuan_id=<?= $tuan_hoc['id']; ?>" class="btn-std" style="color:#d97706;border-color:rgba(217,119,6,0.3);" onmouseover="this.style.background='#d97706';this.style.color='white';this.style.borderColor='#d97706'" onmouseout="this.style.background='';this.style.color='#d97706';this.style.borderColor='rgba(217,119,6,0.3)'">
        <i class="bi bi-person-running"></i>
        X? l˝ tr? h·ªçc
      </a>

      <!-- Quay lai -->
      <a href="/thidua/admin/chon-tuan" class="btn-std">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>
        Quay l?i
      </a>

      <!-- Luu tat ca -->
      <button id="save-all-btn" class="btn-std btn-std-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg>
        Luu t?t c?
      </button>
    </div>
  </div>

  <!-- Phim tat hint -->
  <div class="text-[10px] text-slate-400 mb-2 italic">M?o: Ctrl/Cmd+S d? luu nhanh | Arrow keys d? di chuy?n | Paste Excel tr?c ti?p v‡o b?ng</div>

  <!-- ===== TABLE CARD ===== -->
  <div class="bg-white rounded shadow border border-[#224397]/25 mb-4 p-0 overflow-hidden">
    <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
      <h3 class="mb-0 text-[14px] font-bold text-[#224397] uppercase flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle-fill mr-2" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
        DANH S·CH VI PH?M
      </h3>
      <span class="text-[11px] text-slate-400"><?= count($danh_sach_vi_pham_da_nhap ?? []) ?> b?n ghi</span>
    </div>
    <div class="overflow-x-auto w-full table-scroll-wrapper">
      <table id="violation-table" class="w-full text-left text-sm text-slate-600">
        <thead>
          <tr>
            <th class="delete-col" style="width:36px">
              <input class="w-3.5 h-3.5 rounded border-slate-300" type="checkbox" id="select-all-checkbox" aria-label="Ch·ªçn t?t c?">
            </th>
            <th style="width:36px">STT</th>
            <th style="width:80px">S? CCCD</th>
            <th style="width:180px">H? v‡ TÍn <span class="text-red-400">*</span></th>
            <th style="width:70px">L?p <span class="text-red-400">*</span></th>
            <th style="width:95px">Ng‡y Vi Ph?m <span class="text-red-400">*</span></th>
            <th>TÍn Vi Ph?m <span class="text-red-400">*</span></th>
            <th style="width:150px">Ghi Ch˙</th>
            <th style="width:80px">Tr?ng Th·i</th>
            <th style="width:80px">Ngu?i Nh?p</th>
          </tr>
        </thead>
        <tbody id="violation-table-body">
          <?php foreach ($danh_sach_vi_pham_da_nhap as $index => $vp): ?>
          <tr data-vi-pham-id="<?= htmlspecialchars((string)($vp['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
              data-hoc-sinh-id="<?= htmlspecialchars((string)($vp['hoc_sinh_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
              data-status="saved"
              class="<?= (($vp['trang_thai_hoc_tap'] ?? '') === 'nghi_hoc') ? 'line-through text-slate-400' : '' ?>"
              <?= (($vp['trang_thai_hoc_tap'] ?? '') === 'nghi_hoc') ? 'title="H·ªçc sinh d„ ngh? h·ªçc"' : '' ?>>
            <td class="delete-col text-center"><input type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 row-checkbox" value="<?= htmlspecialchars((string)($vp['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></td>
            <td class="row-number text-center font-semibold text-slate-400"><?= $index + 1; ?></td>
            <td class="student-code text-center">
              <?php if (!empty($vp['hoc_sinh_id'])): ?>
                <?= htmlspecialchars($vp['ma_hoc_sinh'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
              <?php else: ?>
                <span class="text-red-600 font-bold">KXD</span>
              <?php endif; ?>
            </td>
            <td class="editable student-name relative p-0">
              <div contenteditable="true" class="cell-edit"><?= htmlspecialchars($vp['hoc_sinh_id'] ? ($vp['ho_ten_day_du'] ?? '') : ($vp['raw_ho_ten'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
              <span class="status-dot" title="–„ luu"></span>
            </td>
            <td class="editable class-name relative p-0">
              <div contenteditable="true" class="cell-edit"><?= htmlspecialchars($vp['hoc_sinh_id'] ? ($vp['ten_lop'] ?? '') : ($vp['raw_ten_lop'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
            </td>
            <td class="editable violation-date relative p-0">
              <div contenteditable="true" class="cell-edit"><?= date('d/m/Y', strtotime($vp['ngay_vi_pham'])); ?></div>
            </td>
            <td class="violation-name-cell relative p-0">
              <input type="text" class="cell-edit violation-name" list="violation-list-datalist" value="<?= htmlspecialchars($vp['ten_vi_pham'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Gı d? tÏm...">
            </td>
            <td class="editable note relative p-0">
              <div contenteditable="true" class="cell-edit"><?= htmlspecialchars($vp['ghi_chu'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
            </td>
            <td class="notification-status text-center">
              <?php
                $status = $vp['trang_thai_thong_bao'] ?? 'Chua TB';
                if ($status === 'ƒê√£ TB GV') {
                    echo '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-100 text-sky-800">ƒê√£ TB GV</span>';
                } elseif ($status === 'ƒê√£ TB HS') {
                    echo '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">ƒê√£ TB HS</span>';
                } elseif ($status === 'ƒê√£ TB') {
                    echo '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-100 text-green-800">ƒê√£ TB</span>';
                } else {
                    echo '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Chua TB</span>';
                }
              ?>
            </td>
            <td class="user-name text-center text-[11px] text-slate-500" data-user-id="<?= htmlspecialchars((string)($vp['nguoi_nhap_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
              <?php
                $displayName = 'N/A';
                if (!empty($vp['nguoi_nhap_type'])) {
                    $firstName = get_first_name($vp['nguoi_nhap_ten']);
                    if ($vp['nguoi_nhap_type'] === 'admin') {
                        $displayName = $firstName . ' - AD';
                    } elseif ($vp['nguoi_nhap_type'] === 'ctv' && !empty($vp['lop_ctv'])) {
                        $shortClass = get_short_class_name($vp['lop_ctv']);
                        $displayName = $firstName . ' - ' . $shortClass;
                    } else {
                        $displayName = $vp['nguoi_nhap_ten'];
                    }
                }
                echo htmlspecialchars($displayName);
              ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /w-full -->

<!-- ===== TEMPLATE HANG MOI ===== -->
<template id="new-row-template">
  <tr data-status="new">
    <td class="delete-col text-center"><input type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 row-checkbox"></td>
    <td class="row-number text-center font-semibold text-slate-400"></td>
    <td class="student-code text-center"></td>
    <td class="editable student-name relative p-0"><div contenteditable="true" class="cell-edit"></div><span class="status-dot" title="H‡ng m?i"></span></td>
    <td class="editable class-name relative p-0"><div contenteditable="true" class="cell-edit"></div></td>
    <td class="editable violation-date relative p-0"><div contenteditable="true" class="cell-edit"></div></td>
    <td class="violation-name-cell relative p-0"><input type="text" class="cell-edit violation-name" list="violation-list-datalist" placeholder="Gı d? tÏm..."></td>
    <td class="editable note relative p-0"><div contenteditable="true" class="cell-edit"></div></td>
    <td class="notification-status text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Chua TB</span></td>
    <td class="user-name text-center text-[11px] text-slate-400" data-user-id="<?= htmlspecialchars((string)($_SESSION['user_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars((string)($_SESSION['user_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
  </tr>
</template>

<!-- Datalist vi pham -->
<datalist id="violation-list-datalist">
  <?php foreach ($danh_sach_cau_hinh_vi_pham as $vp): ?>
    <option value="<?= htmlspecialchars($vp['ten_vi_pham'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-id="<?= htmlspecialchars((string)($vp['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
  <?php endforeach; ?>
</datalist>

<!-- ===== TOAST CONTAINER ===== -->
<div id="toast-container"></div>

<!-- ===== LOADING OVERLAY ===== -->
<div id="loading-overlay">
  <div class="spinner-ring"></div>
</div>

<!-- ============================
     CUSTOM MODALS (thay the alert())
============================= -->

<!-- Modal canh bao xoa -->
<div class="custom-modal-backdrop" id="modal-confirm-delete">
  <div class="custom-modal-box">
    <div class="custom-modal-icon text-red-500">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
    </div>
    <div class="custom-modal-title text-red-600">C?nh B·o XÛa!</div>
    <div class="custom-modal-msg" id="modal-confirm-delete-msg">B?n cÛ ch?c ch?n mu?n xÛa c·c m?c d„ ch·ªçn?</div>
    <div class="custom-modal-btns">
      <button class="btn-std btn-std-danger" id="modal-confirm-delete-ok">X·c nh?n XÛa</button>
      <button class="btn-std" onclick="closeModal('modal-confirm-delete')">H?y</button>
    </div>
  </div>
</div>

<!-- Modal thanh cong -->
<div class="custom-modal-backdrop" id="modal-success">
  <div class="custom-modal-box">
    <div class="custom-modal-icon text-green-500">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
    </div>
    <div class="custom-modal-title text-[#224397]" id="modal-success-title">Th‡nh cÙng!</div>
    <div class="custom-modal-msg" id="modal-success-msg"></div>
    <div class="custom-modal-btns">
      <button class="btn-std btn-std-primary" id="modal-success-ok" onclick="closeModal('modal-success')">OK</button>
    </div>
  </div>
</div>

<!-- Modal loi -->
<div class="custom-modal-backdrop" id="modal-error">
  <div class="custom-modal-box">
    <div class="custom-modal-icon text-red-500">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg>
    </div>
    <div class="custom-modal-title text-red-600">CÛ L?i X?y Ra</div>
    <div class="custom-modal-msg" id="modal-error-msg"></div>
    <div class="custom-modal-btns">
      <button class="btn-std btn-std-danger" onclick="closeModal('modal-error')">ƒê√≥ng</button>
    </div>
  </div>
</div>

<!-- Modal xac nhan gui mail sau khi luu -->
<div class="custom-modal-backdrop" id="confirmSendMailModal">
  <div class="custom-modal-box" style="max-width:500px">
    <div class="custom-modal-icon" style="color:#224397">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/></svg>
    </div>
    <div class="custom-modal-title text-[#224397]">G?i ThÙng B·o Email</div>
    <div class="custom-modal-msg">ƒê√£ luu th‡nh cÙng! B?n mu?n g?i thÙng b·o d?n ai?</div>
    <div class="custom-modal-btns" style="flex-direction:column; gap:0.5rem">
      <div class="flex gap-2 justify-center">
        <button class="btn-std send-now-btn" data-target="gvcn" style="background:#0ea5e9;color:white;border-color:#0ea5e9;font-size:0.8rem;padding:0.5rem 1rem">Ch? GVCN</button>
        <button class="btn-std send-now-btn" data-target="hs" style="background:#8b5cf6;color:white;border-color:#8b5cf6;font-size:0.8rem;padding:0.5rem 1rem">Ch? H·ªçc sinh</button>
        <button class="btn-std send-now-btn" data-target="both" style="background:#16a34a;color:white;border-color:#16a34a;font-size:0.8rem;padding:0.5rem 1rem">C? hai</button>
      </div>
      <button class="btn-std" onclick="closeModal('confirmSendMailModal'); suppressBeforeUnload=true; window.location.reload();" style="align-self:center">KhÙng g?i, t?i l?i trang</button>
    </div>
  </div>
</div>

<!-- Modal ket qua gui mail -->
<div class="custom-modal-backdrop" id="sendStatusModal">
  <div class="custom-modal-box" style="max-width:560px; text-align:left">
    <div class="text-center mb-4">
      <div class="custom-modal-icon text-green-500" style="font-size:2.5rem">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
      </div>
      <div class="custom-modal-title text-[#224397]">K?t Qu? G?i Email</div>
    </div>
    <div id="sendStatusModalBody" class="text-sm text-slate-600 max-h-64 overflow-y-auto mb-4"></div>
    <div class="text-center">
      <button class="btn-std btn-std-primary" onclick="closeModal('sendStatusModal'); suppressBeforeUnload=true; window.location.reload();" style="font-size:0.85rem;padding:0.5rem 1.5rem">ƒê√≥ng & T?i l?i</button>
    </div>
  </div>
</div>

<!-- Modal chon doi tuong gui -->
<div class="custom-modal-backdrop" id="sendTargetModal">
  <div class="custom-modal-box" style="max-width:480px">
    <div class="custom-modal-icon" style="color:#224397;font-size:2.5rem">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/></svg>
    </div>
    <div class="custom-modal-title text-[#224397]">Ch·ªçn ƒê·ªëi Tu?ng G?i</div>
    <div class="custom-modal-msg">Ch·ªçn d?i tu?ng nh?n thÙng b·o email</div>
    <div class="custom-modal-btns">
      <button class="btn-std send-confirm-btn" data-target="gvcn" style="background:#0ea5e9;color:white;border-color:#0ea5e9">Ch? GVCN</button>
      <button class="btn-std send-confirm-btn" data-target="hs" style="background:#8b5cf6;color:white;border-color:#8b5cf6">Ch? H·ªçc sinh</button>
      <button class="btn-std send-confirm-btn" data-target="both" style="background:#16a34a;color:white;border-color:#16a34a">C? hai</button>
      <button class="btn-std" onclick="closeModal('sendTargetModal')">H?y</button>
    </div>
  </div>
</div>

<!-- Modal Import Excel -->
<div class="custom-modal-backdrop" id="importModal">
  <div class="custom-modal-box has-header" style="max-width:520px; padding:0;">
    <div class="custom-modal-header">
      <h5><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-up mr-2 inline-block" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M7.646 5.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708z"/><path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383m.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z"/></svg> Import Excel</h5>
      <button class="close-btn" onclick="closeModal('importModal')">&times;</button>
    </div>
    <form id="import-form" action="/thidua/admin/vi-pham?action=import" method="POST" enctype="multipart/form-data">
      <div class="custom-modal-body">
        <input type="hidden" name="tuan_id" value="<?php echo $tuan_hoc['id']; ?>">
        <p class="text-sm text-slate-500 mb-3">Ch·ªçn file Excel (.xlsx) theo d˙ng d?nh d?ng m?u.</p>
        <input class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4 focus:border-[#224397] focus:ring-1 focus:ring-[#224397]" type="file" name="excelFile" accept=".xlsx" required>
        <div class="bg-[#224397]/5 rounded-lg p-3 text-xs text-slate-500 flex items-start gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#224397" class="bi bi-info-circle flex-shrink-0 mt-0.5" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg>
          B?n cÛ th? t?i file m?u ? menu <strong>Excel &rarr; T?i File M?u</strong>.
        </div>
      </div>
      <div class="custom-modal-footer">
        <button type="button" class="btn-std" onclick="closeModal('importModal')">H?y</button>
        <button type="submit" class="btn-std btn-std-primary">T?i lÍn</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal QR Scanner -->
<div class="custom-modal-backdrop" id="qr-scanner-modal">
  <div class="custom-modal-box has-header" style="max-width:400px; padding:0;">
    <div class="custom-modal-header">
      <h5><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-qr-code-scan mr-2 inline-block" viewBox="0 0 16 16"><path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z"/><path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z"/><path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z"/><path d="M9 2h5v5H9zm1 1v3h3V3zM8 8h1v1H8zm2 0h1v1h-1zm-1 2h1v1H9zm2 0h1v1h-1zm-3 1h1v1H8zm4 1h1v1h-1zm-3 0h1v1h-1zm3-3h1v1h-1z"/></svg> QuÈt M„ QR</h5>
      <button class="close-btn" onclick="closeScannerModal()">&times;</button>
    </div>
    <div class="custom-modal-body text-center">
      <div id="qr-reader" style="width:100%; border-radius:8px; overflow:hidden; margin:0 auto;"></div>
      <p class="mt-3 text-sm text-slate-500">ƒê∆∞a m„ QR v‡o khung hÏnh camera</p>
    </div>
  </div>
</div>

<!-- Modal X·c nh?n quÈt -->
<div class="custom-modal-backdrop" id="adminConfirmScanModal">
  <div class="custom-modal-box has-header" style="max-width:480px; padding:0;">
    <div class="custom-modal-header">
      <h5>X·c Nh?n Vi Ph?m</h5>
      <button class="close-btn" onclick="closeModal('adminConfirmScanModal')">&times;</button>
    </div>
    <div class="custom-modal-body">
      <div id="admin-scan-info" class="hidden">
        <div class="flex items-center gap-3 mb-3 p-3 bg-green-50 border border-green-200 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#16a34a" viewBox="0 0 16 16"><path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
          <div>
            <div class="font-bold text-green-800" id="admin-student-name"></div>
            <div class="text-sm text-green-700" id="admin-student-class"></div>
            <div class="text-xs text-green-600" id="admin-violation-date"></div>
          </div>
        </div>
        <div class="mb-3">
          <label class="block text-xs font-semibold text-[#224397] uppercase mb-1">Ch·ªçn L?i Vi Ph?m</label>
          <select id="admin-violation-select-qr" class="w-full border border-[#224397]/30 rounded-lg px-3 py-2 text-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]">
            <option value="">-- Ch·ªçn l?i --</option>
            <?php foreach ($danh_sach_cau_hinh_vi_pham as $vp): ?>
            <option value="<?= htmlspecialchars((string)($vp['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($vp['ten_vi_pham'] ?? '', ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="block text-xs font-semibold text-[#224397] uppercase mb-1">Ghi Ch˙ (t˘y ch·ªçn)</label>
          <input type="text" id="admin-violation-notes-qr" class="w-full border border-[#224397]/30 rounded-lg px-3 py-2 text-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]" placeholder="Ghi ch˙ thÍm...">
        </div>
      </div>
      <div id="admin-scan-error" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm"></div>
    </div>
    <div class="custom-modal-footer">
      <button id="admin-cancel-scan-btn" class="btn-std">QuÈt l?i</button>
      <button id="admin-confirm-violation-btn-qr" class="btn-std btn-std-primary">X·c Nh?n & ThÍm</button>
    </div>
  </div>
</div>

<!-- Modal Nh?p Tay -->
<div class="custom-modal-backdrop" id="adminManualAddModal">
  <div class="custom-modal-box has-header" style="max-width:600px; padding:0;">
    <div class="custom-modal-header">
      <h5><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-keyboard mr-2 inline-block" viewBox="0 0 16 16"><path d="M14 5a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zM2 4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><path d="M13 10.25a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm0-2a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm-5 0A.25.25 0 0 1 8.25 8h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 8 8.75zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm-6 2A.25.25 0 0 1 6.25 10h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 6 10.75zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25zm2 0a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 8 10.75zm2 0a.25.25 0 0 1 .25-.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 10 10.75zm2 0a.25.25 0 0 1 .25-.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25v-.5A.25.25 0 0 1 12.25 10zM2.25 8h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 2 8.75v-.5A.25.25 0 0 1 2.25 8zm2 0h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 4 8.75v-.5A.25.25 0 0 1 4.25 8zm2 0h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 6 8.75v-.5A.25.25 0 0 1 6.25 8zM2.25 10h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5A.25.25 0 0 1 2 10.75v-.5A.25.25 0 0 1 2.25 10zm4.5 0h4.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-4.5A.25.25 0 0 1 6.5 10.75v-.5A.25.25 0 0 1 6.75 10z"/></svg> Nh?p Tay Vi Ph?m</h5>
      <button class="close-btn" onclick="closeModal('adminManualAddModal')">&times;</button>
    </div>
    <div class="custom-modal-body">
      <form id="admin-manual-add-form">
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div>
            <label class="block text-xs font-semibold text-[#224397] uppercase mb-1">H·ªç v‡ TÍn <span class="text-red-400">*</span></label>
            <input type="text" id="admin-ho_ten" class="w-full border border-[#224397]/30 rounded-lg px-3 py-2 text-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]" placeholder="Nguyen Van A..." required>
          </div>
          <div>
            <label class="block text-xs font-semibold text-[#224397] uppercase mb-1">L?p <span class="text-red-400">*</span></label>
            <input type="text" id="admin-ten_lop" class="w-full border border-[#224397]/30 rounded-lg px-3 py-2 text-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]" placeholder="10A1..." required>
          </div>
        </div>
        <div id="admin-lookup-result-container" class="mb-3"></div>
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div>
            <label class="block text-xs font-semibold text-[#224397] uppercase mb-1">Ng‡y Vi Ph?m <span class="text-red-400">*</span></label>
            <input type="date" id="admin-ngay_vi_pham" class="w-full border border-[#224397]/30 rounded-lg px-3 py-2 text-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div>
            <label class="block text-xs font-semibold text-[#224397] uppercase mb-1">L?i Vi Ph?m <span class="text-red-400">*</span></label>
            <select id="admin-violation-select-manual" class="w-full border border-[#224397]/30 rounded-lg px-3 py-2 text-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]" required>
              <option value="">-- Ch·ªçn l?i --</option>
              <?php foreach ($danh_sach_cau_hinh_vi_pham as $vp): ?>
              <option value="<?= htmlspecialchars((string)($vp['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($vp['ten_vi_pham'] ?? '', ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-[#224397] uppercase mb-1">Ghi Ch˙</label>
          <input type="text" id="admin-violation-notes-manual" class="w-full border border-[#224397]/30 rounded-lg px-3 py-2 text-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]" placeholder="Ghi ch˙ thÍm...">
        </div>
      </form>
    </div>
    <div class="custom-modal-footer">
      <button type="button" class="btn-std" onclick="closeModal('adminManualAddModal')">ƒê√≥ng</button>
      <button id="admin-confirm-violation-btn-manual" class="btn-std btn-std-primary" disabled>ThÍm V‡o B?ng</button>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
<script src="/thidua/public/assets/libs/html5-qrcode.min.js" type="text/javascript"></script>

<script>
// ===== HELPER FUNCTIONS MODAL & TOAST =====
let suppressBeforeUnload = false;

function openModal(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.add('show'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.remove('show'); document.body.style.overflow = ''; }
}
function showToast(msg, type = 'info', duration = 4000) {
  const c = document.getElementById('toast-container');
  const icons = {
    success: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>',
    error: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg>',
    warning: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>',
    info: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg>'
  };
  const t = document.createElement('div');
  t.className = `toast-item toast-${type}`;
  t.innerHTML = `${icons[type] || ''}<span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => {
    t.style.animation = 'toastOut 0.35s ease forwards';
    setTimeout(() => t.remove(), 350);
  }, duration);
}
function showError(msg) { document.getElementById('modal-error-msg').textContent = msg; openModal('modal-error'); }
function showSuccess(msg, title = 'Th‡nh cÙng!', cb = null) {
  document.getElementById('modal-success-title').textContent = title;
  document.getElementById('modal-success-msg').textContent = msg;
  const btn = document.getElementById('modal-success-ok');
  btn.onclick = () => { closeModal('modal-success'); if (cb) cb(); };
  openModal('modal-success');
}

// Dropdown toggle
function toggleDropdown(id) {
  document.querySelectorAll('.dropdown-menu-std').forEach(m => { if (m.id !== id) m.classList.remove('open'); });
  document.getElementById(id).classList.toggle('open');
}
function closeAllDropdowns() { document.querySelectorAll('.dropdown-menu-std').forEach(m => m.classList.remove('open')); }
document.addEventListener('click', (e) => { if (!e.target.closest('.dropdown-wrapper')) closeAllDropdowns(); });

// Close modal on backdrop click
document.querySelectorAll('.custom-modal-backdrop').forEach(bd => {
  bd.addEventListener('click', (e) => { if (e.target === bd) closeModal(bd.id); });
});

// ============================================================
// MAIN LOGIC (JS tu user - da thay the alert() bang custom modal)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  const table = document.getElementById('violation-table');
  const tableBody = document.getElementById('violation-table-body');
  const newRowTemplate = document.getElementById('new-row-template');
  const addRowBtn = document.getElementById('add-row-btn');
  const saveAllBtn = document.getElementById('save-all-btn');
  const statusIndicator = document.getElementById('status-indicator');
  const violationDatalist = document.getElementById('violation-list-datalist');
  const loadingOverlay = document.getElementById('loading-overlay');
  const deleteModeBtn = document.getElementById('delete-mode-btn');
  const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
  const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
  const selectAllCheckbox = document.getElementById('select-all-checkbox');
  let idsForSending = [];

  // ===== PHP -> JS =====
  const tuanHocId = <?php echo json_encode($tuan_hoc['id']); ?>;
  const tuanStartDate = new Date('<?php echo $tuan_hoc['ngay_bat_dau']; ?>T00:00:00Z');
  const tuanEndDate = new Date('<?php echo $tuan_hoc['ngay_ket_thuc']; ?>T23:59:59Z');

  // Tooltip helper
  const ensureTooltip = (el, title) => {
    if (!el) return;
    if (title !== undefined) el.setAttribute('title', title);
  };

  // Map datalist: ten vi pham -> id
  const violationMap = (() => {
    const map = new Map();
    if (violationDatalist) {
      violationDatalist.querySelectorAll('option').forEach(opt => map.set(opt.value, opt.dataset.id));
    }
    return map;
  })();

  // ===== GUI THONG BAO =====
  async function sendNotifications(violation_ids, target) {
    if (!violation_ids || violation_ids.length === 0) {
      showToast('KhÙng cÛ vi ph?m n‡o d? g?i thÙng b·o.', 'warning');
      return;
    }
    loadingOverlay.style.display = 'flex';
    try {
      const response = await fetch('/thidua/api/send-notification', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ violation_ids, send_target: target })
      });
      const result = await response.json();
      if (result.success) {
        let reportHTML = '';
        if (result.sent_to_gvcn?.length) reportHTML += `<div class="mb-2"><strong class="text-green-700">ƒê√£ g?i d?n GVCN:</strong><ul class="mt-1 ml-4 text-sm">` + result.sent_to_gvcn.map(i => `<li>${i}</li>`).join('') + `</ul></div>`;
        if (result.sent_to_hs?.length) reportHTML += `<div class="mb-2"><strong class="text-green-700">ƒê√£ g?i d?n H·ªçc sinh:</strong><ul class="mt-1 ml-4 text-sm">` + result.sent_to_hs.map(i => `<li>${i}</li>`).join('') + `</ul></div>`;
        if (result.failed_gvcn?.length) reportHTML += `<div class="mb-2"><strong class="text-red-700">L?i khi g?i d?n GVCN:</strong><ul class="mt-1 ml-4 text-sm text-red-600">` + result.failed_gvcn.map(i => `<li>${i}</li>`).join('') + `</ul></div>`;
        if (result.failed_hs?.length) reportHTML += `<div class="mb-2"><strong class="text-red-700">L?i khi g?i d?n H·ªçc sinh:</strong><ul class="mt-1 ml-4 text-sm text-red-600">` + result.failed_hs.map(i => `<li>${i}</li>`).join('') + `</ul></div>`;
        document.getElementById('sendStatusModalBody').innerHTML = reportHTML || '<p class="text-center text-slate-500">KhÙng cÛ email n‡o du?c g?i di.</p>';
        openModal('sendStatusModal');
      } else {
        throw new Error(result.message || 'G?i thÙng b·o th?t b?i.');
      }
    } catch (error) {
      showError('L?i khi g?i thÙng b·o: ' + error.message);
    } finally {
      loadingOverlay.style.display = 'none';
    }
  }

  // Helpers
  const debounce = (fn, ms = 300) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); }; };
  const rowControllers = new WeakMap();
  const startFetchForRow = (row) => { const prev = rowControllers.get(row); if (prev) prev.abort(); const c = new AbortController(); rowControllers.set(row, c); return c; };
  function normalizeSpaces(s) { return (s || '').replace(/\s+/g, ' ').trim(); }

  // Canh bao roi trang khi chua luu
  window.addEventListener('beforeunload', function (e) {
    if (suppressBeforeUnload) return;
    if (tableBody.querySelector('tr[data-status="new"], tr[data-status="modified"]')) {
      e.preventDefault(); e.returnValue = '';
    }
  });

  function normalizeStudentName(name) {
    let n = normalizeSpaces(name).toLowerCase();
    n = n.replace(/(^|[\s-])\p{L}/gu, m => m.toUpperCase());
    return n;
  }
  function normalizeClassName(className) {
    let c = normalizeSpaces(className).toUpperCase().replace(/\./g, '').replace(/\s+/g, '');
    const m1 = c.match(/^([ABC])(\d+)$/);
    if (m1) { const k = {'A':'10','B':'11','C':'12'}; return `${k[m1[1]]}A${m1[2]}`; }
    return c;
  }
  function handleNormalizationAndLookup(row) {
    const snDiv = row.querySelector('.student-name div.cell-edit');
    const cnDiv = row.querySelector('.class-name div.cell-edit');
    if (snDiv) snDiv.textContent = normalizeStudentName(snDiv.textContent);
    if (cnDiv) cnDiv.textContent = normalizeClassName(cnDiv.textContent);
    if (snDiv && cnDiv && snDiv.textContent && cnDiv.textContent) debouncedLookup(row);
  }

  async function handleAutoLookup(row, { force = false } = {}) {
    if (row.dataset.status === 'saved' && !force) return;
    const snDiv = row.querySelector('.student-name div.cell-edit');
    const cnDiv = row.querySelector('.class-name div.cell-edit');
    const codeCell = row.querySelector('.student-code');
    const ho_ten = snDiv ? normalizeSpaces(snDiv.textContent) : '';
    const ten_lop = cnDiv ? normalizeSpaces(cnDiv.textContent) : '';
    codeCell.textContent = '';
    row.removeAttribute('data-hoc-sinh-id');
    if (!ho_ten || !ten_lop) return;
    codeCell.innerHTML = '<span class="inline-block w-3 h-3 border-2 border-[#224397] border-t-transparent rounded-full animate-spin"></span>';
    const controller = startFetchForRow(row);
    try {
      const res = await fetch(`/thidua/api/lookup-student?ho_ten=${encodeURIComponent(ho_ten)}&ten_lop=${encodeURIComponent(ten_lop)}`, { signal: controller.signal });
      const data = await res.json();
      if (data.success && data.student) {
        codeCell.textContent = data.student.ma_hoc_sinh;
        row.dataset.hocSinhId = data.student.id;
        row.dataset.kxd = '0';
        if (snDiv.textContent.trim() !== data.student.ho_ten) snDiv.textContent = data.student.ho_ten;
        const noteTd = row.querySelector('.note');
        const noteDiv = noteTd?.querySelector('div.cell-edit');
        if (noteTd && noteDiv && noteTd.dataset.autoNote === '1') { noteDiv.textContent = ''; delete noteTd.dataset.autoNote; }
      } else {
        codeCell.innerHTML = '<span class="text-red-600 font-bold">KXD</span>';
        row.dataset.kxd = '1';
        const noteTd = row.querySelector('.note');
        const noteDiv = noteTd?.querySelector('div.cell-edit');
        if (noteTd && noteDiv && (!normalizeSpaces(noteDiv.textContent) || noteTd.dataset.autoNote === '1')) {
          noteDiv.textContent = data.message || 'KhÙng tÏm th?y h·ªçc sinh.';
          noteTd.dataset.autoNote = '1';
        }
      }
    } catch (e) {
      if (e.name === 'AbortError') return;
      codeCell.innerHTML = '<span class="text-red-600">L?i API</span>';
    }
  }
  const debouncedLookup = debounce(handleAutoLookup, 350);

  function updateRowNumbers() {
    tableBody.querySelectorAll('tr').forEach((row, idx) => {
      const el = row.querySelector('.row-number');
      if (el) el.textContent = idx + 1;
    });
  }

  function addNewRow({ focus = true, status = 'new' } = {}) {
    const newRow = newRowTemplate.content.cloneNode(true).querySelector('tr');
    newRow.dataset.status = status;
    tableBody.appendChild(newRow);
    updateRowNumbers();
    const dot = newRow.querySelector('.status-dot');
    if (dot) ensureTooltip(dot, 'H‡ng m?i');
    if (focus) { const first = newRow.querySelector('.student-name div.cell-edit'); if (first) first.focus(); }
    attachEventListeners(newRow);
    return newRow;
  }

  function setRowStatus(row, status, tipText) {
    row.dataset.status = status;
    const dot = row.querySelector('.status-dot');
    if (dot) ensureTooltip(dot, tipText);
  }

  function handleDateValidation(row) {
    const dateTd = row.querySelector('.violation-date');
    const dateDiv = dateTd?.querySelector('div.cell-edit');
    if (!dateDiv) return;
    let value = normalizeSpaces(dateDiv.textContent);
    dateDiv.classList.remove('input-error');
    if (!value) { delete row.dataset.isoDate; return; }
    let day, month, year;
    const parts = value.split(/[/.\-]/).map(p => parseInt(p, 10)).filter(n => !isNaN(n));
    if (parts.length === 1 && parts[0] >= 1 && parts[0] <= 31) {
      day = parts[0];
      let tmp = new Date(tuanStartDate);
      while (tmp <= tuanEndDate) {
        if (tmp.getUTCDate() === day) { month = tmp.getUTCMonth() + 1; year = tmp.getUTCFullYear(); break; }
        tmp.setUTCDate(tmp.getUTCDate() + 1);
      }
    } else if (parts.length === 2) { [day, month] = parts; year = tuanStartDate.getUTCFullYear(); }
    else if (parts.length === 3) { [day, month, year] = parts; if (String(year).length === 2) year = 2000 + year; }
    if (!day || !month || !year) { dateDiv.classList.add('input-error'); delete row.dataset.isoDate; return; }
    const inputDate = new Date(Date.UTC(year, month - 1, day));
    if (isNaN(inputDate.getTime()) || inputDate < tuanStartDate || inputDate > tuanEndDate) {
      dateDiv.classList.add('input-error'); delete row.dataset.isoDate; return;
    }
    const formatted = `${String(day).padStart(2,'0')}/${String(month).padStart(2,'0')}/${year}`;
    if (dateDiv.textContent.trim() !== formatted) dateDiv.textContent = formatted;
    row.dataset.isoDate = `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
  }

  function primeInitialValues(row) {
    row.querySelectorAll('.cell-edit, .violation-name').forEach(cell => {
      cell.dataset.initialValue = cell.isContentEditable ? normalizeSpaces(cell.textContent) : normalizeSpaces(cell.value);
    });
  }

  function attachEventListeners(row) {
    const checkbox = row.querySelector('.row-checkbox');
    if (checkbox) checkbox.addEventListener('change', updateSelectAllCheckboxState);
  }

  // Range selection
  const selectorCells = '.editable, .violation-name-cell';
  const selectionState = { active: false, start: null, end: null };
  function getRowList() { return Array.from(tableBody.querySelectorAll('tr')); }
  function getCellListInRow(row) { return Array.from(row.querySelectorAll(selectorCells)); }
  function getCellCoords(td) {
    const rows = getRowList(); const row = td.closest('tr');
    return { rIndex: rows.indexOf(row), cIndex: getCellListInRow(row).indexOf(td) };
  }
  function clearVisualSelection() { table.querySelectorAll('td.cell-selected').forEach(td => td.classList.remove('cell-selected')); }
  function applyVisualSelection() {
    if (!selectionState.start || !selectionState.end) return;
    clearVisualSelection();
    const rows = getRowList();
    const {rIndex:r1,cIndex:c1} = getCellCoords(selectionState.start);
    const {rIndex:r2,cIndex:c2} = getCellCoords(selectionState.end);
    for (let r = Math.min(r1,r2); r <= Math.max(r1,r2); r++) {
      const cells = getCellListInRow(rows[r]);
      for (let c = Math.min(c1,c2); c <= Math.max(c1,c2); c++) { if (cells[c]) cells[c].classList.add('cell-selected'); }
    }
  }
  function getSelectedCells() {
    if (!selectionState.start || !selectionState.end) return [];
    const rows = getRowList();
    const {rIndex:r1,cIndex:c1} = getCellCoords(selectionState.start);
    const {rIndex:r2,cIndex:c2} = getCellCoords(selectionState.end);
    const result = [];
    for (let r = Math.min(r1,r2); r <= Math.max(r1,r2); r++) {
      const cells = getCellListInRow(rows[r]);
      result.push(Array.from({length: Math.max(c1,c2)-Math.min(c1,c2)+1}, (_,i) => cells[Math.min(c1,c2)+i]));
    }
    return result;
  }
  function isSelectableCell(el) { const td = el.closest && el.closest('td'); return !!(td && td.matches(selectorCells)); }
  tableBody.addEventListener('mousedown', (e) => {
    if (!isSelectableCell(e.target)) return;
    selectionState.active = true; selectionState.start = e.target.closest('td'); selectionState.end = selectionState.start;
    table.classList.add('range-selecting'); applyVisualSelection();
  });
  tableBody.addEventListener('mouseover', (e) => {
    if (!selectionState.active || !isSelectableCell(e.target)) return;
    selectionState.end = e.target.closest('td'); applyVisualSelection();
  });
  document.addEventListener('mouseup', () => { if (!selectionState.active) return; selectionState.active = false; table.classList.remove('range-selecting'); });

  function setCellValue(td, value) {
    const v = (value ?? '').toString();
    const editableDiv = td.querySelector && td.querySelector('div[contenteditable]');
    const input = td.querySelector && td.querySelector('input, textarea');
    if (editableDiv) { editableDiv.textContent = v; editableDiv.dispatchEvent(new Event('input',{bubbles:true})); editableDiv.dispatchEvent(new Event('blur',{bubbles:true})); }
    else if (input) { input.value = v; input.dispatchEvent(new Event('input',{bubbles:true})); input.dispatchEvent(new Event('change',{bubbles:true})); input.dispatchEvent(new Event('blur',{bubbles:true})); }
    if (td.classList && td.classList.contains('violation-date')) { const tr = td.closest('tr'); if (tr) handleDateValidation(tr); }
  }

  function parseTableText(text, { forceSingleColumn = false } = {}) {
    const norm = text.replace(/\r\n/g, '\n');
    if (norm.includes('\t')) return norm.trim().split('\n').map(line => line.split('\t'));
    if (forceSingleColumn) return norm.trim().split('\n').map(line => [line]);
    const rows = []; let row = [], field = '', inQuotes = false;
    const pushField = () => { row.push(field); field = ''; };
    const pushRow = () => { rows.push(row); row = []; };
    for (let i = 0; i < norm.length; i++) {
      const ch = norm[i];
      if (inQuotes) { if (ch === '"') { if (norm[i+1] === '"') { field += '"'; i++; } else inQuotes = false; } else field += ch; }
      else { if (ch === '"') inQuotes = true; else if (ch === ',') pushField(); else if (ch === '\n') { pushField(); pushRow(); } else field += ch; }
    }
    pushField(); if (row.length > 1 || (row.length === 1 && row[0] !== '')) pushRow();
    return rows;
  }

  // Blur delegation
  tableBody.addEventListener('blur', function (e) {
    const cell = e.target; const row = cell.closest('tr'); if (!row) return;
    if (cell.closest('.student-name') || cell.closest('.class-name')) handleNormalizationAndLookup(row);
    if (cell.matches('.cell-edit') || cell.matches('.violation-name')) {
      const current = cell.isContentEditable ? normalizeSpaces(cell.textContent) : normalizeSpaces(cell.value);
      if (current !== (cell.dataset.initialValue || '') && row.dataset.status === 'saved') setRowStatus(row, 'modified', 'D? li?u d„ thay d?i');
      cell.dataset.initialValue = current;
    }
    if (cell.closest('.violation-date')) handleDateValidation(row);
  }, true);

  // Paste
  tableBody.addEventListener('paste', function (e) {
    const target = e.target; const focusedCell = target.closest && target.closest('td'); if (!focusedCell) return;
    const raw = (e.clipboardData || window.clipboardData).getData('text');
    const selectedCellsMatrix = (() => { const picked = table.querySelectorAll('td.cell-selected'); return picked.length === 0 ? null : getSelectedCells(); })();
    if (selectedCellsMatrix && raw) {
      e.preventDefault();
      const selRows = selectedCellsMatrix.length; const selCols = selectedCellsMatrix[0].length;
      const parsed = parseTableText(raw, { forceSingleColumn: selCols === 1 });
      const srcR = parsed.length; const srcC = Math.max(...parsed.map(r => r.length));
      const singleCell = (srcR === 1 && srcC === 1), repeatRow = (srcR === 1 && srcC > 0 && srcC <= selCols), exactMatch = (srcR === selRows && srcC === selCols);
      for (let r = 0; r < selRows; r++) {
        let rl = false, rdv = false;
        for (let c = 0; c < selCols; c++) {
          const td = selectedCellsMatrix[r][c];
          const value = exactMatch ? (parsed[r]?.[c]??'') : repeatRow ? (parsed[0]?.[c]??'') : singleCell ? (parsed[0]?.[0]??'') : (parsed[r%srcR]?.[c%srcC]??'');
          setCellValue(td, value);
          if (td.classList.contains('student-name') || td.classList.contains('class-name')) rl = true;
          if (td.classList.contains('violation-date')) rdv = true;
        }
        const tr = selectedCellsMatrix[r][0].closest('tr');
        if (rl) handleNormalizationAndLookup(tr); if (rdv) handleDateValidation(tr);
      }
      return;
    }
    if (target.isContentEditable) e.preventDefault();
    if (raw && (raw.includes('\t') || raw.includes('\n') || raw.includes(','))) {
      e.preventDefault();
      const row = focusedCell.closest('tr'); const rows = getRowList(); const currentRowIndex = rows.indexOf(row);
      const cellsInRow = getCellListInRow(row); const startColIndex = cellsInRow.indexOf(focusedCell);
      const parsed = parseTableText(raw, { forceSingleColumn: (cellsInRow.length - startColIndex) <= 1 });
      while (getRowList().length < currentRowIndex + parsed.length) addNewRow({ focus: false });
      parsed.forEach((cols, r) => {
        const useRow = getRowList()[currentRowIndex + r]; const colsInRowUse = getCellListInRow(useRow);
        let rl = false, rdv = false;
        cols.forEach((val, c) => { const td = colsInRowUse[startColIndex + c]; if (!td) return; setCellValue(td, val); if (td.classList.contains('student-name')||td.classList.contains('class-name')) rl=true; if (td.classList.contains('violation-date')) rdv=true; });
        if (rl) handleNormalizationAndLookup(useRow); if (rdv) handleDateValidation(useRow);
      });
      return;
    }
    if (target.isContentEditable) { e.preventDefault(); document.execCommand('insertText', false, raw); }
  });

  // Input auto-add row
  tableBody.addEventListener('input', function (e) {
    const row = e.target.closest('tr'); if (!row) return;
    if (row.dataset.status === 'saved' && (e.target.closest('.editable') || e.target.classList.contains('violation-name'))) setRowStatus(row, 'modified', 'D? li?u d„ thay d?i');
    if (row === tableBody.lastElementChild && !row.dataset.newRowAdded) {
      const snDiv = row.querySelector('.student-name div.cell-edit');
      const cnDiv = row.querySelector('.class-name div.cell-edit');
      const hasData = normalizeSpaces(snDiv?.textContent) || normalizeSpaces(cnDiv?.textContent);
      if (hasData) { addNewRow({ focus: false }); row.dataset.newRowAdded = '1'; }
    }
    if (e.target.closest('.student-name') || e.target.closest('.class-name')) debouncedLookup(row);
    const noteTd = e.target.closest('.note');
    if (noteTd && noteTd.dataset && noteTd.dataset.autoNote) delete noteTd.dataset.autoNote;
  });

  // Keyboard navigation
  tableBody.addEventListener('keydown', function (e) {
    const ae = document.activeElement;
    if (!ae || (!ae.hasAttribute('contenteditable') && ae.tagName !== 'INPUT' && ae.tagName !== 'TEXTAREA')) return;
    const isNavKey = ['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.key);
    const isEnter = e.key === 'Enter' && !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey;
    if (!isNavKey && !isEnter) return;
    e.preventDefault();
    const currentCell = ae.closest('td'); if (!currentCell) return;
    const currentRow = currentCell.parentElement;
    const allRows = Array.from(tableBody.querySelectorAll('tr'));
    const rIdx = allRows.indexOf(currentRow);
    const allCells = Array.from(currentRow.querySelectorAll(selectorCells));
    const cIdx = allCells.indexOf(currentCell);
    let targetCell = null;
    if (isNavKey) {
      if (e.key === 'ArrowUp' && rIdx > 0) targetCell = allRows[rIdx-1].querySelectorAll(selectorCells)[cIdx];
      else if (e.key === 'ArrowDown') { let nr = allRows[rIdx+1]; if (!nr) nr = addNewRow({focus:false}); targetCell = nr.querySelectorAll(selectorCells)[cIdx]; }
      else if (e.key === 'ArrowLeft' && cIdx > 0) targetCell = allCells[cIdx-1];
      else if (e.key === 'ArrowRight' && cIdx < allCells.length-1) targetCell = allCells[cIdx+1];
    }
    if (isEnter) { let nr = allRows[rIdx+1]; if (!nr) nr = addNewRow({focus:false}); targetCell = nr.querySelectorAll(selectorCells)[cIdx] || nr.querySelectorAll(selectorCells)[0]; }
    if (targetCell) {
      const focus = targetCell.querySelector('div[contenteditable], input, textarea') || targetCell;
      focus.focus();
      if (focus.isContentEditable) { const r = document.createRange(); r.selectNodeContents(focus); r.collapse(false); const s = window.getSelection(); s.removeAllRanges(); s.addRange(r); }
    }
  });

  // Ctrl+S shortcut
  window.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); if (!saveAllBtn.disabled) saveAllBtn.click(); }
  });

  // Replace-on-first-key
  function isPrintableKey(e) { return !e.ctrlKey && !e.metaKey && !e.altKey && e.key !== 'Enter' && e.key && e.key.length === 1; }
  function setCaretToEnd(el) {
    if (el.isContentEditable) { const r = document.createRange(); r.selectNodeContents(el); r.collapse(false); const s = window.getSelection(); s.removeAllRanges(); s.addRange(r); }
    else if ('value' in el) el.setSelectionRange(el.value.length, el.value.length);
  }
  tableBody.addEventListener('focusin', function (e) {
    const t = e.target;
    if ((t.closest('.class-name') && t.isContentEditable) || (t.closest('.violation-date') && t.isContentEditable) || (t.classList.contains('violation-name') && ['INPUT','TEXTAREA'].includes(t.tagName))) t.dataset.replaceOnFirstKey = '1';
  });
  tableBody.addEventListener('keydown', function (e) {
    const t = e.target; if (t.dataset.replaceOnFirstKey !== '1') return;
    const isBksp = e.key === 'Backspace', isDel = e.key === 'Delete';
    if (!(isPrintableKey(e) || isBksp || isDel)) return;
    e.preventDefault();
    if (t.isContentEditable) t.textContent = ''; else if ('value' in t) t.value = '';
    if (isPrintableKey(e)) {
      if (t.isContentEditable) { t.textContent = e.key; t.dispatchEvent(new Event('input',{bubbles:true})); }
      else if ('value' in t) { t.value = e.key; t.dispatchEvent(new Event('input',{bubbles:true})); t.dispatchEvent(new Event('change',{bubbles:true})); }
    }
    delete t.dataset.replaceOnFirstKey; setCaretToEnd(t);
  }, true);
  tableBody.addEventListener('focusout', (e) => { if (e.target?.dataset?.replaceOnFirstKey) delete e.target.dataset.replaceOnFirstKey; });

  // Nut them hang
  if (addRowBtn) addRowBtn.addEventListener('click', () => addNewRow());

  // LUU TAT CA
  saveAllBtn.addEventListener('click', async function () {
    this.disabled = true;
    this.innerHTML = '<span class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></span> ƒêang quÈt...';
    statusIndicator.textContent = 'B?t d?u quÈt...'; statusIndicator.classList.remove('hidden');
    const violations = []; let isValid = true;
    const rowsToProcess = tableBody.querySelectorAll('tr[data-status="new"], tr[data-status="modified"]');
    for (const row of rowsToProcess) {
      if (!isValid) break;
      const snDiv = row.querySelector('.student-name div.cell-edit');
      const cnDiv = row.querySelector('.class-name div.cell-edit');
      const viInput = row.querySelector('.violation-name');
      const studentName = (snDiv?.textContent||'').trim();
      const className = (cnDiv?.textContent||'').trim();
      const violationName = (viInput?.value||'').trim();
      if (!studentName && !className && !violationName && row.dataset.status === 'new') continue;
      
      row.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
      handleDateValidation(row);
      
      const dateDiv = row.querySelector('.violation-date div.cell-edit');
      const cau_hinh_id = violationMap.get(violationName);
      
      if (!studentName) { if(snDiv) snDiv.classList.add('input-error'); isValid = false; }
      if (!className) { if(cnDiv) cnDiv.classList.add('input-error'); isValid = false; }
      if (!row.dataset.isoDate) { if(dateDiv) dateDiv.classList.add('input-error'); isValid = false; }
      if (!violationName || !cau_hinh_id) { viInput.classList.add('input-error'); isValid = false; }
      if (!isValid) continue;
      
      const noteDiv = row.querySelector('.note div.cell-edit');
      violations.push({
        id: row.dataset.viPhamId || null,
        tuan_hoc_id: tuanHocId,
        hoc_sinh_id: row.dataset.hocSinhId || null,
        cau_hinh_vi_pham_id: cau_hinh_id,
        ngay_vi_pham: row.dataset.isoDate,
        ghi_chu: (noteDiv?.textContent||'').trim(),
        ten_hoc_sinh_raw: row.dataset.hocSinhId ? null : studentName,
        ten_lop_raw: row.dataset.hocSinhId ? null : className
      });
    }
    if (!isValid) {
      showToast('Vui lÚng s?a c·c Ù b? l?i (vi·ªÅn ƒë·ªè) tru?c khi luu.', 'error');
      this.disabled = false;
      this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg> Luu t?t c?';
      statusIndicator.textContent = 'ƒê√£ d?ng do cÛ l?i.'; return;
    }
    if (violations.length === 0) {
      showToast('KhÙng cÛ thay d?i n‡o d? luu.', 'info');
      this.disabled = false; this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg> Luu t?t c?';
      statusIndicator.textContent = ''; return;
    }
    statusIndicator.textContent = `ƒêang luu ${violations.length} thay d?i...`;
    try {
      const response = await fetch('/thidua/admin/vi-pham?action=api_save', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({violations}) });
      const result = await response.json();
      if (result.success) {
        suppressBeforeUnload = true;
        const newIds = result.saved_ids || [];
        if (newIds.length > 0) {
          document.querySelectorAll('.send-now-btn').forEach(btn => {
            btn.onclick = async () => { closeModal('confirmSendMailModal'); await sendNotifications(newIds, btn.dataset.target); };
          });
          document.getElementById('confirmSendMailModal').addEventListener('click', function h(e) {
            if (e.target.textContent.includes('KhÙng g?i')) { suppressBeforeUnload = true; window.location.reload(); this.removeEventListener('click', h); }
          });
          openModal('confirmSendMailModal');
        } else {
          showToast('ƒê√£ luu th‡nh cÙng!', 'success');
          setTimeout(() => { suppressBeforeUnload = true; window.location.reload(); }, 1200);
        }
      } else throw new Error(result.message || 'Luu th?t b?i.');
    } catch (error) {
      showError('L?i khi luu d? li?u: ' + error.message);
      this.disabled = false;
      this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg> Luu t?t c?';
      suppressBeforeUnload = false;
    }
  });

  // Logic nut gui thong bao
  document.querySelectorAll('.send-notification-btn').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault(); closeAllDropdowns();
      const mode = this.dataset.sendMode;
      let ids = [];
      if (mode === 'all') {
        tableBody.querySelectorAll('tr[data-vi-pham-id]').forEach(row => { const id = row.dataset.viPhamId; if (id) ids.push(id); });
      } else if (mode === 'unsent') {
        tableBody.querySelectorAll('tr').forEach(row => {
          const badge = row.querySelector('.notification-status span');
          if (badge && badge.textContent.includes('Chua')) { const id = row.dataset.viPhamId; if (id) ids.push(id); }
        });
      } else if (mode === 'selected') {
        const selected = tableBody.querySelectorAll('.row-checkbox:checked');
        if (selected.length === 0) { showToast('Vui lÚng ch·ªçn Ìt nh?t m?t vi ph?m d? g?i.', 'warning'); return; }
        selected.forEach(cb => ids.push(cb.value));
      }
      if (ids.length === 0) { showToast('KhÙng tÏm th?y vi ph?m n‡o ph˘ h?p.', 'warning'); return; }
      idsForSending = ids;
      openModal('sendTargetModal');
    });
  });

  document.querySelectorAll('.send-confirm-btn').forEach(button => {
    button.addEventListener('click', function () {
      const target = this.dataset.target;
      closeModal('sendTargetModal');
      sendNotifications(idsForSending, target);
    });
  });

  // XOA MODE
  function toggleDeletionMode(enable) {
    if (!table) return;
    table.classList.toggle('deletion-mode', enable);
    if (addRowBtn) addRowBtn.classList.toggle('hidden', enable);
    if (saveAllBtn) saveAllBtn.classList.toggle('hidden', enable);
    if (deleteModeBtn) deleteModeBtn.classList.toggle('hidden', enable);
    if (confirmDeleteBtn) confirmDeleteBtn.classList.toggle('hidden', !enable);
    if (cancelDeleteBtn) cancelDeleteBtn.classList.toggle('hidden', !enable);
    if (!enable) {
      if (selectAllCheckbox) { selectAllCheckbox.checked = false; selectAllCheckbox.indeterminate = false; }
      document.querySelectorAll('.row-checkbox:checked').forEach(cb => cb.checked = false);
    }
  }
  function updateSelectAllCheckboxState() {
    if (!selectAllCheckbox) return;
    const all = document.querySelectorAll('.row-checkbox');
    const checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length === 0) { selectAllCheckbox.checked = false; selectAllCheckbox.indeterminate = false; }
    else if (checked.length === all.length) { selectAllCheckbox.checked = true; selectAllCheckbox.indeterminate = false; }
    else { selectAllCheckbox.checked = false; selectAllCheckbox.indeterminate = true; }
  }
  if (deleteModeBtn) deleteModeBtn.addEventListener('click', () => toggleDeletionMode(true));
  if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', () => toggleDeletionMode(false));
  if (selectAllCheckbox) selectAllCheckbox.addEventListener('change', function () { document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked); });

  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', function () {
      const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
      const idsToDelete = Array.from(checkedBoxes).map(cb => cb.value).filter(id => id);
      if (idsToDelete.length === 0) { showToast('Vui lÚng ch·ªçn Ìt nh?t m?t m?c d„ luu d? xÛa.', 'warning'); return; }
      document.getElementById('modal-confirm-delete-msg').textContent = `B?n cÛ ch?c ch?n mu?n xÛa ${idsToDelete.length} m?c d„ ch·ªçn? H‡nh d?ng n‡y khÙng th? ho‡n t·c.`;
      document.getElementById('modal-confirm-delete-ok').onclick = async () => {
        closeModal('modal-confirm-delete');
        loadingOverlay.style.display = 'flex';
        try {
          const response = await fetch('/thidua/admin/vi-pham?action=api_delete', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ids: idsToDelete}) });
          const result = await response.json();
          loadingOverlay.style.display = 'none';
          if (result.success) { showToast(result.message || 'ƒê√£ xÛa th‡nh cÙng!', 'success'); setTimeout(() => { suppressBeforeUnload = true; window.location.reload(); }, 1000); }
          else showError(result.message || 'XÛa th?t b?i.');
        } catch (error) {
          loadingOverlay.style.display = 'none';
          showError('L?i khi xÛa: ' + error.message);
        }
      };
      openModal('modal-confirm-delete');
    });
  }

  // Import overlay
  const importForm = document.getElementById('import-form');
  if (importForm) {
    importForm.addEventListener('submit', function () {
      const fi = this.querySelector('input[type="file"]');
      if (fi && fi.files.length > 0) loadingOverlay.style.display = 'flex';
    });
  }

  // Khoi dong
  tableBody.querySelectorAll('tr').forEach(row => {
    if (row.dataset.viPhamId) setRowStatus(row, 'saved', 'ƒê√£ luu');
    else setRowStatus(row, 'new', 'H‡ng m?i');
    if (!row.dataset.hocSinhId || /KXD/i.test(row.querySelector('.student-code')?.textContent || '')) row.dataset.kxd = '1';
    else row.dataset.kxd = '0';
    primeInitialValues(row);
    attachEventListeners(row);
  });
  if (tableBody.querySelectorAll('tr').length === 0) addNewRow({ status: 'new' });

  // ===== QR SCANNER =====
  const startScanBtn = document.getElementById('start-scan-btn');
  let html5QrCode = null;
  let lastSelectedViolationId = localStorage.getItem('lastSelectedViolationId') || '';
  let scannedStudentData = null;
  let lookedUpStudentData = null;

  function addDataToGrid(studentData, violationId, violationName, date, notes) {
    const newRow = addNewRow({ focus: false });
    newRow.querySelector('.student-code').textContent = studentData.ma_hoc_sinh;
    newRow.querySelector('.student-name div.cell-edit').textContent = studentData.ho_ten;
    newRow.querySelector('.class-name div.cell-edit').textContent = studentData.ten_lop;
    newRow.querySelector('.violation-date div.cell-edit').textContent = date;
    newRow.querySelector('.violation-name').value = violationName;
    newRow.querySelector('.note div.cell-edit').textContent = notes;
    newRow.dataset.hocSinhId = studentData.id;
    setRowStatus(newRow, 'new', 'H‡ng m?i');
    primeInitialValues(newRow);
    handleDateValidation(newRow);
  }

  async function stopScanner() { if (html5QrCode && html5QrCode.isScanning) { try { await html5QrCode.stop(); } catch (e) {} } }

  async function onScanSuccess(decodedText) {
    await stopScanner();
    try {
      const response = await fetch(`/thidua/api/get-student-by-cccd?cccd=${decodedText}`);
      if (!response.ok) { const d = await response.json(); throw new Error(d.message || 'H·ªçc sinh khÙng t?n t?i.'); }
      const data = await response.json();
      scannedStudentData = data.student;
      document.getElementById('admin-student-name').textContent = scannedStudentData.ho_ten;
      document.getElementById('admin-student-class').textContent = scannedStudentData.ten_lop;
      document.getElementById('admin-violation-date').textContent = new Date().toLocaleDateString('vi-VN');
      document.getElementById('admin-violation-select-qr').value = lastSelectedViolationId;
      document.getElementById('admin-scan-info').classList.remove('hidden');
      document.getElementById('admin-scan-error').classList.add('hidden');
    } catch (error) {
      document.getElementById('admin-scan-info').classList.add('hidden');
      document.getElementById('admin-scan-error').classList.remove('hidden');
      document.getElementById('admin-scan-error').textContent = error.message;
    } finally { 
      closeModal('qr-scanner-modal'); 
      openModal('adminConfirmScanModal'); 
    }
  }

  window.closeScannerModal = async function() {
    await stopScanner();
    closeModal('qr-scanner-modal');
  }

  startScanBtn.addEventListener('click', () => {
    openModal('qr-scanner-modal');
    html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, () => {})
      .catch(() => { showToast('KhÙng th? kh?i d?ng camera. Vui lÚng c?p quy·ªÅn truy c?p camera.', 'error'); closeScannerModal(); });
  });

  document.getElementById('admin-cancel-scan-btn').addEventListener('click', () => { 
    closeModal('adminConfirmScanModal'); 
    startScanBtn.click(); // mo lai
  });

  document.getElementById('admin-confirm-violation-btn-qr').addEventListener('click', function () {
    if (!scannedStudentData) return;
    const select = document.getElementById('admin-violation-select-qr');
    const violationId = select.value;
    if (!violationId) { showToast('Vui lÚng ch·ªçn m?t l?i vi ph?m.', 'warning'); return; }
    const violationName = select.options[select.selectedIndex].text;
    const violationNotes = document.getElementById('admin-violation-notes-qr').value;
    lastSelectedViolationId = violationId;
    localStorage.setItem('lastSelectedViolationId', violationId);
    addDataToGrid(scannedStudentData, violationId, violationName, new Date().toLocaleDateString('vi-VN'), violationNotes);
    closeModal('adminConfirmScanModal');
    startScanBtn.click(); // mo lai
  });

  // Modal nhap tay
  const manualForm = document.getElementById('admin-manual-add-form');
  const manualHoTenInput = document.getElementById('admin-ho_ten');
  const manualTenLopInput = document.getElementById('admin-ten_lop');
  const manualLookupResult = document.getElementById('admin-lookup-result-container');
  const manualConfirmBtn = document.getElementById('admin-confirm-violation-btn-manual');

  const manualLookupStudent = async () => {
    const hoTen = manualHoTenInput.value, tenLop = manualTenLopInput.value;
    if (!hoTen.trim() || !tenLop.trim()) { manualLookupResult.innerHTML = ''; manualConfirmBtn.disabled = true; lookedUpStudentData = null; return; }
    manualLookupResult.innerHTML = `<div class="flex items-center text-slate-500 text-sm gap-2"><span class="inline-block w-3 h-3 border-2 border-[#224397] border-t-transparent rounded-full animate-spin"></span>ƒêang tÏm...</div>`;
    manualConfirmBtn.disabled = true; lookedUpStudentData = null;
    try {
      const response = await fetch(`/thidua/api/lookup-student?ho_ten=${encodeURIComponent(hoTen)}&ten_lop=${encodeURIComponent(tenLop)}`);
      const data = await response.json();
      if (data.success) {
        lookedUpStudentData = data.student;
        manualLookupResult.innerHTML = `<div class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#16a34a" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg><strong>${data.student.ho_ten}</strong> &ndash; ${data.student.ten_lop}</div>`;
        manualConfirmBtn.disabled = false;
      } else throw new Error(data.message || 'KhÙng tÏm th?y thÙng tin.');
    } catch (error) {
      manualLookupResult.innerHTML = `<div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dc2626" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>${error.message}</div>`;
      manualConfirmBtn.disabled = true;
    }
  };

  const debouncedManualLookup = debounce(manualLookupStudent, 400);
  manualHoTenInput.addEventListener('blur', () => { manualHoTenInput.value = normalizeStudentName(manualHoTenInput.value); });
  manualTenLopInput.addEventListener('blur', () => { manualTenLopInput.value = normalizeClassName(manualTenLopInput.value); });
  manualHoTenInput.addEventListener('input', debouncedManualLookup);
  manualTenLopInput.addEventListener('input', debouncedManualLookup);

  manualConfirmBtn.addEventListener('click', function () {
    if (!lookedUpStudentData) { showToast('Vui lÚng tÏm h·ªçc sinh h?p l?.', 'warning'); return; }
    const select = document.getElementById('admin-violation-select-manual');
    const violationId = select.value;
    if (!violationId) { showToast('Vui lÚng ch·ªçn m?t l?i vi ph?m.', 'warning'); return; }
    const violationName = select.options[select.selectedIndex].text;
    const violationNotes = document.getElementById('admin-violation-notes-manual').value;
    const violationDate = document.getElementById('admin-ngay_vi_pham').value;
    const dp = violationDate.split('-');
    const formattedDate = dp.length === 3 ? `${dp[2]}/${dp[1]}/${dp[0]}` : violationDate;
    addDataToGrid(lookedUpStudentData, violationId, violationName, formattedDate, violationNotes);
    manualForm.reset();
    document.getElementById('admin-ngay_vi_pham').value = "<?php echo date('Y-m-d'); ?>";
    manualLookupResult.innerHTML = '';
    manualConfirmBtn.disabled = true;
    lookedUpStudentData = null;
    closeModal('adminManualAddModal');
    showToast('ƒê√£ thÍm v‡o b?ng! Nh? luu d? ghi v‡o co s? d? li?u.', 'success');
  });
});
</script>



