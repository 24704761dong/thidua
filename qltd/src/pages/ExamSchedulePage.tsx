import logoImg from '@/assets/logo.png';
import React, { useEffect, useState } from 'react';
import { Page, Spinner, useSnackbar } from 'zmp-ui';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import api from '@/lib/api';

interface ExamShift {
  id: number;
  ten_ca: string;
  ngay_thi: string;
  gio_thi: string;
  so_luot_thi: number;
  danh_sach_mon: string[];
}

interface ExamSchedule {
  ky_thi_id: number;
  ten_ky_thi: string;
  ngay_bat_dau: string;
  ngay_ket_thuc: string;
  trang_thai: string;
  so_bao_danh: string;
  ten_phong: string;
  dang_ky_mon_thi: string[];
  ghi_chu: string;
  ca_thi: ExamShift[];
}

const ExamSchedulePage: React.FC = () => {
  const { openSnackbar } = useSnackbar();
  const [exams, setExams] = useState<ExamSchedule[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchSchedule();
  }, []);

  const fetchSchedule = async () => {
    try {
      setIsLoading(true);
      const res = await api.get('/api/zalo/exam-schedule');
      if (res.data?.success) {
        setExams(res.data.data || []);
      } else {
        openSnackbar({ text: res.data?.message || 'Không thể tải lịch thi', type: 'error' });
      }
    } catch (err) {
      console.error('Lỗi tải lịch thi:', err);
      openSnackbar({ text: 'Lỗi kết nối máy chủ', type: 'error' });
    } finally {
      setIsLoading(false);
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'dang_dien_ra':
        return <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">Đang diễn ra</span>;
      case 'da_ket_thuc':
        return <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Đã kết thúc</span>;
      default:
        return <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-200">Sắp diễn ra</span>;
    }
  };

  return (
    <Page className="bg-[#f0f6fc] min-h-screen relative pb-16">
      <Header title="Lịch học / Lịch thi" variant="back" />

      <div className="p-4 flex flex-col gap-4">
        {isLoading ? (
          <div className="flex flex-col items-center justify-center pt-24 gap-3">
            <Spinner visible logo={logoImg} />
            <p className="text-xs text-slate-400 font-medium">Đang tải lịch thi...</p>
          </div>
        ) : exams.length === 0 ? (
          <div className="flex flex-col items-center justify-center pt-20 px-6 text-center">
            <div className="w-20 h-20 rounded-full bg-blue-100/70 text-[#1e3a8a] flex items-center justify-center mb-4 shadow-sm">
              <Icon icon="zi-calendar" className="!text-[36px]" />
            </div>
            <h3 className="text-base font-bold text-slate-800 mb-1">Chưa có lịch thi</h3>
            <p className="text-xs text-slate-500 max-w-[260px] leading-relaxed">
              Bạn chưa có kỳ thi hoặc lịch thi nào được xếp trong năm học này.
            </p>
          </div>
        ) : (
          exams.map((exam) => (
            <div 
              key={exam.ky_thi_id}
              className="bg-white rounded-2xl p-4.5 shadow-xs border border-slate-100 transition-all hover:shadow-md"
            >
              {/* Header Kỳ thi */}
              <div className="flex items-start justify-between gap-2 border-b border-slate-100 pb-3 mb-3">
                <div>
                  <h3 className="text-[15px] font-bold text-[#1e3a8a] leading-snug">
                    {exam.ten_ky_thi}
                  </h3>
                  <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                    <span>📅 {exam.ngay_bat_dau || '---'} {exam.ngay_ket_thuc ? `đến ${exam.ngay_ket_thuc}` : ''}</span>
                  </div>
                </div>
                {getStatusBadge(exam.trang_thai)}
              </div>

              {/* Thông tin phòng thi & SBD */}
              <div className="grid grid-cols-2 gap-2 bg-[#f8fafc] p-3 rounded-xl mb-3 border border-slate-100">
                <div className="flex flex-col">
                  <span className="text-[11px] text-slate-500 font-medium">Số báo danh (SBD)</span>
                  <span className="text-sm font-bold text-[#1e3a8a] mt-0.5">{exam.so_bao_danh}</span>
                </div>
                <div className="flex flex-col border-l border-slate-200 pl-3">
                  <span className="text-[11px] text-slate-500 font-medium">Phòng thi</span>
                  <span className="text-sm font-bold text-amber-700 mt-0.5">{exam.ten_phong}</span>
                </div>
              </div>

              {/* Danh sách môn đăng ký */}
              {exam.dang_ky_mon_thi && exam.dang_ky_mon_thi.length > 0 && (
                <div className="mb-3">
                  <span className="text-[11.5px] font-bold text-slate-700 block mb-1.5">
                    Môn thi đăng ký:
                  </span>
                  <div className="flex flex-wrap gap-1.5">
                    {exam.dang_ky_mon_thi.map((m, idx) => (
                      <span key={idx} className="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 text-[11px] font-semibold">
                        {m}
                      </span>
                    ))}
                  </div>
                </div>
              )}

              {/* Chi tiết ca thi */}
              {exam.ca_thi && exam.ca_thi.length > 0 && (
                <div className="mt-3 pt-3 border-t border-slate-100">
                  <span className="text-[11.5px] font-bold text-slate-700 block mb-2">
                    Lịch ca thi chi tiết:
                  </span>
                  <div className="flex flex-col gap-2">
                    {exam.ca_thi.map((ca) => (
                      <div key={ca.id} className="p-2.5 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                        <div>
                          <div className="font-bold text-slate-800">{ca.ten_ca}</div>
                          <div className="text-[11px] text-slate-500 mt-0.5">
                            ⏰ {ca.gio_thi || 'Giờ thi'} - 📅 {ca.ngay_thi || 'Ngày thi'}
                          </div>
                        </div>
                        {ca.danh_sach_mon && ca.danh_sach_mon.length > 0 && (
                          <span className="text-[11px] text-slate-600 bg-white px-2 py-1 rounded-md border border-slate-200">
                            {ca.danh_sach_mon.join(', ')}
                          </span>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}

            </div>
          ))
        )}
      </div>
    </Page>
  );
};

export default ExamSchedulePage;
