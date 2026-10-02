---
name: ui-guidelines
description: >-
  Hướng dẫn và quy tắc thiết kế giao diện (UI/UX) cho dự án.
  Bao gồm quy chuẩn về màu sắc (Sky Blue chủ đạo), bo góc, tối giản, và chế độ Sáng/Tối.
---

# 🎨 Hướng Dẫn Thiết Kế Giao Diện (UI/UX) Dự Án

Skill này chứa các quy tắc thiết kế nhằm tạo ra một giao diện hiện đại, thân thiện, và "thoát khỏi tư duy thiết kế AI chung chung" (như quá lạm dụng shadow, gradient không tự nhiên, hoặc nhồi nhét quá nhiều thành phần).

## 1. Triết Lý Thiết Kế (Design Philosophy)

- **Tối Giản & Tốc Độ (Minimalist & Lightweight):** Không nhồi nhét UI. Giữ khoảng trắng (whitespace) rộng rãi để giao diện "thở". Code giao diện phải nhẹ, dùng CSS thuần hoặc framework nhẹ, tránh lạm dụng thư viện nặng nề.
- **Thoát Khỏi "AI Look":** Thiết kế AI thường mắc lỗi lạm dụng border thô cứng và đổ bóng quá đà. Hãy dùng **mảng màu nền (background colors)** và khoảng cách (padding/margin) để phân chia bố cục thay vì dùng quá nhiều đường viền (border).
- **Mềm Mại Nhưng Có Chủ Đích (Intentional Softness):** Xu hướng hiện nay là bo góc (border-radius), nhưng không phải bo mù quáng.

## 2. Quy Tắc Bo Góc (Border Radius)

- **Các thành phần lớn (Card, Modal, Dialog):** Bo góc lớn để tạo cảm giác thân thiện (`border-radius: 16px` đến `24px`).
- **Nút bấm (Buttons) & Input:** Bo góc vừa phải (`8px` đến `12px`) hoặc bo tròn hoàn toàn (`9999px`) với các nút dạng pill-shape (hình viên thuốc).
- **Tuyệt đối không bo:** Các hình ảnh vuông vức cần hiển thị tràn viền, hoặc các menu dropdown thả xuống dính sát vào thanh điều hướng.

## 3. Hệ Thống Màu Sắc (Sky Blue Chủ Đạo)

Màu Xanh Lam (Sky Blue) được chọn làm chủ đạo vì mang lại cảm giác tin cậy, nhẹ nhàng và sạch sẽ.

### ☀️ Chế độ Sáng (Light Mode)
- **Primary (Chủ đạo):** `#0EA5E9` (Sky 600 - Xanh lam sáng, tươi tắn).
- **Primary Hover (Khi di chuột):** `#0284C7` (Sky 700 - Đậm hơn một chút).
- **Background (Nền trang):** `#F8FAFC` (Slate 50 - Trắng ngà hơi xanh, giúp dịu mắt hơn trắng tinh `#FFFFFF`).
- **Surface (Nền các Card/Bảng):** `#FFFFFF` (Trắng tinh để nổi bật trên nền trang).
- **Text (Chữ chính):** `#0F172A` (Slate 900 - Gần đen).
- **Text Muted (Chữ phụ):** `#64748B` (Slate 500 - Xám dịu).
- **Borders (Viền):** `#E2E8F0` (Slate 200).

### 🌙 Chế độ Tối (Dark Mode)
- **Primary (Chủ đạo):** `#38BDF8` (Sky 400 - Xanh lam rực sáng hơn để nổi bật trên nền đen).
- **Primary Hover (Khi di chuột):** `#0EA5E9` (Sky 600).
- **Background (Nền trang):** `#0F172A` (Slate 900 - Đen sâu, hơi ngả xanh).
- **Surface (Nền các Card/Bảng):** `#1E293B` (Slate 800 - Sáng hơn nền trang 1 bậc).
- **Text (Chữ chính):** `#F8FAFC` (Slate 50 - Trắng ngà).
- **Text Muted (Chữ phụ):** `#94A3B8` (Slate 400).
- **Borders (Viền):** `#334155` (Slate 700).

## 4. Quy Tắc Nút Bấm (Buttons)

- **Primary Button (Nút hành động chính):** Nền màu Primary, chữ màu Trắng. Dùng cho hành động quan trọng nhất (ví dụ: "Nộp bài", "Bắt đầu"). Không được có nhiều hơn 1 Primary Button cạnh nhau.
- **Secondary Button (Nút phụ):** Nền trong suốt hoặc xám nhạt (`#F1F5F9`), chữ màu xám đậm (`#475569`). Dùng cho "Hủy bỏ", "Quay lại".
- **Ghost/Outline Button:** Chỉ có viền hoặc không viền, đổi màu nền nhẹ khi hover.
- **Hiệu ứng Hover/Active:** Luôn phải có thay đổi độ sáng hoặc bóng đổ khi hover để báo cho người dùng biết nút này có thể bấm được. Cần có hiệu ứng scale nhỏ lại (ví dụ `transform: scale(0.98)`) khi click (active).

## 5. Bóng Đổ (Shadows) & Ánh Sáng

- Tránh dùng bóng đổ đen đặc (VD: `rgba(0,0,0, 0.5)`).
- **Chế độ Sáng:** Dùng bóng đổ thật mờ, lan tỏa rộng (Soft Drop Shadow) với màu xanh xám. Ví dụ: `box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);`
- **Chế độ Tối:** Thay vì dùng bóng đổ (vì bóng đen không hiện rõ trên nền đen), hãy dùng **màu nền sáng hơn** (Surface color) kết hợp với đường viền mỏng 1px cực mờ (`border: 1px solid rgba(255,255,255,0.1)`) để làm nổi bật các thành phần (Cards, Modals).

## 6. Kiểu Chữ (Typography)
- Tránh dùng các font chữ có chân (Serif). Dùng các font Sans-serif sạch sẽ, bo tròn nhẹ như **Inter**, **Nunito**, hoặc **Plus Jakarta Sans**.
- Font chữ lớn (Headings) cần đậm và rõ ràng. Dòng văn bản (Body text) cần có `line-height` thoải mái (khoảng `1.6` đến `1.75`) để dễ đọc.

---
**Ghi chú cho AI khi áp dụng skill này:**
Khi sinh code UI (HTML/CSS, Tailwind, Vue, React...), BẮT BUỘC phải đọc và áp dụng các mã màu, quy tắc bo góc và triết lý tối giản ở trên. Không được tự ý nhồi nhét code hoặc sinh ra một giao diện phức tạp nếu không có chỉ định từ người dùng. Mọi file CSS sinh ra phải ngắn gọn, tối ưu.
