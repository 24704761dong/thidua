import React, { useState, useEffect } from 'react';
import { Page, Header, Box, Text, Button, Icon, Spinner, useSnackbar } from 'zmp-ui';
import api from '@/lib/api';

interface EmailData {
  trang_thai: 'cho_duyet' | 'da_cap' | 'da_khoa';
  email: string | null;
  mat_khau: string | null;
  error_message: string | null;
}

const EmailPage: React.FC = () => {
  const [loading, setLoading] = useState(true);
  const [allowRequest, setAllowRequest] = useState(true);
  const [emailData, setEmailData] = useState<EmailData | null>(null);
  const [requesting, setRequesting] = useState(false);
  const { openSnackbar } = useSnackbar();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const res = await api.get('/api/zalo/email-hoc-sinh');
      if (res.data?.success) {
        setAllowRequest(res.data.allow_request);
        setEmailData(res.data.data);
      }
    } catch (error) {
      openSnackbar({ text: 'Lỗi tải dữ liệu', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleRequestEmail = async () => {
    try {
      setRequesting(true);
      const res = await api.post('/api/zalo/email-hoc-sinh', { action: 'request' });
      if (res.data?.success) {
        openSnackbar({ text: 'Đăng ký thành công', type: 'success' });
        fetchData();
      } else {
        openSnackbar({ text: res.data?.message || 'Lỗi đăng ký', type: 'error' });
      }
    } catch (error) {
      openSnackbar({ text: 'Có lỗi xảy ra', type: 'error' });
    } finally {
      setRequesting(false);
    }
  };

  return (
    <Page className="bg-slate-50 relative pb-20 flex flex-col">
      <Header
        title="Email Học sinh"
        className="app-header"
        showBackIcon={true}
      />

      {loading ? (
        <div className="flex justify-center mt-10"><Spinner visible /></div>
      ) : (
        <Box p={4} className="flex-1 flex flex-col items-center mt-6 space-y-6">
          <div className="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-[#224397] shadow">
            <Icon icon="zi-mail" size={40} />
          </div>

          <div className="w-full bg-white rounded-xl shadow-sm border border-slate-100 p-5 text-center">
            <Text className="text-lg font-bold text-slate-800 mb-2">Tài khoản Microsoft 365</Text>
            <Text className="text-sm text-slate-500 mb-4">
              Mỗi học sinh được cấp 1 tài khoản email edu duy nhất trong suốt quá trình học tập.
            </Text>

            {!allowRequest && !emailData && (
              <div className="p-3 bg-rose-50 text-rose-600 rounded-lg text-sm font-semibold border border-rose-200">
                Nhà trường đang tạm đóng chức năng đăng ký email.
              </div>
            )}

            {allowRequest && !emailData && (
              <Button
                onClick={handleRequestEmail}
                loading={requesting}
                className="w-full bg-[#224397] rounded-xl text-white font-bold py-3 mt-2"
              >
                Đăng ký nhận mail học sinh
              </Button>
            )}

            {emailData && emailData.trang_thai === 'cho_duyet' && (
              <div className="p-3 bg-amber-50 text-amber-700 rounded-lg text-sm font-semibold border border-amber-200 flex flex-col items-center">
                <Icon icon="zi-clock-1" className="mb-1" />
                Yêu cầu của bạn đang được xử lý...
              </div>
            )}

            {emailData && emailData.trang_thai === 'da_khoa' && (
              <div className="p-3 bg-rose-50 text-rose-600 rounded-lg text-sm font-semibold border border-rose-200 flex flex-col items-center">
                <Icon icon="zi-lock" className="mb-1" />
                Tài khoản email của bạn đã bị khóa.
              </div>
            )}

            {emailData && emailData.trang_thai === 'da_cap' && (
              <div className="text-left mt-4 border-t pt-4">
                <div className="mb-4">
                  <Text className="text-xs font-bold text-slate-400 mb-1 uppercase">Email của bạn</Text>
                  <div className="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-medium flex justify-between items-center">
                    <span className="select-all truncate">{emailData.email}</span>
                    <div className="ml-2 cursor-pointer flex-shrink-0" onClick={() => {
                        navigator.clipboard.writeText(emailData.email);
                        openSnackbar({ text: 'Đã sao chép Email', type: 'success' });
                    }}>
                        <Icon icon="zi-copy" className="text-blue-500" />
                    </div>
                  </div>
                </div>
                <div>
                  <Text className="text-xs font-bold text-slate-400 mb-1 uppercase">Mật khẩu (Khởi tạo)</Text>
                  <div className="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-mono tracking-wider flex justify-between items-center">
                    <span className="select-all truncate">{emailData.mat_khau}</span>
                    <div className="ml-2 cursor-pointer flex-shrink-0" onClick={() => {
                        navigator.clipboard.writeText(emailData.mat_khau);
                        openSnackbar({ text: 'Đã sao chép mật khẩu', type: 'success' });
                    }}>
                        <Icon icon="zi-copy" className="text-blue-500" />
                    </div>
                  </div>
                  <Text className="text-xs text-rose-500 mt-2 italic">
                    * Bạn hãy đăng nhập tại https://outlook.cloud.microsoft/c3binhson.edu.vn và đổi mật khẩu trong lần đầu tiên.
                  </Text>
                </div>
              </div>
            )}
          </div>
        </Box>
      )}
    </Page>
  );
};

export default EmailPage;
