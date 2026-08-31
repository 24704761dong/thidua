<?php
$page_title = 'Hướng dẫn Cấu hình KXTĐ';
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-4xl mx-auto">

<div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5 my-6">
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-book-half text-[#224397]" viewBox="0 0 16 16"><path d="M8.5 2.687c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg> Hướng dẫn Cấu hình KXTĐ
            </h3>
            <div class="flex items-center gap-2">
                <a href="/thidua/admin" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors shadow-sm text-sm font-medium flex items-center gap-1.5" title="Về màn hình chính">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-house-door-fill" viewBox="0 0 16 16"><path d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.5v3.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5"/></svg>
                </a>
                <a href="/thidua/admin/cau-hinh-bao-cao" class="px-4 py-2 bg-[#224397] text-white rounded-lg hover:bg-[#1a367d] transition-all shadow-sm text-sm font-medium flex items-center gap-1.5 hover:scale-105 hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg> Quay lại Trang Cấu hình
                </a>
            </div>
        </div>

    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] mb-6 overflow-hidden">
        <div class="p-8">
            <p class="text-slate-600 mb-8 leading-relaxed text-[15px]">Tính năng này cho phép bạn định nghĩa các quy tắc để tự động đánh dấu một lớp là "KXTĐ" dựa trên các tiêu chí cụ thể trong tuần.</p>

            <h2 class="text-lg font-bold text-[#224397] mb-4 border-b border-[#224397]/25 pb-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-diagram-3-fill mr-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zm-6 8A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm6 0A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm6 0a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5z"/></svg>Luồng Hoạt Động</h2>
            <p class="text-slate-600 mb-8 leading-relaxed text-[15px]">Khi Bảng điểm thi đua được tính toán, hệ thống sẽ duyệt qua từng lớp và kiểm tra tất cả các điều kiện KXTĐ mà bạn đã <strong>kích hoạt</strong>. Nếu một lớp thỏa mãn <strong>dù chỉ một</strong> điều kiện, cột "Xếp Hạng" của lớp đó sẽ hiển thị là "KXTĐ".</p>

            <h2 class="text-lg font-bold text-[#224397] mb-4 border-b border-[#224397]/25 pb-2"><i class="bi bi-pencil-ruler mr-2"></i>Các Thành Phần Của Một Điều Kiện</h2>
            <dl class="mb-8 space-y-4">
                <div>
                    <dt class="font-bold text-slate-800">Tên/Mô tả điều kiện</dt>
                    <dd class="text-slate-600 text-[14px] mt-1 ml-4">Tên gợi nhớ cho điều kiện. Ví dụ: "Vắng KP quá 5 buổi", "Không ghi SĐB Chính Khóa".</dd>
                </div>
                
                <div>
                    <dt class="font-bold text-slate-800">Trường/Mã Cột So Sánh</dt>
                    <dd class="text-slate-600 text-[14px] mt-1 ml-4">Đây là "trái tim" của điều kiện, là mã dữ liệu của lớp mà bạn muốn kiểm tra. Hệ thống sẽ lấy giá trị của mã này để so sánh. Xem danh sách đầy đủ các mã hợp lệ ở phần dưới.</dd>
                </div>

                <div>
                    <dt class="font-bold text-slate-800">Toán tử so sánh</dt>
                    <dd class="text-slate-600 text-[14px] mt-1 ml-4">Phép so sánh sẽ được áp dụng. Ví dụ: <code class="bg-slate-100 text-[#224397] px-1.5 py-0.5 rounded text-[13px]">&gt;=</code> (lớn hơn hoặc bằng), <code class="bg-slate-100 text-[#224397] px-1.5 py-0.5 rounded text-[13px]">&lt;</code> (nhỏ hơn), <code class="bg-slate-100 text-[#224397] px-1.5 py-0.5 rounded text-[13px]">==</code> (bằng), <code class="bg-slate-100 text-[#224397] px-1.5 py-0.5 rounded text-[13px]">SDB_IS_NOT_TICKED</code> (kiểm tra Sổ đầu bài không được tick).</dd>
                </div>

                <div>
                    <dt class="font-bold text-slate-800">Ngưỡng giá trị so sánh</dt>
                    <dd class="text-slate-600 text-[14px] mt-1 ml-4">Con số để so sánh. Ví dụ, nếu bạn muốn KXTĐ khi Vắng KP từ 5 buổi trở lên, bạn sẽ chọn Trường là <code class="bg-slate-100 text-[#224397] px-1.5 py-0.5 rounded text-[13px]">vang_kp</code>, Toán tử là <code class="bg-slate-100 text-[#224397] px-1.5 py-0.5 rounded text-[13px]">&gt;=</code>, và Ngưỡng là <code class="bg-slate-100 text-[#224397] px-1.5 py-0.5 rounded text-[13px]">5</code>.</dd>
                </div>

                <div>
                    <dt class="font-bold text-slate-800">Các mục Sổ Đầu Bài áp dụng</dt>
                    <dd class="text-slate-600 text-[14px] mt-1 ml-4">Chỉ chọn các mục SĐB liên quan khi "Toán tử so sánh" là loại "Tổ hợp" hoặc "Đếm".</dd>
                </div>
            </dl>

            <h2 class="text-lg font-bold text-[#224397] mb-4 border-b border-[#224397]/25 pb-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-joystick mr-2" viewBox="0 0 16 16"><path d="M10 2a2 2 0 0 1-1.5 1.937v5.087c.863.083 1.5.377 1.5.726 0 .414-.895.75-2 .75s-2-.336-2-.75c0-.35.637-.643 1.5-.726V3.937A2 2 0 1 1 10 2"/>   <path d="M0 9.665v1.717a1 1 0 0 0 .553.894l6.553 3.277a2 2 0 0 0 1.788 0l6.553-3.277a1 1 0 0 0 .553-.894V9.665c0-.1-.06-.19-.152-.23L9.5 6.715v.993l5.227 2.178a.125.125 0 0 1 .001.23l-5.94 2.546a2 2 0 0 1-1.576 0l-5.94-2.546a.125.125 0 0 1 .001-.23L6.5 7.708l-.013-.988L.152 9.435a.25.25 0 0 0-.152.23"/></svg>Ví dụ Thực Tế</h2>
            
            <div class="bg-slate-50 border border-[#224397]/[45%] rounded-xl p-5 mb-4 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-[#FAB723]"></div>
                <h5 class="font-bold text-slate-800 mb-2">VD1: KXTĐ nếu lớp có 3 buổi Vắng Không Phép (KP) trở lên.</h5>
                <ul class="list-disc list-inside text-[14px] text-slate-600 space-y-1">
                    <li>Tên/Mô tả: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">Vắng KP >= 3</code></li>
                    <li>Trường/Mã Cột So Sánh: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">vang_kp</code></li>
                    <li>Toán tử so sánh: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">&gt;= (Lớn hơn hoặc bằng)</code></li>
                    <li>Ngưỡng giá trị: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">3</code></li>
                </ul>
            </div>
            <div class="bg-slate-50 border border-[#224397]/[45%] rounded-xl p-5 mb-4 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-[#FAB723]"></div>
                <h5 class="font-bold text-slate-800 mb-2">VD2: KXTĐ nếu lớp không ghi Sổ Đầu Bài Chính Khóa (CK).</h5>
                <ul class="list-disc list-inside text-[14px] text-slate-600 space-y-1">
                    <li>Tên/Mô tả: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">Không SĐB CK</code></li>
                    <li>Trường/Mã Cột So Sánh: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">sdb_ck</code></li>
                    <li>Toán tử so sánh: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">SĐB: Mục được chọn KHÔNG tick</code></li>
                    <li>Ngưỡng giá trị: (để trống)</li>
                </ul>
            </div>
            
            <div class="bg-slate-50 border border-[#224397]/[45%] rounded-xl p-5 mb-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-[#FAB723]"></div>
                <h5 class="font-bold text-slate-800 mb-2">VD3: KXTĐ nếu nộp DƯỚI 2 sổ (trong 3 sổ TT, CK, NK).</h5>
                <p class="text-[13px] text-slate-500 mb-4">Logic: "Dưới 2" có nghĩa là nộp 0 hoặc 1 sổ. Chúng ta cần tạo <strong>hai điều kiện riêng biệt</strong>, hệ thống sẽ tự động áp dụng nếu lớp thỏa mãn 1 trong 2 điều kiện này.</p>
                
                <div class="mb-3">
                    <strong class="text-slate-700 text-[14px]">Điều kiện A: KXTĐ nếu nộp đúng 1 sổ.</strong>
                    <ul class="list-disc list-inside text-[14px] text-slate-600 space-y-1 mt-1 ml-2">
                        <li>Tên/Mô tả: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">Chỉ nộp 1/3 sổ SĐB (TT, CK, NK)</code></li>
                        <li>Toán tử so sánh: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">Đếm: Số mục tick BẰNG ngưỡng</code></li>
                        <li>Ngưỡng giá trị: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">1</code></li>
                        <li>Các mục Sổ Đầu Bài áp dụng: Tích chọn <strong>TT</strong>, <strong>CK</strong>, và <strong>NK</strong>.</li>
                    </ul>
                </div>

                <div>
                    <strong class="text-slate-700 text-[14px]">Điều kiện B: KXTĐ nếu không nộp sổ nào.</strong>
                    <ul class="list-disc list-inside text-[14px] text-slate-600 space-y-1 mt-1 ml-2">
                        <li>Tên/Mô tả: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">Không nộp sổ SĐB nào (TT, CK, NK)</code></li>
                        <li>Toán tử so sánh: <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">Tổ hợp: TẤT CẢ mục chọn KHÔNG tick</code></li>
                        <li>Các mục Sổ Đầu Bài áp dụng: Tích chọn <strong>TT</strong>, <strong>CK</strong>, và <strong>NK</strong>.</li>
                    </ul>
                </div>
                <p class="text-[13px] text-slate-500 mt-4 border-t border-[#224397]/25 pt-3"><em><strong>Cách khác cho Điều kiện B:</strong> Bạn cũng có thể dùng toán tử đếm: "Đếm: Số mục tick BẰNG ngưỡng" với Ngưỡng giá trị là <code class="bg-white border border-[#224397]/[45%] text-[#224397] px-1.5 py-0.5 rounded">0</code>.</em></p>
            </div>
            <h2 class="text-lg font-bold text-[#224397] mb-4 border-b border-[#224397]/25 pb-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-code mr-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.646 5.646a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L10.293 8 8.646 6.354a.5.5 0 0 1 0-.708m-1.292 0a.5.5 0 0 0-.708 0l-2 2a.5.5 0 0 0 0 .708l2 2a.5.5 0 0 0 .708-.708L5.707 8l1.647-1.646a.5.5 0 0 0 0-.708"/>   <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>   <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/></svg>Danh sách Trường/Mã Cột Tham Khảo</h2>
            <p class="text-[14px] text-slate-600 mb-4">Sử dụng các mã này trong ô "Trường/Mã Cột So Sánh" khi tạo hoặc sửa một điều kiện.</p>
            <div class="overflow-x-auto w-full border border-[#224397]/[45%] rounded-lg">
                <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-300 relative">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold sticky top-0 z-10 border-b border-slate-300">
                        <tr>
                            <th class="py-3 px-4 border-r border-slate-300 w-48">Mã Cột/Trường</th>
                            <th class="py-3 px-4">Mô tả</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-300">
                        <?php foreach (($ma_cot_tham_khao ?? []) as $ma => $mo_ta): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 border-r border-slate-300 font-mono text-[#d63384] text-[13px]"><?php echo htmlspecialchars($ma); ?></td>
                            <td class="py-3 px-4 text-slate-700"><?php echo htmlspecialchars($mo_ta); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
