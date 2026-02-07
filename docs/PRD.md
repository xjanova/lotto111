# Product Requirements Document (PRD)
# ระบบหวยออนไลน์ - Lotto Platform

## 1. Project Overview

### 1.1 ชื่อโปรเจค
**Lotto Platform** - ระบบหวยออนไลน์ครบวงจร

### 1.2 วัตถุประสงค์
พัฒนาระบบหวยออนไลน์ใหม่ทั้งระบบด้วย Laravel เวอร์ชันล่าสุด ให้มีความสวยงาม ล้ำสมัย ใช้งานง่าย
โดยอ้างอิงฟีเจอร์จากเว็บต้นแบบ (หวยมีดี889)

### 1.3 Tech Stack
- **Backend:** Laravel 12.x (PHP 8.3+)
- **Frontend:** Blade + Livewire 3 + Alpine.js + Tailwind CSS 4
- **Database:** MySQL 8.0+ / MariaDB 10.6+
- **Cache/Queue:** Redis
- **Real-time:** Laravel Reverb (WebSocket)
- **API:** RESTful JSON API (Laravel Sanctum)
- **SMS OTP:** ThaiSMS / ThaiBulkSMS API
- **Payment:** Bank Transfer Auto-detect / TrueWallet / PromptPay QR

---

## 2. User Roles

| Role | Description |
|------|-------------|
| **Guest** | ผู้เข้าชมที่ยังไม่ได้สมัคร สามารถดูผลรางวัลได้ |
| **Member** | สมาชิกที่สมัครแล้ว สามารถแทงหวย ฝาก-ถอนเงิน ดูโพย |
| **Agent** | ตัวแทน/แนะนำเพื่อน ได้ค่าคอมมิชชั่น |
| **Admin** | ผู้ดูแลระบบ จัดการทุกอย่าง |
| **Super Admin** | ผู้ดูแลระดับสูงสุด ตั้งค่าระบบ จัดการ Admin |

---

## 3. Features Breakdown

### 3.1 ระบบสมัครสมาชิก & เข้าสู่ระบบ (Authentication)

#### 3.1.1 สมัครสมาชิก (Register)
- สมัครด้วย **เบอร์โทรศัพท์**
- ส่ง **OTP** ผ่าน SMS เพื่อยืนยันเบอร์
- กรอกข้อมูล: ชื่อ-นามสกุล, รหัสผ่าน, ธนาคาร, เลขบัญชี
- รองรับ Referral Code (ลิงก์แนะนำเพื่อน)
- ยอมรับเงื่อนไขการใช้บริการ

#### 3.1.2 เข้าสู่ระบบ (Login)
- Login ด้วยเบอร์โทร + รหัสผ่าน
- Remember me
- Forgot password (ส่ง OTP ใหม่)
- Session management

#### 3.1.3 ระบบ OTP
- ส่ง SMS OTP 6 หลัก
- หมดอายุใน 5 นาที
- จำกัด request OTP (rate limiting)
- Resend OTP (cooldown 60 วินาที)

---

### 3.2 Dashboard หน้าหลัก (Member)

- แสดงยอดเงินคงเหลือ
- สถานะบัญชี (ปกติ/ระงับ)
- เบอร์โทรศัพท์ + ชื่อผู้ใช้
- ปุ่มฝากเงิน / ถอนเงิน (เด่นชัด)
- Quick Menu Grid:
  - แทงหวย
  - เกมส์ กีฬา คาสิโน
  - หวยยี่กี
  - หวยชุด
  - ผลรางวัล
  - โพยหวย
  - สร้างเลขชุด
  - แนะนำเพื่อน
  - ติดต่อแอดมิน
  - รายการฝาก-ถอน
  - รายงานการเงิน
- Marquee text (ข้อความประกาศวิ่ง)
- Chat bubble (ติดต่อแอดมิน)

---

### 3.3 ระบบแทงหวย (Lottery Betting)

#### 3.3.1 ประเภทหวย
| หมวด | รายการ |
|------|--------|
| **หวยรัฐบาล** | หวยรัฐบาลไทย (งวดละ 2 ครั้ง/เดือน) |
| **หวยยี่กี** | จับยี่กี 24 ชม. (144 รอบ/วัน) |
| **หวย ธกส.** | หวยธกส. |
| **หวยออมสิน** | หวยออมสิน |
| **หวยต่างประเทศ** | ลาว, ฮานอย, จีน, ฮั่งเส็ง, นิเคอิ, มาเลย์, เกาหลี, ไต้หวัน, สิงคโปร์, อังกฤษ, เยอรมัน, รัสเซีย, ดาวโจนส์, ฮ่องกง ฯลฯ |

#### 3.3.2 ประเภทการแทง (Bet Types)
| ประเภท | อัตราจ่าย (ตัวอย่าง) |
|--------|---------------------|
| 3 ตัวบน | 1,000 |
| 3 ตัวโต๊ด | 150 |
| 3 ตัวล่าง | - |
| 2 ตัวบน | 100 |
| 2 ตัวล่าง | 100 |
| 2 ตัวโต๊ด | 13 |
| วิ่งบน | 3.2 |
| วิ่งล่าง | 4.2 |
| เลขปีก | - |
| 4-5 ตัว | - |
| 4 ตัวบน | 4,000 |
| 4 ตัวโต๊ด | 25 |
| 5 ตัวโต๊ด | 15 |
| ปักหลักหน่วย | 8 |
| ปักหลักสิบ | 8 |
| ปักหลักร้อย | 8 |

#### 3.3.3 หน้าแทงหวย (Betting Interface)
- แสดงชื่อหวย + เวลาปิดรับ + countdown
- Tab เลือกประเภท (3ตัวบน, 2ตัวบน, ฯลฯ)
- ช่องกรอกเลข (numpad สำหรับมือถือ)
- ช่องใส่จำนวนเงิน + ปุ่ม quick amount (5, 10, 20, 50, 100, 500)
- ตะกร้าแทง (รายการที่เลือก) — แก้ไข/ลบได้
- แสดง ยอดเครดิตคงเหลือ / รวมยอดแทง
- ปุ่ม ยกเลิกทั้งหมด / ส่งโพย
- ปุ่ม "เลือกเลขชุด" — เลือกจาก set ที่บันทึกไว้
- ปุ่ม "ดึงโพยเก่า" — ดึงโพยจากงวดก่อน
- ปุ่ม "เลือกจากแผง" — เลือกเลขจาก grid
- ปุ่ม "กติกา & วิธีเล่น"
- ตารางอัตราจ่าย (แสดงด้านขวา)

#### 3.3.4 หวยยี่กี (Yeekee)
- 144 รอบต่อวัน (ทุก 10 นาที, 24 ชม.)
- แสดง countdown แต่ละรอบ
- แสดงสถานะ: เปิด/ปิดรับ/ออกผลแล้ว
- แทงได้เหมือนหวยปกติ (2ตัว, 3ตัว ฯลฯ)

#### 3.3.5 หวยชุด (Set Lottery)
- แทงหวยแบบเลขชุด
- ประเภท: หวยมาเลย์(ชุด), หวยฮานอย(ชุด), หวยลาว(ลาวพัฒนา)(ชุด), หวยรัฐบาล(ชุด)
- มี countdown + เวลาปิดรับ

---

### 3.4 ระบบผลรางวัล (Results)

- เลือกวันที่ดูผล (date picker)
- แสดงผลหวยรัฐบาล (รางวัลที่ 1 + 3ตัวบน, 2ตัวล่าง, 3ตัวล่าง)
- แสดงผลจับยี่กี (รอบที่/3ตัวบน/2ตัวล่าง)
- แสดงผลหวยต่างประเทศ (grid แสดงทุกหวย)
  - แต่ละหวย: ชื่อ + 3ตัวบน + 2ตัวล่าง
- แบ่งตามหมวด: รัฐบาล / ยี่กี / ต่างประเทศ
- สถานะ: รอผล / ออกผลแล้ว

---

### 3.5 ระบบโพยหวย (Ticket/Slip)

- สรุปยอดแทงวันนี้ (ยอดรวม + จำนวนโพยที่รอผล)
- Tab filter: โพยทั้งหมด / โพยที่รอผล / ผลออกแล้ว
- รายการโพย:
  - หมายเลขโพย
  - ชื่อหวย + งวด
  - เวลาที่แทง
  - จำนวนเงินรวม
  - สถานะ (รอผล/ถูก/ไม่ถูก)
  - รายละเอียด (เลขที่แทง, ประเภท, จำนวนเงิน)
- สามารถดึงโพยเก่ากลับมาแทงใหม่

---

### 3.6 ระบบจัดการเลขชุด (Number Set)

- สร้างเลขชุดใหม่ (ตั้งชื่อ + เพิ่มเลข)
- แก้ไข/ลบเลขชุด
- เลือกเลขชุดเข้าไปแทงได้เลยจากหน้าแทงหวย
- เก็บประวัติเลขชุดที่สร้างไว้

---

### 3.7 ระบบการเงิน (Finance)

#### 3.7.1 ฝากเงิน (Deposit)
- โอนเงินเข้าบัญชีแล้วแจ้งโอน
- ฝากอัตโนมัติ (Auto Deposit) — detect จาก bank statement
- สลิปยืนยัน
- PromptPay QR Code
- ขั้นต่ำฝาก

#### 3.7.2 ถอนเงิน (Withdraw)
- ถอนเงินเข้าบัญชีธนาคารที่ผูกไว้
- ขั้นต่ำถอน
- รอ Admin อนุมัติ / อัตโนมัติ
- แจ้งผลผ่าน notification

#### 3.7.3 เติมเงิน (Top Up SMS)
- เติมเงินผ่าน SMS
- แสดงข้อความแนะนำวิธีเติม

#### 3.7.4 รายการฝาก-ถอน (Transactions)
- Tab filter: ทั้งหมด / ฝาก / ถอน
- แสดงรายการ: วันที่, ประเภท, จำนวนเงิน, สถานะ

#### 3.7.5 รายงานการเงิน (Financial Report)
- สรุปรายการเงินเข้า-ออก
- กรองตามช่วงวันที่

---

### 3.8 ระบบแนะนำเพื่อน / Affiliate

- สร้างลิงก์แนะนำ (Referral Link)
- แบนเนอร์ (Promotion Banner)
- Dashboard แสดง:
  - ส่วนแบ่งรายได้ (%)
  - จำนวนสมาชิกแนะนำ (คน)
  - รายได้ทั้งหมด (บาท)
  - รายได้คงเหลือ (บาท)
- ปุ่มถอนคอมมิชชั่น
- Tab: ภาพรวม / สมาชิก / รายได้ / ก๊อปลิ้ง
- ตารางรายวัน: วันที่ / ยอดแทงสมาชิก / คอมมิชชั่น

---

### 3.9 ระบบติดต่อแอดมิน (Inbox/Chat)

- Chat Service (real-time messaging)
- ข้อความจาก Admin
- แจ้งปัญหา / สอบถาม
- แสดง LINE ID ของแอดมิน
- ปุ่ม "สอบถาม/แจ้งปัญหา"

---

### 3.10 เกมส์ กีฬา คาสิโน (Games)

- Games Slot
- Live Casino
- Sportbook
- Poker
- เชื่อมต่อกับ provider ภายนอก (3rd party API)

---

### 3.11 ระบบผู้ใช้ (User Profile)

- สรุปข้อมูลของคุณ (ชื่อ, เบอร์, ธนาคาร, เลขบัญชี)
- เปลี่ยนรหัสผ่าน
- ออกจากระบบ (Logout)

---

### 3.12 Navbar & Navigation

- Logo
- หน้าหลัก
- ผลรางวัล
- เติมเงิน
- ยอดเงิน (แสดงตัวเลข)
- ติดต่อเรา
- ผู้ใช้ (dropdown: ข้อมูล / เปลี่ยน password / logout)
- Marquee ข้อความประกาศ

---

## 4. Admin Panel Features

### 4.1 Dashboard Admin
- สรุปภาพรวมระบบ (จำนวนสมาชิก, ยอดแทง, กำไร/ขาดทุน)
- กราฟรายได้ / ยอดแทง

### 4.2 จัดการสมาชิก
- ดูรายชื่อ / ค้นหา / กรอง
- ดูข้อมูลสมาชิก / ประวัติแทง
- ระงับ/ปลดล็อค
- เติมเครดิต / หักเครดิต
- แก้ไขข้อมูล

### 4.3 จัดการหวย
- เปิด/ปิดรอบหวย
- ตั้งอัตราจ่ายแต่ละประเภท
- กำหนดเวลาเปิด-ปิดรับ
- จำกัดจำนวนเงินแทงสูงสุด/ต่ำสุด
- ตั้งค่าเลขอั้น (เลขที่ไม่รับ)
- กรอกผลรางวัล / ดึงผลอัตโนมัติ

### 4.4 จัดการการเงิน
- อนุมัติฝาก/ถอน
- ดูรายการธุรกรรม
- ตั้งค่าบัญชีรับเงิน
- รายงานกำไร/ขาดทุน

### 4.5 จัดการ Affiliate
- ตั้ง % คอมมิชชั่น
- ดูรายการแนะนำ
- อนุมัติถอนคอมมิชชั่น

### 4.6 ตั้งค่าระบบ
- ตั้งค่าทั่วไป (ชื่อเว็บ, โลโก้, สี)
- ตั้งค่า SMS OTP API
- ตั้งค่า Payment Gateway
- ข้อความประกาศ (Marquee)
- Maintenance Mode

---

## 5. Non-Functional Requirements

### 5.1 Performance
- Page load < 2 วินาที
- API response < 500ms
- รองรับ concurrent users 1,000+
- Redis caching สำหรับข้อมูลที่อ่านบ่อย

### 5.2 Security
- CSRF Protection
- XSS Prevention
- SQL Injection Prevention
- Rate Limiting (login, OTP)
- 2FA (SMS OTP)
- Encrypted passwords (bcrypt)
- Audit logging
- IP Whitelist สำหรับ Admin

### 5.3 Scalability
- Horizontal scaling (multiple app servers)
- Queue workers สำหรับ heavy tasks
- Database read replicas

### 5.4 UI/UX
- Mobile-first responsive design
- Dark/Light theme (เว็บต้นแบบใช้ dark green theme)
- Smooth animations
- Loading states / skeleton screens
- Toast notifications
- PWA ready (installable)

### 5.5 Real-time
- WebSocket สำหรับ countdown timers
- Real-time chat (admin inbox)
- Live notification เมื่อผลหวยออก
- Live balance update

---

## 6. Database Schema Overview

### Core Tables
```
users                    - สมาชิก
user_bank_accounts       - บัญชีธนาคารสมาชิก
otp_verifications        - OTP records

lottery_types            - ประเภทหวย (รัฐบาล, ยี่กี, ลาว ฯลฯ)
lottery_rounds           - รอบหวย (งวด)
lottery_results          - ผลรางวัล

bet_types                - ประเภทการแทง (3ตัวบน, 2ตัวล่าง ฯลฯ)
bet_type_rates           - อัตราจ่ายแต่ละประเภท
bet_limits               - จำกัดวงเงิน / เลขอั้น

tickets                  - โพยหวย (ใบแทง)
ticket_items             - รายการแทงในโพย

deposits                 - รายการฝาก
withdrawals              - รายการถอน
transactions             - ประวัติธุรกรรม (เงินเข้า-ออก)

number_sets              - เลขชุดที่บันทึก
number_set_items         - เลขในชุด

affiliates               - ข้อมูล affiliate
affiliate_commissions    - คอมมิชชั่นที่ได้

messages                 - ข้อความ chat
notifications            - การแจ้งเตือน

settings                 - ตั้งค่าระบบ
admin_logs               - log การทำงานของ admin
```

---

## 7. API Endpoints Overview

### Auth
```
POST   /api/auth/register          - สมัครสมาชิก
POST   /api/auth/send-otp          - ส่ง OTP
POST   /api/auth/verify-otp        - ยืนยัน OTP
POST   /api/auth/login             - เข้าสู่ระบบ
POST   /api/auth/logout            - ออกจากระบบ
POST   /api/auth/forgot-password   - ลืมรหัสผ่าน
POST   /api/auth/reset-password    - รีเซ็ตรหัสผ่าน
```

### User
```
GET    /api/user/profile           - ข้อมูลผู้ใช้
PUT    /api/user/profile           - แก้ไขข้อมูล
PUT    /api/user/change-password   - เปลี่ยนรหัสผ่าน
GET    /api/user/balance           - ยอดเงิน
```

### Lottery
```
GET    /api/lottery/types           - ประเภทหวยทั้งหมด
GET    /api/lottery/rounds          - รอบหวยที่เปิดรับ
GET    /api/lottery/rounds/{id}     - รายละเอียดรอบ
GET    /api/lottery/rates/{roundId} - อัตราจ่าย
POST   /api/lottery/bet             - แทงหวย (ส่งโพย)
```

### Yeekee
```
GET    /api/yeekee/rounds           - รอบยี่กีทั้งหมด
GET    /api/yeekee/rounds/{id}      - รายละเอียดรอบ
POST   /api/yeekee/bet              - แทงยี่กี
```

### Results
```
GET    /api/results                 - ผลรางวัลทั้งหมด
GET    /api/results/{date}          - ผลรางวัลตามวันที่
GET    /api/results/type/{type}     - ผลรางวัลตามประเภท
```

### Tickets
```
GET    /api/tickets                 - โพยทั้งหมด
GET    /api/tickets/{id}            - รายละเอียดโพย
GET    /api/tickets/today           - โพยวันนี้
POST   /api/tickets/reuse/{id}      - ดึงโพยเก่ามาแทงใหม่
```

### Number Sets
```
GET    /api/number-sets             - เลขชุดทั้งหมด
POST   /api/number-sets             - สร้างเลขชุด
PUT    /api/number-sets/{id}        - แก้ไขเลขชุด
DELETE /api/number-sets/{id}        - ลบเลขชุด
```

### Finance
```
POST   /api/deposits                - แจ้งฝากเงิน
GET    /api/deposits                - รายการฝาก
POST   /api/withdrawals             - แจ้งถอนเงิน
GET    /api/withdrawals             - รายการถอน
GET    /api/transactions            - ประวัติธุรกรรม
GET    /api/financial-report        - รายงานการเงิน
```

### Affiliate
```
GET    /api/affiliate/dashboard     - ภาพรวม affiliate
GET    /api/affiliate/members       - สมาชิกที่แนะนำ
GET    /api/affiliate/commissions   - รายได้คอมมิชชั่น
POST   /api/affiliate/withdraw      - ถอนคอมมิชชั่น
GET    /api/affiliate/link          - ลิงก์แนะนำ
```

### Chat
```
GET    /api/messages                - ข้อความทั้งหมด
POST   /api/messages                - ส่งข้อความ
```

---

## 8. Milestones

| Phase | Description | Duration |
|-------|-------------|----------|
| **Phase 1** | Authentication (Register/Login/OTP) + User Profile | 1-2 สัปดาห์ |
| **Phase 2** | Lottery Types & Betting System | 2-3 สัปดาห์ |
| **Phase 3** | Results & Ticket/Slip System | 1 สัปดาห์ |
| **Phase 4** | Finance (Deposit/Withdraw/Transactions) | 1-2 สัปดาห์ |
| **Phase 5** | Yeekee & Set Lottery | 1-2 สัปดาห์ |
| **Phase 6** | Affiliate System | 1 สัปดาห์ |
| **Phase 7** | Admin Panel | 2-3 สัปดาห์ |
| **Phase 8** | Chat, Notifications, Real-time | 1 สัปดาห์ |
| **Phase 9** | Games Integration (3rd party) | 1-2 สัปดาห์ |
| **Phase 10** | Testing, Optimization, Deployment | 1-2 สัปดาห์ |

---

## 9. Advanced Features (ฟีเจอร์เหนือชั้น)

> รายละเอียดเต็มอยู่ใน [ADVANCED_FEATURES.md](./ADVANCED_FEATURES.md)

| # | Feature | Description |
|---|---------|-------------|
| 1 | **AI Smart Number** | วิเคราะห์สถิติ, Hot/Cold Numbers, AI Smart Pick, "ถ้าแทง..." Simulator |
| 2 | **Gamification** | VIP Levels (5 ระดับ), Daily Missions, Lucky Spin, Achievements & Badges |
| 3 | **Social & Group Play** | สร้างกลุ่มแทงหวย, ลงขัน, แบ่งรางวัลอัตโนมัติ, Chat กลุ่ม |
| 4 | **Live Draw Experience** | Animated Reveal, Live Reactions, Win Celebration, Near Miss Alert |
| 5 | **Smart Notifications** | AI-based timing, LINE Notify, Push, Draw Reminder, Hot Number Alert |
| 6 | **Personal Analytics** | กราฟ spending, Win Rate, Heat Map, เลขนำโชค, Monthly Report |
| 7 | **PWA** | Install to Home Screen, Offline Mode, Push Notification |
| 8 | **Dark/Light Theme** | Auto-detect system preference, custom theme colors |
| 9 | **LINE OA Integration** | LINE Login, Notify, Rich Menu, Chatbot, LIFF |
| 10 | **Multi-Language** | ไทย, English, ลาว, เมียนมา, กัมพูชา |
| 11 | **Responsible Gaming** | วงเงินจำกัด, Self-Exclusion, Reality Check, AI ตรวจจับพฤติกรรมเสี่ยง |
| 12 | **Advanced Admin** | Real-time Dashboard, Risk Management, Profit Calculator, Automated Reports |

---

## 10. SMS Auto-Deposit System (ฝากเงินอัตโนมัติ ไม่ต้องสนใจแบงค์)

> รายละเอียดเต็มอยู่ใน [SMS_AUTO_DEPOSIT.md](./SMS_AUTO_DEPOSIT.md)
> Database Schema อยู่ใน [DATABASE_SMS_DEPOSIT.md](./DATABASE_SMS_DEPOSIT.md)

### 10.1 Overview
ระบบฝากเงินอัตโนมัติผ่าน SMS โดยใช้ **smschecker** (Android App) ดัก SMS จากธนาคาร
ลูกค้าโอนจากธนาคารไหนก็ได้ ระบบจับคู่อัตโนมัติจากยอดทศนิยม

### 10.2 Flow
1. ลูกค้ากด "ฝากเงิน" → กรอกจำนวน (500 บาท)
2. ระบบสร้างยอด unique (500.37 บาท) + แสดง QR/บัญชีรับ
3. ลูกค้าโอนเงินยอด 500.37
4. Android app ดัก SMS ธนาคาร → เข้ารหัส AES-256 → ส่ง API
5. Backend ถอดรหัส → จับคู่ยอด → เติมเครดิต → แจ้ง real-time

### 10.3 รองรับ 15 ธนาคาร + PromptPay
กสิกรไทย, ไทยพาณิชย์, กรุงไทย, กรุงเทพ, ออมสิน, กรุงศรี, ทหารไทยธนชาต, CIMB, เกียรตินาคินภัทร, แลนด์ แอนด์ เฮ้าส์, ทิสโก้, ยูโอบี, ICBC, ธ.ก.ส., พร้อมเพย์

### 10.4 Security
- AES-256-GCM encryption
- HMAC-SHA256 request signing
- Nonce-based replay prevention
- Device authentication (API Key)

### 10.5 Package
```
composer require thaiprompt/smschecker-laravel
```

---

## 11. Risk Management & Profit Control (ควบคุมกำไร)

> รายละเอียดเต็มอยู่ใน [ADMIN_RISK_CONTROL.md](./ADMIN_RISK_CONTROL.md)
> Database Schema อยู่ใน [DATABASE_RISK_CONTROL.md](./DATABASE_RISK_CONTROL.md)

### 11.1 Overview
ระบบ AI ควบคุมกำไรแบบ real-time ให้แอดมินเห็นภาพรวมทุก user
ว่าใครกำลังได้/เสีย และปรับ Win Rate ได้ทั้งระดับ Global และรายบุคคล

### 11.2 Key Features
- **Real-time P&L Dashboard** - ยอดแทง, จ่าย, กำไร, margin % แบบ live
- **User Risk Profiling** - Fish / Normal / Watch / Danger / Whale
- **AI Auto-Balance** - ปรับอัตราจ่ายอัตโนมัติทุก 5 นาที
- **Per-User Control** - Win Rate Override, Rate Adjustment, เลขอั้นรายบุคคล
- **Number Exposure** - ติดตามความเสี่ยงต่อเลขแบบ real-time
- **Anomaly Detection** - ตรวจจับพฤติกรรมผิดปกติ (ถูกติดกัน, bot, สมรู้ร่วมคิด)
- **Risk Alerts** - แจ้งเตือนเมื่อ margin ต่ำ, user ชนะเยอะ, exposure สูง

### 11.3 Admin Routes
```
GET  /admin/risk/dashboard         - Live Dashboard
GET  /admin/risk/users             - User Risk Profiles
PUT  /admin/risk/users/{id}/...    - Control User Win Rate / Limits
GET  /admin/risk/top-winners       - ใครได้เยอะสุด
GET  /admin/risk/number-exposure   - เลขที่เสี่ยง
POST /admin/risk/auto-balance      - Trigger AI Balance
```

---

## 12. Original Site Reference

- **URL:** หวยมีดี889
- **Color Theme:** Dark green gradient background
- **Layout:** Single-column centered, card-based UI
- **Navigation:** Top navbar with icons
- **Style:** Modern, mobile-first, bold typography
