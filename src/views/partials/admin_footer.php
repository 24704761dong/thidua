<?php
// File: src/views/partials/admin_footer.php

// Kéo biến $is_iframe và $is_dashboard từ admin_header.php sang
global $is_iframe, $is_dashboard;

$launch_app = null;
if (isset($_SESSION['launch_app'])) {
    $launch_app = $_SESSION['launch_app'];
    unset($_SESSION['launch_app']);
}
?>

<?php if (!$is_iframe): ?>
    
    <!-- Taskbar -->
    <div class="taskbar">
        <!-- LEFT -->
        <div class="flex items-center gap-2 h-full z-10">
            <button class="taskbar-btn group" id="startBtn" onclick="document.getElementById('startMenu').classList.toggle('show')">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-grid-fill text-[#224397] group-hover:text-[#FAB723] transition-colors" viewBox="0 0 16 16"><path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zm8 0A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5z"/></svg>
            </button>
            
            <!-- Search Bar -->
            <div id="taskbarSearchContainer" class="flex items-center bg-gray-100 rounded-md px-2.5 h-[28px] border border-gray-200 ml-1 group cursor-text hidden sm:flex focus-within:border-blue-400 focus-within:bg-white transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search text-[#224397] group-hover:text-[#FAB723] transition-colors" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                <input type="text" id="taskbarSearchInput" placeholder="Tìm kiếm..." class="bg-transparent border-none outline-none ml-2 text-[13px] w-28 focus:w-44 transition-all duration-200 text-gray-700 placeholder-gray-400" autocomplete="off">
            </div>
        </div>
        
        <!-- CENTER: Taskbar Apps Container -->
        <div id="taskbarApps" class="flex-1 px-4 h-full flex items-center gap-1 overflow-x-auto overflow-y-hidden custom-scrollbar mx-2 relative z-10">
            <!-- JS will inject open window buttons here -->
        </div>
        
        <!-- CENTER ABSOLUTE: Copyright -->
        <div id="copyrightText" class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 flex flex-col items-center justify-center text-[10px] text-gray-500 opacity-70 pointer-events-none hidden lg:flex w-full z-0 select-none transition-opacity duration-300">
            <span class="font-bold">Copyright &copy; <?= date('Y') ?> - Hệ thống Đánh Giá Thi Đua Thi Đua <span class="italic font-normal">Version 7.2.4</span></span>
            <span>Thực hiện bởi <a href="https://zalo.me/0362566146" target="_blank" class="hover:text-blue-500 pointer-events-auto transition-colors text-inherit decoration-transparent hover:underline" title="Liên hệ Zalo: 0362566146">Phạm Văn Thành Đồng</a></span>
        </div>
        
        <!-- RIGHT -->
        <div class="flex items-center gap-1 md:gap-3 h-full z-10 shrink-0 bg-white/50 pl-2 rounded-l-md">
            <!-- Year Switcher -->
            <div class="relative h-full flex items-center">
                <button id="taskbarYearBtn" class="group flex items-center gap-2 text-[#224397] hover:bg-gray-100 px-2 py-1.5 rounded-md text-[13px] font-bold transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-event text-[#224397] group-hover:text-[#FAB723] transition-colors" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
                    <span id="taskbarYearLabel" class="group-hover:text-[#FAB723] transition-colors hidden md:inline"><?= $nam_hoc_current ? htmlspecialchars($nam_hoc_current['ten_nam_hoc']) : 'Năm Học' ?></span>
                </button>
                <div id="taskbarYearMenu" class="hidden absolute right-0 bottom-full mb-2 w-36 bg-white/95 backdrop-blur-md border border-gray-200 shadow-2xl rounded-lg p-1.5 z-[10000]">
                    <?php foreach ($nam_hoc_list as $nh): ?>
                        <?php $isActive = ($nam_hoc_current && $nh['id'] == $nam_hoc_current['id']); ?>
                        <div class="px-3 py-1.5 text-center cursor-pointer text-[13px] font-semibold <?= $isActive ? 'bg-blue-50 text-[#224397]' : 'text-gray-700 hover:bg-gray-100' ?> rounded-md transition year-switcher-option" data-id="<?= (int)$nh['id'] ?>">
                            <?= htmlspecialchars($nh['ten_nam_hoc']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Notification Icon -->
            <div class="relative h-full flex items-center">
                <button id="taskbarNotifBtn" class="taskbar-btn relative group h-full px-3 flex items-center justify-center hover:bg-gray-100 transition" title="Thông báo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-bell-fill text-[#224397] group-hover:text-[#FAB723] transition-colors" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/></svg>
                    <span id="notifBadge" class="hidden absolute top-[12px] right-[8px] w-2 h-2 bg-red-500 rounded-full border border-white shadow-[0_0_5px_rgba(239,68,68,0.8)]"></span>
                </button>
                <div id="notificationPopup" class="hidden absolute right-0 bottom-full mb-2 w-80 bg-white/95 backdrop-blur-md border border-gray-200 shadow-2xl rounded-lg z-[10000] text-gray-800 overflow-hidden flex flex-col">
                    <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/80">
                        <h3 class="font-bold text-[14px] text-[#224397]">Thông Báo</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto max-h-[350px] custom-scrollbar" id="notificationList">
                        <div class="p-4 text-center text-gray-500 text-[13px] min-h-[100px] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-repeat animate-spin mr-2" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>   <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg> Đang tải...
                        </div>
                    </div>

                </div>
            </div>

            <!-- Clock & Calendar Popup -->
            <div class="h-full flex items-center">
                <div id="taskbarClock" class="flex flex-col justify-center text-right leading-tight cursor-pointer hover:bg-gray-100 transition h-full px-3 select-none">
                    <span id="taskbarTime" class="text-[12px] font-medium text-gray-700">--:--</span>
                    <span id="taskbarDate" class="text-[12px] font-medium text-gray-700">--/--/----</span>
                </div>
                
                <!-- Calendar Popup -->
                <div id="calendarPopup" class="hidden fixed right-2 bottom-[45px] w-72 bg-white border border-gray-200 shadow-2xl rounded-lg p-4 z-[10000] text-gray-800">
                    <div class="flex justify-between items-center mb-4">
                        <button id="calPrevBtn" class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 hover:bg-gray-100 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-left text-sm text-gray-600" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/></svg></button>
                        <div id="calMonthYear" class="font-bold text-[15px] text-gray-800">tháng -- năm ----</div>
                        <button id="calNextBtn" class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 hover:bg-gray-100 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-right text-sm text-gray-600" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/></svg></button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-[12px] font-bold mb-2 text-gray-600">
                        <div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div><div>CN</div>
                    </div>
                    <div id="calDays" class="grid grid-cols-7 gap-1 text-center text-[14px] font-semibold text-gray-800"></div>
                </div>
            </div>
            
            <!-- Toggle Fullscreen / F11 Button (giữ nguyên kiểu dáng ban đầu) -->
            <div class="w-1 h-full bg-gray-200 hover:bg-gray-300 transition cursor-pointer ml-1" title="Bật/Thu nhỏ toàn màn hình (F11)" onclick="toggleFullscreenF11()"></div>
        </div>
    </div>
    
    <!-- Start Menu -->
    <div class="start-menu" id="startMenu">
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center bg-gray-100 rounded-md px-3 py-2 border border-transparent focus-within:border-blue-400 focus-within:bg-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search text-gray-500 mr-2" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                <input type="text" id="startMenuSearch" placeholder="Tìm kiếm chức năng..." class="bg-transparent border-none outline-none w-full text-[13px] text-gray-700 placeholder-gray-400">
            </div>
        </div>
        
        <div class="p-3 flex-1 overflow-y-auto custom-scrollbar" style="max-height: 400px;" id="startMenuList">
            <style>
            .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #224397; border-radius: 5px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #FAB723; }
            </style>
            <?php foreach ($permission_config as $groupKey => $group): ?>
                <?php 
                $hasItems = false;
                ob_start();
                foreach ($group['permissions'] as $key => $perm): 
                    if (!isset($perm['route']) || $perm['route'] === '#') continue;
                    if ($key === 'quan_ly_tai_khoan_admin' && (strtolower((string)($_SESSION['user_ten_dang_nhap'] ?? '')) !== 'admin')) continue;
                    if ($user_role !== 'admin' && !in_array('all', $user_permissions) && !in_array($key, $user_permissions)) continue;
                    $hasItems = true;
                    $img_file = $icon_map[$key] ?? 'logoapp.png';
                ?>
                <div onclick="openApp('<?php echo $key; ?>', '<?php echo htmlspecialchars($perm['label']); ?>', '<?php echo htmlspecialchars($perm['route']); ?>', '/thidua/public/assets/img/icons/<?= $img_file ?>')" class="group flex items-center gap-3 p-2 hover:bg-orange-50 hover:translate-x-2 rounded-md transition-all duration-200 start-menu-item cursor-pointer">
                    <div class="w-7 h-7 flex items-center justify-center bg-blue-50/50 group-hover:bg-orange-100 rounded transition-colors duration-200 p-1">
                        <img src="/thidua/public/assets/img/icons/<?= $img_file ?>" class="w-full h-full object-contain" onerror="this.src='/thidua/public/assets/img/22logoapp.png'" alt="">
                    </div>
                    <span class="text-[13px] text-gray-700 font-medium item-name group-hover:text-orange-600 transition-colors duration-200"><?php echo htmlspecialchars($perm['label']); ?></span>
                </div>
                <?php endforeach; 
                $itemsHtml = ob_get_clean();
                if ($hasItems):
                ?>
                <div class="mb-3 start-menu-group">
                    <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1 px-2 group-title"><?php echo htmlspecialchars($group['title']); ?></h3>
                    <div class="flex flex-col">
                        <?= $itemsHtml ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <div id="noResultsMsg" class="hidden text-center text-gray-500 text-[13px] py-4">Không tìm thấy chức năng nào.</div>
        </div>
        
        <div class="start-menu-footer">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-fill text-sm" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>
                </div>
                <span class="text-[13px] font-bold text-gray-800"><?php echo isset($_SESSION['user_ten']) ? htmlspecialchars($_SESSION['user_ten']) : 'Admin'; ?></span>
            </div>
            <a href="/thidua/dang-xuat" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition" title="Đăng xuất">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-power text-lg" viewBox="0 0 16 16"><path d="M7.5 1v7h1V1z"/>   <path d="M3 8.812a5 5 0 0 1 2.578-4.375l-.485-.874A6 6 0 1 0 11 3.616l-.501.865A5 5 0 1 1 3 8.812"/></svg>
            </a>
        </div>
    </div>
    
</div> <!-- End os-desktop -->

<!-- Custom Context Menu -->
<div id="desktopContextMenu" class="hidden fixed bg-white/95 backdrop-blur-md shadow-2xl rounded-md border border-gray-200 py-1 w-64 z-[10001] text-[13px] text-gray-700">
    <button id="menuSortAZ" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-3 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-sort-alpha-down text-gray-500" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10.082 5.629 9.664 7H8.598l1.789-5.332h1.234L13.402 7h-1.12l-.419-1.371zm1.57-.785L11 2.687h-.047l-.652 2.157z"/><path d="M12.96 14H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645zM4.5 2.5a.5.5 0 0 0-1 0v9.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L4.5 12.293z"/></svg>Sắp xếp ứng dụng (A-Z)</button>
    <button id="menuSortZA" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-3 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-sort-alpha-up-alt text-gray-500" viewBox="0 0 16 16"><path d="M12.96 7H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645z"/><path fill-rule="evenodd" d="M10.082 12.629 9.664 14H8.598l1.789-5.332h1.234L13.402 14h-1.12l-.419-1.371zm1.57-.785L11 9.688h-.047l-.652 2.156z"/><path d="M4.5 13.5a.5.5 0 1 1-1 0V3.707L2.354 4.854a.5.5 0 1 1-.708-.708l2-1.999.007-.007a.5.5 0 0 1 .7.006l2 2a.5.5 0 1 1-.707.708L4.5 3.707z"/></svg>Sắp xếp ứng dụng (Z-A)</button>
    <button onclick="window.refreshWidgets(); document.getElementById('desktopContextMenu').classList.add('hidden');" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-3 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-clockwise text-gray-500" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/></svg>Làm mới</button>
    <div class="h-[1px] bg-gray-200 my-1 mx-2"></div>
    <button onclick="openCustomizeIconsModal()" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-3 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-app-indicator text-gray-500" viewBox="0 0 16 16"><path d="M5.5 2A3.5 3.5 0 0 0 2 5.5v5A3.5 3.5 0 0 0 5.5 14h5a3.5 3.5 0 0 0 3.5-3.5V8a.5.5 0 0 1 1 0v2.5a4.5 4.5 0 0 1-4.5 4.5h-5A4.5 4.5 0 0 1 1 10.5v-5A4.5 4.5 0 0 1 5.5 1H8a.5.5 0 0 1 0 1z"/><path d="M16 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/></svg>Tùy biến icon ứng dụng</button>
    <button onclick="openCustomizeWidgetsModal()" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-3 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-ui-checks-grid text-gray-500" viewBox="0 0 16 16"><path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/></svg>Tùy biến widgets</button>
    <button onclick="sortWidgets()" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-3 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-grid-3x3-gap text-gray-500" viewBox="0 0 16 16"><path d="M4 2v2H2V2zm1 12v-2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1m0-5V7a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1m0-5V2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1m5 10v-2a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1m0-5V7a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1m0-5V2a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1M9 2v2H7V2zm5 0v2h-2V2zM4 7v2H2V7zm5 0v2H7V7zm5 0h-2v2h2zM4 12v2H2v-2zm5 0v2H7V-2zm5 0v2h-2v-2zM12 1a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zm-1 6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm1 4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1z"/></svg>Sắp xếp widgets</button>
    <div class="h-[1px] bg-gray-200 my-1 mx-2"></div>
    <button id="menuChangeBg" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-3 transition"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-image text-gray-500" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/></svg>Thay đổi hình nền</button>
    <button id="menuResetBg" class="w-full text-left px-4 py-2 hover:bg-red-50 flex items-center gap-3 transition text-red-600"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-counterclockwise text-red-500" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/><path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/></svg>Khôi phục nền gốc</button>
</div>

<!-- Modal Tùy biến Widgets (Tailwind) -->
<div id="customizeWidgetsModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl w-[500px] max-w-[90%] flex flex-col overflow-hidden animate-fade-in-up">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h5 class="text-lg font-bold text-gray-800">Tùy Biến Hiển Thị Widgets</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeCustomizeWidgetsModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
            <p class="text-sm text-gray-500 mb-4">Chọn các widget bạn muốn hiển thị trên màn hình chính.</p>
            <div class="grid grid-cols-2 gap-y-3 gap-x-4" id="widgetCheckboxesContainer">
                <!-- Checkboxes sẽ được tạo bằng JS -->
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition" onclick="closeCustomizeWidgetsModal()">Hủy</button>
            <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition" onclick="saveWidgetCustomization()">Lưu thay đổi</button>
        </div>
    </div>
</div>

<!-- Modal Tùy biến Icons (Tailwind) -->
<div id="customizeIconsModal" class="hidden fixed inset-0 z-[10005] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl w-[550px] max-w-[90%] flex flex-col overflow-hidden animate-fade-in-up">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h5 class="text-lg font-bold text-gray-800">Tùy Biến Hiển Thị Icon Ứng Dụng</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeCustomizeIconsModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        <!-- Body -->
        <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
            <p class="text-sm text-gray-500 mb-4">Chọn các icon ứng dụng bạn muốn hiển thị trên màn hình chính (Desktop).</p>
            <div class="grid grid-cols-2 gap-y-3 gap-x-4" id="iconCheckboxesContainer">
                <!-- Checkboxes sẽ được tạo bằng JS -->
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition" onclick="closeCustomizeIconsModal()">Hủy</button>
            <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition" onclick="saveIconCustomization()">Lưu thay đổi</button>
        </div>
    </div>
</div>

<!-- Hidden File Input for Wallpaper -->
<input type="file" id="wallpaperInput" accept="image/png, image/jpeg, image/webp" class="hidden">

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out forwards;
}
</style>

<script>
// ==========================================
// TRUE WINDOW MANAGER (IFRAME SPA)
// ==========================================
let zIndexCounter = 100;
const openWindows = {};

function updateTaskbar() {
    const container = document.getElementById('taskbarApps');
    if (!container) return;
    container.innerHTML = '';
    
    for (const id in openWindows) {
        const win = openWindows[id];
        const btn = document.createElement('button');
        const isActive = (win.windowEl.style.zIndex == zIndexCounter && !win.isMinimized);
        
        btn.className = `h-8 flex items-center justify-center gap-1.5 px-3 min-w-[120px] max-w-[160px] rounded hover:bg-white/40 transition-colors ${isActive ? 'bg-white/60 border-b-2 border-blue-600 shadow-sm' : 'bg-white/20'}`;
        btn.title = win.title;
        btn.innerHTML = `<img src="${win.icon}" class="w-4 h-4 object-contain"><span class="text-[11px] font-semibold truncate ${isActive ? 'text-blue-900' : 'text-gray-700'}">${win.title}</span>`;
        
        btn.onclick = () => {
            if (win.isMinimized) {
                restoreApp(id);
            } else if (isActive) {
                minimizeApp(id);
            } else {
                bringToFront(id);
            }
        };
        container.appendChild(btn);
    }

    const copyrightEl = document.getElementById('copyrightText');
    if (copyrightEl) {
        if (Object.keys(openWindows).length > 0) {
            copyrightEl.style.opacity = '0';
            copyrightEl.style.pointerEvents = 'none';
        } else {
            copyrightEl.style.opacity = '0.7';
            copyrightEl.style.pointerEvents = 'none';
        }
    }
}

function openApp(id, title, url, icon) {
    document.getElementById('startMenu').classList.remove('show');
    
    // Check logout
    if (url.includes('dang-xuat')) {
        window.location.href = url;
        return;
    }

    if (openWindows[id]) {
        restoreApp(id);
        return;
    }

    const iframeUrl = url + (url.indexOf('?') > -1 ? '&' : '?') + 'iframe=1';

    // Random slight offset for new windows
    const offset = Object.keys(openWindows).length * 20;
    
    const isMobile = window.innerWidth <= 768;
    const maxClass = isMobile ? ' maximized' : '';
    const initialTop = isMobile ? 0 : (20 + offset);
    const initialLeft = isMobile ? 0 : (100 + offset);
    
    const winHtml = `
        <div class="os-window${maxClass}" id="${id}" style="top: ${initialTop}px; left: ${initialLeft}px; width: 850px; height: 550px; z-index: ${++zIndexCounter};" onmousedown="bringToFront('${id}')">
            <div class="resizer resizer-r" data-resize="r"></div>
            <div class="resizer resizer-l" data-resize="l"></div>
            <div class="resizer resizer-b" data-resize="b"></div>
            <div class="resizer resizer-br" data-resize="br"></div>
            <div class="resizer resizer-bl" data-resize="bl"></div>
            
            <div class="os-window-header" onmousedown="startDrag(event, '${id}')">
                <div class="flex items-center">
                    <div class="os-window-title">
                        <img src="${icon}" class="w-4 h-4 object-contain">
                        ${title}
                    </div>
                    <div class="flex items-center gap-1 ml-4" onmousedown="event.stopPropagation()">
                        <button onclick="goBackInIframe('${id}')" class="p-1 hover:bg-slate-200 text-slate-500 rounded transition flex items-center justify-center w-6 h-6" title="Quay lại"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg></button>
                        <button onclick="reloadIframe('${id}')" class="p-1 hover:bg-slate-200 text-slate-500 rounded transition flex items-center justify-center w-6 h-6" title="Tải lại"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/></svg></button>
                    </div>
                </div>
                <div class="os-window-controls">
                    <button class="os-win-btn" onclick="minimizeApp('${id}')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"/></svg></button>
                    <button class="os-win-btn" onclick="maximizeApp('${id}')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-square" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/></svg></button>
                    <button class="os-win-btn close" onclick="closeApp('${id}')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
                </div>
            </div>
            <div class="window-content">
                <div class="iframe-blocker"></div>
                <iframe src="${iframeUrl}" class="app-iframe"></iframe>
            </div>
        </div>
    `;
    
    document.getElementById('windowContainer').insertAdjacentHTML('beforeend', winHtml);
    const windowEl = document.getElementById(id);
    
    openWindows[id] = {
        windowEl: windowEl,
        title: title,
        icon: icon,
        initialUrl: iframeUrl,
        isMinimized: false
    };
    
    bindResizeAndDrag(windowEl, id);
    updateTaskbar();
}

// Reload iframe an toàn - dùng iframe.src làm fallback khi bị chặn cross-origin
function reloadIframe(id) {
    const iframe = document.querySelector('#' + id + ' iframe');
    if (!iframe) return;
    try {
        iframe.contentWindow.location.reload();
    } catch(e) {
        // Fallback: reset src để buộc tải lại (an toàn với cross-origin)
        const currentSrc = iframe.src;
        iframe.src = '';
        setTimeout(function() { iframe.src = currentSrc; }, 50);
    }
}

function goBackInIframe(id) {
    const win = openWindows[id];
    const iframe = document.querySelector('#' + id + ' iframe');
    if (!iframe || !win) return;
    try {
        const currentUrl = new URL(iframe.contentWindow.location.href);
        const initialUrl = new URL(win.initialUrl, window.location.origin);
        if (currentUrl.pathname === initialUrl.pathname && currentUrl.search === initialUrl.search) return;
        iframe.contentWindow.history.back();
    } catch(e) {
        // Fallback: nếu cross-origin, reset về URL ban đầu
        iframe.src = win.initialUrl;
    }
}

function minimizeApp(id) {
    if (openWindows[id]) {
        openWindows[id].windowEl.classList.add('minimized');
        openWindows[id].isMinimized = true;
        updateTaskbar();
    }
}

function restoreApp(id) {
    if (openWindows[id]) {
        openWindows[id].windowEl.classList.remove('minimized');
        openWindows[id].isMinimized = false;
        bringToFront(id);
    }
}

function maximizeApp(id) {
    if (openWindows[id]) {
        const el = openWindows[id].windowEl;
        el.classList.toggle('maximized');
        bringToFront(id);
    }
}

function closeApp(id) {
    if (openWindows[id]) {
        openWindows[id].windowEl.remove();
        delete openWindows[id];
        updateTaskbar();
    }
}

function bringToFront(id) {
    if (openWindows[id]) {
        openWindows[id].windowEl.style.zIndex = ++zIndexCounter;
        updateTaskbar();
    }
}

function minimizeAllApps() {
    for (const id in openWindows) {
        minimizeApp(id);
    }
}

let isDragging = false, isResizing = false;
let currentWindow = null;
let startX, startY, initialLeft, initialTop, initialWidth, initialHeight;
let resizeType = '';

function bindResizeAndDrag(winEl, id) {
    winEl.querySelectorAll('.resizer').forEach(r => {
        r.addEventListener('mousedown', function(e) {
            if (winEl.classList.contains('maximized')) return;
            e.preventDefault();
            isResizing = true;
            currentWindow = winEl;
            resizeType = this.getAttribute('data-resize');
            startX = e.clientX; startY = e.clientY;
            const style = window.getComputedStyle(winEl);
            initialWidth = parseInt(style.width, 10);
            initialHeight = parseInt(style.height, 10);
            initialLeft = parseInt(style.left, 10);
            initialTop = parseInt(style.top, 10);
            winEl.style.right = 'auto'; winEl.style.bottom = 'auto';
            document.body.classList.add('is-dragging');
            bringToFront(id);
        });
    });
}

function startDrag(e, id) {
    if (e.target.closest('.os-window-controls')) return;
    const winEl = document.getElementById(id);
    if (winEl.classList.contains('maximized')) return;
    isDragging = true;
    currentWindow = winEl;
    startX = e.clientX; startY = e.clientY;
    const style = window.getComputedStyle(winEl);
    initialLeft = parseInt(style.left, 10);
    initialTop = parseInt(style.top, 10);
    if (!winEl.style.width) {
        winEl.style.width = style.width;
        winEl.style.height = style.height;
    }
    winEl.style.right = 'auto'; winEl.style.bottom = 'auto';
    document.body.classList.add('is-dragging');
    bringToFront(id);
}

document.addEventListener('mousemove', function(e) {
    if (isDragging && currentWindow) {
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        currentWindow.style.left = (initialLeft + dx) + 'px';
        currentWindow.style.top = Math.max(0, initialTop + dy) + 'px';
    } else if (isResizing && currentWindow) {
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        if (resizeType.includes('r')) currentWindow.style.width = Math.max(300, initialWidth + dx) + 'px';
        if (resizeType.includes('b')) currentWindow.style.height = Math.max(200, initialHeight + dy) + 'px';
        if (resizeType.includes('l')) {
            currentWindow.style.width = Math.max(300, initialWidth - dx) + 'px';
            currentWindow.style.left = (initialLeft + dx) + 'px';
        }
    }
});

document.addEventListener('mouseup', function() {
    isDragging = false;
    isResizing = false;
    currentWindow = null;
    document.body.classList.remove('is-dragging');
});

// Init
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    let launchUrl = urlParams.get('launch');
    
    const sessionLaunchUrl = <?php echo json_encode($launch_app); ?>;
    if (sessionLaunchUrl) {
        launchUrl = sessionLaunchUrl;
    }

    if (launchUrl) {
        openApp('app_' + Date.now(), 'Ứng Dụng', launchUrl, '/thidua/public/assets/img/logoapp.png');
        
        // Xóa tham số launch khỏi URL để F5 không mở lại app
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});

// ==========================================
// EXISTING SYSTEM LOGIC (Clock, Calendar, StartMenu, Icons)
// ==========================================
// ... (All original functionality preserved below)
</script>

<script>
window.toggleFullscreenF11 = function() {
    if (window.electronAPI && typeof window.electronAPI.toggleFullScreen === 'function') {
        window.electronAPI.toggleFullScreen();
        return;
    }
    if (!document.fullscreenElement) {
        const elem = document.documentElement;
        if (elem.requestFullscreen) {
            elem.requestFullscreen().catch(() => {});
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen().catch(() => {});
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Clock Logic
    if (document.getElementById('taskbarClock')) {
        function updateClock() {
            const now = new Date();
            const timeEl = document.getElementById('taskbarTime');
            const dateEl = document.getElementById('taskbarDate');
            if (!timeEl || !dateEl) return;
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            timeEl.textContent = `${hours}:${minutes}:${seconds}`;
            
            const days = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
            const dayOfWeek = days[now.getDay()];
            const date = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            dateEl.textContent = `${dayOfWeek}, ${date}/${month}/${year}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
        
        const taskbarClock = document.getElementById('taskbarClock');
        const calendarPopup = document.getElementById('calendarPopup');
        const calMonthYear = document.getElementById('calMonthYear');
        const calDays = document.getElementById('calDays');
        const calPrevBtn = document.getElementById('calPrevBtn');
        const calNextBtn = document.getElementById('calNextBtn');
        let currentCalDate = new Date();

        function renderCalendar() {
            const year = currentCalDate.getFullYear();
            const month = currentCalDate.getMonth();
            calMonthYear.textContent = `tháng ${month + 1} năm ${year}`;
            calDays.innerHTML = '';
            
            const firstDayOfMonth = new Date(year, month, 1);
            const lastDayOfMonth = new Date(year, month + 1, 0);
            const daysInMonth = lastDayOfMonth.getDate();
            
            let startDayOfWeek = firstDayOfMonth.getDay() - 1;
            if (startDayOfWeek === -1) startDayOfWeek = 6;
            
            const lastDayOfPrevMonth = new Date(year, month, 0).getDate();
            for (let i = startDayOfWeek - 1; i >= 0; i--) {
                const div = document.createElement('div');
                div.className = 'w-8 h-8 flex items-center justify-center mx-auto text-gray-300 font-medium';
                div.textContent = lastDayOfPrevMonth - i;
                calDays.appendChild(div);
            }
            
            const today = new Date();
            for (let i = 1; i <= daysInMonth; i++) {
                const div = document.createElement('div');
                div.className = 'w-8 h-8 flex items-center justify-center mx-auto rounded-md cursor-pointer hover:bg-gray-100 transition';
                if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                    div.className = 'w-8 h-8 flex items-center justify-center mx-auto rounded-md bg-[#5cb85c] text-white shadow-sm cursor-pointer hover:bg-[#4cae4c] transition';
                }
                div.textContent = i;
                calDays.appendChild(div);
            }
            
            const totalCells = startDayOfWeek + daysInMonth;
            let nextMonthDays = totalCells <= 35 ? 35 - totalCells : 42 - totalCells;
            for (let i = 1; i <= nextMonthDays; i++) {
                const div = document.createElement('div');
                div.className = 'w-8 h-8 flex items-center justify-center mx-auto text-gray-300 font-medium';
                div.textContent = i;
                calDays.appendChild(div);
            }
        }

        taskbarClock.addEventListener('click', function(e) {
            e.stopPropagation();
            calendarPopup.classList.toggle('hidden');
            if (!calendarPopup.classList.contains('hidden')) {
                currentCalDate = new Date();
                renderCalendar();
            }
        });
        
        calPrevBtn.addEventListener('click', function(e) { e.stopPropagation(); currentCalDate.setMonth(currentCalDate.getMonth() - 1); renderCalendar(); });
        calNextBtn.addEventListener('click', function(e) { e.stopPropagation(); currentCalDate.setMonth(currentCalDate.getMonth() + 1); renderCalendar(); });
        calendarPopup.addEventListener('click', function(e) { e.stopPropagation(); });
        document.addEventListener('click', function(e) {
            if (!calendarPopup.contains(e.target) && !taskbarClock.contains(e.target)) calendarPopup.classList.add('hidden');
        });
    }

    // Start Menu Toggle
    const startBtn = document.getElementById('startBtn');
    const startMenu = document.getElementById('startMenu');
    const taskbarSearchContainer = document.getElementById('taskbarSearchContainer');
    if (startBtn && startMenu) {
        document.addEventListener('click', function(e) {
            if (!startBtn.contains(e.target) && !startMenu.contains(e.target) && (!taskbarSearchContainer || !taskbarSearchContainer.contains(e.target))) {
                startMenu.classList.remove('show');
            }
        });
    }

    // Year Switcher Logic
    const yearBtn = document.getElementById('taskbarYearBtn');
    const yearMenu = document.getElementById('taskbarYearMenu');
    if (yearBtn && yearMenu) {
        yearBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            yearMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', function(e) {
            if (!yearBtn.contains(e.target) && !yearMenu.contains(e.target)) {
                yearMenu.classList.add('hidden');
            }
        });
        
        document.querySelectorAll('.year-switcher-option').forEach(opt => {
            opt.addEventListener('click', async function() {
                const id = parseInt(this.dataset.id, 10);
                const newName = this.innerText.trim();
                try {
                    const res = await fetch('/thidua/api/set-nam-hoc', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ nam_hoc_id: id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        yearMenu.classList.add('hidden');
                        if (typeof triggerYearSwitchLoading === 'function') {
                            triggerYearSwitchLoading(newName);
                        } else {
                            window.location.href = '/thidua/admin';
                        }
                    } else {
                        alert(data.message || 'Lỗi chuyển năm học');
                    }
                } catch (e) {
                    console.error(e);
                    alert("Có lỗi xảy ra!");
                }
            });
        });

        // Cross-iframe communication
        window.addEventListener('message', function(e) {
            if (e.data && e.data.action === 'nam_hoc_changed') {
                if (typeof triggerYearSwitchLoading === 'function') {
                    triggerYearSwitchLoading(e.data.new_name);
                } else {
                    window.location.href = '/thidua/admin';
                }
            }
        });
    }

    // Notification Popup Logic
    const taskbarNotifBtn = document.getElementById('taskbarNotifBtn');
    const notificationPopup = document.getElementById('notificationPopup');
    const notifBadge = document.getElementById('notifBadge');
    const notificationList = document.getElementById('notificationList');
    
    if (taskbarNotifBtn && notificationPopup) {
        // Yêu cầu quyền Push Notification khi người dùng click lần đầu vào trang
        document.addEventListener('click', function reqNotif() {
            if (window.Notification && Notification.permission === 'default') {
                Notification.requestPermission();
            }
            document.removeEventListener('click', reqNotif);
        }, { once: true });

        async function fetchNotifications() {
            try {
                const res = await fetch('/thidua/api/get-all-notifications');
                const data = await res.json();
                if (data.new_count > 0) {
                    notifBadge.classList.remove('hidden');
                } else {
                    notifBadge.classList.add('hidden');
                }
                
                if (data.history && data.history.length > 0) {
                    let html = '';
                    let pushedIds = [];
                    try {
                        pushedIds = JSON.parse(localStorage.getItem('pushed_notifications') || '[]');
                    } catch(e) {}

                    data.history.forEach(item => {
                        const isUnread = parseInt(item.da_xem) === 0;
                        
                        // Push notification (chỉ báo 1 lần duy nhất)
                        if (isUnread && !pushedIds.includes(item.id)) {
                            if (window.Notification && Notification.permission === 'granted') {
                                const push = new Notification('Hệ thống Đánh Giá Thi Đua', {
                                    body: item.noi_dung,
                                    icon: '/thidua/public/assets/img/icons/thongbao.png'
                                });
                                push.onclick = function() {
                                    openApp('quan_ly_thong_bao', 'Quản Lý Thông Báo', '/thidua/admin/quan-ly-thong-bao', '/thidua/public/assets/img/icons/thongbao.svg');
                                    window.focus();
                                    push.close();
                                };
                            }
                            pushedIds.push(item.id);
                        }

                        html += `
                        <div class="px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition cursor-pointer ${isUnread ? 'bg-blue-50/30' : ''}" onclick="openApp('quan_ly_thong_bao', 'Quản Lý Thông Báo', '/thidua/admin/quan-ly-thong-bao', '/thidua/public/assets/img/icons/thongbao.svg'); document.getElementById('notificationPopup').classList.add('hidden');">
                            <div class="text-[13px] text-gray-800 ${isUnread ? 'font-semibold' : ''} leading-tight mb-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">${item.noi_dung}</div>
                            <div class="text-[11px] text-gray-500"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-clock mr-1" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>   <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/></svg>${item.time_ago}</div>
                        </div>`;
                    });
                    
                    try {
                        localStorage.setItem('pushed_notifications', JSON.stringify(pushedIds));
                    } catch(e) {}

                    notificationList.innerHTML = html;
                } else {
                    notificationList.innerHTML = '<div class="p-4 text-center text-gray-500 text-[13px] min-h-[100px] flex items-center justify-center">Không có thông báo nào.</div>';
                }
            } catch(e) {}
        }

        taskbarNotifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isHidden = notificationPopup.classList.contains('hidden');
            notificationPopup.classList.toggle('hidden');
            
            if (isHidden) {
                fetchNotifications();
                fetch('/thidua/api/mark-all-notifications-as-read').then(() => {
                    notifBadge.classList.add('hidden');
                });
            }
            
            const calendarPopup = document.getElementById('calendarPopup');
            const yearSwitcherMenu = document.getElementById('taskbarYearMenu');
            if (calendarPopup && !calendarPopup.classList.contains('hidden')) calendarPopup.classList.add('hidden');
            if (yearSwitcherMenu && !yearSwitcherMenu.classList.contains('hidden')) yearSwitcherMenu.classList.add('hidden');
        });
        notificationPopup.addEventListener('click', function(e) { e.stopPropagation(); });
        document.addEventListener('click', function(e) {
            if (!notificationPopup.contains(e.target) && !taskbarNotifBtn.contains(e.target)) notificationPopup.classList.add('hidden');
        });
        
        fetchNotifications();
        setInterval(fetchNotifications, 10000);
    }
    // ==========================================
    // DESKTOP ICONS DRAG & DROP AND GRID SYSTEM
    // ==========================================
    const icons = document.querySelectorAll('.desktop-icon-item');
    const CELL_WIDTH = 90;
    const CELL_HEIGHT = 100;
    let draggedIcon = null;
    let offsetX = 0, offsetY = 0;

    let savedPositions = window.USER_ICON_POSITIONS || {};
    if (typeof savedPositions === 'string') {
        try { savedPositions = JSON.parse(savedPositions); } catch(e) { savedPositions = {}; }
    }
    
    let iconPositions = savedPositions.icons || {};
    if (Array.isArray(iconPositions)) iconPositions = {};
    
    let widgetSettings = savedPositions.widgets || {};
    if (Array.isArray(widgetSettings)) widgetSettings = {};
    
    window.widgetVisibility = savedPositions.visibility || {};
    if (Array.isArray(window.widgetVisibility)) window.widgetVisibility = {};
    
    window.iconVisibility = savedPositions.iconVisibility || {};
    if (Array.isArray(window.iconVisibility)) window.iconVisibility = {};
    
    if (Object.keys(widgetVisibility).length === 0) {
        widgetVisibility = {
            'widget_students': true,
            'widget_violations': true,
            'widget_chart': true,
            'widget_birthdays': true
        };
    }
    
    // ==========================================
    // SAVE POSITIONS & VISIBILITY
    // ==========================================
    function savePositionsToDB() {
        const positions = {};
        icons.forEach(icon => {
            const label = icon.querySelector('span').innerText;
            let px = parseInt(icon.style.left, 10);
            let py = parseInt(icon.style.top, 10);
            positions[label] = {
                x: isNaN(px) ? 0 : px,
                y: isNaN(py) ? 30 : py
            };
        });
        
        const dataToSave = {
            icons: positions,
            widgets: widgetSettings,
            visibility: widgetVisibility,
            iconVisibility: iconVisibility
        };
        
        fetch('/thidua/api/luu-vi-tri-icons', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dataToSave)
        }).catch(err => console.error("Lỗi lưu vị trí:", err));
    }

    function snapToGrid(x, y) {
        return {
            x: Math.max(0, Math.round(x / CELL_WIDTH) * CELL_WIDTH),
            y: Math.max(30, Math.round((y - 30) / CELL_HEIGHT) * CELL_HEIGHT + 30)
        };
    }

    function isCellOccupied(x, y, ignoreIcon) {
        let occupied = false;
        icons.forEach(icon => {
            if (icon !== ignoreIcon) {
                const ix = parseInt(icon.style.left || 0);
                const iy = parseInt(icon.style.top || 0);
                if (Math.abs(ix - x) < 10 && Math.abs(iy - y) < 10) occupied = true;
            }
        });
        return occupied;
    }

    function findNearestEmptyCell(startX, startY, ignoreIcon) {
        let radius = 0;
        let maxRadius = 10;
        while (radius < maxRadius) {
            for (let dx = -radius; dx <= radius; dx++) {
                for (let dy = -radius; dy <= radius; dy++) {
                    if (Math.abs(dx) === radius || Math.abs(dy) === radius) {
                        let nx = startX + dx * CELL_WIDTH;
                        let ny = startY + dy * CELL_HEIGHT;
                        if (nx >= 0 && ny >= 30 && !isCellOccupied(nx, ny, ignoreIcon)) return {x: nx, y: ny};
                    }
                }
            }
            radius++;
        }
        return {x: startX, y: startY};
    }

    let currentX = 0;
    let currentY = 30; // Dịch lưới icon xuống 30px
    const maxDesktopHeight = window.innerHeight - 60;

    icons.forEach(icon => {
        const id = icon.querySelector('span').innerText;
        icon.ondragstart = () => false;
        
        if (iconVisibility[id] === false) {
            icon.style.display = 'none';
            icon.classList.add('hidden-icon');
        } else {
            icon.style.display = 'flex';
            icon.classList.remove('hidden-icon');
        }

        if (iconPositions[id] && (iconPositions[id].x != null || iconPositions[id].left != null)) {
            let px = iconPositions[id].x != null ? parseInt(iconPositions[id].x, 10) : parseInt(iconPositions[id].left, 10);
            let py = iconPositions[id].y != null ? parseInt(iconPositions[id].y, 10) : parseInt(iconPositions[id].top, 10);
            if(isNaN(px)) px = 0;
            if(isNaN(py)) py = 30;
            icon.style.left = px + 'px';
            icon.style.top = py + 'px';
        } else {
            if (currentY + CELL_HEIGHT > maxDesktopHeight) {
                currentY = 30;
                currentX += CELL_WIDTH;
            }
            let snap = findNearestEmptyCell(currentX, currentY, icon);
            icon.style.left = snap.x + 'px';
            icon.style.top = snap.y + 'px';
            iconPositions[id] = {x: snap.x, y: snap.y};
            currentY += CELL_HEIGHT;
        }

        icon.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            draggedIcon = icon;
            const rect = icon.getBoundingClientRect();
            const containerRect = document.querySelector('.os-desktop-icons').getBoundingClientRect();
            offsetX = e.clientX - (rect.left - containerRect.left);
            offsetY = e.clientY - (rect.top - containerRect.top);
            icon.style.zIndex = 1000;
        });
    });

    document.addEventListener('mousemove', function(e) {
        if (!draggedIcon) return;
        const containerRect = document.querySelector('.os-desktop-icons').getBoundingClientRect();
        let newX = e.clientX - containerRect.left - offsetX;
        let newY = e.clientY - containerRect.top - offsetY;
        draggedIcon.style.left = newX + 'px';
        draggedIcon.style.top = newY + 'px';
    });

    document.addEventListener('mouseup', function(e) {
        if (!draggedIcon) return;
        
        const containerRect = document.querySelector('.os-desktop-icons').getBoundingClientRect();
        const rect = draggedIcon.getBoundingClientRect();
        let relativeX = rect.left - containerRect.left;
        let relativeY = rect.top - containerRect.top;

        let snap = snapToGrid(relativeX, relativeY);
        if (isCellOccupied(snap.x, snap.y, draggedIcon)) snap = findNearestEmptyCell(snap.x, snap.y, draggedIcon);

        draggedIcon.style.left = snap.x + 'px';
        draggedIcon.style.top = snap.y + 'px';
        draggedIcon.style.zIndex = '';

        iconPositions[draggedIcon.querySelector('span').innerText] = {x: snap.x, y: snap.y};
        savePositionsToDB();
        draggedIcon = null;
    });

    // ==========================================
    // DESKTOP WIDGETS
    // ==========================================
    const widgets = document.querySelectorAll('.desktop-widget');
    let draggedWidget = null;
    let widgetOffsetX = 0, widgetOffsetY = 0;
    
    // Khởi tạo vị trí và trạng thái widget
    let wX = window.innerWidth - 265; // Bắt đầu từ mép phải
    let wY = 50;
    
    widgets.forEach(widget => {
        const id = widget.id;
        // Kiểm tra setting đã lưu
        if (!widgetSettings[id] || widgetSettings[id].x == null || widgetSettings[id].y == null || isNaN(widgetSettings[id].x) || isNaN(widgetSettings[id].y)) {
            widgetSettings[id] = { x: wX, y: wY };
            wY += widget.offsetHeight + 8;
            if (wY > window.innerHeight - 200) {
                wY = 50;
                wX -= 265;
            }
        }
        
        // Cập nhật trạng thái hiển thị
        if (widgetVisibility[id] === false) {
            widget.style.display = 'none';
            widget.classList.add('hidden-widget');
        } else {
            widget.style.display = 'block';
            widget.classList.remove('hidden-widget');
        }
        
        widget.style.left = widgetSettings[id].x + 'px';
        widget.style.top = widgetSettings[id].y + 'px';
        
        // Drag logic
        widget.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            draggedWidget = widget;
            const rect = widget.getBoundingClientRect();
            widgetOffsetX = e.clientX - rect.left;
            widgetOffsetY = e.clientY - rect.top;
            widget.style.zIndex = 1000;
        });
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!draggedWidget) return;
        let newX = e.clientX - widgetOffsetX;
        let newY = e.clientY - widgetOffsetY;
        
        // Hít mép màn hình
        if (newX < 0) newX = 0;
        if (newY < 0) newY = 0;
        if (newX + draggedWidget.offsetWidth > window.innerWidth) newX = window.innerWidth - draggedWidget.offsetWidth;
        if (newY + draggedWidget.offsetHeight > window.innerHeight - 40) newY = window.innerHeight - 40 - draggedWidget.offsetHeight;
        
        // Tạo lưới vô hình bên phải để hít (kích thước ô 20x20)
        newX = Math.round(newX / 20) * 20;
        newY = Math.round(newY / 20) * 20;
        
        draggedWidget.style.left = newX + 'px';
        draggedWidget.style.top = newY + 'px';
    });

    document.addEventListener('mouseup', function(e) {
        if (!draggedWidget) return;
        const id = draggedWidget.id;
        draggedWidget.style.zIndex = 10;
        widgetSettings[id].x = parseInt(draggedWidget.style.left, 10);
        widgetSettings[id].y = parseInt(draggedWidget.style.top, 10);
        savePositionsToDB();
        draggedWidget = null;
    });

    // ==========================================
    // CONTEXT MENU & SORTING
    // ==========================================
    const contextMenu = document.getElementById('desktopContextMenu');
    const osDesktop = document.querySelector('.os-desktop');

    // Hàm mở modal tuỳ biến widgets
    window.openCustomizeWidgetsModal = function() {
        contextMenu.classList.add('hidden');
        const container = document.getElementById('widgetCheckboxesContainer');
        container.innerHTML = '';
        
        Array.from(widgets).forEach(widget => {
            const id = widget.id;
            const name = widget.getAttribute('data-name');
            const isChecked = widgetVisibility[id] !== false; // Mặc định true nếu ko có
            
            container.innerHTML += `
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" value="${id}" id="chk_${id}" class="widget-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer" ${isChecked ? 'checked' : ''}>
                    <span class="text-sm text-gray-700 group-hover:text-blue-600 transition">${name}</span>
                </label>
            `;
        });
        
        document.getElementById('customizeWidgetsModal').classList.remove('hidden');
    };

    window.closeCustomizeWidgetsModal = function() {
        document.getElementById('customizeWidgetsModal').classList.add('hidden');
    };

    window.saveWidgetCustomization = function() {
        const checkboxes = document.querySelectorAll('.widget-checkbox');
        checkboxes.forEach(chk => {
            const id = chk.value;
            widgetVisibility[id] = chk.checked;
            
            const widget = document.getElementById(id);
            if (chk.checked) {
                widget.style.display = 'block';
                widget.classList.remove('hidden-widget');
            } else {
                widget.style.display = 'none';
                widget.classList.add('hidden-widget');
            }
        });
        
        savePositionsToDB();
        closeCustomizeWidgetsModal();
    };

    // Hàm sắp xếp widgets (từ phải sang trái)
    window.sortWidgets = function() {
        let curX = window.innerWidth - 265; // cách lề phải 265px
        let curY = 50;
        const visibleWidgets = Array.from(widgets).filter(w => !w.classList.contains('hidden-widget') && w.style.display !== 'none');
        
        visibleWidgets.forEach(widget => {
            widget.style.left = curX + 'px';
            widget.style.top = curY + 'px';
            widgetSettings[widget.id] = { x: curX, y: curY };
            
            let h = widget.offsetHeight;
            if (!h || isNaN(h)) h = 200;
            curY += h + 8;
            if (curY > window.innerHeight - 200) {
                curY = 50;
                curX -= 265; // chuyển sang cột tiếp theo về bên trái
            }
        });
        savePositionsToDB();
        contextMenu.classList.add('hidden');
    };

    if (osDesktop) {
        osDesktop.addEventListener('contextmenu', function(e) {
            if (e.target.closest('.os-window') || e.target.closest('.taskbar') || e.target.closest('.start-menu') || e.target.closest('.desktop-icon-item') || e.target.closest('.desktop-widget')) return;
            e.preventDefault();
            let x = e.clientX;
            let y = e.clientY;
            contextMenu.classList.remove('hidden');
            if (x + contextMenu.offsetWidth > window.innerWidth) x = window.innerWidth - contextMenu.offsetWidth;
            if (y + contextMenu.offsetHeight > window.innerHeight) y = window.innerHeight - contextMenu.offsetHeight;
            contextMenu.style.left = x + 'px';
            contextMenu.style.top = y + 'px';
        });
    }

    document.addEventListener('click', function(e) {
        if (contextMenu && !contextMenu.contains(e.target)) contextMenu.classList.add('hidden');
    });

    function sortIcons(asc) {
        let iconsArr = Array.from(icons).filter(i => !i.classList.contains('hidden-icon') && i.style.display !== 'none');
        iconsArr.sort((a, b) => {
            let textA = a.querySelector('span').innerText.toLowerCase();
            let textB = b.querySelector('span').innerText.toLowerCase();
            return asc ? textA.localeCompare(textB) : textB.localeCompare(textA);
        });

        let curX = 0, curY = 30;
        const maxH = window.innerHeight - 60;
        iconsArr.forEach(icon => {
            if (curY + CELL_HEIGHT > maxH) { curY = 30; curX += CELL_WIDTH; }
            icon.style.left = curX + 'px'; icon.style.top = curY + 'px';
            iconPositions[icon.querySelector('span').innerText] = {x: curX, y: curY};
            curY += CELL_HEIGHT;
        });
        savePositionsToDB();
        contextMenu.classList.add('hidden');
    }

    const menuSortAZ = document.getElementById('menuSortAZ');
    const menuSortZA = document.getElementById('menuSortZA');
    if (menuSortAZ) menuSortAZ.addEventListener('click', () => sortIcons(true));
    if (menuSortZA) menuSortZA.addEventListener('click', () => sortIcons(false));

    window.openCustomizeIconsModal = function() {
        contextMenu.classList.add('hidden');
        const container = document.getElementById('iconCheckboxesContainer');
        container.innerHTML = '';
        
        Array.from(icons).forEach((icon, idx) => {
            const label = icon.querySelector('span').innerText;
            const isChecked = iconVisibility[label] !== false;
            const imgSrc = icon.querySelector('img').src;
            
            container.innerHTML += `
                <label class="flex items-center gap-3 cursor-pointer group p-2 hover:bg-gray-50 rounded-lg transition border border-transparent hover:border-gray-200">
                    <input type="checkbox" value="${label}" id="chk_icon_${idx}" class="icon-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer" ${isChecked ? 'checked' : ''}>
                    <img src="${imgSrc}" class="w-6 h-6 object-contain" alt="">
                    <span class="text-sm text-gray-700 group-hover:text-blue-600 transition font-medium truncate">${label}</span>
                </label>
            `;
        });
        
        document.getElementById('customizeIconsModal').classList.remove('hidden');
    };

    window.closeCustomizeIconsModal = function() {
        document.getElementById('customizeIconsModal').classList.add('hidden');
    };

    window.saveIconCustomization = function() {
        const checkboxes = document.querySelectorAll('.icon-checkbox');
        checkboxes.forEach(chk => {
            const label = chk.value;
            iconVisibility[label] = chk.checked;
            
            Array.from(icons).forEach(icon => {
                if (icon.querySelector('span').innerText === label) {
                    if (chk.checked) {
                        icon.style.display = 'flex';
                        icon.classList.remove('hidden-icon');
                    } else {
                        icon.style.display = 'none';
                        icon.classList.add('hidden-icon');
                    }
                }
            });
        });
        
        savePositionsToDB();
        closeCustomizeIconsModal();
        sortIcons(true);
    };

    // ==========================================
    // WALLPAPER UPLOAD
    // ==========================================
    const menuChangeBg = document.getElementById('menuChangeBg');
    const wallpaperInput = document.getElementById('wallpaperInput');

    if (menuChangeBg && wallpaperInput) {
        menuChangeBg.addEventListener('click', function() {
            contextMenu.classList.add('hidden');
            wallpaperInput.click();
        });

        wallpaperInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const dataUrl = event.target.result;
                document.body.style.backgroundImage = `url('${dataUrl}')`;
                if(typeof window.updateIconColors === 'function') window.updateIconColors(dataUrl);

                fetch('/thidua/api/luu-hinh-nen', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ backgroundDataUrl: dataUrl })
                })
                .then(res => res.json())
                .then(data => { if (!data.success) alert('Lỗi: ' + (data.message || 'Không thể lưu hình nền.')); })
                .catch(err => { console.error(err); alert('Lỗi kết nối khi lưu hình nền!'); });
            };
            reader.readAsDataURL(file);
        });
    }

    const menuResetBg = document.getElementById('menuResetBg');
    if (menuResetBg) {
        menuResetBg.addEventListener('click', function() {
            contextMenu.classList.add('hidden');
            const defaultBgUrl = '/thidua/public/assets/img/desktop_bg.jpg';
            document.body.style.backgroundImage = `url('${defaultBgUrl}')`;
            if(typeof window.updateIconColors === 'function') window.updateIconColors(defaultBgUrl);

            fetch('/thidua/api/luu-hinh-nen', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ backgroundDataUrl: 'RESET' })
            })
            .then(res => res.json())
            .then(data => { if (!data.success) alert('Lỗi: ' + (data.message || 'Không thể khôi phục hình nền.')); })
            .catch(err => { console.error(err); alert('Lỗi kết nối khi khôi phục hình nền!'); });
        });
    }
    
    // UNIFIED SEARCH FOR TASKBAR & START MENU
    const startMenuSearch = document.getElementById('startMenuSearch');
    const taskbarSearchInput = document.getElementById('taskbarSearchInput');
    const startMenuEl = document.getElementById('startMenu');

    function filterStartMenuItems(query) {
        const q = (query || '').toLowerCase().trim();
        const groups = document.querySelectorAll('.start-menu-group');
        let totalVisible = 0;
        
        groups.forEach(group => {
            const items = group.querySelectorAll('.start-menu-item');
            let groupVisible = 0;
            items.forEach(item => {
                const nameEl = item.querySelector('.item-name');
                const text = nameEl ? nameEl.innerText.toLowerCase() : '';
                if (!q || text.includes(q)) {
                    item.style.display = 'flex';
                    groupVisible++;
                    totalVisible++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            if (groupVisible > 0) group.style.display = 'block';
            else group.style.display = 'none';
        });
        
        const noResultsMsg = document.getElementById('noResultsMsg');
        if (noResultsMsg) {
            if (totalVisible === 0 && q !== '') noResultsMsg.classList.remove('hidden');
            else noResultsMsg.classList.add('hidden');
        }
        return totalVisible;
    }

    function handleSearchEnter() {
        const visibleItem = document.querySelector('.start-menu-item[style*="display: flex"], .start-menu-item:not([style*="display: none"])');
        if (visibleItem) {
            visibleItem.click();
            if (startMenuEl) startMenuEl.classList.remove('show');
            if (taskbarSearchInput) taskbarSearchInput.blur();
            if (startMenuSearch) startMenuSearch.blur();
        }
    }

    if (startMenuSearch) {
        startMenuSearch.addEventListener('input', function(e) {
            const val = e.target.value;
            if (taskbarSearchInput && taskbarSearchInput.value !== val) taskbarSearchInput.value = val;
            filterStartMenuItems(val);
        });

        startMenuSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearchEnter();
            } else if (e.key === 'Escape') {
                if (startMenuEl) startMenuEl.classList.remove('show');
            }
        });
    }

    if (taskbarSearchInput) {
        taskbarSearchInput.addEventListener('focus', function() {
            if (startMenuEl && !startMenuEl.classList.contains('show')) {
                startMenuEl.classList.add('show');
            }
            filterStartMenuItems(this.value);
        });

        taskbarSearchInput.addEventListener('input', function(e) {
            const val = e.target.value;
            if (startMenuEl && !startMenuEl.classList.contains('show')) {
                startMenuEl.classList.add('show');
            }
            if (startMenuSearch && startMenuSearch.value !== val) startMenuSearch.value = val;
            filterStartMenuItems(val);
        });

        taskbarSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearchEnter();
            } else if (e.key === 'Escape') {
                if (startMenuEl) startMenuEl.classList.remove('show');
                this.blur();
            }
        });
    }

    const startBtnEl = document.getElementById('startBtn');
    if (startBtnEl && startMenuSearch) {
        startBtnEl.addEventListener('click', function() {
            setTimeout(() => {
                if (startMenuEl && startMenuEl.classList.contains('show')) {
                    startMenuSearch.focus();
                }
            }, 50);
        });
    }
});
</script>
<?php else: ?>
    <!-- IFRAME MODE: Không cần Bootstrap JS, dùng openModal/closeModal thuần -->
<?php endif; ?>

<script>
// Javascript Bật/Tắt Modal Mượt Mà (Theo Design Note)
window.openModal = function(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const content = modal.querySelector('.bg-white.rounded-2xl, .modal-content') || modal.firstElementChild;
    modal.classList.remove('hidden');
    void modal.offsetWidth; // Ép trình duyệt render lại (Reflow)
    modal.classList.remove('opacity-0');
    if(content) content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
};

window.closeModal = function(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const content = modal.querySelector('.bg-white.rounded-2xl, .modal-content') || modal.firstElementChild;
    modal.classList.add('opacity-0');
    if(content) content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
};

window.showToast = function(type, message, duration = 3000) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        const style = document.createElement('style');
        style.innerHTML = `
            #toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 99999; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none; }
            .toast-item { padding: 0.75rem 1.25rem; border-radius: 10px; font-size: 0.86rem; font-weight: 600; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 20px rgba(0,0,0,0.15); animation: toastIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275); max-width: 380px; border: 1px solid; background: white; pointer-events: auto; }
            .toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
            .toast-error { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
            .toast-warning { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
            .toast-info { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }
            @keyframes toastIn { from { opacity:0; transform: translateX(50px); } to { opacity:1; transform: translateX(0); } }
            @keyframes toastOut { to { opacity:0; transform: translateX(50px); } }
        `;
        document.head.appendChild(style);
        document.body.appendChild(container);
    }
    
    if (type === 'danger') type = 'error';
    if (!['success', 'error', 'warning', 'info'].includes(type)) type = 'info';

    const toast = document.createElement('div');
    toast.className = `toast-item toast-${type}`;
    
    let icon = '';
    if (type === 'success') icon = '<svg width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>';
    else if (type === 'error') icon = '<svg width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>';
    else if (type === 'warning') icon = '<svg width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>';
    else icon = '<svg width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>';
    
    toast.innerHTML = `${icon} <span>${message}</span>`;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'toastOut 0.3s ease forwards';
        setTimeout(() => { toast.remove(); }, 300);
    }, duration);
};
</script>
</body>
</html>
