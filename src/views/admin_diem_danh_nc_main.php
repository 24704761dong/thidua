<?php
// File: src/views/admin_diem_danh_nc_main.php
// PHIÊN BẢN HOÀN CHỈNH: Đã nâng cấp JavaScript để hiển thị màu sắc và chi tiết bỏ tiết.

$page_title = "XỬ LÝ ĐIỂM DANH";
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
body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px !important; height: 8px !important; }
body::-webkit-scrollbar-thumb { background: var(--primary) !important; border-radius: 4px; }
body::-webkit-scrollbar-track { background: transparent !important; }
body, html { scrollbar-width: thin !important; scrollbar-color: var(--primary) transparent !important; -ms-overflow-style: auto !important; }

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

<div class="w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
  <div class="bg-white rounded shadow-sm border border-[#224397]/25 p-3 mb-4 flex flex-wrap gap-2 items-center justify-between">
    <div class="flex items-center gap-2">
      <h1 class="h4 mb-0 font-semibold text-primary-600">
        XỬ LÝ ĐIỂM DANH - <?php echo htmlspecialchars($tuan_hoc['ten_tuan'] ?? ''); ?> - (<?= isset($tuan_hoc['ngay_bat_dau']) ? date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau'])) : '' ?> - <?= isset($tuan_hoc['ngay_ket_thuc']) ? date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc'])) : '' ?>)
      </h1>
    </div>
    <div class="flex flex-wrap gap-2 items-center">
      <a href="/thidua/admin/vi-pham?tuan_id=<?php echo $tuan_hoc['id']; ?>" class="btn-std">
        <span class="mr-1" aria-hidden="true" style="display:inline-flex;width:16px;height:16px;line-height:0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        </span>
        Quay lại
      </a>
      <a href="/thidua/admin/diem-danh-nang-cao/cai-dat" class="btn-std">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear-fill mr-1" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg> Cài đặt
      </a>
      <label for="excel_file_input" class="btn-std mb-0 cursor-pointer" style="margin-bottom:0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-arrow-up-fill mr-1" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.354 9.854a.5.5 0 0 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 8.707V12.5a.5.5 0 0 1-1 0V8.707z"/></svg> Nhập Import Excel
      </label>
      <span id="excel_file_name" class="text-sm text-slate-500 hidden truncate max-w-[200px]"></span>
      <input type="file" id="excel_file_input" class="hidden" accept=".xlsx,.xls">
    </div>
  </div>

  <div class="bg-white rounded shadow border border-[#224397]/25 mb-4 p-0 overflow-hidden">
    <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
      <h3 class="mb-0 text-[14px] font-bold text-[#224397] uppercase flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-ul mr-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
        Danh sách vắng đã Import (Nháp)
      </h3>
      <button id="finalizeBtn" class="btn-std btn-std-primary" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill fa-sm mr-1" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Hoàn tất điểm danh
      </button>
    </div>
    <div class="p-0">
      <div class="overflow-x-auto w-full table-scroll-wrapper" style="max-height: calc(100vh - 280px); overflow-y: auto;">
        <table id="violation-table" class="w-full text-left text-sm text-slate-600">
          <thead style="position: sticky; top: 0; z-index: 10;">
            <tr class="text-center">
              <th style="width:64px">STT</th>
              <th style="width:120px">Mã HS</th>
              <th class="text-left">Họ và tên</th>
              <th style="width:120px">Lớp</th>
              <th class="text-left">Nội dung vắng</th>
            </tr>
          </thead>
          <tbody id="attendanceTableBody">
            <tr><td colspan="5" class="text-center py-6">Vui lòng import file Excel để bắt đầu.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const fileInput    = document.getElementById('excel_file_input');
  const fileNameEl   = document.getElementById('excel_file_name');
  const tableBody    = document.getElementById('attendanceTableBody');
  const finalizeBtn  = document.getElementById('finalizeBtn');
  const tuanId       = <?php echo json_encode($tuan_hoc['id']); ?>;

  fileInput.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (file) {
      fileNameEl.textContent = file.name;
      fileNameEl.classList.remove('hidden');
      await handleFileImport(file);
    } else {
      fileNameEl.textContent = '';
      fileNameEl.classList.add('hidden');
    }
  });

  async function handleFileImport(file) {
      if (!file) return;

      tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-6"><div class="spinner-border" role="status"></div> Đang xử lý file...</td></tr>';
      finalizeBtn.disabled = true;

      const formData = new FormData();
      formData.append('attendance_file', file);
      formData.append('tuan_id', tuanId);

      try {
          const response = await fetch('/thidua/api/admin/diem-danh-nang-cao/xu-ly-import', {
              method: 'POST',
              body: formData
          });
          const result = await response.json();

          if (result.success) {
              renderTable(result.data || []);
              finalizeBtn.disabled = !(result.data && result.data.length > 0);
              if (result.errors && result.errors.length > 0) {
                  alert("Import thành công với một số cảnh báo:\n- " + result.errors.join("\n- "));
              }
          } else {
              throw new Error(result.message || 'Không thể xử lý file.');
          }
      } catch (error) {
          tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-red-600 py-6">Lỗi: ${error.message}</td></tr>`;
      }
  }

  function renderTable(data) {
    tableBody.innerHTML = '';
    if (!Array.isArray(data) || data.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-6">Không tìm thấy dữ liệu hợp lệ trong file.</td></tr>';
      return;
    }
    data.forEach((row, index) => {
      const vangs = Array.isArray(row.noi_dung_vang) ? row.noi_dung_vang : [];
      
      const chips = vangs.map(v => {
        let badgeClass = 'bg-slate-500 text-white';
        let badgeText = `${new Date(v.date).toLocaleDateString('vi-VN')} (${v.code})`;

        switch (v.type.toUpperCase()) {
            case 'P':
                badgeClass = 'bg-green-500 text-white';
                break;
            case 'K':
                badgeClass = 'bg-red-500 text-white';
                break;
            case 'BT':
                badgeClass = 'bg-yellow-500 text-slate-800';
                break;
        }
        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass} mr-1 mb-1" title="${escapeHtml(v.details)}">${escapeHtml(badgeText)}</span>`;
      }).join(' ');

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="text-center">${index + 1}</td>
        <td class="text-center">${row.ma_hs ? escapeHtml(row.ma_hs) : ''}</td>
        <td class="text-left">${escapeHtml(row.ho_ten || '')}</td>
        <td class="text-center">${escapeHtml(row.lop || '')}</td>
        <td class="text-left">${chips || '<span class="text-slate-400">-</span>'}</td>
      `;
      tableBody.appendChild(tr);
    });
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }

  finalizeBtn.addEventListener('click', async function() {
    if (!confirm('Bạn có chắc chắn muốn hoàn tất? Thao tác này sẽ tạo các vi phạm tương ứng và không thể hoàn tác.')) return;

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

    try {
      const response = await fetch('/thidua/api/admin/diem-danh-nang-cao/hoan-tat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
      });
      const result = await response.json();
      alert(result.message || 'Đã xử lý.');
      if (result.success) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-green-600">Đã hoàn tất điểm danh.</td></tr>';
        this.disabled = true;
      } else {
        this.disabled = false;
      }
    } catch (error) {
      alert('Lỗi: ' + error.message);
      this.disabled = false;
    } finally {
      this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill fa-sm mr-1" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Hoàn tất điểm danh';
    }
  });
});
</script>
