<?php
// Trang xem nhật ký đăng nhập và chẩn đoán Session trực tiếp trên trình duyệt
header('Content-Type: text/html; charset=utf-8');

$log_file = __DIR__ . '/../logs/login_debug.log';
$action = $_GET['action'] ?? 'view';

if ($action === 'clear') {
    @file_put_contents($log_file, "");
    header('Location: view_login_log.php');
    exit();
}

$logs = file_exists($log_file) ? file_get_contents($log_file) : "Chưa có dữ liệu log.";

// Thông tin session hiện tại của người đang xem
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_session = [
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
    'server_time' => date('Y-m-d H:i:s')
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chẩn Đoán Đăng Nhập & Session</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; background: #0f172a; color: #e2e8f0; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        h1 { color: #38bdf8; font-size: 20px; margin-bottom: 8px; }
        .box { background: #1e293b; border-radius: 8px; padding: 16px; margin-bottom: 20px; border: 1px solid #334155; }
        pre { margin: 0; white-space: pre-wrap; word-break: break-all; font-size: 13px; line-height: 1.5; color: #a5f3fc; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold; margin-right: 10px; cursor: pointer; }
        .btn-red { background: #dc2626; }
        .btn-green { background: #16a34a; }
        .tag { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .tag-ok { background: #15803d; color: #dcfce7; }
        .tag-err { background: #b91c1c; color: #fee2e2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Chẩn Đoán Quá Trình Đăng Nhập (Login & Session Logs)</h1>
        <div style="margin-bottom: 15px;">
            <a href="view_login_log.php" class="btn btn-green">🔄 Tải lại trang</a>
            <a href="view_login_log.php?action=clear" class="btn btn-red" onclick="return confirm('Bạn có chắc muốn xóa lịch sử log?')">🗑️ Xóa sạch log</a>
            <a href="/thidua/tracuu" class="btn">🏠 Về trang tra cứu</a>
        </div>

        <div class="box">
            <h3 style="margin-top:0; color:#38bdf8; font-size:15px;">📌 Trạng thái Session hiện tại của trình duyệt này:</h3>
            <pre><?php echo htmlspecialchars(json_encode($current_session, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
        </div>

        <div class="box">
            <h3 style="margin-top:0; color:#facc15; font-size:15px;">📋 Chi tiết các bước Đăng Nhập & Chuyển Trang (Mới nhất ở dưới):</h3>
            <pre><?php echo htmlspecialchars($logs); ?></pre>
        </div>
    </div>
</body>
</html>
