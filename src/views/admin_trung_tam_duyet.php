<?php
$page_title = 'Trung Tâm Phê Duyệt';
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="w-full px-2 lg:px-6 mt-4">
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check2-square mr-2 text-[#FAB723]" viewBox="0 0 16 16"><path d="M3 14.5A1.5 1.5 0 0 1 1.5 13V3A1.5 1.5 0 0 1 3 1.5h8a.5.5 0 0 1 0 1H3a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V8a.5.5 0 0 1 1 0v5a1.5 1.5 0 0 1-1.5 1.5z"/><path d="m8.354 10.354 7-7a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0"/></svg>
                TRUNG TÂM PHÊ DUYỆT
            </h3>
            <p class="text-[12px] text-slate-500 mb-0 font-medium italic">Vui lòng chọn một trong các nội dung bên dưới để tiến hành xem xét và phê duyệt.</p>
        </div>
        
        <div class="p-6 bg-slate-50/50">
            <div class="flex flex-wrap -mx-3 justify-center">
                <!-- Duyệt Đăng Ký Trực -->
                <div class="w-full md:w-1/2 px-3 mb-6">
                    <div class="bg-white rounded border border-[#224397]/20 shadow-sm h-full flex flex-col items-center text-center p-8 transition-all duration-300 hover:shadow-md hover:border-[#224397]/50 hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 mb-4 shadow-sm border border-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" fill="currentColor" class="bi bi-calendar-check" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                        </div>
                        <h5 class="text-[16px] font-bold text-slate-800 mb-2">Duyệt Đăng Ký Trực</h5>
                        <p class="text-[13px] text-slate-500 mb-6 flex-1">Xem và xác nhận lịch đăng ký trực tuần của các lớp.</p>
                        
                        <a href="/thidua/quan-ly-dang-ky-truc" class="px-4 py-2 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center justify-center gap-1.5 text-[12px] shadow-sm w-full max-w-[200px]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-right-circle" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/></svg>
                            Đi đến trang duyệt
                        </a>
                    </div>
                </div>

                <!-- Duyệt Vi Phạm -->
                <div class="w-full md:w-1/2 px-3 mb-6">
                    <div class="bg-white rounded border border-[#224397]/20 shadow-sm h-full flex flex-col items-center text-center p-8 transition-all duration-300 hover:shadow-md hover:border-red-400 hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-red-500 mb-4 shadow-sm border border-red-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="2.2em" height="2.2em" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                        </div>
                        <h5 class="text-[16px] font-bold text-slate-800 mb-2">Duyệt Vi Phạm</h5>
                        <p class="text-[13px] text-slate-500 mb-6 flex-1">Phê duyệt hoặc từ chối các vi phạm do Cộng tác viên nhập.</p>
                        
                        <a href="/thidua/admin/duyet-vi-pham" class="px-4 py-2 bg-red-600 border border-transparent rounded text-white hover:bg-red-700 transition-colors font-medium flex items-center justify-center gap-1.5 text-[12px] shadow-sm w-full max-w-[200px]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-fill-check" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263C10.228.826 9.11.47 8 0zm2.146 5.146a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647z"/></svg>
                            Đi đến trang duyệt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>