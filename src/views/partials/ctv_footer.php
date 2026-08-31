<?php
// File: src/views/partials/ctv_footer.php (Bản nâng cấp NProgress)
?>
    </div> <div class="page-loader" id="pageLoader">
        <div class="loader-bar"></div>
    </div>

    <nav class="navbar fixed-bottom bg-slate-50 border-top shadow-lg py-0" style="height: 65px;">
        <div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5 h-100 px-0">
            <div class="active-indicator" id="activeIndicator"></div>
            
            <div class="flex flex-wrap -mx-3 g-0 w-100 h-100 text-center position-relative">

                <div class="flex-1 px-6 h-100 position-relative">
                    <a href="/thidua/hocsinh"
                       class="nav-link-footer flex d-flex-col justify-center items-center h-100 ripple-effect <?php echo (strpos($_SERVER['REQUEST_URI'], '/thidua/hocsinh') === 0 && strpos($_SERVER['REQUEST_URI'], 'thong-tin-ca-nhan') === false) ? 'active' : ''; ?>"
                       data-nav-index="0">
                        <span class="material-symbols-rounded icon-footer">home</span>
                        <span class="footer-text">Trang chủ</span>
                        <div class="active-pulse"></div>
                    </a>
                </div>

                <div class="flex-1 px-6 h-100 position-relative">
                    <a href="/thidua/hocsinh/thong-tin-ca-nhan"
                       class="nav-link-footer flex d-flex-col justify-center items-center h-100 ripple-effect <?php echo (strpos($_SERVER['REQUEST_URI'], 'thong-tin-ca-nhan') !== false) ? 'active' : ''; ?>"
                       data-nav-index="1">
                        <span class="material-symbols-rounded icon-footer">account_circle</span>
                        <span class="footer-text">Tài khoản</span>
                        <div class="active-pulse"></div>
                    </a>
                </div>

                <div class="flex-1 px-6 h-100 position-relative">
                    <a href="/thidua/dang-xuat"
                       class="nav-link-footer flex d-flex-col justify-center items-center h-100 text-red-600 ripple-effect"
                       data-nav-index="2">
                        <span class="material-symbols-rounded icon-footer">logout</span>
                        <span class="footer-text">Đăng&nbsp;xuất</span>
                        <div class="active-pulse"></div>
                    </a>
                </div>

            </div>
        </div>
    </nav>

<style>
    /* ====== NÂNG CẤP: PAGE LOADER (NProgress-style) ====== */
    .page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px; /* Chiều cao thanh loader */
        z-index: 9999;
        opacity: 0; /* 1. Ẩn ban đầu */
        pointer-events: none;
        transition: opacity 0.4s ease 0.2s; /* 5. Fade out */
    }
    .page-loader.active {
        opacity: 1; /* 2. Hiện ra khi click */
        transition: none;
    }
    .page-loader.finished {
         opacity: 0; /* 4. Ẩn đi khi load xong */
    }

    .loader-bar {
        width: 100%;
        height: 100%;
        background: var(--brand-gradient); /* Dùng gradient đồng bộ */
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.7);
        transform-origin: left;
        transform: scaleX(0); /* 1. Bắt đầu từ 0 */
    }
    
    .page-loader.active .loader-bar {
         /* 2. Chạy animation khi click */
        animation: loading-animation 1.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    .page-loader.finished .loader-bar {
         /* 3. Chạy đến 100% khi load xong */
        transform: scaleX(1);
        transition: transform 0.2s ease-out;
    }

    @keyframes loading-animation {
        0% { transform: scaleX(0); }
        50% { transform: scaleX(0.8); } /* Chạy nhanh đến 80% */
        100% { transform: scaleX(0.95); } /* Dừng ở 95% (giả vờ đang tải) */
    }
    /* ====== KẾT THÚC NÂNG CẤP LOADER ====== */


    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* ====== FOOTER STYLE (Modern & Animated) ====== */
    .navbar.fixed-bottom {
        backdrop-filter: blur(20px) saturate(180%);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.92) 100%);
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08), 0 -2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Active Indicator - Sliding Bar */
    .active-indicator {
        position: absolute;
        bottom: 0;
        height: 3px;
        background: linear-gradient(90deg, #2563eb, #3b82f6, #60a5fa);
        border-radius: 3px 3px 0 0;
        transition: left 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), 
                    width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                    opacity 0.3s ease;
        width: 33.333%;
        left: 0;
        opacity: 1;
        box-shadow: 0 -2px 10px rgba(0, 137, 123, 0.4); /* (Mã màu của #00897b) */
        z-index: 10;
    }

    .nav-link-footer {
        color: #64748b;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.75rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    /* Ripple Effect */
    .ripple-effect {
        position: relative;
        overflow: hidden;
    }

    .ripple-effect::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(37, 99, 235, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s ease, height 0.6s ease, opacity 0.6s ease;
        opacity: 0;
        pointer-events: none;
    }

    .ripple-effect:active::before {
        width: 200px;
        height: 200px;
        opacity: 1;
        transition: width 0.3s ease, height 0.3s ease, opacity 0.3s ease;
    }

    .nav-link-footer .icon-footer {
        font-size: 26px;
        line-height: 1;
        margin-bottom: 6px;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        display: d-block;
        position: relative;
        z-index: 1;
    }

    .footer-text {
        font-size: 0.7rem;
        line-height: 1.2;
        font-weight: 500;
        white-space: nowrap;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1;
    }

    /* Active Pulse Animation */
    .active-pulse {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.15), transparent);
        opacity: 0;
        scale: 0;
        transition: all 0.4s ease;
        pointer-events: none;
        z-index: 0;
    }

    .nav-link-footer.active .active-pulse {
        opacity: 1;
        scale: 1;
        animation: pulse-ring 2s ease-out infinite;
    }

    @keyframes pulse-ring {
        0% {
            scale: 1;
            opacity: 0.8;
        }
        50% {
            scale: 1.3;
            opacity: 0.4;
        }
        100% {
            scale: 1.6;
            opacity: 0;
        }
    }

    /* Hover & Active States */
    .nav-link-footer:hover {
        color: #2563eb !important;
        transform: translateY(-2px);
    }

    .nav-link-footer:hover .icon-footer {
        transform: scale(1.15) translateY(-2px);
        color: #2563eb !important;
        filter: drop-shadow(0 4px 8px rgba(37, 99, 235, 0.3));
    }

    .nav-link-footer.active {
        color: #2563eb !important;
    }

    .nav-link-footer.active .icon-footer {
        color: #2563eb !important;
        transform: scale(1.2);
        filter: drop-shadow(0 0 12px rgba(37, 99, 235, 0.5));
        animation: icon-bounce 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes icon-bounce {
        0%, 100% { transform: scale(1.2); }
        50% { transform: scale(1.35) translateY(-3px); }
    }

    .nav-link-footer.active .footer-text {
        font-weight: 700;
        color: #2563eb !important;
        transform: scale(1.05);
    }

    /* Logout Button Special Styling */
    .nav-link-footer.text-danger .icon-footer {
        color: #dc3545;
    }

    .nav-link-footer.text-danger:hover {
        color: #dc3545 !important;
    }

    .nav-link-footer.text-danger:hover .icon-footer {
        color: #dc3545 !important;
        filter: drop-shadow(0 4px 8px rgba(220, 53, 69, 0.4));
    }

    .nav-link-footer.text-danger .active-pulse {
        background: radial-gradient(circle, rgba(220, 53, 69, 0.15), transparent);
    }

    /* ====== Mobile Optimization ====== */
    @media (max-width: 440px) {
        .navbar.fixed-bottom {
            height: 52px;
        }
        .nav-link-footer .icon-footer {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .footer-text {
            font-size: 0.65rem;
            line-height: 1.2;
        }
        .active-indicator {
            height: 2.5px;
        }
    }
    
    /* DARK MODE CHO OVERLAY (Đã xóa) */


    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }
</style>

<script>
    // === NÂNG CẤP: LOGIC PAGE LOADER ===
    (function() {
        const navLinks = document.querySelectorAll('.nav-link-footer');
        const loader = document.getElementById('pageLoader');
        
        // 1. Logic khi click link
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Chỉ kích hoạt với link nội bộ, không phải link đăng xuất hoặc anchor (#)
                if (href && !href.includes('dang-xuat') && !href.startsWith('http') && !href.startsWith('#')) {
                    e.preventDefault();
                    
                    // Kích hoạt thanh loader
                    if(loader) loader.classList.add('active');
                    
                    // (Đã BỎ setTimeout) Đi đến trang ngay lập tức
                    window.location.href = href;
                }
            });
        });

        // 2. Logic khi trang mới TẢI XONG
        window.addEventListener('load', function() {
            if (loader) {
                // Thêm class 'finished' để chạy animation 95% -> 100% và mờ đi
                loader.classList.add('finished');
                
                // Xóa các class sau khi animation hoàn tất (0.4s fade + 0.2s transition)
                setTimeout(() => {
                    loader.classList.remove('active');
                    loader.classList.remove('finished');
                }, 600);
            }
        });
        
        // 3. Logic xử lý nút back/forward của trình duyệt (cache)
        window.addEventListener('pageshow', function(event) {
            // Nếu người dùng quay lại (từ cache), ẩn thanh loader ngay
            if (event.persisted && loader) {
                loader.classList.remove('active');
                loader.classList.remove('finished');
                loader.style.opacity = '0'; // Ẩn ngay lập tức
            }
        });

        // ====== Logic cũ giữ nguyên (Active Indicator) ======
        function updateActiveIndicator() {
            const activeLink = document.querySelector('.nav-link-footer.active');
            const indicator = document.getElementById('activeIndicator');
            
            if (activeLink && indicator) {
                const index = parseInt(activeLink.getAttribute('data-nav-index')) || 0;
                const width = 100 / 3; // 33.333%
                indicator.style.left = (index * width) + '%';
                indicator.style.opacity = '1';
            } else if (indicator) {
                indicator.style.opacity = '0';
            }
        }
        
        document.addEventListener('DOMContentLoaded', updateActiveIndicator);
        setTimeout(updateActiveIndicator, 100);
        window.addEventListener('resize', updateActiveIndicator);
        
        const observer = new MutationObserver(updateActiveIndicator);
        document.querySelectorAll('.nav-link-footer').forEach(link => {
            observer.observe(link, { attributes: true, attributeFilter: ['class'] });
        });
    })();

    // ====== Logic cũ giữ nguyên (Ripple Effect) ======
    document.querySelectorAll('.ripple-effect').forEach(element => {
        element.addEventListener('click', function(e) {
            const ripple = this.querySelector('::before');
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const circle = document.createElement('span');
            circle.style.cssText = `
                position: absolute;
                width: 100px;
                height: 100px;
                border-radius: 50%;
                background: rgba(37, 99, 235, 0.3);
                left: ${x - 50}px;
                top: ${y - 50}px;
                pointer-events: none;
                animation: ripple-animation 0.6s ease-out;
                z-index: 0;
            `;
            
            this.appendChild(circle);
            
            setTimeout(() => circle.remove(), 600);
        });
    });

    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>



</body>
</html>
