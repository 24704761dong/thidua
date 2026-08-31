import React from 'react';
import { Icon } from '@/components/Icon';

interface TaskItemProps {
  title: string;
  icon: string;
  onClick?: () => void;
  badge?: string | boolean;
  action?: 'add' | 'remove';
  locked?: boolean;
}

export const TaskItem: React.FC<TaskItemProps> = ({ title, icon, onClick, badge, action, locked }) => {
  return (
    <div className={`flex flex-col items-center text-center cursor-pointer group active:opacity-70 transition-opacity relative ${locked ? 'opacity-50 grayscale' : ''}`} onClick={onClick}>
      <div className="w-[60px] h-[60px] mb-2 mx-auto bg-white rounded-full flex items-center justify-center border border-[#224397]/20 shadow-sm group-active:bg-slate-50 transition-colors relative">
        <Icon icon={icon} className="text-[#224397]" size={32} />
        {locked && (
          <div className="absolute -top-1 -right-1 bg-slate-500 text-white w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-white shadow-sm z-10">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="w-3 h-3">
              <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
          </div>
        )}
        {badge && !action && (
          <div className="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full border border-white z-10 shadow-sm">
            {badge === true ? 'Mới' : badge}
          </div>
        )}
        {action === 'add' && (
          <div className="absolute -top-1 -right-1 bg-[#FAB723] text-white w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-white shadow-sm z-10">
            <Icon icon="zi-plus" size={14} />
          </div>
        )}
        {action === 'remove' && (
          <div className="absolute -top-1 -right-1 bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center font-bold border-2 border-white shadow-sm z-10">
            <Icon icon="zi-close" size={12} />
          </div>
        )}
      </div>
      <span className="text-[11px] text-slate-700 leading-snug font-medium px-0.5 line-clamp-2 pt-0.5">{title}</span>
    </div>
  );
};
