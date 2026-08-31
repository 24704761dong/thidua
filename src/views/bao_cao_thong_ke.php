<?php
$page_title = "Trung Tâm Báo Cáo & Thống Kê";
require_once __DIR__ . "/partials/admin_header.php";
?>

<div class="w-full px-2 lg:px-6 mt-4">
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-bar-chart-line-fill mr-2" viewBox="0 0 16 16"><path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1z"/></svg> TRUNG TÂM BÁO CÁO & THỐNG KÊ</h3>
        </div>
        <div class="p-6">
            <p class="text-sm text-slate-500 mb-6 text-center">Chọn một loại báo cáo dưới đây để xem chi tiết hoặc xuất dữ liệu.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                
                <a href="/thidua/bao-cao/thi-dua" class="flex flex-col items-center text-center p-6 bg-white border border-slate-200 rounded-xl hover:-translate-y-1 hover:shadow-lg hover:border-[#224397] transition-all duration-300 group">
                    <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-4 group-hover:bg-[#224397] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-award-fill text-3xl text-[#224397] group-hover:text-white transition-colors" viewBox="0 0 16 16"><path d="m8 0 1.669.864 1.858.282.842 1.68 1.337 1.32L13.4 6l.306 1.854-1.337 1.32-.842 1.68-1.858.282L8 12l-1.669-.864-1.858-.282-.842-1.68-1.337-1.32L2.6 6l-.306-1.854 1.337-1.32.842-1.68L6.331.864z"/><path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z"/></svg>
                    </div>
                    <h5 class="font-bold text-slate-800 text-[15px] mb-2 group-hover:text-[#224397] transition-colors">Báo Cáo Thi Đua</h5>
                    <p class="text-[13px] text-slate-500 mb-0">Xem bảng xếp hạng, điểm tổng và các điểm thành phần của các lớp theo tuần.</p>
                </a>

                <a href="/thidua/bao-cao/vi-pham" class="flex flex-col items-center text-center p-6 bg-white border border-slate-200 rounded-xl hover:-translate-y-1 hover:shadow-lg hover:border-amber-500 transition-all duration-300 group">
                    <div class="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center mb-4 group-hover:bg-amber-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle-fill text-3xl text-amber-500 group-hover:text-white transition-colors" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                    </div>
                    <h5 class="font-bold text-slate-800 text-[15px] mb-2 group-hover:text-amber-500 transition-colors">Báo Cáo Vi Phạm</h5>
                    <p class="text-[13px] text-slate-500 mb-0">Xem danh sách, thống kê chi tiết các loại vi phạm trong tuần đã chọn.</p>
                </a>

                <a href="/thidua/bao-cao/nang-cap" class="flex flex-col items-center text-center p-6 bg-white border border-slate-200 rounded-xl hover:-translate-y-1 hover:shadow-lg hover:border-purple-600 transition-all duration-300 group">
                    <div class="w-16 h-16 rounded-full bg-purple-50 flex items-center justify-center mb-4 group-hover:bg-purple-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-bar-graph-fill text-3xl text-purple-600 group-hover:text-white transition-colors" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1m.5 10v-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5m-2.5.5a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5zm-3 0a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5z"/></svg>
                    </div>
                    <h5 class="font-bold text-slate-800 text-[15px] mb-2 group-hover:text-purple-600 transition-colors">Báo Cáo Nâng Cao</h5>
                    <p class="text-[13px] text-slate-500 mb-0">Xuất các báo cáo phân tích sâu, kết hợp nhiều loại dữ liệu khác nhau.</p>
                </a>
                
                <a href="/thidua/admin/tra-cuu-hoc-sinh" class="flex flex-col items-center text-center p-6 bg-white border border-slate-200 rounded-xl hover:-translate-y-1 hover:shadow-lg hover:border-emerald-600 transition-all duration-300 group">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mb-4 group-hover:bg-emerald-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-vcard-fill text-3xl text-emerald-600 group-hover:text-white transition-colors" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm9 1.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5M9 8a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4A.5.5 0 0 0 9 8m1 2.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5m-1 2C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 0 2 13h6.96q.04-.245.04-.5M7 6a2 2 0 1 0-4 0 2 2 0 0 0 4 0"/></svg>
                    </div>
                    <h5 class="font-bold text-slate-800 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Tra Cứu Hồ Sơ</h5>
                    <p class="text-[13px] text-slate-500 mb-0">Tìm kiếm và xem thông tin chi tiết của từng học sinh trong hệ thống.</p>
                </a>
                
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/admin_footer.php"; ?>


