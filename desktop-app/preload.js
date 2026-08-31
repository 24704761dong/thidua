const { contextBridge, ipcRenderer } = require('electron');

// Expose a native API to the web app
contextBridge.exposeInMainWorld('electronAPI', {
    sendNotification: (title, body) => ipcRenderer.send('show-notification', { title, body }),
    doLogin: (credentials) => ipcRenderer.send('do-login', credentials),
    onLoginResponse: (callback) => ipcRenderer.on('login-response', callback),
    clearAppKey: () => ipcRenderer.send('clear-app-key'),
    toggleFullScreen: () => ipcRenderer.send('toggle-fullscreen')
});

// Inject our own Notification override so the web app's new Notification() works properly with Electron
window.addEventListener('DOMContentLoaded', () => {
    if (!window.Notification || window.Notification.permission !== 'granted') {
        const OriginalNotification = window.Notification;
        
        class DesktopNotification {
            constructor(title, options) {
                ipcRenderer.send('show-notification', { title, body: options?.body || '' });
            }
            static get permission() {
                return 'granted';
            }
            static requestPermission() {
                return Promise.resolve('granted');
            }
        }
        window.Notification = DesktopNotification;
    }
});
