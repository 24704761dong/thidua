import api from '@/lib/api';
import { ENDPOINTS } from '@/constants/endpoints';
import type { Profile } from './profile.types';

export const fetchProfile = () => api.get<any>(ENDPOINTS.PROFILE);
