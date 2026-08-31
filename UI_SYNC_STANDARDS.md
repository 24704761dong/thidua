# CHUẨN ĐỒNG BỘ GIAO DIỆN HỆ THỐNG (UI SYNC STANDARDS)

Tài liệu này định nghĩa các components chuẩn bắt buộc phải sử dụng để đồng bộ giao diện trên toàn bộ hệ thống Đánh Giá Thi Đua, thay thế cho các thành phần mặc định của trình duyệt hoặc các design cũ.

> **QUY TẮC TỐI THƯỢNG:**
> 1. KHÔNG SỬ DỤNG `alert()`, `confirm()`, `prompt()` mặc định của trình duyệt.
> 2. Các Dropdown thao tác (Tác vụ, Nhập/Xuất) phải dùng chuẩn của trang Quản Lý Học Sinh.
> 3. Modal Popup nội dung phải dùng cấu trúc chuẩn của trang Quản Lý Năm Học.
> 4. Thông báo góc màn hình (Toast/Session) phải dùng style của trang Nhập Vi Phạm.

---

## 1. Hệ Nút Thao Tác (Dropdown / Nhập Xuất)
*Lấy chuẩn từ trang Danh Sách Học Sinh.*

Sử dụng cấu trúc `group` của Tailwind để tạo hiệu ứng hover mượt mà mà không cần JavaScript.

```html
<div class="relative inline-block text-left group z-50">
    <!-- Nút Trigger -->
    <button type="button" class="px-2 py-1 bg-white border border-[#224397]/25 rounded text-[#224397] hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] transition-all duration-200 font-medium flex items-center gap-1 text-[11px] shadow-sm whitespace-nowrap">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-lightning-charge-fill" viewBox="0 0 16 16"><path d="..."/></svg> 
        Tác vụ 
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-chevron-down text-[9px]" viewBox="0 0 16 16"><path d="..."/></svg>
    </button>
    
    <!-- Menu Dropdown -->
    <ul class="absolute right-0 mt-1 w-40 bg-white rounded shadow-lg border border-slate-100 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] transform origin-top-right scale-95 group-hover:scale-100 py-1">
        
        <!-- Item chuẩn -->
        <li>
            <a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-blue-50 hover:text-[#224397]" href="#">
                <svg>...</svg> Tên chức năng
            </a>
        </li>
        
        <!-- Đường phân cách -->
        <li><hr class="border-t border-slate-100 my-1"></li>
        
        <!-- Item cảnh báo (Xóa/Hủy) -->
        <li>
            <a class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-red-600 hover:bg-red-50" href="#">
                <svg>...</svg> Xóa dữ liệu
            </a>
        </li>
    </ul>
</div>
```

---

## 2. Modal Thông Báo / Form Nhập Liệu
*Lấy chuẩn từ trang Quản Lý Năm Học.*

Modal bắt buộc phải có hiệu ứng `backdrop-blur`, scale mượt từ `95%` lên `100%`, và bo góc `rounded-xl`.

### 2.1 Cấu trúc HTML Modal Chuẩn
```html
<!-- Wrapper Modal -->
<div id="standardModal" class="fixed inset-0 z-[10005] hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal('standardModal')">
    
    <!-- Nội dung Modal -->
    <div class="bg-white rounded-xl shadow-2xl w-[500px] max-w-[90%] flex flex-col overflow-hidden border border-slate-300 transform transition-all duration-300 scale-95 translate-y-4 opacity-0 modal-content-box" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h5 class="text-lg font-bold text-[#224397] flex items-center gap-2">
                <svg class="text-[#FAB723]">...</svg> Tiêu Đề Modal
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" onclick="closeModal('standardModal')">
                <svg class="bi bi-x-lg">...</svg>
            </button>
        </div>
        
        <!-- Body -->
        <div class="px-6 py-5 space-y-4 text-sm text-slate-600">
            Nội dung form hoặc text thông báo...
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
            <button type="button" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 transition" onclick="closeModal('standardModal')">Hủy</button>
            <button type="button" class="px-4 py-2 text-[13px] font-bold text-slate-900 bg-[#FAB723] border border-[#FAB723] rounded shadow-sm hover:bg-[#e5a61d] transition">Xác Nhận</button>
        </div>
        
    </div>
</div>
```

### 2.2 JS Animation cho Modal
```javascript
function openModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.modal-content-box');
    
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    void modal.offsetWidth; // Force reflow
    
    modal.style.opacity = '1';
    modal.classList.remove('opacity-0');
    content.style.transform = 'scale(1) translateY(0)';
    content.style.opacity = '1';
    content.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.modal-content-box');
    
    modal.style.opacity = '0';
    content.style.transform = 'scale(0.95) translateY(1rem)';
    content.style.opacity = '0';
    
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }, 300);
}
```

---

## 3. SweetAlert2 (Thay thế alert/confirm trình duyệt)
Nếu không dùng Modal HTML tự code, bắt buộc sử dụng `AppSwal.fire` với cấu hình CSS custom cực kỳ khắt khe để đồng bộ theme hệ thống.

```javascript
// Dùng cho xác nhận Xóa / Hành động nguy hiểm
AppSwal.fire({
    title: 'Cảnh Báo!',
    text: 'Bạn có chắc chắn muốn thực hiện hành động này?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy',
    customClass: {
        popup: 'bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 py-6 px-6',
        title: 'text-red-600 font-bold text-xl mt-0', // Màu đỏ cho cảnh báo, màu #224397 cho thông tin
        htmlContainer: 'text-slate-600 font-medium text-[14.5px] mt-2 mb-2',
        actions: 'flex justify-center gap-3 w-full mt-6',
        confirmButton: 'bg-red-600 text-white rounded-lg px-6 py-2 font-medium shadow-sm hover:bg-red-700 hover:scale-110 hover:shadow-md transition-all duration-300 outline-none', // Nút chính
        cancelButton: 'bg-white text-slate-600 rounded-lg px-6 py-2 font-medium shadow-sm border border-slate-300 hover:bg-slate-50 transition-all duration-300 outline-none', // Nút phụ
        icon: 'scale-[0.85] my-2'
    },
    buttonsStyling: false
});
```

---

## 4. Session Thông Báo (Toast Notifications)
*Lấy chuẩn từ trang Nhập Vi Phạm.*
Hiển thị góc dưới bên phải, tự động biến mất, dùng cho thông báo trạng thái "Lưu thành công", "Lỗi dữ liệu", v.v.

### 4.1 CSS Cốt Lõi (Thêm vào thẻ style hoặc file CSS)
```css
#toast-container {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    z-index: 10000; display: flex; flex-direction: column; gap: 0.5rem;
}
.toast-item {
    padding: 0.75rem 1.25rem;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 600;
    display: flex; align-items: center; gap: 0.6rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    animation: toastIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    max-width: 380px;
    border: 1px solid;
}
.toast-success { background: #f0fdf4; color: #166534; border-color: #86efac; }
.toast-error { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
.toast-warning { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
.toast-info { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }

@keyframes toastIn { from { opacity:0; transform: translateX(50px); } to { opacity:1; transform: translateX(0); } }
@keyframes toastOut { to { opacity:0; transform: translateX(50px); } }
```

### 4.2 Cấu Trúc HTML Bắt Buộc (Thêm vào cuối thẻ `<body>`)
```html
<div id="toast-container"></div>
```

### 4.3 JS Helper Function
```javascript
function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast-item toast-${type}`;
    
    // Icon theo type
    let icon = '';
    if (type === 'success') icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>`;
    else if (type === 'error') icon = `<svg width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle-fill"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>`;
    
    toast.innerHTML = `${icon} <span>${message}</span>`;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'toastOut 0.3s ease forwards';
        setTimeout(() => { toast.remove(); }, 300);
    }, duration);
}
```
