import logoImg from '@/assets/logo.png';
import React, { useEffect, useState, useMemo } from 'react';
import { Page, Text, useSnackbar, Spinner, Button, Modal, Icon, Box } from 'zmp-ui';
import { useParams, useNavigate } from 'react-router-dom';
import api from '@/lib/api';
import Header from '@/components/Header';

interface Student {
  id: number;
  name: string;
}

interface ScheduleDay {
  index: number;
  name: string;
  date: string;
  students: number[];
}

interface DutyDetailData {
  week_name: string;
  status: string;
  is_locked: boolean;
  students: Student[];
  schedule: ScheduleDay[];
}

const DutyInputPage: React.FC = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();

  const [data, setData] = useState<DutyDetailData | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  // Trạng thái cho Modal chọn học sinh
  const [modalVisible, setModalVisible] = useState(false);
  const [activeDayIndex, setActiveDayIndex] = useState<number | null>(null);
  const [selectedStudentIds, setSelectedStudentIds] = useState<number[]>([]);
  const [searchQuery, setSearchQuery] = useState('');

  useEffect(() => {
    fetchData();
  }, [id]);

  const fetchData = async () => {
    try {
      setLoading(true);
      const res = await api.post('/api/zalo/duty-detail', { tuan_hoc_id: id });
      if (res.data?.success) {
        setData(res.data.data);
      } else {
        openSnackbar({ text: res.data?.message || 'Lỗi lấy dữ liệu', type: 'error' });
      }
    } catch (error) {
      console.error(error);
      openSnackbar({ text: 'Lỗi kết nối', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleOpenStudentModal = (dayIndex: number) => {
    if (data?.is_locked) return;
    setActiveDayIndex(dayIndex);
    const day = data?.schedule.find(d => d.index === dayIndex);
    setSelectedStudentIds(day?.students || []);
    setSearchQuery('');
    setModalVisible(true);
  };

  const handleToggleStudent = (studentId: number) => {
    setSelectedStudentIds(prev => {
      if (prev.includes(studentId)) {
        return prev.filter(id => id !== studentId);
      } else {
        return [...prev, studentId];
      }
    });
  };

  const handleConfirmStudents = () => {
    if (activeDayIndex === null || !data) return;
    
    const newSchedule = data.schedule.map(day => {
      if (day.index === activeDayIndex) {
        return { ...day, students: [...selectedStudentIds] };
      }
      return day;
    });

    setData({ ...data, schedule: newSchedule });
    setModalVisible(false);
  };

  const handleRemoveStudentFromDay = (dayIndex: number, studentId: number) => {
    if (data?.is_locked) return;
    const newSchedule = data.schedule.map(day => {
      if (day.index === dayIndex) {
        return { ...day, students: day.students.filter(id => id !== studentId) };
      }
      return day;
    });
    setData({ ...data, schedule: newSchedule });
  };

  const handleSave = async () => {
    if (!data) return;
    try {
      setSaving(true);
      const payloadSchedule: Record<number, number[]> = {};
      data.schedule.forEach(day => {
        if (day.students.length > 0) {
          payloadSchedule[day.index] = day.students;
        }
      });

      const res = await api.post('/api/zalo/duty-submit', {
        tuan_hoc_id: id,
        schedule: payloadSchedule
      });

      if (res.data?.success) {
        openSnackbar({ text: res.data.message, type: 'success' });
        navigate(-1);
      } else {
        openSnackbar({ text: res.data?.message || 'Lỗi lưu dữ liệu', type: 'error' });
      }
    } catch (error) {
      console.error(error);
      openSnackbar({ text: 'Lỗi kết nối khi lưu', type: 'error' });
    } finally {
      setSaving(false);
    }
  };

  const filteredStudents = useMemo(() => {
    if (!data) return [];
    if (!searchQuery) return data.students;
    const lowerQuery = searchQuery.toLowerCase();
    // Helper remove accents
    const removeAccents = (str: string) => {
      return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    };
    const unaccentedQuery = removeAccents(lowerQuery);
    
    return data.students.filter(s => {
      return s.name.toLowerCase().includes(lowerQuery) || removeAccents(s.name.toLowerCase()).includes(unaccentedQuery);
    });
  }, [data, searchQuery]);


  if (loading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  if (!data) {
    return (
      <Page className="bg-transparent">
        <Header variant="back" title="Đăng ký trực" />
        <div className="p-4 text-center text-slate-500">Không tìm thấy dữ liệu.</div>
      </Page>
    );
  }

  const studentMap = new Map(data.students.map(s => [s.id, s.name]));

  return (
    <Page className="bg-[#f4f7f9] relative pb-24">
      <Header variant="back" title="Phân Công Trực" />
      
      <div className="p-4">
        <div className="bg-white p-4 rounded-xl shadow-sm mb-4">
          <Text className="font-bold text-[16px] text-[#224397] mb-1">{data.week_name}</Text>
          <div className="flex items-center justify-between">
            <Text className="text-sm text-slate-600">Trạng thái:</Text>
            {data.status === 'Đã duyệt' ? (
              <span className="px-2 py-0.5 text-xs font-bold rounded-md bg-green-100 text-green-600">Đã duyệt</span>
            ) : data.status === 'Chờ duyệt' ? (
              <span className="px-2 py-0.5 text-xs font-bold rounded-md bg-blue-100 text-blue-600">Chờ duyệt</span>
            ) : (
              <span className="px-2 py-0.5 text-xs font-bold rounded-md bg-slate-100 text-slate-600">Chưa nộp</span>
            )}
          </div>
          {data.is_locked && (
            <div className="mt-3 p-2 bg-orange-50 text-orange-600 rounded-lg text-xs flex gap-2 items-center">
              <Icon icon="zi-info-circle" size={16} />
              Lịch trực đã được duyệt và khóa, không thể chỉnh sửa.
            </div>
          )}
        </div>

        <div className="space-y-4">
          {data.schedule.map((day) => (
            <div key={day.index} className="bg-white rounded-xl shadow-sm overflow-hidden border border-slate-100">
              <div className="bg-slate-50 px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                <Text className="font-bold text-[#224397]">{day.name} <span className="text-slate-500 text-sm font-normal ml-1">({day.date})</span></Text>
                {!data.is_locked && (
                  <button 
                    type="button"
                    onClick={() => handleOpenStudentModal(day.index)}
                    className="flex items-center gap-1 text-[#224397] text-xs font-bold px-2 py-1 rounded bg-[#224397]/10 active:bg-[#224397]/20 transition-colors"
                  >
                    <Icon icon="zi-plus" size={14} /> Thêm
                  </button>
                )}
              </div>
              <div className="p-3">
                {day.students.length === 0 ? (
                  <Text className="text-sm text-slate-400 italic text-center py-2">Chưa phân công học sinh nào</Text>
                ) : (
                  <div className="flex flex-wrap gap-2">
                    {day.students.map(studentId => (
                      <div key={studentId} className="flex items-center bg-slate-100 px-3 py-1.5 rounded-full text-sm border border-slate-200">
                        <span className="text-slate-700 font-medium">{studentMap.get(studentId) || 'Không xác định'}</span>
                        {!data.is_locked && (
                          <button 
                            onClick={() => handleRemoveStudentFromDay(day.index, studentId)}
                            className="ml-2 text-slate-400 hover:text-red-500 active:text-red-600"
                          >
                            <Icon icon="zi-close" size={14} />
                          </button>
                        )}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Floating Action Button */}
      {!data.is_locked && (
        <div className="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-slate-100 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] z-10 pb-safe">
          <Button 
            fullWidth 
            onClick={handleSave} 
            loading={saving}
            className="bg-[#224397] h-12 rounded-xl text-base font-bold shadow-md"
          >
            Lưu Lịch Trực
          </Button>
        </div>
      )}

      {/* Modal Chọn Học Sinh */}
      <Modal
        visible={modalVisible}
        title={`Chọn HS trực - ${data.schedule.find(d => d.index === activeDayIndex)?.name}`}
        onClose={() => setModalVisible(false)}
        zIndex={1200}
        className="student-select-modal"
      >
        <div className="flex flex-col h-[60vh]">
          <div className="px-1 pb-3">
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Icon icon="zi-search" size={16} className="text-slate-400" />
              </div>
              <input
                type="text"
                placeholder="Tìm kiếm học sinh..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-9 pr-3 py-2 bg-slate-100 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#224397]/20 outline-none transition-all"
              />
            </div>
          </div>
          
          <div className="flex-1 overflow-y-auto px-1 space-y-2 pb-4">
            {filteredStudents.length === 0 ? (
              <div className="text-center py-6 text-slate-400 text-sm">Không tìm thấy học sinh.</div>
            ) : (
              filteredStudents.map(student => {
                const isSelected = selectedStudentIds.includes(student.id);
                return (
                  <div 
                    key={student.id} 
                    onClick={() => handleToggleStudent(student.id)}
                    className={`flex items-center gap-3 p-3 rounded-xl border ${isSelected ? 'border-[#224397] bg-[#224397]/5' : 'border-slate-100 bg-white'} transition-colors`}
                  >
                    <div className={`w-5 h-5 rounded flex items-center justify-center border ${isSelected ? 'bg-[#224397] border-[#224397]' : 'border-slate-300'}`}>
                      {isSelected && <Icon icon="zi-check" size={12} className="text-white" />}
                    </div>
                    <Text className={`font-medium ${isSelected ? 'text-[#224397]' : 'text-slate-700'}`}>{student.name}</Text>
                  </div>
                );
              })
            )}
          </div>
          
          <div className="pt-3 border-t border-slate-100 mt-auto">
            <Button 
              fullWidth 
              onClick={handleConfirmStudents}
              className="bg-[#224397] rounded-xl font-bold"
            >
              Xác nhận ({selectedStudentIds.length})
            </Button>
          </div>
        </div>
      </Modal>
    </Page>
  );
};

export default DutyInputPage;
