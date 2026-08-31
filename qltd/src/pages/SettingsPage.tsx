import logoImg from '@/assets/logo.png';
import { useNavigate } from "react-router-dom";
import React, { useState, useEffect } from 'react';
import { Page, Box, Modal, Button, useSnackbar, Switch, Select, Spinner } from "zmp-ui";
import { Icon } from '@/components/Icon';
import { PATHS } from '@/constants/paths';
import { logout } from '@/utils/auth';
import { useProfile } from '@/features/profile/profile.query';
import { getUserInfo } from 'zmp-sdk';
import logoUrl from '@/assets/logo_ngang.png';
import api from '@/lib/api';
import { useQueryClient } from '@tanstack/react-query';
import { isBiometricAvailable, saveBiometricCredentials, clearBiometricCredentials, authenticateWithBiometric } from '@/lib/biometricAuth';

const defaultAvatar = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%239CA3AF'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z'/%3E%3C/svg%3E";

// Custom Item Component
const SettingItem = ({ title, prefix, suffix, onClick, isLast = false, titleClassName = "text-slate-800" }: any) => (
  <div
    onClick={onClick}
    className="bg-white active:bg-slate-50 cursor-pointer flex items-center px-4"
  >
    {prefix && (
      <div className="mr-3 flex-shrink-0 flex items-center justify-center">
        {prefix}
      </div>
    )}
    <div className={`flex-1 flex items-center justify-between py-3.5 ${!isLast ? 'border-b border-slate-100' : ''}`}>
      <div className="flex flex-col justify-center">
        <div className={`text-[15px] ${titleClassName}`}>{title}</div>
      </div>
      <div className="flex items-center justify-end text-slate-300 ml-2">
        {suffix !== undefined ? suffix : <Icon icon="zi-chevron-right" size={20} />}
      </div>
    </div>
  </div>
);

const SettingsPage: React.FC = () => {
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();
  const { data: profile } = useProfile();

  const [logoutModalVisible, setLogoutModalVisible] = useState(false);
  const [biometricEnabled, setBiometricEnabled] = useState(false);
  const [zaloAvatar, setZaloAvatar] = useState<string | null>(null);
  const [schoolYears, setSchoolYears] = useState<{id: number, ten_nam_hoc: string}[]>([]);
  const [selectedYearId, setSelectedYearId] = useState<string>('');
  const queryClient = useQueryClient();
  const [initialLoading, setInitialLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => setInitialLoading(false), 250);
    return () => clearTimeout(timer);
  }, []);

  useEffect(() => {
    const saved = localStorage.getItem('biometric_enabled');
    if (saved === 'true') {
      setBiometricEnabled(true);
    }

    // Fetch real Zalo avatar
    getUserInfo({}).then(({ userInfo }) => {
      if (userInfo?.avatar) {
        setZaloAvatar(userInfo.avatar);
      }
    }).catch(() => { });

    // Load selected year
    const savedYear = localStorage.getItem('selected_nam_hoc_id') || '';
    setSelectedYearId(savedYear);

    // Fetch school years
    api.get('/api/zalo/get-nam-hoc')
      .then(res => {
        if (res.data?.success) {
          setSchoolYears(res.data.data);
          if (!savedYear && res.data.data.length > 0) {
            const firstId = res.data.data[0].id.toString();
            setSelectedYearId(firstId);
            localStorage.setItem('selected_nam_hoc_id', firstId);
            queryClient.invalidateQueries();
          }
        }
      })
      .catch(console.error);

  }, []);

  const handleBiometricToggle = async (checked: boolean) => {
    if (checked) {
      // Kiểm tra xem thiết bị có hỗ trợ biometric không
      const available = await isBiometricAvailable();
      if (!available) {
        openSnackbar({ text: 'Thiết bị không hỗ trợ đăng nhập sinh trắc học', type: 'error' });
        return;
      }
      // Yêu cầu xác thực một lần để xác nhận
      const authSuccess = await authenticateWithBiometric();
      if (!authSuccess) {
        openSnackbar({ text: 'Xác thực không thành công', type: 'error' });
        return;
      }
      // Lưu token hiện tại cho lần đăng nhập sau
      const token = localStorage.getItem('zalo_jwt_token') || '';
      saveBiometricCredentials('biometric_user', token);
      setBiometricEnabled(true);
      openSnackbar({ text: 'Đã bật đăng nhập bằng sinh trắc học', type: 'success' });
    } else {
      clearBiometricCredentials();
      setBiometricEnabled(false);
      openSnackbar({ text: 'Đã tắt đăng nhập bằng sinh trắc học', type: 'success' });
    }
  };

  const handleLogout = () => {
    logout();
    setLogoutModalVisible(false);
    navigate(PATHS.LOGIN, { replace: true });
    openSnackbar({ text: 'Đã đăng xuất', type: 'success' });
  };

  const handleYearChange = (val: any) => {
    const yearId = val.toString();
    setSelectedYearId(yearId);
    localStorage.setItem('selected_nam_hoc_id', yearId);
    queryClient.invalidateQueries();
    openSnackbar({ text: 'Đã chuyển năm học', type: 'success' });
  };

  // Xác định môi trường chạy (Zalo in-app browser sẽ có chuỗi 'Zalo' trong userAgent)
  const isZaloMiniApp = /Zalo/i.test(navigator.userAgent);

  const displayData = profile?.edit_config?.has_pending_edit && profile?.edit_config?.pending_data
    ? { ...profile.raw_data, ...profile.edit_config.pending_data }
    : profile?.raw_data;

  const studentName = displayData ? `${displayData.ho_dem || ''} ${displayData.ten || ''}`.trim() : 'Học sinh';
  const studentId = displayData?.ma_hoc_sinh || 'Chưa cập nhật';

  const schoolLogo = 'https://c3binhson.edu.vn/thidua/public/assets/img/logoapp.png';
  const serverAvatarUrl = displayData?.avatar_url || displayData?.anh_the_url
    ? (displayData.avatar_url || displayData.anh_the_url)
    : (displayData?.anh_the
        ? (displayData.anh_the.startsWith('http') ? displayData.anh_the : `${import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua'}/public/assets/anh_the/${displayData.anh_the}`)
        : schoolLogo);

  const avatarUrl = serverAvatarUrl;

  if (initialLoading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page className="page bg-transparent flex flex-col hide-scrollbar">
      <div className="flex-1 overflow-y-auto pb-24 relative">

        {/* BLUE HEADER */}
        <div className="bg-[#2563eb] pt-12 pb-16 px-4 flex items-center gap-4 relative z-0">
          <div className="w-16 h-16 rounded-full overflow-hidden border-2 border-white/20 bg-white flex-shrink-0 flex items-center justify-center">
            <img 
              src={avatarUrl} 
              alt="Avatar" 
              referrerPolicy="no-referrer"
              className="w-full h-full object-cover" 
              onError={(e) => { e.currentTarget.src = schoolLogo; }} 
            />
          </div>
          <div className="flex flex-col text-white">
            <div className="text-[18px] font-bold leading-tight">{studentName}</div>
            <div className="text-[13px] opacity-80 mt-1">MSSV: {studentId}</div>
          </div>
        </div>

        {/* MAIN MENU CARD */}
        <div className="mx-4 -mt-8 relative z-10 bg-white rounded-[20px] shadow-sm overflow-hidden border border-slate-100">

          {schoolYears.length > 0 && (
            <div className="bg-white active:bg-slate-50 flex items-center px-4 border-b border-slate-100">
              <div className="mr-3 flex-shrink-0 flex items-center justify-center">
                <Icon icon="zi-calendar" className="text-blue-500 text-[22px]" />
              </div>
              <div className="flex-1 flex items-center justify-between py-2 relative">
                <div className="flex flex-col justify-center">
                  <div className="text-[15px] text-slate-800">Năm học</div>
                </div>
                <div className="flex items-center justify-end">
                  <Select
                    value={selectedYearId}
                    onChange={handleYearChange}
                    closeOnSelect={true}
                    className="border border-slate-300 rounded-md h-[34px] w-[110px] flex items-center text-center font-medium text-slate-700 custom-year-select"
                  >
                    {schoolYears.map(sy => (
                      <Select.Option key={sy.id} value={sy.id.toString()} title={sy.ten_nam_hoc} />
                    ))}
                  </Select>
                </div>
              </div>
            </div>
          )}

          <SettingItem
            title="Thông tin cá nhân"
            prefix={<Icon icon="zi-user" className="text-blue-500 text-[22px]" />}
            onClick={() => navigate(PATHS.PROFILE)}
          />

          <SettingItem
            title="Đổi mật khẩu"
            prefix={<Icon icon="zi-lock" className="text-blue-500 text-[22px]" />}
            onClick={() => navigate(PATHS.CHANGE_PASSWORD)}
          />

          <SettingItem
            title="Điều khoản và chính sách sử dụng"
            prefix={<Icon icon="zi-info-circle" className="text-purple-500 text-[22px]" />}
            onClick={() => navigate(PATHS.TERMS)}
          />

          <SettingItem
            title="Hotline liên hệ"
            prefix={<Icon icon="zi-call" className="text-orange-500 text-[22px]" />}
            suffix={<span className="text-red-500 font-bold text-[15px]">036.256.6146</span>}
            onClick={() => window.location.href = 'tel:0362566146'}
          />

          <SettingItem
            title="Phiên bản ứng dụng"
            prefix={<Icon icon="zi-setting" className="text-slate-500 text-[22px]" />}
            suffix={<span className="text-slate-500 text-[14px]">v1.0.0</span>}
          />

          <SettingItem
            title="Theo dõi Zalo OA trường"
            prefix={<Icon icon="zi-chat" className="text-[#0068ff] text-[22px]" />}
            onClick={() => window.open('https://zalo.me/201393604628267077', '_blank')}
          />

          <SettingItem
            title="Đăng xuất"
            prefix={<Icon icon="zi-leave" className="text-red-500 text-[22px]" />}
            onClick={() => setLogoutModalVisible(true)}
            isLast={true}
          />

        </div>

      </div>

      <Modal
        visible={logoutModalVisible}
        title="Xác nhận đăng xuất"
        onClose={() => setLogoutModalVisible(false)}
        description="Bạn có chắc chắn muốn đăng xuất khỏi ứng dụng không?"
      >
        <div className="flex flex-row justify-center gap-3 px-4 pb-4 mt-6">
          <Button variant="secondary" size="small" className="flex-1" onClick={() => setLogoutModalVisible(false)}>
            Hủy
          </Button>
          <Button variant="secondary" size="small" className="flex-1 !bg-red-500 !text-white !border-red-500" onClick={handleLogout}>
            Đăng xuất
          </Button>
        </div>
      </Modal>
    </Page>
  );
};

export default SettingsPage;
