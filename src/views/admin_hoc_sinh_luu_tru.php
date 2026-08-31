<?php
$page_title = 'Học Sinh Đã Nghỉ Học';
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="w-full px-2 lg:px-6 pt-4">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-5">
        <div>
            <h2 class="text-xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-archive-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M12.643 15C13.979 15 15 13.845 15 12.5V5H1v7.5C1 13.845 2.021 15 3.357 15zM5.5 7h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1M.8 1a.8.8 0 0 0-.8.8V3a.8.8 0 0 0 .8.8h14.4A.8.8 0 0 0 16 3V1.8a.8.8 0 0 0-.8-.8z"/></svg>
                HỌC SINH ĐÃ NGHỈ HỌC
            </h2>
            <p class="text-slate-500 text-xs mt-0.5">Danh sách các học sinh đã chuyển sang trạng thái nghỉ học và lưu trữ.</p>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
                DANH SÁCH LƯU TRỮ (<?= count($danh_sach_hoc_sinh_nghi_hoc) ?>)
            </h3>
        </div>
        <div class="px-4 pb-4 pt-3">
            <?php if (empty($danh_sach_hoc_sinh_nghi_hoc)): ?>
                <div class="p-4 rounded border bg-blue-50 text-blue-800 border-blue-200 text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                    Không có học sinh nào trong danh sách lưu trữ.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto list-scrollbar w-full border border-[#224397]/25 rounded">
                    <table class="w-full text-left text-sm text-slate-600" id="archiveTable">
                        <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                            <tr>
                                <th class="p-3 w-16 text-center">STT</th>
                                <th class="p-3">Số CCCD</th>
                                <th class="p-3">Niên khóa</th>
                                <th class="p-3">Lớp (Lần cuối)</th>
                                <th class="p-3">Năm học nghỉ</th>
                                <th class="p-3">Họ và Tên</th>
                                <th class="p-3">Ngày Nghỉ</th>
                                <th class="p-3">Lý Do</th>
                                <th class="p-3 text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#224397]/20">
                            <?php foreach ($danh_sach_hoc_sinh_nghi_hoc as $index => $hs) : ?>
                                <tr data-id="<?php echo $hs['id']; ?>" class="hover:bg-slate-50 transition">
                                    <td class="p-3 text-center"><?php echo $index + 1; ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($hs['ma_hoc_sinh']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($hs['nien_khoa'] ?? '---'); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($hs['ten_lop']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($hs['nam_nghi_hoc'] ?? 'Không rõ'); ?></td>
                                    <td class="p-3 font-medium text-slate-800"><?php echo htmlspecialchars($hs['ho_dem'] . ' ' . $hs['ten']); ?></td>
                                    <td class="p-3 text-red-600">
                                        <?php 
                                        if (!empty($hs['ngay_nghi_hoc'])) {
                                            echo date('d/m/Y', strtotime($hs['ngay_nghi_hoc']));
                                        }
                                        ?>
                                    </td>
                                    <td class="p-3 text-slate-500 italic"><?php echo htmlspecialchars($hs['ly_do_nghi_hoc'] ?? ''); ?></td>
                                    <td class="p-3 text-center">
                                        <button class="restore-btn px-3 py-1.5 bg-white border border-slate-300 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium inline-flex items-center justify-center gap-1.5 text-xs shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/><path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/></svg> Khôi phục
                                        </button>
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

<!-- Restore Confirmation Modal -->
<div id="restoreConfirmModal" class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[500px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0">
        <!-- Header -->
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/></svg>
                Xác Nhận Khôi Phục
            </h5>
            <button type="button" class="close-restore-modal text-slate-400 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="p-5">
            <p class="text-slate-700">Bạn có chắc chắn muốn khôi phục học sinh <strong id="restoreStudentName" class="text-[#224397]"></strong> không?</p>
            <p class="text-slate-500 mt-2 text-sm italic">Hồ sơ sẽ xuất hiện trở lại trong danh sách chính và học sinh có thể đăng nhập lại.</p>
        </div>
        <!-- Footer -->
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="close-restore-modal px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300">
                Hủy
            </button>
            <button type="button" id="confirmRestoreBtn" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>
                Đồng Ý
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableElement = document.getElementById('archiveTable');
    
    // Elements cho modal khôi phục
    const restoreModal = document.getElementById('restoreConfirmModal');
    const modalContent = restoreModal ? restoreModal.querySelector('.modal-content') : null;
    const confirmBtn = document.getElementById('confirmRestoreBtn');
    const studentNameEl = document.getElementById('restoreStudentName');
    
    let currentStudentId = null;
    let currentRow = null;
    let currentRestoreBtn = null;

    // Hàm mở modal
    function openRestoreModal() {
        restoreModal.classList.remove('hidden');
        // Kích hoạt animation (cần delay ngắn để browser render hidden trước)
        setTimeout(() => {
            restoreModal.classList.remove('opacity-0');
            modalContent.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            modalContent.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }, 10);
    }

    // Hàm đóng modal
    function closeRestoreModal() {
        restoreModal.classList.add('opacity-0');
        modalContent.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        modalContent.classList.add('opacity-0', 'scale-95', 'translate-y-4');
        setTimeout(() => {
            restoreModal.classList.add('hidden');
            currentStudentId = null;
            currentRow = null;
            currentRestoreBtn = null;
        }, 300); // Đợi CSS transition chạy xong
    }

    if (restoreModal) {
        // Đóng modal khi bấm nút Hủy / X
        document.querySelectorAll('.close-restore-modal').forEach(btn => {
            btn.addEventListener('click', closeRestoreModal);
        });

        // Đóng modal khi bấm ra ngoài vùng tối (backdrop)
        restoreModal.addEventListener('click', function(e) {
            if (e.target === restoreModal) {
                closeRestoreModal();
            }
        });
    }

    if (!tableElement) return;

    tableElement.addEventListener('click', function(e) {
        const restoreBtn = e.target.closest('.restore-btn');
        if (!restoreBtn) return;

        currentRow = restoreBtn.closest('tr');
        currentStudentId = currentRow.dataset.id;
        currentRestoreBtn = restoreBtn;
        
        const studentName = currentRow.cells[4].textContent;
        studentNameEl.textContent = studentName;
        
        openRestoreModal();
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function() {
            if (!currentStudentId || !currentRow || !currentRestoreBtn) return;

            const originalHtml = currentRestoreBtn.innerHTML;
            currentRestoreBtn.disabled = true;
            currentRestoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            
            // Disable nút trong modal
            const originalModalBtnHtml = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Xử lý...';

            try {
                const response = await fetch('/thidua/api/set-student-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ student_id: currentStudentId, new_status: 'dang_hoc' })
                });
                const result = await response.json();
                
                if (result.success) {
                    closeRestoreModal();
                    if (typeof showToast === 'function') {
                        showToast('success', result.message);
                    } else {
                        alert(result.message);
                    }
                    currentRow.remove();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                if (typeof showToast === 'function') {
                    showToast('error', 'Lỗi: ' + error.message);
                } else {
                    alert('Lỗi: ' + error.message);
                }
                currentRestoreBtn.disabled = false;
                currentRestoreBtn.innerHTML = originalHtml;
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalModalBtnHtml;
            }
        });
    }
});
</script>
