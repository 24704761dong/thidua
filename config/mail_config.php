<?php
// File: config/mail_config.php
// Đọc cấu hình mail từ các biến môi trường (.env)

return [
    'HOST' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
    'PORT' => $_ENV['MAIL_PORT'] ?? 587,
    'USE_TLS' => true,
    'USERNAME' => $_ENV['MAIL_USERNAME'] ?? '',
    'PASSWORD' => $_ENV['MAIL_PASSWORD'] ?? '',
    'SENDER_EMAIL' => $_ENV['MAIL_USERNAME'] ?? '', // Thường thì email gửi cũng là username
    'SENDER_NAME' => $_ENV['MAIL_SENDER_NAME'] ?? 'Hệ thống Đánh Giá Thi Đua'
];