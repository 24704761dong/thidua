<?php
// FILE: xem_diem_thi_cong_khai.php (ÐÃ Ð?NG B? GLASSMORPHISM + TÍCH H?P CAPTCHA)

// Ph?i kh?i d?ng session d? file captcha_image.php có th? luu mã vào session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'TRA C?U ÐI?M THI';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title; ?></title>
<link rel="icon" type="image/x-icon" href="/thidua/public/assets/img/favicon.ico">
<script src="/thidua/public/assets/libs/sweetalert2.min.js"></script>
<style>
/* ---------------------------------- */
/* GIAO DI?N "GLASSMORPHISM" Ð?NG B?   */
/* ---------------------------------- */

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
  --color-success: #198754;
  --color-danger: #dc3545;
  --color-warning: #ffc107;
}

body {
  font-family: 'Inter', sans-serif; /* Ð?ng b? font */
  background-color: var(--bg-page);
  color: var(--text-strong);
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 2rem 0;
  position: relative; /* Thêm */
  overflow-x: hidden; /* Thêm */
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
@keyframes float {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  25% { transform: translate(50px, -50px) rotate(90deg); }
  50% { transform: translate(-30px, -100px) rotate(180deg); }
  75% { transform: translate(80px, -30px) rotate(270deg); }
}

.w-full max-w-6xl mx-auto px-4 { 
  max-width: 680px;
  animation: fadeInScale 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  padding: 1rem;
  margin-top: 2rem;
  position: relative; /* Thêm */
  z-index: 1; /* Thêm */
}

@keyframes fadeInScale { 
  from { opacity: 0; transform: translateY(20px) scale(0.95); } 
  to { opacity: 1; transform: translateY(0) scale(1); } 
}

/* Card Kính m? (Ðã d?ng b?) */
.content-card {
  border-radius: var(--border-radius-large);
  border: none;
  background: var(--card-bg);
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  backdrop-filter: blur(20px) saturate(180%);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden; /* Quan tr?ng */
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

/* Header */
.logo-img {
  max-height: 80px;
  filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
}
h2 {
  font-weight: 700;
  color: var(--text-strong); /* Ð?i sang màu ch? chính */
  text-shadow: 0 1px 2px rgba(0,0,0,0.05);
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
}
.text-muted {
  color: var(--text-muted) !important;
}

/* Form Elements (Ð?ng b?) */
.form-label {
  color: var(--text-strong);
  font-weight: 600;
  margin-bottom: 0.5rem;
}
.form-control, .form-control {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid var(--card-border);
  color: var(--text-strong);
  border-radius: var(--border-radius-small);
  transition: var(--transition-bouncy);
}
/* Ð?ng b? chi?u cao */
.form-control-lg, .form-control-lg {
    height: 52px;
    padding: 0.5rem 1rem;
    font-size: 1rem;
}
.form-control option {
  background: #fff;
  color: var(--text-strong);
}
.form-control::placeholder {
  color: var(--text-muted);
  opacity: 0.7;
}
.form-control:focus, .form-control:focus {
  background: #fff;
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
  transform: translateY(-2px);
  color: var(--text-strong);
}

/* ====== CSS CHO CAPTCHA (Ðã Thêm) ====== */
.captcha-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid var(--card-border);
    border-radius: var(--border-radius-small); /* Ð?ng b? bo góc */
    overflow: hidden;
    height: 52px; /* Ð?ng b? chi?u cao v?i .form-control-lg */
    background: rgba(255, 255, 255, 0.9);
}
.captcha-wrapper .captcha-img {
    d-flex: 1 1 auto;
    width: 100%;
    height: 100%;
    display: d-block;
    object-fit: contain;
    border: none;
    cursor: pointer; /* Cho phép b?m vào ?nh d? d?i */
}
.captcha-wrapper #refresh-captcha {
    d-flex: 0 0 50px; /* Kích thu?c nút refresh */
    width: 50px;
    height: 100%;
    padding: 0;
    margin: 0;
    border: none;
    border-left: 1px solid var(--card-border);
    border-radius: 0;
    background-color: rgba(255, 255, 255, 0.5);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    transition: backgrouncolor 0.2s ease;
}
.captcha-wrapper #refresh-captcha:hover {
    background-color: rgba(255, 255, 255, 1);
}

/* Buttons (Ð?ng b?) */
.btn.btn-sm {
    border-radius: var(--border-radius-small) !important; 
    font-weight: 600; 
    transition: var(--transition-bouncy);
    padding: 0.75rem 2rem;
    border: none;
}
/* Thêm px-4 py-1.5 text-sm (Fix l?i mobile) */
.px-4 py-1.5 text-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
}
.bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent {
  background: var(--brand-gradient);
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
  color: #fff;
}
.bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:hover, .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:focus {
  background: var(--brand-gradient);
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
  color: #fff;
}
.btn-secondary {
    background: #6c757d;
    color: #fff;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2);
}
.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
    color: #fff;
}
/* Nút phúc kh?o (Ð?ng b? style) */
.btn-warning {
  background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
  border: none;
  font-weight: 600;
  color: #fff;
  box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
}
.btn-warning:hover {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
  color: #fff;
}


/* Result Area */
#resultArea {
  animation: slideIn 0.5s ease-out;
}
@keyframes slideIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}

#resultArea .content-card {
    overflow: hidden; /* Ð?m b?o header bo góc dúng */
}
#resultArea .card-header {
  /* Dùng gradient chính c?a theme */
  background: var(--brand-gradient);
  color: #fff;
  font-weight: 600;
  padding: 1.25rem 1.5rem;
  border-bottom: none;
}
#resultArea .p-4 {
  padding: 1.5rem;
}

/* Thông tin h?c sinh (Gi? nguyên logic, d?ng b? màu) */
.student-info .info-item {
  display: flex;
  justify-content: space-between;
  padding: 0.6rem 0;
  border-bottom: 1px solid var(--card-border);
  font-size: 0.95rem;
}
.student-info .info-item:last-child {
  border-bottom: none;
}
.info-label {
  font-weight: 600;
  color: var(--text-strong);
  d-flex-basis: 40%;
}
.info-value {
  font-weight: 600;
  color: var(--text-strong);
  text-align: right;
  d-flex-basis: 60%;
  font-size: 0.95rem;
}

hr { border-color: var(--card-border); margin: 1.5rem 0; }

h5 { 
  font-weight: 600;
  color: var(--text-strong); /* Ð?i sang màu ch? chính */
  margin-bottom: 1rem;
}
h5 i {
    color: var(--brand-primary); /* Ð?i icon sang màu primary */
}

/* Danh sách di?m (Gi? nguyên logic, d?ng b? màu) */
.score-grid {
  display: flex;
  flex-direction: column;
  gap: 0; 
  margin-top: 1rem;
}
.score-card-item {
  background: transparent;
  padding: 0.6rem 0;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--card-border);
  transition: background 0.2s ease-out;
}
.score-card-item:last-child {
    border-bottom: none;
}
.score-card-item:hover {
  background: rgba(37, 99, 235, 0.04); /* Hover màu xanh nh?t */
}
.score-subject {
  font-weight: 600;
  color: var(--text-strong);
  font-size: 0.95rem; 
  margin-bottom: 0;
  text-align: left;
  d-flex-basis: 40%; 
}
.score-value {
  font-weight: 600;
  color: var(--text-strong);
  font-size: 0.95rem;
  line-height: 1.6;
  text-align: right;
  d-flex-basis: 60%;
}
/* L?p CSS cho JS (Ð?i sang bi?n) */
.text-success-custom {
    color: var(--color-success) !important;
    font-weight: 700 !important;
}
.text-danger-custom {
    color: var(--color-danger) !important;
    font-weight: 700 !important;
}

/* Modal (Ð?ng b?) */
.relative bg-white rounded-xl shadow-xl table-bordered d-flex d-flex-col {
  background: var(--card-bg);
  border: 1px solid rgba(255,255,255,0.5);
  border-radius: var(--border-radius-large);
  backdrop-filter: blur(20px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.d-flex align-items-center justify-content-between p-4 border-b rounded-t-xl {
  background: var(--brand-gradient) !important;
  color: #fff !important;
  border-top-left-radius: var(--border-radius-large);
  border-top-right-radius: var(--border-radius-large);
  border-bottom: none;
}
.d-flex align-items-center justify-content-between p-4 border-b rounded-t-xl .modal-title {
    color: #fff !important;
}
.btn-close {
  filter: brightness(0) invert(1);
  opacity: 0.7;
  transition: opacity 0.2s ease;
}
.btn-close:hover { opacity: 1; }
.p-4 space-y-4 {
  color: var(--text-strong);
  padding-bottom: 0;
}
.d-flex align-items-center justify-content-end p-4 border-t space-x-2 rounded-b-xl {
  border-top: 1px solid var(--card-border);
  padding: 1rem;
  display: flex;
  gap: 0.75rem;
}
.d-flex align-items-center justify-content-end p-4 border-t space-x-2 rounded-b-xl .btn.btn-sm {
    d-flex: 1;
    padding: 0.75rem 1rem;
}

/* Alerts (Ð?ng b?) */
.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #842029;
    border-color: rgba(220, 53, 69, 0.2);
    border-radius: var(--border-radius-small);
}
.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #664d03;
    border-color: rgba(255, 193, 7, 0.2);
    border-radius: var(--border-radius-small);
}
.alert-info {
    background-color: rgba(37, 99, 235, 0.1);
    color: #0a3694;
    border-color: rgba(37, 99, 235, 0.2);
    border-radius: var(--border-radius-small);
}

/* --- T?I UU MOBILE (Ðã s?a l?i nút b?) --- */
@media (max-width: 576px) {
  body { padding: 1rem 0; align-items: flex-start; }
  .w-full max-w-6xl mx-auto px-4 { margin-top: 1rem; padding: 0.5rem; }
  .p-4 { padding: 1rem; }
  h2 { font-size: 1.8rem; margin-top: 1rem; margin-bottom: 0.5rem; }
  .logo-img { max-height: 70px; }
  
  /* S?a l?i: Ch? áp d?ng cho nút không có .px-4 py-1.5 text-sm */
  .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:not(.px-4 py-1.5 text-sm), 
  .btn-warning:not(.px-4 py-1.5 text-sm), 
  .btn-secondary:not(.px-4 py-1.5 text-sm) {
    width: 100%;
    padding: 0.6rem 1rem;
    font-size: 0.95rem;
  }
  
  #resultArea .card-header {
    flex-direction: column;
    align-items: d-flex-start !important;
    gap: 0.5rem;
    padding: 1rem;
  }
  
  .info-item {
    flex-direction: column;
    align-items: flex-start;
    padding: 0.4rem 0;
  }
  .info-label {
    margin-bottom: 0.2rem;
    font-size: 0.9rem;
    d-flex-basis: auto;
  }
  .info-value {
    text-align: left;
    font-size: 1rem;
    width: 100%;
    d-flex-basis: auto;
  }

  .score-card-item { padding: 0.6rem 0; }
  .score-subject { font-size: 0.9rem; d-flex-basis: 40%; }
  .score-value { font-size: 0.95rem; d-flex-basis: 60%; }
  
  .shape { filter: blur(30px); }
  .shape-1, .shape-2, .shape-3 {
    width: 200px !important;
    height: 200px !important;
  }
}
</style>
        <link rel="stylesheet" href="/thidua/public/assets/css/fonts.css">
</head>

<body>

<div class="floating-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="w-full max-w-6xl mx-auto px-6 py-6">
  <div class="text-center mb-6">
    <img src="/thidua/public/assets/img/22logoapp.png" alt="Logo" class="logo-img">
    <h2><?= $page_title; ?></h2>
    <p class="text-slate-500">TRU?NG THPT BÌNH SON</p>
  </div>

  <?php if ($error_message ?? null): ?>
    <div class="p-6 mb-6 rounded-lg border bg-red-50 text-red-800 border-red-200 shadow-sm"><?= htmlspecialchars($error_message); ?></div>
  <?php elseif (empty($ds_ky_thi_cong_khai)): ?>
    <div class="p-6 mb-6 rounded-lg border bg-yellow-50 text-yellow-800 border-yellow-200 text-center shadow-sm">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle mr-1" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg> Hi?n t?i chua có k? thi nào du?c m? tra c?u di?m.
    </div>
  <?php else: ?>
    <div class="content-card">
      <div class="p-6 p-6 p-md-5">
        <form id="lookupForm" onsubmit="handleLookup(event)">
          
          <?php if (count($ds_ky_thi_cong_khai) > 1): ?>
          <div class="mb-6" id="kyThiSelectWrapper">
            <label for="kyThiSelect" class="block text-sm font-medium text-slate-700 mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-event mr-2" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>CH?N K? THI:</label>
            <select id="kyThiSelect" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 form-control-lg" onchange="updateSearchForm(this.value)" required>
              <option value="">-- Vui lòng ch?n --</option>
              <?php foreach ($ds_ky_thi_cong_khai as $id => $ky_thi): ?>
                <option
                  value="<?= $id; ?>"
                  data-method="<?= htmlspecialchars($ky_thi['phuong_thuc_tra_cuu'] ?: 'sbd'); ?>"
                  data-verify-fields='<?= htmlspecialchars(json_encode($ky_thi["phuc_khao_xac_minh"] ?? new stdClass()), ENT_QUOTES, "UTF-8"); ?>'>
                  <?= htmlspecialchars($ky_thi['ten_ky_thi']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php else: ?>
            <?php
              $onlyExam = reset($ds_ky_thi_cong_khai);
              $onlyId = key($ds_ky_thi_cong_khai);
            ?>
            <input type="hidden" id="kyThiSelect" value="<?= htmlspecialchars($onlyId); ?>"
              data-method="<?= htmlspecialchars($onlyExam['phuong_thuc_tra_cuu'] ?: 'sbd'); ?>"
              data-verify-fields='<?= htmlspecialchars(json_encode($onlyExam["phuc_khao_xac_minh"] ?? new stdClass()), ENT_QUOTES, "UTF-8"); ?>'>
            <div class="p-6 mb-6 rounded-lg border bg-cyan-50 text-cyan-800 border-cyan-200 text-center mb-6 shadow-sm font-bold">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle mr-1" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg> K? THI: <?= htmlspecialchars($onlyExam['ten_ky_thi']); ?>
            </div>
          <?php endif; ?>

          <div id="searchInputsArea" style="display:none;">
            <input type="hidden" id="searchMethodHidden" value="">
            
            <div class="mb-6">
              <label id="labelValue1" for="searchValue1" class="block text-sm font-medium text-slate-700 mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person mr-2" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg></label>
              <input type="text" id="searchValue1" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 form-control-lg" required>
            </div>
            
            <div class="mb-6" id="inputField2" style="display:none;">
              <label for="searchValue2" class="block text-sm font-medium text-slate-700 mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar mr-2" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>Ngày Sinh</label>
              <input type="text" id="searchValue2" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 form-control-lg" placeholder="YYYY-MM-DD ho?c DD/MM/YYYY">
              <small class="text-slate-500">Ch? nh?p khi tra c?u b?ng H? Tên & Ngày Sinh.</small>
            </div>
            
            <div class="flex flex-wrap -mx-3 g-2 mb-6">
                <div class="col-7">
                    <label for="captcha" class="block text-sm font-medium text-slate-700 mb-1 visually-hidden">Mã xác nh?n</label>
                    <input type="text" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 form-control-lg" name="captcha" id="captcha" 
                           placeholder="Mã xác nh?n" required autocomplete="off">
                </div>
                <div class="col-5">
                    <div class="captcha-wrapper">
                        <img src="/thidua/src/controllers/captcha_image.php" alt="CAPTCHA" id="captcha-image" 
                             class="captcha-img" loading="lazy" title="B?m d? t?i l?i ?nh">
                        <button type="button" id="refresh-captcha" class="btn btn" title="T?i l?i">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-clockwise text-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>   <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="text-center mt-6">
              <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent px-6 py-6 text-lg" id="lookupButton">
                <span id="lookupSpinner" class="spinner-border spinner-border-sm" style="display:none;" role="status" aria-hidden="true"></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search mr-1" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg> TRA C?U
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div id="resultArea" class="mt-6" style="display:none;">
      <div class="content-card">
        <div class="px-6 py-6 border-b border-slate-200 bg-slate-50 rounded-t-xl font-semibold flex justify-between items-center">
          <span id="resultHeader" class="text-lg"></span>
          <button class="btn bg-yellow-500 hover:bg-yellow-600 text-white shadow-sm border-transparent px-6 py-1.5 text-sm" id="phucKhaoButton" onclick="showVerificationModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square mr-1" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg> Phúc kh?o
          </button>
        </div>
        <div class="p-6" id="resultBody">
          </div>
      </div>
    </div>

    <div id="noResultArea" class="mt-6 p-6 mb-6 rounded-lg border bg-yellow-50 text-yellow-800 border-yellow-200 text-center shadow-sm" style="display:none;"></div>
  <?php endif; ?>

  <footer class="text-center mt-8">
    <small class="text-slate-500">&copy; <?= date('Y'); ?> - Ðoàn TNCS H? Chí Minh Tru?ng THPT Bình Son - Binh Son Edu Progress</small>
  </footer>
</div>

<div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content relative bg-white rounded-xl shadow-xl border border-slate-200 flex flex-col">
      <form id="verificationForm" onsubmit="handleVerification(event)">
        <div class="flex items-center justify-between p-6 border-b rounded-t-xl">
          <h5 class="text-lg font-semibold text-slate-900" id="verificationModalLabel"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock mr-2" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/></svg>Xác minh thông tin</h5>
          <button type="button" class="text-slate-400 hover:text-slate-500 p-2" aria-label="Close"></button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-slate-500">Vui lòng nh?p chính xác các thông tin du?i dây:</p> <div id="verificationFieldsContainer">
            </div>
          <div id="verificationError" class="mt-2 text-red-600 small" style="display:none;"></div>
        </div>
        <div class="flex items-center justify-end p-6 border-t space-x-2 rounded-b-xl">
          <button type="button" class="btn bg-slate-600 hover:bg-slate-700 text-white shadow-sm border-transparent">H?y</button>
          <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent" id="verifyButton">
            <span id="verifySpinner" class="spinner-border spinner-border-sm" style="display:none;" role="status" aria-hidden="true"></span>
            Xác minh
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
//--- Bi?n toàn c?c (Ðã c?p nh?t) ---
const methodLabels = { sbd: 'S? Báo Danh', cccd: 'S? CCCD', moet: 'Mã h?c sinh', ten_ngaysinh: 'H? và Tên' };
const searchInputsArea = document.getElementById('searchInputsArea');
const inputField2 = document.getElementById('inputField2');
const labelValue1 = document.getElementById('labelValue1');
const searchValue1 = document.getElementById('searchValue1');
const searchValue2 = document.getElementById('searchValue2');
const searchMethodHidden = document.getElementById('searchMethodHidden');
const resultArea = document.getElementById('resultArea');
const resultBody = document.getElementById('resultBody');
const resultHeader = document.getElementById('resultHeader');
const lookupSpinner = document.getElementById('lookupSpinner');
const lookupButton = document.getElementById('lookupButton');
const noResultArea = document.getElementById('noResultArea');
const phucKhaoButton = document.getElementById('phucKhaoButton');
// === THÊM BI?N CAPTCHA ===
const refreshBtn = document.getElementById('refresh-captcha');
const captchaImg = document.getElementById('captcha-image');

const friendlyFieldNames = {
    'ngay_sinh': 'Ngày Sinh',
    'cccd': 'S? CCCD',
    'sbd': 'S? Báo Danh',
    'ma_hoc_sinh': 'Mã H?c Sinh',
    'moet': 'Mã H?c Sinh',
    'ho_ten': 'H? và Tên',
    'lop': 'L?p'
};

const verificationModalEl = document.getElementById('verificationModal');
const verificationModal = null /* Removed Bootstrap Modal */;
const verificationFieldsContainer = document.getElementById('verificationFieldsContainer');
const verificationError = document.getElementById('verificationError');
const verifyButton = document.getElementById('verifyButton');
const verifySpinner = document.getElementById('verifySpinner');

let currentKyThiId = null, currentKthsId = null, currentVerifyFieldsConfig = {};

//--- Kh?i ch?y (Ðã c?p nh?t) ---
document.addEventListener('DOMContentLoaded', () => {
    // === THÊM LOGIC REFRESH CAPTCHA ===
    const refreshCaptcha = () => {
        if (captchaImg) {
            captchaImg.src = '/thidua/src/controllers/captcha_image.php?' + Date.now();
        }
    };
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshCaptcha);
    }
    if (captchaImg) {
        captchaImg.addEventListener('click', refreshCaptcha); // Cho phép b?m vào ?nh
        captchaImg.addEventListener('error', () => setTimeout(refreshCaptcha, 300));
    }
    // ===================================

    const select = document.getElementById('kyThiSelect');
    if (select && select.tagName === 'SELECT' && select.options.length === 2) {
        const onlyOption = select.options[1];
        select.value = onlyOption.value;
        updateSearchForm(onlyOption.value);
        document.getElementById('kyThiSelectWrapper').style.display = 'none';
    } else if (select && select.tagName === 'INPUT') {
        updateSearchForm(select.value);
    }
});

//--- Hàm x? lý tra c?u (Ðã c?p nh?t) ---
function updateSearchForm(kyThiId) {
    searchInputsArea.style.display = 'none';
    inputField2.style.display = 'none';
    if (!kyThiId) return;
    currentKyThiId = kyThiId;
    const opt = document.querySelector(`#kyThiSelect[value='${kyThiId}'],#kyThiSelect option[value='${kyThiId}']`);
    if (!opt) return;
    try { currentVerifyFieldsConfig = JSON.parse(opt.dataset.verifyFields || '{}'); }
    catch (e) { currentVerifyFieldsConfig = {}; }
    const method = opt.dataset.method || 'sbd';
    searchMethodHidden.value = method;
    let labelIcon = '';
    if (method === 'sbd') labelIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-card-checklist mr-2" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>   <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg>';
    else if (method === 'cccd') labelIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-vcard mr-2" viewBox="0 0 16 16"><path d="M5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4m4-2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5M9 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 9 8m1 2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5"/>   <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H8.96q.04-.245.04-.5C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 1 1 12z"/></svg>';
    else if (method === 'moet') labelIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge mr-2" viewBox="0 0 16 16"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>   <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492z"/></svg>';
    else if (method === 'ten_ngaysinh') labelIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person mr-2" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg>';
    
    labelValue1.innerHTML = labelIcon + (methodLabels[method] || 'S? Báo Danh') + ':';
    searchValue1.placeholder = `Nh?p ${methodLabels[method]}...`;
    if (method === 'ten_ngaysinh') { inputField2.style.display = 'd-block'; searchValue2.required = true; }
    else { inputField2.style.display = 'none'; searchValue2.required = false; }
    searchInputsArea.style.display = 'd-block';
}

async function handleLookup(e) {
    e.preventDefault();
    
    // 1. L?y giá tr? CAPTCHA
    const captchaValue = document.getElementById('captcha').value.trim();

    // 2. Ki?m tra CAPTCHA (phía client)
    if (!captchaValue) {
        Swal.fire('Thi?u thông tin', 'Vui lòng nh?p Mã xác nh?n.', 'warning');
        return;
    }

    const kyThiId = currentKyThiId, 
          method = searchMethodHidden.value, 
          val1 = searchValue1.value.trim(), 
          val2 = searchValue2.value.trim();

    if (!kyThiId || !method || !val1) return Swal.fire('Thi?u thông tin', 'Vui lòng nh?p d?y d? d? li?u.', 'warning');
    
    lookupButton.disabled = true; 
    lookupSpinner.style.display = 'd-inline-block'; 
    resultArea.style.display = 'none'; 
    noResultArea.style.display = 'none';

    try {
        const res = await fetch('/thidua/api/tra-cuu-diem', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ 
                ky_thi_id: kyThiId, 
                search_method: method, 
                search_value1: val1, 
                search_value2: (method === 'ten_ngaysinh' ? val2 : null),
                'captcha': captchaValue // 3. G?i mã CAPTCHA lên server
            }) 
        });
        
        const result = await res.json();
        
        // 4. Reset CAPTCHA sau m?i l?n tra c?u
        if (captchaImg) captchaImg.src = '/thidua/src/controllers/captcha_image.php?' + Date.now();
        document.getElementById('captcha').value = ''; // Xóa ô nh?p captcha

        if (!res.ok || !result.success) throw new Error(result.message || 'L?i tra c?u');
        
        if (result.found) { 
            currentKthsId = result.kths_id;
            displayResults(result.data); 
        } else { 
            noResultArea.textContent = result.message || 'Không tìm th?y thí sinh.'; 
            noResultArea.style.display = 'd-block'; 
        }
    } catch (err) { 
        noResultArea.textContent = 'L?i: ' + err.message; 
        noResultArea.style.display = 'd-block'; 
    } finally { 
        lookupButton.disabled = false; 
        lookupSpinner.style.display = 'none'; 
    }
}

//--- Các hàm khác (displayResults, showVerificationModal, handleVerification) ---
// ... (Gi? nguyên toàn b? các hàm này) ...
function displayResults(data) {
    resultHeader.textContent = 'K?t qu? tra c?u - ' + (data.ky_thi || '');
    let infoHtml = '<div class="student-info">';
    for (const k in data) {
        if (k !== 'Ði?m thi' && k !== 'ky_thi') {
            infoHtml += `
                <div class="info-item">
                    <span class="info-label">${k}</span>
                    <span class="info-value">${data[k]}</span>
                </div>
            `;
        }
    }
    infoHtml += '</div><hr class="my-6">';
    let scoreHtml = '<h5><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-star-fill mr-2" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>Ði?m các môn</h5>';
    if (data['Ði?m thi'] && Object.keys(data['Ði?m thi']).length > 0) {
        scoreHtml += '<div class="score-grid">';
        for (const mon in data['Ði?m thi']) {
            const value = data['Ði?m thi'][mon];
            let valueClass = '';
            if (mon.toLowerCase().includes('k?t qu?')) {
                const lowerValue = String(value).toLowerCase();
                if (lowerValue === 'd?u' || lowerValue === 'd?t' || lowerValue === 'lên l?p') {
                    valueClass = 'text-success-custom';
                } 
                else if (lowerValue === 'h?ng' || lowerValue === 'h?ng' || lowerValue === 'r?t' || lowerValue === 'không d?t' || lowerValue === '? l?i l?p') {
                    valueClass = 'text-danger-custom';
                }
            }
            scoreHtml += `
                <div class="score-card-item">
                    <span class="score-subject">${mon}</span>
                    <span class="score-value ${valueClass}">${value}</span>
                </div>
            `;
        }
        scoreHtml += '</div>';
    } else {
        scoreHtml += '<p class="text-slate-500">Không có d? li?u di?m thi cho k? thi này.</p>';
    }
    resultBody.innerHTML = infoHtml + scoreHtml;
    resultArea.style.display = 'd-block';
    phucKhaoButton.style.display = Object.keys(currentVerifyFieldsConfig).length > 0 ? 'd-inline-block' : 'none';
}
function showVerificationModal() {
    verificationFieldsContainer.innerHTML = '';
    verificationError.style.display = 'none';
    if (!currentVerifyFieldsConfig || Object.keys(currentVerifyFieldsConfig).length === 0) {
        Swal.fire('L?i', 'K? thi này không h? tr? phúc kh?o tr?c tuy?n.', 'error');
        return;
    }
    let fieldsHtml = '';
    for (const fieldKey in currentVerifyFieldsConfig) {
        const fieldLabel = friendlyFieldNames[fieldKey] || fieldKey; 
        let inputType = 'text';
        let placeholder = '';
        let iconHtml = ''; 
        if (fieldKey.includes('ngay_sinh')) {
            inputType = 'text'; 
            placeholder = 'VD: 2005-12-31 ho?c 31/12/2005';
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-date mr-2" viewBox="0 0 16 16"><path d="M6.445 11.688V6.354h-.633A13 13 0 0 0 4.5 7.16v.695c.375-.257.969-.62 1.258-.777h.012v4.61zm1.188-1.305c.047.64.594 1.406 1.703 1.406 1.258 0 2-1.066 2-2.871 0-1.934-.781-2.668-1.953-2.668-.926 0-1.797.672-1.797 1.809 0 1.16.824 1.77 1.676 1.77.746 0 1.23-.376 1.383-.79h.027c-.004 1.316-.461 2.164-1.305 2.164-.664 0-1.008-.45-1.05-.82zm2.953-2.317c0 .696-.559 1.18-1.184 1.18-.601 0-1.144-.383-1.144-1.2 0-.823.582-1.21 1.168-1.21.633 0 1.16.398 1.16 1.23"/>   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>';
        } else if (fieldKey === 'cccd') {
            inputType = 'number';
            placeholder = 'Nh?p s? CCCD c?a b?n...';
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-credit-card mr-2" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>   <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/></svg>';
        } else if (fieldKey === 'sbd') {
            placeholder = 'Nh?p s? báo danh...';
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-text mr-2" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>   <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/></svg>';
        } else if (fieldKey.includes('ho_ten')) {
            placeholder = 'Nh?p h? và tên d?y d?...';
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person mr-2" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg>';
        } else if (fieldKey.includes('ma_hoc_sinh') || fieldKey.includes('moet')) {
            placeholder = 'Nh?p mã h?c sinh...';
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge mr-2" viewBox="0 0 16 16"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>   <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492z"/></svg>';
        } else if (fieldKey === 'lop') {
            placeholder = 'Nh?p tên l?p, ví d?: 11A1';
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-easel mr-2" viewBox="0 0 16 16"><path d="M8 0a.5.5 0 0 1 .473.337L9.046 2H14a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1.85l1.323 3.837a.5.5 0 1 1-.946.326L11.092 11H8.5v3a.5.5 0 0 1-1 0v-3H4.908l-1.435 4.163a.5.5 0 1 1-.946-.326L3.85 11H2a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1h4.954L7.527.337A.5.5 0 0 1 8 0M2 3v7h12V3z"/></svg>';
        } else {
            placeholder = `Nh?p ${fieldLabel.toLowerCase()}...`;
            iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-text mr-2" viewBox="0 0 16 16"><path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>   <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>   <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/></svg>';
        }
        fieldsHtml += `
            <div class="mb-6">
                <label for="verify_${fieldKey}" class="block text-sm font-medium text-slate-700 mb-1 font-semibold">${iconHtml}${fieldLabel}:</label>
                <input type="${inputType}" id="verify_${fieldKey}" name="${fieldKey}" 
                       class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" placeholder="${placeholder}" required>
            </div>
        `;
    }
    verificationFieldsContainer.innerHTML = fieldsHtml;
    verificationModal.show();
}
async function handleVerification(e) {
    e.preventDefault();
    verificationError.style.display = 'none';
    verifyButton.disabled = true;
    verifySpinner.style.display = 'd-inline-block';
    const form = e.target;
    const formData = new FormData(form);
    const verificationData = {};
    formData.forEach((value, key) => {
        verificationData[key] = value.trim();
    });
    const payload = {
        ky_thi_id: currentKyThiId,
        kths_id: currentKthsId,
        verification_data: verificationData
    };
    try {
        const res = await fetch('/thidua/api/xac-minh-phuc-khao', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload) 
        });
        const result = await res.json();
        if (!res.ok || !result.success) {
            let errorMsg = result.message || 'Thông tin xác minh không chính xác.';
            if (result.errors && Array.isArray(result.errors)) {
                errorMsg += '<br> - ' + result.errors.join('<br> - ');
            }
            throw new Error(errorMsg);
        }
        verificationModal.hide();
        Swal.fire({
            title: 'Xác minh thành công!',
            text: result.message || 'Ðang chuy?n d?n trang dang ký phúc kh?o...',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            willClose: () => {
                window.location.href = result.redirect_url || '/thidua/nop-don-phuc-khao';
            }
        });
    } catch (err) {
        verificationError.innerHTML = 'L?i: ' + err.message;
        verificationError.style.display = 'd-block';
    } finally {
        verifyButton.disabled = false;
        verifySpinner.style.display = 'none';
    }
}
</script>
</body>
</html>
