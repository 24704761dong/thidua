<?php
// File: src/views/admin_quan_ly_khao_sat.php
$page_title = 'Quản Lý Khảo Sát Ý Kiến';
require_once __DIR__ . '/partials/admin_header.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $db = get_db_connection();
    $stmt_surveys = $db->query("SELECT * FROM khao_sat ORDER BY created_at DESC");
    $surveys = $stmt_surveys->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách lớp học để filter báo cáo
    $stmt_classes = $db->query("SELECT id, ten_lop FROM raw_lop_hoc ORDER BY ten_lop ASC");
    $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}
?>

<!-- Include Chart.js for gorgeous analytics charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Include SheetJS (XLSX) for elegant client-side Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<!-- Include JSZip for downloading all files as ZIP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>



<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase" id="reportPageTitle">
                <i class="bi bi-bar-chart-fill"></i> Báo Cáo Thống Kê
            </h3>
            <a href="/thidua/admin/quan-ly-khao-sat?iframe=1" class="px-4 py-2 bg-white text-slate-700 font-medium rounded border border-slate-300 shadow-sm hover:bg-slate-50 transition flex items-center gap-2 text-sm">
                Quay lại
            </a>
        </div>

        <div id="reportTab" class="w-full mb-12">
            <!-- Filter Báo cáo -->
            <div class="bg-white rounded shadow border border-[#224397]/25 mb-4 p-4 flex flex-wrap items-end justify-between gap-4">
                <div class="flex flex-row items-end gap-2 m-0 flex-1">
                    <div>
                        <label class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Bài khảo sát</label>
                        <select id="filterSurveyId" onchange="fetchReportData()" class="block w-64 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px] outline-none">
                            <option value="">-- Chọn bài khảo sát --</option>
                            <?php foreach ($surveys as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['tieu_de']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Khối</label>
                        <select id="filterKhoi" onchange="fetchReportData()" class="block w-20 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px] outline-none">
                            <option value="">Tất cả</option>
                            <option value="10">Khối 10</option>
                            <option value="11">Khối 11</option>
                            <option value="12">Khối 12</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13.5px] font-bold text-[#224397] mb-0.5">Lớp học</label>
                        <select id="filterClassId" onchange="fetchReportData()" class="block w-24 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-2 py-1 text-[13px] outline-none">
                            <option value="">Toàn trường</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['ten_lop']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <button onclick="exportExcelReport()" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Xuất Excel
                    </button>
                    <button onclick="openAllFilesModal()" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 6.854-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.293V5.5a.5.5 0 0 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708z"/></svg> Tệp đính kèm
                    </button>
                    <button onclick="fetchReportData()" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg> Làm mới
                    </button>
                </div>
            </div>

            <!-- NỘI DUNG BÁO CÁO -->
            <div id="reportContentArea" class="space-y-6 hidden">
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white border border-slate-200 rounded p-6 shadow-sm flex items-center gap-4">
                        <div class="w-14 h-14 rounded bg-blue-50 text-[#224397] flex items-center justify-center text-2xl font-bold shadow-sm">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-500 uppercase">Tổng số học sinh</h4>
                            <div class="text-2xl font-black text-slate-800 mt-1" id="lblTotalStudents">0</div>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-6 shadow-sm flex items-center gap-4">
                        <div class="w-14 h-14 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shadow-sm">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-500 uppercase">Đã hoàn thành</h4>
                            <div class="text-2xl font-black text-emerald-600 mt-1" id="lblCompleted">0</div>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded p-6 shadow-sm flex items-center gap-4">
                        <div class="w-14 h-14 rounded bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold shadow-sm">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-500 uppercase">Chưa thực hiện</h4>
                            <div class="text-2xl font-black text-amber-600 mt-1" id="lblPending">0</div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS & THỐNG KÊ CÂU HỎI -->
                <div class="bg-white rounded shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 font-bold text-[#224397] flex items-center gap-2 uppercase">
                        <i class="bi bi-pie-chart-fill"></i> Biểu Đồ Thống Kê Theo Từng Câu Hỏi
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8" id="chartsContainer">
                        <!-- Charts dynamically generated here -->
                    </div>
                </div>

                <!-- DANH SÁCH HỌC SINH ĐÃ LÀM / CHƯA LÀM -->
                <div class="bg-white rounded shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 font-bold text-[#224397] flex items-center justify-between uppercase">
                        <span class="flex items-center gap-2"><i class="bi bi-person-lines-fill"></i> Danh Sách Chi Tiết Theo Lớp</span>
                        <div class="flex items-center gap-1.5 text-xs">
                            <button onclick="exportStudentListExcel()" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm mr-2"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg> Xuất Excel</button>
                            <button onclick="toggleStudentTable('completed')" id="btnTabCompleted" class="px-4 py-1.5 bg-[#224397] text-white font-bold rounded-lg shadow-sm">Đã làm</button>
                            <button onclick="toggleStudentTable('pending')" id="btnTabPending" class="px-4 py-1.5 bg-white text-slate-600 font-bold rounded-lg border border-slate-200 shadow-sm hover:bg-slate-50">Chưa làm</button>
                        </div>
                    </div>
                    <div class="p-6 overflow-x-auto list-scrollbar">
                        <table class="w-full text-left text-sm text-slate-600" id="tableCompleted">
                            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 w-16 text-center">STT</th>
                                    <th class="px-4 py-3">Họ và Tên</th>
                                    <th class="px-4 py-3 w-32 text-center">Lớp</th>
                                    <th class="px-4 py-3 w-44 text-center">Ngày nộp</th>
                                    <th class="px-4 py-3 w-40 text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200" id="tbodyCompleted">
                                <!-- Generated via JS -->
                            </tbody>
                        </table>

                        <table class="w-full text-left text-sm text-slate-600 hidden" id="tablePending">
                            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 w-16 text-center">STT</th>
                                    <th class="px-4 py-3">Họ và Tên</th>
                                    <th class="px-4 py-3 w-32 text-center">Lớp</th>
                                    <th class="px-4 py-3 w-44 text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200" id="tbodyPending">
                                <!-- Generated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL XEM TẤT CẢ TỆP ĐÍNH KÈM -->
<div id="allFilesModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-4xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="bg-indigo-600 px-6 py-4 text-white font-bold flex items-center justify-between shrink-0">
            <span class="text-lg flex items-center gap-2"><i class="bi bi-folder-fill"></i> Tệp Đính Kèm Của Biểu Mẫu</span>
            <button onclick="closeAllFilesModal()" class="text-white/80 hover:text-white font-bold text-xl">&times;</button>
        </div>
        <div class="p-6 overflow-y-auto list-scrollbar space-y-4 flex-1" id="allFilesContent">
            <!-- Details generated via JS -->
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0 gap-3">
            <button onclick="downloadAllFilesZip()" class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded shadow-sm hover:bg-indigo-700 transition flex items-center gap-2">
                <i class="bi bi-download"></i> Tải tất cả (ZIP)
            </button>
            <button onclick="closeAllFilesModal()" class="px-6 py-2.5 bg-slate-200 text-slate-700 font-bold rounded shadow-sm hover:bg-slate-300 transition">Đóng</button>
        </div>
    </div>
</div>

<!-- MODAL XEM CHI TIẾT CÂU TRẢ LỜI CỦA HỌC SINH -->
<div id="studentAnswersModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="bg-[#224397] px-6 py-4 text-white font-bold flex items-center justify-between shrink-0">
            <span class="text-lg flex items-center gap-2"><i class="bi bi-file-earmark-person"></i> Chi Tiết Bài Làm Học Sinh</span>
            <button onclick="closeStudentAnswersModal()" class="text-white/80 hover:text-white font-bold text-xl">&times;</button>
        </div>
        <div class="p-6 overflow-y-auto list-scrollbar space-y-6 flex-1" id="studentAnswersContent">
            <!-- Details generated via JS -->
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0">
            <button onclick="closeStudentAnswersModal()" class="px-6 py-2.5 bg-[#224397] text-white font-bold rounded shadow-sm hover:bg-[#FAB723] hover:text-slate-900 transition">Đóng</button>
        </div>
    </div>
</div>

<script>
// Hàm tải Excel an toàn - hỗ trợ cả iframe
function downloadExcelBlob(blob, filename) {
    try {
        // Thử tải từ cửa sổ cha (tránh bị iframe chặn)
        const targetWin = window.top || window.parent || window;
        const url = targetWin.URL.createObjectURL(blob);
        const a = targetWin.document.createElement('a');
        a.href = url;
        a.download = filename;
        a.style.display = 'none';
        targetWin.document.body.appendChild(a);
        a.click();
        setTimeout(() => { try { targetWin.document.body.removeChild(a); } catch(e){} targetWin.URL.revokeObjectURL(url); }, 15000);
    } catch (e) {
        // Fallback: tải từ window hiện tại
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        setTimeout(() => { try { document.body.removeChild(a); } catch(e){} URL.revokeObjectURL(url); }, 15000);
    }
}
let currentReportData = null;
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    if (id) {
        document.getElementById('filterSurveyId').value = id;
        fetchReportData();
    }
});
let activeChartInstances = [];



function fetchReportData() {
    const surveyId = document.getElementById('filterSurveyId').value;
    const classId = document.getElementById('filterClassId').value;
    const khoi = document.getElementById('filterKhoi').value;
    const area = document.getElementById('reportContentArea');

    if (!surveyId) {
        area.classList.add('hidden');
        return;
    }

    area.classList.remove('hidden');

    fetch(`/thidua/api/admin/khao-sat?action=report&survey_id=${surveyId}&lop_hoc_id=${classId}&khoi=${khoi}`)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            currentReportData = data;
            document.getElementById('lblTotalStudents').innerText = data.da_lam.length + data.chua_lam.length;
            document.getElementById('lblCompleted').innerText = data.da_lam.length;
            document.getElementById('lblPending').innerText = data.chua_lam.length;

            renderStudentTables(data.da_lam, data.chua_lam);
            renderCharts(data.charts);
        }
    });
}

function renderStudentTables(daLam, chuaLam) {
    const tbodyCom = document.getElementById('tbodyCompleted');
    const tbodyPen = document.getElementById('tbodyPending');

    tbodyCom.innerHTML = daLam.length === 0 ? `<tr><td colspan="5" class="py-8 text-center text-slate-400">Không có học sinh nào đã làm trong lớp này.</td></tr>` : daLam.map((hs, idx) => `
        <tr class="hover:bg-slate-50 transition">
            <td class="px-4 py-3 text-center font-medium">${idx + 1}</td>
            <td class="px-4 py-3 font-bold text-[#224397]">${hs.ho_ten}</td>
            <td class="px-4 py-3 text-center font-semibold text-slate-700">${hs.ten_lop}</td>
            <td class="px-4 py-3 text-center font-medium text-slate-500">${hs.ngay_nop}</td>
            <td class="px-4 py-3 text-center">
                <button onclick="showStudentAnswers(${hs.id})" class="px-3 py-1.5 bg-[#224397] text-white rounded-lg text-xs font-bold hover:bg-[#FAB723] hover:text-slate-900 transition shadow-sm">
                    Xem bài làm
                </button>
            </td>
        </tr>
    `).join('');

    tbodyPen.innerHTML = chuaLam.length === 0 ? `<tr><td colspan="4" class="py-8 text-center text-slate-400">Tất cả học sinh trong lớp đã hoàn thành!</td></tr>` : chuaLam.map((hs, idx) => `
        <tr class="hover:bg-slate-50 transition">
            <td class="px-4 py-3 text-center font-medium">${idx + 1}</td>
            <td class="px-4 py-3 font-bold text-slate-700">${hs.ho_ten}</td>
            <td class="px-4 py-3 text-center font-semibold text-slate-700">${hs.ten_lop}</td>
            <td class="px-4 py-3 text-center"><span class="px-3 py-1 bg-amber-100 text-amber-700 font-bold text-xs rounded-full border border-amber-200">Chưa nộp</span></td>
        </tr>
    `).join('');
}

function toggleStudentTable(type) {
    const btnCom = document.getElementById('btnTabCompleted');
    const btnPen = document.getElementById('btnTabPending');
    const tblCom = document.getElementById('tableCompleted');
    const tblPen = document.getElementById('tablePending');

    if (type === 'completed') {
        btnCom.className = "px-4 py-1.5 bg-[#224397] text-white font-bold rounded-lg shadow-sm";
        btnPen.className = "px-4 py-1.5 bg-white text-slate-600 font-bold rounded-lg border border-slate-200 shadow-sm hover:bg-slate-50";
        tblCom.classList.remove('hidden');
        tblPen.classList.add('hidden');
    } else {
        btnPen.className = "px-4 py-1.5 bg-[#224397] text-white font-bold rounded-lg shadow-sm";
        btnCom.className = "px-4 py-1.5 bg-white text-slate-600 font-bold rounded-lg border border-slate-200 shadow-sm hover:bg-slate-50";
        tblPen.classList.remove('hidden');
        tblCom.classList.add('hidden');
    }
}

function exportStudentListExcel() {
    if (!currentReportData) return;
    try {
        const wb = XLSX.utils.book_new();
        
        // Sheet Đã làm
        const doneRows = [['STT', 'Mã Học Sinh', 'Họ và Tên', 'Lớp', 'Ngày Nộp']];
        currentReportData.da_lam.forEach((hs, idx) => {
            doneRows.push([idx + 1, hs.ma_hoc_sinh || '', hs.ho_ten, hs.ten_lop, hs.ngay_nop]);
        });
        const wsDone = XLSX.utils.aoa_to_sheet(doneRows);
        XLSX.utils.book_append_sheet(wb, wsDone, "Da_Lam");

        // Sheet Chưa làm
        const pendingRows = [['STT', 'Mã Học Sinh', 'Họ và Tên', 'Lớp']];
        currentReportData.chua_lam.forEach((hs, idx) => {
            pendingRows.push([idx + 1, hs.ma_hoc_sinh || '', hs.ho_ten, hs.ten_lop]);
        });
        const wsPending = XLSX.utils.aoa_to_sheet(pendingRows);
        XLSX.utils.book_append_sheet(wb, wsPending, "Chua_Lam");

        const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
        const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        downloadExcelBlob(blob, `Danh_Sach_Chi_Tiet_Khao_Sat_${document.getElementById('filterSurveyId').value}.xlsx`);
    } catch (e) {
        console.error('Lỗi xuất Excel DS:', e);
        AppSwal.fire({ title: 'Lỗi', text: 'Không thể xuất file Excel DS: ' + e.message, icon: 'error' });
    }
}

function showStudentAnswers(hsId) {
    if (!currentReportData) return;
    const hs = currentReportData.da_lam.find(item => item.id === hsId);
    if (!hs) return;

    const container = document.getElementById('studentAnswersContent');
    container.innerHTML = `
        <div class="bg-slate-50 p-4 rounded border border-slate-200 flex items-center justify-between">
            <div>
                <h4 class="text-base font-bold text-[#224397]">${hs.ho_ten}</h4>
                <p class="text-xs text-slate-500 mt-0.5">Lớp: ${hs.ten_lop}</p>
            </div>
            <span class="text-xs font-medium text-slate-500">Nộp: ${hs.ngay_nop}</span>
        </div>
    `;

    currentReportData.questions.forEach((q, idx) => {
        if (q.loai_cau_hoi === 'section_header') {
            container.insertAdjacentHTML('beforeend', `<div class="bg-[#224397]/10 px-4 py-2 rounded font-bold text-[#224397] text-xs uppercase mt-4">${q.tieu_de}</div>`);
            return;
        }

        const ans = hs.answers[q.id];
        let ansHtml = '<span class="text-slate-400 italic text-xs">Không trả lời</span>';

        if (ans !== undefined && ans !== '') {
            if (q.loai_cau_hoi === 'file_upload') {
                const files = Array.isArray(ans) ? ans : [ans];
                ansHtml = `<div class="space-y-1.5">${files.map(url => `<a href="${url}" target="_blank" class="text-xs font-bold text-[#224397] underline flex items-center gap-1"><i class="bi bi-cloud-arrow-down-fill"></i> Xem / Tải tệp: ${url.split('/').pop()}</a>`).join('')}</div>`;
            } else if (['grid_radio', 'grid_checkbox'].includes(q.loai_cau_hoi)) {
                ansHtml = `<div class="space-y-1 bg-slate-50 p-3 rounded border border-slate-200 text-xs font-semibold text-slate-800">`;
                Object.entries(ans).forEach(([rKey, rVal]) => {
                    const valStr = Array.isArray(rVal) ? rVal.join(', ') : rVal;
                    ansHtml += `<div><span class="font-bold text-[#224397]">${rKey}:</span> ${valStr}</div>`;
                });
                ansHtml += `</div>`;
            } else if (Array.isArray(ans)) {
                ansHtml = `<ul class="list-disc list-inside text-xs font-semibold text-slate-800 space-y-1">${ans.map(v => `<li>${v}</li>`).join('')}</ul>`;
            } else if (q.loai_cau_hoi === 'star_rating') {
                ansHtml = `<div class="flex items-center gap-1 text-amber-500">${Array.from({length: Number(ans)}).map(() => '<i class="bi bi-star-fill"></i>').join('')} <span class="text-slate-600 font-bold text-xs ml-1">(${ans} sao)</span></div>`;
            } else {
                ansHtml = `<p class="text-xs font-semibold text-slate-800 whitespace-pre-line">${ans}</p>`;
            }
        }

        container.insertAdjacentHTML('beforeend', `
            <div class="space-y-1.5 pb-3 border-b border-slate-100">
                <h5 class="text-xs font-bold text-slate-600">${idx + 1}. ${q.tieu_de}</h5>
                <div class="pt-1">${ansHtml}</div>
            </div>
        `);
    });

    document.getElementById('studentAnswersModal').classList.remove('hidden');
}

function closeStudentAnswersModal() {
    document.getElementById('studentAnswersModal').classList.add('hidden');
}

function renderCharts(chartsData) {
    const container = document.getElementById('chartsContainer');
    container.innerHTML = '';

    // Destroy old instances
    activeChartInstances.forEach(chart => chart.destroy());
    activeChartInstances = [];

    chartsData.forEach((item, idx) => {
        const cardId = `chartCard_${idx}`;
        const canvasId = `chartCanvas_${idx}`;

        let contentHtml = '';
        if (['radio', 'dropdown', 'checkbox', 'linear_scale', 'star_rating'].includes(item.type)) {
            contentHtml = `<div class="h-64 flex items-center justify-center"><canvas id="${canvasId}"></canvas></div>`;
        } else {
            contentHtml = `
                <div class="max-h-64 overflow-y-auto list-scrollbar space-y-2 pr-1">
                    ${item.responses.length === 0 ? `<p class="text-xs text-slate-400 italic text-center py-8">Chưa có câu trả lời nào.</p>` : item.responses.map(r => `
                        <div class="bg-slate-50 p-3 rounded border border-slate-100 flex flex-col gap-1">
                            <span class="text-[11px] font-bold text-[#224397]">${r.ho_ten}</span>
                            <p class="text-xs font-semibold text-slate-700 whitespace-pre-line">${r.gia_tri.startsWith('http') ? `<a href="${r.gia_tri}" target="_blank" class="text-[#224397] underline flex items-center gap-1"><i class="bi bi-cloud-arrow-down-fill"></i> Tải tệp đính kèm</a>` : r.gia_tri}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        container.insertAdjacentHTML('beforeend', `
            <div class="bg-white border border-slate-200 rounded p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 leading-snug flex items-center gap-2">
                    <i class="bi bi-question-circle-fill text-[#224397]"></i> ${item.title}
                </h4>
                ${contentHtml}
            </div>
        `);

        if (['radio', 'dropdown', 'checkbox', 'linear_scale', 'star_rating'].includes(item.type)) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            const isPie = ['radio', 'dropdown'].includes(item.type);

            const newChart = new Chart(ctx, {
                type: isPie ? 'doughnut' : 'bar',
                data: {
                    labels: item.labels,
                    datasets: [{
                        label: 'Số lượng chọn',
                        data: item.series,
                        backgroundColor: ['#224397', '#FAB723', '#10B981', '#F43F5E', '#8B5CF6', '#06B6D4', '#F97316'],
                        borderRadius: isPie ? 0 : 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: isPie, position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    scales: isPie ? {} : {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
            activeChartInstances.push(newChart);
        }
    });
}

function exportExcelReport() {
    try {
        if (!currentReportData || currentReportData.da_lam.length === 0) {
            AppSwal.fire({ title: 'Thông báo', text: 'Chưa có dữ liệu bài làm để xuất Excel.', icon: 'warning' });
            return;
        }

        const rows = [];
        const header = ['STT', 'Mã Học Sinh', 'Họ và Tên', 'Lớp', 'Ngày Nộp'];
        currentReportData.questions.forEach((q, idx) => {
            if (q.loai_cau_hoi !== 'section_header') {
                header.push(`${idx + 1}. ${q.tieu_de}`);
            }
        });
        rows.push(header);

        currentReportData.da_lam.forEach((hs, idx) => {
            const row = [idx + 1, hs.ma_hoc_sinh || '', hs.ho_ten, hs.ten_lop, hs.ngay_nop];
            currentReportData.questions.forEach(q => {
                if (q.loai_cau_hoi !== 'section_header') {
                    const ans = hs.answers[q.id];
                    let val = '';
                    if (ans !== undefined && ans !== null && ans !== '') {
                        if (q.loai_cau_hoi === 'file_upload') {
                            val = (Array.isArray(ans) ? ans : [ans]).join(', ');
                        } else if (['grid_radio', 'grid_checkbox'].includes(q.loai_cau_hoi)) {
                            if (typeof ans === 'object') {
                                val = Object.entries(ans).map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(', ') : v}`).join('; ');
                            } else {
                                val = String(ans);
                            }
                        } else if (Array.isArray(ans)) {
                            val = ans.join(', ');
                        } else {
                            val = String(ans);
                        }
                    }
                    row.push(val);
                }
            });
            rows.push(row);
        });

        const worksheet = XLSX.utils.aoa_to_sheet(rows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Danh_Sach_Cau_Tra_Loi");
        
        const wbout = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
        const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        downloadExcelBlob(blob, `Bao_Cao_Khao_Sat_${document.getElementById('filterSurveyId').value}.xlsx`);
    } catch (e) {
        console.error('Lỗi xuất Excel:', e);
        AppSwal.fire({ title: 'Lỗi', text: 'Không thể xuất file Excel: ' + e.message, icon: 'error' });
    }
}

function openAllFilesModal() {
    if (!currentReportData || currentReportData.da_lam.length === 0) {
        AppSwal.fire({ title: 'Thông báo', text: 'Chưa có dữ liệu bài làm để tải tệp.', icon: 'warning' });
        return;
    }

    const container = document.getElementById('allFilesContent');
    container.innerHTML = '';
    let hasFiles = false;

    currentReportData.questions.forEach(q => {
        if (q.loai_cau_hoi === 'file_upload') {
            let filesHtml = '';
            currentReportData.da_lam.forEach(hs => {
                const ans = hs.answers[q.id];
                if (ans && ans !== '') {
                    const files = Array.isArray(ans) ? ans : [ans];
                    if (files.length > 0) hasFiles = true;
                    files.forEach(url => {
                        filesHtml += `
                            <div class="bg-slate-50 p-3 rounded border border-slate-200 flex items-center justify-between gap-4">
                                <div class="flex-1 overflow-hidden">
                                    <h4 class="text-[11px] font-bold text-[#224397] uppercase">${hs.ho_ten} (${hs.ten_lop})</h4>
                                    <a href="${url}" target="_blank" class="text-xs font-semibold text-slate-700 truncate hover:text-indigo-600 block transition">${url.split('/').pop()}</a>
                                </div>
                                <a href="${url}" target="_blank" class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center shrink-0 hover:bg-slate-300 transition text-[#224397]">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        `;
                    });
                }
            });

            if (filesHtml) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="space-y-2 pb-4">
                        <h4 class="text-xs font-bold text-slate-600 border-b border-slate-100 pb-1">${q.tieu_de}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">${filesHtml}</div>
                    </div>
                `);
            }
        }
    });

    if (!hasFiles) {
        AppSwal.fire({ title: 'Thông báo', text: 'Không có tệp đính kèm nào được tải lên trong bài khảo sát này.', icon: 'info' });
        return;
    }

    document.getElementById('allFilesModal').classList.remove('hidden');
}

function closeAllFilesModal() {
    document.getElementById('allFilesModal').classList.add('hidden');
}

function downloadAllFilesZip() {
    if (typeof JSZip === 'undefined' || typeof saveAs === 'undefined') {
        AppSwal.fire({ title: 'Lỗi', text: 'Thư viện tạo ZIP chưa được tải.', icon: 'error' });
        return;
    }

    AppSwal.fire({ title: 'Đang tải và nén...', text: 'Vui lòng chờ trong lúc hệ thống xử lý các tệp', allowOutsideClick: false, didOpen: () => AppSwal.showLoading() });
    
    const zip = new JSZip();
    const promises = [];
    let fileCount = 0;

    currentReportData.questions.forEach(q => {
        if (q.loai_cau_hoi === 'file_upload') {
            const folder = zip.folder(q.tieu_de.substring(0, 50).replace(/[^a-zA-Z0-9_\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF \-]/g, ''));
            
            currentReportData.da_lam.forEach(hs => {
                const ans = hs.answers[q.id];
                if (ans && ans !== '') {
                    const files = Array.isArray(ans) ? ans : [ans];
                    files.forEach((url, fIdx) => {
                        const hsName = hs.ho_ten.replace(/[^a-zA-Z0-9_\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]/g, '');
                        const extMatch = url.match(/\.([a-zA-Z0-9]+)(?:[\?#]|$)/);
                        const ext = extMatch ? extMatch[1] : 'file';
                        const fileName = `${hsName}_${hs.ten_lop}_F${fIdx + 1}.${ext}`;
                        
                        fileCount++;
                        const p = fetch(url, { mode: 'cors' })
                            .then(res => {
                                if (res.ok) return res.blob();
                                throw new Error('Network response was not ok');
                            })
                            .then(blob => folder.file(fileName, blob))
                            .catch(err => console.error('Lỗi tải file:', url, err));
                        promises.push(p);
                    });
                }
            });
        }
    });

    if (fileCount === 0) {
        AppSwal.fire({ title: 'Thông báo', text: 'Không có tệp đính kèm nào.', icon: 'info' });
        return;
    }

    Promise.allSettled(promises).then(() => {
        zip.generateAsync({ type: 'blob' }).then(content => {
            saveAs(content, `KhaoSat_${document.getElementById('filterSurveyId').value}_TepDinhKem.zip`);
            AppSwal.fire({ title: 'Thành công', text: 'Đã tải xuống ZIP thành công!', icon: 'success' });
        }).catch(err => {
            AppSwal.fire({ title: 'Lỗi', text: 'Lỗi khi nén file: ' + err.message, icon: 'error' });
        });
    });
}
</script>

<!-- CROPPER MODAL -->
<div id="cropperModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden">
    <div class="bg-white w-full max-w-4xl rounded shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-[#224397] font-extrabold text-base flex items-center gap-2">
                <i class="bi bi-crop"></i> Căn chỉnh & Cắt Banner (Tỷ lệ chuẩn 21:9 / 1680×720)
            </h3>
            <button onclick="closeCropperModal()" class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-300 transition font-bold text-xs">✕</button>
        </div>
        <div class="p-6 flex-1 overflow-y-auto max-h-[60vh] flex items-center justify-center bg-slate-100">
            <div class="w-full max-h-[50vh]">
                <img id="cropperImage" src="" alt="Image for cropping" class="max-w-full block" />
            </div>
        </div>
        <div class="p-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50">
            <button onclick="closeCropperModal()" class="px-5 py-2.5 bg-slate-200 text-slate-700 font-bold rounded hover:bg-slate-300 transition text-xs">Hủy bỏ</button>
            <button onclick="confirmCropBanner()" class="px-6 py-2.5 bg-[#224397] text-white font-extrabold rounded hover:bg-[#FAB723] hover:text-slate-900 transition shadow-md text-xs flex items-center gap-2">
                <i class="bi bi-check2-circle"></i> Xác nhận & Tải lên
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>



