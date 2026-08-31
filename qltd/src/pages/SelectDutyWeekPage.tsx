import logoImg from '@/assets/logo.png';
import { useNavigate } from "react-router-dom";
import React, { useEffect, useState } from 'react';
import { Page, Text, useSnackbar, Icon, Spinner } from "zmp-ui";
import api from '@/lib/api';
import { PATHS } from '@/constants/paths';
import Header from '@/components/Header';

const SelectDutyWeekPage: React.FC = () => {
  const [weeks, setWeeks] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();

  useEffect(() => {
    const fetchWeeks = async () => {
      try {
        const res = await api.get('/api/zalo/duty-weeks');
        if (res.data.success) {
          setWeeks(res.data.data);
        } else {
          openSnackbar({ text: res.data.message || 'Lỗi lấy dữ liệu', type: 'error' });
        }
      } catch (error) {
        console.error(error);
        openSnackbar({ text: 'Lỗi kết nối', type: 'error' });
      } finally {
        setLoading(false);
      }
    };
    fetchWeeks();
  }, []);

  const formatShortDate = (dateStr: string) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}`;
  };

  if (loading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page className="bg-transparent">
      <Header variant="back" title="Chọn tuần - Đăng Ký Trực" />
      <div className="px-4 pt-4 pb-32 space-y-3">
        {weeks.length === 0 ? (
          <div className="text-center py-10 text-slate-400">Không có tuần học nào được mở.</div>
        ) : (
          weeks.map((week) => {
            const status = week.status;
            let statusBadge = null;
            if (status === 'Đã duyệt') {
              statusBadge = <span className="px-2 py-0.5 text-[10px] font-bold rounded-md bg-green-100 text-green-600 uppercase">Đã Duyệt</span>;
            } else if (status === 'Chờ duyệt') {
              statusBadge = <span className="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-600 uppercase">Chờ Duyệt</span>;
            } else {
              statusBadge = <span className="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-500 uppercase">Chưa Nộp</span>;
            }

            return (
              <div 
                key={week.id}
                onClick={() => navigate(PATHS.DUTY_INPUT.replace(':id', week.id))}
                className="bg-white px-4 py-3 rounded-[12px] shadow-sm border border-[#224397]/25 flex items-center justify-between active:bg-slate-50 transition-colors mx-1"
              >
                <div>
                  <Text className="font-bold text-[14px] text-[#224397] mb-0.5 uppercase">{week.ten_tuan}</Text>
                  <div className="flex items-center gap-2 flex-wrap mt-1">
                    <Text className="text-[12px] text-slate-500 font-medium">
                      {formatShortDate(week.ngay_bat_dau)} - {formatShortDate(week.ngay_ket_thuc)}
                    </Text>
                    {statusBadge}
                  </div>
                </div>
                <div className="text-[#224397]">
                  <Icon icon="zi-chevron-right" />
                </div>
              </div>
            );
          })
        )}
      </div>
    </Page>
  );
};

export default SelectDutyWeekPage;
