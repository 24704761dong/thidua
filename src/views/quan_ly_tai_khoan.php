<?php
$page_title = 'Quản Lý Tài Khoản';
require_once __DIR__ . '/partials/admin_header.php';
// Nạp file cấu hình quyền hạn
$permission_groups = require __DIR__ . '/../../config/permissions.php';
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
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}

/* Custom scrollbar with arrows matching the school theme */
.list-scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
.list-scrollbar::-webkit-scrollbar-track {
    background: #eef2ff;
    border-left: 1px solid #e2e8f0;
}
.list-scrollbar::-webkit-scrollbar-thumb {
    background: #224397;
    border-radius: 4px;
    border: 1px solid #eef2ff;
}
.list-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #FAB723;
}
.list-scrollbar::-webkit-scrollbar-button:single-button {
    background-color: #eef2ff;
    display: block;
    height: 10px;
    width: 8px;
}
/* Up arrow */
.list-scrollbar::-webkit-scrollbar-button:single-button:vertical:decrement {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='rgb(34, 67, 151)'><polygon points='50,20 10,80 90,80'/></svg>");
    background-size: 6px;
    background-position: center 3px;
    background-repeat: no-repeat;
}
.list-scrollbar::-webkit-scrollbar-button:single-button:vertical:decrement:hover {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='rgb(250, 183, 35)'><polygon points='50,20 10,80 90,80'/></svg>");
}
/* Down arrow */
.list-scrollbar::-webkit-scrollbar-button:single-button:vertical:increment {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='rgb(34, 67, 151)'><polygon points='10,20 90,20 50,80'/></svg>");
    background-size: 6px;
    background-position: center 2px;
    background-repeat: no-repeat;
}
.list-scrollbar::-webkit-scrollbar-button:single-button:vertical:increment:hover {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' fill='rgb(250, 183, 35)'><polygon points='10,20 90,20 50,80'/></svg>");
}
</style>

<!-- Page Content -->
<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg> Quản Lý Tài Khoản Hệ Thống
            </h3>
            <button id="addUserBtn" class="px-4 py-2 bg-[#224397] text-white rounded shadow-sm hover:bg-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>   <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg> <span class="hidden md:inline">Tạo Tài Khoản Mới</span><span class="inline md:hidden">Tạo Mới</span>
            </button>
        </div>

        <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden">
            <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> Danh sách tài khoản
            </div>
            <div class="overflow-x-auto list-scrollbar max-h-[70vh]">
                <table class="w-full text-left text-sm text-slate-600 border-collapse relative">
                    <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0 z-10">
                        <tr>
                            <th class="px-5 py-3">Tài Khoản</th>
                            <th class="px-5 py-3">Vai Trò</th>
                            <th class="px-5 py-3 text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#224397]/20">
                        <?php if (empty($danh_sach_user)): ?>
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">Chưa có tài khoản nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($danh_sach_user as $user): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center">
                                            <img src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : '/thidua/public/assets/img/favicon.ico'; ?>" 
                                                 alt="Avatar" class="w-10 h-10 rounded-full mr-3 border-2 border-blue-100 shadow-sm transition-transform hover:scale-110">
                                            <div>
                                                <div class="font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($user['ho_ten']); ?></div>
                                                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-envelope text-slate-400 shrink-0" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/></svg> <span><?php echo !empty($user['email']) ? htmlspecialchars($user['email']) : '<i class="text-slate-400">Chưa có Email</i>'; ?></span></div>
                                                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-telephone text-slate-400 shrink-0" viewBox="0 0 16 16"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/></svg> <span><?php echo !empty($user['sdt']) ? htmlspecialchars($user['sdt']) : '<i class="text-slate-400">Chưa có SĐT</i>'; ?></span></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php if($user['vai_tro'] === 'admin'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-[#224397] border border-blue-200">Admin</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-[#224397]/25">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button class="px-3 py-1.5 bg-white border border-slate-300 rounded shadow-sm text-sm font-medium text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center editUserBtn" data-id="<?php echo $user['id']; ?>" title="Sửa thông tin"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg></button>
                                            <button class="px-3 py-1.5 bg-white border border-slate-300 rounded shadow-sm text-sm font-medium text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center changePasswordBtn" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['ten_dang_nhap']); ?>" title="Đổi mật khẩu"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg></button>
                                            <?php if($user['vai_tro'] === 'admin'): ?>
                                            <button class="px-3 py-1.5 bg-white border border-indigo-200 rounded shadow-sm text-sm font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center appKeyBtn" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['ten_dang_nhap']); ?>" data-appkey="<?php echo htmlspecialchars($user['app_key'] ?? ''); ?>" data-ip="<?php echo htmlspecialchars($user['app_key_ip'] ?? ''); ?>" data-machine="<?php echo htmlspecialchars($user['app_key_machine'] ?? ''); ?>" data-time="<?php echo htmlspecialchars($user['app_key_activated_at'] ?? ''); ?>" title="Quản lý App Key"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.198.803.198 1.189 0a11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"/></svg></button>
                                            <?php endif; ?>
                                            <button class="px-3 py-1.5 bg-white border border-red-200 rounded shadow-sm text-sm font-medium text-red-600 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center justify-center deleteUserBtn" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['ten_dang_nhap']); ?>" title="Xóa tài khoản"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg></button>
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

<!-- Modal User -->
<div id="userModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('userModal')">
    <div class="modal-content bg-white rounded shadow-2xl w-[800px] max-w-[95%] max-h-[95vh] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
        <form id="userForm" action="/thidua/admin/tai-khoan?action=add" method="POST" class="flex flex-col h-full max-h-[95vh]">
            <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
                <h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg" id="userModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>   <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg> Tạo Tài Khoản Mới
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal('userModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            
            <div class="p-6 overflow-y-auto list-scrollbar bg-white flex-1 space-y-4">
                <input type="hidden" id="user_id" name="user_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ten_dang_nhap" class="block text-sm font-semibold text-slate-700 mb-1">Tên Đăng Nhập *</label>
                        <input type="text" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="ten_dang_nhap" name="ten_dang_nhap" required>
                    </div>
                    <div>
                        <label for="ho_ten" class="block text-sm font-semibold text-slate-700 mb-1">Họ Và Tên</label>
                        <input type="text" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="ho_ten" name="ho_ten">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                        <input type="email" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="email" name="email">
                    </div>
                    <div>
                        <label for="sdt" class="block text-sm font-semibold text-slate-700 mb-1">Số điện thoại</label>
                        <input type="text" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="sdt" name="sdt">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="vai_tro" class="block text-sm font-semibold text-slate-700 mb-1">Vai Trò *</label>
                        <select class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="vai_tro" name="vai_tro" required>
                            <option value="admin">Admin (Toàn quyền)</option>
                            <option value="user">User (Quyền tùy chỉnh)</option>
                        </select>
                    </div>
                    <div id="password-field">
                        <label for="mat_khau" class="block text-sm font-semibold text-slate-700 mb-1">Mật Khẩu *</label>
                        <input type="password" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="mat_khau" name="mat_khau">
                    </div>
                </div>
                <div>
                    <label for="ghi_chu" class="block text-sm font-semibold text-slate-700 mb-1">Ghi Chú</label>
                    <textarea class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="ghi_chu" name="ghi_chu" rows="2"></textarea>
                </div>

                <div id="permissions-section" class="hidden mt-4 pt-4 border-t border-[#224397]/25">
                    <h6 class="font-bold text-[#224397] mb-3">Phân Quyền Cho User</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($permission_groups as $group_key => $group): ?>
                        <div class="permission-group">
                            <h6 class="text-sm font-semibold text-slate-700 mb-2 border-b border-[#224397]/25 pb-1"><?php echo htmlspecialchars($group['title']); ?></h6>
                            <div class="space-y-2">
                            <?php foreach ($group['permissions'] as $key => $perm): ?>
                                <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer hover:text-[#224397] transition">
                                    <input type="checkbox" name="permissions[]" value="<?php echo $key; ?>" id="perm_<?php echo $key; ?>" class="w-4 h-4 text-[#224397] border-slate-300 rounded focus:ring-[#224397]">
                                    <?php echo htmlspecialchars($perm['label']); ?>
                                </label>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300" onclick="closeModal('userModal')">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg> Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Password Modal -->
<div id="passwordModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('passwordModal')">
    <div class="modal-content bg-white rounded shadow-2xl w-[500px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
        <form id="passwordForm" action="/thidua/admin/tai-khoan?action=change_password" method="POST">
            <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center">
                <h5 class="text-[#224397] font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> Đổi Mật Khẩu
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal('passwordModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
            </div>
            <div class="p-5 space-y-4 bg-white">
                <input type="hidden" id="password_user_id" name="user_id">
                <p class="text-sm text-slate-600">Đang đổi mật khẩu cho tài khoản: <strong id="usernameForPasswordChange" class="text-[#224397]"></strong></p>
                
                <div>
                    <label for="new_password" class="block text-sm font-semibold text-slate-700 mb-1">Mật khẩu mới *</label>
                    <input type="password" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="new_password" name="new_password" required>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-1">Xác nhận mật khẩu mới *</label>
                    <input type="password" class="w-full px-4 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300" onclick="closeModal('passwordModal')">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg> Lưu Mật Khẩu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- App Key Modal -->
<div id="appKeyModal" class="hidden fixed inset-0 z-[1050] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('appKeyModal')">
    <div class="modal-content bg-white rounded shadow-2xl w-[500px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center">
            <h5 class="text-indigo-600 font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.198.803.198 1.189 0a11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"/></svg> Quản lý App Key Desktop
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal('appKeyModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        </div>
        <div class="p-5 space-y-4 bg-white text-center">
            <input type="hidden" id="app_key_user_id">
            <p class="text-sm text-slate-600 mb-2">Tài khoản: <strong id="usernameForAppKey" class="text-[#224397]"></strong></p>
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                <p class="text-xs text-indigo-700 font-medium mb-2 uppercase tracking-wide">Key Hiện Tại</p>
                <div class="flex items-center justify-center gap-2">
                    <code id="currentAppKey" class="text-lg font-bold text-slate-800 tracking-wider">Chưa có</code>
                    <button id="copyAppKeyBtn" class="hidden text-indigo-600 hover:text-indigo-800" onclick="copyAppKey()" title="Sao chép">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg>
                    </button>
                </div>
                <div id="appKeyInfo" class="mt-4 pt-3 border-t border-indigo-200 hidden text-left text-sm text-slate-600 space-y-1.5 bg-white p-3 rounded shadow-sm">
                    <p>🌐 <strong>IP Kích Hoạt:</strong> <span id="infoIp" class="text-indigo-700 font-medium"></span></p>
                    <p>💻 <strong>Tên Máy:</strong> <span id="infoMachine" class="text-indigo-700 font-medium"></span></p>
                    <p>⏱ <strong>Thời Gian:</strong> <span id="infoTime" class="text-indigo-700 font-medium"></span></p>
                </div>
            </div>
            <p class="text-xs text-red-500 bg-red-50 p-2 rounded text-left mt-2">
                <strong>Lưu ý:</strong> Khi tài khoản Admin có App Key, họ BẮT BUỘC phải dùng phần mềm Desktop .exe đã kích hoạt bằng Key này mới đăng nhập được. Đăng nhập qua Chrome/Cốc Cốc sẽ bị chặn.
            </p>
        </div>
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-between gap-2">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-slate-100 font-medium transition-all duration-300" onclick="closeModal('appKeyModal')">Đóng</button>
            <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 font-medium shadow-sm transition-all duration-300 flex items-center gap-2" onclick="generateAppKey()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg> Tạo / Làm Mới Key
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
// Modal Animation Utility
function openModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.modal-content');
    modal.classList.remove('hidden');
    // Force reflow
    void modal.offsetWidth;
    // Add active classes
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.modal-content');
    // Remove active classes
    modal.classList.add('opacity-0');
    content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    // Wait for transition, then hide
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

document.addEventListener('DOMContentLoaded', function() {
    const userForm = document.getElementById('userForm');
    const passwordForm = document.getElementById('passwordForm');
    const modalLabel = document.getElementById('userModalLabel');
    const passwordField = document.getElementById('password-field');
    const passwordInput = document.getElementById('mat_khau');
    const usernameForPasswordChange = document.getElementById('usernameForPasswordChange');
    const passwordUserIdInput = document.getElementById('password_user_id');
    
    const roleSelect = document.getElementById('vai_tro');
    const permissionsSection = document.getElementById('permissions-section');
    const allPermissionCheckboxes = permissionsSection.querySelectorAll('input[type="checkbox"]');

    function togglePermissionsSection() {
        if (roleSelect.value === 'user') {
            permissionsSection.classList.remove('hidden');
        } else {
            permissionsSection.classList.add('hidden');
            allPermissionCheckboxes.forEach(cb => cb.checked = false);
        }
    }

    roleSelect.addEventListener('change', togglePermissionsSection);

    const urlParams = new URLSearchParams(window.location.search);
    const iframeParam = urlParams.has('iframe') ? '&iframe=1' : '';

    userForm.addEventListener('submit', function(e) {
        if (passwordInput.required && !passwordInput.value) {
            e.preventDefault();
            alert('Vui lòng nhập mật khẩu!');
        }
    });

    document.getElementById('addUserBtn').addEventListener('click', function() {
        modalLabel.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>   <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg> Tạo Tài Khoản Mới';
        userForm.action = '/thidua/admin/tai-khoan?action=add' + iframeParam;
        userForm.reset();
        allPermissionCheckboxes.forEach(cb => cb.checked = false);
        document.getElementById('user_id').value = '';
        passwordField.style.display = 'block';
        passwordInput.required = true;
        togglePermissionsSection();
        openModal('userModal');
    });

    document.querySelectorAll('.editUserBtn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            modalLabel.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg> Cập Nhật Thông Tin';
            userForm.action = '/thidua/admin/tai-khoan?action=edit' + iframeParam;
            
            fetch('/thidua/admin/tai-khoan?action=api_get&id=' + userId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('user_id').value = data.id;
                    document.getElementById('ten_dang_nhap').value = data.ten_dang_nhap;
                    document.getElementById('ho_ten').value = data.ho_ten || '';
                    document.getElementById('email').value = data.email || '';
                    document.getElementById('sdt').value = data.sdt || '';
                    document.getElementById('vai_tro').value = data.vai_tro;
                    document.getElementById('ghi_chu').value = data.ghi_chu || '';

                    if (data.quyen_han) {
                        try {
                            const userPermissions = JSON.parse(data.quyen_han);
                            if (Array.isArray(userPermissions)) {
                                userPermissions.forEach(permKey => {
                                    const checkbox = document.getElementById(`perm_${permKey}`);
                                    if (checkbox) checkbox.checked = true;
                                });
                            }
                        } catch (e) {
                            console.error("Lỗi parse JSON quyền hạn:", e);
                        }
                    }

                    passwordField.style.display = 'none';
                    passwordInput.required = false;
                    togglePermissionsSection();
                    openModal('userModal');
                })
                .catch(error => {
                    console.error('Lỗi khi lấy thông tin người dùng:', error);
                    alert('Không thể tải thông tin người dùng. Vui lòng kiểm tra lại.');
                });
        });
    });

    document.querySelectorAll('.changePasswordBtn').forEach(button => {
        button.addEventListener('click', function() {
            passwordUserIdInput.value = this.dataset.id;
            usernameForPasswordChange.textContent = this.dataset.username;
            passwordForm.reset();
            passwordForm.action = '/thidua/admin/tai-khoan?action=change_password' + iframeParam;
            openModal('passwordModal');
        });
    });
    
    // Modal App Key
    document.querySelectorAll('.appKeyBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            const currentKey = this.getAttribute('data-appkey');
            const ip = this.getAttribute('data-ip');
            const machine = this.getAttribute('data-machine');
            const time = this.getAttribute('data-time');
            
            document.getElementById('app_key_user_id').value = userId;
            document.getElementById('usernameForAppKey').textContent = username;
            
            const keyElem = document.getElementById('currentAppKey');
            const copyBtn = document.getElementById('copyAppKeyBtn');
            const appKeyInfo = document.getElementById('appKeyInfo');
            
            if (currentKey && currentKey.trim() !== '') {
                keyElem.textContent = currentKey;
                keyElem.classList.remove('text-slate-800');
                keyElem.classList.add('text-indigo-600');
                copyBtn.classList.remove('hidden');
                
                if (ip && ip.trim() !== '') {
                    appKeyInfo.classList.remove('hidden');
                    document.getElementById('infoIp').textContent = ip;
                    document.getElementById('infoMachine').textContent = machine || 'Không xác định';
                    document.getElementById('infoTime').textContent = time || 'Không xác định';
                } else {
                    appKeyInfo.classList.add('hidden');
                }
            } else {
                keyElem.textContent = 'Chưa có';
                keyElem.classList.remove('text-indigo-600');
                keyElem.classList.add('text-slate-800');
                copyBtn.classList.add('hidden');
                appKeyInfo.classList.add('hidden');
            }
            
            openModal('appKeyModal');
        });
    });
    
    document.querySelectorAll('.deleteUserBtn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            const username = this.dataset.username;

            AppSwal.fire({
                title: 'Xóa Tài Khoản?',
                text: `Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản "${username}" không?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Đồng Ý Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/thidua/admin/tai-khoan?action=api_delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: userId, username: username })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (typeof showToast === 'function') {
                            showToast(data.success ? 'success' : 'error', data.message);
                        } else {
                            alert(data.message);
                        }
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        if (typeof showToast === 'function') {
                            showToast('error', 'Đã xảy ra lỗi khi gửi yêu cầu xóa.');
                        } else {
                            alert('Đã xảy ra lỗi khi gửi yêu cầu xóa.');
                        }
                    });
                }
            });
        });
    });
});

function generateAppKey() {
    const userId = document.getElementById('app_key_user_id').value;
    if (!userId) return;

    AppSwal.fire({
        title: 'Bạn chắc chắn?',
        text: "Nếu tạo key mới, phần mềm Desktop cũ dùng key này sẽ bị văng ra ngoài!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Có, tạo mới!',
        cancelButtonText: 'Hủy',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Hiển thị loading
            const keyElem = document.getElementById('currentAppKey');
            keyElem.textContent = "Đang tạo...";
            
            // Gọi API
            fetch('/thidua/admin/tai-khoan?action=generate_app_key', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user_id=' + userId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    keyElem.textContent = data.app_key;
                    keyElem.classList.remove('text-slate-800');
                    keyElem.classList.add('text-indigo-600');
                    document.getElementById('copyAppKeyBtn').classList.remove('hidden');
                    
                    // Cập nhật lại data attribute trong danh sách
                    const btn = document.querySelector(`.appKeyBtn[data-id="${userId}"]`);
                    if (btn) {
                        btn.setAttribute('data-appkey', data.app_key);
                        btn.setAttribute('data-ip', '');
                        btn.setAttribute('data-machine', '');
                        btn.setAttribute('data-time', '');
                    }
                    document.getElementById('appKeyInfo').classList.add('hidden');
                    
                    showToast('success', 'Đã tạo App Key mới thành công!');
                } else {
                    showToast('error', data.message || 'Có lỗi xảy ra!');
                    keyElem.textContent = "Lỗi!";
                }
            })
            .catch(err => {
                showToast('error', 'Không thể kết nối máy chủ!');
                keyElem.textContent = "Lỗi kết nối!";
            });
        }
    });
}

function copyAppKey() {
    const text = document.getElementById('currentAppKey').textContent;
    if (text === 'Chưa có' || text === 'Đang tạo...' || text === 'Lỗi!') return;
    
    navigator.clipboard.writeText(text).then(() => {
        showToast('success', 'Đã sao chép Key!');
    });
}
</script>
