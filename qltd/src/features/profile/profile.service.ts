import { fetchProfile } from './profile.api';
import type { Profile } from './profile.types';

export const getProfile = async (): Promise<Profile> => {
  const { data: res } = await fetchProfile();
  
  // Ánh xạ dữ liệu từ backend (api_zalo_get_profile.php) sang cấu trúc giao diện
  const user = res.data;
  return {
    name: user.ho_dem + ' ' + user.ten,
    faculty: 'Lớp ' + (user.ten_lop || 'Chưa xếp lớp'),
    studentId: user.ma_hoc_sinh,
    address: user.chuc_vu || 'Học sinh',
    tong_diem_tru: user.tong_diem_tru || 0,
    raw_data: user,
    card_image_url: res.card_image_url || null,
    edit_config: res.edit_config || null
  };
};
