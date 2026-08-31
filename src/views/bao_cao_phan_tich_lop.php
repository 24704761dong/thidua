<?php
$page_title = 'Phân Tích & So Sánh Lớp';
require_once __DIR__ . '/partials/admin_header.php';
?>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 pb-6 mt-4">
    <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
        <h1 class="text-xl mb-0 font-semibold text-[#224397] flex items-center gap-2 uppercase">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" fill="currentColor" class="bi bi-graph-up-arrow text-[#FAB723]" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5"/></svg>
            Phân Tích & So Sánh: Lớp <?php echo htmlspecialchars($lop_hoc['ten_lop']); ?>
        </h1>
        <div class="flex items-center gap-2">
            <a href="/thidua/bao-cao/thi-dua" class="btn-action">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/></svg>
                Quay Lại Báo Cáo
            </a>
        </div>
    </div>
    
    <div class="flex flex-wrap -mx-3">
        <div class="w-full lg:w-2/3 px-3 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-[#224397]/20 overflow-hidden h-full">
                <div class="px-5 py-3 border-b border-[#224397]/12 bg-[#f8fafc] font-bold text-[#224397] text-sm uppercase">
                    Biểu đồ Biến động Điểm số (so với Trung bình khối)
                </div>
                <div class="p-6 h-[400px]">
                    <canvas id="scoreHistoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="w-full lg:w-1/3 px-3 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-[#224397]/20 overflow-hidden h-full">
                <div class="px-5 py-3 border-b border-[#224397]/12 bg-[#f8fafc] font-bold text-[#224397] text-sm uppercase">
                    Cơ Cấu Điểm Tuần Gần Nhất
                </div>
                <div class="p-6 flex items-center justify-center h-[400px]">
                    <?php if($tuan_hien_tai_data): ?>
                        <canvas id="scoreStructureChart"></canvas>
                    <?php else: ?>
                        <div class="p-6 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200 text-sm w-full text-center">Chưa có dữ liệu điểm cho tuần gần nhất.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-[#224397]/20 overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-[#224397]/12 bg-[#f8fafc] font-bold text-[#224397] text-sm uppercase">
            Lịch sử Thi đua qua các tuần
        </div>
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-[13px] text-slate-600 border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-5 font-bold text-[#224397] uppercase text-xs">Tuần</th>
                        <th class="py-3 px-5 font-bold text-[#224397] uppercase text-xs text-center">Điểm Số</th>
                        <th class="py-3 px-5 font-bold text-[#224397] uppercase text-xs text-center">Điểm TB Khối</th>
                        <th class="py-3 px-5 font-bold text-[#224397] uppercase text-xs text-center">So sánh</th>
                        <th class="py-3 px-5 font-bold text-[#224397] uppercase text-xs text-center">Xếp Hạng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($history_data)): ?>
                        <tr><td colspan="5" class="py-6 px-5 text-center text-slate-500">Chưa có dữ liệu lịch sử thi đua.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_reverse($history_data) as $row): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-5 font-semibold text-slate-700"><?php echo htmlspecialchars($row['ten_tuan']); ?></td>
                                <td class="py-3 px-5 text-center font-bold text-[#16a34a] text-sm"><?php echo round($row['tong_diem'], 1); ?></td>
                                <td class="py-3 px-5 text-center font-medium text-slate-600"><?php echo round($row['diem_trung_binh_khoi'], 1); ?></td>
                                <td class="py-3 px-5 text-center font-medium">
                                    <?php 
                                        $diff = $row['tong_diem'] - $row['diem_trung_binh_khoi'];
                                        if ($diff > 0) echo "<span class='text-green-600 flex items-center justify-center gap-1'><svg xmlns='http://www.w3.org/2000/svg' width='1em' height='1em' fill='currentColor' class='bi bi-arrow-up-short' viewBox='0 0 16 16'><path fill-rule='evenodd' d='M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5z'/></svg> " . round($diff, 2) . "</span>";
                                        else if ($diff < 0) echo "<span class='text-red-600 flex items-center justify-center gap-1'><svg xmlns='http://www.w3.org/2000/svg' width='1em' height='1em' fill='currentColor' class='bi bi-arrow-down-short' viewBox='0 0 16 16'><path fill-rule='evenodd' d='M8 4a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L7.5 10.293V4.5A.5.5 0 0 1 8 4z'/></svg> " . round(abs($diff), 2) . "</span>";
                                        else echo "<span class='text-slate-400'>-</span>";
                                    ?>
                                </td>
                                <td class="py-3 px-5 text-center font-bold">
                                    <?php if ($row['xep_hang'] === null): ?>
                                        <span class="text-red-600">KXTD</span>
                                    <?php else: ?>
                                        <span class="text-slate-700">Hạng <?php echo $row['xep_hang']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.btn-action { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; background-color: #fff; color: #224397; border: 1px solid rgba(34,67,151,0.25); cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.btn-action:hover { background-color: #f8fafc; border-color: #224397; }
</style>

<?php require_once __DIR__ . '/partials/admin_footer.php'; ?>

<script src="/thidua/public/assets/libs/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?php echo json_encode($chart_data); ?>;
    const currentWeekData = <?php echo json_encode($tuan_hien_tai_data); ?>;

    // Biểu đồ đường
    const scoreCtx = document.getElementById('scoreHistoryChart').getContext('2d');
    new Chart(scoreCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Điểm Của Lớp',
                data: chartData.scoreData,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22, 163, 74, 0.1)',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                pointBackgroundColor: '#16a34a',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }, {
                label: 'Điểm Trung Bình Khối',
                data: chartData.averageScoreData,
                borderColor: '#64748b',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                pointBackgroundColor: '#64748b',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, boxWidth: 8, font: { size: 12, family: "'Inter', sans-serif" } }
                },
                tooltip: {
                    callbacks: {
                        footer: function(tooltipItems) {
                            const index = tooltipItems[0].dataIndex;
                            const rank = chartData.rankData[index];
                            if (rank !== null) {
                                return 'Xếp hạng: ' + rank;
                            }
                            return 'Không xét thi đua';
                        }
                    }
                }
            },
            scales: {
                y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: "'Inter', sans-serif" } } },
                x: { grid: { display: false }, ticks: { font: { family: "'Inter', sans-serif" } } }
            }
        }
    });

    // Biểu đồ tròn
    if(currentWeekData){
        const structureCtx = document.getElementById('scoreStructureChart').getContext('2d');
        new Chart(structureCtx, {
            type: 'doughnut',
            data: {
                labels: ['Điểm cộng', 'Điểm trừ'],
                datasets: [{
                    data: [
                        (currentWeekData.tong_diem || 0) + Math.abs(currentWeekData.diem_noi_quy || 0) + Math.abs(currentWeekData.tru_vang || 0) + Math.abs(Math.min(0, currentWeekData.diem_cong_tru || 0)),
                        Math.abs(currentWeekData.diem_noi_quy || 0) + Math.abs(currentWeekData.tru_vang || 0) + Math.abs(Math.min(0, currentWeekData.diem_cong_tru || 0))
                    ],
                    backgroundColor: [
                        '#3b82f6',
                        '#ef4444',
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20, font: { size: 12, family: "'Inter', sans-serif" } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
