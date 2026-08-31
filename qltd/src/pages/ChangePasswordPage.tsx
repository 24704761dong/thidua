import logoImg from '@/assets/logo_ngang.png';
import React, { useState, useEffect } from 'react';
import { Page, useSnackbar, Spinner } from 'zmp-ui';
import { useNavigate } from 'react-router-dom';
import Header from '@/components/Header';
import { PATHS } from '@/constants/paths';
import { logout } from '@/utils/auth';
import api from '@/lib/api';

const ChangePasswordPage: React.FC = () => {
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();

  const [oldPassword, setOldPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showOldPassword, setShowOldPassword] = useState(false);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const [isLoading, setIsLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(true);
  const [isFirstLogin, setIsFirstLogin] = useState(false);

  useEffect(() => {
    const checkPasswordStatus = async () => {
      try {
        const res = await api.get('/api/zalo/me');
        if (res.data?.success && res.data.data) {
          const profile = res.data.data;
          const mustChange = profile.must_change_password === true;
          setIsFirstLogin(mustChange);
          if (!mustChange) {
            localStorage.removeItem('must_change_password');
          } else {
            localStorage.setItem('must_change_password', 'true');
          }
        }
      } catch (err) {
        const firstLogin = localStorage.getItem('must_change_password') === 'true';
        setIsFirstLogin(firstLogin);
      } finally {
        setInitialLoading(false);
      }
    };

    checkPasswordStatus();
  }, [navigate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!oldPassword.trim() || !newPassword || !confirmPassword) {
      openSnackbar({ text: 'Vui lòng nhập đầy đủ thông tin', type: 'error' });
      return;
    }

    if (newPassword !== confirmPassword) {
      openSnackbar({ text: 'Mật khẩu mới và xác nhận mật khẩu không khớp', type: 'error' });
      return;
    }

    if (newPassword.length < 6) {
      openSnackbar({ text: 'Mật khẩu mới phải có ít nhất 6 ký tự', type: 'error' });
      return;
    }

    if (newPassword === oldPassword) {
      openSnackbar({ text: 'Mật khẩu mới phải khác mật khẩu mặc định hiện tại', type: 'warning' });
      return;
    }

    try {
      setIsLoading(true);
      const response = await api.post('/api/zalo/change-password', {
        old_password: oldPassword.trim(),
        new_password: newPassword,
        confirm_password: confirmPassword
      });

      if (response.data && response.data.success) {
        localStorage.removeItem('must_change_password');
        openSnackbar({ 
          text: isFirstLogin 
            ? 'Đổi mật khẩu thành công! Chào mừng bạn đến với hệ thống.' 
            : 'Đổi mật khẩu thành công!', 
          type: 'success' 
        });
        setOldPassword('');
        setNewPassword('');
        setConfirmPassword('');
        
        // Chuyển về trang trước đó hoặc trang chủ
        if (isFirstLogin) {
          navigate(PATHS.HOME, { replace: true });
        } else {
          navigate(-1);
        }
      } else {
        openSnackbar({ text: response.data?.message || 'Mật khẩu hiện tại không chính xác', type: 'error' });
      }
    } catch (error: any) {
      console.error('Lỗi đổi mật khẩu:', error);
      openSnackbar({ text: error.response?.data?.message || 'Không thể kết nối đến máy chủ', type: 'error' });
    } finally {
      setIsLoading(false);
    }
  };

  const handleLogout = () => {
    logout();
    navigate(PATHS.LOGIN, { replace: true });
  };

  if (initialLoading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-[#f0f6fc]">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page className="min-h-screen bg-[#f0f6fc] flex flex-col items-center justify-center px-4 py-4 relative overflow-y-auto select-none">
      
      {/* Background decoration */}
      <div className="absolute inset-0 bg-gradient-to-b from-[#f2f8fd] via-[#ebf4fc] to-[#e4f0fa] -z-10 pointer-events-none"></div>

      {/* Nếu đổi mật khẩu bình thường từ Settings */}
      {!isFirstLogin && (
        <div className="w-full absolute top-0 left-0 z-40">
          <Header variant="back" title="Đổi mật khẩu" />
        </div>
      )}

      <div className={`w-full max-w-[340px] flex flex-col items-center ${isFirstLogin ? 'mt-[-4vh]' : 'mt-12'}`}>
        
        {/* Logo & Header cho trang đổi mật khẩu lần đầu */}
        {isFirstLogin && (
          <div className="flex flex-col items-center mb-4 w-full text-center">
            <div className="w-40 h-16 flex items-center justify-center mb-1">
              <img src={logoImg} alt="Logo" className="max-w-full max-h-full object-contain drop-shadow-xs" />
            </div>
            <h1 className="text-[17px] font-extrabold uppercase tracking-wide text-[#1e3a8a] m-0 leading-tight">
              TRƯỜNG THPT BÌNH SƠN
            </h1>
            <div className="text-slate-500 mt-0.5 text-[11px] font-bold uppercase tracking-[0.08em]">
              HỆ THỐNG ĐÁNH GIÁ THI ĐUA
            </div>
          </div>
        )}

        {/* Card Đổi Mật Khẩu */}
        <div className="w-full bg-white rounded-[24px] p-4.5 sm:p-5 shadow-[0_4px_24px_rgba(0,0,0,0.04)] border border-slate-100/80 mb-3">
          
          {/* Banner cảnh báo nếu là lần đầu đăng nhập */}
          {isFirstLogin ? (
            <div className="mb-3.5 bg-amber-50/80 border border-amber-200/70 rounded-xl p-3">
              <div className="flex items-start gap-2.5">
                <div className="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                  </svg>
                </div>
                <div>
                  <h4 className="text-[11.5px] font-bold text-amber-900 m-0 uppercase tracking-wide">
                    Đổi mật khẩu lần đầu
                  </h4>
                  <p className="text-[11px] text-amber-800 mt-0.5 leading-relaxed">
                    Để đảm bảo an toàn thông tin, bạn <strong>bắt buộc phải đổi mật khẩu mới</strong> trước khi sử dụng hệ thống.
                  </p>
                </div>
              </div>
            </div>
          ) : (
            <div className="mb-3 text-center">
              <h2 className="text-base font-bold text-[#1e3a8a] m-0">Thay Đổi Mật Khẩu</h2>
              <p className="text-xs text-slate-500 mt-0.5">Cập nhật mật khẩu định kỳ để bảo vệ tài khoản</p>
            </div>
          )}

          {/* Form đổi mật khẩu */}
          <form onSubmit={handleSubmit} className="flex flex-col gap-3">
            
            {/* Field Mật khẩu hiện tại */}
            <div>
              <label className="block text-[12.5px] font-bold mb-1 text-slate-700">
                {isFirstLogin ? 'Mật khẩu hiện tại (Ngày sinh):' : 'Mật khẩu hiện tại:'}
              </label>
              <div className="relative flex items-center">
                <input 
                  type={showOldPassword ? "text" : "password"}
                  placeholder={isFirstLogin ? "Nhập ngày sinh (VD: 20012009)" : "Nhập mật khẩu hiện tại"}
                  value={oldPassword}
                  onChange={(e) => setOldPassword(e.target.value)}
                  className="w-full h-10 pl-3.5 pr-10 bg-[#f8fafc] border border-slate-200 focus:border-[#1e3a8a] focus:bg-white focus:ring-2 focus:ring-[#1e3a8a]/15 rounded-xl transition-all duration-200 text-[13.5px] text-slate-800 placeholder:text-slate-300 outline-none"
                  required
                />
                <button 
                  type="button" 
                  onClick={() => setShowOldPassword(!showOldPassword)}
                  className="absolute right-3 p-1 text-slate-400 hover:text-slate-600 transition flex items-center justify-center"
                >
                  {showOldPassword ? (
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

            {/* Field Mật khẩu mới */}
            <div>
              <label className="block text-[12.5px] font-bold mb-1 text-slate-700">Mật khẩu mới:</label>
              <div className="relative flex items-center">
                <input 
                  type={showNewPassword ? "text" : "password"}
                  placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  className="w-full h-10 pl-3.5 pr-10 bg-[#f8fafc] border border-slate-200 focus:border-[#1e3a8a] focus:bg-white focus:ring-2 focus:ring-[#1e3a8a]/15 rounded-xl transition-all duration-200 text-[13.5px] text-slate-800 placeholder:text-slate-300 outline-none"
                  required
                />
                <button 
                  type="button" 
                  onClick={() => setShowNewPassword(!showNewPassword)}
                  className="absolute right-3 p-1 text-slate-400 hover:text-slate-600 transition flex items-center justify-center"
                >
                  {showNewPassword ? (
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

            {/* Field Xác nhận mật khẩu mới */}
            <div>
              <label className="block text-[12.5px] font-bold mb-1 text-slate-700">Xác nhận mật khẩu mới:</label>
              <div className="relative flex items-center">
                <input 
                  type={showConfirmPassword ? "text" : "password"}
                  placeholder="Nhập lại mật khẩu mới"
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  className="w-full h-10 pl-3.5 pr-10 bg-[#f8fafc] border border-slate-200 focus:border-[#1e3a8a] focus:bg-white focus:ring-2 focus:ring-[#1e3a8a]/15 rounded-xl transition-all duration-200 text-[13.5px] text-slate-800 placeholder:text-slate-300 outline-none"
                  required
                />
                <button 
                  type="button" 
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  className="absolute right-3 p-1 text-slate-400 hover:text-slate-600 transition flex items-center justify-center"
                >
                  {showConfirmPassword ? (
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

            {/* Nút Submit */}
            <button 
              type="submit"
              disabled={isLoading}
              className="w-full h-11 flex items-center justify-center rounded-xl font-bold text-sm text-white bg-[#1e3a8a] hover:bg-[#162d6b] active:scale-[0.98] transition-all shadow-md mt-1 disabled:opacity-70 cursor-pointer"
            >
              {isLoading ? (
                <div className="flex items-center gap-2">
                  <span className="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                  <span className="text-xs">Đang cập nhật...</span>
                </div>
              ) : (
                isFirstLogin ? 'Lưu mật khẩu & Bắt đầu' : 'Cập nhật mật khẩu'
              )}
            </button>

          </form>
        </div>

        {/* Nút Đăng xuất nếu là lần đầu đăng nhập */}
        {isFirstLogin && (
          <div className="text-center mt-1">
            <button 
              type="button" 
              onClick={handleLogout}
              className="text-[12px] text-slate-400 hover:text-red-600 font-semibold transition underline cursor-pointer"
            >
              Đăng xuất tài khoản
            </button>
          </div>
        )}

      </div>
    </Page>
  );
};

export default ChangePasswordPage;
