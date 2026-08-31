<!-- 
=============================================================================
⛔ WARNING: SYSTEM FILE - DO NOT DELETE ⛔
Tệp tin này chứa tiêu chuẩn thiết kế lõi (Design System) của toàn bộ hệ thống.
Vui lòng KHÔNG XÓA để đảm bảo sự đồng bộ UI/UX cho các dự án và tính năng sau này.
=============================================================================
-->

# Hướng Dẫn Chuẩn Thiết Kế Giao Diện (UI/UX Guidelines)
Hệ Thống Đánh Giá Thi Đua

Tài liệu này quy định các tiêu chuẩn thiết kế giao diện (UI) và trải nghiệm người dùng (UX) nhằm đảm bảo tính đồng bộ, chuyên nghiệp và hiện đại trên toàn bộ hệ thống. Bất kỳ trang nào được tạo mới hoặc chỉnh sửa đều phải tuân thủ nghiêm ngặt các quy tắc dưới đây.

## 1. Bảng Màu Hệ Thống (Color Palette)
Hệ thống sử dụng các tông màu chủ đạo mang hơi hướng giáo dục, chuyên nghiệp:
- **Màu Nhận Diện Chính (Primary):** Xanh dương đậm `#224397`
- **Màu Nhấn (Accent/Hover):** Vàng cam `#FAB723`
- **Màu Nền (Background):** Sử dụng dải màu gradient nhạt: `linear-gradient(to bottom right, #f8fafc, #E4F6FD)`
- **Màu Đường Viền (Borders):** Xanh dương có độ trong suốt `border-[#224397]/25` hoặc `border-[#224397]/20`
- **Màu Text:** Xám đậm (`slate-800`, `slate-700`) cho text chính, xám nhạt (`slate-500`) cho text phụ/tiêu đề cột.

## 2. Layout & Nền (Background & Layout)
- Nền trang (Body) phải luôn áp dụng style gradient như sau (thường đặt trong thẻ `<style>`):
  ```css
  body, body > div.w-full.min-h-screen.bg-slate-50 {
      background: linear-gradient(to bottom right, #f8fafc, #E4F6FD) !important;
  }
  ```
- **Thanh cuộn dọc chính (Body Scrollbar):** Ẩn thanh cuộn mặc định của trình duyệt để giao diện gọn gàng.
  ```css
  body::-webkit-scrollbar, html::-webkit-scrollbar { display: none; }
  ```
- **Thanh cuộn phụ (Inner Scrollbar):** Nếu có bảng dài hoặc danh sách cuộn, sử dụng class `.list-scrollbar` với CSS custom màu xanh `#224397` và có mũi tên SVG (đã được định nghĩa sẵn trong các trang danh sách).

## 3. Cấu Trúc Khối Nội Dung (Cards / Panels)
Tất cả các khối nội dung (thông tin, danh sách, form) đều đặt trong Card:
- **Wrapper Card:** `class="bg-white rounded shadow-sm border border-[#224397]/25 overflow-hidden"`
- **Header Card (Tiêu đề khối):** `class="bg-slate-50 px-5 py-3 border-b border-[#224397]/25 font-semibold text-[#224397] flex items-center gap-2 text-sm uppercase"`
  - *Lưu ý:* Luôn kèm một icon SVG (Bootstrap Icons) ở trước tiêu đề.

## 4. Nút Bấm (Buttons) & Hiệu Ứng (Micro-Animations)
Hiệu ứng tương tác là "linh hồn" của giao diện hệ thống. Bắt buộc áp dụng hiệu ứng này cho các nút hành động (Thêm, Sửa, Xóa, Lưu...):
- **Hiệu ứng chuẩn (Hover Effects):** `hover:translate-x-1 hover:scale-[1.02] transition-all duration-300` (dịch sang phải 1 xíu và phóng to 2% một cách mượt mà).
- **Mẫu Nút Hành Động (Primary Action - Lưu/Thêm):**
  ```html
  <button class="px-4 py-2 bg-[#224397] text-white rounded hover:bg-[#FAB723] font-medium shadow-sm hover:translate-x-1 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
      <svg>...</svg> Text Nút
  </button>
  ```
- **Mẫu Nút Đóng/Hủy (Secondary Action):**
  ```html
  <button class="px-4 py-2 bg-white border border-slate-300 rounded text-slate-700 hover:bg-[#FAB723] hover:text-white hover:border-[#FAB723] hover:translate-x-1 hover:scale-[1.02] font-medium transition-all duration-300">
      Đóng
  </button>
  ```

## 5. Bảng Dữ Liệu (Data Tables)
Cấu trúc bảng phải tuân thủ để đồng bộ trên toàn hệ thống:
- **Container:** Đặt trong thẻ `div` có class `overflow-x-auto list-scrollbar` (để cuộn ngang và dọc mượt mà).
- **Table Tag:** `class="w-full text-left text-sm text-slate-600"`
- **Thead (Tiêu đề bảng):** `class="bg-slate-50 border-b border-[#224397]/25 text-xs uppercase font-semibold text-slate-500 sticky top-0"` (Header luôn phải dính - sticky khi cuộn).
  - *Hoặc sử dụng Custom CSS (Style 2):* `background-color: rgba(34, 67, 151, 0.08); color: #224397; font-weight: 800;`
- **Tbody (Dòng dữ liệu):**
  - Viền phân cách các dòng (nếu không dùng table lines): `class="divide-y divide-[#224397]/20"`
  - Hiệu ứng Hover dòng: Từng thẻ `<tr>` dùng class `class="hover:bg-slate-50 transition"` (hoặc `hover:bg-[#224397]/5`).
  - Viền chung cho các thẻ `td` (nếu dùng CSS thuần): `border: 1px solid rgba(34, 67, 151, 0.25);`

## 6. Hộp Thoại (Modals)
Cấu trúc Modal đòi hỏi sự chính xác tuyệt đối để hiệu ứng chuyển cảnh hoạt động đồng nhất:
- **Backdrop (Lớp phủ ngoài cùng):** `class="fixed inset-0 z-[10005] hidden flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0"`
- **Modal Content (Khối nội dung):** `class="modal-content bg-white rounded shadow-2xl border border-slate-300 w-full max-w-[800px] flex flex-col transform transition-all duration-300 scale-95 translate-y-4 opacity-0"`
  - *Giải thích:* Hiệu ứng Scale 95% và Translate Y tạo cảm giác modal "nổi lên" một cách êm ái khi bật.
- **Header Modal:** `class="bg-slate-50 border-b border-[#224397]/25 px-5 py-4 flex justify-between items-center shrink-0"`
  - Tiêu đề modal: `<h5 class="text-[#224397] font-bold flex items-center gap-2 text-lg">...</h5>`
- **Footer Modal (Khu vực nút):** `class="bg-slate-50 border-t border-[#224397]/25 px-5 py-3 flex justify-end gap-2 shrink-0"`

## 7. Các Điểm Nhấn Nhỏ (Micro Details)
- **Drop-down Menus (Menu thả xuống của Tác Vụ/Nhập Xuất):** Phải có hiệu ứng xuất hiện mượt: `opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right scale-95 group-hover:scale-100`.
- **Trạng thái vô hiệu hóa (Ví dụ HS nghỉ học):** Dùng class `row-strike-through` và màu `text-slate-400` cho các dòng dữ liệu để phân biệt trực quan.
- **Alerts / Toasts:** Sử dụng Toast notification mượt mà nằm ở góc trên phải, trượt vào (`translate-x-0`) và trượt ra (`translate-x-full`).

---
> [!IMPORTANT]  
> Bất kỳ dev nào khi xây dựng tính năng mới hoặc tạo trang mới, bắt buộc phải đối chiếu và tái sử dụng các bộ class Tailwind & CSS được ghi chú ở trên để duy trì sự cao cấp và tính "Signature" của hệ thống đánh giá thi đua.
