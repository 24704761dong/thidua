<?php
// File: src/views/the_hoc_sinh_danh_sach.php (Đã đồng bộ Premium Tailwind UI, Bộ lọc Niên Khóa & Bố cục cột chuẩn)

$page_title = 'Danh Sách Thông Tin In Thẻ';
require_once __DIR__ . '/partials/admin_header.php';

// Các biến đã được controller truyền sang:
$danh_sach_hoc_sinh = $danh_sach_hoc_sinh ?? [];
$danh_sach_lop = $danh_sach_lop ?? [];
$danh_sach_tat_ca_mau_the = $danh_sach_tat_ca_mau_the ?? [];
$danh_sach_nien_khoa = $danh_sach_nien_khoa ?? [];
$filter_khoi = $filter_khoi ?? 'all';
$filter_lop_id = $filter_lop_id ?? 'all';
?>

<style>
    /* ----- Bảng màu và biến CSS hiện đại ----- */
    :root {
        --primary-blue: #00a8e8;
        --primary-green: #97c93c;
        --dark-blue: #2c3e50;
        --text-primary: #1d2d35;
        --text-secondary: #5a6a72;
        --bg-light: #f4f7f9;
        --card-border: #e9ecef;
    }
    
    body {
        background-color: var(--bg-light);
    }

    /* ----- Thiết kế bảng chính ----- */
    #studentTable {
        border: 1px solid rgba(34, 67, 151, 0.25);
        border-collapse: collapse;
        width: 100%;
    }
    #studentTable thead th {
        background-color: rgba(34, 67, 151, 0.08);
        color: #224397;
        font-weight: 800; /* Tiêu đề bảng in đậm hơn */
        text-transform: uppercase;
        font-size: 0.88rem; /* To hơn dữ liệu bảng 1 xíu */
        text-align: center;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(34, 67, 151, 0.25);
    }
    #studentTable td {
        padding: 0.75rem 1rem;
        border: 1px solid rgba(34, 67, 151, 0.25); /* Các đường kẻ đều nhau */
        vertical-align: middle;
        font-size: 0.85rem;
        font-weight: 600; /* Chữ đậm lên dễ nhìn */
        color: #1e293b;
    }
    #studentTable tbody tr:hover {
        background-color: rgba(34, 67, 151, 0.05) !important;
    }
    
    tr.checked-row {
        background-color: #e6f4ff !important;
    }

    /* ----- Ép hiện thanh cuộn cho trang danh sách dài ----- */
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34, 67, 151, 0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }
</style>

<div class="w-full px-2 lg:px-6 py-4">
    <form id="printForm" action="/thidua/admin/the-hoc-sinh/in" method="POST" target="_blank" class="m-0">
        <!-- NÉ TRẦN max_input_vars: gom toàn bộ ID đã chọn vào 1 input ẩn -->
        <input type="hidden" name="selected_ids" id="selected_ids" value="">
        
        <!-- Filter và Buttons trên 1 hàng (Đồng bộ chuẩn Quản Lý Học Sinh) -->
        <div class="flex flex-row items-end justify-between gap-2 mb-4 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            
            <!-- Filter Controls (Bên trái) -->
            <div class="flex flex-row items-end gap-3 m-0 flex-wrap">
                <div>
                    <label for="searchKeyword" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Tên / Mã HS</label>
                    <div class="relative">
                        <input type="text" id="searchKeyword" class="block w-40 rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397]/20 px-3 py-1.5 text-[13px]" placeholder="Nhập tên/mã...">
                    </div>
                </div>
                <div>
                    <label for="nienKhoaSelect" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Niên Khóa</label>
                    <select id="nienKhoaSelect" class="block w-32 rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397]/20 px-3 py-1.5 text-[13px]">
                        <option value="all" selected>Tất cả</option>
                        <?php foreach ($danh_sach_nien_khoa as $nk): ?>
                            <option value="<?php echo htmlspecialchars($nk); ?>"><?php echo htmlspecialchars($nk); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="khoiSelect" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Khối</label>
                    <select id="khoiSelect" class="block w-28 rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397]/20 px-3 py-1.5 text-[13px]">
                        <option value="all" selected>Tất cả</option>
                        <option value="10">Khối 10</option>
                        <option value="11">Khối 11</option>
                        <option value="12">Khối 12</option>
                    </select>
                </div>
                <div>
                    <label for="lopSelect" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Lớp</label>
                    <select id="lopSelect" class="block w-32 rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397]/20 px-3 py-1.5 text-[13px]">
                        <option value="all">Tất cả các lớp</option>
                        <?php foreach ($danh_sach_lop as $lop): ?>
                            <option value="<?php echo $lop['id']; ?>" data-khoi="<?php echo substr($lop['ten_lop'], 0, 2); ?>">
                                <?php echo htmlspecialchars($lop['ten_lop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="mau-the-select" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Chọn mẫu thẻ in</label>
                    <select name="mau_the_id" id="mau-the-select" class="block w-52 rounded-lg border-slate-300 shadow-sm focus:border-[#224397] focus:ring focus:ring-[#224397]/20 px-3 py-1.5 text-[13px] font-semibold text-[#224397]">
                        <?php foreach ($danh_sach_tat_ca_mau_the as $mau): ?>
                            <option value="<?php echo $mau['id']; ?>" <?php if ($mau['is_default']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($mau['ten_mau']); ?>
                                <?php if ($mau['is_default']) echo ' (Mặc định)'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Action Buttons (Bên phải, phong cách nút tinh gọn) -->
            <div class="flex items-center gap-2 flex-wrap">
                <button type="submit" class="px-4 py-1.5 bg-[#224397] border border-[#224397] rounded-lg text-white hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-bold flex items-center gap-1.5 text-[12px] shadow-sm whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-printer-fill text-[#FAB723] group-hover:text-white transition-colors" viewBox="0 0 16 16"><path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/><path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/></svg> 
                    IN ĐÃ CHỌN
                </button>
                
                <!-- Dropdown Khác -->
                <div class="relative inline-block text-left group z-50">
                    <button type="button" class="px-4 py-1.5 bg-white border border-[#224397]/25 rounded-lg text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-bold flex items-center gap-1.5 text-[12px] shadow-sm whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg> 
                        KHÁC 
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[10px]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                    </button>
                    <ul class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-2">
                        <li>
                            <a class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-[#224397] text-decoration-none" href="/thidua/admin/the-hoc-sinh/xuat-mau-import?iframe=1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-download text-[#FAB723]" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
                                Xuất file Excel
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-[#224397] text-decoration-none" href="/thidua/admin/the-hoc-sinh/nhap-file-cap-nhat?iframe=1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-upload text-[#FAB723]" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>
                                Nhập file Excel
                            </a>
                        </li>
                    </ul>
                </div>

                <a href="/thidua/admin/the-hoc-sinh?iframe=1" class="px-4 py-1.5 bg-white border border-[#224397]/25 rounded-lg text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-bold flex items-center gap-1.5 text-[12px] shadow-sm whitespace-nowrap text-decoration-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg> 
                    QUAY LẠI HUB
                </a>
            </div>
        </div>

        <!-- BẢNG DANH SÁCH HỌC SINH -->
        <div class="bg-white rounded-xl shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
            <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
                <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-person-badge-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/></svg>
                    DANH SÁCH THÔNG TIN IN THẺ
                </h3>
                <span id="rowCountDisplay" class="text-xs font-bold text-[#224397] bg-blue-100 px-3 py-1 rounded-full border border-blue-200">
                    Hiển thị: <?php echo count($danh_sach_hoc_sinh); ?> học sinh
                </span>
            </div>
            
            <div class="w-full p-0">
                <div class="overflow-x-auto w-full">
                    <table id="studentTable">
                        <thead>
                            <tr>
                                <th class="w-12"><input type="checkbox" id="selectAllCheckbox" class="rounded border-slate-300 text-[#224397] shadow-sm focus:border-[#224397]" checked></th>
                                <th class="w-16 text-center">STT</th>
                                <th class="w-24 text-center">Ảnh Thẻ</th>
                                <th class="text-left">Họ và Tên</th>
                                <th class="text-center w-32">Ngày Sinh</th>
                                <th class="text-center">Lớp</th>
                                <th class="text-center">Niên Khóa</th>
                                <th class="text-center">Mã Học Sinh</th>
                                <th class="text-center">Mã MOET</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <?php if (empty($danh_sach_hoc_sinh)): ?>
                                <tr id="emptyRow">
                                    <td colspan="9" class="text-center text-slate-500 py-12 font-medium text-base">Không có dữ liệu học sinh trong hệ thống.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($danh_sach_hoc_sinh as $index => $hs) : ?>
                                    <tr class="student-row hover:bg-slate-50 transition-colors checked-row" 
                                        data-khoi="<?php echo substr($hs['ten_lop'], 0, 2); ?>" 
                                        data-lop="<?php echo $hs['lop_id'] ?? $hs['lop_hoc_id'] ?? $hs['ten_lop']; ?>" 
                                        data-nk="<?php echo htmlspecialchars($hs['nien_khoa'] ?? ''); ?>" 
                                        data-ten="<?php echo strtolower($hs['ho_dem'] . ' ' . $hs['ten']); ?>" 
                                        data-ma="<?php echo strtolower($hs['ma_hoc_sinh']); ?>">
                                        
                                        <td class="text-center">
                                            <input class="rounded border-slate-300 text-[#224397] shadow-sm focus:border-[#224397] student-checkbox" type="checkbox" data-id="<?php echo $hs['id']; ?>" value="<?php echo $hs['id']; ?>" checked>
                                        </td>
                                        <td class="text-center font-medium text-slate-700 row-index"><?php echo $index + 1; ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($hs['anh_the']) || !empty($hs['anh_the_cloud_key'])): ?>
                                                <img src="<?php echo htmlspecialchars(get_student_avatar_url($hs['anh_the'] ?? '', $hs['anh_the_driver'] ?? 'local', $hs['anh_the_cloud_key'] ?? null)); ?>" alt="Ảnh thẻ" class="w-10 h-14 object-cover rounded shadow-sm mx-auto border border-slate-200">
                                            <?php else: ?>
                                                <span class="text-slate-400 italic text-xs font-medium">Chưa có</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-left font-bold text-slate-800"><?php echo htmlspecialchars($hs['ho_dem'] . ' ' . $hs['ten']); ?></td>
                                        <td class="text-center font-medium text-slate-700">
                                            <?php
                                            $ngay_sinh_str = trim($hs['ngay_sinh'] ?? '');
                                            if (!empty($ngay_sinh_str)) {
                                                $date_obj = DateTime::createFromFormat('d/m/Y', $ngay_sinh_str) ?: DateTime::createFromFormat('Y-m-d', $ngay_sinh_str);
                                                if ($date_obj) { echo $date_obj->format('d/m/Y'); }
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center font-bold text-[#224397] student-lop"><?php echo htmlspecialchars($hs['ten_lop']); ?></td>
                                        <td class="text-center font-semibold text-slate-700"><?php echo htmlspecialchars($hs['nien_khoa'] ?? ''); ?></td>
                                        <td class="text-center font-mono font-bold text-slate-800"><?php echo htmlspecialchars($hs['ma_hoc_sinh']); ?></td>
                                        <td class="text-center font-mono text-slate-600"><?php echo htmlspecialchars($hs['ma_moet'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchKeyword');
    const nienKhoaSelect = document.getElementById('nienKhoaSelect');
    const khoiSelect = document.getElementById('khoiSelect');
    const lopSelect = document.getElementById('lopSelect');
    const originalLopOptions = Array.from(lopSelect.options);
    const studentRows = document.querySelectorAll('.student-row');
    const rowCountDisplay = document.getElementById('rowCountDisplay');

    // 1. CẬP NHẬT DANH SÁCH LỚP THEO KHỐI
    function filterLopTheoKhoi() {
        const selectedKhoi = khoiSelect.value;
        const currentSelectedLop = lopSelect.value;
        
        lopSelect.innerHTML = ''; 
        
        originalLopOptions.forEach(option => {
            if (option.value === 'all' || selectedKhoi === 'all' || option.dataset.khoi === selectedKhoi) {
                lopSelect.appendChild(option.cloneNode(true));
            }
        });
        
        lopSelect.value = currentSelectedLop;
        if (lopSelect.selectedIndex === -1) {
            lopSelect.value = 'all'; 
        }
    }

    // 2. BỘ LỌC CLIENT-SIDE 100% (KHÔNG F5 TRANG)
    function applyClientFilters() {
        const keyword = searchInput.value.toLowerCase().trim();
        const selectedNk = nienKhoaSelect.value;
        const selectedKhoi = khoiSelect.value;
        const selectedLop = lopSelect.value;
        let visibleCount = 0;

        studentRows.forEach(row => {
            const ten = row.getAttribute('data-ten') || '';
            const ma = row.getAttribute('data-ma') || '';
            const nk = row.getAttribute('data-nk') || '';
            const khoi = row.getAttribute('data-khoi') || '';
            
            // Xử lý so khớp lớp (kiểm tra cả ID lớp và Tên lớp)
            const lopAttr = row.getAttribute('data-lop') || '';
            const lopText = row.querySelector('.student-lop').textContent.trim();
            
            const matchKeyword = keyword === '' || ten.includes(keyword) || ma.includes(keyword);
            const matchNk = selectedNk === 'all' || nk === selectedNk;
            const matchKhoi = selectedKhoi === 'all' || khoi === selectedKhoi;
            
            // Nếu option chọn có text là tên lớp hoặc value là ID lớp
            let matchLop = selectedLop === 'all';
            if (!matchLop) {
                const selectedOptionText = lopSelect.options[lopSelect.selectedIndex].text.trim();
                matchLop = (lopAttr === selectedLop || lopText === selectedOptionText);
            }

            if (matchKeyword && matchNk && matchKhoi && matchLop) {
                row.style.display = '';
                visibleCount++;
                row.querySelector('.row-index').textContent = visibleCount;
            } else {
                row.style.display = 'none';
            }
        });

        rowCountDisplay.textContent = `Hiển thị: ${visibleCount} học sinh`;
    }

    // Gắn sự kiện lắng nghe
    searchInput.addEventListener('input', applyClientFilters);
    nienKhoaSelect.addEventListener('change', applyClientFilters);
    
    khoiSelect.addEventListener('change', () => {
        filterLopTheoKhoi();
        lopSelect.value = 'all';
        applyClientFilters();
    });

    lopSelect.addEventListener('change', applyClientFilters);

    // Chạy khởi tạo ban đầu
    filterLopTheoKhoi();
    applyClientFilters();

    // 3. XỬ LÝ CHECKBOX & HÀNG TRONG BẢNG
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        studentCheckboxes.forEach(cb => {
            const row = cb.closest('tr');
            // Chỉ toggle các hàng đang hiển thị
            if (row.style.display !== 'none') {
                cb.checked = selectAllCheckbox.checked;
                row.classList.toggle('checked-row', cb.checked);
            }
        });
    });

    studentCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            cb.closest('tr').classList.toggle('checked-row', cb.checked);
            // Kiểm tra trạng thái selectAll
            const visibleCheckboxes = Array.from(studentCheckboxes).filter(c => c.closest('tr').style.display !== 'none');
            selectAllCheckbox.checked = visibleCheckboxes.every(c => c.checked);
        });
    });

    // 4. XỬ LÝ SUBMIT FORM IN THẺ
    const printForm = document.getElementById('printForm');
    const hiddenSelected = document.getElementById('selected_ids');
    
    printForm.addEventListener('submit', function(e) {
        // Lấy các checkbox được check VÀ dòng đó đang hiển thị (được lọc)
        const checked = Array.from(document.querySelectorAll('.student-checkbox:checked')).filter(cb => cb.closest('tr').style.display !== 'none');
        
        if (checked.length === 0) {
            e.preventDefault();
            if (typeof customAlertModal === 'function') {
                customAlertModal('Vui lòng chọn ít nhất một học sinh trong danh sách hiển thị để in thẻ.');
            } else {
                alert('Vui lòng chọn ít nhất một học sinh trong danh sách hiển thị để in thẻ.');
            }
            return;
        }

        const ids = checked.map(cb => cb.getAttribute('data-id') || cb.value);
        hiddenSelected.value = ids.join(',');
    });
});
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
