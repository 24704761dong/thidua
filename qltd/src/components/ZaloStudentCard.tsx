import React, { useState } from 'react';
import QRCode from 'react-qr-code';

interface ZaloStudentCardProps {
  studentData?: {
    ho_dem?: string;
    ten?: string;
    anh_the?: string;
    ngay_sinh?: string;
    ten_lop?: string;
    lop?: string;
    ten_nam_hoc?: string;
    ma_hoc_sinh?: string;
    cccd?: string;
  };
  imageUrl?: string;
}

export const ZaloStudentCard: React.FC<ZaloStudentCardProps> = ({ studentData }) => {
  const [imgError, setImgError] = useState(false);

  const fullName = `${studentData?.ho_dem || ''} ${studentData?.ten || ''}`.trim() || 'HỌC SINH';
  const initialChar = (studentData?.ten || fullName || 'H').charAt(0).toUpperCase();

  const formatDob = (dob?: string) => {
    if (!dob) return '-';
    if (dob.includes('/')) return dob;
    const parts = dob.split('-');
    if (parts.length === 3) {
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dob;
  };

  const avatarUrl = studentData?.avatar_url || studentData?.anh_the_url
    ? (studentData.avatar_url || studentData.anh_the_url)
    : (studentData?.anh_the
        ? (studentData.anh_the.startsWith('http')
            ? studentData.anh_the
            : `${import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua'}/public/assets/anh_the/${studentData.anh_the}`)
        : `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=1e3a8a&color=ffffff`);

  const qrValue = studentData?.ma_hoc_sinh || studentData?.cccd || fullName;

  return (
    <div className="bg-gradient-to-r from-[#ebf5fe] via-[#e2f1fe] to-[#d3ebfd] rounded-[22px] p-3.5 sm:p-4 border border-[#bce1fc] shadow-[0_4px_16px_rgba(34,67,151,0.06)] flex items-center justify-between gap-3 select-none">
      
      {/* 1. Ảnh thẻ học sinh (Tỷ lệ đứng 3x4) */}
      <div className="w-[72px] h-[96px] sm:w-[78px] sm:h-[104px] rounded-xl overflow-hidden bg-white border-2 border-white shadow-xs shrink-0 flex items-center justify-center">
        {!imgError ? (
          <img 
            src={avatarUrl} 
            alt="Ảnh thẻ" 
            onError={() => setImgError(true)}
            className="w-full h-full object-cover" 
          />
        ) : (
          <div className="w-full h-full bg-[#1e3a8a] text-white flex items-center justify-center font-black text-xl">
            {initialChar}
          </div>
        )}
      </div>

      {/* 2. Thông tin học sinh */}
      <div className="flex flex-col flex-1 min-w-0 justify-center">
        {/* Tên học sinh màu đỏ san hô nổi bật */}
        <h3 className="text-[#e11d48] font-black text-[15px] sm:text-[16px] uppercase tracking-wide leading-tight truncate mb-1.5">
          {fullName}
        </h3>

        <div className="flex flex-col gap-0.5 text-[11.5px] sm:text-[12px] text-slate-700">
          <div className="flex items-center gap-1.5">
            <span className="text-slate-500 font-medium">Năm sinh:</span>
            <span className="font-bold text-slate-800">{formatDob(studentData?.ngay_sinh)}</span>
          </div>

          <div className="flex items-center gap-1.5">
            <span className="text-slate-500 font-medium">Lớp:</span>
            <span className="font-bold text-slate-800">{studentData?.ten_lop || studentData?.lop || '-'}</span>
          </div>

          <div className="flex items-center gap-1.5">
            <span className="text-slate-500 font-medium">Năm học:</span>
            <span className="font-bold text-slate-800">{studentData?.ten_nam_hoc || '2026 - 2027'}</span>
          </div>
        </div>
      </div>

      {/* 3. QR Code học sinh */}
      <div className="shrink-0 p-1.5 bg-white rounded-xl border border-blue-100/80 shadow-xs flex items-center justify-center">
        <QRCode 
          value={qrValue} 
          size={66}
          style={{ height: "auto", maxWidth: "100%", width: "100%" }}
          viewBox={`0 0 256 256`}
        />
      </div>

    </div>
  );
};
