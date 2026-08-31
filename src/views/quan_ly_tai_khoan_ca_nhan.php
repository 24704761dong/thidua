<?php
$page_title = 'Tài Khoản Của Tôi';
require_once __DIR__ . '/partials/admin_header.php';

// Các biến giả định đã được nạp từ controller
$user_info = $user_info ?? ['ho_ten' => 'Admin', 'vai_tro' => 'Quản trị viên'];
$auto_logout_duration = $auto_logout_duration ?? 1800;
$active_sessions = $active_sessions ?? [];
$login_history = $login_history ?? [];

// Hàm parse_user_agent
if (!function_exists('parse_user_agent')) {
    function parse_user_agent($ua_string = '') {
        if (empty($ua_string)) return ['full_string' => 'Không rõ'];
        $browser = 'Unknown Browser';
        if (preg_match('/(MSIE|Trident|Edge|Firefox|Chrome|Safari|Opera)/i', $ua_string, $matches)) {
            $browser = $matches[1];
        }
        $os = 'Unknown OS';
        if (preg_match('/(Windows|Macintosh|Linux|Android|iOS)/i', $ua_string, $matches)) {
            $os = $matches[1];
        }
        return ['full_string' => "$browser trên $os"];
    }
}
?>
<style>
/* Custom Scrollbar for inner window */
.window-content::-webkit-scrollbar {
    width: 8px;
}
.window-content::-webkit-scrollbar-track {
    background: transparent;
}
.window-content::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 4px;
}
.window-content::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.3);
}

/* Bootstrap form-switch override for Tailwind alignment */
.form-switch .form-check-input {
    width: 2.5em;
    height: 1.25em;
    cursor: pointer;
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
</style>

<!-- Page Content -->
<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>   <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg> HỒ SƠ CÁ NHÂN
            </h3>
            <?php if (strtolower((string)($_SESSION['user_ten_dang_nhap'] ?? '')) === 'admin'): ?>
            <a href="#" onclick="if(window.parent && typeof window.parent.openApp === 'function') { window.parent.openApp('tai_khoan', 'Quản Lý Tài Khoản', '/thidua/admin/tai-khoan', '/thidua/public/assets/img/favicon.ico'); } else { window.location.href='/thidua/admin/tai-khoan'; } return false;" class="px-4 py-2 bg-white border border-blue-200 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg> Quản lý tài khoản Admin
            </a>
            <?php endif; ?>
        </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cột trái (1/3) -->
                <div class="space-y-6">
                    <!-- Profile Card -->
                    <div class="bg-white rounded shadow-sm border border-[#224397]/25 p-6 flex flex-col items-center text-center">
                        <img src="<?php echo !empty($user_info['avatar']) ? htmlspecialchars($user_info['avatar']) : '/thidua/public/assets/img/favicon.ico'; ?>" 
                             alt="Avatar" class="w-24 h-24 rounded-full border border-[#224397]/25 mb-4 object-cover">
                        <h3 class="text-lg font-bold text-[#224397]"><?php echo htmlspecialchars($user_info['ho_ten']); ?></h3>
                        <span class="mt-2 px-3 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded border border-[#224397]/25">
                            <?php echo htmlspecialchars(ucfirst($user_info['vai_tro'])); ?>
                        </span>
                    </div>

                    <!-- Basic Info -->
                    <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden">
                        <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg> Thông Tin Cơ Bản
                        </div>
                        <div class="divide-y divide-[#224397]/20">
                            <div class="px-5 py-4">
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Tên đăng nhập</p>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($user_info['ten_dang_nhap'] ?? ''); ?></p>
                            </div>
                            <div class="px-5 py-4">
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Email</p>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($user_info['email'] ?? 'Chưa cập nhật'); ?></p>
                            </div>
                            <div class="px-5 py-4 flex justify-between items-center bg-slate-50 border-b border-[#224397]/25">
                                <div>
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Mật khẩu</p>
                                    <p class="text-slate-800 font-medium tracking-widest">•••••••••</p>
                                </div>
                                <button class="px-3 py-1.5 bg-white border border-slate-300 rounded shadow-sm text-sm font-medium text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-1" onclick="openModal('changePasswordModal')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16"><path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>   <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg> Đổi
                                </button>
                            </div>
                            <!-- Liên kết Zalo -->
                            <div class="px-5 py-4 flex justify-between items-center bg-slate-50 border-t border-[#224397]/25">
                                <div class="overflow-hidden pr-2">
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Liên kết Zalo</p>
                                    <?php if (!empty($user_info['zalo_id'])): ?>
                                        <p class="text-sm font-bold text-green-600 flex items-center gap-1.5 mb-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Đã liên kết
                                        </p>
                                        <p class="text-xs text-slate-500 truncate" title="<?php echo htmlspecialchars($user_info['zalo_name'] ?? 'Chưa rõ tên'); ?>"><?php echo htmlspecialchars($user_info['zalo_name'] ?? 'Chưa rõ tên'); ?></p>
                                    <?php else: ?>
                                        <p class="text-sm font-medium text-slate-500">Chưa liên kết</p>
                                    <?php endif; ?>
                                </div>
                                <div class="shrink-0">
                                    <?php if (empty($user_info['zalo_id'])): ?>
                                    <a href="#" onclick="window.open('/thidua/oauth-redirect-zalo', 'LinkZalo', 'width=600,height=700'); return false;" class="px-3 py-1.5 bg-[#0068FF] rounded shadow-sm text-sm font-medium text-white hover:bg-[#0054cc] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-1.5">
                                        <img src="/thidua/public/assets/img/icons/zalo.svg" alt="Zalo" class="w-4 h-4"> Liên kết
                                    </a>
                                    <?php else: ?>
                                    <button onclick="unlinkZalo()" class="px-3 py-1.5 bg-white border border-red-200 text-red-600 rounded shadow-sm text-sm font-medium hover:bg-red-50 hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-1.5" title="Hủy liên kết Zalo để đổi tài khoản khác">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg> Hủy
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Liên kết Google -->
                            <div class="px-5 py-4 flex justify-between items-center bg-white border-t border-[#224397]/25 rounded-b-xl">
                                <div class="overflow-hidden pr-2">
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Liên kết Google</p>
                                    <?php if (!empty($user_info['google_id'])): ?>
                                        <p class="text-sm font-bold text-green-600 flex items-center gap-1.5 mb-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Đã liên kết
                                        </p>
                                        <p class="text-xs text-slate-500 truncate" title="<?php echo htmlspecialchars($user_info['email'] ?? 'Chưa rõ email'); ?>"><?php echo htmlspecialchars($user_info['email'] ?? 'Chưa rõ email'); ?></p>
                                    <?php else: ?>
                                        <p class="text-sm font-medium text-slate-500">Chưa liên kết</p>
                                    <?php endif; ?>
                                </div>
                                <div class="shrink-0">
                                    <?php if (empty($user_info['google_id'])): ?>
                                    <a href="#" onclick="window.open('/thidua/oauth-redirect-google', 'LinkGoogle', 'width=600,height=700'); return false;" class="px-3 py-1.5 bg-white border border-slate-300 rounded shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-1.5">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" class="w-4 h-4"> Liên kết
                                    </a>
                                    <?php else: ?>
                                    <button onclick="unlinkGoogle()" class="px-3 py-1.5 bg-white border border-red-200 text-red-600 rounded shadow-sm text-sm font-medium hover:bg-red-50 hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-1.5" title="Hủy liên kết Google để đổi tài khoản khác">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg> Hủy
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột phải (2/3) -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Security Settings -->
                    <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden">
                        <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg> Cài Đặt Bảo Mật & 2FA
                        </div>
                        <div class="divide-y divide-[#224397]/20">
                            <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition">
                                <div>
                                    <h6 class="font-semibold text-slate-800 text-sm">Xác Thực 2 Yếu Tố (2FA)</h6>
                                    
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="toggle-2fa-switch" <?php echo $is_2fa_enabled ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition">
                                <div>
                                    <h6 class="font-semibold text-slate-800 text-sm">Cảnh báo đăng nhập qua email</h6>

                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="toggle-login-alert" <?php echo !empty($user_info['nhan_canh_bao_dang_nhap']) ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition">
                                <div>
                                    <h6 class="font-semibold text-slate-800 text-sm">Cảnh báo đăng nhập qua Zalo</h6>
                    
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="toggle-zalo-alert" <?php echo !empty($user_info['nhan_canh_bao_zalo']) ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition">
                                <div>
                                    <h6 class="font-semibold text-slate-800 text-sm">Tự động đăng xuất</h6>
                                    <?php 
                                        $duration_display = '';
                                        if ($auto_logout_duration < 60) {
                                            $duration_display = $auto_logout_duration . ' giây';
                                        } elseif ($auto_logout_duration % 60 === 0) {
                                            $duration_display = ($auto_logout_duration / 60) . ' phút';
                                        } else {
                                            $duration_display = round($auto_logout_duration / 60, 1) . ' phút (' . $auto_logout_duration . ' giây)';
                                        }
                                    ?>
                                    <p class="text-slate-500 text-xs mt-1">Bảo vệ phiên làm việc sau <?php echo $duration_display; ?> không hoạt động.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="toggle-auto-logout" <?php echo !empty($user_info['auto_logout_enabled']) ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Sessions -->
                    <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden">
                        <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pc-display" viewBox="0 0 16 16"><path d="M8 1a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1zm1 13.5a.5.5 0 1 0 1 0 .5.5 0 0 0-1 0m2 0a.5.5 0 1 0 1 0 .5.5 0 0 0-1 0M9.5 1a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM9 3.5a.5.5 0 0 0 .5.5h5a.5.5 0 0 0 0-1h-5a.5.5 0 0 0-.5.5M1.5 2A1.5 1.5 0 0 0 0 3.5v7A1.5 1.5 0 0 0 1.5 12H6v2h-.5a.5.5 0 0 0 0 1H7v-4H1.5a.5.5 0 0 1-.5-.5v-7a.5.5 0 0 1 .5-.5H7V2z"/></svg> Phiên Đăng Nhập Hoạt Động
                        </div>
                        <div class="divide-y divide-[#224397]/20 max-h-[300px] overflow-y-auto list-scrollbar" id="active-sessions-list">
                            <?php if (empty($active_sessions)): ?>
                                <div class="p-6 text-center text-slate-500 text-sm">Không có phiên nào đang hoạt động.</div>
                            <?php else: ?>
                                <?php foreach($active_sessions as $session): 
                                    $ua_info = parse_user_agent($session['user_agent'] ?? '');
                                    $is_current_session = ($session['session_id'] === session_id());
                                ?>
                                    <div class="px-5 py-4 flex justify-between items-center hover:bg-slate-50 transition" id="session-<?php echo htmlspecialchars($session['session_id']); ?>">
                                        <div>
                                            <p class="font-semibold text-slate-800 text-sm flex items-center gap-2">
                                                <i class="bi <?php echo strpos(strtolower($ua_info['full_string']), 'windows') !== false ? 'bi-windows' : (strpos(strtolower($ua_info['full_string']), 'mac') !== false ? 'bi-apple' : 'bi-display'); ?> text-slate-400"></i>
                                                <?php echo htmlspecialchars($ua_info['full_string']); ?>
                                            </p>
                                            <p class="text-slate-500 text-xs mt-1">
                                                <span class="inline-block w-2 h-2 rounded-full bg-green-500 mr-1"></span> IP: <?php echo htmlspecialchars($session['ip_address']); ?> &bull; Hoạt động: <?php echo date('H:i', (int)$session['last_activity']); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <?php if ($is_current_session): ?>
                                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded border border-green-200">Phiên hiện tại</span>
                                            <?php else: ?>
                                                <button class="px-3 py-1.5 bg-white border border-red-200 text-red-600 rounded shadow-sm text-xs font-medium hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 terminate-session-btn" data-session-id="<?php echo htmlspecialchars($session['session_id']); ?>">
                                                    Đăng xuất
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Login History -->
                    <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden">
                        <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>   <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>   <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/></svg> Lịch Sử Đăng Nhập Gần Đây
                        </div>
                        <div class="overflow-x-auto max-h-[300px] overflow-y-auto list-scrollbar">
                            <table class="w-full text-left text-sm text-slate-600">
                                <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                                    <tr>
                                        <th class="px-5 py-3">Thời gian</th>
                                        <th class="px-5 py-3">IP</th>
                                        <th class="px-5 py-3">Thiết bị</th>
                                        <th class="px-5 py-3">Vị trí</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#224397]/20">
                                    <?php if (empty($login_history)): ?>
                                        <tr><td colspan="4" class="px-5 py-6 text-center text-slate-500">Chưa có lịch sử.</td></tr>
                                    <?php else: ?>
                                        <?php foreach(array_slice($login_history, 0, 10) as $log): ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-5 py-3 whitespace-nowrap"><?php echo date('d/m/Y H:i', strtotime($log['thoi_gian_dang_nhap'])); ?></td>
                                            <td class="px-5 py-3 whitespace-nowrap font-mono text-xs"><?php echo htmlspecialchars($log['dia_chi_ip'] ?? ''); ?></td>
                                            <td class="px-5 py-3">
                                                <?php 
                                                    $user_agent_string = $log['user_agent'] ?? '';
                                                    $ua_info_log = parse_user_agent($user_agent_string);
                                                ?>
                                                <div class="text-xs truncate max-w-[250px]" title="<?php echo htmlspecialchars($user_agent_string); ?>">
                                                    <?php echo htmlspecialchars($ua_info_log['full_string']); ?>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3">
                                                <div class="text-xs text-slate-600 truncate max-w-[150px]" title="<?php echo htmlspecialchars($log['vi_tri_ip'] ?? 'Không rõ'); ?>">
                                                    <?php echo htmlspecialchars($log['vi_tri_ip'] ?? 'Không rõ'); ?>
                                                </div>
                                                <?php if (!empty($log['vi_tri_gps'])): ?>
                                                    <a href="<?php echo htmlspecialchars($log['vi_tri_gps']); ?>" target="_blank" title="Xem vị trí trên Google Maps" class="inline-flex items-center justify-center w-6 h-6 mt-1 rounded-full bg-blue-50 text-[11px] font-bold text-blue-600 hover:bg-blue-100 hover:text-blue-700 hover:scale-110 transition-all shadow-sm border border-blue-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/></svg>
                                                    </a>
                                                <?php endif; ?>
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
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('changePasswordModal')">
    <div class="bg-white rounded shadow-2xl w-[500px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center">
            <h5 class="text-[#224397] font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> Đổi Mật Khẩu
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal('changePasswordModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        </div>
        <div class="p-5 space-y-4 bg-white">
            <form id="changePasswordForm">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mật khẩu hiện tại</label>
                    <input type="password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="currentPassword" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mật khẩu mới</label>
                    <input type="password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="newPassword" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Xác nhận mật khẩu mới</label>
                    <input type="password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" id="confirmNewPassword" required>
                </div>
            </form>
        </div>
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300" onclick="closeModal('changePasswordModal')">Hủy</button>
            <button type="submit" form="changePasswordForm" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg> Cập nhật
            </button>
        </div>
    </div>
</div>

<!-- 2FA Modal -->
<div id="2faModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('2faModal')">
    <div class="bg-white rounded shadow-2xl w-[500px] max-w-[90%] max-h-[90vh] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
        <div class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0">
            <h5 class="text-[#224397] font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-plus" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M8 4.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V9a.5.5 0 0 1-1 0V7.5H6a.5.5 0 0 1 0-1h1.5V5a.5.5 0 0 1 .5-.5"/></svg> Kích hoạt Xác thực 2 Yếu Tố
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded transition" onclick="closeModal('2faModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        </div>
        <div class="p-6 bg-white text-center overflow-y-auto list-scrollbar">
            <p class="text-slate-600 text-sm mb-4">Vui lòng quét mã QR này bằng ứng dụng Google Authenticator hoặc Authy.</p>
            
            <div id="qrCodeContainer" class="flex justify-center items-center min-h-[200px] bg-slate-50 rounded-lg border border-slate-100 p-4 mb-4 text-[#224397]">
                Đang tạo mã...
            </div>
            
            <p class="text-xs text-slate-500 mb-1">Hoặc nhập thủ công mã bí mật:</p>
            <code id="secretKeyContainer" class="block bg-slate-100 text-red-600 px-3 py-2 rounded text-sm font-mono break-all mb-4">...</code>
            
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-left mb-4">
                <p class="text-xs text-blue-800 font-semibold mb-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle mr-1" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg> Quan trọng:</p>
                <p class="text-xs text-blue-700">Sau khi quét, hãy nhập 6 số hiển thị trên ứng dụng của bạn vào ô bên dưới để hoàn tất xác nhận.</p>
            </div>
            
            <div class="text-left">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Mã xác nhận (6 số)</label>
                <input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-center text-lg tracking-widest font-mono" id="2fa_code_verify" placeholder="••••••" inputmode="numeric" maxlength="6" autocomplete="off">
            </div>
        </div>
        <div class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300" onclick="closeModal('2faModal')">Để sau</button>
            <button type="button" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium shadow-sm transition-all duration-300 flex items-center gap-2" id="confirm2faBtn" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg> Xác nhận & Kích hoạt
            </button>
        </div>
    </div>
</div>

<!-- System Alert Modal -->
<div id="systemAlertModal" class="hidden fixed inset-0 z-[10010] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('systemAlertModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] max-w-[90%] flex flex-col overflow-hidden border border-slate-200 transform transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
        <div class="bg-slate-50 border-b border-slate-100 px-6 py-4 flex justify-between items-center">
            <h5 class="text-[#224397] font-bold flex items-center gap-2" id="systemAlertTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-bell-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/></svg> Thông báo hệ thống
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('systemAlertModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        </div>
        <div class="p-6 bg-white text-center">
            <p class="text-slate-700 text-base font-medium" id="systemAlertMessage"></p>
        </div>
        <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 flex justify-end">
            <button type="button" class="px-6 py-2 bg-[#224397] text-white rounded-lg hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" id="systemAlertOkBtn" onclick="closeModal('systemAlertModal')">OK</button>
        </div>
    </div>
</div>

<!-- System Confirm Modal -->
<div id="systemConfirmModal" class="hidden fixed inset-0 z-[10010] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('systemConfirmModal')">
    <div class="bg-white rounded-xl shadow-2xl w-[420px] max-w-[90%] flex flex-col overflow-hidden border border-slate-200 transform transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
        <div class="bg-slate-50 border-b border-slate-100 px-6 py-4 flex justify-between items-center">
            <h5 class="text-[#224397] font-bold flex items-center gap-2" id="systemConfirmTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-question-circle-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.496 6.033h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286a.237.237 0 0 0 .241.247zm2.325 6.443c.61 0 1.029-.394 1.029-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94 0 .533.425.927 1.01.927z"/></svg> Xác nhận hệ thống
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('systemConfirmModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        </div>
        <div class="p-6 bg-white text-slate-700 text-sm font-medium leading-relaxed" id="systemConfirmMessage"></div>
        <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 flex justify-end gap-2">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300" onclick="closeModal('systemConfirmModal')">Hủy</button>
            <button type="button" class="px-6 py-2 bg-[#224397] text-white rounded-lg hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" id="systemConfirmOkBtn">Đồng ý</button>
        </div>
    </div>
</div>

<!-- Modal Tắt 2FA -->
<div id="disable2faModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('disable2faModal')">
    <div class="modal-content bg-white rounded-xl shadow-2xl w-[450px] max-w-[90%] flex flex-col overflow-hidden border border-slate-200 transform transition-all duration-300 scale-95 translate-y-4 opacity-0" onclick="event.stopPropagation()">
        <div class="bg-slate-50 border-b border-slate-100 px-6 py-4 flex justify-between items-center">
            <h5 class="text-[#224397] font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-shield-lock-fill text-[#FAB723]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7.2 7.2 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263c-.658-.214-1.777-.57-2.887-.87C9.843.265 8.69 0 8 0m0 5a1.5 1.5 0 0 1 1.5 1.5v.5h.5a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5h.5v-.5A1.5 1.5 0 0 1 8 5m1 2.5v-.5a1 1 0 0 0-2 0v.5z"/></svg> Tắt Xác Thực 2 Yếu Tố (2FA)
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('disable2faModal')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        </div>
        <div class="p-6 bg-white space-y-4">
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-slate-700 text-sm">
                <p class="font-semibold text-amber-800 mb-1">CẢNH BÁO:</p>
                Bạn có chắc chắn muốn tắt Xác thực 2 yếu tố không? Tài khoản của bạn sẽ giảm đi một lớp bảo mật.
            </div>
            <div>
                <label for="disable_2fa_code" class="block text-sm font-semibold text-slate-700 mb-1">Mã xác nhận 6 số từ ứng dụng Authenticator *</label>
                <input type="text" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-center tracking-widest font-mono text-lg" id="disable_2fa_code" placeholder="••••••" maxlength="6">
            </div>
        </div>
        <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 flex justify-end gap-2">
            <button type="button" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300" onclick="closeModal('disable2faModal')">Hủy</button>
            <button type="button" id="confirmDisable2faBtn" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                Xác nhận Tắt
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
// Modal Animation Utility
function openModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('div.bg-white');
    modal.classList.remove('hidden');
    // Force reflow
    void modal.offsetWidth;
    // Add active classes
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('div.bg-white');
    // Remove active classes
    modal.classList.add('opacity-0');
    content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    // Wait for transition, then hide
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function showSystemAlert(message, title = 'Thông báo hệ thống', callback = null) {
    document.getElementById('systemAlertTitle').innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-bell-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/></svg> ` + title;
    document.getElementById('systemAlertMessage').textContent = message;
    const okBtn = document.getElementById('systemAlertOkBtn');
    okBtn.onclick = () => {
        closeModal('systemAlertModal');
        if (callback) callback();
    };
    openModal('systemAlertModal');
}

function showSystemConfirm(message, title = 'Xác nhận hệ thống', onConfirm = null) {
    document.getElementById('systemConfirmTitle').innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-question-circle-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.496 6.033h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286a.237.237 0 0 0 .241.247zm2.325 6.443c.61 0 1.029-.394 1.029-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94 0 .533.425.927 1.01.927z"/></svg> ` + title;
    document.getElementById('systemConfirmMessage').textContent = message;
    const okBtn = document.getElementById('systemConfirmOkBtn');
    okBtn.onclick = () => {
        closeModal('systemConfirmModal');
        if (onConfirm) onConfirm();
    };
    openModal('systemConfirmModal');
}

document.addEventListener('DOMContentLoaded', function() {
    // API Call for basic settings
    async function saveUserSetting(key, value, element) {
        const apiUrl = '/thidua/api/save-user-settings'; 
        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ setting: key, value: value ? 1 : 0 })
            });
            const data = await response.json();
            if (!data.success) {
                showSystemAlert('Lỗi khi cập nhật cài đặt: ' + (data.message || 'Không rõ lỗi.'), 'Lỗi hệ thống');
                element.checked = !value;
            }
        } catch (error) {
            console.error('Error:', error);
            showSystemAlert('Có lỗi xảy ra khi giao tiếp với máy chủ.', 'Lỗi kết nối');
            element.checked = !value;
        }
    }

    const alertToggle = document.getElementById('toggle-login-alert');
    if(alertToggle) {
        alertToggle.addEventListener('change', function() {
            saveUserSetting('nhan_canh_bao_dang_nhap', this.checked, this);
        });
    }

    const zaloAlertToggle = document.getElementById('toggle-zalo-alert');
    if(zaloAlertToggle) {
        zaloAlertToggle.addEventListener('change', function() {
            saveUserSetting('nhan_canh_bao_zalo', this.checked, this);
        });
    }

    const logoutToggle = document.getElementById('toggle-auto-logout');
    if(logoutToggle) {
        logoutToggle.addEventListener('change', function() {
            saveUserSetting('auto_logout_enabled', this.checked, this);
            if (window.top) {
                window.top.AUTO_LOGOUT_ENABLED = this.checked;
                if (!this.checked && typeof window.top.cancelInactivityTimer === 'function') {
                    window.top.cancelInactivityTimer();
                } else if (this.checked && typeof window.top.resetInactivityTimer === 'function') {
                    window.top.resetInactivityTimer();
                }
            }
        });
    }

    // Terminate Session
    const activeSessionsList = document.getElementById('active-sessions-list');
    if (activeSessionsList) {
        activeSessionsList.addEventListener('click', async function(e) {
            const terminateBtn = e.target.closest('.terminate-session-btn');
            if (terminateBtn) {
                if (confirm('Bạn có chắc chắn muốn đăng xuất phiên này?')) {
                    const sessionId = terminateBtn.dataset.sessionId;
                    try {
                        const response = await fetch('/thidua/api/terminate-session', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ session_id: sessionId })
                        });
                        const data = await response.json();
                        if (data.success) {
                            showSystemAlert('Phiên đã được đăng xuất thành công.', 'Thành công');
                            document.getElementById(`session-${sessionId}`).remove();
                            if (activeSessionsList.querySelectorAll('.terminate-session-btn').length === 0 && activeSessionsList.children.length <= 1) {
                                activeSessionsList.innerHTML = '<div class="p-6 text-center text-slate-500 text-sm">Không có phiên nào đang hoạt động.</div>';
                            }
                        } else {
                            showSystemAlert('Lỗi khi đăng xuất phiên: ' + (data.message || 'Không rõ lỗi.'), 'Lỗi hệ thống');
                        }
                    } catch (error) {
                        console.error('Error terminating session:', error);
                        showSystemAlert('Có lỗi xảy ra khi giao tiếp với máy chủ.', 'Lỗi kết nối');
                    }
                }
            }
        });
    }

    // 2FA Logic
    const fa2ModalEl = document.getElementById('2faModal');
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const secretKeyContainer = document.getElementById('secretKeyContainer');
    const confirm2faBtn = document.getElementById('confirm2faBtn');
    const verifyCodeInput = document.getElementById('2fa_code_verify');
    let current2faSecret = '';

    const faToggleSwitch = document.getElementById('toggle-2fa-switch');
    if (faToggleSwitch) {
        faToggleSwitch.addEventListener('click', async function(e) {
            e.preventDefault(); 
            const isCurrentlyEnabled = this.checked;

            if (!isCurrentlyEnabled) {
                document.getElementById('disable_2fa_code').value = '';
                openModal('disable2faModal');
            } else {
                openModal('2faModal');
                load2faQR();
            }
        });
    }

    const confirmDisable2faBtn = document.getElementById('confirmDisable2faBtn');
    if (confirmDisable2faBtn) {
        confirmDisable2faBtn.addEventListener('click', async function() {
            const codeInput = document.getElementById('disable_2fa_code');
            const code = codeInput.value.trim();
            if (code.length !== 6 || !/^\d+$/.test(code)) {
                showSystemAlert('Vui lòng nhập mã 6 số hợp lệ từ ứng dụng.', 'Lỗi nhập liệu');
                return;
            }

            confirmDisable2faBtn.disabled = true;
            confirmDisable2faBtn.innerHTML = 'Đang xử lý...';

            try {
                const response = await fetch('/thidua/api/2fa-disable', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code: code })
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Mã 6 số không chính xác.');
                }
                closeModal('disable2faModal');
                showSystemAlert(data.message, 'Thành công', () => { location.reload(); });
            } catch (error) {
                showSystemAlert('Lỗi khi tắt 2FA: ' + error.message, 'Lỗi 2FA');
                confirmDisable2faBtn.disabled = false;
                confirmDisable2faBtn.innerHTML = 'Xác nhận Tắt';
            }
        });
    }

    async function load2faQR() {
        qrCodeContainer.innerHTML = 'Đang tạo mã...';
        secretKeyContainer.textContent = '...';
        confirm2faBtn.disabled = true; 
        verifyCodeInput.value = '';
        
        try {
            const response = await fetch('/thidua/api/2fa-generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json(); 

            if (!response.ok || !data.success) {
                qrCodeContainer.innerHTML = `<div class="p-4 bg-red-50 text-red-700 text-sm rounded border border-red-200">Lỗi: ${data.message || 'Không rõ lỗi.'}</div>`;
                secretKeyContainer.textContent = 'Không thể tải';
                return; 
            }
            
            qrCodeContainer.innerHTML = `<img src="${data.qr_image_data_uri}" alt="QR Code 2FA" class="w-48 h-48 object-contain rounded border border-[#224397]/25 inline-block shadow-sm">`;
            secretKeyContainer.textContent = data.secret_key;
            current2faSecret = data.secret_key; 
            confirm2faBtn.disabled = false; 
        } catch (error) {
            qrCodeContainer.innerHTML = `<div class="p-4 bg-red-50 text-red-700 text-sm rounded border border-red-200">Lỗi kết nối: ${error.message}</div>`;
            secretKeyContainer.textContent = 'Không thể tải';
        }
    }

    if (confirm2faBtn) {
        confirm2faBtn.addEventListener('click', async () => {
            const code = verifyCodeInput.value.trim();
            if (code.length !== 6 || !/^\d+$/.test(code)) {
                showSystemAlert('Vui lòng nhập mã 6 số hợp lệ từ ứng dụng.', 'Lỗi nhập liệu');
                return;
            }

            confirm2faBtn.disabled = true;
            confirm2faBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xác thực...';

            try {
                const response = await fetch('/thidua/api/2fa-verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code: code })
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Mã 6 số không chính xác.');
                }
                
                closeModal('2faModal');
                showSystemAlert('Bật 2FA thành công!', 'Thành công', () => { location.reload(); });
            } catch (error) {
                showSystemAlert('Lỗi: ' + error.message, 'Xác thực thất bại');
                confirm2faBtn.disabled = false;
                confirm2faBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg> Xác nhận & Kích hoạt';
            }
        });
    }
           
    // Change Password Logic
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmNewPassword = document.getElementById('confirmNewPassword').value;

            if (newPassword !== confirmNewPassword) {
                showSystemAlert('Mật khẩu mới và xác nhận mật khẩu không khớp.', 'Lỗi nhập liệu');
                return;
            }

            const btn = document.querySelector('button[form="changePasswordForm"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Đang xử lý...';
            }

            try {
                const response = await fetch('/thidua/api/admin-change-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        current_password: currentPassword, 
                        new_password: newPassword 
                    })
                });
                const data = await response.json();
                if (data.success) {
                    closeModal('changePasswordModal');
                    changePasswordForm.reset();
                    showSystemAlert(data.message, 'Thành công');
                } else {
                    showSystemAlert(data.message, 'Đổi mật khẩu thất bại');
                }
            } catch (error) {
                console.error('Lỗi khi đổi mật khẩu:', error);
                showSystemAlert('Đã xảy ra lỗi khi gửi yêu cầu đổi mật khẩu.', 'Lỗi kết nối');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg> Cập nhật';
                }
            }
        });
    }
});

function unlinkZalo() {
    showSystemConfirm('Bạn có chắc chắn muốn hủy liên kết tài khoản Zalo này? Sau khi hủy, bạn có thể liên kết với một tài khoản Zalo khác.', 'Hủy liên kết Zalo', async () => {
        try {
            const response = await fetch('/thidua/api/unlink-zalo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                showSystemAlert('Đã hủy liên kết Zalo thành công!', 'Thành công', () => { location.reload(); });
            } else {
                showSystemAlert('Lỗi: ' + data.message, 'Lỗi hủy liên kết');
            }
        } catch (error) {
            showSystemAlert('Có lỗi xảy ra khi gọi máy chủ.', 'Lỗi kết nối');
            console.error(error);
        }
    });
}

function unlinkGoogle() {
    showSystemConfirm('Bạn có chắc chắn muốn hủy liên kết tài khoản Google này? Sau khi hủy, bạn có thể liên kết với một tài khoản Google khác.', 'Hủy liên kết Google', async () => {
        try {
            const response = await fetch('/thidua/api/unlink-google', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                showSystemAlert('Đã hủy liên kết Google thành công!', 'Thành công', () => { location.reload(); });
            } else {
                showSystemAlert('Lỗi: ' + data.message, 'Lỗi hủy liên kết');
            }
        } catch (error) {
            showSystemAlert('Có lỗi xảy ra khi gọi máy chủ.', 'Lỗi kết nối');
            console.error(error);
        }
    });
}
</script>

<script>
// Lắng nghe postMessage từ popup Zalo OAuth (tránh lỗi cross-origin với window.opener)
window.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'ZALO_LINK_SUCCESS') {
        if (typeof showSystemAlert === 'function') {
            showSystemAlert(event.data.message || 'Liên kết Zalo thành công!', 'Thành công', function() {
                location.reload();
            });
        } else {
            alert(event.data.message || 'Liên kết Zalo thành công!');
            location.reload();
        }
    }
    if (event.data && event.data.type === 'ZALO_LINK_ERROR') {
        if (typeof showSystemAlert === 'function') {
            showSystemAlert(event.data.message || 'Liên kết Zalo thất bại.', 'Lỗi');
        } else {
            alert(event.data.message || 'Liên kết Zalo thất bại.');
        }
    }
});

// Xử lý fallback: nếu popup không được hỗ trợ, callback redirect thẳng về trang này kèm query param
(function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('zalo_linked') === '1') {
        // Xóa query string khỏi URL mà không reload
        history.replaceState({}, '', window.location.pathname);
        if (typeof showSystemAlert === 'function') {
            showSystemAlert('Liên kết tài khoản Zalo thành công!', 'Thành công', function() {
                location.reload();
            });
        } else {
            location.reload();
        }
    }
})();
</script>
