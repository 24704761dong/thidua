<?php
$page_title = 'Học Sinh Đã Tốt Nghiệp';
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="w-full px-2 lg:px-6 pt-4">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-5">
        <div>
            <h2 class="text-xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-award-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="m8 0 1.669.864 1.858.282.842 1.68 1.337 1.32L13.4 6l.305 1.854-1.337 1.32-.842 1.68-1.858.282L8 12l-1.669-.864-1.858-.282-.842-1.68-1.337-1.32L2.6 6l-.305-1.854 1.337-1.32.842-1.68 1.858-.282L8 0z"/><path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/></svg>
                HỌC SINH ĐÃ TỐT NGHIỆP
            </h2>
            <p class="text-slate-500 text-xs mt-0.5">Danh sách chi tiết các học sinh đã hoàn thành chương trình học và tốt nghiệp.</p>
        </div>
    </div>

    <!-- Bộ lọc client-side (Không F5) -->
    <div class="bg-white rounded shadow border border-[#224397]/25 p-3.5 mb-5">
        <form id="filterForm" onsubmit="return false;" class="flex flex-wrap items-end gap-3 m-0">
            <div class="flex-1 min-w-[220px]">
                <label for="keyword" class="block text-xs font-bold text-[#224397] mb-1">Tên / CCCD</label>
                <input type="text" id="keyword" class="block w-full rounded border border-slate-300 shadow-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397] px-3 py-1.5 text-xs outline-none" placeholder="Nhập tên hoặc số CCCD...">
            </div>
            <div class="w-44">
                <label for="nien_khoa" class="block text-xs font-bold text-[#224397] mb-1">Niên khóa</label>
                <select id="nien_khoa" class="block w-full rounded border border-slate-300 shadow-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397] px-3 py-1.5 text-xs outline-none">
                    <option value="all">Tất cả niên khóa</option>
                    <?php foreach ($ds_nien_khoa as $nk) : ?>
                        <option value="<?php echo htmlspecialchars($nk); ?>"><?php echo htmlspecialchars($nk); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-44">
                <label for="nam_tot_nghiep" class="block text-xs font-bold text-[#224397] mb-1">Năm tốt nghiệp</label>
                <select id="nam_tot_nghiep" class="block w-full rounded border border-slate-300 shadow-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397] px-3 py-1.5 text-xs outline-none">
                    <option value="all">Tất cả năm TN</option>
                    <?php foreach ($ds_nam_tot_nghiep as $nam) : ?>
                        <option value="<?php echo htmlspecialchars($nam); ?>"><?php echo htmlspecialchars($nam); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
                DANH SÁCH HỌC SINH TỐT NGHIỆP (<span id="graduateCount"><?php echo count($danh_sach_tot_nghiep); ?></span>)
            </h3>
        </div>
        <div class="px-4 pb-4 pt-3">
            <?php if (empty($danh_sach_tot_nghiep)): ?>
                <div class="p-4 rounded border bg-blue-50 text-blue-800 border-blue-200 text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                    Không có học sinh nào phù hợp với điều kiện tìm kiếm.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto list-scrollbar w-full border border-[#224397]/25 rounded">
                    <table class="w-full text-left text-sm text-slate-600 border-collapse" id="graduateTable">
                        <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                            <tr>
                                <th class="p-3 w-12 text-center border-r border-[#224397]/25">STT</th>
                                <th class="p-3 w-16 text-center border-r border-[#224397]/25">Ảnh</th>
                                <th class="p-3 text-center border-r border-[#224397]/25">Niên khóa</th>
                                <th class="p-3 border-r border-[#224397]/25">Số CCCD</th>
                                <th class="p-3 border-r border-[#224397]/25">Họ và Tên</th>
                                <th class="p-3 text-center border-r border-[#224397]/25">Ngày sinh</th>
                                <th class="p-3 text-center border-r border-[#224397]/25">SDT</th>
                                <th class="p-3 border-r border-[#224397]/25">Gmail</th>
                                <th class="p-3 text-center border-r border-[#224397]/25">Lớp 10</th>
                                <th class="p-3 text-center border-r border-[#224397]/25">Lớp 11</th>
                                <th class="p-3 text-center border-r border-[#224397]/25">Lớp 12</th>
                                <th class="p-3 text-center border-r border-[#224397]/25">Trạng thái</th>
                                <th class="p-3 text-center border-r border-[#224397]/25">Năm TN</th>
                                <th class="p-3 text-center w-40">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#224397]/20">
                            <tr id="noResultRow" style="display: none;"><td colspan="14" class="text-center py-8 text-slate-500 italic">Không tìm thấy học sinh nào phù hợp với điều kiện lọc.</td></tr>
                            <?php foreach ($danh_sach_tot_nghiep as $index => $hs) : 
                                $ma = $hs['ma_hoc_sinh'];
                                $qt = $quatrinh_map[$ma] ?? ['lop10' => '', 'lop11' => '', 'lop12' => ''];
                            ?>
                                <tr data-id="<?php echo $hs['id']; ?>" data-nien-khoa="<?php echo htmlspecialchars($hs['nien_khoa'] ?? ''); ?>" data-nam-tn="<?php echo htmlspecialchars($hs['nam_tot_nghiep'] ?? ''); ?>" data-cccd="<?php echo htmlspecialchars(strtolower($hs['ma_hoc_sinh'])); ?>" data-name="<?php echo htmlspecialchars(strtolower($hs['ho_dem'] . ' ' . $hs['ten'])); ?>" class="graduate-row hover:bg-slate-50 transition">
                                    <td class="p-3 text-center border-r border-[#224397]/25 font-bold"><?php echo $index + 1; ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/25">
                                        <img src="<?php echo htmlspecialchars(get_student_avatar_url($hs['anh_the'] ?? '', $hs['anh_the_driver'] ?? 'local', $hs['anh_the_cloud_key'] ?? null)); ?>" class="w-10 h-12 object-cover rounded mx-auto border border-slate-300 shadow-sm" alt="Avatar" onerror="this.src='/thidua/public/assets/img/anhthegoc.JPG'">
                                    </td>
                                    <td class="p-3 text-center border-r border-[#224397]/25 font-medium"><?php echo htmlspecialchars($hs['nien_khoa'] ?? '---'); ?></td>
                                    <td class="p-3 border-r border-[#224397]/25 font-semibold text-[#224397]"><?php echo htmlspecialchars($hs['ma_hoc_sinh']); ?></td>
                                    <td class="p-3 border-r border-[#224397]/25 font-bold text-slate-800"><?php echo htmlspecialchars($hs['ho_dem'] . ' ' . $hs['ten']); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/25"><?php echo htmlspecialchars($hs['ngay_sinh'] ?? '---'); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/25"><?php echo htmlspecialchars($hs['sdt'] ?? '---'); ?></td>
                                    <td class="p-3 border-r border-[#224397]/25"><?php echo htmlspecialchars($hs['email'] ?? '---'); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/25 font-medium text-blue-700"><?php echo htmlspecialchars(!empty($qt['lop10']) ? $qt['lop10'] : '---'); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/25 font-medium text-indigo-700"><?php echo htmlspecialchars(!empty($qt['lop11']) ? $qt['lop11'] : '---'); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/25 font-medium text-purple-700"><?php echo htmlspecialchars(!empty($qt['lop12']) ? $qt['lop12'] : '---'); ?></td>
                                    <td class="p-3 text-center border-r border-[#224397]/25">
                                        <span class="px-2.5 py-1 bg-green-100 text-green-800 font-bold rounded text-xs inline-block shadow-sm">Đã Tốt Nghiệp</span>
                                    </td>
                                    <td class="p-3 text-center border-r border-[#224397]/25 font-bold text-green-700"><?php echo htmlspecialchars($hs['nam_tot_nghiep'] ?? '---'); ?></td>
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button class="reset-pass-btn px-2.5 py-1 bg-amber-500 text-white rounded hover:bg-amber-600 transition-all font-medium text-[11px] shadow-sm flex items-center gap-1" title="Reset mật khẩu về mặc định (CCCD)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> Reset Pass
                                            </button>
                                            <button class="view-details-btn px-2.5 py-1 bg-[#224397] text-white rounded hover:bg-[#FAB723] transition-all font-medium text-[11px] shadow-sm flex items-center gap-1" title="Xem chi tiết thông tin học sinh">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg> Xem Info
                                            </button>
                                        </div>
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

<!-- Modal Reset Password Confirmation -->
<div id="resetPassModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[450px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
                Xác Nhận Reset Mật Khẩu
            </h5>
            <button type="button" class="close-reset-modal text-slate-400 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div class="p-5">
            <p class="text-slate-700">Bạn có chắc chắn muốn đặt lại mật khẩu cho học sinh <strong id="resetStudentName" class="text-[#224397]"></strong> về mặc định không?</p>
            <p class="text-amber-700 bg-amber-50 border border-amber-200 p-3 rounded mt-3 text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="currentColor" class="bi bi-info-circle-fill shrink-0" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                <span>Mật khẩu mới sẽ là <strong>Số CCCD</strong> của học sinh.</span>
            </p>
        </div>
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="close-reset-modal px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300">
                Hủy
            </button>
            <button type="button" id="confirmResetBtn" class="px-4 py-2 bg-amber-500 text-white rounded hover:bg-amber-600 font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>
                Xác nhận Reset
            </button>
        </div>
    </div>
</div>

<!-- Modal Xem Thông Tin Chi Tiết Học Sinh -->
<div id="viewDetailsModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0 p-4">
    <div class="modal-content bg-white rounded-lg shadow-2xl border border-slate-300 w-full max-w-[850px] max-h-[90vh] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
        <div class="bg-[#224397] text-white px-6 py-4 flex justify-between items-center shrink-0 rounded-t-lg">
            <h5 class="font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-person-vcard-fill" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm9 1.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5M9 8a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4A.5.5 0 0 0 9 8m1 2.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5m-1 2C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 0 2 13h6.96q.04-.245.04-.5M7 6a2 2 0 1 0-4 0 2 2 0 0 0 4 0"/></svg>
                HỒ SƠ HỌC SINH TỐT NGHIỆP
            </h5>
            <button type="button" class="close-details-modal text-white/80 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto list-scrollbar flex-1 space-y-6">
            <!-- Section 1: Thông tin cơ bản -->
            <div class="bg-slate-50 border border-[#224397]/20 rounded-lg p-5">
                <h4 class="text-[#224397] font-bold text-sm uppercase mb-4 pb-2 border-b border-[#224397]/20 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>
                    Thông tin cơ bản
                </h4>
                <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                    <div class="w-32 h-40 shrink-0 bg-white border border-slate-300 rounded shadow-sm overflow-hidden flex items-center justify-center" id="detailAvatarContainer">
                        <!-- JS fills image -->
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 w-full text-sm">
                        <div><span class="text-slate-500 block text-xs font-medium">Họ và tên:</span> <strong class="text-slate-800 text-base" id="detailHoTen"></strong></div>
                        <div><span class="text-slate-500 block text-xs font-medium">Số CCCD:</span> <strong class="text-[#224397] text-base" id="detailCCCD"></strong></div>
                        <div><span class="text-slate-500 block text-xs font-medium">Niên khóa:</span> <strong class="text-slate-800" id="detailNienKhoa"></strong></div>
                        <div><span class="text-slate-500 block text-xs font-medium">Ngày sinh:</span> <strong class="text-slate-800" id="detailNgaySinh"></strong></div>
                        <div><span class="text-slate-500 block text-xs font-medium">Số điện thoại:</span> <strong class="text-slate-800" id="detailSDT"></strong></div>
                        <div><span class="text-slate-500 block text-xs font-medium">Gmail:</span> <strong class="text-slate-800" id="detailEmail"></strong></div>
                        <div><span class="text-slate-500 block text-xs font-medium">Trạng thái:</span> <span class="px-2.5 py-0.5 bg-green-100 text-green-800 font-bold rounded text-xs inline-block mt-0.5" id="detailTrangThai">Đã Tốt Nghiệp</span></div>
                        <div><span class="text-slate-500 block text-xs font-medium">Năm tốt nghiệp:</span> <strong class="text-green-700 text-base" id="detailNamTN"></strong></div>
                        <div class="md:col-span-2"><span class="text-slate-500 block text-xs font-medium">Liên kết Google:</span> <span id="detailGoogleLink" class="text-xs font-bold"></span></div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Quá trình học tập (Lớp 10, 11, 12 + GVCN) -->
            <div class="bg-slate-50 border border-[#224397]/20 rounded-lg p-5">
                <h4 class="text-[#224397] font-bold text-sm uppercase mb-4 pb-2 border-b border-[#224397]/20 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-journal-bookmark-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 1h6c.55 0 1 .45 1 1v12c0 .55-.45 1-1 1H6zm.5 2.5a.5.5 0 0 0-.5.5v4.5l1.5-1 1.5 1V4a.5.5 0 0 0-.5-.5z"/><path d="M2 3h1v1H2zm0 3h1v1H2zm0 3h1v1H2z"/></svg>
                    Quá trình học tập & Giáo Viên Chủ Nhiệm
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded border border-slate-200 shadow-sm">
                        <div class="font-bold text-blue-800 text-base mb-2 pb-1 border-b border-blue-100 flex items-center justify-between">
                            <span>LỚP 10</span>
                            <span class="text-xs text-slate-500 font-normal" id="detailNam10"></span>
                        </div>
                        <div class="text-sm mb-1"><span class="text-slate-500">Tên lớp:</span> <strong class="text-blue-700" id="detailLop10"></strong></div>
                        <div class="text-sm"><span class="text-slate-500">GVCN:</span> <strong class="text-slate-700" id="detailGVCN10"></strong></div>
                    </div>
                    <div class="bg-white p-4 rounded border border-slate-200 shadow-sm">
                        <div class="font-bold text-indigo-800 text-base mb-2 pb-1 border-b border-indigo-100 flex items-center justify-between">
                            <span>LỚP 11</span>
                            <span class="text-xs text-slate-500 font-normal" id="detailNam11"></span>
                        </div>
                        <div class="text-sm mb-1"><span class="text-slate-500">Tên lớp:</span> <strong class="text-indigo-700" id="detailLop11"></strong></div>
                        <div class="text-sm"><span class="text-slate-500">GVCN:</span> <strong class="text-slate-700" id="detailGVCN11"></strong></div>
                    </div>
                    <div class="bg-white p-4 rounded border border-slate-200 shadow-sm">
                        <div class="font-bold text-purple-800 text-base mb-2 pb-1 border-b border-purple-100 flex items-center justify-between">
                            <span>LỚP 12</span>
                            <span class="text-xs text-slate-500 font-normal" id="detailNam12"></span>
                        </div>
                        <div class="text-sm mb-1"><span class="text-slate-500">Tên lớp:</span> <strong class="text-purple-700" id="detailLop12"></strong></div>
                        <div class="text-sm"><span class="text-slate-500">GVCN:</span> <strong class="text-slate-700" id="detailGVCN12"></strong></div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Thống kê hoạt động & Nhật kỳ -->
            <div class="bg-slate-50 border border-[#224397]/20 rounded-lg p-5">
                <h4 class="text-[#224397] font-bold text-sm uppercase mb-4 pb-2 border-b border-[#224397]/20 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-bar-chart-fill" viewBox="0 0 16 16"><path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1z"/></svg>
                    Thống kê truy cập & Tra cứu
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div class="bg-white p-4 rounded border border-slate-200 shadow-sm">
                        <div class="text-2xl font-bold text-[#224397]" id="detailLastLogin"></div>
                        <div class="text-xs text-slate-500 mt-1 font-medium uppercase">Lần đăng nhập cuối</div>
                    </div>
                    <div class="bg-white p-4 rounded border border-slate-200 shadow-sm">
                        <div class="text-2xl font-bold text-blue-600" id="detailLoginCount"></div>
                        <div class="text-xs text-slate-500 mt-1 font-medium uppercase">Số lần đăng nhập</div>
                    </div>
                    <div class="bg-white p-4 rounded border border-slate-200 shadow-sm">
                        <div class="text-2xl font-bold text-amber-600" id="detailLookupCount"></div>
                        <div class="text-xs text-slate-500 mt-1 font-medium uppercase">Số lần tra cứu vi phạm</div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Lịch sử vi phạm (Qua các năm học) -->
            <!-- Section 4: Lịch sử vi phạm (Qua các năm học) -->
            <div class="bg-slate-50 border border-[#224397]/20 rounded-lg p-5">
                <h4 class="text-[#224397] font-bold text-sm uppercase mb-4 pb-2 border-b border-[#224397]/20 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-shield-exclamation" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/><path d="M7.001 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.553.553 0 0 1-1.1 0z"/></svg>
                    Lịch sử vi phạm (Qua các năm học)
                </h4>
                <div id="detailViPhamContainer">
                    <!-- JS fills violations table -->
                </div>
            </div>

            <!-- Section 5: Lịch sử khen thưởng (Qua các năm học) -->
            <div class="bg-slate-50 border border-[#224397]/20 rounded-lg p-5">
                <h4 class="text-[#224397] font-bold text-sm uppercase mb-4 pb-2 border-b border-[#224397]/20 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-award-fill text-amber-500" viewBox="0 0 16 16"><path d="m8 0 1.669.864 1.858.282.842 1.68 1.337 1.32L13.4 6l.305 1.854-1.337 1.32-.842 1.68-1.858.282L8 12l-1.669-.864-1.858-.282-.842-1.68-1.337-1.32L2.6 6l-.305-1.854 1.337-1.32.842-1.68 1.858-.282L8 0z"/><path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/></svg>
                    Lịch sử khen thưởng (Qua các năm học)
                </h4>
                <div id="detailKhenThuongContainer">
                    <!-- JS fills rewards table -->
                </div>
            </div>

            <!-- Section 6: Hoạt động đã tham gia -->
            <div class="bg-slate-50 border border-[#224397]/20 rounded-lg p-5">
                <h4 class="text-[#224397] font-bold text-sm uppercase mb-4 pb-2 border-b border-[#224397]/20 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-activity text-blue-500" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2Z"/></svg>
                    Hoạt động đã tham gia
                </h4>
                <div id="detailHoatDongContainer">
                    <!-- JS fills activities table -->
                </div>
            </div>
        </div>
        <div class="bg-slate-100 border-t border-slate-200 px-6 py-3 flex justify-end shrink-0 rounded-b-lg">
            <button type="button" class="close-details-modal px-6 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300">
                Đóng
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableElement = document.getElementById('graduateTable');
    
    // Bộ lọc Client-side tự động (Không F5)
    const keywordInput = document.getElementById('keyword');
    const nienKhoaSelect = document.getElementById('nien_khoa');
    const namTNSelect = document.getElementById('nam_tot_nghiep');
    const graduateRows = document.querySelectorAll('.graduate-row');
    const graduateCountEl = document.getElementById('graduateCount');
    const noResultRow = document.getElementById('noResultRow');

    function filterGraduateTable() {
        const keyword = keywordInput ? keywordInput.value.toLowerCase().trim() : '';
        const nienKhoa = nienKhoaSelect ? nienKhoaSelect.value : 'all';
        const namTN = namTNSelect ? namTNSelect.value : 'all';
        let count = 0;

        graduateRows.forEach(row => {
            const rNienKhoa = row.dataset.nienKhoa;
            const rNamTN = row.dataset.namTn;
            const rCccd = row.dataset.cccd;
            const rName = row.dataset.name;

            const matchKeyword = keyword === '' || rCccd.includes(keyword) || rName.includes(keyword);
            const matchNienKhoa = nienKhoa === 'all' || rNienKhoa === nienKhoa;
            const matchNamTN = namTN === 'all' || rNamTN === namTN;

            if (matchKeyword && matchNienKhoa && matchNamTN) {
                row.style.display = '';
                count++;
            } else {
                row.style.display = 'none';
            }
        });

        if (graduateCountEl) graduateCountEl.textContent = count;
        if (noResultRow) {
            noResultRow.style.display = count === 0 ? '' : 'none';
        }
    }

    if (keywordInput) keywordInput.addEventListener('input', filterGraduateTable);
    if (nienKhoaSelect) nienKhoaSelect.addEventListener('change', filterGraduateTable);
    if (namTNSelect) namTNSelect.addEventListener('change', filterGraduateTable);
    
    // Modal Reset Password
    const resetModal = document.getElementById('resetPassModal');
    const resetModalContent = resetModal ? resetModal.querySelector('.modal-content') : null;
    const confirmResetBtn = document.getElementById('confirmResetBtn');
    const resetStudentNameEl = document.getElementById('resetStudentName');
    
    // Modal View Details
    const detailsModal = document.getElementById('viewDetailsModal');
    const detailsModalContent = detailsModal ? detailsModal.querySelector('.modal-content') : null;
    
    let currentStudentId = null;

    // Hàm mở modal Reset Pass
    function openResetModal() {
        resetModal.classList.remove('hidden');
        setTimeout(() => {
            resetModal.classList.remove('opacity-0');
            resetModalContent.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            resetModalContent.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }, 10);
    }

    function closeResetModal() {
        resetModal.classList.add('opacity-0');
        resetModalContent.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        resetModalContent.classList.add('opacity-0', 'scale-95', 'translate-y-4');
        setTimeout(() => {
            resetModal.classList.add('hidden');
            currentStudentId = null;
        }, 300);
    }

    if (resetModal) {
        document.querySelectorAll('.close-reset-modal').forEach(btn => {
            btn.addEventListener('click', closeResetModal);
        });
        resetModal.addEventListener('click', function(e) {
            if (e.target === resetModal) closeResetModal();
        });
    }

    // Hàm mở modal View Details
    function openDetailsModal() {
        detailsModal.classList.remove('hidden');
        setTimeout(() => {
            detailsModal.classList.remove('opacity-0');
            detailsModalContent.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            detailsModalContent.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }, 10);
    }

    function closeDetailsModal() {
        detailsModal.classList.add('opacity-0');
        detailsModalContent.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        detailsModalContent.classList.add('opacity-0', 'scale-95', 'translate-y-4');
        setTimeout(() => {
            detailsModal.classList.add('hidden');
            currentStudentId = null;
        }, 300);
    }

    if (detailsModal) {
        document.querySelectorAll('.close-details-modal').forEach(btn => {
            btn.addEventListener('click', closeDetailsModal);
        });
        detailsModal.addEventListener('click', function(e) {
            if (e.target === detailsModal) closeDetailsModal();
        });
    }

    if (!tableElement) return;

    tableElement.addEventListener('click', function(e) {
        const resetBtn = e.target.closest('.reset-pass-btn');
        const viewBtn = e.target.closest('.view-details-btn');
        
        if (resetBtn) {
            const row = resetBtn.closest('tr');
            currentStudentId = row.dataset.id;
            const studentName = row.cells[4].textContent;
            resetStudentNameEl.textContent = studentName;
            openResetModal();
            return;
        }

        if (viewBtn) {
            const row = viewBtn.closest('tr');
            currentStudentId = row.dataset.id;
            const originalHtml = viewBtn.innerHTML;
            viewBtn.disabled = true;
            viewBtn.innerHTML = '<span class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Đang tải...';

            fetch(`/thidua/admin/hoc-sinh-tot-nghiep?action=api_get_details&id=${currentStudentId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const st = data.student;
                        document.getElementById('detailHoTen').textContent = st.ho_dem + ' ' + st.ten;
                        document.getElementById('detailCCCD').textContent = st.ma_hoc_sinh;
                        document.getElementById('detailNienKhoa').textContent = st.nien_khoa || 'Không rõ';
                        document.getElementById('detailNgaySinh').textContent = st.ngay_sinh || 'Không rõ';
                        document.getElementById('detailSDT').textContent = st.sdt || 'Không rõ';
                        document.getElementById('detailEmail').textContent = st.email || 'Không rõ';
                        document.getElementById('detailNamTN').textContent = st.nam_tot_nghiep || 'Không rõ';

                        const avatarContainer = document.getElementById('detailAvatarContainer');
                        const url = st.anh_the_url || (st.anh_the ? `/thidua/public/assets/anh_the/${st.anh_the}` : '/thidua/public/assets/img/anhthegoc.JPG');
                        avatarContainer.innerHTML = `<img src="${url}" class="w-full h-full object-cover" alt="Avatar" onerror="this.src='/thidua/public/assets/img/anhthegoc.JPG'">`;

                        const googleLink = document.getElementById('detailGoogleLink');
                        if (st.google_id) {
                            googleLink.innerHTML = `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs flex items-center gap-1 inline-flex"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-google text-red-500" viewBox="0 0 16 16"><path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.648a4.28 4.28 0 0 0-4.275 4.275A4.28 4.28 0 0 0 8 12.201c2.39 0 3.82-1.63 3.99-3.26h-3.99V6.558z"/></svg> Đã liên kết</span>`;
                        } else {
                            googleLink.innerHTML = `<span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs inline-block">Chưa liên kết</span>`;
                        }

                        // Lớp 10, 11, 12
                        document.getElementById('detailLop10').textContent = data.lop10.lop;
                        document.getElementById('detailGVCN10').textContent = data.lop10.gvcn;
                        document.getElementById('detailNam10').textContent = data.lop10.nam;

                        document.getElementById('detailLop11').textContent = data.lop11.lop;
                        document.getElementById('detailGVCN11').textContent = data.lop11.gvcn;
                        document.getElementById('detailNam11').textContent = data.lop11.nam;

                        document.getElementById('detailLop12').textContent = data.lop12.lop;
                        document.getElementById('detailGVCN12').textContent = data.lop12.gvcn;
                        document.getElementById('detailNam12').textContent = data.lop12.nam;

                        // Thống kê
                        document.getElementById('detailLastLogin').textContent = data.last_login;
                        document.getElementById('detailLoginCount').textContent = data.login_count + ' lần';
                        document.getElementById('detailLookupCount').textContent = data.lookup_count + ' lần';

                        // Lịch sử vi phạm
                        const vpContainer = document.getElementById('detailViPhamContainer');
                        if (data.vi_pham.length === 0) {
                            vpContainer.innerHTML = `<div class="p-4 rounded border bg-green-50 text-green-800 border-green-200 text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill text-green-600" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                                Học sinh không có ghi nhận vi phạm nào trong suốt quá trình học.
                            </div>`;
                        } else {
                            let tableHtml = `<div class="overflow-x-auto border border-[#224397]/25 rounded">
                                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                                    <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500">
                                        <tr>
                                            <th class="p-2.5 text-center border-r border-[#224397]/25">STT</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Năm học</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Tuần</th>
                                            <th class="p-2.5 text-center border-r border-[#224397]/25">Ngày VP</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Tên vi phạm</th>
                                            <th class="p-2.5 text-center border-r border-[#224397]/25">Điểm trừ</th>
                                            <th class="p-2.5">Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#224397]/20">`;
                            data.vi_pham.forEach((vp, idx) => {
                                tableHtml += `<tr class="hover:bg-slate-50">
                                    <td class="p-2.5 text-center border-r border-[#224397]/25 font-bold">${idx + 1}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25 font-medium">${vp.ten_nam_hoc || '---'}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25">${vp.ten_tuan || '---'}</td>
                                    <td class="p-2.5 text-center border-r border-[#224397]/25 text-red-600 font-medium">${vp.ngay_vi_pham ? vp.ngay_vi_pham.split('-').reverse().join('/') : '---'}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25 font-semibold text-slate-800">${vp.ten_vi_pham}</td>
                                    <td class="p-2.5 text-center border-r border-[#224397]/25 font-bold text-red-600">-${vp.diem_tru}</td>
                                    <td class="p-2.5 text-slate-500 italic">${vp.ghi_chu || ''}</td>
                                </tr>`;
                            });
                            tableHtml += `</tbody></table></div>`;
                            vpContainer.innerHTML = tableHtml;
                        }

                        // Lịch sử khen thưởng
                        const ktContainer = document.getElementById('detailKhenThuongContainer');
                        if (!data.khen_thuong || data.khen_thuong.length === 0) {
                            ktContainer.innerHTML = `<div class="p-4 rounded border bg-amber-50 text-amber-800 border-amber-200 text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill text-amber-600" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                                Học sinh chưa có ghi nhận khen thưởng nào trong hệ thống.
                            </div>`;
                        } else {
                            let ktHtml = `<div class="overflow-x-auto border border-[#224397]/25 rounded">
                                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                                    <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-50 text-slate-500">
                                        <tr>
                                            <th class="p-2.5 text-center border-r border-[#224397]/25">STT</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Năm học</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Lớp</th>
                                            <th class="p-2.5 text-center border-r border-[#224397]/25">Ngày KT</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Tên khen thưởng</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Số QĐ</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Cấp KT</th>
                                            <th class="p-2.5">Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#224397]/20">`;
                            data.khen_thuong.forEach((kt, idx) => {
                                ktHtml += `<tr class="hover:bg-slate-50">
                                    <td class="p-2.5 text-center border-r border-[#224397]/25 font-bold">${idx + 1}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25 font-medium">${kt.ten_nam_hoc || '---'}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25 font-medium text-[#224397]">${kt.ten_lop || '---'}</td>
                                    <td class="p-2.5 text-center border-r border-[#224397]/25 text-green-700 font-medium">${kt.ngay_khen_thuong ? kt.ngay_khen_thuong.split('-').reverse().join('/') : '---'}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25 font-bold text-slate-800">${kt.ten_khen_thuong}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25 font-medium">${kt.so_quyet_dinh || '---'}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25 font-medium">${kt.cap_khen_thuong || '---'}</td>
                                    <td class="p-2.5 text-slate-500 italic">${kt.ghi_chu || ''}</td>
                                </tr>`;
                            });
                            ktHtml += `</tbody></table></div>`;
                            ktContainer.innerHTML = ktHtml;
                        }

                        // Lịch sử hoạt động tham gia
                        const hdContainer = document.getElementById('detailHoatDongContainer');
                        if (!data.hoat_dong || data.hoat_dong.length === 0) {
                            hdContainer.innerHTML = `<div class="p-4 rounded border bg-blue-50 text-blue-800 border-blue-200 text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill text-blue-600" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                                Học sinh chưa tham gia hoạt động nào.
                            </div>`;
                        } else {
                            let hdHtml = `<div class="overflow-x-auto border border-[#224397]/25 rounded">
                                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                                    <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-50 text-slate-500">
                                        <tr>
                                            <th class="p-2.5 text-center border-r border-[#224397]/25" style="width: 50px;">STT</th>
                                            <th class="p-2.5 text-center border-r border-[#224397]/25" style="width: 140px;">Ngày tham gia</th>
                                            <th class="p-2.5 border-r border-[#224397]/25">Tên hoạt động</th>
                                            <th class="p-2.5 text-center" style="width: 120px;">Đánh giá</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#224397]/20">`;
                            data.hoat_dong.forEach((hd, idx) => {
                                let danhGiaHtml = '';
                                if (hd.trang_thai_diem_danh == 1) {
                                    danhGiaHtml = `<span class="px-2 py-1 rounded text-[11px] bg-green-100 text-green-700 font-bold border border-green-200">Đã tham gia (+${hd.diem_thuc_te}đ)</span>`;
                                } else {
                                    danhGiaHtml = `<span class="px-2 py-1 rounded text-[11px] bg-slate-100 text-slate-600 font-bold border border-slate-200">Chưa điểm danh</span>`;
                                }
                                
                                // Format date YYYY-MM-DD HH:MM:SS to DD/MM/YYYY
                                let dateStr = hd.ngay_tham_gia;
                                if (dateStr) {
                                    let parts = dateStr.split(' ')[0].split('-');
                                    if (parts.length === 3) {
                                        dateStr = parts[2] + '/' + parts[1] + '/' + parts[0];
                                    }
                                }

                                hdHtml += `<tr class="hover:bg-slate-50">
                                    <td class="p-2.5 text-center border-r border-[#224397]/25 font-bold">${idx + 1}</td>
                                    <td class="p-2.5 text-center border-r border-[#224397]/25 font-semibold text-blue-600">${dateStr || '---'}</td>
                                    <td class="p-2.5 border-r border-[#224397]/25 font-bold text-slate-800">${hd.ten_hoat_dong}</td>
                                    <td class="p-2.5 text-center font-medium">${danhGiaHtml}</td>
                                </tr>`;
                            });
                            hdHtml += `</tbody></table></div>`;
                            hdContainer.innerHTML = hdHtml;
                        }

                        openDetailsModal();
                    } else {
                        showToast('error', data.message || 'Có lỗi xảy ra khi tải dữ liệu.');
                    }
                })
                .catch(err => {
                    showToast('error', 'Lỗi kết nối mạng.');
                })
                .finally(() => {
                    viewBtn.disabled = false;
                    viewBtn.innerHTML = originalHtml;
                });
        }
    });

    if (confirmResetBtn) {
        confirmResetBtn.addEventListener('click', async function() {
            if (!currentStudentId) return;
            const originalHtml = confirmResetBtn.innerHTML;
            confirmResetBtn.disabled = true;
            confirmResetBtn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Đang xử lý...';

            try {
                const response = await fetch('/thidua/admin/hoc-sinh-tot-nghiep?action=api_reset_pass', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: currentStudentId })
                });
                const result = await response.json();
                
                if (result.success) {
                    closeResetModal();
                    showToast('success', result.message);
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                showToast('error', 'Lỗi: ' + error.message);
            } finally {
                confirmResetBtn.disabled = false;
                confirmResetBtn.innerHTML = originalHtml;
            }
        });
    }
});
</script>
