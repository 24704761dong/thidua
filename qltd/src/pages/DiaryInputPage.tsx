import logoImg from '@/assets/logo.png';
import React, { useEffect, useState, useRef } from 'react';
import { Page, Box, Text, Button, useSnackbar, Input, Icon, Modal, Spinner } from 'zmp-ui';
import { useParams, useNavigate } from 'react-router-dom';
import api from '@/lib/api';
import CustomImageViewer from '@/components/CustomImageViewer';
import Header from '@/components/Header';

const SECTION_MAP: Record<string, string> = {
  sdb_ck: 'Sổ đầu bài - Chính khóa',
  sdb_nk: 'Sổ đầu bài - Ngoại khóa',
  sdb_tt: 'Sổ Nhật kỳ'
};

const DiaryInputPage: React.FC = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [uploading, setUploading] = useState(false);

  const [nhatKy, setNhatKy] = useState<any>(null);
  const [details, setDetails] = useState<Record<string, any>>({});
  const [proofs, setProofs] = useState<Record<string, any[]>>({});

  const [uploadingSection, setUploadingSection] = useState('');
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [deletingId, setDeletingId] = useState<string | null>(null);
  const [confirmDelete, setConfirmDelete] = useState<{ id: string; section: string } | null>(null);

  const fileInputRef = useRef<HTMLInputElement>(null);

  const fetchData = async () => {
    try {
      setLoading(true);
      const res = await api.get(`/api/zalo/so-nhat-ky-chi-tiet?tuan_id=${id}`);
      if (res.data.success) {
        setNhatKy(res.data.data.nhat_ky);
        setDetails(res.data.data.details);
        setProofs(res.data.data.proofs);
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi tải dữ liệu', type: 'error' });
      }
    } catch (e) {
      console.error(e);
      openSnackbar({ text: 'Lỗi kết nối', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (id) {
      fetchData();
    }
  }, [id]);

  const saveSection = async (sectionToSave: string) => {
    if (!nhatKy) return;
    try {
      setSaving(true);
      const dataToSave = {
        nhat_ky_id: nhatKy.id,
        loai_so: sectionToSave,
        so_tiet_tot: details[sectionToSave]?.so_tiet_tot || 0,
        so_tiet_kha: details[sectionToSave]?.so_tiet_kha || 0,
        so_tiet_tb: details[sectionToSave]?.so_tiet_tb || 0,
        so_tiet_yeu: details[sectionToSave]?.so_tiet_yeu || 0,
      };

      const res = await api.post('/api/zalo/so-nhat-ky-luu', dataToSave);
      if (!res.data.success) {
        console.error('Lỗi tự động lưu:', res.data.message);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setSaving(false);
    }
  };

  const submitDiary = async () => {
    if (!nhatKy) return;
    try {
      setSubmitting(true);
      for (const loaiSo of ['sdb_tt', 'sdb_ck', 'sdb_nk']) {
        await api.post('/api/zalo/so-nhat-ky-luu', {
          nhat_ky_id: nhatKy.id,
          loai_so: loaiSo,
          so_tiet_tot: details[loaiSo]?.so_tiet_tot || 0,
          so_tiet_kha: details[loaiSo]?.so_tiet_kha || 0,
          so_tiet_tb: details[loaiSo]?.so_tiet_tb || 0,
          so_tiet_yeu: details[loaiSo]?.so_tiet_yeu || 0,
        });
      }

      const res = await api.post('/api/zalo/so-nhat-ky-gui', { nhat_ky_id: nhatKy.id });
      if (res.data.success) {
        openSnackbar({ text: 'Nộp sổ thành công!', type: 'success' });
        navigate(-1);
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi nộp sổ', type: 'error' });
      }
    } catch (e) {
      console.error(e);
      openSnackbar({ text: 'Lỗi kết nối', type: 'error' });
    } finally {
      setSubmitting(false);
    }
  };

  const handleUploadClick = (section: string) => {
    setUploadingSection(section);
    fileInputRef.current?.click();
  };

  const handleFileUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    if (!nhatKy || !uploadingSection) return;
    const files = event.target.files;
    if (!files || files.length === 0) return;
    const file = files[0];

    if (file.size > 10 * 1024 * 1024) {
      openSnackbar({ text: 'File tối đa 10MB', type: 'warning' });
      return;
    }

    const formData = new FormData();
    formData.append('nhat_ky_id', nhatKy.id);
    formData.append('loai_minh_chung', uploadingSection);
    formData.append('file', file);

    try {
      setUploading(true);
      const res = await api.post('/api/zalo/so-nhat-ky-upload-minh-chung', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        timeout: 120000 // 2 minutes for large files
      });
      if (res.data.success) {
        openSnackbar({ text: 'Tải minh chứng thành công', type: 'success' });
        setProofs(prev => ({
          ...prev,
          [uploadingSection]: [...(prev[uploadingSection] || []), res.data.data]
        }));
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi upload', type: 'error' });
      }
    } catch (e) {
      console.error(e);
      openSnackbar({ text: 'Lỗi upload', type: 'error' });
    } finally {
      setUploading(false);
      setUploadingSection('');
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  };

  const isLocked = nhatKy?.trang_thai === 'da_duyet' || nhatKy?.trang_thai === 'da_gui';

  const handleInputChange = (section: string, field: string, value: string) => {
    if (isLocked) return;
    setDetails(prev => ({
      ...prev,
      [section]: {
        ...prev[section],
        [field]: value === '' ? 0 : parseInt(value, 10)
      }
    }));
  };

  const handleDeleteProof = async (proofId: string, section: string) => {
    try {
      setDeletingId(proofId);
      const res = await api.post('/api/zalo/so-nhat-ky-xoa-minh-chung', { proof_id: proofId });
      if (res.data.success) {
        openSnackbar({ text: 'Đã xóa minh chứng', type: 'success' });
        setProofs(prev => ({
          ...prev,
          [section]: prev[section].filter((p: any) => p.id !== proofId)
        }));
      } else {
        openSnackbar({ text: res.data.message || 'Lỗi khi xóa', type: 'error' });
      }
    } catch (err) {
      openSnackbar({ text: 'Lỗi hệ thống', type: 'error' });
    } finally {
      setDeletingId(null);
    }
  };

  const getImageUrl = (p: any) => {
    if (!p) return '';
    // Ưu tiên link Cloud trực tiếp từ R2
    if (p.url) return p.url;
    if (p.cloud_url) return p.cloud_url;

    const path = p.file_path || '';
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }

    if (path) {
      const baseUrl = api.defaults.baseURL || import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua';
      
      if (path.startsWith('/thidua/')) {
        let origin = 'https://c3binhson.edu.vn';
        try { origin = new URL(baseUrl).origin; } catch (e) { }
        return `${origin}${path}`;
      }

      return `${baseUrl}/${path.replace(/^\//, '')}`;
    }
    return '';
  };

  const getLatestReason = (ghiChuAdmin: string | null) => {
    if (!ghiChuAdmin) return null;
    const blocks = ghiChuAdmin.split('[Lần');
    if (blocks.length > 1) {
      return ('[Lần' + blocks[blocks.length - 1]).trim();
    }
    return ghiChuAdmin.trim();
  };
  const latestReason = getLatestReason(nhatKy?.ghi_chu_admin);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'chua_nhap':
        return <span className="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-500 uppercase">Chưa nhập</span>;
      case 'nhap':
        return <span className="px-2 py-0.5 text-[10px] font-bold rounded-md bg-orange-100 text-orange-600 uppercase">Đang nháp</span>;
      case 'da_gui':
        return <span className="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-600 uppercase">Chờ duyệt</span>;
      case 'da_duyet':
        return <span className="px-2 py-0.5 text-[10px] font-bold rounded-md bg-green-100 text-green-600 uppercase">Đã duyệt</span>;
      case 'tu_choi':
        return <span className="px-2 py-0.5 text-[10px] font-bold rounded-md bg-red-100 text-red-600 uppercase">Bị từ chối</span>;
      default:
        return null;
    }
  };

  // Debounced auto-save function
  useEffect(() => {
    if (isLocked || loading) return;
    const timer = setTimeout(() => {
      saveSection('sdb_ck');
      saveSection('sdb_nk');
      saveSection('sdb_tt');
    }, 1500);
    return () => clearTimeout(timer);
  }, [details, isLocked, loading]);

  if (loading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  const getTotal = (field: string) => {
    return ['sdb_tt', 'sdb_ck', 'sdb_nk'].reduce((sum, key) => sum + (details[key]?.[field] || 0), 0);
  };

  const renderProofs = (section: string) => {
    const sectionProofs = proofs[section] || [];
    return (
      <div className="mt-4">
        <div className="flex items-center justify-between mb-2">
          <Text className="font-bold text-sm text-slate-700">Minh chứng đính kèm:</Text>
          {!isLocked && (
            <div
              className="px-2 py-1 bg-blue-50 text-blue-600 rounded-md text-[11px] font-bold active:bg-blue-100"
              onClick={(e) => { e.stopPropagation(); handleUploadClick(section); }}
            >
              + THÊM ẢNH
            </div>
          )}
        </div>
        {sectionProofs.length > 0 ? (
          <div className="flex gap-3 overflow-x-auto pb-2 snap-x">
            {sectionProofs.map((p: any) => {
              const isDeleting = deletingId === p.id;
              return (
                <div
                  key={p.id}
                  className="relative w-24 h-24 flex-shrink-0 snap-start bg-slate-100 rounded-lg overflow-hidden border border-slate-200 shadow-sm cursor-pointer active:opacity-80 transition-opacity"
                  onClick={() => setSelectedImage(getImageUrl(p))}
                >
                  {isDeleting && (
                    <div className="absolute inset-0 bg-white/50 flex items-center justify-center z-10 backdrop-blur-[2px]">
                      <div className="w-5 h-5 border-2 border-[#224397] border-t-transparent rounded-full animate-spin"></div>
                    </div>
                  )}
                  <img
                    src={getImageUrl(p)}
                    className="w-full h-full object-cover"
                    alt="Minh chứng"
                    onError={(e) => { (e.target as HTMLImageElement).src = 'https://placehold.co/100x100?text=Loi+Anh' }}
                  />
                  {!isLocked && !isDeleting && (
                    <div
                      className="absolute top-1 right-1 bg-red-500/90 rounded-full p-1 text-white flex items-center justify-center cursor-pointer active:scale-95 shadow-sm backdrop-blur-sm z-20"
                      onClick={(e) => { e.stopPropagation(); setConfirmDelete({ id: p.id, section }); }}
                    >
                      <Icon icon="zi-close" size={14} className="!w-3 !h-3" />
                    </div>
                  )}
                </div>
              )
            })}
          </div>
        ) : (
          <div className="text-center py-5 text-slate-400 text-xs border border-dashed border-slate-300 rounded-lg bg-slate-50">
            Không có minh chứng cho mục này.
          </div>
        )}
      </div>
    );
  };

  const renderInputsOrBadges = (section: string) => {
    if (isLocked) {
      const tot = details[section]?.so_tiet_tot || 0;
      const kha = details[section]?.so_tiet_kha || 0;
      const tb = details[section]?.so_tiet_tb || 0;
      const yeu = details[section]?.so_tiet_yeu || 0;
      return (
        <div className="flex items-center gap-2 mb-3 flex-wrap">
          <div className="px-3 py-1.5 bg-[#f0fdf4] border border-[#bbf7d0] text-[#15803d] rounded-lg text-xs font-bold">
            Tốt: {tot}
          </div>
          <div className="px-3 py-1.5 bg-[#eff6ff] border border-[#bfdbfe] text-[#1d4ed8] rounded-lg text-xs font-bold">
            Khá: {kha}
          </div>
          <div className="px-3 py-1.5 bg-[#fefce8] border border-[#fef08a] text-[#a16207] rounded-lg text-xs font-bold">
            TB: {tb}
          </div>
          <div className="px-3 py-1.5 bg-[#fef2f2] border border-[#fecaca] text-[#b91c1c] rounded-lg text-xs font-bold">
            Yếu: {yeu}
          </div>
        </div>
      );
    }

    return (
      <div className="grid grid-cols-4 gap-2 mb-3">
        <div>
          <Text className="text-[11px] font-semibold text-slate-500 mb-1 text-center">Tốt</Text>
          <input
            type="number"
            value={details[section]?.so_tiet_tot?.toString() || ''}
            onChange={(e) => handleInputChange(section, 'so_tiet_tot', e.target.value)}
            className="w-full h-10 bg-slate-50 border border-slate-200 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] rounded-lg text-center font-bold text-[#15803d] outline-none transition-all block"
          />
        </div>
        <div>
          <Text className="text-[11px] font-semibold text-slate-500 mb-1 text-center">Khá</Text>
          <input
            type="number"
            value={details[section]?.so_tiet_kha?.toString() || ''}
            onChange={(e) => handleInputChange(section, 'so_tiet_kha', e.target.value)}
            className="w-full h-10 bg-slate-50 border border-slate-200 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] rounded-lg text-center font-bold text-[#1d4ed8] outline-none transition-all block"
          />
        </div>
        <div>
          <Text className="text-[11px] font-semibold text-slate-500 mb-1 text-center">TB</Text>
          <input
            type="number"
            value={details[section]?.so_tiet_tb?.toString() || ''}
            onChange={(e) => handleInputChange(section, 'so_tiet_tb', e.target.value)}
            className="w-full h-10 bg-slate-50 border border-slate-200 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] rounded-lg text-center font-bold text-[#a16207] outline-none transition-all block"
          />
        </div>
        <div>
          <Text className="text-[11px] font-semibold text-slate-500 mb-1 text-center">Yếu</Text>
          <input
            type="number"
            value={details[section]?.so_tiet_yeu?.toString() || ''}
            onChange={(e) => handleInputChange(section, 'so_tiet_yeu', e.target.value)}
            className="w-full h-10 bg-slate-50 border border-slate-200 focus:border-[#224397] focus:ring-1 focus:ring-[#224397] rounded-lg text-center font-bold text-[#b91c1c] outline-none transition-all block"
          />
        </div>
      </div>
    );
  };

  return (
    <Page className="bg-transparent flex flex-col h-screen pt-0">
      <Header variant="back" title="Nhập sổ nhật kỳ" />

      <input
        type="file"
        accept="image/png, image/jpeg, image/jpg"
        ref={fileInputRef}
        className="hidden"
        onChange={handleFileUpload}
      />

      <div className="px-4 py-4 flex-1 overflow-y-auto space-y-5">
        {latestReason && (
          <div className="bg-red-50 rounded-[16px] shadow-sm border border-red-200 overflow-hidden">
            <div className="bg-red-100/60 px-4 py-3 border-b border-red-200 flex items-center gap-2">
              <Icon icon="zi-warning-solid" className="text-red-500" />
              <h3 className="font-bold text-red-600 text-[13px] uppercase tracking-wide">Lý do từ chối</h3>
            </div>
            <div className="p-4 text-red-800 text-[13px] whitespace-pre-line font-medium leading-relaxed">
              {latestReason}
            </div>
          </div>
        )}


        {/* Overview Card */}
        <div className="bg-white rounded-[16px] shadow-sm border border-[#224397]/25 overflow-hidden">
          <div className="bg-[#E4F6FD]/30 px-4 py-3 border-b border-[#224397]/10 flex items-center justify-between">
            <h3 className="font-bold text-[#224397] text-[13px] uppercase tracking-wide">Tổng quan điểm</h3>
            {nhatKy?.trang_thai && getStatusBadge(nhatKy.trang_thai)}
          </div>
          <div className="p-4 grid grid-cols-2 gap-3">
            <div className="border border-[#224397]/20 rounded-xl p-3 text-center">
              <div className="text-slate-600 font-bold text-[13px] mb-1">Tốt</div>
              <div className="text-[#15803d] font-black text-2xl">{getTotal('so_tiet_tot')}</div>
            </div>
            <div className="border border-[#224397]/20 rounded-xl p-3 text-center">
              <div className="text-slate-600 font-bold text-[13px] mb-1">Khá</div>
              <div className="text-[#1d4ed8] font-black text-2xl">{getTotal('so_tiet_kha')}</div>
            </div>
            <div className="border border-[#224397]/20 rounded-xl p-3 text-center">
              <div className="text-slate-600 font-bold text-[13px] mb-1">TB</div>
              <div className="text-[#a16207] font-black text-2xl">{getTotal('so_tiet_tb')}</div>
            </div>
            <div className="border border-[#224397]/20 rounded-xl p-3 text-center">
              <div className="text-slate-600 font-bold text-[13px] mb-1">Yếu</div>
              <div className="text-[#b91c1c] font-black text-2xl">{getTotal('so_tiet_yeu')}</div>
            </div>
          </div>
        </div>

        {/* Section Cards */}
        {['sdb_ck', 'sdb_nk', 'sdb_tt'].map((section) => (
          <div
            key={section}
            className="bg-white rounded-[16px] shadow-sm border border-[#224397]/25 overflow-hidden"
          >
            <div className="bg-[#E4F6FD]/30 px-4 py-3 border-b border-[#224397]/10">
              <h3 className="font-bold text-[#224397] text-[13px]">{SECTION_MAP[section]}</h3>
            </div>
            <div className="p-4">
              {section !== 'sdb_tt' && renderInputsOrBadges(section)}
              {renderProofs(section)}
            </div>
          </div>
        ))}
        {!isLocked && (
          <div className="pt-2 pb-24 flex gap-3">
            <Button
              variant="secondary"
              onClick={() => navigate(-1)}
              className="!h-12 !rounded-xl font-bold flex-[1] !bg-slate-200 !text-slate-700 border-none active:!bg-slate-300"
            >
              Quay lại
            </Button>
            <Button
              loading={submitting}
              onClick={submitDiary}
              className="!bg-[#224397] !text-white !h-12 !rounded-xl font-bold flex-[1] shadow-md shadow-[#224397]/20 active:!bg-[#1a3478]"
            >
              Nộp sổ ngay
            </Button>
          </div>
        )}
        {isLocked && <div className="pb-24"></div>}
      </div>

      <CustomImageViewer
        visible={!!selectedImage}
        src={selectedImage}
        onClose={() => setSelectedImage(null)}
      />

      <Modal
        visible={!!confirmDelete}
        title="Xác nhận xóa"
        description="Bạn có chắc chắn muốn xóa minh chứng này?"
        actions={[
          {
            text: 'Hủy',
            onClick: () => setConfirmDelete(null),
          },
          {
            text: 'Xóa',
            danger: true,
            onClick: () => {
              if (confirmDelete) {
                handleDeleteProof(confirmDelete.id, confirmDelete.section);
                setConfirmDelete(null);
              }
            },
          },
        ]}
      />

      {uploading && (
        <div className="fixed inset-0 bg-white/80 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center">
          <div className="w-12 h-12 border-4 border-[#224397]/20 border-t-[#224397] rounded-full animate-spin mb-4"></div>
          <p className="text-[#224397] font-bold text-sm bg-white px-4 py-1.5 rounded-full shadow-sm animate-pulse">Đang đẩy ảnh lên Cloud...</p>
        </div>
      )}
    </Page>
  );
};

export default DiaryInputPage;
