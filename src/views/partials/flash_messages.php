<?php
// Kiểm tra xem có thông báo trong session không
if (isset($_SESSION['flash_message'])) {
    // Lấy thông báo ra
    $flash_message = $_SESSION['flash_message'];

    // Mặc định là loại 'success' (thành công)
    $message_type = 'success';
    $message_text = $flash_message;

    if (is_array($flash_message)) {
        $message_type = $flash_message['type'];
        $message_text = $flash_message['message'];
    }

    if ($message_type === 'danger') $message_type = 'error';

    unset($_SESSION['flash_message']);
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof showToast === 'function') {
                showToast('" . addslashes($message_type) . "', '" . addslashes($message_text) . "');
            } else {
                setTimeout(() => alert('" . addslashes($message_text) . "'), 500);
            }
        });
    </script>";
}
?>