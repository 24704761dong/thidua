<?php
// File: public/index.php (Phiên bản linh hoạt, tùy chỉnh tay View/Controller)

// ===================================================================
// KHỞI ĐỘNG VÀ CÀI ĐẶT CHUNG
// ===================================================================
require_once __DIR__ . '/../src/lib/maintenance.php'; //dòng này để bảo trì toàn sever, xóa 2 dấu gạch khi có bảo trì, để lại 2 dấu gạch khi bảo trì xong

// Tắt hiển thị lỗi trên môi trường production, chỉ ghi vào file log
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error_log.txt');

file_put_contents(__DIR__ . '/../../request_log.txt', date('Y-m-d H:i:s') . ' - REQUEST: ' . $_SERVER['REQUEST_URI'] . ' - METHOD: ' . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);

// Ngăn chặn trình duyệt cache trang (đặc biệt là trình duyệt Zalo)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Nạp file khởi động chính của ứng dụng
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../src/lib/login_logger.php';

if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') !== false || strpos($_SERVER['REQUEST_URI'] ?? '', 'dang-nhap') !== false) {
    write_login_log('ROUTER_REQUEST', [
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'session_id' => session_id(),
        'has_user_id' => isset($_SESSION['user_id']),
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_vai_tro' => $_SESSION['user_vai_tro'] ?? null,
        'cookie_sessid' => $_COOKIE[session_name()] ?? 'NOT_SENT'
    ]);
}


// ===================================================================
// BƯỚC 1: PHÂN TÍCH URL YÊU CẦU
// ===================================================================
$base_path = '/thidua';
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = '/'; // Giá trị mặc định cho trang chủ

if (isset($_GET['route']) && $_GET['route'] !== '') {
    $route = urldecode($_GET['route']);
} elseif (strpos($request_uri, $base_path) === 0) {
    $route = substr($request_uri, strlen($base_path));
}

// Chuẩn hóa route
if (empty($route)) {
    $route = '/';
}
if (strlen($route) > 1 && substr($route, -1) === '/') {
    $route = rtrim($route, '/');
}


// ===================================================================
// BƯỚC 2: ĐỊNH NGHĨA CÁC NHÓM ROUTE (VỚI CẤU TRÚC CHI TIẾT)
// ===================================================================

// --- Nhóm route Công khai & Xác thực ---
$public_routes = [
    '/hocsinh/so-nhat-ky/chon-tuan' => ['type' => 'controller', 'file' => 'ctv_nhat_ky_chon_tuan.php'],
'/hocsinh/so-nhat-ky/nhap' => ['type' => 'controller', 'file' => 'ctv_nhap_nhat_ky.php'],

'/public-scan' => ['type' => 'controller', 'file' => 'public_scan_controller.php'],
'/api/public-scan' => ['type' => 'controller', 'file' => 'api_public_scan.php'],

  '/api/set-nam-hoc' => ['type' => 'controller', 'file' => 'api_set_nam_hoc.php'],
  '/api/zalo-bot-webhook' => ['type' => 'controller', 'file' => 'api_zalo_bot_webhook.php'],
  '/admin/quan-ly-nam-hoc' => ['type' => 'controller', 'file' => 'admin_quan_ly_nam_hoc.php'],
  '/admin/quan-ly-email-hoc-sinh' => ['type' => 'view', 'file' => 'admin_quan_ly_email_hoc_sinh.php'],
  '/api/admin/email-hoc-sinh' => ['type' => 'controller', 'file' => 'api_admin_email_hoc_sinh.php'],
'/api/nam-hoc-crud' => ['type' => 'controller', 'file' => 'api_nam_hoc_crud.php'],
'/admin/nhan-hoc-sinh' => ['type' => 'controller', 'file' => 'admin_nhan_hoc_sinh.php'],
  '/api/nhan-hoc-sinh' => ['type' => 'controller', 'file' => 'api_nhan_hoc_sinh.php'],
  
  '/admin/hoat-dong' => [
      'type' => 'controller',
      'file' => 'quan_ly_hoat_dong.php'
  ],
  '/admin/hoat-dong-diem-danh' => [
      'type' => 'controller',
      'file' => 'admin_hoat_dong_diem_danh.php'
  ],
  '/api/hoat-dong-crud' => [
      'type' => 'controller',
      'file' => 'api_hoat_dong_crud.php'
  ],
  '/api/hoat-dong-diem-danh' => [
      'type' => 'controller',
      'file' => 'api_hoat_dong_diem_danh.php'
  ],

  '/admin/exam-list' => [
        'type' => 'controller',
        'file' => 'admin_exam_list.php'
    ],
    '/api/exam-crud' => [
        'type' => 'controller',
        'file' => 'api_exam_crud.php'
    ],
    '/admin/exam-detail' => [
        'type' => 'controller',
        'file' => 'admin_exam_detail.php'
    ],
    '/admin/exam-participants' => [
        'type' => 'controller',
        'file' => 'admin_exam_participants.php'
    ],
    '/api/exam-manage-students' => [
        'type' => 'controller',
        'file' => 'api_exam_manage_students.php'
    ],
    '/api/exam-generate-sbd' => [
        'type' => 'controller',
        'file' => 'api_exam_generate_sbd.php'
    ],
    '/admin/exam-export-template' => [
        'type' => 'controller',
        'file' => 'admin_exam_export_template.php'
    ],
    '/api/exam-import-data' => [
        'type' => 'controller',
        'file' => 'api_exam_import_data.php'
    ],
    '/admin/exam-rooms' => [
        'type' => 'controller',
        'file' => 'admin_exam_rooms.php'
    ],
    '/api/exam-room-crud' => [
        'type' => 'controller',
        'file' => 'api_exam_room_crud.php'
    ],
    '/api/exam-room-auto-assign' => [
        'type' => 'controller',
        'file' => 'api_exam_room_auto_assign.php'
    ],
    '/api/exam-shift-crud' => [
        'type' => 'controller',
        'file' => 'api_exam_shift_crud.php'
    ],
    '/admin/quan-ly-diem-thi' => [
        'type' => 'controller',
        'file' => 'quan_ly_diem_thi.php' // Controller chính
    ],
    '/admin/quan-ly-phuc-khao' => [
        'type' => 'controller',
        'file' => 'quan_ly_phuc_khao.php' // Controller trang quản lý
    ],
    '/api/xu-ly-phuc-khao' => [
        'type' => 'controller',
        'file' => 'api_xu_ly_phuc_khao.php' // API xử lý (lưu điểm mới, cập nhật trạng thái)
    ],
    '/nop-don-phuc-khao' => [ // Trang nộp đơn (cần session xác minh)
        'type' => 'controller',
        'file' => 'trang_nop_don_phuc_khao.php'
    ],
    '/api/submit-phuc-khao' => [ // API xử lý nộp đơn
        'type' => 'controller',
        'file' => 'api_submit_phuc_khao.php'
    ],
    '/diemthi' => [
        'type' => 'controller', 
        'file' => 'trang_tra_cuu_diem.php' // Controller trang tra cứu
    ],
    '/api/xac-minh-phuc-khao' => [
        'type' => 'controller', 
        'file' => 'api_xac_minh_phuc_khao.php' 
    ],
    '/api/tra-cuu-diem' => [
        'type' => 'controller', 
        'file' => 'api_tra_cuu_diem.php' // API xử lý tìm kiếm
    ],
    '/admin/xuat-mau-diem-thi' => [
        'type' => 'controller',
        'file' => 'xuat_mau_diem_thi.php' // API xuất file mẫu Excel
    ],
    '/api/nhap-diem-thi-excel' => [
        'type' => 'controller',
        'file' => 'nhap_diem_thi_excel.php' // API nhận file Excel import
    ],
    '/api/luu-diem-thi-tay' => [
        'type' => 'controller',
        'file' => 'luu_diem_thi_tay.php' // API lưu điểm khi nhập tay
    ],
    '/admin/cau-hinh-tra-cuu-diem-thi' => [
        'type' => 'controller',
        'file' => 'cau_hinh_tra_cuu_diem_thi.php' // Controller trang cài đặt
    ],
    '/api/luu-cau-hinh-tra-cuu' => [
        'type' => 'controller',
        'file' => 'api_luu_cau_hinh_tra_cuu.php' // API lưu cài đặt
    ],
    '/api/zalo/update-profile' => ['type' => 'controller', 'file' => 'api_zalo_update_profile.php'],
    '/api/zalo/send-otp' => ['type' => 'controller', 'file' => 'api_zalo_send_otp.php'],
    '/api/zalo/get-notifications' => ['type' => 'controller', 'file' => 'api_zalo_get_notifications.php'],
    '/api/zalo/read-notifications' => ['type' => 'controller', 'file' => 'api_zalo_read_notifications.php'],
    '/api/zalo/get-gpa-data' => ['type' => 'controller', 'file' => 'api_zalo_get_gpa_data.php'],
    '/api/zalo/save-gpa-data' => ['type' => 'controller', 'file' => 'api_zalo_save_gpa_data.php'],
    '/api/zalo/email-hoc-sinh' => ['type' => 'controller', 'file' => 'api_zalo_email_hoc_sinh.php'],
    '/api/zalo/register-fcm-token' => ['type' => 'controller', 'file' => 'api_zalo_register_fcm_token.php'],

// ... các route api cũ
'/api/set-student-status' => ['type' => 'controller', 'file' => 'api_set_student_status.php'],
 '/api/dong-bo-nhat-ky' => ['type' => 'controller', 'file' => 'api_dong_bo_nhat_ky.php'], // <-- DÒNG MỚI
 '/admin/xem-minh-chung' => ['type' => 'controller', 'file' => 'admin_xem_minh_chung.php'],
    '/admin/xuat-minh-chung-zip' => ['type' => 'controller', 'file' => 'xuat_minh_chung_zip.php'],
    '/api/admin/xoa-minh-chung-tuan' => ['type' => 'controller', 'file' => 'api_xoa_minh_chung_tuan.php'],
    '/api/admin/tao-lai-thumbnail-tuan' => ['type' => 'controller', 'file' => 'api_regen_thumbnails.php'],
    '/api/admin/tao-lai-thumbnail-nam' => ['type' => 'controller', 'file' => 'api_regen_thumbnails.php'],
    '/api/archive-selected-proofs' => ['type' => 'controller', 'file' => 'api_archive_selected_proofs.php'],
    '/api/restore-selected-proofs' => ['type' => 'controller', 'file' => 'api_restore_selected_proofs.php'],
    '/api/admin/api_get_proof_ids_for_backup' => ['type' => 'controller', 'file' => 'api_get_proof_ids_for_backup.php'],
    '/api/admin/api_backup_single_onedrive' => ['type' => 'controller', 'file' => 'api_backup_single_onedrive.php'],
    '/api/admin/api_get_violation_proof_ids_for_backup' => ['type' => 'controller', 'file' => 'api_get_violation_proof_ids_for_backup.php'],
    '/api/admin/api_backup_single_violation_onedrive' => ['type' => 'controller', 'file' => 'api_backup_single_violation_onedrive.php'],
'/api/ctv/luu-nhat-ky' => ['type' => 'controller', 'file' => 'api_ctv_luu_nhat_ky.php'],
'/admin/hoc-sinh-luu-tru' => ['type' => 'controller', 'file' => 'admin_hoc_sinh_luu_tru.php'], // <-- THÊM DÒNG NÀY
    '/admin/the-hoc-sinh/in' => ['type' => 'controller', 'file' => 'in_the_hoc_sinh_controller.php'],
'/api/ctv/upload-minh-chung-nhat-ky' => ['type' => 'controller', 'file' => 'api_ctv_upload_minh_chung_nhat_ky.php'],
'/api/ctv/xoa-minh-chung-nhat-ky' => ['type' => 'controller', 'file' => 'api_ctv_xoa_minh_chung_nhat_ky.php'],
'/api/ctv/gui-nhat-ky' => ['type' => 'controller', 'file' => 'api_ctv_gui_nhat_ky.php'],
// ... các route admin cũ
  '/admin/duyet-so-nhat-ky' => ['type' => 'controller', 'file' => 'admin_duyet_nhat_ky.php'],
  '/admin/xem-chi-tiet-nhat-ky' => ['type' => 'controller', 'file' => 'admin_xem_chi_tiet_nhat_ky.php'],
  '/admin/xuat-so-nhat-ky-zip' => ['type' => 'controller', 'file' => 'xuat_so_nhat_ky_zip.php'],
// ... các route api cũ
'/gioi-thieu' => ['type' => 'controller', 'file' => 'public_gioi_thieu.php'],
'/api/admin/xu-ly-nhat-ky' => ['type' => 'controller', 'file' => 'api_admin_xu_ly_nhat_ky.php'],
    '/' => ['type' => 'controller', 'file' => 'tra_cuu_vi_pham.php'],
    '/tracuu' => ['type' => 'controller', 'file' => 'tra_cuu_vi_pham.php'],
    '/dang-nhap' => ['type' => 'controller', 'file' => 'trang_dang_nhap.php'],
    '/dang-nhap-xu-ly' => ['type' => 'controller', 'file' => 'dang_nhap_xu_ly.php'],
    '/dang-xuat' => ['type' => 'controller', 'file' => 'dang_xuat.php'],
    '/captcha-image' => ['type' => 'controller', 'file' => 'captcha_image.php'],
];

// --- Nhóm route khu vực Quản trị (Admin) ---
$admin_routes = [
    '/admin/quan-ly-zalo-mini' => ['type' => 'view', 'file' => 'admin_quan_ly_zalo_mini.php'],
    '/admin/duyet-thong-tin-zalo' => ['type' => 'view', 'file' => 'admin_duyet_thong_tin_zalo.php'],
    '/admin/dang-ky-truc/duyet' => ['type' => 'controller', 'file' => 'api_admin_dang_ky_truc_duyet.php'],
    '/admin/dang-ky-truc/xoa' => ['type' => 'controller', 'file' => 'api_admin_dang_ky_truc_xoa.php'],
    '/admin/dang-ky-truc/chi-tiet' => ['type' => 'controller', 'file' => 'api_admin_dang_ky_truc_chi_tiet.php'],
    '/admin/hoc-sinh-tot-nghiep' => ['type' => 'controller', 'file' => 'admin_hoc_sinh_tot_nghiep.php'],
    '/admin/the-hoc-sinh/in' => ['type' => 'controller', 'file' => 'in_the_hoc_sinh_controller.php'],
     '/admin/lich-su-thong-bao-cong-khai' => ['type' => 'controller', 'file' => 'admin_lich_su_thong_bao.php'],
    '/api/quan-ly-mau-the' => ['type' => 'controller', 'file' => 'the_hoc_sinh_api.php'],
    '/api/upload-phoi-the' => ['type' => 'controller', 'file' => 'the_hoc_sinh_api.php'],
    '/admin/quan-ly-thong-bao' => ['type' => 'controller', 'file' => 'admin_quan_ly_thong_bao.php'],

    '/api/rename-anh-the' => [
        'type' => 'controller',
        'file' => 'the_hoc_sinh_api.php'
    ],
    '/api/auto-rename-anh-the' => [
        'type' => 'controller',
        'file' => 'the_hoc_sinh_api.php'
    ],
    // --- Routes cho Module Xử lý Trễ học ---
'/admin/xu-ly-tre-hoc' => ['type' => 'controller', 'file' => 'tre_hoc_main.php'],
'/admin/xu-ly-tre-hoc/cai-dat' => ['type' => 'controller', 'file' => 'tre_hoc_cai_dat.php'],
// --- APIs cho Module Xử lý Trễ học ---
'/api/admin/tre-hoc/luu-cai-dat' => ['type' => 'controller', 'file' => 'api_tre_hoc_luu_cai_dat.php'],
'/api/admin/tre-hoc/xu-ly-import' => ['type' => 'controller', 'file' => 'api_tre_hoc_xu_ly_import.php'],
'/api/admin/tre-hoc/hoan-tat' => ['type' => 'controller', 'file' => 'api_tre_hoc_hoan_tat.php'],
    '/admin/diem-danh-nang-cao' => ['type' => 'controller', 'file' => 'diem_danh_nc_chon_tuan.php'],
'/admin/diem-danh-nang-cao/nhap' => ['type' => 'controller', 'file' => 'diem_danh_nc_main.php'],
'/admin/diem-danh-nang-cao/cai-dat' => ['type' => 'controller', 'file' => 'diem_danh_nc_cai_dat.php'],
// --- APIs cho Module Điểm danh Nâng cao ---
'/api/admin/diem-danh-nang-cao/luu-cai-dat' => ['type' => 'controller', 'file' => 'api_diem_danh_nc_luu_cai_dat.php'],
'/api/admin/diem-danh-nang-cao/xu-ly-import' => ['type' => 'controller', 'file' => 'api_diem_danh_nc_xu_ly_import.php'],
'/api/admin/diem-danh-nang-cao/hoan-tat' => ['type' => 'controller', 'file' => 'api_diem_danh_nc_hoan_tat.php'],
    '/api/admin/delete-ctv-codes' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/admin/xuat-excel-ma-ctv' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/run-tasks' => ['type' => 'controller', 'file' => 'run_cron_tasks.php'],
    '/api/admin/create-daily-codes' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/kich-hoat-ctv' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/admin/get-ctv-codes' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/admin/create-ctv-code' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/admin/toggle-ctv-code-status' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/admin/get-code-users' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/admin/quan-ly-ma-ctv' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/luu-thong-bao-cong-khai' => ['type' => 'controller', 'file' => 'api_luu_thong_bao_cong_khai.php'],
    '/admin/the-hoc-sinh' => ['type' => 'controller', 'file' => 'the_hoc_sinh_web.php'],
    '/api/luu-mau-the' => ['type' => 'controller', 'file' => 'the_hoc_sinh_api.php'],
    '/admin/the-hoc-sinh/danh-sach' => ['type' => 'controller', 'file' => 'the_hoc_sinh_web.php'],
    '/admin/the-hoc-sinh/xuat-mau-import' => ['type' => 'controller', 'file' => 'xuat_mau_import_the.php'],
    '/admin/the-hoc-sinh/nhap-file-cap-nhat' => ['type' => 'controller', 'file' => 'nhap_file_cap_nhat_the.php'],
    '/admin/the-hoc-sinh/cai-dat' => ['type' => 'controller', 'file' => 'the_hoc_sinh_web.php'],
    '/admin' => ['type' => 'controller', 'file' => 'quan_ly_admin.php'],
    '/admin/the-hoc-sinh/xem-truoc-import' => ['type' => 'view', 'file' => 'xem_truoc_import_the.php'],
    '/admin/cai-dat' => ['type' => 'controller', 'file' => 'admin_cai_dat.php'],
    '/quan-ly-tai-khoan-ca-nhan' => ['type' => 'controller', 'file' => 'quan_ly_tai_khoan_ca_nhan.php'],
    '/quan-ly-the-hoc-sinh' => ['type' => 'controller', 'file' => 'the_hoc_sinh_web.php'],
    '/admin/hoc-sinh' => ['type' => 'controller', 'file' => 'HocSinhController.php'],
    '/admin/giao-vien' => ['type' => 'controller', 'file' => 'GiaoVienController.php'],
    '/admin/tai-khoan' => ['type' => 'controller', 'file' => 'TaiKhoanController.php'],
    '/admin/tuan-hoc' => ['type' => 'controller', 'file' => 'TuanHocController.php'],
    '/admin/ctv' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/admin/cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'quan_ly_cau_hinh_vi_pham.php'],
    '/admin/khen-thuong' => ['type' => 'controller', 'file' => 'KhenThuongController.php'],
    '/doi-mat-khau' => ['type' => 'controller', 'file' => 'doi_mat_khau.php'],
    '/update-gvcn-info' => ['type' => 'controller', 'file' => 'update_gvcn_info.php'],
    '/admin/email-logs' => ['type' => 'controller', 'file' => 'admin_email_logs.php'],

    '/them-cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'api_cau_hinh_vi_pham.php'],
    '/sua-cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'api_cau_hinh_vi_pham.php'],
    '/xoa-cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'api_cau_hinh_vi_pham.php'],

    '/admin/trung-tam-duyet' => ['type' => 'controller', 'file' => 'admin_trung_tam_duyet.php'],
    '/admin/duyet-vi-pham' => ['type' => 'controller', 'file' => 'admin_duyet_vi_pham.php'],
    '/admin/duyet-vang-hoc' => ['type' => 'controller', 'file' => 'admin_duyet_vang_hoc.php'],
    '/api/admin/xu-ly-vang-hoc' => ['type' => 'controller', 'file' => 'api_admin_xu_ly_vang_hoc.php'],

    '/quan-ly-dang-ky-truc' => ['type' => 'controller', 'file' => 'quan_ly_dang_ky_truc.php'],
    '/admin/vi-pham' => ['type' => 'controller', 'file' => 'ViPhamController.php'],


    '/nhap-diem-thi-dua' => ['type' => 'controller', 'file' => 'nhap_thi_dua.php'],
    '/tai-file-mau-hoc-sinh' => ['type' => 'controller', 'file' => 'tai_file_mau.php'],
    '/api/import-vi-pham-excel' => ['type' => 'controller', 'file' => 'api_import_vi_pham_excel.php'],
    '/api/unlink-zalo' => ['type' => 'controller', 'file' => 'api_unlink_zalo.php'],
    '/api/unlink-google' => ['type' => 'controller', 'file' => 'api_unlink_google.php'],
    '/tai-mau-import-vi-pham' => ['type' => 'controller', 'file' => 'tai_mau_import_vi_pham.php'],
    '/xuat-ds-tai-khoan-ctv' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/xuat-mau-gvcn' => ['type' => 'controller', 'file' => 'xuat_mau_gvcn.php'],
    '/import-gvcn' => ['type' => 'controller', 'file' => 'import_gvcn.php'],
    '/tai-mau-cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'api_cau_hinh_vi_pham.php'],
    '/import-cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'api_cau_hinh_vi_pham.php'],
    '/xem-truoc-import-cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'api_cau_hinh_vi_pham.php'],
    '/luu-import-cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'api_cau_hinh_vi_pham.php'],
    '/xuat-cau-hinh-vi-pham' => ['type' => 'controller', 'file' => 'api_cau_hinh_vi_pham.php'],


    '/nhap-diem-thi-dua/tai-mau' => ['type' => 'controller', 'file' => 'nhap_thi_dua.php'],
    '/nhap-diem-thi-dua/import' => ['type' => 'controller', 'file' => 'nhap_thi_dua.php'],
    '/tai-mau-khen-thuong' => ['type' => 'controller', 'file' => 'tai_mau_khen_thuong.php'],

    '/bao-cao' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/bao-cao/thi-dua' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/bao-cao/vi-pham' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/bao-cao/vi-pham-chung-theo-lop' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/bao-cao/theo-ten-vi-pham' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/bao-cao/vi-pham-chi-tiet-theo-lop' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/bao-cao/nang-cap' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/bao-cao/phan-tich-lop' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/bao-cao/cong-khai' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/xuat-bao-cao-thi-dua' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao-thi-dua-pdf' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/print/bao-cao-thi-dua' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-ds-truc-tuan' => ['type' => 'controller', 'file' => 'xuat_ds_truc_tuan.php'],
    '/in-bao-cao-thi-dua' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao-vi-pham' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/print/bao-cao-vi-pham' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao-vi-pham-chung' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao-theo-ten-vi-pham' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao-chi-tiet-lop' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao/thanh-tich-toan-dien' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao/chi-tiet-tuan-theo-lop' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao/ds-vi-pham' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao/hs-sl-vp-ca-nhan' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao/toan-bo-ho-so-zip' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao/chi-tiet-tuan-zip' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/xuat-bao-cao/chot-kxtd-lt2t' => ['type' => 'controller', 'file' => 'bao_cao_export.php'],
    '/admin/tra-cuu-hoc-sinh' => ['type' => 'controller', 'file' => 'tra_cuu_hoc_sinh.php'],
    '/admin/lich-sinh-nhat' => ['type' => 'controller', 'file' => 'lich_sinh_nhat.php'],


    '/admin/nhat-ky' => ['type' => 'controller', 'file' => 'nhat_ky_he_thong.php'],
    '/admin/nhat-ky/su-dung' => ['type' => 'controller', 'file' => 'nhat_ky_su_dung.php'],
    '/admin/cau-hinh-bao-cao' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/admin/huong-dan-cau-hinh-bao-cao' => ['type' => 'controller', 'file' => 'bao_cao_web.php'],
    '/admin/lich-su-thong-bao' => ['type' => 'controller', 'file' => 'lich_su_thong_bao.php'],
    '/admin/quan-ly-anh-the' => ['type' => 'controller', 'file' => 'the_hoc_sinh_web.php'],
    '/api/upload-anh-the' => ['type' => 'controller', 'file' => 'the_hoc_sinh_api.php'],
    '/api/delete-anh-the' => ['type' => 'controller', 'file' => 'the_hoc_sinh_api.php'],
    '/api/assign-anh-the' => ['type' => 'controller', 'file' => 'the_hoc_sinh_api.php'],
    '/admin/xuat-anh-the-zip' => ['type' => 'controller', 'file' => 'xuat_anh_the_zip.php'],
    '/api/2fa-generate' => ['type' => 'controller', 'file' => 'api_2fa_generate.php'],
    '/api/2fa-verify' => ['type' => 'controller', 'file' => 'api_2fa_verify.php'],
    '/api/2fa-disable' => ['type' => 'controller', 'file' => 'api_2fa_disable.php'],
    '/xac-thuc-2fa' => ['type' => 'controller', 'file' => 'xac_thuc_2fa.php'],
    '/api/2fa-login' => ['type' => 'controller', 'file' => 'api_2fa_login.php'],
    '/oauth-callback-google' => ['type' => 'controller', 'file' => 'oauth_callback_google.php'],
    '/oauth-redirect-google' => ['type' => 'controller', 'file' => 'oauth_redirect_google.php'],
    '/oauth-callback-zalo' => ['type' => 'controller', 'file' => 'oauth_callback_zalo.php'],
    '/oauth-redirect-zalo' => ['type' => 'controller', 'file' => 'oauth_redirect_zalo.php'],
    // Zalo OA Token OAuth (lấy token cho hệ thống gửi ZNS)
    '/oauth-redirect-zalo-oa' => ['type' => 'controller', 'file' => 'oauth_redirect_zalo_oa.php'],
    '/oauth-callback-zalo-oa' => ['type' => 'controller', 'file' => 'oauth_callback_zalo_oa.php'],
    '/api/zalo-oa-refresh-token' => ['type' => 'controller', 'file' => 'api_zalo_oa_refresh_token.php'],
];

// --- Nhóm route Cộng tác viên (CTV / Học sinh) ---
$ctv_routes = [
    '/hocsinh' => ['type' => 'controller', 'file' => 'giao_vu.php'],
    '/hocsinh/chon-tuan' => ['type' => 'controller', 'file' => 'ctv_chon_tuan.php'],
    '/dang-ky-truc' => ['type' => 'controller', 'file' => 'dang_ky_truc.php'],
    '/hocsinh/nhap-vi-pham' => ['type' => 'controller', 'file' => 'ctv_nhap_vi_pham.php'],
    '/hocsinh/danh-sach-da-gui' => ['type' => 'controller', 'file' => 'ctv_danh_sach_da_gui.php'],
  
    '/hocsinh/thong-tin-ca-nhan' => ['type' => 'controller', 'file' => 'ctv_thong_tin_ca_nhan.php'],
    '/hocsinh/doi-mat-khau-xu-ly' => ['type' => 'controller', 'file' => 'ctv_doi_mat_khau.php'],
];

// --- Nhóm route API (Xử lý dữ liệu ngầm) ---
$api_routes = [
    '/api/get-student-by-cccd' => ['type' => 'controller', 'file' => 'api_get_student_by_cccd.php'],
    '/api-student' => ['type' => 'controller', 'file' => 'api_student.php'],
    '/api/submit-support-request' => ['type' => 'controller', 'file' => 'api_submit_support_request.php'],
    '/api/reply-support-request' => ['type' => 'controller', 'file' => 'api_reply_support_request.php'],
    '/api/send-otp' => ['type' => 'controller', 'file' => 'api_send_otp.php'],
    '/api/verify-otp' => ['type' => 'controller', 'file' => 'api_verify_otp_and_update_email.php'],
    '/api/lookup-student' => ['type' => 'controller', 'file' => 'api_lookup_student.php'],
    '/api/get-hoc-sinh-details' => ['type' => 'controller', 'file' => 'get_hoc_sinh_details.php'],
    '/api/get-user-details' => ['type' => 'controller', 'file' => 'get_user_details.php'],
    '/api/get-class-summary' => ['type' => 'controller', 'file' => 'get_class_summary.php'],
    '/api/search-students' => ['type' => 'controller', 'file' => 'api_search_students.php'],
    '/api/toggle-student-permission' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/bulk-grant-permissions' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/bulk-provision-accounts' => ['type' => 'controller', 'file' => 'api_bulk_provision_accounts.php'],
    '/api/bulk-revoke-permissions' => ['type' => 'controller', 'file' => 'CtvController.php'],
    '/api/revoke-all-permissions' => ['type' => 'controller', 'file' => 'CtvController.php'],

    '/api/send-notification' => ['type' => 'controller', 'file' => 'api_send_notification.php'],
    '/api/luu-thi-dua' => ['type' => 'controller', 'file' => 'api_luu_thi_dua.php'],

    '/api/luu-dang-ky-truc' => ['type' => 'controller', 'file' => 'api_luu_dang_ky_truc.php'],
    '/api/get-duty-details' => ['type' => 'controller', 'file' => 'api_get_duty_details.php'],
    '/api/manage-duty' => ['type' => 'controller', 'file' => 'api_manage_duty.php'],
    '/api/ctv-add-violation' => ['type' => 'controller', 'file' => 'api_ctv_add_violation.php'],
    '/api/ctv-delete-violation' => ['type' => 'controller', 'file' => 'api_ctv_delete_violation.php'],
    '/api/ctv-submit-violations' => ['type' => 'controller', 'file' => 'api_ctv_submit_violations.php'],
    '/api/ctv-luu-nhieu-vi-pham' => ['type' => 'controller', 'file' => 'api_ctv_luu_nhieu_vi_pham.php'],
    '/api/ctv-xoa-nhieu-vi-pham' => ['type' => 'controller', 'file' => 'api_ctv_xoa_nhieu_vi_pham.php'],
    '/api/admin-xu-ly-vi-pham' => ['type' => 'controller', 'file' => 'api_admin_xu_ly_vi_pham.php'],

    '/api/get-all-notifications' => ['type' => 'controller', 'file' => 'api_get_all_notifications.php'],
    '/api/mark-all-notifications-as-read' => ['type' => 'controller', 'file' => 'api_mark_all_notifications_as_read.php'],
    '/api/ctv-cap-nhat-thong-tin' => ['type' => 'controller', 'file' => 'ctv_cap_nhat_thong_tin.php'],
    '/api/ctv-send-otp' => ['type' => 'controller', 'file' => 'api_ctv_send_otp.php'],
    '/api/ctv-verify-otp' => ['type' => 'controller', 'file' => 'api_ctv_verify_otp.php'],
    '/api/toggle-setting' => ['type' => 'controller', 'file' => 'api_toggle_setting.php'],
    '/api/toggle-admin-login-alert' => ['type' => 'controller', 'file' => 'api_toggle_admin_login_alert.php'],
    '/api/admin-change-password' => ['type' => 'controller', 'file' => 'api_admin_change_password.php'],
    '/api/save-user-settings' => ['type' => 'controller', 'file' => 'api_save_user_settings.php'],
    '/api/get-dashboard-stats' => ['type' => 'controller', 'file' => 'api_get_dashboard_stats.php'],
    '/api/luu-hinh-nen' => ['type' => 'controller', 'file' => 'api_luu_hinh_nen.php'],
    '/api/luu-vi-tri-icons' => ['type' => 'controller', 'file' => 'api_luu_vi_tri_icons.php'],
    '/api/get-student-log-details' => ['type' => 'controller', 'file' => 'api_get_student_log_details.php'],

    '/api/get-kxtd-reason' => ['type' => 'controller', 'file' => 'api_get_kxtd_reason.php'],
    '/api/get-violation-details' => ['type' => 'controller', 'file' => 'api_get_violation_details.php'],
    '/api/get-attendance-details' => ['type' => 'controller', 'file' => 'api_get_attendance_details.php'],

    '/api/terminate-session' => ['type' => 'controller', 'file' => 'api_terminate_session.php'],

    '/api/admin-pwa' => ['type' => 'controller', 'file' => 'api_admin_pwa.php'],
    '/api/admin/regen-thumbnails' => ['type' => 'controller', 'file' => 'api_regen_thumbnails.php'],
    // Cloud storage helpers
    '/api/get-presigned-url' => ['type' => 'controller', 'file' => 'api_get_presigned_url.php'],
    '/api/archive-selected-proofs' => ['type' => 'controller', 'file' => 'api_archive_selected_proofs.php'],
    '/api/restore-selected-proofs' => ['type' => 'controller', 'file' => 'api_restore_selected_proofs.php'],
    '/api/get-all-cloud-proof-ids' => ['type' => 'controller', 'file' => 'api_get_all_cloud_proof_ids.php'],
    '/api/admin/api_get_proof_ids_for_backup' => ['type' => 'controller', 'file' => 'api_get_proof_ids_for_backup.php'],
    '/api/admin/api_backup_single_onedrive' => ['type' => 'controller', 'file' => 'api_backup_single_onedrive.php'],
    '/api/admin/api_get_violation_proof_ids_for_backup' => ['type' => 'controller', 'file' => 'api_get_violation_proof_ids_for_backup.php'],
    '/api/admin/api_backup_single_violation_onedrive' => ['type' => 'controller', 'file' => 'api_backup_single_violation_onedrive.php'],
    '/admin/quan-ly-khao-sat' => ['type' => 'view', 'file' => 'admin_quan_ly_khao_sat.php'],
    '/admin/khao-sat-builder' => ['type' => 'view', 'file' => 'admin_khao_sat_builder.php'],
    '/admin/khao-sat-bao-cao' => ['type' => 'view', 'file' => 'admin_khao_sat_bao_cao.php'],
    '/api/admin/khao-sat' => ['type' => 'controller', 'file' => 'api_admin_khao_sat.php'],
    '/api/zalo/get-surveys' => ['type' => 'controller', 'file' => 'api_zalo_get_surveys.php'],
    '/api/zalo/get-survey-detail' => ['type' => 'controller', 'file' => 'api_zalo_get_survey_detail.php'],
    '/api/zalo/submit-survey' => ['type' => 'controller', 'file' => 'api_zalo_submit_survey.php'],
    '/api/zalo/upload-survey-file' => ['type' => 'controller', 'file' => 'api_zalo_upload_survey_file.php'],
    
    '/api/zalo/hoat-dong' => ['type' => 'controller', 'file' => 'api_zalo_hoat_dong.php'],
    '/api/zalo/hoat-dong/dang-ky' => ['type' => 'controller', 'file' => 'api_zalo_hoat_dong_dang_ky.php'],
];

// --- Nhóm route Tác vụ ngầm (Cron Job) ---
$task_routes = [
    '/tasks/send-birthday-wishes' => ['type' => 'controller', 'file' => 'send_birthday_wishes.php'],


];


// ===================================================================
// BƯỚC 3: GỘP ROUTE VÀ XỬ LÝ YÊU CẦU
// ===================================================================

// Gộp tất cả các mảng route lại thành một
$routes = array_merge(
    $public_routes,
    $admin_routes,
    $ctv_routes,
    $api_routes,
    $task_routes
);

// --- CƠ CHẾ DYNAMIC ROUTING CHO /api/zalo/ ---
if (strpos($route, '/api/zalo/') === 0) {
    $action = substr($route, strlen('/api/zalo/'));
    // Thay thế dấu '-' thành '_' (ví dụ: get-nam-hoc -> get_nam_hoc)
    $script_name = str_replace('-', '_', $action);
    $file_path = __DIR__ . '/../src/controllers/api_zalo_' . $script_name . '.php';
    
    // Nếu file tồn tại thì include và kết thúc request luôn
    if (file_exists($file_path)) {
        require_once $file_path;
        exit;
    }
}

// Xử lý yêu cầu với logic tĩnh (nếu không bắt được ở dynamic routing)
if (array_key_exists($route, $routes)) {
    $route_info = $routes[$route]; // Lấy thông tin chi tiết của route
    $file_path = null;

    // Kiểm tra loại route và xây dựng đường dẫn tương ứng
    if ($route_info['type'] === 'view') {
        $file_path = __DIR__ . '/../src/views/' . $route_info['file'];
    } elseif ($route_info['type'] === 'controller') {
        $file_path = __DIR__ . '/../src/controllers/' . $route_info['file'];
    }

    // Tải file nếu đường dẫn hợp lệ
    if ($file_path && file_exists($file_path)) {
        file_put_contents(__DIR__ . '/../../request_log.txt', date('Y-m-d H:i:s') . ' - REQUIRING: ' . $file_path . "\n", FILE_APPEND);
        require_once $file_path;
    } else {
        http_response_code(500);
        file_put_contents(__DIR__ . '/../../request_log.txt', date('Y-m-d H:i:s') . ' - 500 ERROR: File not found ' . $file_path . "\n", FILE_APPEND);
        echo "Lỗi server: File cho route không tồn tại hoặc loại route không hợp lệ. Đường dẫn: " . htmlspecialchars($file_path);
    }
} else {
    http_response_code(404);
    file_put_contents(__DIR__ . '/../../request_log.txt', date('Y-m-d H:i:s') . ' - 404 ERROR for route ' . $route . "\n", FILE_APPEND);
    $global_404 = $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    if (file_exists($global_404)) {
        require $global_404;
    } else {
        echo "<h1>404 Not Found</h1>";
    }
    exit;
}