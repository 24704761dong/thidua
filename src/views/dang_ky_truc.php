<?php
$page_title = 'Ðang Ký L?ch Tr?c Tu?n';
require_once __DIR__ . '/partials/ctv_header.php';

$is_locked = ($trang_thai_dang_ky === 'Ðã duy?t');
$days_of_week = ['Th? Hai', 'Th? Ba', 'Th? Tu', 'Th? Nam', 'Th? Sáu', 'Th? B?y', 'Ch? Nh?t'];

$student_map = [];
$full_student_list_for_modal = [];

$stmt_full_lop = $db->prepare("SELECT id, ho_dem, ten FROM hoc_sinh WHERE lop_hoc_id = ? ORDER BY ten, ho_dem");
$stmt_full_lop->execute([$lop_cua_ctv['lop_hoc_id']]);
$full_lop = $stmt_full_lop->fetchAll();

foreach($full_lop as $hs) {
    $student_map[$hs['id']] = htmlspecialchars($hs['ho_dem'] . ' ' . $hs['ten']);
    $full_student_list_for_modal[] = [
        'id' => $hs['id'],
        'name' => htmlspecialchars($hs['ho_dem'] . ' ' . $hs['ten'])
    ];
}
?>
<style>
/* ====== GIAO DI?N CHUNG (Ð?ng b?) ====== */
:root {
  --brand-primary: #2563eb;
  --brand-accent: #60a5fa;
  --brand-gradient: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  --bg-page: #f4f7f9;
  --card-bg: rgba(255, 255, 255, 0.85);
  --card-border: rgba(0, 0, 0, 0.06);
  --text-strong: #1d2d35;
  --text-muted: #64748b;
  --purple-light: rgba(124, 58, 237, 0.1);
  --blue-light: rgba(37, 99, 235, 0.1);
  --green-light: rgba(34, 197, 94, 0.1);
  --border-radius-large: 1.5rem;
  --border-radius-medium: 0.75rem;
  --border-radius-small: 0.5rem;
  --shadow-soft: 0 8px 25px rgba(0,0,0,0.06);
  --shadow-medium: 0 12px 30px rgba(0,0,0,0.09);
  --transition-smooth: all 0.3s ease;
}

body, .main-content { 
  font-family: 'Inter', sans-serif; 
  background-color: var(--bg-page); 
  color: var(--text-strong);
  position: relative;
  overflow-x: hidden;
}

/* ====== ABSTRACT FLOATING SHAPES BACKGROUND ====== */
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

.shape-1 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #60a5fa, #3b82f6);
  top: -200px;
  left: -100px;
  animation-delay: 0s;
}

.shape-2 {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #7c3aed, #a855f7);
  top: 20%;
  right: -100px;
  animation-delay: 2s;
}

.shape-3 {
  width: 350px;
  height: 350px;
  background: linear-gradient(135deg, #22c55e, #10b981);
  bottom: -150px;
  left: 10%;
  animation-delay: 4s;
}

.shape-4 {
  width: 250px;
  height: 250px;
  background: linear-gradient(135deg, #f59e0b, #f97316);
  top: 50%;
  left: 60%;
  animation-delay: 6s;
}

.shape-5 {
  width: 280px;
  height: 280px;
  background: linear-gradient(135deg, #ec4899, #f472b6);
  bottom: 20%;
  right: 20%;
  animation-delay: 8s;
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0) rotate(0deg);
  }
  25% {
    transform: translate(50px, -50px) rotate(90deg);
  }
  50% {
    transform: translate(-30px, -100px) rotate(180deg);
  }
  75% {
    transform: translate(80px, -30px) rotate(270deg);
  }
}

/* Geometric shapes */
.geometric-shape {
  position: absolute;
  opacity: 0.08;
  animation: rotate-shape 30s infinite linear;
}

.geo-circle {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  top: 15%;
  right: 15%;
}

.geo-square {
  width: 150px;
  height: 150px;
  background: linear-gradient(45deg, #60a5fa, #a855f7);
  transform: rotate(45deg);
  bottom: 25%;
  left: 20%;
}

.geo-triangle {
  width: 0;
  height: 0;
  border-left: 100px solid transparent;
  border-right: 100px solid transparent;
  border-bottom: 173px solid rgba(37, 99, 235, 0.1);
  top: 40%;
  right: 25%;
}

@keyframes rotate-shape {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ====== KHUNG T?NG (Ð?ng b?) ====== */
.content-card {
  max-width: 1100px;
  margin: 1.5rem auto;
  background: var(--card-bg);
  backdrop-filter: blur(20px) saturate(180%);
  border: none;
  border-radius: var(--border-radius-large);
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  position: relative;
  z-index: 1;
  animation: fadeUp 0.8s ease forwards;
  opacity: 0;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
  border-radius: var(--border-radius-large) var(--border-radius-large) 0 0;
}

.content-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}

.content-card:hover::before {
  opacity: 1;
}

/* ====== HEADER (Ð?ng b?) ====== */
.card-header-custom {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--card-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: .75rem;
  background: transparent;
}
.card-header-custom h5 {
  font-weight: 700;
  font-size: 1.35rem;
  margin: 0;
  color: var(--text-strong);
}
.card-header-custom small { color: var(--text-muted); }
.card-header-custom .badge {
  background: var(--brand-gradient) !important;
  border: none;
  padding: 0.4em 0.9em;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
  font-weight: 600;
}

/* ====== GRID NGÀY TR?C (Ð?ng b?) ====== */
.duty-schedule-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.day-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid var(--card-border);
  border-radius: var(--border-radius-medium);
  display: flex;
  flex-direction: column;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  animation: fadeUp 0.8s ease forwards;
  opacity: 0;
}

.day-card:nth-child(1) { animation-delay: 0.1s; }
.day-card:nth-child(2) { animation-delay: 0.2s; }
.day-card:nth-child(3) { animation-delay: 0.3s; }
.day-card:nth-child(4) { animation-delay: 0.4s; }
.day-card:nth-child(5) { animation-delay: 0.5s; }
.day-card:nth-child(6) { animation-delay: 0.6s; }
.day-card:nth-child(7) { animation-delay: 0.7s; }

.day-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--brand-gradient);
  opacity: 0;
  transition: opacity 0.4s ease;
}

.day-card:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 15px 35px rgba(37, 99, 235, 0.15),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}

.day-card:hover::before {
  opacity: 1;
}

.day-card-header {
  padding: .9rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--card-border);
  background: rgba(255, 255, 255, 0.5);
}

.day-card-header h6 {
  margin: 0;
  font-weight: 600;
  font-size: 1rem;
  color: var(--text-strong);
}

.day-p-4 { 
  padding: 1rem; 
  d-flex-grow: 1; 
  background: linear-gradient(to bottom, rgba(250, 251, 252, 0.5), rgba(255, 255, 255, 0.8));
}

.assigned-list {
  list-style-type: none;
  padding: 0;
  margin: 0;
  min-height: 100px;
}

.assigned-list li {
  padding: .5rem .75rem;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid var(--card-border);
  border-radius: var(--border-radius-small);
  margin-bottom: .5rem;
  font-size: .9rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.assigned-list li:hover { 
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(124, 58, 237, 0.1));
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
  border-color: rgba(37, 99, 235, 0.3);
}

.empty-duty-slot {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: var(--text-muted);
  font-style: italic;
}

/* ====== NÚT (Ð?ng b?) ====== */
.btn.btn-sm {
  border-radius: var(--border-radius-small);
  font-weight: 600;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent {
  background: var(--brand-gradient);
  border: none;
  color: white;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:hover { 
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
}

.btn-success { 
  background: linear-gradient(135deg, #198754 0%, #146c43 100%);
  border: none;
  color: white;
  box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
  border-radius: var(--border-radius-small);
  font-weight: 600;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.btn-success:hover {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 25px rgba(25, 135, 84, 0.4);
}

.btn-secondary {
  background: #6c757d;
  border: none;
  color: white;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.btn-secondary:hover {
  background: var(--brand-gradient);
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

/* ====== MODAL (Ð?ng b?) ====== */
#studentSelectionModal .relative bg-white rounded-xl shadow-xl table-bordered d-flex d-flex-col {
  border-radius: var(--border-radius-large);
  border: none;
  box-shadow: 0 20px 50px rgba(0,0,0,0.2);
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.95);
}

#studentSelectionModal .d-flex align-items-center justify-content-between p-4 border-b rounded-t-xl {
  border-bottom: 1px solid var(--card-border);
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(124, 58, 237, 0.05));
}

#studentSelectionModal .modal-title {
  font-weight: 700;
  color: var(--text-strong);
}

#studentSelectionModal .p-4 space-y-4 {
  max-height: 65vh;
  overflow-y: auto;
  padding: 1.25rem 1.5rem;
}

#studentSelectionModal .d-flex align-items-center {
  border-bottom: 1px solid var(--card-border);
  padding: .7rem 0 .7rem 0.5rem;
  margin: 0;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

#studentSelectionModal .rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 {
  margin-top: 0;
  margin-left: 0;
  flex-shrink: 0;
}

#studentSelectionModal .d-flex align-items-center:hover {
  background: rgba(37, 99, 235, 0.03);
  margin-left: -0.5rem;
  margin-right: -0.5rem;
  padding-left: 1rem;
  padding-right: 0.5rem;
  border-radius: var(--border-radius-small);
}

#studentSelectionModal .d-flex align-items-center:last-child { border-bottom: none; }

#studentSelectionModal label { 
  font-size: .9rem;
  color: var(--text-strong);
  cursor: pointer;
}

#studentSelectionModal .rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50:checked {
  background-color: var(--brand-primary);
  border-color: var(--brand-primary);
}

#studentSelectionModal .d-flex align-items-center justify-content-end p-4 border-t space-x-2 rounded-b-xl {
  border-top: 1px solid var(--card-border);
  background: rgba(255, 255, 255, 0.5);
}

/* ====== FOOTER (Ð?ng b?) ====== */
.card-footer {
  border-top: 1px solid var(--card-border);
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(10px);
  border-radius: 0 0 var(--border-radius-large) var(--border-radius-large);
  padding: 1rem 1.5rem;
  display: flex;
  flex-wrap: wrap;
  justify-content: d-flex-end;
  gap: .75rem;
}

/* ====== KEYFRAMES (Ð?ng b?) ====== */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

[data-theme="dark"] .content-card,
[data-theme="dark"] .day-card {
  background: rgba(30, 41, 59, 0.85);
  border-color: rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
}

[data-theme="dark"] .day-p-4 {
  background: linear-gradient(to bottom, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.8));
}

[data-theme="dark"] .assigned-list li {
  background: rgba(30, 41, 59, 0.9);
  border-color: rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
}

[data-theme="dark"] .assigned-list li:hover {
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.2), rgba(124, 58, 237, 0.2));
}

/* ====== RESPONSIVE ====== */
@media (max-width: 991px) {
  .card-header-custom { flex-direction: column; align-items: flex-start; }
  .card-header-custom h5 { font-size: 1.2rem; }
  .duty-schedule-grid { gap: 1rem; padding: 1rem; }
  .day-card-header h6 { font-size: .95rem; }
  .px-4 py-1.5 text-sm { padding: .4rem .7rem; font-size: .8rem; }
}

@media (max-width: 767px) {
  .content-card { margin: 1rem; }
  .card-header-custom { text-align: center; align-items: center; }
  .duty-schedule-grid { grid-template-columns: 1fr; }
  .assigned-list li { font-size: .85rem; }
  .card-footer { justify-content: center; }
  .card-footer .btn.btn-sm { d-flex: 1; min-width: 130px; text-align: center; }
  .shape { filter: blur(30px); }
  .shape-1, .shape-2, .shape-3, .shape-4, .shape-5 {
    width: 200px !important;
    height: 200px !important;
  }
}

@media (max-width: 480px) {
  .day-p-4 { padding: .75rem; }
  .day-card-header { padding: .7rem .8rem; }
  .assigned-list { min-height: 80px; }
  .card-header-custom h5 { font-size: 1.1rem; }
  .relative w-full max-w-lg mx-auto p-4 mt-10 { max-width: 95%; margin: 0 auto; }
}
</style>

<!-- Floating Abstract Shapes Background -->
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


<div class="content-card">
    <div class="card-header-custom">
        <div>
            <h5 class="mb-0">Ðang Ký L?ch Tr?c - <?php echo htmlspecialchars($tuan_hien_tai['ten_tuan']); ?></h5>
            <small class="text-slate-500">L?p: <?php echo htmlspecialchars($lop_cua_ctv['ten_lop']); ?></small>
        </div>
        <div>
            <?php if ($trang_thai_dang_ky === 'Ðã duy?t'): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 text-base"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill mr-2" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>ÐÃ DUY?T</span>
            <?php elseif ($trang_thai_dang_ky === 'Ch? duy?t'): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 text-slate-900 text-base"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hourglass-split mr-2" viewBox="0 0 16 16"><path d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48zm1 0v3.17c2.134.181 3 1.48 3 1.48a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351z"/></svg>CH? DUY?T</span>
            <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 text-base">B?N NHÁP</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="p-6">
        <?php if ($is_locked): ?>
            <div class="p-6 mb-6 rounded-lg border bg-cyan-50 text-cyan-800 border-cyan-200 m-3"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-lock-fill mr-2" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg> Danh sách tr?c tu?n này dã du?c Admin duy?t và không th? thay d?i.</div>
        <?php else: ?>
             <div class="p-6 mb-6 rounded-lg border alert-primary m-3"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill mr-2" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg> Nh?n nút "Thêm / S?a" ? m?i ngày d? ch?n h?c sinh tr?c.</div>
        <?php endif; ?>

        <div class="duty-schedule-grid" id="duty-schedule-grid">
            <?php foreach($days_of_week as $index => $day_name): ?>
                <div class="day-card" data-day-index="<?php echo $index; ?>">
                    <div class="day-card-header">
                        <h6><?php echo $day_name; ?></h6>
                        <?php if (!$is_locked): ?>
                            <button class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent px-6 py-1.5 text-sm edit-day-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square mr-1" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg> Thêm / S?a
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="day-p-4">
                        <ul class="assigned-list">
                        </ul>
                        <div class="empty-duty-slot"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="px-6 py-6 border-t border-slate-200 bg-slate-50 rounded-b-xl text-right">
         <a href="/thidua/hocsinh/chon-tuan?type=dang_ky_truc" class="btn bg-slate-600 hover:bg-slate-700 text-white shadow-sm border-transparent">Ch?n Tu?n Khác</a>
        <?php if (!$is_locked): ?>
            <button id="save-duty-btn" class="btn bg-green-600 hover:bg-green-700 text-white shadow-sm border-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-send-fill mr-2" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/></svg>Ðang Ký</button>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="studentSelectionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content relative bg-white rounded-xl shadow-xl border border-slate-200 flex flex-col">
      <div class="flex items-center justify-between p-6 border-b rounded-t-xl">
        <h5 class="text-lg font-semibold text-slate-900" id="studentSelectionModalLabel">Ch?n h?c sinh tr?c</h5>
        <button type="button" class="text-slate-400 hover:text-slate-500 p-2" aria-label="Close"></button>
      </div>
      <div class="p-6 space-y-4">
        <div id="modal-student-list"></div>
      </div>
      <div class="flex items-center justify-end p-6 border-t space-x-2 rounded-b-xl">
        <button type="button" class="btn bg-slate-600 hover:bg-slate-700 text-white shadow-sm border-transparent">H?y</button>
        <button type="button" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent" id="confirm-selection-btn">Xác nh?n</button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/ctv_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isLocked = <?php echo json_encode($is_locked); ?>;
    const studentMap = <?php echo json_encode($student_map); ?>;
    const fullStudentList = <?php echo json_encode($full_student_list_for_modal); ?>;
    let initialSchedule = <?php echo json_encode($lich_truc_da_dang_ky); ?>;
    
    const scheduleGrid = document.getElementById('duty-schedule-grid');
    const studentSelectionModal = null /* Removed Bootstrap Modal */);
    const modalStudentList = document.getElementById('modal-student-list');
    const confirmSelectionBtn = document.getElementById('confirm-selection-btn');
    let currentEditingDay = null;

    function renderDay(dayIndex) {
        const dayCard = scheduleGrid.querySelector(`.day-card[data-day-index='${dayIndex}']`);
        const assignedList = dayCard.querySelector('.assigned-list');
        const emptySlot = dayCard.querySelector('.empty-duty-slot');
        const studentIds = initialSchedule[dayIndex] || [];

        assignedList.innerHTML = '';
        if (studentIds.length > 0) {
            studentIds.forEach(id => {
                const li = document.createElement('li');
                li.textContent = studentMap[id] || `ID: ${id}`;
                assignedList.appendChild(li);
            });
            emptySlot.style.display = 'none';
        } else {
            emptySlot.style.display = 'd-flex';
        }
    }

    function renderAllDays() {
        for (let i = 0; i < 7; i++) {
            renderDay(i);
        }
    }

    function openStudentSelectionModal(dayIndex) {
        currentEditingDay = dayIndex;
        const assignedStudentsToday = initialSchedule[dayIndex] || [];
        const assignedStudentsOtherDays = Object.entries(initialSchedule)
            .filter(([key, value]) => key != dayIndex)
            .flatMap(([key, value]) => value);

        modalStudentList.innerHTML = '';
        fullStudentList.forEach(student => {
            const isChecked = assignedStudentsToday.includes(student.id.toString());
            const isDisabled = !isChecked && assignedStudentsOtherDays.includes(student.id.toString());
            
            const formCheck = document.createElement('div');
            formCheck.className = 'd-flex align-items-center';
            formCheck.innerHTML = `
                <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" value="${student.id}" id="student-${student.id}" ${isChecked ? 'checked' : ''} ${isDisabled ? 'disabled' : ''}>
                <label class="ml-2 block text-sm text-slate-900 ${isDisabled ? 'text-muted' : ''}" for="student-${student.id}">
                    ${student.name} ${isDisabled ? '<small>(Ðã tr?c ngày khác)</small>' : ''}
                </label>
            `;
            modalStudentList.appendChild(formCheck);
        });
        
        const modalLabel = document.getElementById('studentSelectionModalLabel');
        modalLabel.textContent = `Ch?n h?c sinh tr?c - ${document.querySelector(`.day-card[data-day-index="${dayIndex}"] h6`).textContent}`;
        studentSelectionModal.show();
    }
    
    if (!isLocked) {
        scheduleGrid.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.edit-day-btn');
            if (editBtn) {
                const dayCard = editBtn.closest('.day-card');
                const dayIndex = dayCard.dataset.dayIndex;
                openStudentSelectionModal(dayIndex);
            }
        });

        confirmSelectionBtn.addEventListener('click', function() {
            const selectedIds = Array.from(modalStudentList.querySelectorAll('input[type="checkbox"]:checked')).map(input => input.value);
            initialSchedule[currentEditingDay] = selectedIds;
            renderDay(currentEditingDay);
            studentSelectionModal.hide();
        });

        document.getElementById('save-duty-btn').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ðang g?i...';

            fetch('/thidua/api/luu-dang-ky-truc', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    tuan_hoc_id: <?php echo $tuan_hien_tai['id']; ?>,
                    schedule: initialSchedule
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if(data.success) {
                    window.location.reload();
                } else {
                    this.disabled = false;
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-send-fill mr-2" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/></svg>Luu và G?i Admin';
                }
            })
            .catch(err => {
                alert('Ðã x?y ra l?i k?t n?i.');
                this.disabled = false;
                this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-send-fill mr-2" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/></svg>Luu và G?i Admin';
            });
        });
    }

    renderAllDays();
});
</script>
