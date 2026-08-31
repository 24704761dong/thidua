import logoImg from '@/assets/logo.png';
import React, { useState } from 'react';
import { Page, Modal, Spinner } from 'zmp-ui';
import { Icon } from '@/components/Icon';
import { configAppView } from 'zmp-sdk';
import { useProfile } from '@/features/profile/profile.query';
import { useNavigate } from 'react-router-dom';
import { ZaloStudentCard } from '@/components/ZaloStudentCard';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';


const useSchoolYear = () => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['school_years'],
    queryFn: async () => {
      const response = await api.get('/api/zalo/get-nam-hoc');
      return response.data;
    },
    staleTime: 5 * 60 * 1000,
  });

  return {
    schoolYears: data?.data || [],
    isLoading,
    error,
  };
};

const ProfilePage: React.FC = () => {
  const { data: profile, refetch } = useProfile();
  const navigate = useNavigate();
  const [modalVisible, setModalVisible] = useState(false);
  const { schoolYears, isLoading } = useSchoolYear();
  const [initialLoading, setInitialLoading] = useState(true);

  const handleEditClick = async () => {
    try {
      const { data: latestProfile } = await refetch();
      const canEdit = latestProfile?.edit_config?.allow_edit ?? profile?.edit_config?.allow_edit;
      if (canEdit) {
        navigate('/edit-profile');
      } else {
        setModalVisible(true);
      }
    } catch {
      if (profile?.edit_config?.allow_edit) {
        navigate('/edit-profile');
      } else {
        setModalVisible(true);
      }
    }
  };

  React.useEffect(() => {
    const timer = setTimeout(() => setInitialLoading(false), 250);
    return () => clearTimeout(timer);
  }, []);

  const isLatestYear = profile?.raw_data?.is_latest_year ?? false;


  const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    const parts = dateString.split('-');
    if (parts.length === 3) {
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateString;
  };

  React.useEffect(() => {
    configAppView({
      headerColor: '#ffffff',
      headerTextColor: 'black',
      actionBar: { hide: true },
    }).catch(() => { });
  }, []);

  const isGraduated = profile?.raw_data?.trang_thai_hoc_tap === 'da_tot_nghiep';

  const displayData = profile?.edit_config?.has_pending_edit && profile?.edit_config?.pending_data
    ? { ...profile.raw_data, ...profile.edit_config.pending_data }
    : profile?.raw_data;

  if (initialLoading || isLoading || !profile) {
    return <Page className="bg-transparent overflow-y-auto [&::-webkit-scrollbar]:hidden flex justify-center items-center h-full pt-20 pb-20"></Page>;
  }

  return (
    <Page className="bg-transparent overflow-y-auto [&::-webkit-scrollbar]:hidden">
      {/* Content */}
      <div className="px-4 pt-4 pb-28 flex flex-col gap-2">
        {/* Dynamic Vibrant Student ID Card */}
        <div className="rounded-[22px] overflow-hidden">
          <ZaloStudentCard studentData={displayData} />
        </div>

        {/* Info List (Reduced) */}
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 mt-2 p-4">
          <h3 className="text-sm font-bold text-[#224397] uppercase mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Icon icon="zi-info-circle" className="text-[#FAB723]" />
              Thông tin chi tiết
            </div>
            {!isGraduated && (
              <button
                onClick={handleEditClick}
                className="flex items-center gap-1 text-xs bg-[#224397] text-white px-3 py-1.5 rounded-full font-medium hover:bg-[#1a367d] transition-colors"
              >
                <Icon icon="zi-edit" size={14} /> Chỉnh sửa
              </button>
            )}
          </h3>

          <Modal
            visible={modalVisible}
            title="Thông báo"
            onClose={() => setModalVisible(false)}
            actions={[
              {
                text: 'Đóng',
                close: true,
                highLight: true
              }
            ]}
            description="Hệ thống không cho phép chỉnh sửa, vui lòng liên hệ nhà trường!"
          />

          {!isGraduated && profile?.edit_config?.has_pending_edit && (
            <div className="bg-orange-50 border border-orange-200 text-orange-700 p-3 rounded-lg text-sm mb-4 flex items-start gap-2">
              <Icon icon="zi-clock-1" className="mt-0.5 shrink-0" size={16} />
              <p>Bạn đang có một yêu cầu chỉnh sửa thông tin chờ duyệt.</p>
            </div>
          )}

          {displayData && (
            <div className="flex flex-col gap-3 text-sm">
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Mã học sinh / CCCD:</span>
                <span className="font-semibold text-slate-800">{displayData.ma_hoc_sinh || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Họ và tên:</span>
                <span className="font-semibold text-slate-800">{displayData.ho_dem} {displayData.ten}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Ngày sinh:</span>
                <span className="font-semibold text-slate-800">{formatDate(displayData.ngay_sinh)}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Giới tính:</span>
                <span className="font-semibold text-slate-800">{displayData.gioi_tinh || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Lớp:</span>
                <span className="font-semibold text-[#224397]">{displayData.ten_lop || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Giáo viên chủ nhiệm:</span>
                <span className="font-semibold text-slate-800">{displayData.gvcn || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Chức vụ:</span>
                <span className="font-semibold text-slate-800">{displayData.chuc_vu || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Số điện thoại:</span>
                <span className="font-semibold text-slate-800">{displayData.sdt || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Email:</span>
                <span className="font-semibold text-slate-800">{displayData.email || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Năm học:</span>
                <span className="font-semibold text-slate-800">{displayData.ten_nam_hoc || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Niên khóa:</span>
                <span className="font-semibold text-slate-800">{displayData.nien_khoa || '-'}</span>
              </div>
              <div className="flex justify-between border-b border-slate-50 pb-2">
                <span className="text-slate-500">Trạng thái:</span>
                <span className="font-semibold text-emerald-600">
                  {displayData.trang_thai_hien_thi || (
                    displayData.trang_thai_hoc_tap === 'dang_hoc' ? 'Đang học' :
                    displayData.trang_thai_hoc_tap === 'da_tot_nghiep' ? 'Đã tốt nghiệp' :
                    displayData.trang_thai_hoc_tap === 'chuyen_truong' ? 'Chuyển trường' :
                    displayData.trang_thai_hoc_tap === 'nghi_hoc' ? 'Nghỉ học' : displayData.trang_thai_hoc_tap || '-'
                  )}
                </span>
              </div>
              <div className="flex flex-col gap-1 border-b border-slate-50 pb-2">
                <span className="text-slate-500">Địa chỉ:</span>
                <span className="font-semibold text-slate-800 leading-snug">
                  {[displayData.dia_chi_chi_tiet, displayData.ap_khupho, displayData.xa_phuong, displayData.tinh_thanhpho].filter(Boolean).join(', ') || '-'}
                </span>
              </div>
            </div>
          )}
        </div>
      </div>
    </Page>
  );
};

export default ProfilePage;
