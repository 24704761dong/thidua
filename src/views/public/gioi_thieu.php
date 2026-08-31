<?php
$page_title = $page_title ?? 'Gi?i Thi?u H? Th?ng';
$logo_path = $logo_path ?? '/thidua/public/assets/img/22logoapp.png';
$school_name = $school_name ?? 'TRU?NG THPT BÌNH SON';
$school_year = 'NAM H?C 2025 – 2026';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> | Binh Son Edu Progress</title>
<meta name="description" content="Gi?i thi?u h? th?ng Binh Son Edu Progress – H? th?ng thi dua, khen thu?ng và k? lu?t tr?c tuy?n c?a Tru?ng THPT Bình Son.">
<link rel="icon" href="/thidua/public/assets/img/favicon.ico" type="image/x-icon">
<style>
:root {
    --text:#0f172a;
    --muted:#64748b;
    --primary:#2563eb;
}
body {
    margin:0;
    font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
    color:var(--text);
    background-color:#0052D4;
    background-image:url('/thidua/public/assets/bgdaihoi.png');
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
    background-attachment:fixed;
}
.hero {
    text-align:center;
    padding:2rem 1rem 1rem;
}
.hero .logo {height:85px;margin-bottom:.75rem;}
.hero .school {font-weight:800;font-size:1.6rem;letter-spacing:1.5px;line-height:1.2;}
.hero .year {font-weight:700;color:#dc2626;font-size:1.1rem;letter-spacing:1px;}
.hero .subtitle {color:var(--muted);font-weight:600;font-size:1rem;letter-spacing:1px;text-transform:uppercase;}

.content-card {
    width:100%;
    max-width:900px;
    margin:1.5rem auto 2rem;
    border:none;
    border-radius:16px;
    background:rgba(255,255,255,0.92);
    backdrop-filter:blur(8px);
    box-shadow:0 10px 22px rgba(2,6,23,.08);
    padding:2.5rem 2rem;
}
.content-card h1 {
    font-weight:800;
    color:var(--primary);
    font-size:1.75rem;
    margin-bottom:1rem;
}
.content-card h2 {
    font-weight:700;
    color:#1d2d35;
    margin-top:1.5rem;
}
.content-card p {
    color:#495057;
    line-height:1.7;
    margin-bottom:.75rem;
    text-align:justify;
}
.content-card ul {
    color:#444;
    margin-bottom:1rem;
}
.content-card li {margin-bottom:.4rem;}
.content-card li strong {color:#0d6efd;}
.footer {
    text-align:center;
    font-size:.85rem;
    padding:.75rem;
    background:rgba(255,255,255,0.6);
    backdrop-filter:blur(10px);
    border-top:1px solid rgba(230,230,230,0.7);
    color:#333;
    font-weight:600;
}
.btn btn-sm-back {
    position:fixed;
    top:1rem;
    right:1rem;
    z-index:1080;
    width:54px;
    height:54px;
    border-radius:50%;
    border:none;
    background:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    transition:all .2s ease-in-out;
    color:#0066ff;
}
.btn btn-sm-back:hover {
    transform:translateY(-1px);
    box-shadow:0 10px 22px rgba(2,6,23,.14);
}
</style>
        <link rel="stylesheet" href="/thidua/public/assets/css/fonts.css">
</head>
<body>

<!-- Nút quay l?i -->
<a href="/thidua/tracuu" class="btn-back" title="V? C?ng Tra C?u"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left text-xl" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg></a>

<!-- Header hero -->
<section class="hero">
    <img src="<?= htmlspecialchars($logo_path) ?>" alt="Logo" class="logo">
    <div class="school"><?= htmlspecialchars($school_name) ?></div>
    <div class="year"><?= htmlspecialchars($school_year) ?></div>
    <div class="subtitle">Gi?i thi?u H? th?ng Ðánh Giá Thi Ðua</div>
</section>

<!-- N?i dung -->
<div class="content-card">
    <h1>1.1. Gi?i thi?u</h1>
    <p><strong>Binh Son Edu Progress</strong> là m?t h? th?ng thi dua tr?c tuy?n, du?c phát tri?n nh?m m?c tiêu s? hóa và hi?n d?i hóa công tác qu?n lý n? n?p, thi dua, khen thu?ng và k? lu?t c?a Tru?ng THPT Bình Son. H? th?ng dóng vai trò là m?t công c? toàn di?n, giúp h?c sinh, giáo viên ch? nhi?m và Ban qu?n tr? có th? truy c?p, c?p nh?t và theo dõi thông tin m?t cách nhanh chóng, minh b?ch và hi?u qu? ? m?i lúc, m?i noi.</p>
    <p>H? th?ng EduProgress du?c thi?t k? d? mang l?i nh?ng l?i ích thi?t th?c và toàn di?n cho m?i d?i tu?ng trong môi tru?ng giáo d?c, t? h?c sinh, giáo viên d?n ban qu?n tr? nhà tru?ng:</p>
    <p><strong>Ð?i v?i h?c sinh</strong>, h? th?ng không ch? là m?t công c? tra c?u thông tin cá nhân, k?t qu? rèn luy?n hay l?ch s? khen thu?ng và vi ph?m m?t cách nhanh chóng, mà còn là m?t phuong ti?n d? ch? d?ng hon trong quá trình rèn luy?n. B?ng cách theo dõi sát sao tình hình thi dua c?a b?n thân và t?p th? l?p theo t?ng tu?n, các em có co s? v?ng ch?c d? t? di?u ch?nh hành vi và phuong pháp h?c t?p.</p>
    <p><strong>Ð?i v?i giáo viên ch? nhi?m</strong>, EduProgress là m?t tr? th? d?c l?c trong công tác qu?n lý l?p h?c, giúp n?m b?t t?c th?i tình hình si s?, k?t qu? rèn luy?n và các vi ph?m c?a h?c sinh m?t cách chi ti?t. D? li?u du?c h? th?ng hóa và minh b?ch, tr? thành co s? dáng tin c?y d? giáo viên dua ra nh?ng nh?n xét, quy?t d?nh khen thu?ng ho?c nh?c nh? k? lu?t m?t cách công b?ng. Ð?ng th?i, thông tin rõ ràng và d? dàng truy xu?t cung giúp tang cu?ng hi?u qu? ph?i h?p v?i ph? huynh trong các bu?i h?p d?nh k?.</p>

    <h2>1.2. Ngu?i dùng</h2>
    <p><strong>Nhóm ngu?i dùng ?ng d?ng:</strong></p>
    <ul>
        <li><strong>H?c sinh:</strong> Toàn b? h?c sinh s? d?ng h? th?ng d? tra c?u thông tin cá nhân, theo dõi k?t qu? rèn luy?n, l?ch s? khen thu?ng và các vi ph?m (n?u có). Các h?c sinh du?c phân công nhi?m v? s? d?ng các ch?c nang nâng cao nhu: ghi nh?n và g?i báo cáo vi ph?m, th?c hi?n di?m danh, ho?c dang ký l?ch tr?c thi dua cho t?p th? l?p.</li>
        <li><strong>Giáo viên Ch? nhi?m (GVCN):</strong> S? d?ng mã giáo viên d? tra c?u và theo dõi tình hình vi ph?m, di?m danh và k?t qu? thi dua c?a toàn b? h?c sinh trong l?p mình ph? trách.</li>
    </ul>

    <hr>
    <p class="text-center text-slate-500">H? th?ng Ðánh Giá Thi Ðua H?c sinh – Công trình c?a Ðoàn TNCS H? Chí Minh Tru?ng THPT Bình Son.</p>
</div>

<footer class="footer">
    © 2025 ÐOÀN TNCS H? CHÍ MINH TRU?NG THPT BÌNH SON<br>
    <span class="small text-slate-500">Công trình thanh niên chào m?ng Ð?i h?i Ðoàn TNCS H? Chí Minh Tru?ng THPT Bình Son nam h?c 2025–2026</span>
</footer>

</body>
</html>

