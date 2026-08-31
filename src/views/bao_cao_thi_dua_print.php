<?php
// File: src/views/bao_cao_thi_dua_print.php
function print_if_not_zero($value, $decimals = 1) {
    $numeric_value = round((float)$value, $decimals);
    if ($numeric_value != 0) {
        if (floor($numeric_value) == $numeric_value) {
            echo (int)$numeric_value;
        } else {
            echo number_format($numeric_value, $decimals);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo Cáo Thi Đua - <?php echo htmlspecialchars($tuan_hoc['ten_tuan']); ?></title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.3in; /* Tell browser to use 0.3in margin */
            }
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 0 0.1in; /* Add a bit of padding to push it inwards just in case */
                box-sizing: border-box;
            }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
        }
        .print-meta { font-size: 8pt; color: #555; font-style: italic; margin-bottom: 4px; }
        .school-name { font-size: 10pt; font-weight: bold; line-height: 1.2; text-align: center; }
        .school-name p:first-child { font-weight: normal; }
        .school-name p { margin: 0; }
        .title-section { text-align: center; margin-top: 12px; margin-bottom: 12px; }
        .title-section h1 { font-size: 13.5pt; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase;}
        .title-section p { margin: 0; font-style: italic; font-size: 10pt; }
        
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; border: 2px solid #000; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 2px 1px; text-align: center; vertical-align: middle; word-wrap: break-word; }
        table.data-table th { font-weight: bold; }
        
        .bg-khoi-11 { background-color: #e2efda !important; }
        .lop-col { font-weight: bold; }
        .kxtd { background-color: #d9d9d9 !important; }
        
        /* Thick borders between blocks */
        .thick-top { border-top: 2px solid #000 !important; }
        
        .footer-notes { font-size: 10pt; font-style: italic; }
        .footer-signature { text-align: center; font-size: 10pt; }
        .footer-signature p { margin-top: 2px; margin-bottom: 2px; }
        .signature-image { display: block; margin: 5px auto; width: 140px; height: auto; }
        .signer-name { font-weight: bold; margin-top: 10px; }
        .print-url { margin-top: 15px; text-align: left; font-size: 8pt; color: #555; }
        
        table.layout-table { width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0; }
        table.layout-table tr, table.layout-table td { border: none; padding: 0; }
    </style>
</head>
<body onload="window.print();">

<?php $is_pdf = isset($is_pdf_export) && $is_pdf_export; ?>
<table class="layout-table">
    <tr>
        <td style="width: 50%; text-align: left; vertical-align: top;">
            <p class="print-meta">In lúc: <?php echo date('H:i:s d/m/Y'); ?></p>
            <?php if ($is_pdf): ?>
            <table style="width: 230px; border: none; border-collapse: collapse; margin: 0; padding: 0;">
                <tr style="border: none;">
                    <td style="border: none; text-align: center; padding: 0;">
                        <span style="font-size: 10pt; font-weight: bold;">TRƯỜNG THPT BÌNH SƠN</span><br>
                        <span style="font-size: 10pt; font-weight: bold;">HỆ THỐNG ĐÁNH GIÁ THI ĐUA</span>
                    </td>
                </tr>
            </table>
            <?php else: ?>
            <div class="school-name" style="text-align: left;">
                <p>TRƯỜNG THPT BÌNH SƠN</p>
                <p>HỆ THỐNG ĐÁNH GIÁ THI ĐUA</p>
            </div>
            <?php endif; ?>
        </td>
        <td style="width: 50%; text-align: right; vertical-align: top;">
            <?php if (isset($qr_code_base64) && $qr_code_base64): ?>
                <img src="<?php echo $qr_code_base64; ?>" alt="QR Code" style="width:80px; height:80px;">
            <?php endif; ?>
        </td>
    </tr>
</table>

<div class="title-section">
    <h1>BẢNG THỐNG KÊ ĐIỂM THI ĐUA HÀNG TUẦN NĂM HỌC <?php echo mb_strtoupper(htmlspecialchars($ten_nam_hoc), 'UTF-8'); ?></h1>
    <p><?php echo htmlspecialchars($tuan_hoc['ten_tuan']); ?> (Từ <?php echo date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau'])); ?> đến <?php echo date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc'])); ?>)</p>
</div>
    
<table class="data-table">
    <thead>
        <tr>
            <th rowspan="2" style="width: 10%;">LỚP</th>
            <th rowspan="2" style="width: 9%;">TIẾT<br>TỐT</th>
            <th rowspan="2" style="width: 10%;">TIẾT<br>TRUNG<br>BÌNH</th>
            <th rowspan="2" style="width: 9%;">ĐIỂM</th>
            <th colspan="2" style="width: 16%;">VẮNG</th>
            <th rowspan="2" style="width: 12%;">ĐIỂM<br>(+; -)<br>KHÁC</th>
            <th rowspan="2" style="width: 14%;">NỘI QUY<br>CHUYÊN CẦN</th>
            <th rowspan="2" style="width: 11%;">TỔNG<br>ĐIỂM</th>
            <th rowspan="2" style="width: 9%;">XẾP<br>HẠNG</th>
        </tr>
        <tr>
            <th style="width: 8%;">KP</th>
            <th style="width: 8%;">P</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $last_khoi = '';
            foreach ($report_data as $index => $data): 
                $current_khoi = substr($data['lop'], 0, 2);
                
                $row_class = '';
                if ($current_khoi == '11') {
                    $row_class .= ' bg-khoi-11';
                }
                if ($data['kxtd']) {
                    $row_class .= ' kxtd';
                }
                
                $tr_style_class = trim($row_class);
                
                $thick_border_class = '';
                if ($last_khoi != '' && $current_khoi != $last_khoi) {
                    $thick_border_class = ' thick-top';
                }
                $last_khoi = $current_khoi;
        ?>
            <tr class="<?php echo $tr_style_class . $thick_border_class; ?>">
                <td class="lop-col"><?php echo htmlspecialchars($data['lop']); ?></td>
                <td><?php print_if_not_zero($data['diem_tiet_tot']); ?></td>
                <td><?php print_if_not_zero($data['diem_tiet_tb']); ?></td>
                <td><?php print_if_not_zero($data['diem_sdb_nk']); ?></td>
                <td><?php print_if_not_zero($data['vang_kp'] ?? 0); ?></td>
                <td><?php print_if_not_zero($data['vang_p'] ?? 0); ?></td>
                <td><?php print_if_not_zero($data['diem_cong_tru']); ?></td>
                <td><?php print_if_not_zero(($data['diem_noi_quy'] ?? 0) + ($data['tru_vang'] ?? 0)); ?></td>
                <td><strong><?php echo round($data['tong_diem'] ?? 0, 1); ?></strong></td>
                <td style="font-weight: bold;"><?php if ($data['kxtd']) echo 'KXTĐ'; else echo $data['xep_hang'] ?? ''; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    
<table class="layout-table" style="margin-top: 15px; page-break-inside: avoid;">
    <tr>
        <td style="width: 60%; text-align: left; vertical-align: top; padding-right: 10px;">
            <div class="footer-notes">
                <strong>Ghi chú:</strong><br>
                <?php echo nl2br(htmlspecialchars($ghi_chu_bao_cao)); ?>
            </div>
        </td>
        <td style="width: 40%; text-align: center; vertical-align: top;">
            <div class="footer-signature">
                <p>Đồng Nai, ngày <?php echo date('d'); ?> tháng <?php echo date('m'); ?> năm <?php echo date('Y'); ?></p>
                <p><strong>NGƯỜI LẬP BẢNG</strong></p>
                <br>
                <img src="/thidua/public/assets/img/22logoapp.png" alt="Chữ ký" class="signature-image">
                <br>
                <p class="signer-name">BAN THI ĐUA</p>
            </div>
        </td>
    </tr>
</table>

<div class="print-url">
    <span><?php echo "https://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? ''); ?></span>
</div>

</body>
</html>
