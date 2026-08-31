<?php
$page_title = 'Lịch Sử Thông Báo';
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
    /* ----- Bảng màu và biến CSS hiện đại ----- */
    :root {
        --primary-blue: #224397;
        --accent-gold: #FAB723;
        --bg-light: #f4f7f9;
        --card-border: rgba(34, 67, 151, 0.25);
    }
    
    body {
        background-color: var(--bg-light);
    }

    /* ----- Thiết kế bảng chính giống DANH SÁCH HỌC SINH ----- */
    #notificationTable {
        border: 1px solid var(--card-border);
        border-collapse: collapse;
        width: 100%;
    }
    #notificationTable thead th {
        background-color: rgba(34, 67, 151, 0.08);
        color: var(--primary-blue);
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.82rem;
        text-align: center;
        padding: 0.75rem 1rem;
        border: 1px solid var(--card-border);
    }
    #notificationTable td {
        padding: 0.75rem 1rem;
        border: 1px solid var(--card-border);
        vertical-align: middle;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
    }
    #notificationTable tbody tr:hover {
        background-color: rgba(34, 67, 151, 0.05) !important;
    }

    /* Ép hiện thanh cuộn cho trang danh sách dài */
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34, 67, 151, 0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }

    /* Custom Toast Notification theo chuẩn Nhập Vi Phạm */
    .custom-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #eff6ff; /* blue-50 */
        border: 1px solid #bfdbfe; /* blue-200 */
        border-radius: 8px;
        padding: 12px 16px;
        color: #1e40af; /* blue-800 */
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 9999;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .custom-toast.show {
        transform: translateY(0);
        opacity: 1;
    }
</style>

<div class="w-full px-2 lg:px-6">
    <div class="flex flex-row items-end justify-between gap-2 mb-4">
        <div>
            <h3 class="text-2xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/></svg> 
                Lịch Sử Thông Báo
            </h3>
        </div>
        
        <!-- Nút thao tác theo chuẩn style DANH SÁCH HỌC SINH -->
        <div class="flex items-center gap-1.5">
            <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="markAllAsRead()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16"><path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/><path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/></svg>
                ĐÁNH DẤU ĐÃ ĐỌC
            </button>
            <button type="button" class="px-2 py-1 bg-white border border-red-300 rounded text-red-600 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap" onclick="deleteAll()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                XÓA TẤT CẢ
            </button>
        </div>
    </div>

    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-card-list mr-2" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/><path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/></svg>DANH SÁCH THÔNG BÁO</h3>
        </div>
        <div class="px-4 pb-4 pt-3 w-full">
            <div class="overflow-x-auto w-full">
                <table id="notificationTable">
                    <thead>
                        <tr>
                            <th class="w-16 text-center">Trạng Thái</th>
                            <th class="text-left">Nội Dung</th>
                            <th class="text-left w-48">Thời Gian</th>
                            <th class="text-center w-24">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-8 text-slate-500 font-medium">Không có thông báo nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $index => $item): ?>
                                <tr class="<?php echo ($item['da_xem'] == 0) ? 'bg-blue-50/30 font-bold' : ''; ?>">
                                    <td class="text-center">
                                        <?php if ($item['da_xem'] == 0): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-circle text-amber-500 mx-auto" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/></svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-check-circle text-green-500 mx-auto" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-left text-slate-700">
                                        <?php 
                                            // Lấy nội dung thông báo. Cố gắng lấy từ cột noi_dung nếu có, nếu không lấy từ tieu_de
                                            $noi_dung = $item['noi_dung'] ?? ($item['tieu_de'] ?? 'Có một thông báo mới (loại: ' . ($item['loai_thong_bao'] ?? 'chung') . ')');
                                            echo htmlspecialchars($noi_dung);
                                        ?>
                                    </td>
                                    <td class="text-left text-sm text-slate-500">
                                        <?php echo date('H:i:s d/m/Y', strtotime($item['thoi_gian'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex justify-center gap-1.5">
                                            <button onclick="deleteNotification(<?php echo $item['id']; ?>)" class="px-2.5 py-1 text-xs font-medium bg-white text-red-600 border border-red-200 hover:bg-red-50 rounded shadow-sm hover:-translate-y-1 hover:scale-110 transition-all duration-300 flex items-center justify-center" title="Xóa">
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
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex items-center justify-between mt-4">
                <div class="text-[13px] text-slate-500 font-medium">
                    Hiển thị từ <?php echo $offset + 1; ?> đến <?php echo min($offset + $limit, $total_records); ?> trong tổng số <?php echo $total_records; ?> bản ghi
                </div>
                <div class="flex gap-1">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] text-xs font-bold transition-all shadow-sm">Trước</a>
                    <?php endif; ?>
                    
                    <span class="px-3 py-1 bg-[#224397] text-white border border-[#224397] rounded text-xs font-bold shadow-sm"><?php echo $page; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] text-xs font-bold transition-all shadow-sm">Tiếp</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Hàm hiển thị Toast theo chuẩn Nhập Vi Phạm
function showSessionNotification(message) {
    // Xóa toast cũ nếu có
    const existingToast = document.getElementById('system-session-toast');
    if (existingToast) existingToast.remove();

    // Tạo HTML cho Toast
    const toast = document.createElement('div');
    toast.id = 'system-session-toast';
    toast.className = 'custom-toast';
    toast.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
        </svg>
        ${message}
    `;

    document.body.appendChild(toast);

    // Show animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Hide and remove after 3s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Hàm Xóa thông báo với Modal chuẩn Quản Lý Năm Học
function deleteNotification(id) {
    AppSwal.fire({
        title: 'Cảnh Báo Xóa!',
        text: 'Bạn có chắc chắn muốn xóa thông báo này? Dữ liệu sẽ bị mất và không thể khôi phục!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận Xóa',
        cancelButtonText: 'Hủy',
        customClass: {
            popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
            title: 'text-red-600 font-bold text-xl mt-0',
            htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
            actions: 'flex justify-center gap-3 w-full mt-6',
            confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 hover:scale-110 hover:shadow-md transition-all duration-300 outline-none',
            cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none',
            icon: 'scale-[0.85] my-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if(result.isConfirmed) {
            // Giả lập call API thành công
            AppSwal.fire({
                title: 'Thành công!',
                text: 'Xóa thông báo thành công.',
                icon: 'success',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
                    title: 'text-[#224397] font-bold text-xl mt-0',
                    htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
                    actions: 'flex justify-center w-full mt-6',
                    confirmButton: 'bg-[#224397] text-white rounded-lg px-8 py-2 font-medium shadow-sm hover:bg-[#FAB723] hover:text-slate-900 transition-all duration-300 outline-none',
                    icon: 'scale-[0.85] my-2 text-[#224397]'
                },
                buttonsStyling: false
            }).then(() => {
                window.location.reload();
            });
        }
    });
}

// Xóa tất cả
function deleteAll() {
    AppSwal.fire({
        title: 'Xóa Tất Cả!',
        text: 'Bạn có chắc chắn muốn dọn dẹp toàn bộ lịch sử thông báo?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận Xóa',
        cancelButtonText: 'Hủy',
        customClass: {
            popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
            title: 'text-red-600 font-bold text-xl mt-0',
            htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
            actions: 'flex justify-center gap-3 w-full mt-6',
            confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 hover:scale-110 hover:shadow-md transition-all duration-300 outline-none',
            cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none',
            icon: 'scale-[0.85] my-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if(result.isConfirmed) {
            AppSwal.fire({
                title: 'Thành công!',
                text: 'Đã dọn dẹp lịch sử thông báo.',
                icon: 'success',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
                    title: 'text-[#224397] font-bold text-xl mt-0',
                    htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
                    actions: 'flex justify-center w-full mt-6',
                    confirmButton: 'bg-[#224397] text-white rounded-lg px-8 py-2 font-medium shadow-sm hover:bg-[#FAB723] hover:text-slate-900 transition-all duration-300 outline-none',
                    icon: 'scale-[0.85] my-2 text-[#224397]'
                },
                buttonsStyling: false
            }).then(() => {
                window.location.reload();
            });
        }
    });
}

function markAllAsRead() {
    // Dùng thông báo Session chuẩn Nhập Vi Phạm
    showSessionNotification('Đã đánh dấu tất cả là đã đọc.');
    
    // Gửi request API ngầm ở đây (giả lập)
    fetch('/thidua/api/mark-all-notifications-as-read', { method: 'POST' })
    .then(() => {
        setTimeout(() => window.location.reload(), 1500);
    });
}
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
