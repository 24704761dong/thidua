import logoImg from '@/assets/logo.png';
import React, { useState, useEffect } from 'react';
import { Page, Spinner } from 'zmp-ui';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';

const TuitionPage: React.FC = () => {
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 1500); // 1.5 seconds fake loading
    return () => clearTimeout(timer);
  }, []);

  return (
    <Page className="bg-slate-50 relative">
      <Header title="Học phí" showBack={true} />
      
      <div className="flex flex-col items-center justify-center h-[calc(100vh-100px)] p-6 text-center">
        {isLoading ? (
          <div className="flex flex-col items-center justify-center space-y-4">
            <Spinner visible logo={logoImg} />
            <p className="text-slate-500 text-[14px] animate-pulse">Đang tải dữ liệu...</p>
          </div>
        ) : (
          <>
            <div className="relative mb-8">
              <div className="absolute inset-0 bg-emerald-100 rounded-full animate-ping opacity-50 blur-xl"></div>
              <div className="relative w-28 h-28 bg-gradient-to-tr from-emerald-50 to-white rounded-full flex items-center justify-center shadow-lg border border-white">
                <div className="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center">
                  <Icon name="zi-qrline" className="text-emerald-500 !text-[40px]" />
                </div>
              </div>
            </div>
            <h2 className="text-xl font-bold text-slate-800 mb-3">
              Chưa có dữ liệu
            </h2>
            <p className="text-slate-500 text-[15px] leading-relaxed max-w-[280px]">
              Hiện tại chưa có thông tin học phí nào cần thanh toán trên hệ thống.
            </p>
          </>
        )}
      </div>
    </Page>
  );
};

export default TuitionPage;
