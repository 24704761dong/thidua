<?php
$page_title = 'Xác th?c 2 y?u t?';
$logo_path = '/thidua/public/assets/img/22logoapp.png';
$error_message = $error_message ?? null; // L?y t? controller
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title; ?></title>
    <link rel="icon" type="image/x-icon" href="/thidua/public/assets/img/favicon.ico">
    <style>
        :root { --primary: #2563eb; --card-border: #e9ecef; }
        body {
            font-family: Inter, system-ui, sans-serif;
            display: flex; flex-direction: column; height: 100vh;
            background-color: #0052D4; 
            background-image: url('/thidua/public/assets/bgdaihoi.png');
            background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;
        }
        .search-card {
            width: 100%; max-width: 450px; margin: 1rem auto; border: none;
            border-radius: 16px; background: #fff;
            box-shadow: 0 10px 22px rgba(2, 6, 23, .08);
        }
        .form-control { height: 42px; border-radius: 10px; font-size: 0.95rem; }
    </style>
        <link rel="stylesheet" href="/thidua/public/assets/css/fonts.css">
</head>
<body>
    <main class="w-full max-w-6xl mx-auto px-6 my-auto">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 search-card p-6 p-md-4">
            <div class="text-center mb-6">
                <img src="<?= htmlspecialchars($logo_path) ?>" alt="Logo" style="height: 70px;">
            </div>
            <h4 class="portal-title text-center mb-6 font-bold">XÁC TH?C 2 Y?U T?</h4>
            <p class="text-center text-slate-500 small">Vui lòng m? ?ng d?ng Google Authenticator và nh?p mã 6 s? d? hoàn t?t dang nh?p.</p>

            <?php if ($error_message): ?>
                <div class="p-6 mb-6 rounded-lg border bg-red-50 text-red-800 border-red-200 alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="text-slate-400 hover:text-slate-500 p-2" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="/thidua/api/2fa-login" method="POST" id="2faForm" novalidate>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1 font-semibold" for="2fa_code">Mã 6 s?</label>
                    <div class="flex w-full">
                        <span class="flex items-center px-6 rounded-l-md border border-r-0 border-slate-300 bg-slate-50 text-slate-500 sm:text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/></svg></span>
                        <input type="text" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" name="2fa_code" id="2fa_code" 
                               placeholder="123456" required autocomplete="off" 
                               inputmode="numeric" pattern="[0-9]*" maxlength="6">
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle mr-1" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>   <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>Xác nh?n</button>
                    <a href="/thidua/dang-xuat" class="btn bg-transparent hover:bg-slate-600 text-slate-600 hover:text-white border border-slate-600">H?y b?</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
