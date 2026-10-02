---
name: messlearn-specs
description: >-
  Đặc tả toàn bộ hệ thống MessLearn: ý tưởng nghiệp vụ, công nghệ sử dụng,
  cấu trúc tin nhắn tương tác, cơ chế chấm điểm và lộ trình phát triển 100% miễn phí.
---

# 📚 ĐẶC TẢ DỰ ÁN MESSLEARN (CHAT & HỌC TẬP TƯƠNG TÁC)

Dự án là nền tảng trò chuyện nhóm kết hợp học tập, làm bài tập và mini-game tương tác thời gian thực dành cho sinh viên/học sinh, tối ưu chi phí (0đ) và kiến trúc gọn nhẹ.

---

## 1. CÔNG NGHỆ SỬ DỤNG (100% MIỄN PHÍ & TỐI ƯU CHO LOCAL DEMO)

*   **Backend:** **PHP 8.4** với framework **Laravel 11**.
    *   *Lý do:* Cực kỳ nhanh, hệ sinh thái phong phú, dễ cấu hình và tài liệu tiếng Việt dồi dào.
*   **Database:** **MySQL** (hoặc MariaDB / SQLite cho bản test siêu nhẹ).
*   **Giao diện (Frontend):** **Blade Template** kết hợp **Tailwind CSS / CSS thuần** tuân thủ bộ quy tắc trong `ui-guidelines` (Sky Blue `#0EA5E9`, bo góc mềm mại, tối giản, hỗ trợ Dark/Light mode).
*   **Tương tác Realtime (Thời gian thực):** **Laravel Reverb** (WebSocket miễn phí chính chủ của Laravel 11, không cần trả phí cho Pusher).
*   **Xử lý đồ họa & Đa phương tiện:**
    *   Vẽ lên ảnh: HTML5 `<canvas>` (chạy trực tiếp trên trình duyệt, không tốn tài nguyên server).
    *   Thu âm tin nhắn thoại: JavaScript `MediaRecorder API`.
*   **Triển khai Demo qua mạng miễn phí:** **Ngrok** hoặc **Cloudflare Tunnels** (kết hợp cấu hình `URL::forceScheme('https')` và sửa `APP_URL`).

---

## 2. Ý TƯỞNG CỐT LÕI: KIẾN TRÚC TIN NHẮN TƯƠNG TÁC (INTERACTIVE MESSAGES)

Tất cả các tính năng tương tác được quy về cùng một bản chất: **Một bản ghi trong bảng `messages` với trường `type` khác nhau**:

1.  `type = 'text'`: Tin nhắn văn bản thông thường.
2.  `type = 'image'`: Tin nhắn hình ảnh (hỗ trợ mở canvas để vẽ/ghi chú rồi gửi lại).
3.  `type = 'audio'`: Tin nhắn thoại ngắn (voice note).
4.  `type = 'quiz'`: **[TÍNH NĂNG ĐINH]** Thẻ bài tập / trắc nghiệm hoặc câu hỏi tự luận ngắn:
    *   Người nhận bấm chọn phương án hoặc nhập câu trả lời trực tiếp trong tin nhắn.
    *   Backend tự động đối soát đáp án:
        *   Trắc nghiệm: so khớp ID đáp án đúng.
        *   Tự luận ngắn: chuẩn hóa chuỗi (`trim()`, `strtolower()`) hoặc dùng regex để chấm điểm chính xác không phân biệt hoa thường.
    *   Hiển thị kết quả điểm số và trạng thái "Đã nộp bài" ngay tại thẻ tin nhắn.
5.  `type = 'game_dice'`: Tung xúc xắc ngẫu nhiên (`rand(1, 6)`).
6.  `type = 'game_rps'`: Kéo - búa - bao thách đấu.
7.  `type = 'event'`: Thẻ nhắc hẹn / lịch họp nhóm (kèm nút "Tham gia" / "Từ chối").

---

## 3. CÁC TÍNH NĂNG MỞ RỘNG ĐỀ XUẤT (TĂNG TÍNH HỌC TẬP & TƯƠNG TÁC)

1.  **Flashcard đố vui chớp nhoáng (Flashcard Challenge):**
    *   Thành viên gửi 1 thẻ từ vựng / khái niệm (ví dụ: Mặt trước là câu hỏi, bấm lật mặt sau để xem đáp án hoặc ai gõ đáp án đúng nhanh nhất sẽ được tick điểm).
2.  **Bảng xếp hạng tuần trong nhóm (Group Leaderboard):**
    *   Tự động tính điểm dựa trên số lượng quiz làm đúng trong tuần/tháng để kích thích tinh thần học tập.
3.  **Khối chia sẻ mã nguồn (Code Snippet Block):**
    *   Cho phép gửi đoạn code có đánh số dòng và tô màu cú pháp (Syntax Highlighting) kèm nút "Copy 1 chạm", cực kỳ hữu ích cho sinh viên IT.
4.  **Kho lưu trữ bài tập nhóm (Group Quiz Vault):**
    *   Các bài tập từng gửi trong đoạn chat được tự động gom vào tab "Kho bài tập" để sau này ôn thi không phải cuộn tìm lại tin nhắn cũ.
5.  **Bộ đếm Pomodoro học nhóm (Group Focus Timer):**
    *   Một người bấm bắt đầu "25 phút tập trung làm bài", khung chat chuyển sang chế độ im lặng kèm đồng hồ đếm ngược cho cả nhóm.

---

## 4. LỘ TRÌNH THỰC HIỆN (ROADMAP)

### Giai đoạn 1: MVP Cốt Lõi (Phải hoàn thành trước)
*   Xác thực người dùng: Đăng ký, đăng nhập, hồ sơ cá nhân.
*   Chat cơ bản: Danh sách bạn bè, phòng chat 1-1 và chat nhóm.
*   Nhắn tin text, gửi ảnh, reply tin nhắn, thả cảm xúc (reactions), ghim tin nhắn.
*   **Quiz Engine:** Tạo câu hỏi trắc nghiệm/tự luận ngắn gửi vào chat + Tự động chấm điểm.
*   Mini-game xúc xắc / kéo búa bao đơn giản.

### Giai đoạn 2: Trải Nghiệm Nâng Cao (Hoàn thiện đồ án)
*   Công cụ vẽ chú thích lên ảnh (Canvas).
*   Ghi âm gửi tin nhắn thoại (Voice note).
*   Sự kiện lịch hẹn / Lời nhắc họp nhóm.
*   Kho lưu trữ bài tập và bảng xếp hạng nhóm.

### Giai đoạn 3: Tối Ưu & Mở Rộng (Làm sau cùng nếu còn thời gian)
*   Cuộc gọi thoại / Video Call 1-1 (WebRTC).

---

## 5. NGUYÊN TẮC THIẾT KẾ & CODE DÀNH CHO AI AGENT KHI LÀM VIỆC VỚI DỰ ÁN NÀY
*   **Tuân thủ UI Skill:** Luôn áp dụng các quy chuẩn màu sắc (Sky Blue `#0EA5E9`), bo góc (border-radius), chế độ sáng/tối từ file `d:\KimDanh\.agent\skills\ui-guidelines\SKILL.md`.
*   **Giải thích cho sinh viên:** Không âm thầm sửa code mà luôn chỉ rõ nguyên nhân lỗi, cơ chế hoạt động bằng ví dụ trực quan.
*   **Giữ code đơn giản:** Ưu tiên code rõ ràng, dễ bảo trì, chia nhỏ controller và model, không over-engineering.
