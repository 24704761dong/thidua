import React, { useEffect, useState } from 'react';
import { Box, Text, Icon, Button, useSnackbar } from 'zmp-ui';
import api from '@/lib/api';
import CustomImageViewer from './CustomImageViewer';

interface Proof {
  id: number;
  file_name: string;
  url: string;
}

interface Batch {
  batch_id: string;
  trang_thai_gui: string;
  thoi_gian_gui: string;
  so_luong_vi_pham: number;
  proofs: Proof[];
}

interface Props {
  tuanId: string;
}

export const ViolationBatchManager: React.FC<Props> = ({ tuanId }) => {
  const [batches, setBatches] = useState<Batch[]>([]);
  const [loading, setLoading] = useState(true);
  const [viewerImages, setViewerImages] = useState<string[]>([]);
  const [viewerIndex, setViewerIndex] = useState(0);
  const [isViewerOpen, setIsViewerOpen] = useState(false);
  const { openSnackbar } = useSnackbar();

  const fetchBatches = async () => {
    try {
      const res = await api.get(`/api/zalo/violation-batches?tuan_id=${tuanId}`);
      if (res.data.success) {
        setBatches(res.data.data);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBatches();
    
    // Listen for custom event to refresh when new batch submitted
    const handleRefresh = () => fetchBatches();
    window.addEventListener('refresh_violation_batches', handleRefresh);
    return () => window.removeEventListener('refresh_violation_batches', handleRefresh);
  }, [tuanId]);

  const handleViewImage = (images: string[], index: number) => {
    setViewerImages(images);
    setViewerIndex(index);
    setIsViewerOpen(true);
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'da_gui': return <span className="text-orange-500">Chờ duyệt</span>;
      case 'da_duyet': return <span className="text-green-500">Đã duyệt</span>;
      case 'da_loai_bo': return <span className="text-red-500">Đã từ chối</span>;
      default: return <span>{status}</span>;
    }
  };

  if (loading) return <div className="p-4 text-center text-slate-400">Đang tải...</div>;
  if (batches.length === 0) return null;

  return (
    <div className="pt-4">
      <h3 className="font-bold text-slate-700 text-[14px] mb-3 uppercase">Lịch sử đợt nộp trong tuần</h3>
      <div className="space-y-3">
        {batches.map((batch) => (
          <div key={batch.batch_id} className="bg-white p-3 rounded-xl shadow-sm border border-slate-100">
            <div className="flex justify-between items-start mb-2">
              <div>
                <div className="text-[14px] font-bold text-slate-800">
                  {new Date(batch.thoi_gian_gui).toLocaleString('vi-VN')}
                </div>
                <div className="text-[12px] text-slate-500 mt-1">
                  Đã nộp {batch.so_luong_vi_pham} vi phạm
                </div>
              </div>
              <div className="text-[12px] font-semibold bg-slate-50 px-2 py-1 rounded">
                {getStatusText(batch.trang_thai_gui)}
              </div>
            </div>

            {batch.proofs && batch.proofs.length > 0 && (
              <div className="mt-3">
                <Text className="text-[12px] font-semibold text-slate-600 mb-2">Minh chứng đính kèm:</Text>
                <div className="flex flex-wrap gap-2">
                  {batch.proofs.map((proof, idx) => (
                    <div 
                      key={proof.id} 
                      className="relative w-16 h-16 rounded-lg overflow-hidden border border-slate-200"
                      onClick={() => handleViewImage(batch.proofs.map(p => p.url), idx)}
                    >
                      <img src={proof.url} className="w-full h-full object-cover" alt="Proof" />
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        ))}
      </div>

      <CustomImageViewer
        images={viewerImages}
        visible={isViewerOpen}
        activeIndex={viewerIndex}
        onClose={() => setIsViewerOpen(false)}
      />
    </div>
  );
};

export default ViolationBatchManager;
