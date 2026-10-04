---
name: feature-development-safety
description: >-
  Quy trình phát triển tính năng an toàn, kiểm soát tác động chéo (Impact Analysis),
  bảo toàn các tính năng cũ và loại bỏ lỗi hồi quy (Regression Bugs).
---

# QUY TRÌNH PHÁT TRIỂN TÍNH NĂNG AN TOÀN & TRÁNH LỖI HỒI QUY

Khi phát triển tính năng mới hoặc sửa lỗi, AI Agent BẮT BUỘC tuân thủ nghiêm ngặt các nguyên tắc dưới đây để đảm bảo không phá vỡ bất kỳ tính năng nào đang hoạt động ổn định.

---

## 1. Nguyên tắc cốt lõi: Phân tích tác động trước khi viết code (Impact Analysis)

Trước khi chỉnh sửa một file, hàm hoặc component:
*   **Xác định phạm vi ảnh hưởng:** Tìm kiếm xem hàm/component đó đang được gọi ở những nơi nào khác và đang phục vụ những tính năng gì.
    *   *Ví dụ:* Hàm `appendMessageToChat()` không chỉ phục vụ tính năng "Trả lời tin nhắn" mà còn đang phục vụ cả "Tin nhắn văn bản thường", "Tin nhắn Quiz", và sự kiện "WebSocket realtime". Chỉnh sửa hàm này bắt buộc phải kiểm tra tất cả các loại tin nhắn trên.
*   **Không xóa/ghi đè biến dữ liệu cũ:** Khi thêm nhánh xử lý mới (như `if (message.reply_to)`), phải đảm bảo luồng mặc định (`message.body`, tin nhắn thông thường) vẫn được giữ nguyên vẹn 100%.

---

## 2. Đồng bộ 4 tầng (Fullstack Synchronization)

Khi thêm hoặc sửa một trường dữ liệu (ví dụ: `reply_to_id`, `quiz_id`):
1.  **Database Migration:** Tạo cột, đặt kiểu dữ liệu, foreign key và index thích hợp.
2.  **Eloquent Model:** Bắt buộc khai báo trường mới vào `$fillable`, khai báo `$casts` nếu cần, và định nghĩa Relationships tương ứng.
3.  **Controller & Request:** Validate trường dữ liệu đầu vào, xử lý lưu trữ, và `load()` các relationship trước khi trả về JSON hoặc phát Broadcast.
4.  **Frontend View & JavaScript:** Đọc đúng ID từ form/input, gửi đủ trường dữ liệu trong payload của `fetch()`, và xử lý hiển thị ở cả 2 trạng thái: tải lần đầu từ server (Blade) và nhận real-time (JavaScript DOM).

---

## 3. Nguyên tắc an toàn khi can thiệp mã nguồn

*   **Bảo vệ chuỗi và ký tự đặc biệt:**
    *   Khi truyền biến từ Blade sang JavaScript (như tên người dùng, nội dung tin nhắn), phải xử lý khử ký tự xuống dòng (`\r`, `\n`) và escape dấu nháy đơn/kép an toàn để tránh làm vỡ cú pháp JavaScript.
*   **Kiểm tra tính toàn vẹn sau khi sửa file:**
    *   Sau mỗi lần chỉnh sửa file (đặc biệt là các file Blade/JavaScript lớn), luôn đọc lại đoạn mã vừa sửa để đảm bảo không bị sót ký tự rác, không bị cắt cụt thẻ đóng HTML, và không ghi đè mất logic lân cận.
    *   Chạy kiểm tra cú pháp (PHP lint: `php -l`, Node syntax: `node -c`) trước khi bàn giao.

---

## 4. Checklist kiểm thử đa chiều (Regression Checklist)

Trước khi xác nhận hoàn thành một tính năng mới, bắt buộc phải tự kiểm tra chéo:
1.  **Luồng mới có hoạt động không?** (Tính năng vừa thêm chạy đúng mong đợi).
2.  **Luồng cũ có bị ảnh hưởng không?** (Các tính năng trước đó có bị lỗi, mất chữ hay gián đoạn không).
3.  **Trạng thái F5 vs Realtime:** 
    *   Vừa thao tác xong trên giao diện có hiển thị chuẩn không?
    *   F5 lại trang dữ liệu có tải từ Database lên chuẩn không?
    *   Người dùng ở phía bên kia qua WebSocket có nhận được dữ liệu chuẩn không?
