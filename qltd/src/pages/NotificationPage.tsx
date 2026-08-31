import logoImg from '@/assets/logo.png';
import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Page, Text, Box, Button, useSnackbar, Spinner } from 'zmp-ui';
import { useQueryClient } from '@tanstack/react-query';
import { Icon } from '@/components/Icon';
import api from '@/lib/api';
import { ENDPOINTS } from '@/constants/endpoints';
import { PATHS } from '@/constants/paths';

interface Notification {
  id: number;
  tieu_de: string;
  noi_dung: string;
  loai_thong_bao: string;
  thoi_gian: string;
  da_xem: number;
}

const NotificationPage: React.FC = () => {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const { openSnackbar } = useSnackbar();
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const handleNotificationClick = async (notif: Notification) => {
    // 1. Đánh dấu đã đọc
    if (Number(notif.da_xem) === 0) {
      try {
        await api.post('/api/zalo/read-notifications', { id: notif.id });
        setNotifications((prev) =>
          prev.map((n) => (n.id === notif.id ? { ...n, da_xem: 1 } : n))
        );
        queryClient.invalidateQueries({ queryKey: ['unread_notifications'] });
      } catch (error) {
        console.error('Lỗi khi đánh dấu đã đọc:', error);
      }
    }

    // 2. Chuyển trang dựa trên loại thông báo
    const profileRelatedTypes = ['gui_yeu_cau_ho_so', 'duyet_ho_so', 'tu_choi_ho_so', 'cap_nhat_ho_so'];
    if (profileRelatedTypes.includes(notif.loai_thong_bao)) {
      navigate(PATHS.PROFILE);
    }
  };

  const fetchNotifications = async () => {
    try {
      const res = await api.get(ENDPOINTS.NOTIFICATIONS);
      if (res.data?.success) {
        setNotifications(res.data.data);
      }
    } catch (error) {
      console.error('Lỗi lấy thông báo:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchNotifications();
  }, []);

  const handleMarkAllAsRead = async () => {
    try {
      const res = await api.post(ENDPOINTS.READ_NOTIFICATIONS, {});
      if (res.data?.success) {
        setNotifications(prev => prev.map(n => ({ ...n, da_xem: 1 })));
        queryClient.invalidateQueries({ queryKey: ['unread_notifications'] });
        openSnackbar({ text: 'Đã đánh dấu tất cả là đã đọc', type: 'success' });
      }
    } catch (error) {
      console.error(error);
    }
  };

  const groupNotificationsByDate = (notifs: Notification[]) => {
    const groups: { [key: string]: Notification[] } = {};
    notifs.forEach(n => {
      const dateStr = new Date(n.thoi_gian).toLocaleDateString('vi-VN', {
        day: '2-digit', month: '2-digit', year: 'numeric'
      }).replace(/\//g, '-');
      
      if (!groups[dateStr]) {
        groups[dateStr] = [];
      }
      groups[dateStr].push(n);
    });
    return groups;
  };

  const groupedNotifications = groupNotificationsByDate(notifications);
  const hasUnread = notifications.some(n => Number(n.da_xem) === 0);

  if (loading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page hideScrollbar className="bg-transparent relative pb-24">
      <div className="px-4 pt-2 pb-2 flex items-center justify-between relative z-10">
        <h2 className="text-[16px] font-bold text-[#224397] uppercase tracking-wide">
          Thông báo
        </h2>
        {hasUnread && (
          <span 
            onClick={handleMarkAllAsRead} 
            className="text-sm font-medium text-primary active:text-blue-700 transition-colors cursor-pointer"
          >
            Đánh dấu tất cả đã đọc
          </span>
        )}
      </div>
      {notifications.length === 0 ? (
        <div className="flex flex-col items-center justify-center pt-20 text-slate-400">
          <Text className="text-slate-400">Không có thông báo nào</Text>
        </div>
      ) : (
        <div className="p-4 pt-0">
          <div className="flex flex-col gap-6">
            {Object.keys(groupedNotifications).map((date) => (
              <div key={date}>
                <Text className="text-slate-500 font-semibold mb-3">{date}</Text>
                <div className="flex flex-col gap-3">
                  {groupedNotifications[date].map((notif) => (
                    <div 
                      key={notif.id} 
                      onClick={() => handleNotificationClick(notif)}
                      className={`bg-white p-4 rounded-xl shadow-sm border border-slate-100 relative overflow-hidden active:bg-slate-50 transition-colors cursor-pointer ${Number(notif.da_xem) === 0 ? 'border-l-[4px] border-l-[#FAB723]' : ''}`}
                    >
                      <div className="flex items-center gap-2 mb-1">
                        <Text className="font-bold text-slate-800 text-[15px] leading-snug">
                          {notif.tieu_de}
                        </Text>
                        {Number(notif.da_xem) === 0 && <span className="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>}
                      </div>
                      <Text className="text-slate-600 text-[14px] leading-relaxed mb-2">
                        {notif.noi_dung}
                      </Text>
                      <div className="flex items-center text-slate-400 text-xs">
                        <Icon icon="zi-clock-1" size={12} className="mr-1" />
                        {new Date(notif.thoi_gian).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </Page>
  );
};

export default NotificationPage;
