import logoImg from '@/assets/logo.png';
import { useNavigate } from "react-router-dom";
import React, { useState, useEffect } from 'react';
import { Page, List, Spinner, Modal, Box, useSnackbar, Swiper } from "zmp-ui";
import { PATHS } from '@/constants/paths';
import { TaskItem } from '@/components/TaskItem';
import { useFavoriteTasks } from '@/hooks/useFavoriteTasks';
import { useNews } from '@/hooks/useNews';
import { Icon } from '@/components/Icon';
import { NewsCard } from '@/components/NewsCard';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import { navigateForward } from '@/utils/navigation';
import { useProfile } from '@/features/profile/profile.query';
import QRCode from 'react-qr-code';


const useSchoolYear = () => {
  const { data } = useQuery({
    queryKey: ['school_years'],
    queryFn: async () => {
      const response = await api.get('/api/zalo/get-nam-hoc');
      return response.data;
    },
    staleTime: 5 * 60 * 1000,
  });
  return { schoolYears: data?.data || [] };
};

const ALLOWED_OLD_YEAR_TASKS = [
  'Tính điểm TB',
  'Thành tích',
  'Vi phạm',
  'Hoạt động',
  'Khảo sát',
  'Kết quả tuần',
  'Lịch học/thi',
  'Điểm thi',
  'Phúc khảo'
];

const HomePage: React.FC = () => {
  const navigate = useNavigate();
  const [isFlipped, setIsFlipped] = useState(false);
  const { openSnackbar } = useSnackbar();

  const { tasks: favoriteTasks, loading: loadingTasks } = useFavoriteTasks();
  const { news, loading: newsLoading } = useNews(8);
  const { schoolYears } = useSchoolYear();

  // Group news into chunks of 2 for a 2-card grid slider
  const newsChunks = [];
  for (let i = 0; i < news.length; i += 2) {
    newsChunks.push(news.slice(i, i + 2));
  }

  const { data: profile, isLoading: loading, error, refetch } = useProfile();
  const student = profile?.raw_data;
  const isLatestYear = student?.is_latest_year ?? false;
  const latestYearName = schoolYears?.[0]?.ten_nam_hoc || 'mới nhất';
  const errorMsg = error ? 'Lỗi mạng hoặc server.' : '';

  // Removed imagePreloaded state

  useEffect(() => {
    if (student) {
      const fullName = `${student.ho_dem || ''} ${student.ten || ''}`.trim();
      const serverAvatarUrl = student.avatar_url || student.anh_the_url
        ? (student.avatar_url || student.anh_the_url)
        : (student.anh_the
            ? (student.anh_the.startsWith('http') ? student.anh_the : `${import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua'}/public/assets/anh_the/${student.anh_the}`)
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=224397&color=ffffff`);

      const img = new Image();
      img.src = serverAvatarUrl;
    }
  }, [student]);

  const isGraduated = student?.trang_thai_hoc_tap === 'da_tot_nghiep';
  const GRADUATED_BLOCKED_TASKS = [
    'Nhập vi phạm',
    'Sổ nhật kỳ',
    'Cấp quyền',
    'Lịch trực',
    'Xin vắng học',
    'Email học sinh',
    'Khảo sát'
  ];

  const handleTaskClick = async (title: string, locked: boolean, isLockedByGraduated?: boolean) => {
    if (isLockedByGraduated || (isGraduated && GRADUATED_BLOCKED_TASKS.includes(title))) {
      openSnackbar({ text: 'Chức năng này không khả dụng đối với học sinh đã tốt nghiệp', type: 'warning' });
      return;
    }
    if (locked) {
      openSnackbar({ text: `Vui lòng truy cập năm học ${latestYearName} để sử dụng`, type: 'warning' });
      return;
    }
    if (title === 'Tính điểm TB') {
      navigateForward(navigate, PATHS.CALCULATE_GPA);
      return;
    }
    if (title === 'Khảo sát') {
      navigateForward(navigate, PATHS.SURVEYS);
      return;
    }
    if (title === 'Vi phạm') {
      navigateForward(navigate, PATHS.VIOLATIONS);
      return;
    }
    if (title === 'Thành tích') {
      navigateForward(navigate, PATHS.ACHIEVEMENTS);
      return;
    }
    if (title === 'Hoạt động') {
      navigateForward(navigate, PATHS.ACTIVITIES);
      return;
    }
    if (title === 'Cấp quyền') {
      navigateForward(navigate, PATHS.GRANT_PERMISSION);
      return;
    }

    // Các tác vụ có phân quyền: Luôn lấy quyền mới nhất từ server ngay lúc click
    let currentPermissions = student?.quyen_truy_cap;
    try {
      const { data: latestProfile } = await refetch();
      if (latestProfile?.raw_data?.quyen_truy_cap) {
        currentPermissions = latestProfile.raw_data.quyen_truy_cap;
      }
    } catch {}

    if (title === 'Sổ nhật kỳ') {
      if (currentPermissions?.so_nhat_ky_online) {
        navigateForward(navigate, PATHS.DIARY_WEEKS);
      } else {
        openSnackbar({ text: 'Bạn không có quyền Nhập Sổ Nhật Kỳ.', type: 'error' });
      }
      return;
    }
    if (title === 'Nhập vi phạm') {
      if (currentPermissions?.nhap_vi_pham) {
        navigateForward(navigate, PATHS.VIOLATION_WEEKS);
      } else {
        openSnackbar({ text: 'Bạn không có quyền Nhập Vi Phạm.', type: 'error' });
      }
      return;
    }
    if (title === 'Lịch học/thi') {
      navigateForward(navigate, PATHS.EXAM_SCHEDULE);
      return;
    }
    if (title === 'Điểm thi') {
      navigateForward(navigate, PATHS.EXAM_SCORES);
      return;
    }
    if (title === 'Phúc khảo') {
      navigateForward(navigate, PATHS.EXAM_APPEAL);
      return;
    }
    if (title === 'Kết quả tuần') {
      navigateForward(navigate, PATHS.WEEKLY_RESULTS);
      return;
    }
    if (title === 'Xin vắng học') {
      navigateForward(navigate, PATHS.LEAVE_REQUESTS);
      return;
    }
    openSnackbar({ text: 'Tính năng đang được phát triển', type: 'warning' });
  };


  if (loading || !student) {
    return (
      <Page className="bg-transparent flex justify-center items-center h-full pt-20 pb-20">
      </Page>
    );
  }



  // Format tên, avatar
  const fullName = `${student.ho_dem || ''} ${student.ten || ''}`.trim();
  const serverAvatarUrl = student.avatar_url || student.anh_the_url
    ? (student.avatar_url || student.anh_the_url)
    : (student.anh_the
        ? (student.anh_the.startsWith('http') ? student.anh_the : `${import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua'}/public/assets/anh_the/${student.anh_the}`)
        : `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=224397&color=ffffff`);

  // Format ngày sinh (yyyy-mm-dd to dd/mm/yyyy)
  const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
    return dateStr;
  };

  // Format trạng thái học tập
  const formatStatus = (status: string) => {
    if (!status) return 'Đang học';
    switch (status) {
      case 'dang_hoc': return 'Đang học';
      case 'nghi_hoc': return 'Nghỉ học';
      case 'da_tot_nghiep': return 'Đã tốt nghiệp';
      default: return status;
    }
  };

  return (
    <Page hideScrollbar className="bg-transparent pb-24 relative">

      {/* SECTION 1: HỒ SƠ HỌC SINH */}
      <div className="pt-6 px-4 pb-4 relative z-10">


        {/* Thẻ Sơ Yếu Lý Lịch (Lật 3D) */}
        <div className="w-full mb-1" style={{ perspective: '1000px' }}>
          <div
            className={`relative w-full rounded-[16px] transition-transform duration-700 cursor-pointer`}
            style={{ WebkitTransformStyle: 'preserve-3d', transformStyle: 'preserve-3d', transform: isFlipped ? 'rotateY(180deg)' : 'rotateY(0deg)' }}
            onClick={() => setIsFlipped(!isFlipped)}
          >
            {/* 🔳 MẶT TRƯỚC (Thông tin) - Không dùng absolute để thẻ tự động co giãn theo nội dung */}
            <div
              className={`w-full bg-white rounded-[16px] shadow-[0_4px_20px_rgba(34,67,151,0.06)] border border-[#224397]/20 p-5 flex flex-col`}
              style={{ WebkitBackfaceVisibility: 'hidden', backfaceVisibility: 'hidden' }}
            >
              {/* Avatar */}
              <div className="flex justify-center mb-4">
                <div className="w-24 h-24 rounded-full border-[4px] border-[#224397]/25 overflow-hidden">
                  <img 
                    src={serverAvatarUrl} 
                    alt="Avatar" 
                    referrerPolicy="no-referrer"
                    className="w-full h-full rounded-full object-cover" 
                    onError={(e) => {
                      e.currentTarget.src = 'https://c3binhson.edu.vn/thidua/public/assets/img/anhthegoc.JPG';
                    }}
                  />
                </div>
              </div>

              <h3 className="text-center font-bold text-[15px] text-slate-800 mb-2">Sơ yếu lý lịch:</h3>
              <div className="border-t border-dashed border-[#224397]/20 mb-2"></div>

              {/* Thông chi tiết */}
              <div className="flex flex-col gap-2 text-[13px]">
                <div className="flex justify-between items-center">
                  <span className="font-semibold text-slate-500">Họ và tên:</span>
                  <span className="text-slate-800 font-medium">{fullName}</span>
                </div>
                <div className="h-px bg-slate-50"></div>
                <div className="flex justify-between items-center">
                  <span className="font-semibold text-slate-500">Số CCCD:</span>
                  <span className="text-slate-800 font-medium">{student.ma_hoc_sinh}</span>
                </div>
                <div className="h-px bg-slate-50"></div>
                <div className="flex justify-between items-center">
                  <span className="font-semibold text-slate-500">Trạng thái:</span>
                  <span className={`font-bold ${student.trang_thai_hoc_tap === 'nghi_hoc' ? 'text-red-500' : 'text-green-600'}`}>
                    {student.trang_thai_hien_thi || formatStatus(student.trang_thai_hoc_tap)}
                  </span>
                </div>
                <div className="h-px bg-slate-50"></div>
                <div className="flex justify-between items-center">
                  <span className="font-semibold text-slate-500">Lớp:</span>
                  <span className="text-slate-800 font-medium">{student.ten_lop}</span>
                </div>
                <div className="h-px bg-slate-50"></div>
                <div className="flex justify-between items-center">
                  <span className="font-semibold text-slate-500">Năm học:</span>
                  <span className="text-slate-800 font-medium">{student.ten_nam_hoc || '-'}</span>
                </div>
                <div className="h-px bg-slate-50"></div>
                <div className="flex justify-between items-center">
                  <span className="font-semibold text-slate-500">Ngày sinh:</span>
                  <span className="text-slate-800 font-medium">{formatDate(student.ngay_sinh)}</span>
                </div>
                <div className="h-px bg-slate-50"></div>
                <div className="flex justify-between items-center">
                  <span className="font-semibold text-slate-500">Giới tính:</span>
                  <span className="text-slate-800 font-medium">{student.gioi_tinh}</span>
                </div>
                <div className="h-px bg-slate-50"></div>
                <div className="flex justify-between items-center">
                  <span className="font-semibold text-slate-500">Niên khóa:</span>
                  <span className="text-slate-800 font-medium">{student.nien_khoa || '2025 - 2028'}</span>
                </div>
              </div>
            </div>

            {/* 🔳 MẶT SAU (Mã QR) - Dùng absolute inset-0 để nó lấp đầy chiều cao của mặt trước */}
            <div
              className={`absolute inset-0 w-full h-full bg-white rounded-[16px] shadow-[0_4px_20px_rgba(34,67,151,0.06)] border border-[#224397]/20 p-5 flex flex-col items-center justify-center`}
              style={{ WebkitBackfaceVisibility: 'hidden', backfaceVisibility: 'hidden', transform: 'rotateY(180deg)' }}
            >
              <h3 className="font-bold text-[#224397] mb-6 uppercase tracking-wide">Mã Định Danh Cá Nhân</h3>
              <div className="bg-white p-3.5 rounded-xl shadow-[0_2px_15px_rgba(34,67,151,0.06)] border border-[#224397]/20 flex items-center justify-center">
                <QRCode value={student.ma_hoc_sinh || ''} size={160} fgColor="#224397" bgColor="#ffffff" />
              </div>
              <p className="font-mono font-bold text-[18px] text-[#224397] mt-5 tracking-widest">{student.ma_hoc_sinh}</p>
              <p className="text-slate-500 text-xs mt-3 text-center px-4 leading-relaxed">Chạm vào thẻ để quay lại<br />Sử dụng mã QR này để điểm danh.</p>
            </div>
          </div>
        </div>
        <div className="text-center mt-4">
          <span className="text-slate-400 text-[11px] italic animate-pulse">Chạm vào thẻ để xem mã QR</span>
        </div>
      </div>

      {/* SECTION 2: NHÓM DỊCH VỤ / TÁC VỤ (VNeID Style) */}
      <div className="px-4 mb-6 relative z-10 mt-2">
        {/* Tiêu đề nhóm tác vụ với icon chỉnh sửa */}
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-[16px] font-bold text-slate-800">
            Tác vụ thường dùng
          </h3>
          <div
            className="flex items-center gap-1 cursor-pointer active:opacity-70 transition-opacity"
            onClick={() => navigateForward(navigate, '/edit-tasks')}
          >
            <span className="text-[13px] font-medium text-slate-600">Chỉnh sửa</span>
            <Icon icon="zi-edit" size={16} className="text-[#224397]" />
          </div>
        </div>

        {loadingTasks ? (
          <div className="flex justify-center items-center h-24">
            <Spinner />
          </div>
        ) : (
          <div className="grid grid-cols-4 gap-y-5 gap-x-2">
            {favoriteTasks.map(task => {
              const isLockedByGraduated = isGraduated && GRADUATED_BLOCKED_TASKS.includes(task.title);
              const locked = isLockedByGraduated || (!isLatestYear && !ALLOWED_OLD_YEAR_TASKS.includes(task.title));
              return (
                <TaskItem
                  key={task.id}
                  title={task.title}
                  icon={task.icon}
                  badge={task.isNew ? 'Mới' : undefined}
                  locked={locked}
                  onClick={() => handleTaskClick(task.title, locked, isLockedByGraduated)}
                />
              );
            })}
          </div>
        )}
        {/* News Section */}
        <div className="mt-8 pb-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-[16px] font-bold text-slate-800">Tin tức - Sự kiện</h3>
            <span
              className="text-[13px] font-medium text-slate-600 cursor-pointer active:opacity-70"
              onClick={() => navigate(PATHS.NEWS)}
            >
              Xem tất cả &gt;
            </span>
          </div>

          <div className="w-full">
            {newsLoading ? (
              <div className="flex justify-center py-6">
                <Spinner />
              </div>
            ) : news.length > 0 ? (
              <Swiper autoplay duration={2500} loop={true}>
                {newsChunks.map((chunk, idx) => (
                  <Swiper.Slide key={idx}>
                    <div className="grid grid-cols-2 gap-3 pb-8">
                      {chunk.map((item) => (
                        <NewsCard key={item.id} news={item} variant="horizontal" />
                      ))}
                    </div>
                  </Swiper.Slide>
                ))}
              </Swiper>
            ) : (
              <div className="text-center py-6 text-sm text-slate-400">Chưa có tin tức nào</div>
            )}
          </div>
        </div>

      </div>

    </Page>
  );
};

export default HomePage;
