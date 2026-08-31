<?php
// File: config/permissions.php (BẢN CÓ ICON)
// Định nghĩa tất cả các quyền hạn có thể gán cho vai trò "User"

return [

    'nhap_lieu' => [
        'title' => 'Nhập Liệu & Tác Vụ',
        'permissions' => [
            'nhap_vi_pham' => [
                'label' => 'Nhập Vi Phạm',
                'route' => '/thidua/admin/tuan-hoc',
                'icon'  => 'bi bi-exclamation-triangle'
            ],
            'nhap_diem_thi_dua' => [
                'label' => 'Nhập Điểm Thi Đua',
                'route' => '/thidua/nhap-diem-thi-dua',
                'icon'  => 'bi bi-clipboard-check'
            ],
        
            'duyet_so_nhat_ky' => [
                'label' => 'Duyệt Sổ Nhật Kỳ',
                'route' => '/thidua/admin/duyet-so-nhat-ky',
                'icon'  => 'bi bi-book'
            ],
            'duyet_vang_hoc' => [
                'label' => 'Duyệt Vắng Học',
                'route' => '/thidua/admin/duyet-vang-hoc',
                'icon'  => 'bi bi-calendar-x'
            ],
            'xu_ly_tre_hoc' => [
                'label' => 'Xử Lý Trễ Học',
                'route' => '/thidua/admin/xu-ly-tre-hoc',
                'icon'  => 'bi bi-clock'
            ],
       
            'quan_ly_dang_ky_truc' => [
                'label' => 'Đăng Ký Trực',
                'route' => '/thidua/quan-ly-dang-ky-truc',
                'icon'  => 'bi bi-calendar-check'
            ],
    
        ]
    ],

    'bao_cao' => [
        'title' => 'Báo Cáo & Thống Kê',
        'permissions' => [
            'bao_cao_thong_ke' => [
                'label' => 'Báo Cáo Thống Kê',
                'route' => '/thidua/bao-cao',
                'icon'  => 'bi bi-graph-up'
            ],
             'trung_tam_duyet' => [
                'label' => 'Trung Tâm Duyệt',
                'route' => '/thidua/admin/trung-tam-duyet',
                'icon'  => 'bi bi-check-circle'
            ],
            'xem_minh_chung' => [
                'label' => 'Xem Minh Chứng',
                'route' => '/thidua/admin/xem-minh-chung',
                'icon'  => 'bi bi-file-earmark-image'
            ],
        ]
           
    ],
    'quan_ly' => [
        'title' => 'Quản Lý Dữ Liệu & Cấu Hình',
        'permissions' => [
            'quan_ly_khao_sat' => [
                'label' => 'Quản Lý Khảo Sát',
                'route' => '/thidua/admin/quan-ly-khao-sat',
                'icon'  => 'bi bi-ui-checks-grid'
            ],
            'quan_ly_hoc_sinh' => [
                'label' => 'Quản Lý Học Sinh',
                'route' => '/thidua/admin/hoc-sinh',
                'icon'  => 'bi bi-mortarboard'
            ],
            'quan_ly_email_hoc_sinh' => [
                'label' => 'Email Học Sinh',
                'route' => '/thidua/admin/quan-ly-email-hoc-sinh',
                'icon'  => 'bi bi-envelope'
            ],
         
            'quan_ly_giao_vien' => [
                'label' => 'Quản Lý Giáo Viên',
                'route' => '/thidua/admin/giao-vien',
                'icon'  => 'bi bi-person-badge'
            ],
            'quan_ly_nam_hoc' => [
                'label' => 'Quản Lý Năm Học',
                'route' => '/thidua/admin/quan-ly-nam-hoc',
                'icon'  => 'bi bi-calendar3'
            ],
            'chinh_sua_tuan_hoc' => [
                'label' => 'Chỉnh Sửa Tuần Học',
                'route' => '#',
                'icon'  => 'bi bi-calendar-week'
            ],
            'quan_ly_khen_thuong' => [
                'label' => 'Khen Thưởng',
                'route' => '/thidua/admin/khen-thuong',
                'icon'  => 'bi bi-trophy'
            ],
            'quan_ly_ky_thi' => [
                'label' => 'Quản Lý Kỳ Thi',
                'route' => '/thidua/admin/exam-list', // Đường dẫn này phải được router của bạn trỏ đến file admin_exam_list.php
                'icon'  => 'bi bi-calendar-event' // (Bạn có thể cần đổi icon nếu chưa có)
            ],
            'quan_ly_hoat_dong' => [
                'label' => 'Quản Lý Hoạt Động',
                'route' => '/thidua/admin/hoat-dong',
                'icon'  => 'bi bi-activity'
            ],

            'quan_ly_the_hoc_sinh' => [
                'label' => 'Thẻ Học Sinh',
                'route' => '/thidua/admin/the-hoc-sinh',
                'icon'  => 'bi bi-person-badge'
            ],
      
            'quan_ly_diem_thi' => [
                'label' => 'Quản Lý Điểm Thi',
                'route' => '/thidua/admin/quan-ly-diem-thi',
                'icon'  => 'bi bi-clipboard-data'
            ],
            'quan_ly_phuc_khao' => [
                'label' => 'Quản Lý Phúc Khảo',
                'route' => '/thidua/admin/quan-ly-phuc-khao',
                'icon'  => 'bi bi-file-earmark-medical'
            ],
            'quan_ly_ma_ctv' => [
                'label' => 'Quản Lý Mã CTV',
                'route' => '/thidua/admin/quan-ly-ma-ctv',
                'icon'  => 'bi bi-qr-code'
            ],
            'quan_ly_thong_bao' => [
                'label' => 'Quản Lý Thông Báo',
                'route' => '/thidua/admin/quan-ly-thong-bao',
                'icon'  => 'bi bi-megaphone'
            ],
        ]
    ],

    'he_thong' => [
        'title' => 'Hệ Thống & Công Cụ',
        'permissions' => [
            'quan_ly_tai_khoan_ca_nhan' => [
                'label' => 'Tài Khoản Cá Nhân',
                'route' => '/thidua/quan-ly-tai-khoan-ca-nhan',
                'icon'  => 'bi bi-person-vcard'
            ],
            'quan_ly_tai_khoan_admin' => [
                'label' => 'Quản Lý Tài Khoản Admin',
                'route' => '/thidua/admin/tai-khoan',
                'icon'  => 'bi bi-people'
            ],
            'cai_dat_he_thong' => [
                'label' => 'Cài Đặt Hệ Thống',
                'route' => '/thidua/admin/cai-dat',
                'icon'  => 'bi bi-gear'
            ],
            'quan_ly_zalo_mini' => [
                'label' => 'Quản Lý Mini Zalo',
                'route' => '/thidua/admin/quan-ly-zalo-mini',
                'icon'  => 'bi bi-phone'
            ],
      
            'nhat_ky_email' => [
                'label' => 'Lịch sử Email',
                'route' => '/thidua/admin/email-logs',
                'icon'  => 'bi bi-envelope-paper'
            ],
            'nhat_ky_he_thong' => [
                'label' => 'Nhật ký Hệ Thống',
                'route' => '/thidua/admin/nhat-ky',
                'icon'  => 'bi bi-list-ul'
            ],
            'cau_hinh_vi_pham' => [
                'label' => 'Cấu Hình Vi Phạm',
                'route' => '/thidua/admin/cau-hinh-vi-pham',
                'icon'  => 'bi bi-hammer'
            ],
            'cau_hinh_bao_cao' => [
                'label' => 'Cấu Hình Báo Cáo',
                'route' => '/thidua/admin/cau-hinh-bao-cao',
                'icon'  => 'bi bi-pie-chart'
            ],

      
            
        ]
    ],
];
