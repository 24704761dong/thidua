import { useNavigate } from "react-router-dom";
import React, { useState } from 'react';
import { Page, Text, Icon, Spinner, Button, Modal } from "zmp-ui";
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import Header from '@/components/Header';
import { PATHS } from '@/constants/paths';

interface LeaveRequest {
  id: number;
  ly_do: string;
  tu_ngay: string;
  den_ngay: string;
  trang_thai: number;
  ngay_tao: string;
  minh_chung_url?: string | null;
}

const LeaveRequestsPage: React.FC = () => {
  const navigate = useNavigate();
  const [previewImage, setPreviewImage] = useState<string | null>(null);

  const getFullProofUrl = (url?: string | null) => {
    if (!url) return '';
    if (url.startsWith('http://') || url.startsWith('https://')) return url;
    const baseUrl = api.defaults.baseURL || import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua';
    try {
      const origin = new URL(baseUrl, window.location.origin).origin;
      return origin + (url.startsWith('/') ? url : '/' + url);
    } catch {
      return (url.startsWith('/') ? '' : '/') + url;
    }
  };

  const { data, isLoading, error, refetch } = useQuery({
    queryKey: ['leave_requests'],
    queryFn: async () => {
      const res = await api.get('/api/zalo/xin-vang-hoc-list');
      return res.data;
    },
    staleTime: 0,
    refetchOnMount: 'always',
  });

  const requests: LeaveRequest[] = data?.data || [];

  const formatDate = (dateString: string) => {
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
  };

  const getStatusDisplay = (status: any) => {
    const s = Number(status);
    switch (s) {
      case 0:
        return { text: 'Chờ duyệt', color: 'text-amber-500', bg: 'bg-amber-50', border: 'border-amber-200' };
      case 1:
        return { text: 'Đã duyệt', color: 'text-emerald-500', bg: 'bg-emerald-50', border: 'border-emerald-200' };
      case 2:
        return { text: 'Từ chối', color: 'text-red-500', bg: 'bg-red-50', border: 'border-red-200' };
      default:
        return { text: 'Không xác định', color: 'text-slate-500', bg: 'bg-slate-50', border: 'border-slate-200' };
    }
  };

  return (
    <Page className="bg-slate-50 relative pb-24 flex flex-col h-screen">
      <Header variant="back" title="Lịch sử Xin vắng học" />

      <div className="flex-1 overflow-y-auto px-4 py-4 space-y-4">
        {isLoading && (
          <div className="flex justify-center py-10">
            <Spinner />
          </div>
        )}

        {!isLoading && error && (
          <div className="text-center text-red-500 py-10 bg-white rounded-xl shadow-sm">
            <Icon icon="zi-warning" className="text-red-500 mb-2" size={32} />
            <p>Có lỗi xảy ra khi tải dữ liệu.</p>
          </div>
        )}

        {!isLoading && !error && requests.length === 0 && (
          <div className="text-center text-slate-500 py-10 bg-white rounded-xl shadow-sm border border-slate-100 flex flex-col items-center">
            <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
              <Icon icon="zi-note" className="text-slate-400" size={36} />
            </div>
            <p className="font-medium text-slate-700">Chưa có đơn xin phép</p>
            <p className="text-sm mt-1">Bạn chưa gửi đơn xin vắng học nào.</p>
          </div>
        )}

        {!isLoading && !error && requests.length > 0 && (
          <div className="space-y-3">
            {requests.map((req, idx) => {
              const status = getStatusDisplay(req.trang_thai);
              return (
                <div key={idx} className={`bg-white p-4 rounded-xl shadow-sm border ${status.border} relative overflow-hidden`}>
                  <div className="flex justify-between items-start mb-2">
                    <h3 className="font-bold text-slate-800 text-[15px] leading-snug flex-1">
                      {req.ly_do}
                    </h3>
                    <div className={`px-2 py-1 rounded text-[11px] font-bold ${status.bg} ${status.color}`}>
                      {status.text}
                    </div>
                  </div>
                  
                  <div className="flex items-center text-[13px] text-slate-600 mb-2">
                    <Icon icon="zi-calendar" size={14} className="mr-1.5 text-slate-400" />
                    Từ <span className="font-medium text-slate-700 mx-1">{formatDate(req.tu_ngay)}</span> 
                    đến <span className="font-medium text-slate-700 ml-1">{formatDate(req.den_ngay)}</span>
                  </div>

                  <div className="flex items-center text-[12px] text-slate-500 mt-2">
                    <Icon icon="zi-clock-1" size={12} className="mr-1" />
                    Ngày tạo: {formatDate(req.ngay_tao)}
                  </div>
                  
                  {req.minh_chung_url && (
                    <div className="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                      <button 
                        type="button"
                        onClick={() => setPreviewImage(getFullProofUrl(req.minh_chung_url))}
                        className="text-[#224397] text-[13px] font-medium flex items-center hover:underline focus:outline-none"
                      >
                        <Icon icon="zi-photo" size={16} className="mr-1 text-blue-500" />
                        Xem minh chứng đính kèm
                      </button>
                      <img 
                        src={getFullProofUrl(req.minh_chung_url)} 
                        alt="Minh chứng" 
                        onClick={() => setPreviewImage(getFullProofUrl(req.minh_chung_url))}
                        className="w-10 h-10 rounded-lg object-cover border border-slate-200 cursor-pointer hover:opacity-80 transition-opacity"
                        onError={(e) => { (e.target as HTMLElement).style.display = 'none'; }}
                      />
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        )}
      </div>

      {/* Modal Xem ảnh minh chứng */}
      <Modal
        visible={!!previewImage}
        title="Minh chứng xin vắng học"
        onClose={() => setPreviewImage(null)}
      >
        <div className="p-2 flex flex-col gap-3 items-center">
          {previewImage && (
            <div className="w-full max-h-[60vh] overflow-hidden rounded-xl bg-slate-100 flex items-center justify-center border border-slate-200">
              <img 
                src={previewImage} 
                alt="Minh chứng" 
                className="max-h-[60vh] max-w-full object-contain"
              />
            </div>
          )}
          <div className="flex items-center justify-center w-full pt-2 border-t border-slate-100">
            <Button
              fullWidth
              variant="primary"
              size="medium"
              onClick={() => setPreviewImage(null)}
              className="bg-[#224397] rounded-xl font-medium"
            >
              Đóng
            </Button>
          </div>
        </div>
      </Modal>

      <div className="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-slate-100 shadow-[0_-5px_10px_rgba(0,0,0,0.02)] z-50">
        <Button 
          fullWidth 
          variant="primary" 
          onClick={() => navigate(PATHS.LEAVE_REQUEST_CREATE)}
          className="rounded-xl h-11"
        >
          Tạo đơn mới
        </Button>
      </div>
    </Page>
  );
};

export default LeaveRequestsPage;
