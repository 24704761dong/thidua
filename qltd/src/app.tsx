import React, { useEffect } from 'react';
import { QueryClientProvider, useQuery } from '@tanstack/react-query';
import { queryClient } from '@/lib/queryClient';
import { App, ZMPRouter, SnackbarProvider, BottomNavigation, Box, Text, Modal, Spinner, Page } from 'zmp-ui';
import { Route, Routes, useLocation, useNavigate, Navigate, useNavigationType } from 'react-router-dom';
import { CSSTransition, TransitionGroup } from 'react-transition-group';
import HomePage from '@/pages/HomePage';
import ProfilePage from '@/pages/ProfilePage';
import EditProfilePage from '@/pages/EditProfilePage';
import LoginPage from '@/pages/LoginPage';
import NotificationPage from '@/pages/NotificationPage';
import TasksPage from '@/pages/TasksPage';
import EditTasksPage from '@/pages/EditTasksPage';
import NewsPage from '@/pages/NewsPage';
import SettingsPage from '@/pages/SettingsPage';
import ChangePasswordPage from '@/pages/ChangePasswordPage';
import TermsPage from '@/pages/TermsPage';
import ViolationsPage from '@/pages/ViolationsPage';
import AchievementsPage from '@/pages/AchievementsPage';
import GrantPermissionPage from '@/pages/GrantPermissionPage';
import SelectDiaryWeekPage from '@/pages/SelectDiaryWeekPage';
import DiaryInputPage from '@/pages/DiaryInputPage';
import SelectViolationWeekPage from '@/pages/SelectViolationWeekPage';
import ViolationInputPage from '@/pages/ViolationInputPage';
import CalculateGpaPage from '@/pages/CalculateGpaPage';
import SurveysPage from '@/pages/SurveysPage';
import SurveyTakePage from '@/pages/SurveyTakePage';
import SelectDutyWeekPage from '@/pages/SelectDutyWeekPage';
import DutyInputPage from '@/pages/DutyInputPage';
import WeeklyResultsPage from '@/pages/WeeklyResultsPage';
import ExamSchedulePage from '@/pages/ExamSchedulePage';
import ExamScoresPage from '@/pages/ExamScoresPage';
import ExamAppealPage from '@/pages/ExamAppealPage';
import EmailPage from '@/pages/EmailPage';
import ActivitiesPage from '@/pages/ActivitiesPage';
import ActivityDetailPage from '@/pages/ActivityDetailPage';
import LeaveRequestsPage from '@/pages/LeaveRequestsPage';
import LeaveRequestCreatePage from '@/pages/LeaveRequestCreatePage';
import {
  HomeNavIcon,
  UserNavIcon,
  CategoryNavIcon,
  NotifNavIcon,
  SettingNavIcon,
} from '@/components/CustomIcons';
import { navigateTab, getNavigationDirection } from '@/utils/navigation';
import Header from '@/components/Header';
import { AutoUpdater } from '@/components/AutoUpdater';
import { PATHS, PATH_TO_TAB } from '@/constants/paths';
import api from '@/lib/api';
import { Icon } from '@/components/Icon';
import { isAuthenticated, logout } from '@/utils/auth';
import UpdateModal from '@/components/UpdateModal';
import { initPushNotifications } from '@/lib/pushNotifications';
import { App as CapacitorApp } from '@capacitor/app';

// Hằng số quản lý phiên bản app
const APP_VERSION = '0.9.0'; // TEST: Đặt là 0.9.0 để test modal khóa màn hình (thấp hơn 1.0.1 của server)

// Hàm kiểm tra đăng nhập cũ đã được chuyển sang utils/auth.ts

const AnimatedRoutes: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const navType = useNavigationType();
  let direction = getNavigationDirection();
  
  if (navType === 'POP') {
    direction = 'backward';
  }
  const activeTab = PATH_TO_TAB[location.pathname] || 'home';
  const [isOnline, setIsOnline] = React.useState(navigator.onLine);
  const { data: isInactive } = useQuery({ queryKey: ['student_inactive'], initialData: false });
  const [inactiveModalVisible, setInactiveModalVisible] = React.useState(false);
  const [inactiveCountdown, setInactiveCountdown] = React.useState(5);

  // Update Logic
  const [updateData, setUpdateData] = React.useState<any>(null);
  const [showUpdateModal, setShowUpdateModal] = React.useState(false);
  const [isForceUpdate, setIsForceUpdate] = React.useState(false);

  // Khởi tạo push notifications khi đã đăng nhập
  useEffect(() => {
    if (isAuthenticated()) {
      initPushNotifications();
    }
  }, [location.pathname]);

  useEffect(() => {
    const backButtonListener = CapacitorApp.addListener('backButton', ({ canGoBack }) => {
      if (location.pathname === PATHS.HOME) {
        CapacitorApp.exitApp();
      } else {
        navigate(-1);
      }
    });
    return () => {
      backButtonListener.then(listener => listener.remove());
    };
  }, [location.pathname, navigate]);

  useEffect(() => {
    // Chỉ kiểm tra version khi có mạng
    if (!navigator.onLine) return;
    
    api.get('/api_check_version.php')
      .then(res => {
        if (res.data?.success) {
          const data = res.data.data;
          const currentParts = APP_VERSION.split('.').map(Number);
          const minParts = data.min_required_version.split('.').map(Number);
          const latestParts = data.latest_version.split('.').map(Number);

          let force = false;
          let recommend = false;

          for (let i = 0; i < 3; i++) {
            if (currentParts[i] < minParts[i]) { force = true; break; } 
            else if (currentParts[i] > minParts[i]) break;
          }

          if (!force) {
            for (let i = 0; i < 3; i++) {
              if (currentParts[i] < latestParts[i]) { recommend = true; break; } 
              else if (currentParts[i] > latestParts[i]) break;
            }
          }

          if (force || recommend) {
            setUpdateData(data);
            setIsForceUpdate(force);
            setShowUpdateModal(true);
          }
        }
      })
      .catch(console.error);
  }, []);

  useEffect(() => {
    if (isInactive && !inactiveModalVisible) {
      setInactiveModalVisible(true);
    }
  }, [isInactive, inactiveModalVisible]);

  const { data: unreadCount = 0 } = useQuery({
    queryKey: ['unread_notifications'],
    queryFn: async () => {
      if (!isAuthenticated() || location.pathname === PATHS.LOGIN) return 0;
      try {
        const res = await api.get('/api/zalo/get-notifications');
        return res.data?.success ? (res.data.unread_count || 0) : 0;
      } catch (error) {
        return 0;
      }
    },
    refetchInterval: 60000,
  });

  // Xử lý countdown 5s rồi đăng xuất
  useEffect(() => {
    let timer: ReturnType<typeof setTimeout>;
    if (inactiveModalVisible) {
      if (inactiveCountdown > 0) {
        timer = setTimeout(() => {
          setInactiveCountdown(prev => prev - 1);
        }, 1000);
      } else {
        // Đăng xuất bằng hàm chuẩn hóa
        logout();
        setInactiveModalVisible(false);
        navigate(PATHS.LOGIN, { replace: true });
      }
    }
    return () => clearTimeout(timer);
  }, [inactiveModalVisible, inactiveCountdown, navigate]);

  useEffect(() => {
    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);

    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, []);

  // Chặn người dùng nếu chưa đăng nhập (ngoại trừ trang Login & Terms)
  useEffect(() => {
    const publicPaths = [PATHS.LOGIN, PATHS.TERMS];
    if (!isAuthenticated() && !publicPaths.includes(location.pathname as any)) {
      navigate(PATHS.LOGIN, { replace: true });
      return;
    }

    // Bắt buộc học sinh đổi mật khẩu lần đầu trước khi dùng các trang khác
    const mustChangePassword = localStorage.getItem('must_change_password') === 'true';
    if (isAuthenticated() && mustChangePassword && location.pathname !== PATHS.CHANGE_PASSWORD && !publicPaths.includes(location.pathname as any)) {
      navigate(PATHS.CHANGE_PASSWORD, { replace: true });
    }
  }, [location.pathname, navigate]);

  // Kiểm tra Khảo sát mới (Bắt buộc / Tự nguyện)
  const [requiredSurvey, setRequiredSurvey] = React.useState<any>(null);
  const [optionalSurvey, setOptionalSurvey] = React.useState<any>(null);
  const [showOptionalModal, setShowOptionalModal] = React.useState(false);
  const [showRequiredModal, setShowRequiredModal] = React.useState(false);

  useEffect(() => {
    if (!isAuthenticated() || location.pathname === PATHS.LOGIN) return;
    
    const checkSurveys = async () => {
      try {
        const res = await api.get('/api/zalo/get-surveys');
        if (res.data?.success && res.data.pending?.length > 0) {
          const pendingList = res.data.pending;
          const reqSurvey = pendingList.find((s: any) => s.badgeType === 'required');
          if (reqSurvey) {
            setRequiredSurvey(reqSurvey);
            if (location.pathname !== PATHS.SURVEYS && !location.pathname.startsWith('/survey-take')) {
              setShowRequiredModal(true);
            } else {
              setShowRequiredModal(false);
            }
          } else {
            setRequiredSurvey(null);
            setShowRequiredModal(false);
            const optSurvey = pendingList.find((s: any) => s.badgeType === 'optional');
            if (optSurvey && !sessionStorage.getItem(`opt_survey_dismissed_${optSurvey.id}`)) {
              setOptionalSurvey(optSurvey);
              setShowOptionalModal(true);
            }
          }
        } else {
          setRequiredSurvey(null);
          setShowRequiredModal(false);
        }
      } catch (err) {
        // ignore
      }
    };
    checkSurveys();
  }, [location.pathname]);

  const handleTabChange = (key: string): void => {
    navigateTab(navigate, activeTab, key);
  };

  if (!isOnline && !isAuthenticated()) {
    return (
      <div className="flex flex-col h-screen justify-center items-center bg-slate-50 px-6 text-center">
        <Icon icon="zi-warning" size={48} className="text-red-500 mb-4" />
        <Text className="text-lg font-semibold text-slate-800">Không có kết nối Internet</Text>
        <Text className="text-sm text-slate-500 mt-2">
          Vui lòng kiểm tra lại Wifi hoặc mạng di động của bạn để tiếp tục sử dụng ứng dụng.
        </Text>
      </div>
    );
  }

  return (
    <div className="flex flex-col h-screen" style={{ background: 'linear-gradient(to bottom right, #f8fafc, #E4F6FD)' }}>
      {!isOnline && isAuthenticated() && (
        <div className="bg-amber-500 text-white text-[11px] font-medium py-1 px-3 text-center shadow-sm flex items-center justify-center gap-1 z-[60]">
          <Icon icon="zi-warning" size={14} className="text-white" />
          <span>Đang hoạt động ngoại tuyến (Offline) - Dữ liệu từ bộ nhớ đệm</span>
        </div>
      )}
      {['/', '/profile', '/tasks', '/notifications', '/settings'].includes(location.pathname) ? <Header variant="logo" /> : null}
      <TransitionGroup
        className={`page-transition-group flex-1 ${
          direction === 'forward' ? 'slide-forward' : 
          direction === 'backward' ? 'slide-backward' : 
          'fade-through'
        }`}
      >
        <CSSTransition key={location.pathname} classNames="page" timeout={300}>
          <Routes location={location}>
            <Route path={PATHS.LOGIN} element={<LoginPage />} />
            <Route path={PATHS.HOME} element={<HomePage />} />
            <Route path={PATHS.PROFILE} element={<ProfilePage />} />
            <Route path="/edit-profile" element={<EditProfilePage />} />
            <Route path={PATHS.TASKS} element={<TasksPage />} />
            <Route path="/edit-tasks" element={<EditTasksPage />} />
            <Route path={PATHS.NEWS} element={<NewsPage />} />
            <Route path={PATHS.NOTIFICATIONS} element={<NotificationPage />} />
            <Route path={PATHS.SETTINGS} element={<SettingsPage />} />
            <Route path={PATHS.CHANGE_PASSWORD} element={<ChangePasswordPage />} />
            <Route path={PATHS.TERMS} element={<TermsPage />} />
            <Route path={PATHS.VIOLATIONS} element={<ViolationsPage />} />
            <Route path={PATHS.ACHIEVEMENTS} element={<AchievementsPage />} />
            <Route path={PATHS.GRANT_PERMISSION} element={<GrantPermissionPage />} />
            <Route path={PATHS.DIARY_WEEKS} element={<SelectDiaryWeekPage />} />
            <Route path={PATHS.DIARY_INPUT} element={<DiaryInputPage />} />
            <Route path={PATHS.VIOLATION_WEEKS} element={<SelectViolationWeekPage />} />
            <Route path={PATHS.VIOLATION_INPUT} element={<ViolationInputPage />} />
            <Route path={PATHS.CALCULATE_GPA} element={<CalculateGpaPage />} />
            <Route path={PATHS.SURVEYS} element={<SurveysPage />} />
            <Route path={PATHS.SURVEY_TAKE} element={<SurveyTakePage />} />
            <Route path={PATHS.DUTY_WEEKS} element={<SelectDutyWeekPage />} />
            <Route path={PATHS.DUTY_INPUT} element={<DutyInputPage />} />
            <Route path={PATHS.WEEKLY_RESULTS} element={<WeeklyResultsPage />} />
            <Route path={PATHS.EXAM_SCHEDULE} element={<ExamSchedulePage />} />
            <Route path={PATHS.EXAM_SCORES} element={<ExamScoresPage />} />
            <Route path={PATHS.EXAM_APPEAL} element={<ExamAppealPage />} />
            <Route path={PATHS.EMAIL_STUDENT} element={<EmailPage />} />
            <Route path={PATHS.ACTIVITIES} element={<ActivitiesPage />} />
            <Route path={PATHS.ACTIVITY_DETAIL} element={<ActivityDetailPage />} />
            <Route path={PATHS.LEAVE_REQUESTS} element={<LeaveRequestsPage />} />
            <Route path={PATHS.LEAVE_REQUEST_CREATE} element={<LeaveRequestCreatePage />} />
            <Route
              path="*"
              element={
                <Page className="flex flex-col items-center justify-center bg-slate-50 h-screen p-6 text-center">
                  <Icon icon="zi-warning" size={64} className="text-slate-300 mb-4" />
                  <Text className="text-lg font-bold text-slate-800">404 - Trang không tồn tại</Text>
                  <Text className="text-sm text-slate-500 mt-2 mb-6">
                    Tính năng hoặc trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.
                  </Text>
                  <button
                    onClick={() => navigate(PATHS.HOME, { replace: true })}
                    className="px-6 py-3 bg-[#224397] text-white font-bold rounded-xl shadow-sm"
                  >
                    Về Trang chủ
                  </button>
                </Page>
              }
            />
          </Routes>
        </CSSTransition>
      </TransitionGroup>
      {(['/', '/profile', '/tasks', '/notifications', '/settings', '/calculate-gpa', '/surveys'].includes(location.pathname) || location.pathname.startsWith('/survey-take')) && (
        <BottomNavigation 
          fixed 
          activeKey={activeTab} 
          onChange={handleTabChange}
          className="bg-white border-t border-slate-100 pb-safe shadow-[0_-5px_10px_rgba(0,0,0,0.02)]"
        >
          <BottomNavigation.Item key="home" label="Trang chủ" icon={<HomeNavIcon size={20} active={false} />} activeIcon={<HomeNavIcon size={20} active={true} />} />
          <BottomNavigation.Item key="profile" label="Thông tin" icon={<UserNavIcon size={20} active={false} />} activeIcon={<UserNavIcon size={20} active={true} />} />
          <BottomNavigation.Item key="tasks" label="Tác vụ" icon={<CategoryNavIcon size={20} active={false} />} activeIcon={<CategoryNavIcon size={20} active={true} />} />
          <BottomNavigation.Item 
            key="notification" 
            label="Thông báo" 
            icon={
              <div className="relative">
                <NotifNavIcon size={20} active={false} />
                {unreadCount > 0 && <span className="absolute -top-1.5 -right-2 bg-red-500 text-white text-[10px] font-bold px-[4px] h-[16px] min-w-[16px] flex items-center justify-center rounded-full border border-white leading-none">{unreadCount > 99 ? '99+' : unreadCount}</span>}
              </div>
            } 
            activeIcon={
              <div className="relative">
                <NotifNavIcon size={20} active={true} />
                {unreadCount > 0 && <span className="absolute -top-1.5 -right-2 bg-red-500 text-white text-[10px] font-bold px-[4px] h-[16px] min-w-[16px] flex items-center justify-center rounded-full border border-white leading-none">{unreadCount > 99 ? '99+' : unreadCount}</span>}
              </div>
            } 
          />
          <BottomNavigation.Item key="setting" label="Cài đặt" icon={<SettingNavIcon size={20} active={false} />} activeIcon={<SettingNavIcon size={20} active={true} />} />
        </BottomNavigation>
      )}

      <Modal
        visible={inactiveModalVisible}
        title="Tài khoản không hợp lệ"
        description={`Học sinh đã không còn học tập ở trường, bạn sẽ đăng xuất sau ${inactiveCountdown} giây nữa.`}
      >
        <div className="flex justify-center mt-4">
          <Spinner visible />
        </div>
      </Modal>

      {/* MODAL KHẢO SÁT BẮT BUỘC */}
      <Modal
        visible={showRequiredModal}
        title="Thông báo khảo sát"
        onClose={() => {}}
        zIndex={1200}
      >
        <div className="flex flex-col items-center text-center py-4 space-y-4">
          <div className="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center text-rose-600 font-bold text-xl shadow-sm">
            !
          </div>
          <Text className="text-base font-bold text-slate-800">
            Bạn có 1 khảo sát mới cần làm
          </Text>
          <Text className="text-xs text-slate-500 px-2">
            Đây là bài khảo sát bắt buộc. Bạn vui lòng hoàn thành trước khi tiếp tục sử dụng các tính năng khác của ứng dụng.
          </Text>
          <button
            type="button"
            onClick={() => {
              setShowRequiredModal(false);
              navigate(PATHS.SURVEYS, { replace: true });
            }}
            className="w-full py-3 bg-[#224397] text-white font-extrabold rounded-2xl shadow-md hover:bg-[#1e3a8a] transition text-xs mt-2"
          >
            Đi đến danh sách khảo sát
          </button>
        </div>
      </Modal>

      {/* MODAL KHẢO SÁT TỰ NGUYỆN */}
      <Modal
        visible={showOptionalModal}
        title="Khảo sát mới"
        onClose={() => {
          if (optionalSurvey) sessionStorage.setItem(`opt_survey_dismissed_${optionalSurvey.id}`, 'true');
          setShowOptionalModal(false);
        }}
        zIndex={1200}
      >
        <div className="flex flex-col items-center text-center py-4 space-y-4">
          <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-[#224397] font-bold text-xl shadow-sm">
            ?
          </div>
          <Text className="text-base font-bold text-slate-800">
            Bạn có 1 khảo sát mới, bạn có muốn thực hiện không
          </Text>
          <Text className="text-xs text-slate-500 px-2">
            {optionalSurvey?.title || 'Ý kiến đóng góp của bạn sẽ giúp nhà trường nâng cao chất lượng hoạt động.'}
          </Text>
          <div className="flex items-center gap-3 w-full mt-4">
            <button
              type="button"
              onClick={() => {
                if (optionalSurvey) sessionStorage.setItem(`opt_survey_dismissed_${optionalSurvey.id}`, 'true');
                setShowOptionalModal(false);
              }}
              className="flex-1 py-3 bg-slate-100 text-slate-700 font-bold rounded-2xl hover:bg-slate-200 transition text-xs border border-slate-200"
            >
              Không
            </button>
            <button
              type="button"
              onClick={() => {
                if (optionalSurvey) sessionStorage.setItem(`opt_survey_dismissed_${optionalSurvey.id}`, 'true');
                setShowOptionalModal(false);
                navigate(PATHS.SURVEYS);
              }}
              className="flex-1 py-3 bg-[#224397] text-white font-extrabold rounded-2xl shadow-md hover:bg-[#1e3a8a] transition text-xs"
            >
              Thực hiện
            </button>
          </div>
        </div>
      </Modal>

      {/* Modal cập nhật phiên bản */}
      <UpdateModal
        visible={showUpdateModal}
        isForce={isForceUpdate}
        versionData={updateData}
        onClose={() => setShowUpdateModal(false)}
      />
    </div>
  );
};

import { HashRouter } from 'react-router-dom';

const MyApp: React.FC = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <App theme="light">
        <AutoUpdater />
        <SnackbarProvider>
          <HashRouter>
            <AnimatedRoutes />
          </HashRouter>
        </SnackbarProvider>
      </App>
    </QueryClientProvider>
  );
};

export default MyApp;
