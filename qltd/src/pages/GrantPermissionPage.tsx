import logoImg from '@/assets/logo.png';
import { useNavigate } from "react-router-dom";
import React, { useState, useEffect } from 'react';
import { Page, Box, Text, Input, Button, useSnackbar, Spinner } from "zmp-ui";
import Header from '@/components/Header';
import { navigateBack } from '@/utils/navigation';
import api from '@/lib/api';

const GrantPermissionPage: React.FC = () => {
  const [code, setCode] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [history, setHistory] = useState<any[]>([]);
  const [initialLoading, setInitialLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => setInitialLoading(false), 250);
    return () => clearTimeout(timer);
  }, []);
  const { openSnackbar } = useSnackbar();
  const navigate = useNavigate();

  const fetchHistory = async () => {
    try {
      const res = await api.get('/api/zalo/lich-su-kich-hoat-quyen');
      if (res.data.success) {
        setHistory(res.data.data);
      }
    } catch (e) {
      console.error(e);
    }
  };

  useEffect(() => {
    fetchHistory();
    localStorage.removeItem('grant_permission_attempts');
  }, []);

  const checkRateLimit = () => {
    localStorage.removeItem('grant_permission_attempts');
    return true;
  };

  const recordFailure = () => {
    // Không khóa người dùng khi đang test
    localStorage.removeItem('grant_permission_attempts');
  };

  const handleActivate = async () => {
    if (!checkRateLimit()) return;

    if (!code.trim()) {
      openSnackbar({ text: 'Vui lòng nhập mã kích hoạt', type: 'warning' });
      return;
    }

    setIsLoading(true);
    try {
      const res = await api.post('/api/zalo/kich-hoat-quyen', {
        ma_kich_hoat: code.trim()
      });

      if (res.data.success) {
        localStorage.removeItem('grant_permission_attempts');
        openSnackbar({ text: 'Kích hoạt thành công!', type: 'success' });

        // Notify the app to refresh profile (permissions have updated)
        window.dispatchEvent(new Event('profile_updated_event'));

        fetchHistory();
        setCode('');
      } else {
        recordFailure();
        openSnackbar({ text: res.data.message || 'Có lỗi xảy ra', type: 'error' });
      }
    } catch (error: any) {
      recordFailure();
      openSnackbar({ text: error.response?.data?.message || 'Không thể kết nối đến máy chủ', type: 'error' });
    } finally {
      setIsLoading(false);
    }
  };

  if (initialLoading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page className="bg-transparent relative flex flex-col h-screen">
      <Header variant="back" title="Cấp quyền truy cập" />
      <div className="flex-1 flex flex-col items-center pt-6 px-4 pb-24">
        <div className="bg-white w-full max-w-sm rounded-3xl shadow-[0_8px_30px_rgba(34,67,151,0.08)] p-7 flex flex-col items-center border border-[#224397]/5 relative overflow-hidden">

          <div className="w-16 h-16 bg-[#224397]/5 rounded-[14px] border border-[#224397]/25 flex items-center justify-center mb-6 text-[#224397] relative z-10 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" width="30" height="30">
              <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
          </div>

          <Text.Title className="text-[22px] text-slate-800 text-center mb-2 font-extrabold relative z-10">Nhập mã kích hoạt</Text.Title>
          <Text className="text-[14px] text-slate-500 text-center mb-8 px-1 leading-relaxed relative z-10">
            Vui lòng nhập <strong className="text-slate-700 font-semibold">mã gồm 6 chữ số</strong> do nhà trường cung cấp để mở khóa các tính năng nội bộ.
          </Text>

          <div className="w-full mb-6 relative z-10">
            <Input
              placeholder="Ví dụ: 123456"
              value={code}
              onChange={(e) => setCode(e.target.value)}
              type="number"
              className="text-center text-2xl tracking-[0.25em] font-extrabold !h-[60px] !bg-slate-50 !border-slate-200 focus:!border-[#224397] focus:!bg-white !rounded-[12px] transition-all"
              maxLength={6}
            />
          </div>

          <div className="flex w-full gap-3 relative z-10">
            <Button
              variant="secondary"
              onClick={() => navigateBack(navigate)}
              className="!h-14 !bg-slate-100 hover:!bg-slate-200 !text-slate-600 !rounded-[10px] text-[15px] font-bold shadow-sm active:scale-[0.98] transition-all flex-[1]"
            >
              Quay Lại
            </Button>
            <Button
              onClick={handleActivate}
              loading={isLoading}
              className="!bg-[#224397] !text-white !h-14 !rounded-[10px] text-[15px] font-bold shadow-md shadow-[#224397]/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 flex-[1]"
            >
              Xác nhận
            </Button>
          </div>
        </div>

        <div className="mt-8 flex items-center gap-2 text-slate-400 text-[13px] bg-white/60 px-4 py-2 rounded-full border border-slate-200/60 shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
          </svg>
          <span className="font-medium tracking-wide">Bảo mật thông tin</span>
        </div>

        {history.length > 0 && (
          <div className="w-full mt-10 pb-10 max-w-sm">
            <h3 className="text-sm font-bold text-slate-700 uppercase mb-4 px-2">Danh sách mã đã kích hoạt</h3>
            <div className="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
              <table className="w-full text-left text-sm text-slate-600">
                <thead className="bg-slate-50 text-slate-500 text-xs uppercase border-b border-slate-200/60">
                  <tr>
                    <th className="px-4 py-3 font-semibold text-center w-12">STT</th>
                    <th className="px-4 py-3 font-semibold">Mã</th>
                    <th className="px-4 py-3 font-semibold text-center">Trạng thái</th>
                    <th className="px-4 py-3 font-semibold text-right">Thời gian</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {history.map((item, index) => {
                    const isExpired = item.trang_thai === 'inactive' || (item.thoi_gian_het_han && new Date(item.thoi_gian_het_han).getTime() < new Date().getTime());
                    return (
                      <tr key={item.id} className="hover:bg-slate-50/50 transition-colors">
                        <td className="px-4 py-3 text-center text-slate-400">{index + 1}</td>
                        <td className="px-4 py-3 font-medium text-[#224397] tracking-wider">{item.ma_kich_hoat}</td>
                        <td className="px-4 py-3 text-center">
                          {isExpired ? (
                            <span className="inline-block px-2 py-1 bg-red-50 text-red-600 text-[11px] font-semibold rounded-md">Hết hạn</span>
                          ) : (
                            <span className="inline-block px-2 py-1 bg-green-50 text-green-600 text-[11px] font-semibold rounded-md">Hoạt động</span>
                          )}
                        </td>
                        <td className="px-4 py-3 text-right text-slate-500 text-[13px]">{new Date(item.ngay_kich_hoat).toLocaleString('vi-VN', {
                          day: '2-digit', month: '2-digit', year: 'numeric',
                          hour: '2-digit', minute: '2-digit'
                        })}</td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </Page>
  );
};

export default GrantPermissionPage;
