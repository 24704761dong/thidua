import logoImg from '@/assets/logo.png';
import React, { useEffect, useState } from 'react';
import { Page, Spinner, useSnackbar, Modal, Button } from 'zmp-ui';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import api from '@/lib/api';

interface AppealSubject {
  id: number;
  mon_hoc_db_col: string;
  diem_goc: number;
  diem_tong_cu: number;
  diem_tong_moi: number | null;
  minh_chung_path: string | null;
}

interface AppealItem {
  appeal_id: number;
  ky_thi_id: number;
  ten_ky_thi: string;
  so_bao_danh: string;
  thoi_gian_nop: string;
  trang_thai: string;
  subjects: AppealSubject[];
}

interface AvailableExam {
  ky_thi_id: number;
  ten_ky_thi: string;
  ky_thi_hoc_sinh_id: number;
  so_bao_danh: string;
  [key: string]: any;
}

const SUBJECT_NAMES: { [key: string]: string } = {
  'diem_toan': 'Toán',
  'diem_van': 'Ngữ Văn',
  'diem_ly': 'Vật Lý',
  'diem_hoa': 'Hóa Học',
  'diem_sinh': 'Sinh Học',
  'diem_su': 'Lịch Sử',
  'diem_dia': 'Địa Lý',
  'diem_gdktpl': 'GDKT-PL',
  'diem_ngoai_ngu': 'Ngoại Ngữ',
  'diem_cn_nn': 'CN-NN'
};

const ExamAppealPage: React.FC = () => {
  const { openSnackbar } = useSnackbar();
  const [appeals, setAppeals] = useState<AppealItem[]>([]);
  const [availableExams, setAvailableExams] = useState<AvailableExam[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modal nộp đơn
  const [showModal, setShowModal] = useState(false);
  const [selectedExam, setSelectedExam] = useState<AvailableExam | null>(null);
  const [selectedSubjects, setSelectedSubjects] = useState<{ [key: string]: boolean }>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    fetchAppeals();
  }, []);

  const fetchAppeals = async () => {
    try {
      setIsLoading(true);
      const res = await api.get('/api/zalo/exam-appeal');
      if (res.data?.success) {
        setAppeals(res.data.appeals || []);
        setAvailableExams(res.data.available_exams || []);
      } else {
        openSnackbar({ text: res.data?.message || 'Không thể tải đơn phúc khảo', type: 'error' });
      }
    } catch (err) {
      console.error('Lỗi tải phúc khảo:', err);
      openSnackbar({ text: 'Lỗi kết nối máy chủ', type: 'error' });
    } finally {
      setIsLoading(false);
    }
  };

  const handleOpenAppealModal = (exam: AvailableExam) => {
    setSelectedExam(exam);
    setSelectedSubjects({});
    setShowModal(true);
  };

  const handleToggleSubject = (key: string) => {
    setSelectedSubjects(prev => ({
      ...prev,
      [key]: !prev[key]
    }));
  };

  const handleSubmitAppeal = async () => {
    if (!selectedExam) return;

    const subjectsToSubmit = Object.keys(selectedSubjects)
      .filter(k => selectedSubjects[k])
      .map(k => ({
        mon_col: k,
        diem_goc: selectedExam[k] || 0
      }));

    if (subjectsToSubmit.length === 0) {
      openSnackbar({ text: 'Vui lòng tích chọn ít nhất 1 môn cần phúc khảo', type: 'warning' });
      return;
    }

    try {
      setIsSubmitting(true);
      const res = await api.post('/api/zalo/exam-appeal', {
        ky_thi_hoc_sinh_id: selectedExam.ky_thi_hoc_sinh_id,
        subjects: subjectsToSubmit
      });

      if (res.data?.success) {
        openSnackbar({ text: 'Gửi đơn phúc khảo thành công!', type: 'success' });
        setShowModal(false);
        fetchAppeals();
      } else {
        openSnackbar({ text: res.data?.message || 'Gửi đơn thất bại', type: 'error' });
      }
    } catch (err) {
      console.error('Lỗi gửi phúc khảo:', err);
      openSnackbar({ text: 'Lỗi kết nối máy chủ', type: 'error' });
    } finally {
      setIsSubmitting(false);
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'cho_xu_ly':
        return <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-600 border border-amber-200">Đang chờ xử lý</span>;
      case 'da_xu_ly':
        return <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">Đã cập nhật điểm</span>;
      case 'tu_choi':
        return <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200">Từ chối</span>;
      default:
        return <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">{status}</span>;
    }
  };

  return (
    <Page className="bg-[#f0f6fc] min-h-screen relative pb-16">
      <Header title="Đơn phúc khảo bài thi" variant="back" />

      <div className="p-4 flex flex-col gap-4">
        {isLoading ? (
          <div className="flex flex-col items-center justify-center pt-24 gap-3">
            <Spinner visible logo={logoImg} />
            <p className="text-xs text-slate-400 font-medium">Đang tải danh sách phúc khảo...</p>
          </div>
        ) : (
          <>
            {/* Danh sách kỳ thi có thể nộp đơn */}
            {availableExams.length > 0 && (
              <div>
                <h3 className="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2 px-1">
                  Kỳ thi có thể nộp phúc khảo
                </h3>
                <div className="flex flex-col gap-2.5">
                  {availableExams.map((exam) => (
                    <div 
                      key={exam.ky_thi_id}
                      className="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center justify-between"
                    >
                      <div>
                        <h4 className="text-sm font-bold text-[#1e3a8a] m-0">{exam.ten_ky_thi}</h4>
                        <div className="text-[11px] text-slate-400 mt-0.5">
                          SBD: <strong className="text-slate-700">{exam.so_bao_danh}</strong>
                        </div>
                      </div>
                      <button
                        onClick={() => handleOpenAppealModal(exam)}
                        className="px-3 py-1.5 rounded-xl bg-[#1e3a8a] text-white text-xs font-bold shadow-xs hover:bg-[#162d6b] transition active:scale-95 cursor-pointer"
                      >
                        Nộp đơn
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Lịch sử đơn đã nộp */}
            <div>
              <h3 className="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2 px-1">
                Lịch sử đơn phúc khảo ({appeals.length})
              </h3>
              {appeals.length === 0 ? (
                <div className="bg-white rounded-2xl p-8 text-center border border-slate-100 shadow-xs">
                  <div className="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <Icon icon="zi-edit-text" className="!text-[28px]" />
                  </div>
                  <h4 className="text-sm font-bold text-slate-700 m-0">Chưa có đơn phúc khảo</h4>
                  <p className="text-xs text-slate-400 mt-1">Các đơn phúc khảo bạn đã nộp sẽ được theo dõi tại đây.</p>
                </div>
              ) : (
                <div className="flex flex-col gap-3">
                  {appeals.map((app) => (
                    <div 
                      key={app.appeal_id}
                      className="bg-white rounded-2xl p-4 shadow-xs border border-slate-100"
                    >
                      <div className="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5 mb-2.5">
                        <div>
                          <h4 className="text-sm font-bold text-[#1e3a8a] m-0">{app.ten_ky_thi}</h4>
                          <div className="text-[11px] text-slate-400 mt-0.5">
                            Gửi lúc: {app.thoi_gian_nop}
                          </div>
                        </div>
                        {getStatusBadge(app.trang_thai)}
                      </div>

                      <div className="flex flex-col gap-1.5">
                        {app.subjects.map((sub, idx) => (
                          <div key={idx} className="p-2 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                            <span className="font-semibold text-slate-700">
                              {SUBJECT_NAMES[sub.mon_hoc_db_col] || sub.mon_hoc_db_col}
                            </span>
                            <div className="flex items-center gap-2">
                              <span className="text-slate-500">Gốc: <strong>{sub.diem_goc}</strong></span>
                              {sub.diem_tong_moi !== null && (
                                <span className="text-emerald-700 font-bold">➔ Mới: {sub.diem_tong_moi}</span>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </>
        )}
      </div>

      {/* Modal Nộp đơn phúc khảo */}
      <Modal
        visible={showModal}
        title={`Phúc khảo: ${selectedExam?.ten_ky_thi || ''}`}
        onClose={() => setShowModal(false)}
      >
        <div className="p-2 flex flex-col gap-3">
          <p className="text-xs text-slate-500 m-0">
            Chọn các môn bạn có nguyện vọng phúc khảo điểm:
          </p>

          <div className="flex flex-col gap-2 max-h-60 overflow-y-auto">
            {selectedExam && Object.keys(SUBJECT_NAMES).map((colKey) => {
              const score = selectedExam[colKey];
              if (score === null || score === undefined || score === '') return null;

              return (
                <label 
                  key={colKey}
                  className={`flex items-center justify-between p-2.5 rounded-xl border cursor-pointer transition ${
                    selectedSubjects[colKey] 
                      ? 'bg-blue-50/80 border-[#1e3a8a] text-[#1e3a8a]' 
                      : 'bg-white border-slate-200 text-slate-700'
                  }`}
                >
                  <div className="flex items-center gap-2.5">
                    <input 
                      type="checkbox"
                      checked={!!selectedSubjects[colKey]}
                      onChange={() => handleToggleSubject(colKey)}
                      className="rounded border-slate-300 text-[#1e3a8a] focus:ring-0"
                    />
                    <span className="text-xs font-bold">{SUBJECT_NAMES[colKey]}</span>
                  </div>
                  <span className="text-xs font-bold text-slate-500">Điểm: {score}</span>
                </label>
              );
            })}
          </div>

          <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <Button
              variant="secondary"
              size="small"
              onClick={() => setShowModal(false)}
            >
              Hủy
            </Button>
            <Button
              variant="primary"
              size="small"
              loading={isSubmitting}
              onClick={handleSubmitAppeal}
              className="bg-[#1e3a8a]"
            >
              Xác nhận gửi đơn
            </Button>
          </div>
        </div>
      </Modal>

    </Page>
  );
};

export default ExamAppealPage;
