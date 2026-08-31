<?php
$page_title = 'Hồ Sơ Học Sinh';
require_once __DIR__ . '/partials/admin_header.php';

// Các biến được nạp từ controller (HocSinhController.php)
$hoc_sinh = $hoc_sinh ?? [];
$login_history = $login_history ?? [];
$violations_list = $violations_list ?? [];
$rewards_list = $rewards_list ?? [];
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track { background: transparent; }
    
    /* Table chuẩn như các trang báo cáo khác */
    .log-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .log-table thead th { 
        background: #f8fafc; color: #1e293b; font-weight: 700; 
        text-transform: uppercase; font-size: 0.75rem; 
        padding: 0.85rem 0.75rem; border: 1px solid #cbd5e1; 
        white-space: nowrap; text-align: center; 
    }
    .log-table td { 
        padding: 0.85rem 0.75rem; border: 1px solid #cbd5e1; 
        vertical-align: middle; color: #334155; 
    }
    .log-table tbody tr:hover { background: #f1f5f9; }

    /* Button UI chuẩn nhỏ gọn */
    .btn-action-sm { display: inline-flex; align-items: center; gap: 0.4rem; height: 34px; padding: 0 0.85rem; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid rgba(34,67,151,0.25); background: #fff; color: #224397; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm:hover { background: #FAB723; color: #fff; border-color: #FAB723; }
    
    .btn-action-sm-green { display: inline-flex; align-items: center; gap: 0.4rem; height: 34px; padding: 0 0.85rem; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid rgba(22,163,74,0.25); background: #16a34a; color: #fff; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action-sm-green:hover { background: #15803d; color: #fff; border-color: #15803d; }

    /* Tabs UI */
    .tab-btn-active { 
        display: inline-flex; align-items: center; gap: 0.5rem; 
        padding: 0.75rem 1.5rem; font-size: 0.9rem; font-weight: 700; 
        color: #224397; background-color: #fff; 
        border-bottom: 3px solid #224397; cursor: pointer; transition: all 0.2s; 
    }
    .tab-btn-inactive { 
        display: inline-flex; align-items: center; gap: 0.5rem; 
        padding: 0.75rem 1.5rem; font-size: 0.9rem; font-weight: 600; 
        color: #64748b; background-color: transparent; 
        border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.2s; 
    }
    .tab-btn-inactive:hover { color: #1e293b; border-bottom-color: #cbd5e1; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 pb-12 mt-6">
    <!-- HEADER -->
    <div class="flex flex-wrap items-center justify-between mb-6 gap-3">
        <h1 class="text-xl mb-0 font-bold text-[#224397] flex items-center gap-2 uppercase">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-person-vcard-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm9 1.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5M9 8a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4A.5.5 0 0 0 9 8m1 2.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5m-1 2a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5M4 5a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-3 7c0 1 1 1 1 1h4s1 0 1-1-1-2-3-2-3 1-3 2"/></svg>
            <?php echo 'Hồ Sơ Học Sinh: ' . htmlspecialchars(($hoc_sinh['ho_dem'] ?? '') . ' ' . ($hoc_sinh['ten'] ?? '')); ?>
        </h1>
        <div class="flex items-center gap-2">
            <!-- Link tải Excel chính xác trỏ đến action=export_profile -->
            <a href="/thidua/admin/hoc-sinh?action=export_profile&id=<?php echo $hoc_sinh['id'] ?? 0; ?>" class="btn-action-sm-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                Tải Hồ Sơ (Excel)
            </a>
            <a href="/thidua/admin/tra-cuu-hoc-sinh<?php echo isset($_GET['iframe']) ? '?iframe=1' : ''; ?>" class="btn-action-sm bg-slate-600 border-slate-600 text-white hover:bg-slate-700 hover:border-slate-700 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>
                Quay lại tra cứu
            </a>
        </div>
    </div>
    
    <!-- GRID CHÍNH -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- CỘT TRÁI (THÔNG TIN CÁ NHÂN & ĐĂNG NHẬP) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- THÔNG TIN CÁ NHÂN -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 bg-gradient-to-b from-[#f8fafc] to-white text-center border-b border-slate-100">
                    <!-- ĐÃ SỬA: Ảnh thẻ chuẩn tỷ lệ 3:4 (w-36 h-48) kèm khung viền đẹp mắt -->
                    <div class="w-36 h-48 overflow-hidden rounded-lg border-2 border-slate-300 shadow-md p-1 bg-white mx-auto mb-4">
                        <img 
                            src="<?php echo htmlspecialchars(get_student_avatar_url($hoc_sinh['anh_the'] ?? '', $hoc_sinh['anh_the_driver'] ?? 'local', $hoc_sinh['anh_the_cloud_key'] ?? null)); ?>" 
                            alt="Ảnh đại diện của <?php echo htmlspecialchars($hoc_sinh['ho_dem'] . ' ' . $hoc_sinh['ten']); ?>"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            onerror="this.onerror=null;this.src='/thidua/public/assets/img/anhthegoc.JPG';">
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 m-0"><?php echo htmlspecialchars(($hoc_sinh['ho_dem'] ?? '') . ' ' . ($hoc_sinh['ten'] ?? '')); ?></h3>
                    <p class="text-xs font-semibold text-slate-400 mt-1 mb-0 uppercase tracking-wider">Học sinh lớp <?php echo htmlspecialchars($hoc_sinh['ten_lop'] ?? 'Chưa xếp'); ?></p>
                </div>
                
                <div class="px-6 py-3 bg-[#f8fafc] border-b border-slate-200">
                    <span class="text-xs font-bold text-[#224397] uppercase tracking-wider">Chi Tiết Hồ Sơ</span>
                </div>
                
                <div class="p-6 divide-y divide-slate-100 text-sm">
                    <div class="py-3 flex items-center justify-between first:pt-0">
                        <span class="font-medium text-slate-500">Số CCCD / Mã HS:</span>
                        <span class="font-bold text-slate-800"><?php echo htmlspecialchars($hoc_sinh['ma_hoc_sinh'] ?? 'KXD'); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Lớp học:</span>
                        <span class="font-bold text-[#224397]"><?php echo htmlspecialchars($hoc_sinh['ten_lop'] ?? 'Chưa xếp'); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Niên khóa:</span>
                        <span class="font-medium text-slate-800"><?php echo htmlspecialchars($hoc_sinh['nien_khoa'] ?? 'Chưa cập nhật'); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Giáo viên CN:</span>
                        <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($hoc_sinh['gvcn_ten'] ?? 'Chưa có'); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Ngày sinh:</span>
                        <span class="font-medium text-slate-800">
                            <?php
                                $ns = $hoc_sinh['ngay_sinh'] ?? '';
                                $date = DateTime::createFromFormat('Y-m-d', $ns);
                                if (!$date) { $date = DateTime::createFromFormat('d/m/Y', $ns); }
                                echo $date ? $date->format('d/m/Y') : 'Không hợp lệ';
                            ?>
                        </span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Giới tính:</span>
                        <span class="font-medium text-slate-800"><?php echo htmlspecialchars($hoc_sinh['gioi_tinh'] ?? 'KXD'); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Chức vụ:</span>
                        <span class="font-medium text-slate-800"><?php echo htmlspecialchars($hoc_sinh['chuc_vu'] ?? 'Không'); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Số điện thoại:</span>
                        <span class="font-medium text-slate-800"><?php echo htmlspecialchars($hoc_sinh['sdt'] ?? 'Chưa có'); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Email:</span>
                        <span class="font-medium text-slate-800"><?php echo htmlspecialchars($hoc_sinh['email'] ?? 'Chưa có'); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Địa chỉ:</span>
                        <span class="font-medium text-slate-800 text-right max-w-[60%]">
                            <?php 
                                $dia_chi_parts = [];
                                if (!empty($hoc_sinh['dia_chi_chi_tiet'])) $dia_chi_parts[] = $hoc_sinh['dia_chi_chi_tiet'];
                                if (!empty($hoc_sinh['ap_khupho'])) $dia_chi_parts[] = $hoc_sinh['ap_khupho'];
                                if (!empty($hoc_sinh['xa_phuong'])) $dia_chi_parts[] = $hoc_sinh['xa_phuong'];
                                if (!empty($hoc_sinh['tinh_thanhpho'])) $dia_chi_parts[] = $hoc_sinh['tinh_thanhpho'];
                                echo !empty($dia_chi_parts) ? htmlspecialchars(implode(', ', $dia_chi_parts)) : 'Chưa có';
                            ?>
                        </span>
                    </div>
                    <div class="py-3 flex items-center justify-between last:pb-0">
                        <span class="font-medium text-slate-500">Trạng thái học tập:</span>
                        <span>
                            <?php 
                                $status = $hoc_sinh['trang_thai_hoc_tap'] ?? 'dang_hoc';
                                if ($status === 'dang_hoc') {
                                    echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Đang học</span>';
                                } elseif ($status === 'nghi_hoc') {
                                    echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">Đã nghỉ học</span>';
                                } elseif ($status === 'da_tot_nghiep') {
                                    echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Đã tốt nghiệp</span>';
                                } else {
                                    echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">' . htmlspecialchars($status) . '</span>';
                                }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- THÔNG TIN ĐĂNG NHẬP -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-3 bg-[#f8fafc] border-b border-slate-200">
                    <span class="text-xs font-bold text-[#224397] uppercase tracking-wider">Thông Tin Đăng Nhập</span>
                </div>
                <div class="p-6 divide-y divide-slate-100 text-sm">
                    <div class="py-3 flex items-center justify-between first:pt-0">
                        <span class="font-medium text-slate-500">Trạng thái tài khoản:</span>
                        <?php 
                            $tk_status = $hoc_sinh['trang_thai_tai_khoan'] ?? 'Chưa cấp TK';
                            $badge_class = 'bg-slate-100 text-slate-700 border-slate-200';
                            if ($tk_status === 'Đã cấp TK') $badge_class = 'bg-amber-100 text-amber-800 border-amber-200';
                            if ($tk_status === 'Đã đổi MK') $badge_class = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border <?php echo $badge_class; ?>"><?php echo htmlspecialchars($tk_status); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-medium text-slate-500">Tổng số lần đăng nhập:</span>
                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-slate-800 text-white"><?php echo count($login_history); ?></span>
                    </div>
                    <div class="py-3 flex items-center justify-between last:pb-0">
                        <span class="font-medium text-slate-500">Đăng nhập lần cuối:</span>
                        <?php if (!empty($login_history)): ?>
                            <div class="text-right">
                                <span class="block font-semibold text-slate-800 text-xs"><?php echo date('d/m/Y H:i:s', strtotime($login_history[0]['thoi_gian_dang_nhap'])); ?></span>
                                <span class="block text-[11px] text-slate-400">IP: <?php echo htmlspecialchars($login_history[0]['dia_chi_ip']); ?></span>
                            </div>
                        <?php else: ?>
                            <span class="text-slate-400 font-medium italic">Chưa đăng nhập lần nào</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($login_history)): ?>
                <div class="px-6 pb-6">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Lịch Sử Đăng Nhập Chi Tiết</p>
                    <div class="overflow-y-auto max-h-48 w-full rounded-lg border border-slate-200">
                        <table class="log-table">
                            <thead class="sticky top-0 z-10 bg-[#f8fafc]">
                                <tr>
                                    <th>Thời Gian</th>
                                    <th>Địa chỉ IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($login_history as $log): ?>
                                    <tr>
                                        <td class="text-center font-medium text-xs"><?php echo date('d/m/Y H:i:s', strtotime($log['thoi_gian_dang_nhap'])); ?></td>
                                        <td class="text-center text-xs text-slate-500"><?php echo htmlspecialchars($log['dia_chi_ip']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- CỘT PHẢI (LỊCH SỬ VI PHẠM & KHEN THƯỞNG) -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- TABS HEADER -->
                <div class="bg-[#f8fafc] border-b border-slate-200 flex flex-wrap pt-2 px-4 gap-2">
                    <button type="button" id="btnViolationsTab" class="tab-btn-active" onclick="switchTab('violations')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-exclamation-triangle-fill text-rose-500" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                        Lịch Sử Vi Phạm (<?php echo count($violations_list); ?>)
                    </button>
                    <button type="button" id="btnRewardsTab" class="tab-btn-inactive" onclick="switchTab('rewards')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-award-fill text-amber-500" viewBox="0 0 16 16"><path d="m8 0 1.669.864 1.858.282.842 1.68 1.337 1.32L13.4 6l.306 1.854-1.337 1.32-.842 1.68-1.858.282L8 12l-1.669-.864-1.858-.282-.842-1.68-1.337-1.32L2.6 6l-.306-1.854 1.337-1.32.842-1.68 1.858-.282L8 0z"/><path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z"/></svg>
                        Lịch Sử Khen Thưởng (<?php echo count($rewards_list); ?>)
                    </button>
                    <button type="button" id="btnActivitiesTab" class="tab-btn-inactive" onclick="switchTab('activities')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-activity text-blue-500" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2Z"/></svg>
                        Hoạt Động Tham Gia (<?php echo count($activities_list ?? []); ?>)
                    </button>
                </div>
                
                <!-- TABS CONTENT -->
                <div class="p-6">
                    <!-- TAB VI PHẠM -->
                    <div id="violationsTab" class="animate-in fade-in duration-200">
                        <?php if (empty($violations_list)): ?>
                            <div class="p-8 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-center font-semibold flex items-center justify-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.8em" height="1.8em" fill="currentColor" class="bi bi-check-circle-fill text-emerald-600" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                                Tuyệt vời! Học sinh không có vi phạm nào trong suốt quá trình học tập.
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto overflow-y-auto max-h-[600px] w-full rounded-lg border border-slate-200">
                                <table class="log-table">
                                    <thead class="sticky top-0 z-10 bg-[#f8fafc]">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">STT</th>
                                            <th style="width: 100px;">Năm học</th>
                                            <th style="width: 80px;">Tuần</th>
                                            <th style="width: 100px;">Ngày VP</th>
                                            <th style="text-align: left; padding-left: 1rem;">Tên Nhóm Vi Phạm</th>
                                            <th style="width: 80px;" class="text-center">Điểm trừ</th>
                                            <th style="text-align: left; padding-left: 1rem;">Ghi Chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($violations_list as $idx => $vp): ?>
                                        <tr>
                                            <td class="text-center font-bold text-slate-700"><?php echo $idx + 1; ?></td>
                                            <td class="text-center font-medium"><?php echo htmlspecialchars($vp['ten_nam_hoc'] ?? '---'); ?></td>
                                            <td class="text-center font-bold text-slate-700"><?php echo htmlspecialchars($vp['ten_tuan']); ?></td>
                                            <td class="text-center font-semibold text-rose-600"><?php echo date('d/m/Y', strtotime($vp['ngay_vi_pham'])); ?></td>
                                            <td style="text-align: left; padding-left: 1rem;" class="font-bold text-slate-800"><?php echo htmlspecialchars($vp['ten_vi_pham']); ?></td>
                                            <td class="text-center font-bold text-rose-600">-<?php echo htmlspecialchars($vp['diem_tru']); ?></td>
                                            <td style="text-align: left; padding-left: 1rem;" class="text-slate-600"><?php echo htmlspecialchars($vp['ghi_chu'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB KHEN THƯỞNG -->
                    <div id="rewardsTab" class="hidden animate-in fade-in duration-200">
                        <?php if (empty($rewards_list)): ?>
                            <div class="p-8 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 text-center font-semibold flex items-center justify-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.8em" height="1.8em" fill="currentColor" class="bi bi-info-circle-fill text-sky-600" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>
                                Học sinh chưa có thông tin khen thưởng cá nhân nào.
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto overflow-y-auto max-h-[600px] w-full rounded-lg border border-slate-200">
                                <table class="log-table">
                                    <thead class="sticky top-0 z-10 bg-[#f8fafc]">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">STT</th>
                                            <th style="width: 100px;">Năm học</th>
                                            <th style="width: 80px;">Lớp</th>
                                            <th style="width: 100px;">Ngày KT</th>
                                            <th style="text-align: left; padding-left: 1rem;">Tên Khen Thưởng</th>
                                            <th style="width: 120px;">Số QĐ</th>
                                            <th style="width: 120px;">Cấp KT</th>
                                            <th style="text-align: left; padding-left: 1rem;">Ghi Chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($rewards_list as $idx => $kt): ?>
                                        <tr>
                                            <td class="text-center font-bold text-slate-700"><?php echo $idx + 1; ?></td>
                                            <td class="text-center font-medium"><?php echo htmlspecialchars($kt['ten_nam_hoc'] ?? '---'); ?></td>
                                            <td class="text-center font-bold text-[#224397]"><?php echo htmlspecialchars($kt['ten_lop'] ?? '---'); ?></td>
                                            <td class="text-center font-semibold text-emerald-600"><?php echo date('d/m/Y', strtotime($kt['ngay_khen_thuong'])); ?></td>
                                            <td style="text-align: left; padding-left: 1rem;" class="font-bold text-slate-800"><?php echo htmlspecialchars($kt['ten_khen_thuong']); ?></td>
                                            <td class="text-center font-mono text-slate-600"><?php echo htmlspecialchars($kt['so_quyet_dinh']); ?></td>
                                            <td class="text-center font-semibold text-indigo-700 bg-indigo-50/50"><?php echo htmlspecialchars($kt['cap_khen_thuong']); ?></td>
                                            <td style="text-align: left; padding-left: 1rem;" class="text-slate-600"><?php echo htmlspecialchars($kt['ghi_chu'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB HOẠT ĐỘNG THAM GIA -->
                    <div id="activitiesTab" class="hidden animate-in fade-in duration-200">
                        <?php if (empty($activities_list)): ?>
                            <div class="p-8 rounded-xl bg-blue-50 text-blue-800 border border-blue-200 text-center font-semibold flex items-center justify-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.8em" height="1.8em" fill="currentColor" class="bi bi-info-circle-fill text-blue-600" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>
                                Học sinh chưa tham gia hoạt động nào.
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto overflow-y-auto max-h-[600px] w-full rounded-lg border border-slate-200">
                                <table class="log-table">
                                    <thead class="sticky top-0 z-10 bg-[#f8fafc]">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">STT</th>
                                            <th style="width: 140px;" class="text-center">Ngày Tham Gia</th>
                                            <th style="text-align: left; padding-left: 1rem;">Tên Hoạt Động</th>
                                            <th style="width: 120px;" class="text-center">Đánh Giá</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($activities_list as $idx => $act): ?>
                                        <tr>
                                            <td class="text-center font-bold text-slate-700"><?php echo $idx + 1; ?></td>
                                            <td class="text-center font-semibold text-blue-600"><?php echo date('d/m/Y', strtotime($act['ngay_tham_gia'])); ?></td>
                                            <td style="text-align: left; padding-left: 1rem;" class="font-bold text-slate-800"><?php echo htmlspecialchars($act['ten_hoat_dong']); ?></td>
                                            <td class="text-center font-medium">
                                                <?php if ($act['trang_thai_diem_danh'] == 1): ?>
                                                    <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700 font-bold border border-green-200">Đã tham gia (+<?php echo (float)$act['diem_thuc_te']; ?>đ)</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 rounded text-xs bg-slate-100 text-slate-600 font-bold border border-slate-200">Chưa điểm danh</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
function switchTab(tabId) {
    const vTab = document.getElementById('violationsTab');
    const rTab = document.getElementById('rewardsTab');
    const aTab = document.getElementById('activitiesTab');
    const vBtn = document.getElementById('btnViolationsTab');
    const rBtn = document.getElementById('btnRewardsTab');
    const aBtn = document.getElementById('btnActivitiesTab');
    
    if (!vTab || !rTab || !vBtn || !rBtn) return;

    vTab.classList.add('hidden');
    rTab.classList.add('hidden');
    if (aTab) aTab.classList.add('hidden');
    
    vBtn.className = 'tab-btn-inactive';
    rBtn.className = 'tab-btn-inactive';
    if (aBtn) aBtn.className = 'tab-btn-inactive';

    if (tabId === 'violations') {
        vTab.classList.remove('hidden');
        vBtn.className = 'tab-btn-active';
    } else if (tabId === 'rewards') {
        rTab.classList.remove('hidden');
        rBtn.className = 'tab-btn-active';
    } else if (tabId === 'activities') {
        if (aTab) aTab.classList.remove('hidden');
        if (aBtn) aBtn.className = 'tab-btn-active';
    }
}
</script>
