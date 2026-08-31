import React, { useState, useRef } from 'react';
import { Page, Box, Text, Icon, Button, Input, Select, useSnackbar, Spinner } from 'zmp-ui';
import { useNavigate } from 'react-router-dom';
import api from '@/lib/api';
import { queryClient } from '@/lib/queryClient';
import Header from '@/components/Header';

const LeaveRequestCreatePage: React.FC = () => {
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();

  const [lyDo, setLyDo] = useState('');
  const [tuNgay, setTuNgay] = useState('');
  const [denNgay, setDenNgay] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  const [isUploading, setIsUploading] = useState(false);
  const [cloudKey, setCloudKey] = useState('');
  const [previewUrl, setPreviewUrl] = useState('');
  
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleUploadClick = () => {
    fileInputRef.current?.click();
  };

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
      openSnackbar({ text: 'Dung lượng file tối đa là 10MB', type: 'error' });
      return;
    }

    const formData = new FormData();
    formData.append('file', file);

    setIsUploading(true);
    try {
      const res = await api.post('/api/zalo/xin-vang-hoc-upload-proof', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (res.data.success) {
        setCloudKey(res.data.data.cloud_key);
        setPreviewUrl(res.data.data.url);
        openSnackbar({ text: 'Tải minh chứng thành công', type: 'success' });
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi tải ảnh', type: 'error' });
      }
    } catch (err: any) {
      openSnackbar({ text: err.response?.data?.message || 'Lỗi tải ảnh', type: 'error' });
    } finally {
      setIsUploading(false);
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    }
  };

  const removeProof = () => {
    setCloudKey('');
    setPreviewUrl('');
  };

  const handleSubmit = async () => {
    if (!lyDo.trim()) {
      openSnackbar({ text: 'Vui lòng nhập lý do', type: 'warning' });
      return;
    }
    if (!tuNgay || !denNgay) {
      openSnackbar({ text: 'Vui lòng chọn thời gian', type: 'warning' });
      return;
    }

    const start = new Date(tuNgay);
    const end = new Date(denNgay);
    if (start > end) {
      openSnackbar({ text: 'Ngày kết thúc không hợp lệ', type: 'warning' });
      return;
    }

    setIsSubmitting(true);
    try {
      const res = await api.post('/api/zalo/xin-vang-hoc-submit', {
        ly_do: lyDo,
        tu_ngay: tuNgay,
        den_ngay: denNgay,
        cloud_key: cloudKey
      });

      if (res.data.success) {
        openSnackbar({ text: res.data.message, type: 'success' });
        await queryClient.invalidateQueries({ queryKey: ['leave_requests'] });
        navigate(-1); // Quay lại trang danh sách
      } else {
        openSnackbar({ text: res.data.message || 'Có lỗi xảy ra', type: 'error' });
      }
    } catch (err: any) {
      openSnackbar({ text: err.response?.data?.message || 'Lỗi kết nối server', type: 'error' });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <Page className="bg-slate-50 relative pb-24 h-screen overflow-y-auto">
      <Header variant="back" title="Tạo đơn xin vắng học" />

      <div className="p-4 space-y-4 mt-2">
        
        <div className="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <Text className="font-semibold text-slate-800 mb-3 text-[15px]">Thời gian nghỉ</Text>
          <div className="grid grid-cols-2 gap-3">
            <div className="min-w-0">
              <Text className="text-[13px] text-slate-500 mb-1">Từ ngày <span className="text-red-500">*</span></Text>
              <input
                type="date"
                value={tuNgay}
                onChange={(e) => setTuNgay(e.target.value)}
                className="w-full max-w-full box-border block appearance-none min-w-0 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[14px] outline-none focus:border-blue-500 transition-colors"
              />
            </div>
            <div className="min-w-0">
              <Text className="text-[13px] text-slate-500 mb-1">Đến ngày <span className="text-red-500">*</span></Text>
              <input
                type="date"
                value={denNgay}
                onChange={(e) => setDenNgay(e.target.value)}
                className="w-full max-w-full box-border block appearance-none min-w-0 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[14px] outline-none focus:border-blue-500 transition-colors"
              />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <Text className="font-semibold text-slate-800 mb-3 text-[15px]">Lý do xin nghỉ <span className="text-red-500">*</span></Text>
          <Input.TextArea
            value={lyDo}
            onChange={(e) => setLyDo(e.target.value)}
            placeholder="Ghi rõ lý do xin phép nghỉ học..."
            showCount
            maxLength={255}
            className="!m-0 bg-slate-50 !border-slate-200 !rounded-lg"
          />
        </div>

        <div className="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
          <Text className="font-semibold text-slate-800 mb-3 text-[15px]">Minh chứng (Tùy chọn)</Text>
          
          <input
            type="file"
            accept="image/*"
            ref={fileInputRef}
            onChange={handleFileChange}
            style={{ display: 'none' }}
          />

          {!previewUrl && (
            <div 
              onClick={handleUploadClick}
              className="border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 p-6 flex flex-col items-center justify-center cursor-pointer hover:bg-slate-100 transition-colors"
            >
              {isUploading ? (
                <Spinner />
              ) : (
                <>
                  <div className="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-2">
                    <Icon icon="zi-camera" className="text-blue-500" size={24} />
                  </div>
                  <Text className="text-sm font-medium text-slate-700">Chụp hoặc tải ảnh lên</Text>
                  <Text className="text-xs text-slate-500 mt-1">Hỗ trợ JPG, PNG (Tối đa 10MB)</Text>
                </>
              )}
            </div>
          )}

          {previewUrl && (
            <div className="relative rounded-xl overflow-hidden border border-slate-200 bg-slate-100 group">
              <img src={previewUrl} alt="Preview" className="w-full h-auto object-cover max-h-[200px]" />
              <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <Button variant="secondary" size="small" onClick={removeProof}>
                  Xóa ảnh
                </Button>
              </div>
              <div 
                className="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg"
                onClick={removeProof}
              >
                <Icon icon="zi-close" size={16} />
              </div>
            </div>
          )}
        </div>

      </div>

      <div className="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-slate-100 shadow-[0_-5px_10px_rgba(0,0,0,0.02)] z-50">
        <Button 
          fullWidth 
          variant="primary" 
          onClick={handleSubmit}
          loading={isSubmitting}
          className="rounded-xl h-11 text-[15px]"
        >
          Gửi đơn xin phép
        </Button>
      </div>
    </Page>
  );
};

export default LeaveRequestCreatePage;
