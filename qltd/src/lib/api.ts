import axios from 'axios';
import { queryClient } from './queryClient';
import { get, set } from 'idb-keyval';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'https://c3binhson.edu.vn/thidua', 
  timeout: 60000,
  headers: { 'Content-Type': 'application/json' },
});

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('zalo_jwt_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    const namHocId = localStorage.getItem('selected_nam_hoc_id');
    if (namHocId) {
      config.headers['X-Nam-Hoc-Id'] = namHocId;
    }
    if (config.method === 'get') {
      config.params = {
        ...config.params,
        _t: new Date().getTime(),
      };
    }
    return config;
  },
  (error) => Promise.reject(error),
);

api.interceptors.response.use(
  async (response) => {
    if (typeof response.data === 'string') {
      const jsonStart = response.data.indexOf('{');
      const jsonEnd = response.data.lastIndexOf('}');
      if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
        try {
          const parsed = JSON.parse(response.data.substring(jsonStart, jsonEnd + 1));
          response.data = parsed;
          if (!parsed.success && parsed.message) {
             return Promise.reject({ response, message: parsed.message, config: response.config });
          }
        } catch (e) {}
      }
    }
    // Chỉ kích hoạt popup cảnh báo nghỉ học nếu trang_thai_hoc_tap là 'nghi_hoc' (cho phép 'dang_hoc' và 'da_tot_nghiep')
    if (response.data?.data?.trang_thai_hoc_tap === 'nghi_hoc') {
      queryClient.setQueryData(['student_inactive'], true);
    }
    // Chỉ cache các endpoint công khai hoặc không nhạy cảm
    const cacheableEndpoints = ['/api/zalo/get-nam-hoc', '/api/zalo/public-emulation-results', '/api/zalo/public-weeks'];
    const isCacheable = cacheableEndpoints.some(ep => response.config?.url?.includes(ep));

    if (isCacheable && response.config && response.config.method === 'get' && response.data?.success) {
      try {
        const _params = { ...response.config.params };
        delete _params._t; // bỏ qua cache buster
        const urlParams = new URLSearchParams(_params).toString();
        const cacheKey = 'cache_' + response.config.url + (urlParams ? '?' + urlParams : '');
        await set(cacheKey, response.data);
      } catch (e) {
        // Bỏ qua lỗi ghi db
      }
    }
    return response;
  },
  async (error) => {
    // Cơ chế Offline Fallback: Nếu lỗi kết nối/offline, trả về dữ liệu từ bộ nhớ đệm IndexedDB
    if (error.config && error.config.method === 'get') {
      try {
        const _params = { ...error.config.params };
        delete _params._t;
        const urlParams = new URLSearchParams(_params).toString();
        const cacheKey = 'cache_' + error.config.url + (urlParams ? '?' + urlParams : '');
        const cachedData = await get(cacheKey);
        if (cachedData) {
          return Promise.resolve({
            data: cachedData,
            status: 200,
            statusText: 'OK (Cached)',
            headers: {},
            config: error.config,
            isCached: true
          });
        }
      } catch (e) {
        // Bỏ qua lỗi đọc db
      }
    }
    return Promise.reject(error);
  },
);

export default api;
