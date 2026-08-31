import { useNavigate } from "react-router-dom";
import React, { useState, useEffect } from 'react';
import { Page, Box, Text, Spinner, useSnackbar, Button } from "zmp-ui";
import { useParams } from 'react-router-dom';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import api from '@/lib/api';

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
}

const ActivityDetailPage: React.FC = () => {
  const { id } = useParams();
  const [activity, setActivity] = useState<Activity | null>(null);
  const [loading, setLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { openSnackbar } = useSnackbar();
  const navigate = useNavigate();

  useEffect(() => {
    fetchActivity();
  }, [id]);

  const fetchActivity = async () => {
    try {
      setLoading(true);
      // We will reuse api_zalo_hoat_dong.php and filter locally since there is no separate detail API
      const response = await api.get('/api/zalo/hoat-dong');
      if (response.data?.success) {
        const found = response.data.data.find((a: Activity) => a.id.toString() === id);
        if (found) {
          setActivity(found);
        } else {
          openSnackbar({ text: 'Hoạt động không tồn tại hoặc đã bị khoá', type: 'error' });
          navigate(-1);
        }
      }
    } catch (error: any) {
      openSnackbar({ text: 'Lỗi kết nối', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleRegister = async (action: 'register' | 'unregister') => {
    if (!activity) return;
    
    setIsSubmitting(true);
    try {
      const response = await api.post('/api/zalo/hoat-dong-dang-ky', {
        hoat_dong_id: activity.id,
        action: action
      });
      
      if (response.data?.success) {
        openSnackbar({ text: response.data.message, type: 'success' });
        fetchActivity(); // reload state
      } else {
        openSnackbar({ text: response.data?.message || 'Lỗi xử lý', type: 'error' });
      }
    } catch (error: any) {
      openSnackbar({ text: 'Không thể kết nối đến máy chủ', type: 'error' });
    } finally {
      setIsSubmitting(false);
    }
  };

  const isRegisterOpen = (start: string, end: string) => {
    if (!start && !end) return true;
    const now = new Date().getTime();
    if (start && now < new Date(start).getTime()) return false;
    if (end && now > new Date(end).getTime()) return false;
    return true;
  };

  const formatDateTime = (dateStr: string) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')} ${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`;
  };

  if (loading) {
    return (
      <Page className="bg-slate-50 relative pb-10">
        <Header title="Chi tiết hoạt động" showBackIcon={true} />
        <div className="flex justify-center py-20">
          <Spinner visible />
        </div>
      </Page>
    );
  }

  if (!activity) return null;

  const isOpen = isRegisterOpen(activity.thoi_gian_bd_dang_ky, activity.thoi_gian_kt_dang_ky);
  const isFull = activity.so_luong_dang_ky > 0 && activity.dang_ky_count >= activity.so_luong_dang_ky;
  const isRegistered = activity.user_status !== null;

  return (
    <Page className="bg-slate-50 relative pb-32">
      <Header title="Chi tiết hoạt động" showBackIcon={true} />
      
      <div className="p-4 pt-6 space-y-4">
        {/* Title Card */}
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="p-5 border-b border-slate-200">
            <div className="text-[20px] font-bold text-black mb-2 leading-tight">
              {activity.ten_hoat_dong}
            </div>
            <div className="flex flex-col gap-1 mt-2">
              <span className={`text-sm font-bold ${activity.diem_tich_luy > 0 ? 'text-green-600' : 'text-red-600'}`}>
                Điểm tích luỹ gốc: {activity.diem_tich_luy > 0 ? '+' : ''}{activity.diem_tich_luy}đ
              </span>
              {activity.diem_thuc_te !== undefined && (
                <span className={`text-sm font-bold ${activity.diem_thuc_te > 0 ? 'text-green-600' : (activity.diem_thuc_te < 0 ? 'text-red-600' : 'text-slate-600')}`}>
                  Điểm thực tế: {activity.diem_thuc_te > 0 ? '+' : ''}{activity.diem_thuc_te}đ
                </span>
              )}
            </div>
          </div>

          {/* Status Card if Registered */}
          {isRegistered && (
            <div className={`p-4 border-b ${activity.user_status === 0 ? 'bg-amber-50 border-amber-100' : activity.user_status === 1 ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100'}`}>
              <Text className="font-bold mb-1 flex items-center gap-2">
                <Icon icon="zi-info-circle" size={18} />
                Trạng thái của bạn:
              </Text>
              <Text className="text-sm">
                {activity.user_status === 0 && 'Đã đăng ký. Vui lòng tham gia hoạt động và quét mã QR để được điểm danh.'}
                {activity.user_status === 1 && 'Bạn đã tham gia hoạt động này và được cộng điểm rèn luyện.'}
                {(activity.user_status === 2 || activity.user_status === 3 || activity.user_status === 4 || activity.user_status === 5) && 'Bạn vắng mặt hoặc vi phạm quy chế hoạt động nên đã bị hệ thống trừ điểm/huỷ điểm.'}
              </Text>
            </div>
          )}

          {/* Details Card */}
          <div className="p-5 space-y-4">
            <div>
              <Text className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Mô tả</Text>
              <Text className="text-sm text-slate-800 whitespace-pre-line">
                {activity.mo_ta_ngan || 'Không có mô tả chi tiết'}
              </Text>
            </div>
            
            <div className="h-[1px] bg-slate-200"></div>
            
            <div>
              <Text className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Đối tượng tham gia</Text>
              <Text className="text-sm text-slate-800 font-medium">{activity.doi_tuong}</Text>
            </div>

            <div className="h-[1px] bg-slate-200"></div>

            <div>
              <Text className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Thời gian đăng ký</Text>
              {(!activity.thoi_gian_bd_dang_ky && !activity.thoi_gian_kt_dang_ky) ? (
                <Text className="text-sm text-slate-800 font-medium">Không giới hạn thời gian</Text>
              ) : (
                <Text className="text-sm text-slate-800 font-medium">
                  {activity.thoi_gian_bd_dang_ky ? formatDateTime(activity.thoi_gian_bd_dang_ky) : 'Bây giờ'} 
                  <span className="mx-2 text-slate-400">đến</span> 
                  {activity.thoi_gian_kt_dang_ky ? formatDateTime(activity.thoi_gian_kt_dang_ky) : 'Không giới hạn'}
                </Text>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Fixed Bottom Bar */}
      <div className="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-slate-200 z-50 pb-safe shadow-[0_-5px_15px_rgba(0,0,0,0.05)]">
        {isRegistered ? (
          <Button 
            fullWidth 
            disabled 
            variant="secondary"
            className="bg-green-50 text-green-600 border-green-200"
          >
            Đã đăng ký thành công (Không thể huỷ)
          </Button>
        ) : !isOpen ? (
          <Button fullWidth disabled variant="secondary" className="bg-slate-100 text-slate-500">
            Đã đóng form đăng ký
          </Button>
        ) : isFull ? (
          <Button fullWidth disabled variant="secondary" className="bg-rose-50 text-rose-500">
            Đã đủ số lượng đăng ký
          </Button>
        ) : (
          <Button 
            fullWidth 
            loading={isSubmitting}
            onClick={() => handleRegister('register')}
            className="bg-[#224397] text-white font-bold"
          >
            Đăng ký tham gia ngay
          </Button>
        )}
      </div>
    </Page>
  );
};

export default ActivityDetailPage;
