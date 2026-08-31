<?php
// File: src/views/partials/ctv_header.php (Nâng c?p v3: Thêm Dark Mode Toggle)

require_once __DIR__ . '/../../lib/tracking.php';
update_activity_log();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = isset($page_title) ? htmlspecialchars($page_title) : 'C?ng CTV';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title ?> - H? th?ng Ðánh Giá Thi Ðua</title>

    <!-- Fonts -->
    <link rel="icon" type="image/x-icon" href="/thidua/public/assets/img/favicon.ico">
    
    <meta name="theme-color" content="#f0f8ff">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
        <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ["Inter", "ui-sans-serif", "system-ui", "Segoe UI", "Roboto", "Arial", "sans-serif"],
            },
            colors: {
              primary: {
                50: "#edf1fa", 100: "#dbe3f5", 200: "#b7c7eb", 300: "#93aae1", 400: "#4d71cc",
                500: "#2852b7", 600: "#21409a", 700: "#1b3580", 800: "#162a66", 900: "#11204d"
              },
              secondary: {
                50: "#fff9ec", 100: "#fff3d9", 200: "#fee7b3", 300: "#fddb8c", 400: "#fcc340",
                500: "#fdb924", 600: "#e4a720", 700: "#be8b1b", 800: "#986f16", 900: "#7c5a12"
              },
              accent: {
                50: "#fff9ec", 100: "#fff3d9", 200: "#fee7b3", 300: "#fddb8c", 400: "#fcc340",
                500: "#fdb924", 600: "#e4a720", 700: "#be8b1b", 800: "#986f16", 900: "#7c5a12"
              }
            },
            boxShadow: {
              "soft": "0 10px 25px rgba(33,64,154,0.08)",
              "soft-lg": "0 18px 45px rgba(33,64,154,0.12)"
            }
          }
        }
      }
    </script>

    <style>
      html { scroll-behavior: smooth; }
      html, body { font-family: Inter, ui-sans-serif, system-ui, "Segoe UI", Roboto, Arial, sans-serif; }
      body { overflow-x: hidden; padding-top: 60px; padding-bottom: 58px; background-color: #f4f7f9; }
      body.home-loading { overflow: hidden; }
      
      .home-page-loader {
        position: fixed; inset: 0; z-index: 9999;
        background: #E4F6FD; display: grid; place-items: center;
        opacity: 1; visibility: visible;
        transition: opacity .42s ease, visibility .42s ease;
      }
      .home-page-loader.is-hiding {
        opacity: 0; visibility: hidden; pointer-events: none;
      }
      .home-page-loader-inner {
        display: flex; flex-direction: column; align-items: center; gap: .95rem;
      }
      .home-page-loader-logo {
        width: 118px; height: 118px; object-fit: contain;
        filter: drop-shadow(0 8px 20px rgba(23, 63, 186, 0.15));
        animation: loaderFloat 1.6s ease-in-out infinite;
      }
      .home-page-loader-dots {
        display: inline-flex; align-items: center; gap: .72rem;
      }
      .home-page-loader-dots span {
        width: 17px; height: 17px; border-radius: 9999px;
        background: #1e40af; opacity: .35;
        transform: translateY(0); animation: loaderDots 1.05s ease-in-out infinite;
      }
      .home-page-loader-dots span:nth-child(2) { animation-delay: .14s; }
      .home-page-loader-dots span:nth-child(3) { animation-delay: .28s; }
      .home-page-loader-dots span:nth-child(4) { animation-delay: .42s; }
      .home-page-loader-dots span:nth-child(5) { animation-delay: .56s; }
      @keyframes loaderDots {
        0%, 80%, 100% { opacity: .35; transform: translateY(0) scale(.9); }
        40% { opacity: 1; transform: translateY(-4px) scale(1); }
      }
      @keyframes loaderFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
      }

      .app-header {
        position: fixed; top: 0; width: 100%; z-index: 1000; height: 60px;
        background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        transition: backgrouncolor 0.3s ease;
      }
      
      @media (min-width: 576px) { .app-header { height: 70px; } body { padding-top: 70px; } }
      
      .nav-action-btn { color: #64748b; background: transparent; border: none; font-size: 1.35rem; padding: 6px; border-radius: 50%; transition: all 0.2s ease; }
      .nav-action-btn:hover { background-color: rgba(37, 99, 235, 0.08); color: #2563eb; transform: scale(1.15); }
      #theme-toggle-btn i { transition: transform 0.3s ease; }
      #theme-toggle-btn:hover i { transform: scale(1.1) rotate(15deg); }
      .main-content { d-flex: 1; width: 100%; }
      .flash-w-full max-w-6xl mx-auto px-4 { margin-top: 0.75rem; }
      .logo-dark { display: none; }
      .logo-light { display: d-inline-block; }
      body[data-theme="dark"] { background-color: #0f172a; color: #f1f5f9; }
      body[data-theme="dark"] .app-header { background: rgba(15, 23, 42, 0.85); border-bottom-color: rgba(255, 255, 255, 0.1); }
      body[data-theme="dark"] .brand-title { color: #f1f5f9; }
      body[data-theme="dark"] .nav-action-btn { color: #cbd5e1; }
      body[data-theme="dark"] .nav-action-btn:hover { background-color: rgba(96, 165, 250, 0.1); color: #60a5fa; }
    </style>
        <link rel="stylesheet" href="/thidua/public/assets/css/fonts.css">
</head>
<body class="home-loading">

<!-- Loading Screen -->
<div id="pageLoader" class="home-page-loader">
  <div class="home-page-loader-inner">
    <img src="/assets/img/logo.png" alt="Loading" class="home-page-loader-logo" onerror="this.src='/thidua/public/assets/img/favicon.ico'">
    <div class="home-page-loader-dots">
      <span></span><span></span><span></span><span></span><span></span>
    </div>
  </div>
</div>
<script>
  window.addEventListener('load', () => {
    setTimeout(() => {
      document.getElementById('pageLoader').classList.add('is-hiding');
      document.body.classList.remove('home-loading');
    }, 500);
  });
</script>



<header class="navbar navbar-expand-lg navbar-light fixed-top app-header">
    <div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5 px-6 flex items-center justify-between">

        <a class="navbar-brand flex items-center me-auto" href="/thidua/hocsinh">
            <img src="/thidua/public/assets/img/22logoapp.png" alt="Logo" class="mr-2 logo-light">
            <img src="/thidua/public/assets/img/logoapp.png" alt="Logo" class="mr-2 logo-dark">
            <span class="brand-title">C?NG THÔNG TIN H?C SINH</span>
        </a>

        

    </div>
</header>

<div class="main-content w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5 flash-w-full max-w-6xl mx-auto px-6">
    <?php require_once __DIR__ . '/flash_messages.php'; ?>
