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


<!-- Include Cropper.js for professional 21:9 banner cropping (local) -->
<link rel="stylesheet" href="/thidua/public/assets/libs/cropper.min.css" />
<script src="/thidua/public/assets/libs/cropper.min.js"></script>


<div class="flex-1 overflow-y-auto bg-transparent p-6 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6 border-b border-[#224397]/25 pb-3">
            <h3 class="text-xl font-bold text-[#224397] flex items-center gap-2 uppercase">
                <i class="bi bi-pencil-square"></i> Thiết Kế Khảo Sát
            </h3>
            <div class="flex items-center gap-2">
                <a href="/thidua/admin/quan-ly-khao-sat?iframe=1" class="px-4 py-2 bg-white text-slate-700 font-medium rounded border border-slate-300 shadow-sm hover:bg-slate-50 transition flex items-center gap-2 text-sm">
                    Quay lại
                </a>
                <button onclick="submitCreateSurvey()" class="px-4 py-2 bg-[#224397] text-white font-medium rounded shadow-sm hover:bg-[#FAB723] hover:text-slate-900 transition flex items-center gap-2 text-sm">
                    Lưu & Phát hành
                </button>
            </div>
        </div>

        <div id="createFormCard" class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden w-full mb-12">
                <div class="bg-[#224397] px-6 py-4 text-white font-bold flex items-center justify-between">
                    <span class="text-lg flex items-center gap-2"><i class="bi bi-pencil-square"></i> Thiết Kế Bài Khảo Sát</span>
                    <button onclick="toggleCreateForm(false)" class="text-white/80 hover:text-white font-bold text-xl">&times;</button>
                </div>
                <div class="p-6 space-y-6">
                    <div class="space-y-6">
                        <!-- Dòng 1: Tiêu đề -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Tiêu đề khảo sát <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg border border-slate-200 shadow-sm" id="surveyStyleToolbar">
                                    <button onclick="updateSurveyStyle('bold', !surveyStyle.bold)" id="btnSurveyBold" class="w-7 h-7 rounded flex items-center justify-center text-xs font-bold transition bg-white shadow-sm text-slate-600 hover:text-[#224397]">B</button>
                                    <button onclick="updateSurveyStyle('italic', !surveyStyle.italic)" id="btnSurveyItalic" class="w-7 h-7 rounded flex items-center justify-center text-xs font-bold italic transition bg-white shadow-sm text-slate-600 hover:text-[#224397]">I</button>
                                    <button onclick="updateSurveyStyle('underline', !surveyStyle.underline)" id="btnSurveyUnderline" class="w-7 h-7 rounded flex items-center justify-center text-xs font-bold underline transition bg-white shadow-sm text-slate-600 hover:text-[#224397]">U</button>
                                    <div class="w-px h-5 bg-slate-300 mx-1"></div>
                                    <input type="color" id="surveyColor" value="#1e293b" onchange="updateSurveyStyle('color', this.value)" class="w-6 h-6 rounded cursor-pointer border-0 p-0 shadow-sm" title="Màu chữ">
                                </div>
                            </div>
                            <input type="text" id="surveyTitle" placeholder="Ví dụ: Khảo sát hoạt động ngoại khóa..." class="w-full bg-slate-50 border border-slate-200 rounded px-4 py-3 font-semibold text-slate-800 text-lg focus:bg-white focus:border-[#224397] outline-none transition shadow-sm placeholder:font-normal placeholder:text-slate-400">
                        </div>

                        <!-- Dòng 2: Hạn nộp & Loại khảo sát -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Hạn nộp</label>
                                <input type="datetime-local" id="surveyDueDate" class="w-full bg-slate-50 border border-slate-200 rounded px-4 py-2.5 font-semibold text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition shadow-sm">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Loại khảo sát</label>
                                <select id="surveyType" class="w-full bg-slate-50 border border-slate-200 rounded px-4 py-2.5 font-semibold text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition shadow-sm">
                                    <option value="bat_buoc">Bắt buộc</option>
                                    <option value="tu_nguyen">Tự nguyện</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dòng 3: Mô tả -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Mô tả chi tiết</label>
                            <textarea id="surveyDesc" rows="3" placeholder="Nhập mục đích hoặc hướng dẫn khảo sát..." class="w-full bg-slate-50 border border-slate-200 rounded px-4 py-3 font-medium text-slate-700 focus:bg-white focus:border-[#224397] outline-none transition shadow-sm placeholder:font-normal placeholder:text-slate-400"></textarea>
                        </div>
                    </div>

                    <!-- BANNER KHẢO SÁT 21:9 -->
                    <div class="space-y-2 pt-6 border-t border-slate-100">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Banner / Ảnh Bìa Khảo Sát</label>
                        <p class="text-[11px] text-slate-500 italic mb-2">Kích thước khuyến nghị: 1680 × 720 px (Tỷ lệ khung hình: 21:9)</p>
                        <div class="flex items-center gap-3">
                            <input type="text" id="surveyBannerUrl" placeholder="URL ảnh banner hoặc tải lên từ máy..." class="flex-1 bg-slate-50 border border-slate-200 rounded px-4 py-2.5 font-semibold text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition shadow-sm">
                            <label class="px-5 py-2.5 bg-[#224397]/5 text-[#224397] font-bold rounded border border-[#224397]/20 hover:bg-[#224397]/10 transition cursor-pointer flex items-center gap-2 shrink-0">
                                <i class="bi bi-upload"></i> Tải ảnh lên
                                <input type="file" onclick="this.value = ''" onchange="uploadSurveyBanner(this)" class="hidden" accept="image/*">
                            </label>
                        </div>
                    </div>

                    <!-- DANH SÁCH CÂU HỎI -->
                    <div class="space-y-4" id="questionBuilderList">
                        <!-- Câu hỏi sẽ được render qua JS -->
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex gap-2">
                            <button onclick="addQuestion()" class="px-4 py-2 bg-[#224397] text-white font-bold rounded hover:bg-[#FAB723] hover:text-slate-900 transition flex items-center gap-2 shadow-sm">
                                <i class="bi bi-plus-circle"></i> Thêm câu hỏi
                            </button>
                            <button onclick="addSection()" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded hover:bg-slate-200 transition flex items-center gap-2 border border-slate-200 shadow-sm">
                                <i class="bi bi-card-heading text-[#224397]"></i> Thêm phần mới
                            </button>
                        </div>
                        
                    </div>
                </div>
    </div>
</div>
<script>
let questions = [];
let currentReportData = null;
let surveyId = <?= isset($_GET['id']) ? (int)$_GET['id'] : 'null' ?>;
let activeChartInstances = [];
let editingSurveyId = null;
let surveyStyle = { bold: false, italic: false, underline: false, color: '#1e293b' };
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    if (id) {
        // Fetch survey to edit
        fetch('/thidua/api/admin/khao-sat?action=get_detail&survey_id=' + id)
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    editingSurveyId = res.survey.id;
                    document.getElementById('surveyTitle').value = res.survey.tieu_de || '';
                    document.getElementById('surveyDesc').value = res.survey.mo_ta || '';
                    document.getElementById('surveyDueDate').value = res.survey.han_nop || '';
                    document.getElementById('surveyType').value = res.survey.loai_khao_sat || 'bat_buoc';
                    document.getElementById('surveyBannerUrl').value = res.survey.banner_url || '';
                    
                    surveyStyle = res.survey.style || { bold: false, italic: false, underline: false, color: '#1e293b' };
                    questions = res.questions || [];
                    
                    if(questions.length === 0) {
                        questions = [{ id: Date.now(), tieu_de: 'Câu hỏi 1', mo_ta: '', loai_cau_hoi: 'radio', bat_buoc: true, tuy_chon: { options: ['Lựa chọn 1', 'Lựa chọn 2'], has_other: false } }];
                    }
                    applySurveyStyle();
                    renderQuestions();
                } else {
                    AppSwal.fire('Lỗi', 'Không tìm thấy bài khảo sát', 'error');
                }
            }).catch(e => {
                AppSwal.fire('Lỗi', 'Lỗi tải dữ liệu', 'error');
            });
    } else {
        // Create new
        questions = [{ id: Date.now(), tieu_de: 'Câu hỏi 1', mo_ta: '', loai_cau_hoi: 'radio', bat_buoc: true, tuy_chon: { options: ['Lựa chọn 1', 'Lựa chọn 2'], has_other: false } }];
        applySurveyStyle();
        renderQuestions();
    }
});

function applySurveyStyle() {
    const input = document.getElementById('surveyTitle');
    input.style.fontWeight = surveyStyle.bold ? 'bold' : 'normal';
    input.style.fontStyle = surveyStyle.italic ? 'italic' : 'normal';
    input.style.textDecoration = surveyStyle.underline ? 'underline' : 'none';
    input.style.color = surveyStyle.color || '#1e293b';

    document.getElementById('btnSurveyBold').className = `w-6 h-6 rounded flex items-center justify-center text-xs font-bold transition ${surveyStyle.bold ? 'bg-[#224397] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`;
    document.getElementById('btnSurveyItalic').className = `w-6 h-6 rounded flex items-center justify-center text-xs font-bold italic transition ${surveyStyle.italic ? 'bg-[#224397] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`;
    document.getElementById('btnSurveyUnderline').className = `w-6 h-6 rounded flex items-center justify-center text-xs font-bold underline transition ${surveyStyle.underline ? 'bg-[#224397] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`;
    document.getElementById('surveyColor').value = surveyStyle.color || '#1e293b';
}

function updateSurveyStyle(prop, val) {
    surveyStyle[prop] = val;
    applySurveyStyle();
}

function addQuestion() {
    questions.push({
        id: Date.now(),
        tieu_de: 'Câu hỏi mới',
        mo_ta: '',
        loai_cau_hoi: 'short_text',
        bat_buoc: true,
        tuy_chon: { options: ['Lựa chọn 1'], has_other: false }
    });
    renderQuestions();
    setTimeout(() => {
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }, 100);
}

function addSection() {
    questions.push({
        id: Date.now(),
        tieu_de: 'Phần mới',
        mo_ta: 'Mô tả phần này...',
        loai_cau_hoi: 'section_header',
        bat_buoc: false,
        tuy_chon: { options: [], has_other: false }
    });
    renderQuestions();
    setTimeout(() => {
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }, 100);
}

function removeQuestion(id) {
    questions = questions.filter(q => q.id !== id);
    renderQuestions();
}

function updateQuestionProp(id, prop, val) {
    const q = questions.find(item => item.id === id);
    if (q) {
        q[prop] = val;
        if (prop === 'loai_cau_hoi') renderQuestions();
    }
}

function updateQuestionStyle(qId, prop, val) {
    const q = questions.find(item => item.id === qId);
    if (q) {
        if (!q.tuy_chon) q.tuy_chon = {};
        if (!q.tuy_chon.style) q.tuy_chon.style = { bold: false, italic: false, underline: false, color: '#1e293b' };
        q.tuy_chon.style[prop] = val;
        renderQuestions();
    }
}

function updateQuestionOption(qId, optIdx, val) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon && q.tuy_chon.options) {
        q.tuy_chon.options[optIdx] = val;
    }
}

function addOption(qId) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon) {
        if (!q.tuy_chon.options) q.tuy_chon.options = [];
        q.tuy_chon.options.push('Lựa chọn mới ' + (q.tuy_chon.options.length + 1));
        renderQuestions();
    }
}

function removeOption(qId, optIdx) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon && q.tuy_chon.options) {
        q.tuy_chon.options.splice(optIdx, 1);
        renderQuestions();
    }
}

function toggleHasOther(qId, val) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon) {
        q.tuy_chon.has_other = val;
    }
}

function addGridRow(qId) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon) {
        if (!q.tuy_chon.grid_rows) q.tuy_chon.grid_rows = [];
        q.tuy_chon.grid_rows.push('Hàng mới ' + (q.tuy_chon.grid_rows.length + 1));
        renderQuestions();
    }
}

function removeGridRow(qId, idx) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon && q.tuy_chon.grid_rows) {
        q.tuy_chon.grid_rows.splice(idx, 1);
        renderQuestions();
    }
}

function updateGridRow(qId, idx, val) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon && q.tuy_chon.grid_rows) {
        q.tuy_chon.grid_rows[idx] = val;
    }
}

function addGridCol(qId) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon) {
        if (!q.tuy_chon.grid_cols) q.tuy_chon.grid_cols = [];
        q.tuy_chon.grid_cols.push('Cột mới ' + (q.tuy_chon.grid_cols.length + 1));
        renderQuestions();
    }
}

function removeGridCol(qId, idx) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon && q.tuy_chon.grid_cols) {
        q.tuy_chon.grid_cols.splice(idx, 1);
        renderQuestions();
    }
}

function updateGridCol(qId, idx, val) {
    const q = questions.find(item => item.id === qId);
    if (q && q.tuy_chon && q.tuy_chon.grid_cols) {
        q.tuy_chon.grid_cols[idx] = val;
    }
}

function renderQuestions() {
    const container = document.getElementById('questionBuilderList');
    container.innerHTML = '';

    questions.forEach((q, idx) => {
        let optionsHtml = '';
        if (['radio', 'checkbox', 'dropdown'].includes(q.loai_cau_hoi)) {
            const opts = q.tuy_chon?.options || [];
            optionsHtml = `
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="text-[11px] font-bold text-slate-600 uppercase block">Các lựa chọn đáp án:</label>
                    ${opts.map((opt, oIdx) => `
                        <div class="flex items-center gap-2">
                            <span class="text-[#224397] font-bold w-6 text-center">${oIdx + 1}.</span>
                            <input type="text" value="${opt}" onchange="updateQuestionOption(${q.id}, ${oIdx}, this.value)" class="flex-1 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-800 outline-none focus:border-[#224397]">
                            <button onclick="removeOption(${q.id}, ${oIdx})" class="text-rose-500 hover:text-rose-700 font-bold px-2 py-1">&times;</button>
                        </div>
                    `).join('')}
                    <div class="flex items-center justify-between pt-2">
                        <button onclick="addOption(${q.id})" class="text-xs font-bold text-[#224397] hover:underline flex items-center gap-1">
                            <i class="bi bi-plus-circle"></i> Thêm lựa chọn
                        </button>
                        ${['radio', 'checkbox'].includes(q.loai_cau_hoi) ? `
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                                <input type="checkbox" ${q.tuy_chon?.has_other ? 'checked' : ''} onchange="toggleHasOther(${q.id}, this.checked)" class="w-4 h-4 accent-[#224397]">
                                Cho phép điền Khác
                            </label>
                        ` : ''}
                    </div>
                </div>
            `;
        } else if (['linear_scale'].includes(q.loai_cau_hoi)) {
            optionsHtml = `<p class="text-xs text-slate-500 italic pt-1">Học sinh sẽ đánh giá trên thang điểm từ 1 đến 10.</p>`;
        } else if (['star_rating'].includes(q.loai_cau_hoi)) {
            optionsHtml = `<p class="text-xs text-slate-500 italic pt-1">Học sinh sẽ đánh giá xếp hạng 5 sao.</p>`;
        } else if (['grid_radio', 'grid_checkbox'].includes(q.loai_cau_hoi)) {
            if (!q.tuy_chon) q.tuy_chon = {};
            if (!q.tuy_chon.grid_rows) q.tuy_chon.grid_rows = ['Hàng 1', 'Hàng 2'];
            if (!q.tuy_chon.grid_cols) q.tuy_chon.grid_cols = ['Cột 1', 'Cột 2'];

            optionsHtml = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-3 border-t border-slate-100">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-600 uppercase block">Các hàng (Câu hỏi phụ):</label>
                        ${q.tuy_chon.grid_rows.map((row, rIdx) => `
                            <div class="flex items-center gap-2">
                                <span class="text-[#224397] font-bold w-6 text-center">H${rIdx + 1}.</span>
                                <input type="text" value="${row}" onchange="updateGridRow(${q.id}, ${rIdx}, this.value)" class="flex-1 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-800 outline-none focus:border-[#224397]">
                                <button onclick="removeGridRow(${q.id}, ${rIdx})" class="text-rose-500 hover:text-rose-700 font-bold px-2 py-1">&times;</button>
                            </div>
                        `).join('')}
                        <button onclick="addGridRow(${q.id})" class="text-xs font-bold text-[#224397] hover:underline flex items-center gap-1 pt-1">
                            <i class="bi bi-plus-circle"></i> Thêm hàng
                        </button>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-600 uppercase block">Các cột (Thang đánh giá):</label>
                        ${q.tuy_chon.grid_cols.map((col, cIdx) => `
                            <div class="flex items-center gap-2">
                                <span class="text-[#224397] font-bold w-6 text-center">C${cIdx + 1}.</span>
                                <input type="text" value="${col}" onchange="updateGridCol(${q.id}, ${cIdx}, this.value)" class="flex-1 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-800 outline-none focus:border-[#224397]">
                                <button onclick="removeGridCol(${q.id}, ${cIdx})" class="text-rose-500 hover:text-rose-700 font-bold px-2 py-1">&times;</button>
                            </div>
                        `).join('')}
                        <button onclick="addGridCol(${q.id})" class="text-xs font-bold text-[#224397] hover:underline flex items-center gap-1 pt-1">
                            <i class="bi bi-plus-circle"></i> Thêm cột
                        </button>
                    </div>
                </div>
            `;
        } else if (['file_upload'].includes(q.loai_cau_hoi)) {
            optionsHtml = `<p class="text-xs text-slate-500 italic pt-1">Học sinh tải lên file/ảnh (lưu trữ trực tiếp trên Cloudflare R2).</p>`;
        }

        const isSection = q.loai_cau_hoi === 'section_header';
        const cardStyle = isSection ? 'bg-[#224397]/5 border-[#224397]/30 shadow-sm' : 'bg-white border-slate-200 shadow-sm hover:shadow-md transition-shadow';
        const labelText = isSection ? 'Tiêu đề Phần' : 'Tiêu đề / Nội dung câu hỏi';

        const card = `
            <div class="border rounded p-6 space-y-5 relative group ${cardStyle}">
                <div class="flex flex-col md:flex-row gap-5 items-start">
                    <!-- Left: Question Title & Formatting -->
                    <div class="flex-1 space-y-3 w-full">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide flex items-center gap-2">
                                <span class="bg-[#224397] text-white w-6 h-6 rounded-full flex items-center justify-center text-[10px] shadow-sm">${idx + 1}</span>
                                ${labelText}
                            </label>
                            
                            <!-- Formatting Toolbar -->
                            <div class="flex items-center gap-1 bg-white border border-slate-200 p-1 rounded-lg">
                                <button onclick="updateQuestionStyle(${q.id}, 'bold', ${!(q.tuy_chon?.style?.bold)})" class="w-7 h-7 rounded flex items-center justify-center text-xs font-bold transition ${q.tuy_chon?.style?.bold ? 'bg-[#224397] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'}">B</button>
                                <button onclick="updateQuestionStyle(${q.id}, 'italic', ${!(q.tuy_chon?.style?.italic)})" class="w-7 h-7 rounded flex items-center justify-center text-xs font-bold italic transition ${q.tuy_chon?.style?.italic ? 'bg-[#224397] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'}">I</button>
                                <button onclick="updateQuestionStyle(${q.id}, 'underline', ${!(q.tuy_chon?.style?.underline)})" class="w-7 h-7 rounded flex items-center justify-center text-xs font-bold underline transition ${q.tuy_chon?.style?.underline ? 'bg-[#224397] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'}">U</button>
                                <div class="w-px h-4 bg-slate-300 mx-1"></div>
                                <input type="color" value="${q.tuy_chon?.style?.color || '#1e293b'}" onchange="updateQuestionStyle(${q.id}, 'color', this.value)" class="w-6 h-6 rounded cursor-pointer border-0 p-0 shadow-sm" title="Màu chữ">
                            </div>
                        </div>
                        <input type="text" value="${q.tieu_de}" onchange="updateQuestionProp(${q.id}, 'tieu_de', this.value)" style="color: ${q.tuy_chon?.style?.color || '#1e293b'}; font-weight: ${q.tuy_chon?.style?.bold ? 'bold' : 'normal'}; font-style: ${q.tuy_chon?.style?.italic ? 'italic' : 'normal'}; text-decoration: ${q.tuy_chon?.style?.underline ? 'underline' : 'none'}" class="w-full bg-slate-50 border-b-2 border-slate-200 focus:bg-slate-100 px-4 py-3 text-base ${isSection ? 'text-lg font-bold' : ''} outline-none focus:border-[#224397] transition rounded-t-xl placeholder:text-slate-400 placeholder:font-normal" placeholder="${isSection ? 'Nhập tiêu đề phần...' : 'Nhập câu hỏi...'}">
                    </div>

                    <!-- Right: Question Type -->
                    <div class="w-full md:w-64 shrink-0 space-y-3 md:pt-7">
                        <select onchange="updateQuestionProp(${q.id}, 'loai_cau_hoi', this.value)" class="w-full bg-white border border-slate-200 rounded px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-[#224397] shadow-sm cursor-pointer hover:bg-slate-50 transition">
                            <option value="short_text" ${q.loai_cau_hoi === 'short_text' ? 'selected' : ''}>Trả lời ngắn</option>
                            <option value="long_text" ${q.loai_cau_hoi === 'long_text' ? 'selected' : ''}>Đoạn (Văn bản dài)</option>
                            <option value="radio" ${q.loai_cau_hoi === 'radio' ? 'selected' : ''}>Trắc nghiệm (1 đáp án)</option>
                            <option value="checkbox" ${q.loai_cau_hoi === 'checkbox' ? 'selected' : ''}>Hộp kiểm (Nhiều đáp án)</option>
                            <option value="dropdown" ${q.loai_cau_hoi === 'dropdown' ? 'selected' : ''}>Menu thả xuống</option>
                            <option value="file_upload" ${q.loai_cau_hoi === 'file_upload' ? 'selected' : ''}>Tải tệp / Ảnh lên</option>
                            <option value="linear_scale" ${q.loai_cau_hoi === 'linear_scale' ? 'selected' : ''}>Phạm vi tuyến tính (1-10)</option>
                            <option value="star_rating" ${q.loai_cau_hoi === 'star_rating' ? 'selected' : ''}>Xếp hạng (5 sao)</option>
                            <option value="grid_radio" ${q.loai_cau_hoi === 'grid_radio' ? 'selected' : ''}>Lưới trắc nghiệm</option>
                            <option value="grid_checkbox" ${q.loai_cau_hoi === 'grid_checkbox' ? 'selected' : ''}>Lưới hộp kiểm</option>
                            <option value="date" ${q.loai_cau_hoi === 'date' ? 'selected' : ''}>Ngày</option>
                            <option value="time" ${q.loai_cau_hoi === 'time' ? 'selected' : ''}>Giờ</option>
                            <option value="section_header" ${q.loai_cau_hoi === 'section_header' ? 'selected' : ''}>--- Tiêu đề Phần ---</option>
                        </select>
                    </div>
                </div>

                <!-- Description / Subtitle -->
                <div>
                    <input type="text" value="${q.mo_ta}" onchange="updateQuestionProp(${q.id}, 'mo_ta', this.value)" placeholder="Mô tả / Hướng dẫn phụ (Không bắt buộc)..." class="w-full bg-transparent border-b border-dashed border-slate-300 focus:border-[#224397] px-2 py-1.5 text-xs text-slate-500 outline-none transition placeholder:text-slate-300">
                </div>

                <!-- Options / Details -->
                <div class="${optionsHtml ? 'pt-4' : 'hidden'}">
                    ${optionsHtml}
                </div>

                <!-- Bottom Controls: Require & Delete -->
                <div class="flex items-center justify-end gap-6 pt-5 mt-3 border-t border-slate-100">
                    ${!isSection ? `
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" ${q.bat_buoc ? 'checked' : ''} onchange="updateQuestionProp(${q.id}, 'bat_buoc', this.checked)">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#224397]"></div>
                        <span class="ml-2 text-xs font-bold text-slate-600 select-none">Bắt buộc</span>
                    </label>
                    <div class="w-px h-5 bg-slate-300"></div>
                    ` : ''}
                    <button onclick="removeQuestion(${q.id})" class="text-slate-400 hover:text-rose-500 transition-colors flex items-center justify-center p-2 rounded-full hover:bg-rose-50" title="Xóa">
                        <i class="bi bi-trash3-fill text-lg"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', card);
    });
}

function submitCreateSurvey() {
    const title = document.getElementById('surveyTitle').value.trim();
    const dueDate = document.getElementById('surveyDueDate').value.trim();
    const desc = document.getElementById('surveyDesc').value.trim();
    const type = document.getElementById('surveyType').value;
    const bannerUrl = document.getElementById('surveyBannerUrl').value.trim();

    if (!title) {
        AppSwal.fire({ title: 'Cảnh báo', text: 'Vui lòng nhập tiêu đề bài khảo sát!', icon: 'warning' });
        return;
    }
    if (questions.length === 0) {
        AppSwal.fire({ title: 'Cảnh báo', text: 'Vui lòng thêm ít nhất 1 câu hỏi!', icon: 'warning' });
        return;
    }

    fetch('/thidua/api/admin/khao-sat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: editingSurveyId ? 'update' : 'create',
            survey_id: editingSurveyId,
            tieu_de: title,
            mo_ta: desc,
            loai_khao_sat: type,
            han_nop: dueDate,
            banner_url: bannerUrl,
            style: surveyStyle,
            questions: questions
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AppSwal.fire({ title: 'Thành công', text: data.message, icon: 'success' }).then(() => window.location.reload());
        } else {
            AppSwal.fire({ title: 'Lỗi', text: data.message, icon: 'error' });
        }
    })
    .catch(err => AppSwal.fire({ title: 'Lỗi', text: 'Không thể kết nối đến máy chủ.', icon: 'error' }));
}

let cropperInstance = null;
function uploadSurveyBanner(input) {
    if (!input.files || input.files.length === 0) return;
    const file = input.files[0];

    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        // Nếu Cropper chưa load, load động rồi mới mở
        if (typeof Cropper === 'undefined') {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = '/thidua/public/assets/libs/cropper.min.css';
            document.head.appendChild(link);

            const script = document.createElement('script');
            script.src = '/thidua/public/assets/libs/cropper.min.js';
            script.onload = function() {
                openCropperWithImage(dataUrl);
            };
            script.onerror = function() {
                AppSwal.fire({ title: 'Lỗi', text: 'Không thể tải thư viện cắt ảnh.', icon: 'error' });
            };
            document.head.appendChild(script);
        } else {
            openCropperWithImage(dataUrl);
        }
    };
    reader.onerror = function() {
        AppSwal.fire({ title: 'Lỗi', text: 'Không thể đọc file ảnh', icon: 'error' });
    };
    reader.readAsDataURL(file);
}

function openCropperWithImage(dataUrl) {
    try {
        // Destroy old cropper instance
        if (cropperInstance) {
            try { cropperInstance.destroy(); } catch(ex) {}
            cropperInstance = null;
        }

        // Replace the img element entirely to avoid stale Cropper state
        const container = document.getElementById('cropperImage').parentNode;
        const oldImg = document.getElementById('cropperImage');
        const newImg = document.createElement('img');
        newImg.id = 'cropperImage';
        newImg.alt = 'Image for cropping';
        newImg.className = 'max-w-full block';
        newImg.style.maxHeight = '50vh';
        container.replaceChild(newImg, oldImg);

        // Show modal
        document.getElementById('cropperModal').classList.remove('hidden');

        // Wait for image to fully load before initializing Cropper
        newImg.onload = function() {
            try {
                cropperInstance = new Cropper(newImg, {
                    aspectRatio: 21 / 9,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true
                });
            } catch(ex) {
                AppSwal.fire({ title: 'Lỗi', text: 'Không thể khởi tạo bộ cắt ảnh: ' + ex.message, icon: 'error' });
            }
        };
        newImg.onerror = function() {
            AppSwal.fire({ title: 'Lỗi', text: 'Không thể hiển thị ảnh', icon: 'error' });
        };
        newImg.src = dataUrl;
    } catch(ex) {
        AppSwal.fire({ title: 'Lỗi', text: 'Lỗi xử lý ảnh: ' + ex.message, icon: 'error' });
    }
}

function confirmCropBanner() {
    if (!cropperInstance) return;
    AppSwal.fire({ title: 'Đang tải lên...', text: 'Vui lòng chờ trong giây lát', allowOutsideClick: false, didOpen: () => AppSwal.showLoading() });
    
    cropperInstance.getCroppedCanvas({ width: 1680, height: 720 }).toBlob(blob => {
        const formData = new FormData();
        formData.append('file', blob, 'banner_cropped.jpg');
        
        fetch('/thidua/api/zalo/upload-survey-file', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            document.getElementById('cropperModal').classList.add('hidden');
            if (cropperInstance) cropperInstance.destroy();
            cropperInstance = null;

            if (data.success) {
                document.getElementById('surveyBannerUrl').value = data.file_url;
                AppSwal.fire({ title: 'Thành công', text: 'Tải banner 21:9 lên thành công!', icon: 'success' });
            } else {
                AppSwal.fire({ title: 'Lỗi', text: data.message || 'Tải ảnh thất bại', icon: 'error' });
            }
        })
        .catch(() => AppSwal.fire({ title: 'Lỗi', text: 'Không thể kết nối máy chủ', icon: 'error' }));
    }, 'image/jpeg', 0.9);
}

function closeCropperModal() {
    document.getElementById('cropperModal').classList.add('hidden');
    if (cropperInstance) cropperInstance.destroy();
    cropperInstance = null;
}

function editSurvey(id) {
    AppSwal.fire({ title: 'Đang tải dữ liệu...', allowOutsideClick: false, didOpen: () => AppSwal.showLoading() });
    fetch(`/thidua/api/admin/khao-sat?action=get_detail&survey_id=${id}`)
    .then(res => res.json())
    .then(data => {
        AppSwal.close();
        if (data.success) {
            editingSurveyId = data.survey.id;
            document.getElementById('surveyTitle').value = data.survey.tieu_de || '';
            document.getElementById('surveyDesc').value = data.survey.mo_ta || '';
            document.getElementById('surveyDueDate').value = data.survey.han_nop || '';
            document.getElementById('surveyType').value = data.survey.loai_khao_sat || 'tu_nguyen';
            document.getElementById('surveyBannerUrl').value = data.survey.banner_url || '';
            surveyStyle = data.survey.style || { bold: false, italic: false, underline: false, color: '#1e293b' };
            applySurveyStyle();
            questions = data.questions;
            document.getElementById('createFormCard').classList.remove('hidden');
            renderQuestions();
            document.getElementById('createFormCard').scrollIntoView({ behavior: 'smooth' });
        } else {
            AppSwal.fire({ title: 'Lỗi', text: data.message, icon: 'error' });
        }
    })
    .catch(() => AppSwal.fire({ title: 'Lỗi', text: 'Lỗi kết nối máy chủ', icon: 'error' }));
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




