const { app, BrowserWindow, ipcMain, Notification, shell, session, net } = require('electron');
const path = require('path');
const fs = require('fs');
const os = require('os');
const MACHINE_NAME = os.hostname();

let mainWindow;
let splash;
let activationWindow;
let currentAppKey = null;

const configPath = path.join(app.getPath('userData'), 'app-config.json');

// Load config
try {
    if (fs.existsSync(configPath)) {
        const configData = JSON.parse(fs.readFileSync(configPath, 'utf8'));
        currentAppKey = configData.appKey || null;
    }
} catch (e) {
    console.error("Lỗi đọc config:", e);
}

function showActivationWindow() {
    activationWindow = new BrowserWindow({
        width: 480,
        height: 480,
        transparent: true,
        frame: false,
        icon: path.join(__dirname, 'icon.png'),
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false
        }
    });
    activationWindow.loadFile('activation.html');
}

function createWindow() {
    // Create Splash Screen
    splash = new BrowserWindow({
        width: 600,
        height: 400,
        transparent: true,
        frame: false,
        alwaysOnTop: true,
        icon: path.join(__dirname, 'icon.png'),
        webPreferences: {
            nodeIntegration: false
        }
    });
    splash.loadFile('splash.html');

    const isMac = process.platform === 'darwin';

    // Create Main Window
    const windowOptions = {
        width: 1280,
        height: 800,
        minWidth: 1024,
        minHeight: 768,
        fullscreen: true, // Mặc định khởi động Toàn màn hình
        show: false, // Don't show until ready
        autoHideMenuBar: true, // Hide default menu bar
        titleBarStyle: isMac ? 'hiddenInset' : 'hidden', // Native traffic lights on Mac
        icon: path.join(__dirname, 'icon.png'),
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            preload: path.join(__dirname, 'preload.js'),
        }
    };

    if (!isMac) {
        windowOptions.titleBarOverlay = {
            color: '#ffffff',
            symbolColor: '#224397',
            height: 0 // Mặc định ẩn 3 nút điều hướng ở góc trên bên phải
        };
    }

    mainWindow = new BrowserWindow(windowOptions);

    mainWindow.on('enter-full-screen', () => {
        if (!isMac) {
            try {
                mainWindow.setTitleBarOverlay({ height: 0 });
            } catch (e) {}
        }
    });

    mainWindow.on('leave-full-screen', () => {
        if (!isMac) {
            try {
                mainWindow.setTitleBarOverlay({
                    color: '#ffffff',
                    symbolColor: '#224397',
                    height: 35
                });
            } catch (e) {}
        }
    });

    const targetUrl = 'https://c3binhson.edu.vn/thidua/admin';

    function isAuthRedirect(url) {
        if (!url || url.includes('/dang-nhap-xu-ly')) return false;
        return url.includes('/tracuu') || url.includes('/dang-nhap');
    }

    // Intercept client-side navigation
    mainWindow.webContents.on('will-navigate', (event, url) => {
        if (isAuthRedirect(url)) {
            event.preventDefault();
            mainWindow.loadFile('login.html');
        }
    });
    
    // Intercept server-side redirects (e.g., 302 from /admin to /tracuu)
    mainWindow.webContents.on('will-redirect', (event, url) => {
        if (isAuthRedirect(url)) {
            event.preventDefault();
            mainWindow.loadFile('login.html');
        }
    });

    // Lắng nghe tín hiệu X-App-Key-Invalid từ Server để văng ra màn hình nhập Key
    session.defaultSession.webRequest.onHeadersReceived((details, callback) => {
        const headers = details.responseHeaders || {};
        const isInvalid = headers['x-app-key-invalid'] || headers['X-App-Key-Invalid'];
        if (isInvalid) {
            // Server báo Key không hợp lệ (đã bị đổi) -> Clear Key và văng ra ngoài
            currentAppKey = null;
            try {
                if (fs.existsSync(configPath)) fs.unlinkSync(configPath);
            } catch (e) {}
            
            // Hiện lại modal kích hoạt
            if (mainWindow) {
                mainWindow.close();
            }
            showActivationWindow();
        }
        callback({ cancel: false, responseHeaders: headers });
    });

    // Catch did-navigate just in case
    mainWindow.webContents.on('did-navigate', (event, url) => {
        if (isAuthRedirect(url)) {
            mainWindow.loadFile('login.html');
        }
    });

    // Load the web app
    mainWindow.loadURL(targetUrl);

    mainWindow.webContents.on('did-finish-load', () => {
        if (splash && !splash.isDestroyed()) {
            setTimeout(() => {
                splash.close();
                mainWindow.setFullScreen(true);
                try {
                    mainWindow.setTitleBarOverlay({ height: 0 });
                } catch(e) {}
                mainWindow.show();
            }, 1000); // Give the splash screen a moment to be seen!
        }
        
        // Inject CSS to make the header draggable
        mainWindow.webContents.insertCSS(`
            /* Top draggable area on desktop dashboard */
            .electron-drag-bar, #electronDragBar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 140px !important;
                height: 35px !important;
                -webkit-app-region: drag !important;
                z-index: 8 !important;
            }
            /* Makes the top navbar draggable */
            header, .navbar, .top-bar, .bg-white.shadow-sm.h-16 {
                -webkit-app-region: drag !important;
            }
            /* Exclude clickable elements from dragging */
            button, input, a, .btn, .cursor-pointer, .select2, .os-desktop-icons, .desktop-icon-item, .desktop-widget, .taskbar, .start-menu, .os-window {
                -webkit-app-region: no-drag !important;
            }
            /* Custom Scrollbar for better UI */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            ::-webkit-scrollbar-track {
                background: #f8fafc;
            }
            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        `);
    });

    function toggleWindowMode() {
        if (!mainWindow) return;
        const isFS = mainWindow.isFullScreen();
        if (isFS) {
            // Đang full màn -> Thoát full màn, hiển thị 3 nút điều hướng ở góc trên bên phải
            mainWindow.setFullScreen(false);
            try {
                mainWindow.setTitleBarOverlay({
                    color: '#ffffff',
                    symbolColor: '#224397',
                    height: 35
                });
            } catch (e) {}
        } else {
            // Đang cửa sổ -> Bật toàn màn hình, tự động ẩn 3 nút điều hướng đi
            mainWindow.setFullScreen(true);
            try {
                mainWindow.setTitleBarOverlay({ height: 0 });
            } catch (e) {}
        }
    }

    // IPC to toggle fullscreen
    ipcMain.on('toggle-fullscreen', () => {
        toggleWindowMode();
    });

    // Handle F11 for Fullscreen
    mainWindow.webContents.on('before-input-event', (event, input) => {
        if (input.key === 'F11' && input.type === 'keyDown') {
            toggleWindowMode();
            event.preventDefault();
        }
    });

    // Handle OAuth Popups (Google/Zalo)
    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        if (url.includes('google.com') || url.includes('zalo.me') || url.includes('oauth')) {
            return {
                action: 'allow',
                overrideBrowserWindowOptions: {
                    frame: true,
                    autoHideMenuBar: true,
                    width: 600,
                    height: 800
                }
            };
        }
        // Open external links in default OS browser
        shell.openExternal(url);
        return { action: 'deny' };
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

app.whenReady().then(() => {
    // Handle HTTP Header Injection for App Key
    session.defaultSession.webRequest.onBeforeSendHeaders((details, callback) => {
        if (currentAppKey) {
            details.requestHeaders['X-Desktop-App-Key'] = currentAppKey;
            details.requestHeaders['X-Desktop-Machine-Name'] = MACHINE_NAME;
        }
        callback({ requestHeaders: details.requestHeaders });
    });

    if (!currentAppKey) {
        showActivationWindow();
    } else {
        createWindow();
    }

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) {
            if (!currentAppKey) showActivationWindow();
            else createWindow();
        }
    });
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') app.quit();
});

// IPC Handler for Notifications
ipcMain.on('show-notification', (event, { title, body }) => {
    if (Notification.isSupported()) {
        const notif = new Notification({ 
            title, 
            body, 
            icon: path.join(__dirname, 'icon.png') 
        });
        notif.show();
    }
});

// IPC Handler for Saving App Key
ipcMain.on('save-app-key', (event, key) => {
    currentAppKey = key;
    try {
        fs.writeFileSync(configPath, JSON.stringify({ appKey: key }), 'utf8');
    } catch (e) {
        console.error("Lỗi lưu config:", e);
    }
    
    if (activationWindow) {
        activationWindow.close();
    }
    createWindow();
});

// IPC Handler for Clearing App Key
ipcMain.on('clear-app-key', (event) => {
    currentAppKey = null;
    try {
        if (fs.existsSync(configPath)) {
            fs.unlinkSync(configPath);
        }
    } catch (e) {}
    
    if (mainWindow) {
        mainWindow.close();
    }
    showActivationWindow();
});

// IPC Handler for Local Login
ipcMain.on('do-login', async (event, { username, password }) => {
    try {
        const postData = new URLSearchParams();
        postData.append('ten_dang_nhap', username);
        postData.append('mat_khau', password);
        postData.append('remember_me', '1');

        const headers = {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        };
        if (currentAppKey) {
            headers['X-Desktop-App-Key'] = currentAppKey;
            headers['X-Desktop-Machine-Name'] = MACHINE_NAME;
        }

        const response = await net.fetch('https://c3binhson.edu.vn/thidua/dang-nhap-xu-ly', {
            method: 'POST',
            headers: headers,
            body: postData.toString()
        });

        const rawText = await response.text();
        let data = {};
        try {
            data = JSON.parse(rawText);
        } catch (parseErr) {
            console.error('Lỗi parse JSON từ server:', rawText);
            data = { success: false, message: 'Phản hồi từ máy chủ không hợp lệ.' };
        }

        // Đảm bảo Cookie Session được ghi chắc chắn vào Cookie Store của Electron
        try {
            const setCookieHeaders = (typeof response.headers.getSetCookie === 'function') 
                ? response.headers.getSetCookie() 
                : [response.headers.get('set-cookie')].filter(Boolean);

            if (setCookieHeaders && setCookieHeaders.length > 0) {
                for (const cookieStr of setCookieHeaders) {
                    const parts = cookieStr.split(';');
                    const nameValue = parts[0].split('=');
                    if (nameValue.length >= 2) {
                        const cookieName = nameValue[0].trim();
                        const cookieVal = nameValue.slice(1).join('=');
                        await session.defaultSession.cookies.set({
                            url: 'https://c3binhson.edu.vn',
                            domain: '.c3binhson.edu.vn',
                            name: cookieName,
                            value: cookieVal,
                            path: '/',
                            secure: true,
                            httpOnly: false,
                            sameSite: 'no_restriction'
                        }).catch(e => console.error('Cookie set error 1:', e));
                        
                        await session.defaultSession.cookies.set({
                            url: 'https://c3binhson.edu.vn/thidua/',
                            name: cookieName,
                            value: cookieVal,
                            path: '/',
                            secure: true
                        }).catch(e => console.error('Cookie set error 2:', e));
                    }
                }
            }
            await session.defaultSession.cookies.flushStore();
        } catch (cookieErr) {
            console.error('Lỗi lưu cookie:', cookieErr);
        }

        event.reply('login-response', data);

        if (data.success) {
            setTimeout(() => {
                if (mainWindow) {
                    mainWindow.loadURL('https://c3binhson.edu.vn/thidua/admin');
                }
            }, 300);
        }
    } catch (err) {
        console.error('Lỗi đăng nhập Desktop App:', err);
        event.reply('login-response', { 
            success: false, 
            message: 'Không thể kết nối đến máy chủ: ' + (err.message || err) 
        });
    }
});

