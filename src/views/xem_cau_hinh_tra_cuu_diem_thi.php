<?php
// File: src/views/xem_cau_hinh_tra_cuu_diem_thi.php (�� N�NG C?P "WOW")

require_once __DIR__ . '/partials/admin_header.php';
// C�c bi?n: $ky_thi_id, $ky_thi_info, $config, $available_methods, $available_fields, $available_verification_fields, $error_message
?>

<style>
:root {
    --primary: #224397;
    --accent: #FAB723;
    --bg-light: #f4f7f9;
    --text-primary: #1d2d35;
    --text-secondary: #5a6a72;
    --card-border: rgba(34,67,151,0.25);
}

/* N?n h? th?ng */
body {
    background-color: var(--bg-light);
    font-family: 'Inter', sans-serif;
    color: var(--text-primary);
    
    transition: background 0.4s ease, color 0.4s ease;
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
}
.content-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

/* Header trong card */
.content-card .card-header {
    background-color: transparent;
    border-bottom: 1px solid var(--card-border);
    padding: 1rem 1.25rem;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: space-between;
    letter-spacing: 0.2px;
}
.content-card .card-header i {
    color: #FAB723;
    margin-right: 0.75rem;
    font-size: 1.25rem;
    opacity: 0.85;
}

/* N�NG C?P: Style cho <legend> gi?ng card-header */
.content-card legend {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    letter-spacing: 0.2px;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--card-border);
    margin-bottom: 1rem; /* Thay v� mb-4 */
}
.content-card legend i {
    color: #FAB723;
    margin-right: 0.75rem;
    font-size: 1.25rem;
    opacity: 0.85;
}
.content-card fieldset {
    padding: 0.5rem 0.25rem; /* Th�m padding cho fieldset */
}

/* N�NG C?P: Style cho Switch (t? file T�i Kho?n) */
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 {
    width: 2.8rem;
    height: 1.5rem;
    border-radius: 1rem;
    background-color: #d6dee3;
    border: none;
    position: relative;
    transition: backgrouncolor 0.3s ease;
    float: none; /* Ghi d� bootstrap */
    margin-left: 0; /* Ghi d� bootstrap */
}
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50::before {
    content: "";
    position: absolute;
    top: 0.18rem;
    left: 0.2rem;
    width: 1.15rem;
    height: 1.15rem;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.3s ease;
}
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50:checked {
    background-color: #224397;
}
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50:checked::before {
    transform: translateX(1.25rem);
}
.card-header .d-flex align-items-center {
    margin-bottom: 0; /* Ghi d� bootstrap */
}

/* Style cho c�c radio/checkbox thu?ng */
.d-flex align-items-center {
    margin-bottom: 0.75rem; /* Tang kho?ng c�ch */
}
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50[type="radio"],
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50[type="checkbox"] {
    width: 1.25em; /* K�ch thu?c chu?n */
    height: 1.25em; /* K�ch thu?c chu?n */
    margin-top: 0.15em;
    background-color: #fff;
    border: 1px solid #adb5bd;
}
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50[type="radio"]:checked,
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50[type="checkbox"]:checked {
    background-color: #224397;
    border-color: #224397;
}
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50[type="radio"]::before,
.rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50[type="checkbox"]::before {
    display: none; /* B? style switch cho radio/checkbox */
}

/* N�NG C?P: Style cho Button */
.bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600 {
    color: #224397;
    border-color: #224397;
    border-width: 1.8px;
    font-weight: 500;
    transition: all 0.25s ease;
}
.bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600:hover {
    background: #224397;
    color: #fff;
    transform: translateY(-2px);
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
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-toggles mr-2" viewBox="0 0 16 16"><path d="M4.5 9a3.5 3.5 0 1 0 0 7h7a3.5 3.5 0 1 0 0-7zm7 6a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m-7-14a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5m2.45 0A3.5 3.5 0 0 1 8 3.5 3.5 3.5 0 0 1 6.95 6h4.55a2.5 2.5 0 0 0 0-5zM4.5 0h7a3.5 3.5 0 1 1 0 7h-7a3.5 3.5 0 1 1 0-7"/></svg>
            C�I �?T K? THI
        </h3>
    </div>
    



    <div class="flex flex-wrap -mx-3 justify-center">
        <div class="col-lg-10">
            <?php if ($error_message ?? null): ?>
                <div class="p-6 mb-6 rounded-lg border bg-red-50 text-red-800 border-red-200"><?php echo htmlspecialchars($error_message); ?></div>
            <?php elseif (!$ky_thi_info): ?>
                <div class="p-6 mb-6 rounded-lg border bg-yellow-50 text-yellow-800 border-yellow-200">Kh�ng th? t?i th�ng tin k? thi.</div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 content-card border-[#224397]/25 overflow-hidden">
                    <div class="px-6 py-6 border-b border-slate-200 bg-slate-50 rounded-t-xl font-semibold">
                        <h5 class="mb-0 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-gear-wide-connected" viewBox="0 0 16 16"><path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5m0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78zM5.048 3.967l-.087.065zm-.431.355A4.98 4.98 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8zm.344 7.646.087.065z"/></svg>
                            C�i d?t cho: <?php echo htmlspecialchars($ky_thi_info['ten_ky_thi']); ?>
                        </h5>
                        <div class="flex items-center form-switch">
                            <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="checkbox" role="switch" id="togglePublicSwitch" <?php echo ($ky_thi_info['tra_cuu_cong_khai'] ?? 0) ? 'checked' : ''; ?> onchange="togglePublicStatus()">
                            <label class="ml-2 block text-sm text-slate-900" for="togglePublicSwitch">
                                <span id="publicStatusLabel"><?php echo ($ky_thi_info['tra_cuu_cong_khai'] ?? 0) ? 'B?t C�ng khai' : 'T?t C�ng khai'; ?></span>
                            </label>
                        </div>
                    </div>
                    <div class="p-6 p-6">
                        <form id="configForm" onsubmit="saveConfig(event)">

                            <fieldset class="mb-6">
                                <legend class="text-lg"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>Phuong th?c Tra c?u Ch�nh</legend>
                                <p class="text-secondary small">Ch?n <strong>m?t</strong> phuong th?c duy nh?t d? h?c sinh t�m ki?m di?m.</p>
                                <?php foreach ($available_methods as $key => $label): ?>
                                    <div class="flex items-center">
                                        <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 method-radio" type="radio" name="phuong_thuc_tra_cuu" id="method_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo (($config['phuong_thuc_tra_cuu'] ?? '') === $key) ? 'checked' : ''; ?> required>
                                        <label class="ml-2 block text-sm text-slate-900" for="method_<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </fieldset>

                            <hr class="my-6">

                            <fieldset class="mb-6">
                                <legend class="text-lg"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>   <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>Tru?ng Th�ng tin Hi?n th?</legend>
                                <p class="text-secondary small">Ch?n c�c th�ng tin c� nh�n s? hi?n th? sau khi tra c?u th�nh c�ng.</p>
                                <div class="flex flex-wrap gap-6">
                                   <?php if (!empty($available_fields)): ?>
                                        <?php foreach ($available_fields as $key => $label): ?>
                                            <div class="flex items-center flex align-items-center-inline mb-0">
                                                <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 field-checkbox" type="checkbox" id="field_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo isset($config['truong_hien_thi'][$key]) && $config['truong_hien_thi'][$key] ? 'checked' : ''; ?>>
                                                <label class="ml-2 block text-sm text-slate-900" for="field_<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-red-600">L?i: Kh�ng t�m th?y danh s�ch tru?ng th�ng tin.</p>
                                    <?php endif; ?>
                                </div>
                            </fieldset>

                            <hr class="my-6">

                            <fieldset class="mb-6">
                                <legend class="text-lg"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>   <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg>C?u h�nh X�c minh Ph�c kh?o</legend>
                                <p class="text-secondary small">Ch?n nh?ng th�ng tin h?c sinh <strong>b?t bu?c ph?i nh?p d�ng</strong> d? c� th? n?p don ph�c kh?o.</p>
                                <div class="flex flex-wrap gap-6">
                                <?php if (!empty($available_verification_fields)): ?>
                                    <?php foreach ($available_verification_fields as $key => $label): ?>
                                        <div class="flex items-center flex align-items-center-inline mb-0">
                                            <input class="rounded-lg border-slate-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 verification-checkbox" type="checkbox" id="verify_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo isset($config['phuc_khao_xac_minh'][$key]) && $config['phuc_khao_xac_minh'][$key] ? 'checked' : ''; ?>>
                                            <label class="ml-2 block text-sm text-slate-900" for="verify_<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-red-600">L?i: Kh�ng t�m th?y danh s�ch tru?ng x�c minh.</p>
                                <?php endif; ?>
                                </div>
                            </fieldset>

                            <div class="text-right mt-6">
                                <button type="submit" class="btn bg-transparent hover:bg-primary-600 text-primary-600 hover:text-white border border-primary-600 px-6 py-6 text-lg hover:translate-x-1 hover:scale-[1.02] transition-all duration-300" id="saveConfigButton">
                                    <span id="saveConfigSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none; margin-bottom: 2px;"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-save mr-2" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z"/></svg>
                                    Luu C?u h�nh
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
    // Ki?m tra xem bi?n KY_THI_ID c� t?n t?i kh�ng tru?c khi d�ng
    const KY_THI_ID = typeof <?php echo json_encode($ky_thi_id ?? null); ?> === 'number' ? <?php echo $ky_thi_id; ?> : null;
    const API_URL = '/thidua/api/luu-cau-hinh-tra-cuu';

    // Luu c?u h�nh (radio + checkboxes)
    async function saveConfig(event) {
        event.preventDefault();
        if (!KY_THI_ID) {
            alert('L?i: Kh�ng x�c d?nh du?c ID K? thi.');
            return;
        }

        const btn = document.getElementById('saveConfigButton');
        const spinner = document.getElementById('saveConfigSpinner');
        btn.disabled = true;
        spinner.style.display = 'd-inline-block';
        btn.querySelector('.bi-save').style.display = 'none'; // ?n icon save

        const selectedMethodRadio = document.querySelector('.method-radio:checked');
        const phuong_thuc_tra_cuu = selectedMethodRadio ? selectedMethodRadio.value : null;

        const truong_hien_thi = {};
        document.querySelectorAll('.field-checkbox:checked').forEach(cb => {
            truong_hien_thi[cb.value] = true;
        });

        const phuc_khao_xac_minh = {};
        document.querySelectorAll('.verification-checkbox:checked').forEach(cb => {
            phuc_khao_xac_minh[cb.value] = true;
        });

        if (!phuong_thuc_tra_cuu) {
             alert('Vui l�ng ch?n m?t Phuong th?c Tra c?u Ch�nh.');
             btn.disabled = false;
             spinner.style.display = 'none';
             btn.querySelector('.bi-save').style.display = 'd-inline-block';
             return;
        }

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_config',
                    ky_thi_id: KY_THI_ID,
                    phuong_thuc_tra_cuu: phuong_thuc_tra_cuu,
                    truong_hien_thi: truong_hien_thi,
                    phuc_khao_xac_minh: phuc_khao_xac_minh
                })
            });
            const result = await response.json();
            if (!response.ok || !result.success) { throw new Error(result.message || 'L?i kh�ng x�c d?nh'); }
            alert(result.message);
        } catch (error) {
            console.error('L?i luu c?u h�nh:', error);
            alert('L?i: ' + error.message);
        } finally {
            btn.disabled = false;
            spinner.style.display = 'none';
            btn.querySelector('.bi-save').style.display = 'd-inline-block';
        }
    }

    // B?t/t?t c�ng khai
    async function togglePublicStatus() {
         if (!KY_THI_ID) {
            alert('L?i: Kh�ng x�c d?nh du?c ID K? thi.');
            // Ho�n t�c switch n?u ID kh�ng h?p l?
            const sw = document.getElementById('togglePublicSwitch');
            if(sw) sw.checked = !sw.checked;
            return;
         }

        const sw = document.getElementById('togglePublicSwitch');
        const label = document.getElementById('publicStatusLabel');
        const isChecked = sw.checked;
        sw.disabled = true;

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_public', ky_thi_id: KY_THI_ID, cong_khai: isChecked })
            });
            const result = await response.json();
            if (!response.ok || !result.success) { throw new Error(result.message || 'L?i'); }
            label.textContent = result.new_status ? 'B?t C�ng khai' : 'T?t C�ng khai';
            // �?i m�u label cho sinh d?ng
            label.style.color = result.new_status ? '#224397' : 'var(--text-secondary)';
            label.style.fontWeight = result.new_status ? '600' : '500';

        } catch (error) {
            console.error('L?i b?t/t?t:', error); alert('L?i: ' + error.message); sw.checked = !isChecked;
        } finally {
            sw.disabled = false;
        }
    }
    
    // N�NG C?P: K�ch ho?t hi?u ?ng cho label khi t?i trang
    document.addEventListener('DOMContentLoaded', () => {
        const sw = document.getElementById('togglePublicSwitch');
        const label = document.getElementById('publicStatusLabel');
        if (sw && label) {
             label.style.color = sw.checked ? '#224397' : 'var(--text-secondary)';
             label.style.fontWeight = sw.checked ? '600' : '500';
        }
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // T�m .content-card (card ch�nh)
    const cards = document.querySelectorAll(".content-card");
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = 1;
                entry.target.style.transform = "translateY(0)";
            }
        });
    }, { threshold: 0.1 });
    
    cards.forEach(card => {
        card.style.opacity = 0;
        card.style.transform = "translateY(25px)";
        card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
        observer.observe(card);
    });
});
</script>
