# HƯỚNG DẪN CÀI ĐẶT VÀ CHẠY ỨNG DỤNG TRÊN MACOS

Ứng dụng **ThiDua System** được phát triển trên nền tảng Electron.js, hỗ trợ đầy đủ cho mọi dòng máy Mac:
* **Mac chip Intel** (x64)
* **Mac Apple Silicon M1, M2, M3, M4** (arm64)

---

## CÁCH 1: Chạy trực tiếp từ mã nguồn trên máy Mac (Dành cho nhà phát triển / Quản trị viên)

1. Sao chép thư mục `desktop-app` sang máy Mac.
2. Mở ứng dụng **Terminal** trên Mac, chuyển vào thư mục này:
   ```bash
   cd /duong-dan-toi/desktop-app
   ```
3. Cài đặt các gói thư viện:
   ```bash
   npm install
   ```
4. Khởi chạy ứng dụng:
   ```bash
   npm start
   ```

---

## CÁCH 2: Xuất file cài đặt `.dmg` trên máy Mac

Trong Terminal tại thư mục `desktop-app`, chạy lệnh:
```bash
npm run build:mac
```
Sau khi hoàn tất, bạn sẽ có các file cài đặt trong thư mục `desktop-app/release/`:
* `ThiDua System-1.0.0.dmg` (Bấm đúp để kéo vào thư mục Applications như ứng dụng Mac thông thường)
* `ThiDua System-1.0.0-arm64.dmg` (Dành riêng cho chip Apple Silicon M1/M2/M3)
* `ThiDua System-1.0.0-mac.zip` (Bản nén không cần cài đặt)

---

## CÁCH 3: Tự động Build file `.dmg` bằng GitHub Actions (Không cần sở hữu máy Mac)

Dự án đã được tích hợp sẵn tệp cấu hình `.github/workflows/build_desktop.yml`:
1. Đẩy mã nguồn lên kho lưu trữ **GitHub**.
2. Vào tab **Actions** trên GitHub $\rightarrow$ chọn workflow **"Build Desktop App (Windows & macOS)"**.
3. Bấm **Run workflow**.
4. GitHub sẽ sử dụng máy chủ ảo macOS để tự động biên dịch và tạo sẵn file `.dmg` để bạn tải về ngay lập tức!
