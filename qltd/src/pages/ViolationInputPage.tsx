import logoImg from '@/assets/logo.png';
import { useNavigate } from "react-router-dom";
import React, { useEffect, useState, useRef } from 'react';
import { Page, Box, Text, useSnackbar, Icon, Button, Select, Spinner } from "zmp-ui";
import { useParams } from 'react-router-dom';
import { CSSTransition, TransitionGroup } from 'react-transition-group';
import api from '@/lib/api';
import Header from '@/components/Header';
import { ViolationBatchManager } from '@/components/ViolationBatchManager';
import CustomImageViewer from '@/components/CustomImageViewer';

const ViolationInputPage: React.FC = () => {
  const { id: tuanId } = useParams();
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();

  const [loading, setLoading] = useState(true);
  const [violations, setViolations] = useState<any[]>([]);
  const [errors, setErrors] = useState<any[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Form states
  const [hoTen, setHoTen] = useState('');
  const [tenLop, setTenLop] = useState('');
  const [ngayViPham, setNgayViPham] = useState<string>(
    new Date().toLocaleDateString('en-GB')
  );
  const [viPhamId, setViPhamId] = useState<string>('');
  const [ghiChu, setGhiChu] = useState('');
  const [selectResetKey, setSelectResetKey] = useState(0);
  
  // Pending proofs state
  const [pendingProofs, setPendingProofs] = useState<any[]>([]);
  const [isUploading, setIsUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const [viewerImages, setViewerImages] = useState<string[]>([]);
  const [viewerIndex, setViewerIndex] = useState(0);
  const [isViewerOpen, setIsViewerOpen] = useState(false);
  
  // Format date to YYYY-MM-DD
  const getTodayFormatted = () => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  };

  const [studentSearchStatus, setStudentSearchStatus] = useState<'idle' | 'searching' | 'success' | 'error'>('idle');
  const [studentSearchMessage, setStudentSearchMessage] = useState('');
  const [selectedStudent, setSelectedStudent] = useState<any>(null);

  // Timeout ref for debouncing
  const searchTimeoutRef = useRef<NodeJS.Timeout | null>(null);

  useEffect(() => {
    fetchData();
  }, [tuanId]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [vpRes, errRes, proofRes] = await Promise.all([
        api.get(`/api/zalo/pending-violations?tuan_id=${tuanId}`),
        api.get('/api/zalo/violation-errors'),
        api.get(`/api/zalo/violation-get-proofs?tuan_id=${tuanId}&batch_id=null`)
      ]);

      if (vpRes.data.success) {
        setViolations(vpRes.data.data);
      }
      if (errRes.data.success) {
        setErrors(errRes.data.data);
      }
      if (proofRes.data.success) {
        setPendingProofs(proofRes.data.data);
      }
    } catch (err: any) {
      console.error(err);
      openSnackbar({ text: err.message || 'Lỗi tải dữ liệu', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const lookupStudent = async (name: string, cls: string) => {
    if (!name || !cls) {
      setStudentSearchStatus('idle');
      setStudentSearchMessage('');
      setSelectedStudent(null);
      return;
    }
    
    setStudentSearchStatus('searching');
    try {
      const res = await api.get(`/api/zalo/lookup-student?ho_ten=${encodeURIComponent(name)}&ten_lop=${encodeURIComponent(cls)}`);
      if (res.data.success) {
        setStudentSearchStatus('success');
        setStudentSearchMessage(`Tìm thấy: ${res.data.student.ho_ten} (${res.data.student.ma_hoc_sinh})`);
        setSelectedStudent(res.data.student);
        // Tự động nhập thông tin chuẩn xác
        setHoTen(res.data.student.ho_ten);
        setTenLop(res.data.student.ten_lop);
      } else {
        setStudentSearchStatus('error');
        setStudentSearchMessage(res.data.message || 'Không tìm thấy');
        setSelectedStudent(null);
      }
    } catch (err: any) {
      setStudentSearchStatus('error');
      setStudentSearchMessage(err.message || 'Lỗi tìm kiếm');
      setSelectedStudent(null);
    }
  };

  // Debounced search when typing Name or Class
  useEffect(() => {
    if (searchTimeoutRef.current) {
      clearTimeout(searchTimeoutRef.current);
    }
    searchTimeoutRef.current = setTimeout(() => {
      lookupStudent(hoTen, tenLop);
    }, 800);
    return () => {
      if (searchTimeoutRef.current) clearTimeout(searchTimeoutRef.current);
    };
  }, [hoTen, tenLop]);

  const handleAdd = async () => {
    if (!selectedStudent) {
      openSnackbar({ text: 'Vui lòng xác nhận học sinh hợp lệ trước khi thêm.', type: 'warning' });
      return;
    }
    if (!viPhamId) {
      openSnackbar({ text: 'Vui lòng chọn lỗi vi phạm.', type: 'warning' });
      return;
    }
    if (!ghiChu.trim()) {
      openSnackbar({ text: 'Vui lòng nhập mô tả chi tiết lỗi.', type: 'warning' });
      return;
    }

    try {
      const payload = {
        hoc_sinh_id: selectedStudent.id,
        ho_ten: hoTen,
        ten_lop: tenLop,
        vi_pham_id: viPhamId,
        ngay_vi_pham: ngayViPham,
        ghi_chu: ghiChu,
        tuan_hoc_id: tuanId
      };
      
      const res = await api.post('/api/zalo/add-violation', payload);
      
      if (res.data.success) {
        openSnackbar({ text: 'Đã thêm vào bản nháp', type: 'success' });
        // Add to top of list
        setViolations([res.data.data, ...violations]);
        // Reset các trường để bắt buộc nhập mới
        setHoTen('');
        setTenLop('');
        setViPhamId('');
        setGhiChu('');
        setSelectResetKey(k => k + 1);
        setStudentSearchStatus('idle');
        setSelectedStudent(null);
        setStudentSearchMessage('');
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi thêm vi phạm', type: 'error' });
      }
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message || 'Lỗi hệ thống';
      openSnackbar({ text: msg, type: 'error' });
    }
  };

  const handleProofUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) return;
    const file = e.target.files[0];
    if (file.size > 10 * 1024 * 1024) {
      openSnackbar({ text: 'Dung lượng file vượt quá 10MB', type: 'error' });
      return;
    }
    
    setIsUploading(true);
    const formData = new FormData();
    formData.append('file', file);
    formData.append('tuan_hoc_id', tuanId || '');

    try {
      const res = await api.post('/api/zalo/violation-upload-proof', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      if (res.data.success) {
        openSnackbar({ text: 'Đã tải minh chứng lên', type: 'success' });
        setPendingProofs([res.data.data, ...pendingProofs]);
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi upload', type: 'error' });
      }
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message || 'Lỗi hệ thống';
      openSnackbar({ text: msg, type: 'error' });
    } finally {
      setIsUploading(false);
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  };

  const handleProofDelete = async (id: number) => {
    try {
      const res = await api.post('/api/zalo/violation-delete-proof', { id });
      if (res.data.success) {
        setPendingProofs(pendingProofs.filter(p => p.id !== id));
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi xóa', type: 'error' });
      }
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message || 'Lỗi hệ thống';
      openSnackbar({ text: msg, type: 'error' });
    }
  };

  const handleDelete = async (id: string) => {
    try {
      const res = await api.post('/api/zalo/delete-violation', { id });
      if (res.data.success) {
        openSnackbar({ text: 'Đã xóa vi phạm', type: 'success' });
        setViolations(violations.filter(v => v.id !== id));
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi khi xóa', type: 'error' });
      }
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message || 'Lỗi hệ thống';
      openSnackbar({ text: msg, type: 'error' });
    }
  };

  const handleSubmit = async () => {
    if (violations.length === 0) return;
    if (isUploading) {
      openSnackbar({ text: 'Ảnh minh chứng đang được tải lên, vui lòng chờ trong giây lát...', type: 'warning' });
      return;
    }
    if (pendingProofs.length === 0) {
      openSnackbar({ text: 'Vui lòng tải lên ít nhất 1 ảnh minh chứng trước khi gửi.', type: 'warning' });
      return;
    }
    setIsSubmitting(true);
    try {
      const res = await api.post('/api/zalo/submit-violations', { tuan_hoc_id: tuanId });
      if (res.data.success) {
        openSnackbar({ text: res.data.message, type: 'success' });
        setViolations([]);
        setPendingProofs([]);
        window.dispatchEvent(new Event('refresh_violation_batches'));
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi khi gửi', type: 'error' });
      }
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message || 'Lỗi hệ thống';
      openSnackbar({ text: msg, type: 'error' });
    } finally {
      setIsSubmitting(false);
    }
  };

  const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
  };

  if (loading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page className="bg-transparent pb-24">
      <Header variant="back" title="Nhập vi phạm" />

      <div className="p-4 space-y-4">
        {/* Form nhập tay vi phạm (Hiển thị trực tiếp trên màn hình) */}
        <div className="bg-white rounded-xl shadow-sm border border-slate-100 p-4 space-y-4">
          <div className="grid grid-cols-3 gap-3">
            <div className="col-span-2">
              <label className="block text-[13px] font-semibold text-slate-700 mb-1">Họ và tên</label>
              <input 
                type="text" 
                value={hoTen}
                onChange={(e) => setHoTen(e.target.value)}
                placeholder="VD: Nguyễn Văn A"
                className="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[14px] focus:outline-none focus:border-[#224397]"
              />
            </div>
            <div className="col-span-1">
              <label className="block text-[13px] font-semibold text-slate-700 mb-1">Lớp</label>
              <input 
                type="text" 
                value={tenLop}
                onChange={(e) => setTenLop(e.target.value)}
                placeholder="VD: 10A1"
                className="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[14px] focus:outline-none focus:border-[#224397]"
              />
            </div>
          </div>

          {/* Status of student search */}
          {studentSearchStatus !== 'idle' && (
            <div className={`px-3 py-2 rounded-lg text-[13px] font-medium flex items-center gap-2 ${
              studentSearchStatus === 'searching' ? 'bg-slate-100 text-slate-500' :
              studentSearchStatus === 'success' ? 'bg-green-50 text-green-600 border border-green-100' :
              'bg-red-50 text-red-600 border border-red-100'
            }`}>
              {studentSearchStatus === 'searching' ? (
                <span>Đang tìm...</span>
              ) : studentSearchStatus === 'success' ? (
                <><Icon icon="zi-check-circle" size={16} /> <span>{studentSearchMessage}</span></>
              ) : (
                <><Icon icon="zi-warning" size={16} /> <span>{studentSearchMessage}</span></>
              )}
            </div>
          )}

          <div className="w-full min-w-0">
            <label className="block text-[13px] font-semibold text-slate-700 mb-1">Ngày vi phạm</label>
            <input 
              type="date" 
              value={ngayViPham}
              onChange={(e) => setNgayViPham(e.target.value)}
              className="w-full max-w-full box-border block bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[14px] focus:outline-none focus:border-[#224397] appearance-none min-w-0"
            />
          </div>

          <div>
            <label className="block text-[13px] font-semibold text-slate-700 mb-1">Lỗi vi phạm</label>
            <Select 
              key={selectResetKey}
              value={viPhamId}
              onChange={(val) => setViPhamId(val as string)}
              placeholder="-- Chọn lỗi vi phạm --"
              closeOnSelect
              className="w-full bg-slate-50 border border-slate-200 rounded-lg text-[13px] focus:outline-none focus:border-[#224397] custom-select-text"
            >
              {errors.map(err => {
                let name = err.ten_vi_pham;
                if (name.length > 60) {
                  name = name.substring(0, 60) + '...';
                }
                return (
                  <Select.Option key={err.id} value={err.id.toString()} title={name} />
                );
              })}
            </Select>
          </div>

          <div>
            <label className="block text-[13px] font-semibold text-slate-700 mb-1">Mô tả chi tiết lỗi <span className="text-red-500">*</span></label>
            <input 
              type="text" 
              value={ghiChu}
              onChange={(e) => setGhiChu(e.target.value)}
              placeholder="Nhập mô tả chi tiết lỗi..."
              className="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[14px] focus:outline-none focus:border-[#224397]"
            />
          </div>

          <div className="pt-2">
            <Button
              fullWidth
              onClick={handleAdd}
              disabled={!selectedStudent || !viPhamId || !ghiChu.trim()}
              className={`font-bold h-11 rounded-xl text-[14px] active:scale-[0.98] transition-transform ${
                (!selectedStudent || !viPhamId || !ghiChu.trim()) ? 'bg-slate-200 text-slate-400' : 'bg-[#224397] text-white shadow-md'
              }`}
            >
              THÊM VÀO BẢNG
            </Button>
          </div>
        </div>

        {/* Danh sách vi phạm đã thêm */}
        <div className="pt-2">
          <h3 className="font-bold text-slate-700 text-[14px] mb-3 uppercase">Danh sách chờ gửi</h3>
          {loading ? (
            <div className="text-center py-10 text-slate-400 bg-white rounded-xl shadow-sm border border-slate-100">Đang tải...</div>
          ) : violations.length === 0 ? (
            <div className="text-center py-10 text-slate-400 bg-white rounded-xl shadow-sm border border-slate-100">
              Chưa có vi phạm nào được nhập.
            </div>
          ) : (
            <TransitionGroup className="space-y-3 pb-6">
              {violations.map(vp => (
                <CSSTransition key={vp.id} timeout={400} classNames="violation-item">
                  <div className="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex gap-3 relative">
                    <div className="w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center font-bold flex-shrink-0">
                      {vp.diem_tru}
                    </div>
                    <div className="flex-1 overflow-hidden">
                      <div className="font-bold text-[#224397] text-[14px] truncate">{vp.ho_ten_day_du}</div>
                      <div className="text-[12px] text-slate-500 flex gap-2 mb-1">
                        <span>Lớp: {vp.ten_lop}</span>
                        <span>|</span>
                        <span>Ngày: {formatDate(vp.ngay_vi_pham)}</span>
                      </div>
                      <div className="text-[13px] font-medium text-slate-700 leading-tight">
                        {vp.ten_vi_pham}
                      </div>
                      {vp.ghi_chu && (
                        <div className="text-[12px] text-slate-400 mt-1 italic">
                          Ghi chú: {vp.ghi_chu}
                        </div>
                      )}
                    </div>
                    <div 
                      onClick={() => handleDelete(vp.id)}
                      className="w-8 h-8 bg-red-50 text-red-500 rounded-full flex items-center justify-center active:bg-red-100 absolute top-3 right-3"
                    >
                      <Icon icon="zi-delete" size={18} />
                    </div>
                  </div>
                </CSSTransition>
              ))}
              
              {violations.length > 0 && (
                <CSSTransition key="proof-manager" timeout={0}>
                  <div className="bg-white p-3 rounded-xl shadow-sm border border-slate-100 mt-2">
                    <h4 className="text-[13px] font-bold text-slate-700 mb-2">Minh chứng cho đợt này</h4>
                    
                    <div className="flex flex-wrap gap-2 mb-2">
                      {pendingProofs.map((proof, idx) => (
                        <div key={proof.id} className="relative w-16 h-16 rounded-lg overflow-hidden border border-slate-200">
                          <img 
                            src={proof.url} 
                            className="w-full h-full object-cover" 
                            alt="Proof" 
                            onClick={() => {
                              setViewerImages(pendingProofs.map(p => p.url));
                              setViewerIndex(idx);
                              setIsViewerOpen(true);
                            }}
                          />
                          <div 
                            className="absolute top-1 right-1 w-5 h-5 bg-black/50 rounded-full flex items-center justify-center text-white"
                            onClick={(e) => { e.stopPropagation(); handleProofDelete(proof.id); }}
                          >
                            <Icon icon="zi-close" size={14} />
                          </div>
                        </div>
                      ))}
                    </div>

                    <Button 
                      size="small" 
                      variant="secondary"
                      loading={isUploading}
                      onClick={() => fileInputRef.current?.click()}
                      className="text-[13px] bg-slate-100 text-slate-700 border-none w-full"
                    >
                      <Icon icon="zi-upload" className="mr-1" size={16} /> Thêm ảnh minh chứng
                    </Button>
                    <input 
                      type="file" 
                      accept="image/*" 
                      className="hidden" 
                      ref={fileInputRef}
                      onChange={handleProofUpload}
                    />
                  </div>
                </CSSTransition>
              )}
              
              {violations.length > 0 && (
                <CSSTransition key="submit-btn" timeout={0}>
                  <div className="pt-2">
                    <Button
                      fullWidth
                      loading={isSubmitting}
                      disabled={isUploading}
                      onClick={handleSubmit}
                      className={`font-bold h-12 rounded-xl text-[15px] shadow-lg transition-all ${
                        pendingProofs.length === 0 || isUploading
                          ? 'bg-slate-400 text-white cursor-not-allowed opacity-80 shadow-none'
                          : 'bg-[#224397] text-white shadow-blue-900/20 active:scale-[0.98]'
                      }`}
                    >
                      {isUploading 
                        ? 'ĐANG TẢI ẢNH MINH CHỨNG...' 
                        : pendingProofs.length === 0 
                        ? `CẦN MINH CHỨNG (${violations.length} VI PHẠM)` 
                        : `GỬI (${violations.length} VI PHẠM)`}
                    </Button>
                    {pendingProofs.length === 0 && (
                      <p className="text-[11.5px] text-amber-600 font-medium text-center mt-2 flex items-center justify-center gap-1">
                        <Icon icon="zi-warning-solid" size={14} /> Vui lòng thêm ít nhất 1 ảnh minh chứng trước khi gửi
                      </p>
                    )}
                  </div>
                </CSSTransition>
              )}
            </TransitionGroup>
          )}
        </div>

        {/* Lịch sử nộp trong tuần */}
        <ViolationBatchManager tuanId={tuanId || ''} />

        <CustomImageViewer
          images={viewerImages}
          visible={isViewerOpen}
          activeIndex={viewerIndex}
          onClose={() => setIsViewerOpen(false)}
        />
      </div>
    </Page>
  );
};

export default ViolationInputPage;
