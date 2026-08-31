<?php
// File: src/views/ctv_nhap_nhat_ky.php (PHIÊN B?N NÂNG C?P "WINDOWS 11" + CLOUD HYBRID)
// NÂNG C?P V4: Drag & Drop + Progress Bar + Inline Errors

$page_title = 'Nh?p S? Nh?t K?';
require_once __DIR__ . '/partials/ctv_header.php';

$is_locked = ($nhat_ky['trang_thai'] === 'da_duyet');

function is_image($file_type) {
    return strpos($file_type, 'image/') === 0;
}
// === THÊM HÀM M?I (Ð?ng b? v?i admin) ===
function is_pdf_by_type($file_type) {
    return strtolower($file_type) === 'application/pdf';
}
?>
<link href="/thidua/public/assets/libs/fancybox.css" rel="stylesheet">
<style>
/* ====== GIAO DI?N CHUNG (Ð?ng b?) ====== */
:root {
  --brand-primary: #2563eb;
  --brand-accent: #60a5fa;
  --brand-gradient: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  --primary-brand: #2563eb;
  --primary-light: rgba(37, 99, 235, 0.1);
  --text-strong: #1d2d35;
  --text-normal: #495057;
  --text-muted: #64748b;
  --bg-main: #f4f7f9;
  --bg-page: #f4f7f9;
  --border-color: rgba(0, 0, 0, 0.06);
  --card-bg: rgba(255, 255, 255, 0.85);
  --card-shadow: 0 8px 24px rgba(0,0,0,0.08);
  --shadow-soft: 0 8px 25px rgba(0,0,0,0.06);
  --shadow-medium: 0 12px 30px rgba(0,0,0,0.09);
  --success: #198754;
  --warning: #ffc107;
  --danger: #dc3545;
  --border-radius-large: 1.5rem;
  --border-radius-medium: 0.75rem;
  --border-radius-small: 0.5rem;
  --transition-smooth: all 0.3s ease;
}

body {
  font-family: 'Inter', sans-serif;
  background-color: var(--bg-main);
  overflow-x: hidden;
  position: relative;
}

.page-w-full max-w-6xl mx-auto px-4 { 
  max-width: 900px; 
  margin: 1rem auto; 
  padding: 0 1rem;
  position: relative;
  z-index: 1;
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
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  25% { transform: translate(50px, -50px) rotate(90deg); }
  50% { transform: translate(-30px, -100px) rotate(180deg); }
  75% { transform: translate(80px, -30px) rotate(270deg); }
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

.page-header { 
  margin-bottom: 1.5rem; 
  padding: 0 .5rem; 
  animation: fadeUp 0.8s ease forwards;
  opacity: 0;
}
.page-header h3 {
  font-weight: 800;
  font-size: 1.75rem;
  color: var(--brand-primary); /* Ð?i #2563eb thành bi?n --brand-primary cho nh?t quán */
  
  /* Các thu?c tính stroke và shadow này cung không còn c?n thi?t n?a */
  /* -webkit-text-stroke: 0.3px rgba(0,0,0,0.15); */
  /* text-shadow: 0 0 0.4px rgba(0,0,0,0.2); */ 
}


.page-header .badge { 
  font-size: .85rem; 
  font-weight: 600; 
  padding: 0.4em 0.9em; 
  border-radius: 50px;
  background: var(--brand-gradient) !important;
  border: none;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
}

.summary-card {
  background: var(--card-bg);
  backdrop-filter: blur(20px) saturate(180%);
  border: none;
  border-radius: var(--border-radius-large);
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  position: relative;
  animation: fadeUp 0.8s ease 0.1s forwards;
  opacity: 0;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
@media (max-width: 767px) {
  .proof-item .delete-proof-btn {
    opacity: 1 !important;
    transform: scale(1) !important;
  }
} 
.summary-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--brand-gradient);
  opacity: 0;
  transition: opacity 0.4s ease;
  border-radius: var(--border-radius-large) var(--border-radius-large) 0 0;
}

.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}

.summary-card:hover::before {
  opacity: 1;
}
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4,1fr);
  gap: 1.25rem;
  text-align: center;
}
.summary-item .summary-value {
  font-size: 2.4rem;
  font-weight: 800;
  line-height: 1.1;
  background: var(--brand-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  transition: transform 0.3s ease;
}
.summary-item:hover .summary-value {
  transform: scale(1.1);
}
.summary-item .summary-label { 
  font-weight: 600; 
  color: var(--text-muted); 
  font-size: .9rem; 
  margin-top: 0.5rem;
}

.section-card {
  background: var(--card-bg);
  backdrop-filter: blur(20px) saturate(180%);
  border: none;
  border-radius: var(--border-radius-large);
  margin-bottom: 1.5rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  animation: fadeUp 0.8s ease forwards;
  opacity: 0;
}

.section-card:nth-child(1) { animation-delay: 0.2s; }
.section-card:nth-child(2) { animation-delay: 0.3s; }
.section-card:nth-child(3) { animation-delay: 0.4s; }
.section-card:nth-child(4) { animation-delay: 0.5s; }

.section-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--brand-gradient);
  opacity: 0;
  transition: opacity 0.4s ease;
}

.section-card:hover { 
  transform: translateY(-6px) scale(1.01);
  box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}
.section-card:hover::before {
  opacity: 1;
}

.section-card-header {
  padding: 1rem 1.25rem;
  font-weight: 700;
  font-size: 1.1rem;
  color: var(--text-strong);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: .75rem;
  background: rgba(255, 255, 255, 0.5);
}

.section-card-header i { 
  background: var(--brand-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.2rem;
}

.section-p-4 { 
  padding: 1.25rem; 
  background: linear-gradient(to bottom, rgba(250, 251, 252, 0.3), rgba(255, 255, 255, 0.5));
}

.input-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(130px,1fr));
  gap: 1rem;
}
.form-control {
  border-radius: var(--border-radius-small);
  background-color: rgba(255, 255, 255, 0.9);
  border: 1px solid var(--border-color);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.form-control:focus {
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
  background-color: #fff;
  transform: translateY(-2px);
}
.form-control[type=number]::-webkit-inner-spin-button,
.form-control[type=number]::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
.form-control[type=number]{ -moz-appearance:textfield; }

.proof-section-header {
  border-top: 1px solid var(--border-color);
  margin-top: 1.25rem;
  padding-top: 1.25rem;
}
.proof-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill,minmax(140px,1fr));
  gap: 1rem;
  margin-top: 1rem;
}
.proof-item {
  position: relative;
  border-radius: var(--border-radius-medium);
  overflow: hidden;
  border: 1px solid var(--border-color);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
}

.proof-item:hover {
  transform: translateY(-6px) scale(1.05);
  box-shadow: 0 12px 25px rgba(37, 99, 235, 0.2);
  border-color: rgba(37, 99, 235, 0.3);
}
.thumbnail-w-full max-w-6xl mx-auto px-4 {
  height: 110px;
  background-color: var(--bg-main);
  display: flex;
  align-items: center;
  justify-content: center;
}
.thumbnail-img { width: 100%; height: 100%; object-fit: cover; }
.file-icon { font-size: 2.5rem; color: var(--text-muted); }
.file-name {
  padding: .6rem;
  font-size: .8rem;
  font-weight: 500;
  color: var(--text-normal);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  background-color: #fff;
}
/* === FIX v? trí icon xóa n?m trong khung === */
.delete-proof-btn {
  position: absolute;
  top: 6px;               /* n?m trong vùng ?nh */
  right: 6px;             /* không b? c?t bo tròn */
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background-color: var(--danger);
  color: #fff;
  border: none;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0;
  transform: scale(.9);
  transition: all 0.25s ease;
  z-index: 3;
}
/* ==== BADGE TR?NG THÁI NH?T KÝ ==== */
.badge-status {
  font-size: .9rem;
  font-weight: 600;
  padding: 0.45em 1.1em;
  border-radius: 999px;
  display: d-inline-block;
  border: none;
  box-shadow: 0 2px 6px rgba(0,0,0,0.12);
  transition: all 0.3s ease;
}

/* === B?n nháp === */
.badge-draft {
  background: linear-gradient(135deg, #facc15, #fbbf24); /* vàng */
  color: #111827; /* ch? den */
}

/* === Ch? duy?t === */
.badge-pending {
  background: linear-gradient(135deg, #6366f1, #8b5cf6); /* tím xanh */
  color: #fff; /* ch? tr?ng */
}

/* === Ðã duy?t === */
.badge-approved {
  background: linear-gradient(135deg, #10b981, #059669); /* xanh ng?c */
  color: #fff; /* ch? tr?ng */
}

/* === Hi?u ?ng hover nh? === */
.badge-status:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  opacity: 0.95;
}

/* === N?n t?i (dark mode) === */
[data-theme="dark"] .badge-draft {
  background: linear-gradient(135deg, #eab308, #facc15);
  color: #111827;
}
[data-theme="dark"] .badge-pending,
[data-theme="dark"] .badge-approved {
  filter: brightness(1.1);
  color: #fff;
}

.proof-item:hover .delete-proof-btn {
  opacity: 1;
  transform: scale(1);
}

/* Hi?n th? luôn trên mobile */
@media (max-width: 767px) {
  .proof-item .delete-proof-btn {
    opacity: 1 !important;
    transform: scale(1) !important;
  }
}
/* ====== S?A L?I DARK MODE (Thêm vào) ====== */
[data-theme="dark"] .drop-zone-text {
    color: var(--text-secondary-dark);
}
[data-theme="dark"] .drop-zone .text-muted {
    color: var(--text-secondary-dark); /* Dùng màu ch? ph? sáng */
    opacity: 0.8; /* Có th? thêm dòng này d? làm nó m? hon m?t chút */
}
[data-theme="dark"] .drop-zone .text-muted {
    color: var(--text-secondary-dark) !important;
}
/* 1. S?a tiêu d? "NH?T K? TR?C TUY?N" (b? gradient, dùng màu sáng) */
[data-theme="dark"] .page-header h3 {
    background: none;
    -webkit-background-clip: unset;
    -webkit-text-fill-color: unset;
    color: var(--text-primary-dark); /* Màu ch? sáng */
    -webkit-text-stroke: 0; /* T?t stroke */
    text-shadow: none; /* T?t shadow */
}

/* 2. S?a ch? "L?p: 11A1 - TU?N 9" */
[data-theme="dark"] .page-header .text-muted {
    color: var(--text-secondary-dark) !important;
}

/* 3. S?a ch? "T?NG H?P" (dang b? ?n) */
[data-theme="dark"] .summary-card .text-black {
    color: var(--text-primary-dark) !important;
}

/* 4. S?a n?n header c?a card "S? Ð?u Bài..." (dang b? tr?ng) */
[data-theme="dark"] .section-card-header {
    background: rgba(30, 41, 59, 0.7);
    color: var(--text-primary-dark);
}

/* 5. S?a label "T?t", "Khá", "Trung Bình", "Y?u" (dang b? ?n) */
[data-theme="dark"] .form-label {
    color: var(--text-secondary-dark);
}
/* =================================================== */
/* ====== NÂNG C?P V4: DROP ZONE & UPLOAD PREVIEW ====== */
.drop-zone {
    display: d-block;
    border: 2px dashed var(--border-color);
    border-radius: var(--border-radius-medium);
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
    background: rgba(255, 255, 255, 0.5);
}
.drop-zone:hover, .drop-zone.drag-over {
    border-color: var(--brand-primary);
    background: var(--primary-light);
}
.drop-zone-text {
    color: var(--text-muted);
    font-weight: 500;
}
.drop-zone-text i {
    font-size: 1.5rem;
    color: var(--brand-primary);
    display: d-block;
    margin-bottom: 0.5rem;
}
.upload-proof-input {
    display: none; /* Gi?u input di */
}

.upload-preview-list {
    margin-top: 1rem;
    display: grid;
    gap: 1rem;
}
.upload-preview-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: var(--border-radius-small);
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 10px rgba(0,0,0,0.04);
}
.upload-thumbnail {
    width: 40px;
    height: 40px;
    border-radius: var(--border-radius-small);
    object-fit: cover;
    background-color: var(--bg-main);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    flex-shrink: 0;
}
.upload-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: var(--border-radius-small);
}
.upload-info {
    d-flex-grow: 1;
    overflow: hidden;
}
.upload-filename {
    font-size: 0.85rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.upload-status {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.upload-error {
    font-size: 0.75rem;
    color: var(--danger);
    font-weight: 500;
}
.progress {
    height: 6px;
    border-radius: 6px;
    overflow: hidden;
    background-color: var(--border-color);
    margin-top: 4px;
}
.progress-bar {
    height: 100%;
    background: var(--brand-gradient);
    width: 0%;
    transition: width 0.3s ease;
}
.upload-preview-item.is-error {
    border-color: rgba(220, 53, 69, 0.3);
    background: rgba(220, 53, 69, 0.05);
}
/* ====== K?T THÚC NÂNG C?P V4 CSS ====== */


.action-buttons-footer {
  display: flex; flex-wrap: wrap;
  justify-content: d-flex-end;
  gap: .75rem;
  margin-top: 1.5rem;
  padding: 0 .5rem;
}
.action-buttons-footer .btn.btn-sm {
  border-radius: var(--border-radius-small);
  font-weight: 600;
  padding: .6rem 1.2rem;
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

.btn-secondary {
  background: #6c757d;
  border: none;
  color: white;
}

.btn-secondary:hover {
  background: var(--brand-gradient);
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600 {
  border-color: var(--brand-primary);
  color: var(--brand-primary);
}

.bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600:hover {
  background: var(--brand-gradient);
  border-color: transparent;
  color: white;
  transform: translateY(-3px) scale(1.05);
}

/* ====== KEYFRAMES (Ð?ng b?) ====== */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

[data-theme="dark"] .summary-card,
[data-theme="dark"] .section-card {
  background: rgba(30, 41, 59, 0.85);
  border-color: rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
}

[data-theme="dark"] .section-p-4 {
  background: linear-gradient(to bottom, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.8));
}

[data-theme="dark"] .form-control {
  background-color: rgba(30, 41, 59, 0.9);
  border-color: rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
}

[data-theme="dark"] .proof-item {
  background: rgba(30, 41, 59, 0.9);
  border-color: rgba(255, 255, 255, 0.1);
}

/* Dark mode cho Drop Zone & Preview */
[data-theme="dark"] .drop-zone {
    background: rgba(30, 41, 59, 0.5);
    border-color: rgba(255, 255, 255, 0.1);
}
[data-theme="dark"] .drop-zone:hover, 
[data-theme="dark"] .drop-zone.drag-over {
    border-color: var(--brand-accent);
    background: rgba(37, 99, 235, 0.2);
}
[data-theme="dark"] .upload-preview-item {
    background: rgba(30, 41, 59, 0.9);
    border-color: rgba(255, 255, 255, 0.1);
}
[data-theme="dark"] .upload-thumbnail {
    background-color: rgba(15, 23, 42, 0.8);
}


/* === RESPONSIVE === */
@media (max-width: 991px){
  .page-w-full max-w-6xl mx-auto px-4 { padding: 0 .75rem; }
  .summary-grid { grid-template-columns: repeat(2,1fr); gap: .75rem; }
  .summary-item .summary-value { font-size: 2.1rem; }
}

@media (max-width: 767px){
  .page-header { flex-direction: column; align-items: center; text-align: center; gap:.5rem; }
  .summary-card { padding: 1rem; }
  .section-p-4 { padding: 1rem; }
  .input-grid { grid-template-columns: repeat(2,1fr); }
  .proof-gallery { grid-template-columns: repeat(auto-fill,minmax(120px,1fr)); }
  .action-buttons-footer { justify-content: center; }
  .action-buttons-footer .btn.btn-sm { d-flex: 1; min-width: 120px; text-align: center; }
  .shape { filter: blur(30px); }
  .shape-1, .shape-2, .shape-3, .shape-4, .shape-5 {
    width: 200px !important;
    height: 200px !important;
  }
}

@media (max-width:480px){
  .summary-grid { grid-template-columns: repeat(2,1fr); }
  .input-grid { grid-template-columns: 1fr; }
  .proof-gallery { grid-template-columns: repeat(auto-fill,minmax(100px,1fr)); }
  .thumbnail-w-full max-w-6xl mx-auto px-4 { height: 90px; }
  .file-icon { font-size: 2rem; }
  .page-header h3 { font-size: 1.4rem; }
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
    <div class="page-header flex justify-between items-center flex-wrap">
        <div>
            <h3 class="mb-0">NH?T K? TR?C TUY?N </h3>
            
            <p class="text-slate-500 mb-0">L?p: <?php echo htmlspecialchars($ten_lop); ?> - <?php echo htmlspecialchars($ten_tuan); ?></p>
        </div>
        <div>
            <?php
$status_badge = 'badge-status badge-draft';
$status_text = 'B?n Nháp';
if ($nhat_ky['trang_thai'] === 'da_gui') { 
    $status_badge = 'badge-status badge-pending'; 
    $status_text = 'Ch? Duy?t'; 
}
if ($nhat_ky['trang_thai'] === 'da_duyet') { 
    $status_badge = 'badge-status badge-approved'; 
    $status_text = 'Ðã Duy?t'; 
}
?>
<span class="<?php echo $status_badge; ?>"><?php echo $status_text; ?></span>

        </div>
    </div>
    
    <?php if ($nhat_ky['ghi_chu_admin']): ?>
        <div class="p-6 mb-6 rounded-lg border bg-yellow-50 text-yellow-800 border-yellow-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle-fill mr-2" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
            <strong>Admin t? ch?i:</strong> <?php echo htmlspecialchars($nhat_ky['ghi_chu_admin']); ?>
        </div>
    <?php endif; ?>

    <div class="summary-card">
        <h5 class="text-center mb-6 font-bold text-black" style="opacity: 0.9;">T?NG H?P</h5>
        <div class="summary-grid">
            <div class="summary-item"><div id="total-tot" class="summary-value">0</div><div class="summary-label">Ti?t T?t</div></div>
            <div class="summary-item"><div id="total-kha" class="summary-value">0</div><div class="summary-label">Ti?t Khá</div></div>
            <div class="summary-item"><div id="total-tb" class="summary-value">0</div><div class="summary-label">Ti?t TB</div></div>
            <div class="summary-item"><div id="total-yeu" class="summary-value">0</div><div class="summary-label">Ti?t Y?u</div></div>
        </div>
    </div>

    <?php
    $sections = [
        'sdb_ck' => ['title' => 'S? Ð?u Bài Chính Khóa', 'icon' => 'bi-book-half'],
        //'sdb_tt' => ['title' => 'S? Ð?u Bài Tang Ti?t', 'icon' => 'bi-book'], b?t dòng này lên d? hi?n th? ra ô nh?p s? tang ti?t
        'sdb_nk' => ['title' => 'S? Ð?u Bài Ngo?i Khóa', 'icon' => 'bi-briefcase-fill'],
        'khac'   => ['title' => 'S? Nh?t K? & Minh Ch?ng Khác', 'icon' => 'bi-journal-check']
    ];
    foreach ($sections as $key => $section):
        $detail = $details[$key] ?? null;
    ?>
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi <?php echo $section['icon']; ?>"></i>
            <span><?php echo $section['title']; ?></span>
        </div>
        <div class="section-p-4">
            <?php if ($key !== 'khac'): ?>
                <div class="input-grid">
                    <?php
                    $fields = ['so_tiet_tot' => 'T?t', 'so_tiet_kha' => 'Khá', 'so_tiet_tb' => 'Trung Bình', 'so_tiet_yeu' => 'Y?u'];
                    foreach ($fields as $field_key => $field_label):
                    ?>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1 small"><?php echo $field_label; ?></label>
                        <input type="number" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 auto-save-input" 
                               data-nhat-ky-id="<?php echo $nhat_ky_id; ?>"
                               data-loai-so="<?php echo $key; ?>"
                               name="<?php echo $field_key; ?>"
                               value="<?php echo ($detail[$field_key] ?? null) ?: ''; ?>"
                               placeholder="0"
                               min="0"
                               <?php if ($is_locked) echo 'disabled'; ?>>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="proof-section-header">
                <div class="flex justify-between items-center">
                    <h6 class="mb-0 small text-slate-500">MINH CH?NG</h6>
                    </div>
            </div>

            <?php if (!$is_locked): ?>
            <label class="drop-zone" for="upload-proof-<?php echo $key; ?>" 
                   data-nhat-ky-id="<?php echo $nhat_ky_id; ?>" 
                   data-loai-minh-chung="<?php echo $key; ?>">
                
                <span class="drop-zone-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-up-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0z"/></svg>
                    Kéo & th? file vào dây, ho?c <span class="text-primary-600">ch?n file</span>
                    <br><small class="text-slate-500">(T?i da 7MB / file. Ch? nh?n ?nh, PDF)</small>
                </span>
                
                <input type="file" id="upload-proof-<?php echo $key; ?>" class="upload-proof-input" 
                       multiple 
                       accept="image/*,application/pdf">
            </label>
            
            <div class="upload-preview-list" id="upload-preview-<?php echo $key; ?>"></div>
            <?php endif; ?>

            <div class="proof-gallery" id="proof-list-<?php echo $key; ?>">
                <?php foreach (($proofs[$key] ?? []) as $proof): ?>
                
                    <?php
                        // --- LOGIC HYBRID (Gi? nguyên) ---
                        $file = $proof;
                        $gallery_group = "gallery-" . $key;
                        $name = htmlspecialchars($file['original_filename']);
                        $attrs = "data-fancybox='{$gallery_group}' data-caption='{$name}'";
                        $is_cloud_file = ($file['storage_driver'] === 'cloud');
                        
                        if ($is_cloud_file) {
                            $path = "/thidua/api/get-presigned-url?key=" . urlencode($file['cloud_key']);
                            if (is_pdf_by_type($file['file_type'])) { $attrs .= " data-type='pdf'"; } 
                            else { $attrs .= " data-type='image'"; }
                        } else {
                            $path = "/thidua/" . htmlspecialchars($file['file_path']);
                            if (is_pdf_by_type($file['file_type'])) { $attrs .= " data-type='pdf'"; }
                            else { $attrs .= " data-type='image'"; }
                        }
                    ?>

                    <div class="proof-item" id="proof-item-<?php echo $proof['id']; ?>">
                        
                        <?php if ($is_cloud_file): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-check-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 4.854-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg>
                        <?php endif; ?>

                        <a href="<?= $path ?>" 
                           title="<?= $name ?>"
                           <?= $attrs ?>
                           class="proof-item-link"
                           style="<?= $is_cloud_file ? 'opacity: 0.7;' : '' // Làm m? file dã lên mây ?>"
                           >
                            <div class="thumbnail-w-full max-w-6xl mx-auto px-6">
                                <?php if (is_image($file['file_type'])):
                                    $display_src = '';
                                    if (!$is_cloud_file && !empty($file['thumbnail_path'])) {
                                        $display_src = "/thidua/" . htmlspecialchars($file['thumbnail_path']);
                                    } else if (!$is_cloud_file && !empty($file['file_path'])) {
                                        $display_src = "/thidua/" . htmlspecialchars($file['file_path']);
                                    }
                                ?>
                                    <?php if ($display_src): ?>
                                        <img src="<?= $display_src ?>" class="thumbnail-img" alt="Thumbnail" loading="lazy">
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-image-fill file-icon text-cyan-600" viewBox="0 0 16 16"><path d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707v5.586l-2.73-2.73a1 1 0 0 0-1.52.127l-1.889 2.644-1.769-1.062a1 1 0 0 0-1.222.15L2 12.292V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zm-1.498 4a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>   <path d="M10.564 8.27 14 11.708V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-.293l3.578-3.577 2.56 1.536 2.426-3.395z"/></svg>
                                    <?php endif; ?>
                                    
                                <?php elseif (is_pdf_by_type($file['file_type'])): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-pdf-fill file-icon text-red-600" viewBox="0 0 16 16"><path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.035.012a.3.3 0 0 1-.026-.044c-.056-.11-.054-.216.04-.36.106-.165.319-.354.647-.548m2.455-1.647q-.178.037-.356.078a21 21 0 0 0 .5-1.05 12 12 0 0 0 .51.858q-.326.048-.654.114m2.525.939a4 4 0 0 1-.435-.41q.344.007.612.054c.317.057.466.147.518.209a.1.1 0 0 1 .026.064.44.44 0 0 1-.06.2.3.3 0 0 1-.094.124.1.1 0 0 1-.069.015c-.09-.003-.258-.066-.498-.256M8.278 6.97c-.04.244-.108.524-.2.829a5 5 0 0 1-.089-.346c-.076-.353-.087-.63-.046-.822.038-.177.11-.248.196-.283a.5.5 0 0 1 .145-.04c.013.03.028.092.032.198q.008.183-.038.465z"/>   <path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM4.165 13.668c.09.18.23.343.438.419.207.075.412.04.58-.03.318-.13.635-.436.926-.786.333-.401.683-.927 1.021-1.51a11.7 11.7 0 0 1 1.997-.406c.3.383.61.713.91.95.28.22.603.403.934.417a.86.86 0 0 0 .51-.138c.155-.101.27-.247.354-.416.09-.181.145-.37.138-.563a.84.84 0 0 0-.2-.518c-.226-.27-.596-.4-.96-.465a5.8 5.8 0 0 0-1.335-.05 11 11 0 0 1-.98-1.686c.25-.66.437-1.284.52-1.794.036-.218.055-.426.048-.614a1.24 1.24 0 0 0-.127-.538.7.7 0 0 0-.477-.365c-.202-.043-.41 0-.601.077-.377.15-.576.47-.651.823-.073.34-.04.736.046 1.136.088.406.238.848.43 1.295a20 20 0 0 1-1.062 2.227 7.7 7.7 0 0 0-1.482.645c-.37.22-.699.48-.897.787-.21.326-.275.714-.08 1.103"/></svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-text-fill file-icon" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M4.5 9a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM4 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 0 1z"/></svg>
                                <?php endif; ?>
                            </div>
                            <div class="file-name"><?= $name ?></div>
                        </a>
                        
                        <?php if (!$is_locked && !$is_cloud_file): ?>
                            <div class="delete-proof-btn" data-proof-id="<?php echo $proof['id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="action-buttons-footer">
        <a href="/thidua/hocsinh/so-nhat-ky/chon-tuan" class="btn bg-slate-600 hover:bg-slate-700 text-white shadow-sm border-transparent">Quay l?i</a>
        <?php if (!$is_locked): ?>
            <button id="submit-journal-btn" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent font-bold" data-nhat-ky-id="<?php echo $nhat_ky_id; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-send-check-fill mr-1" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 1.59 2.498C8 14 8 13 8 12.5a4.5 4.5 0 0 1 5.026-4.47zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>   <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686"/></svg> G?i
            </button>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/partials/ctv_footer.php'; ?>
<script src="/thidua/public/assets/libs/fancybox.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // === B?T Ð?U NÂNG C?P: THÊM 2 HÀM HELPER B? THI?U ===
    function is_image(file_type) {
        if (!file_type) return false;
        return file_type.startsWith('image/');
    }
    function is_pdf_by_type(file_type) {
        if (!file_type) return false;
        return file_type.toLowerCase() === 'application/pdf';
    }
    // === K?T THÚC NÂNG C?P ===

    Fancybox.bind('[data-fancybox]', {
        groupAll: false, 
        dragToClose: true, 
        Toolbar: {
            display: {
                left: ["infobar"],
                middle: [ "zoomIn", "zoomOut", "toggle1to1", "rotateCCW", "rotateCW", "flipX", "flipY" ],
                right: ["slideshow", "fullscreen", "download", "close"],
            },
        },
        Thumbs: { type: "classic" },
        Images: { load: "fade" },
    });

    const isLocked = <?php echo json_encode($is_locked); ?>;
    
    function calculateTotals() {
        const totals = { tot: 0, kha: 0, tb: 0, yeu: 0 };
        document.querySelectorAll('.auto-save-input').forEach(input => {
            const val = parseInt(input.value) || 0;
            if (input.name.includes('tot')) totals.tot += val;
            if (input.name.includes('kha')) totals.kha += val;
            if (input.name.includes('tb')) totals.tb += val;
            if (input.name.includes('yeu')) totals.yeu += val;
        });
        
        document.getElementById('total-tot').textContent = totals.tot;
        document.getElementById('total-kha').textContent = totals.kha;
        document.getElementById('total-tb').textContent = totals.tb;
        document.getElementById('total-yeu').textContent = totals.yeu;
    }

    /**
     * Luu m?t kh?i s? d?u bài (m?t lo?i_so). Dùng chung cho auto-save và tru?c khi g?i duy?t
     * d? tránh race: trên hosting reload có th? h?y fetch luu n?u chua await xong.
     */
    async function saveJournalGrid(grid) {
        const first = grid.querySelector('.auto-save-input');
        if (!first || first.disabled) return;
        const data = {
            nhat_ky_id: first.dataset.nhatKyId,
            loai_so: first.dataset.loaiSo,
            so_tiet_tot: grid.querySelector('[name="so_tiet_tot"]').value,
            so_tiet_kha: grid.querySelector('[name="so_tiet_kha"]').value,
            so_tiet_tb: grid.querySelector('[name="so_tiet_tb"]').value,
            so_tiet_yeu: grid.querySelector('[name="so_tiet_yeu"]').value,
        };
        const response = await fetch('/thidua/api/ctv/luu-nhat-ky', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Không luu du?c s? ti?t.');
        }
    }

    async function saveAllJournalGrids() {
        for (const grid of document.querySelectorAll('.input-grid')) {
            await saveJournalGrid(grid);
        }
    }

    document.querySelectorAll('.auto-save-input').forEach(input => {
        input.addEventListener('change', async (e) => {
            calculateTotals();
            if (isLocked) return;

            const grid = e.target.closest('.input-grid');
            try {
                await saveJournalGrid(grid);
            } catch (error) { console.error('L?i khi luu t? d?ng:', error); }
        });
    });

    if (isLocked) {
        calculateTotals();
        return; 
    }
    
    // === B?T Ð?U NÂNG C?P V4: UPLOAD LOGIC (Drag/Drop + Progress + Inline Errors) ===

    document.querySelectorAll('.drop-zone').forEach(dropZone => {
        // L?y các element liên quan cho t?ng khu v?c
        const input = document.getElementById(`upload-proof-${dropZone.dataset.loaiMinhChung}`);
        const previewList = document.getElementById(`upload-preview-${dropZone.dataset.loaiMinhChung}`);
        const proofList = document.getElementById(`proof-list-${dropZone.dataset.loaiMinhChung}`);
        const nhatKyId = dropZone.dataset.nhatKyId;
        const loaiMc = dropZone.dataset.loaiMinhChung;

        // 1. X? lý khi ch?n file b?ng cách click (dã có <label for...>)
        input.addEventListener('change', (e) => {
            handleFileUploads(e.target.files, nhatKyId, loaiMc, proofList, previewList);
            e.target.value = ''; // Reset input d? có th? ch?n l?i file cu
        });

        // 2. X? lý Drag & Drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            handleFileUploads(e.dataTransfer.files, nhatKyId, loaiMc, proofList, previewList);
        });
    });

    /**
     * Hàm x? lý file trung tâm (l?p qua các file du?c ch?n/kéo)
     */
    function handleFileUploads(files, nhatKyId, loaiMc, proofList, previewList) {
      const maxFileSize = 7 * 1024 * 1024; // 7MB

        for (const file of files) {
            // T?o m?t ID duy nh?t cho m?i item preview
            const uploadId = `upload-item-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
            
            // --- S?A L?I: HI?N TH? L?I INLINE THAY VÌ ALERT ---
            if (file.size > maxFileSize) {
              const errorMsg = `L?i: File quá l?n (${(file.size / 1024 / 1024).toFixed(2)} MB). Gi?i h?n là 7MB.`;
                createUploadPreviewItem(uploadId, file, previewList, true, errorMsg);
                continue; // B? qua file này
            }

            // N?u file OK, t?o preview và b?t d?u t?i lên
            createUploadPreviewItem(uploadId, file, previewList, false);
            uploadFile(file, nhatKyId, loaiMc, uploadId, proofList, previewList);
        }
    }

    /**
     * T?o item xem tru?c (cho c? l?i và dang t?i)
     */
    function createUploadPreviewItem(id, file, previewList, isError = false, errorMessage = '') {
        const item = document.createElement('div');
        item.className = 'upload-preview-item';
        item.id = id;
        if (isError) {
            item.classList.add('is-error');
        }

        const isImage = file.type.startsWith('image/');
        const isPdf = file.type === 'application/pdf';
        
        // T?o thumbnail xem tru?c
        let thumbHtml = '';
        if (isImage) {
            thumbHtml = `<img src="${URL.createObjectURL(file)}" alt="Thumb">`;
        } else if (isPdf) {
            thumbHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-pdf-fill text-red-600 text-lg" viewBox="0 0 16 16"><path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.035.012a.3.3 0 0 1-.026-.044c-.056-.11-.054-.216.04-.36.106-.165.319-.354.647-.548m2.455-1.647q-.178.037-.356.078a21 21 0 0 0 .5-1.05 12 12 0 0 0 .51.858q-.326.048-.654.114m2.525.939a4 4 0 0 1-.435-.41q.344.007.612.054c.317.057.466.147.518.209a.1.1 0 0 1 .026.064.44.44 0 0 1-.06.2.3.3 0 0 1-.094.124.1.1 0 0 1-.069.015c-.09-.003-.258-.066-.498-.256M8.278 6.97c-.04.244-.108.524-.2.829a5 5 0 0 1-.089-.346c-.076-.353-.087-.63-.046-.822.038-.177.11-.248.196-.283a.5.5 0 0 1 .145-.04c.013.03.028.092.032.198q.008.183-.038.465z"/>   <path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM4.165 13.668c.09.18.23.343.438.419.207.075.412.04.58-.03.318-.13.635-.436.926-.786.333-.401.683-.927 1.021-1.51a11.7 11.7 0 0 1 1.997-.406c.3.383.61.713.91.95.28.22.603.403.934.417a.86.86 0 0 0 .51-.138c.155-.101.27-.247.354-.416.09-.181.145-.37.138-.563a.84.84 0 0 0-.2-.518c-.226-.27-.596-.4-.96-.465a5.8 5.8 0 0 0-1.335-.05 11 11 0 0 1-.98-1.686c.25-.66.437-1.284.52-1.794.036-.218.055-.426.048-.614a1.24 1.24 0 0 0-.127-.538.7.7 0 0 0-.477-.365c-.202-.043-.41 0-.601.077-.377.15-.576.47-.651.823-.073.34-.04.736.046 1.136.088.406.238.848.43 1.295a20 20 0 0 1-1.062 2.227 7.7 7.7 0 0 0-1.482.645c-.37.22-.699.48-.897.787-.21.326-.275.714-.08 1.103"/></svg>';
        } else {
            thumbHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-text-fill text-lg" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M4.5 9a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM4 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 0 1z"/></svg>';
        }

        item.innerHTML = `
            <div class="upload-thumbnail">
                ${thumbHtml}
            </div>
            <div class="upload-info">
                <div class="upload-filename">${file.name}</div>
                ${isError 
                    ? `<div class="upload-error">${errorMessage}</div>`
                    : `
                        <div class="upload-status">Ðang t?i...</div>
                        <div class="progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                    `
                }
            </div>
        `;
        previewList.appendChild(item);
    }

    /**
     * Hàm t?i file (dùng XHR d? có progress bar)
     */
    function uploadFile(file, nhatKyId, loaiMc, uploadId, proofList, previewList) {
        const formData = new FormData();
        formData.append('nhat_ky_id', nhatKyId);
        formData.append('loai_minh_chung', loaiMc);
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        const previewItem = document.getElementById(uploadId);
        if (!previewItem) return; // Item dã b? xóa (hi h?u)
        
        const statusEl = previewItem.querySelector('.upload-status');
        const progressEl = previewItem.querySelector('.progress-bar');

        xhr.open('POST', '/thidua/api/ctv/upload-minh-chung-nhat-ky', true);

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                if (progressEl) progressEl.style.width = `${percent}%`;
                if (statusEl) statusEl.textContent = `Ðang t?i... ${percent}%`;
            }
        };

        xhr.onload = () => {
          let result = null;
          try {
            result = JSON.parse(xhr.responseText);
          } catch (err) {
            result = null;
          }

          if (xhr.status === 200 && result && result.success) {
            // T?i lên thành công
            previewItem.remove(); // Xóa item preview
            appendSuccessfulProof(result.proof, loaiMc, proofList); // Thêm item dã duy?t
            return;
          }

          const serverMessage = result && result.message ? result.message : null;
          const fallbackMessage = xhr.statusText ? `L?i t?i lên: ${xhr.statusText}` : 'L?i máy ch? không xác d?nh.';
          showUploadError(previewItem, serverMessage || fallbackMessage);
        };

        xhr.onerror = () => {
            // L?i k?t n?i m?ng
            showUploadError(previewItem, 'L?i m?ng. Không th? k?t n?i d?n máy ch?.');
        };

        xhr.send(formData);
    }
    
    /**
     * Hàm hi?n th? l?i trên item preview (thay cho alert)
     */
    function showUploadError(previewItem, message) {
        if (!previewItem) return;
        previewItem.classList.add('is-error');
        const infoEl = previewItem.querySelector('.upload-info');
        if (infoEl) {
            infoEl.innerHTML = `
                <div class="upload-filename">${previewItem.querySelector('.upload-filename').textContent}</div>
                <div class="upload-error">${message}</div>
            `;
        }
    }

    /**
     * Hàm thêm item thành công (tái s? d?ng code cu c?a b?n)
     */
    function appendSuccessfulProof(proof, loaiMc, proofList) {
        const isImage = is_image(proof.file_type); 
        const isPdf = is_pdf_by_type(proof.file_type);
        
        const gallery_group = "gallery-" + loaiMc;
        let attrs = `data-fancybox='${gallery_group}' data-caption='${proof.original_filename}'`;
        let path = `/thidua/${proof.file_path}`;
        
        if (isImage) attrs += " data-type='image'";
        else if (isPdf) attrs += " data-type='pdf'";
        
        const thumbSrc = (isImage && proof.thumbnail_path) 
            ? `/thidua/${proof.thumbnail_path}` 
            : (isImage ? `/thidua/${proof.file_path}` : null);

        // Chú ý: s?a l?i class_name thành class
        const newProofItem = `
            <div class="proof-item" id="proof-item-${proof.id}">
                <a href="${path}" 
                   title="${proof.original_filename}" 
                   ${attrs} 
                   class="proof-item-link">
                    <div class="thumbnail-w-full max-w-6xl mx-auto px-6">
                    ${thumbSrc 
                        ? `<img src="${thumbSrc}" class="thumbnail-img" alt="Thumbnail">`
                        : (isPdf
                            ? `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-pdf-fill file-icon text-red-600" viewBox="0 0 16 16"><path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.035.012a.3.3 0 0 1-.026-.044c-.056-.11-.054-.216.04-.36.106-.165.319-.354.647-.548m2.455-1.647q-.178.037-.356.078a21 21 0 0 0 .5-1.05 12 12 0 0 0 .51.858q-.326.048-.654.114m2.525.939a4 4 0 0 1-.435-.41q.344.007.612.054c.317.057.466.147.518.209a.1.1 0 0 1 .026.064.44.44 0 0 1-.06.2.3.3 0 0 1-.094.124.1.1 0 0 1-.069.015c-.09-.003-.258-.066-.498-.256M8.278 6.97c-.04.244-.108.524-.2.829a5 5 0 0 1-.089-.346c-.076-.353-.087-.63-.046-.822.038-.177.11-.248.196-.283a.5.5 0 0 1 .145-.04c.013.03.028.092.032.198q.008.183-.038.465z"/>   <path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM4.165 13.668c.09.18.23.343.438.419.207.075.412.04.58-.03.318-.13.635-.436.926-.786.333-.401.683-.927 1.021-1.51a11.7 11.7 0 0 1 1.997-.406c.3.383.61.713.91.95.28.22.603.403.934.417a.86.86 0 0 0 .51-.138c.155-.101.27-.247.354-.416.09-.181.145-.37.138-.563a.84.84 0 0 0-.2-.518c-.226-.27-.596-.4-.96-.465a5.8 5.8 0 0 0-1.335-.05 11 11 0 0 1-.98-1.686c.25-.66.437-1.284.52-1.794.036-.218.055-.426.048-.614a1.24 1.24 0 0 0-.127-.538.7.7 0 0 0-.477-.365c-.202-.043-.41 0-.601.077-.377.15-.576.47-.651.823-.073.34-.04.736.046 1.136.088.406.238.848.43 1.295a20 20 0 0 1-1.062 2.227 7.7 7.7 0 0 0-1.482.645c-.37.22-.699.48-.897.787-.21.326-.275.714-.08 1.103"/></svg>`
                            : `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-text-fill file-icon" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M4.5 9a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM4 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 0 1z"/></svg>`)
                    }
                    </div>
                    <div class="file-name">${proof.original_filename}</div>
                </a>
                <div class="delete-proof-btn" data-proof-id="${proof.id}"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg></div>
            </div>`;
            
        proofList.insertAdjacentHTML('beforeend', newProofItem);
        // Re-bind Fancybox cho các item m?i
        Fancybox.bind(`[data-fancybox='${gallery_group}']`, {}); 
    }

    // === K?T THÚC NÂNG C?P V4 ===

    document.body.addEventListener('click', async function(e) {
        if (e.target.closest('.delete-proof-btn')) {
            const button = e.target.closest('.delete-proof-btn');
            const proofId = button.dataset.proofId;
            if (!confirm('B?n có ch?c ch?n mu?n xóa minh ch?ng này?')) return;
            try {
                const response = await fetch('/thidua/api/ctv/xoa-minh-chung-nhat-ky', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ proof_id: proofId })
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById(`proof-item-${proofId}`).remove();
                } else { throw new Error(result.message); }
            } catch (error) { alert('L?i khi xóa: ' + error.message); }
        }
    });

    document.getElementById('submit-journal-btn').addEventListener('click', async function(e) {
        if (!confirm('Sau khi g?i, b?n s? không th? ch?nh s?a. B?n có ch?c ch?n mu?n g?i cho Admin duy?t không?')) return;
        const button = e.currentTarget;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ðang g?i...';
        try {
            await saveAllJournalGrids();
            const response = await fetch('/thidua/api/ctv/gui-nhat-ky', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nhat_ky_id: button.dataset.nhatKyId })
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) {
                location.reload();
            } else {
                button.disabled = false;
                button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-send-check-fill mr-1" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 1.59 2.498C8 14 8 13 8 12.5a4.5 4.5 0 0 1 5.026-4.47zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>   <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686"/></svg> G?i';
            }
        } catch (error) {
            alert('L?i: ' + (error.message || error));
            button.disabled = false;
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-send-check-fill mr-1" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 1.59 2.498C8 14 8 13 8 12.5a4.5 4.5 0 0 1 5.026-4.47zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>   <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686"/></svg> G?i';
        }
    });
    
    calculateTotals();
});
</script>
