import { Capacitor } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';
import api from '@/lib/api';

let hasInitialized = false;

export async function initPushNotifications() {
  // Chỉ chạy trên native (Android/iOS), không chạy trên web
  if (!Capacitor.isNativePlatform()) {
    console.log('[Push] Not native platform, skipping push init');
    return;
  }
  
  if (hasInitialized) return;
  hasInitialized = true;

  try {
    // Yêu cầu quyền thông báo
    let permStatus = await PushNotifications.checkPermissions();
    
    if (permStatus.receive === 'prompt') {
      permStatus = await PushNotifications.requestPermissions();
    }

    if (permStatus.receive !== 'granted') {
      console.warn('[Push] Permission not granted');
      return;
    }

    // Đăng ký nhận push
    await PushNotifications.register();

    // Lắng nghe sự kiện nhận token
    PushNotifications.addListener('registration', async (token) => {
      console.log('[Push] FCM Token:', token.value);
      
      // Gửi token lên server để lưu
      try {
        await api.post('/api/zalo/register-fcm-token', {
          fcm_token: token.value,
          platform: 'android'
        });
        console.log('[Push] Token registered on server');
      } catch (e) {
        console.error('[Push] Failed to register token on server:', e);
      }
    });

    // Lắng nghe lỗi đăng ký
    PushNotifications.addListener('registrationError', (error) => {
      console.error('[Push] Registration error:', error);
    });

    // Lắng nghe thông báo khi app đang mở (foreground)
    PushNotifications.addListener('pushNotificationReceived', (notification) => {
      console.log('[Push] Notification received in foreground:', notification);
      // Có thể hiển thị snackbar hoặc badge ở đây
    });

    // Lắng nghe khi người dùng nhấn vào thông báo
    PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
      console.log('[Push] Notification tapped:', notification);
      // Có thể navigate đến trang thông báo ở đây
      // window.location.hash = '#/notifications';
    });

  } catch (error) {
    console.error('[Push] Init error:', error);
  }
}
