import logoImg from '@/assets/logo.png';
import React, { useState, useEffect } from 'react';
import { Page, useSnackbar, Spinner } from 'zmp-ui';
import Header from '@/components/Header';
import { TASK_SECTIONS, getTaskById, TaskDefinition } from '@/constants/tasks';
import { useFavoriteTasks } from '@/hooks/useFavoriteTasks';
import { TaskItem } from '@/components/TaskItem';
import { useNavigate } from 'react-router-dom';
import { navigateBack } from '@/utils/navigation';

const EditTasksPage: React.FC = () => {
  const { taskIds, saveTasks, loading } = useFavoriteTasks();
  const [selectedIds, setSelectedIds] = useState<string[]>([]);
  const [isSaving, setIsSaving] = useState(false);
  const { openSnackbar } = useSnackbar();
  const navigate = useNavigate();

  useEffect(() => {
    setSelectedIds(taskIds);
  }, [taskIds]);

  const handleAdd = (id: string) => {
    if (selectedIds.includes(id)) return;
    if (selectedIds.length >= 4) {
      openSnackbar({ text: 'Chỉ được chọn tối đa 4 tiện ích', type: 'warning' });
      return;
    }
    setSelectedIds([...selectedIds, id]);
  };

  const handleRemove = (id: string) => {
    setSelectedIds(selectedIds.filter(t => t !== id));
  };

  const handleSave = async () => {
    setIsSaving(true);
    const success = await saveTasks(selectedIds);
    setIsSaving(false);
    if (success) {
      openSnackbar({ text: 'Lưu thành công', type: 'success' });
      navigateBack(navigate);
    } else {
      openSnackbar({ text: 'Có lỗi xảy ra, vui lòng thử lại', type: 'error' });
    }
  };

  const selectedTasks = selectedIds.map(getTaskById).filter((t): t is TaskDefinition => t !== undefined);

  if (loading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page hideScrollbar className="bg-transparent relative">
      <Header 
        variant="back" 
        title="Sửa tiện ích yêu thích" 
        rightContent={
          <button 
            className="bg-[#e53935] text-white px-4 py-1.5 rounded-lg font-medium text-sm active:opacity-80 transition-opacity disabled:opacity-50"
            onClick={handleSave}
            disabled={isSaving || loading}
          >
            {isSaving ? 'Đang lưu...' : 'Lưu'}
          </button>
        }
      />

      <div className="flex flex-col">
        {/* Selected Tasks Section */}
        <div className="bg-white/90 backdrop-blur-md px-4 py-6 mb-4 rounded-2xl shadow-sm border border-slate-100 mx-4 mt-4">
          <div className="flex items-center justify-between mb-2">
            <h3 className="text-[16px] font-bold text-slate-800">Tiện ích yêu thích</h3>
            <span className="text-sm text-slate-500">{selectedIds.length}/4</span>
          </div>

          <div className="grid grid-cols-4 gap-y-6 gap-x-2 mt-6">
            {selectedTasks.map(task => (
              <TaskItem 
                key={task.id} 
                title={task.title} 
                icon={task.icon} 
                action="remove"
                onClick={() => handleRemove(task.id)}
              />
            ))}
            {selectedTasks.length === 0 && (
              <div className="col-span-4 text-center py-4 text-slate-400 text-sm">
                Chưa có tiện ích nào được chọn
              </div>
            )}
          </div>
        </div>

        {/* Available Tasks Section */}
        <div className="bg-white/90 backdrop-blur-md px-4 pt-6 pb-24 rounded-2xl shadow-sm border border-slate-100 mx-4 mb-12">
          <div className="flex items-center justify-between mb-2">
            <h3 className="text-[16px] font-bold text-slate-800">Thêm dịch vụ</h3>
          </div>

          <div className="flex flex-col gap-8 mt-6">
            {TASK_SECTIONS.map((section, index) => (
              <div key={index}>
                <h4 className="text-[13px] font-bold text-[#224397] mb-4 uppercase">
                  {section.title}
                </h4>
                <div className="grid grid-cols-4 gap-y-6 gap-x-2">
                  {section.items.map(task => {
                    const isSelected = selectedIds.includes(task.id);
                    return (
                      <div key={task.id} className={isSelected ? 'opacity-50 grayscale' : ''}>
                        <TaskItem 
                          title={task.title} 
                          icon={task.icon} 
                          action={isSelected ? undefined : 'add'}
                          badge={task.isNew && !isSelected ? 'Mới' : undefined}
                          onClick={() => {
                            if (!isSelected) handleAdd(task.id);
                          }}
                        />
                      </div>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </Page>
  );
};

export default EditTasksPage;
