<?php
// File: src/controllers/TuanHocController.php
if (function_exists('opcache_invalidate')) { opcache_invalidate(__FILE__, true); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Bảo vệ chung: Chỉ admin/user đăng nhập mới được thao tác
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    // Trả về lỗi JSON nếu là request fetch
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || strpos($_SERVER['REQUEST_URI'], '/api/') !== false || isset($_GET['action']) && strpos($_GET['action'], 'api_') === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
        exit();
    }
    header('Location: /thidua/tracuu');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/week_permissions.php';

$action = $_GET['action'] ?? 'manage';
$db = get_db_connection();
$current_nam_hoc = $_SESSION['current_nam_hoc_id'] ?? 1;

switch ($action) {
    case 'manage':
        // ==========================================
        // 1. GIAO DIỆN QUẢN LÝ TUẦN HỌC
        // ==========================================
        $page_title = 'Chọn Tuần - Nhập Vi Phạm';
        $page_icon = 'bi-journal-check';
        $base_url = '/thidua/admin/vi-pham?tuan_id=';
        require_once __DIR__ . '/chon_tuan_controller.php';
        break;

    case 'select_thidua':
        // ==========================================
        // 2. GIAO DIỆN CHỌN TUẦN - NHẬP ĐIỂM THI ĐUA
        // ==========================================
        $page_title = 'Chọn Tuần - Nhập Điểm Thi Đua';
        $page_icon = 'bi-award-fill';
        $base_url = '/thidua/nhap-diem-thi-dua?tuan_id=';
        require_once __DIR__ . '/chon_tuan_controller.php';
        break;

    case 'api_add_edit':
        // ==========================================
        // 3. THÊM / SỬA TUẦN HỌC
        // ==========================================
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit();
        }
        if (!can_current_user_manage_weeks()) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa tuần học.']);
            exit();
        }

        $tuan_id = $_POST['tuan_id'] ?? null;
        $ten_tuan = trim($_POST['ten_tuan'] ?? '');
        $hoc_ky = $_POST['hoc_ky'] ?? 1;
        $ngay_bat_dau = $_POST['ngay_bat_dau'] ?? '';
        $ngay_ket_thuc = $_POST['ngay_ket_thuc'] ?? '';

        if (empty($ten_tuan) || empty($ngay_bat_dau) || empty($ngay_ket_thuc) || empty($hoc_ky)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc.']);
            exit();
        }

        try {
            if (!empty($tuan_id)) {
                $stmt = $db->prepare("UPDATE raw_tuan_hoc SET ten_tuan = ?, hoc_ky = ?, ngay_bat_dau = ?, ngay_ket_thuc = ? WHERE id = ? AND nam_hoc_id = ?");
                $stmt->execute([$ten_tuan, $hoc_ky, $ngay_bat_dau, $ngay_ket_thuc, $tuan_id, $current_nam_hoc]);
                echo json_encode(['success' => true, 'message' => 'Đã cập nhật tuần học thành công!']);
            } else {
                $stmt = $db->prepare("INSERT INTO raw_tuan_hoc (ten_tuan, ngay_bat_dau, ngay_ket_thuc, hoc_ky, nam_hoc_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$ten_tuan, $ngay_bat_dau, $ngay_ket_thuc, $hoc_ky, $current_nam_hoc]);
                echo json_encode(['success' => true, 'message' => 'Thêm tuần học mới thành công!']);
            }
        } catch (PDOException $e) {
            error_log('Lỗi CSDL khi thao tác với tuần học: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Thao tác thất bại do lỗi hệ thống.']);
        }
        exit();

    case 'api_get_week':
        // ==========================================
        // 4. LẤY THÔNG TIN CHI TIẾT 1 TUẦN ĐỂ SỬA
        // ==========================================
        header('Content-Type: application/json');
        if (!can_current_user_manage_weeks()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
            exit();
        }
        $week_id = $_GET['id'] ?? 0;
        if (!$week_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID tuần.']);
            exit();
        }
        try {
            $stmt = $db->prepare("SELECT * FROM raw_tuan_hoc WHERE id = ? AND nam_hoc_id = ?");
            $stmt->execute([$week_id, $current_nam_hoc]);
            $week = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($week) {
                echo json_encode(['success' => true, 'week' => $week]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy tuần học.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ nội bộ.']);
        }
        exit();

    case 'api_delete_week':
        // ==========================================
        // 5. XÓA TUẦN HỌC VÀ DỮ LIỆU LIÊN QUAN
        // ==========================================
        header('Content-Type: application/json');
        if (!can_current_user_manage_weeks()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $week_id = $data['tuan_id'] ?? 0;
        if (!$week_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID tuần cần xóa.']);
            exit();
        }
        try {
            $stmt_check = $db->prepare("SELECT id FROM raw_tuan_hoc WHERE id = ? AND nam_hoc_id = ?");
            $stmt_check->execute([$week_id, $current_nam_hoc]);
            if (!$stmt_check->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Tuần học không tồn tại hoặc sai năm học.']);
                exit();
            }

            $db->beginTransaction();
            $stmt1 = $db->prepare("DELETE FROM thi_dua_tuan WHERE tuan_hoc_id = ?");
            $stmt1->execute([$week_id]);
            $stmt2 = $db->prepare("DELETE FROM diem_danh WHERE tuan_hoc_id = ?");
            $stmt2->execute([$week_id]);
            $stmt3 = $db->prepare("DELETE FROM vi_pham_hoc_sinh WHERE tuan_hoc_id = ?");
            $stmt3->execute([$week_id]);
            $stmt_final = $db->prepare("DELETE FROM raw_tuan_hoc WHERE id = ?");
            $stmt_final->execute([$week_id]);
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Đã xóa tuần học và dữ liệu liên quan.']);
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Xóa thất bại. Lỗi CSDL.']);
        }
        exit();

    case 'api_lock_week':
        // ==========================================
        // 6. KHÓA / MỞ KHÓA TUẦN
        // ==========================================
        header('Content-Type: application/json');
        if (!can_current_user_manage_weeks()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $week_id = $data['week_id'] ?? null;
        $action = $data['action'] ?? null; // 'lock' or 'unlock'
        $password = $data['password'] ?? null;

        if (!$week_id || !$action) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            exit();
        }

        try {
            $new_lock_status = 0;
            if ($action === 'unlock') {
                $stmt_pass = $db->prepare("SELECT setting_value FROM he_thong_cai_dat WHERE setting_key = 'week_lock_password' AND nam_hoc_id = ?");
                $stmt_pass->execute([$current_nam_hoc]);
                $correct_password = $stmt_pass->fetchColumn() ?: '1';
                if ($password !== $correct_password) {
                    echo json_encode(['success' => false, 'message' => 'Mật khẩu không chính xác.']);
                    exit();
                }
                $new_lock_status = 0;
            } elseif ($action === 'lock') {
                $new_lock_status = 1;
            } else {
                echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
                exit();
            }

            $stmt_update = $db->prepare("UPDATE raw_tuan_hoc SET is_locked = ? WHERE id = ? AND nam_hoc_id = ?");
            $stmt_update->execute([$new_lock_status, $week_id, $current_nam_hoc]);
            
            if ($stmt_update->rowCount() > 0) {
                echo json_encode(['success' => true, 'is_locked' => ($new_lock_status == 1)]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể khóa/mở khóa. Vui lòng kiểm tra lại.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        exit();

    case 'api_get_public_status':
        // ==========================================
        // 7. LẤY TRẠNG THÁI PUBLIC CỦA TẤT CẢ TUẦN
        // ==========================================
        header('Content-Type: application/json');
        try {
            $stmt = $db->prepare("SELECT id, ten_tuan, is_public FROM raw_tuan_hoc WHERE nam_hoc_id = ? ORDER BY ngay_bat_dau DESC");
            $stmt->execute([$current_nam_hoc]);
            $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($weeks);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database query failed']);
        }
        exit();

    case 'api_toggle_public_status':
        // ==========================================
        // 8. BẬT/TẮT PUBLIC CHO 1 TUẦN
        // ==========================================
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $tuan_id = $data['tuan_id'] ?? 0;
        $status = $data['status'] ?? 0;

        if (!$tuan_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing week ID.']);
            exit();
        }

        try {
            $stmt = $db->prepare("UPDATE raw_tuan_hoc SET is_public = ? WHERE id = ? AND nam_hoc_id = ?");
            $stmt->execute([$status, $tuan_id, $current_nam_hoc]);
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database update failed.']);
        }
        exit();

    case 'api_save_note':
        // ==========================================
        // 9. LƯU GHI CHÚ BÁO CÁO CỦA TUẦN
        // ==========================================
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("UPDATE tuan_hoc SET ghi_chu_bao_cao = ? WHERE id = ?");
        $stmt->execute([$data['ghi_chu'] ?? '', $data['tuan_id'] ?? 0]);
        echo json_encode(['success' => true]);
        exit();

    default:
        header('Location: /thidua/admin/tuan-hoc');
        exit();
}
