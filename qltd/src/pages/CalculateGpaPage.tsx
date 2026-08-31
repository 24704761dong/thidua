import logoImg from '@/assets/logo.png';
import React, { useState, useEffect, useMemo } from 'react';
import { Page, Text, Modal, useSnackbar, Spinner } from 'zmp-ui';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import { useProfile } from '@/features/profile/profile.query';
import api from '@/lib/api';

export interface ScoreItem {
  tx: string[];
  gk: string;
  ck: string;
  passed: boolean;
}

export interface SubjectData {
  id: string;
  name: string;
  code: string;
  isElective: boolean;
  isGraded: boolean;
  active: boolean;
  icon: string;
  color: string;
  hk1: ScoreItem;
  hk2: ScoreItem;
}

const DEFAULT_SUBJECTS: SubjectData[] = [
  { id: '1', name: 'Toán', code: 'TO', isElective: false, isGraded: true, active: false, icon: 'zi-star', color: '#2563eb', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '2', name: 'Ngữ văn', code: 'VA', isElective: false, isGraded: true, active: false, icon: 'zi-note', color: '#e11d48', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '3', name: 'Tiếng Anh', code: 'N1', isElective: false, isGraded: true, active: false, icon: 'zi-chat', color: '#4f46e5', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '4', name: 'Lịch sử', code: 'SU', isElective: false, isGraded: true, active: false, icon: 'zi-clock-1', color: '#d97706', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '5', name: 'GDQP & AN', code: 'QP', isElective: false, isGraded: true, active: false, icon: 'zi-check-circle', color: '#059669', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '6', name: 'Giáo dục thể chất', code: 'TD', isElective: false, isGraded: false, active: false, icon: 'zi-star', color: '#0d9488', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '7', name: 'HĐTN, hướng nghiệp', code: 'HN', isElective: false, isGraded: false, active: false, icon: 'zi-group', color: '#0891b2', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '8', name: 'Giáo dục địa phương', code: 'DP', isElective: false, isGraded: false, active: false, icon: 'zi-more-grid', color: '#9333ea', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '9', name: 'Vật lý', code: 'LY', isElective: true, isGraded: true, active: false, icon: 'zi-star', color: '#0284c7', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '10', name: 'Hóa học', code: 'HH', isElective: true, isGraded: true, active: false, icon: 'zi-star', color: '#ea580c', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '11', name: 'Sinh học', code: 'SH', isElective: true, isGraded: true, active: false, icon: 'zi-star', color: '#16a34a', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '12', name: 'Tin học', code: 'TH', isElective: true, isGraded: true, active: false, icon: 'zi-more-grid', color: '#1d4ed8', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '13', name: 'Công nghệ', code: 'CN', isElective: true, isGraded: true, active: false, icon: 'zi-setting', color: '#7c3aed', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '14', name: 'Địa lý', code: 'DI', isElective: true, isGraded: true, active: false, icon: 'zi-star', color: '#ca8a04', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
  { id: '15', name: 'GDKT & PL', code: 'KTPL', isElective: true, isGraded: true, active: false, icon: 'zi-star', color: '#db2777', hk1: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true }, hk2: { tx: ['', '', '', '', ''], gk: '', ck: '', passed: true } },
];

const CalculateGpaPage: React.FC = () => {
  const { data: profile } = useProfile();
  const { openSnackbar } = useSnackbar();
  const [subjects, setSubjects] = useState<SubjectData[]>(DEFAULT_SUBJECTS);
  const [loading, setLoading] = useState<boolean>(true);
  const [selectedTab, setSelectedTab] = useState<'hk1' | 'hk2' | 'cn'>('hk1');
  const [comboModalVisible, setComboModalVisible] = useState<boolean>(false);
  const [imgError, setImgError] = useState<boolean>(false);

  useEffect(() => {
    const loadData = async () => {
      try {
        const namHocId = localStorage.getItem('selected_nam_hoc_id') || '1';
        const cacheKey = `zalo_student_gpa_data_v2_${namHocId}`;
        const localData = localStorage.getItem(cacheKey);
        if (localData) {
          const parsed = JSON.parse(localData).map((sub: any) => ({
            ...sub,
            hk1: { ...sub.hk1, tx: Array.from({ length: 5 }, (_, i) => sub.hk1?.tx?.[i] || '') },
            hk2: { ...sub.hk2, tx: Array.from({ length: 5 }, (_, i) => sub.hk2?.tx?.[i] || '') },
          }));
          setSubjects(parsed);
        } else {
          setSubjects(DEFAULT_SUBJECTS);
        }
        
        const res = await api.get('/api/zalo/get-gpa-data');
        if (res.data?.success) {
          if (res.data.data) {
            const fetched = res.data.data.map((sub: any) => ({
              ...sub,
              hk1: { ...sub.hk1, tx: Array.from({ length: 5 }, (_, i) => sub.hk1?.tx?.[i] || '') },
              hk2: { ...sub.hk2, tx: Array.from({ length: 5 }, (_, i) => sub.hk2?.tx?.[i] || '') },
            }));
            setSubjects(fetched);
            localStorage.setItem(cacheKey, JSON.stringify(fetched));
          } else {
            setSubjects(DEFAULT_SUBJECTS);
            localStorage.setItem(cacheKey, JSON.stringify(DEFAULT_SUBJECTS));
          }
        }
      } catch (err) {
        // fallback
      } finally {
        setLoading(false);
      }
    };
    loadData();
  }, []);

  const saveToServer = (newSubjects: SubjectData[]) => {
    const namHocId = localStorage.getItem('selected_nam_hoc_id') || '1';
    localStorage.setItem(`zalo_student_gpa_data_v2_${namHocId}`, JSON.stringify(newSubjects));
    api.post('/api/zalo/save-gpa-data', { subjects_data: newSubjects }).catch(() => {});
  };

  const handleToggleSubject = (id: string) => {
    const updated = subjects.map(sub => {
      if (sub.id === id) {
        return { ...sub, active: !sub.active };
      }
      return sub;
    });
    setSubjects(updated);
    saveToServer(updated);
  };

  const handleScoreChange = (subId: string, semester: 'hk1' | 'hk2', field: 'gk' | 'ck' | 'passed', value: any) => {
    const updated = subjects.map(sub => {
      if (sub.id === subId) {
        const currentScore = sub[semester];
        return {
          ...sub,
          [semester]: {
            ...currentScore,
            [field]: value
          }
        };
      }
      return sub;
    });
    setSubjects(updated);
    saveToServer(updated);
  };

  const handleTxChange = (subId: string, semester: 'hk1' | 'hk2', index: number, value: string) => {
    const updated = subjects.map(sub => {
      if (sub.id === subId) {
        const currentScore = sub[semester];
        const newTx = [...currentScore.tx];
        newTx[index] = value;
        return {
          ...sub,
          [semester]: {
            ...currentScore,
            tx: newTx
          }
        };
      }
      return sub;
    });
    setSubjects(updated);
    saveToServer(updated);
  };

  // Tính điểm trung bình môn học kỳ (GDPT 2018: (Tổng TX + 2*GK + 3*CK) / (Số cột TX + 5))
  const calcSubAverage = (sub: SubjectData, semester: 'hk1' | 'hk2'): number | null => {
    if (!sub.isGraded) return null;
    const scoreItem = sub[semester];
    const txNums = scoreItem.tx.map(v => parseFloat(v)).filter(v => !isNaN(v));
    const gkNum = parseFloat(scoreItem.gk);
    const ckNum = parseFloat(scoreItem.ck);

    if (txNums.length === 0 || isNaN(gkNum) || isNaN(ckNum)) return null;

    const sumTx = txNums.reduce((a, b) => a + b, 0);
    const totalPoints = sumTx + (gkNum * 2) + (ckNum * 3);
    const totalWeights = txNums.length + 2 + 3;

    return Math.round((totalPoints / totalWeights) * 10) / 10;
  };

  // Tính điểm trung bình môn cả năm: (ĐTB_HK1 + 2*ĐTB_HK2) / 3
  const calcSubYearAverage = (sub: SubjectData): number | null => {
    if (!sub.isGraded) return null;
    const hk1 = calcSubAverage(sub, 'hk1');
    const hk2 = calcSubAverage(sub, 'hk2');
    if (hk1 === null || hk2 === null) return null;
    return Math.round(((hk1 + hk2 * 2) / 3) * 10) / 10;
  };

  // Thống kê tổng hợp
  const stats = useMemo(() => {
    const active = subjects.filter(s => s.active);
    if (active.length === 0) {
      return {
        tbHk1: null, tbHk2: null, tbCn: null,
        hlHk1: 'Chưa đủ điều kiện', hlHk2: 'Chưa đủ điều kiện', hlCn: 'Chưa đủ điều kiện'
      };
    }

    const gradedSubs = active.filter(s => s.isGraded);
    const nonGradedSubs = active.filter(s => !s.isGraded);

    const getHkStats = (sem: 'hk1' | 'hk2') => {
      const avgs = gradedSubs.map(s => calcSubAverage(s, sem));
      const hasMissing = avgs.some(a => a === null);
      const allPassed = nonGradedSubs.every(s => s[sem].passed);

      if (hasMissing || avgs.length === 0) {
        return { tb: null, minScore: null, allPassed, isMissing: true };
      }

      const validAvgs = avgs as number[];
      const sum = validAvgs.reduce((a, b) => a + b, 0);
      const tb = Math.round((sum / validAvgs.length) * 10) / 10;
      const minScore = Math.min(...validAvgs);

      return { tb, minScore, allPassed, isMissing: false };
    };

    const { tb: tbHk1, minScore: hk1Min, allPassed: hk1AllPassed, isMissing: hk1Missing } = getHkStats('hk1');
    const { tb: tbHk2, minScore: hk2Min, allPassed: hk2AllPassed, isMissing: hk2Missing } = getHkStats('hk2');

    let tbCn: number | null = null;
    let cnMin: number | null = null;
    let cnAllPassed = true;
    let cnMissing = true;

    if (tbHk1 !== null && tbHk2 !== null) {
      const yearAvgs = gradedSubs.map(s => calcSubYearAverage(s));
      if (!yearAvgs.some(a => a === null) && yearAvgs.length > 0) {
        const validYearAvgs = yearAvgs as number[];
        tbCn = Math.round((validYearAvgs.reduce((a, b) => a + b, 0) / validYearAvgs.length) * 10) / 10;
        cnMin = Math.min(...validYearAvgs);
        cnAllPassed = nonGradedSubs.every(s => s.hk2.passed);
        cnMissing = false;
      }
    }

    // Xếp loại học lực GDPT 2018 (Thông tư 22)
    const evaluate = (tb: number | null, minScore: number | null, allPassed: boolean, isMissing: boolean): string => {
      if (isMissing || tb === null || minScore === null) return 'Chưa đủ điều kiện';
      if (!allPassed) return 'Chưa đạt';

      // Đếm số môn >= 8.0
      if (tb >= 8.0 && minScore >= 6.5) {
        if (tb >= 9.0 && minScore >= 8.0) return 'Xuất sắc';
        return 'Giỏi';
      }
      if (tb >= 6.5 && minScore >= 5.0) return 'Khá';
      if (tb >= 5.0 && minScore >= 3.5) return 'Đạt';
      return 'Chưa đạt';
    };

    return {
      tbHk1, tbHk2, tbCn,
      hlHk1: evaluate(tbHk1, hk1Min, hk1AllPassed, hk1Missing),
      hlHk2: evaluate(tbHk2, hk2Min, hk2AllPassed, hk2Missing),
      hlCn: evaluate(tbCn, cnMin, cnAllPassed, cnMissing)
    };
  }, [subjects]);

  const activeSubjects = subjects.filter(s => s.active);

  if (loading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-[#f0f6fc]">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  const currentHl = selectedTab === 'hk1' ? stats.hlHk1 : selectedTab === 'hk2' ? stats.hlHk2 : stats.hlCn;

  const fullName = `${profile?.raw_data?.ho_dem || ''} ${profile?.raw_data?.ten || ''}`.trim() || 'Học Sinh';
  const initialChar = (profile?.raw_data?.ten || fullName || 'H').charAt(0).toUpperCase();
  const avatarUrl = profile?.raw_data?.anh_the 
    ? (profile.raw_data.anh_the.startsWith('http') ? profile.raw_data.anh_the : `${import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua'}/public/assets/anh_the/${profile.raw_data.anh_the}`)
    : `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=1e3a8a&color=ffffff`;

  return (
    <Page className="bg-[#f0f6fc] min-h-screen relative pb-16" hideScrollbar>
      <Header variant="back" title="Tính điểm Trung bình (GDPT 2018)" />

      <div className="p-4 flex flex-col gap-3.5 max-w-md mx-auto">
        
        {/* Thẻ thông tin học sinh & Điểm tổng hợp */}
        <div className="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex flex-col gap-3">
          
          {/* Header Thông tin */}
          <div className="flex items-center gap-3">
            <div className="w-11 h-11 rounded-full overflow-hidden shrink-0 border border-slate-200 shadow-2xs">
              {!imgError ? (
                <img 
                  src={avatarUrl} 
                  onError={() => setImgError(true)}
                  className="w-full h-full object-cover"
                  alt="Avatar"
                />
              ) : (
                <div className="w-full h-full bg-[#1e3a8a] text-white flex items-center justify-center font-bold text-base">
                  {initialChar}
                </div>
              )}
            </div>

            <div className="flex flex-col flex-1 min-w-0">
              <span className="text-[14.5px] font-bold text-[#1e3a8a] truncate">
                {fullName}
              </span>
              <div className="flex items-center gap-1.5 flex-wrap mt-0.5">
                <span className="bg-slate-100 text-slate-600 text-[10.5px] font-semibold px-2 py-0.5 rounded-md">
                  Lớp: {profile?.raw_data?.ten_lop || profile?.raw_data?.lop || '11A1'}
                </span>
                <span className="bg-blue-50 text-[#1e3a8a] text-[10.5px] font-semibold px-2 py-0.5 rounded-md">
                  Năm học: {profile?.raw_data?.ten_nam_hoc || '2026 - 2027'}
                </span>
              </div>
            </div>
          </div>

          {/* 3 Ô Điểm Tổng Hợp */}
          <div className="grid grid-cols-3 gap-2 pt-2.5 border-t border-slate-100">
            <div className="bg-[#f8fafc] p-2.5 rounded-xl flex flex-col items-center justify-center border border-slate-100">
              <span className="text-[10px] font-bold text-slate-400">TB HỌC KỲ I</span>
              <span className="text-base font-extrabold text-[#1e3a8a] mt-0.5">{stats.tbHk1 !== null ? stats.tbHk1.toFixed(1) : '-'}</span>
            </div>
            <div className="bg-[#f8fafc] p-2.5 rounded-xl flex flex-col items-center justify-center border border-slate-100">
              <span className="text-[10px] font-bold text-slate-400">TB HỌC KỲ II</span>
              <span className="text-base font-extrabold text-[#1e3a8a] mt-0.5">{stats.tbHk2 !== null ? stats.tbHk2.toFixed(1) : '-'}</span>
            </div>
            <div className="bg-[#f8fafc] p-2.5 rounded-xl flex flex-col items-center justify-center border border-slate-100">
              <span className="text-[10px] font-bold text-slate-400">TB CẢ NĂM</span>
              <span className="text-base font-extrabold text-[#1e3a8a] mt-0.5">{stats.tbCn !== null ? stats.tbCn.toFixed(1) : '-'}</span>
            </div>
          </div>

          {/* Xếp loại học lực */}
          <div className="bg-[#1e3a8a] text-white p-2.5 rounded-xl flex items-center justify-between shadow-2xs">
            <div className="flex items-center gap-2">
              <span className="text-amber-300 text-sm">⭐</span>
              <span className="text-[11.5px] font-medium text-blue-100">Học lực (Dự kiến):</span>
            </div>
            <span className="text-[12px] font-bold text-white bg-white/20 px-2.5 py-0.5 rounded-lg">
              {currentHl}
            </span>
          </div>
        </div>

        {/* Tab Chuyển đổi Học kỳ */}
        <div className="flex bg-slate-200/70 p-1 rounded-xl gap-1">
          {[
            { key: 'hk1', label: 'Học kỳ I' },
            { key: 'hk2', label: 'Học kỳ II' },
            { key: 'cn', label: 'Cả năm' },
          ].map(tab => (
            <button
              key={tab.key}
              onClick={() => setSelectedTab(tab.key as any)}
              className={`flex-1 py-1.5 text-xs font-bold rounded-lg transition-all ${
                selectedTab === tab.key 
                  ? 'bg-[#1e3a8a] text-white shadow-xs' 
                  : 'text-slate-600 hover:bg-white/40'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>

        {/* Tiêu đề Bảng Điểm & Nút Chọn Môn */}
        <div className="flex items-center justify-between px-1">
          <div className="flex items-center gap-2">
            <span className="text-xs font-bold text-slate-700 uppercase tracking-wide">BẢNG ĐIỂM CHI TIẾT</span>
            <span className="bg-[#1e3a8a]/10 text-[#1e3a8a] text-[10.5px] font-bold px-2 py-0.5 rounded-full">{activeSubjects.length}</span>
          </div>
          <button 
            onClick={() => setComboModalVisible(true)}
            className="bg-[#1e3a8a] hover:bg-[#162d6b] text-white text-xs font-bold py-1.5 px-3 rounded-xl shadow-xs active:scale-95 transition-all flex items-center gap-1 cursor-pointer"
          >
            <span>+ Chọn môn học</span>
          </button>
        </div>

        {/* Danh sách Môn học & Nhập điểm */}
        <div className="flex flex-col gap-3">
          {activeSubjects.length === 0 ? (
            <div className="bg-white rounded-2xl p-6 shadow-xs border border-slate-100 text-center flex flex-col items-center gap-2.5">
              <div className="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-[#1e3a8a] mb-1">
                <Icon icon="zi-note" size={24} />
              </div>
              <h4 className="text-sm font-bold text-slate-800 m-0">Chưa có môn học nào được chọn</h4>
              <p className="text-xs text-slate-400 max-w-[240px] leading-relaxed m-0">
                Nhấn vào nút <strong className="text-[#1e3a8a]">"Chọn môn học"</strong> để thiết lập danh sách môn học của bạn.
              </p>
              <button
                onClick={() => setComboModalVisible(true)}
                className="mt-2 bg-[#1e3a8a] text-white text-xs font-bold py-2 px-5 rounded-xl shadow-sm hover:bg-[#162d6b] transition cursor-pointer"
              >
                Chọn môn học ngay
              </button>
            </div>
          ) : (
            activeSubjects.map(sub => {
              const semKey = selectedTab === 'cn' ? 'hk2' : selectedTab;
              const scoreItem = sub[semKey];
              
              let avgVal: number | null = null;
              if (selectedTab === 'cn') {
                avgVal = calcSubYearAverage(sub);
              } else {
                avgVal = calcSubAverage(sub, selectedTab);
              }

              return (
                <div key={sub.id} className="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100 flex flex-col gap-2.5">
                  <div className="flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2.5 min-w-0">
                      <div className="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold shrink-0" style={{ backgroundColor: sub.color }}>
                        {sub.code}
                      </div>
                      <div className="flex flex-col min-w-0">
                        <span className="text-xs font-bold text-slate-800 truncate">
                          {sub.name}
                        </span>
                        <span className="text-[10px] text-slate-400 font-medium">
                          {sub.isElective ? 'Môn lựa chọn' : 'Môn bắt buộc'}
                        </span>
                      </div>
                    </div>

                    {/* Badge Điểm Số Trung Bình */}
                    <div className="shrink-0 flex items-center gap-1.5">
                      {sub.isGraded ? (
                        <div className="flex items-center gap-1.5">
                          <div className="flex flex-col items-center justify-center bg-blue-50 border border-blue-100 px-2 py-1 rounded-lg">
                            <span className="text-[8.5px] font-bold text-slate-400 leading-none">
                              {selectedTab === 'cn' ? 'CN' : selectedTab === 'hk1' ? 'HKI' : 'HKII'}
                            </span>
                            <span className={`text-xs font-extrabold leading-tight mt-0.5 ${
                              avgVal !== null && avgVal < 5 ? 'text-rose-600' : 'text-[#1e3a8a]'
                            }`}>
                              {avgVal !== null ? avgVal.toFixed(1) : '-'}
                            </span>
                          </div>
                        </div>
                      ) : (
                        <span className={`text-[11px] font-bold px-2.5 py-1 rounded-lg ${
                          scoreItem.passed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                        }`}>
                          {scoreItem.passed ? 'Đạt' : 'Chưa đạt'}
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Phần Nhập Điểm (ở tab HK1 hoặc HK2) */}
                  {selectedTab !== 'cn' && (
                    <div className="pt-2 border-t border-slate-100 flex flex-col gap-2">
                      {sub.isGraded ? (
                        <>
                          {/* Điểm Thường Xuyên */}
                          <div className="flex flex-col gap-1">
                            <div className="flex items-center justify-between text-[11px]">
                              <span className="font-bold text-slate-600">Điểm TX (5 cột):</span>
                            </div>
                            <div className="grid grid-cols-5 gap-1.5">
                              {scoreItem.tx.map((txVal, idx) => (
                                <input
                                  key={idx}
                                  type="text"
                                  value={txVal}
                                  onChange={(e) => {
                                    const val = e.target.value.replace(/[^0-9.]/g, '');
                                    if (val !== '' && parseFloat(val) > 10) return;
                                    handleTxChange(sub.id, selectedTab, idx, val);
                                  }}
                                  className="h-8 bg-[#f8fafc] border border-slate-200 rounded-lg text-xs font-bold text-[#1e3a8a] text-center outline-none focus:border-[#1e3a8a] focus:bg-white"
                                  placeholder="-"
                                />
                              ))}
                            </div>
                          </div>

                          {/* Giữa kỳ & Cuối kỳ */}
                          <div className="grid grid-cols-2 gap-2">
                            <div className="flex items-center justify-between bg-[#f8fafc] border border-slate-200/80 rounded-lg px-2.5 py-1">
                              <span className="text-[11px] font-bold text-slate-600">Giữa kỳ:</span>
                              <input
                                type="text"
                                value={scoreItem.gk}
                                onChange={(e) => {
                                  const val = e.target.value.replace(/[^0-9.]/g, '');
                                  if (val !== '' && parseFloat(val) > 10) return;
                                  handleScoreChange(sub.id, selectedTab, 'gk', val);
                                }}
                                className="w-10 h-7 text-xs font-extrabold text-[#1e3a8a] bg-white border border-slate-200 rounded text-center outline-none focus:border-[#1e3a8a]"
                                placeholder="-"
                              />
                            </div>
                            <div className="flex items-center justify-between bg-[#f8fafc] border border-slate-200/80 rounded-lg px-2.5 py-1">
                              <span className="text-[11px] font-bold text-slate-600">Cuối kỳ:</span>
                              <input
                                type="text"
                                value={scoreItem.ck}
                                onChange={(e) => {
                                  const val = e.target.value.replace(/[^0-9.]/g, '');
                                  if (val !== '' && parseFloat(val) > 10) return;
                                  handleScoreChange(sub.id, selectedTab, 'ck', val);
                                }}
                                className="w-10 h-7 text-xs font-extrabold text-[#1e3a8a] bg-white border border-slate-200 rounded text-center outline-none focus:border-[#1e3a8a]"
                                placeholder="-"
                              />
                            </div>
                          </div>
                        </>
                      ) : (
                        <div className="flex items-center justify-between px-1 py-1">
                          <span className="text-xs font-bold text-slate-600">Kết quả đánh giá:</span>
                          <div className="flex gap-1.5">
                            <button
                              type="button"
                              onClick={() => handleScoreChange(sub.id, selectedTab, 'passed', true)}
                              className={`px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer ${
                                scoreItem.passed ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500'
                              }`}
                            >
                              Đạt
                            </button>
                            <button
                              type="button"
                              onClick={() => handleScoreChange(sub.id, selectedTab, 'passed', false)}
                              className={`px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer ${
                                !scoreItem.passed ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500'
                              }`}
                            >
                              Chưa đạt
                            </button>
                          </div>
                        </div>
                      )}
                    </div>
                  )}
                </div>
              );
            })
          )}
        </div>
      </div>

      {/* Modal Chọn Tổ Hợp Môn */}
      <Modal
        visible={comboModalVisible}
        title="Chọn tổ hợp môn học"
        onClose={() => setComboModalVisible(false)}
        actions={[
          {
            text: 'Hoàn tất',
            close: true,
            highLight: true,
          }
        ]}
      >
        <div className="flex flex-col gap-2 my-2 max-h-80 overflow-y-auto pr-1">
          <Text className="text-xs text-slate-500 mb-2">
            Tích chọn các môn học bạn đang theo học để quản lý bảng điểm chính xác:
          </Text>
          {subjects.map(sub => (
            <div 
              key={sub.id} 
              onClick={() => handleToggleSubject(sub.id)}
              className={`flex items-center justify-between p-2.5 rounded-xl border cursor-pointer transition ${
                sub.active ? 'bg-blue-50/80 border-[#1e3a8a]' : 'bg-white border-slate-200'
              }`}
            >
              <div className="flex items-center gap-2.5">
                <div className="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0" style={{ backgroundColor: sub.color }}>
                  {sub.code}
                </div>
                <div className="flex flex-col">
                  <span className="text-xs font-bold text-slate-800">{sub.name}</span>
                  <span className="text-[10px] text-slate-400">{sub.isElective ? 'Môn lựa chọn' : 'Bắt buộc'}</span>
                </div>
              </div>
              <input 
                type="checkbox" 
                checked={sub.active} 
                readOnly
                className="w-4 h-4 accent-[#1e3a8a] rounded cursor-pointer"
              />
            </div>
          ))}
        </div>
      </Modal>
    </Page>
  );
};

export default CalculateGpaPage;
