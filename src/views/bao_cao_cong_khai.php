<?php 
function print_if_not_zero($value, $decimals = 1) { 
    $numeric_value = round((float)$value, $decimals);
    if ($numeric_value != 0) {
        if (floor($numeric_value) == $numeric_value) echo (int)$numeric_value;
        else echo number_format($numeric_value, $decimals);
    }
}
$current_url = "https://" . ($_SERVER['HTTP_HOST'] ?? '') . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=850">
    <title>THI ÐUA ÐI?N T? - <?php echo htmlspecialchars($tuan_hoc['ten_tuan']); ?></title>
    <link rel="icon" type="image/x-icon" href="/thidua/public/assets/img/favicon.ico">
    <style>
        body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 10pt;
    line-height: 1.2;
    background: #f0f2f5;
    -webkit-user-select: none;
    -ms-user-select: none;
    user-select: none
}

#no-print-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: #fff;
    z-index: 999999;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem
}

#no-print-overlay .msg {
    font: 700 22px/1.5 Arial, sans-serif;
    text-transform: uppercase;
    letter-spacing: 1px
}

#no-print-overlay .sub {
    margin-top: .5rem;
    font: 500 14px/1.4 Arial, sans-serif;
    opacity: .9;
    word-break: break-word
}

@media print {
    body * {
        visibility: hidden !important
    }

    #no-print-overlay,
    #no-print-overlay * {
        display: d-block !important;
        visibility: visible !important
    }

    @page {
        margin: 0
    }
}

.top-bar {
    background: #343a40;
    color: #f8f9fa;
    padding: 5px 10px;
    font-size: 8.5pt;
    text-align: center;
    font-family: Arial, sans-serif
}

.top-bar p {
    margin: 0;
    word-break: break-all
}

.report-w-full max-w-6xl mx-auto px-4 {
    max-width: 800px;
    margin: 1.5rem auto;
    padding: 1.5rem;
    background: #fff;
    box-shadow: 0 0 15px rgba(0, 0, 0, .1)
}


.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}

.header-left {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.header-left .logo {
    height: 40px;
    margin-bottom: 8px;
}

.header-left .school-name-d-block {
    text-align: center;
    font-size: 11pt;
    font-weight: 700;
    line-height: 1.2;
}

.header-left .school-name-d-block p {
    margin: 0
}

.header-left .school-name-d-block p:first-child {
    font-weight: 400
}


.qr-code {
    width: 80px;
    height: 80px
}

.title-section {
    text-align: center;
    margin: 8px 0 5px
}

.title-section h1 {
    font-size: 13pt;
    margin: 0 0 2px;
    font-weight: 700
}

.title-section p {
    margin: 0;
    font-style: italic;
    font-size: 10pt
}

w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light {
    width: 100%;
    border-collapse: collapse;
    w-full text-start text-sm text-slate-600 border-collapse table-bordered [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-layout: fixed;
    margin-top: 5px
}

th,
td {
    border: 1px solid #000;
    padding: 3px;
    text-align: center;
    vertical-align: middle;
    word-wrap: break-word
}

th {
    font-weight: 700
}

td.lop {
    font-weight: 700
}

.kxtd {
    background: #f8cbad;
    font-weight: 700
}

.rank-1 {
    background: #a9d08e
}

.rank-2 {
    background: #ffd966
}

.rank-3 {
    background: #f4b183
}

.khoi-separator td {
    border: none;
    border-top: 1px solid #aeaeae;
    height: 1px;
    padding: 1px
}

.footer-section {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: d-flex-start
}

.footer-notes {
    width: 60%;
    font-size: 9pt;
    font-style: italic
}

.footer-signature {
    width: 35%;
    text-align: center;
    font-size: 11pt
}

.footer-signature p {
    margin: 0 0 5px
}

.signature-image {
    width: 150px;
    height: auto;
    display: d-block;
    margin: 5px auto
}

.footer-signature .signer-name {
    margin-top: 5px;
    font-weight: 700
}

.drill-down {
    cursor: pointer;
    transition: backgrouncolor .2s
}

.drill-down:hover {
    background: #e9ecef !important
}

.toolbar {
    background-color: #f8f9fa;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toolbar-title {
    font-weight: bold;
}

.dropdown-toggle::after {
    display: none;
}
    </style>
    </head>
<body>
<div id="no-print-overlay" aria-hidden="true"><div><div class="msg">IN ?N ÐÃ B? VÔ HI?U HÓA</div><div class="sub">Vui lòng xem tr?c ti?p trên h? th?ng.<br>URL: <?php echo htmlspecialchars($current_url); ?></div></div></div>
<div class="top-bar"><p><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-lock-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"/></svg> <strong>Trang xem k?t qu? thi dua di?n t? c?a tru?ng THPT Bình Son:</strong> <?php echo htmlspecialchars($current_url); ?></p></div>

<div class="report-w-full max-w-6xl mx-auto px-6">
    <div class="page-header">
        <div class="header-left">
            <img src="/thidua/public/assets/img/22logoapp.png" alt="Logo" class="logo">
            <div class="school-name-d-block">
                <p>TRU?NG THPT BÌNH SON</p>
                <p><strong>H? th?ng Ðánh Giá Thi Ðua</strong></p>
            </div>
        </div>
        <div class="header-right">
            <?php if(isset($qr_code_base64)):?>
                <img src="<?php echo $qr_code_base64; ?>" alt="QR Code" class="qr-code">
            <?php endif;?>
        </div>
    </div>
    <div class="title-section">
        <h1>B?NG TH?NG KÊ ÐI?M THI ÐUA ÐI?N T? NAM H?C 2025 - 2026</h1>
        <p><?php echo htmlspecialchars($tuan_hoc['ten_tuan']); ?> (T? <?php echo date('d/m/Y', strtotime($tuan_hoc['ngay_bat_dau'])); ?> d?n <?php echo date('d/m/Y', strtotime($tuan_hoc['ngay_ket_thuc'])); ?>)</p>
    </div>

<div class="toolbar">
    <span class="toolbar-title">Ðang xem: <?php echo htmlspecialchars($tuan_hoc['ten_tuan']); ?></span>
    <div class="dropdown">
        <button class="btn btn-sm bg-transparent hover:bg-slate-600 text-slate-600 hover:text-white border border-slate-600" type="button" id="weekFilterDropdown" aria-expanded="false" title="Ch?n tu?n khác">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-calendar-week" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>   <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
            &nbsp; Ch?n tu?n
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="weekFilterDropdown">
            <?php if (empty($public_weeks)): ?>
                <li><a class="dropdown-item disabled" href="#">Không có tu?n nào</a></li>
            <?php else: ?>
                <?php foreach($public_weeks as $week): ?>
                    <li>
                        <a class="dropdown-item <?php echo $week['id'] == $tuan_id ? 'active' : ''; ?>"
                           href="/thidua/bao-cao/cong-khai?tuan_id=<?php echo $week['id']; ?>">
                            <?php echo htmlspecialchars($week['ten_tuan']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

    <div class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-w-full max-w-6xl mx-auto px-6">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width:12%;">L?P</th>
                    <th colspan="2">ÐI?M SÐB</th>
                    <th rowspan="2" style="width:10%;">SÐB-NK</th>
                    <th colspan="2">V?NG</th>
                    <th rowspan="2" style="width:10%;">ÐI?M (+; -) KHÁC</th>
                    <th rowspan="2" style="width:10%;">N?I QUY</th>
                    <th rowspan="2" style="width:10%;">T?NG ÐI?M</th>
                    <th rowspan="2" style="width:10%;">X?P H?NG</th>
                </tr>
                <tr>
                    <th style="width:7%;">T?t</th>
                    <th style="width:7%;">TB</th>
                    <th style="width:6%;">KP</th>
                    <th style="width:6%;">P</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($report_data)): ?>
                    <tr><td colspan="10" class="text-center p-6">Không có d? li?u cho tu?n này.</td></tr>
                <?php else: ?>
                    <?php 
                    $last_khoi='';
                    foreach($report_data as $data):
                        $current_khoi = substr($data['lop'], 0, 2);
                        if ($last_khoi != '' && $current_khoi != $last_khoi) {
                            echo '<tr class="khoi-separator"><td colspan="10"></td></tr>';
                        } 
                        $last_khoi = $current_khoi;
                    ?>
                    <tr>
                        <td class="lop"><?php echo htmlspecialchars($data['lop']); ?></td>
                        <td><?php print_if_not_zero($data['diem_tiet_tot']); ?></td>
                        <td><?php print_if_not_zero($data['diem_tiet_tb']); ?></td>
                        <td><?php print_if_not_zero($data['diem_sdb_nk']); ?></td>
                        <td><?php print_if_not_zero($data['vang_kp'] ?? null, 0); ?></td>
                        <td><?php print_if_not_zero($data['vang_p'] ?? null, 0); ?></td>
                        <td><?php print_if_not_zero($data['diem_cong_tru']); ?></td>
                        <td class="drill-down" data-type="noi_quy" data-lop-id="<?php echo $data['lop_id_for_analysis']; ?>" title="Click d? xem chi ti?t vi ph?m">
                            <?php print_if_not_zero($data['diem_noi_quy']); ?>
                        </td>
                        <td><strong><?php echo round($data['tong_diem'] ?? 0, 1); ?></strong></td>
                        <td class="<?php if($data['kxtd']){echo 'kxtd drill-down';} elseif(isset($data['xep_hang'])){if($data['xep_hang']==1)echo'rank-1';elseif($data['xep_hang']==2)echo'rank-2';elseif($data['xep_hang']==3)echo'rank-3';} ?>" 
                            data-type="kxtd" data-lop-id="<?php echo $data['lop_id_for_analysis']; ?>" title="<?php if($data['kxtd'])echo 'Click d? xem lý do KXTÐ'; ?>">
                            <?php echo $data['kxtd'] ? 'KXTÐ' : ($data['xep_hang'] ?? 'N/A'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="footer-section">
        <div class="footer-notes"><strong>Ghi chú:</strong><br><?php echo nl2br(htmlspecialchars($ghi_chu_bao_cao)); ?></div>
        <div class="footer-signature">
            <p>Ð?ng Nai, ngày <?php echo date('d'); ?> tháng <?php echo date('m'); ?> nam <?php echo date('Y'); ?></p>
            <p><strong>NGU?I L?P B?NG</strong></p>
            <img src="/thidua/public/assets/img/22logoapp.png" alt="Ch? ký" class="signature-image">
            <p class="signer-name"><strong>BAN THI ÐUA</strong></p>
        </div>
    </div>
</div>
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content relative bg-white rounded-xl shadow-xl border border-slate-200 flex flex-col">
            <div class="flex items-center justify-between p-6 border-b rounded-t-xl">
                <h5 class="text-lg font-semibold text-slate-900" id="detailsModalLabel">Chi ti?t</h5>
                <button type="button" class="text-slate-400 hover:text-slate-500 p-2"></button>
            </div>
            <div class="p-6 space-y-4" id="detailsModalBody"></div>
            <div class="flex items-center justify-end p-6 border-t space-x-2 rounded-b-xl">
                <button type="button" class="btn bg-slate-600 hover:bg-slate-700 text-white shadow-sm border-transparent">Ðóng</button>
            </div>
        </div>
    </div>
</div>
<script>
(function(){var D=function(s){try{return atob(s)}catch(_){return s}},A=D("YWRkRXZlbnRMaXN0ZW5lcg=="),P=D("cHJldmVudERlZmF1bHQ="),K=D("a2V5ZG93bg=="),C=D("Y29udGV4dG1lbnU="),B=D("YmVmb3JlcHJpbnQ="),R=D("YWZ0ZXJwcmludA=="),O=D("bm8tcHJpbnQtb3ZlcmxheQ=="),G=D("Z2V0RWxlbWVudEJ5SWQ="),M=D("cHJpbnQ="),H=D("dmlzaWJpbGl0eWNoYW5nZQ=="),U=D("c2VsZWN0c3RhcnQ="),V=D("ZHJhZ3N0YXJ0"),W=D("Y29weQ=="),X=D("Y3V0"),Y=D("cGFzdGU="),Z=D("aW5wdXQ="),Q=D("Y2xpY2s="),J=D("cHJlc3NlZA==");document[A](C,function(e){e[P]()},{capture:!0});document[A](K,function(e){var t=(e.key||"").toLowerCase(),m=e.ctrlKey||e.metaKey,s=e.shiftKey;if(t==="f12")return e[P]();if(m&&s&&(t==="i"||t==="j"||t==="c"))return e[P]();if(m&&(t==="u"||t==="s"||t==="p"||t==="o"||t==="a"))return e[P]();if(t==="PrintScreen")return e[P]();},{capture:!0});["copy","cut","paste","contextmenu",U,V,W,X,Y,Z].forEach(function(evt){try{document[A](evt,function(e){e[P]()},{capture:!0})}catch(_){}});try{Object.defineProperty(window,M,{value:function(){throw new Error("print-blocked")}})}catch(_){window[M]=function(){throw new Error("print-blocked")}};var E=document[G](O);function _S(){try{E.style.display="d-flex"}catch(_){}}function _H(){try{E.style.display="none"}catch(_){}}try{if(window.matchMedia){var l=window.matchMedia("print"),h=function(m){m.matches?_S():_H()};if(l.addEventListener)l.addEventListener("change",h);else if(l.addListener)l.addListener(h)}}catch(_){ }window[A](B,_S);window[A](R,_H);function _K(){try{document.documentElement.innerHTML="";setTimeout(function(){try{while(document.firstChild)document.removeChild(document.firstChild)}catch(e){}},10)}catch(e){}try{setTimeout(function(){try{location.replace("about:blank")}catch(e){}},30)}catch(e){} }(function(){var T=160;function F(){try{var d=(window.outerWidth-window.innerWidth>T)||(window.outerHeight-window.innerHeight>T);if(d)_K()}catch(_){}}setInterval(F,400)})();(function(){var i=new Image();Object.defineProperty(i,"id",{get:function(){_K()}});setInterval(function(){try{console.log(i)}catch(_){ }},600)})();(function(){setInterval(function(){try{var t=performance.now();debugger;var q=performance.now()-t;if(q>100)_K()}catch(_){ }},800)})();(function(){var f=[D("ZG9tY29udGVudA=="),D("Ym9keQ=="),D("aHRtbA==")],n=0;function g(){try{var e=f[n%f.length];n++;var el=document.getElementsByTagName(e)[0];if(!el)return;el.setAttribute("data-"+Math.random().toString(36).slice(2),Math.random().toString(36));setTimeout(g,2000)}catch(_){}}g()})();(function(){try{var i=0;setInterval(function(){i++;if(i%29===0){try{console.clear()}catch(_){}}},5000)}catch(_){}})();document.addEventListener("DOMContentLoaded",function(){try{var dm=null /* Removed Bootstrap Modal */),L=document.getElementById("detailsModalLabel"),BD=document.getElementById("detailsModalBody"),TI=<?php echo json_encode($tuan_id); ?>;document.querySelectorAll(".drill-down").forEach(function(cell){if(cell.textContent.trim()!==""||cell.classList.contains("kxtd")){cell.addEventListener("click",function(){var type=this.dataset.type,lopId=this.dataset.lopId,lopName=this.closest("tr").querySelector("td.lop").textContent.trim();BD.innerHTML='<div class="text-center p-8"><div class="spinner-border text-primary-600"></div></div>';dm.show();var content='<div class="p-6 mb-6 rounded-lg border bg-yellow-50 text-yellow-800 border-yellow-200">Không có d? li?u chi ti?t.</div>';(async function(){try{var basePath="/thidua/api/",endpoints={noi_quy:"get-violation-details",vang:"get-attendance-details",kxtd:"get-kxtd-reason"},url=basePath+endpoints[type]+"?tuan_id="+TI+"&lop_id="+lopId;if(type==="noi_quy"){L.textContent="Chi ti?t Vi ph?m N?i quy - L?p "+lopName;var r=await fetch(url),data=await r.json();if(Array.isArray(data)&&data.length){content='<div class="overflow-x-auto w-full"><table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light border border-slate-200"><thead><tr><th>H? Tên</th><th>Tên Nhóm Vi Ph?m</th><th>Ghi Chú</th></tr></thead><tbody>';data.forEach(function(item){content+='<tr><td>'+(item.ho_ten||"")+'</td><td>'+(item.ten_vi_pham||"")+'</td><td>'+(item.ghi_chu||"")+"</td></tr>"});content+="</tbody></table></div>"}}else if(type==="vang"){L.textContent="Chi ti?t Ði?m danh - L?p "+lopName;var r2=await fetch(url),d2=await r2.json();if(Array.isArray(d2)&&d2.length){content='<div class="overflow-x-auto w-full"><table class="w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light border border-slate-200 text-center"><thead><tr><th>Ngày</th><th>V?ng P</th><th>V?ng KP</th><th>B? Ti?t</th></tr></thead><tbody>';d2.forEach(function(item){var dd=item.ngay_diem_danh?new Date(item.ngay_diem_danh):null;content+='<tr><td>'+(dd?dd.toLocaleDateString("vi-VN"):"")+'</td><td>'+(item.vang_p||"")+'</td><td>'+(item.vang_kp||"")+'</td><td>'+(item.bo_tiet||"")+"</td></tr>"});content+="</tbody></table></div>"}}else if(type==="kxtd"){L.textContent="Lý do Không Xét Thi Ðua - L?p "+lopName;var r3=await fetch(url),d3=await r3.json();content='<div class="p-6 mb-6 rounded-lg border bg-yellow-50 text-yellow-800 border-yellow-200 font-bold">'+((d3&&d3.reason)?d3.reason:"Không xác d?nh du?c lý do c? th?.")+"</div>"} }catch(e){content='<div class="p-6 mb-6 rounded-lg border bg-red-50 text-red-800 border-red-200">L?i khi t?i d? li?u.</div>'}BD.innerHTML=content})()})}})}catch(_){}})})();
</script>
</body>
</html>
