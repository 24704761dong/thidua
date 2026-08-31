<?php
$page_title = 'L?ch & Nh?t Ký Sinh Nh?t';
require_once __DIR__ . '/partials/admin_header.php';
$months_vietnamese = ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"];

// Gi? d?nh các bi?n dã du?c n?p
$upcoming_birthdays = $upcoming_birthdays ?? [];
$birthdays_by_month = $birthdays_by_month ?? [];
$logs = $logs ?? [];
?>
<style>
    /* ----- B?ng màu và bi?n CSS hi?n d?i ----- */
    :root {
        --primary-blue: #00a8e8;
        --text-primary: #1d2d35;
        --text-secondary: #5a6a72;
        --bg-light: #f4f7f9;
        --card-border: #e9ecef;
    }

    body {
        background-color: var(--bg-light);
    }

    /* ----- Header c?a trang ----- */
    .page-header {
        margin-bottom: 1.5rem;
    }
    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    /* ----- Card & B?ng D? Li?u ----- */
    .card {
        border-radius: 12px;
        border: 1px solid var(--card-border);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .card-header {
        background-color: transparent;
        border-bottom: 1px solid var(--card-border);
        font-weight: 600;
        color: var(--text-primary);
    }
    .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light thead th {
        background-color: #f8f9fa;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
    }
    .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light td {
        vertical-align: middle;
    }

    /* ----- Giao di?n Tabs ----- */
    .nav-tabs {
        border-bottom: 2px solid var(--card-border);
    }
    .nav-tabs .nav-link {
        border-radius: 8px 8px 0 0 !important;
        border: 2px solid transparent;
        color: var(--text-secondary);
        font-weight: 600;
    }
    .nav-tabs .nav-link.active {
        color: var(--primary-blue);
        background-color: #fff;
        border-color: var(--card-border) var(--card-border) #fff;
    }

    /* ----- NÂNG C?P: Card cho t?ng tháng ----- */
    .month-card .card-header {
        background-color: #f8f9fa;
    }
</style>

<div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5">
    <div class="page-header">
        <h3 class="page-title"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gift-fill mr-2 text-primary-600" viewBox="0 0 16 16"><path d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A3 3 0 0 1 3 2.506zm1.068.5H7v-.5a1.5 1.5 0 1 0-3 0c0 .085.002.274.045.43zM9 3h2.932l.023-.07c.043-.156.045-.345.045-.43a1.5 1.5 0 0 0-3 0zm6 4v7.5a1.5 1.5 0 0 1-1.5 1.5H9V7zM2.5 16A1.5 1.5 0 0 1 1 14.5V7h6v9z"/></svg>L?ch & Nh?t Ký Sinh Nh?t</h3>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
        <div class="p-6">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="calendar-tab" type="button">?? L?ch Sinh Nh?t</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="log-tab" type="button">?? Nh?t Ký G?i L?i Chúc</button>
                </li>
            </ul>

            <div class="tab-content pt-6" id="myTabContent">
                <div class="tab-pane fade show active" id="calendar-pane" role="tabpanel">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 border-success mb-6">
                        <div class="px-6 py-6 border-b border-slate-200 bg-slate-50 rounded-t-xl font-semibold bg-green-600 text-white text-white">
                            <h5 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cake2-fill mr-2" viewBox="0 0 16 16"><path d="m2.899.804.595-.792.598.79A.747.747 0 0 1 4 1.806v4.886q-.532-.09-1-.201V1.813a.747.747 0 0 1-.1-1.01ZM13 1.806v4.685a15 15 0 0 1-1 .201v-4.88a.747.747 0 0 1-.1-1.007l.595-.792.598.79A.746.746 0 0 1 13 1.806m-3 0a.746.746 0 0 0 .092-1.004l-.598-.79-.595.792A.747.747 0 0 0 9 1.813v5.17q.512-.02 1-.055zm-3 0v5.176q-.512-.018-1-.054V1.813a.747.747 0 0 1-.1-1.01l.595-.79.598.789A.747.747 0 0 1 7 1.806"/>   <path d="M4.5 6.988V4.226a23 23 0 0 1 1-.114V7.16c0 .131.101.24.232.25l.231.017q.498.037 1.02.055l.258.01a.25.25 0 0 0 .26-.25V4.003a29 29 0 0 1 1 0V7.24a.25.25 0 0 0 .258.25l.259-.009q.52-.018 1.019-.055l.231-.017a.25.25 0 0 0 .232-.25V4.112q.518.047 1 .114v2.762a.25.25 0 0 0 .292.246l.291-.049q.547-.091 1.033-.208l.192-.046a.25.25 0 0 0 .192-.243V4.621c.672.184 1.251.409 1.677.678.415.261.823.655.823 1.2V13.5c0 .546-.408.94-.823 1.201-.44.278-1.043.51-1.745.696-1.41.376-3.33.603-5.432.603s-4.022-.227-5.432-.603c-.702-.187-1.305-.418-1.745-.696C.408 14.44 0 14.046 0 13.5v-7c0-.546.408-.94.823-1.201.426-.269 1.005-.494 1.677-.678v2.067c0 .116.08.216.192.243l.192.046q.486.116 1.033.208l.292.05a.25.25 0 0 0 .291-.247M1 8.82v1.659a1.935 1.935 0 0 0 2.298.43.935.935 0 0 1 1.08.175l.348.349a2 2 0 0 0 2.615.185l.059-.044a1 1 0 0 1 1.2 0l.06.044a2 2 0 0 0 2.613-.185l.348-.348a.94.94 0 0 1 1.082-.175c.781.39 1.718.208 2.297-.426V8.833l-.68.907a.94.94 0 0 1-1.17.276 1.94 1.94 0 0 0-2.236.363l-.348.348a1 1 0 0 1-1.307.092l-.06-.044a2 2 0 0 0-2.399 0l-.06.044a1 1 0 0 1-1.306-.092l-.35-.35a1.935 1.935 0 0 0-2.233-.362.935.935 0 0 1-1.168-.277z"/></svg>Sinh Nh?t S?p T?i (Hôm nay & 2 ngày t?i)</h5>
                        </div>
                        <div class="p-6">
                            <?php if (empty($upcoming_birthdays)): ?>
                                <p class="text-slate-500 mb-0">Không có ai có sinh nh?t trong 3 ngày t?i.</p>
                            <?php else: ?>
                                <div class="overflow-x-auto w-full">
                                    <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light border border-slate-200 mb-0">
                                        <thead class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-light"><tr><th>S? CCCD/GV</th><th>H? và Tên</th><th>L?p</th><th>Ngày Sinh</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($upcoming_birthdays as $person): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo htmlspecialchars($person['ma']); ?></td>
                                                    <td><?php echo htmlspecialchars($person['ten']); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($person['lop']); ?></td>
                                                    <td class="text-center font-bold"><?php echo htmlspecialchars($person['ngay_sinh']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php foreach ($birthdays_by_month as $month => $days): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 month-card mb-6">
                            <div class="px-6 py-6 border-b border-slate-200 bg-slate-50 rounded-t-xl font-semibold">
                                <h5 class="mb-0"><?php echo $months_vietnamese[$month-1]; ?></h5>
                            </div>
                            <div class="p-6">
                                <?php if (empty($days)): ?>
                                    <p class="text-slate-500 mb-0">Không có sinh nh?t trong tháng này.</p>
                                <?php else: ?>
                                    <div class="overflow-x-auto w-full">
                                        <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light border border-slate-200 mb-0">
                                            <tbody>
                                            <?php foreach ($days as $day => $people): ?>
                                                <?php foreach ($people as $index => $person): ?>
                                                    <tr>
                                                        <?php if ($index === 0): ?>
                                                            <td rowspan="<?php echo count($people); ?>" class="text-center font-bold bg-slate-50" style="width: 10%;">Ngày <?php echo $day; ?></td>
                                                        <?php endif; ?>
                                                        <td style="width: 15%;"><?php echo htmlspecialchars($person['ma']); ?></td>
                                                        <td><?php echo htmlspecialchars($person['ten']); ?></td>
                                                        <td style="width: 15%;"><?php echo htmlspecialchars($person['lop']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="tab-pane fade" id="log-pane" role="tabpanel">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
                         <div class="px-6 py-6 border-b border-slate-200 bg-slate-50 rounded-t-xl font-semibold"><h5 class="mb-0">Nh?t Ký G?i L?i Chúc M?ng Sinh Nh?t</h5></div>
                         <div class="p-6">
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light border border-slate-200">
                                    <thead><tr><th>Th?i gian g?i</th><th>Ngu?i nh?n</th><th>Ngày Sinh</th><th>Tr?ng thái</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                            <?php
                                                // ====== CH?NH NH?: ch?ng Notice & map tr?ng thái dúng ======
                                                // Fallback tên: uu tiên person_name, sau dó ten_day_du
                                                $name = isset($log['person_name']) && $log['person_name'] !== ''
                                                    ? $log['person_name']
                                                    : ($log['ten_day_du'] ?? '');
                                                // Fallback error_message/message
                                                $err  = $log['error_message'] ?? ($log['message'] ?? '');
                                                // Chu?n hóa status d? nh?n c? “Thành công”, “thanh_cong”, “success”
                                                $statusRaw = trim((string)($log['status'] ?? ''));
                                                $s = mb_strtolower($statusRaw, 'UTF-8');
                                                $sPlain = strtr($s, [
                                                    'a'=>'a','â'=>'a','á'=>'a','à'=>'a','?'=>'a','ã'=>'a','?'=>'a',
                                                    'd'=>'d',
                                                    'ê'=>'e','é'=>'e','è'=>'e','?'=>'e','?'=>'e','?'=>'e',
                                                    'ô'=>'o','o'=>'o','ó'=>'o','ò'=>'o','?'=>'o','õ'=>'o','?'=>'o',
                                                    'ú'=>'u','ù'=>'u','?'=>'u','u'=>'u','?'=>'u',
                                                    'í'=>'i','ì'=>'i','?'=>'i','i'=>'i','?'=>'i'
                                                ]);
                                                $isSuccess = ($s === 'thành công' || $sPlain === 'thanh cong' || $s === 'thanh_cong' || $s === 'success');
                                            ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($log['sent_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($name); ?></td>
                                                <td><?php echo htmlspecialchars($log['birthday_date'] ?? ''); ?></td>
                                                <td>
                                                    <?php if ($isSuccess): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Thành công</span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="<?php echo htmlspecialchars($err); ?>">Th?t b?i</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

