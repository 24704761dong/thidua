<?php
// File: src/views/xem_quan_ly_phuc_khao.php (N�NG C?P D?NG B?NG - �� S?A L?I HI?N TH?)

require_once __DIR__ . '/partials/admin_header.php';
// C�c bi?n: $ky_thi_id, $ky_thi_info, $flat_appeal_list (danh s�ch ph?ng)
?>

<style>
:root {
    --primary: #224397;
    --accent: #FAB723;
    --bg-light: #f4f7f9;
    --text-primary: #1d2d35;
    --text-secondary: #5a6a72;
    --card-border: rgba(34,67,151,0.25);
    --success: #198754;
    --warning: #ffc107;
    --danger: #dc3545;
}

/* N?n h? th?ng */
body {
    font-family: 'Inter', sans-serif;
    background-color: var(--bg-light);
    color: var(--text-primary);
    
}

/* Header */
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.page-title i {
    color: #224397;
    font-size: 1.8rem;
}

/* Card */
.content-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 14px;
    border: 1px solid var(--card-border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(8px);
    overflow: hidden; /* �? bo g�c header */
}
.content-card .card-header {
    background-color: transparent;
    border-bottom: 1px solid var(--card-border);
    padding: 1rem 1.25rem;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text-primary);
}
.content-card .card-header i {
  color: #FAB723;
  margin-right: 0.75rem;
  font-size: 1.25rem;
  opacity: 0.85;
}

/* B?ng di?m ph�c kh?o */
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 th {
    background-color: #f8f9fa;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    position: sticky;
    top: 0;
    z-index: 2;
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 td { font-size: 0.9rem; }
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 input[type="number"] {
    text-align: center;
    padding: 0.2rem 0.4rem;
    font-size: 0.9em;
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    width: 80px; /* C? d?nh chi?u r?ng � di?m */
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 input[type="number"]:focus {
    border-color: #224397;
    box-shadow: 0 0 0 0.2rem rgba(0, 168, 232, 0.25);
}

/* ?n mui t�n spinner */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none; margin: 0;
}
input[type=number] { -moz-appearance: textfield; }

/* C?t di?m HS nh?p */
.score-hs {
    font-weight: 600;
    color: #224397;
    font-size: 0.9rem;
}
.score-hs small {
    font-weight: 500;
    color: var(--text-secondary);
    font-size: 0.8rem;
}

/* C?t T?ng di?m (m?i) */
.total-score-new {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--danger);
    width: 80px; /* �?ng b? v?i � input */
    display: d-inline-block;
}

.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 td, .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 th {
    vertical-align: middle;
    text-align: center;
}
.w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 th.text-start, .w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 td.text-start {
    text-align: left;
}
    /* === Chuan hoa giao dien #224397 === */
    table thead th { background-color: rgba(34,67,151,0.08) !important; color: #224397 !important; font-weight: 800 !important; text-transform: uppercase; font-size: 0.82rem; border: 1px solid rgba(34,67,151,0.25) !important; }
    table td, table th { border: 1px solid rgba(34,67,151,0.25) !important; }
    table tbody tr:hover { background-color: rgba(34,67,151,0.04) !important; }
    body::-webkit-scrollbar { display: block !important; width: 8px; }
    body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }</style>

<div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5">
    
    <div class="page-header">
        <h3 class="page-title mb-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-journal-check mr-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10.854 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 8.793l2.646-2.647a.5.5 0 0 1 .708 0"/>   <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>   <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/></svg>
            QU?N L� PH�C KH?O
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="background: transparent;">
                <li class="breadcrumb-item"><a href="/thidua/admin/exam-list">Qu?n l� K? thi</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($ky_thi_info['ten_ky_thi']); ?></li>
            </ol>
        </nav>
    </div>
    
    <?php if ($error_message ?? null): ?>
        <div class="p-6 mb-6 rounded-lg border bg-red-50 text-red-800 border-red-200"><?php echo htmlspecialchars($error_message); ?></div>
    <?php elseif (empty($flat_appeal_list)): ?>
        <div class="p-6 mb-6 rounded-lg border bg-cyan-50 text-cyan-800 border-cyan-200 text-center shadow-sm">Chua c� don ph�c kh?o n�o cho k? thi n�y.</div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] mb-6 content-card mb-6 border-[#224397]/25 overflow-hidden">
            <div class="px-6 py-6 border-b border-[#224397]/25 bg-[#224397]/5 rounded-t-xl font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>   <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg>
                Danh s�ch Y�u c?u Ph�c kh?o
            </div>
            <div class="p-6 p-0">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm text-slate-600 border-collapse border border-[#224397]/[45%] [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-slate-50/50 border border-[#224397]/[45%] align-middle mb-0">
                        <thead class="w-full text-left text-sm text-slate-600 border-collapse border border-[#224397]/[45%] [&_th]:bg-[#224397]/5 [&_th]:text-[#224397] [&_th]:uppercase [&_th]:text-[11px] [&_th]:tracking-wider [&_th]:font-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-[#224397]/5/50 bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0">
                            <tr>
                                <th rowspan="2">STT</th>
                                <th rowspan="2" class="text-left">SBD</th>
                                <th rowspan="2" class="text-left">H? v� T�n</th>
                                <th rowspan="2">L?p</th>
                                <th rowspan="2">M�n Ph�c Kh?o</th>
                                <th rowspan="2">�i?m G?c</th>
                                <th colspan="3">�i?m HS Nh?p</th>
                                <th rowspan="2">Minh Ch?ng</th>
                                <th colspan="3">�i?m Sau Ph�c Kh?o (Admin)</th>
                                <th rowspan="2">Luu</th>
                            </tr>
                            <tr>
                                <th>TN</th>
                                <th>TL</th>
                                <th>T?ng</th>
                                <th>TN (M?i)</th>
                                <th>TL (M?i)</th>
                                <th>T?ng (M?i)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flat_appeal_list as $index => $item): ?>
                                <tr data-chi-tiet-id="<?php echo $item['chi_tiet_id']; ?>"
                                    data-kths-id="<?php echo $item['ky_thi_hoc_sinh_id']; ?>"
                                    data-mon-col="<?php echo $item['mon_hoc_db_col']; ?>"
                                    data-phuc-khao-id="<?php echo $item['phuc_khao_id']; ?>"
                                    class="appeal-row status-<?php echo $item['trang_thai']; ?>">
                                    
                                    <td><?php echo $index + 1; ?></td>
                                    
                                    <td class="text-left"><?php echo htmlspecialchars($item['so_bao_danh']); ?></td>
                                    <td class="text-left"><?php echo htmlspecialchars($item['ho_dem'] . ' ' . $item['ten']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ten_lop']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ten_mon']); ?></td>
                                    <td><?php echo htmlspecialchars($item['diem_goc'] ?? 'N/A'); ?></td>
                                    
                                    <td class="score-hs"><small><?php echo htmlspecialchars($item['diem_tn_cu'] ?? '-'); ?></small></td>
                                    <td class="score-hs"><small><?php echo htmlspecialchars($item['diem_tl_cu'] ?? '-'); ?></small></td>
                                    <td class="score-hs"><strong><?php echo htmlspecialchars($item['diem_tong_cu'] ?? 'N/A'); ?></strong></td>

                                    <td>
                                        <a href="/thidua/public/<?php echo htmlspecialchars($item['minh_chung_path']); ?>" target="_blank" class="px-4 py-2 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm" title="Xem Minh ch?ng">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>   <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                                        </a>
                                    </td>
                                    
                                    <td>
                                        <input type="number" step="0.01" min="0" max="10" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm score-input tn-moi" 
                                               value="<?php echo htmlspecialchars($item['diem_tn_moi'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="10" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 px-6 py-1.5 text-sm score-input tl-moi" 
                                               value="<?php echo htmlspecialchars($item['diem_tl_moi'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <span class="total-score-new" id="total_new_<?php echo $item['chi_tiet_id']; ?>">
                                            <?php echo htmlspecialchars($item['diem_tong_moi'] ?? '0.00'); ?>
                                        </span>
                                    </td>
                                    <td>
                                         <button class="px-4 py-2 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm" onclick="savePhucKhaoScore(this)">
                                             <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                             <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg>
                                         </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
    const API_URL = '/thidua/api/xu-ly-phuc-khao';

    // === H�M T�NH T?NG T? �?NG (RULE 1) ===
    function calculateTotal(row) {
        const tnInput = row.querySelector('.tn-moi');
        const tlInput = row.querySelector('.tl-moi');
        const totalSpan = row.querySelector('.total-score-new');
        
        const tn = parseFloat(tnInput.value) || 0;
        const tl = parseFloat(tlInput.value) || 0;
        
        const total = tn + tl;
        totalSpan.textContent = total.toFixed(2); // L�m tr�n 2 ch? s?
    }

    // G?n s? ki?n 'input' cho t?t c? c�c � di?m TN v� TL
    document.querySelectorAll('.score-input.tn-moi, .score-input.tl-moi').forEach(input => {
        input.addEventListener('input', function() {
            calculateTotal(this.closest('tr'));
        });
    });

    // === H�M LUU �I?M (N�NG C?P) ===
    async function savePhucKhaoScore(buttonElement) {
        const row = buttonElement.closest('tr');
        const chiTietId = row.dataset.chiTietId;
        const kthsId = row.dataset.kthsId;
        const monCol = row.dataset.monCol;
        const phucKhaoId = row.dataset.phucKhaoId; // L?y ID c?a don ph�c kh?o
        
        const diemTnMoiInput = row.querySelector('.tn-moi');
        const diemTlMoiInput = row.querySelector('.tl-moi');
        
        // L?y gi� tr? d� du?c l�m s?ch (tr?ng = null)
        const getSafeValue = (input) => {
            const val = input.value.trim();
            if (val === '') return null;
            const num = parseFloat(val);
            if (isNaN(num) || num < 0 || num > 10) return 'invalid'; // Tr? v? 'invalid' n?u l?i
            return num;
        };

        const diemTnMoi = getSafeValue(diemTnMoiInput);
        const diemTlMoi = getSafeValue(diemTlMoiInput);

        if (diemTnMoi === 'invalid' || diemTlMoi === 'invalid') {
             alert('�i?m nh?p ph?i l� s? t? 0 d?n 10, ho?c d? tr?ng.');
             return;
        }

        // T? d?ng t�nh t?ng (Rule 1)
        const diemTongMoi = (diemTnMoi !== null || diemTlMoi !== null) 
                            ? (diemTnMoi || 0) + (diemTlMoi || 0) 
                            : null;

        // C?p nh?t l?i � T?ng tr�n UI
        row.querySelector('.total-score-new').textContent = diemTongMoi !== null ? diemTongMoi.toFixed(2) : '0.00';

        const spinner = buttonElement.querySelector('.spinner-border');
        const icon = buttonElement.querySelector('.bi-save') || buttonElement.querySelector('.bi-check-lg');
        
        buttonElement.disabled = true;
        spinner.style.display = 'd-inline-block';
        if (icon) icon.style.display = 'none';

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_score',
                    chi_tiet_id: chiTietId,
                    phuc_khao_id: phucKhaoId, // <--- TH�M (RULE 3)
                    ky_thi_hoc_sinh_id: kthsId,
                    mon_hoc_db_col: monCol,
                    diem_tn_moi: diemTnMoi,
                    diem_tl_moi: diemTlMoi,
                    diem_tong_moi: diemTongMoi // G?i t?ng d� t�nh to�n
                })
            });
            const result = await response.json();
            if (!response.ok || !result.success) { throw new Error(result.message || 'L?i'); }
            
            // Hi?n th? n�t check "�� luu"
            buttonElement.classList.remove('btn-success');
            buttonElement.classList.add('btn btn-sm-outline-success');
            buttonElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg>';
            
            // C?p nh?t tr?ng th�i c?a h�ng (Rule 3)
            row.classList.remove('status-cho_xu_ly');
            row.classList.add('status-da_xu_ly');
            
            setTimeout(() => {
                 buttonElement.classList.remove('btn btn-sm-outline-success');
                 buttonElement.classList.add('btn-success');
                 buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" style="display: none;"></span><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg>';
            }, 2000);

        } catch (error) {
            console.error("L?i luu di?m PK:", error);
            alert('L?i: ' + error.message);
            if (icon) icon.style.display = 'd-inline-block'; // Hi?n l?i icon save n?u l?i
        } finally {
            // === N�NG C?P (RULE 2) ===
            // KH�NG v� hi?u h�a n�t, cho ph�p admin s?a l?i
            buttonElement.disabled = false; 
            spinner.style.display = 'none';
        }
    }
</script>
