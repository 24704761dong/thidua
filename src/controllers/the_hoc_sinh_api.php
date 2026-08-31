<?php
// File: src/controllers/the_hoc_sinh_api.php
// Gom chung các API xử lý liên quan đến Thẻ học sinh và Ảnh thẻ

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_vai_tro'], ['admin', 'user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../lib/zalo_helpers.php';
$db = get_db_connection();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Quản lý mẫu thẻ (Create, Rename, Delete, Set Default)
if (strpos($uri, '/api/quan-ly-mau-the') !== false) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;
    $id = $data['id'] ?? null;
    $name = $data['name'] ?? null;

    if (!$action) {
        echo json_encode(['success' => false, 'message' => 'Hành động không xác định.']);
        exit();
    }

    try {
        if ($action === 'create' && !empty($name)) {
            // Đảm bảo bảng có AUTO_INCREMENT, nếu chưa có thì tự động ALTER TABLE
            try {
                $db->exec("ALTER TABLE mau_the_hoc_sinh MODIFY id INT AUTO_INCREMENT");
            } catch (Exception $e) {
                // Bỏ qua nếu đã có hoặc không thể alter do vướng quyền/id=0
            }

            // Nếu AUTO_INCREMENT vẫn không hoạt động (hoặc có sẵn id=0), tính ID tiếp theo tường minh
            $stmt_max = $db->query("SELECT MAX(id) FROM mau_the_hoc_sinh");
            $max_id = (int)$stmt_max->fetchColumn();
            $next_id = $max_id > 0 ? $max_id + 1 : 1;

            $default_config = '{"background":"/thidua/public/assets/phoi_the_mac_dinh.png","elements":[]}';
            
            try {
                $stmt = $db->prepare("INSERT INTO mau_the_hoc_sinh (ten_mau, cau_hinh_json, created_at) VALUES (?, ?, ?)");
                $stmt->execute([$name, $default_config, date('Y-m-d H:i:s')]);
                $new_id = $db->lastInsertId();
            } catch (Exception $e) {
                // Fallback chèn tường minh ID tiếp theo
                $stmt = $db->prepare("INSERT INTO mau_the_hoc_sinh (id, ten_mau, cau_hinh_json, created_at) VALUES (?, ?, ?, ?)");
                $stmt->execute([$next_id, $name, $default_config, date('Y-m-d H:i:s')]);
                $new_id = $next_id;
            }

            echo json_encode(['success' => true, 'new_id' => $new_id]);
        
        } elseif ($action === 'rename' && !empty($id) && !empty($name)) {
            $stmt = $db->prepare("UPDATE mau_the_hoc_sinh SET ten_mau = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            echo json_encode(['success' => true]);

        } elseif ($action === 'delete' && !empty($id)) {
            $stmt_check = $db->prepare("SELECT is_default FROM mau_the_hoc_sinh WHERE id = ?");
            $stmt_check->execute([$id]);
            if ($stmt_check->fetchColumn() == 1) throw new Exception("Không thể xóa mẫu đang được đặt làm mặc định.");
            
            $stmt = $db->prepare("DELETE FROM mau_the_hoc_sinh WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);

        } elseif ($action === 'duplicate' && !empty($id)) {
            $stmt = $db->prepare("SELECT ten_mau, cau_hinh_json FROM mau_the_hoc_sinh WHERE id = ?");
            $stmt->execute([$id]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$template) throw new Exception("Không tìm thấy mẫu thẻ để nhân bản.");
            
            $new_name = $template['ten_mau'] . ' (Bản sao)';
            
            // Lấy max ID để insert (tương tự logic create)
            $stmt_max = $db->query("SELECT MAX(id) FROM mau_the_hoc_sinh");
            $max_id = (int)$stmt_max->fetchColumn();
            $next_id = $max_id > 0 ? $max_id + 1 : 1;
            
            try {
                $stmt_insert = $db->prepare("INSERT INTO mau_the_hoc_sinh (ten_mau, cau_hinh_json, created_at) VALUES (?, ?, ?)");
                $stmt_insert->execute([$new_name, $template['cau_hinh_json'], date('Y-m-d H:i:s')]);
                $new_id = $db->lastInsertId();
            } catch (Exception $e) {
                // Fallback nếu AUTO_INCREMENT bị lỗi
                $stmt_insert = $db->prepare("INSERT INTO mau_the_hoc_sinh (id, ten_mau, cau_hinh_json, created_at) VALUES (?, ?, ?, ?)");
                $stmt_insert->execute([$next_id, $new_name, $template['cau_hinh_json'], date('Y-m-d H:i:s')]);
                $new_id = $next_id;
            }
            echo json_encode(['success' => true, 'new_id' => $new_id]);

        } elseif ($action === 'set_default' && !empty($id)) {
            $db->beginTransaction();
            $db->exec("UPDATE mau_the_hoc_sinh SET is_default = 0");
            $stmt = $db->prepare("UPDATE mau_the_hoc_sinh SET is_default = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $db->commit();
            echo json_encode(['success' => true]);

        } elseif ($action === 'set_zalo_default' && !empty($id)) {
            $db->beginTransaction();
            $db->exec("UPDATE mau_the_hoc_sinh SET is_zalo_default = 0");
            $stmt = $db->prepare("UPDATE mau_the_hoc_sinh SET is_zalo_default = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $db->commit();
            echo json_encode(['success' => true]);

        } elseif ($action === 'save_zalo_settings') {
            $allow_edit = $data['allow_edit'] ?? '0';
            $auto_approve = $data['auto_approve'] ?? '0';
            $editable_fields = isset($data['editable_fields']) && is_array($data['editable_fields']) ? json_encode($data['editable_fields']) : '[]';

            $settings_to_save = [
                'zalo_allow_edit_profile' => $allow_edit,
                'zalo_auto_approve_edit' => $auto_approve,
                'zalo_editable_fields' => $editable_fields
            ];

            foreach ($settings_to_save as $key => $val) {
                $check = $db->prepare("SELECT id FROM settings WHERE setting_key = ? LIMIT 1");
                $check->execute([$key]);
                if ($check->fetch()) {
                    $stmt = $db->prepare("UPDATE settings SET setting_value = ?, group_name = 'zalo', updated_at = NOW() WHERE setting_key = ?");
                    $stmt->execute([$val, $key]);
                } else {
                    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, group_name, updated_at) VALUES (?, ?, 'zalo', NOW())");
                    $stmt->execute([$key, $val]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Đã lưu cấu hình thành công.']);

        } elseif ($action === 'handle_zalo_edit_request') {
            $status = $data['status'] ?? ''; // 'approve' or 'reject'
            if (!in_array($status, ['approve', 'reject'])) throw new Exception("Trạng thái không hợp lệ.");
            
            $stmt = $db->prepare("SELECT * FROM yeu_cau_chinh_sua_zalo WHERE id = ?");
            $stmt->execute([$id]);
            $req = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$req || $req['trang_thai'] !== 'cho_duyet') throw new Exception("Yêu cầu không tồn tại hoặc đã được xử lý.");

            $db->beginTransaction();
            if ($status === 'approve') {
                $new_info = json_decode($req['thong_tin_moi'], true);
                if (is_array($new_info) && !empty($new_info)) {
                    $set_clauses = [];
                    $values = [];
                    $has_chuc_vu = false;
                    $chuc_vu_val = '';
                    $allowed_keys = ['anh_the', 'chuc_vu', 'sdt', 'email', 'tinh_thanhpho', 'xa_phuong', 'ap_khupho', 'dia_chi_chi_tiet'];
                    foreach ($new_info as $key => $val) {
                        if (in_array($key, $allowed_keys)) {
                            if ($key === 'chuc_vu') {
                                $has_chuc_vu = true;
                                $chuc_vu_val = $val;
                            } else {
                                $set_clauses[] = "$key = ?";
                                $values[] = $val;
                            }
                        }
                    }
                    if (!empty($set_clauses)) {
                        $values[] = $req['hoc_sinh_id']; // ho_so_hoc_sinh.id
                        $update_sql = "UPDATE ho_so_hoc_sinh SET " . implode(", ", $set_clauses) . " WHERE id = ?";
                        $stmt_update = $db->prepare($update_sql);
                        $stmt_update->execute($values);
                    }
                    if ($has_chuc_vu) {
                        $stmt_ma = $db->prepare("SELECT ma_hoc_sinh FROM ho_so_hoc_sinh WHERE id = ?");
                        $stmt_ma->execute([$req['hoc_sinh_id']]);
                        $ma_hs = $stmt_ma->fetchColumn();
                        if ($ma_hs) {
                            $stmt_qt = $db->prepare("UPDATE quatrinh_hoc_tap SET chuc_vu = ? WHERE ma_hoc_sinh = ? AND nam_hoc_id = get_current_nam_hoc_id_mysql()");
                            $stmt_qt->execute([$chuc_vu_val, $ma_hs]);
                        }
                    }
                }
                $stmt = $db->prepare("UPDATE yeu_cau_chinh_sua_zalo SET trang_thai = 'da_duyet' WHERE id = ?");
                $stmt->execute([$id]);

                // Gửi thông báo học sinh
                $msg = "Yêu cầu cập nhật thông tin hồ sơ của bạn đã được phê duyệt.";
                create_student_notification($db, $req['hoc_sinh_id'], 'Yêu cầu được duyệt', $msg, 'duyet_ho_so');

                $stmt_zalo = $db->prepare("SELECT zalo_id FROM ho_so_hoc_sinh WHERE id = ?");
                $stmt_zalo->execute([$req['hoc_sinh_id']]);
                $zalo_id = $stmt_zalo->fetchColumn();
                if (!empty($zalo_id)) {
                    send_zalo_push_notification($zalo_id, $msg, 'Thông báo hồ sơ');
                }

            } else {
                $stmt = $db->prepare("UPDATE yeu_cau_chinh_sua_zalo SET trang_thai = 'tu_choi' WHERE id = ?");
                $stmt->execute([$id]);

                // Gửi thông báo học sinh
                $msg = "Yêu cầu cập nhật thông tin hồ sơ của bạn đã bị từ chối.";
                create_student_notification($db, $req['hoc_sinh_id'], 'Yêu cầu bị từ chối', $msg, 'tu_choi_ho_so');

                $stmt_zalo = $db->prepare("SELECT zalo_id FROM ho_so_hoc_sinh WHERE id = ?");
                $stmt_zalo->execute([$req['hoc_sinh_id']]);
                $zalo_id = $stmt_zalo->fetchColumn();
                if (!empty($zalo_id)) {
                    send_zalo_push_notification($zalo_id, $msg, 'Thông báo hồ sơ');
                }
            }
            $db->commit();
            echo json_encode(['success' => true]);

        } else {
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ hoặc thiếu tham số.']);
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

// 2. Lưu cấu hình thiết kế mẫu thẻ
} elseif (strpos($uri, '/api/luu-mau-the') !== false) {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
        exit();
    }
    $request_data = json_decode(file_get_contents('php://input'), true);
    if (!isset($request_data['id']) || !isset($request_data['template'])) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu gửi lên không hợp lệ.']);
        exit();
    }
    
    try {
        $stmt = $db->prepare('UPDATE mau_the_hoc_sinh SET cau_hinh_json = :cau_hinh_json WHERE id = :id');
        $stmt->bindValue(':cau_hinh_json', json_encode($request_data['template']), PDO::PARAM_STR);
        $stmt->bindValue(':id', $request_data['id'], PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Lưu mẫu thẻ thành công!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
    }

// 3. Upload ảnh thẻ (Form POST)
} elseif (strpos($uri, '/api/upload-anh-the') !== false) {
    $upload_dir = __DIR__ . '/../../public/assets/anh_the/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $success_count = 0; $error_count = 0; $errors = [];
    if (isset($_FILES['anh_the_files'])) {
        $total_files = count($_FILES['anh_the_files']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['anh_the_files']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['anh_the_files']['tmp_name'][$i];
                $original_name = $_FILES['anh_the_files']['name'][$i];
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $errors[] = "File '$original_name': Định dạng không hợp lệ.";
                    $error_count++; continue;
                }
                $destination = $upload_dir . basename($original_name);
                if (file_exists($destination)) {
                    $errors[] = "File '$original_name' đã tồn tại.";
                    $error_count++; continue;
                }
                if (move_uploaded_file($tmp_name, $destination)) {
                    $success_count++;
                } else {
                    $errors[] = "File '$original_name': Lỗi khi di chuyển file.";
                    $error_count++;
                }
            }
        }
    }
    $message = "Xử lý hoàn tất! Đã tải $success_count file. Lỗi: $error_count file.";
    if (!empty($errors)) $_SESSION['error_message'] = $message . "<br>" . implode("<br>", $errors);
    else $_SESSION['success_message'] = $message;
    header('Location: /thidua/admin/quan-ly-anh-the?iframe=1');
    exit();

// 4. Gán ảnh thẻ (Assign)
} elseif (strpos($uri, '/api/assign-anh-the') !== false) {
    header('Content-Type: application/json');
    try {
        $payload = json_decode(file_get_contents('php://input'), true);
        $studentId = (int)($payload['student_id'] ?? 0);
        $filename = basename(trim($payload['filename'] ?? ''));

        if ($studentId <= 0 || $filename === '') throw new Exception('Thiếu thông tin.');
        if (!is_file(__DIR__ . '/../../public/assets/anh_the/' . $filename)) throw new Exception('File ảnh không tồn tại.');

        $stmt = $db->prepare('SELECT id FROM ho_so_hoc_sinh WHERE id = ? LIMIT 1');
        $stmt->execute([$studentId]);
        if (!$stmt->fetch()) throw new Exception('Không tìm thấy học sinh.');

        $stmt = $db->prepare('SELECT id FROM ho_so_hoc_sinh WHERE anh_the = ? LIMIT 1');
        $stmt->execute([$filename]);
        $existing = $stmt->fetch();
        if ($existing && (int)$existing['id'] !== $studentId) throw new Exception('Ảnh này đã gán cho học sinh khác.');

        $stmt = $db->prepare('UPDATE ho_so_hoc_sinh SET anh_the = ? WHERE id = ?');
        $stmt->execute([$filename, $studentId]);
        echo json_encode(['success' => true, 'message' => 'Gán ảnh thành công.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

// 5. Xóa ảnh thẻ
} elseif (strpos($uri, '/api/delete-anh-the') !== false) {
    header('Content-Type: application/json');
    try {
        $filename = trim(json_decode(file_get_contents("php://input"), true)['filename'] ?? '');
        if (empty($filename)) throw new Exception('Thiếu tên file.');
        
        $stmt = $db->prepare("SELECT id FROM ho_so_hoc_sinh WHERE anh_the = ? LIMIT 1");
        $stmt->execute([$filename]);
        if ($student = $stmt->fetch()) {
            $db->prepare("UPDATE ho_so_hoc_sinh SET anh_the = NULL WHERE id = ?")->execute([$student['id']]);
        }
        $path = __DIR__ . '/../../public/assets/anh_the/' . basename($filename);
        if (file_exists($path)) unlink($path);
        echo json_encode(['success' => true, 'message' => 'Đã xóa ảnh thành công.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

// 6. Đổi tên ảnh (Rename)
} elseif (strpos($uri, '/api/rename-anh-the') !== false) {
    header('Content-Type: application/json');
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $old = basename($data['old_filename']);
        $new = basename($data['new_filename']);
        if (!$old || !$new) throw new Exception("Thiếu tên file.");
        
        $dir = __DIR__ . '/../../public/assets/anh_the/';
        if (!file_exists($dir . $old)) throw new Exception("File cũ không tồn tại.");
        if (file_exists($dir . $new) && strtolower($old) !== strtolower($new)) throw new Exception("Tên mới đã tồn tại.");
        if (!rename($dir . $old, $dir . $new)) throw new Exception("Không thể đổi tên.");
        
        $db->prepare("UPDATE ho_so_hoc_sinh SET anh_the = ? WHERE anh_the = ?")->execute([$new, $old]);
        echo json_encode(['success' => true, 'message' => "Đổi tên thành công!"]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

// 7. Tự động đổi tên ảnh (Auto-rename)
} elseif (strpos($uri, '/api/auto-rename-anh-the') !== false) {
    header('Content-Type: application/json');
    try {
        $image_dir = __DIR__ . '/../../public/assets/anh_the/';
        $rows = $db->query("
            SELECT hs.id, hs.ma_hoc_sinh, hs.ho_dem, hs.ten, hs.anh_the, hs.nien_khoa,
                   (SELECT lh.ten_lop FROM quatrinh_hoc_tap qt JOIN raw_lop_hoc lh ON qt.lop_hoc_id = lh.id WHERE qt.ma_hoc_sinh = hs.ma_hoc_sinh ORDER BY qt.nam_hoc_id DESC LIMIT 1) as ten_lop
            FROM ho_so_hoc_sinh hs WHERE hs.anh_the IS NOT NULL AND hs.anh_the != ''
        ")->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        foreach ($rows as $r) {
            $old = $r['anh_the'];
            $ext = pathinfo($old, PATHINFO_EXTENSION);
            
            $nk = !empty($r['nien_khoa']) ? preg_replace('/[^A-Za-z0-9\-]/', '', $r['nien_khoa']) : 'NK';
            $cccd = trim($r['ma_hoc_sinh']);
            $ho_ten = preg_replace('/\s+/', '_', trim($r['ho_dem'] . ' ' . $r['ten']));
            $new = $nk . '_' . $cccd . '_' . $ho_ten . '.' . strtolower($ext);
            
            if (!file_exists($image_dir . $old)) continue;
            if (file_exists($image_dir . $new) && strtolower($old) !== strtolower($new)) {
                $new = pathinfo($new, PATHINFO_FILENAME) . '-' . uniqid() . '.' . strtolower($ext);
            }
            if (rename($image_dir . $old, $image_dir . $new)) {
                $db->prepare("UPDATE ho_so_hoc_sinh SET anh_the = ? WHERE id = ?")->execute([$new, $r['id']]);
                $count++;
            }
        }
        echo json_encode(['success' => true, 'message' => "Đã đổi tên tự động $count ảnh theo cấu trúc Niên khóa_Số CCCD_Họ và tên."]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

// 8. Upload phôi thẻ (Background)
} elseif (strpos($uri, '/api/upload-phoi-the') !== false) {
    header('Content-Type: application/json');
    try {
        $upload_dir = __DIR__ . '/../../public/assets/uploads/phoi_the/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        if (!isset($_FILES['bg_file']) || $_FILES['bg_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Không có file được tải lên hoặc có lỗi.");
        }
        
        $tmp_name = $_FILES['bg_file']['tmp_name'];
        $original_name = $_FILES['bg_file']['name'];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            throw new Exception("Chỉ hỗ trợ file jpg, jpeg, png.");
        }
        
        $new_name = 'phoi_the_' . time() . '_' . uniqid() . '.' . $extension;
        $destination = $upload_dir . $new_name;
        
        if (move_uploaded_file($tmp_name, $destination)) {
            $file_url = '/thidua/public/assets/uploads/phoi_the/' . $new_name;
            echo json_encode(['success' => true, 'file_url' => $file_url, 'message' => 'Upload thành công.']);
        } else {
            throw new Exception("Lỗi khi di chuyển file.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

// Xử lý không tìm thấy API
} else {
    http_response_code(404);
    echo "API Endpoint not found.";
}
