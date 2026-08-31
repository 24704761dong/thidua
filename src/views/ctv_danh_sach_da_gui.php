<?php
// === BU?C 1: TOÀN B? LOGIC PHP ÐUA LÊN Ð?U ===
$page_title = 'B?ng Tin Vi Ph?m Toàn Tu?n';

// (Gi? s? $tuan_hoc và $danh_sach_tong_hop dã du?c n?p b?i m?t file controller g?i file này)
// N?u không, b?n c?n require logic l?y d? li?u ? dây.

// L?y d? li?u l?c
$q = trim(mb_strtolower($_GET['q'] ?? '', 'UTF-8'));
$st = $_GET['st'] ?? 'all';
$lop = $_GET['lop'] ?? 'all';

// L?c d? li?u
$data_filtered = array_values(array_filter(($danh_sach_tong_hop ?? []), function($it) use($q,$st,$lop){
    $map_status_raw = ['Ðã duy?t' => 'duyet', 'Ch? duy?t' => 'cho', 'B? lo?i b?' => 'loai'];
    $trang_thai_raw = $map_status_raw[$it['trang_thai'] ?? ''] ?? '';

    if($q !== '' && mb_strpos(mb_strtolower(($it['ho_ten']??'').' '.($it['ten_lop']??'').' '.($it['ten_vi_pham']??''), 'UTF-8'), $q) === false) return false;
    if($st !=='all' && $trang_thai_raw !== $st) return false;
    if($lop !=='all' && ($it['ten_lop']??'') !== $lop) return false;
    return true;
}));

// Hàm render các hàng c?a b?ng (<tbody>)
function render_table_rows($data, $danh_sach_tong_hop_raw) {
    ob_start(); // B?t d?u b? d?m
    
    foreach($data as $index => $item):
      $trang_thai = $item['trang_thai'] ?? '';
      $badge_class = 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle';
      if ($trang_thai === 'Ðã duy?t') $badge_class = 'bg-success-subtle text-success-emphasis border border-success-subtle';
      elseif ($trang_thai === 'Ch? duy?t') $badge_class = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
      elseif ($trang_thai === 'B? lo?i b?') $badge_class = 'bg-danger-subtle text-danger-emphasis border border-danger-subtle';
  ?>
    <tr>
      <td class="text-center"><?php echo $index + 1; ?></td>
      <td><?php echo htmlspecialchars($item['ho_ten']); ?></td>
      <td class="text-center"><?php echo htmlspecialchars($item['ten_lop']); ?></td>
      <td class="text-center"><?php echo date('d/m/Y', strtotime($item['ngay_vi_pham'])); ?></td>
      <td><?php echo htmlspecialchars($item['ten_vi_pham']); ?></td>
      <td><?php echo htmlspecialchars($item['ghi_chu']); ?></td>
      <td><strong><?php echo htmlspecialchars($item['ten_nguoi_gui']); ?></strong></td>
      <td class="text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $badge_class; ?>"><?php echo htmlspecialchars($trang_thai); ?></span></td>
    </tr>
  <?php 
    endforeach; 

    // S?a l?i cú pháp <?dphp
    if (empty($data) && !empty($danh_sach_tong_hop_raw)): ?>
        <tr><td colspan="8" class="text-center p-6 text-slate-500">Không tìm th?y k?t qu? nào phù h?p v?i b? l?c c?a b?n.</td></tr>
    <?php endif;

    return ob_get_clean(); // Tr? v? HTML c?a các hàng
}

// === BU?C 2: KI?M TRA AJAX ===
if (isset($_GET['ajax'])) {
    // Ch? render các hàng <tr> và g?i di
    echo render_table_rows($data_filtered, $danh_sach_tong_hop);
    die(); // D?ng l?i, không render header/footer
}

// === BU?C 3: N?U KHÔNG PH?I AJAX, RENDER TRANG BÌNH THU?NG ===
// Ch? g?i header SAU khi dã x? lý xong logic AJAX
require_once __DIR__ . '/partials/ctv_header.php';
?>
<style>
/* ====== GIAO DI?N CHUNG (ÐÃ Ð?NG B?) ====== */
:root {
  --brand-primary: #2563eb;
  --brand-accent: #60a5fa;
  --brand-gradient: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  --bg-page: #f4f7f9;
  --card-bg: rgba(255, 255, 255, 0.85);
  --card-border: rgba(0, 0, 0, 0.06);
  --text-strong: #1d2d35;
  --text-muted: #64748b;
  --border-radius-large: 1.5rem;
  --border-radius-medium: 0.75rem;
  --border-radius-small: 0.5rem;
  --shadow-soft: 0 8px 25px rgba(0,0,0,0.06);
  --shadow-medium: 0 12px 30px rgba(0,0,0,0.09);
  --transition-smooth: all 0.3s ease;
  --transition-bouncy: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
/* Dark mode variables */
:root {
  --card-bg-light: #ffffff;
  --card-bg-dark: rgba(30, 41, 59, 0.85);
  --card-border-light: rgba(0,0,0,0.08);
  --card-border-dark: rgba(255,255,255,0.1);
  --text-primary-light: #1d2d35;
  --text-primary-dark: #f1f5f9;
  --text-secondary-light: #64748b;
  --text-secondary-dark: #cbd5e1;
  --bg-input-dark: rgba(40, 51, 69, 0.8);
}

/* Dark Mode Styles */
[data-theme="light"] .content-card,
[data-theme="light"] .filter-toolbar {
  background-color: var(--card-bg-light);
  color: var(--text-primary-light);
  border: 1px solid var(--card-border-light);
}
[data-theme="dark"] .content-card,
[data-theme="dark"] .filter-toolbar {
  background-color: var(--card-bg-dark);
  color: var(--text-primary-dark);
  border: 1px solid var(--card-border-dark);
  backdrop-filter: blur(10px);
}
[data-theme="dark"] .page-title,
[data-theme="dark"] .kpi-pill { color: var(--text-primary-dark); }
[data-theme="dark"] .page-subtitle { color: var(--text-secondary-dark); }
[data-theme="dark"] .kpi-pill { background: var(--bg-input-dark); border-color: var(--card-border-dark); }
[data-theme="dark"] .form-label,
[data-theme="dark"] .form-control::placeholder,
[data-theme="dark"] .form-control { color: var(--text-secondary-dark); }
[data-theme="dark"] .form-control,
[data-theme="dark"] .form-control { background-color: var(--bg-input-dark) !important; color: var(--text-primary-dark); border-color: var(--card-border-dark); }
[data-theme="dark"] .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light { color: var(--text-primary-dark); }
[data-theme="dark"] .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light thead th { background: var(--bg-input-dark); border-bottom-color: var(--card-border-dark); color: var(--text-secondary-dark); }
[data-theme="dark"] .floating-shapes .shape { opacity: 0.08; }
[data-theme="dark"] .floating-shapes .geometric-shape { opacity: 0.05; }


body, .main-content { 
    font-family: 'Inter', sans-serif; 
    background-color: var(--bg-page);
}
body {
    position: relative;
    overflow-x: hidden;
    /* Thêm padding cho footer */
    padding-bottom: 90px;
}
.main-content {
    position: relative;
    z-index: 1;
}

/* ====== ABSTRACT FLOATING SHAPES BACKGROUND (ÐÃ THÊM) ====== */
.floating-shapes {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  pointer-events: none;
  z-index: 0;
}
.shape {
  position: absolute;
  border-radius: 50%;
  opacity: 0.15;
  filter: blur(40px);
  animation: float 20s infinite ease-in-out;
}
.shape-1 { width: 400px; height: 400px; background: linear-gradient(135deg, #60a5fa, #3b82f6); top: -200px; left: -100px; animation-delay: 0s; }
.shape-2 { width: 300px; height: 300px; background: linear-gradient(135deg, #7c3aed, #a855f7); top: 20%; right: -100px; animation-delay: 2s; }
.shape-3 { width: 350px; height: 350px; background: linear-gradient(135deg, #22c55e, #10b981); bottom: -150px; left: 10%; animation-delay: 4s; }
.shape-4 { width: 250px; height: 250px; background: linear-gradient(135deg, #f59e0b, #f97316); top: 50%; left: 60%; animation-delay: 6s; }
.shape-5 { width: 280px; height: 280px; background: linear-gradient(135deg, #ec4899, #f472b6); bottom: 20%; right: 20%; animation-delay: 8s; }
@keyframes float {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  25% { transform: translate(50px, -50px) rotate(90deg); }
  50% { transform: translate(-30px, -100px) rotate(180deg); }
  75% { transform: translate(80px, -30px) rotate(270deg); }
}
.geometric-shape { position: absolute; opacity: 0.08; animation: rotate-shape 30s infinite linear; }
.geo-circle { width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #7c3aed); top: 15%; right: 15%; }
.geo-square { width: 150px; height: 150px; background: linear-gradient(45deg, #60a5fa, #a855f7); transform: rotate(45deg); bottom: 25%; left: 20%; }
.geo-triangle { width: 0; height: 0; border-left: 100px solid transparent; border-right: 100px solid transparent; border-bottom: 173px solid rgba(37, 99, 235, 0.1); top: 40%; right: 25%; }
@keyframes rotate-shape { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Page Layout */
.page-w-full max-w-6xl mx-auto px-4 { max-width: 1200px; margin: 1.5rem auto; padding: 0 1rem; }
.page-toolbar { 
    margin-bottom: 1.5rem; 
    animation: fadeUp 0.6s ease forwards;
    opacity: 0;
}
.page-title { 
    font-size: 1.5rem; 
    font-weight: 700; 
    color: var(--text-strong); 
    display: flex; 
    align-items: center; 
    gap: .5rem; 
    flex-wrap: wrap; 
}
.page-title i { color: var(--brand-primary); } /* Ð?ng b? màu icon */
.page-subtitle { font-size: 0.9rem; color: var(--text-muted); margin-top: 0.25rem; }

/* KPI Pills (Ð?ng b? style Glassy) */
.kpi-row { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.5rem; }
.kpi-pill { 
    display: inline-flex; 
    align-items: center; 
    gap: .4rem; 
    font-size: .8rem; 
    padding: .35rem .75rem; 
    border-radius: 999px; 
    background: var(--card-bg); /* N?n glass */
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.5);
    color: var(--text-strong); 
    font-weight: 500; 
    box-shadow: 0 4px 10px rgba(0,0,0,0.05), 0 0 0 1px rgba(255,255,255,0.3) inset;
    transition: var(--transition-bouncy);
}
.kpi-pill:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 15px rgba(0,0,0,0.08), 0 0 0 1px rgba(255,255,255,0.5) inset;
}
.kpi-dot { width: .6rem; height: .6rem; border-radius: 50%; }
.kpi-total .kpi-dot { background: #4dabf7; }
.kpi-ok .kpi-dot { background: #28a745; }
.kpi-pend .kpi-dot { background: #ffc107; }
.kpi-drop .kpi-dot { background: #dc3545; }

/* Filter Card (Ð?ng b? style Card) */
.filter-toolbar {
  margin-top: 1.5rem;
  /* Áp d?ng style c?a content-card */
  border: none;
  border-radius: var(--border-radius-large);
  background: var(--card-bg);
  backdrop-filter: blur(20px) saturate(180%);
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 1;
  padding: 1rem; /* Padding bên trong card */
  display: flex;
  gap: .75rem;
  flex-wrap: wrap;
  animation: fadeUp 0.7s ease forwards;
  opacity: 0;
}
.filter-toolbar:hover {
  transform: translateY(-4px) scale(1.01);
  box-shadow: 0 15px 45px rgba(37, 99, 235, 0.12),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}
/* Form elements (Ð?ng b?) */
.filter-toolbar .form-control, .filter-toolbar .form-control {
  border-radius: var(--border-radius-small);
  background-color: rgba(255, 255, 255, 0.9);
  border: 1px solid var(--card-border);
  transition: var(--transition-bouncy);
  font-size: .9rem; 
  min-width: 180px;
}
.filter-toolbar .form-control:focus, .filter-toolbar .form-control:focus {
  background-color: #fff;
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
  transform: translateY(-2px);
}

/* Nút b?m (Ð?ng b?) */
.btn.btn-sm { 
    border-radius: var(--border-radius-small) !important; 
    font-weight: 600; 
    transition: var(--transition-bouncy);
    padding: 0.6rem 1.2rem;
    border: none;
}
.px-4 py-1.5 text-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
}
.btn-outline-secondary {
    color: var(--text-muted); 
    border: 1px solid var(--card-border);
    background-color: #fff; 
    box-shadow: var(--shadow-soft);
}
.btn-outline-secondary:hover {
    background: var(--brand-gradient);
    color: white;
    border-color: transparent;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

/* Main Content Card (Ð?ng b?) */
.content-card {
  border: none;
  border-radius: var(--border-radius-large);
  background: var(--card-bg);
  backdrop-filter: blur(20px) saturate(180%);
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 1;
  margin-bottom: 2rem;
  animation: fadeUp 0.8s ease forwards;
  opacity: 0;
}
.content-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--brand-gradient);
  opacity: 0;
  transition: opacity 0.4s ease;
  z-index: 1;
}
.content-card:hover {
  transform: translateY(-8px) scale(1.01);
  box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}
.content-card:hover::before {
  opacity: 1;
}

/* Table styles (Ð?ng b?) */
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: rgba(0,0,0,0.2) transparent;
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-wrap::-webkit-scrollbar { height: 8px; }
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-wrap::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 8px; }

.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light {
  margin-bottom: 0;
  white-space: nowrap;
  font-size: .85rem;
  min-width: 800px;
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  background: rgba(243, 246, 249, 0.8); /* N?n glass */
  backdrop-filter: blur(5px);
  border-bottom: 2px solid var(--card-border);
  text-transform: uppercase;
  font-size: .7rem;
  font-weight: 600;
  color: var(--text-muted);
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light td, .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light th { vertical-align: middle; }
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light tbody tr:hover { background-color: rgba(37,99,235,0.05); }

.badge {
  font-size: .7rem;
  border-radius: 999px;
  padding: .3rem .6rem;
  font-weight: 600;
}

/* Responsive (Gi? nguyên) */
@media (max-width: 991px) {
  .page-w-full max-w-6xl mx-auto px-4 { padding: 0 .75rem; }
  .page-title { font-size: 1.25rem; justify-content: space-between; }
  .filter-toolbar { flex-direction: column; align-items: stretch; }
  .filter-toolbar .form-control, .filter-toolbar .form-control { width: 100%; }
}
@media (max-width: 768px) {
  .page-title i { font-size: 1.2rem; }
  .page-subtitle { font-size: .8rem; }
  .kpi-pill { font-size: .75rem; padding: .3rem .6rem; }
  .filter-toolbar { padding: .75rem .75rem; }
  .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light { font-size: .8rem; min-width: 640px; }
  .shape { filter: blur(30px); }
  .shape-1, .shape-2, .shape-3, .shape-4, .shape-5 {
    width: 200px !important;
    height: 200px !important;
  }
}
@media (max-width: 480px) {
  .page-title { font-size: 1.1rem; flex-direction: column; align-items: flex-start; }
  .page-toolbar a.btn.btn-sm { width: 100%; text-align: center; margin-top: .5rem; }
  .kpi-row { justify-content: d-flex-start; }
  .kpi-pill { width: 48%; justify-content: center; }
}
</style>

<div class="floating-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
  <div class="shape shape-4"></div>
  <div class="shape shape-5"></div>
  <div class="geometric-shape geo-circle"></div>
  <div class="geometric-shape geo-square"></div>
  <div class="geometric-shape geo-triangle"></div>
</div>


<div class="page-w-full max-w-6xl mx-auto px-6">
  <div class="page-toolbar">
    <div class="flex justify-between items-start flex-wrap">
        <div>
            <h3 class="page-title"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-broadcast text-primary-600" viewBox="0 0 16 16"><path d="M3.05 3.05a7 7 0 0 0 0 9.9.5.5 0 0 1-.707.707 8 8 0 0 1 0-11.314.5.5 0 0 1 .707.707m2.122 2.122a4 4 0 0 0 0 5.656.5.5 0 1 1-.708.708 5 5 0 0 1 0-7.072.5.5 0 0 1 .708.708m5.656-.708a.5.5 0 0 1 .708 0 5 5 0 0 1 0 7.072.5.5 0 1 1-.708-.708 4 4 0 0 0 0-5.656.5.5 0 0 1 0-.708m2.122-2.12a.5.5 0 0 1 .707 0 8 8 0 0 1 0 11.313.5.5 0 0 1-.707-.707 7 7 0 0 0 0-9.9.5.5 0 0 1 0-.707zM10 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0"/></svg>B?ng Tin Vi Ph?m</h3>
            <p class="page-subtitle"><strong><?php echo htmlspecialchars($tuan_hoc['ten_tuan']); ?></strong></p>
        </div>
        <a href="/thidua/hocsinh/nhap-vi-pham?tuan_id=<?php echo htmlspecialchars($_GET['tuan_id']); ?>" class="btn bg-transparent hover:bg-slate-600 text-slate-600 hover:text-white border border-slate-600 px-6 py-1.5 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left-circle mr-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/></svg> Quay l?i
        </a>
    </div>

    <div class="kpi-row">
      <?php
        $total = count($danh_sach_tong_hop ?? []);
        $duyet = 0; $cho = 0; $loai = 0;
        foreach(($danh_sach_tong_hop ?? []) as $it){
          if(($it['trang_thai'] ?? '') === 'Ðã duy?t') $duyet++;
          elseif(($it['trang_thai'] ?? '') === 'Ch? duy?t') $cho++;
          elseif(($it['trang_thai'] ?? '') === 'B? lo?i b?') $loai++;
        }
      ?>
      <span class="kpi-pill kpi-total"><span class="kpi-dot"></span>T?ng: <strong><?php echo $total; ?></strong></span>
      <span class="kpi-pill kpi-ok"><span class="kpi-dot"></span>Ðã duy?t: <strong><?php echo $duyet; ?></strong></span>
      <span class="kpi-pill kpi-pend"><span class="kpi-dot"></span>Ch? duy?t: <strong><?php echo $cho; ?></strong></span>
      <span class="kpi-pill kpi-drop"><span class="kpi-dot"></span>B? lo?i: <strong><?php echo $loai; ?></strong></span>
    </div>

    <form class="filter-toolbar" id="filterForm" method="GET">
      <input type="hidden" name="tuan_id" value="<?php echo htmlspecialchars($_GET['tuan_id'] ?? ''); ?>">
      <div class="d-flex-grow-1">
        <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm" placeholder="Tìm theo tên h?c sinh, l?p, ho?c tên nhóm vi ph?m...">
      </div>
      <div>
        <select name="st" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm">
          <option value="all">T?t c? tr?ng thái</option>
          <option value="duyet" <?php if(($_GET['st'] ?? '')==='duyet') echo 'selected'; ?>>Ðã duy?t</option>
          <option value="cho" <?php if(($_GET['st'] ?? '')==='cho') echo 'selected'; ?>>Ch? duy?t</option>
          <option value="loai" <?php if(($_GET['st'] ?? '')==='loai') echo 'selected'; ?>>B? lo?i b?</option>
        </select>
      </div>
      <div>
        <select name="lop" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm">
          <option value="all">T?t c? l?p</option>
          <?php
            $lopSet = [];
            foreach(($danh_sach_tong_hop ?? []) as $it){ $lopSet[$it['ten_lop']] = true; }
            ksort($lopSet);
            foreach(array_keys($lopSet) as $tenLop){
              $sel = ($_GET['lop'] ?? '')===$tenLop ? 'selected' : '';
              echo '<option value="'.htmlspecialchars($tenLop).'" '.$sel.'>'.htmlspecialchars($tenLop).'</option>';
            }
          ?>
        </select>
      </div>
      </form>
  </div>

  <div class="content-card">
    <?php if (empty($danh_sach_tong_hop)): ?>
      <div class="p-6 mb-6 rounded-lg border bg-cyan-50 text-cyan-800 border-cyan-200 text-center m-3">Chua có vi ph?m nào trong tu?n này.</div>
    <?php else: ?>
      <div class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-wrap">
        <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light border border-slate-200">
          <thead>
            <tr>
              <th>STT</th><th>HS Vi ph?m</th><th>L?p</th><th>Ngày VP</th><th>Tên Nhóm Vi ph?m</th><th>Ghi chú</th><th>Ngu?i ghi nh?n</th><th class="text-center">Tr?ng thái</th>
            </tr>
          </thead>
          <tbody id="violations-table-body">
            <?php 
                // In các hàng cho l?n t?i trang d?u tiên
                echo render_table_rows($data_filtered, $danh_sach_tong_hop); 
            ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/partials/ctv_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filterForm');
    const inputs = filterForm.querySelectorAll('input, select');
    // Tr? dúng vào tbody
    const tableBody = document.getElementById('violations-table-body');

    // Hàm debounce d? tránh g?i form liên t?c khi gõ phím
    const debounce = (func, delay) => {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    };

    // === HÀM L?C M?I B?NG AJAX ===
    const fetchFilters = async () => {
        // 1. Hi?n th? tr?ng thái dang t?i
        if (tableBody) {
            tableBody.style.opacity = '0.5';
            tableBody.style.transition = 'opacity 0.3s ease';
        }

        // 2. L?y d? li?u form và thêm tham s? 'ajax=1'
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.set('ajax', '1'); // Báo cho PHP bi?t dây là yêu c?u AJAX
        
        const queryString = params.toString();

        try {
            // 3. G?i fetch
            const response = await fetch(window.location.pathname + '?' + queryString);
            if (!response.ok) {
                throw new Error('L?i máy ch?');
            }
            const newHtml = await response.text();

            // 4. C?p nh?t b?ng
            if (tableBody) {
                tableBody.innerHTML = newHtml;
                tableBody.style.opacity = '1';
            }

            // 5. C?p nh?t URL trên thanh d?a ch? mà không t?i l?i trang
            // B? &ajax=1 ra kh?i URL
            window.history.pushState({}, '', window.location.pathname + '?' + queryString.replace(/&?ajax=1/, ''));

        } catch (error) {
            console.error('L?i khi l?c:', error);
            if (tableBody) {
                tableBody.style.opacity = '1'; // Ph?c h?i l?i n?u l?i
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center p-6 text-red-600">Không th? t?i k?t qu? l?c.</td></tr>';
            }
        }
    };

    // T?o m?t phiên b?n debounced c?a hàm
    const debouncedFetch = debounce(fetchFilters, 400); // 400ms delay

    // === NGAN CH?N FORM G?I ÐI ===
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Ngan ch?n t?i l?i trang khi nh?n Enter
        fetchFilters();
    });

    // === G?N S? KI?N VÀO CÁC Ô L?C ===
    inputs.forEach(input => {
        if (input.type === 'text') {
            // Dùng debounced cho ô tìm ki?m
            input.addEventListener('input', debouncedFetch);
        } else {
            // G?i ngay l?p t?c cho các l?a ch?n dropdown
            input.addEventListener('change', fetchFilters);
        }
    });
});
</script>
