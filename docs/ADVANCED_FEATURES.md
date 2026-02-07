# Advanced Features - ฟีเจอร์เหนือชั้น
# ระบบหวยออนไลน์ - Lotto Platform

> ฟีเจอร์ขั้นสูงที่ทำให้ระบบแตกต่างและเหนือกว่าเว็บต้นแบบอย่างชัดเจน

---

## 1. AI Smart Number (เลขเด็ด AI)

### แนวคิด
ระบบ AI วิเคราะห์สถิติหวยย้อนหลัง แนะนำเลขเด็ดให้ผู้ใช้ พร้อมเหตุผล

### ฟีเจอร์
- **Hot/Cold Numbers** — เลขที่ออกบ่อย / เลขที่ไม่ออกนาน
- **Frequency Analysis** — กราฟความถี่เลขแต่ละตัวย้อนหลัง 100 งวด
- **Pattern Detection** — จับรูปแบบเลขที่มีโอกาสออกซ้ำ
- **AI Smart Pick** — กดปุ่มเดียว AI เลือกเลขให้ พร้อมแสดง confidence level
- **Overdue Alert** — แจ้งเตือนเมื่อเลขไม่ออกเกินค่าเฉลี่ย
- **"ถ้าแทง..." Simulator** — จำลองว่าถ้าแทงเลขนี้ 10 งวดที่ผ่านมา จะได้/เสียเท่าไร

### Tech Implementation
```
- Python microservice (FastAPI) สำหรับ ML model
- Laravel calls via HTTP/gRPC
- Redis cache ผลวิเคราะห์ (update หลังผลออก)
- Charts: Chart.js / ApexCharts
```

### Database
```sql
CREATE TABLE number_analytics (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_type_id BIGINT UNSIGNED NOT NULL,
    number          VARCHAR(10) NOT NULL,
    digit_position  VARCHAR(20) NOT NULL,        -- 'three_top', 'two_bottom', etc.
    frequency       INT DEFAULT 0,
    last_appeared   DATE NULL,
    gap_count       INT DEFAULT 0,               -- จำนวนงวดที่ไม่ออก
    avg_gap         DECIMAL(5,2) DEFAULT 0,
    is_hot          BOOLEAN DEFAULT FALSE,
    is_cold         BOOLEAN DEFAULT FALSE,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_type_number (lottery_type_id, number),
    INDEX idx_hot_cold (is_hot, is_cold)
);

CREATE TABLE ai_predictions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_type_id BIGINT UNSIGNED NOT NULL,
    lottery_round_id BIGINT UNSIGNED NOT NULL,
    numbers         JSON NOT NULL,                -- {"three_top": ["123","456"], "two_bottom": ["12","34"]}
    confidence      DECIMAL(5,2) DEFAULT 0,
    algorithm       VARCHAR(50) NOT NULL,
    is_correct      BOOLEAN NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_round (lottery_round_id)
);
```

---

## 2. Gamification System (ระบบเกมมิฟิเคชัน)

### 2.1 VIP Level System (ระบบระดับสมาชิก)

| Level | ชื่อ | XP Required | สิทธิพิเศษ |
|-------|------|-------------|-----------|
| 1 | Bronze | 0 | - |
| 2 | Silver | 1,000 | ส่วนลดค่าแทง 1% |
| 3 | Gold | 5,000 | ส่วนลด 2%, ถอนไม่จำกัดรอบ |
| 4 | Platinum | 20,000 | ส่วนลด 3%, ถอนด่วน, AI Smart Pick ฟรี |
| 5 | Diamond | 100,000 | ส่วนลด 5%, ถอนทันที, VIP Support, Exclusive games |

### การได้ XP
- แทงหวย: 1 XP ต่อ 10 บาท
- Login รายวัน: 10 XP
- ทำ Mission สำเร็จ: 50-500 XP
- แนะนำเพื่อน: 200 XP

### 2.2 Daily Mission System (ภารกิจรายวัน)

```
ภารกิจประจำวัน:
├── Login วันนี้                    → +10 XP
├── แทงหวยอย่างน้อย 1 โพย            → +20 XP + Lucky Spin 1 ครั้ง
├── แทงหวย 3 ประเภทต่างกัน           → +50 XP
└── แทงหวยครบ 500 บาท              → +100 XP + Badge

ภารกิจประจำสัปดาห์:
├── แทงหวยครบ 5 วัน                → +200 XP + Free Ticket
├── แนะนำเพื่อน 1 คน                → +300 XP
└── ถูกรางวัลอย่างน้อย 1 ครั้ง        → +500 XP + Bonus 2%

ภารกิจพิเศษ (Event):
├── แทงหวยรัฐบาลงวดนี้              → 2x XP
└── ลุ้นรางวัล Grand Prize            → Random reward
```

### 2.3 Lucky Spin (วงล้อนำโชค)

- หมุนฟรีวันละ 1 ครั้ง (ได้จาก login)
- รางวัล: XP, เครดิตฟรี, ส่วนลด, Badge, คูปอง
- Animation สวยงาม + เสียงเอฟเฟกต์

### 2.4 Achievement & Badge System

| Badge | เงื่อนไข | รางวัล |
|-------|---------|--------|
| First Timer | แทงหวยครั้งแรก | +50 XP |
| Lucky Star | ถูกรางวัลครั้งแรก | +100 XP |
| Streak Master | Login ติดต่อกัน 7 วัน | +200 XP |
| High Roller | แทงรวมครบ 10,000 | Badge + Profile Frame |
| Social Butterfly | แนะนำเพื่อน 5 คน | Exclusive Avatar |
| Analyst | ใช้ AI Smart Pick 10 ครั้ง | Unlock Advanced Stats |
| Veteran | สมาชิกครบ 1 ปี | Diamond Frame |

### Database
```sql
CREATE TABLE vip_levels (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(50) NOT NULL,
    slug            VARCHAR(50) NOT NULL UNIQUE,
    min_xp          INT NOT NULL,
    discount_rate   DECIMAL(5,2) DEFAULT 0,
    benefits        JSON NULL,
    icon            VARCHAR(255) NULL,
    color           VARCHAR(7) NULL,
    sort_order      INT DEFAULT 0
);

CREATE TABLE user_gamification (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL UNIQUE,
    xp              INT DEFAULT 0,
    vip_level_id    BIGINT UNSIGNED DEFAULT 1,
    login_streak    INT DEFAULT 0,
    longest_streak  INT DEFAULT 0,
    last_daily_claim DATE NULL,
    last_spin_at    TIMESTAMP NULL,
    spin_count      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vip_level_id) REFERENCES vip_levels(id)
);

CREATE TABLE missions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    type            ENUM('daily','weekly','special','achievement') NOT NULL,
    condition_type  VARCHAR(50) NOT NULL,          -- 'bet_count','bet_amount','login','referral','win'
    condition_value DECIMAL(12,2) NOT NULL,
    reward_xp       INT DEFAULT 0,
    reward_credit   DECIMAL(10,2) DEFAULT 0,
    reward_badge_id BIGINT UNSIGNED NULL,
    reward_spins    INT DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    start_at        TIMESTAMP NULL,
    end_at          TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_missions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    mission_id      BIGINT UNSIGNED NOT NULL,
    progress        DECIMAL(12,2) DEFAULT 0,
    is_completed    BOOLEAN DEFAULT FALSE,
    is_claimed      BOOLEAN DEFAULT FALSE,
    completed_at    TIMESTAMP NULL,
    claimed_at      TIMESTAMP NULL,
    period_date     DATE NOT NULL,                 -- วันที่ของ mission (สำหรับ daily/weekly)
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (mission_id) REFERENCES missions(id),
    UNIQUE idx_user_mission_period (user_id, mission_id, period_date)
);

CREATE TABLE badges (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    description     TEXT NULL,
    icon            VARCHAR(255) NULL,
    rarity          ENUM('common','rare','epic','legendary') DEFAULT 'common',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_badges (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    badge_id        BIGINT UNSIGNED NOT NULL,
    earned_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (badge_id) REFERENCES badges(id),
    UNIQUE idx_user_badge (user_id, badge_id)
);

CREATE TABLE spin_rewards (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    type            ENUM('xp','credit','discount','badge','coupon','empty') NOT NULL,
    value           DECIMAL(10,2) DEFAULT 0,
    probability     DECIMAL(5,4) NOT NULL,         -- 0.0001 - 1.0000
    icon            VARCHAR(255) NULL,
    color           VARCHAR(7) NULL,
    is_active       BOOLEAN DEFAULT TRUE
);

CREATE TABLE user_spin_history (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    spin_reward_id  BIGINT UNSIGNED NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (spin_reward_id) REFERENCES spin_rewards(id)
);
```

---

## 3. Social & Group Play (เล่นเป็นกลุ่ม/ซินดิเคท)

### แนวคิด
ให้ผู้ใช้สร้าง "กลุ่มแทงหวย" ร่วมกับเพื่อน แบ่งเงิน/รางวัลอัตโนมัติ

### ฟีเจอร์
- **สร้างกลุ่ม** — ตั้งชื่อ, คำอธิบาย, เชิญเพื่อนด้วย invite code
- **ลงขัน (Pool Fund)** — สมาชิกลงเงินเข้ากองกลาง
- **แทงหวยกลุ่ม** — หัวหน้ากลุ่มแทงจากเงินกองกลาง
- **แบ่งรางวัลอัตโนมัติ** — ถูกรางวัล → แบ่งตามสัดส่วนที่ลงขัน
- **Chat กลุ่ม** — คุยกันในกลุ่ม, แชร์เลขเด็ด
- **สถิติกลุ่ม** — ยอดแทงรวม, ยอดถูกรวม, สมาชิกที่ active
- **Leaderboard กลุ่ม** — จัดอันดับกลุ่มที่ถูกรางวัลมากที่สุด

### Database
```sql
CREATE TABLE lottery_groups (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    owner_id        BIGINT UNSIGNED NOT NULL,
    invite_code     VARCHAR(20) NOT NULL UNIQUE,
    avatar          VARCHAR(255) NULL,
    max_members     INT DEFAULT 20,
    pool_balance    DECIMAL(12,2) DEFAULT 0,
    is_public       BOOLEAN DEFAULT FALSE,
    status          ENUM('active','closed') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE lottery_group_members (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id        BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    role            ENUM('owner','admin','member') DEFAULT 'member',
    contribution    DECIMAL(12,2) DEFAULT 0,       -- เงินที่ลงขัน
    share_percent   DECIMAL(5,2) DEFAULT 0,        -- % ส่วนแบ่ง
    joined_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (group_id) REFERENCES lottery_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE idx_group_user (group_id, user_id)
);

CREATE TABLE group_tickets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id        BIGINT UNSIGNED NOT NULL,
    ticket_id       BIGINT UNSIGNED NOT NULL,
    placed_by       BIGINT UNSIGNED NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (group_id) REFERENCES lottery_groups(id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (placed_by) REFERENCES users(id)
);

CREATE TABLE group_messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id        BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    message         TEXT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (group_id) REFERENCES lottery_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 4. Real-time Live Draw Experience (ถ่ายทอดสดหวย)

### ฟีเจอร์
- **Animated Number Reveal** — เลขออกทีละตัว animation สวยงาม (ไม่ใช่แค่แสดงเลข)
- **Live Reactions** — กดปุ่ม emoji/reaction ขณะดูผลสด
- **Win Celebration** — ถ้าถูกรางวัล confetti animation + เสียง + แสดงจำนวนเงินที่ได้
- **Near Miss Alert** — "คุณพลาดไป 1 ตัว!" กระตุ้นให้เล่นต่อ
- **Live Chat** — แชทกับผู้เล่นคนอื่นขณะรอผล
- **"What If" Replay** — หลังผลออก แสดงว่า "ถ้าคุณแทงเลขนี้ จะได้เท่าไร"
- **Social Share** — แชร์ผลถูก/ไม่ถูกลง Social Media ด้วย branded card

### Tech
```
- Laravel Reverb WebSocket
- Canvas/Lottie animations
- Alpine.js + Web Audio API
```

---

## 5. Smart Notification System (ระบบแจ้งเตือนอัจฉริยะ)

### ฟีเจอร์
- **Draw Reminder** — แจ้งเตือน 30 นาที / 5 นาที ก่อนปิดรับ
- **Result Alert** — แจ้งผลทันทีที่ออก พร้อมสถานะถูก/ไม่ถูก
- **Jackpot Alert** — แจ้งเมื่อ jackpot สูงเกินค่าที่ตั้ง
- **Hot Number Alert** — แจ้งเมื่อ AI ตรวจพบเลขที่ควรสนใจ
- **Streak Alert** — "คุณ login ติดต่อกัน 7 วัน! รับ bonus"
- **Friend Activity** — "เพื่อนของคุณเพิ่งถูกรางวัล!"
- **Smart Timing** — AI เรียนรู้เวลาที่ user ชอบเปิดแอป → ส่งแจ้งเตือนตอนนั้น

### Channels
- In-App Notification (bell icon)
- Push Notification (PWA / Mobile)
- LINE Notify (เชื่อมต่อ LINE)
- SMS (สำหรับแจ้งเตือนสำคัญ)

### Database
```sql
CREATE TABLE notification_preferences (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL UNIQUE,
    draw_reminder   BOOLEAN DEFAULT TRUE,
    result_alert    BOOLEAN DEFAULT TRUE,
    jackpot_alert   BOOLEAN DEFAULT TRUE,
    hot_number_alert BOOLEAN DEFAULT FALSE,
    friend_activity BOOLEAN DEFAULT TRUE,
    promotion       BOOLEAN DEFAULT TRUE,
    channels        JSON DEFAULT '["app","line"]',
    reminder_minutes INT DEFAULT 30,               -- แจ้งเตือนก่อนปิดรับกี่นาที
    jackpot_threshold DECIMAL(12,2) DEFAULT 0,     -- แจ้งเมื่อ jackpot เกินกี่บาท
    quiet_start     TIME NULL,                     -- ช่วงเวลาไม่รบกวน เริ่ม
    quiet_end       TIME NULL,                     -- ช่วงเวลาไม่รบกวน สิ้นสุด
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 6. Dashboard Analytics สำหรับผู้ใช้ (Personal Stats)

### ฟีเจอร์
- **ภาพรวม** — ยอดแทงรวม, ยอดถูกรวม, กำไร/ขาดทุนสุทธิ, Win Rate %
- **กราฟ Spending** — กราฟยอดแทง/ยอดถูก รายวัน/สัปดาห์/เดือน
- **Heat Map** — เลขที่ชอบแทง, วันที่ชอบแทง
- **เลขนำโชค** — เลขที่แทงแล้วถูกบ่อยที่สุด (จากประวัติจริง)
- **Bet Type Analysis** — วิเคราะห์ว่าแทงประเภทไหนได้ผลดีที่สุด
- **Monthly Report** — สรุปรายเดือนอัตโนมัติ

### Database
```sql
CREATE TABLE user_statistics (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    period_type     ENUM('daily','weekly','monthly','all_time') NOT NULL,
    period_date     DATE NOT NULL,
    total_bets      INT DEFAULT 0,
    total_bet_amount DECIMAL(12,2) DEFAULT 0,
    total_wins      INT DEFAULT 0,
    total_win_amount DECIMAL(12,2) DEFAULT 0,
    net_profit      DECIMAL(12,2) DEFAULT 0,
    win_rate        DECIMAL(5,2) DEFAULT 0,
    favorite_numbers JSON NULL,
    favorite_bet_type VARCHAR(50) NULL,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE idx_user_period (user_id, period_type, period_date)
);
```

---

## 7. PWA + Offline Support (Progressive Web App)

### ฟีเจอร์
- **Install to Home Screen** — เปิดใช้งานเหมือนแอปจริง
- **Offline Mode** — ดูผลรางวัล, โพยเก่า, เลขชุด แม้ไม่มีเน็ต
- **Push Notification** — แจ้งเตือนแม้ไม่ได้เปิดเว็บ
- **Background Sync** — พอมีเน็ตกลับ sync ข้อมูลอัตโนมัติ
- **App-like UX** — Full screen, smooth transitions, no browser chrome

### Files
```
public/manifest.json
public/sw.js (Service Worker)
public/icons/icon-192.png
public/icons/icon-512.png
```

---

## 8. Multi-Language Support (รองรับหลายภาษา)

- ไทย (default)
- English
- ลาว
- เมียนมา
- กัมพูชา

ใช้ Laravel Localization + JSON lang files

---

## 9. Dark/Light Theme System (ธีมมืด/สว่าง)

### ฟีเจอร์
- Toggle สลับ Dark/Light
- Auto-detect จาก system preference
- บันทึก preference ใน localStorage + user profile
- Custom theme colors (admin ตั้งค่าสีหลักได้)

### Implementation
```css
/* Tailwind CSS 4 with CSS Variables */
:root {
    --color-primary: #16a34a;      /* green-600 */
    --color-primary-dark: #15803d;
    --color-bg: #ffffff;
    --color-surface: #f8fafc;
    --color-text: #1e293b;
}

.dark {
    --color-bg: #0f172a;
    --color-surface: #1e293b;
    --color-text: #f1f5f9;
}
```

---

## 10. Responsible Gaming (เล่นอย่างรับผิดชอบ)

### ฟีเจอร์
- **ตั้งวงเงินรายวัน/สัปดาห์/เดือน** — เมื่อถึงวงเงินจะถูกล็อคอัตโนมัติ
- **ตั้งเวลาเล่น** — จำกัดเวลาที่เล่นได้ต่อวัน
- **Self-Exclusion** — ระงับตัวเอง 24 ชม. / 7 วัน / 30 วัน
- **Reality Check** — Pop-up ทุก X นาที แสดงยอดที่เสียไป
- **Spending Velocity Alert** — AI ตรวจจับพฤติกรรมเสี่ยง (แทงเร็วผิดปกติ, เพิ่มเงินเรื่อยๆ)
- **Cool-off Period** — หลังแพ้ต่อเนื่อง แนะนำให้หยุดพัก

### Database
```sql
CREATE TABLE user_limits (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL UNIQUE,
    daily_limit     DECIMAL(12,2) NULL,
    weekly_limit    DECIMAL(12,2) NULL,
    monthly_limit   DECIMAL(12,2) NULL,
    daily_time_limit INT NULL,                     -- นาทีต่อวัน
    self_excluded_until TIMESTAMP NULL,
    reality_check_minutes INT DEFAULT 60,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 11. Advanced Admin Dashboard

### ฟีเจอร์เพิ่มเติมจากเดิม
- **Real-time Dashboard** — กราฟ live ยอดแทง/ฝาก/ถอน
- **Risk Management** — AI ตรวจจับการแทงที่ผิดปกติ
- **Profit/Loss Calculator** — คำนวณกำไร/ขาดทุนแบบ real-time ก่อนผลออก
- **Member Heatmap** — ดูว่า member active ช่วงไหน
- **Automated Reports** — ส่งรายงานอัตโนมัติทุกวัน/สัปดาห์
- **A/B Testing** — ทดสอบ UI/promotion ต่างๆ
- **Audit Trail** — log ทุกการกระทำของ admin

---

## 12. LINE OA Integration (เชื่อมต่อ LINE)

### ฟีเจอร์
- **LINE Login** — สมัคร/Login ด้วย LINE
- **LINE Notify** — ส่งแจ้งเตือนผ่าน LINE
- **LINE Rich Menu** — เมนูลัดใน LINE
- **LINE Chatbot** — ตอบคำถามอัตโนมัติ, ดูยอดเงิน, ดูผลรางวัล
- **LINE LIFF** — เปิดเว็บแทงหวยใน LINE browser

### Database
```sql
ALTER TABLE users ADD COLUMN line_user_id VARCHAR(50) NULL UNIQUE;
ALTER TABLE users ADD COLUMN line_display_name VARCHAR(100) NULL;
ALTER TABLE users ADD COLUMN line_picture_url VARCHAR(500) NULL;
```

---

## สรุปลำดับความสำคัญ

| Priority | Feature | Impact | Effort |
|----------|---------|--------|--------|
| **P0** | Gamification (VIP + Mission + Spin) | สูงมาก | กลาง |
| **P0** | Smart Notification System | สูงมาก | ต่ำ |
| **P0** | PWA Support | สูง | ต่ำ |
| **P0** | Dark/Light Theme | สูง | ต่ำ |
| **P1** | AI Smart Number | สูงมาก | สูง |
| **P1** | Personal Analytics Dashboard | สูง | กลาง |
| **P1** | LINE OA Integration | สูง | กลาง |
| **P1** | Live Draw Experience | สูง | กลาง |
| **P2** | Social & Group Play | กลาง | สูง |
| **P2** | Responsible Gaming | กลาง | กลาง |
| **P2** | Multi-Language | กลาง | กลาง |
| **P3** | Advanced Admin Dashboard | กลาง | สูง |
