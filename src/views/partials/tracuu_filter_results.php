<?php
/** Partial: phần kết quả thay đổi khi lọc tuần (AJAX hoặc render ban đầu). */
?>
            <?php if (!empty($teacher_info) && !empty($results) && !empty($violation_summary)) : ?>
                <div class="rounded-lg border border-slate-200 bg-white p-6 sm:p-5">
                    <div class="thidua-result-title">Thống kê số lượng học sinh vi phạm</div>
                    <?php
                    $total_violations = 0;
                    foreach ($violation_summary as $summary_item) :
                        $total_violations += (int) $summary_item['so_luong'];
                    ?>
                        <div class="thidua-summary-row">
                            <span><?= htmlspecialchars($summary_item['nhom_vi_pham']) ?></span>
                            <span class="thidua-badge thidua-badge-danger"><?= htmlspecialchars($summary_item['so_luong']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="thidua-summary-row border-b-0 font-extrabold">
                        <span>Tổng cộng</span>
                        <span class="thidua-badge thidua-badge-dark"><?= $total_violations ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($commendations)) : ?>
                <div class="rounded-lg border border-slate-200 bg-white p-6 sm:p-5">
                        <div class="thidua-result-title">Thành tích khen thưởng</div>
                        <div class="thidua-w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-wrap">
                            <table class="thidua-w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light">
                                <thead class="text-center">
                                    <tr>
                                        <th>STT</th>
                                        <th>Họ và tên</th>
                                        <th>Ngày KT</th>
                                        <th>Tên Khen Thưởng</th>
                                        <th>Cấp KT</th>
                                        <th>Số QĐ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commendations as $index => $kt) : ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td>
                                                <?php if (isset($kt['loai_hien_thi']) && $kt['loai_hien_thi'] === 'tap_the') {
                                                    $ten_lop_kt = !empty($teacher_info) ? $teacher_info['ten_lop'] : ($student_info['ten_lop'] ?? '');
                                                    echo '<strong>Tập thể ' . htmlspecialchars($ten_lop_kt) . '</strong>';
                                                } else {
                                                    $ten_hs_kt = $kt['ho_ten_hs'] ?? ($student_info['ho_dem'] . ' ' . $student_info['ten']);
                                                    echo htmlspecialchars($ten_hs_kt);
                                                } ?>
                                            </td>
                                            <td class="text-center"><?= !empty($kt['ngay_khen_thuong']) ? date('d/m/Y', strtotime($kt['ngay_khen_thuong'])) : '' ?></td>
                                            <td><?= htmlspecialchars($kt['ten_khen_thuong'] ?? '') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($kt['cap_khen_thuong'] ?? '') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($kt['so_quyet_dinh'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($results)) : ?>
                <div class="rounded-lg border border-slate-200 bg-white p-6 sm:p-5">
                        <div class="thidua-result-title">Danh sách vi phạm <span class="text-xs font-semibold uppercase text-slate-500">(<?= htmlspecialchars($current_scope_label) ?>)</span></div>
                        <div class="thidua-w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light-wrap">
                            <table class="thidua-w-full text-left text-sm text-slate-600 border-collapse border border-slate-200 [&_th]:bg-light [&_th]:text-slate-700 [&_th]:fw-semibold [&_th]:p-4 [&_th]:border-b [&_td]:p-4 [&_td]:border-b [&_tr:hover]:bg-light">
                                <thead class="text-center">
                                    <tr>
                                        <th class="col-stt">STT</th>
                                        <th>Tuần</th>
                                        <th>Họ và Tên</th>
                                        <th class="col-lop">Lớp</th>
                                        <th class="col-ngay">Ngày VP</th>
                                        <th>Tên Nhóm Vi Phạm</th>
                                        <th>Ghi Chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1;
                                    $violations_by_week = [];
                                    foreach ($results as $vp) {
                                        $violations_by_week[$vp['ten_tuan']][] = $vp;
                                    }
                                    foreach ($violations_by_week as $ten_tuan => $violations) :
                                        foreach ($violations as $i => $vp) : ?>
                                            <tr>
                                                <td class="text-center col-stt"><?= $stt++ ?></td>
                                                <?php if ($i === 0) : ?>
                                                    <td class="text-center align-middle" rowspan="<?= count($violations) ?>"><?= htmlspecialchars(tuan_label_ngan($ten_tuan)) ?></td>
                                                <?php endif; ?>
                                                <td><?= htmlspecialchars($vp['ho_ten']) ?></td>
                                                <td class="text-center col-lop"><?= htmlspecialchars($vp['ten_lop']) ?></td>
                                                <td class="text-center col-ngay"><?= date('d/m/Y', strtotime($vp['ngay_vi_pham'])) ?></td>
                                                <td><?= htmlspecialchars($vp['ten_vi_pham']) ?></td>
                                                <td><?= htmlspecialchars($vp['ghi_chu']) ?></td>
                                            </tr>
                                    <?php endforeach;
                                    endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                </div>
            <?php else : ?>
                <div class="rounded-lg border border-green-200 bg-green-50 px-6 py-6 text-center text-sm text-green-800"><?php if (!empty($student_info)) : ?>Học sinh <strong><?= htmlspecialchars($student_info['ho_dem'] . ' ' . $student_info['ten']) ?></strong> không có lỗi vi phạm!<?php elseif (!empty($teacher_info)) : ?>Lớp <strong><?= htmlspecialchars($teacher_info['ten_lop']) ?></strong> không có học sinh nào vi phạm!<?php else : ?>Không có vi phạm.<?php endif; ?></div>
            <?php endif; ?>
