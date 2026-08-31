<?php
// File: src/views/admin_hoat_dong_diem_danh.php
require_once __DIR__ . '/partials/admin_header.php';
?>

<!-- Page Content -->
<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <div>
                <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase">
                    <i class="fa-solid fa-clipboard-user"></i> Điểm Danh: <?= htmlspecialchars($hoat_dong['ten_hoat_dong']) ?>
                </h3>
                <p class="text-sm text-[#224397]/80 mt-1 font-medium">
                    Điểm tích luỹ gốc: <span class="font-bold text-green-600"><?= $hoat_dong['diem_tich_luy'] ?>đ</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openShareLinkModal()" class="px-4 py-2 bg-indigo-600 text-white rounded shadow-sm hover:bg-indigo-700 hover:-translate-y-0.5 transition-all duration-300 font-medium flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/><path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/></svg> Tạo link quét
                </button>
                <button onclick="openQrModal()" class="px-4 py-2 bg-[#224397] text-white rounded shadow-sm hover:bg-blue-700 hover:-translate-y-0.5 transition-all duration-300 font-medium flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-qrcode"></i> Mở trình quét mã
                </button>
                <button onclick="openImportModal()" class="px-4 py-2 bg-[#224397] text-white rounded shadow-sm hover:bg-blue-700 hover:-translate-y-0.5 transition-all duration-300 font-medium flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/></svg> Danh sách tham gia
                </button>
                <button onclick="exportExcel()" class="px-4 py-2 bg-emerald-600 text-white rounded shadow-sm hover:bg-emerald-700 hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-file-excel"></i> Xuất Excel
                </button>
            </div>
        </div>

        <!-- QR Modal Content moved to bottom -->

        <!-- Bảng danh sách đăng ký -->
        <div class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden">
            <div class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center justify-between gap-2 text-sm uppercase">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-users"></i> Danh sách học sinh
                </div>
            </div>
            
            <!-- Bộ lọc -->
            <div class="bg-white border-b border-[#224397]/25 px-5 py-3 flex flex-row items-end gap-3">
                <div>
                    <label for="filterKhoi" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Khối</label>
                    <select id="filterKhoi" class="block w-24 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                        <option value="">Tất cả</option>
                        <option value="10">Khối 10</option>
                        <option value="11">Khối 11</option>
                        <option value="12">Khối 12</option>
                    </select>
                </div>
                <div>
                    <label for="filterLop" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Lớp</label>
                    <select id="filterLop" class="block w-28 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                        <option value="">Tất cả</option>
                        <?php foreach ($danh_sach_lop as $l): ?>
                            <option value="<?= htmlspecialchars($l['ten_lop']) ?>"><?= htmlspecialchars($l['ten_lop']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filterTrangThai" class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Trạng thái đánh giá</label>
                    <select id="filterTrangThai" class="block w-48 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px]">
                        <option value="">Tất cả</option>
                        <option value="0">Chưa đánh giá</option>
                        <option value="1">Tham gia (+100%)</option>
                        <option value="2">Không tham gia (0đ)</option>
                        <option value="3">Vi phạm/Bỏ về (-50%)</option>
                        <option value="4">Vắng không phép (-100%)</option>
                        <option value="5">Vi phạm nghiêm trọng (-200%)</option>
                    </select>
                </div>
            </div>
            <div class="p-5 overflow-x-auto list-scrollbar max-h-[75vh]">
                <table id="attendanceTable" class="w-full text-left text-sm text-slate-600 border-collapse relative">
                    <thead class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-center">STT</th>
                            <th class="px-4 py-3">Lớp</th>
                            <th class="px-4 py-3">Họ và tên</th>
                            <th class="px-4 py-3">CCCD</th>
                            <th class="px-4 py-3 text-center">Trạng thái đánh giá</th>
                            <th class="px-4 py-3 text-center">Điểm thực tế</th>
                            <th class="px-4 py-3 text-center">Phương thức</th>
                            <th class="px-4 py-3 text-right">Tuỳ chọn</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#224397]/20">
                        <!-- Load via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/thidua/public/assets/libs/dataTables.tailwindcss.min.css">
<style>
    /* Fix DataTables Tailwind default dark/ugly colors overriding dark mode utilities */
    .dataTables_wrapper select, 
    .dataTables_wrapper input {
        background-color: #fff !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
        border-radius: 0.5rem !important;
        padding: 0.375rem 0.75rem !important;
        outline: none !important;
        font-size: 0.875rem !important;
        line-height: 1.25rem !important;
    }
    .dataTables_wrapper select:focus,
    .dataTables_wrapper input:focus {
        border-color: #224397 !important;
        box-shadow: 0 0 0 2px rgba(34, 67, 151, 0.2) !important;
    }
    
    /* Pagination Overrides for Tailwind Plugin */
    .dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link,
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        background-color: #fff !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
        border-radius: 0.375rem !important;
        padding: 0.25rem 0.75rem !important;
        margin: 0 0.125rem !important;
        cursor: pointer !important;
        font-size: 0.875rem !important;
    }
    .dataTables_wrapper .dataTables_paginate .pagination .page-item.active .page-link,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .pagination .page-item:not(.disabled) .page-link:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
        background-color: #224397 !important;
        color: #fff !important;
        border-color: #224397 !important;
    }
    .dataTables_wrapper .dataTables_paginate .pagination .page-item.disabled .page-link,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        background-color: #f8fafc !important;
        color: #94a3b8 !important;
    }

    /* Table body and empty state overrides to fight dark mode */
    table.dataTable tbody tr,
    table.dataTable tbody td,
    table.dataTable tbody th {
        background-color: #fff !important;
        color: #475569 !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    table.dataTable tbody tr:hover,
    table.dataTable tbody tr:hover td {
        background-color: #f8fafc !important;
    }
    table.dataTable tbody tr.odd,
    table.dataTable tbody tr.even {
        background-color: #fff !important;
    }
    table.dataTable tbody td.dataTables_empty {
        background-color: #fff !important;
        color: #64748b !important;
        padding: 2rem !important;
        font-weight: 500 !important;
    }

    /* Headers and footers */
    table.dataTable thead th, table.dataTable thead td,
    table.dataTable tfoot th, table.dataTable tfoot td {
        background-color: #f8fafc !important; /* bg-slate-50 */
        color: #475569 !important; /* text-slate-600 */
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 0.75rem 1rem !important;
    }
    table.dataTable.no-footer {
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter {
        color: #475569 !important;
        font-size: 0.875rem !important;
        margin-bottom: 1rem !important;
        margin-top: 0.5rem !important;
    }
    /* Remove ugly sorting icons background if they clash */
    table.dataTable thead .sorting { background-image: none !important; }
</style>
<script src="/thidua/public/assets/libs/jquery-3.7.0.min.js"></script>
<script src="/thidua/public/assets/libs/jquery.dataTables.min.js"></script>
<script src="/thidua/public/assets/libs/dataTables.tailwindcss.min.js"></script>
<script src="/thidua/public/assets/libs/html5-qrcode.min.js"></script>

<script>
    const HOAT_DONG_ID = <?= $hoat_dong['id'] ?>;
    let dtTable;

    // Giữ focus cho ô input QR khi mở modal
    $(document).on('click', '#qrModal', function(e) {
        if (!$(e.target).closest('button').length) {
            $('#qr_input').focus();
        }
    });

    $(document).ready(function() {
        dtTable = $('#attendanceTable').DataTable({
            ajax: {
                url: '/thidua/api/hoat-dong-diem-danh',
                type: 'POST',
                data: function(d) {
                    d.action = 'list';
                    d.hoat_dong_id = HOAT_DONG_ID;
                },
                dataSrc: 'data'
            },
            columns: [
                { data: null, className: 'text-center font-medium', render: (d, t, r, meta) => meta.row + 1 },
                { data: 'lop', className: 'font-medium' },
                { data: 'ho_ten' },
                { data: 'cccd' },
                { 
                    data: 'trang_thai_diem_danh',
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                            <select onchange="updateStatus(${row.id}, this.value)" class="px-2 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-md focus:ring-[#224397] focus:border-[#224397] outline-none">
                                <option value="0" ${data == 0 ? 'selected' : ''}>Chưa đánh giá</option>
                                <option value="1" ${data == 1 ? 'selected' : ''}>Tham gia (+100%)</option>
                                <option value="2" ${data == 2 ? 'selected' : ''}>Không tham gia (0đ)</option>
                                <option value="3" ${data == 3 ? 'selected' : ''}>Vi phạm/Bỏ về (-50%)</option>
                                <option value="4" ${data == 4 ? 'selected' : ''}>Vắng không phép (-100%)</option>
                                <option value="5" ${data == 5 ? 'selected' : ''}>Vi phạm nghiêm trọng (-200%)</option>
                            </select>
                        `;
                    }
                },
                { 
                    data: 'diem_thuc_te',
                    className: 'text-center font-bold',
                    render: function(data) {
                        const val = parseFloat(data);
                        if (val > 0) return `<span class="text-green-600">+${val}</span>`;
                        if (val < 0) return `<span class="text-red-600">${val}</span>`;
                        return `<span class="text-slate-500">${val}</span>`;
                    }
                },
                { 
                    data: 'phuong_thuc',
                    className: 'text-center',
                    render: function(data) {
                        if (data === 'qr') return `<span class="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 rounded-md"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-qr-code mr-1 inline-block" viewBox="0 0 16 16"><path d="M2 2h2v2H2V2Z"/><path d="M6 0v6H0V0h6ZM5 1H1v4h4V1ZM4 12H2v2h2v-2Z"/><path d="M6 10v6H0v-6h6Zm-5 1v4h4v-4H1Zm11-9h2v2h-2V2Z"/><path d="M10 0v6h6V0h-6Zm5 1v4h-4V1h4ZM8 1V0h1v2H8v2H7V1h1Zm0 5V4h1v2H8ZM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8H6Zm0 0v1H2V8H1v1H0V7h3v1h3Zm10 1h-1V7h1v2Zm-1 0h-1v2h2v-1h-1V9Zm-4 0h2v1h-1v1h-1V9Zm2 3v-1h-1v1h-1v1H9v1h3v-2h1Zm0 0h3v1h-2v1h-1v-2Zm-4-1v1h1v-2H7v1h2Z"/><path d="M7 12h1v3h4v1H7v-4Zm9 2v2h-3v-1h2v-1h1Z"/></svg> Quét mã</span>`;
                        if (data === 'zalo') return `<span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-md"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-phone mr-1 inline-block" viewBox="0 0 16 16"><path d="M11 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h6zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/><path d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg> Zalo Mini App</span>`;
                        if (data === 'mobile') return `<span class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded-md"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-phone-vibrate mr-1 inline-block" viewBox="0 0 16 16"><path d="M10 3a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4zM6 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H6z"/><path d="M8 12a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM1.5 9.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5v-1zm10.5 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zM2.5 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5v-1zm10.5 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/></svg> Mobile App</span>`;
                        return `<span class="text-xs px-2 py-1 bg-slate-100 text-slate-700 rounded-md">Thủ công</span>`;
                    }
                },
                {
                    data: null,
                    className: 'text-right',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <button onclick="deleteRegistration(${row.id})" class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors ml-auto" title="Xoá học sinh khỏi danh sách">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash3-fill text-xs" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/></svg>
                            </button>
                        `;
                    }
                }
            ],
            dom: 'rt',
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json',
                info: "Tổng: _TOTAL_ học sinh · Trang _PAGE_/_PAGES_",
                infoEmpty: "Tổng: 0 học sinh",
                infoFiltered: "(lọc từ _MAX_ học sinh)",
                paginate: {
                    first: "««",
                    last: "»»",
                    next: "»",
                    previous: "«"
                }
            },
            paging: false,
            info: false
        });

        // Custom filtering function
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'attendanceTable') return true;
            
            const filterKhoi = $('#filterKhoi').val();
            const filterLop = $('#filterLop').val();
            const filterTrangThai = $('#filterTrangThai').val();
            
            const rowData = dtTable.row(dataIndex).data();
            const lop = data[1] || ''; // Data in column 1
            
            if (filterKhoi && !lop.startsWith(filterKhoi)) return false;
            if (filterLop && lop !== filterLop) return false;
            if (filterTrangThai !== '' && rowData.trang_thai_diem_danh.toString() !== filterTrangThai) return false;
            
            return true;
        });

        // Redraw table on filter change
        $('#filterKhoi, #filterLop, #filterTrangThai').on('change', function() {
            dtTable.draw();
        });
    });

    // Audio Context cho âm thanh báo hiệu
    let audioCtx = null;
    function playBeep(type) {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            if (type === 'success') {
                // Tiếng "pip" trong trẻo (880Hz)
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
                oscillator.start(audioCtx.currentTime);
                oscillator.stop(audioCtx.currentTime + 0.1);
            } else if (type === 'error') {
                // Tiếng "tịt tịt" (300Hz, 2 nhịp)
                oscillator.type = 'square';
                oscillator.frequency.setValueAtTime(300, audioCtx.currentTime); 
                gainNode.gain.setValueAtTime(0.05, audioCtx.currentTime);
                oscillator.start(audioCtx.currentTime);
                oscillator.stop(audioCtx.currentTime + 0.1);
                
                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'square';
                osc2.frequency.setValueAtTime(300, audioCtx.currentTime + 0.15);
                gain2.gain.setValueAtTime(0.05, audioCtx.currentTime + 0.15);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start(audioCtx.currentTime + 0.15);
                osc2.stop(audioCtx.currentTime + 0.25);
            }
        } catch (e) { console.error('AudioContext error:', e); }
    }

    let html5QrcodeScanner = null;
    let isProcessingScan = false;

    function openQrModal() {
        $('#qr_input').val('');
        $('#scanResult').text('');
        $('#qrModal').removeClass('hidden').addClass('flex');
        
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 250} }, false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
    }

    function closeQrModal() {
        $('#qrModal').removeClass('flex').addClass('hidden');
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().then(() => {
                html5QrcodeScanner = null;
            }).catch(error => {
                console.error("Failed to clear html5QrcodeScanner. ", error);
            });
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessingScan) return;
        submitQrScan(decodedText);
    }

    function onScanFailure(error) {
        // ignore continuous scan failures
    }

    function submitQrScan(scannedCccd = null) {
        const cccd = scannedCccd || $('#qr_input').val().trim();
        if (!cccd) return;
        
        isProcessingScan = true;
        $('#qr_input').val('');
        $('#scanResult').html('<span class="text-blue-500"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang xử lý...</span>');
        
        if (html5QrcodeScanner) {
            html5QrcodeScanner.pause(true); // pause camera while processing
        }
        
        $.ajax({
            url: '/thidua/api/hoat-dong-diem-danh',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'scan_qr', hoat_dong_id: HOAT_DONG_ID, cccd: cccd }),
            success: function(res) {
                if(res.success) {
                    playBeep('success');
                    $('#scanResult').html('');
                    dtTable.ajax.reload(null, false);
                    AppSwal.fire({
                        title: 'Thành công!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonText: 'OK (Tiếp tục quét)'
                    }).then(() => {
                        isProcessingScan = false;
                        if (html5QrcodeScanner) html5QrcodeScanner.resume();
                    });
                } else {
                    playBeep('error');
                    $('#scanResult').html('');
                    if (res.error_type === 'already_scanned') {
                        showToast('info', res.message);
                        isProcessingScan = false;
                        if (html5QrcodeScanner) html5QrcodeScanner.resume();
                    } else if (res.error_type === 'not_registered') {
                        AppSwal.fire({
                            title: 'Chưa đăng ký',
                            text: res.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Có, thêm vào danh sách',
                            cancelButtonText: 'Không'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                addAndScanQr(cccd);
                            } else {
                                isProcessingScan = false;
                                if (html5QrcodeScanner) html5QrcodeScanner.resume();
                            }
                        });
                    } else {
                        showToast('error', res.message);
                        isProcessingScan = false;
                        if (html5QrcodeScanner) html5QrcodeScanner.resume();
                    }
                }
            },
            error: function() {
                showToast('error', 'Lỗi kết nối máy chủ');
                isProcessingScan = false;
                if (html5QrcodeScanner) html5QrcodeScanner.resume();
            }
        });
    }

    function addAndScanQr(cccd) {
        AppSwal.showLoading();
        $.ajax({
            url: '/thidua/api/hoat-dong-diem-danh',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'add_and_scan_qr', hoat_dong_id: HOAT_DONG_ID, cccd: cccd }),
            success: function(res) {
                if (res.success) {
                    playBeep('success');
                    dtTable.ajax.reload(null, false);
                    AppSwal.fire({
                        title: 'Thành công!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonText: 'OK (Tiếp tục quét)'
                    }).then(() => {
                        isProcessingScan = false;
                        if (html5QrcodeScanner) html5QrcodeScanner.resume();
                    });
                } else {
                    playBeep('error');
                    AppSwal.fire('Lỗi', res.message, 'error').then(() => {
                        isProcessingScan = false;
                        if (html5QrcodeScanner) html5QrcodeScanner.resume();
                    });
                }
            },
            error: function() {
                AppSwal.fire('Lỗi', 'Lỗi kết nối máy chủ', 'error').then(() => {
                    isProcessingScan = false;
                    if (html5QrcodeScanner) html5QrcodeScanner.resume();
                });
            }
        });
    }

    function updateStatus(id, status) {
        $.ajax({
            url: '/thidua/api/hoat-dong-diem-danh',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'update_status', id: id, trang_thai: status }),
            success: function(res) {
                if(res.success) {
                    showToast('success', 'Đã cập nhật trạng thái');
                    dtTable.ajax.reload(null, false);
                } else {
                    showToast('error', res.message || 'Lỗi cập nhật');
                }
            }
        });
    }

    function deleteRegistration(id) {
        AppSwal.fire({
            title: 'Xác nhận xoá',
            text: "Xoá học sinh này khỏi danh sách hoạt động?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Huỷ'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/thidua/api/hoat-dong-diem-danh',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ action: 'delete', id: id }),
                    success: function(res) {
                        if(res.success) {
                            showToast('success', 'Đã xoá thành công');
                            dtTable.ajax.reload(null, false);
                        } else {
                            showToast('error', res.message || 'Không thể xoá');
                        }
                    }
                });
            }
        });
    }

    function exportExcel() {
        window.location.href = `/thidua/api/hoat-dong-diem-danh?action=export&hoat_dong_id=${HOAT_DONG_ID}`;
    }

    function updateImportTargets(clickedCb) {
        const val = clickedCb.value;
        const isChecked = clickedCb.checked;
        
        if (val === 'Tất cả' && isChecked) {
            $('.import-target-cb').not(clickedCb).prop('checked', false);
        } else if (isChecked && val !== 'Tất cả') {
            $('.import-target-cb[value="Tất cả"]').prop('checked', false);
        }

        const selected = [];
        $('.import-target-cb:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            $('.import-target-cb[value="Tất cả"]').prop('checked', true);
            selected.push('Tất cả');
        }

        $('#import_targets').val(selected.join(', '));
        const text = selected.join(', ');
        $('#importTargetsText').text(text.length > 40 ? text.substring(0, 40) + '...' : text);
    }

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#importTargetsDropdown').length && !$(e.target).closest('button[onclick*="importTargetsDropdown"]').length) {
            $('#importTargetsDropdown').addClass('hidden');
        }
    });

    function openImportModal() {
        $('#import_targets').val('Tất cả');
        $('#importTargetsText').text('Tất cả học sinh');
        $('.import-target-cb').prop('checked', false);
        $('.import-target-cb[value="Tất cả"]').prop('checked', true);
        $('#import_cccd_list').val('');
        $('#importModal').removeClass('hidden').addClass('flex');
    }

    function closeImportModal() {
        $('#importModal').removeClass('flex').addClass('hidden');
    }

    function importTargets() {
        const targets = $('#import_targets').val();
        if(!targets) return showToast('warning', 'Vui lòng chọn đối tượng');
        AppSwal.showLoading();
        $.ajax({
            url: '/thidua/api/hoat-dong-diem-danh', type: 'POST', contentType: 'application/json',
            data: JSON.stringify({ action: 'import_targets', hoat_dong_id: HOAT_DONG_ID, targets: targets }),
            success: function(res) {
                AppSwal.close();
                if(res.success) { showToast('success', res.message); closeImportModal(); dtTable.ajax.reload(null, false); }
                else { showToast('error', res.message); }
            }
        });
    }

    function importCCCDList() {
        const list = $('#import_cccd_list').val().trim();
        if(!list) return showToast('warning', 'Vui lòng nhập danh sách CCCD');
        AppSwal.showLoading();
        $.ajax({
            url: '/thidua/api/hoat-dong-diem-danh', type: 'POST', contentType: 'application/json',
            data: JSON.stringify({ action: 'import_cccd_list', hoat_dong_id: HOAT_DONG_ID, cccd_list: list }),
            success: function(res) {
                AppSwal.close();
                if(res.success) { showToast('success', res.message); closeImportModal(); dtTable.ajax.reload(null, false); }
                else { showToast('error', res.message); }
            }
        });
    }

    // --- LOGIC SHARE LINK ---
    function openShareLinkModal() {
        $('#shareLinkModal').removeClass('hidden').addClass('flex');
        $('#shareLinkConfig').show();
        $('#shareLinkResult').hide();
        $('#scan_password').val('');
        
        // Kiểm tra xem đã có link chưa
        $.ajax({
            url: '/thidua/api/hoat-dong-diem-danh',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'get_scan_link', hoat_dong_id: HOAT_DONG_ID }),
            success: function(res) {
                if (res.success && res.link) {
                    showScanLink(res.link);
                }
            }
        });
    }
    
    function closeShareLinkModal() {
        $('#shareLinkModal').removeClass('flex').addClass('hidden');
    }
    
    function generateScanLink() {
        const pwd = $('#scan_password').val();
        AppSwal.showLoading();
        $.ajax({
            url: '/thidua/api/hoat-dong-diem-danh',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'generate_scan_link', hoat_dong_id: HOAT_DONG_ID, password: pwd }),
            success: function(res) {
                AppSwal.close();
                if (res.success) {
                    showScanLink(res.link);
                    showToast('success', 'Đã tạo link quét thành công!');
                } else {
                    showToast('error', 'Lỗi tạo link');
                }
            }
        });
    }
    
    function showScanLink(linkPath) {
        const fullLink = window.location.origin + linkPath;
        $('#shareLinkConfig').hide();
        $('#shareLinkResult').show();
        $('#generatedLinkInput').val(fullLink);
    }
    
    function copyScanLink() {
        const copyText = document.getElementById("generatedLinkInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value).then(() => {
            showToast('success', 'Đã copy link!');
        });
    }
    
    function deleteScanLink() {
        AppSwal.fire({
            title: 'Hủy link này?',
            text: "Những người đang có link sẽ không thể tiếp tục quét điểm danh.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hủy link',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                AppSwal.showLoading();
                $.ajax({
                    url: '/thidua/api/hoat-dong-diem-danh',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ action: 'delete_scan_link', hoat_dong_id: HOAT_DONG_ID }),
                    success: function(res) {
                        if (res.success) {
                            showToast('success', 'Đã hủy link quét!');
                            $('#shareLinkResult').hide();
                            $('#shareLinkConfig').show();
                        }
                    }
                });
            }
        });
    }
</script>

<!-- Modal Import Danh Sách -->
<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[999] hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-slate-50 px-5 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/></svg> 
                Thêm danh sách tham gia
            </h3>
            <button onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        
        <div class="p-5 overflow-y-auto list-scrollbar flex-1">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Chọn nhóm đối tượng (Chọn nhiều)</label>
                
                <input type="hidden" id="import_targets" value="Tất cả">
                <button type="button" onclick="document.getElementById('importTargetsDropdown').classList.toggle('hidden')" class="w-full px-3 py-2 border border-slate-300 rounded-lg flex justify-between items-center bg-white text-left text-slate-700 focus:ring-2 focus:ring-[#224397] outline-none transition-all">
                    <span id="importTargetsText" class="truncate font-medium text-sm">Tất cả học sinh</span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div id="importTargetsDropdown" class="hidden relative z-[100] w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl max-h-[250px] overflow-y-auto list-scrollbar">
                    <div class="p-2 space-y-1">
                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                            <input type="checkbox" value="Tất cả" class="import-target-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateImportTargets(this)">
                            <span class="text-sm text-slate-700 font-medium">Toàn trường</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                            <input type="checkbox" value="Khối 10" class="import-target-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateImportTargets(this)">
                            <span class="text-sm text-slate-700 font-medium">Toàn Khối 10</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                            <input type="checkbox" value="Khối 11" class="import-target-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateImportTargets(this)">
                            <span class="text-sm text-slate-700 font-medium">Toàn Khối 11</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
                            <input type="checkbox" value="Khối 12" class="import-target-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateImportTargets(this)">
                            <span class="text-sm text-slate-700 font-medium">Toàn Khối 12</span>
                        </label>
                        
                        <div class="border-t border-slate-200 my-1"></div>
                        <div class="px-2 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider">Từng lớp cụ thể</div>
                        <div class="grid grid-cols-3 gap-1 px-1">
                            <?php foreach ($danh_sach_lop ?? [] as $lop): ?>
                                <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" value="<?php echo htmlspecialchars($lop['ten_lop']); ?>" class="import-target-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateImportTargets(this)">
                                    <span class="text-sm text-slate-700"><?php echo htmlspecialchars($lop['ten_lop']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="border-t border-slate-200 my-1"></div>
                        <div class="px-2 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider">Theo chức vụ</div>
                        <div class="grid grid-cols-2 gap-1 px-1">
                            <?php foreach ($danh_sach_chuc_vu ?? [] as $cv): ?>
                                <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" value="<?php echo htmlspecialchars($cv); ?>" class="import-target-cb w-4 h-4 text-[#224397] rounded border-slate-300 focus:ring-[#224397]" onchange="updateImportTargets(this)">
                                    <span class="text-sm text-slate-700 truncate"><?php echo htmlspecialchars($cv); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <button onclick="importTargets()" class="mt-3 w-full px-4 py-2 bg-[#224397] text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    Thêm Các Nhóm Đã Chọn
                </button>
            </div>

            <div class="relative flex py-2 items-center mb-4">
                <div class="flex-grow border-t border-slate-300"></div>
                <span class="flex-shrink-0 mx-4 text-slate-400 text-xs font-medium">HOẶC</span>
                <div class="flex-grow border-t border-slate-300"></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nhập danh sách mã CCCD (Mỗi mã 1 dòng)</label>
                <textarea id="import_cccd_list" rows="5" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#224397] outline-none text-sm font-mono" placeholder="Ví dụ:&#10;079012345678&#10;079012345679"></textarea>
                <button onclick="importCCCDList()" class="mt-2 w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">Thêm Danh Sách CCCD</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Quét QR Camera -->
<div id="qrModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[999] hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-slate-50 px-5 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-[#224397] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M2 2h2v2H2V2Z"/><path d="M6 0v6H0V0h6ZM5 1H1v4h4V1ZM4 12H2v2h2v-2Z"/><path d="M6 10v6H0v-6h6Zm-5 1v4h4v-4H1Zm11-9h2v2h-2V2Z"/><path d="M10 0v6h6V0h-6Zm5 1v4h-4V1h4ZM8 1V0h1v2H8v2H7V1h1Zm0 5V4h1v2H8ZM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8H6Zm0 0v1H2V8H1v1H0V7h3v1h3Zm10 1h-1V7h1v2Zm-1 0h-1v2h2v-1h-1V9Zm-4 0h2v1h-1v1h-1V9Zm2 3v-1h-1v1h-1v1H9v1h3v-2h1Zm0 0h3v1h-2v1h-1v-2Zm-4-1v1h1v-2H7v1h2Z"/><path d="M7 12h1v3h4v1H7v-4Zm9 2v2h-3v-1h2v-1h1Z"/></svg>
                Quét mã điểm danh
            </h3>
            <button onclick="closeQrModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        <div class="p-5 flex-1 overflow-y-auto">
            <div id="reader" width="100%"></div>
            <div class="mt-4 text-center">
                <p id="scanResult" class="text-sm font-medium h-6"></p>
            </div>
            
            <div class="mt-4 pt-4 border-t border-slate-200">
                <label class="block text-sm font-medium text-slate-700 mb-2">Hoặc nhập mã CCCD thủ công</label>
                <div class="flex gap-2">
                    <input type="text" id="qr_input" class="flex-1 px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-[#224397] outline-none" placeholder="Nhập mã CCCD...">
                    <button type="button" onclick="submitQrScan()" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-blue-700 transition">Điểm danh</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Share Link -->
<div id="shareLinkModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[999] hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden flex flex-col">
        <div class="bg-slate-50 px-5 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-indigo-700 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/><path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/></svg> 
                Tạo link quét điểm danh
            </h3>
            <button onclick="closeShareLinkModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </button>
        </div>
        
        <div class="p-5">
            <!-- Form cấu hình (khi chưa có link) -->
            <div id="shareLinkConfig">
                <p class="text-sm text-slate-600 mb-4">Bạn có thể tạo một đường link đặc biệt để gửi cho Cộng tác viên / Giáo viên quét mã điểm danh hoạt động này bằng điện thoại của họ.</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mật khẩu bảo mật (Không bắt buộc)</label>
                    <input type="text" id="scan_password" class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Để trống nếu không cần mật khẩu">
                </div>
                <button onclick="generateScanLink()" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                    Tạo link ngay
                </button>
            </div>
            
            <!-- Hiển thị link (khi đã có) -->
            <div id="shareLinkResult" class="hidden">
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg mb-4 text-emerald-800 text-sm">
                    Link quét đã được tạo thành công! Hãy gửi link này cho người phụ trách điểm danh.
                </div>
                
                <div class="flex gap-2 mb-4">
                    <input type="text" id="generatedLinkInput" readonly class="flex-1 px-3 py-2 border border-slate-300 rounded bg-slate-50 text-sm outline-none">
                    <button onclick="copyScanLink()" class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-blue-700 font-medium whitespace-nowrap">Copy</button>
                </div>
                
                <button onclick="deleteScanLink()" class="w-full px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 font-medium text-sm">
                    Hủy link này
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
