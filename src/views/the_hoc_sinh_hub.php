<?php
// File: src/views/the_hoc_sinh_hub.php
$page_title = 'Quản Lý Thẻ Học Sinh';
require_once __DIR__ . '/partials/admin_header.php';
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8 border-b border-slate-200 pb-4">
        <h1 class="text-xl font-bold text-[#224397] uppercase flex items-center gap-2 m-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" class="bi bi-person-badge text-[#FAB723]" viewBox="0 0 16 16"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492z"/></svg>
            Quản Lý Thẻ Học Sinh
        </h1>
        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-wider">Hệ Thống Trung Tâm</span>
    </div>

    <!-- GRID MENU -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- CARD 1 -->
        <a href="/thidua/admin/the-hoc-sinh/danh-sach" class="group bg-white border border-[#224397]/20 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:border-[#224397] hover:-translate-y-1 transition-all duration-300 text-decoration-none flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-2xl bg-indigo-50 flex items-center justify-center group-hover:bg-[#224397] transition-colors duration-300 mb-6 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" fill="currentColor" class="bi bi-list-ul text-[#224397] group-hover:text-white transition-colors duration-300" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
            </div>
            <h4 class="text-lg font-bold text-slate-800 group-hover:text-[#224397] transition-colors duration-200 mb-2">Danh Sách & In Thẻ</h4>
            <p class="text-sm text-slate-500 mb-0 leading-relaxed">Xem, quản lý thông tin và thực hiện in thẻ hàng loạt cho học sinh theo từng lớp học.</p>
        </a>
        
        <!-- CARD 2 -->
        <a href="/thidua/admin/quan-ly-anh-the" class="group bg-white border border-[#224397]/20 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:border-[#224397] hover:-translate-y-1 transition-all duration-300 text-decoration-none flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-2xl bg-cyan-50 flex items-center justify-center group-hover:bg-[#224397] transition-colors duration-300 mb-6 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" fill="currentColor" class="bi bi-images text-cyan-600 group-hover:text-white transition-colors duration-300" viewBox="0 0 16 16"><path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/></svg>
            </div>
            <h4 class="text-lg font-bold text-slate-800 group-hover:text-[#224397] transition-colors duration-200 mb-2">Quản Lý Thư Viện Ảnh</h4>
            <p class="text-sm text-slate-500 mb-0 leading-relaxed">Tải lên, xem, xóa và đồng bộ ảnh thẻ của học sinh một cách tập trung và nhanh chóng.</p>
        </a>

        <!-- CARD 3 -->
        <a href="/thidua/admin/the-hoc-sinh/cai-dat" class="group bg-white border border-[#224397]/20 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:border-[#224397] hover:-translate-y-1 transition-all duration-300 text-decoration-none flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-2xl bg-emerald-50 flex items-center justify-center group-hover:bg-[#224397] transition-colors duration-300 mb-6 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" fill="currentColor" class="bi bi-palette-fill text-emerald-600 group-hover:text-white transition-colors duration-300" viewBox="0 0 16 16"><path d="M12.433 10.07C14.133 10.585 16 11.15 16 8a8 8 0 1 0-8 8c1.996 0 1.826-1.504 1.649-3.08-.124-1.101-.252-2.237.351-2.92.465-.527 1.42-.237 2.433.07M8 5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m4.5 3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3M5 6.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/></svg>
            </div>
            <h4 class="text-lg font-bold text-slate-800 group-hover:text-[#224397] transition-colors duration-200 mb-2">Thiết Kế Mẫu Thẻ</h4>
            <p class="text-sm text-slate-500 mb-0 leading-relaxed">Tùy chỉnh phôi thẻ, bố cục và các trường thông tin sẽ hiển thị trên thẻ học sinh.</p>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
