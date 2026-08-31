import { useQuery } from '@tanstack/react-query';
import { getProfile } from './profile.service';

export const PROFILE_QUERY_KEY = ['profile'] as const;

export const useProfile = () =>
  useQuery({
    queryKey: PROFILE_QUERY_KEY,
    queryFn: getProfile,
    staleTime: 0,
    refetchOnMount: 'always',
    refetchOnWindowFocus: true,
    refetchInterval: 3000, // Tự động đồng bộ quyền mới và trạng thái chỉnh sửa mỗi 3s
  });
