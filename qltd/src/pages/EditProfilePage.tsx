import logoImg from '@/assets/logo.png';
import React, { useState, useEffect, useRef } from 'react';
import { Page, useSnackbar, Modal, Spinner } from 'zmp-ui';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import { useProfile } from '@/features/profile/profile.query';
import { useNavigate } from 'react-router-dom';
import api from '@/lib/api';

const formatDob = (dob?: string) => {
  if (!dob) return '';
  if (dob.includes('/')) return dob;
  const parts = dob.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return dob;
};

const inputBaseClass = "w-full h-10 px-3 rounded-xl text-xs outline-none transition-all box-border";
const inputReadonlyClass = "bg-[#f8fafc] border border-slate-200/80 text-slate-500 font-medium cursor-default";
const inputEditableClass = "bg-white border border-[#1e3a8a]/50 focus:border-[#1e3a8a] text-slate-800 font-semibold shadow-2xs";

const EditProfilePage: React.FC = () => {
  const { data: profile, refetch } = useProfile();
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();

  const [formData, setFormData] = useState<any>({});
  const [avatarFile, setAvatarFile] = useState<File | null>(null);
  const [avatarPreview, setAvatarPreview] = useState<string>('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showOtpModal, setShowOtpModal] = useState(false);
  const [otpCode, setOtpCode] = useState('');
  const [isSendingOtp, setIsSendingOtp] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const editConfig = profile?.edit_config;
  const rawData = profile?.raw_data;
  const [initialLoading, setInitialLoading] = useState(true);

  const allowEdit = !!editConfig?.allow_edit;
  const allowedFields = editConfig?.editable_fields || [];

  const canEdit = (field: string) => allowEdit && allowedFields.includes(field);

  useEffect(() => {
    const timer = setTimeout(() => setInitialLoading(false), 200);
    return () => clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (rawData) {
      const pendingData = editConfig?.pending_data || {};
      setFormData({
        sdt: pendingData.sdt ?? rawData.sdt ?? '',
        email: pendingData.email ?? rawData.email ?? '',
        chuc_vu: pendingData.chuc_vu ?? rawData.chuc_vu ?? 'Học sinh',
        dia_chi_chi_tiet: pendingData.dia_chi_chi_tiet ?? rawData.dia_chi_chi_tiet ?? '',
        tinh_thanhpho: pendingData.tinh_thanhpho ?? rawData.tinh_thanhpho ?? 'Thành phố Đồng Nai',
        xa_phuong: pendingData.xa_phuong ?? rawData.xa_phuong ?? '',
        ap_khupho: pendingData.ap_khupho ?? rawData.ap_khupho ?? ''
      });
    }
  }, [rawData, editConfig]);

  // Parse address options
  let addressOptions: any = {};
  if (editConfig?.dia_chi_options) {
    const lines = editConfig.dia_chi_options.split('\n');
    const cityOptions: any = {};
    lines.forEach((line: string) => {
      if (!line.trim()) return;
      const parts = line.split(':');
      if (parts.length >= 2) {
        const ward = parts[0].trim();
        const hamlets = parts.slice(1).join(':').split(',').map((h: string) => h.trim()).filter((h: string) => h);
        cityOptions[ward] = hamlets;
      }
    });
    addressOptions['Thành phố Đồng Nai'] = Object.keys(cityOptions).length > 0 ? cityOptions : null;
  }

  const xaPhuongList = addressOptions['Thành phố Đồng Nai'] ? Object.keys(addressOptions['Thành phố Đồng Nai']) : [];
  const apKhuPhoList = (formData.xa_phuong && addressOptions['Thành phố Đồng Nai']?.[formData.xa_phuong]) 
                        ? addressOptions['Thành phố Đồng Nai'][formData.xa_phuong] : [];

  const handleInputChange = (field: string, value: string) => {
    setFormData((prev: any) => ({ ...prev, [field]: value }));
    if (field === 'xa_phuong') {
      setFormData((prev: any) => ({ ...prev, ap_khupho: '' }));
    }
  };

  const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (file.size > 2 * 1024 * 1024) {
        openSnackbar({ text: 'Dung lượng ảnh quá lớn (tối đa 2MB).', type: 'error' });
        if (fileInputRef.current) fileInputRef.current.value = '';
        return;
      }
      setAvatarFile(file);
      const reader = new FileReader();
      reader.onloadend = () => {
        setAvatarPreview(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const startSubmitProcess = async () => {
    if (!allowEdit || allowedFields.length === 0) {
      openSnackbar({ text: 'Hiện tại hệ thống không mở quyền chỉnh sửa.', type: 'warning' });
      return;
    }

    if (canEdit('email') && formData.email !== rawData?.email) {
      setIsSendingOtp(true);
      try {
        const res = await api.post('/api/zalo/send-otp', { email: formData.email });
        if (res.data?.success) {
          setShowOtpModal(true);
          openSnackbar({ text: res.data.message || 'Mã OTP đã được gửi đến email mới.', type: 'success' });
        } else {
          openSnackbar({ text: res.data?.message || 'Lỗi gửi OTP', type: 'error' });
        }
      } catch (err) {
        openSnackbar({ text: 'Lỗi kết nối máy chủ', type: 'error' });
      } finally {
        setIsSendingOtp(false);
      }
    } else {
      submitUpdate();
    }
  };

  const submitUpdate = async () => {
    setIsSubmitting(true);
    try {
      const submitData = new FormData();
      const payload: any = {};
      
      allowedFields.forEach((field: string) => {
        if (field !== 'anh_the' && field !== 'dia_chi') {
          payload[field] = formData[field];
        } else if (field === 'dia_chi') {
          payload.tinh_thanhpho = formData.tinh_thanhpho;
          payload.xa_phuong = formData.xa_phuong;
          payload.ap_khupho = formData.ap_khupho;
          payload.dia_chi_chi_tiet = formData.dia_chi_chi_tiet;
        }
      });
      
      if (otpCode) {
        payload.otp = otpCode;
      }

      submitData.append('data', JSON.stringify(payload));
      
      if (canEdit('anh_the') && avatarFile) {
        submitData.append('anh_the', avatarFile);
      }

      const res = await api.post('/api/zalo/update-profile', submitData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        timeout: 120000
      });
      
      if (res.data?.success) {
        setShowOtpModal(false);
        setOtpCode('');
        openSnackbar({ text: res.data.message || 'Cập nhật thành công!', type: 'success' });
        refetch();
        setTimeout(() => navigate(-1), 1200);
      } else {
        openSnackbar({ text: res.data?.message || 'Có lỗi xảy ra', type: 'error' });
      }
    } catch (err) {
      openSnackbar({ text: 'Lỗi kết nối máy chủ', type: 'error' });
    } finally {
      setIsSubmitting(false);
    }
  };

  const fullName = `${rawData?.ho_dem || ''} ${rawData?.ten || ''}`.trim() || 'Học sinh';
  const avatarUrl = avatarPreview 
    ? avatarPreview 
    : (rawData?.avatar_url || rawData?.anh_the_url
        ? (rawData.avatar_url || rawData.anh_the_url)
        : (editConfig?.pending_data?.anh_the || rawData?.anh_the
            ? `${import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua'}/public/assets/anh_the/${editConfig?.pending_data?.anh_the || rawData?.anh_the}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=1e3a8a&color=ffffff`));

  if (initialLoading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-[#f0f6fc]">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  const hasAnyEditableField = allowEdit && allowedFields.length > 0;

  return (
    <Page className="bg-[#f0f6fc] min-h-screen relative pb-20">
      <Header variant="back" title="Chỉnh sửa thông tin" />

      <div className="p-4 flex flex-col gap-3.5 max-w-md mx-auto">
        
        {/* Banner trạng thái chờ duyệt nếu có */}
        {editConfig?.has_pending_edit && (
          <div className="bg-amber-50 border border-amber-200/80 rounded-2xl p-3 shadow-2xs">
            <div className="flex items-start gap-2">
              <span className="text-amber-600 text-sm mt-0.5">⏳</span>
              <div>
                <h4 className="text-xs font-bold text-amber-900 m-0 uppercase">Yêu cầu đang chờ duyệt</h4>
                <p className="text-[11.5px] text-amber-800 mt-0.5 leading-relaxed m-0">
                  Bạn đang có yêu cầu chỉnh sửa thông tin gửi tới Quản trị viên và đang chờ phê duyệt.
                </p>
              </div>
            </div>
          </div>
        )}

        {/* Khung 1: Ảnh thẻ */}
        <div className="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <div className="w-13 h-17 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 relative shadow-2xs">
              <img src={avatarUrl} alt="Ảnh thẻ" className="w-full h-full object-cover" />
            </div>
            <div>
              <span className="text-xs font-bold text-slate-800 block">Ảnh thẻ học sinh</span>
              <span className="text-[11px] text-slate-400 mt-0.5 block">
                {canEdit('anh_the') ? 'Được phép thay đổi ảnh mới' : 'Ảnh hồ sơ chính thức'}
              </span>
            </div>
          </div>

          {canEdit('anh_the') && (
            <div>
              <input 
                type="file" 
                accept="image/*" 
                ref={fileInputRef} 
                className="hidden" 
                onChange={handleAvatarChange}
              />
              <button 
                type="button" 
                onClick={() => fileInputRef.current?.click()}
                className="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-[#1e3a8a] text-xs font-bold transition border border-blue-200 active:scale-95 cursor-pointer"
              >
                Chọn ảnh
              </button>
            </div>
          )}
        </div>

        {/* Khung 2: Thông tin định danh */}
        <div className="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex flex-col gap-3">
          <div className="border-b border-slate-100 pb-2">
            <span className="text-xs font-bold text-slate-700 uppercase tracking-wide">Thông tin định danh</span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            {/* Họ và tên */}
            <div>
              <label className="block text-[11px] font-semibold text-slate-500 mb-1">Họ và tên:</label>
              <input 
                type="text" 
                value={fullName} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            </div>

            {/* Mã học sinh / CCCD */}
            <div>
              <label className="block text-[11px] font-semibold text-slate-500 mb-1">Mã học sinh / CCCD:</label>
              <input 
                type="text" 
                value={rawData?.ma_hoc_sinh || ''} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            </div>

            {/* Ngày sinh */}
            <div>
              <label className="block text-[11px] font-semibold text-slate-500 mb-1">Ngày sinh:</label>
              <input 
                type="text" 
                value={formatDob(rawData?.ngay_sinh)} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            </div>

            {/* Giới tính */}
            <div>
              <label className="block text-[11px] font-semibold text-slate-500 mb-1">Giới tính:</label>
              <input 
                type="text" 
                value={rawData?.gioi_tinh || 'Nam'} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            </div>

            {/* Lớp học */}
            <div>
              <label className="block text-[11px] font-semibold text-slate-500 mb-1">Lớp học:</label>
              <input 
                type="text" 
                value={rawData?.ten_lop || rawData?.lop || ''} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            </div>

            {/* Niên khóa */}
            <div>
              <label className="block text-[11px] font-semibold text-slate-500 mb-1">Niên khóa:</label>
              <input 
                type="text" 
                value={rawData?.nien_khoa || ''} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            </div>
          </div>
        </div>

        {/* Khung 3: Thông tin liên lạc & Chức vụ */}
        <div className="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex flex-col gap-3">
          <div className="border-b border-slate-100 pb-2">
            <span className="text-xs font-bold text-slate-700 uppercase tracking-wide">Thông tin liên lạc & Chức vụ</span>
          </div>

          {/* Chức vụ */}
          <div>
            <label className="block text-[11px] font-semibold text-slate-600 mb-1">Chức vụ trong lớp:</label>
            {canEdit('chuc_vu') ? (
              <div className="relative">
                <select 
                  value={formData.chuc_vu || 'Học sinh'}
                  onChange={(e) => handleInputChange('chuc_vu', e.target.value)}
                  className={`${inputBaseClass} ${inputEditableClass} appearance-none pr-8 cursor-pointer`}
                >
                  <option value="Học sinh">Học sinh</option>
                  <option value="Bí thư">Bí thư</option>
                  <option value="Lớp trưởng">Lớp trưởng</option>
                  <option value="Lớp phó">Lớp phó</option>
                </select>
                <div className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                  <Icon icon="zi-chevron-down" size={16} />
                </div>
              </div>
            ) : (
              <input 
                type="text" 
                value={formData.chuc_vu || 'Học sinh'} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            )}
          </div>

          {/* Số điện thoại */}
          <div>
            <label className="block text-[11px] font-semibold text-slate-600 mb-1">Số điện thoại:</label>
            <input 
              type="text" 
              value={formData.sdt || ''} 
              onChange={(e) => handleInputChange('sdt', e.target.value)}
              disabled={!canEdit('sdt')}
              placeholder={canEdit('sdt') ? "Nhập số điện thoại mới" : "Chưa cập nhật"}
              className={`${inputBaseClass} ${canEdit('sdt') ? inputEditableClass : inputReadonlyClass}`}
            />
          </div>

          {/* Email */}
          <div>
            <label className="block text-[11px] font-semibold text-slate-600 mb-1">Địa chỉ Email:</label>
            <input 
              type="email" 
              value={formData.email || ''} 
              onChange={(e) => handleInputChange('email', e.target.value)}
              disabled={!canEdit('email')}
              placeholder={canEdit('email') ? "Nhập email mới" : "Chưa cập nhật"}
              className={`${inputBaseClass} ${canEdit('email') ? inputEditableClass : inputReadonlyClass}`}
            />
          </div>
        </div>

        {/* Khung 4: Địa chỉ cư trú */}
        <div className="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex flex-col gap-3">
          <div className="border-b border-slate-100 pb-2">
            <span className="text-xs font-bold text-slate-700 uppercase tracking-wide">Địa chỉ cư trú</span>
          </div>

          {/* Tỉnh / Thành phố */}
          <div>
            <label className="block text-[11px] font-semibold text-slate-500 mb-1">Tỉnh / Thành phố:</label>
            <input 
              type="text" 
              value={formData.tinh_thanhpho || 'Thành phố Đồng Nai'} 
              disabled 
              className={`${inputBaseClass} ${inputReadonlyClass}`}
            />
          </div>

          {/* Xã / Phường */}
          <div>
            <label className="block text-[11px] font-semibold text-slate-600 mb-1">Xã / Phường:</label>
            {canEdit('dia_chi') && xaPhuongList.length > 0 ? (
              <div className="relative">
                <select 
                  value={formData.xa_phuong || ''}
                  onChange={(e) => handleInputChange('xa_phuong', e.target.value)}
                  className={`${inputBaseClass} ${inputEditableClass} appearance-none pr-8 cursor-pointer`}
                >
                  <option value="">-- Chọn Xã/Phường --</option>
                  {xaPhuongList.map((xp: string) => (
                    <option key={xp} value={xp}>{xp}</option>
                  ))}
                </select>
                <div className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                  <Icon icon="zi-chevron-down" size={16} />
                </div>
              </div>
            ) : (
              <input 
                type="text" 
                value={formData.xa_phuong || 'Chưa cập nhật'} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            )}
          </div>

          {/* Ấp / Khu phố */}
          <div>
            <label className="block text-[11px] font-semibold text-slate-600 mb-1">Ấp / Khu phố:</label>
            {canEdit('dia_chi') && apKhuPhoList.length > 0 ? (
              <div className="relative">
                <select 
                  value={formData.ap_khupho || ''}
                  onChange={(e) => handleInputChange('ap_khupho', e.target.value)}
                  disabled={!formData.xa_phuong}
                  className={`${inputBaseClass} ${inputEditableClass} appearance-none pr-8 cursor-pointer disabled:opacity-50`}
                >
                  <option value="">-- Chọn Ấp/Khu phố --</option>
                  {apKhuPhoList.map((ap: string) => (
                    <option key={ap} value={ap}>{ap}</option>
                  ))}
                </select>
                <div className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                  <Icon icon="zi-chevron-down" size={16} />
                </div>
              </div>
            ) : (
              <input 
                type="text" 
                value={formData.ap_khupho || 'Chưa cập nhật'} 
                disabled 
                className={`${inputBaseClass} ${inputReadonlyClass}`}
              />
            )}
          </div>

          {/* Địa chỉ chi tiết */}
          <div>
            <label className="block text-[11px] font-semibold text-slate-600 mb-1">Địa chỉ chi tiết:</label>
            <input 
              type="text" 
              value={formData.dia_chi_chi_tiet || ''} 
              onChange={(e) => handleInputChange('dia_chi_chi_tiet', e.target.value)}
              disabled={!canEdit('dia_chi')}
              placeholder={canEdit('dia_chi') ? "Ví dụ: Số 123 đường Hùng Vương" : "Chưa cập nhật"}
              className={`${inputBaseClass} ${canEdit('dia_chi') ? inputEditableClass : inputReadonlyClass}`}
            />
          </div>
        </div>

        {/* Nút Submit Lưu thay đổi */}
        {hasAnyEditableField && (
          <button 
            type="button" 
            onClick={startSubmitProcess}
            disabled={isSubmitting || isSendingOtp}
            className="w-full h-11 rounded-xl bg-[#1e3a8a] hover:bg-[#162d6b] active:scale-[0.98] text-white text-sm font-bold shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 mt-1"
          >
            {isSubmitting || isSendingOtp ? (
              <div className="flex items-center gap-2">
                <span className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span className="text-xs">Đang xử lý...</span>
              </div>
            ) : (
              'Lưu thay đổi'
            )}
          </button>
        )}

      </div>

      {/* Modal Xác thực OTP khi đổi Email */}
      <Modal
        visible={showOtpModal}
        title="Xác thực OTP Email"
        onClose={() => setShowOtpModal(false)}
      >
        <div className="p-2 flex flex-col gap-3">
          <p className="text-xs text-slate-500 m-0 leading-relaxed">
            Hệ thống đã gửi mã OTP 6 số đến email <strong>{formData.email}</strong>. Vui lòng kiểm tra hòm thư và nhập mã vào bên dưới:
          </p>

          <input 
            type="text" 
            value={otpCode}
            onChange={(e) => setOtpCode(e.target.value.trim())}
            placeholder="Nhập mã OTP (6 số)"
            maxLength={6}
            className="h-10 border border-slate-300 rounded-xl px-3 text-center text-base font-bold tracking-widest text-[#1e3a8a] outline-none focus:border-[#1e3a8a]"
          />

          <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button 
              type="button"
              onClick={() => setShowOtpModal(false)}
              className="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition cursor-pointer"
            >
              Hủy
            </button>
            <button 
              type="button"
              onClick={submitUpdate}
              disabled={isSubmitting || !otpCode}
              className="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#1e3a8a] hover:bg-[#162d6b] transition disabled:opacity-50 cursor-pointer"
            >
              Xác nhận & Cập nhật
            </button>
          </div>
        </div>
      </Modal>

    </Page>
  );
};

export default EditProfilePage;
