<?php
// File: config/security.php
// TUYỆT ĐỐI KHÔNG CHIA SẺ FILE NÀY HOẶC ĐƯA LÊN REPOSITORY CÔNG KHAI
// Encryption key được đọc từ biến môi trường ENCRYPTION_KEY
// Fallback về giá trị mặc định nếu chưa cấu hình
define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY'] ?? 'hSgYp_qRz-9!w#z%C&F)J@NcRfUjXn2r');