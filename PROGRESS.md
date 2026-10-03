# MessLearn - Tiến trình phát triển

## Hiện tại (Ngày 3 Tháng 10, 2026)

- Đã refactor giao diện theo hướng dùng Tailwind, chuẩn thiết kế Sky Blue tối giản.
- Đã cài đặt xác thực người dùng (Auth) cơ bản.
- Đã thiết lập cấu trúc cho giao diện ChatBoard (3 cột) và Modal tìm bạn, tạo nhóm.
- Đã thiết lập backend cơ bản cho `FriendshipController` và `ConversationController`.
- Người dùng đã cho phép **code trực tiếp** vào hệ thống với tốc độ nhanh.

## Tiếp theo cần làm:

1. **Cài đặt Toastify-js:**
   - [x] Thay thế các đoạn code hiển thị thông báo `session('success')` và `session('error')` thủ công bằng thư viện Toastify.
2. **Logic cho bạn bè & phòng chat:**
   - [x] Hoàn thiện xử lý tìm kiếm bạn bè (AJAX), gửi/nhận lời mời.
   - [x] Hoàn thiện logic tạo nhóm chat và hiển thị danh sách conversation trong cột bên trái.
   - [x] Chức năng chọn conversation và hiện thị chi tiết trong cột giữa.
3. **Chat thời gian thực (Laravel Reverb):**
   - [x] Cài đặt backend phát sự kiện (broadcast).
   - [x] Tích hợp Echo vào frontend.
4. **Quiz Engine:**
   - [x] Bổ sung giao diện Không gian học tập (Cột phải).
   - [x] Xây dựng Modal tạo bài kiểm tra với Javascript động (Thêm/bớt câu hỏi).
   - [x] Backend API lưu trữ dữ liệu Quiz, Câu hỏi, Lựa chọn.
   - [x] Render Quiz Card bên trong giao diện Chat.
   - [ ] Giao diện Modal khi nhấn "Bắt đầu làm bài".
   - [ ] Chấm điểm tự động và thông báo kết quả.

## Ghi chú thiết kế và lập trình:

- **Style:** Code trực tiếp, tối giản, không viết comment giải thích vào trong code. Dùng biến tiếng Anh dễ hiểu.
- **UI:** KHÔNG dùng icon dạng ký tự (emoji). Các chỗ cần icon thì sử dụng thư viện **Lucide Icons** (lucide.dev). Giữ phong cách thiết kế tối giản, Tailwind thuần túy. Hạn chế "code bung bét", ưu tiên thư viện nếu cần (ví dụ toast).
