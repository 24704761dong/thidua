import { queryClient } from '@/lib/queryClient';

export const isAuthenticated = (): boolean => {
  const token = localStorage.getItem('zalo_jwt_token');
  if (!token) return false;
  
  try {
    const parts = token.split('.');
    if (parts.length !== 3) return false;
    
    // Decode base64 URL payload
    const payloadBase64 = parts[1].replace(/-/g, '+').replace(/_/g, '/');
    const payload = JSON.parse(atob(payloadBase64));
    
    // Check expiration (exp is in seconds)
    if (payload.exp && payload.exp * 1000 < Date.now()) {
      // Token expired, let's clean up
      logout();
      return false;
    }
    return true;
  } catch (e) {
    return false;
  }
};

export const logout = (): void => {
  localStorage.removeItem('zalo_jwt_token');
  localStorage.removeItem('selected_nam_hoc_id');
  localStorage.removeItem('grant_permission_attempts');
  queryClient.clear();
};
