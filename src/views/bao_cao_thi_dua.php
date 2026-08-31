<?php
$page_title = 'Bảng Điểm Thi Đua Hàng Tuần';
require_once __DIR__ . '/partials/admin_header.php';

$tuan_hoc = $tuan_hoc ?? ['ten_tuan' => 'Chưa chọn tuần'];
$all_weeks = $all_weeks ?? [];
$tuan_id = $tuan_id ?? 0;
$report_data = $report_data ?? [];

function get_grade_class($class_name) {
    if (strpos($class_name, '12') === 0) return '12';
    if (strpos($class_name, '11') === 0) return '11';
    if (strpos($class_name, '10') === 0) return '10';
    return 'Khác'; 
}

function print_if_not_zero($value, $decimals = 1) {
    if ($value === null || $value === '') {
        echo '';
        return;
    }
    $float_val = (float)$value;
    if (abs($float_val) > 0.0001) {
        if ($decimals > 0) {
            echo round($float_val, $decimals);
        } else {
            echo (int)$float_val;
        }
    } else {
        echo '';
    }
}

// Lấy tổng số lượng lớp để xác định hạng cuối
$total_lops = count($report_data);
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track { background: transparent; }
    
    /* Table chuẩn như trang danh sách học sinh */
    .log-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .log-table thead th { 
        background: #f8fafc; color: #1e293b; font-weight: 700; 
        text-transform: uppercase; font-size: 0.75rem; 
        padding: 0.75rem 0.5rem; border: 1px solid #cbd5e1; 
        white-space: nowrap; text-align: center; 
    }
    .log-table td { 
        padding: 0.6rem 0.5rem; border: 1px solid #cbd5e1; 
        vertical-align: middle; color: #334155; text-align: center; 
    }
    .log-table tbody tr:hover { background: #f1f5f9; }

    /* Fix các cột điểm số có độ rộng bằng nhau */
    .col-score { width: 8%; min-width: 70px; }

    /* Form UI chuẩn nhỏ gn */
    .form-select-sm, .form-input-sm {
        display: block; width: 100%; border-radius: 6px;
        border: 1px solid #cbd5e1; padding: 0.25rem 0.6rem;
        font-size: 13px; color: #1e293b; background: #fff;
        height: 32px; 
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-select-sm:focus, .form-input-sm:focus { outline: none; border-color: #224397; box-shadow: 0 0 0 3px rgba(34,67,151,0.1); }
    .form-select-sm:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
    
    .btn-action-sm { display: inline-flex; align-items: center; gap: 0.3rem; height: 32px; padding: 0 0.7rem; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid rgba(34,67,151,0.25); background: #fff; color: #224397; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm:hover { background: #FAB723; color: #fff; border-color: #FAB723; }
    
    .btn-action-sm-red { display: inline-flex; align-items: center; gap: 0.3rem; height: 32px; padding: 0 0.7rem; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid rgba(220,38,38,0.25); background: #fff; color: #dc2626; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm-red:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
    
    .drill-down, .drill-down-kxtd { cursor: pointer; transition: background 0.2s; }
    .drill-down:hover, .drill-down-kxtd:hover { background-color: rgba(250,183,35,0.2) !important; font-weight: 700; color: #224397; }
    
    .modal-content-box { transition: transform 0.3s ease, opacity 0.3s ease; }
    
    /* Thứ hạng nổi bật */
    .rank-1 { color: #d97706; font-weight: 900; font-size: 1.1rem; }
    .rank-last { color: #dc2626; font-weight: 900; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 pb-6 mt-4">
    <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
        <h1 class="text-xl mb-0 font-semibold text-[#224397] flex items-center gap-2 uppercase">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-bar-chart-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2z"/></svg>
            <?php echo 'Bảng Điểm Thi Đua Hàng Tuần'; ?>
        </h1>
    </div>

    <!-- KHU VỰC ĐIỀU KHIỂN ĐỒNG BỘ -->
    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/20 mb-6">
        
        <!-- Toolbar: Trái (Bộ lọc) - Phải (Actions) -->
        <div class="px-5 py-3 border-b border-[#224397]/12 bg-[#f8fafc] flex flex-wrap items-end justify-between gap-4">
            
            <!-- Left: Filters -->
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" action="" class="flex items-end" id="form-chon-tuan" onsubmit="event.preventDefault();">
                    <input type="hidden" name="iframe" value="1">
                    <div>
                        <label class="block text-[12px] font-bold text-[#224397] mb-1">Chọn Tuần</label>
                        <select name="tuan_id" id="tuan_id_select" class="form-select-sm min-w-[140px]" onchange="loadTuanData(this.value)">
                            <option value="">-- Chọn tuần --</option>
                            <?php foreach ($all_weeks as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $t['id'] == $tuan_id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['ten_tuan']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <div id="filters-container" class="flex flex-wrap items-end gap-3">
                <?php if (!empty($report_data)): ?>
                <div>
                    <label class="block text-[12px] font-bold text-[#224397] mb-1">Khối</label>
                    <select id="filter-khoi" class="form-select-sm min-w-[100px]">
                        <option value="">Tất cả</option>
                        <option value="10">Khối 10</option>
                        <option value="11">Khối 11</option>
                        <option value="12">Khối 12</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-[#224397] mb-1">Lớp</label>
                    <select id="filter-lop" class="form-select-sm min-w-[100px]" disabled>
                        <option value="">Tất cả</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-[#224397] mb-1">Tìm Nhanh</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                        <input id="quick-search" type="text" class="form-input-sm w-40" style="padding-left: 30px;" placeholder="Tên lớp...">
                    </div>
                </div>
                <?php endif; ?>
                </div>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2" id="actions-container">
                <?php if ($tuan_id): ?>
                <div class="relative inline-block text-left group z-50">
                    <button type="button" class="btn-action-sm shadow-sm whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel" viewBox="0 0 16 16"><path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.539a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/></svg> 
                        Nhập/Xuất
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                    </button>
                    <ul class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1">
                        <li>
                            <a class="flex items-center gap-2 px-3 py-2 text-[13px] font-medium text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="/thidua/xuat-bao-cao-thi-dua?tuan_id=<?php echo htmlspecialchars($tuan_id); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="#16a34a" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.539a.5.5 0 1 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                                Xuất ra Excel
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center gap-2 px-3 py-2 text-[13px] font-medium text-slate-700 hover:bg-red-50 hover:text-red-700" target="_blank" href="/thidua/xuat-bao-cao-thi-dua-pdf?tuan_id=<?php echo htmlspecialchars($tuan_id); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="#dc2626" class="bi bi-file-earmark-pdf-fill" viewBox="0 0 16 16">
                                  <path d="M5.523 12.424c.14-.082.293-.162.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572q-.131.054-.239.052c-.237 0-.53-.149-.53-.446 0-.27.13-.53.42-.716.208-.133.45-.213.725-.213.19 0 .39.04.55.123zM6.86 11.168c.15-.31.29-.65.41-.98.24-.69.45-1.42.6-2.11.15-.71.26-1.4.3-1.99.04-.55.05-1.04.05-1.47 0-.39-.02-.73-.08-1.01a1 1 0 0 0-.25-.56c-.16-.17-.38-.26-.64-.26-.37 0-.69.17-.92.49-.22.31-.34.71-.34 1.15 0 .43.06.87.16 1.3.11.45.26.9.45 1.34.22.5.47.98.74 1.45a27 27 0 0 0 .97 1.57c-.24.4-.5.8-.78 1.18-.32.44-.66.86-1.02 1.25-.33.37-.67.72-1.02 1.05zM10.15 8.169c.17.29.35.56.54.81.18.25.37.48.56.68.21.22.42.42.63.59.2.16.4.29.59.39.18.09.35.15.5.15.2 0 .36-.08.47-.23.1-.14.15-.32.15-.54 0-.31-.1-.63-.3-.92-.2-.29-.48-.56-.8-.79-.31-.22-.67-.4-1.05-.53-.36-.12-.74-.2-1.12-.22a7.7 7.7 0 0 0-.17-.39zm-3.13-5.26c-.1.21-.18.44-.24.69-.06.24-.09.49-.09.76 0 .26.03.52.09.78.06.26.15.52.26.78.12.27.26.54.43.81.16-.27.32-.55.45-.83.14-.28.25-.56.33-.84.08-.28.13-.56.15-.83.02-.27.02-.54 0-.81a3 3 0 0 0-.1-.77c-.05-.24-.13-.46-.24-.66-.1-.2-.23-.37-.39-.51-.15-.14-.33-.23-.53-.26z"/>
                                  <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M4.776 12.046c-.52.28-.9.65-1.14 1.1-.23.44-.26.96-.09 1.55.15.56.46.99.9 1.27.42.27.91.38 1.44.33.51-.05.99-.25 1.42-.6.41-.35.79-.81 1.12-1.35.32-.53.6-1.13.83-1.78.23-.63.41-1.3.54-2 .13-.68.22-1.37.26-2.04.04-.66.04-1.3-.01-1.92-.05-.6-.15-1.17-.31-1.68-.15-.51-.37-.96-.65-1.32-.27-.35-.61-.62-1.02-.79-.39-.16-.83-.22-1.3-.17-.46.04-.88.2-1.24.47-.35.26-.64.6-.85 1.01-.2.4-.33.87-.39 1.39-.06.51-.04 1.06.05 1.62.09.56.24 1.13.46 1.7.2.55.47 1.1.78 1.63z"/>
                                </svg>
                                Xuất ra PDF
                            </a>
                        </li>
                    </ul>
                </div>
                
                <a href="/thidua/print/bao-cao-thi-dua?tuan_id=<?php echo htmlspecialchars($tuan_id); ?>" target="_blank" class="btn-action-sm-red">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16"><path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/><path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/></svg> 
                    In Báo Cáo
                </a>
                
                <button type="button" class="btn-action-sm" onclick="openPublicWeeksModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-broadcast" viewBox="0 0 16 16"><path d="M3.05 3.05a7 7 0 0 0 0 9.9.5.5 0 0 1-.707.707 8 8 0 0 1 0-11.314.5.5 0 0 1 .707.707m2.122 2.122a4 4 0 0 0 0 5.656.5.5 0 1 1-.708.708 5 5 0 0 1 0-7.072.5.5 0 0 1 .708.708m5.656-.708a.5.5 0 0 1 .708 0 5 5 0 0 1 0 7.072.5.5 0 1 1-.708-.708 4 4 0 0 0 0-5.656.5.5 0 0 1 0-.708m2.122-2.12a.5.5 0 0 1 .707 0 8 8 0 0 1 0 11.313.5.5 0 0 1-.707-.707 7 7 0 0 0 0-9.9.5.5 0 0 1 0-.707zM10 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0"/></svg>
                    Công Khai Tuần
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div id="dynamic-content-wrapper">
        <!-- Ghi chú nằm ở dưới các nút thao tác -->
        <?php if (!empty($report_data)): ?>
        <div class="px-5 py-3 border-b border-[#224397]/10 bg-white">
            <label class="block text-[12px] font-bold text-[#224397] mb-1">Ghi chú báo cáo</label>
            <textarea id="ghiChuBaoCao" class="form-input-sm w-full" style="height:auto; padding-top:0.4rem; padding-bottom:0.4rem;" rows="1" placeholder="Nhập ghi chú tuần..."><?php echo htmlspecialchars($tuan_hoc['ghi_chu_bao_cao'] ?? ''); ?></textarea>
        </div>
        <!-- Thanh trạng thái lọc -->
        <div class="px-5 py-2 border-b border-[#224397]/10 bg-[#224397]/[0.01]">
            <div id="summary-text" class="text-[11px] text-slate-500 font-medium"></div>
        </div>
        <?php endif; ?>

        <div class="p-6">
            <h5 class="mb-4 text-sm font-semibold text-slate-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="#FAB723" class="bi bi-calendar-check" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                <span class="text-[#224397]">Tuần:</span> 
                <?php 
                    if (!isset($tuan_hoc['ten_tuan']) || $tuan_hoc['ten_tuan'] === 'Chưa chọn tuần') {
                        echo 'Chưa chọn tuần';
                    } else {
                        echo htmlspecialchars($tuan_hoc['ten_tuan']);
                    }
                ?>
            </h5>
            
            <div class="overflow-x-auto w-full rounded-lg border border-slate-200">
                <table class="log-table" id="reportTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">STT</th>
                            <th style="width:70px;">Lớp</th>
                            <th class="col-score">Tiết Tốt</th>
                            <th class="col-score">Tiết TB</th>
                            <th class="col-score">Sổ ĐB-NK</th>
                            <th class="col-score">Điểm (+/-)<br>Khác</th>
                            <th class="col-score">Vắng<br>Phép</th>
                            <th class="col-score">Vắng<br>K.Phép</th>
                            <th class="col-score">Nội Quy<br>Chuyên Cần</th>
                            <th style="width:100px;">Tổng Điểm</th>
                            <th style="width:90px;">Xếp Hạng</th>
                            <th style="width:50px;">HĐ</th>
                        </tr>
                    </thead>
                    <tbody id="report-tbody">
                        <?php if (empty($report_data)): ?>
                            <tr><td colspan="12" class="text-center p-6 text-slate-500 font-medium border-0">Không có dữ liệu thi đua cho tuần này.</td></tr>
                        <?php else: ?>
                            <?php 
                                $stt = 0; 
                                foreach($report_data as $lop): 
                                $stt++;
                                $khoi = get_grade_class($lop['lop']);
                                
                                $isRank1 = ((int)$lop['xep_hang'] === 1 && !$lop['kxtd']);
                                $isKXTD = $lop['kxtd'] ? true : false;
                                $isRankLast = (!$isKXTD && (int)$lop['xep_hang'] === $total_lops);
                            ?>
                                <tr class="report-row" data-khoi="<?php echo $khoi; ?>" data-lop="<?php echo htmlspecialchars($lop['lop']); ?>" data-rank="<?php echo $lop['kxtd'] ? 9999 : $lop['xep_hang']; ?>" data-total="<?php echo $lop['tong_diem']; ?>">
                                    <td class="font-bold text-slate-400 row-stt"><?php echo $stt; ?></td>
                                    <td class="font-bold text-[#224397]"><?php echo htmlspecialchars($lop['lop']); ?></td>
                                    
                                    <td><?php print_if_not_zero($lop['diem_tiet_tot_thanh_phan']); ?></td>
                                    <td><?php print_if_not_zero($lop['diem_tiet_tb_thanh_phan']); ?></td>
                                    <td><?php print_if_not_zero($lop['diem_sdb_thanh_phan']); ?></td>
                                    <td><?php print_if_not_zero($lop['diem_cong_tru']); ?></td>
                                    <td><?php print_if_not_zero($lop['vang_p'], 0); ?></td>
                                    <td><?php print_if_not_zero($lop['vang_kp'], 0); ?></td>
                                    
                                    <td class="drill-down font-semibold text-red-600" data-type="noi_quy" data-lop-id="<?php echo $lop['lop_id']; ?>">
                                        <?php print_if_not_zero($lop['diem_noi_quy']); ?>
                                    </td>
                                    
                                    <td class="font-bold text-base text-[#16a34a]"><?php echo round($lop['tong_diem'], 1); ?></td>
                                    
                                    <td class="font-bold <?php 
                                        if($isKXTD) echo 'drill-down-kxtd rank-last'; 
                                        else if($isRank1) echo 'rank-1';
                                        else if($isRankLast) echo 'rank-last';
                                        else echo 'text-slate-700';
                                        ?>" 
                                        <?php if($lop['kxtd']) echo 'data-type="kxtd" data-lop-id="' . $lop['lop_id'] . '"'; ?>
                                    >
                                        <?php 
                                            if($isRank1) echo '1 <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trophy-fill inline text-[#d97706] mb-1" viewBox="0 0 16 16"><path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5c0 .538-.012 1.05-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33.076 33.076 0 0 1 2.5.5zm.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935zm10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935z"/></svg>';
                                            else if($isKXTD) echo 'KXTD';
                                            else echo htmlspecialchars($lop['xep_hang']); 
                                        ?>
                                    </td>
                                    
                                    <td>
                                        <a href="/thidua/bao-cao/phan-tich-lop?lop_id=<?php echo $lop['lop_id']; ?>&tuan_id=<?php echo $tuan_id; ?>" class="btn-action-sm" style="padding:0.2rem 0.4rem; height:auto;" title="Phân tích chi tiết">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-graph-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07"/></svg>
                                        </a>
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
</div>

<!-- Modal Public Weeks (chuẩn UI_SYNC) -->
<div id="publicWeeksModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" style="align-items:center;justify-content:center;" onclick="closePublicWeeksModal()">
    <div class="bg-white rounded-xl shadow-2xl w-[600px] max-w-[95%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
            <h5 class="text-base font-bold text-[#224397] flex items-center gap-2 mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-broadcast text-[#FAB723]" viewBox="0 0 16 16"><path d="M3.05 3.05a7 7 0 0 0 0 9.9.5.5 0 0 1-.707.707 8 8 0 0 1 0-11.314.5.5 0 0 1 .707.707m2.122 2.122a4 4 0 0 0 0 5.656.5.5 0 1 1-.708.708 5 5 0 0 1 0-7.072.5.5 0 0 1 .708.708m5.656-.708a.5.5 0 0 1 .708 0 5 5 0 0 1 0 7.072.5.5 0 1 1-.708-.708 4 4 0 0 0 0-5.656.5.5 0 0 1 0-.708m2.122-2.12a.5.5 0 0 1 .707 0 8 8 0 0 1 0 11.313.5.5 0 0 1-.707-.707 7 7 0 0 0 0-9.9.5.5 0 0 1 0-.707zM10 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0"/></svg>
                Quản Lý Công Khai Tuần Thi Đua
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closePublicWeeksModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <p class="text-sm text-slate-500">Bật công tắc cho những tuần bạn muốn hiển thị trên trang báo cáo công khai.</p>
            <div id="publicWeeksList" class="max-h-[60vh] overflow-y-auto space-y-2"></div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
            <button type="button" class="btn-action-sm" onclick="closePublicWeeksModal()">Đóng</button>
        </div>
    </div>
</div>

<!-- Modal Chi Tiết (chuẩn UI_SYNC) -->
<div id="detailsModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" style="align-items:center;justify-content:center;" onclick="closeDetailsModal()">
    <div class="bg-white rounded-xl shadow-2xl w-[700px] max-w-[95%] max-h-[85vh] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50">
            <h5 class="text-base font-bold text-[#224397] flex items-center gap-2 mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>
                <span id="detailsModalLabel">Chi Tiết</span>
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeDetailsModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto" id="detailsModalBody"></div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
            <button type="button" class="btn-action-sm" onclick="closeDetailsModal()">Đóng</button>
        </div>
    </div>
</div>

<style>
/* Toogle Switch Style */
.form-switch input { appearance: none; width: 2.5rem; height: 1.25rem; background-color: #cbd5e1; border-radius: 1rem; position: relative; cursor: pointer; outline: none; transition: background-color 0.2s; }
.form-switch input::after { content: ''; position: absolute; top: 0.125rem; left: 0.125rem; width: 1rem; height: 1rem; background-color: white; border-radius: 50%; transition: transform 0.2s; }
.form-switch input:checked { background-color: #16a34a; }
.form-switch input:checked::after { transform: translateX(1.25rem); }
</style>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
function openPublicWeeksModal() {
    const modal = document.getElementById('publicWeeksModal');
    const content = modal.querySelector('.modal-content-box');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.style.opacity = '1';
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
    
    // Load data
    const list = document.getElementById('publicWeeksList');
    list.innerHTML = '<div class="flex justify-center p-6"><svg class="animate-spin h-6 w-6 text-[#224397]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
    
    fetch('/thidua/admin/tuan-hoc?action=api_get_public_status')
        .then(res => res.json())
        .then(weeks => {
            list.innerHTML = '';
            if (weeks.length > 0) {
                weeks.forEach(week => {
                    const isChecked = parseInt(week.is_public, 10) === 1 ? 'checked' : '';
                    const item = `
                        <label class="flex justify-between items-center p-3 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 cursor-pointer transition">
                            <span class="text-sm font-semibold text-slate-700">${week.ten_tuan}</span>
                            <div class="form-switch flex items-center">
                                <input type="checkbox" id="week-${week.id}" data-week-id="${week.id}" ${isChecked}>
                            </div>
                        </label>
                    `;
                    list.insertAdjacentHTML('beforeend', item);
                });
            } else {
                list.innerHTML = '<p class="text-center text-sm text-slate-500">Kh&ocirc;ng c&oacute; tu&#x1EA7;n h&#x1ECD;c n&agrave;o.</p>';
            }
        })
        .catch(err => {
            list.innerHTML = '<p class="text-center text-sm text-red-600">L&#x1ED7;i khi t&#x1EA3;i danh s&aacute;ch tu&#x1EA7;n.</p>';
        });
}

function closePublicWeeksModal() {
    const modal = document.getElementById('publicWeeksModal');
    const content = modal.querySelector('.modal-content-box');
    modal.style.opacity = '0';
    content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => { modal.style.display = 'none'; modal.classList.add('hidden'); }, 300);
}

function openDetailsModal() {
    const modal = document.getElementById('detailsModal');
    const content = modal.querySelector('.modal-content-box');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.style.opacity = '1';
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}

function closeDetailsModal() {
    const modal = document.getElementById('detailsModal');
    const content = modal.querySelector('.modal-content-box');
    modal.style.opacity = '0';
    content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => { modal.style.display = 'none'; modal.classList.add('hidden'); }, 300);
}


function initBaoCaoEvents() {
    const tuanId = document.getElementById('tuan_id_select').value;

    document.querySelectorAll('.drill-down, .drill-down-kxtd').forEach(cell => {
        // remove old listeners by replacing element (or just bind carefully if replaced)
        if (cell.dataset.bound) return;
        cell.dataset.bound = "true";
        if (cell.textContent.trim() !== '' && cell.textContent.trim() !== '0' && !cell.textContent.trim().startsWith('0 l')) {
            cell.addEventListener('click', async function() {
                const type = this.dataset.type;
                const lopId = this.dataset.lopId;
                const lopName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();

                document.getElementById('detailsModalBody').innerHTML = '<div class="flex justify-center p-8"><svg class="animate-spin h-8 w-8 text-[#224397]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
                openDetailsModal();

                let content = '<div class="p-4 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200 text-sm">Kh&ocirc;ng c&oacute; d&#x1EEF; li&#x1EC7;u chi ti&#x1EBF;t.</div>';
                let url = '';
                let title = '';

                if (type === 'noi_quy') {
                    title = `Chi Ti&#x1EBF;t Vi Ph&#x1EA1;m N&#x1ED9;i Quy - L&#x1EDB;p ${lopName}`;
                    url = `/thidua/api/get-violation-details?tuan_id=${tuanId}&lop_id=${lopId}`;
                } else if (type === 'vang') {
                    title = `Chi Ti&#x1EBF;t &#x110;i&#x1EC3;m Danh - L&#x1EDB;p ${lopName}`;
                    url = `/thidua/api/get-attendance-details?tuan_id=${tuanId}&lop_id=${lopId}`;
                } else if (type === 'kxtd') {
                    title = `L&yacute; Do Kh&ocirc;ng X&eacute;t Thi &#x110;ua - L&#x1EDB;p ${lopName}`;
                    url = `/thidua/api/get-kxtd-reason?tuan_id=${tuanId}&lop_id=${lopId}`;
                }
                
                document.getElementById('detailsModalLabel').innerHTML = title;

                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    if (type === 'noi_quy' && data.length > 0) {
                        content = '<table class="log-table" style="font-size:0.75rem;"><thead><tr><th>Ng&agrave;y</th><th>H&#x1ECD; T&ecirc;n</th><th>T&ecirc;n Vi Ph&#x1EA1;m</th><th>Ghi Ch&uacute;</th></tr></thead><tbody>';
                        data.forEach(item => {
                            let formattedDate = 'N/A';
                            if (item.ngay_vi_pham) {
                                const parts = item.ngay_vi_pham.split('-');
                                if (parts.length === 3) formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
                            }
                            content += `<tr><td>${formattedDate}</td><td>${item.ho_ten || ''}</td><td class="font-semibold">${item.ten_vi_pham || ''}</td><td class="text-slate-500">${item.ghi_chu || ''}</td></tr>`;
                        });
                        content += '</tbody></table>';
                    } else if (type === 'vang' && data.length > 0) {
                        content = '<table class="log-table" style="font-size:0.75rem;"><thead><tr><th>Ng&agrave;y</th><th>V&#x1EAF;ng P</th><th>V&#x1EAF;ng KP</th><th>B&#x1ECF; Ti&#x1EBF;t</th></tr></thead><tbody>';
                        data.forEach(item => {
                            content += `<tr><td>${new Date(item.ngay_diem_danh).toLocaleDateString('vi-VN')}</td><td>${item.vang_p || '0'}</td><td>${item.vang_kp || '0'}</td><td>${item.bo_tiet || ''}</td></tr>`;
                        });
                        content += '</tbody></table>';
                    } else if (type === 'kxtd') {
                        content = `<div class="p-4 rounded-lg bg-red-50 text-red-800 border border-red-200 text-sm font-bold">${data.reason || 'Kh&ocirc;ng x&aacute;c &#x111;&#x1ECB;nh &#x111;&#x1B0;&#x1EE3;c l&yacute; do c&#x1EE5; th&#x1EC3;.'}</div>`;
                    }
                } catch (error) {
                    content = `<div class="p-4 rounded-lg bg-red-50 text-red-800 border border-red-200 text-sm">L&#x1ED7;i khi t&#x1EA3;i d&#x1EEF; li&#x1EC7;u: ${error.message}</div>`;
                }
                
                document.getElementById('detailsModalBody').innerHTML = content;
            });
        }
    });

    const ghiChuTextarea = document.getElementById('ghiChuBaoCao');
    if (ghiChuTextarea) {
        let timeout;
        ghiChuTextarea.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                fetch('/thidua/admin/tuan-hoc?action=api_save_note', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tuan_id: tuanId, ghi_chu: this.value })
                });
            }, 500);
        });
    }

    // Client-side Filter
    const tbody = document.getElementById('report-tbody');
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr.report-row'));
    const filterKhoi = document.getElementById('filter-khoi');
    const filterLop = document.getElementById('filter-lop');
    const quickSearch = document.getElementById('quick-search');
    const summaryText = document.getElementById('summary-text');

    if (rows.length === 0) return;

    const lopByKhoi = new Map();
    rows.forEach(tr => {
        const k = tr.dataset.khoi;
        const l = tr.dataset.lop;
        if (!lopByKhoi.has(k)) lopByKhoi.set(k, new Set());
        lopByKhoi.get(k).add(l);
    });

    function populateLopOptions(khoi) {
        if(!filterLop) return;
        filterLop.innerHTML = '<option value="">T&#x1EA5;t c&#x1EA3;</option>';
        if (!khoi) {
            filterLop.disabled = true;
            return;
        }
        const set = lopByKhoi.get(khoi);
        if (!set || set.size === 0) {
            filterLop.disabled = true;
            return;
        }
        [...set].sort((a,b) => a.localeCompare(b,'vi')).forEach(l => {
            const opt = document.createElement('option');
            opt.value = l; opt.textContent = l;
            filterLop.appendChild(opt);
        });
        filterLop.disabled = false;
    }

    function applyFilters() {
        if(!filterKhoi || !filterLop || !quickSearch) return;
        const valKhoi = filterKhoi.value;
        const valLop = filterLop.value;
        const q = quickSearch.value.trim().toLowerCase();
        
        let vis = 0;
        rows.forEach(tr => {
            let ok = true;
            if (valKhoi && tr.dataset.khoi !== valKhoi) ok = false;
            if (ok && valLop && tr.dataset.lop !== valLop) ok = false;
            if (ok && q && !tr.dataset.lop.toLowerCase().includes(q)) ok = false;
            
            tr.style.display = ok ? '' : 'none';
            if (ok) vis++;
        });

        if(summaryText) summaryText.innerHTML = `Hi&#x1EC3;n th&#x1ECB; ${vis} / ${rows.length} l&#x1EDB;p &middot; &#x110;ang d&ugrave;ng b&#x1ED9; l&#x1ECD;c client`;
    }

    if(filterKhoi) {
        filterKhoi.addEventListener('change', () => {
            populateLopOptions(filterKhoi.value);
            filterLop.value = '';
            applyFilters();
        });
    }
    if(filterLop) filterLop.addEventListener('change', applyFilters);
    if(quickSearch) quickSearch.addEventListener('input', applyFilters);
}

async function loadTuanData(tuanId) {
    if(!tuanId) return;
    
    // Show loading state
    const wrapper = document.getElementById('dynamic-content-wrapper');
    if(wrapper) {
        wrapper.style.opacity = '0.5';
        wrapper.style.pointerEvents = 'none';
    }
    
    try {
        const url = `/thidua/bao-cao/thi-dua?iframe=1&tuan_id=${tuanId}`;
        const response = await fetch(url);
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        
        // Update URL without reloading
        window.history.pushState({}, '', `?tuan_id=${tuanId}`);
        
        // Replace dynamic content wrapper
        const newWrapper = doc.getElementById('dynamic-content-wrapper');
        if(newWrapper && wrapper) {
            wrapper.innerHTML = newWrapper.innerHTML;
        }
        
        // Replace actions container
        const newActions = doc.getElementById('actions-container');
        const oldActions = document.getElementById('actions-container');
        if(newActions && oldActions) {
            oldActions.innerHTML = newActions.innerHTML;
        }
        
        // Re-initialize events
        initBaoCaoEvents();
        
    } catch (e) {
        console.error("Error loading data:", e);
        if (typeof showToast === 'function') showToast('Lỗi khi tải dữ liệu', 'error');
    } finally {
        if(wrapper) {
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
        }
    }
}


document.addEventListener('DOMContentLoaded', function() {
    initBaoCaoEvents();
    
    // Toggle Public Status
    document.getElementById('publicWeeksList').addEventListener('change', async function(event) {
        if (event.target.matches('input[type="checkbox"]')) {
            const weekId = event.target.dataset.weekId;
            const isPublic = event.target.checked ? 1 : 0;
            try {
                const res = await fetch('/thidua/admin/tuan-hoc?action=api_toggle_public_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tuan_id: weekId, status: isPublic })
                });
                if(!res.ok) throw new Error('Lỗi cập nhật');
                if (typeof showToast === 'function') showToast('Cập nhật trạng thái thành công', 'success');
            } catch (error) {
                if (typeof showToast === 'function') showToast('Lỗi cập nhật', 'error');
                event.target.checked = !event.target.checked;
            }
        }
    });
});
</script>