<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Kiá»ƒm tra Ä‘Äƒng nháº­p
if (isset($_SESSION['user_id']) || isset($_SESSION['student_id'])) {
    if (isset($_SESSION['user_vai_tro']) && $_SESSION['user_vai_tro'] === 'hoc_sinh') {
        header("Location: /thidua/hocsinh");
    } else {
        header("Location: /thidua/admin");
    }
    exit();
}

$page_title = "Đăng Nhập Hệ Thống";
require_once __DIR__ . '/../../config/database.php';
$db = get_db_connection();

// Lấy năm học hiện tại
require_once __DIR__ . '/../lib/nam_hoc.php';
$public_lookup_nam_hoc_id = get_setting($db, 'public_lookup_nam_hoc_id', 0);
$public_nam_hoc_ten = 'HỆ THỐNG QUẢN LÝ';
if ($public_lookup_nam_hoc_id > 0) {
    $nh = get_nam_hoc_by_id($db, $public_lookup_nam_hoc_id);
    if ($nh) {
        $public_nam_hoc_ten = 'NĂM HỌC ' . mb_strtoupper($nh['ten_nam_hoc'], 'UTF-8');
    }
}
$school_year_display = $public_nam_hoc_ten;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Hệ Thống</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        /* CSS keo tha header cho Electron */
        .electron-drag { -webkit-app-region: drag; }
        .electron-no-drag { -webkit-app-region: no-drag; }
    </style>
</head>
<body class="h-screen flex items-center justify-center relative overflow-hidden bg-gradient-to-br from-[#1e3a8a] to-[#3b82f6]">
    <div class="absolute inset-0 z-0 electron-drag"></div>
    
    <!-- Header title for desktop -->
    <div class="absolute top-0 left-0 right-0 h-10 flex items-center px-4 text-white/80 text-sm electron-drag z-10 font-medium tracking-wide">
        Hệ Thống Thi Đua & Nề Nếp
    </div>

    <!-- Login Container -->
    <div class="z-20 w-[450px] max-w-[90%] bg-white rounded-xl shadow-2xl overflow-hidden electron-no-drag">
        <!-- Re-use the existing dang_nhap.php logic, but make it static instead of a modal -->
        <?php 
        // We will include the dang_nhap.php view, but we need to trick it to show by default
        // The modal is usually hidden by CSS classes. We can use JS to force it open.
        require_once __DIR__ . '/../views/dang_nhap.php'; 
        ?>
    </div>

    <script>
        // Force show the modal from dang_nhap.php inside our container
        document.addEventListener('DOMContentLoaded', () => {
            const loginModal = document.getElementById('loginModal');
            if(loginModal) {
                // Remove fixed positioning and overlay background
                loginModal.className = 'w-full h-full'; 
                const modalContent = loginModal.querySelector('.modal-content');
                if(modalContent) {
                    modalContent.className = 'bg-white w-full';
                    // Hide the close button
                    const closeBtn = modalContent.querySelector('button[onclick*="closeModal"]');
                    if (closeBtn) closeBtn.style.display = 'none';
                }
            }
        });
        function showLoginModal() {} // Override function to prevent errors
        function closeModal(id) {} // Override function to prevent errors
    </script>
</body>
</html>
