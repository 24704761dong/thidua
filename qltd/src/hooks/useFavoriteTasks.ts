import { useState, useEffect } from 'react';
import api from '@/lib/api';
import { TASK_SECTIONS, TaskDefinition } from '@/constants/tasks';

export const DEFAULT_TASKS = ['lich-hoc-thi', 'vi-pham', 'thanh-tich', 'xin-vang-hoc'];
const STORAGE_KEY = 'qltd_favorite_tasks';

const getCachedTasks = (): string[] => {
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed;
      }
    }
  } catch (e) {}
  return DEFAULT_TASKS;
};

export const useFavoriteTasks = () => {
  const [taskIds, setTaskIds] = useState<string[]>(getCachedTasks);
  const [loading, setLoading] = useState(false);

  const fetchTasks = async () => {
    try {
      const res = await api.get('/api/zalo/get-favorite-tasks');
      if (res.data?.success && Array.isArray(res.data.tasks) && res.data.tasks.length > 0) {
        setTaskIds(res.data.tasks);
        try {
          localStorage.setItem(STORAGE_KEY, JSON.stringify(res.data.tasks));
        } catch (e) {}
      }
    } catch (error) {
      console.warn('Could not fetch favorite tasks, using cached or defaults.', error);
    }
  };

  useEffect(() => {
    fetchTasks();

    const handleUpdate = () => {
      setTaskIds(getCachedTasks());
      fetchTasks();
    };

    window.addEventListener('favorite_tasks_updated', handleUpdate);
    return () => {
      window.removeEventListener('favorite_tasks_updated', handleUpdate);
    };
  }, []);

  const saveTasks = async (newTasks: string[]) => {
    try {
      // 1. Lưu ngay lập tức vào state & localStorage
      setTaskIds(newTasks);
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newTasks));
      } catch (e) {}

      // 2. Gửi lưu lên backend CSDL
      const res = await api.post('/api/zalo/update-favorite-tasks', { tasks: newTasks });
      
      // 3. Báo cho tất cả các trang / component đang lắng nghe
      window.dispatchEvent(new Event('favorite_tasks_updated'));
      
      return res.data?.success ?? true;
    } catch (error) {
      console.error('Failed to save tasks to server, kept in local storage', error);
      window.dispatchEvent(new Event('favorite_tasks_updated'));
      return true;
    }
  };

  // Helper to get full task objects
  const getTasks = (): TaskDefinition[] => {
    const allTasks: TaskDefinition[] = [];
    TASK_SECTIONS.forEach(section => {
      allTasks.push(...section.items);
    });

    return taskIds
      .map(id => allTasks.find(t => t.id === id))
      .filter((t): t is TaskDefinition => t !== undefined);
  };

  return {
    taskIds,
    tasks: getTasks(),
    loading,
    saveTasks,
    refresh: fetchTasks
  };
};
