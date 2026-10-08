# HƯỚNG DẪN DỰ ÁN MESSLEARN (DÀNH CHO TẤT CẢ CÁC PHIÊN BẢN AI AGENT)

Dự án này là **MessLearn** - Nền tảng Chat kết hợp Học tập & Làm bài tập tương tác thời gian thực cho sinh viên/học sinh, phát triển bằng Laravel 11 + PHP 8.4 + MySQL.

## Các tài liệu quy chuẩn quan trọng bắt buộc đọc:
1. **Quy chuẩn Đại tu Giao diện & Kiến trúc Frontend:** Xem tại `.agent/skills/frontend-architecture-overhaul/SKILL.md` (Triết lý Anti-AI Slop, font Be Vietnam Pro, Action bar Discord, Right Sidebar Zalo, bảo toàn ID/function, tiếng Việt chuẩn có dấu).
2. **Quy chuẩn UI/UX cơ bản:** Xem tại `.agent/skills/ui-guidelines/SKILL.md` (Màu Sky Blue `#0EA5E9`, bo góc mềm mại, tối giản, Light/Dark mode).
3. **Đặc tả nghiệp vụ & Tính năng:** Xem tại `.agent/skills/messlearn-specs/SKILL.md`.
4. **Tiêu chuẩn viết code sạch (Bắt buộc tuân thủ):** Xem tại `.agent/skills/clean-code-standards/SKILL.md`.
5. **Quy tắc an toàn tính năng:** Xem tại `.agent/skills/feature-development-safety/SKILL.md`.

## Nguyên tắc cốt lõi khi sinh code và tương tác:
- Luôn trả lời bằng **tiếng Việt**.
- Người dùng là sinh viên, giải thích cặn kẽ logic, dùng ví dụ trực quan dễ hiểu.
- **Phong cách sinh code:** 
  + Hạn chế tối đa comment thừa (chỉ dùng comment để đánh dấu các khối lớn `// --- Section ---`).
  + TUYỆT ĐỐI KHÔNG chèn emoji vào comment hay trong file mã nguồn.
  + Viết code ngắn gọn, tự giải thích (self-documenting), không bloat code.
  + Bảo toàn 100% các ID, data attributes và hàm JS đang hoạt động khi refactor UI.
