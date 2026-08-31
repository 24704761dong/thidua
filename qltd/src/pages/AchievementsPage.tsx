import logoImg from '@/assets/logo.png';
import React, { useState } from 'react';
import { Page, Box, Text, Icon, Spinner, Tabs } from 'zmp-ui';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import Header from '@/components/Header';

interface Achievement {
  ngay_khen_thuong: string;
  ten_khen_thuong: string;
  cap_khen_thuong: string;
  ghi_chu: string;
  loai: 'ca_nhan' | 'tap_the';
  ten_tap_the?: string;
}

const AchievementsPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'all' | 'ca_nhan' | 'tap_the'>('all');

  const { data, isLoading, error } = useQuery({
    queryKey: ['achievements'],
    queryFn: async () => {
      const res = await api.get('/api/zalo/achievements');
      return res.data;
    },
  });

  const achievements: Achievement[] = data?.data || [];

  const filteredAchievements = achievements.filter(a => {
    if (activeTab === 'all') return true;
    return a.loai === activeTab;
  });

  const formatDate = (dateString: string) => {
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
  };

  if (isLoading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page className="bg-transparent relative pb-10 flex flex-col h-screen">
      <Header variant="back" title="Thành tích khen thưởng" />
      
      <div className="bg-white">
        <Tabs activeKey={activeTab} onChange={(key) => setActiveTab(key as any)}>
          <Tabs.Tab key="all" label="Tất cả" />
          <Tabs.Tab key="ca_nhan" label="Cá nhân" />
          <Tabs.Tab key="tap_the" label="Tập thể" />
        </Tabs>
      </div>

      <div className="flex-1 overflow-y-auto px-4 py-4 space-y-4">

        {!isLoading && error && (
          <div className="text-center text-red-500 py-10 bg-white rounded-xl shadow-sm">
            <Icon icon="zi-warning" className="text-red-500 mb-2" size={32} />
            <p>Có lỗi xảy ra khi tải dữ liệu.</p>
          </div>
        )}

        {!isLoading && !error && filteredAchievements.length === 0 && (
          <div className="text-center text-slate-500 py-10 bg-white rounded-xl shadow-sm border border-slate-100 flex flex-col items-center">
            <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
              <Icon icon="zi-star-solid" className="text-slate-300" size={36} />
            </div>
            <p className="font-medium text-slate-700">Chưa có thành tích</p>
            <p className="text-sm mt-1">Không có thành tích nào phù hợp với bộ lọc này.</p>
          </div>
        )}

        {!isLoading && !error && filteredAchievements.length > 0 && (
          <div className="space-y-3">
            {filteredAchievements.map((a, idx) => (
              <div key={idx} className={`bg-white p-4 rounded-xl shadow-sm border relative overflow-hidden ${a.loai === 'tap_the' ? 'border-indigo-100' : 'border-amber-100'}`}>
                <div className={`absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b ${a.loai === 'tap_the' ? 'from-indigo-400 to-indigo-600' : 'from-[#FAB723] to-[#f59e0b]'}`}></div>
                <div className="flex items-start pl-2 gap-3">
                  <div className={`w-10 h-10 rounded-full flex flex-shrink-0 items-center justify-center border ${a.loai === 'tap_the' ? 'bg-indigo-50 border-indigo-100' : 'bg-amber-50 border-amber-100'}`}>
                    <Icon icon={a.loai === 'tap_the' ? 'zi-group-solid' : 'zi-star-solid'} className={a.loai === 'tap_the' ? 'text-indigo-500' : 'text-[#FAB723]'} size={20} />
                  </div>
                  <div className="flex-1">
                    <h3 className="font-bold text-slate-800 text-[15px] leading-snug mb-1.5">
                      {a.ten_khen_thuong}
                    </h3>
                    <div className="flex items-center text-xs text-slate-500 mb-1 gap-2 flex-wrap">
                      {a.loai === 'tap_the' && (
                        <span className="px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[10px] font-semibold border border-indigo-100 mb-1">
                          Tập thể {a.ten_tap_the}
                        </span>
                      )}
                      <span className="flex items-center mb-1">
                        <Icon icon="zi-calendar" size={12} className="mr-1" />
                        {formatDate(a.ngay_khen_thuong)}
                      </span>
                      {a.cap_khen_thuong && (
                        <span className="px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-semibold border border-blue-100 mb-1">
                          {a.cap_khen_thuong}
                        </span>
                      )}
                    </div>
                    {a.ghi_chu && (
                      <p className="text-[13px] text-slate-600 mt-2 bg-slate-50 p-2 rounded-lg border border-slate-100">
                        {a.ghi_chu}
                      </p>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </Page>
  );
};

export default AchievementsPage;
