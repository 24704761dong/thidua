<?php
// File: src/views/bao_cao_vi_pham_print.php
$ten_tuan = $tuan_hoc['ten_tuan'] ?? '';
$range = '';
if (!empty($tuan_hoc['ngay_bat_dau']) && !empty($tuan_hoc['ngay_ket_thuc'])) {
  $range = ' (Từ ngày '.date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau'])).
           ' đến ngày '.date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc'])).')';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<title>Báo Cáo Vi Phạm - <?= htmlspecialchars($ten_tuan) ?></title>
<meta name="viewport" content="width=850" />
<style>
  @page { size: A4 portrait; margin: 1.3cm; }
  @media print { thead{display:w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-header-group;} }

  body {
    font-family:"Times New Roman", Times, serif;
    font-size:11pt;
    color:#000;
  }

  .header { 
    display:flex; 
    justify-content:space-between; 
    align-items:flex-start;
  }
  .header-left {
    text-transform:uppercase;
    line-height:1.3;
    text-align:center;     /* căn giữa 2 dòng */
    width:7cm;             /* chỉnh tay độ rộng khối này */
  }
  .header-left .line-1 { font-size:11pt; font-weight:400; }
  .header-left .line-2 { font-size:11pt; font-weight:700; }
  .header-right { text-align:center; d-flex:1; }

  .title { text-align:center; margin-top:10px; text-transform:uppercase; line-height:1.4; }
  .title .line-1 { font-weight:700; font-size:13pt; }
  .title .line-2 { font-weight:700; font-size:12pt; }

  w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-layout:fixed;
  }
  th, td {
    border:1px solid #000;
    padding:6px 5px;
    vertical-align:middle;
    text-align:center;
    white-space:nowrap;
    overflow:hidden;
  }
  th { font-weight:700; font-size:9pt; }
  td.text-start { text-align:left; }
  td.note > div {
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .col-stt{width:4%;}
  .col-name{width:22%;}
  .col-class{width:5%;}
  .col-date{width:9%;}
  .col-vp{width:54%;}
  .col-note{width:6%;}

  /* Cỡ chữ theo yêu cầu */
  tbody td.col-stt,
  tbody td.col-name,
  tbody td.col-class { font-size:8.5pt; }
  tbody td.col-date { font-size:8pt; }
  tbody td.col-vp { font-size:7pt; }
  tbody td.col-note { font-size:7pt; }

  /* Footer ký tên: căn phải */
  .footer { margin-top:20px; display:flex; justify-content:d-flex-end; page-break-inside:avoid; }
  .footer-sign { text-align:center; }
</style>
</head>
<body onload="window.print();">

  <div class="header">
    <div class="header-left">
      <div class="line-1">TRƯỜNG THPT BÌNH SƠN</div>
      <div class="line-2">Hệ thống Đánh Giá Thi Đua</div>
    </div>
    <div class="header-right"></div>
  </div>

  <div class="title">
    <div class="line-1">BẢNG DANH SÁCH HỌC SINH VI PHẠM NỘI QUY NHÀ TRƯỜNG</div>
    <div class="line-2"> <?= htmlspecialchars(mb_strtoupper($ten_tuan,'UTF-8')).htmlspecialchars($range) ?></div>
  </div>

  <table>
    <thead>
      <tr>
        <th class="col-stt">TT</th>
        <th class="col-name">Họ và tên</th>
        <th class="col-class">Lớp</th>
        <th class="col-date">Ngày VP</th>
        <th class="col-vp">Danh mục vi phạm</th>
        <th class="col-note">Ghi chú</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($danh_sach_vi_pham)): ?>
        <tr><td colspan="6">Không có vi phạm trong tuần này.</td></tr>
      <?php else: foreach ($danh_sach_vi_pham as $i => $vp): ?>
        <tr>
          <td class="col-stt"><?= $i+1 ?></td>
          <td class="col-name text-left"><?= htmlspecialchars($vp['ho_ten'] ?? '') ?></td>
          <td class="col-class"><?= htmlspecialchars($vp['ten_lop'] ?? '') ?></td>
          <td class="col-date">
            <?php $ts = isset($vp['ngay_vi_pham'])?strtotime($vp['ngay_vi_pham']):0; echo $ts?date('d/m/Y',$ts):''; ?>
          </td>
          <td class="col-vp text-left"><?= htmlspecialchars($vp['ten_vi_pham'] ?? '') ?></td>
          <td class="col-note"><div><?= htmlspecialchars($vp['ghi_chu'] ?? '') ?></div></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <div class="footer">
    <div class="footer-sign">
      <div>Đồng Nai, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?></div>
      <div><strong>NGƯỜI LẬP BẢNG</strong></div>
      <br><br><br>
      <div><strong><?= htmlspecialchars($admin_name ?? '') ?></strong></div>
    </div>
  </div>

</body>
</html>
