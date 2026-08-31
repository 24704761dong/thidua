<?php
$page_title = "Nhật ký Email Hệ thống";
require_once __DIR__ . '/partials/admin_header.php';
?>

<style>
    body { background-color: #f4f7f9; }
    #emailLogTable { border: 1px solid rgba(34,67,151,0.25); border-collapse: collapse; width: 100%; }
    #emailLogTable thead th { background-color: rgba(34,67,151,0.08); color: #224397; font-weight: 800; text-transform: uppercase; font-size: 0.82rem; text-align: center; padding: 0.65rem 0.85rem; border: 1px solid rgba(34,67,151,0.25); }
    #emailLogTable td { padding: 0.65rem 0.85rem; border: 1px solid rgba(34,67,151,0.25); vertical-align: middle; font-size: 0.83rem; font-weight: 600; color: #1e293b; }
    #emailLogTable tbody tr:hover { background-color: rgba(34,67,151,0.04) !important; }
    body::-webkit-scrollbar, html::-webkit-scrollbar { display: block !important; width: 8px; height: 8px; }
    body::-webkit-scrollbar-thumb, html::-webkit-scrollbar-thumb { background: rgba(34,67,151,0.3); border-radius: 4px; }
    body::-webkit-scrollbar-track, html::-webkit-scrollbar-track { background: transparent; }
    .pagination-btn { padding: 0.35rem 0.75rem; border: 1px solid rgba(34,67,151,0.25); border-radius: 4px; background: white; color: #224397; font-size: 0.82rem; font-weight: 600; text-decoration: none; transition: all 0.2s; display: inline-block; }
    .pagination-btn:hover { background: #FAB723; color: white; border-color: #FAB723; }
    .pagination-btn.active { background: #224397; color: white; border-color: #224397; }
    .pagination-btn.disabled { opacity: 0.45; pointer-events: none; }
</style>

<div class="w-full px-2 lg:px-6 pt-4">
    <div class="bg-white rounded shadow border border-[#224397]/25 mb-6 p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-[#224397]/20 bg-[#224397]/5 flex items-center">
            <h3 class="mb-0 text-[15px] font-bold text-[#224397] uppercase flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-envelope-paper mr-2" viewBox="0 0 16 16"><path d="M4 0a2 2 0 0 0-2 2v1.133l-.941.502A2 2 0 0 0 0 5.4V14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5.4a2 2 0 0 0-1.059-1.765L14 3.133V2a2 2 0 0 0-2-2zm10 4.267.47.25A1 1 0 0 1 15 5.4v.817l-1 .6zm-1 3.15-3.75 2.25L8 8.917l-1.25.75L3 7.417V2a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1zm-11-.6-1-.6V5.4a1 1 0 0 1 .53-.882L2 4.267zm13 .566v5.734l-4.778-2.867zm-.035 6.88A1 1 0 0 1 14 15H2a1 1 0 0 1-.965-.738L8 10.083zM1 13.116V7.383l4.778 2.867L1 13.117Z"/></svg>
                Nhật ký Email hệ thống
            </h3>
        </div>
        <div class="px-4 pb-4 pt-3">
            <div class="overflow-x-auto w-full">
                <table id="emailLogTable">
                    <thead>
                        <tr>
                            <th style="width:50px">ID</th>
                            <th>Người nhận</th>
                            <th>Tiêu đề</th>
                            <th>Trạng thái</th>
                            <th>Thời gian tạo</th>
                            <th>Thời gian gửi</th>
                            <th>Lỗi (nếu có)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                        <tr><td colspan="7" class="text-center py-8 text-slate-500 font-medium">Không có dữ liệu Nhật kỳ email.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-center"><?= $log['id'] ?></td>
                                <td><strong><?= htmlspecialchars($log['recipient_name'] ?? 'Không rõ') ?></strong><br><small class="text-slate-500"><?= htmlspecialchars($log['recipient_email'] ?? '') ?></small></td>
                                <td><?= htmlspecialchars($log['subject'] ?? '') ?></td>
                                <td class="text-center">
                                    <?php if ($log['status'] === 'sent'): ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Thành công</span>
                                    <?php elseif ($log['status'] === 'failed'): ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Thất bại</span>
                                    <?php elseif ($log['status'] === 'pending'): ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Đang chờ</span>
                                    <?php else: ?><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?= htmlspecialchars($log['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                <td class="text-center"><?= $log['sent_at'] ? date('d/m/Y H:i', strtotime($log['sent_at'])) : '&mdash;' ?></td>
                                <td class="text-red-600 text-xs"><?= htmlspecialchars($log['error_message'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <p class="text-sm text-slate-500">Trang <span class="font-bold text-[#224397]"><?= $page ?></span> / <?= $total_pages ?></p>
        <div class="flex items-center gap-1">
            <a href="?page=<?= max(1,$page-1) ?>" class="pagination-btn <?= ($page<=1)?'disabled':'' ?>">Trước</a>
            <?php for($p=max(1,$page-2);$p<=min($total_pages,$page+2);$p++): ?><a href="?page=<?= $p ?>" class="pagination-btn <?= $p==$page?'active':'' ?>"><?= $p ?></a><?php endfor; ?>
            <a href="?page=<?= min($total_pages,$page+1) ?>" class="pagination-btn <?= ($page>=$total_pages)?'disabled':'' ?>">Sau</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>