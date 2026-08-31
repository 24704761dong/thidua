<?php
$page_title = 'Duyệt Thông Tin Zalo Mini App';
require_once __DIR__ . '/partials/admin_header.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $db = get_db_connection();
    // Lấy danh sách yêu cầu chờ duyệt
    $stmt = $db->query("
        SELECT yc.*, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, lh.ten_lop 
        FROM yeu_cau_chinh_sua_zalo yc
        JOIN ho_so_hoc_sinh hs ON yc.hoc_sinh_id = hs.id
        LEFT JOIN quatrinh_hoc_tap qt ON hs.ma_hoc_sinh = qt.ma_hoc_sinh AND qt.nam_hoc_id = get_current_nam_hoc_id_mysql()
        LEFT JOIN lop_hoc lh ON qt.lop_hoc_id = lh.id
        WHERE yc.trang_thai = 'cho_duyet'
        ORDER BY yc.created_at DESC
    ");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}
?>

<div class="h-full flex flex-col bg-transparent relative">


    <div class="p-6 flex-1 overflow-y-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($requests)): ?>
                <div class="col-span-full py-12 text-center text-slate-500 bg-white rounded shadow-sm border border-slate-200">
                    <i class="bi bi-inbox text-4xl mb-3 block text-slate-300"></i>
                    Không có yêu cầu chỉnh sửa nào đang chờ duyệt.
                </div>
            <?php else: ?>
                <?php foreach ($requests as $req): 
                    $old = json_decode($req['thong_tin_cu'], true) ?: [];
                    $new = json_decode($req['thong_tin_moi'], true) ?: [];
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="bg-slate-50 px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-[#224397]"><?= htmlspecialchars($req['ho_dem'] . ' ' . $req['ten']) ?></div>
                            <div class="text-xs text-slate-500">
                                Lớp: <span class="font-semibold text-slate-700"><?= htmlspecialchars($req['ten_lop'] ?? 'N/A') ?></span> | 
                                Mã HS: <span class="font-semibold text-slate-700"><?= htmlspecialchars($req['ma_hoc_sinh']) ?></span>
                            </div>
                        </div>
                        <span class="text-[10px] bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-bold uppercase">
                            Chờ duyệt
                        </span>
                    </div>
                    
                    <div class="p-4 flex-1 text-sm">
                        <table class="w-full">
                            <tbody>
                                <?php foreach ($new as $key => $new_val): 
                                    $old_val = $old[$key] ?? '';
                                    if ($new_val == $old_val) continue; // Only show changes
                                    
                                    // Translate keys
                                    $labels = [
                                        'anh_the' => 'Ảnh thẻ', 'sdt' => 'SĐT', 'email' => 'Email',
                                        'ngay_sinh' => 'Ngày sinh', 'gioi_tinh' => 'Giới tính',
                                        'tinh_thanhpho' => 'Tỉnh/TP', 'xa_phuong' => 'Xã/Phường',
                                        'ap_khupho' => 'Ấp/Khu phố', 'dia_chi_chi_tiet' => 'Đ/c chi tiết'
                                    ];
                                    $label = $labels[$key] ?? $key;
                                ?>
                                <tr>
                                    <td class="py-2 text-slate-500 font-medium w-1/3 align-top"><?= $label ?></td>
                                    <td class="py-2 align-top">
                                        <?php if ($key === 'anh_the'): ?>
                                            <div class="flex gap-2 items-center">
                                                <?php if ($old_val): ?>
                                                    <img src="/thidua/public/assets/anh_the/<?= $old_val ?>" class="w-12 h-16 object-cover rounded border line-through opacity-50">
                                                <?php endif; ?>
                                                <i class="bi bi-arrow-right text-slate-400"></i>
                                                <img src="/thidua/public/assets/anh_the/<?= $new_val ?>" class="w-12 h-16 object-cover rounded border border-green-500 shadow-sm">
                                            </div>
                                        <?php else: ?>
                                            <div class="text-slate-400 line-through text-xs mb-0.5"><?= htmlspecialchars($old_val ?: '(Trống)') ?></div>
                                            <div class="text-red-600 font-semibold bg-red-50 p-1 rounded"><?= htmlspecialchars($new_val ?: '(Trống)') ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="text-xs text-slate-400 mt-4 text-right">
                            <i class="bi bi-clock"></i> Gửi lúc: <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 divide-x divide-slate-100 border-t border-slate-100">
                        <button onclick="handleRequest(<?= $req['id'] ?>, 'reject')" class="px-3 py-1.5 bg-white border border-slate-300 rounded text-slate-600 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-1 text-[12px]">
                            <i class="bi bi-x-circle"></i> Từ chối
                        </button>
                        <button onclick="handleRequest(<?= $req['id'] ?>, 'approve')" class="px-3 py-1.5 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-1 text-[12px]">
                            <i class="bi bi-check-circle"></i> Phê duyệt
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function handleRequest(id, action) {
    AppSwal.fire({
        title: 'Xác nhận',
        text: 'Bạn có chắc chắn muốn ' + (action === 'approve' ? 'phê duyệt' : 'từ chối') + ' thông tin cập nhật này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/thidua/api/quan-ly-mau-the', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'handle_zalo_edit_request', id: id, status: action })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    if (typeof showToast === 'function') {
                        showToast('Đã xử lý thành công!', 'success');
                    } else {
                        AppSwal.fire('Thành công', 'Đã thực hiện xong!', 'success');
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    AppSwal.fire('Lỗi', res.message || 'Đã có lỗi xảy ra', 'error');
                }
            })
            .catch(err => AppSwal.fire('Lỗi', 'Lỗi kết nối máy chủ', 'error'));
        }
    });
}
</script>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
