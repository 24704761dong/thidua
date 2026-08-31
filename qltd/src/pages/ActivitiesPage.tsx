import { useNavigate } from "react-router-dom";
import React, { useState, useEffect } from 'react';
import { Page, Box, Text, Spinner, useSnackbar } from "zmp-ui";
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import api from '@/lib/api';
import { PATHS } from '@/constants/paths';
import { navigateForward } from '@/utils/navigation';

interface Activity {
  id: number;
  ten_hoat_dong: string;
  mo_ta_ngan: string;
  diem_tich_luy: number;
  so_luong_dang_ky: number;
  dang_ky_count: number;
  doi_tuong: string;
  thoi_gian_bd_dang_ky: string;
  thoi_gian_kt_dang_ky: string;
  user_status: number | null;
  diem_thuc_te?: number;
}

const ActivitiesPage: React.FC = () => {
  const [activities, setActivities] = useState<Activity[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<'pending' | 'joined'>('pending');
  const { openSnackbar } = useSnackbar();
  const navigate = useNavigate();

  useEffect(() => {
    fetchActivities();
  }, []);

  const fetchActivities = async () => {
    try {
      setLoading(true);
      const response = await api.get('/api/zalo/hoat-dong');
      if (response.data?.success) {
        setActivities(response.data.data);
      } else {
        openSnackbar({ text: response.data?.message || 'Lỗi tải dữ liệu', type: 'error' });
      }
    } catch (error: any) {
      openSnackbar({ text: error.message || 'Lỗi kết nối', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const isRegisterOpen = (start: string, end: string) => {
    if (!start && !end) return true;
    const now = new Date().getTime();
    if (start && now < new Date(start).getTime()) return false;
    if (end && now > new Date(end).getTime()) return false;
    return true;
  };

  const filteredActivities = activities.filter(item => {
    const isOpen = isRegisterOpen(item.thoi_gian_bd_dang_ky, item.thoi_gian_kt_dang_ky);
    const isRegistered = item.user_status !== null;
    if (activeTab === 'pending') {
      // Đối với hoạt động còn thời hạn đăng ký thì vẫn hiển thị ở Đang mở đăng ký (kể cả đã đăng ký)
      return !isRegistered || isOpen;
    } else {
      return isRegistered;
    }
  });

  return (
    <Page className="bg-transparent relative pb-24" hideScrollbar>
      <Header title="Hoạt động phong trào" showBackIcon={true} />
      
      {/* Tab Navigation */}
      <div className="bg-white border-b border-slate-200 flex items-center justify-around px-4 sticky top-0 z-20 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
        <button
          onClick={() => setActiveTab('pending')}
          className={`py-3.5 text-[15px] transition-all relative ${
            activeTab === 'pending'
              ? 'text-[#224397] font-bold'
              : 'text-slate-400 font-medium hover:text-slate-600'
          }`}
        >
          Đang mở đăng ký
          {activeTab === 'pending' && (
            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-[#224397] rounded-full" />
          )}
        </button>
        <button
          onClick={() => setActiveTab('joined')}
          className={`py-3.5 text-[15px] transition-all relative ${
            activeTab === 'joined'
              ? 'text-[#224397] font-bold'
              : 'text-slate-400 font-medium hover:text-slate-600'
          }`}
        >
          Đã tham gia
          {activeTab === 'joined' && (
            <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-[#224397] rounded-full" />
          )}
        </button>
      </div>

      <div className="p-4 pt-5 pb-24 space-y-4">
        {loading ? (
          <div className="flex justify-center py-10">
            <Spinner visible />
          </div>
        ) : filteredActivities.length === 0 ? (
          <div className="bg-white rounded-xl p-8 text-center border border-slate-200">
            <Icon icon="zi-auto-solid" className="text-slate-300 mx-auto mb-3" size={48} />
            <Text className="text-slate-500">
              {activeTab === 'pending' ? 'Hiện tại chưa có hoạt động nào đang mở' : 'Bạn chưa tham gia hoạt động nào'}
            </Text>
          </div>
        ) : (
          filteredActivities.map((item) => {
            const isOpen = isRegisterOpen(item.thoi_gian_bd_dang_ky, item.thoi_gian_kt_dang_ky);
            const isFull = item.so_luong_dang_ky > 0 && item.dang_ky_count >= item.so_luong_dang_ky;
            const isRegistered = item.user_status !== null;
            
            let statusBadge = null;
            if (activeTab === 'pending' && isRegistered) {
              statusBadge = (
                <div className="flex gap-1.5 flex-wrap">
                  <span className="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 text-[11px] rounded-md font-medium">
                    Đang mở
                  </span>
                  <span className="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[11px] rounded-md font-bold flex items-center gap-1">
                    <Icon icon="zi-check" size={12} /> Đã đăng ký
                  </span>
                </div>
              );
            } else if (isRegistered) {
              if (item.user_status === 0) {
                statusBadge = <span className="px-2 py-1 bg-amber-100 text-amber-700 text-[11px] rounded-md font-bold">Chờ điểm danh</span>;
              } else if (item.user_status === 1) {
                statusBadge = <span className="px-2 py-1 bg-green-100 text-green-700 text-[11px] rounded-md font-bold">Đã tham gia</span>;
              } else {
                statusBadge = <span className="px-2 py-1 bg-red-100 text-red-700 text-[11px] rounded-md font-bold">Vắng / Vi phạm</span>;
              }
            } else if (!isOpen) {
              statusBadge = <span className="px-2 py-1 bg-slate-200 text-slate-600 text-[11px] rounded-md font-bold">Đã đóng đăng ký</span>;
            } else if (isFull) {
              statusBadge = <span className="px-2 py-1 bg-rose-100 text-rose-700 text-[11px] rounded-md font-bold">Đã đủ số lượng</span>;
            } else {
              statusBadge = <span className="px-2 py-1 bg-blue-100 text-blue-700 text-[11px] rounded-md font-bold">Đang mở đăng ký</span>;
            }

            return (
              <div 
                key={item.id} 
                onClick={() => navigateForward(navigate, PATHS.ACTIVITY_DETAIL.replace(':id', item.id.toString()))}
                className="bg-white rounded-xl p-4 border border-slate-200 shadow-sm active:bg-slate-50 transition-colors"
              >
                <div className="flex justify-between items-start mb-2">
                  <div className="font-bold text-black text-[16px] flex-1 mr-2 leading-tight">
                    {item.ten_hoat_dong}
                  </div>
                  <div className="flex-shrink-0">
                    <span className={`text-[13px] font-bold ${
                      (activeTab === 'joined' && item.diem_thuc_te !== undefined ? item.diem_thuc_te : item.diem_tich_luy) > 0 
                      ? 'text-green-600' 
                      : 'text-red-600'
                    }`}>
                      {(activeTab === 'joined' && item.diem_thuc_te !== undefined ? item.diem_thuc_te : item.diem_tich_luy) > 0 ? '+' : ''}
                      {activeTab === 'joined' && item.diem_thuc_te !== undefined ? item.diem_thuc_te : item.diem_tich_luy}đ
                    </span>
                  </div>
                </div>
                
                <Text className="text-slate-500 text-xs mb-3 line-clamp-2 whitespace-pre-line">
                  {item.mo_ta_ngan || 'Chưa có mô tả'}
                </Text>
                
                <div className="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                  <div className="flex gap-2">
                    {statusBadge}
                  </div>
                  <div className="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                    <Icon icon="zi-user" size={12} />
                    {item.dang_ky_count} {item.so_luong_dang_ky > 0 ? `/ ${item.so_luong_dang_ky}` : ''}
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>
    </Page>
  );
};

export default ActivitiesPage;
