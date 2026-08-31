import React, { useState } from 'react';
import { Page, Box, Text, Icon, Spinner, Input, Select } from 'zmp-ui';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import Header from '@/components/Header';

interface Violation {
  ngay_vi_pham: string;
  ten_vi_pham: string;
  diem_tru: number;
  ghi_chu: string;
  ten_tuan: string;
}

const ViolationsPage: React.FC = () => {
  const [weekFilter, setWeekFilter] = useState<string>('all');
  const [pointsFilter, setPointsFilter] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState<string>('');

  const { data, isLoading, error } = useQuery({
    queryKey: ['violations'],
    queryFn: async () => {
      const res = await api.get('/api/zalo/violations');
      return res.data;
    },
  });

  const violations: Violation[] = data?.data || [];

  const uniqueWeeks = Array.from(new Set(violations.map(v => v.ten_tuan))).sort();
  const uniquePoints = Array.from(new Set(violations.map(v => v.diem_tru))).sort((a,b) => a - b);

  const filteredViolations = violations.filter(v => {
    if (weekFilter !== 'all' && v.ten_tuan !== weekFilter) return false;
    if (pointsFilter !== 'all' && v.diem_tru.toString() !== pointsFilter) return false;
    if (searchQuery && !v.ten_vi_pham.toLowerCase().includes(searchQuery.toLowerCase())) return false;
    return true;
  });

  const formatDate = (dateString: string) => {
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
  };

  return (
    <Page className="bg-slate-50 relative pb-10 flex flex-col h-screen">
      <Header variant="back" title="Lịch sử Vi phạm" />

      <div className="bg-white px-4 py-2.5 shadow-[0_2px_10px_rgba(0,0,0,0.03)] border-b border-slate-100 flex flex-col gap-2 z-10 relative">
        <div>
          <Input.Search 
            placeholder="Tìm tên vi phạm..." 
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            clearable
            className="!m-0 text-[13px]"
          />
        </div>
        <div className="flex gap-2">
          <div className="flex-1 h-9">
            <Select 
              value={weekFilter} 
              onChange={(val) => setWeekFilter(val as string)}
              closeOnSelect
              className="!h-9 !m-0 !text-[13px] !min-h-[36px]"
            >
              <Select.Option value="all" title="Tất cả các tuần" />
              {uniqueWeeks.map(w => (
                <Select.Option key={w} value={w} title={w} />
              ))}
            </Select>
          </div>
          <div className="flex-1 h-9">
            <Select 
              value={pointsFilter} 
              onChange={(val) => setPointsFilter(val as string)}
              closeOnSelect
              className="!h-9 !m-0 !text-[13px] !min-h-[36px]"
            >
              <Select.Option value="all" title="Mọi mức điểm" />
              {uniquePoints.map(p => (
                <Select.Option key={p} value={p.toString()} title={`-${p} điểm`} />
              ))}
            </Select>
          </div>
        </div>
      </div>

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

        {!isLoading && !error && filteredViolations.length === 0 && (
          <div className="text-center text-slate-500 py-10 bg-white rounded-xl shadow-sm border border-slate-100 flex flex-col items-center">
            <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
              <Icon icon="zi-check-circle-solid" className="text-emerald-500" size={36} />
            </div>
            <p className="font-medium text-slate-700">Tuyệt vời!</p>
            <p className="text-sm mt-1">{violations.length === 0 ? "Không có vi phạm nào trong năm học này." : "Không có vi phạm nào phù hợp với bộ lọc."}</p>
          </div>
        )}

        {!isLoading && !error && filteredViolations.length > 0 && (
          <div className="space-y-3">
            {filteredViolations.map((v, idx) => (
              <div key={idx} className="bg-white p-4 rounded-xl shadow-sm border border-red-100 relative overflow-hidden">
                <div className="absolute left-0 top-0 bottom-0 w-1.5 bg-red-500"></div>
                <div className="flex justify-between items-start pl-2">
                  <div className="flex-1 pr-3">
                    <h3 className="font-bold text-slate-800 text-[15px] leading-snug mb-1.5">{v.ten_vi_pham}</h3>
                    <div className="flex items-center text-xs text-slate-500 mb-1">
                      <Icon icon="zi-calendar" size={12} className="mr-1" />
                      {formatDate(v.ngay_vi_pham)} &bull; {v.ten_tuan}
                    </div>
                    {v.ghi_chu && (
                      <p className="text-[13px] text-slate-600 mt-2 bg-slate-50 p-2 rounded-lg border border-slate-100">
                        {v.ghi_chu}
                      </p>
                    )}
                  </div>
                  <div className="flex flex-col items-center justify-center bg-red-50 rounded-lg px-2.5 py-1.5 border border-red-100 min-w-[50px]">
                    <span className="text-red-600 font-bold text-[15px]">-{v.diem_tru}</span>
                    <span className="text-red-500 text-[10px] font-medium">điểm</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </Page>
  );
};

export default ViolationsPage;
