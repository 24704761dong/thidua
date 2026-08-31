<?php
// File: src/views/xem_truoc_import_vi_pham.php (ÐÃ THÊM LOGIC CHU?N HÓA)
$page_title = 'X? Lý Import Vi Ph?m';
require_once __DIR__ . '/partials/admin_header.php';

// D? li?u du?c truy?n t? controller
$json_raw_rows = json_encode($raw_rows ?? []);
$json_tuan_info = json_encode($tuan_info ?? null);
$json_all_violations = json_encode($all_violations ?? []);
$tuan_id = $tuan_id ?? 0;
?>

<style>
    /* ----- Bạng màu và bi?n CSS hi?n d?i ----- */
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
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* ----- Card chính ----- */
    .card {
        border-radius: 12px;
        border: 1px solid var(--card-border);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .card-footer {
        background-color: #f8f9fa;
        border-top: 1px solid var(--card-border);
    }
    .btn.btn-sm {
        border-radius: 8px !important;
        font-weight: 500;
    }
    
    /* ----- Bạng & Thanh ti?n trình ----- */
    .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light { border-radius: 8px !important; overflow: hidden; }
    .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light thead th {
        background-color: #f8f9fa;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
    }
    .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .progress {
        height: 8px !important;
        border-radius: 8px;
    }
    .progress-bar {
        transition: width 0.2s ease-in-out;
    }

    /* Alert thông tin tu?n h?c */
    .week-info-alert {
        border-radius: 8px !important;
        background-color: #e7f5ff;
        border-color: #b3e5fc;
        color: #01579b;
    }
</style>

<div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5">
    <div class="page-header">
        <h3 class="page-title"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear-wide-connected mr-2 text-primary-600" viewBox="0 0 16 16"><path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5m0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78zM5.048 3.967l-.087.065zm-.431.355A4.98 4.98 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8zm.344 7.646.087.065z"/></svg>X? lý Import Vi Ph?m</h3>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
        <div class="p-6 p-6">
            <?php if ($tuan_info): ?>
                <div class="p-6 mb-6 rounded-lg border week-info-alert py-2 mt-2">
                    <strong>Tu?n h?c:</strong> <?php echo htmlspecialchars($tuan_info['ten_tuan']); ?> |
                    <strong>Ph?m vi ngày h?p l?:</strong>
                    <span class="font-bold"><?php echo date('d/m/Y', strtotime($tuan_info['ngay_bat_dau'])); ?></span> đến
                    <span class="font-bold"><?php echo date('d/m/Y', strtotime($tuan_info['ngay_ket_thuc'])); ?></span>
                </div>
            <?php endif; ?>
            
            <div id="processing-controls" class="my-6 flex justify-between items-center">
                <button id="start-processing-btn" class="btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm border-transparent" <?php echo empty($raw_rows) ? 'disabled' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-play-circle-fill mr-2" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814z"/></svg>B?t d?u x? lý
                </button>
                <div id="status-text" class="text-slate-500 font-bold">S?n sàng x? lý <?php echo count($raw_rows ?? []); ?> dòng.</div>
            </div>
            <div class="progress mb-6">
                <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light border border-slate-200">
                    <thead>
                        <tr>
                            <th>Dòng</th>
                            <th>Tên H?c Sinh</th>
                            <th>Lớp</th>
                            <th>Ngày VP</th>
                            <th>Tên Nhóm Vi Ph?m</th>
                            <th>Tr?ng thái X? lý</th>
                        </tr>
                    </thead>
                    <tbody id="preview-table-body">
                        <?php foreach(($raw_rows ?? []) as $row): ?>
                            <tr id="row-<?php echo $row['line_number']; ?>">
                                <td><?php echo $row['line_number']; ?></td>
                                <td class="ten-hs"><?php echo htmlspecialchars($row['ten_hs']); ?></td>
                                <td class="lop"><?php echo htmlspecialchars($row['lop']); ?></td>
                                <td class="ngay-vp"><?php echo htmlspecialchars($row['ngay_vp_formatted']); ?></td>
                                <td class="ten-vp"><?php echo htmlspecialchars($row['ten_vp']); ?></td>
                                <td class="status"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Chua x? lý</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-6 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end gap-2">
            <a href="/thidua/admin/vi-pham?tuan_id=<?php echo $tuan_id; ?>" class="btn bg-slate-600 hover:bg-slate-700 text-white shadow-sm border-transparent">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg mr-1" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>H?y B?
            </a>
            <button id="save-btn" class="btn bg-green-600 hover:bg-green-700 text-white shadow-sm border-transparent" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save-fill mr-2" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293z"/></svg>Xác Nh?n và Luu
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- KH?I D? LI?U T? PHP ---
    const rawRows = <?php echo $json_raw_rows; ?>;
    const tuanInfo = <?php echo $json_tuan_info; ?>;
    const allViolations = <?php echo $json_all_violations; ?>;
    const currentUserID = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;

    // --- L?Y CÁC THÀNH PH?N GIAO DI?N ---
    const startBtn = document.getElementById('start-processing-btn');
    const saveBtn = document.getElementById('save-btn');
    const statusText = document.getElementById('status-text');
    const progressBar = document.getElementById('progress-bar');
    const tableBody = document.getElementById('preview-table-body');

    let processedData = []; // M?ng ch?a d? li?u dã x? lý thành công
    
    // --- CÁC HÀM CHU?N HÓA (GI?NG H?T BÊN NH?P TAY) ---
    function normalizeStudentName(name) {
        if (!name) return '';
        return name.toLowerCase().replace(/(^|\s)\S/g, l => l.toUpperCase());
    }

    function normalizeClassName(className) {
        if (!className) return '';
        let finalName = className.toUpperCase();
        if (/^([ABC])(\d+)$/.test(finalName)) {
            const prefix = finalName.charAt(0);
            const number = finalName.substring(1);
            const khoiMap = { 'A': '10', 'B': '11', 'C': '12' };
            finalName = khoiMap[prefix] + 'A' + number;
        }
        return finalName;
    }

    // --- HÀM X? LÝ NGÀY THÁNG (Bạn JavaScript) ---
    function parseAndValidateDate(dateInput) {
        if (!dateInput || !tuanInfo) return null;
        let date = luxon.DateTime.fromObject({}, { zone: 'utc' });
        const startDate = luxon.DateTime.fromISO(tuanInfo.ngay_bat_dau, { zone: 'utc' });
        const endDate = luxon.DateTime.fromISO(tuanInfo.ngay_ket_thuc, { zone: 'utc' });

        if (!isNaN(dateInput) && Number(dateInput) > 31) {
            date = luxon.DateTime.fromMillis((dateInput - 25569) * 86400 * 1000, { zone: 'utc' });
        } else if (!isNaN(dateInput) && Number(dateInput) >= 1 && Number(dateInput) <= 31) {
            let tempDate = startDate;
            while (tempDate <= endDate) {
                if (tempDate.day === Number(dateInput)) {
                    date = tempDate;
                    break;
                }
                tempDate = tempDate.plus({ days: 1 });
            }
        } else {
            date = luxon.DateTime.fromFormat(String(dateInput), 'd/M/yyyy', { zone: 'utc' });
            if (!date.isValid) {
                date = luxon.DateTime.fromFormat(String(dateInput), 'd/M', { zone: 'utc' });
                if (date.isValid) date = date.set({ year: startDate.year });
            }
        }

        if (date.isValid && date >= startDate && date <= endDate) {
            return date;
        }
        return null;
    }

    // --- HÀM X? LÝ CHÍNH ---
    async function processRows() {
        startBtn.disabled = true;
        startBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Ðang x? lý...`;
        
        let validRowsToSave = [];
        let invalidRows = [];

        for (let i = 0; i < rawRows.length; i++) {
            const rowData = rawRows[i];
            const rowElement = document.getElementById(`row-${rowData.line_number}`);
            const statusCell = rowElement.querySelector('.status');
            
            statusText.textContent = `Ðang x? lý dòng ${i + 1}/${rawRows.length}...`;
            statusCell.innerHTML = `<span class="spinner-border spinner-border-sm text-primary-600" role="status"></span>`;
            
            let errors = [];
            let warnings = [];
            let processedRow = { ...rowData, hoc_sinh_id: null, vi_pham_id: null };

            // --- BU?C NÂNG C?P: CHU?N HÓA D? LI?U TRU?C KHI TÌM KI?M ---
            const normalizedName = normalizeStudentName(rowData.ten_hs);
            const normalizedClass = normalizeClassName(rowData.lop);
            
            // C?p nh?t l?i giao di?n ngay d? ngu?i dùng th?y
            rowElement.querySelector('.ten-hs').textContent = normalizedName;
            rowElement.querySelector('.lop').textContent = normalizedClass;
            // --- K?T THÚC BU?C NÂNG C?P ---

            if (!normalizedName) errors.push("Tên HS tr?ng");
            if (!normalizedClass) errors.push("Lớp tr?ng");
            if (!rowData.ten_vp) errors.push("Tên VP tr?ng");
            
            const validDate = parseAndValidateDate(rowData.ngay_vp);
            if (!validDate) {
                errors.push("Ngày VP không h?p l?");
            } else {
                processedRow.ngay_vp_iso = validDate.toISODate();
                rowElement.querySelector('.ngay-vp').textContent = validDate.toFormat('dd/MM/yyyy');
            }

            const vpKey = rowData.ten_vp.trim().toLowerCase();
            if (allViolations[vpKey]) {
                processedRow.vi_pham_id = allViolations[vpKey];
            } else {
                warnings.push("Tên vi ph?m không có trong c?u hình");
            }

            if (errors.length === 0) {
                 try {
                    // G?i API v?i d? li?u dã du?c chu?n hóa
                    const response = await fetch(`/thidua/api/lookup-student?ho_ten=${encodeURIComponent(normalizedName)}&ten_lop=${encodeURIComponent(normalizedClass)}`);
                    const studentData = await response.json();
                    if(studentData.success) {
                        processedRow.hoc_sinh_id = studentData.student.id;
                        rowElement.querySelector('.ten-hs').textContent = studentData.student.ho_ten;
                        rowElement.querySelector('.lop').textContent = studentData.student.ten_lop;
                    } else {
                        warnings.push(studentData.message || "Không tìm th?y h?c sinh.");
                    }
                 } catch (e) {
                     warnings.push("L?i API tìm HS.");
                 }
            }
            
            if (errors.length > 0) {
                statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">${errors.join(', ')}</span>`;
                invalidRows.push(processedRow);
            } else {
                validRowsToSave.push(processedRow);
                let statusHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Ðã kh?p</span>';
                if(warnings.length > 0) {
                    statusHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 text-slate-900">${warnings.join(', ')}</span>`;
                }
                statusCell.innerHTML = statusHTML;
            }
            
            progressBar.style.width = `${((i + 1) / rawRows.length) * 100}%`;
        }
        
        processedData = validRowsToSave;
        statusText.textContent = `Hoàn t?t! ${validRowsToSave.length} h?p l?, ${invalidRows.length} có l?i.`;
        startBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-circle-fill mr-2" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>Ðã x? lý xong`;
        
        if (processedData.length > 0) {
            saveBtn.disabled = false;
        }
    }

    async function saveData() {
        saveBtn.disabled = true;
        saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Ðang luu...`;

        try {
             const response = await fetch('/thidua/admin/vi-pham?action=api_save_import', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    tuan_id: tuanInfo.id,
                    user_id: currentUserID,
                    violations: processedData
                })
            });

            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.href = `/thidua/admin/vi-pham?tuan_id=${tuanInfo.id}`;
            } else {
                throw new Error(result.message);
            }

        } catch(e) {
            alert("L?i khi luu d? li?u: " + e.message);
            saveBtn.disabled = false;
            saveBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save-fill mr-2" viewBox="0 0 16 16"><path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293z"/></svg>Xác Nh?n và Luu`;
        }
    }

    startBtn.addEventListener('click', processRows);
    saveBtn.addEventListener('click', saveData);
});
</script>

<script src="/thidua/public/assets/libs/luxon.min.js"></script>
