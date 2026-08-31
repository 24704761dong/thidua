import React from 'react';
import { useNavigate } from 'react-router-dom';
import { navigateBack } from '@/utils/navigation';
import logoImg from '@/assets/logo.png';
import { Icon } from '@/components/Icon';

type HeaderProps = 
  | { variant?: 'logo'; title?: string; showBack?: boolean; showBackIcon?: boolean; rightContent?: React.ReactNode }
  | { variant: 'back'; title: string; rightContent?: React.ReactNode }
  | { title: string; showBack?: boolean; showBackIcon?: boolean; rightContent?: React.ReactNode };

const Header: React.FC<HeaderProps> = (props) => {
  const navigate = useNavigate();

  const isBackVariant = 
    ('variant' in props && props.variant === 'back') || 
    ('showBack' in props && props.showBack) || 
    ('showBackIcon' in props && props.showBackIcon);

  return (
    <div 
      className="z-40 bg-white/95 sticky top-0 border-b border-slate-100 shadow-[0_1px_3px_rgba(0,0,0,0.02)] select-none"
    >
      {!isBackVariant ? (
        <div className="flex items-center justify-between h-13 px-4">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 flex items-center justify-center">
              <img src={logoImg} alt="Logo" className="w-full h-full object-contain" />
            </div>
            <span className="font-extrabold text-[14px] uppercase tracking-wide text-[#1e3a8a]">
              Trường THPT Bình Sơn
            </span>
          </div>
        </div>
      ) : (
        <div className="flex items-center justify-between h-13 px-2">
          <div className="flex items-center gap-1 min-w-0">
            <button
              type="button"
              className="flex items-center justify-center w-10 h-10 rounded-full hover:bg-slate-100 active:bg-slate-200 active:scale-95 text-[#1e3a8a] transition-all cursor-pointer border-none bg-transparent shrink-0"
              onClick={() => navigateBack(navigate)}
            >
              <Icon icon="zi-arrow-left" size={24} />
            </button>
            <span className="font-bold text-[15px] text-[#1e3a8a] truncate">
              {props.title || 'Quay lại'}
            </span>
          </div>
          {props.rightContent && (
            <div className="pr-2 shrink-0">
              {props.rightContent}
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default Header;
