---
name: messlearn-specs
description: >-
  Đặc tả toàn bộ hệ thống MessLearn: ý tưởng nghiệp vụ, công nghệ sử dụng,
  cấu trúc tin nhắn tương tác, từ điển dữ liệu DB (Data Dictionary) và lộ trình phát triển 100% miễn phí.
---

# 📚 ĐẶC TẢ DỰ ÁN MESSLEARN (CHAT & HỌC TẬP TƯƠNG TÁC)

Dự án là nền tảng trò chuyện nhóm kết hợp học tập, làm bài tập và mini-game tương tác thời gian thực dành cho sinh viên/học sinh, tối ưu chi phí (0đ) và kiến trúc gọn nhẹ.

---

## 1. CÔNG NGHỆ SỬ DỤNG (100% MIỄN PHÍ & TỐI ƯU CHO LOCAL DEMO)

*   **Backend:** **PHP 8.4** với framework **Laravel 11**.
*   **Database:** **MySQL** (quản lý qua Laravel Migrations).
*   **Giao diện (Frontend):** **Blade Template** kết hợp CSS tuân thủ bộ quy tắc trong `ui-guidelines` (Sky Blue `#0EA5E9`, bo góc mềm mại, tối giản, hỗ trợ Dark/Light mode).
*   **Tương tác Realtime (Thời gian thực):** **Laravel Reverb** (WebSocket miễn phí chính chủ của Laravel 11).
*   **Xử lý đồ họa & Đa phương tiện:**
    *   Vẽ lên ảnh: HTML5 `<canvas>`.
    *   Thu âm tin nhắn thoại: JavaScript `MediaRecorder API`.
*   **Triển khai Demo qua mạng miễn phí:** **Ngrok** hoặc **Cloudflare Tunnels**.

---

## 2. TỪ ĐIỂN DỮ LIỆU CƠ SỞ DỮ LIỆU (DATABASE DATA DICTIONARY)

Hệ thống được chuẩn hóa dữ liệu chặt chẽ và chia thành các nhóm bảng sau:

### 2.1. Nhóm Người Dùng & Bạn Bè
*   **`users`**: Tài khoản người dùng (`id`, `name`, `email`, `password`, `avatar`, `status`).
*   **`friendships`**: Mối quan hệ bạn bè 2 chiều:
    *   `user_id`: ID người gửi lời mời.
    *   `friend_id`: ID người nhận lời mời.
    *   `status`: Trạng thái (`pending`, `accepted`, `blocked`).
    *   *Ràng buộc:* Unique `(user_id, friend_id)`.

### 2.2. Nhóm Phòng Chat & Thành Viên
*   **`conversations`**: Cuộc trò chuyện / phòng chat:
    *   `type`: `direct` (chat đôi 1-1) hoặc `group` (chat nhóm).
    *   `title`: Tên nhóm chat (null nếu là chat 1-1).
    *   `avatar`: Ảnh đại diện nhóm.
*   **`conversation_participants`**: Thành viên trong phòng:
    *   `conversation_id`, `user_id`: Cặp khóa xác định ai ở phòng nào.
    *   `role`: `admin` (trưởng nhóm) hoặc `member` (thành viên).
    *   `last_read_at`: Đánh dấu thời điểm đọc tin gần nhất để tính tin nhắn chưa đọc.

### 2.3. Nhóm Đề Thi & Bài Tập Quiz (Đã Tinh Giản)
*   **`quizzes`**: Đầu đề bài tập / câu hỏi:
    *   `id`: Mã bài quiz.
    *   `title`: Tiêu đề bài tập.
    *   `description`: Hướng dẫn hoặc mô tả.
    *   *(Đã bỏ `created_by` theo quyết định thiết kế: người gửi tin nhắn chính là người ra đề, chuyển tiếp thì copy bản ghi).*
*   **`quiz_questions`**: Từng câu hỏi trong đề:
    *   `quiz_id`: Thuộc bài quiz nào.
    *   `question_text`: Nội dung câu hỏi.
    *   `type`: `single_choice` (trắc nghiệm 1 đáp án), `multiple_choice` (nhiều đáp án), `short_answer` (tự luận ngắn).
    *   `points`: Điểm của câu (mặc định 1).
    *   `correct_text_answer`: Đáp án mẫu cho câu tự luận ngắn để máy tự động đối soát chấm điểm.
    *   `order`: Thứ tự hiển thị câu hỏi.
*   **`quiz_options`**: Lựa chọn A, B, C, D cho câu trắc nghiệm:
    *   `quiz_question_id`: Thuộc câu hỏi nào.
    *   `option_text`: Nội dung lựa chọn.
    *   `is_correct`: `true` nếu là đáp án đúng, `false` nếu sai.

### 2.4. Nhóm Tin Nhắn Tương Tác (Interactive Messages)
*   **`messages`**: Trung tâm điều phối mọi hoạt động trong phòng chat:
    *   `conversation_id`: Thuộc phòng nào.
    *   `user_id`: Người gửi.
    *   `type`: Loại tin (`text`, `image`, `audio`, `quiz`, `game_dice`, `game_rps`, `event`).
    *   `body`: Nội dung chữ hoặc caption.
    *   `file_path`: Đường dẫn ảnh hoặc file voice nếu có.
    *   `quiz_id`: Khóa ngoại trỏ đến `quizzes` (nếu `type = quiz`, ngược lại là `null`).
    *   `reply_to_id`: ID tin nhắn cũ được trích dẫn/trả lời.
    *   `is_pinned`: Đánh dấu có ghim tin nhắn lên đầu nhóm hay không.
    *   `metadata`: Dữ liệu JSON linh hoạt (ví dụ `{ "dice": 5 }`, cấu hình sự kiện...).
*   **`message_reactions`**: Thả cảm xúc:
    *   `message_id`, `user_id`, `reaction` (`like`, `heart`, `laugh`, `wow`, `sad`, `angry`).

### 2.5. Nhóm Kết Quả & Chấm Điểm Tự Động
*   **`quiz_submissions`**: Lượt nộp bài của thành viên:
    *   `quiz_id`: Thuộc bài quiz nào.
    *   `user_id`: Ai làm bài.
    *   `total_score`: Tổng điểm đạt được.
    *   `completed_at`: Thời gian hoàn thành.
*   **`quiz_answers`**: Chi tiết từng câu trả lời của thí sinh:
    *   `quiz_submission_id`: Thuộc lượt nộp bài nào.
    *   `quiz_question_id`: Thuộc câu hỏi nào.
    *   `selected_option_id`: Đáp án trắc nghiệm đã chọn.
    *   `text_answer`: Đoạn chữ người làm tự gõ (với tự luận).
    *   `is_correct`: Máy tự động chấm đúng/sai (`true/false`).
    *   `points_earned`: Điểm đạt được của câu.

---

## 3. LỘ TRÌNH THỰC HIỆN (ROADMAP)

### Giai đoạn 1: MVP Cốt Lõi (Phải hoàn thành trước)
*   Chạy Migrations tạo toàn bộ bảng trên MySQL.
*   Xác thực: Đăng ký, đăng nhập.
*   Danh sách bạn bè & phòng chat 1-1 / nhóm.
*   Gửi tin nhắn text, ảnh, reply, thả cảm xúc, ghim tin nhắn.
*   **Quiz Engine:** Soạn quiz gửi vào chat + Tự động chấm điểm.
*   Mini-game xúc xắc / kéo búa bao đơn giản.

### Giai đoạn 2: Trải Nghiệm Nâng Cao
*   Vẽ chú thích lên ảnh (Canvas).
*   Ghi âm gửi voice note.
*   Kho lưu trữ bài tập nhóm & bảng xếp hạng tuần.
*   Sự kiện lịch hẹn nhóm.

### Giai đoạn 3: Tối Ưu & Mở Rộng
*   Cuộc gọi thoại / Video Call 1-1 (WebRTC).
