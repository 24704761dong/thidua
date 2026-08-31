<?php
$page_title = 'Sổ Nhật Kỳ Trực Tuyến';
require_once __DIR__ . '/partials/admin_header.php';

// Các biến giả định đã được nạp
$all_weeks = $all_weeks ?? [];
$selected_tuan_id = $selected_tuan_id ?? 0;
$data_for_view = $data_for_view ?? [];
?>

<style>
:root {
    --accent: #FAB723;
    --primary: #224397;
    --text-primary: #1d2d35;
    --bg-light: #f4f7f9;
    --ck-bg: #e8f6ff;   /* Chính khóa */
    --nk-bg: #f3e8ff;   /* Ngoại khóa */
    --tt-bg: #fff4e8;   /* Tăng tiết */
    --total-bg: #e8ffe8;/* Tổng cộng */
}

/* ===== Bảng ===== */
.nhatky-table {
    border-collapse: collapse !important;
    width: 100%;
    text-align: center;
}
.nhatky-table th, .nhatky-table td {
    border: 1px solid rgba(34,67,151,0.15) !important;
    text-align: center;
    vertical-align: middle;
    padding: 6px 4px;
    font-size: 13px;
}
.nhatky-table tbody td {
    color: #0f172a;
    font-weight: 600;
}
.nhatky-table thead th {
    background-color: rgba(34,67,151,0.05);
    color: #224397 !important;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11px;
}

/* ===== Cột đều nhau ===== */
.nhatky-table th, .nhatky-table td {
    width: 4.8%;
}
.nhatky-table th:first-child, .nhatky-table td:first-child {
    width: 3%;
}
.nhatky-table th:nth-child(2), .nhatky-table td:nth-child(2) {
    width: 7%;
    font-weight: bold;
    color: #224397;
}

/* ===== Màu nền phân nhóm ===== */
.nhatky-table thead tr:nth-child(2) th:nth-child(1),
.nhatky-table thead tr:nth-child(2) th:nth-child(2),
.nhatky-table thead tr:nth-child(2) th:nth-child(3),
.nhatky-table thead tr:nth-child(2) th:nth-child(4),
.nhatky-table tbody td:nth-child(3),
.nhatky-table tbody td:nth-child(4),
.nhatky-table tbody td:nth-child(5),
.nhatky-table tbody td:nth-child(6) {
    background-color: var(--ck-bg);
}
.nhatky-table thead tr:nth-child(2) th:nth-child(5),
.nhatky-table thead tr:nth-child(2) th:nth-child(6),
.nhatky-table thead tr:nth-child(2) th:nth-child(7),
.nhatky-table thead tr:nth-child(2) th:nth-child(8),
.nhatky-table tbody td:nth-child(7),
.nhatky-table tbody td:nth-child(8),
.nhatky-table tbody td:nth-child(9),
.nhatky-table tbody td:nth-child(10) {
    background-color: var(--nk-bg);
}
.nhatky-table thead tr:nth-child(2) th:nth-child(9),
.nhatky-table thead tr:nth-child(2) th:nth-child(10),
.nhatky-table thead tr:nth-child(2) th:nth-child(11),
.nhatky-table thead tr:nth-child(2) th:nth-child(12),
.nhatky-table tbody td:nth-child(11),
.nhatky-table tbody td:nth-child(12),
.nhatky-table tbody td:nth-child(13),
.nhatky-table tbody td:nth-child(14) {
    background-color: var(--tt-bg);
}
.nhatky-table thead tr:nth-child(2) th:nth-child(13),
.nhatky-table thead tr:nth-child(2) th:nth-child(14),
.nhatky-table thead tr:nth-child(2) th:nth-child(15),
.nhatky-table thead tr:nth-child(2) th:nth-child(16),
.nhatky-table tbody td:nth-child(15),
.nhatky-table tbody td:nth-child(16),
.nhatky-table tbody td:nth-child(17),
.nhatky-table tbody td:nth-child(18) {
    background-color: var(--total-bg);
    font-weight: 700;
}

/* ===== Hover ===== */
.nhatky-table tbody tr:hover td {
    background-color: rgba(34, 67, 151, 0.05);
}

</style>

<div class="w-full px-2 lg:px-6 mt-4">
    <div class="flex flex-col md:flex-row items-end justify-between gap-4 mb-4">
        <div>
            <h3 class="text-[18px] font-bold text-[#224397] flex items-center gap-2 mb-0 uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-book-half text-[#FAB723]" viewBox="0 0 16 16"><path d="M8.5 2.687c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>
                SỔ NHẬT KỲ TRỰC TUYẾN
            </h3>
        </div>
        
        <div class="flex items-center gap-1.5 flex-wrap">
            <a href="/thidua/admin/trung-tam-duyet" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg> 
                Quay lại Trung Tâm
            </a>
        </div>
    </div>

    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex flex-wrap justify-between items-center gap-4">
            <form id="weekFilterForm" action="/thidua/admin/duyet-so-nhat-ky" method="GET" class="flex items-center gap-2 m-0">
                <?php if (isset($_GET['iframe'])): ?>
                    <input type="hidden" name="iframe" value="1">
                <?php endif; ?>
                <label for="tuan_id_select" class="block text-[13px] font-bold text-[#224397] mb-0 whitespace-nowrap">Chọn Tuần:</label>
                <select name="tuan_id" id="tuan_id_select" class="block w-48 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]" onchange="this.form.submit()">
                    <?php foreach ($all_weeks as $week): ?>
                        <option value="<?= $week['id'] ?>" <?= $week['id'] == $selected_tuan_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($week['ten_tuan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div class="h-6 w-px bg-slate-300 mx-1 hidden md:block"></div>
                
                <select id="filter_khoi" class="block w-28 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                    <option value="">Tất cả Khối</option>
                    <option value="10">Khối 10</option>
                    <option value="11">Khối 11</option>
                    <option value="12">Khối 12</option>
                </select>

                <select id="filter_lop" class="block w-28 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                    <option value="">Tất cả Lớp</option>
                    <?php foreach ($all_classes ?? [] as $cls): ?>
                        <option value="<?= htmlspecialchars($cls['ten_lop']) ?>"><?= htmlspecialchars($cls['ten_lop']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="filter_trang_thai" class="block w-36 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                    <option value="">Mọi trạng thái</option>
                    <option value="da_gui">Chờ duyệt</option>
                    <option value="da_duyet">Đã duyệt</option>
                    <option value="nhap">Đang nháp</option>
                    <option value="tu_choi">Đã từ chối</option>
                    <option value="chua_nop">Chưa nộp</option>
                </select>
            </form>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="px-2 py-1 text-[11px] bg-white border border-[#224397] text-[#224397] rounded shadow-sm hover:bg-slate-50 transition-colors flex items-center gap-1 font-medium whitespace-nowrap" onclick="window.location.href='/thidua/admin/xem-minh-chung?iframe=1&tuan_id=<?= $selected_tuan_id ?>'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg> Xem Minh Chứng
                </button>
                <a href="/thidua/admin/xuat-minh-chung-zip?tuan_id=<?= $selected_tuan_id ?>" target="_blank" class="px-2 py-1 text-[11px] bg-[#0d6efd] text-white rounded shadow-sm hover:bg-[#0b5ed7] transition-colors flex items-center gap-1 font-medium whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-zip-fill" viewBox="0 0 16 16"><path d="M5.5 9.438V8.5h1v.938a1 1 0 0 0 .03.243l.4 1.598-.93.62-.93-.62.4-1.598a1 1 0 0 0 .03-.243z"/><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2zm-4 3.5v.938l-.4 1.599a1 1 0 0 0 .416 1.074l.93.62a1 1 0 0 0 1.108 0l.93-.62a1 1 0 0 0 .415-1.074l-.4-1.599V7h-3zm2 .938v.562h-1v-.562z"/></svg> Tải ZIP Minh Chứng
                </a>
                <a href="/thidua/admin/xuat-so-nhat-ky-zip?tuan_id=<?= $selected_tuan_id ?>" target="_blank" class="px-2 py-1 text-[11px] bg-[#198754] text-white rounded shadow-sm hover:bg-[#157347] transition-colors flex items-center gap-1 font-medium whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-zip" viewBox="0 0 16 16"><path d="M5 7.5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v.938l.4 1.599a1 1 0 0 1-.416 1.074l-.93.62a1 1 0 0 1-1.11 0l-.929-.62a1 1 0 0 1-.415-1.074L5 8.438zm2 0H6v.938a1 1 0 0 1-.03.243l-.4 1.598.93.62.93-.62-.4-1.598A1 1 0 0 1 7 8.438z"/><path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/></svg> Tải ZIP Excel
                </a>
            </div>
        </div>

        <div class="px-4 pb-4 pt-3">
            <div class="overflow-x-auto w-full border border-slate-200 rounded">
                <table class="nhatky-table">
                    <thead>
                        <tr>
                            <th rowspan="2">STT</th>
                            <th rowspan="2" class="text-left" style="padding-left: 10px;">Lớp</th>
                            <th colspan="4" class="group-separator border-r border-[#224397]/20">Chính Khóa (CK)</th>
                            <th colspan="4" class="group-separator border-r border-[#224397]/20">Ngoại Khóa (NK)</th>
                            <th colspan="4" class="group-separator border-r border-[#224397]/20">Tăng Tiết (TT)</th>
                            <th colspan="4" class="total-col group-separator border-r border-[#224397]/20">TỔNG CỘNG</th>
                            <th rowspan="2">Minh Chứng Khác</th>
                            <th rowspan="2">Trạng Thái</th>
                            <th rowspan="2">Chi Tiết</th>
                        </tr>
                        <tr>
                            <th>Tốt</th><th>Khá</th><th>TB</th><th class="group-separator border-r border-[#224397]/20">Yếu</th>
                            <th>Tốt</th><th>Khá</th><th>TB</th><th class="group-separator border-r border-[#224397]/20">Yếu</th>
                            <th>Tốt</th><th>Khá</th><th>TB</th><th class="group-separator border-r border-[#224397]/20">Yếu</th>
                            <th class="total-col">Tốt</th><th class="total-col">Khá</th><th class="total-col">TB</th><th class="total-col group-separator border-r border-[#224397]/20">Yếu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_for_view)): ?>
                            <tr><td colspan="21" class="text-center p-6 text-slate-500 italic">Không có dữ liệu cho tuần đã chọn.</td></tr>
                        <?php else: ?>
                            <?php 
                            function showNum($val) {
                                if ($val === null || $val === '' || (int)$val === 0) return '';
                                return '<span class="font-bold text-slate-800">' . htmlspecialchars($val) . '</span>';
                            }
                            ?>
                            <?php foreach ($data_for_view as $index => $item): ?>
                                <tr class="nhatky-row" data-khoi="<?= substr($item['ten_lop'], 0, 2) ?>" data-lop="<?= htmlspecialchars($item['ten_lop']) ?>" data-trangthai="<?= htmlspecialchars($item['trang_thai'] ?: 'chua_nop') ?>">
                                    <td class="stt-cell"><?= $index + 1 ?></td>
                                    <td class="text-left font-bold" style="padding-left: 10px;"><?= htmlspecialchars($item['ten_lop']) ?></td>

                                    <td><?= showNum($item['details']['sdb_ck']['so_tiet_tot'] ?? '') ?></td>
                                    <td><?= showNum($item['details']['sdb_ck']['so_tiet_kha'] ?? '') ?></td>
                                    <td><?= showNum($item['details']['sdb_ck']['so_tiet_tb'] ?? '') ?></td>
                                    <td class="group-separator border-r border-[#224397]/20"><?= showNum($item['details']['sdb_ck']['so_tiet_yeu'] ?? '') ?></td>

                                    <td><?= showNum($item['details']['sdb_nk']['so_tiet_tot'] ?? '') ?></td>
                                    <td><?= showNum($item['details']['sdb_nk']['so_tiet_kha'] ?? '') ?></td>
                                    <td><?= showNum($item['details']['sdb_nk']['so_tiet_tb'] ?? '') ?></td>
                                    <td class="group-separator border-r border-[#224397]/20"><?= showNum($item['details']['sdb_nk']['so_tiet_yeu'] ?? '') ?></td>

                                    <td><?= showNum($item['details']['sdb_tt']['so_tiet_tot'] ?? '') ?></td>
                                    <td><?= showNum($item['details']['sdb_tt']['so_tiet_kha'] ?? '') ?></td>
                                    <td><?= showNum($item['details']['sdb_tt']['so_tiet_tb'] ?? '') ?></td>
                                    <td class="group-separator border-r border-[#224397]/20"><?= showNum($item['details']['sdb_tt']['so_tiet_yeu'] ?? '') ?></td>

                                    <td class="total-col"><?= showNum($item['total_tot'] ?: '') ?></td>
                                    <td class="total-col"><?= showNum($item['total_kha'] ?: '') ?></td>
                                    <td class="total-col"><?= showNum($item['total_tb'] ?: '') ?></td>
                                    <td class="total-col group-separator border-r border-[#224397]/20"><?= showNum($item['total_yeu'] ?: '') ?></td>
                                    
                                    <td><?= ($item['has_other_proofs'] ?? 0) ? '<svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-check-circle-fill text-green-600 inline-block" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>' : '' ?></td>

                                    <td>
                                        <?php
                                            switch ($item['trang_thai']) {
                                                case 'da_gui': echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">Chờ duyệt</span>'; break;
                                                case 'da_duyet': echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 uppercase">Đã duyệt</span>'; break;
                                                case 'tu_choi': echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 uppercase">Đã từ chối</span>'; break;
                                                case 'nhap': echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800 uppercase">Đang nháp</span>'; break;
                                                default: echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 uppercase">Chưa nộp</span>'; break;
                                            }
                                        ?>
                                    </td>

                                    <td>
                                        <?php if ($item['nhat_ky_id']): ?>
                                            <a href="/thidua/admin/xem-chi-tiet-nhat-ky?id=<?= $item['nhat_ky_id'] ?>" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium inline-flex items-center shadow-sm" title="Xem chi tiết">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>   <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                                            </a>
                                        <?php endif; ?>
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

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterKhoi = document.getElementById('filter_khoi');
    const filterLop = document.getElementById('filter_lop');
    const filterTrangThai = document.getElementById('filter_trang_thai');
    
    function applyFilters() {
        const khoiVal = filterKhoi.value;
        const lopVal = filterLop.value;
        const ttVal = filterTrangThai.value;
        
        const rows = document.querySelectorAll('.nhatky-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const rowKhoi = row.getAttribute('data-khoi');
            const rowLop = row.getAttribute('data-lop');
            const rowTT = row.getAttribute('data-trangthai');
            
            let show = true;
            if (khoiVal && rowKhoi !== khoiVal) show = false;
            if (lopVal && rowLop !== lopVal) show = false;
            if (ttVal && rowTT !== ttVal) show = false;
            
            if (show) {
                row.style.display = '';
                visibleCount++;
                row.querySelector('.stt-cell').textContent = visibleCount;
            } else {
                row.style.display = 'none';
            }
        });
    }

    if(filterKhoi) filterKhoi.addEventListener('change', applyFilters);
    if(filterLop) filterLop.addEventListener('change', applyFilters);
    if(filterTrangThai) filterTrangThai.addEventListener('change', applyFilters);
});
</script>