<?php
// File: src/views/admin_duyet_vang_hoc.php
$page_title = 'Duyệt Xin Vắng Học';
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
/* Override background để phủ full tab */
body, body > div.w-full.min-h-screen.bg-slate-50 {
    background: linear-gradient(to bottom right, #f8fafc, #E4F6FD) !important;
}

/* Ẩn thanh cuộn dọc lớn bên phải */
body::-webkit-scrollbar, html::-webkit-scrollbar {
    display: none;
}
body, html {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Custom scrollbar matching the school theme */
.list-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
.list-scrollbar::-webkit-scrollbar-track { background: #eef2ff; border-left: 1px solid #e2e8f0; }
.list-scrollbar::-webkit-scrollbar-thumb { background: #224397; border-radius: 4px; border: 1px solid #eef2ff; }
.list-scrollbar::-webkit-scrollbar-thumb:hover { background: #FAB723; }

/* Image preview hover effect */
.img-preview { max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 1px solid #dee2e6; transition: transform 0.2s; }
.img-preview:hover { transform: scale(1.05); }
</style>

<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-check" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg> 
                Duyệt Xin Vắng Học
            </h3>
        </div>

        <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden mb-6">
            <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/></svg> Lọc Trạng Thái
            </div>
            <div class="p-5">
                <form method="GET" action="/thidua/admin/duyet-vang-hoc" class="flex items-end gap-4">
                    <div>
                        <select name="status" class="px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397] focus:border-[#224397] outline-none transition text-sm" onchange="this.form.submit()">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                            <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="2" <?= $status_filter === '2' ? 'selected' : '' ?>>Từ chối</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden">
            <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> Danh sách Đơn Xin Vắng Học
            </div>
            <div class="overflow-x-auto list-scrollbar max-h-[60vh]">
                <table class="w-full text-left text-sm text-slate-600 border-collapse relative">
                    <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0 z-10">
                        <tr>
                            <th class="px-5 py-3">ID</th>
                            <th class="px-5 py-3">Học Sinh</th>
                            <th class="px-5 py-3">Lớp</th>
                            <th class="px-5 py-3">Thời Gian Nghỉ</th>
                            <th class="px-5 py-3">Lý Do</th>
                            <th class="px-5 py-3 text-center">Minh Chứng</th>
                            <th class="px-5 py-3 text-center">Trạng Thái</th>
                            <th class="px-5 py-3 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#224397]/20">
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">Không có đơn xin vắng học nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-4 font-medium text-slate-700">#<?= $req['id'] ?></td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($req['ho_ten']) ?></div>
                                        <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($req['ma_hoc_sinh']) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            <?= htmlspecialchars($req['ten_lop'] ?? 'Chưa xếp lớp') ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-xs">
                                        <div class="text-slate-700 mb-1">Từ: <span class="font-semibold"><?= date('d/m/Y', strtotime($req['tu_ngay'])) ?></span></div>
                                        <div class="text-slate-700">Đến: <span class="font-semibold"><?= date('d/m/Y', strtotime($req['den_ngay'])) ?></span></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm text-slate-700 max-h-20 overflow-y-auto list-scrollbar pr-2">
                                            <?= nl2br(htmlspecialchars($req['ly_do'])) ?>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-clock"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/></svg>
                                            <?= date('d/m/Y H:i', strtotime($req['ngay_tao'])) ?>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <?php if ($req['minh_chung_url']): ?>
                                            <img src="<?= $req['minh_chung_url'] ?>" class="img-preview inline-block" alt="Minh chứng" onclick="openImage('<?= $req['minh_chung_url'] ?>')">
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 italic">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <?php if ($req['trang_thai'] == 0): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.99.14q-.04.281-.108.552zm-.398 1.584-.96-.326a7 7 0 0 0 .287-.508l.893.448a8 8 0 0 1-.22.386zm-.761 1.393c-.086.115-.176.227-.27.336l-.723-.692a7 7 0 0 0 .215-.262zm-.99 1.066c-.146.12-.296.236-.45.347l-.548-.838a7 7 0 0 0 .344-.257zM8 15a7 7 0 1 1 0-14 7 7 0 0 1 0 14m0-1A6 6 0 1 0 8 2a6 6 0 0 0 0 12"/><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/></svg> Chờ duyệt
                                            </span>
                                        <?php elseif ($req['trang_thai'] == 1): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg> Đã duyệt
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg> Từ chối
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex flex-col gap-2 w-28 mx-auto">
                                            <?php if ($req['trang_thai'] == 0): ?>
                                                <button onclick="updateStatus(<?= $req['id'] ?>, 1)" class="w-full px-3 py-1.5 bg-emerald-600 text-white rounded shadow-sm hover:bg-emerald-500 font-medium text-xs transition flex items-center justify-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg> Duyệt
                                                </button>
                                                <button onclick="updateStatus(<?= $req['id'] ?>, 2)" class="w-full px-3 py-1.5 bg-rose-600 text-white rounded shadow-sm hover:bg-rose-500 font-medium text-xs transition flex items-center justify-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg> Từ chối
                                                </button>
                                            <?php elseif ($req['trang_thai'] == 1): ?>
                                                <button onclick="updateStatus(<?= $req['id'] ?>, 2)" class="w-full px-3 py-1.5 bg-white border border-rose-300 text-rose-600 rounded shadow-sm hover:bg-rose-50 font-medium text-xs transition flex items-center justify-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg> Hủy Duyệt
                                                </button>
                                            <?php elseif ($req['trang_thai'] == 2): ?>
                                                <button onclick="updateStatus(<?= $req['id'] ?>, 1)" class="w-full px-3 py-1.5 bg-white border border-emerald-300 text-emerald-600 rounded shadow-sm hover:bg-emerald-50 font-medium text-xs transition flex items-center justify-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg> Duyệt Lại
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<!-- Modal xem ảnh lớn -->
<div id="imageModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal()">
    <div class="modal-content relative bg-transparent rounded w-full max-w-4xl p-4 flex justify-center items-center transform transition-all duration-300 scale-95 opacity-0" onclick="event.stopPropagation()">
        <button type="button" class="absolute top-2 right-2 text-white/70 hover:text-white bg-black/40 hover:bg-black/60 rounded-full p-2 transition z-10" onclick="closeModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
        </button>
        <img id="largeImage" src="" class="max-w-full max-h-[85vh] rounded shadow-2xl" alt="Minh chứng">
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function openImage(url) {
        document.getElementById('largeImage').src = url;
        const modal = document.getElementById('imageModal');
        const modalContent = modal.querySelector('.modal-content');
        
        modal.classList.remove('hidden');
        // Force reflow
        void modal.offsetWidth;
        
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }

    function closeModal() {
        const modal = document.getElementById('imageModal');
        const modalContent = modal.querySelector('.modal-content');
        
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function updateStatus(id, status) {
        let actionText = status === 1 ? 'duyệt' : 'từ chối';
        let actionColor = status === 1 ? '#10b981' : '#e11d48'; // Emerald 500 / Rose 600
        
        Swal.fire({
            title: 'Xác nhận',
            text: `Bạn có chắc chắn muốn ${actionText} đơn xin vắng học này?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: actionColor,
            cancelButtonColor: '#64748b', // Slate 500
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy',
            customClass: {
                confirmButton: 'rounded shadow-sm',
                cancelButton: 'rounded shadow-sm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('/thidua/api/admin/xu-ly-vang-hoc', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id + '&status=' + status
                })
                .then(response => response.text())
                .then(rawResponse => {
                    let response;
                    try {
                        let start = rawResponse.indexOf('{');
                        let end = rawResponse.lastIndexOf('}');
                        if (start !== -1 && end !== -1) {
                            response = JSON.parse(rawResponse.substring(start, end + 1));
                        } else {
                            response = JSON.parse(rawResponse);
                        }
                    } catch(e) {
                        alert("Lỗi phân tích JSON: " + rawResponse);
                        Swal.fire('Lỗi!', 'Dữ liệu không hợp lệ.', 'error');
                        return;
                    }
                    
                    if (response && response.success) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        alert("Lỗi từ máy chủ: " + (response ? response.message : rawResponse));
                        Swal.fire('Lỗi!', response ? response.message : 'Có lỗi xảy ra.', 'error');
                    }
                })
                .catch(error => {
                    alert("Lỗi FETCH API: " + error);
                    Swal.fire('Lỗi!', 'Không thể kết nối đến máy chủ: ' + error, 'error');
                });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
