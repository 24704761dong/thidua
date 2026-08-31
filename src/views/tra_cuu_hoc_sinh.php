<?php
$page_title = 'Tra Cứu Hồ Sơ Học Sinh';
require_once __DIR__ . '/partials/admin_header.php';
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track { background: transparent; }
    
    /* Table chuẩn như các trang báo cáo khác */
    .log-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .log-table thead th { 
        background: #f8fafc; color: #1e293b; font-weight: 700; 
        text-transform: uppercase; font-size: 0.75rem; 
        padding: 0.85rem 0.75rem; border: 1px solid #cbd5e1; 
        white-space: nowrap; text-align: center; 
    }
    .log-table td { 
        padding: 0.85rem 0.75rem; border: 1px solid #cbd5e1; 
        vertical-align: middle; color: #334155; 
    }
    .log-table tbody tr { transition: background-color 0.15s ease; cursor: pointer; }
    .log-table tbody tr:hover { background-color: #f1f5f9; }

    /* Thanh tìm kiếm cao cấp */
    .search-input-premium {
        display: block; width: 100%; border-radius: 50px;
        border: 1px solid #cbd5e1; padding: 0.85rem 1.75rem; padding-right: 3.5rem;
        font-size: 15px; color: #1e293b; background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
    }
    .search-input-premium:focus { 
        outline: none; border-color: #224397; 
        box-shadow: 0 0 0 4px rgba(34,67,151,0.15); 
    }
</style>

<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 pb-12 mt-6">
    <!-- HEADER -->
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-[#224397] flex items-center justify-center gap-3 uppercase m-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-search text-[#FAB723]" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
            <?php echo 'Tra Cứu Hồ Sơ Học Sinh'; ?>
        </h1>
        <p class="text-sm text-slate-500 mt-2 mb-0 font-medium">Hệ thống tìm kiếm nhanh hồ sơ học sinh toàn trường theo Tên hoặc Số CCCD</p>
    </div>

    <!-- THANH TÌM KIẾM -->
    <div class="max-w-2xl mx-auto mb-10 relative">
        <input type="text" id="searchInput" class="search-input-premium" placeholder="Nhập tên hoặc Số CCCD để tìm kiếm nhanh..." autofocus>
        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-search absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
    </div>
    
    <!-- BẢNG KẾT QUẢ TÌM KIẾM (Mặc định ẩn, chỉ hiển thị khi bắt đầu gõ tìm kiếm) -->
    <div id="resultsContainer" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hidden animate-in fade-in duration-200">
        <div class="px-6 py-4 bg-[#f8fafc] border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold text-[#224397] tracking-wider uppercase">DANH SÁCH KẾT QUẢ</span>
            <span class="text-xs text-slate-500 font-medium">Bấm vào dòng học sinh để xem hồ sơ chi tiết</span>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto w-full rounded-lg border border-slate-200">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th style="width: 180px;">Số CCCD</th>
                            <th style="text-align: left; padding-left: 1.5rem;">Họ và Tên</th>
                            <th style="width: 140px;">Lớp</th>
                            <th style="width: 150px;">Ngày Sinh</th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody">
                        <!-- Kết quả sẽ được nạp qua AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsBody = document.getElementById('resultsBody');
    let searchTimeout;

    function toTitleCaseVietnamese(str) {
        if (isNaN(str)) {
            return str.toLowerCase().split(' ').map(word => {
                return word.charAt(0).toUpperCase() + word.slice(1);
            }).join(' ');
        }
        return str;
    }

    searchInput.addEventListener('input', function() {
        const originalValue = this.value;
        const formattedValue = toTitleCaseVietnamese(originalValue);
        
        if (originalValue !== formattedValue) {
            this.value = formattedValue;
        }
    });

    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            // Ẩn bảng kết quả nếu người dùng chưa nhập hoặc nhập dưới 2 ký tự
            resultsContainer.classList.add('hidden');
            return;
        }

        // Hiển thị bảng kết quả khi bắt đầu tìm kiếm
        resultsContainer.classList.remove('hidden');

        searchTimeout = setTimeout(async () => {
            resultsBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-[#224397] py-12 font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" fill="currentColor" class="bi bi-arrow-repeat mx-auto mb-3 animate-spin text-[#FAB723]" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg>
                        Đang tìm kiếm hồ sơ học sinh...
                    </td>
                </tr>`;
            
            try {
                const response = await fetch(`/thidua/api/search-students?query=${encodeURIComponent(query)}`);
                let students = await response.json();

                if (students.length === 0) {
                    resultsBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-rose-600 py-12 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" fill="currentColor" class="bi bi-x-circle mx-auto mb-3 text-rose-400" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
                                Không tìm thấy học sinh nào khớp với từ khóa "${query}".
                            </td>
                        </tr>`;
                } else {
                    students.sort((a, b) => (a.ten_lop || '').localeCompare(b.ten_lop || '', undefined, { numeric: true }));
                    let html = '';
                    students.forEach((student, index) => {
                        html += `
                            <tr data-student-id="${student.id}" title="Bấm để xem hồ sơ chi tiết">
                                <td class="text-center font-bold text-slate-400">${index + 1}</td>
                                <td class="text-center font-semibold text-[#224397]">${student.ma_hoc_sinh || student.so_cccd || 'KXD'}</td>
                                <td style="text-align: left; padding-left: 1.5rem;" class="font-bold text-slate-800">${student.ho_ten}</td>
                                <td class="text-center font-semibold text-indigo-700 bg-indigo-50/50">${student.ten_lop || 'Chưa xếp'}</td>
                                <td class="text-center text-slate-600">${student.ngay_sinh || 'N/A'}</td>
                            </tr>
                        `;
                    });
                    resultsBody.innerHTML = html;
                }
            } catch (error) {
                resultsBody.innerHTML = `<tr><td colspan="5" class="text-center text-rose-600 py-12 font-medium">Lỗi khi tìm kiếm: ${error.message}</td></tr>`;
            }
        }, 300);
    });

    resultsBody.addEventListener('click', function(e) {
        const row = e.target.closest('tr');
        if (row && row.dataset.studentId) {
            const urlParams = new URLSearchParams(window.location.search);
            const iframeParam = urlParams.get('iframe') ? '&iframe=1' : '';
            window.location.href = `/thidua/admin/hoc-sinh?action=view_profile&id=${row.dataset.studentId}${iframeParam}`;
        }
    });
});
</script>
