<?php
// File: config/database.php (Đã nâng cấp lên MySQL/MariaDB)
require_once __DIR__ . '/bootstrap.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
/**
 * Hàm này tạo và trả về một đối tượng kết nối CSDL (PDO) đến máy chủ MySQL.
 * Nó sử dụng một biến static để đảm bảo chỉ có một kết nối duy nhất
 * được tạo ra trong mỗi lần request.
 *
 * @return PDO Đối tượng kết nối PDO.
 */
function get_db_connection()
{
    // Biến static $db sẽ chỉ được khởi tạo một lần duy nhất.
    static $db = null;

    // Nếu chưa có kết nối, hãy tạo nó.
    if ($db === null) {
        
        // Đọc cấu hình từ .env (đã được nạp bởi bootstrap.php)
        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_DATABASE'] ?? 'data';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $charset = 'utf8mb4'; // Chuẩn charset hiện đại

        // Tạo chuỗi DSN (Data Source Name)
        $dsn = "{$driver}:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $initCommand = "SET NAMES {$charset} COLLATE utf8mb4_vietnamese_ci";
        if (class_exists(\Pdo\Mysql::class)) {
            $options[\Pdo\Mysql::ATTR_INIT_COMMAND] = $initCommand;
        } else {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = $initCommand;
        }

        try {
            // Tạo đối tượng PDO mới
            $db = new PDO($dsn, $username, $password, $options);
            
            // Lấy Header X-Nam-Hoc-Id (dành cho API Stateless như Zalo Mini App)
            $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
            $headers_lower = array_change_key_case($headers, CASE_LOWER);
            
            if (isset($headers_lower['x-nam-hoc-id']) && is_numeric($headers_lower['x-nam-hoc-id'])) {
                $current_nh = (int)$headers_lower['x-nam-hoc-id'];
            } else {
                // Gán biến session của MySQL để các VIEW có thể sử dụng lọc dữ liệu theo năm học
                $current_nh = $_SESSION['current_nam_hoc_id'] ?? 1;
            }

            $db->exec("SET @current_nam_hoc_id = $current_nh");

        } catch (PDOException $e) {
            // Nếu kết nối thất bại, dừng ứng dụng và báo lỗi
            die("Lỗi kết nối CSDL CHÍNH (thidua_main): " . $e->getMessage());
        }
    }

    // Trả về đối tượng kết nối đã có hoặc vừa được tạo
    return $db;
}