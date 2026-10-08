---
name: frontend-architecture-overhaul
description: >-
  Hướng dẫn và tiêu chuẩn đại tu toàn diện giao diện (Frontend Architecture & UI Overhaul).
  Bao gồm triết lý thiết kế Anti-AI Slop, chuẩn Typography tiếng Việt Be Vietnam Pro,
  gom nhóm hành động tin nhắn kiểu Discord, tái cấu trúc menu bên phải kiểu Zalo,
  quy tắc an toàn chức năng và chuẩn hóa tiếng Việt có dấu.
---

# Tiêu Chuẩn Đại Tu Giao Diện & Kiến Trúc Frontend MessLearn

Tài liệu này là kim chỉ nam bắt buộc cho mọi công việc liên quan đến thiết kế lại giao diện (UI), trải nghiệm người dùng (UX) và phân chia cấu trúc mã nguồn Blade/JS trên MessLearn.

---

## 1. Triết Lý Thiết Kế "Anti-AI Slop" (Thoát Khỏi Giao Diện AI Rẻ Tiền)

Giao diện do AI sinh ra thường mắc các bệnh phổ biến:
- Lạm dụng dải màu gradient tím/hồng/xanh lòe loẹt, viền phát sáng (neon glow).
- Nút nào cũng bo tròn bóng bẩy giống nhau, không có phân cấp chính/phụ (visual hierarchy).
- Nhồi nhét icon dàn trải thành một hàng ngang dài ngoằng thiếu trật tự.
- Typography không phân cấp, dòng chữ quá sát hoặc quá thưa, lỗi hiển thị dấu tiếng Việt.

### Quy chuẩn thiết kế chuyên nghiệp (Human-Crafted Look & Feel):
1. **Trật tự thị giác rõ ràng (Visual Hierarchy):**
   - Chỉ có 1 hành động chính (Primary Action) trên một khung nhìn.
   - Các hành động phụ (Secondary/Tertiary) dùng màu trung tính (Slate), chỉ nổi bật khi di chuột (hover).
   - Thao tác ít dùng được gom vào Menu ngữ cảnh (Context Menu/Dropdown/Popover).
2. **Hệ màu chức năng có ý đồ (Semantic Colors):**
   - Chủ đạo: Sky Blue (`#0EA5E9` / `#0284C7`) cho các điểm nhấn quan trọng và học tập.
   - Nền & Thẻ: Slate (`slate-50` / `slate-100` cho Light Mode; `slate-900` / `slate-800` cho Dark Mode).
   - Trạng thái: Emerald (`#10B981`) cho trực tuyến/thành công, Amber (`#F59E0B`) cho ghim/chú ý, Rose (`#F43F5E`) cho nguy hiểm/gỡ bỏ/cuộc gọi lỡ.
3. **Khoảng thở & Bố cục (Whitespace & Rhythm):**
   - Tuân thủ hệ số 4px của Tailwind: `p-2` (8px), `p-3` (12px), `p-4` (16px), `p-6` (24px).
   - Dùng mảng màu nền chênh lệch nhẹ (1 shade) để phân vùng khu vực thay vì kẻ border dày đặc.

---

## 2. Chuẩn Typography & Khắc Phục Lỗi Font Tiếng Việt

### Vấn đề hiện tại:
- Hệ thống dùng `font-sans` mặc định của Tailwind. Trên Windows, font fallback là `Segoe UI` hoặc system font không đồng bộ kích thước dấu thanh tiếng Việt, dẫn đến các ký tự `ấ, ầ, ể, ễ, ộ, ơ, ư` bị méo mó, lệch cỡ chữ.

### Giải pháp bắt buộc:
1. **Nhúng font Be Vietnam Pro từ Google Fonts:**
   Font `Be Vietnam Pro` được thiết kế riêng tối ưu hoàn hảo cho bộ ký tự và dấu thanh tiếng Việt (dấu mũ, dấu móc, dấu thanh cân đối tuyệt đối):
   ```html
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
   ```
2. **Cấu hình Tailwind:**
   ```javascript
   theme: {
       extend: {
           fontFamily: {
               sans: ['"Be Vietnam Pro"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
           }
       }
   }
   ```
3. **Độ giãn dòng (Line-height) & Kích thước:**
   - Tin nhắn chat: `text-sm leading-relaxed` (`line-height: 1.625`).
   - Nhãn thời gian, chú thích: `text-[11px]` hoặc `text-xs text-slate-400`.

---

## 3. Cải Tiến Action Bar Tin Nhắn (Lấy Cảm Hứng Từ Discord)

### Vấn đề cũ:
- Khi hover vào tin nhắn, hiện một hàng dài 5-6 icon ngang: [Thả cảm xúc] [Trả lời] [Chuyển tiếp] [Ghim] [Gỡ] làm choán chỗ, tràn viền và lộn xộn.

### Thiết kế mới theo phong cách Discord:
- Thanh công cụ nổi (Floating Action Bar) nằm gọn gàng ở góc trên tin nhắn:
  1. **Nút Thả cảm xúc nhanh (Smile Icon):** Nhấn mở picker chọn 6 emoji cảm xúc.
  2. **Nút Trả lời (Reply Icon):** Kích hoạt trích dẫn tin nhắn.
  3. **Nút Thêm (More Icon - 3 chấm `...`):** Nhấn mở Menu ngữ cảnh nhỏ (Dropdown menu):
     - Sao chép nội dung tin nhắn (`copy`)
     - Ghim tin nhắn (`pin`)
     - Chuyển tiếp tin nhắn (`forward`)
     - Phân cách mỏng (`border-t`)
     - Thu hồi / Gỡ tin nhắn (màu đỏ `rose-500` - chỉ hiện với tin nhắn của chính mình hoặc admin)
- **Quy tắc an toàn:**
  - Giữ nguyên toàn bộ các hàm JS gọi trong onclick: `toggleReactionMenu(id)`, `prepareReply(id, name, body)`, `openForwardModal(id)`, `togglePinMessage(id)`, `confirmUnsendMessage(id)`.
  - Cập nhật đồng bộ cả template Blade tĩnh (`message-item.blade.php`) và hàm tạo tin nhắn động trong JS (`chat-core.blade.php` hàm `renderMessageHtml`).

---

## 4. Tái Cấu Trúc Menu Bên Phải (Right Sidebar Kiểu Zalo) & Thu Gọn Header

### 4.1. Thu gọn Header Chat
- Không để 8 icon dàn hàng ngang trên Header.
- Header chỉ giữ lại 4 nút tiện ích chính:
  1. Gọi thoại (Phone)
  2. Gọi video (Video)
  3. Tìm kiếm tin nhắn (Search)
  4. Nút Mở/Đóng Menu thông tin bên phải (Panel Toggle - icon `panel-right` hoặc `info`)
- Toàn bộ các tính năng cài đặt, thông tin, quản trị được đưa sang Menu bên phải.

### 4.2. Cấu trúc Menu bên phải (Conversation Info Drawer - Tương tự Zalo)
Menu bên phải có chiều rộng cố định (`w-80` hoặc `w-96`), có thể bật/tắt (toggle) và cuộn dọc mượt mà gồm 4 phân khu:

1. **Phân khu 1: Thẻ hồ sơ hội thoại (Profile Card):**
   - Avatar lớn (có nút đổi ảnh đại diện nhóm nếu là trưởng nhóm).
   - Tên cuộc trò chuyện / nhóm học tập (có nút bút chì đổi tên nhóm hoặc đổi biệt danh cá nhân).
   - Trạng thái trực tuyến / Số lượng thành viên nhóm.

2. **Phân khu 2: Lối tắt hành động nhanh (Quick Actions):**
   - Nút Bật/Tắt chuông thông báo (Mute/Unmute với popup chọn thời gian 1h, 8h, mãi mãi).
   - Nút Ghim cuộc trò chuyện (Pin chat).
   - Nút Bảng phân công công việc (Task Board).
   - Nút Phòng học trực tuyến (Meeting Lobby).

3. **Phân khu 3: Danh sách & Quản lý thành viên (Với nhóm học tập):**
   - Hiển thị danh sách tóm tắt các thành viên, phân biệt huy hiệu Trưởng nhóm / Phó nhóm.
   - Nút "Thêm thành viên mới" và nút "Xem tất cả thành viên" (mở modal).

4. **Phân khu 4: Kho lưu trữ & Học tập (Media & Study Assets):**
   - Danh sách file tài liệu đã gửi trong phòng chat.
   - Kho ảnh / video đã gửi.
   - Bài kiểm tra Quiz & Mini-game học tập.

5. **Phân khu 5: Cài đặt an toàn & Rời nhóm:**
   - Cài đặt quyền nhóm (Chỉ trưởng nhóm được gửi tin, cấm gọi thoại).
   - Chặn người dùng (với chat 1-1) / Rời nhóm học tập (với chat nhóm).

---

## 5. Quy Tắc Vàng Bảo Vệ Chức Năng (Feature Safety First)

1. **Bảo toàn ID phần tử (DOM IDs):**
   - Tuyệt đối không xóa hoặc đổi tên các ID mà JavaScript đang truy vấn, bao gồm nhưng không giới hạn:
     + `chat-messages-container`, `loading-old-messages`, `empty-messages-placeholder`
     + `btn-scroll-bottom`, `scroll-bottom-badge`
     + `msg-${id}`, `msg-status-${id}`, `btn-pin-${id}`, `pin-badge-${id}`
     + `chat-header-main`, `chat-header-name-text`, `chat-header-original-badge`
     + `btn-header-call-voice`, `btn-header-call-video`, `btn-header-pin-chat`, `btn-header-mute-chat`, `btn-header-nickname`
     + `header-mute-container`, `header-mute-dropdown`
     + Toàn bộ modal IDs: `modal-task-board`, `modal-meeting-lobby`, `modal-group-members`, v.v.
2. **Bảo toàn Data Attributes:**
   - Giữ nguyên `data-conversation-id`, `data-is-pinned`, `data-is-muted`, `data-nickname`, `data-original-name` trên `chat-header-main`.
3. **Bảo toàn hàm gọi sự kiện:**
   - Mọi hàm JS toàn cục (`window.openTaskBoardModal`, `window.startCall`, `window.togglePinMessage`, ...) phải tiếp tục được gắn đúng tham số vào thẻ tương ứng.

---

## 6. Chuẩn Hóa Tiếng Việt Có Dấu 100%

- Tuyệt đối không viết tiếng Việt không dấu trong giao diện (trừ biến mã nguồn).
- Thay thế triệt để các chuỗi cũ:
  + `Nguoi dung` -> `Người dùng`
  + `Thnh vin` / `Thành viên` -> `Thành viên`
  + `Cu?c g?i tho?i` -> `Cuộc gọi thoại`
  + `Micro ang t?t` -> `Micro đang tắt`
  + `Camera ang t?t` -> `Camera đang tắt`
  + `Chat voi ban hoc` -> `Trò chuyện cùng bạn học`
- Văn phong chuẩn mực: Lịch sự, thân thiện, rõ ràng, phù hợp môi trường học tập sinh viên.
