import logoImg from '@/assets/logo.png';
import { useNavigate } from "react-router-dom";
import React, { useEffect, useState } from 'react';
import { Page, Box, Text, useSnackbar, Icon, Spinner } from "zmp-ui";
import api from '@/lib/api';
import { PATHS } from '@/constants/paths';
import Header from '@/components/Header';

const SelectDiaryWeekPage: React.FC = () => {
  const [weeks, setWeeks] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();

  useEffect(() => {
    const fetchWeeks = async () => {
      try {
        const res = await api.get('/api/zalo/so-nhat-ky-tuan');
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

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'chua_nhap':
        return <span className="px-2 py-1 text-[10px] font-bold rounded-md bg-slate-100 text-slate-500 uppercase">Chưa nhập</span>;
      case 'nhap':
        return <span className="px-2 py-1 text-[10px] font-bold rounded-md bg-orange-100 text-orange-600 uppercase">Đang nháp</span>;
      case 'da_gui':
        return <span className="px-2 py-1 text-[10px] font-bold rounded-md bg-blue-100 text-blue-600 uppercase">Chờ duyệt</span>;
      case 'da_duyet':
        return <span className="px-2 py-1 text-[10px] font-bold rounded-md bg-green-100 text-green-600 uppercase">Đã duyệt</span>;
      case 'tu_choi':
        return <span className="px-2 py-1 text-[10px] font-bold rounded-md bg-red-100 text-red-600 uppercase">Bị từ chối</span>;
      default:
        return null;
    }
  };

  const formatShortDate = (dateStr: string) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}`;
  };

  const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
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
      <Header variant="back" title="Chọn tuần - Sổ nhật kỳ" />
      <div className="px-4 pt-4 pb-32 space-y-3">
        {weeks.length === 0 ? (
          <div className="text-center py-10 text-slate-400">Không có tuần học nào được mở.</div>
        ) : (
          weeks.map((week) => {
            const weekId = week.id || week.tuan_hoc_id;
            const startDate = week.ngay_bat_dau || week.tu_ngay;
            const endDate = week.ngay_ket_thuc || week.den_ngay;
            const status = week.trang_thai || week.trang_thai_nhat_ky;

            return (
              <div
                key={weekId}
                onClick={() => navigate(PATHS.DIARY_INPUT.replace(':id', String(weekId)))}
                className="bg-white px-4 py-2.5 rounded-[12px] shadow-sm border border-[#224397]/25 flex items-center justify-between active:bg-slate-50 transition-colors mx-1 cursor-pointer"
              >
                <div>
                  <Text className="font-bold text-[14px] text-[#224397] mb-0.5 uppercase">{week.ten_tuan}</Text>
                  <div className="flex items-center gap-3">
                    <Text className="text-[12px] text-slate-500 font-medium">
                      {formatShortDate(startDate)} - {formatShortDate(endDate)}
                    </Text>
                    {getStatusBadge(status)}
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

export default SelectDiaryWeekPage;
