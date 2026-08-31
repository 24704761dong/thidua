<?php
$page_title = 'Thông Tin & Cài Ð?t Tài Kho?n';
require_once __DIR__ . '/partials/ctv_header.php';
?>
<style>

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

[data-theme="light"] .profile-card,
[data-theme="light"] .settings-card {
  background-color: var(--card-bg-light);
  color: var(--text-primary-light);
  border: 1px solid var(--card-border-light);
}

[data-theme="dark"] .profile-card,
[data-theme="dark"] .settings-card {
  background-color: var(--card-bg-dark);
  color: var(--text-primary-dark);
  border: 1px solid var(--card-border-dark);
  backdrop-filter: blur(10px);
}
[data-theme="dark"] .nav-tabs .nav-link { color: var(--text-secondary-dark); }
[data-theme="dark"] .nav-tabs .nav-link.active { color: #60a5fa; border-bottom: 2px solid #60a5fa; }
[data-theme="dark"] .form-label,
[data-theme="dark"] .form-control::placeholder,
[data-theme="dark"] .form-control,
[data-theme="dark"] .profile-details li strong { color: var(--text-secondary-dark); }
[data-theme="dark"] .form-control,
[data-theme="dark"] .form-control { background-color: var(--bg-input-dark) !important; color: var(--text-primary-dark); border-color: var(--card-border-dark); }
[data-theme="dark"] .profile-details li span { color: var(--text-primary-dark); }
[data-theme="dark"] .settings-card .nav-tabs { background-color: var(--card-bg-dark); border-bottom-color: var(--card-border-dark); }
[data-theme="dark"] #email-change-section { background-color: rgba(40, 51, 69, 0.9) !important; border-color: var(--card-border-dark); }
[data-theme="dark"] #email-flow-status { color: var(--text-secondary-dark) !important; }
/* Dark mode cho Avatar */
[data-theme="dark"] .profile-avatar {
    background-image: linear-gradient(var(--card-bg-dark), var(--card-bg-dark)),
                      linear-gradient(45deg, var(--brand-accent), var(--brand-primary));
}
[data-theme="dark"] .avatar-overlay {
    background: rgba(241, 245, 249, 0.85);
    color: #1e293b;
    border-color: var(--card-bg-dark);
}

body {
  font-family: "Inter", sans-serif;
  background-color: var(--bg-page);
  position: relative;
  overflow-x: hidden;
  padding-bottom: 90px; /* <-- Thêm dòng này vào */
}
[data-theme="dark"] .page-header h3 {
    color: var(--text-primary-dark); /* Ð?i ch? sang màu sáng */
}
/* ====== S?A L?I DARK MODE TIÊU Ð? PH? (Thêm vào) ====== */
[data-theme="dark"] .profile-card .card-title,
[data-theme="dark"] .section-subtitle {
    color: var(--text-primary-dark); /* Ð?i ch? sang màu sáng */
    border-bottom-color: var(--card-border-dark); /* Ð?i c? màu vi?n g?ch d?t cho h?p */
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
/* ====== S?A L?I N?N TR?NG C?A THANH TAB (Thêm vào) ====== */
[data-theme="dark"] .nav-tabs {
    background: transparent; /* Làm trong su?t n?n tr?ng b? dè */
    border-bottom-color: var(--card-border-dark); /* Ð?i màu vi?n du?i cho h?p */
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
/* --- STYLE CHO KH?I XÁC TH?C 2FA (B? SUNG) --- */
.content-card {
    border-radius: var(--border-radius-medium);
    border: 1px solid var(--card-border); /* Dùng border bi?n có s?n */
    background-color: var(--card-bg-light); /* Dùng n?n nh? */
    box-shadow: var(--shadow-soft);
    padding: 1rem;
    margin-bottom: 1.5rem;
    transition: var(--transition-smooth);
}
[data-theme="dark"] .content-card {
    background-color: var(--card-bg-dark);
    border-color: var(--card-border-dark);
}
.content-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.section-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-strong);
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
    border-bottom: 1px dashed var(--card-border);
}
.section-card-header i {
    color: var(--brand-primary);
    font-size: 1.5rem;
}
[data-theme="dark"] .section-card-header {
    color: var(--text-primary-dark);
    border-bottom-color: var(--card-border-dark);
}

/* Tinh ch?nh riêng cho Switch 2FA */
#toggle-2fa-switch {
    width: 3.2rem !important; /* L?n hon m?t chút */
    height: 1.8rem !important; /* L?n hon m?t chút */
    border-radius: 2rem !important;
}
.d-flex.justify-content-between.align-items-center > div:first-child {
    d-flex-grow: 1; /* Ð?m b?o n?i dung chi?m h?t không gian */
}

/* --- STYLE CHO KH?I LIÊN K?T GOOGLE (B? SUNG) --- */
.google-link-card {
    background: var(--card-bg-light) !important; /* S? d?ng bi?n cho d?ng b? */
    border: 1px solid var(--card-border); /* Dùng border bi?n có s?n */
    border-radius: var(--border-radius-medium);
    padding: 1rem !important;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-smooth);
}
[data-theme="dark"] .google-link-card {
    background: var(--card-bg-dark) !important;
    border-color: var(--card-border-dark);
}
.google-link-card:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.google-link-card .btn.btn-sm {
    /* Thi?t l?p l?i nút Google d? d?ng b? v?i nút khác */
    border-color: #e0e0e0;
    color: var(--text-strong);
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
.google-link-card .btn btn-sm:hover {
    border-color: var(--brand-accent);
    color: var(--brand-primary);
    background-color: var(--blue-light);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
}

/* Nút ng?t liên k?t Google */
.btn btn-sm-unlink-google {
    background: #dc3545;
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: var(--border-radius-small);
    box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
    transition: all 0.3s ease;
}
.btn btn-sm-unlink-google:hover {
    background: #c82333;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4);
}

/* Tinh ch?nh Modal 2FA */
.relative bg-white rounded-xl shadow-xl table-bordered d-flex d-flex-col {
    border-radius: var(--border-radius-large);
    backdrop-filter: blur(20px);
    background: var(--card-bg) !important;
    box-shadow: var(--shadow-medium);
}
[data-theme="dark"] .relative bg-white rounded-xl shadow-xl table-bordered d-flex d-flex-col {
    background: var(--card-bg-dark) !important;
    color: var(--text-primary-dark);
}
.d-flex align-items-center justify-content-between p-4 border-b rounded-t-xl {
    border-bottom: 1px solid var(--card-border);
}
[data-theme="dark"] .d-flex align-items-center justify-content-between p-4 border-b rounded-t-xl {
    border-bottom-color: var(--card-border-dark);
}
#qrCodeContainer img {
    border: 5px solid var(--brand-primary);
    border-radius: var(--border-radius-small);
    box-shadow: 0 0 20px rgba(37, 99, 235, 0.2);
}
#secretKeyContainer {
    font-weight: 700;
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

.w-full max-w-7xl mx-auto px-4 sm:px-4 lg:px-5 {
  position: relative;
  z-index: 1;
}

.main-content { font-family: 'Inter', sans-serif; }

/* Page Header */
.page-header h3 {
    font-weight: 700;
    color: var(--text-strong);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.75rem;
}
.page-header h3 i {
    color: var(--brand-primary);
}

/* ====== PROFILE CARD & SETTINGS CARD (Ð?ng b? Glassmorphism + Hi?u ?ng) ====== */
.profile-card,
.settings-card {
  border: none;
  border-radius: 1.5rem;
  background: var(--card-bg);
  backdrop-filter: blur(20px) saturate(180%);
  box-shadow: 0 10px 40px rgba(0,0,0,0.08),
              0 0 0 1px rgba(255,255,255,0.5) inset;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  animation: fadeUp 0.8s ease forwards;
  opacity: 0;
}

.profile-card::before,
.settings-card::before {
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

.profile-card:hover,
.settings-card:hover {
  transform: translateY(-8px) scale(1.01);
  box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15),
              0 0 0 1px rgba(255,255,255,0.8) inset;
}

.profile-card:hover::before,
.settings-card:hover::before {
  opacity: 1;
}

.settings-card { 
  animation-delay: 0.2s; 
}

/* ====== NÂNG C?P: AVATAR STYLES (Ð?ng b? t? giao_vu.php) ====== */
.profile-avatar {
    width: 140px; 
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
    z-index: 1;
    position: relative;
}
.profile-avatar:hover {
    transform: scale(1.05);
}

/* Avatar spin animation (from giao_vu.php) */
@keyframes spin-glow {
  0% { box-shadow: 0 0 0 rgba(37,99,235,0.0); transform: rotate(0deg); }
  50% { box-shadow: 0 0 30px rgba(37,99,235,0.25); }
  100% { transform: rotate(360deg); }
}
.profile-avatar::after {
  content: "";
  position: absolute;
  top: -8px;
  left: -8px;
  width: 156px; /* 140px + 4px border*2 + 8px gap */
  height: 156px;
  border-radius: 50%;
  border: 2px dashed rgba(96,165,250,0.4);
  animation: spin-glow 8s linear infinite;
  z-index: -1;
}

/* Overlay for 'coming soon' feature */
#avatar-wrapper {
    cursor: pointer;
    position: relative; 
    display: d-inline-block;
}

.avatar-overlay {
    position: absolute;
    bottom: 1rem; /* Ngay trên mb-4 c?a img */
    right: 0;
    width: 40px;
    height: 40px;
    background: rgba(30, 41, 59, 0.85);
    backdrop-filter: blur(5px);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    border: 2px solid white;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
    z-index: 2;
}

#avatar-wrapper:hover .avatar-overlay {
    opacity: 1;
    transform: scale(1);
}

/* CSS cho ul (b? text-center) */
.profile-card .list-group {
    text-align: left;
}
/* ========================================================== */

/* Profile Card - So y?u lý l?ch */
.profile-card .p-4 { padding: 1.5rem; }
.profile-card .card-title {
    font-weight: 700;
    color: var(--text-strong);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px dashed var(--card-border);
}
.profile-card .list-group-item {
    padding: .75rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: transparent;
    border-left: none; border-right: none;
}
.profile-card .list-group-item:last-child { border-bottom: none; }
.profile-card strong {
    color: var(--text-strong); /* Label d?m hon */
    width: 90px;
    display: d-inline-block;
    flex-shrink: 0;
    font-size: 0.95rem; /* Tang c? ch? label */
}
.profile-card span {
    color: var(--text-strong);
    font-weight: 500;
    text-align: right;
    font-size: 0.9rem;
}

/* ====== TABS (Ð?ng b?) ====== */
.settings-card .nav-tabs {
  border-bottom: 2px solid rgba(0,0,0,0.06);
  background: linear-gradient(180deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.9) 100%);
  backdrop-filter: blur(10px);
  border-radius: 1.5rem 1.5rem 0 0;
  padding: 0.5rem 0.75rem;
  position: relative;
}

.settings-card .nav-tabs .nav-link {
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

.settings-card .nav-tabs .nav-link::before {
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

.settings-card .nav-tabs .nav-link:hover {
  color: var(--brand-primary);
  background: linear-gradient(180deg, rgba(37,99,235,0.08) 0%, rgba(37,99,235,0.04) 100%);
  transform: translateY(-2px);
}

.settings-card .nav-tabs .nav-link.active {
  color: var(--brand-primary);
  background: linear-gradient(180deg, rgba(37,99,235,0.12) 0%, transparent 100%);
}

.settings-card .nav-tabs .nav-link.active::before {
  transform: translateX(-50%) scaleX(1);
}
.settings-card .nav-tabs .nav-link i {
    font-size: 1.1em;
    line-height: 1;
}

.tab-content { padding: 1.5rem; }

/* Section Subtitle (Ð?ng b?) */
.section-subtitle {
    font-weight: 700;
    color: var(--text-strong);
    display: flex;
    align-items: center;
    gap: .5rem;
    border-bottom: 1px dashed var(--card-border);
    padding-bottom: .5rem;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}
.section-subtitle i { color: var(--brand-primary); }

/* ====== FORM ELEMENTS (Ð?ng b?) ====== */
.form-label { font-weight: 600; color: var(--text-strong); }
.form-control, .form-control {
    border-radius: var(--border-radius-small);
    background-color: var(--bg-light);
    border: 1px solid var(--card-border);
    transition: var(--transition-smooth);
    padding: 0.6rem 0.9rem;
}
.form-control:focus, .form-control:focus {
    background-color: #fff;
    border-color: var(--brand-primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.form-control:disabled { background-color: #e9ecef; opacity: 0.7; }
.input-group .btn.btn-sm { border-radius: 0 var(--border-radius-small) var(--border-radius-small) 0 !important; }
.input-group .form-control { border-radius: var(--border-radius-small) 0 0 var(--border-radius-small) !important; }
#email-change-section { border-radius: var(--border-radius-medium); }

/* Switch Toggle (Ð?ng b?) */
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50[type="checkbox"] {
    width: 2.8rem; height: 1.5rem; border-radius: 1rem;
    background-color: #d6dee3; border: none; position: relative;
    transition: backgrouncolor 0.3s ease; cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    background-position: left center;
    background-repeat: no-repeat;
    background-size: contain;
}
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50[type="checkbox"]:checked {
    background-color: var(--brand-primary);
    background-position: right center;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
}

/* ====== NÂNG C?P NÚT B?M V2 (Tinh T?) ====== */
.btn.btn-sm { 
    border-radius: var(--border-radius-small) !important; 
    font-weight: 600; 
    transition: var(--transition-smooth); 
    padding: 0.6rem 1.2rem; /* Padding chu?n, không b? */
    border-width: 1px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.07); /* Bóng nh? */
}

/* Nút "Luu Thay Ð?i" (Màu tr?ng) */
.bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600 {
    color: var(--brand-primary);
    border-color: var(--brand-primary);
    background-color: #fff; 
    border-width: 1.5px; 
    box-shadow: var(--shadow-soft);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600:hover {
    background: var(--brand-gradient);
    border-color: transparent;
    color: white;
    transform: translateY(-3px) scale(1.05); 
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3); 
}

/* CSS cho các nút khác (G?i OTP, Xác nh?n) */
.bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent {
    background: var(--brand-gradient);
    border: none;
    color: white;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
}
.bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:disabled { /* Style cho nút 'G?i l?i sau' */
    background-color: #a5b4fc;
    border-color: #a5b4fc;
    box-shadow: none;
    transform: none;
}

.btn-success { 
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    border: none;
    color: white;
    box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.btn-success:hover { 
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(25, 135, 84, 0.4);
}

/* ====== KEYFRAMES (Ð?ng b?) ====== */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Tab animations */
.tab-pane {
  opacity: 0;
  /* S?a 0.5s -> 0.25s (dã s?a ? file giao_vu) */
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); 
  transform: translateY(20px) scale(0.98);
}

.tab-pane.active.show {
  opacity: 1;
  transform: translateY(0) scale(1);
  /* S?a 0.5s -> 0.25s (dã s?a ? file giao_vu) */
  animation: slideInTab 0.25s cubic-bezier(0.4, 0, 0.2, 1); 
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

@media (max-width: 768px) {
  .profile-card, .settings-card { border-radius: 0.75rem; }
  .tab-content { padding: 1rem; }
  .settings-card .nav-tabs .nav-link { padding: 0.7rem 0.9rem; font-size: 0.9rem; }
  .shape { filter: blur(30px); }
  .shape-1, .shape-2, .shape-3, .shape-4, .shape-5 {
    width: 200px !important;
    height: 200px !important;
  }
  /* Thêm thu g?n */
  .w-full max-w-7xl mx-auto px-4 sm:px-4 lg:px-5 {
     padding-left: 0.75rem;
     padding-right: 0.75rem;
  }
  .page-header h3 {
     font-size: 1.3rem;
  }
  .profile-card .p-4 { padding: 1rem; }
  .profile-details li { padding: 0.5rem 0; font-size: 0.85rem; }
  .profile-details li strong { width: 90px; }
  .btn.btn-sm { padding: 0.5rem 0.9rem; font-size: 0.9rem; }
}
@media (max-width: 768px) {
  .settings-card .nav-tabs {
    display: flex;
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
  }

  .settings-card .nav-tabs .nav-item {
    flex-shrink: 0;
  }

  .settings-card .nav-tabs .nav-link {
    min-width: 140px; /* giúp d? ch?m */
    text-align: center;
  }
}
@media (max-width: 991px) {
  .profile-card.sticky-top {
    position: static !important;
    top: auto !important;
  }
}
/* B? c?c 1 c?t - th? So y?u lý l?ch trên tab */
.profile-card {
  margin-bottom: 1.5rem;
}

.settings-card {
  margin-top: 0;
}

/* Tang nh? kho?ng cách d? 2 kh?i nhìn “li?n m?ch” */
@media (min-width: 992px) {
  .profile-card {
    margin-bottom: 2rem;
  }
}
/* === FIX: Liên k?t Google hi?n th? d?p trên mobile === */
.google-link-card .d-flex.align-items-center.d-flex-grow-1 {
  min-width: 0; /* Cho phép text bên trong co l?i */
}

#linked-email {
  display: d-block;
  max-width: 160px; /* Gi?i h?n chi?u r?ng d? tránh d?ng nút */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.85rem;
}

#linked-action-buttons {
  flex-shrink: 0; /* Gi? nút không b? ép nh? */
  margin-left: 0.5rem;
}

#btn btn-sm-change-google-link {
  white-space: nowrap;
  padding: 0.4rem 0.8rem;
  font-size: 0.85rem;
}

/* Mobile responsive fix */
@media (max-width: 576px) {
  #linked-email {
    max-width: 110px;
    font-size: 0.8rem;
  }
  #btn btn-sm-change-google-link {
    padding: 0.35rem 0.7rem;
    font-size: 0.8rem;
  }
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

<div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5">
    <div class="page-header mb-6">
        <h3><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge mr-2 text-primary-600" viewBox="0 0 16 16"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>   <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492z"/></svg>THÔNG TIN CÁ NHÂN</h3>
    </div>

    <div class="flex flex-wrap -mx-3 g-4">
  <div class="w-full px-6">
      <div class="profile-card mb-6">
          <div class="p-6 text-center">
              
              <div id="avatar-wrapper" title="Ð?i ?nh d?i di?n (Ðang phát tri?n)">
                  <img src="<?php echo !empty($student_info['anh_the']) ? '/thidua/public/assets/anh_the/' . htmlspecialchars($student_info['anh_the']) : '/thidua/public/assets/img/anhthegoc.JPG'; ?>"
                       alt="Avatar"
                       class="profile-avatar mb-6"
                       onerror="this.onerror=null;this.src='/thidua/public/assets/img/anhthegoc.JPG';">
                  
                  <div class="avatar-overlay">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-camera-fill" viewBox="0 0 16 16"><path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>   <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1m9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0"/></svg>
                  </div>
              </div>

              <h5 class="text-lg font-semibold text-slate-800 font-bold mb-6 mt-2">So y?u lý l?ch: </h5>
              <ul class="list-group list-group-flush profile-details text-left">
                  <li class="list-group-item"><strong>H? và tên:</strong> <span><?= htmlspecialchars($student_info['ho_dem'] . ' ' . $student_info['ten']); ?></span></li>
                  <li class="list-group-item"><strong>S? CCCD:</strong> <span><?= htmlspecialchars($student_info['ma_hoc_sinh']); ?></span></li>
                  <li class="list-group-item"><strong>Tr?ng thái:</strong> 
                      <span>
                          <?php
                            $status = $student_info['trang_thai_hoc_tap'] ?? 'dang_hoc';
                            echo $status === 'dang_hoc'
                              ? '<span class="font-bold text-green-600">Ðang h?c</span>'
                              : '<span class="font-bold text-red-600">Ðã ngh? h?c</span>';
                          ?>
                      </span>
                  </li>
                  <li class="list-group-item"><strong>L?p:</strong> <span><?= htmlspecialchars($student_info['ten_lop']); ?></span></li>
                  <li class="list-group-item"><strong>GVCN:</strong> <span><?= htmlspecialchars($student_info['gvcn_ten'] ?? 'Chua c?p nh?t'); ?></span></li>
                  <li class="list-group-item"><strong>Ngày sinh:</strong> <span><?= htmlspecialchars(format_date_display($student_info['ngay_sinh'] ?? '')); ?></span></li>
                  <li class="list-group-item"><strong>Gi?i tính:</strong> <span><?= htmlspecialchars($student_info['gioi_tinh']); ?></span></li>
              </ul>
          </div>
      </div>
      <div class="settings-card">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="profile-tab" type="button">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16"><path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/></svg> Liên h?
                  </button>
              </li>
              <li class="nav-item" role="presentation">
                  <button class="nav-link" id="password-tab" type="button">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16"><path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg> M?t kh?u
                  </button>
              </li>
          </ul>

                <div class="tab-content" id="myTabContent">
              
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <form id="profileForm">
                             <h5 class="section-subtitle"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>Thông Tin liên h?</h5>
                             <div class="flex flex-wrap -mx-3">
                                <div class="w-full md:w-5/12 px-6 mb-6">
                                    <label for="chuc_vu" class="block text-sm font-medium text-slate-700 mb-1">Ch?c v?</label>
                                    <select class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="chuc_vu" name="chuc_vu" <?php if (empty($settings['can_edit_chuc_vu'])) echo 'disabled'; ?>>
                                        <?php
                                        $options = ['H?c sinh', 'L?p tru?ng', 'L?p phó', 'Bí thu'];
                                        $current_chuc_vu = $student_info['chuc_vu'] ?? 'H?c sinh';
                                        foreach ($options as $option) {
                                            $selected = ($current_chuc_vu === $option) ? 'selected' : '';
                                            echo "<option value=\"".htmlspecialchars($option)."\" $selected>".htmlspecialchars($option)."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-7 mb-6">
                                    <label for="sdt" class="block text-sm font-medium text-slate-700 mb-1">S? di?n tho?i</label>
                                    <input type="tel" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="sdt" name="sdt" value="<?php echo htmlspecialchars($student_info['sdt'] ?? ''); ?>" <?php if (empty($settings['can_edit_sdt'])) echo 'disabled'; ?>>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                <div class="flex w-full">
                                    <input type="email" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="email" name="email" value="<?php echo htmlspecialchars($student_info['email'] ?? ''); ?>" disabled>
                                    <button class="btn bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600" type="button" id="btn btn-sm-change-email" <?php if (empty($settings['can_edit_email'])) echo 'disabled'; ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-fill mr-1" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg> Ð?i
                                    </button>
                                </div>
                                <div id="email-change-section" class="mt-2 p-6 border rounded-lg bg-slate-50" style="display:none;">
                                    <div class="flex w-full mb-2">
                                        <input type="email" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="new_email" placeholder="Nh?p Gmail m?i">
                                        <button class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent" type="button" id="btn btn-sm-send-otp">G?i OTP</button>
                                    </div>
                                    <div id="otp-section" style="display:none;" class="mt-2">
                                        <div class="flex w-full">
                                             <input type="text" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="otp_code" placeholder="Nh?p mã OTP" inputmode="numeric" pattern="[0-9]*">
                                             <button class="btn bg-green-600 hover:bg-green-700 text-white shadow-sm border-transparent" type="button" id="btn btn-sm-verify-otp">Xác nh?n</button>
                                        </div>
                                    </div>
                                    <div id="email-flow-status" class="text-slate-500 mt-2 small"></div>
                                </div>
                            </div>
                            <div class="flex items-center form-switch mb-6">
                                <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" role="switch" id="nhan_thong_bao" name="nhan_thong_bao"
                                       <?php echo ($student_info['nhan_thong_bao_vi_pham'] ?? 0) == 1 ? 'checked' : ''; ?>
                                       <?php echo empty($student_info['email']) ? 'disabled' : ''; ?>>
                                <label class="ml-2 block text-sm text-slate-900" for="nhan_thong_bao">Ðang ký nh?n thông báo qua Email</label>
                                <?php if (empty($student_info['email'])): ?>
                                    <div class="form-text text-red-600 small mt-1">B?n c?n c?p nh?t email d? s? d?ng tính nang này.</div>
                                <?php endif; ?>
                            </div>
                            <div class="content-card">
    <div class="section-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"/></svg>
        <span>Xác Th?c 2 Y?u T? (2FA)</span>
    </div>
    <div class="section-p-4">
        <div class="flex justify-between items-center">
            <div>
                <h6 class="mb-1 font-bold">Tr?ng thái 2FA</h6>
                <small class="text-slate-500">S? d?ng app Google Authenticator.</small>
            </div>
            
            <div class="flex items-center form-switch ml-4">
                <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" role="switch" 
                       id="toggle-2fa-switch"
                       style="width: 3rem; height: 1.5rem;"
                       <?php echo $is_2fa_enabled ? 'checked' : ''; ?>>
            </div>
        </div>
    </div>
</div>
<h5 class="section-subtitle"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>   <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/></svg>Liên k?t Tài kho?n</h5>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 mb-6 google-link-card">
    <div class="p-6 p-6">
        <p class="small text-slate-500 mb-6">Liên k?t tài kho?n Google d? dang nh?p nhanh hon mà không c?n m?t kh?u.</p>
        
        <?php if (!empty($student_info['google_id'])): ?>
            <div class="flex justify-between items-center">
                <div class="flex items-center d-flex-grow-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-google text-xl mr-2" viewBox="0 0 16 16"><path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z"/></svg>
                    <div>
                        <strong class="block">Ðã liên k?t v?i Google</strong>
                        <span class="small text-slate-500" id="linked-email">
  <?php 
    $email = htmlspecialchars($student_info['verified_email'] ?? 'Không rõ email'); 
    echo strlen($email) > 30 ? substr($email, 0, 27) . '...' : $email;
  ?>
</span>

                    </div>
                </div>
                
                <div id="linked-action-buttons">
                    <button type="button" class="btn bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600 px-6 py-1.5 text-sm mr-2" id="btn btn-sm-change-google-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-repeat mr-1" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>   <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg>
                    </button>
                    
                </div>
            </div>
            <div id="google-relink-section" class="mt-6 pt-6 border-top hidden">
                 <p class="small text-yellow-600 mb-2"></p>
                 <a href="/thidua/oauth-redirect-google" class="btn btn w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-google mr-2" viewBox="0 0 16 16"><path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z"/></svg>
                    Liên k?t v?i tài kho?n Google m?i
                 </a>
            </div>

        <?php else: ?>
            <a href="/thidua/oauth-redirect-google" class="btn btn w-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-google mr-2" viewBox="0 0 16 16"><path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z"/></svg>
                Liên k?t v?i tài kho?n Google
            </a>
        <?php endif; ?>
    </div>
</div>
                            <div class="text-center mt-6">
                                <button type="submit" class="btn bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600">
                   <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-magic mr-2" viewBox="0 0 16 16"><path d="M9.5 2.672a.5.5 0 1 0 1 0V.843a.5.5 0 0 0-1 0zm4.5.035A.5.5 0 0 0 13.293 2L12 3.293a.5.5 0 1 0 .707.707zM7.293 4A.5.5 0 1 0 8 3.293L6.707 2A.5.5 0 0 0 6 2.707zm-.621 2.5a.5.5 0 1 0 0-1H4.843a.5.5 0 1 0 0 1zm8.485 0a.5.5 0 1 0 0-1h-1.829a.5.5 0 0 0 0 1zM13.293 10A.5.5 0 1 0 14 9.293L12.707 8a.5.5 0 1 0-.707.707zM9.5 11.157a.5.5 0 0 0 1 0V9.328a.5.5 0 0 0-1 0zm1.854-5.097a.5.5 0 0 0 0-.706l-.708-.708a.5.5 0 0 0-.707 0L8.646 5.94a.5.5 0 0 0 0 .707l.708.708a.5.5 0 0 0 .707 0l1.293-1.293Zm-3 3a.5.5 0 0 0 0-.706l-.708-.708a.5.5 0 0 0-.707 0L.646 13.94a.5.5 0 0 0 0 .707l.708.708a.5.5 0 0 0 .707 0z"/></svg> Luu Thay Ð?i

                                </button>
                            </div>

                        </form>
                    </div>

              
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <h5 class="section-subtitle"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"/></svg>Thay Ð?i M?t Kh?u</h5>
                        <form id="passwordForm" action="/thidua/hocsinh/doi-mat-khau-xu-ly" method="POST">
                            <div class="mb-6">
                                <label for="old_password" class="block text-sm font-medium text-slate-700 mb-1">M?t kh?u cu</label>
                                <input type="password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="old_password" name="old_password" required>
                            </div>
                            <div class="mb-6">
                                <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1">M?t kh?u m?i</label>
                                <input type="password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-6">
                                <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1">Xác nh?n m?t kh?u m?i</label>
                                <input type="password" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="confirm_password" name="confirm_password" required>
                            </div>
                            
          <div class="text-center mt-6">
  <button type="submit" class="btn bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600">
    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-check mr-2" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg> Xác Nh?n
  </button>
</div>
        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/ctv_footer.php'; ?>
<div class="modal fade" id="2faModal" tabindex="-1" aria-labelledby="2faModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content relative bg-white rounded-xl shadow-xl border border-slate-200 flex flex-col">
            <div class="flex items-center justify-between p-6 border-b rounded-t-xl">
                <h5 class="text-lg font-semibold text-slate-900" id="2faModalLabel"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-plus mr-2" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M8 4.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V9a.5.5 0 0 1-1 0V7.5H6a.5.5 0 0 1 0-1h1.5V5a.5.5 0 0 1 .5-.5"/></svg>Kích ho?t Xác th?c 2 Y?u T?</h5>
                <button type="button" class="text-slate-400 hover:text-slate-500 p-2" aria-label="Close"></button>
            </div>
            <div class="p-6 space-y-4 text-center">
                <p>Vui lòng quét mã QR này b?ng ?ng d?ng Google Authenticator (ho?c Authy) c?a b?n.</p>
                
                <div id="qrCodeContainer" class="my-6" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                    <div class="spinner-border text-primary-600" role="status">
                        <span class="visually-hidden">Ðang t?o mã...</span>
                    </div>
                </div>
                
                <p class="mt-6">Ho?c, nh?p th? công mã bí m?t:</p>
                <code id="secretKeyContainer" class="text-base text-red-600" style="word-wrap: break-word;">...</code>
                
                <hr>
                <p class="font-bold">Quan tr?ng: Sau khi quét, hãy nh?p 6 s? t? ?ng d?ng c?a b?n d? xác nh?n.</p>
                
                <div class="form-floating my-6">
                    <input type="text" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" id="2fa_code_verify" placeholder="123456" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="off">
                    <label for="2fa_code_verify">Nh?p mã 6 s? d? xác nh?n</label>
                </div>
            </div>
            <div class="flex items-center justify-end p-6 border-t space-x-2 rounded-b-xl">
                <button type="button" class="btn bg-slate-600 hover:bg-slate-700 text-white shadow-sm border-transparent">Ð? sau</button>
                <button type="button" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent" id="confirm2faBtn" disabled>Xác nh?n & Kích ho?t</button>
            </div>
        </div>
    </div>
</div>
<script>
// JavaScript v?i tab animations
document.addEventListener('DOMContentLoaded', function() {
    
    // === NÂNG C?P: THÊM S? KI?N CLICK CHO AVATAR ===
    const avatarWrapper = document.getElementById('avatar-wrapper');
    if (avatarWrapper) {
        avatarWrapper.addEventListener('click', () => {
            alert('Tính nang dang phát tri?n. H? th?ng chua cho phép d?i ?nh h?c sinh, vui lòng liên h? Admin d? c?p nh?t.');
        });
    }
    // ===========================================


    // ====== Tab Animation Handler ======
    // (Ðã xóa b? theo yêu c?u tru?c dó d? fix gi?t)
    
    const profileForm = document.getElementById('profileForm');
    const emailInput = document.getElementById('email');
    const originalEmail = emailInput ? emailInput.value : '';
    
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            data.email = originalEmail;
            data.nhan_thong_bao = document.getElementById('nhan_thong_bao').checked;

            try {
                const response = await fetch('/thidua/api/ctv-cap-nhat-thong-tin', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) location.reload();
            } catch (error) {
                alert('L?i k?t n?i!');
            }
        });
    }
// ===== B?T Ð?U CODE 2FA NÂNG C?P (BU?C 3 - ÐÃ S?A L?I LOGIC) =====
    
    // L?y các element c?a Modal (Modal này v?n du?c dùng d? B?T 2FA)
    const fa2ModalEl = document.getElementById('2faModal');
    const fa2Modal = null /* Removed Bootstrap Modal */;
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const secretKeyContainer = document.getElementById('secretKeyContainer');
    const confirm2faBtn = document.getElementById('confirm2faBtn');
    const verifyCodeInput = document.getElementById('2fa_code_verify');
    let current2faSecret = ''; // Bi?n t?m

    // L?y công t?c (toggle) m?i
    const faToggleSwitch = document.getElementById('toggle-2fa-switch');

    if (faToggleSwitch) {
        // L?ng nghe s? ki?n CLICK
        faToggleSwitch.addEventListener('click', async function(e) {
            // NGAN CH?N CÔNG T?C T? L?T NGAY L?P T?C
            e.preventDefault(); 
            
            // L?y tr?ng thái M?I (s?p du?c set)
            const isTryingToEnable = this.checked; 

            // ===== S?A L?I: Ð?O NGU?C LOGIC IF/ELSE =====
            
            if (isTryingToEnable) { 
                // === HÀNH Ð?NG: NGU?I DÙNG MU?N B?T (T? T?T -> B?T) ===
                // M? modal d? quét mã QR
                fa2Modal.show();

            } else {
                // === HÀNH Ð?NG: NGU?I DÙNG MU?N T?T (T? B?T -> T?T) ===
                const code = prompt(
                    "C?NH BÁO:\nB?n có ch?c ch?n mu?n t?t Xác th?c 2 y?u t? không?\n\n" +
                    "Ð? xác nh?n, vui lòng nh?p mã 6 s? t? ?ng d?ng Authenticator c?a b?n:"
                );

                if (code === null || code.trim() === '') {
                    return; // Ngu?i dùng b?m H?y
                }
                
                try {
                    // G?i API T?T 2FA
                    const response = await fetch('/thidua/api/2fa-disable', { 
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ code: code.trim() })
                    });
                    
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Mã 6 s? không chính xác.');
                    }

                    // T?t thành công!
                    alert(data.message);
                    location.reload(); // T?i l?i trang d? c?p nh?t giao di?n

                } catch (error) {
                    alert('L?i khi t?t 2FA: ' + error.message);
                }
            }
        });
    }
    
const btnChangeGoogleLink = document.getElementById('btn btn-sm-change-google-link');
const btnGoogleUnlink = document.getElementById('btn btn-sm-google-unlink');
const googleRelinkSection = document.getElementById('google-relink-section');

// 1. Logic cho nút "Thay Ð?i"
if (btnChangeGoogleLink) {
    btnChangeGoogleLink.addEventListener('click', () => {
        // ?n nút "Thay Ð?i"
        btnChangeGoogleLink.classList.add('hidden');
        // Hi?n nút "H?y liên k?t"
        btnGoogleUnlink?.classList.remove('hidden');
        // Hi?n ph?n Thay d?i liên k?t
        googleRelinkSection?.classList.remove('hidden');
    });
}

// 2. Logic cho nút "H?y liên k?t" (S? d?ng l?i logic dã d? xu?t tru?c dó)
if (btnGoogleUnlink) {
    btnGoogleUnlink.addEventListener('click', async () => {
        if (!confirm("B?n có ch?c ch?n mu?n H?Y liên k?t v?i tài kho?n Google này không? B?n s? c?n nh?p m?t kh?u d? dang nh?p l?n sau.")) {
            return;
        }

        btnGoogleUnlink.disabled = true;
        btnGoogleUnlink.textContent = 'Ðang h?y...';

        try {
            const response = await fetch('/thidua/api/google-unlink', { // Gi? d?nh endpoint này t?n t?i
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'L?i khi h?y liên k?t.');
            }

            alert(data.message || 'Ðã h?y liên k?t Google thành công!');
            location.reload();

        } catch (error) {
            alert('L?i: ' + error.message);
            btnGoogleUnlink.disabled = false;
            btnGoogleUnlink.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-circle-fill mr-1" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg> H?y liên k?t';
        }
    });
}
    // S? ki?n 'shown.bs.modal' (Khi modal quét QR hi?n ra)
    // (Logic này gi? nguyên, nó dã dúng)
    fa2ModalEl.addEventListener('shown.bs.modal', async () => {
        // Ð?t l?i modal v? tr?ng thái loading
        qrCodeContainer.innerHTML = '<div class="spinner-border text-primary-600" role="status"><span class="visually-hidden">Ðang t?o mã...</span></div>';
        secretKeyContainer.textContent = '...';
        confirm2faBtn.disabled = true; 
        verifyCodeInput.value = '';
        
        try {
            const response = await fetch('/thidua/api/2fa-generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            
            const data = await response.json(); 

            if (!response.ok || !data.success) {
                let errorHtml = `
                    <div class="p-6 mb-6 rounded-lg border bg-red-50 text-red-800 border-red-200">
                        <strong>L?i máy ch?:</strong> ${data.message || 'Không rõ l?i.'}
                        <hr>
                        <strong class'text-dark'>Debug Trace:</strong>
                        <pre style='font-size: 0.75rem; text-align: left; white-space: pre-wrap; word-break: break-all;'>${data.debug_trace || 'Không có trace.'}</pre>
                    </div>`;
                qrCodeContainer.innerHTML = errorHtml;
                secretKeyContainer.textContent = 'Không th? t?i';
                return; 
            }
            
            // N?u thành công
            qrCodeContainer.innerHTML = `<img src="${data.qr_image_data_uri}" alt="QR Code 2FA" class="img-fluid">`;
            secretKeyContainer.textContent = data.secret_key;
            current2faSecret = data.secret_key; 
            confirm2faBtn.disabled = false; 

        } catch (error) {
            qrCodeContainer.innerHTML = `<div class="p-6 mb-6 rounded-lg border bg-red-50 text-red-800 border-red-200">L?i k?t n?i: ${error.message}</div>`;
            secretKeyContainer.textContent = 'Không th? t?i';
        }
    });

    // Nút "Xác nh?n & Kích ho?t" trong modal (B?t 2FA)
    // (Logic này gi? nguyên, nó dã dúng)
    if (confirm2faBtn) {
        confirm2faBtn.addEventListener('click', async () => {
            const code = verifyCodeInput.value.trim();
            
            if (code.length !== 6 || !/^\d+$/.test(code)) {
                alert('Vui lòng nh?p mã 6 s? h?p l? t? ?ng d?ng.');
                return;
            }

            confirm2faBtn.disabled = true;
            confirm2faBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Ðang xác th?c...';

            try {
                const response = await fetch('/thidua/api/2fa-verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code: code })
                });
                
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Mã 6 s? không chính xác.');
                }
                
                alert('B?t 2FA thành công!');
                fa2Modal.hide();
                location.reload(); 

            } catch (error) {
                alert('L?i: ' + error.message);
                confirm2faBtn.disabled = false;
                confirm2faBtn.innerHTML = 'Xác nh?n & Kích ho?t';
            }
        });
    }
    // ===== K?T THÚC CODE 2FA NÂNG C?P =====
    const btnChangeEmail = document.getElementById('btn btn-sm-change-email');
    const emailChangeSection = document.getElementById('email-change-section');
    const btnSendOtp = document.getElementById('btn btn-sm-send-otp');
    const otpSection = document.getElementById('otp-section');
    const btnVerifyOtp = document.getElementById('btn btn-sm-verify-otp');
    const flowStatus = document.getElementById('email-flow-status');
    const newEmailInput = document.getElementById('new_email');
    const otpCodeInput = document.getElementById('otp_code');

    btnChangeEmail?.addEventListener('click', () => {
        emailChangeSection.style.display = 'd-block';
        flowStatus.textContent = '';
        newEmailInput?.focus();
    });

    btnSendOtp?.addEventListener('click', async () => {
        const newEmail = newEmailInput?.value.trim();
        flowStatus.textContent = '';
        if (!newEmail) {
            alert('Vui lòng nh?p Gmail m?i.');
            return;
        }
        btnSendOtp.disabled = true;
        btnSendOtp.textContent = 'Ðang g?i...';
        let countdownInterval;

        try {
            const response = await fetch('/thidua/api/ctv-send-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: newEmail })
            });
            const result = await response.json();
            if (result.success) {
                otpSection.style.display = 'd-block';
                flowStatus.classList.remove('text-danger');
                flowStatus.classList.add('text-success');
                flowStatus.textContent = 'Mã OTP dã du?c g?i. Vui lòng ki?m tra h?p thu (Spam) và nh?p mã d? xác nh?n.';
                otpCodeInput?.focus();

                let countdown = 300;
                btnSendOtp.textContent = `G?i l?i sau (${countdown})`;
                countdownInterval = setInterval(() => {
                    countdown--;
                    if (countdown > 0) {
                        btnSendOtp.textContent = `G?i l?i sau (${countdown})`;
                    } else {
                        clearInterval(countdownInterval);
                        btnSendOtp.disabled = false;
                        btnSendOtp.textContent = 'G?i OTP';
                    }
                }, 1000);

            } else {
                throw new Error(result.message || 'Không th? g?i OTP.');
            }
        } catch (error) {
            flowStatus.classList.add('text-danger');
            flowStatus.textContent = error.message;
            clearInterval(countdownInterval);
            btnSendOtp.disabled = false;
            btnSendOtp.textContent = 'G?i OTP';
        }
    });

    btnVerifyOtp?.addEventListener('click', async () => {
        const otpCode = otpCodeInput?.value.trim();
        flowStatus.textContent = '';
        if (!otpCode) {
            alert('Vui lòng nh?p mã OTP.');
            return;
        }
        btnVerifyOtp.disabled = true;
        btnVerifyOtp.textContent = 'Ðang xác nh?n...';
        try {
            const response = await fetch('/thidua/api/ctv-verify-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ otp: otpCode })
            });
            const result = await response.json();
            if (result.success) {
                alert('Ð?i email thành công!');
                location.reload();
            } else {
                throw new Error(result.message || 'OTP không h?p l?.');
            }
        } catch (error) {
            flowStatus.classList.add('text-danger');
            flowStatus.textContent = error.message;
        } finally {
            btnVerifyOtp.disabled = false;
            btnVerifyOtp.textContent = 'Xác nh?n';
        }
    });
});
</script>
