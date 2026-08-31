export interface TaskDefinition {
  id: string;
  title: string;
  icon: string;
  isNew?: boolean;
}

export interface TaskSection {
  title: string;
  items: TaskDefinition[];
}

export const TASK_SECTIONS: TaskSection[] = [
  {
    title: 'Học tập & Khảo thí',
    items: [
      { id: 'lich-hoc-thi', title: 'Lịch học/thi', icon: 'zi-calendar' },
      { id: 'diem-thi', title: 'Điểm thi', icon: 'zi-star' },
      { id: 'tinh-diem-tb', title: 'Tính điểm TB', icon: 'zi-more-grid' },
      { id: 'phuc-khao', title: 'Phúc khảo', icon: 'zi-note' },
    ]
  },
  {
    title: 'Nề nếp',
    items: [
      { id: 'ket-qua-tuan', title: 'Kết quả tuần', icon: 'zi-list-1', isNew: true },
      { id: 'thanh-tich', title: 'Thành tích', icon: 'zi-check-circle' },
      { id: 'vi-pham', title: 'Vi phạm', icon: 'zi-warning' },
      { id: 'hoat-dong', title: 'Hoạt động', icon: 'zi-group' },
    ]
  },
  {
    title: 'Tác vụ',
    items: [
      { id: 'nhap-vi-pham', title: 'Nhập vi phạm', icon: 'zi-edit-text', isNew: true },
      { id: 'so-nhat-ky', title: 'Sổ nhật kỳ', icon: 'zi-post' },
      { id: 'cap-quyen', title: 'Cấp quyền', icon: 'zi-setting' },
      { id: 'lich-truc', title: 'Lịch trực', icon: 'zi-list-1' },
      { id: 'xin-vang-hoc', title: 'Xin vắng học', icon: 'zi-close-circle' },
    ]
  },
  {
    title: 'Hành chính',
    items: [
      { id: 'email-hoc-sinh', title: 'Email học sinh', icon: 'zi-mail' },
      { id: 'khao-sat', title: 'Khảo sát', icon: 'zi-chat' },
    ]
  }
];

// Helper to get task by id
export const getTaskById = (id: string): TaskDefinition | undefined => {
  for (const section of TASK_SECTIONS) {
    const task = section.items.find(item => item.id === id);
    if (task) return task;
  }
  return undefined;
};
