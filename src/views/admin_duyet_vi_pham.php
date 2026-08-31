<?php
$page_title = 'Duyệt Vi Phạm Từ CTV';
require_once __DIR__ . '/partials/admin_header.php';

// Giả định các biến đã được nạp
$vi_pham_cho_duyet_grouped = $vi_pham_cho_duyet_grouped ?? [];
?>

<link href="/thidua/public/assets/libs/fancybox.css" rel="stylesheet">
<style>
    .import-table th { padding: 8px 12px; font-size: 12px; font-weight: 600; color: #334155; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .import-table td { padding: 8px 12px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9; }
    .import-table tr:hover { background-color: rgba(34, 67, 151, 0.05) !important; }
</style>

<div class="w-full px-2 lg:px-6 mt-4">
    <div class="flex flex-col md:flex-row items-end justify-between gap-4 mb-4">
        <div>
            <h3 class="text-[18px] font-bold text-[#224397] flex items-center gap-2 mb-0 uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-fill-check text-[#FAB723]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263C10.228.826 9.11.47 8 0zm2.146 5.146a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647z"/></svg>
                Duyệt Vi Phạm Chờ Xử Lý
            </h3>
        </div>
        
        <div class="flex items-center gap-1.5 flex-wrap">
            <form method="GET" action="/thidua/admin/duyet-vi-pham" class="flex items-center gap-2 mr-2">
                <?php if (isset($_GET['iframe'])): ?>
                    <input type="hidden" name="iframe" value="1">
                <?php endif; ?>
                <select name="trang_thai" onchange="this.form.submit()" class="text-[12px] border-slate-300 rounded px-2 py-1 font-bold text-[#224397] bg-white shadow-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]">
                    <option value="da_gui" <?php echo $filter_trang_thai === 'da_gui' ? 'selected' : ''; ?>>Đang chờ duyệt</option>
                    <option value="da_duyet" <?php echo $filter_trang_thai === 'da_duyet' ? 'selected' : ''; ?>>Đã phê duyệt</option>
                    <option value="da_loai_bo" <?php echo $filter_trang_thai === 'da_loai_bo' ? 'selected' : ''; ?>>Đã loại bỏ</option>
                    <option value="tat_ca" <?php echo $filter_trang_thai === 'tat_ca' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                </select>
                <select name="tuan_id" onchange="this.form.submit()" class="text-[12px] border-slate-300 rounded px-2 py-1 text-slate-700 bg-white shadow-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397]">
                    <option value="">-- Tất cả các tuần --</option>
                    <?php foreach ($weeks as $w): ?>
                        <option value="<?php echo $w['id']; ?>" <?php echo $filter_tuan_id == $w['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($w['ten_tuan']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <select id="client-sort-select" class="text-[12px] border-slate-300 rounded px-2 py-1 text-slate-700 bg-white shadow-sm focus:border-[#224397] focus:ring-1 focus:ring-[#224397] mr-2">
                <option value="desc">Mới nhất đến cũ nhất</option>
                <option value="asc">Cũ nhất đến mới nhất</option>
            </select>
            <!-- OneDrive Button -->
            <button type="button" id="backup-onedrive-btn" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-microsoft" viewBox="0 0 16 16"><path d="M7.462 0H0v7.19h7.462V0zM16 0H8.538v7.19H16V0zM7.462 8.211H0V16h7.462V8.211zm8.538 0H8.538V16H16V8.211z"/></svg> SAO LƯU ONEDRIVE
            </button>
            <a href="/thidua/admin/trung-tam-duyet" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg> 
                Quay lại Trung Tâm
            </a>
        </div>
    </div>

    <?php if (empty($vi_pham_cho_duyet_grouped)): ?>
        <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-6 flex flex-col items-center justify-center min-h-[200px]">
            <div class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center text-green-500 mb-4 border border-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
            </div>
            <p class="text-[14px] font-bold text-slate-700 mb-1"><?php echo $filter_trang_thai === 'da_gui' ? 'Thật tuyệt vời!' : 'Không có dữ liệu'; ?></p>
            <p class="text-[13px] text-slate-500"><?php echo $filter_trang_thai === 'da_gui' ? 'Hiện tại không có vi phạm nào đang chờ duyệt.' : 'Không tìm thấy vi phạm nào ở trạng thái này.'; ?></p>
        </div>
    <?php else: ?>
        <div id="batches-container" class="w-full">
        <?php foreach ($vi_pham_cho_duyet_grouped as $batch_key => $batch_data): ?>
            <div class="batch-item bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden" data-timestamp="<?php echo strtotime($batch_data['thoi_gian_gui'] ?? '2000-01-01'); ?>">
                <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h3 class="mb-1 text-[15px] font-bold text-[#224397] uppercase flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge-fill" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/></svg>
                            BÁO CÁO TỪ CTV: <span class="text-[#FAB723]"><?php echo htmlspecialchars($batch_data['ten_ctv']); ?> - LỚP <?php echo htmlspecialchars($batch_data['lop_ctv']); ?></span>
                        </h3>
                        <div class="text-[12px] text-slate-500 font-medium">
                            Đợt nộp: <?php echo $batch_data['thoi_gian_gui'] ? date('H:i d/m/Y', strtotime($batch_data['thoi_gian_gui'])) : 'Legacy'; ?> 
                            <span class="mx-2">|</span> 
                            Tuần: <?php echo htmlspecialchars($batch_data['ten_tuan']); ?>
                        </div>
                    </div>
                    <?php if (!empty($batch_data['proofs'])): ?>
                        <div class="flex items-center gap-2">
                            <?php foreach ($batch_data['proofs'] as $idx => $proof): ?>
                                <a href="<?php echo htmlspecialchars($proof['url']); ?>" data-fancybox="gallery-<?php echo htmlspecialchars($batch_key); ?>" data-caption="<?php echo htmlspecialchars($proof['file_name']); ?>" data-type="image">
                                    <?php if ($idx === 0): ?>
                                        <button class="px-2 py-1 bg-white border border-[#224397] rounded text-[#224397] hover:bg-[#224397] hover:text-white transition-colors text-[11px] font-medium flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-image" viewBox="0 0 16 16">
                                              <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h11A1.5 1.5 0 0 1 15 2.5v11a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 13.5v-11zm1.5-.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5v-11a.5.5 0 0 0-.5-.5h-11z"/>
                                              <path d="M10.5 5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-2 0a.5.5 0 1 0 1 0 .5.5 0 0 0-1 0zm-3 5.5l2-2 2.5 2.5a.5.5 0 0 0 .707 0l1.5-1.5L14 11.5v1a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-1.5l2-2z"/>
                                            </svg>
                                            Xem <?php echo count($batch_data['proofs']); ?> minh chứng
                                        </button>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="px-4 pb-4 pt-3">
                    <div class="w-full overflow-x-auto border border-slate-200 rounded mb-4">
                        <table class="w-full text-left import-table border-collapse">
                            <thead>
                                <tr>
                                    <?php if ($filter_trang_thai === 'da_gui'): ?>
                                    <th style="width: 5%;" class="text-center"><input type="checkbox" class="rounded border-slate-300 text-[#224397] shadow-sm focus:border-[#224397] select-all-group"></th>
                                    <?php endif; ?>
                                    <th>HS Vi Phạm</th>
                                    <th>Lớp</th>
                                    <th>Ngày VP</th>
                                    <th>Tên Nhóm Vi Phạm</th>
                                    <th>Ghi Chú</th>
                                    <?php if ($filter_trang_thai !== 'da_gui'): ?>
                                    <th>Trạng Thái</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($batch_data['items'] as $item): ?>
                                    <tr class="<?php echo (($item['trang_thai_hoc_tap'] ?? '') === 'nghi_hoc') ? 'opacity-50 line-through bg-slate-50' : ''; ?>" <?php echo (($item['trang_thai_hoc_tap'] ?? '') === 'nghi_hoc') ? 'title="Học sinh đã nghỉ học"' : ''; ?>>
                                        <?php if ($filter_trang_thai === 'da_gui'): ?>
                                        <td class="text-center"><input type="checkbox" class="rounded border-slate-300 text-[#224397] shadow-sm focus:border-[#224397] violation-checkbox" value="<?php echo $item['id']; ?>"></td>
                                        <?php endif; ?>
                                        <td class="font-medium text-[#224397]"><?php echo htmlspecialchars($item['raw_ho_ten']); ?></td>
                                        <td><?php echo htmlspecialchars($item['raw_ten_lop']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($item['ngay_vi_pham'])); ?></td>
                                        <td><?php echo htmlspecialchars($item['ten_vi_pham']); ?></td>
                                        <td class="text-slate-500 italic"><?php echo htmlspecialchars($item['ghi_chu']); ?></td>
                                        <?php if ($filter_trang_thai !== 'da_gui'): ?>
                                        <td>
                                            <?php if (($item['trang_thai_gui'] ?? '') === 'da_duyet'): ?>
                                                <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-[11px] font-bold">Đã Duyệt</span>
                                            <?php elseif (($item['trang_thai_gui'] ?? '') === 'da_loai_bo'): ?>
                                                <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-[11px] font-bold">Đã Loại Bỏ</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[11px] font-bold">Chờ Duyệt</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($filter_trang_thai === 'da_gui'): ?>
                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                        <button class="px-2 py-1 bg-red-500 border border-transparent rounded text-white hover:bg-red-600 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm reject-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                            Loại Bỏ Các Mục Đã Chọn
                        </button>
                        <button class="px-2 py-1 bg-green-600 border border-transparent rounded text-white hover:bg-green-700 transition-colors font-medium flex items-center gap-1 text-[11px] shadow-sm approve-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                            Duyệt Các Mục Đã Chọn
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check all checkbox
    document.querySelectorAll('.select-all-group').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const table = this.closest('table');
            const checkboxes = table.querySelectorAll('.violation-checkbox');
            checkboxes.forEach(cb => {
                if (!cb.closest('tr').classList.contains('opacity-50')) { // Không check các dòng gạch ngang
                    cb.checked = this.checked;
                }
            });
        });
    });

    // Handle action buttons
    const handleAction = function(btn, action) {
        btn.addEventListener('click', function() {
            const container = this.closest('.pb-4');
            const checkedBoxes = container.querySelectorAll('.violation-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa chọn mục nào',
                    text: 'Vui lòng chọn ít nhất một vi phạm để ' + (action === 'approve' ? 'duyệt' : 'loại bỏ'),
                    confirmButtonColor: '#224397'
                });
                return;
            }

            const ids = Array.from(checkedBoxes).map(cb => cb.value);

            Swal.fire({
                title: 'Xác nhận ' + (action === 'approve' ? 'duyệt' : 'loại bỏ') + '?',
                text: 'Bạn đang chọn ' + ids.length + ' mục.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: action === 'approve' ? '#16a34a' : '#dc2626',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Đang xử lý...',
                        text: 'Vui lòng chờ (việc gửi hàng loạt email có thể mất 1-2 giây)',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('/thidua/api/admin-xu-ly-vi-pham', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: action, ids: ids })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: data.message,
                                confirmButtonColor: '#224397'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: data.message || 'Đã xảy ra lỗi hệ thống',
                                confirmButtonColor: '#224397'
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi kết nối',
                            text: 'Không thể kết nối đến máy chủ.',
                            confirmButtonColor: '#224397'
                        });
                    });
                }
            });
        });
    };

    document.querySelectorAll('.approve-btn').forEach(btn => handleAction(btn, 'approve'));
    document.querySelectorAll('.reject-btn').forEach(btn => handleAction(btn, 'reject'));

    // Client-side sort
    const sortSelect = document.getElementById('client-sort-select');
    const batchesContainer = document.getElementById('batches-container');
    if (sortSelect && batchesContainer) {
        sortSelect.addEventListener('change', function() {
            const order = this.value;
            const items = Array.from(batchesContainer.querySelectorAll('.batch-item'));
            items.sort((a, b) => {
                const timeA = parseInt(a.getAttribute('data-timestamp')) || 0;
                const timeB = parseInt(b.getAttribute('data-timestamp')) || 0;
                return order === 'desc' ? (timeB - timeA) : (timeA - timeB);
            });
            items.forEach(item => batchesContainer.appendChild(item));
        });
    }

    // Logic Sao lưu OneDrive cho minh chứng vi phạm
    const backupOneDriveBtn = document.getElementById('backup-onedrive-btn');
    if (backupOneDriveBtn) {
        backupOneDriveBtn.addEventListener('click', function() {
            Swal.fire({
                title: "Xác nhận sao lưu",
                text: "Sao lưu toàn bộ minh chứng vi phạm từ R2 sang OneDrive?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#224397',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: "Bắt đầu",
                cancelButtonText: "Hủy"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        Swal.fire({
                            title: "Đang phân tích...",
                            html: "Đang lấy danh sách các file minh chứng cần sao lưu.",
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        const res = await fetch("/thidua/api/admin/api_get_violation_proof_ids_for_backup", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ backup_all: true })
                        }).then(r => r.json());

                        if (!res.success) {
                            Swal.fire("Lỗi!", res.message, "error");
                            return;
                        }

                        const ids = res.ids || [];
                        if (ids.length === 0) {
                            Swal.fire("Hoàn tất", "Không có file nào (hoặc đã sao lưu hết) để đẩy lên OneDrive.", "info");
                            return;
                        }

                        let successCount = 0;
                        let errorCount = 0;

                        for (let i = 0; i < ids.length; i++) {
                            const proofId = ids[i];
                            Swal.update({
                                title: "Đang sao lưu OneDrive",
                                html: `Đang xử lý file ${i + 1}/${ids.length}.<br><br>Thành công: ${successCount}<br>Lỗi: ${errorCount}<br><br><span class="text-sm text-red-500 font-bold">Vui lòng không đóng trình duyệt!</span>`
                            });

                            try {
                                const uploadRes = await fetch("/thidua/api/admin/api_backup_single_violation_onedrive", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({ id: proofId })
                                }).then(r => r.json());

                                if (uploadRes.success) {
                                    successCount++;
                                } else {
                                    console.error(`Lỗi upload ID ${proofId}:`, uploadRes.message);
                                    errorCount++;
                                }
                            } catch (e) {
                                console.error(`Network lỗi ID ${proofId}:`, e);
                                errorCount++;
                            }
                        }

                        Swal.fire({
                            title: "Hoàn tất sao lưu",
                            html: `Đã xử lý xong.<br>Thành công: ${successCount}<br>Lỗi: ${errorCount}`,
                            icon: errorCount === 0 ? "success" : "warning",
                            confirmButtonColor: '#224397'
                        }).then(() => window.location.reload());

                    } catch (e) {
                        Swal.fire("Lỗi hệ thống", e.message, "error");
                    }
                }
            });
        });
    }
});
</script>

<script src="/thidua/public/assets/libs/fancybox.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Fancybox.unbind("[data-fancybox]");
    Fancybox.bind("[data-fancybox]", { groupAll: false, dragToClose: true, Hash: false });
});
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>