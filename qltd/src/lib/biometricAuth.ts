import { Capacitor } from '@capacitor/core';

// Dynamic import to avoid errors on web
let BiometricAuth: any = null;

async function getBiometricAuth() {
  if (!BiometricAuth && Capacitor.isNativePlatform()) {
    try {
      const mod = await import('@aparajita/capacitor-biometric-auth');
      BiometricAuth = mod.BiometricAuth;
    } catch (e) {
      console.error('[Biometric] Failed to load BiometricAuth:', e);
    }
  }
  return BiometricAuth;
}

export async function isBiometricAvailable(): Promise<boolean> {
  if (!Capacitor.isNativePlatform()) return false;
  
  try {
    const auth = await getBiometricAuth();
    if (!auth) return false;
    
    await auth.checkBiometry();
    const result = await auth.checkBiometry();
    return result.isAvailable;
  } catch (e) {
    console.error('[Biometric] Check availability error:', e);
    return false;
  }
}

export async function authenticateWithBiometric(): Promise<boolean> {
  if (!Capacitor.isNativePlatform()) return false;
  
  try {
    const auth = await getBiometricAuth();
    if (!auth) return false;

    await auth.authenticate({
      reason: 'Xác thực để đăng nhập',
      cancelTitle: 'Hủy',
      allowDeviceCredential: true,
    });
    
    return true;
  } catch (e: any) {
    console.error('[Biometric] Auth failed:', e);
    return false;
  }
}

// Lưu thông tin đăng nhập vào secure storage cho biometric login
export function saveBiometricCredentials(username: string, token: string) {
  localStorage.setItem('biometric_credentials', JSON.stringify({ username, token }));
  localStorage.setItem('biometric_enabled', 'true');
}

export function getBiometricCredentials(): { username: string; token: string } | null {
  const saved = localStorage.getItem('biometric_credentials');
  if (!saved) return null;
  try {
    return JSON.parse(saved);
  } catch {
    return null;
  }
}

export function clearBiometricCredentials() {
  localStorage.removeItem('biometric_credentials');
  localStorage.removeItem('biometric_enabled');
}
