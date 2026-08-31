import logoImg from '@/assets/logo.png';
import React, { useState, useEffect } from 'react';
import { Page, Spinner } from 'zmp-ui';
import Header from '@/components/Header';

const TermsPage: React.FC = () => {
  const [initialLoading, setInitialLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => setInitialLoading(false), 200);
    return () => clearTimeout(timer);
  }, []);

  if (initialLoading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-[#f0f6fc]">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page className="page bg-[#f0f6fc] min-h-screen">
      <Header title="Chính sách quyền riêng tư" variant="back" />
      
      <div className="flex-1 overflow-y-auto pb-10 px-3.5 pt-2">
        <div className="bg-white shadow-sm border border-slate-200 p-6 max-w-2xl mx-auto min-h-[80vh]">
          
          <div className="text-center pb-5 mb-5 border-b border-black">
            <h1 className="text-lg font-bold text-black uppercase tracking-wide m-0 leading-tight">
              CHÍNH SÁCH BẢO VỆ DỮ LIỆU CÁ NHÂN<br/>VÀ QUYỀN RIÊNG TƯ
            </h1>
            <p className="text-sm text-black mt-2 font-bold uppercase">
              TRƯỜNG TRUNG HỌC PHỔ THÔNG BÌNH SƠN
            </p>
          </div>

          <div className="text-black text-sm leading-relaxed space-y-4 text-justify">
            
            <div className="font-bold mb-2">Căn cứ:</div>
            <ul className="list-disc pl-6 space-y-1 mb-4 italic text-sm">
              <li>Luật Bảo vệ dữ liệu cá nhân số 91/2025/QH15 do Quốc hội ban hành;</li>
              <li>Nghị định số 13/2023/NĐ-CP do Chính phủ ban hành ngày 17/04/2023 về bảo vệ dữ liệu cá nhân;</li>
              <li>Các quy định hiện hành của Nền tảng Zalo Mini App.</li>
            </ul>

            <p>
              Chính sách bảo vệ dữ liệu cá nhân của Trường THPT Bình Sơn (sau đây gọi tắt là “Chính sách”) nhằm mục đích thông báo minh bạch tới Học sinh, Phụ huynh và Cán bộ giáo viên về việc xử lý thông tin cá nhân trên Ứng dụng Zalo Mini App "QLTĐ - THPT Bình Sơn".
            </p>
            <p>
              Bằng việc truy cập, xác thực bằng số Căn cước công dân và sử dụng các tính năng trên Ứng dụng, Người dùng xác nhận rằng đã đọc kỹ, hiểu rõ và chấp thuận toàn bộ các điều khoản trong Chính sách này.
            </p>

            {/* Điều 1 */}
            <h2 className="font-bold text-black mt-6 mb-2">Điều 1. Giải thích từ ngữ</h2>
            <ul className="list-none pl-4 space-y-2">
              <li><strong>1. Nhà trường:</strong> Là Trường Trung học Phổ thông Bình Sơn, đơn vị chủ quản hệ thống quản lý thi đua.</li>
              <li><strong>2. Người dùng:</strong> Là Học sinh, Phụ huynh, hoặc Cán bộ Giáo viên truy cập và sử dụng Zalo Mini App "QLTĐ - THPT Bình Sơn".</li>
              <li><strong>3. Ứng dụng:</strong> Là Zalo Mini App "QLTĐ - THPT Bình Sơn".</li>
            </ul>

            {/* Điều 2 */}
            <h2 className="font-bold text-black mt-6 mb-2">Điều 2. Xử lý Dữ liệu Cá nhân</h2>
            <p><strong>1. Nguyên tắc thu thập tối thiểu:</strong></p>
            <p className="pl-4">Ứng dụng tuân thủ nghiêm ngặt nguyên tắc thu thập tối thiểu. Nhà trường <strong>KHÔNG</strong> yêu cầu cấp quyền lấy Số điện thoại, Ảnh đại diện, Danh bạ hay Vị trí (Location) từ hệ thống Zalo của Người dùng.</p>
            
            <p className="mt-2"><strong>2. Các loại dữ liệu được xử lý:</strong></p>
            <ul className="list-disc pl-8 space-y-1">
              <li><strong>Thông tin xác thực:</strong> Số Căn cước công dân (CCCD). Người dùng chủ động cung cấp số CCCD duy nhất một lần để liên kết tài khoản Zalo với cơ sở dữ liệu của Nhà trường.</li>
              <li><strong>Thông tin từ nội bộ Nhà trường:</strong> Các thông tin hiển thị trên Ứng dụng (Họ tên, ngày sinh, lớp học, điểm thi đua, vi phạm nề nếp...) được trích xuất một chiều từ phần mềm quản lý nội bộ của Trường, hoàn toàn không lấy từ Zalo.</li>
              <li><strong>Thông tin định danh kỹ thuật:</strong> Zalo ID (định danh ẩn danh do Zalo cấp) dùng để hệ thống Zalo Notification Service (ZNS) gửi thông báo chính xác.</li>
            </ul>

            {/* Điều 3 */}
            <h2 className="font-bold text-black mt-6 mb-2">Điều 3. Mục đích xử lý dữ liệu</h2>
            <p>Nhà trường chỉ xử lý dữ liệu cho các mục đích hợp pháp sau đây:</p>
            <ul className="list-disc pl-8 space-y-1">
              <li>Xác thực chính xác danh tính của học sinh thuộc Trường THPT Bình Sơn nhằm cấp quyền truy cập vào xem điểm thi đua cá nhân.</li>
              <li>Theo dõi, quản lý và hiển thị minh bạch kết quả đánh giá nề nếp thi đua tuần, điểm số, khen thưởng hoặc vi phạm.</li>
              <li>Gửi các thông báo quan trọng, lịch học, sự kiện hoặc các cảnh báo kịp thời từ Ban Giám hiệu, Đoàn trường tới tận thiết bị của Học sinh/Phụ huynh.</li>
            </ul>

            {/* Điều 4 */}
            <h2 className="font-bold text-black mt-6 mb-2">Điều 4. Cam kết bảo mật và Không chia sẻ dữ liệu</h2>
            <ul className="list-none pl-4 space-y-2">
              <li><strong>1. Không thương mại hóa và Không chia sẻ:</strong> Nhà trường cam kết tuyệt đối không bán, trao đổi, chia sẻ hay chuyển giao bất kỳ dữ liệu cá nhân nào của Người dùng cho các bên thứ ba (bao gồm cả đối tác quảng cáo, đối tác thương mại).</li>
              <li><strong>2. Xử lý nội bộ:</strong> Toàn bộ quy trình xác thực bằng CCCD và tra cứu dữ liệu được thực hiện khép kín qua API mã hóa, giao tiếp trực tiếp với máy chủ cơ sở dữ liệu được đặt tại hạ tầng nội bộ do Trường THPT Bình Sơn quản lý.</li>
            </ul>

            {/* Điều 5 */}
            <h2 className="font-bold text-black mt-6 mb-2">Điều 5. Quyền của Người dùng</h2>
            <ul className="list-none pl-4 space-y-2">
              <li><strong>1. Quyền xem và kiểm tra:</strong> Người dùng có quyền tra cứu toàn bộ thông tin học tập, thi đua của mình hiển thị trên Ứng dụng.</li>
              <li><strong>2. Quyền yêu cầu chỉnh sửa:</strong> Nếu phát hiện thông tin thi đua hoặc thông tin cá nhân sai lệch, Người dùng có quyền liên hệ trực tiếp với Giáo viên chủ nhiệm hoặc Đoàn trường để điều chỉnh trên hệ thống gốc.</li>
              <li><strong>3. Quyền rút lại sự đồng ý:</strong> Người dùng có quyền rút lại quyền xử lý dữ liệu bằng cách hủy bỏ liên kết tài khoản Zalo với số CCCD hoặc xóa Ứng dụng bất kỳ lúc nào nếu không còn nhu cầu.</li>
            </ul>

            {/* Điều 6 */}
            <h2 className="font-bold text-black mt-6 mb-2">Điều 6. Nghĩa vụ của Người dùng</h2>
            <ul className="list-none pl-4 space-y-2">
              <li><strong>1. Tự bảo vệ tài khoản:</strong> Người dùng có trách nhiệm giữ bí mật thông tin tài khoản Zalo của cá nhân và quản lý thiết bị an toàn để tránh bị truy cập trái phép.</li>
              <li><strong>2. Tính chính xác:</strong> Người dùng cam kết sử dụng chính xác số CCCD của bản thân. Mọi hành vi mạo danh sử dụng CCCD của người khác để truy cập trái phép vào dữ liệu thi đua của học sinh khác đều bị nghiêm cấm và sẽ bị xử lý theo kỷ luật của Nhà trường và pháp luật.</li>
            </ul>

            {/* Điều 7 */}
            <h2 className="font-bold text-black mt-6 mb-2">Điều 7. Lưu trữ dữ liệu</h2>
            <p>
              Dữ liệu cá nhân và kết quả thi đua, học tập được mã hóa và lưu trữ an toàn trong suốt thời gian học sinh theo học tại trường và theo quy định về thời hạn lưu trữ hồ sơ của ngành Giáo dục & Đào tạo, hoặc cho đến khi Người dùng có yêu cầu hủy liên kết.
            </p>

            {/* Điều 8 */}
            <h2 className="font-bold text-black mt-6 mb-2">Điều 8. Thông tin liên hệ</h2>
            <p>
              Trong trường hợp Người dùng có bất kỳ thắc mắc nào về Chính sách này hoặc muốn thực hiện quyền khiếu nại, báo lỗi liên quan tới dữ liệu cá nhân, vui lòng liên hệ với Nhà trường qua các đầu mối sau:
            </p>
            <ul className="list-none pl-6 space-y-1 mt-2 font-bold">
              <li>- Hotline hỗ trợ: 036.256.6146</li>
              <li>- Email tiếp nhận: csm@c3binhson.edu.vn</li>
              <li>- Zalo OA chính thức: Đoàn trường THPT Bình Sơn - TP Đồng Nai</li>
            </ul>

          </div>

          <div className="mt-8 pt-6 border-t border-black text-right text-sm text-black">
            Ngày ban hành: {new Date().toLocaleDateString('vi-VN')}<br />
            <strong>ĐẠI DIỆN TRƯỜNG THPT BÌNH SƠN</strong>
          </div>

        </div>
      </div>
    </Page>
  );
};

export default TermsPage;
