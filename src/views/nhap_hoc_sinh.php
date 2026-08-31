<?php
$page_title = 'Import Data Excel';
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
    /* ----- B?ng màu và bi?n CSS hi?n d?i ----- */
    :root {
        --primary-blue: #00a8e8;
        --primary-green: #97c93c;
        --dark-blue: #2c3e50;
        --text-primary: #1d2d35;
        --text-secondary: #5a6a72;
        --bg-light: #f4f7f9;
        --card-border: #e9ecef;
    }

    body {
        background-color: var(--bg-light);
    }

    /* ----- Giao di?n Card hi?n d?i ----- */
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
        font-size: 1.25rem;
        padding: 1rem 1.5rem;
    }

    /* ----- NÂNG C?P: Khu v?c t?i file kéo-th? (Drag & Drop) ----- */
    .upload-area {
        border: 2px dashed var(--card-border);
        border-radius: 12px;
        padding: 2.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        background-color: #fafcfe;
    }
    .upload-area:hover, .upload-area.is-dragover {
        border-color: var(--primary-blue);
        background-color: #f0f8ff;
    }
    .upload-area .upload-icon {
        font-size: 3rem;
        color: var(--primary-blue);
    }
    .upload-area .upload-text {
        color: var(--text-secondary);
        font-weight: 500;
        margin-top: 1rem;
    }
    #excelFile {
        display: none; /* ?n input file m?c d?nh */
    }

    /* ----- Hi?n th? thông tin file dã ch?n ----- */
    #file-info {
        margin-top: 1rem;
        padding: 0.75rem 1.25rem;
        background-color: var(--bg-light);
        border-radius: 8px;
        font-weight: 500;
        display: none; /* M?c d?nh ?n */
    }
    #file-info .bi-file-earmark-excel-fill {
        color: var(--primary-green);
    }

    /* ----- Nút b?m và các thành ph?n khác ----- */
    .btn.btn-sm {
        border-radius: 8px !important;
        font-weight: 500;
        padding: 0.6rem 1.2rem;
    }
    .btn btn-sm-gradient-primary {
        background-image: linear-gradient(45deg, var(--primary-blue), #00c0ff);
        border: none;
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.07);
        transition: all 0.2s ease-in-out;
    }
    .btn btn-sm-gradient-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .alert {
        border-radius: 8px !important;
    }

</style>
<div class="w-full max-w-7xl mx-auto px-6 sm:px-4 lg:px-5">
    <div class="flex flex-wrap -mx-3 justify-center mt-8">
        <div class="w-full md:w-2/3 px-6 col-lg-7">
            <div class="bg-white rounded-xl shadow-sm border border-[#224397]/[45%] mb-6">
                <div class="px-6 py-6 border-b border-[#224397]/25 bg-[#224397]/5 rounded-t-xl font-semibold">
                    <h3 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-upload mr-2 text-primary-600" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>   <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/></svg>T?i Lên File Danh Sách H?c Sinh</h3>
                </div>
                <div class="p-6 p-6 p-md-5">
                    <div class="p-6 mb-6 rounded-lg border bg-cyan-50 text-cyan-800 border-cyan-200" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-info-circle-fill mr-2" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                        Vui lòng ch?n file Excel (.xlsx) theo dúng d?nh d?ng. Th?y/cô có th? <a href="/thidua/tai-file-mau-hoc-sinh" class="alert-link">t?i file m?u t?i dây</a>.
                    </div>

                    <form action="/thidua/admin/hoc-sinh?action=import_process" method="POST" enctype="multipart/form-data" id="uploadForm">
                        
                        <div class="upload-area" id="uploadArea">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-cloud-arrow-up-fill upload-icon" viewBox="0 0 16 16"><path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0z"/></svg>
                            <p class="upload-text">Kéo và th? file vào dây, ho?c nh?n d? ch?n file</p>
                        </div>
                        <input class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" type="file" id="excelFile" name="excelFile" accept=".xlsx" required>
                        
                        <div id="file-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-excel-fill mr-2" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                            <span id="file-name"></span>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <a href="/thidua/admin/hoc-sinh" class="px-4 py-2 bg-slate-600 border border-transparent rounded text-white hover:bg-slate-700 hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm ml-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left mr-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>Quay l?i
                            </a>
                            <button type="submit" class="px-4 py-2 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 font-medium flex items-center justify-center gap-2 text-sm shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-eye-fill mr-2" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>   <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>B?t d?u nh?p
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('excelFile');
    const fileInfo = document.getElementById('file-info');
    const fileNameSpan = document.getElementById('file-name');

    // M? c?a s? ch?n file khi click vào khu v?c upload
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    // X? lý khi file du?c ch?n qua c?a s?
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            handleFile(fileInput.files[0]);
        }
    });

    // Ngan ch?n hành vi m?c d?nh c?a trình duy?t
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Thêm/xóa class d? thay d?i giao di?n khi kéo file vào
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.add('is-dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.remove('is-dragover');
        }, false);
    });

    // X? lý khi th? file
    uploadArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            // Gán file dã th? vào input ?n
            fileInput.files = files;
            handleFile(files[0]);
        }
    }, false);

    // Hàm hi?n th? thông tin file
    function handleFile(file) {
        fileNameSpan.textContent = file.name;
        fileInfo.style.display = 'd-flex';
    }
});
</script>
