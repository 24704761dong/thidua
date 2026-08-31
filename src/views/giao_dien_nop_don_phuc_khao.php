<?php
// FILE: nop-don-phuc-khao.php (ÐÃ Ð?NG B? GIAO DI?N GLASSMORPHISM)
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'N?p don Phúc kh?o'; ?></title>
    
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
      position: relative;
      overflow-x: hidden;
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

    .container { 
      max-width: 800px;
      animation: fadeInScale 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      padding: 1rem;
      margin-top: 2rem;
      position: relative;
      z-index: 1;
    }

    @keyframes fadeInScale { 
      from { opacity: 0; transform: translateY(20px) scale(0.95); } 
      to { opacity: 1; transform: translateY(0) scale(1); } 
    }

    /* Card Kính m? (Ðã d?ng b?) */
    .card {
      border-radius: var(--border-radius-large);
      border: none;
      background: var(--card-bg);
      box-shadow: 0 10px 40px rgba(0,0,0,0.08),
                  0 0 0 1px rgba(255,255,255,0.5) inset;
      backdrop-filter: blur(20px) saturate(180%);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
    }
    .card:hover { 
      transform: translateY(-8px) scale(1.01);
      box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15),
                  0 0 0 1px rgba(255,255,255,0.8) inset;
    }
    
    .logo-img {
      max-height: 80px;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }
    h2 {
      font-weight: 700;
      color: var(--text-strong);
      text-shadow: 0 1px 2px rgba(0,0,0,0.05);
      margin-top: 1.5rem;
      margin-bottom: 0.5rem;
    }
    .text-muted {
      color: var(--text-muted) !important;
    }

    /* Form (Ð?ng b?) */
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

    /* Nút b?m (Ð?ng b?) */
    .btn.btn-sm {
        border-radius: var(--border-radius-small) !important; 
        font-weight: 600; 
        transition: var(--transition-bouncy);
        padding: 0.75rem 2rem;
        border: none;
    }
    .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent {
      background: var(--brand-gradient);
      box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
      color: #fff;
    }
    .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:hover, .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent:focus {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
      color: #fff;
    }
    .btn btn-sm-outline-success {
        color: var(--color-success);
        border: 1px solid var(--color-success);
        background: #fff;
        box-shadow: var(--shadow-soft);
    }
    .btn btn-sm-outline-success:hover {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        border-color: transparent;
        color: white;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(25, 135, 84, 0.3);
    }

    /* Header c?a card (Ð?ng b?) */
    .card-header {
      background: var(--brand-gradient);
      color: #fff;
      font-weight: 600;
      border-top-left-radius: var(--border-radius-large);
      border-top-right-radius: var(--border-radius-large);
      padding: 1.25rem 1.5rem;
      border-bottom: none;
    }
    
    /* --- CSS CHO TRANG PHÚC KH?O --- */
    
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

    .d-flex align-items-center.subject-row {
        background: transparent;
        border-radius: 0;
        padding: 0.6rem 0;
        border-bottom: 1px solid var(--card-border);
        padding-left: 0; 
        display: flex; 
        align-items: flex-start;
        margin-bottom: 0; 
    }
    .d-flex align-items-center.subject-row:last-child {
        border-bottom: none;
    }

    .d-flex align-items-center.subject-row .rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 {
        margin-top: 0.35rem; 
        margin-left: 0;
    }
    .d-flex align-items-center.subject-row .form-check-label {
        d-flex: 1; 
        padding-left: 1rem;
        cursor: pointer;
    }
    .d-flex align-items-center.subject-row .form-check-label strong {
        font-weight: 600; 
        color: var(--text-strong); 
        font-size: 0.95rem; 
    }
    
    .d-flex align-items-center.subject-row .form-check-label .badge {
        background-color: var(--text-muted) !important;
        transition: backgrouncolor 0.2s ease;
        font-weight: 600;
    }
    .rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50:checked + .form-check-label .badge {
        background: var(--brand-gradient) !important; /* Ð?i sang gradient */
    }

    /* Style khu v?c upload file (Ð?ng b?) */
    .upload-area {
        margin-top: 1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.7);
        border-radius: var(--border-radius-medium);
        border: 1px solid var(--card-border);
    }
    .upload-area .form-label {
        font-weight: 600;
        color: var(--color-danger);
        font-size: 0.9rem;
    }
    .upload-area .px-4 py-1.5 text-sm { 
         background: rgba(255, 255, 255, 0.9);
         border-color: var(--card-border);
    }
    
    .upload-area .form-label.small {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 0.2rem;
    }
    .upload-area .row.g-2 {
        padding: 0.5rem 0.25rem;
        background-color: rgba(0,0,0,0.02);
        border-radius: var(--border-radius-small);
    }

    /* Style cho khu v?c b? khóa (Ð?ng b?) */
    .locked-area {
        margin-top: 1rem;
        padding: 1rem;
        background: rgba(233, 236, 239, 0.7);
        border-radius: var(--border-radius-medium);
        border: 1px solid rgba(206, 212, 218, 0.9);
    }
    .locked-area .form-label {
        font-weight: 600;
        color: var(--text-strong);
        font-size: 0.9rem;
    }
    .locked-area .submitted-value {
        font-weight: 600;
        color: var(--text-strong);
    }
    .locked-area a.btn btn-sm-link {
        font-weight: 600;
        color: var(--brand-primary);
    }
    
    /* Alerts (Ð?ng b?) */
    .alert-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #842029;
        border-color: rgba(220, 53, 69, 0.2);
        border-radius: var(--border-radius-small);
    }
    .alert-danger a { color: #721c24; font-weight: 600; text-decoration: underline; }
    
    .alert-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #664d03;
        border-color: rgba(255, 193, 7, 0.2);
        border-radius: var(--border-radius-small);
    }
    
    #successMessage {
        background-color: rgba(25, 135, 84, 0.1);
        color: #0f5132;
        border-color: rgba(25, 135, 84, 0.2);
        border-radius: var(--border-radius-large); /* Gi? bo góc l?n cho d?p */
    }
    
    footer { margin-top: 3rem; color: var(--text-muted); font-size: 0.9rem; }
    
    /* (T?i uu mobile gi? nguyên) */
    @media (max-width: 576px) {
      body { padding: 1rem 0; align-items: flex-start; }
      .container { margin-top: 1rem; padding: 0.5rem; }
      .p-4 { padding: 1rem; }
      h2 { font-size: 1.8rem; margin-top: 1rem; margin-bottom: 0.5rem; }
      .logo-img { max-height: 70px; }
      .bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent, .btn-warning, .btn-secondary { width: 100%; padding: 0.6rem 1rem; font-size: 0.95rem; }
      .info-item { flex-direction: column; align-items: flex-start; padding: 0.4rem 0; }
      .info-label { margin-bottom: 0.2rem; font-size: 0.9rem; d-flex-basis: auto; }
      .info-value { text-align: left; font-size: 1rem; width: 100%; d-flex-basis: auto; }
      .d-flex align-items-center.subject-row .d-flex { flex-direction: column; align-items: d-flex-start !important; gap: 0.25rem; }
      .locked-area { padding: 0.75rem; }
      .locked-area .submitted-value { font-size: 0.9rem; }
      
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

    <div class="w-full max-w-6xl mx-auto px-6 py-8">
        <div class="text-center mb-6">
              <img src="/thidua/public/assets/img/22logoapp.png" alt="Logo" class="logo-img">
              <h2 class="mt-6"><?php echo $page_title ?? 'N?P ÐON PHÚC KH?O'; ?></h2>
        </div>

        <?php if ($error_message ?? null): ?>
            <div class="p-6 mb-6 rounded-lg border bg-red-50 text-red-800 border-red-200 text-center">
                <?php echo htmlspecialchars($error_message); ?><br>
                <a href="/thidua/diemthi">Quay l?i trang tra c?u</a>
            </div>
        <?php elseif (!$student_data): ?>
             <div class="p-6 mb-6 rounded-lg border bg-yellow-50 text-yellow-800 border-yellow-200 text-center">Không t?i du?c thông tin h?c sinh.</div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 content-card shadow-sm border-[#224397]/25 overflow-hidden">
                 <div class="px-6 py-6 border-b border-slate-200 bg-slate-50 rounded-t-xl font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-fill mr-2" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>Thông tin Thí sinh
                 </div>
                 <div class="p-6">
                    <div class="student-info">
                        <div class="info-item">
                            <span class="info-label">K? thi</span>
                            <span class="info-value"><?php echo htmlspecialchars($student_data['ten_ky_thi']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">H? và Tên</span>
                            <span class="info-value"><?php echo htmlspecialchars($student_data['ho_dem'] . ' ' . $student_data['ten']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">L?p</span>
                            <span class="info-value"><?php echo htmlspecialchars($student_data['ten_lop']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">SBD</span>
                            <span class="info-value"><?php echo htmlspecialchars($student_data['so_bao_danh']); ?></span>
                        </div>
                    </div>
                    </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 content-card shadow-sm mt-6 border-[#224397]/25 overflow-hidden">
                <div class="px-6 py-6 border-b border-slate-200 bg-slate-50 rounded-t-xl font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-pencil-square mr-2" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>   <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>Yêu c?u Phúc kh?o
                </div>
                <div class="p-6">
                    <form id="phucKhaoForm" enctype="multipart/form-data" onsubmit="handleSubmitPhucKhao(event)">
                        <p class="text-slate-500">Ch?n các môn b?n mu?n yêu c?u phúc kh?o và t?i lên file minh ch?ng (?nh ch?p bài thi, don vi?t tay...). Ch? nh?ng môn có di?m m?i hi?n th?.</p>

                        <?php
                        $has_scores = false;
                        
                        // ================== LOGIC PHP (Gi? nguyên) ==================
                        
                        foreach($diem_columns_mon_hoc as $col_db => $col_display):
                            if (isset($student_data[$col_db]) && $student_data[$col_db] !== null && $student_data[$col_db] !== ''):
                                $has_scores = true;
                                $col_display_safe = htmlspecialchars($col_display);
                                
                                $is_locked = isset($pending_appeals_map[$col_db]);
                                $details = $appeal_details_map[$col_db] ?? null;
                        ?>
                                <div class="subject-row flex items-center">
                                    <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 subject-checkbox" type="checkbox" 
                                           name="subjects[]" 
                                           value="<?php echo $col_db; ?>" 
                                           id="check_<?php echo $col_db; ?>"
                                           <?php if($is_locked) echo 'disabled'; ?>>
                                           
                                    <label class="ml-2 block text-sm text-slate-900 w-100" for="check_<?php echo $col_db; ?>">
                                        <div class="flex justify-between items-center">
                                            <span><strong><?php echo $col_display_safe; ?>:</strong> <strong><?php echo htmlspecialchars($student_data[$col_db]); ?></strong></span>
                                            
                                            <?php if($is_locked): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 text-slate-900">Ðang ch? x? lý</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Ch?n d? phúc kh?o</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if($is_locked && $details): ?>
                                            <div class="mt-2 locked-area">
                                                <div class="mb-2">
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">Ði?m b?n dã nh?p:</label>
                                                    <span class="submitted-value">
                                                        T?ng: <?php echo htmlspecialchars($details['tong_hs'] ?? 'N/A'); ?> 
                                                        (TN: <?php echo htmlspecialchars($details['tn_hs'] ?? '-'); ?> / 
                                                         TL: <?php echo htmlspecialchars($details['tl_hs'] ?? '-'); ?>)
                                                    </span>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">Minh ch?ng dã g?i:</label>
                                                    <a href="/thidua/public/<?php echo htmlspecialchars($details['path']); ?>" target="_blank" class="btn-link">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-arrow-down-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1m-1 4v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L7.5 11.293V7.5a.5.5 0 0 1 1 0"/></svg> Xem file
                                                    </a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-2 upload-area" style="display: none;">
                                                <div class="flex flex-wrap -mx-3 g-2 mb-2">
                                                    <div class="col-4">
                                                        <label class="block text-sm font-medium text-slate-700 mb-1 small">Ði?m TN (HS nh?p)</label>
                                                        <input type="number" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm" name="diem_tn_hs[<?php echo $col_db; ?>]" step="0.01" min="0" max="10" placeholder="TN">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="block text-sm font-medium text-slate-700 mb-1 small">Ði?m TL (HS nh?p)</label>
                                                        <input type="number" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm" name="diem_tl_hs[<?php echo $col_db; ?>]" step="0.01" min="0" max="10" placeholder="TL">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="block text-sm font-medium text-slate-700 mb-1 small">T?ng (HS nh?p) *</label>
                                                        <input type="number" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm" name="diem_tong_hs[<?php echo $col_db; ?>]" step="0.01" min="0" max="10" placeholder="T?ng">
                                                    </div>
                                                </div>
                                                
                                                <label for="file_<?php echo $col_db; ?>" class="block text-sm font-medium text-slate-700 mb-1 small text-red-600">T?i lên minh ch?ng (PDF, JPG... - T?i da 5MB): *</label>
                                                <input class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm" type="file" 
                                                       name="minhchung[<?php echo $col_db; ?>]" 
                                                       id="file_<?php echo $col_db; ?>" 
                                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            </div>
                                        <?php endif; ?>
                                    </label>
                                </div>
                        <?php
                            endif;
                        endforeach;
                        
                        // ================== K?T THÚC LOGIC ==================
                        
                        if (!$has_scores):
                            echo '<p class="text-center text-slate-500">Chua có di?m thi nào du?c ghi nh?n.</p>';
                        endif;
                        ?>

                        <div id="submitError" class="mt-6 text-red-600 small" style="display: none;"></div>

                        <div class="text-center mt-6">
                            <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent px-6 py-6 text-lg hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" id="submitButton" <?php echo !$has_scores ? 'disabled' : ''; ?>>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-send-fill mr-1" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/></svg> G?i Yêu c?u Phúc kh?o
                            </button>
                        </div>
                    </form>
                </div>
            </div>

             <div id="successMessage" class="mt-6 p-6 mb-6 rounded-lg border bg-green-50 text-green-800 border-green-200 text-center" style="display: none;">
                 <h4><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill mr-2" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>G?i yêu c?u thành công!</h4>
                 <p>Yêu c?u phúc kh?o c?a b?n dã du?c ghi nh?n. Nhà tru?ng s? xem xét và c?p nh?t k?t qu? (n?u có).</p>
                 <a href="/thidua/diemthi" class="btn btn-outline-success hover:translate-x-1 hover:scale-[1.02] transition-all duration-300">Quay l?i trang Tra c?u</a>
             </div>

        <?php endif; ?>

        <footer class="text-center mt-8">
  <small class="text-slate-500">
    &copy; <?php echo date('Y'); ?> - Ðoàn TNCS H? Chí Minh Tru?ng THPT Bình Son<br>
    Binh Son Edu Progress
  </small>
</footer>

    </div>

    <script>
        // === TOÀN B? JAVASCRIPT ÐU?C GI? NGUYÊN ===
        const phucKhaoForm = document.getElementById('phucKhaoForm');
        const submitButton = document.getElementById('submitButton');
        const submitSpinner = document.getElementById('submitSpinner');
        const submitError = document.getElementById('submitError');
        const successMessage = document.getElementById('successMessage');

        document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.disabled) return;
                
                const key = this.value;
                const uploadArea = this.nextElementSibling.querySelector('.upload-area');
                const fileInput = document.getElementById(`file_${key}`);
                
                const diemTnInput = uploadArea.querySelector(`input[name="diem_tn_hs[${key}]"]`);
                const diemTlInput = uploadArea.querySelector(`input[name="diem_tl_hs[${key}]"]`);
                const diemTongInput = uploadArea.querySelector(`input[name="diem_tong_hs[${key}]"]`);
                
                if (this.checked) {
                    uploadArea.style.display = 'd-block';
                    fileInput.required = true;
                    diemTongInput.required = true;
                } else {
                    uploadArea.style.display = 'none';
                    fileInput.required = false;
                    diemTongInput.required = false;
                    fileInput.value = ''; 
                    diemTnInput.value = '';
                    diemTlInput.value = '';
                    diemTongInput.value = '';
                }
            });
        });

        async function handleSubmitPhucKhao(event) {
        event.preventDefault();
        submitButton.disabled = true;
        submitSpinner.style.display = 'd-inline-block';
        submitError.style.display = 'none';
        submitError.textContent = '';

        const checkedSubjects = phucKhaoForm.querySelectorAll('.subject-checkbox:checked');
        if (checkedSubjects.length === 0) {
             submitError.textContent = 'Vui lòng ch?n ít nh?t m?t môn d? phúc kh?o.';
             submitError.style.display = 'd-block';
             submitButton.disabled = false;
             submitSpinner.style.display = 'none';
             return;
        }

        let fileError = false;
        let scoreError = false;
         checkedSubjects.forEach(checkbox => {
             const key = checkbox.value;
             const fileInput = document.getElementById(`file_${key}`);
             const diemTongInput = phucKhaoForm.querySelector(`input[name="diem_tong_hs[${key}]"]`);
             
             if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                  fileError = true;
                  submitError.innerHTML += `Vui lòng t?i lên minh ch?ng cho môn ${checkbox.nextElementSibling.querySelector('strong').textContent}.<br>`;
             }
             if (!diemTongInput || diemTongInput.value.trim() === '') {
                 scoreError = true;
                 submitError.innerHTML += `Vui lòng nh?p 'T?ng di?m (HS nh?p)' cho môn ${checkbox.nextElementSibling.querySelector('strong').textContent}.<br>`;
             }
         });

          if(fileError || scoreError) {
               submitError.style.display = 'd-block';
               submitButton.disabled = false;
               submitSpinner.style.display = 'none';
               return;
          }

        const formData = new FormData(phucKhaoForm);
        
        try {
            const response = await fetch('/thidua/api/submit-phuc-khao', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || `L?i ${response.status}`);
            }
            
            phucKhaoForm.style.display = 'none';
            document.querySelector('.card:last-of-type').style.display = 'none';
            successMessage.style.display = 'd-block';

        } catch (error) {
             submitError.textContent = 'L?i: ' + error.message;
             submitError.style.display = 'd-block';
        } finally {
             if (submitError.style.display === 'd-block') { 
                  submitButton.disabled = false;
             }
             submitSpinner.style.display = 'none';
        }
    }
    </script>
</body>
</html>
