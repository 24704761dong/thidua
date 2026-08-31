import { useNavigate } from "react-router-dom";
import React from 'react';
import { Page, useSnackbar } from "zmp-ui";
import { TaskItem } from '@/components/TaskItem';
import { TASK_SECTIONS } from '@/constants/tasks';
import { PATHS } from '@/constants/paths';
import { useProfile } from '@/features/profile/profile.query';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import { navigateForward } from '@/utils/navigation';
import { openWebview } from 'zmp-sdk/apis';

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

const TasksPage: React.FC = () => {
  const { openSnackbar } = useSnackbar();
  const { data: profile, refetch } = useProfile();
  const { schoolYears } = useSchoolYear();

  const isLatestYear = profile?.raw_data?.is_latest_year ?? false;
  const isGraduated = profile?.raw_data?.trang_thai_hoc_tap === 'da_tot_nghiep';
  const latestYearName = schoolYears?.[0]?.ten_nam_hoc || 'mới nhất';

  const navigate = useNavigate();

  const handleTaskClick = async (title: string, locked: boolean, isLockedByGraduated?: boolean) => {
    if (isLockedByGraduated) {
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
    let currentPermissions = profile?.raw_data?.quyen_truy_cap;
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
    if (title === 'Lịch trực') {
      if (currentPermissions?.dang_ky_truc) {
        navigateForward(navigate, PATHS.DUTY_WEEKS);
      } else {
        openSnackbar({ text: 'Bạn không có quyền Đăng Ký Trực.', type: 'error' });
      }
      return;
    }
    if (title === 'Kết quả tuần') {
      navigateForward(navigate, PATHS.WEEKLY_RESULTS);
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
    if (title === 'Email học sinh') {
      navigateForward(navigate, PATHS.EMAIL_STUDENT);
      return;
    }
    if (title === 'Xin vắng học') {
      navigateForward(navigate, PATHS.LEAVE_REQUESTS);
      return;
    }
    openSnackbar({ text: 'Tính năng đang được phát triển', type: 'warning' });
  };

  return (
    <Page hideScrollbar className="bg-transparent relative pb-24">
      <div className="px-4 pb-6 flex flex-col gap-8 mt-2">
        {TASK_SECTIONS.map((section, index) => {
          const isSectionBlocked = isGraduated && (section.title === 'Tác vụ' || section.title === 'Hành chính');
          return (
            <div key={index}>
              <h3 className="text-sm font-bold text-[#224397] mb-4 uppercase flex items-center justify-between">
                <span>{section.title}</span>
                {isSectionBlocked && (
                  <span className="text-[11px] font-normal lowercase text-slate-400">
                    (khóa cho học sinh tốt nghiệp)
                  </span>
                )}
              </h3>
              <div className="grid grid-cols-4 gap-y-6 gap-x-2">
                {section.items.map((item, idx) => {
                  const isLockedByGraduated = isSectionBlocked;
                  const locked = isLockedByGraduated || (!isLatestYear && !ALLOWED_OLD_YEAR_TASKS.includes(item.title));
                  return (
                    <TaskItem
                      key={idx}
                      title={item.title}
                      icon={item.icon}
                      locked={locked}
                      onClick={() => handleTaskClick(item.title, locked, isLockedByGraduated)}
                    />
                  );
                })}
              </div>
            </div>
          );
        })}
      </div>
    </Page>
  );
};

export default TasksPage;
