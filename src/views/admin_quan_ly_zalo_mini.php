<?php
$page_title = 'Quản Lý Mini Zalo';
require_once __DIR__ . '/partials/admin_header.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $db = get_db_connection();

    // 1. Lấy cài đặt Zalo
    $stmt_settings = $db->query("SELECT setting_key, setting_value FROM settings WHERE group_name = 'zalo'");
    $zalo_settings = [];
    while ($row = $stmt_settings->fetch()) {
        $zalo_settings[$row['setting_key']] = $row['setting_value'];
    }
    $allow_edit = $zalo_settings['zalo_allow_edit_profile'] ?? '0';
    $auto_approve = $zalo_settings['zalo_auto_approve_edit'] ?? '0';
    $editable_fields = json_decode($zalo_settings['zalo_editable_fields'] ?? '[]', true);
    if (!is_array($editable_fields)) $editable_fields = [];

    // 2. Đếm số lượng yêu cầu chờ duyệt
    $stmt_count = $db->query("SELECT COUNT(*) as cnt FROM yeu_cau_chinh_sua_zalo WHERE trang_thai = 'cho_duyet'");
    $pending_count = $stmt_count->fetch()['cnt'] ?? 0;

    // 3. Thống kê Zalo Bot & Mini App
    $stat_total = $db->query("SELECT COUNT(*) FROM ho_so_hoc_sinh")->fetchColumn() ?: 0;
    $stat_connected = $db->query("SELECT COUNT(*) FROM ho_so_hoc_sinh WHERE (zalo_chat_id IS NOT NULL AND zalo_chat_id != '') OR (zalo_id IS NOT NULL AND zalo_id != '')")->fetchColumn() ?: 0;
    $stat_not_connected = $stat_total - $stat_connected;
    $stat_total_access = $db->query("SELECT SUM(zalo_access_count) FROM ho_so_hoc_sinh")->fetchColumn() ?: 0;

    // 4. Danh sách niên khóa & lớp học để lọc
    $nien_khoa_list = $db->query("SELECT DISTINCT nien_khoa FROM ho_so_hoc_sinh WHERE nien_khoa IS NOT NULL AND nien_khoa != '' ORDER BY nien_khoa DESC")->fetchAll(PDO::FETCH_COLUMN);
    $lop_list = $db->query("SELECT id, ten_lop FROM lop_hoc ORDER BY ten_lop ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 5. Lấy toàn bộ danh sách học sinh
    $sql_students = "
        SELECT 
            h.id,
            h.ma_hoc_sinh,
            h.ho_dem,
            h.ten,
            h.ngay_sinh,
            h.gioi_tinh,
            h.sdt,
            h.nien_khoa,
            h.trang_thai_hoc_tap,
            h.nam_tot_nghiep,
            h.zalo_chat_id,
            h.zalo_id,
            h.zalo_last_active,
            h.zalo_access_count,
            l.ten_lop,
            nh.ten_nam_hoc
        FROM ho_so_hoc_sinh h
        LEFT JOIN (
            SELECT qt1.* 
            FROM quatrinh_hoc_tap qt1
            JOIN (
                SELECT ma_hoc_sinh, MAX(nam_hoc_id) as max_nam_hoc_id
                FROM quatrinh_hoc_tap
                GROUP BY ma_hoc_sinh
            ) qt2 ON qt1.ma_hoc_sinh = qt2.ma_hoc_sinh AND qt1.nam_hoc_id = qt2.max_nam_hoc_id
        ) q ON h.ma_hoc_sinh = q.ma_hoc_sinh
        LEFT JOIN lop_hoc l ON q.lop_hoc_id = l.id
        LEFT JOIN nam_hoc nh ON q.nam_hoc_id = nh.id
        ORDER BY h.nien_khoa DESC, l.ten_lop ASC, h.ten ASC, h.ho_dem ASC
    ";

    $stmt_students = $db->query($sql_students);
    $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}

function format_ddmmyyyy($date_str) {
    if (empty($date_str)) return '-';
    if (strpos($date_str, '/') !== false) return $date_str;
    $parts = explode('-', $date_str);
    if (count($parts) === 3) {
        return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
    }
    return $date_str;
}
?>

<div class="flex-1 overflow-y-auto bg-[#f8fafc] p-4 md:p-6 min-h-screen">
    <div class="max-w-7xl mx-auto flex flex-col gap-5">

        <!-- HEADER TITLE -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#224397]/20 pb-4">
            <div>
                <h3 class="text-xl md:text-2xl font-black text-[#224397] flex items-center gap-2.5 uppercase tracking-wide">
                    <i class="bi bi-chat-dots-fill text-blue-600"></i> Quản Lý Mini Zalo & Bot Thông Báo
                </h3>
                <p class="text-xs md:text-sm text-slate-500 mt-1">
                    Danh sách học sinh, ID khung chat Zalo Bot và thiết lập quyền chỉnh sửa thông tin.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="/thidua/admin/duyet-thong-tin-zalo" class="px-4 py-2 bg-[#FAB723] hover:bg-[#e5a61d] text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5 active:scale-95">
                    <i class="bi bi-list-check text-sm"></i> Duyệt thông tin 
                    <?php if ($pending_count > 0): ?>
                        <span class="bg-rose-600 text-white px-2 py-0.5 rounded-full text-[10px] font-black ml-1 animate-pulse"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#224397] flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 block">Tổng học sinh</span>
                    <span class="text-xl font-black text-slate-800" id="statTotal"><?= number_format($stat_total) ?></span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 block">Đã kết nối Bot Zalo</span>
                    <span class="text-xl font-black text-emerald-600" id="statConnected"><?= number_format($stat_connected) ?></span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 block">Chưa kết nối Bot</span>
                    <span class="text-xl font-black text-amber-600" id="statNotConnected"><?= number_format($stat_not_connected) ?></span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="bi bi-phone-fill"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 block">Lượt mở Mini App</span>
                    <span class="text-xl font-black text-indigo-600"><?= number_format($stat_total_access) ?></span>
                </div>
            </div>
        </div>

        <!-- MAIN TABLE SECTION -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden flex flex-col">
            
            <!-- TOOLBAR TITLE -->
            <div class="bg-slate-50/80 px-5 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2 font-bold text-[#224397] text-sm uppercase">
                    <i class="bi bi-table"></i> Danh Sách Học Sinh & Kết Nối Zalo Bot
                    <span class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded-full" id="filterCount">
                        <?= count($students) ?> học sinh
                    </span>
                </div>
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i class="bi bi-info-circle text-blue-500"></i> Học sinh nhắn số CCCD vào Bot Zalo để tự động gán tài khoản.
                </div>
            </div>

            <!-- SEARCH & FILTERS BAR (Redesigned & Aligned) -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/30">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-12 gap-2.5 items-center">
                    
                    <!-- Search Input (Span 4) -->
                    <div class="relative lg:col-span-4">
                        <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                        <input 
                            type="text" 
                            id="searchInput" 
                            oninput="applyFilter()"
                            placeholder="Tìm Họ tên, Số CCCD/Mã HS, SĐT, Zalo ID..." 
                            class="w-full h-10 pl-9 pr-8 text-xs bg-white border border-slate-200 rounded-xl outline-none focus:border-[#224397] focus:ring-2 focus:ring-[#224397]/10 transition shadow-2xs"
                        />
                        <button type="button" onclick="clearSearch()" id="clearSearchBtn" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <!-- Filter Lớp (Span 2) -->
                    <div class="lg:col-span-2">
                        <select id="filterLop" onchange="applyFilter()" class="w-full h-10 px-3 text-xs bg-white border border-slate-200 rounded-xl outline-none focus:border-[#224397] focus:ring-2 focus:ring-[#224397]/10 transition shadow-2xs cursor-pointer">
                            <option value="">-- Tất cả Lớp --</option>
                            <?php foreach ($lop_list as $l): ?>
                                <option value="<?= htmlspecialchars($l['ten_lop']) ?>">Lớp <?= htmlspecialchars($l['ten_lop']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Niên khóa (Span 2) -->
                    <div class="lg:col-span-2">
                        <select id="filterNienKhoa" onchange="applyFilter()" class="w-full h-10 px-3 text-xs bg-white border border-slate-200 rounded-xl outline-none focus:border-[#224397] focus:ring-2 focus:ring-[#224397]/10 transition shadow-2xs cursor-pointer">
                            <option value="">-- Tất cả Niên khóa --</option>
                            <?php foreach ($nien_khoa_list as $nk): ?>
                                <option value="<?= htmlspecialchars($nk) ?>"><?= htmlspecialchars($nk) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Bot Zalo (Span 2) -->
                    <div class="lg:col-span-2">
                        <select id="filterBot" onchange="applyFilter()" class="w-full h-10 px-3 text-xs bg-white border border-slate-200 rounded-xl outline-none focus:border-[#224397] focus:ring-2 focus:ring-[#224397]/10 transition shadow-2xs cursor-pointer">
                            <option value="">-- Trạng thái Bot --</option>
                            <option value="connected">Đã kết nối</option>
                            <option value="not_connected">Chưa kết nối</option>
                        </select>
                    </div>

                    <!-- Action Buttons (Span 2) -->
                    <div class="lg:col-span-2 flex items-center gap-2">
                        <button type="button" onclick="resetFilters()" class="flex-1 h-10 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer" title="Xóa tất cả bộ lọc">
                            <i class="bi bi-arrow-counterclockwise text-sm"></i> Làm mới
                        </button>
                    </div>
                </div>
            </div>

            <!-- TABLE VIEW (9 COLUMNS) -->
            <div class="overflow-x-auto list-scrollbar max-h-[62vh]">
                <table class="w-full text-left text-xs text-slate-700 border-collapse" id="studentsTable">
                    <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200 sticky top-0 z-10">
                        <tr>
                            <th class="px-3.5 py-3 w-12 text-center">STT</th>
                            <th class="px-3.5 py-3 w-28">Niên khóa</th>
                            <th class="px-3.5 py-3 w-36 font-semibold">Số CCCD / Mã HS</th>
                            <th class="px-3.5 py-3 font-semibold">Họ và tên</th>
                            <th class="px-3.5 py-3 w-32">Lớp</th>
                            <th class="px-3.5 py-3 w-28 text-center">Ngày sinh</th>
                            <th class="px-3.5 py-3 w-48">ID tin khung chat Zalo</th>
                            <th class="px-3.5 py-3 w-40 text-center">Lần truy cập cuối</th>
                            <th class="px-3.5 py-3 w-28 text-center">Lượt truy cập</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="studentsTableBody">
                        <?php foreach ($students as $idx => $st): 
                            $isGraduated = ($st['trang_thai_hoc_tap'] === 'da_tot_nghiep');
                            $isDropped = ($st['trang_thai_hoc_tap'] === 'da_nghi_hoc');
                            $zaloChatId = $st['zalo_chat_id'] ?: $st['zalo_id'];
                            $hasZalo = !empty($zaloChatId);
                            $fullName = trim("{$st['ho_dem']} {$st['ten']}");
                            $lopName = $st['ten_lop'] ?: 'Chưa xếp lớp';
                        ?>
                        <tr class="student-row hover:bg-blue-50/40 transition"
                            data-name="<?= htmlspecialchars(mb_strtolower($fullName, 'UTF-8')) ?>"
                            data-cccd="<?= htmlspecialchars(strtolower($st['ma_hoc_sinh'])) ?>"
                            data-sdt="<?= htmlspecialchars($st['sdt'] ?? '') ?>"
                            data-zalo="<?= htmlspecialchars(strtolower($zaloChatId ?: '')) ?>"
                            data-lop="<?= htmlspecialchars($st['ten_lop'] ?? '') ?>"
                            data-nienkhoa="<?= htmlspecialchars($st['nien_khoa'] ?? '') ?>"
                            data-haszalo="<?= $hasZalo ? 'connected' : 'not_connected' ?>"
                            data-status="<?= htmlspecialchars($st['trang_thai_hoc_tap'] ?? 'dang_hoc') ?>"
                        >
                            <!-- 1. STT -->
                            <td class="px-3.5 py-2.5 text-center font-bold text-slate-400 row-stt">
                                <?= $idx + 1 ?>
                            </td>

                            <!-- 2. Niên khóa -->
                            <td class="px-3.5 py-2.5 font-medium text-slate-600">
                                <?= htmlspecialchars($st['nien_khoa'] ?: '-') ?>
                            </td>

                            <!-- 3. Số CCCD / Mã học sinh -->
                            <td class="px-3.5 py-2.5 font-bold text-slate-800 tracking-wide font-mono">
                                <?= htmlspecialchars($st['ma_hoc_sinh']) ?>
                            </td>

                            <!-- 4. Họ và tên -->
                            <td class="px-3.5 py-2.5 font-bold text-[#224397]">
                                <span><?= htmlspecialchars($fullName) ?></span>
                                <?php if ($st['gioi_tinh'] === 'Nữ'): ?>
                                    <span class="text-[10px] text-pink-500 font-normal ml-0.5">♀</span>
                                <?php else: ?>
                                    <span class="text-[10px] text-blue-500 font-normal ml-0.5">♂</span>
                                <?php endif; ?>
                            </td>

                            <!-- 5. Lớp (Hiện tại / Đã tốt nghiệp / Đã nghỉ học) -->
                            <td class="px-3.5 py-2.5">
                                <?php if ($isGraduated): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        🎓 Đã tốt nghiệp
                                    </span>
                                <?php elseif ($isDropped): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        ❌ Đã nghỉ học
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#224397] border border-blue-100">
                                        <?= htmlspecialchars($lopName) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 6. Ngày sinh -->
                            <td class="px-3.5 py-2.5 text-center font-medium text-slate-600">
                                <?= format_ddmmyyyy($st['ngay_sinh']) ?>
                            </td>

                            <!-- 7. ID tin khung chat Zalo -->
                            <td class="px-3.5 py-2.5 cell-zalo-id">
                                <?php if ($hasZalo): ?>
                                    <div class="inline-flex items-center gap-1.5 cursor-pointer group select-none" 
                                         ondblclick="confirmUnlinkZalo(<?= $st['id'] ?>, '<?= htmlspecialchars(addslashes($fullName)) ?>', '<?= htmlspecialchars($st['ma_hoc_sinh']) ?>', this)" 
                                         title="Nhấn đúp (Double-click) để xóa liên kết Zalo với học sinh này">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 animate-pulse"></span>
                                        <span class="font-mono text-[11px] font-bold text-emerald-700 bg-emerald-50 group-hover:bg-red-50 group-hover:text-red-600 group-hover:border-red-200 px-2 py-0.5 rounded-md border border-emerald-200/80 truncate max-w-[130px] transition">
                                            <?= htmlspecialchars($zaloChatId) ?>
                                        </span>
                                        <i class="bi bi-x-circle text-[12px] text-red-400 group-hover:text-red-600 transition" title="Double click để xóa"></i>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-block text-[10.5px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">
                                        Chưa liên kết
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 8. Lần truy cập Zalo Mini App cuối -->
                            <td class="px-3.5 py-2.5 text-center text-[11px] text-slate-500">
                                <?php if (!empty($st['zalo_last_active'])): ?>
                                    <span class="font-medium text-slate-700"><?= date('H:i d/m/Y', strtotime($st['zalo_last_active'])) ?></span>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- 9. Số lượt truy cập -->
                            <td class="px-3.5 py-2.5 text-center">
                                <?php if ($st['zalo_access_count'] > 0): ?>
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded-lg border border-indigo-100 text-[11px]">
                                        <?= number_format($st['zalo_access_count']) ?> lượt
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 font-medium">0</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr id="noResultsRow" class="hidden">
                            <td colspan="9" class="py-12 text-center text-slate-400 text-sm">
                                <i class="bi bi-inbox text-3xl block mb-2 opacity-50"></i>
                                Không tìm thấy học sinh nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TABLE FOOTER -->
            <div class="bg-slate-50 px-4 py-3 border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <span id="footerCount">Đang hiển thị <strong><?= count($students) ?></strong> học sinh</span>
                <span class="text-[11px] italic">Dữ liệu được cập nhật tức thì</span>
            </div>
        </div>

        <!-- CẤU HÌNH CHỈNH SỬA THÔNG TIN (ZALO MINI APP) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden flex flex-col">
            <div class="bg-slate-50/80 px-5 py-3.5 border-b border-slate-200 font-bold text-[#224397] flex items-center gap-2 text-sm uppercase">
                <i class="bi bi-sliders"></i> Cấu Hình Quyền Chỉnh Sửa Thông Tin (Zalo Mini App)
            </div>

            <div class="p-5">
                <form id="zaloSettingsForm" onsubmit="saveZaloSettings(event)" class="flex flex-col gap-4">
                    
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="allow_edit_profile" class="sr-only peer" <?= $allow_edit == '1' ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#224397]"></div>
                        </label>
                        <span class="text-sm font-bold text-slate-700">Cho phép học sinh chỉnh sửa thông tin</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="auto_approve_edit" class="sr-only peer" <?= $auto_approve == '1' ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                        <span class="text-sm font-bold text-slate-700">Tự động duyệt thông tin chỉnh sửa (không cần Admin duyệt)</span>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2.5">Các trường được phép sửa:</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                            <?php 
                            $all_fields = [
                                'anh_the' => 'Ảnh thẻ',
                                'chuc_vu' => 'Chức vụ',
                                'sdt' => 'Số điện thoại',
                                'email' => 'Email',
                                'dia_chi' => 'Địa chỉ (Tỉnh, Xã, Khu phố, Chi tiết)'
                            ];
                            foreach ($all_fields as $key => $label): 
                            ?>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50/50 transition">
                                <input type="checkbox" name="editable_fields[]" value="<?= $key ?>" class="w-4 h-4 text-[#224397] bg-white border-slate-300 rounded focus:ring-[#224397] accent-[#224397]" <?= in_array($key, $editable_fields) ? 'checked' : '' ?>>
                                <span><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-[#224397] hover:bg-[#1a367d] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer active:scale-95">
                            <i class="bi bi-save"></i> Lưu cấu hình
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
// ==========================================
// INSTANT LIVE FILTERING (NO PAGE RELOAD / NO IFRAME WHITE SCREEN)
// ==========================================
function applyFilter() {
    const q = (document.getElementById('searchInput').value || '').toLowerCase().trim();
    const lop = document.getElementById('filterLop').value;
    const nienKhoa = document.getElementById('filterNienKhoa').value;
    const bot = document.getElementById('filterBot').value;

    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) {
        clearBtn.classList.toggle('hidden', q.length === 0);
    }

    const rows = document.querySelectorAll('.student-row');
    let visibleCount = 0;

    rows.forEach((row) => {
        const name = row.getAttribute('data-name') || '';
        const cccd = row.getAttribute('data-cccd') || '';
        const sdt = row.getAttribute('data-sdt') || '';
        const zalo = row.getAttribute('data-zalo') || '';
        const rowLop = row.getAttribute('data-lop') || '';
        const rowNk = row.getAttribute('data-nienkhoa') || '';
        const rowHasZalo = row.getAttribute('data-haszalo') || '';

        let matchSearch = true;
        if (q) {
            matchSearch = name.includes(q) || cccd.includes(q) || sdt.includes(q) || zalo.includes(q);
        }

        let matchLop = true;
        if (lop) {
            matchLop = (rowLop === lop);
        }

        let matchNk = true;
        if (nienKhoa) {
            matchNk = (rowNk === nienKhoa);
        }

        let matchBot = true;
        if (bot) {
            matchBot = (rowHasZalo === bot);
        }

        if (matchSearch && matchLop && matchNk && matchBot) {
            row.style.display = '';
            visibleCount++;
            const sttCell = row.querySelector('.row-stt');
            if (sttCell) sttCell.textContent = visibleCount;
        } else {
            row.style.display = 'none';
        }
    });

    const noResultsRow = document.getElementById('noResultsRow');
    if (noResultsRow) {
        noResultsRow.classList.toggle('hidden', visibleCount > 0);
    }

    const filterCount = document.getElementById('filterCount');
    if (filterCount) {
        filterCount.textContent = visibleCount + ' học sinh';
    }

    const footerCount = document.getElementById('footerCount');
    if (footerCount) {
        footerCount.innerHTML = 'Đang hiển thị <strong>' + visibleCount + '</strong> học sinh';
    }
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    applyFilter();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterLop').value = '';
    document.getElementById('filterNienKhoa').value = '';
    document.getElementById('filterBot').value = '';
    applyFilter();
}

function saveZaloSettings(e) {
    e.preventDefault();
    const allow_edit = document.getElementById('allow_edit_profile').checked ? '1' : '0';
    const auto_approve = document.getElementById('auto_approve_edit').checked ? '1' : '0';
    const editable_fields = Array.from(document.querySelectorAll('input[name="editable_fields[]"]:checked')).map(cb => cb.value);

    fetch('/thidua/api/quan-ly-mau-the', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'save_zalo_settings',
            allow_edit: allow_edit,
            auto_approve: auto_approve,
            editable_fields: editable_fields
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            if (typeof showToast === 'function') {
                showToast('Đã lưu cấu hình chỉnh sửa thông tin thành công.', 'success');
            } else if (typeof AppSwal !== 'undefined') {
                AppSwal.fire({ icon: 'success', title: 'Thành công', text: 'Đã lưu cấu hình.', showConfirmButton: false, timer: 1500 });
            } else {
                alert('Đã lưu cấu hình thành công.');
            }
        } else {
            alert(res.message || 'Đã có lỗi xảy ra.');
        }
    })
    .catch(err => {
        alert('Lỗi kết nối máy chủ.');
    });
}

function confirmUnlinkZalo(studentId, fullName, cccd, element) {
    const swalConfig = {
        title: 'Xóa liên kết Zalo?',
        html: `Bạn có muốn xóa liên kết Zalo với học sinh <strong>${fullName}</strong> (CCCD: <code>${cccd}</code>) không?<br><br><span class="text-xs text-slate-500">Sau khi xóa, học sinh có thể đăng ký lại tài khoản Zalo mới.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý xóa',
        cancelButtonText: 'Hủy bỏ',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b'
    };

    const doUnlink = () => {
        fetch('/thidua/api/zalo-bot-webhook', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'unlink_zalo_bot',
                student_id: studentId,
                ma_hoc_sinh: cccd
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                // Cập nhật giao diện dòng học sinh ngay lập tức
                const row = element.closest('.student-row');
                if (row) {
                    row.setAttribute('data-haszalo', 'not_connected');
                    row.setAttribute('data-zalo', '');
                    const cell = row.querySelector('.cell-zalo-id');
                    if (cell) {
                        cell.innerHTML = '<span class="inline-block text-[10.5px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">Chưa liên kết</span>';
                    }
                }

                // Cập nhật thống kê
                const statConnected = document.getElementById('statConnected');
                const statNotConnected = document.getElementById('statNotConnected');
                if (statConnected && statNotConnected) {
                    let c = parseInt(statConnected.innerText.replace(/,/g, '')) || 0;
                    let nc = parseInt(statNotConnected.innerText.replace(/,/g, '')) || 0;
                    if (c > 0) {
                        statConnected.innerText = (c - 1).toLocaleString('en-US');
                        statNotConnected.innerText = (nc + 1).toLocaleString('en-US');
                    }
                }

                if (typeof showToast === 'function') {
                    showToast('Đã xóa liên kết Zalo thành công.', 'success');
                } else if (typeof AppSwal !== 'undefined') {
                    AppSwal.fire({ icon: 'success', title: 'Thành công', text: 'Đã xóa liên kết Zalo của học sinh.', timer: 1500, showConfirmButton: false });
                } else {
                    alert('Đã xóa liên kết Zalo thành công.');
                }
            } else {
                alert(res.message || 'Lỗi khi xóa liên kết.');
            }
        })
        .catch(err => {
            alert('Lỗi kết nối máy chủ.');
        });
    };

    if (typeof AppSwal !== 'undefined') {
        AppSwal.fire(swalConfig).then(result => {
            if (result.isConfirmed) doUnlink();
        });
    } else if (typeof Swal !== 'undefined') {
        Swal.fire(swalConfig).then(result => {
            if (result.isConfirmed) doUnlink();
        });
    } else {
        if (confirm(`Bạn có chắc chắn muốn xóa liên kết Zalo với học sinh "${fullName}" (CCCD: ${cccd}) không?`)) {
            doUnlink();
        }
    }
}
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
