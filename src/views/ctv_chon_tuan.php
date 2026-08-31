<?php
require_once __DIR__ . '/partials/ctv_header.php';
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
/* Dark mode (Gi? CSS cu c?a b?n) */
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
[data-theme="light"] .content-card {
  background-color: var(--card-bg-light);
  color: var(--text-primary-light);
  border: 1px solid var(--card-border-light);
}
[data-theme="dark"] .content-card {
  background-color: var(--card-bg-dark);
  color: var(--text-primary-dark);
  border: 1px solid var(--card-border-dark);
  backdrop-filter: blur(10px);
}
[data-theme="dark"] .page-header-title,
[data-theme="dark"] .semester-title,
[data-theme="dark"] .week-name { color: var(--text-primary-dark); }
[data-theme="dark"] .page-header-subtitle,
[data-theme="dark"] .semester-title i,
[data-theme="dark"] .week-date,
[data-theme="dark"] .empty-state { color: var(--text-secondary-dark); }
[data-theme="dark"] .week-card { background-color: var(--bg-input-dark); border-color: var(--card-border-dark); }
[data-theme="dark"] .empty-state { background-color: transparent; border-color: var(--card-border-dark); }
[data-theme="dark"] .back-button-w-full max-w-6xl mx-auto px-4 .btn.btn-sm { background-color: var(--bg-input-dark); border-color: var(--card-border-dark); color: var(--text-secondary-dark); }
[data-theme="dark"] .back-button-w-full max-w-6xl mx-auto px-4 .btn btn-sm:hover { background-color: rgba(255,255,255,0.1); color: var(--text-primary-dark); }

body {
  font-family: "Inter", sans-serif;
  background-color: var(--bg-page);
  position: relative;
  overflow-x: hidden;
}

.main-content { font-family: 'Inter', sans-serif; }

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

.content-card {
  position: relative;
  z-index: 1;
}

/* ====== NÂNG C?P: Card chính ====== */
.content-card {
    max-width: 980px;
    margin: 1.5rem auto;
    background: var(--card-bg);
    border: none;
    border-radius: 1.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08),
                0 0 0 1px rgba(255,255,255,0.5) inset;
    backdrop-filter: blur(20px) saturate(180%);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
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
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15),
                0 0 0 1px rgba(255,255,255,0.8) inset;
}

.content-card:hover::before {
    opacity: 1;
}

/* ====== NÂNG C?P: Header trang ====== */
.page-header {
    padding: 1.5rem;
    text-align: center;
    border-bottom: 1px solid var(--card-border);
}
.page-header-icon {
    font-size: 2.5rem;
    background: var(--brand-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: iconPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s forwards;
    opacity: 0;
    transform: scale(0.8);
    filter: drop-shadow(0 4px 8px rgba(37, 99, 235, 0.2));
}
.page-header-title {
    font-weight: 700;
    color: var(--text-strong);
    margin-top: 0.5rem;
}
.page-header-subtitle {
    color: var(--text-muted);
    margin-bottom: 0.75rem; /* Tang kho?ng cách */
}
.page-header .badge {
    background: var(--brand-gradient) !important;
    border: none;
    border-radius: 50px;
    padding: 0.4em 0.8em;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    transition: all 0.3s ease;
}

.page-header .badge:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.4);
}

/* ====== NÂNG C?P: Tiêu d? H?c k? ====== */
.semester-section {
    padding: 1.5rem;
}
.semester-title {
    display: flex;
    align-items: center;
    gap: .6rem;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--text-strong);
    padding-left: .8rem;
    border-left: 4px solid var(--brand-primary);
    margin-bottom: 1.5rem; /* Tang kho?ng cách */
}
.semester-title i { color: var(--text-muted); }

/* ====== NÂNG C?P: Lu?i & Th? Tu?n (Week Card) ====== */
.week-grid {
    display: grid;
    /* M?c d?nh 2 c?t trên mobile */
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 1rem;
}
.week-card {
    text-decoration: none;
    border: 1px solid rgba(255,255,255,0.8);
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    padding: 1rem;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.06),
                0 0 0 1px rgba(255,255,255,0.5) inset;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
    animation: fadeUp 0.6s ease forwards;
    animation-delay: calc(var(--item-index, 0) * 50ms);
    position: relative;
    overflow: hidden;
}

.week-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--brand-gradient);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1;
}

.week-card::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(37,99,235,0.1), transparent);
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
    pointer-events: none;
    z-index: 0;
}

.week-card:hover {
    transform: translateY(-8px) scale(1.03);
    border-color: rgba(255,255,255,1);
    box-shadow: 0 20px 50px rgba(37,99,235,0.2),
                0 0 0 1px rgba(255,255,255,1) inset;
    background: linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(240,247,255,0.98) 100%);
}

.week-card:hover::before {
    transform: scaleX(1);
}

.week-card:hover::after {
    width: 300px;
    height: 300px;
}
.week-name {
    font-weight: 600;
    color: var(--text-strong);
    margin-bottom: 0.25rem;
}
.week-date {
    color: var(--text-muted);
    font-size: .85rem;
}

/* Tr?ng thái tr?ng (Gi? nguyên) */
.empty-state {
    gricolumn: 1 / -1;
    border: 2px dashed var(--card-border);
    background: var(--bg-light);
    color: var(--text-muted);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
}

/* ====== NÂNG C?P: Nút b?m footer ====== */
.back-button-w-full max-w-6xl mx-auto px-4 {
    text-align: center;
    padding: 1.5rem;
    border-top: 1px solid var(--card-border);
}
.back-button-w-full max-w-6xl mx-auto px-4 .btn.btn-sm {
    border-radius: 50px !important; /* Bo tròn */
    font-weight: 600;
    padding: 0.6rem 1.2rem;
    transition: var(--transition-smooth);
    border-width: 1.5px;
}
.back-button-w-full max-w-6xl mx-auto px-4 .btn-outline-secondary {
    color: var(--text-muted);
    border-color: var(--card-border);
    background-color: #fff;
    box-shadow: var(--shadow-soft);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.back-button-w-full max-w-6xl mx-auto px-4 .btn-outline-secondary:hover {
    background: var(--brand-gradient);
    border-color: transparent;
    color: white;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

/* ====== KEYFRAMES (cho hi?u ?ng) ====== */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes iconPop {
  0% { 
    opacity: 0; 
    transform: scale(0.8) rotate(-10deg); 
  }
  50% { 
    opacity: 1; 
    transform: scale(1.15) rotate(5deg); 
  }
  100% { 
    opacity: 1; 
    transform: scale(1) rotate(0deg); 
  }
}

/* ====== T?I UU MOBILE (Thu g?n) ====== */
@media (max-width: 576px) {
    .content-card {
        margin: 0.75rem;
        padding: 0;
        border-radius: 0.75rem;
    }
    .semester-section {
        padding: 1rem;
    }
    .page-header {
        padding: 1rem;
        margin-bottom: 0;
    }
    .page-header-title { font-size: 1.3rem; }
    .page-header-icon { font-size: 2rem; }
    
    .week-card { padding: 0.75rem; }
    .week-name { font-size: 0.85rem; }
    .week-date { font-size: 0.75rem; }
    
    .shape { filter: blur(30px); }
    .shape-1, .shape-2, .shape-3, .shape-4, .shape-5 {
        width: 200px !important;
        height: 200px !important;
    }
}
@media (max-width: 380px) {
    /* 1 c?t n?u màn hình quá nh? */
    .week-grid { grid-template-columns: 1fr; } 
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
  <div class="page-header">
    <i class="bi <?php echo htmlspecialchars($page_icon); ?> page-header-icon"></i>
    <h2 class="page-header-title"><?php echo htmlspecialchars($page_title); ?></h2>
    <p class="page-header-subtitle">Ch?n tu?n h?c.</p>
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 rounded-pill"><?php echo $school_year; ?></span>
  </div>

  <div class="semester-section">
    <h5 class="semester-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hourglass-split" viewBox="0 0 16 16"><path d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48zm1 0v3.17c2.134.181 3 1.48 3 1.48a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351z"/></svg> H?c k? 1
    </h5>
    <div class="week-grid">
      <?php if (!empty($weeks_hk1)): ?>
        <?php foreach ($weeks_hk1 as $index => $week): // Thêm $index ?>
          <a class="week-card" href="<?php echo htmlspecialchars($base_url . $week['id']); ?>" style="--item-index: <?php echo $index; ?>">
            <div class="week-name"><?php echo htmlspecialchars($week['ten_tuan']); ?></div>
            <div class="week-date">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar3-range mr-1" viewBox="0 0 16 16"><path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>   <path d="M7 10a1 1 0 0 0 0-2H1v2zm2-3h6V5H9a1 1 0 0 0 0 2"/></svg>
              <?php echo date('d/m', strtotime($week['ngay_bat_dau'])) . ' - ' . date('d/m', strtotime($week['ngay_ket_thuc'])); ?>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">Không có tu?n h?c nào m? cho h?c k? 1.</div>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="semester-section" style="border-top: 1px solid var(--card-border);">
    <h5 class="semester-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hourglass-bottom" viewBox="0 0 16 16"><path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5m2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702s.18.149.5.149.5-.15.5-.15v-.7c0-.701.478-1.236 1.011-1.492A3.5 3.5 0 0 0 11.5 3V2z"/></svg> H?c k? 2
    </h5>
    <div class="week-grid">
      <?php if (!empty($weeks_hk2)): ?>
        <?php 
            $baseIndex = count($weeks_hk1); // Ð?m ti?p n?i
            foreach ($weeks_hk2 as $index => $week): 
        ?>
          <a class="week-card" href="<?php echo htmlspecialchars($base_url . $week['id']); ?>" style="--item-index: <?php echo $baseIndex + $index; ?>">
            <div class="week-name"><?php echo htmlspecialchars($week['ten_tuan']); ?></div>
            <div class="week-date">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar3-range mr-1" viewBox="0 0 16 16"><path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>   <path d="M7 10a1 1 0 0 0 0-2H1v2zm2-3h6V5H9a1 1 0 0 0 0 2"/></svg>
              <?php echo date('d/m', strtotime($week['ngay_bat_dau'])) . ' - ' . date('d/m', strtotime($week['ngay_ket_thuc'])); ?>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">Không có tu?n h?c nào m? cho h?c k? 2.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="back-button-w-full max-w-6xl mx-auto px-6">
    <a href="/thidua/hocsinh" class="btn bg-transparent hover:bg-slate-600 text-slate-600 hover:text-white border border-slate-600 rounded-pill">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left mr-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg> Quay l?i
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/partials/ctv_footer.php'; ?>
