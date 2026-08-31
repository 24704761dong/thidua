import logoImg from '@/assets/logo.png';
import React, { useEffect, useState } from 'react';
import { Page, Spinner, useSnackbar } from 'zmp-ui';
import { useNavigate } from 'react-router-dom';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import { PATHS } from '@/constants/paths';
import { navigateForward } from '@/utils/navigation';
import api from '@/lib/api';

interface ScoreItem {
  key: string;
  name: string;
  score: number | string;
  is_reviewed: boolean;
}

interface ExamScoreItem {
  ky_thi_id: number;
  ky_thi_hoc_sinh_id: number;
  ten_ky_thi: string;
  ngay_bat_dau: string;
  ngay_ket_thuc: string;
  so_bao_danh: string;
  ten_phong: string;
  has_score: boolean;
  scores: ScoreItem[];
  appeal_status: string | null;
  appeal_time: string | null;
  can_appeal: boolean;
}

const ExamScoresPage: React.FC = () => {
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();
  const [exams, setExams] = useState<ExamScoreItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchScores();
  }, []);

  const fetchScores = async () => {
    try {
      setIsLoading(true);
      const res = await api.get('/api/zalo/exam-scores');
      if (res.data?.success) {
        setExams(res.data.data || []);
      } else {
        openSnackbar({ text: res.data?.message || 'Không thể tải điểm thi', type: 'error' });
      }
    } catch (err) {
      console.error('Lỗi tải điểm thi:', err);
      openSnackbar({ text: 'Lỗi kết nối máy chủ', type: 'error' });
    } finally {
      setIsLoading(false);
    }
  };

  const getAppealStatusBadge = (status: string | null) => {
    if (!status) return null;
    switch (status) {
      case 'cho_xu_ly':
        return <span className="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-600 border border-amber-200">Đang phúc khảo</span>;
      case 'da_xu_ly':
        return <span className="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">Đã cập nhật sau PK</span>;
      case 'tu_choi':
        return <span className="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-50 text-rose-600 border border-rose-200">Từ chối PK</span>;
      default:
        return null;
    }
  };

  return (
    <Page className="bg-[#f0f6fc] min-h-screen relative pb-16">
      <Header title="Kết quả điểm thi" variant="back" />

      <div className="p-4 flex flex-col gap-4">
        {isLoading ? (
          <div className="flex flex-col items-center justify-center pt-24 gap-3">
            <Spinner visible logo={logoImg} />
            <p className="text-xs text-slate-400 font-medium">Đang tải điểm thi...</p>
          </div>
        ) : exams.length === 0 ? (
          <div className="flex flex-col items-center justify-center pt-20 px-6 text-center">
            <div className="w-20 h-20 rounded-full bg-amber-100/70 text-amber-600 flex items-center justify-center mb-4 shadow-sm">
              <Icon icon="zi-star" className="!text-[36px]" />
            </div>
            <h3 className="text-base font-bold text-slate-800 mb-1">Chưa có điểm thi</h3>
            <p className="text-xs text-slate-500 max-w-[260px] leading-relaxed">
              Điểm thi của bạn trong năm học này chưa được công bố hoặc chưa có dữ liệu.
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
                  <div className="text-[11px] text-slate-400 mt-1">
                    SBD: <strong className="text-slate-700">{exam.so_bao_danh}</strong> - Phòng: <strong className="text-slate-700">{exam.ten_phong}</strong>
                  </div>
                </div>
                {getAppealStatusBadge(exam.appeal_status)}
              </div>

              {/* Bảng điểm */}
              {!exam.has_score ? (
                <div className="p-4 bg-slate-50 rounded-xl text-center text-xs text-slate-400 font-medium">
                  Chưa có kết quả điểm của kỳ thi này
                </div>
              ) : (
                <div className="grid grid-cols-3 sm:grid-cols-4 gap-2">
                  {exam.scores.map((sc, idx) => (
                    <div 
                      key={idx} 
                      className={`p-2.5 rounded-xl border flex flex-col items-center justify-center text-center relative ${
                        sc.is_reviewed 
                          ? 'bg-amber-50/60 border-amber-200/80' 
                          : 'bg-[#f8fafc] border-slate-100'
                      }`}
                    >
                      {sc.is_reviewed && (
                        <span className="absolute top-1 right-1 w-2 h-2 rounded-full bg-amber-500" title="Điểm đã sửa"></span>
                      )}
                      <span className="text-[11px] font-medium text-slate-500">{sc.name}</span>
                      <span className={`text-[15px] font-bold mt-1 ${
                        typeof sc.score === 'number' && sc.score < 5 
                          ? 'text-rose-600' 
                          : typeof sc.score === 'number' && sc.score >= 8 
                          ? 'text-emerald-700' 
                          : 'text-[#1e3a8a]'
                      }`}>
                        {sc.score}
                      </span>
                    </div>
                  ))}
                </div>
              )}

              {/* Nút nộp đơn phúc khảo nếu có */}
              {exam.can_appeal && !exam.appeal_status && (
                <div className="mt-3.5 pt-3 border-t border-slate-100 flex justify-end">
                  <button 
                    onClick={() => navigateForward(navigate, PATHS.EXAM_APPEAL)}
                    className="px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                  >
                    <span>📝 Nộp đơn phúc khảo</span>
                  </button>
                </div>
              )}

            </div>
          ))
        )}
      </div>
    </Page>
  );
};

export default ExamScoresPage;
