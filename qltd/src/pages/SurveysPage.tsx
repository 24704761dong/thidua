import logoImg from '@/assets/logo.png';
import React, { useState, useEffect } from 'react';
import { Page, Text, useSnackbar, Spinner } from 'zmp-ui';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import api from '@/lib/api';
import { navigateForward } from '@/utils/navigation';
import { useNavigate } from 'react-router-dom';

interface Survey {
  id: string;
  title: string;
  badge: string;
  badgeType: 'required' | 'optional' | 'completed';
  dueDate: string;
  description: string;
  completed: boolean;
  banner_url?: string;
  submittedAt?: string;
}

const SurveysPage: React.FC = () => {
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();
  const [activeTab, setActiveTab] = useState<'pending' | 'completed'>('pending');
  const [loading, setLoading] = useState<boolean>(true);
  const [surveys, setSurveys] = useState<Survey[]>([]);
  const [activeCardId, setActiveCardId] = useState<string | null>(null);

  useEffect(() => {
    const fetchSurveys = async () => {
      try {
        setLoading(true);
        const res = await api.get('/api/zalo/get-surveys');
        if (res.data?.success) {
          setSurveys([...(res.data.pending || []), ...(res.data.completed || [])]);
        } else {
          openSnackbar({ text: res.data?.message || 'Không thể tải danh sách khảo sát', type: 'error' });
        }
      } catch (err) {
        openSnackbar({ text: 'Lỗi kết nối máy chủ', type: 'error' });
      } finally {
        setLoading(false);
      }
    };

    fetchSurveys();
  }, []);

  const handleOpenSurvey = (survey: Survey) => {
    navigateForward(navigate, `/survey-take/${survey.id}`);
  };

  const filteredSurveys = surveys.filter(s => activeTab === 'pending' ? !s.completed : s.completed);

  return (
    <Page className="bg-transparent relative pb-24" hideScrollbar>
      <Header variant="back" title="Khảo sát ý kiến" />

      {/* Tab Navigation giống hệt ảnh mẫu của user */}
      <div className="bg-white border-b border-slate-200 flex items-center justify-around px-4 sticky top-0 z-20 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
        <button
          onClick={() => setActiveTab('pending')}
          className={`py-3.5 text-[15px] transition-all relative ${
            activeTab === 'pending'
              ? 'text-[#224397] font-bold'
              : 'text-slate-400 font-medium hover:text-slate-600'
          }`}
        >
          Chưa thực hiện
          {activeTab === 'pending' && (
            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-[#224397] rounded-full" />
          )}
        </button>
        <button
          onClick={() => setActiveTab('completed')}
          className={`py-3.5 text-[15px] transition-all relative ${
            activeTab === 'completed'
              ? 'text-[#224397] font-bold'
              : 'text-slate-400 font-medium hover:text-slate-600'
          }`}
        >
          Đã thực hiện
          {activeTab === 'completed' && (
            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-[#224397] rounded-full" />
          )}
        </button>
      </div>

      {/* Content Area */}
      <div className="px-4 py-5 flex flex-col gap-4">
        {loading ? (
          <div className="flex items-center justify-center h-[60vh]">
            <Spinner visible logo={logoImg} />
          </div>
        ) : filteredSurveys.length === 0 ? (
          <div className="bg-white/90 backdrop-blur-md rounded-3xl p-8 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.08)] border border-slate-100 text-center flex flex-col items-center gap-4 my-6">
            <div className="w-16 h-16 rounded-full bg-[#E4F6FD] flex items-center justify-center text-[#224397] shadow-sm">
              <Icon icon="zi-check-circle" size={32} />
            </div>
            <Text className="text-base font-bold text-slate-800">
              {activeTab === 'pending' ? 'Bạn đã hoàn thành tất cả khảo sát!' : 'Chưa có khảo sát nào đã thực hiện'}
            </Text>
            <Text className="text-xs text-slate-500 max-w-[260px] leading-relaxed">
              {activeTab === 'pending' 
                ? 'Tuyệt vời! Hiện tại không có bài khảo sát nào đang chờ bạn thực hiện. Hãy kiểm tra lại sau nhé.' 
                : 'Những bài khảo sát bạn đã gửi sẽ xuất hiện tại đây để bạn có thể dễ dàng xem lại câu trả lời.'}
            </Text>
          </div>
        ) : (
          filteredSurveys.map(survey => {
            const isActive = activeCardId === survey.id;
            return (
              <div 
                key={survey.id}
                onClick={() => {
                  if (survey.badgeType !== 'expired') {
                    setActiveCardId(survey.id);
                    handleOpenSurvey(survey);
                  } else {
                    openSnackbar({ text: 'Bài khảo sát này đã hết hạn nộp.', type: 'warning', duration: 3000 });
                  }
                }}
                className={`group backdrop-blur-md rounded-xl p-5 shadow-[0_10px_30px_-10px_rgba(34,67,151,0.08)] border flex flex-col gap-4 transition-all duration-300 overflow-hidden ${
                  survey.badgeType === 'expired' ? 'opacity-80' : 'cursor-pointer'
                } ${
                  isActive 
                    ? 'bg-[#FAB723]/10 border-[#FAB723] shadow-[0_10px_30px_-5px_rgba(250,183,35,0.2)]' 
                    : 'bg-white/90 border-[#224397]/25 hover:border-[#FAB723] hover:bg-[#FAB723]/5 hover:shadow-[0_10px_30px_-5px_rgba(250,183,35,0.15)]'
                }`}
              >
                {/* Banner (Tỷ lệ 21:9) */}
                {survey.banner_url && (
                  <div className="-mt-5 -mx-5 mb-1 aspect-[21/9] overflow-hidden bg-slate-100">
                    <img src={survey.banner_url} alt="Banner" className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                  </div>
                )}

                {/* Header Card: Badges & Hạn nộp */}
                <div className="flex items-center justify-between">
                  <span className={`text-xs font-bold transition-colors ${
                    survey.badgeType === 'expired' ? 'text-rose-500' :
                    survey.badgeType === 'required' ? 'text-rose-600' :
                    survey.badgeType === 'optional' ? 'text-blue-600' :
                    'text-emerald-600'
                  } ${
                    survey.badgeType !== 'expired' && !isActive 
                      ? 'group-hover:text-[#d97706]' 
                      : ''
                  }`}>
                    {survey.badge}
                  </span>
                  <span className="text-xs font-medium text-slate-500 flex items-center gap-1">
                    <Icon icon="zi-clock-1" size={12} className="text-slate-400" />
                    <span>{survey.completed ? `Đã nộp: ${survey.submittedAt}` : `Hạn: ${survey.dueDate}`}</span>
                  </span>
                </div>

                {/* Tên & Mô tả Khảo sát kèm Icon Nút chuẩn thiết kế mẫu */}
                <div className="flex items-center justify-between gap-4">
                  <div className="flex flex-col gap-1.5 flex-1">
                    <Text className={`text-base font-bold leading-snug transition-colors duration-300 ${
                      isActive ? 'text-[#d97706]' : 'text-[#224397] group-hover:text-[#d97706]'
                    }`}>
                      {survey.title}
                    </Text>
                    <Text className="text-xs text-slate-500 leading-relaxed line-clamp-2">
                      {survey.description}
                    </Text>
                  </div>
                  {survey.badgeType !== 'expired' && (
                  <div className={`w-11 h-11 rounded-xl flex items-center justify-center font-bold text-lg transition-all duration-300 shrink-0 shadow-sm ${
                    isActive 
                      ? 'bg-[#FAB723]/20 text-[#d97706] border border-[#FAB723]/40' 
                      : 'bg-[#224397]/10 text-[#224397] border border-[#224397]/20 group-hover:bg-[#FAB723]/20 group-hover:text-[#d97706] group-hover:border-[#FAB723]/40'
                  }`}>
                    <Icon icon={!survey.completed ? "zi-edit-text" : "zi-note"} size={20} />
                  </div>
                  )}
                </div>
              </div>
            );
          })
        )}
      </div>
    </Page>
  );
};

export default SurveysPage;
