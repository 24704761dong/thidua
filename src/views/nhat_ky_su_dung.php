<?php
$page_title = 'Nhat Ky Su Dung Cua Hoc Sinh';
require_once __DIR__ . '/partials/admin_header.php';

function infer_khoi_from_lop($ten_lop) {
    if (preg_match('/^(\d{2})/u', $ten_lop, $m)) return $m[1];
    return 'Khac';
}
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track { background: transparent; }
    .log-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .log-table thead th { background: rgba(34,67,151,0.08); color: #224397; font-weight: 800; text-transform: uppercase; font-size: 0.73rem; padding: 0.6rem 0.75rem; border: 1px solid rgba(34,67,151,0.2); white-space: nowrap; }
    .log-table td { padding: 0.55rem 0.75rem; border: 1px solid rgba(34,67,151,0.1); vertical-align: middle; font-weight: 500; color: #1e293b; }
    .log-table tbody tr:hover { background: rgba(34,67,151,0.03); }
    .form-select-std, .form-input-std { display: block; width: 100%; border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.3rem 0.6rem; font-size: 0.83rem; color: #1e293b; background: #fff; transition: border-color 0.2s, box-shadow 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
    .form-select-std:focus, .form-input-std:focus { outline: none; border-color: #224397; box-shadow: 0 0 0 3px rgba(34,67,151,0.1); }
    .form-select-std:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
    .btn-action { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.22rem 0.65rem; border-radius: 6px; font-size: 11px; font-weight: 600; border: 1px solid rgba(34,67,151,0.25); background: #fff; color: #224397; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
    .btn-action:hover { background: #FAB723; color: #fff; border-color: #FAB723; text-decoration: none; }
    .btn-sort { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.22rem 0.65rem; border-radius: 6px; font-size: 11px; font-weight: 600; border: 1px solid rgba(34,67,151,0.25); background: #fff; color: #224397; transition: all 0.2s; cursor: pointer; white-space: nowrap; }
    .btn-sort:hover, .btn-sort.active { background: #224397; color: #fff; border-color: #224397; }
    .badge-tk { display: inline-flex; align-items: center; padding: 0.18rem 0.55rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; background: #e0f2fe; color: #0369a1; }
    .badge-lop { display: inline-flex; align-items: center; padding: 0.15rem 0.5rem; border-radius: 5px; font-size: 0.7rem; font-weight: 700; background: #eff6ff; color: #1d4ed8; }
    .modal-content-box { transition: transform 0.3s ease, opacity 0.3s ease; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 pb-6">
    <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
        <h1 class="h4 mb-0 font-semibold text-[#224397] flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-person-lines-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/></svg>
            <?php echo 'Nh&#x1EAD;t K&yacute; S&#x1EED; D&#x1EE5;ng C&#x1EE7;a H&#x1ECD;c Sinh'; ?>
        </h1>
        <a href="/thidua/admin/nhat-ky" class="btn-action">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>
            Quay L&#x1EA1;i Nh&#x1EAD;t K&yacute; Chung
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/20 overflow-hidden">
        <div class="px-5 py-4 border-b border-[#224397]/10 bg-[#224397]/[0.02]">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-shrink-0 w-32">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Kh&#x1ED1;i</label>
                    <select id="filter-khoi" class="form-select-std">
                        <option value="">T&#x1EA5;t c&#x1EA3;</option>
                        <option value="10">Kh&#x1ED1;i 10</option>
                        <option value="11">Kh&#x1ED1;i 11</option>
                        <option value="12">Kh&#x1ED1;i 12</option>
                        <option value="Khac">Kh&aacute;c</option>
                    </select>
                </div>
                <div class="flex-shrink-0 w-40">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">L&#x1EDB;p</label>
                    <select id="filter-lop" class="form-select-std" disabled>
                        <option value="">T&#x1EA5;t c&#x1EA3;</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">T&igrave;m Nhanh</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                        <input id="quick-search" type="text" class="form-input-std pl-8" placeholder="H&#x1ECD; t&ecirc;n / S&#x1ED1; CCCD / Email...">
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">S&#x1EAF;p X&#x1EBF;p</label>
                    <div class="flex items-center gap-1">
                        <button class="btn-sort active" data-sort="total">Theo T&#x1ED5;ng</button>
                        <button class="btn-sort" data-sort="lookup">Tra C&#x1EE9;u</button>
                        <button class="btn-sort" data-sort="login">&#x110;&#x0103;ng Nh&#x1EAD;p</button>
                        <button id="toggle-order" class="btn-action ml-1" title="&#x110;&#x1EA3;o chi&#x1EC1;u">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-sort-down" viewBox="0 0 16 16"><path d="M3.5 2.5a.5.5 0 0 0-1 0v8.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L3.5 11.293zm3.5 1a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5M7.5 6a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z"/></svg>
                            <span id="order-label">Gi&#x1EA3;m d&#x1EA7;n</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-5 py-2 border-b border-[#224397]/10 bg-[#224397]/[0.01]">
            <div id="summary-text" class="text-[11px] text-slate-500 font-medium"></div>
        </div>
        <div class="overflow-x-auto">
            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width:50px;text-align:center;">STT</th>
                        <th style="width:110px;">S&#x1ED1; CCCD</th>
                        <th style="width:80px;text-align:center;">L&#x1EDB;p</th>
                        <th>H&#x1ECD; v&agrave; T&ecirc;n</th>
                        <th style="width:110px;text-align:center;">Ng&agrave;y Sinh</th>
                        <th style="width:80px;text-align:center;">Gi&#x1EDB;i T&iacute;nh</th>
                        <th>Gmail</th>
                        <th style="width:110px;text-align:center;">Tr&#x1EA1;ng Th&aacute;i TK</th>
                        <th style="width:90px;text-align:center;">Tra C&#x1EE9;u</th>
                        <th style="width:90px;text-align:center;">&#x110;&#x0103;ng Nh&#x1EAD;p</th>
                        <th style="width:70px;text-align:center;">T&#x1ED5;ng</th>
                        <th style="width:90px;text-align:center;">H&agrave;nh &#x110;&#x1ED9;ng</th>
                    </tr>
                </thead>
                <tbody id="student-tbody">
                    <?php foreach ($student_logs as $index => $log):
                        $lop    = htmlspecialchars($log['ten_lop']);
                        $khoi   = infer_khoi_from_lop($lop);
                        $lookup = (int)$log['lookup_count'];
                        $login  = (int)$log['login_count'];
                        $total  = $lookup + $login;
                    ?>
                    <tr
                        data-mahs="<?php echo htmlspecialchars($log['ma_hoc_sinh']); ?>"
                        data-lop="<?php echo $lop; ?>"
                        data-khoi="<?php echo htmlspecialchars($khoi); ?>"
                        data-name="<?php echo htmlspecialchars($log['ho_dem'].' '.$log['ten']); ?>"
                        data-email="<?php echo htmlspecialchars($log['email']); ?>"
                        data-lookup="<?php echo $lookup; ?>"
                        data-login="<?php echo $login; ?>"
                        data-total="<?php echo $total; ?>"
                    >
                        <td class="text-center stt-col font-mono text-[11px] text-slate-400"><?php echo $index + 1; ?></td>
                        <td class="font-mono text-[11px]"><?php echo htmlspecialchars($log['ma_hoc_sinh']); ?></td>
                        <td class="text-center"><span class="badge-lop"><?php echo $lop; ?></span></td>
                        <td class="font-semibold text-[12px]"><?php echo htmlspecialchars($log['ho_dem'] . ' ' . $log['ten']); ?></td>
                        <td class="text-center text-[11px] text-slate-500"><?php echo htmlspecialchars(format_date_display($log['ngay_sinh'] ?? '')); ?></td>
                        <td class="text-center text-[11px]"><?php echo htmlspecialchars($log['gioi_tinh']); ?></td>
                        <td class="text-[11px] text-slate-500"><?php echo htmlspecialchars($log['email']); ?></td>
                        <td class="text-center"><span class="badge-tk"><?php echo htmlspecialchars($log['trang_thai_tai_khoan']); ?></span></td>
                        <td class="text-center font-semibold <?php echo $lookup > 0 ? 'text-blue-700' : 'text-slate-300'; ?>"><?php echo $lookup; ?></td>
                        <td class="text-center font-semibold <?php echo $login > 0 ? 'text-green-700' : 'text-slate-300'; ?>"><?php echo $login; ?></td>
                        <td class="text-center font-bold <?php echo $total > 0 ? 'text-[#224397]' : 'text-slate-300'; ?>"><?php echo $total; ?></td>
                        <td class="text-center">
                            <button class="btn-action view-details-btn"
                                data-student-id="<?php echo $log['id']; ?>"
                                data-student-name="<?php echo htmlspecialchars($log['ho_dem'] . ' ' . $log['ten']); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                                Chi ti&#x1EBF;t
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal chi tiet (chuan UI_SYNC) -->
<div id="detailsModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" style="align-items:center;justify-content:center;" onclick="closeDetailsModal()">
    <div class="bg-white rounded-xl shadow-2xl w-[700px] max-w-[95%] max-h-[85vh] flex flex-col overflow-hidden border border-slate-200 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-slate-50 flex-shrink-0">
            <h5 class="text-base font-bold text-[#224397] flex items-center gap-2 mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge-fill text-[#FAB723]" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/></svg>
                <span id="detailsModalLabel">Chi Ti&#x1EBF;t S&#x1EED; D&#x1EE5;ng</span>
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeDetailsModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5" id="detailsModalBody"></div>
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end flex-shrink-0">
            <button type="button" class="px-4 py-1.5 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 transition" onclick="closeDetailsModal()">&#x110;&oacute;ng</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
<script>
function openDetailsModal() {
    var modal = document.getElementById('detailsModal');
    var content = modal.querySelector('.modal-content-box');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.style.opacity = '1';
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}
function closeDetailsModal() {
    var modal = document.getElementById('detailsModal');
    var content = modal.querySelector('.modal-content-box');
    modal.style.opacity = '0';
    content.classList.add('scale-95', 'translate-y-4', 'opacity-0');
    setTimeout(function() {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }, 300);
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-details-btn').forEach(function(button) {
        button.addEventListener('click', async function() {
            var studentId = this.dataset.studentId;
            var studentName = this.dataset.studentName;
            document.getElementById('detailsModalLabel').textContent = 'Chi Tiet Su Dung - ' + studentName;
            document.getElementById('detailsModalBody').innerHTML = '<div class="flex items-center justify-center py-10 text-slate-400">Dang tai du lieu...</div>';
            openDetailsModal();
            try {
                var response = await fetch('/thidua/api/get-student-log-details?id=' + encodeURIComponent(studentId));
                var data = await response.json();
                if (data.success) {
                    var html = '';
                    html += '<h6 class="font-bold text-[#224397] text-sm mb-2">Lich Su Dang Nhap</h6>';
                    if (data.logins && data.logins.length > 0) {
                        html += '<div class="overflow-x-auto mb-4"><table style="width:100%;border-collapse:collapse;font-size:0.8rem"><thead><tr><th style="background:rgba(34,67,151,0.08);color:#224397;font-weight:700;text-transform:uppercase;font-size:0.7rem;padding:0.5rem 0.7rem;border:1px solid rgba(34,67,151,0.2);">Thoi Gian</th><th style="background:rgba(34,67,151,0.08);color:#224397;font-weight:700;text-transform:uppercase;font-size:0.7rem;padding:0.5rem 0.7rem;border:1px solid rgba(34,67,151,0.2);">IP</th><th style="background:rgba(34,67,151,0.08);color:#224397;font-weight:700;text-transform:uppercase;font-size:0.7rem;padding:0.5rem 0.7rem;border:1px solid rgba(34,67,151,0.2);">Thiet Bi</th></tr></thead><tbody>';
                        data.logins.forEach(function(log) {
                            html += '<tr><td style="padding:0.45rem 0.7rem;border:1px solid rgba(34,67,151,0.1);font-family:monospace;font-size:0.75rem;">' + (log.thoi_gian||'') + '</td><td style="padding:0.45rem 0.7rem;border:1px solid rgba(34,67,151,0.1);font-family:monospace;font-size:0.75rem;">' + (log.ip||'') + '</td><td style="padding:0.45rem 0.7rem;border:1px solid rgba(34,67,151,0.1);font-size:0.75rem;">' + (log.thiet_bi||'') + '</td></tr>';
                        });
                        html += '</tbody></table></div>';
                    } else { html += '<p class="text-slate-400 text-sm mb-4">Khong co lich su dang nhap.</p>'; }
                    html += '<h6 class="font-bold text-[#224397] text-sm mb-2 mt-4">Lich Su Bi Tra Cuu</h6>';
                    if (data.lookups && data.lookups.length > 0) {
                        html += '<div class="overflow-x-auto"><table style="width:100%;border-collapse:collapse;font-size:0.8rem"><thead><tr><th style="background:rgba(34,67,151,0.08);color:#224397;font-weight:700;text-transform:uppercase;font-size:0.7rem;padding:0.5rem 0.7rem;border:1px solid rgba(34,67,151,0.2);">Thoi Gian</th><th style="background:rgba(34,67,151,0.08);color:#224397;font-weight:700;text-transform:uppercase;font-size:0.7rem;padding:0.5rem 0.7rem;border:1px solid rgba(34,67,151,0.2);">IP</th><th style="background:rgba(34,67,151,0.08);color:#224397;font-weight:700;text-transform:uppercase;font-size:0.7rem;padding:0.5rem 0.7rem;border:1px solid rgba(34,67,151,0.2);">Thiet Bi</th></tr></thead><tbody>';
                        data.lookups.forEach(function(log) {
                            html += '<tr><td style="padding:0.45rem 0.7rem;border:1px solid rgba(34,67,151,0.1);font-family:monospace;font-size:0.75rem;">' + (log.thoi_gian||'') + '</td><td style="padding:0.45rem 0.7rem;border:1px solid rgba(34,67,151,0.1);font-family:monospace;font-size:0.75rem;">' + (log.ip||'') + '</td><td style="padding:0.45rem 0.7rem;border:1px solid rgba(34,67,151,0.1);font-size:0.75rem;">' + (log.thiet_bi||'') + '</td></tr>';
                        });
                        html += '</tbody></table></div>';
                    } else { html += '<p class="text-slate-400 text-sm">Chua tung bi tra cuu.</p>'; }
                    document.getElementById('detailsModalBody').innerHTML = html;
                } else { throw new Error(data.message || 'Khong lay duoc du lieu.'); }
            } catch(error) {
                document.getElementById('detailsModalBody').innerHTML = '<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">' + error.message + '</div>';
            }
        });
    });

    var tbody = document.getElementById('student-tbody');
    var rows = Array.from(tbody.querySelectorAll('tr'));
    var filterKhoi = document.getElementById('filter-khoi');
    var filterLop = document.getElementById('filter-lop');
    var quickSearch = document.getElementById('quick-search');
    var sortBtns = document.querySelectorAll('.btn-sort[data-sort]');
    var toggleOrder = document.getElementById('toggle-order');
    var orderLabel = document.getElementById('order-label');
    var summaryText = document.getElementById('summary-text');
    var currentSort = 'total';
    var descending = true;
    var lopByKhoi = new Map();
    rows.forEach(function(tr) {
        var k = (tr.dataset.khoi||'').trim();
        var l = (tr.dataset.lop||'').trim();
        if (!lopByKhoi.has(k)) lopByKhoi.set(k, new Set());
        lopByKhoi.get(k).add(l);
    });
    function populateLopOptions(khoi) {
        filterLop.innerHTML = '<option value="">Tat ca</option>';
        if (!khoi) { filterLop.disabled = true; return; }
        var set = lopByKhoi.get(khoi);
        if (!set || set.size === 0) { filterLop.disabled = true; return; }
        Array.from(set).sort(function(a,b){ return a.localeCompare(b,'vi'); }).forEach(function(l) {
            var opt = document.createElement('option'); opt.value = l; opt.textContent = l; filterLop.appendChild(opt);
        });
        filterLop.disabled = false;
    }
    function applyFiltersAndSort() {
        var valKhoi = (filterKhoi.value||'').trim();
        var valLop = (filterLop.value||'').trim();
        var q = (quickSearch.value||'').trim().toLowerCase();
        var vis = 0;
        rows.forEach(function(tr) {
            var ok = true;
            if (valKhoi && tr.dataset.khoi !== valKhoi) ok = false;
            if (ok && valLop && tr.dataset.lop !== valLop) ok = false;
            if (ok && q) { ok = (tr.dataset.mahs||'').toLowerCase().includes(q) || (tr.dataset.name||'').toLowerCase().includes(q) || (tr.dataset.email||'').toLowerCase().includes(q); }
            tr.style.display = ok ? '' : 'none';
            if (ok) vis++;
        });
        var sign = descending ? -1 : 1;
        var visRows = rows.filter(function(tr){ return tr.style.display !== 'none'; });
        visRows.sort(function(a, b) {
            var av = Number(a.dataset[currentSort]||0), bv = Number(b.dataset[currentSort]||0);
            return av === bv ? sign * (a.dataset.name||'').localeCompare(b.dataset.name||'','vi') : sign * (av - bv);
        });
        visRows.forEach(function(tr, i) { tr.querySelector('.stt-col').textContent = i + 1; tbody.appendChild(tr); });
        var sortLabel = currentSort === 'total' ? 'Tong' : (currentSort === 'lookup' ? 'Tra cuu' : 'Dang nhap');
        summaryText.textContent = 'Hien thi ' + vis + ' / ' + rows.length + ' hoc sinh - Sap xep: ' + sortLabel + ' - ' + (descending ? 'Giam dan' : 'Tang dan');
    }
    filterKhoi.addEventListener('change', function() { populateLopOptions(filterKhoi.value); filterLop.value=''; applyFiltersAndSort(); });
    filterLop.addEventListener('change', applyFiltersAndSort);
    quickSearch.addEventListener('input', applyFiltersAndSort);
    sortBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentSort = btn.dataset.sort;
            sortBtns.forEach(function(b){ b.classList.remove('active'); });
            btn.classList.add('active');
            applyFiltersAndSort();
        });
    });
    toggleOrder.addEventListener('click', function() {
        descending = !descending;
        orderLabel.textContent = descending ? 'Giam dan' : 'Tang dan';
        applyFiltersAndSort();
    });
    applyFiltersAndSort();
});
</script>
