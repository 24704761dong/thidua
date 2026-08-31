<?php
$page_title = "Cài đặt Xử lý Trễ học";
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear text-[#224397]" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/></svg>
                CÀI ĐẶT XỬ LÝ TRỄ HỌC
            </h1>
        </div>
        <!-- Không có nút quay lại theo yêu cầu -->
    </div>

    <!-- Wrapper -->
    <div class="bg-white rounded shadow border border-[#224397]/25 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center justify-between">
            <h3 class="mb-0 text-[14px] font-bold text-[#224397] uppercase flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-link mr-2" viewBox="0 0 16 16"><path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9q-.13 0-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/><path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4 4 0 0 1-.82 1H12a3 3 0 1 0 0-6z"/></svg> 
                Liên kết Lỗi Vi phạm
            </h3>
        </div>

        <div class="p-4 sm:p-6">
            <p class="text-sm text-slate-500 mb-6 italic">
                Chọn <b>lỗi vi phạm tương ứng</b> cho trường hợp "Đi trễ". Khi hoàn tất xử lý đi trễ, hệ thống sẽ tự động tạo vi phạm với lỗi đã cấu hình tại đây.
            </p>

            <form id="settingForm" class="needs-validation" novalidate>
                <div class="flex flex-wrap -mx-3 gap-y-4">
                    <div class="w-full px-3">
                        <label for="loi_di_tre" class="block text-[13px] font-semibold text-[#224397] mb-1">Khi học sinh "Đi trễ" <span class="text-red-500">*</span></label>
                        <select class="block w-full rounded border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 text-[13px] p-2 border" id="loi_di_tre" name="trehoc_loi_vi_pham" required>
                            <option value="">-- Chọn lỗi vi phạm tương ứng --</option>
                            <?php foreach ($danh_sach_vi_pham as $vp): ?>
                                <option value="<?php echo $vp['id']; ?>" <?php echo ($cai_dat_di_tre == $vp['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vp['ten_vi_pham']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback text-red-500 text-xs mt-1 hidden">Vui lòng chọn lỗi cho trường hợp Đi trễ.</div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6 pt-4 border-t border-slate-200">
                    <span id="saveStatus" class="text-slate-500 text-sm"></span>
                    <button type="submit" id="saveBtn" class="px-5 py-2 bg-[#224397] text-white rounded font-semibold text-[13px] shadow-sm hover:bg-[#FAB723] hover:text-slate-900 transition-colors flex items-center gap-2 min-w-[120px] justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-floppy2-fill" viewBox="0 0 16 16"><path d="M12 2h-2v3h2V2Z"/><path d="M1.5 0A1.5 1.5 0 0 0 0 1.5v13A1.5 1.5 0 0 0 1.5 16h13a1.5 1.5 0 0 0 1.5-1.5V2.914a1.5 1.5 0 0 0-.44-1.06L14.147.439A1.5 1.5 0 0 0 13.086 0H1.5ZM4 6a1 1 0 0 1-1-1V1h10v4a1 1 0 0 1-1 1H4ZM3 9h10a1 1 0 0 1 1 1v5H2v-5a1 1 0 0 1 1-1Z"/></svg>
                        Lưu Cài đặt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
<script>
document.getElementById('settingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const select = document.getElementById('loi_di_tre');
    const invalidFeedback = select.nextElementSibling;
    
    if (!select.value) {
        invalidFeedback.classList.remove('hidden');
        return;
    }
    invalidFeedback.classList.add('hidden');

    const btn = document.getElementById('saveBtn');
    const status = document.getElementById('saveStatus');
    const prev = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Đang lưu...';
    status.innerHTML = '';
    
    const data = { trehoc_loi_vi_pham: select.value };
    
    try {
        const response = await fetch('/thidua/api/admin/tre-hoc/luu-cai-dat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if(result.success) {
            showToast('success', result.message || 'Lưu cài đặt thành công');
            status.innerHTML = '<span class="text-emerald-600 font-medium">Đã lưu cài đặt</span>';
        } else {
            showToast('error', result.message || 'Lỗi khi lưu cài đặt');
        }
    } catch (err) {
        showToast('error', 'Lỗi kết nối. Vui lòng thử lại sau.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = prev;
    }
});
</script>
