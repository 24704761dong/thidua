import React, { useState, useEffect } from 'react';
import { Page, useSnackbar } from 'zmp-ui';
import { useNavigate } from 'react-router-dom';
import { getPhoneNumber, getUserInfo, getAccessToken } from 'zmp-sdk';
import api from '@/lib/api';
import { PATHS } from '@/constants/paths';
import logoImg from '@/assets/logo_ngang.png';
import zaloIcon from '@/assets/zalo.svg';
import { initPushNotifications } from '@/lib/pushNotifications';
import { authenticateWithBiometric, getBiometricCredentials, saveBiometricCredentials, isBiometricAvailable } from '@/lib/biometricAuth';

const LoginPage: React.FC = () => {
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();
  
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [showFaqModal, setShowFaqModal] = useState(false);
  const [showGuideModal, setShowGuideModal] = useState(false);

  // Kiểm tra đăng nhập tự động (Silent login hoặc Token cũ)
  useEffect(() => {
    const checkExistingAuth = async () => {
      // 1. Kiểm tra JWT token hiện có
      const token = localStorage.getItem('zalo_jwt_token');
      if (token) {
        try {
          const res = await api.get('/api/zalo/get-profile');
          if (res.data?.success) {
            navigate(PATHS.HOME, { replace: true });
            return;
          }
        } catch (e) {
          localStorage.removeItem('zalo_jwt_token');
        }
      }

      // 2. Thử Silent Auto-Login nếu học sinh đã liên kết Zalo ID trước đó
      try {
        const { userInfo } = await getUserInfo({});
        if (userInfo?.id) {
          const res = await api.post('/api/zalo/login', {
            zalo_id: userInfo.id
          });
          if (res.data?.success && res.data.token) {
            localStorage.setItem('zalo_jwt_token', res.data.token);
            initPushNotifications();
            
            try {
              const nhRes = await api.get('/api/zalo/get-nam-hoc');
              if (nhRes.data?.success && nhRes.data.data.length > 0) {
                localStorage.setItem('selected_nam_hoc_id', nhRes.data.data[0].id.toString());
              }
            } catch (err) {
              console.error('Lỗi lấy năm học', err);
            }

            openSnackbar({ text: `Chào mừng ${res.data.user?.name || ''} quay trở lại!`, type: 'success' });
            navigate(PATHS.HOME, { replace: true });
            return;
          }
        }
      } catch (e) {
        // Môi trường Web browser hoặc chưa cấp quyền, ở lại trang Login
      }

      // 3. Kiểm tra biometric nếu có bật
      const enabled = localStorage.getItem('biometric_enabled') === 'true';
      const creds = getBiometricCredentials();
      const available = await isBiometricAvailable();
      if (available && enabled && creds) {
        handleBiometricLogin();
      }
    };

    checkExistingAuth();
  }, []);

  // Hàm đăng nhập bằng vân tay
  const handleBiometricLogin = async () => {
    const creds = getBiometricCredentials();
    if (!creds) return;

    const success = await authenticateWithBiometric();
    if (success) {
      localStorage.setItem('zalo_jwt_token', creds.token);
      initPushNotifications();
      openSnackbar({ text: 'Đăng nhập sinh trắc học thành công!', type: 'success' });
      navigate(PATHS.HOME);
    }
  };

  // Hàm xử lý đăng nhập Zalo (Lấy SĐT chính thức qua Zalo SDK)
  const handleZaloLogin = async () => {
    try {
      setIsLoading(true);

      // 1. Lấy user access token
      let accessToken = '';
      try {
        accessToken = await getAccessToken({});
      } catch (err) {
        console.warn('Lỗi getAccessToken (có thể do đang chạy trên Web browser):', err);
      }

      // 2. Gọi popup xin cấp quyền số điện thoại từ Zalo SDK
      getPhoneNumber({
        success: async (phoneData) => {
          let userInfo: any = null;
          try {
            const userRes = await getUserInfo({});
            userInfo = userRes.userInfo;
          } catch (e) {
            console.warn('Lỗi getUserInfo:', e);
          }

          // Nếu đang chạy trên máy tính (localhost) mà token Zalo không có thật
          if ((!accessToken || accessToken === 'dummy_access_token' || !phoneData.token) && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
            const testPhone = window.prompt('Bạn đang test trên Trình duyệt Web máy tính (ngoài ứng dụng Zalo trên điện thoại).\n\nVui lòng nhập số điện thoại học sinh bạn vừa cấu hình để test:');
            if (!testPhone) {
              setIsLoading(false);
              return;
            }
            try {
              const res = await api.post('/api/zalo/login', {
                phone: testPhone.trim(),
                zalo_id: userInfo?.id
              });
              if (res.data?.success) {
                localStorage.setItem('zalo_jwt_token', res.data.token);
                initPushNotifications();
                openSnackbar({ text: 'Đăng nhập test thành công!', type: 'success' });
                navigate(PATHS.HOME);
                return;
              } else {
                openSnackbar({ text: res.data?.message || 'Không tìm thấy số điện thoại', type: 'error' });
                return;
              }
            } catch (e: any) {
              openSnackbar({ text: e.response?.data?.message || 'Lỗi kết nối máy chủ!', type: 'error' });
              return;
            } finally {
              setIsLoading(false);
            }
          }

          try {
            const res = await api.post('/api/zalo/login', {
              phone_token: phoneData.token,
              access_token: accessToken,
              zalo_id: userInfo?.id,
              zalo_name: userInfo?.name,
              zalo_avatar: userInfo?.avatar
            });

            if (res.data && res.data.success) {
              localStorage.setItem('zalo_jwt_token', res.data.token);
              initPushNotifications();

              try {
                const nhRes = await api.get('/api/zalo/get-nam-hoc');
                if (nhRes.data?.success && nhRes.data.data.length > 0) {
                  localStorage.setItem('selected_nam_hoc_id', nhRes.data.data[0].id.toString());
                }
              } catch (err) {
                console.error('Lỗi lấy năm học', err);
              }

              openSnackbar({ text: 'Đăng nhập Zalo thành công!', type: 'success' });
              navigate(PATHS.HOME);
            } else {
              // Nếu không giải mã được SĐT (do đang chạy trên trình duyệt web máy tính)
              if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                const testPhone = window.prompt('Trình duyệt máy tính chưa có ứng dụng Zalo để lấy SĐT thật.\n\nNhập số điện thoại học sinh bạn vừa cấu hình để test đăng nhập:');
                if (testPhone) {
                  const retryRes = await api.post('/api/zalo/login', { phone: testPhone.trim(), zalo_id: userInfo?.id });
                  if (retryRes.data?.success) {
                    localStorage.setItem('zalo_jwt_token', retryRes.data.token);
                    initPushNotifications();
                    openSnackbar({ text: 'Đăng nhập test thành công!', type: 'success' });
                    navigate(PATHS.HOME);
                    return;
                  } else {
                    openSnackbar({ text: retryRes.data?.message || 'Không tìm thấy số điện thoại', type: 'error' });
                    return;
                  }
                }
              }
              openSnackbar({ text: res.data?.message || 'Đăng nhập Zalo thất bại', type: 'error' });
            }
          } catch (e: any) {
            openSnackbar({ text: e.response?.data?.message || 'Lỗi kết nối máy chủ!', type: 'error' });
          } finally {
            setIsLoading(false);
          }
        },
        fail: (error) => {
          setIsLoading(false);
          if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            const testPhone = window.prompt('Trình duyệt máy tính không có Zalo để cấp quyền SĐT tự động.\n\nNhập số điện thoại học sinh bạn vừa cấu hình để test đăng nhập:');
            if (testPhone) {
              setIsLoading(true);
              api.post('/api/zalo/login', { phone: testPhone.trim() })
                .then((res) => {
                  if (res.data?.success) {
                    localStorage.setItem('zalo_jwt_token', res.data.token);
                    initPushNotifications();
                    openSnackbar({ text: 'Đăng nhập test thành công!', type: 'success' });
                    navigate(PATHS.HOME);
                  } else {
                    openSnackbar({ text: res.data?.message || 'Không tìm thấy số điện thoại', type: 'error' });
                  }
                })
                .catch((err) => {
                  openSnackbar({ text: err.response?.data?.message || 'Lỗi kết nối máy chủ!', type: 'error' });
                })
                .finally(() => setIsLoading(false));
              return;
            }
          }
          openSnackbar({ text: 'Bạn đã từ chối cấp quyền hoặc cần mở trên ứng dụng Zalo điện thoại.', type: 'warning' });
        }
      });
    } catch (error) {
      setIsLoading(false);
      openSnackbar({ text: 'Lỗi khởi tạo đăng nhập Zalo.', type: 'error' });
    }
  };

  // Hàm xử lý đăng nhập tài khoản / mật khẩu
  const handleLogin = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();

    if (!username.trim() || !password) {
      openSnackbar({ text: 'Vui lòng nhập tài khoản và mật khẩu', type: 'warning' });
      return;
    }

    try {
      setIsLoading(true);

      // Lấy zalo_id nếu đang trong Zalo App để tự động liên kết vĩnh viễn
      let zaloId = '';
      try {
        const { userInfo } = await getUserInfo({});
        if (userInfo?.id) zaloId = userInfo.id;
      } catch (e) {
        // Bỏ qua nếu ngoài môi trường Zalo
      }

      const res = await api.post('/api/zalo/login-fallback', {
        username: username.trim(),
        password,
        zalo_id: zaloId
      });

      if (res.data && res.data.success) {
        localStorage.setItem('zalo_jwt_token', res.data.token);
        saveBiometricCredentials(username.trim(), res.data.token);
        initPushNotifications();
        
        // Lấy danh sách năm học
        try {
          const nhRes = await api.get('/api/zalo/get-nam-hoc');
          if (nhRes.data?.success && nhRes.data.data.length > 0) {
            localStorage.setItem('selected_nam_hoc_id', nhRes.data.data[0].id.toString());
          }
        } catch (err) {
          console.error('Lỗi lấy năm học', err);
        }

        // Bắt buộc đổi mật khẩu nếu là lần đầu đăng nhập
        if (res.data.must_change_password) {
          localStorage.setItem('must_change_password', 'true');
          openSnackbar({ text: 'Vui lòng đổi mật khẩu cho lần đầu đăng nhập!', type: 'info' });
          navigate(PATHS.CHANGE_PASSWORD, { replace: true });
          return;
        } else {
          localStorage.removeItem('must_change_password');
        }

        openSnackbar({ text: 'Đăng nhập thành công!', type: 'success' });
        navigate(PATHS.HOME);
      } else {
        openSnackbar({ text: res.data?.message || 'Tài khoản hoặc mật khẩu không chính xác', type: 'error' });
      }
    } catch (error: any) {
      console.error(error);
      openSnackbar({ text: error.response?.data?.message || 'Lỗi kết nối máy chủ!', type: 'error' });
    } finally {
      setIsLoading(false);
    }
  };

  const handleForgotPassword = () => {
    openSnackbar({ 
      text: 'Vui lòng liên hệ Giáo viên chủ nhiệm hoặc Quản trị viên nhà trường để được cấp lại mật khẩu.', 
      type: 'info',
      duration: 5000
    });
  };

  const handleCallHotline = () => {
    window.location.href = 'tel:0362566146';
  };

  return (
    <Page className="min-h-screen bg-[#f0f6fc] flex flex-col items-center justify-center px-4 py-4 relative overflow-y-auto select-none">
      
      {/* Background decoration */}
      <div className="absolute inset-0 bg-gradient-to-b from-[#f2f8fd] via-[#ebf4fc] to-[#e4f0fa] -z-10 pointer-events-none"></div>

      <div className="w-full max-w-[340px] flex flex-col items-center mt-[-6vh]">
        
        {/* Logo & Header */}
        <div className="flex flex-col items-center mb-5 w-full text-center">
          <div className="w-44 h-18 flex items-center justify-center mb-1.5">
            <img src={logoImg} alt="Logo" className="max-w-full max-h-full object-contain drop-shadow-xs" />
          </div>
          <h1 className="text-[18px] font-extrabold uppercase tracking-wide text-[#1e3a8a] m-0 leading-tight">
            TRƯỜNG THPT BÌNH SƠN
          </h1>
          <div className="text-slate-500 mt-1 text-[11.5px] font-bold uppercase tracking-[0.08em]">
            HỆ THỐNG ĐÁNH GIÁ THI ĐUA
          </div>
        </div>

        {/* Card Form Đăng nhập */}
        <div className="w-full bg-white rounded-[24px] p-4 sm:p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100/80 mb-2.5">
          <form onSubmit={handleLogin} className="flex flex-col gap-3">
            
            {/* Field Tài khoản */}
            <div>
              <label className="block text-[12.5px] font-bold mb-1 text-slate-700">Tài khoản:</label>
              <input 
                type="text"
                placeholder="Nhập tài khoản"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                autoCapitalize="none"
                autoCorrect="off"
                className="w-full h-10 px-3.5 bg-[#f8fafc] border border-slate-200 focus:border-[#1e3a8a] focus:bg-white focus:ring-2 focus:ring-[#1e3a8a]/15 rounded-xl transition-all duration-200 text-[13.5px] text-slate-800 placeholder:text-slate-300 outline-none"
              />
            </div>

            {/* Field Mật khẩu */}
            <div>
              <label className="block text-[12.5px] font-bold mb-1 text-slate-700">Mật khẩu:</label>
              <div className="relative flex items-center">
                <input 
                  type={showPassword ? "text" : "password"}
                  placeholder="Nhập mật khẩu"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full h-10 pl-3.5 pr-10 bg-[#f8fafc] border border-slate-200 focus:border-[#1e3a8a] focus:bg-white focus:ring-2 focus:ring-[#1e3a8a]/15 rounded-xl transition-all duration-200 text-[13.5px] text-slate-800 placeholder:text-slate-300 outline-none"
                />
                <button 
                  type="button" 
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 p-1 text-slate-400 hover:text-slate-600 transition flex items-center justify-center"
                >
                  {showPassword ? (
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/>
                      <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-5.657-5.657a.5.5 0 0 0-.708.708l5.657 5.657a.5.5 0 0 0 .708-.708"/>
                    </svg>
                  ) : (
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-8.3 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                      <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                    </svg>
                  )}
                </button>
              </div>
            </div>

            {/* Quên mật khẩu */}
            <div className="flex justify-end -mt-1">
              <button 
                type="button" 
                onClick={handleForgotPassword}
                className="text-[11.5px] font-bold text-[#1e3a8a] hover:underline"
              >
                Quên mật khẩu?
              </button>
            </div>

            {/* Nút Đăng nhập */}
            <button 
              type="submit"
              disabled={isLoading}
              className="w-full h-10 flex items-center justify-center rounded-xl font-bold text-sm text-white bg-[#1e3a8a] hover:bg-[#162d6b] active:scale-[0.98] transition-all shadow-sm disabled:opacity-70 disabled:scale-100 cursor-pointer"
            >
              {isLoading ? (
                <div className="flex items-center gap-2">
                  <span className="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                  <span className="text-xs">Đang xử lý...</span>
                </div>
              ) : (
                'Đăng nhập'
              )}
            </button>

            {/* Divider */}
            <div className="flex items-center gap-2 my-0">
              <div className="h-[1px] bg-slate-200 flex-1"></div>
              <span className="text-slate-400 text-[9.5px] font-bold uppercase tracking-wider">HOẶC</span>
              <div className="h-[1px] bg-slate-200 flex-1"></div>
            </div>

            {/* Nút Đăng nhập Zalo */}
            <button 
              type="button"
              onClick={handleZaloLogin} 
              disabled={isLoading}
              className="w-full h-10 flex items-center justify-center gap-2 rounded-xl bg-white border border-[#0068FF] text-[#0068FF] hover:bg-[#0068FF]/5 active:scale-[0.98] transition-all font-bold text-[12.5px] disabled:opacity-70 cursor-pointer shadow-xs"
            >
              <img src={zaloIcon} alt="Zalo" className="w-4.5 h-4.5 object-contain" />
              <span>Đăng nhập với Zalo</span>
            </button>

          </form>
        </div>

        {/* Divider: Thông tin & Hỗ trợ */}
        <div className="flex items-center gap-2.5 w-full mb-2">
          <div className="h-[1px] bg-slate-200/90 flex-1"></div>
          <span className="text-slate-400 text-[10.5px] font-medium">Thông tin & Hỗ trợ</span>
          <div className="h-[1px] bg-slate-200/90 flex-1"></div>
        </div>

        {/* 3 Action Cards */}
        <div className="grid grid-cols-3 gap-2.5 w-full mb-3">
          
          {/* Card 1: Hướng dẫn sử dụng */}
          <div 
            onClick={() => setShowGuideModal(true)}
            className="bg-white rounded-2xl p-2.5 border border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.03)] flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-200 active:scale-95 transition-all"
          >
            <div className="w-7 h-7 flex items-center justify-center text-[#1e3a8a]">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
              </svg>
            </div>
            <div className="text-[10.5px] font-bold text-slate-700 leading-tight mt-1">
              Hướng dẫn<br/>sử dụng
            </div>
          </div>

          {/* Card 2: Câu hỏi thường gặp */}
          <div 
            onClick={() => setShowFaqModal(true)}
            className="bg-white rounded-2xl p-2.5 border border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.03)] flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-200 active:scale-95 transition-all"
          >
            <div className="w-7 h-7 flex items-center justify-center text-[#1e3a8a]">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                <path d="M5 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
              </svg>
            </div>
            <div className="text-[10.5px] font-bold text-slate-700 leading-tight mt-1">
              Câu hỏi<br/>thường gặp
            </div>
          </div>

          {/* Card 3: Hotline hỗ trợ */}
          <div 
            onClick={handleCallHotline}
            className="bg-white rounded-2xl p-2.5 border border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.03)] flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-200 active:scale-95 transition-all"
          >
            <div className="w-7 h-7 flex items-center justify-center text-[#1e3a8a]">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58z"/>
              </svg>
            </div>
            <div className="text-[10.5px] font-bold text-slate-700 leading-tight mt-1">
              Hotline<br/>hỗ trợ
            </div>
          </div>

        </div>

        {/* Privacy Policy & Version */}
        <div className="flex flex-col items-center gap-1 text-center mt-1">
          <button 
            type="button" 
            onClick={() => navigate(PATHS.TERMS)}
            className="flex items-center gap-1 text-[11.5px] font-semibold text-[#1e3a8a] hover:underline cursor-pointer"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
              <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
            </svg>
            <span>Chính sách quyền riêng tư</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="currentColor" viewBox="0 0 16 16">
              <path fillRule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
            </svg>
          </button>
          <span className="text-slate-400 text-[10.5px] font-medium">
            Phiên bản 2.2.9
          </span>
        </div>

      </div>

      {/* Modal Hướng Dẫn Sử Dụng */}
      {showGuideModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
          <div className="bg-white rounded-2xl w-full max-w-sm p-4.5 shadow-2xl flex flex-col max-h-[80vh]">
            <div className="flex justify-between items-center pb-2.5 border-b border-slate-100">
              <h3 className="font-bold text-sm text-[#1e3a8a]">Hướng Dẫn Sử Dụng</h3>
              <button onClick={() => setShowGuideModal(false)} className="text-slate-400 hover:text-slate-600 p-1">
                ✕
              </button>
            </div>
            <div className="overflow-y-auto py-3 text-xs text-slate-600 space-y-2.5 leading-relaxed">
              <p><strong>1. Đăng nhập:</strong> Sử dụng tài khoản được nhà trường cung cấp hoặc nhấn "Đăng nhập với Zalo" để xác thực nhanh.</p>
              <p><strong>2. Tra cứu nề nếp & điểm:</strong> Xem kết quả thi đua tuần, vi phạm, lịch thi và điểm thi trực tiếp trên ứng dụng.</p>
              <p><strong>3. Gửi đơn nghỉ phép:</strong> Học sinh/Phụ huynh có thể nộp đơn xin nghỉ phép trực tuyến gửi đến GVCN.</p>
            </div>
            <div className="pt-2.5 border-t border-slate-100 flex justify-end">
              <button 
                onClick={() => setShowGuideModal(false)}
                className="px-4 py-1.5 bg-[#1e3a8a] text-white text-xs font-bold rounded-lg"
              >
                Đóng
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal Câu Hỏi Thường Gặp */}
      {showFaqModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
          <div className="bg-white rounded-2xl w-full max-w-sm p-4.5 shadow-2xl flex flex-col max-h-[80vh]">
            <div className="flex justify-between items-center pb-2.5 border-b border-slate-100">
              <h3 className="font-bold text-sm text-[#1e3a8a]">Câu Hỏi Thường Gặp</h3>
              <button onClick={() => setShowFaqModal(false)} className="text-slate-400 hover:text-slate-600 p-1">
                ✕
              </button>
            </div>
            <div className="overflow-y-auto py-3 text-xs text-slate-600 space-y-2.5 leading-relaxed">
              <div>
                <p className="font-bold text-slate-800">Q: Làm sao để đổi mật khẩu?</p>
                <p className="text-slate-500">A: Sau khi đăng nhập, vào mục Cá nhân ➔ Chọn Đổi mật khẩu.</p>
              </div>
              <div>
                <p className="font-bold text-slate-800">Q: Quên mật khẩu thì làm thế nào?</p>
                <p className="text-slate-500">A: Liên hệ GVCN hoặc bấm Hotline để được hỗ trợ đặt lại mật khẩu.</p>
              </div>
              <div>
                <p className="font-bold text-slate-800">Q: Đăng nhập Zalo có bị lộ thông tin không?</p>
                <p className="text-slate-500">A: Toàn bộ thông tin được bảo mật theo tiêu chuẩn an toàn thông tin của nhà trường và nền tảng Zalo.</p>
              </div>
            </div>
            <div className="pt-2.5 border-t border-slate-100 flex justify-end">
              <button 
                onClick={() => setShowFaqModal(false)}
                className="px-4 py-1.5 bg-[#1e3a8a] text-white text-xs font-bold rounded-lg"
              >
                Đóng
              </button>
            </div>
          </div>
        </div>
      )}

    </Page>
  );
};

export default LoginPage;
