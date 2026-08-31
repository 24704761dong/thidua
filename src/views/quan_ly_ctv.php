<?php
$page_title = 'Quản Lý Trang Cộng Tác Viên';
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="container-fluid p-2">
    <style>
    /* Kẻ ngang full hàng cho HS nghỉ học */
    .row-strike-through td {
        position: relative;
    }
    .row-strike-through td::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        border-top: 1.5px solid #94a3b8; /* Slate-400 */
        z-index: 10;
        pointer-events: none;
    }
</style>
<div class="w-full px-2 lg:px-6">
    <!-- Filter và Buttons trên 1 hàng -->
    <div class="flex flex-row items-end justify-between gap-2 mt-1 mb-2">
        <!-- Filter Form (Bên trái) -->
        <form id="filterFormCTV" action="/thidua/admin/ctv" method="GET" class="flex flex-row items-end gap-2 m-0">
            <?php if(isset($_GET['iframe'])) echo '<input type="hidden" name="iframe" value="1">'; ?>
            <div>
                <label class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Tên / Mã HS</label>
                <input type="text" name="keyword" class="block w-36 rounded border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px] outline-none" placeholder="Nhập tên..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
            </div>
            <div>
                <label class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Khối</label>
                <select name="khoi" class="block w-20 rounded border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px] outline-none">
                    <option value="all">Tất cả</option>
                    <option value="10" <?php echo (isset($_GET['khoi']) && $_GET['khoi'] == '10') ? 'selected' : ''; ?>>Khối 10</option>
                    <option value="11" <?php echo (isset($_GET['khoi']) && $_GET['khoi'] == '11') ? 'selected' : ''; ?>>Khối 11</option>
                    <option value="12" <?php echo (isset($_GET['khoi']) && $_GET['khoi'] == '12') ? 'selected' : ''; ?>>Khối 12</option>
                </select>
            </div>
            <div>
                <label class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Lớp</label>
                <select name="lop_id" class="block w-24 rounded border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px] outline-none">
                    <option value="all">Tất cả</option>
                    <?php if(isset($danh_sach_lop)): foreach ($danh_sach_lop as $lop): ?>
                        <option value="<?php echo htmlspecialchars($lop['id']); ?>" <?php echo (isset($_GET['lop_id']) && $_GET['lop_id'] == $lop['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lop['ten_lop']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Chức vụ</label>
                <select name="chuc_vu" class="block w-24 rounded border border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px] outline-none">
                    <option value="all">Tất cả</option>
                    <?php if(isset($danh_sach_chuc_vu)): foreach ($danh_sach_chuc_vu as $cv): ?>
                        <option value="<?php echo htmlspecialchars($cv); ?>" <?php echo (isset($_GET['chuc_vu']) && $_GET['chuc_vu'] == $cv) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cv); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="flex items-center mb-1">
                <input type="checkbox" name="has_permission" value="1" id="has_permission_filter" class="rounded border-slate-300 text-[#224397] shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" <?php echo (isset($_GET['has_permission']) && $_GET['has_permission'] === '1') ? 'checked' : ''; ?>>
                <label for="has_permission_filter" class="ml-1 block text-[13px] font-bold text-[#224397] mr-1">Chỉ CTV</label>
            </div>
        </form>

        <!-- Action Buttons (Bên phải) -->
        <div class="flex items-center gap-1.5 flex-wrap justify-end max-w-full">
            <a href="/thidua/admin/ctv?action=manage_codes" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> MÃ CẤP QUYỀN</a>
            
            <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="openLocalModal('quickGrantModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg> Cấp quyền</button>

            <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="openLocalModal('quickRevokeModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-slash-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.093 3.093c-.465 4.275.885 7.46 2.513 9.589a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.3 11.3 0 0 0 1.733-1.525zm12.215 8.215L3.128 1.128A61 61 0 0 1 5.073.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.483 3.626-.332 6.491-1.551 8.616m.338 3.046-13-13 .708-.708 13 13z"/></svg> Thu hồi</button>

            <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="openLocalModal('provisionAccountModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg> Cấp TK</button>

            <a href="/thidua/quan-ly-dang-ky-truc" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-check-fill" viewBox="0 0 16 16"><path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2m-5.146-5.146-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg> DS Trực</a>

            <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="openLocalModal('exportCtvOptionsModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Excel</button>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded shadow border border-[#224397]/25 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center gap-2">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge-fill mr-2" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/></svg>
                DANH SÁCH CỘNG TÁC VIÊN
            </h3>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto list-scrollbar w-full">
                <table id="ctvStudentTable" class="w-full text-left text-[13px] text-slate-700 font-medium">
                    <thead class="bg-white border-b border-[#224397]/25 text-[12px] uppercase font-bold text-[#224397] sticky top-0 z-10">
                        <tr>
                            <th class="p-3 text-center border-r border-[#224397]/20 w-12">STT</th>
                            <th class="p-0 text-center border-r border-[#224397]/20 w-[60px]">Ảnh</th>
                            <th class="p-3 text-center border-r border-[#224397]/20">Niên khóa</th>
                            <th class="p-3 text-center border-r border-[#224397]/20">Lớp</th>
                            <th class="p-3 text-center border-r border-[#224397]/20">Số CCCD</th>
                            <th class="p-3 border-r border-[#224397]/20">Họ và Tên</th>
                            <th class="p-3 text-center border-r border-[#224397]/20">Ngày sinh</th>
                            <th class="p-3 text-center border-r border-[#224397]/20">Chức vụ</th>
                            <th class="p-3 text-center border-r border-[#224397]/20">SĐT</th>
                            <th class="p-3 border-r border-[#224397]/20">Gmail</th>
                            <th class="p-3 border-r border-[#224397]/20">Địa chỉ</th>
                            <th class="p-3 text-center border-r border-[#224397]/20">Liên kết Google</th>
                            <th class="p-3 text-center border-r border-[#224397]/20">Trạng thái TK</th>
                            <th class="p-3 text-center w-36">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#224397]/20">
                        <?php if (isset($danh_sach_hoc_sinh) && count($danh_sach_hoc_sinh) > 0): ?>
                            <?php foreach ($danh_sach_hoc_sinh as $index => $hs): 
                                $is_nghi = (($hs['trang_thai_hoc_tap'] ?? '') === 'nghi_hoc');
                                $row_class = $is_nghi ? 'bg-slate-50 opacity-70' : 'hover:bg-blue-50/50 transition-colors bg-white';
                                $offset = $pagination['offset'] ?? 0;
                                $quyen_json = json_decode($hs['quyen_truy_cap'] ?? '{}', true) ?: [];
                                $hs['quyen_nhap_vi_pham'] = $quyen_json['nhap_vi_pham'] ?? false;
                                $hs['quyen_dang_ky_truc'] = $quyen_json['dang_ky_truc'] ?? false;
                                $hs['quyen_so_nhat_ky_online'] = $quyen_json['so_nhat_ky_online'] ?? false;
                            ?>
                                <tr class="<?php echo $row_class; ?>" <?php echo $is_nghi ? 'title="Học sinh đã nghỉ học"' : ''; ?>>
                                    <td class="p-3 text-center border-r border-[#224397]/20"><?php echo $offset + $index + 1; ?></td>
                                    <td class="p-0 text-center border-r border-[#224397]/20 align-middle w-[60px] h-[80px]">
                                        <img loading="lazy" src="<?php echo htmlspecialchars(get_student_avatar_url($hs['anh_the'] ?? '', $hs['anh_the_driver'] ?? 'local', $hs['anh_the_cloud_key'] ?? null)); ?>" alt="Avatar" class="w-[60px] h-[80px] object-cover block mx-auto" onerror="this.src='/thidua/public/assets/img/anhthegoc.JPG'">
                                    </td>
                                    <td class="p-3 text-center border-r border-[#224397]/20 font-semibold"><?php echo htmlspecialchars($hs['nien_khoa'] ?? ''); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/20 text-slate-700 font-medium"><?php echo htmlspecialchars($hs['ten_lop'] ?? ''); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/20 text-slate-700 font-medium"><?php echo htmlspecialchars($hs['ma_hoc_sinh'] ?? ''); ?></td>
                                    <td class="p-3 border-r border-[#224397]/20 text-slate-700 font-medium <?php echo $is_nghi ? 'line-through text-slate-400' : ''; ?>">
                                        <div class="flex items-center gap-1">
                                            <span><?php echo htmlspecialchars(($hs['ho_dem'] ?? '') . ' ' . ($hs['ten'] ?? '')); ?></span>
                                            <?php if (($hs['trang_thai_hoc_tap'] ?? '') === 'da_tot_nghiep'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-green-600 shrink-0" viewBox="0 0 16 16" title="Học sinh đã tốt nghiệp">
                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center border-r border-[#224397]/20 text-slate-600"><?php echo isset($hs['ngay_sinh']) ? date('d/m/Y', strtotime(str_replace('/', '-', $hs['ngay_sinh']))) : ''; ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/20 text-slate-600"><?php echo htmlspecialchars($hs['chuc_vu'] ?? ''); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/20 text-slate-600"><?php echo htmlspecialchars($hs['sdt'] ?? ''); ?></td>
                                    <td class="p-3 border-r border-[#224397]/20 text-slate-600"><?php echo htmlspecialchars($hs['email'] ?? ''); ?></td>
                                    <td class="p-3 border-r border-[#224397]/20 text-slate-600">
                                        <?php 
                                        $dia_chi_parts = [];
                                        if (!empty($hs['dia_chi_chi_tiet'])) $dia_chi_parts[] = $hs['dia_chi_chi_tiet'];
                                        if (!empty($hs['ap_khupho'])) $dia_chi_parts[] = $hs['ap_khupho'];
                                        if (!empty($hs['xa_phuong'])) $dia_chi_parts[] = $hs['xa_phuong'];
                                        if (!empty($hs['tinh_thanhpho'])) $dia_chi_parts[] = $hs['tinh_thanhpho'];
                                        echo htmlspecialchars(implode(', ', $dia_chi_parts));
                                        ?>
                                    </td>
                                    <td class="p-3 text-center border-r border-[#224397]/20">
                                        <?php if (!empty($hs['google_id'])): ?>
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-[11px] font-semibold flex items-center justify-center gap-1 mx-auto w-fit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-google" viewBox="0 0 16 16"><path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.06c.632 1.896 2.405 3.304 4.492 3.304 1.944 0 3.44-.7 4.09-1.85a3.8 3.8 0 0 0 .19-1.99H8v-2.735z"/></svg> Đã LK</span>
                                                <span class="text-[10px] text-slate-500"><?php echo htmlspecialchars($hs['verified_email'] ?? $hs['email'] ?? ''); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-xs italic">Chưa LK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-center border-r border-[#224397]/20">
                                        <?php if (($hs['trang_thai_tai_khoan'] ?? '') === 'Đã cấp TK' || ($hs['trang_thai_tai_khoan'] ?? '') === 'Đã đổi MK' || ($hs['trang_thai_tai_khoan'] ?? '') === 'active'): ?>
                                            <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-[11px] font-semibold w-fit mx-auto flex"><?php echo (($hs['trang_thai_tai_khoan'] ?? '') === 'active' ? 'Đã cấp TK' : htmlspecialchars($hs['trang_thai_tai_khoan'])); ?></span>
                                            <div class="mt-2">
                                                <button type="button" class="px-2 py-1 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white transition-colors text-[10px] font-medium shadow-sm" onclick="showResetPasswordModal(<?php echo $hs['id']; ?>, '<?php echo htmlspecialchars(addslashes(($hs['ho_dem'] ?? '') . ' ' . ($hs['ten'] ?? ''))); ?>')">Reset Pass</button>
                                            </div>
                                        <?php elseif (($hs['trang_thai_tai_khoan'] ?? '') === 'Khóa' || ($hs['trang_thai_tai_khoan'] ?? '') === 'locked'): ?>
                                            <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-[11px] font-semibold w-fit mx-auto flex">Khóa</span>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-xs italic">Chưa có TK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-2 align-middle w-32">
                                        <div class="grid grid-cols-2 gap-1.5 text-[11px] text-[#224397] font-semibold w-full">
                                            <label class="col-span-2 flex items-center justify-center gap-1.5 cursor-pointer bg-slate-50 px-2 py-1.5 rounded hover:bg-blue-50 transition-colors w-full border border-slate-200 shadow-sm" title="Nhập vi phạm">
                                                <input type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 permission-toggle" data-id="<?php echo $hs['id']; ?>" data-perm="nhap_vi_pham" <?php echo !empty($hs['quyen_nhap_vi_pham']) ? 'checked' : ''; ?>> VP
                                            </label>
                                            <label class="flex items-center justify-start gap-1.5 cursor-pointer bg-slate-50 px-2 py-1.5 rounded hover:bg-blue-50 transition-colors w-full border border-slate-200 shadow-sm" title="Đăng ký trực">
                                                <input type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 permission-toggle" data-id="<?php echo $hs['id']; ?>" data-perm="dang_ky_truc" <?php echo !empty($hs['quyen_dang_ky_truc']) ? 'checked' : ''; ?>> T
                                            </label>
                                            <label class="flex items-center justify-start gap-1.5 cursor-pointer bg-slate-50 px-2 py-1.5 rounded hover:bg-blue-50 transition-colors w-full border border-slate-200 shadow-sm" title="Sổ Nhật kỳ">
                                                <input type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 permission-toggle" data-id="<?php echo $hs['id']; ?>" data-perm="so_nhat_ky_online" <?php echo !empty($hs['quyen_so_nhat_ky_online']) ? 'checked' : ''; ?>> NK
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="13" class="text-center py-10 text-slate-500 italic">Không tìm thấy dữ liệu phù hợp.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Phân trang -->
    <div id="ctvPaginationWrapper">
    <?php if (isset($pagination) && ($pagination['total_pages'] ?? 1) > 1):
        $qs = $_GET; unset($qs['page']); $base = '/thidua/admin/ctv?'.http_build_query($qs);
        $cur = $pagination['page']; $totalPages = $pagination['total_pages'];
    ?>
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-sm text-slate-500">
            Tổng cộng: <span class="font-bold text-[#224397]"><?php echo number_format($pagination['total']); ?></span> học sinh — Trang <?php echo $cur; ?>/<?php echo $totalPages; ?>
        </p>
        <div class="flex items-center gap-1">
            <a href="<?php echo $base.'&page='.max(1,$cur-1); ?>" class="px-3 py-1.5 border border-slate-300 rounded bg-white text-slate-600 hover:bg-slate-50 <?php echo $cur<=1?'opacity-50 pointer-events-none':''; ?>">«</a>
            <?php
                $start = max(1, $cur-2); $end = min($totalPages, $cur+2);
                for ($p=$start; $p<=$end; $p++):
            ?>
                <a href="<?php echo $base.'&page='.$p; ?>" class="px-3 py-1.5 border <?php echo $p==$cur?'bg-[#224397] text-white border-[#224397]':'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'; ?> rounded font-medium"><?php echo $p; ?></a>
            <?php endfor; ?>
            <a href="<?php echo $base.'&page='.min($totalPages,$cur+1); ?>" class="px-3 py-1.5 border border-slate-300 rounded bg-white text-slate-600 hover:bg-slate-50 <?php echo $cur>=$totalPages?'opacity-50 pointer-events-none':''; ?>">»</a>
        </div>
    </div>
    <?php endif; ?>
    </div>
</div>

<!-- ================== MODALS ================== -->

<!-- Modal Cấp Quyền Hàng Loạt -->
<div id="quickGrantModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0 max-h-[90vh]">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-6 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-plus mr-2" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/><path d="M8 4.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V9a.5.5 0 0 1-1 0V7.5H6a.5.5 0 0 1 0-1h1.5V5a.5.5 0 0 1 .5-.5"/></svg>
                Cấp Quyền Hàng Loạt
            </h5>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="closeLocalModal('quickGrantModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
            <form id="quickGrantForm">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">1. Chọn đối tượng áp dụng:</label>
                    <select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" id="target-type-select" name="target_type">
                        <option value="chuc_vu">Theo Chức vụ</option>
                        <option value="lop">Theo Lớp</option>
                    </select>
                </div>

                <div id="target-value-container" class="mt-3 p-4 bg-slate-50 border border-[#224397]/10 rounded shadow-inner mb-6">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">2. Chọn các quyền để cấp:</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input class="rounded-lg border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-4 h-4 cursor-pointer" type="checkbox" value="1" name="permissions[nhap_vi_pham]" id="grant_nhap_vi_pham">
                            <label class="ml-2 block text-sm text-slate-900 cursor-pointer" for="grant_nhap_vi_pham">Quyền Nhập Vi Phạm</label>
                        </div>
                        <div class="flex items-center">
                            <input class="rounded-lg border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-4 h-4 cursor-pointer" type="checkbox" value="1" name="permissions[dang_ky_truc]" id="grant_dang_ky_truc">
                            <label class="ml-2 block text-sm text-slate-900 cursor-pointer" for="grant_dang_ky_truc">Quyền Đăng Ký Trực</label>
                        </div>
                        <div class="flex items-center">
                            <input class="rounded-lg border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-4 h-4 cursor-pointer" type="checkbox" value="1" name="permissions[so_nhat_ky_online]" id="grant_so_nhat_ky_online">
                            <label class="ml-2 block text-sm text-slate-900 cursor-pointer" for="grant_so_nhat_ky_online">Quyền Sổ Nhật kỳ Online</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex justify-end gap-3 shrink-0">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 text-sm" onclick="closeLocalModal('quickGrantModal')">Hủy</button>
            <button type="submit" form="quickGrantForm" class="px-5 py-2.5 bg-[#224397] text-white rounded font-medium hover:bg-blue-800 transition shadow-sm hover:shadow flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg>
                Cấp Quyền
            </button>
        </div>
    </div>
</div>

<!-- Modal Thu Hồi Quyền -->
<div id="quickRevokeModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0 max-h-[90vh]">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-6 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-red-600 font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-slash-fill mr-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.093 3.093c-.465 4.275.885 7.46 2.513 9.589a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.3 11.3 0 0 0 1.733-1.525zm12.215 8.215L3.128 1.128A61 61 0 0 1 5.073.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.483 3.626-.332 6.491-1.551 8.616m.338 3.046-13-13 .708-.708 13 13z"/></svg>
                Thu Hồi Quyền Hàng Loạt
            </h5>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="closeLocalModal('quickRevokeModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
            <form id="quickRevokeForm">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">1. Chọn đối tượng bị thu hồi:</label>
                    <select name="target_type" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm">
                        <option value="all">Toàn bộ học sinh</option>
                        <option value="lop">Theo Lớp</option>
                        <option value="chuc_vu">Theo Chức vụ</option>
                    </select>
                </div>
                <div id="revoke-target-value-container" class="mt-3 p-4 bg-slate-50 border border-[#224397]/10 rounded shadow-inner mb-6" style="display: none;"></div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">2. Chọn hành động thu hồi:</label>
                    <select name="revoke_action" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm">
                        <option value="all">Thu hồi toàn bộ quyền</option>
                        <option value="nhap_vi_pham">Chỉ thu hồi quyền Nhập vi phạm</option>
                        <option value="dang_ky_truc">Chỉ thu hồi quyền Đăng ký trực</option>
                        <option value="so_nhat_ky_online">Chỉ thu hồi quyền Sổ Nhật kỳ</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex justify-end gap-3 shrink-0">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300 text-sm" onclick="closeLocalModal('quickRevokeModal')">Hủy</button>
            <button type="submit" form="quickRevokeForm" class="px-5 py-2.5 bg-red-600 text-white rounded font-medium hover:bg-red-700 transition shadow-sm hover:shadow flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-slash" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.093 3.093c-.465 4.275.885 7.46 2.513 9.589a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.3 11.3 0 0 0 1.733-1.525zm12.215 8.215L3.128 1.128A61 61 0 0 1 5.073.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.483 3.626-.332 6.491-1.551 8.616m.338 3.046-13-13 .708-.708 13 13z"/></svg>
                Xác Nhận Thu Hồi
            </button>
        </div>
    </div>
</div>

<!-- Modal Cấp Tài Khoản (Provision) -->
<div id="provisionAccountModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[500px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0 max-h-[90vh]">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg>
                Cấp Tài Khoản Hàng Loạt
            </h5>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="closeLocalModal('provisionAccountModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div class="p-5 overflow-y-auto list-scrollbar">
            <div class="mb-4 text-sm text-slate-600 bg-blue-50 border border-blue-200 p-3 rounded">
                <strong>Lưu ý:</strong> Hệ thống sẽ tạo tài khoản cho các Học Sinh chưa có tài khoản. Mật khẩu mặc định là <code class="font-bold text-[#224397]">Ngày tháng năm sinh (ddmmyyyy)</code>.
            </div>
            <form id="provisionForm">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Phạm vi cấp tài khoản:</label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="scope" value="all" checked class="w-4 h-4 text-[#224397] border-slate-300 focus:ring-[#224397]"> Toàn trường
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="scope" value="class" class="w-4 h-4 text-[#224397] border-slate-300 focus:ring-[#224397]"> Chọn theo lớp
                        </label>
                    </div>
                </div>
                <div id="class-select-container" class="mb-4" style="display: none;">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Chọn Lớp:</label>
                    <select name="lop_id" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] outline-none transition-colors">
                        <option value="">-- Chọn lớp --</option>
                        <?php if(isset($danh_sach_lop)): foreach ($danh_sach_lop as $lop): ?>
                            <option value="<?php echo $lop['id']; ?>"><?php echo htmlspecialchars($lop['ten_lop']); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                
                <div id="provisionProgressWrapper" style="display: none;">
                    <div class="w-full bg-slate-200 rounded-full h-2.5 mb-2 mt-4">
                        <div id="provisionProgressBar" class="bg-[#224397] h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                    <div id="provisionProgressText" class="text-xs text-slate-500 font-semibold text-center">Đang xử lý: 0%</div>
                </div>
            </form>
        </div>
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300" onclick="closeLocalModal('provisionAccountModal')">Hủy</button>
            <button type="submit" form="provisionForm" id="startProvisionBtn" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300">Bắt đầu cấp TK</button>
        </div>
    </div>
</div>

<!-- Modal Reset Mật Khẩu -->
<div id="resetPasswordModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[400px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-red-600 font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
                Reset Mật Khẩu
            </h5>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="closeLocalModal('resetPasswordModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div class="p-5">
            <p class="text-sm text-slate-600 mb-2">Bạn có chắc chắn muốn reset mật khẩu cho học sinh <strong id="studentNameInModal" class="text-red-600"></strong>?</p>
            <p class="text-xs text-slate-500 italic">Mật khẩu mới sẽ là: <strong>Abc@1234</strong></p>
            <input type="hidden" id="resetStudentId">
        </div>
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300" onclick="closeLocalModal('resetPasswordModal')">Hủy</button>
            <button type="button" id="confirmResetBtn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300">Xác Nhận Reset</button>
        </div>
    </div>
</div>

<!-- Modal Tùy Chọn Xuất Excel -->
<div id="exportCtvOptionsModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[600px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0 max-h-[90vh]">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-columns-gap" viewBox="0 0 16 16"><path d="M6 1v3H1V1zM1 0a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm14 12v3h-5v-3zm-5-1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM6 8v7H1V8zM1 7a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1zm14-6v7h-5V1zm-5-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1z"/></svg>
                Tùy Chọn Xuất Excel CTV
            </h5>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="closeLocalModal('exportCtvOptionsModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div class="p-5 overflow-y-auto list-scrollbar">
            <form id="exportCtvOptionsForm">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Phạm vi dữ liệu:</label>
                    <select name="export_scope" class="block w-full rounded border border-slate-300 text-sm p-2 shadow-sm focus:border-[#224397] outline-none transition-colors">
                        <option value="all">Tất cả học sinh</option>
                        <option value="has_permission" selected>Chỉ những học sinh ĐÃ ĐƯỢC CẤP QUYỀN CTV</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Chọn các cột cần xuất:</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="khoi" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Khối</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="lop" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Lớp</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="ma_hs" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Số CCCD</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="ho_ten" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Họ và Tên</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="ngay_sinh" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Ngày sinh</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="gioi_tinh" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Giới tính</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="chuc_vu" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Chức vụ</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="sdt" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> SĐT</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="gmail" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Gmail</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="trang_thai_tk" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Trạng thái TK</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="quyen_vp" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Q.Nhập VP</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="quyen_dd" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Q.Điểm danh</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="quyen_truc" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Q.Đăng ký trực</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="quyen_nhat_ky" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Q.Sổ nhật kỳ</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" name="columns[]" value="ghi_chu" checked class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]"> Ghi chú</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300" onclick="closeLocalModal('exportCtvOptionsModal')">Hủy</button>
            <button type="submit" form="exportCtvOptionsForm" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Xác Nhận Xuất Excel</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
// JS Helper to open/close modals properly
function openLocalModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('.modal-content').classList.remove('opacity-0', 'scale-95', 'translate-y-4');
        modal.querySelector('.modal-content').classList.add('opacity-100', 'scale-100', 'translate-y-0');
    }, 10);
}
function closeLocalModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('opacity-0');
    modal.querySelector('.modal-content').classList.remove('opacity-100', 'scale-100', 'translate-y-0');
    modal.querySelector('.modal-content').classList.add('opacity-0', 'scale-95', 'translate-y-4');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Reset Password Logic
function showResetPasswordModal(id, name) {
    document.getElementById('resetStudentId').value = id;
    document.getElementById('studentNameInModal').textContent = name;
    openLocalModal('resetPasswordModal');
}

document.addEventListener('DOMContentLoaded', function() {
    // Permission Toggles
    document.querySelectorAll('.permission-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = this.dataset.id;
            const perm = this.dataset.perm;
            const action = this.checked ? 'grant' : 'revoke';
            
            fetch('/thidua/admin/ctv?action=api_toggle_permission', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, permission: perm, action })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    if(typeof showToast==='function') showToast('error', data.message);
                    else alert(data.message);
                    this.checked = !this.checked; // revert
                }
            })
            .catch(() => {
                this.checked = !this.checked; // revert
            });
        });
    });

    // Toggle All Button
    document.querySelectorAll('.toggle-all-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const action = this.dataset.action; // 'grant' or 'revoke'
            
            fetch('/thidua/admin/ctv?action=api_toggle_permission', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, permission: 'all', action })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    if(typeof showToast==='function') showToast('error', data.message);
                    else alert(data.message);
                }
            });
        });
    });

    // Reset Password
    document.getElementById('confirmResetBtn').addEventListener('click', function() {
        const id = document.getElementById('resetStudentId').value;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
        
        fetch('/thidua/admin/tai-khoan?action=api_reset_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if(typeof showToast==='function') showToast('success', data.message);
                else alert(data.message);
                closeLocalModal('resetPasswordModal');
            } else {
                if(typeof showToast==='function') showToast('error', data.message);
                else alert(data.message);
            }
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = 'Xác Nhận Reset';
        });
    });

    // Export Excel
    document.getElementById('exportCtvOptionsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData).toString();
        
        if (!params.includes('columns')) {
            if(typeof showToast==='function') showToast('warning', 'Bạn phải chọn ít nhất một cột để xuất!');
            else alert('Bạn phải chọn ít nhất một cột để xuất!');
            return;
        }

        const finalUrl = `/thidua/admin/ctv?action=export_accounts&${params}`;
        window.location.href = finalUrl;
        closeLocalModal('exportCtvOptionsModal');
    });

    // Quick Grant
    const targetTypeSelect = document.getElementById('target-type-select');
    const targetValueContainer = document.getElementById('target-value-container');
    const quickGrantForm = document.getElementById('quickGrantForm');

    const optionsHtml = {
        chuc_vu: `<?php echo '<label class="block text-sm font-medium text-slate-700 mb-1">Chọn chức vụ cụ thể:</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_value"><option value="">-- Chọn chức vụ --</option>'; if(isset($danh_sach_chuc_vu)){foreach($danh_sach_chuc_vu as $cv) { echo "<option value=\'".htmlspecialchars($cv)."\'>".htmlspecialchars($cv)."</option>"; }} echo '</select>'; ?>`,
        lop: `<?php echo '<label class="block text-sm font-medium text-slate-700 mb-1">Chọn lớp cụ thể:</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_value"><option value="">-- Chọn lớp --</option>'; if(isset($danh_sach_lop)){foreach($danh_sach_lop as $l) { echo "<option value=\'".$l['id']."\'>".htmlspecialchars($l['ten_lop'])."</option>"; }} echo '</select>'; ?>`
    };

    targetTypeSelect.addEventListener('change', function() {
        targetValueContainer.innerHTML = optionsHtml[this.value] || '';
    });
    targetValueContainer.innerHTML = optionsHtml[targetTypeSelect.value] || '';

    quickGrantForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            target_type: formData.get('target_type'),
            target_value: formData.get('target_value'),
            permissions: {
                nhap_vi_pham: formData.has('permissions[nhap_vi_pham]'),
                nhap_diem_danh: formData.has('permissions[nhap_diem_danh]'),
                dang_ky_truc: formData.has('permissions[dang_ky_truc]'),
                so_nhat_ky_online: formData.has('permissions[so_nhat_ky_online]')
            }
        };

        if (!data.target_value) {
            alert('Vui lòng chọn đối tượng áp dụng.');
            return;
        }

        fetch('/thidua/admin/ctv?action=api_bulk_grant_permissions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(apiResult => {
            closeLocalModal('quickGrantModal');
            if(typeof showToast === 'function') {
                showToast(apiResult.success ? 'success' : 'error', apiResult.message);
                if (apiResult.success) setTimeout(() => window.location.reload(), 1500);
            } else {
                AppSwal.fire(
                    apiResult.success ? 'Thành công!' : 'Lỗi!',
                    apiResult.message,
                    apiResult.success ? 'success' : 'error'
                ).then(() => {
                    if (apiResult.success) window.location.reload();
                });
            }
        });
    });

    // Quick Revoke
    const quickRevokeForm = document.getElementById('quickRevokeForm');
    const revokeTargetTypeSelect = quickRevokeForm.querySelector('select[name="target_type"]');
    const revokeTargetValueContainer = document.getElementById('revoke-target-value-container');

    const revokeOptionsHtml = {
        chuc_vu: `<?php echo '<label class="block text-sm font-medium text-slate-700 mb-1">Chọn chức vụ cụ thể:</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_value"><option value="">-- Chọn chức vụ --</option>'; if(isset($danh_sach_chuc_vu)){foreach($danh_sach_chuc_vu as $cv) { echo "<option value=\'".htmlspecialchars($cv)."\'>".htmlspecialchars($cv)."</option>"; }} echo '</select>'; ?>`,
        lop: `<?php echo '<label class="block text-sm font-medium text-slate-700 mb-1">Chọn lớp cụ thể:</label><select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397]/50 focus:border-[#224397] outline-none transition text-sm" name="target_value"><option value="">-- Chọn lớp --</option>'; if(isset($danh_sach_lop)){foreach($danh_sach_lop as $l) { echo "<option value=\'".$l['id']."\'>".htmlspecialchars($l['ten_lop'])."</option>"; }} echo '</select>'; ?>`
    };

    revokeTargetTypeSelect.addEventListener('change', function() {
        if (this.value === 'lop' || this.value === 'chuc_vu') {
            revokeTargetValueContainer.innerHTML = revokeOptionsHtml[this.value];
            revokeTargetValueContainer.style.display = 'block';
        } else {
            revokeTargetValueContainer.style.display = 'none';
        }
    });

    quickRevokeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            target_type: formData.get('target_type'),
            target_value: formData.get('target_value'),
            revoke_action: formData.get('revoke_action')
        };
        
        closeLocalModal('quickRevokeModal');
        AppSwal.fire({
            title: 'Cảnh Báo Thu Hồi Quyền!',
            text: 'Bạn có chắc chắn muốn thực hiện hành động thu hồi quyền này không? Hệ thống sẽ gỡ bỏ các quyền đã chọn của những đối tượng trong phạm vi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<div class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.093 3.093c-.465 4.275.885 7.46 2.513 9.589a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.3 11.3 0 0 0 1.733-1.525zm12.215 8.215L3.128 1.128A61 61 0 0 1 5.073.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.483 3.626-.332 6.491-1.551 8.616m.338 3.046-13-13 .708-.708 13 13z"/></svg> Xác Nhận Thu Hồi</div>',
            cancelButtonText: 'Hủy',
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-red-200/50',
                title: 'text-red-600 font-bold text-2xl mt-2',
                htmlContainer: 'text-slate-600 text-sm mt-2 mb-4',
                actions: 'gap-3 w-full justify-center mt-4',
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg px-6 py-2.5 shadow-md shadow-red-500/30 transition-all hover:scale-105 outline-none',
                cancelButton: 'bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-lg px-6 py-2.5 border border-slate-300 shadow-sm transition-all hover:scale-105 outline-none'
            },
            buttonsStyling: false,
            backdrop: `rgba(0,0,0,0.4)`
        }).then((result) => {
            if (!result.isConfirmed) {
                openLocalModal('quickRevokeModal');
                return;
            }
                fetch('/thidua/admin/ctv?action=api_bulk_revoke_permissions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(apiResult => {
                    if(typeof showToast === 'function') {
                        showToast(apiResult.success ? 'success' : 'error', apiResult.message);
                        if (apiResult.success) setTimeout(() => window.location.reload(), 1500);
                    } else {
                        AppSwal.fire(
                            apiResult.success ? 'Thành công!' : 'Lỗi!',
                            apiResult.message,
                            apiResult.success ? 'success' : 'error'
                        ).then(() => {
                            if (apiResult.success) window.location.reload();
                        });
                    }
                });
        });
    });

    // Provision Accounts
    const provisionForm = document.getElementById('provisionForm');
    const classSelectContainer = document.getElementById('class-select-container');
    const provisionProgressWrapper = document.getElementById('provisionProgressWrapper');
    const provisionProgressBar = document.getElementById('provisionProgressBar');
    const provisionProgressText = document.getElementById('provisionProgressText');
    const startProvisionBtn = document.getElementById('startProvisionBtn');

    document.querySelectorAll('input[name="scope"]').forEach(radio => {
        radio.addEventListener('change', function() {
            classSelectContainer.style.display = this.value === 'class' ? 'block' : 'none';
        });
    });

    provisionForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const payload = {
            scope: formData.get('scope'),
            lop_id: formData.get('lop_id')
        };

        if (payload.scope === 'class' && !payload.lop_id) {
            if(typeof showToast === 'function') showToast('warning', 'Vui lòng chọn một lớp để cấp tài khoản.');
            else AppSwal.fire('Chú ý', 'Vui lòng chọn một lớp để cấp tài khoản.', 'warning');
            return;
        }

        closeLocalModal('provisionAccountModal');
        const confirmResult = await AppSwal.fire({
            title: 'Xác Nhận Cấp Tài Khoản',
            text: 'Bạn có chắc chắn muốn cấp tài khoản cho những học sinh chưa có tài khoản trong phạm vi đã chọn không?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<div class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg> Bắt đầu cấp TK</div>',
            cancelButtonText: 'Hủy',
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-blue-200/50',
                title: 'text-[#224397] font-bold text-2xl mt-2',
                htmlContainer: 'text-slate-600 text-sm mt-2 mb-4',
                actions: 'gap-3 w-full justify-center mt-4',
                confirmButton: 'bg-[#224397] hover:bg-[#1a3476] text-white font-semibold rounded-lg px-6 py-2.5 shadow-md shadow-blue-500/30 transition-all hover:scale-105 outline-none',
                cancelButton: 'bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-lg px-6 py-2.5 border border-slate-300 shadow-sm transition-all hover:scale-105 outline-none'
            },
            buttonsStyling: false,
            backdrop: `rgba(0,0,0,0.4)`
        });

        if (!confirmResult.isConfirmed) {
            openLocalModal('provisionAccountModal');
            return;
        }
        openLocalModal('provisionAccountModal');

        startProvisionBtn.disabled = true;
        provisionProgressWrapper.style.display = 'block';
        provisionProgressBar.style.width = '0%';
        provisionProgressText.textContent = 'Đang lấy danh sách học sinh...';

        try {
            const listRes = await fetch('/thidua/admin/ctv?action=api_get_unprovisioned_students', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const listData = await listRes.json();

            if (!listData.success || !listData.data || listData.data.length === 0) {
                if(typeof showToast === 'function') showToast('warning', listData.message || 'Không có học sinh nào cần cấp TK.');
                else AppSwal.fire('Thông báo', listData.message || 'Không có học sinh nào cần cấp TK.', 'info');
                startProvisionBtn.disabled = false;
                provisionProgressWrapper.style.display = 'none';
                return;
            }

            const students = listData.data;
            const total = students.length;
            let updated = 0;
            let skipped = 0;
            const batchSize = 10;

            for (let i = 0; i < total; i += batchSize) {
                const batch = students.slice(i, i + batchSize);
                
                const batchRes = await fetch('/thidua/admin/ctv?action=api_provision_batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ students: batch })
                });
                const batchData = await batchRes.json();
                
                if (batchData.success) {
                    updated += batchData.updated;
                    skipped += batchData.skipped;
                } else {
                    console.error('Lỗi batch:', batchData.message);
                }

                const percent = Math.min(100, Math.round(((i + batchSize) / total) * 100));
                provisionProgressBar.style.width = `${percent}%`;
                provisionProgressText.textContent = `Đang xử lý: ${percent}% (${Math.min(i + batchSize, total)}/${total})`;
            }

            provisionProgressBar.style.width = '100%';
            provisionProgressText.textContent = `Đã hoàn tất: ${updated}/${total} học sinh. (Bỏ qua: ${skipped})`;
            
            AppSwal.fire(
                'Hoàn Tất!',
                `Đã cấp tài khoản thành công cho ${updated}/${total} học sinh. (Bỏ qua: ${skipped}).`,
                'success'
            ).then(() => {
                window.location.reload();
            });
        } catch (err) {
            if(typeof showToast === 'function') showToast('error', 'Lỗi: ' + err.message);
            else AppSwal.fire('Lỗi', err.message, 'error');
        } finally {
            startProvisionBtn.disabled = false;
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById("filterFormCTV");
    if (!filterForm) return;

    let typingTimer;
    
    // Hàm thực hiện lọc bằng AJAX
    function performAjaxFilterCTV() {
        const formData = new FormData(filterForm);
        const searchParams = new URLSearchParams(formData);

        // Nút disable UI mờ đi
        const tableBody = document.querySelector('#ctvStudentTable tbody');
        if(tableBody) tableBody.style.opacity = '0.5';

        fetch('/thidua/admin/ctv?' + searchParams.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Thay thế table body
            const newTbody = doc.querySelector('#ctvStudentTable tbody');
            if (newTbody && tableBody) {
                tableBody.innerHTML = newTbody.innerHTML;
                tableBody.style.opacity = '1';
            }

            // Thay thế phân trang
            const oldNav = document.getElementById('ctvPaginationWrapper');
            const newNav = doc.getElementById('ctvPaginationWrapper');
            if (oldNav && newNav) {
                oldNav.innerHTML = newNav.innerHTML;
            }
            
            // Cập nhật URL trình duyệt (nếu muốn)
            // window.history.replaceState({}, '', '/thidua/admin/ctv?' + searchParams.toString());
        })
        .catch(err => {
            console.error("AJAX filter error:", err);
            if(tableBody) tableBody.style.opacity = '1';
        });
    }

    // Lắng nghe sự kiện change trên các select/checkbox
    filterForm.querySelectorAll('select, input[type="checkbox"]').forEach(el => {
        el.addEventListener('change', performAjaxFilterCTV);
    });

    // Lắng nghe sự kiện gõ phím trên keyword (có debounce)
    const keywordInput = filterForm.querySelector('input[name="keyword"]');
    if (keywordInput) {
        keywordInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                // Xoá tham số page nếu có (hoặc reset về 1) 
                // Do form gửi GET ko có field hidden page nên bỏ qua
                performAjaxFilterCTV();
            }, 400); 
        });
    }

    // Submit form chặn mặc định
    filterForm.addEventListener("submit", function(e) {
        e.preventDefault();
        performAjaxFilterCTV();
    });

    // Handle AJAX for pagination links
    document.body.addEventListener('click', function(e) {
        const link = e.target.closest('#ctvPaginationWrapper a');
        if (link && link.href) {
            e.preventDefault();
            const url = new URL(link.href);
            // Cập nhật lại form với URL params từ link
            const urlParams = new URLSearchParams(url.search);
            const hiddenPage = filterForm.querySelector('input[name="page"]');
            if (hiddenPage) {
                hiddenPage.value = urlParams.get('page') || 1;
            } else {
                const newHidden = document.createElement('input');
                newHidden.type = 'hidden';
                newHidden.name = 'page';
                newHidden.value = urlParams.get('page') || 1;
                filterForm.appendChild(newHidden);
            }
            performAjaxFilterCTV();
        }
    });
});
</script>