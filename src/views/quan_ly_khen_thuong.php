<?php
$page_title = 'Quản Lý Khen Thưởng';
require_once __DIR__ . '/partials/admin_header.php';

// Giả định các biến đã được nạp
$khen_thuong_ca_nhan = $khen_thuong_ca_nhan ?? [];
$khen_thuong_tap_the = $khen_thuong_tap_the ?? [];
$danh_sach_hoc_sinh = $danh_sach_hoc_sinh ?? [];
$danh_sach_lop = $danh_sach_lop ?? [];
?>

<style>
  /* ----- Bảng màu và biến CSS ----- */
  :root {
    --primary-blue: #224397;
    --accent-gold: #FAB723;
    --bg-light: #f4f7f9;
    --card-border: rgba(34, 67, 151, 0.25);
  }

  body {
    background-color: var(--bg-light);
  }

  /* ----- Thiết kế bảng chuẩn DANH SÁCH HỌC SINH ----- */
  .standard-table {
    border: 1px solid var(--card-border);
    border-collapse: collapse;
    width: 100%;
  }
  .standard-table thead th {
    background-color: rgba(34, 67, 151, 0.08);
    color: var(--primary-blue);
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.82rem;
    text-align: center;
    padding: 0.75rem 1rem;
    border: 1px solid var(--card-border);
  }
  .standard-table td {
    padding: 0.75rem 1rem;
    border: 1px solid var(--card-border);
    vertical-align: middle;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
  }
  .standard-table tbody tr:hover {
    background-color: rgba(34, 67, 151, 0.04);
  }

  /* Ép hiện thanh cuộn */
  body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
  body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34, 67, 151, 0.3); border-radius: 4px; }
  body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }

  /* Custom Toast Notification */
  .custom-toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 12px 16px;
    color: #1e40af;
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 99999;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .custom-toast.show {
    transform: translateY(0);
    opacity: 1;
  }
  .custom-toast.error {
    background-color: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
  }

  /* Tabs Style */
  .custom-tab {
    color: #64748b;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
  }
  .custom-tab:hover { color: #224397; }
  .custom-tab.active {
    color: #224397;
    font-weight: 700;
    border-bottom-color: #224397;
  }
</style>

<div class="w-full px-2 lg:px-6">
  <div class="flex flex-col md:flex-row items-end justify-between gap-4 mb-4 mt-2">
    <div>
      <h3 class="text-2xl font-bold text-[#224397] flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trophy-fill" viewBox="0 0 16 16"><path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5q0 .807-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33 33 0 0 1 2.5.5m.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935m10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935"/></svg>
        Quản Lý Khen Thưởng
      </h3>
    </div>
    
    <!-- Nút thao tác theo chuẩn style DANH SÁCH HỌC SINH -->
    <div class="flex items-center gap-1.5 flex-wrap">
      <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="openAddRewardModal()">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>
        THÊM KHEN THƯỞNG
      </button>
      <button type="button" class="px-2 py-1 bg-white border border-red-300 rounded text-red-600 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" id="deleteAllBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
        XÓA TOÀN BỘ
      </button>

      <!-- Dropdown CSS Thuần -->
      <div class="relative group inline-block">
        <button type="button" class="px-2 py-1 bg-[#107c41] border border-[#107c41] rounded text-white hover:bg-[#185c37] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
          EXCEL
        </button>
        <div class="absolute right-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
          <div class="bg-white rounded-lg shadow-xl border border-slate-200 py-1 min-w-[160px]">
            <button type="button" onclick="openModal('importExcelModal')" class="w-full text-left px-4 py-2 text-[13px] text-slate-700 hover:bg-slate-50 hover:text-[#224397] transition-colors">Import Dữ Liệu</button>
            <a href="/thidua/tai-mau-khen-thuong" class="block w-full text-left px-4 py-2 text-[13px] text-slate-700 hover:bg-slate-50 hover:text-[#224397] transition-colors">Tải File Mẫu</a>
            <div class="h-px bg-slate-200 my-1"></div>
            <a href="/thidua/admin/khen-thuong?action=export_excel" class="block w-full text-left px-4 py-2 text-[13px] text-[#107c41] font-medium hover:bg-green-50 transition-colors">Xuất Danh Sách</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-[#224397]/25 mb-6 overflow-hidden">
    <!-- TABS -->
    <!-- TABS & BỘ LỌC -->
    <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-[#224397]/25 px-4 pt-2 gap-4 bg-slate-50/50">
      <div class="flex gap-6">
        <button class="custom-tab active px-2 py-3 text-[14px] font-medium uppercase tracking-wide focus:outline-none flex items-center gap-2" onclick="switchTab('ca-nhan')" id="tab-btn-ca-nhan">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-badge" viewBox="0 0 16 16"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492z"/></svg>
          Cá Nhân
        </button>
        <button class="custom-tab px-2 py-3 text-[14px] font-medium uppercase tracking-wide focus:outline-none flex items-center gap-2" onclick="switchTab('tap-the')" id="tab-btn-tap-the">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg>
          Tập Thể
        </button>
      </div>
      
      <!-- BỘ LỌC -->
      <div class="flex items-center gap-2 pb-2">
        <select id="filterKhoi" class="text-[12px] px-2 py-1.5 border border-slate-300 rounded focus:border-[#224397] outline-none" onchange="applyFilters()">
            <option value="">-- Tất cả Khối --</option>
            <option value="10">Khối 10</option>
            <option value="11">Khối 11</option>
            <option value="12">Khối 12</option>
        </select>
        <input type="text" id="filterLop" placeholder="Tìm lớp (VD: 10A1)" class="text-[12px] px-2 py-1.5 border border-slate-300 rounded focus:border-[#224397] outline-none w-32" oninput="applyFilters()">
        <input type="text" id="filterTen" placeholder="Lọc theo Tên khen thưởng..." class="text-[12px] px-2 py-1.5 border border-slate-300 rounded focus:border-[#224397] outline-none w-48" oninput="applyFilters()">
      </div>
    </div>

    <div class="p-0">
      <!-- PANE CÁ NHÂN -->
      <div id="pane-ca-nhan" class="block w-full overflow-x-auto">
        <table class="standard-table">
          <thead>
            <tr>
              <th class="w-12">STT</th>
              <th class="text-left">Họ và Tên</th>
              <th class="text-left w-24">Lớp</th>
              <th class="text-center w-32">Ngày KT</th>
              <th class="text-left">Tên Khen Thưởng</th>
              <th class="text-left w-32">Số QĐ</th>
              <th class="text-left w-32">Cấp KT</th>
              <th class="text-left">Ghi chú</th>
              <th class="text-center w-24">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($khen_thuong_ca_nhan)): ?>
              <tr><td colspan="9" class="text-center py-6 text-slate-500 font-medium">Không có dữ liệu khen thưởng cá nhân.</td></tr>
            <?php else: ?>
              <?php foreach($khen_thuong_ca_nhan as $index => $kt): ?>
              <tr data-item="<?php echo htmlspecialchars(json_encode(array_merge($kt, ['loai' => 'ca_nhan'])), ENT_QUOTES, 'UTF-8'); ?>">
                <td class="text-center text-slate-500 font-medium"><?php echo $index + 1; ?></td>
                <td class="text-left font-bold text-[#224397]"><?php echo htmlspecialchars($kt['ho_ten']); ?></td>
                <td class="text-left font-medium text-slate-600"><?php echo htmlspecialchars($kt['ten_lop']); ?></td>
                <td class="text-center text-slate-600"><?php echo date('d/m/Y', strtotime($kt['ngay_khen_thuong'])); ?></td>
                <td class="text-left text-slate-700"><?php echo htmlspecialchars($kt['ten_khen_thuong']); ?></td>
                <td class="text-left text-slate-600"><?php echo htmlspecialchars($kt['so_quyet_dinh']); ?></td>
                <td class="text-left text-slate-600"><?php echo htmlspecialchars($kt['cap_khen_thuong']); ?></td>
                <td class="text-left text-slate-500 italic"><?php echo htmlspecialchars($kt['ghi_chu']); ?></td>
                <td class="text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button onclick="openEditModal(this.closest('tr').dataset.item)" class="px-2 py-1 text-xs font-medium bg-white text-[#224397] border border-[#224397]/20 hover:bg-blue-50 rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 flex items-center gap-1" title="Sửa">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg>
                    </button>
                    <button onclick="deleteReward(<?php echo $kt['id']; ?>)" class="px-2 py-1 text-xs font-medium bg-white text-red-600 border border-red-200 hover:bg-red-50 rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 flex items-center gap-1" title="Xóa">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- PANE TẬP THỂ -->
      <div id="pane-tap-the" class="hidden w-full overflow-x-auto">
        <table class="standard-table">
          <thead>
            <tr>
              <th class="w-12">STT</th>
              <th class="text-left">Tên Tập Thể / Lớp</th>
              <th class="text-center w-32">Ngày KT</th>
              <th class="text-left">Tên Khen Thưởng</th>
              <th class="text-left w-32">Số QĐ</th>
              <th class="text-left w-32">Cấp KT</th>
              <th class="text-left">Ghi chú</th>
              <th class="text-center w-24">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($khen_thuong_tap_the)): ?>
              <tr><td colspan="8" class="text-center py-6 text-slate-500 font-medium">Không có dữ liệu khen thưởng tập thể.</td></tr>
            <?php else: ?>
              <?php foreach($khen_thuong_tap_the as $index => $kt): ?>
              <tr data-item="<?php echo htmlspecialchars(json_encode(array_merge($kt, ['loai' => 'tap_the'])), ENT_QUOTES, 'UTF-8'); ?>">
                <td class="text-center text-slate-500 font-medium"><?php echo $index + 1; ?></td>
                <td class="text-left font-bold text-[#224397]"><?php echo htmlspecialchars($kt['ten_lop'] ?? $kt['ten_tap_the']); ?></td>
                <td class="text-center text-slate-600"><?php echo date('d/m/Y', strtotime($kt['ngay_khen_thuong'])); ?></td>
                <td class="text-left text-slate-700"><?php echo htmlspecialchars($kt['ten_khen_thuong']); ?></td>
                <td class="text-left text-slate-600"><?php echo htmlspecialchars($kt['so_quyet_dinh']); ?></td>
                <td class="text-left text-slate-600"><?php echo htmlspecialchars($kt['cap_khen_thuong']); ?></td>
                <td class="text-left text-slate-500 italic"><?php echo htmlspecialchars($kt['ghi_chu']); ?></td>
                <td class="text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button onclick="openEditModal(this.closest('tr').dataset.item)" class="px-2 py-1 text-xs font-medium bg-white text-[#224397] border border-[#224397]/20 hover:bg-blue-50 rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 flex items-center gap-1" title="Sửa">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg>
                    </button>
                    <button onclick="deleteReward(<?php echo $kt['id']; ?>)" class="px-2 py-1 text-xs font-medium bg-white text-red-600 border border-red-200 hover:bg-red-50 rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 flex items-center gap-1" title="Xóa">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ================================= MODALS ================================= -->

<!-- IMPORT EXCEL MODAL -->
<div id="importExcelModal" class="hidden fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('importExcelModal')">
  <div class="bg-white rounded-xl shadow-2xl w-[500px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
      <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill text-[#107c41]" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
        Import Khen Thưởng
      </h5>
      <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 p-1.5 rounded-lg transition" onclick="closeModal('importExcelModal')">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
      </button>
    </div>
    <form action="/thidua/admin/khen-thuong?action=import" method="POST" enctype="multipart/form-data">
      <div class="px-6 py-5 space-y-4">
        <p class="text-[13px] text-slate-600">Vui lòng chọn file Excel đúng định dạng đã tải về từ file mẫu.</p>
        <div>
          <label class="block text-[13px] font-semibold text-slate-700 mb-1">Chọn file <span class="text-red-500">*</span></label>
          <input type="file" name="excelFile" accept=".xlsx, .xls" required class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>
        <div class="p-4 rounded border bg-blue-50 text-blue-800 border-blue-200 text-[12px] flex items-start gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill shrink-0 mt-0.5" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
          <span>Hệ thống sẽ đọc cả hai sheet <strong>MauCaNhan</strong> và <strong>MauTapThe</strong> trong file của bạn.</span>
        </div>
      </div>
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 transition" onclick="closeModal('importExcelModal')">Hủy</button>
        <button type="submit" class="px-4 py-2 text-[13px] font-bold text-white bg-[#224397] border border-[#224397] rounded shadow-sm hover:bg-blue-800 transition flex items-center justify-center gap-1.5">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-upload" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/><path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708z"/></svg>
          Tải Lên
        </button>
      </div>
    </form>
  </div>
</div>

<!-- THÊM/SỬA KHEN THƯỞNG MODAL -->
<div id="addRewardModal" class="hidden fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('addRewardModal')">
  <div class="bg-white rounded-xl shadow-2xl w-[700px] max-w-[95%] flex flex-col overflow-hidden border border-slate-300 transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
      <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2" id="modalTitle">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-plus text-[#FAB723]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 5.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 .5-.5"/>  <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>  <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/></svg>
        <span id="modalTitleText">Thêm Khen Thưởng</span>
      </h5>
      <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 p-1.5 rounded-lg transition" onclick="closeModal('addRewardModal')">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
      </button>
    </div>
    
    <form id="rewardForm" method="POST" action="/thidua/admin/khen-thuong?action=add">
      <input type="hidden" name="id" id="form_id">
      <input type="hidden" name="action" id="form_action" value="add">
      
      <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
        <div>
          <label class="block text-[13px] font-semibold text-slate-700 mb-1">Loại Khen Thưởng <span class="text-red-500">*</span></label>
          <select name="loai" id="form_loai" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors" required onchange="toggleFormType()">
            <option value="ca_nhan">Cá Nhân</option>
            <option value="tap_the">Tập Thể</option>
          </select>
        </div>

        <div id="section_ca_nhan">
          <label class="block text-[13px] font-semibold text-slate-700 mb-1">Học sinh <span class="text-red-500">*</span></label>
          <select name="hoc_sinh_id" id="form_hoc_sinh_id" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors">
            <option value="">-- Chọn học sinh --</option>
            <?php foreach($danh_sach_hoc_sinh as $hs): ?>
              <option value="<?php echo $hs['id']; ?>"><?php echo htmlspecialchars($hs['ten_lop'] . ' - ' . $hs['ho_dem'] . ' ' . $hs['ten']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div id="section_tap_the" class="hidden space-y-4">
          <div>
            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Tập thể Lớp</label>
            <select name="lop_hoc_id" id="form_lop_hoc_id" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors">
              <option value="">-- Chọn lớp hoặc điền tên tập thể khác --</option>
              <?php foreach($danh_sach_lop as $lop): ?>
                <option value="<?php echo $lop['id']; ?>"><?php echo htmlspecialchars($lop['ten_lop']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Hoặc Tên tập thể khác</label>
            <input type="text" name="ten_tap_the" id="form_ten_tap_the" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors" placeholder="VD: Đội tuyển Học sinh Giỏi Toán">
          </div>
        </div>

        <hr class="border-slate-200">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Tên Khen thưởng <span class="text-red-500">*</span></label>
            <input type="text" name="ten_khen_thuong" id="form_ten_khen_thuong" required class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors" placeholder="Nhập tên khen thưởng">
          </div>
          <div>
            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Ngày Khen thưởng <span class="text-red-500">*</span></label>
            <input type="date" name="ngay_khen_thuong" id="form_ngay_khen_thuong" required value="<?php echo date('Y-m-d'); ?>" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Số Quyết định</label>
            <input type="text" name="so_quyet_dinh" id="form_so_quyet_dinh" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors" placeholder="Số QĐ">
          </div>
          <div>
            <label class="block text-[13px] font-semibold text-slate-700 mb-1">Cấp Khen thưởng</label>
            <input type="text" name="cap_khen_thuong" id="form_cap_khen_thuong" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors" placeholder="VD: Cấp Trường, Cấp Tỉnh">
          </div>
        </div>

        <div>
          <label class="block text-[13px] font-semibold text-slate-700 mb-1">Ghi chú</label>
          <textarea name="ghi_chu" id="form_ghi_chu" class="w-full text-[13px] px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-[#224397] transition-colors resize-y" rows="3" placeholder="Nhập ghi chú..."></textarea>
        </div>
      </div>
      
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 transition" onclick="closeModal('addRewardModal')">Hủy</button>
        <button type="submit" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded shadow-sm hover:bg-[#e5a61d] transition flex items-center justify-center gap-1.5" id="btnSubmitForm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16"><path d="M11 2H9v3h2z"/><path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/></svg> 
          Lưu Lại
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// ----- Toast Notification -----
function showSessionNotification(message, isError = false) {
  const existingToast = document.getElementById('system-session-toast');
  if (existingToast) existingToast.remove();

  const toast = document.createElement('div');
  toast.id = 'system-session-toast';
  toast.className = 'custom-toast' + (isError ? ' error' : '');
  
  let icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle shrink-0" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg>';
  if (isError) {
    icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill shrink-0" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>';
  }

  toast.innerHTML = icon + '<span>' + message + '</span>';
  document.body.appendChild(toast);

  setTimeout(() => toast.classList.add('show'), 10);
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ----- Reward Modal Logic (dùng window.openModal/closeModal từ footer) -----
function openAddRewardModal() {
  document.getElementById('rewardForm').reset();
  document.getElementById('form_id').value = '';
  document.getElementById('form_action').value = 'add';
  document.getElementById('modalTitleText').innerText = 'Thêm Khen Thưởng';
  document.getElementById('form_loai').disabled = false;
  toggleFormType();
  openModal('addRewardModal');
}

function openEditModal(dataString) {
  const data = JSON.parse(dataString);
  document.getElementById('form_id').value = data.id;
  document.getElementById('form_action').value = 'api_edit'; // Using the fetch endpoint logic
  document.getElementById('modalTitleText').innerText = 'Chỉnh sửa Khen Thưởng';
  
  const loaiEl = document.getElementById('form_loai');
  loaiEl.value = data.loai;
  loaiEl.disabled = true; // Cannot change type when editing
  
  toggleFormType();
  
  if (data.loai === 'ca_nhan') {
    document.getElementById('form_hoc_sinh_id').value = data.hoc_sinh_id || '';
  } else {
    document.getElementById('form_lop_hoc_id').value = data.lop_hoc_id || '';
    document.getElementById('form_ten_tap_the').value = data.ten_tap_the || '';
  }
  
  document.getElementById('form_ten_khen_thuong').value = data.ten_khen_thuong || '';
  document.getElementById('form_ngay_khen_thuong').value = data.ngay_khen_thuong || '';
  document.getElementById('form_so_quyet_dinh').value = data.so_quyet_dinh || '';
  document.getElementById('form_cap_khen_thuong').value = data.cap_khen_thuong || '';
  document.getElementById('form_ghi_chu').value = data.ghi_chu || '';
  
  openModal('addRewardModal');
}

function toggleFormType() {
  const loai = document.getElementById('form_loai').value;
  const secCaNhan = document.getElementById('section_ca_nhan');
  const secTapThe = document.getElementById('section_tap_the');
  
  if (loai === 'ca_nhan') {
    secCaNhan.classList.remove('hidden');
    secTapThe.classList.add('hidden');
  } else {
    secCaNhan.classList.add('hidden');
    secTapThe.classList.remove('hidden');
  }
}

// ----- Tabs Management -----
function switchTab(tab) {
  document.getElementById('tab-btn-ca-nhan').classList.remove('active');
  document.getElementById('tab-btn-tap-the').classList.remove('active');
  document.getElementById('pane-ca-nhan').classList.add('hidden');
  document.getElementById('pane-tap-the').classList.add('hidden');

  document.getElementById('tab-btn-' + tab).classList.add('active');
  document.getElementById('pane-' + tab).classList.remove('hidden');
}

// ----- Form Submit via Fetch for Add/Edit -----
document.getElementById('rewardForm').addEventListener('submit', function(ev) {
  ev.preventDefault();
  const action = document.getElementById('form_action').value;
  const submitBtn = document.getElementById('btnSubmitForm');
  submitBtn.disabled = true;
  
  const payload = {
    id: document.getElementById('form_id').value,
    loai: document.getElementById('form_loai').value,
    hoc_sinh_id: document.getElementById('form_hoc_sinh_id').value || null,
    lop_hoc_id: document.getElementById('form_lop_hoc_id').value || null,
    ten_tap_the: document.getElementById('form_ten_tap_the').value,
    ten_khen_thuong: document.getElementById('form_ten_khen_thuong').value,
    ngay_khen_thuong: document.getElementById('form_ngay_khen_thuong').value,
    so_quyet_dinh: document.getElementById('form_so_quyet_dinh').value,
    cap_khen_thuong: document.getElementById('form_cap_khen_thuong').value,
    ghi_chu: document.getElementById('form_ghi_chu').value
  };

  const endpoint = action === 'api_edit' ? '/thidua/admin/khen-thuong?action=api_edit' : '/thidua/admin/khen-thuong?action=api_add';

  fetch(endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showSessionNotification(data.message);
      setTimeout(() => location.reload(), 1000);
    } else {
      showSessionNotification(data.message || 'Có lỗi xảy ra', true);
      submitBtn.disabled = false;
    }
  })
  .catch(err => {
    showSessionNotification('Lỗi kết nối máy chủ', true);
    submitBtn.disabled = false;
  });
});

// ----- Delete Logic với AppSwal -----
function deleteReward(id) {
  AppSwal.fire({
    title: 'Cảnh Báo Xóa!',
    text: 'Bạn có chắc chắn muốn xóa mục khen thưởng này?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận Xóa',
    cancelButtonText: 'Hủy',
    customClass: {
      popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
      title: 'text-red-600 font-bold text-xl mt-0',
      htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
      actions: 'flex justify-center gap-3 w-full mt-6',
      confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 hover:scale-110 transition-all duration-300 outline-none',
      cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none',
      icon: 'scale-[0.85] my-2'
    },
    buttonsStyling: false
  }).then((result) => {
    if(result.isConfirmed) {
      fetch('/thidua/admin/khen-thuong?action=api_delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showSessionNotification(data.message);
          setTimeout(() => location.reload(), 1000);
        } else {
          showSessionNotification(data.message, true);
        }
      });
    }
  });
}

const deleteAllBtn = document.getElementById('deleteAllBtn');
if (deleteAllBtn) {
  deleteAllBtn.addEventListener('click', function() {
    AppSwal.fire({
      title: 'Xóa Toàn Bộ?',
      text: 'Bạn có chắc chắn muốn xóa toàn bộ khen thưởng? Hành động này không thể hoàn tác!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Xóa Tất Cả',
      cancelButtonText: 'Hủy',
      customClass: {
        popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
        title: 'text-red-600 font-bold text-xl mt-0',
        htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
        actions: 'flex justify-center gap-3 w-full mt-6',
        confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 hover:scale-110 transition-all duration-300 outline-none',
        cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none',
        icon: 'scale-[0.85] my-2'
      },
      buttonsStyling: false
    }).then((result) => {
      if(result.isConfirmed) {
        fetch('/thidua/admin/khen-thuong?action=api_delete_all', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showSessionNotification(data.message);
            setTimeout(() => location.reload(), 1000);
          } else {
            showSessionNotification(data.message, true);
          }
        });
      }
    });
  });
}

// ----- Filtering Logic -----
function applyFilters() {
  const khoi = document.getElementById('filterKhoi').value.toLowerCase().trim();
  const lop = document.getElementById('filterLop').value.toLowerCase().trim();
  const ten = document.getElementById('filterTen').value.toLowerCase().trim();

  // Cá nhân table rows
  const caNhanRows = document.querySelectorAll('#pane-ca-nhan tbody tr[data-item]');
  let visibleCaNhan = 0;
  caNhanRows.forEach(row => {
    const lopText = row.children[2].innerText.toLowerCase();
    const tenText = row.children[4].innerText.toLowerCase();
    
    let match = true;
    if (khoi && !lopText.startsWith(khoi)) match = false;
    if (lop && !lopText.includes(lop)) match = false;
    if (ten && !tenText.includes(ten)) match = false;
    
    row.style.display = match ? '' : 'none';
    if (match) visibleCaNhan++;
  });

  // Tập thể table rows
  const tapTheRows = document.querySelectorAll('#pane-tap-the tbody tr[data-item]');
  let visibleTapThe = 0;
  tapTheRows.forEach(row => {
    const lopText = row.children[1].innerText.toLowerCase();
    const tenText = row.children[3].innerText.toLowerCase();
    
    let match = true;
    if (khoi && !lopText.startsWith(khoi)) match = false;
    if (lop && !lopText.includes(lop)) match = false;
    if (ten && !tenText.includes(ten)) match = false;
    
    row.style.display = match ? '' : 'none';
    if (match) visibleTapThe++;
  });
}
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
