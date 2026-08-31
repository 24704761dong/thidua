<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Thẻ Học Sinh</title>
   <style>
    /* --- CÀI ĐẶT CHUNG --- */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #eee; /* Chỉ hiển thị trên màn hình */
    }

    /* --- VÙNG CHỨA CÁC THẺ --- */
    .card-container {
        /* Trải toàn bộ khổ giấy A4 */
        width: 210mm;
        height: 297mm;
        
        /* Dàn trang bằng Flexbox */
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-content: flex-start;
        
        /* Khoảng cách giữa các thẻ */
        gap: 10mm;

        /* Quan trọng: Dùng padding thay cho margin của trang in */
        padding: 10mm; /* Lề 1cm */
        box-sizing: border-box; /* Đảm bảo padding không làm tăng kích thước tổng */
        
        /* Chỉ hiển thị trên màn hình */
        margin: 1cm auto; 
        background-color: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }

    /* --- THIẾT KẾ THẺ HỌC SINH --- */
    .student-card {
        /* KÍCH THƯỚC CHUẨN */
        width: 85mm;
        height: 54mm;
        
        position: relative;
        background-image: url('<?php echo htmlspecialchars($mau_the['background']); ?>');
        background-size: 100% 100%;
        background-position: center;
        overflow: hidden;
        box-sizing: border-box;
        border: 1px dashed #ccc; /* Đường viền đứt để dễ cắt */

        /* Quan trọng: Tránh thẻ bị cắt đôi khi qua trang mới */
        page-break-inside: avoid;
    }
    
    .card-element {
        position: absolute;
        white-space: nowrap;
        box-sizing: border-box;
    }
    
    .element-image {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    
    /* --- NÚT IN --- */
    .print-button-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }
    .print-button {
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        background-color: #0d6efd;
        color: white;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    .print-button:hover {
        background-color: #0b5ed7;
    }

    /* --- CÀI ĐẶT KHI IN --- */
    @media print {
        @page {
            size: A4 portrait;
            /* Quan trọng: Xóa bỏ mọi lề mặc định */
            margin: 0 !important;
        }
        
        body {
            /* Buộc in màu nền và ảnh nền */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background-color: transparent !important; /* Xóa nền xám khi in */
        }

        /* Xóa bỏ các hiệu ứng hiển thị trên màn hình */
        .card-container {
            margin: 0;
            box-shadow: none;
            /* Đảm bảo mỗi trang A4 là một container */
            page-break-after: always;
        }

        /* Ẩn nút in khi in */
        .print-button-container {
            display: none !important;
        }
    }
</style>
    <link rel="stylesheet" href="/thidua/public/assets/css/fonts.css">
</head>
<body>
    <div class="print-button-container">
        <button onclick="window.print();" class="print-button">
            In Trang Này
        </button>
    </div>
    
    <?php if (!empty($cards_html)) { echo $cards_html; } else { ?>
        <div style="width:100%;height:100vh;display:flex;align-items:center;justify-content:center;color:#666;font-family:Arial, sans-serif;">
            <div style="text-align:center">
                <div style="font-size:18px;margin-bottom:8px;font-weight:bold;">Không có thẻ nào để hiển thị</div>
                <div style="font-size:14px">Có thể danh sách chọn rỗng hoặc phiên đăng nhập đã hết hạn. Vui lòng quay lại danh sách và thử in lại.</div>
            </div>
        </div>
    <?php } ?>

</body>
</html>
