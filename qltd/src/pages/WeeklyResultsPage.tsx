import React, { useState, useEffect } from 'react';
import { Page, Box, Text, Spinner, Select } from 'zmp-ui';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import Header from '@/components/Header';

interface Week {
  id: number;
  ten_tuan: string;
}

interface ResultData {
  lop_id: number;
  ten_lop: string;
  khoi: number;
  so_tiet_tot: number;
  so_tiet_tb: number;
  diem_sdb: number;
  diem_cong_tru: number;
  vang_kp: number;
  vang_p: number;
  diem_noi_quy: number;
  tong_diem: number;
  kxtd: boolean;
  xep_hang: number | string;
}

const WeeklyResultsPage: React.FC = () => {
  const [selectedWeek, setSelectedWeek] = useState<string>('');
  const [selectedKhoi, setSelectedKhoi] = useState<string>('all');
  const [selectedLop, setSelectedLop] = useState<string>('all');

  const { data: weeksData, isLoading: isLoadingWeeks } = useQuery({
    queryKey: ['publicWeeks'],
    queryFn: async () => {
      const res = await api.get('/api/zalo/public-weeks');
      return res.data;
    }
  });

  const weeks: Week[] = weeksData?.data || [];

  useEffect(() => {
    if (weeks.length > 0 && !selectedWeek) {
      setSelectedWeek(weeks[0].id.toString());
    }
  }, [weeks]);

  const { data: resultsData, isLoading: isLoadingResults } = useQuery({
    queryKey: ['emulationResults', selectedWeek],
    queryFn: async () => {
      if (!selectedWeek) return null;
      const res = await api.get(`/api/zalo/public-emulation-results?tuan_id=${selectedWeek}`);
      return res.data;
    },
    enabled: !!selectedWeek,
  });

  const results: ResultData[] = resultsData?.data || [];

  const khoiList = Array.from(new Set(results.map(r => r.khoi))).sort((a, b) => a - b);
  
  const lopList = results
    .filter(r => selectedKhoi === 'all' || r.khoi.toString() === selectedKhoi)
    .map(r => r.ten_lop)
    .sort();

  const filteredResults = results.filter(r => {
    if (selectedKhoi !== 'all' && r.khoi.toString() !== selectedKhoi) return false;
    if (selectedLop !== 'all' && r.ten_lop !== selectedLop) return false;
    return true;
  });

  return (
    <Page className="bg-slate-50 relative flex flex-col">
      <Header title="Kết quả tuần" showBack={true} />
      
      <Box className="flex-1 overflow-y-auto pb-6">
        <div className="bg-white p-4 shadow-sm mb-3">
          {isLoadingWeeks ? (
            <div className="flex justify-center p-4"><Spinner /></div>
          ) : weeks.length === 0 ? (
            <div className="text-center text-slate-500 py-4">Chưa có tuần nào được công khai.</div>
          ) : (
            <div className="space-y-4">
              <div>
                <label className="block text-xs font-medium text-slate-500 mb-1">Tuần thi đua</label>
                <Select 
                  value={selectedWeek} 
                  onChange={(val) => setSelectedWeek(val as string)}
                  closeOnSelect
                  className="!h-10 !m-0 !text-[14px]"
                >
                  {weeks.map(w => (
                    <Select.Option key={w.id} value={w.id.toString()} title={w.ten_tuan} />
                  ))}
                </Select>
              </div>
              
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-500 mb-1">Khối</label>
                  <Select 
                    value={selectedKhoi} 
                    onChange={(val) => {
                      setSelectedKhoi(val as string);
                      setSelectedLop('all');
                    }}
                    closeOnSelect
                    className="!h-10 !m-0 !text-[14px]"
                  >
                    <Select.Option value="all" title="Tất cả khối" />
                    {khoiList.map(k => (
                      <Select.Option key={k} value={k.toString()} title={`Khối ${k}`} />
                    ))}
                  </Select>
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-500 mb-1">Lớp</label>
                  <Select 
                    value={selectedLop} 
                    onChange={(val) => setSelectedLop(val as string)}
                    closeOnSelect
                    className="!h-10 !m-0 !text-[14px]"
                  >
                    <Select.Option value="all" title="Tất cả lớp" />
                    {lopList.map(l => (
                      <Select.Option key={l} value={l} title={l} />
                    ))}
                  </Select>
                </div>
              </div>
            </div>
          )}
        </div>

        {selectedWeek && (
          <div className="bg-white shadow-sm overflow-hidden min-h-[300px]">
            {isLoadingResults ? (
              <div className="flex justify-center items-center h-40">
                <Spinner />
              </div>
            ) : filteredResults.length === 0 ? (
              <div className="text-center text-slate-500 py-10">Không có dữ liệu cho bộ lọc này.</div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-[13px] text-center whitespace-nowrap border-collapse border border-slate-300">
                  <thead className="bg-slate-100 text-slate-700 font-semibold text-[11px] uppercase sticky top-0 z-10">
                    <tr>
                      <th className="px-3 py-3 w-12 border border-slate-300">STT</th>
                      <th className="px-3 py-3 w-16 border border-slate-300">Lớp</th>
                      <th className="px-2 py-3 border border-slate-300">Số Tiết</th>
                      <th className="px-2 py-3 border border-slate-300">Sổ<br/>ĐK-NK</th>
                      <th className="px-2 py-3 border border-slate-300">Điểm (+/-)<br/>khác</th>
                      <th className="px-2 py-3 border border-slate-300">Vắng</th>
                      <th className="px-2 py-3 border border-slate-300">Nội quy/<br/>Chuyên cần</th>
                      <th className="px-2 py-3 border border-slate-300 font-bold text-blue-600">Tổng điểm</th>
                      <th className="px-2 py-3 border border-slate-300">Xếp<br/>hạng</th>
                    </tr>
                  </thead>
                  <tbody>
                    {filteredResults.map((row, idx) => {
                      const isRank1 = row.xep_hang === 1 || row.xep_hang === '1';
                      const isRank2 = row.xep_hang === 2 || row.xep_hang === '2';
                      const isRank3 = row.xep_hang === 3 || row.xep_hang === '3';
                      const rankText = row.kxtd ? 'KXTĐ' : row.xep_hang;
                      
                      let rankStyle = "text-slate-600";
                      if (isRank1) rankStyle = "text-yellow-600 font-bold";
                      else if (isRank2) rankStyle = "text-slate-600 font-bold";
                      else if (isRank3) rankStyle = "text-orange-600 font-bold";

                      const totalTiet = (row.so_tiet_tot || 0) + (row.so_tiet_tb || 0);
                      const totalVang = (row.vang_kp || 0) + (row.vang_p || 0);

                      return (
                        <tr key={row.lop_id} className={isRank1 ? 'bg-yellow-50/40 hover:bg-yellow-100/50' : isRank2 ? 'bg-slate-50/50 hover:bg-slate-100' : isRank3 ? 'bg-orange-50/30 hover:bg-orange-100/50' : 'hover:bg-slate-50'}>
                          <td className="px-2 py-3 border border-slate-300 text-slate-600 text-center">
                            {idx + 1}
                          </td>
                          <td className="px-2 py-3 font-bold text-slate-800 border border-slate-300 text-center">
                            {row.ten_lop}
                          </td>
                          <td className="px-2 py-3 border border-slate-300">{totalTiet || ''}</td>
                          <td className="px-2 py-3 border border-slate-300">{row.diem_sdb || ''}</td>
                          <td className="px-2 py-3 border border-slate-300">{row.diem_cong_tru || ''}</td>
                          <td className="px-2 py-3 border border-slate-300">{totalVang || ''}</td>
                          <td className="px-2 py-3 text-red-600 border border-slate-300">{row.diem_noi_quy || ''}</td>
                          <td className="px-2 py-3 border border-slate-300 font-bold text-blue-600">
                            {row.tong_diem}
                          </td>
                          <td className={`px-2 py-3 border border-slate-300 ${rankStyle}`}>
                            {rankText}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        )}
      </Box>
    </Page>
  );
};

export default WeeklyResultsPage;
