<?php
// File: src/views/giao_vu.php (GIAO DI?N NÂNG C?P HI?N Ð?I – TINH T? V2)
$page_title = 'C?ng Thông Tin Cá Nhân';
require_once __DIR__ . '/partials/ctv_header.php';

$permissions = $_SESSION['student_permissions'] ?? [];
?>

<style>
/* ====== GIAO DI?N CHUNG ====== */
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
}
/* Dark mode variables (gi? nguyên) */
:root {
  --card-bg-light: #ffffff;
  --card-bg-dark: rgba(30, 41, 59, 0.85); /* T?i hon chút */
  --card-border-light: rgba(0,0,0,0.08);
  --card-border-dark: rgba(255,255,255,0.1);
  --text-primary-light: #1d2d35;
  --text-primary-dark: #f1f5f9;
  --text-secondary-light: #64748b;
  --text-secondary-dark: #cbd5e1;
}

[data-theme="light"] .profile-card,
[data-theme="light"] .tabs-card {
  background-color: var(--card-bg-light);
  color: var(--text-primary-light);
  border: 1px solid var(--card-border-light);
}

[data-theme="dark"] .profile-card,
[data-theme="dark"] .tabs-card {
  background-color: var(--card-bg-dark);
  color: var(--text-primary-dark);
  border: 1px solid var(--card-border-dark);
  backdrop-filter: blur(10px);
}

[data-theme="dark"] .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light {
  color: var(--text-primary-dark);
}

[data-theme="dark"] .nav-tabs .nav-link {
  color: var(--text-secondary-dark);
}
[data-theme="dark"] .nav-tabs .nav-link.active {
  color: #60a5fa;
  border-bottom: 2px solid #60a5fa;
}
body {
  font-family: "Inter", sans-serif;
  background-color: var(--bg-page);
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

/* ====== PROFILE CARD NÂNG C?P V3 ====== */
.w-full max-w-6xl mx-auto px-4 {
  position: relative;
  z-index: 1;
}

.profile-card {
  border: none;
  border-radius: 1.5rem;
  background: var(--card-bg);
  backdrop-filter: blur(20px) saturate(180%);
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.profile-card::before {
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

.profile-card:hover {
  transform: translateY(-8px) scale(1.01);
  box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}

.profile-card:hover::before {
  opacity: 1;
}
.profile-avatar {
    width: 140px; /* **TANG KÍCH THU?C AVATAR** */
    height: 140px;
    border-radius: 50%;
    border: 4px solid transparent;
    background-image: linear-gradient(white, white),
                      linear-gradient(45deg, var(--brand-accent), var(--brand-primary));
    background-origin: border-box;
    background-clip: content-box, border-box;
    box-shadow: 0 6px 15px rgba(37, 99, 235, 0.15);
    object-fit: cover;
    object-position: center top;
    transition: transform 0.3s ease;
}
.profile-avatar:hover {
    transform: scale(1.05);
}
/* **CSS M?I CHO TR?NG THÁI DU?I AVATAR** */
.profile-status {
    margin-top: 0.75rem; /* Kho?ng cách du?i avatar */
    font-weight: 600;
    padding: 0.3em 0.8em;
    border-radius: 50px; /* Bo tròn */
    font-size: 0.85rem;
    display: d-inline-block; /* Ð? v?a n?i dung */
}
.profile-status.status-dang-hoc {
    background-color: rgba(25, 135, 84, 0.1); /* N?n xanh lá m? */
    color: #0f5132; /* Ch? xanh lá d?m */
}
.profile-status.status-nghi-hoc {
    background-color: rgba(220, 53, 69, 0.1); /* N?n d? m? */
    color: #842029; /* Ch? d? d?m */
}

/* Profile Details */
.profile-details {
  list-style: none;
  margin: 0;
  padding: 0;
}
.profile-details li {
  padding: 0.7rem 0; /* Tang kho?ng cách */
  border-bottom: 1px solid rgba(0, 0, 0, 0.05); /* Vi?n m?nh hon */
  font-size: 0.9rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.profile-details li:last-child { border-bottom: none; }
.profile-details li strong {
  width: 110px;
  display: d-inline-block;
  color: var(--text-strong); /* Ð?i sang màu ch? d?m chính */
  flex-shrink: 0;
  font-size: 0.95rem; /* Tang nh? kích thu?c ch? */
  /* font-weight: 600; */ /* Có th? thêm n?u mu?n d?m hon n?a */
}
.profile-details li span {
  font-weight: 500;
  color: var(--text-strong);
  text-align: right;
}

/* ====== TABS CARD NÂNG C?P ====== */
.tabs-card {
  border: none;
  border-radius: 1.5rem;
  background: var(--card-bg);
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  backdrop-filter: blur(20px) saturate(180%);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.tabs-card::before {
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

.tabs-card:hover {
  box-shadow: 0 20px 50px rgba(37, 99, 235, 0.12),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}

.tabs-card:hover::before {
  opacity: 1;
}

.nav-tabs {
  border-bottom: 2px solid rgba(0,0,0,0.06);
  background: linear-gradient(180deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.9) 100%);
  backdrop-filter: blur(10px);
  border-radius: 1.5rem 1.5rem 0 0;
  padding: 0.5rem 0.75rem;
  position: relative;
}

.nav-tabs::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: var(--brand-gradient);
  width: 33.333%;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  transform-origin: center;
}

.nav-tabs .nav-link {
  border: none;
  color: var(--text-muted);
  font-weight: 600;
  padding: 1rem 1.5rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  position: relative;
  border-radius: 0.75rem 0.75rem 0 0;
  margin: 0 0.25rem;
}

.nav-tabs .nav-link::before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%) scaleX(0);
  width: 80%;
  height: 3px;
  background: var(--brand-gradient);
  border-radius: 3px 3px 0 0;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.nav-tabs .nav-link:hover {
  color: var(--brand-primary);
  background: linear-gradient(180deg, rgba(37,99,235,0.08) 0%, rgba(37,99,235,0.04) 100%);
  transform: translateY(-2px);
}

.nav-tabs .nav-link.active {
  color: var(--brand-primary);
  background: linear-gradient(180deg, rgba(37,99,235,0.12) 0%, transparent 100%);
}

.nav-tabs .nav-link.active::before {
  transform: translateX(-50%) scaleX(1);
}
.nav-tabs .nav-link i { /* CSS cho icon trong tab */
    font-size: 1.1em; /* Kích thu?c icon tuong d?i */
    line-height: 1;
}

.tab-content {
  padding: 1.5rem;
}

/* ====== SECTION TITLE ====== */
.section-title {
  font-weight: 700;
  color: var(--text-strong);
  display: flex;
  align-items: center;
  gap: .5rem;
  border-bottom: 1px dashed var(--card-border);
  padding-bottom: .5rem;
  margin-bottom: 1.5rem; /* Tang kho?ng cách du?i */
}
.section-title i {
  color: var(--brand-primary);
}

/* ====== ACTION CARDS NÂNG C?P ====== */
.action-card {
  border-radius: 1.25rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20px) saturate(180%);
  box-shadow: 0 8px 30px rgba(0,0,0,0.06),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(255,255,255,0.8);
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.action-card::before {
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
}

.action-card .p-4 {
    d-flex-grow: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1.5rem;
    position: relative;
    z-index: 1;
}

.action-card:hover {
  transform: translateY(-10px) scale(1.03);
  box-shadow: 0 20px 50px rgba(37,99,235,0.2),
              0 0 0 1px rgba(255,255,255,1) inset;
  background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(240,247,255,0.95) 100%);
}

.action-card:hover::before {
  transform: scaleX(1);
}

.action-card i.card-icon {
    font-size: 3.5rem;
    background: var(--brand-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1.25rem;
    display: d-block;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    filter: drop-shadow(0 4px 8px rgba(37, 99, 235, 0.2));
    position: relative;
}

.action-card:hover i.card-icon {
    transform: scale(1.2) translateY(-5px);
    filter: drop-shadow(0 8px 16px rgba(37, 99, 235, 0.4));
}

.action-card h5 {
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  color: var(--text-strong);
  transition: color 0.3s ease;
}

.action-card:hover h5 {
  background: var(--brand-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.action-card p {
  font-size: 0.875rem;
  color: var(--text-muted);
  margin: 0;
  line-height: 1.5;
}
/* ====== HI?U ?NG Ð?NG NÂNG C?P ====== */

/* 1?? Avatar – vòng sáng nh? quay ch?m */
@keyframes spin-glow {
  0% { box-shadow: 0 0 0 rgba(37,99,235,0.0); transform: rotate(0deg); }
  50% { box-shadow: 0 0 30px rgba(37,99,235,0.25); }
  100% { transform: rotate(360deg); }
}
.profile-avatar {
  position: relative;
  z-index: 1;
}
.profile-avatar::after {
  content: "";
  position: absolute;
  top: -8px;
  left: -8px;
  width: 156px;
  height: 156px;
  border-radius: 50%;
  border: 2px dashed rgba(96,165,250,0.4);
  animation: spin-glow 8s linear infinite;
  z-index: -1;
}

/* 2?? Card xu?t hi?n tru?t nh? */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.profile-card {
  animation: fadeUp 0.8s ease forwards;
  animation-delay: 0.1s;
  opacity: 0;
}

.tabs-card {
  animation: fadeUp 0.8s ease forwards;
  animation-delay: 0.3s;
  opacity: 0;
}

.action-card {
  animation: fadeUp 0.6s ease forwards;
  opacity: 0;
}

/* Stagger animation for action cards */
.action-card:nth-child(1) { animation-delay: 0.4s; }
.action-card:nth-child(2) { animation-delay: 0.5s; }
.action-card:nth-child(3) { animation-delay: 0.6s; }

/* 3?? Enhanced hover effects */
.action-card::after {
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

.action-card:hover::after {
  width: 300px;
  height: 300px;
}

/* 4?? Tab chuy?n d?ng mu?t */
.tab-pane {
  opacity: 0;
  transform: translateY(20px) scale(0.98);
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); /* <-- S?A ? ÐÂY */
  position: relative;
}

.tab-pane.active.show {
  opacity: 1;
  transform: translateY(0) scale(1);
  animation: slideInTab 0.25s cubic-bezier(0.4, 0, 0.2, 1); /* <-- VÀ ? ÐÂY */
}

@keyframes slideInTab {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes slideInTab {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* 5?? Hi?u ?ng xu?t hi?n tu?n t? */
[data-aos] {
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.6s ease-out;
}
[data-aos].aos-animate {
  opacity: 1;
  transform: translateY(0);
}
/* ====== NÂNG C?P S?A L?I DARK MODE (Thêm vào) ====== */

/* 1. S?a ch? trong Profile Card (Th? thông tin cá nhân) */
[data-theme="dark"] .profile-card h4,
[data-theme="dark"] .profile-details li strong,
[data-theme="dark"] .profile-details li span {
    color: var(--text-primary-dark); /* Ch? sáng chính (#f1f5f9) */
}

[data-theme="dark"] .profile-card .text-muted {
     /* Dùng !important d? ghi dè class .text-muted c?a Bootstrap */
    color: var(--text-secondary-dark) !important; /* Ch? sáng ph? (#cbd5e1) */
}

/* 2. S?a ch? trong Tab "CTV" (Th? hành d?ng) */
[data-theme="dark"] .section-title {
    color: var(--text-primary-dark);
}

/* 3. S?a ch? trong Tab "Khen Thu?ng" & "Vi Ph?m" (khi b?ng tr?ng) */
[data-theme="dark"] .alert-info {
     background-color: transparent;
     color: var(--text-secondary-dark);
     border-color: var(--card-border-dark);
}
[data-theme="dark"] .alert-success {
     background-color: transparent;
     color: #10b981; /* Gi? màu xanh lá cho d?p */
     border-color: var(--card-border-dark);
}
/* =================================================== */

/* ====== TABLE NÂNG C?P ====== */
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light {
  font-size: 0.875rem;
  border-color: var(--card-border);
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light thead th {
  background-color: rgba(37,99,235,0.05);
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  border-bottom-width: 2px; /* Ð?m hon */
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light tbody tr:hover {
  background-color: rgba(37,99,235,0.04);
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light td, .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light th {
    padding: 0.7rem 0.8rem; /* Tang padding ô */
}

/* ====== RESPONSIVE ====== */
@media (max-width: 768px) {
  .profile-card, .tabs-card { border-radius: 0.75rem; }
  .profile-avatar { width: 100px; height: 100px; }
  .tab-content { padding: 1rem; }
  .nav-tabs .nav-link { padding: 0.7rem 0.9rem; font-size: 0.9rem; }
  .action-card i.card-icon { font-size: 2.5rem; }
  .shape { filter: blur(30px); }
  .shape-1, .shape-2, .shape-3, .shape-4, .shape-5 {
    width: 200px !important;
    height: 200px !important;
  }
}
/* ALERT KÍCH HO?T CTV */
#activate-ctv-section {
    background: linear-gradient(135deg, rgba(37,99,235,0.08) 0%, rgba(124,58,237,0.08) 100%);
    border: 2px dashed rgba(37,99,235,0.3);
    border-radius: 1rem;
    padding: 2rem;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

#activate-ctv-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(37,99,235,0.05), transparent);
    animation: pulse-ring 3s ease-out infinite;
}

#activate-ctv-section .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent {
    background: var(--brand-gradient);
    border: none;
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

#activate-ctv-section .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
}

#activate-ctv-section .form-control {
    border: 2px solid rgba(37,99,235,0.2);
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

#activate-ctv-section .form-control:focus {
    border-color: var(--brand-primary);
    box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
    outline: none;
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

<div class="w-full max-w-6xl mx-auto px-6 my-6">

  <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 profile-card mb-6">
    <div class="p-6 text-center">
      <img src="<?php echo !empty($student_info['anh_the']) ? '/thidua/public/assets/anh_the/' . htmlspecialchars($student_info['anh_the']) : '/thidua/public/assets/img/anhthegoc.JPG'; ?>"
           alt="Avatar"
           class="profile-avatar mb-6"
           onerror="this.onerror=null;this.src='/thidua/public/assets/img/anhthegoc.JPG';">
      <h4 class="mb-1"><?php echo htmlspecialchars($student_info['ho_dem'] . ' ' . $student_info['ten']); ?></h4>
      <p class="text-slate-500 mb-2"><?php echo htmlspecialchars($student_info['chuc_vu'] ?? 'H?c sinh'); ?> - <?php echo htmlspecialchars($student_info['ten_lop']); ?></p>
    </div>
    <div class="p-6 border-top">
      <ul class="profile-details">
        <li><strong>H? và Tên:</strong> <span><?= htmlspecialchars($student_info['ho_dem'] . ' ' . $student_info['ten']); ?></span></li>
        <li><strong>L?p:</strong> <span><?= htmlspecialchars($student_info['ten_lop']); ?></span></li>
        <li><strong>Ch?c v?:</strong> <span><?= htmlspecialchars($student_info['chuc_vu'] ?? 'H?c sinh'); ?></span></li>
        <li><strong>CCCD:</strong> <span><?= htmlspecialchars($student_info['ma_hoc_sinh'] ?? 'Chua có'); ?></span></li>
        <li><strong>Tr?ng thái:</strong>
          <?php
            $status = $student_info['trang_thai_hoc_tap'] ?? 'dang_hoc';
            echo $status === 'dang_hoc'
              ? '<span class="text-green-600 font-semibold">Ðang h?c</span>'
              : '<span class="text-red-600 font-semibold">Ðã ngh? h?c</span>';
          ?>
        </li>
        <li><strong>Ngày sinh:</strong> <span><?= htmlspecialchars($student_info['ngay_sinh'] ?? 'Chua có'); ?></span></li>
        <li><strong>Gi?i tính:</strong> <span><?= htmlspecialchars($student_info['gioi_tinh'] ?? 'Chua có'); ?></span></li>
        <li><strong>SÐT:</strong> <span><?= htmlspecialchars($student_info['sdt'] ?? 'Chua có'); ?></span></li>
        <li><strong>Email:</strong> <span><?= htmlspecialchars($student_info['email'] ?? 'Chua có'); ?></span></li>
        <li><strong>GVCN:</strong> <span><?= htmlspecialchars($student_info['gvcn_ten'] ?? 'Chua có'); ?></span></li>
      </ul>
    </div>

  </div>

  <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 tabs-card">
    <ul class="nav nav-tabs" id="profileTab" role="tablist">
      <li class="nav-item">
          <button class="nav-link active" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-workspace" viewBox="0 0 16 16"><path d="M4 16s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-5.95a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>   <path d="M2 1a2 2 0 0 0-2 2v9.5A1.5 1.5 0 0 0 1.5 14h.653a5.4 5.4 0 0 1 1.066-2H1V3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v9h-2.219c.554.654.89 1.373 1.066 2h.653a1.5 1.5 0 0 0 1.5-1.5V3a2 2 0 0 0-2-2z"/></svg> CTV
          </button>
      </li>
      <li class="nav-item">
          <button class="nav-link" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trophy-fill" viewBox="0 0 16 16"><path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5q0 .807-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33 33 0 0 1 2.5.5m.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935m10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935"/></svg>Khen Thu?ng
          </button>
      </li>
      <li class="nav-item">
          <button class="nav-link" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-check" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg> Vi Ph?m
          </button>
      </li>
    </ul>

    <div class="tab-content" id="profileTabContent">
      <div class="tab-pane fade show active" id="ctv">
        <h5 class="section-title"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-workspace" viewBox="0 0 16 16"><path d="M4 16s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-5.95a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>   <path d="M2 1a2 2 0 0 0-2 2v9.5A1.5 1.5 0 0 0 1.5 14h.653a5.4 5.4 0 0 1 1.066-2H1V3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v9h-2.219c.554.654.89 1.373 1.066 2h.653a1.5 1.5 0 0 0 1.5-1.5V3a2 2 0 0 0-2-2z"/></svg> Tr?c Thi Ðua</h5>
        <?php if (!empty($permissions) && in_array(true, array_values($permissions))): ?>
          <p>Hãy ch?n ch?c nang phù h?p bên du?i d? b?t d?u:</p>
          <div class="flex flex-wrap -mx-3 g-3"> 
            <?php if (!empty($permissions['nhap_vi_pham'])): ?>
            <div class="w-full md:w-1/3 px-6 mb-6" data-aos="fade-up" data-aos-delay="100">
              <a href="/thidua/hocsinh/chon-tuan?type=vi_pham" class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 text-center text-decoration-none action-card">
                <div class="p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-exclamation-triangle-fill card-icon" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                    <h5>Nh?p Vi Ph?m</h5>
                    <p>Ghi nh?n vi ph?m c?a h?c sinh.</p>
                </div>
              </a>
            </div>
            <?php endif; ?>
            <?php if (!empty($permissions['dang_ky_truc'])): ?>
            <div class="w-full md:w-1/3 px-6 mb-6" data-aos="fade-up" data-aos-delay="200">
              <a href="/thidua/hocsinh/chon-tuan?type=dang_ky_truc" class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 text-center text-decoration-none action-card">
                <div class="p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-check-fill card-icon" viewBox="0 0 16 16"><path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2m-5.146-5.146-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg>
                    <h5>Ðang Ký Tr?c</h5>
                    <p>Ðang ký và xem l?ch tr?c.</p>
                </div>
              </a>
            </div>
            <?php endif; ?>
            <?php if (!empty($permissions['so_nhat_ky_online'])): ?>
            <div class="w-full md:w-1/3 px-6 mb-6" data-aos="fade-up" data-aos-delay="300">
              <a href="/thidua/hocsinh/so-nhat-ky/chon-tuan" class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 text-center text-decoration-none action-card">
                <div class="p-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-text card-icon" viewBox="0 0 16 16"><path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>   <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>   <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/></svg>
                    <h5>Nh?t K? Ði?n T?</h5>
                    <p>Nh?p s? li?u và minh ch?ng.</p>
                </div>
              </a>
            </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div id="activate-ctv-section" class="p-6 mb-6 rounded-lg border alert-light border-dashed">
            <p>Nh?p mã d? kích ho?t quy?n:</p>
            <div class="flex w-full" style="max-width:350px;">
              <input type="text" id="ma_kich_hoat_input" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" placeholder="Nh?p mã 6 s?..." maxlength="6" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
              <button class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent" id="activate-ctv-btn">Kích ho?t</button>
            </div>
            <small id="activation-error" class="text-red-600 mt-1 block"></small>
          </div>
        <?php endif; ?>
      </div>

      <div class="tab-pane fade" id="kt">
        <h5 class="section-title"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-trophy-fill text-yellow-600" viewBox="0 0 16 16"><path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5q0 .807-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33 33 0 0 1 2.5.5m.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935m10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935"/></svg> Khen Thu?ng</h5>
        <?php if (empty($commendations_list)): ?>
          <div class="p-6 mb-6 rounded-lg border bg-cyan-50 text-cyan-800 border-cyan-200 text-center">Chua có thành tích nào.</div>
        <?php else: ?>
          <div class="overflow-x-auto w-full"> 
              <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light align-middle">
                <thead><tr><th>Ð?i tu?ng</th><th>Ngày KT</th><th>Tên</th><th>C?p</th><th>S? QÐ</th></tr></thead>
                <tbody>
                <?php foreach ($commendations_list as $kt): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($kt['doi_tuong']); ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($kt['ngay_khen_thuong'])); ?></td>
                    <td><?= htmlspecialchars($kt['ten_khen_thuong']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($kt['cap_khen_thuong']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($kt['so_quyet_dinh']); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
          </div>
        <?php endif; ?>
      </div>

      <div class="tab-pane fade" id="vp">
        <h5 class="section-title"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-list-check text-red-600" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg> L?ch S? Vi Ph?m</h5>
        <?php if (empty($violations_list)): ?>
          <div class="p-6 mb-6 rounded-lg border bg-green-50 text-green-800 border-green-200 text-center">Chúc m?ng! B?n không có vi ph?m nào.</div>
        <?php else: ?>
          <div class="overflow-x-auto w-full"> 
              <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light align-middle">
                <thead><tr><th>Tu?n</th><th>Ngày VP</th><th>N?i dung</th><th>Ði?m Tr?</th><th>Ghi chú</th></tr></thead>
                <tbody>
                <?php foreach ($violations_list as $vp): ?>
                  <tr>
                    <td><?= htmlspecialchars($vp['ten_tuan']); ?></td>
                    <td><?= date('d/m/Y', strtotime($vp['ngay_vi_pham'])); ?></td>
                    <td><?= htmlspecialchars($vp['ten_vi_pham']); ?></td>
                    <td class="text-center text-red-600 font-bold"><?= htmlspecialchars($vp['diem_tru']); ?></td>
                    <td><?= htmlspecialchars($vp['ghi_chu']); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/ctv_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // ====== Activate CTV Function ======
  const btn = document.getElementById('activate-ctv-btn');
  const input = document.getElementById('ma_kich_hoat_input');
  const err = document.getElementById('activation-error');
  if (btn && input) {
    btn.onclick = async () => {
      const code = input.value.trim();
      err.textContent = '';
      if (code.length !== 6) { err.textContent = 'Mã kích ho?t ph?i có 6 ch? s?.'; return; }
      btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ðang x? lý...';
      try {
        const res = await fetch('/thidua/admin/ctv?action=api_kich_hoat_ctv', {
          method: 'POST', headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ma_kich_hoat: code})
        });
        const result = await res.json();
        if (result.success) { alert(result.message); location.reload(); }
        else err.textContent = result.message;
      } catch(e) {
        err.textContent = 'Không th? k?t n?i d?n máy ch?.'; console.error(e);
      } finally { btn.disabled = false; btn.textContent = 'Kích ho?t'; }
    };
  }

  // ====== Tab Animation Handler ======


  // ====== AOS (Animate On Scroll) Implementation ======
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        entry.target.classList.add('aos-animate');
      }
    });
  }, observerOptions);

  document.querySelectorAll('[data-aos]').forEach(el => {
    observer.observe(el);
  });

  // ====== Action Card Ripple Effect ======
  document.querySelectorAll('.action-card').forEach(card => {
    card.addEventListener('click', function(e) {
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const ripple = document.createElement('span');
      ripple.style.cssText = `
        position: absolute;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(37, 99, 235, 0.3);
        left: ${x - 50}px;
        top: ${y - 50}px;
        pointer-events: none;
        animation: ripple-expand 0.6s ease-out;
        z-index: 2;
      `;
      
      this.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });
  });

  // Add ripple animation if not exists
  if (!document.querySelector('#ripple-style')) {
    const style = document.createElement('style');
    style.id = 'ripple-style';
    style.textContent = `
      @keyframes ripple-expand {
        to {
          transform: scale(4);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  }

  // ====== Enhanced Hover Effects for Cards ======
  document.querySelectorAll('.action-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
    });
  });

  // ====== Smooth Scroll for Better UX ======
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href !== '#' && href.startsWith('#')) {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            d-block: 'start'
          });
        }
      }
    });
  });
});
</script>
