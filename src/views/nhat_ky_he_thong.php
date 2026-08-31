<?php
$page_title = 'Nhật ký hệ thống & Thống kê';
require_once __DIR__ . '/partials/admin_header.php';

$total_visits = $total_visits ?? 0;
$active_sessions = $active_sessions ?? [];
$lookup_history = $lookup_history ?? [];
$login_history = $login_history ?? [];
$support_request_count = $support_request_count ?? 0;
?>
<style>
    body { background-color: #f4f7f9; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }

    /* === Stat Cards === */
    .stat-card {
        background: #fff;
        border-radius: 10px;
        border: 1px solid rgba(34,67,151,0.18);
        box-shadow: 0 2px 8px rgba(34,67,151,0.06);
        padding: 1.1rem 1.25rem;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
        color: inherit;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(34,67,151,0.12); color: inherit; text-decoration: none; }
    .stat-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; color: #fff; flex-shrink: 0; }
    .stat-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px; }
    .stat-value { font-size: 1.9rem; font-weight: 800; color: #1e293b; line-height: 1; }

    /* === Content Cards === */
    .content-card { background: #fff; border-radius: 10px; border: 1px solid rgba(34,67,151,0.18); box-shadow: 0 2px 8px rgba(34,67,151,0.06); overflow: hidden; }
    .card-hdr { background: rgba(34,67,151,0.04); border-bottom: 1px solid rgba(34,67,151,0.18); padding: 0.7rem 1.1rem; display: flex; align-items: center; justify-content: space-between; }
    .card-hdr-title { font-size: 0.88rem; font-weight: 700; color: #224397; margin: 0; display: flex; align-items: center; gap: 0.4rem; }

    /* === Tables === */
    .log-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .log-table thead th { background: rgba(34,67,151,0.08); color: #224397; font-weight: 800; text-transform: uppercase; font-size: 0.73rem; padding: 0.6rem 0.8rem; border: 1px solid rgba(34,67,151,0.2); white-space: nowrap; position: sticky; top: 0; z-index: 2; }
    .log-table td { padding: 0.55rem 0.8rem; border: 1px solid rgba(34,67,151,0.1); vertical-align: middle; font-weight: 500; color: #1e293b; }
    .log-table tbody tr:hover { background: rgba(34,67,151,0.03); }
    .table-scroll { max-height: 360px; overflow-y: auto; overflow-x: auto; }

    /* === Badges === */
    .badge-found { display: inline-flex; align-items: center; gap: 3px; padding: 0.18rem 0.55rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; background: #dcfce7; color: #15803d; white-space: nowrap; }
    .badge-notfound { display: inline-flex; align-items: center; gap: 3px; padding: 0.18rem 0.55rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; background: #fee2e2; color: #b91c1c; white-space: nowrap; }
    .badge-lop { display: inline-flex; align-items: center; padding: 0.15rem 0.5rem; border-radius: 5px; font-size: 0.7rem; font-weight: 700; background: #eff6ff; color: #1d4ed8; white-space: nowrap; }

    /* === Nút action (chuẩn UI_SYNC) === */
    .btn-action { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.22rem 0.65rem; border-radius: 6px; font-size: 11px; font-weight: 600; border: 1px solid rgba(34,67,151,0.25); background: #fff; color: #224397; transition: all 0.2s; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
    .btn-action:hover { background: #FAB723; color: #fff; border-color: #FAB723; text-decoration: none; }

    /* === Active dot === */
    .active-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #22c55e; flex-shrink: 0; animation: pulse-dot 1.5s ease-in-out infinite; }
    @keyframes pulse-dot { 0%,100% { opacity:1; transform: scale(1); } 50% { opacity:.5; transform: scale(1.3); } }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 pb-6">

    <!-- Header theo chuẩn UI_SYNC -->
    <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
        <h1 class="h4 mb-0 font-semibold text-[#224397] flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" class="bi bi-journals text-[#FAB723]" viewBox="0 0 16 16"><path d="M5 0h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2 2 2 0 0 1-2 2H3a2 2 0 0 1-2-2h1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1H1a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v9a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1H3a2 2 0 0 1 2-2"/><path d="M1 6v-.5a.5.5 0 0 1 1 0V6h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V9h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 2.5v.5H.5a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1H2v-.5a.5.5 0 0 0-1 0"/></svg>
            Nhật ký &amp; Thống Kê Hệ Thống
        </h1>
        <div class="flex items-center gap-2">
            <a href="/thidua/admin/nhat-ky/su-dung" class="btn-action">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16"><path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/></svg>
                Nhật ký sử dụng
            </a>
      
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="stat-card">
            <div class="stat-icon" style="background:#224397;">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-door-open-fill" viewBox="0 0 16 16"><path d="M1.5 15a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2.5A1.5 1.5 0 0 0 11.5 1H11V.5a.5.5 0 0 0-.57-.495l-7 1A.5.5 0 0 0 3 1.5V15zM11 2h.5a.5.5 0 0 1 .5.5V15h-1zm-2.5 8c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/></svg>
            </div>
            <div>
                <div class="stat-label">Tổng Lượt Truy Cập</div>
                <div class="stat-value"><?= number_format($total_visits) ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#16a34a;">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-reception-4" viewBox="0 0 16 16"><path d="M0 11.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5zm4-3a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5zm4-3a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5zm4-3a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v11a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5z"/></svg>
            </div>
            <div>
                <div class="stat-label">Đang Hoạt Động <small style="font-size:0.68rem;opacity:.7;">(5 phút)</small></div>
                <div class="stat-value"><?= count($active_sessions) ?></div>
            </div>
        </div>
        <a href="/thidua/admin/ho-tro-khan-cap" class="stat-card">
            <div class="stat-icon" style="background:#d97706;">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-headset" viewBox="0 0 16 16"><path d="M8 1a5 5 0 0 0-5 5v1h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a6 6 0 1 1 12 0v6a2.5 2.5 0 0 1-2.5 2.5H9.366a1 1 0 0 1-.866.5h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 .866.5H11.5A1.5 1.5 0 0 0 13 12h-1a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1V6a5 5 0 0 0-5-5"/></svg>
            </div>
            <div>
                <div class="stat-label">Yêu Cầu Hỗ Trợ</div>
                <div class="stat-value"><?= (int)$support_request_count ?></div>
            </div>
        </a>
    </div>

    <!-- Grid chính -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        <!-- LEFT: Phiên hoạt động -->
        <div class="lg:col-span-4">
            <div class="content-card h-full">
                <div class="card-hdr">
                    <h6 class="card-hdr-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-check-fill text-[#FAB723]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>
                        Các Phiên Đang Hoạt Động
                    </h6>
                    <?php if (!empty($active_sessions)): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">
                        <span class="active-dot" style="width:6px;height:6px;"></span><?= count($active_sessions) ?> online
                    </span>
                    <?php endif; ?>
                </div>
                <?php if (empty($active_sessions)): ?>
                    <div class="p-8 text-center text-slate-400 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" fill="currentColor" class="bi bi-person-slash mx-auto mb-2 opacity-25" viewBox="0 0 16 16"><path d="M13.879 10.414a2.501 2.501 0 0 0-3.465 3.465zm.707.707-3.464 3.464a2.501 2.501 0 0 0 3.464-3.464zm-4.56-1.096a3.5 3.5 0 1 1 4.949 4.95 3.5 3.5 0 0 1-4.95-4.95zM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4M5.018 11.478q.184-.857.53-1.617C3.21 10.306 2 11.382 2 13c0 .447.068.916.176 1.354A2 2 0 0 1 2 14c0-.002 0-.004 0-.006v.007a8.6 8.6 0 0 1-.006-.14c0-.45.065-.918.176-1.38A2 2 0 0 1 2 12c0 .447.068.916.176 1.354A2 2 0 0 1 2 14"/></svg>
                        <p class="mt-1">Không có ai đang hoạt động.</p>
                    </div>
                <?php else: ?>
                    <div class="table-scroll">
                        <table class="log-table">
                            <thead><tr><th>Người Dùng</th><th>IP</th></tr></thead>
                            <tbody>
                                <?php foreach($active_sessions as $s): ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-1.5">
                                            <span class="active-dot"></span>
                                            <div>
                                                <div class="font-semibold text-slate-800 text-[12px]"><?= htmlspecialchars($s['user_name']) ?></div>
                                                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($s['user_type']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-mono text-[11px] text-slate-500"><?= htmlspecialchars($s['ip_address']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: Lịch sử -->
        <div class="lg:col-span-8 flex flex-col gap-4">

            <!-- Lịch Sử Tra Cứu -->
            <div class="content-card">
                <div class="card-hdr">
                    <h6 class="card-hdr-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search text-[#FAB723]" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                        Lịch Sử Tra Cứu Gần Nhất
                    </h6>
                    <span class="text-[10px] text-slate-400 font-medium"><?= count($lookup_history) ?> bản ghi</span>
                </div>
                <div class="table-scroll">
                    <table class="log-table">
                        <thead><tr><th>Thời Gian</th><th>Mã Tra Cứu</th><th>Tên Đối Tượng</th><th>Kết Quả</th><th>Địa Chỉ IP</th></tr></thead>
                        <tbody>
                            <?php if (empty($lookup_history)): ?>
                                <tr><td colspan="5" class="text-center py-6 text-slate-400 text-sm">Chưa có lịch sử tra cứu.</td></tr>
                            <?php else: foreach($lookup_history as $log): ?>
                            <tr>
                                <td class="font-mono text-[11px] text-slate-500 whitespace-nowrap"><?= date('d/m H:i', strtotime($log['thoi_gian_tra_cuu'])) ?></td>
                                <td class="font-mono text-[11px]"><?= htmlspecialchars($log['ma_tra_cuu']) ?></td>
                                <td class="font-semibold text-[12px]"><?= htmlspecialchars($log['ten_doi_tuong'] ?? '') ?></td>
                                <td>
                                    <?php if($log['ket_qua_tim_thay']): ?>
                                        <span class="badge-found">✓ Tìm thấy</span>
                                    <?php else: ?>
                                        <span class="badge-notfound">✗ Không thấy</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-mono text-[11px] text-slate-500"><?= htmlspecialchars($log['dia_chi_ip']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lịch Sử Đăng Nhập Học Sinh -->
            <div class="content-card">
                <div class="card-hdr">
                    <h6 class="card-hdr-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-box-arrow-in-right text-[#FAB723]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/><path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/></svg>
                        Lịch Sử Đăng Nhập Của Học Sinh
                    </h6>
                    <span class="text-[10px] text-slate-400 font-medium"><?= count($login_history) ?> bản ghi</span>
                </div>
                <div class="table-scroll">
                    <table class="log-table">
                        <thead><tr><th>Thời Gian</th><th>Học Sinh</th><th>Lớp</th><th>Địa Chỉ IP</th></tr></thead>
                        <tbody>
                            <?php if (empty($login_history)): ?>
                                <tr><td colspan="4" class="text-center py-6 text-slate-400 text-sm">Chưa có lịch sử đăng nhập.</td></tr>
                            <?php else: foreach($login_history as $log): ?>
                            <tr>
                                <td class="font-mono text-[11px] text-slate-500 whitespace-nowrap"><?= date('d/m H:i', strtotime($log['thoi_gian_dang_nhap'])) ?></td>
                                <td class="font-semibold text-[12px]"><?= htmlspecialchars($log['ten_hoc_sinh']) ?></td>
                                <td><span class="badge-lop"><?= htmlspecialchars($log['ten_lop']) ?></span></td>
                                <td class="font-mono text-[11px] text-slate-500"><?= htmlspecialchars($log['dia_chi_ip']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>
