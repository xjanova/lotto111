# Database Schema Design
# ระบบหวยออนไลน์ - Lotto Platform

## ER Diagram Overview

```
users ──┬── user_bank_accounts
        ├── otp_verifications
        ├── tickets ── ticket_items
        ├── deposits
        ├── withdrawals
        ├── transactions
        ├── number_sets ── number_set_items
        ├── affiliate_commissions
        └── messages

lottery_types ── lottery_rounds ── lottery_results
                      └── bet_type_rates ── bet_types
                      └── bet_limits
```

---

## Tables Detail

### 1. users
```sql
CREATE TABLE users (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone               VARCHAR(15) NOT NULL UNIQUE,
    password            VARCHAR(255) NOT NULL,
    name                VARCHAR(100) NOT NULL,
    role                ENUM('member','agent','admin','super_admin') DEFAULT 'member',
    status              ENUM('active','suspended','banned') DEFAULT 'active',
    balance             DECIMAL(12,2) DEFAULT 0.00,
    referral_code       VARCHAR(20) UNIQUE,
    referred_by         BIGINT UNSIGNED NULL,
    avatar              VARCHAR(255) NULL,
    phone_verified_at   TIMESTAMP NULL,
    last_login_at       TIMESTAMP NULL,
    last_login_ip       VARCHAR(45) NULL,
    remember_token      VARCHAR(100) NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_phone (phone),
    INDEX idx_referral_code (referral_code),
    INDEX idx_status (status),
    INDEX idx_role (role)
);
```

### 2. user_bank_accounts
```sql
CREATE TABLE user_bank_accounts (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    bank_code       VARCHAR(10) NOT NULL,
    bank_name       VARCHAR(100) NOT NULL,
    account_number  VARCHAR(20) NOT NULL,
    account_name    VARCHAR(100) NOT NULL,
    is_primary      BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);
```

### 3. otp_verifications
```sql
CREATE TABLE otp_verifications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone           VARCHAR(15) NOT NULL,
    otp_code        VARCHAR(6) NOT NULL,
    purpose         ENUM('register','login','reset_password','verify') NOT NULL,
    is_used         BOOLEAN DEFAULT FALSE,
    attempts        TINYINT UNSIGNED DEFAULT 0,
    expires_at      TIMESTAMP NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_phone_purpose (phone, purpose),
    INDEX idx_expires_at (expires_at)
);
```

### 4. lottery_types
```sql
CREATE TABLE lottery_types (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    category        ENUM('government','yeekee','bank','international','set') NOT NULL,
    country         VARCHAR(50) NULL,
    icon            VARCHAR(255) NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    sort_order      INT DEFAULT 0,
    settings        JSON NULL,  -- เก็บ config เพิ่มเติม
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);
```

### 5. lottery_rounds
```sql
CREATE TABLE lottery_rounds (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_type_id BIGINT UNSIGNED NOT NULL,
    round_code      VARCHAR(50) NOT NULL UNIQUE,  -- e.g. GOV-202602161530-xxx
    round_number    INT NULL,  -- สำหรับยี่กี (รอบที่ 1-144)
    status          ENUM('upcoming','open','closed','resulted','cancelled') DEFAULT 'upcoming',
    open_at         TIMESTAMP NOT NULL,
    close_at        TIMESTAMP NOT NULL,
    result_at       TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (lottery_type_id) REFERENCES lottery_types(id),
    INDEX idx_lottery_type (lottery_type_id),
    INDEX idx_status (status),
    INDEX idx_close_at (close_at),
    INDEX idx_round_code (round_code)
);
```

### 6. lottery_results
```sql
CREATE TABLE lottery_results (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_round_id BIGINT UNSIGNED NOT NULL,
    result_type     VARCHAR(30) NOT NULL,  -- 'first_prize','three_top','three_tod','two_bottom' etc.
    result_value    VARCHAR(10) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (lottery_round_id) REFERENCES lottery_rounds(id),
    INDEX idx_round (lottery_round_id),
    UNIQUE idx_round_type (lottery_round_id, result_type)
);
```

### 7. bet_types
```sql
CREATE TABLE bet_types (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(50) NOT NULL,       -- e.g. '3 ตัวบน'
    slug            VARCHAR(50) NOT NULL UNIQUE, -- e.g. 'three_top'
    digit_count     TINYINT NOT NULL,            -- จำนวนหลัก 2,3,4,5
    description     TEXT NULL,
    sort_order      INT DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 8. bet_type_rates
```sql
CREATE TABLE bet_type_rates (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_type_id BIGINT UNSIGNED NOT NULL,
    bet_type_id     BIGINT UNSIGNED NOT NULL,
    rate            DECIMAL(10,2) NOT NULL,       -- อัตราจ่าย
    min_amount      DECIMAL(10,2) DEFAULT 1.00,   -- แทงขั้นต่ำ
    max_amount      DECIMAL(10,2) DEFAULT 99999.00, -- แทงสูงสุด
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (lottery_type_id) REFERENCES lottery_types(id),
    FOREIGN KEY (bet_type_id) REFERENCES bet_types(id),
    UNIQUE idx_lottery_bet (lottery_type_id, bet_type_id)
);
```

### 9. bet_limits
```sql
CREATE TABLE bet_limits (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_round_id BIGINT UNSIGNED NOT NULL,
    bet_type_id     BIGINT UNSIGNED NOT NULL,
    number          VARCHAR(10) NOT NULL,          -- เลขอั้น
    max_amount      DECIMAL(10,2) DEFAULT 0.00,    -- 0 = ไม่รับ
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (lottery_round_id) REFERENCES lottery_rounds(id),
    FOREIGN KEY (bet_type_id) REFERENCES bet_types(id),
    INDEX idx_round_bet_number (lottery_round_id, bet_type_id, number)
);
```

### 10. tickets
```sql
CREATE TABLE tickets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    lottery_round_id BIGINT UNSIGNED NOT NULL,
    ticket_code     VARCHAR(30) NOT NULL UNIQUE,
    total_amount    DECIMAL(12,2) NOT NULL,
    total_win       DECIMAL(12,2) DEFAULT 0.00,
    status          ENUM('pending','won','lost','cancelled','refunded') DEFAULT 'pending',
    bet_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    result_at       TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (lottery_round_id) REFERENCES lottery_rounds(id),
    INDEX idx_user (user_id),
    INDEX idx_round (lottery_round_id),
    INDEX idx_status (status),
    INDEX idx_ticket_code (ticket_code),
    INDEX idx_bet_at (bet_at)
);
```

### 11. ticket_items
```sql
CREATE TABLE ticket_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id       BIGINT UNSIGNED NOT NULL,
    bet_type_id     BIGINT UNSIGNED NOT NULL,
    number          VARCHAR(10) NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    rate            DECIMAL(10,2) NOT NULL,        -- อัตราจ่ายตอนแทง
    win_amount      DECIMAL(12,2) DEFAULT 0.00,
    is_won          BOOLEAN NULL,                   -- NULL=รอผล, TRUE=ถูก, FALSE=ไม่ถูก
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (bet_type_id) REFERENCES bet_types(id),
    INDEX idx_ticket (ticket_id),
    INDEX idx_number (number)
);
```

### 12. deposits
```sql
CREATE TABLE deposits (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    method          ENUM('bank_transfer','promptpay','truewallet','auto') NOT NULL,
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    slip_image      VARCHAR(255) NULL,
    bank_ref        VARCHAR(100) NULL,
    note            TEXT NULL,
    approved_by     BIGINT UNSIGNED NULL,
    approved_at     TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);
```

### 13. withdrawals
```sql
CREATE TABLE withdrawals (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    bank_account_id BIGINT UNSIGNED NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    status          ENUM('pending','approved','rejected','processing','completed') DEFAULT 'pending',
    note            TEXT NULL,
    approved_by     BIGINT UNSIGNED NULL,
    approved_at     TIMESTAMP NULL,
    completed_at    TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (bank_account_id) REFERENCES user_bank_accounts(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);
```

### 14. transactions
```sql
CREATE TABLE transactions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    type            ENUM('deposit','withdraw','bet','win','refund','commission','adjustment') NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,         -- +เงินเข้า / -เงินออก
    balance_before  DECIMAL(12,2) NOT NULL,
    balance_after   DECIMAL(12,2) NOT NULL,
    reference_type  VARCHAR(50) NULL,                -- e.g. 'ticket', 'deposit'
    reference_id    BIGINT UNSIGNED NULL,
    description     VARCHAR(255) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at),
    INDEX idx_reference (reference_type, reference_id)
);
```

### 15. number_sets
```sql
CREATE TABLE number_sets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(100) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 16. number_set_items
```sql
CREATE TABLE number_set_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number_set_id   BIGINT UNSIGNED NOT NULL,
    bet_type_id     BIGINT UNSIGNED NOT NULL,
    number          VARCHAR(10) NOT NULL,
    amount          DECIMAL(10,2) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (number_set_id) REFERENCES number_sets(id) ON DELETE CASCADE,
    FOREIGN KEY (bet_type_id) REFERENCES bet_types(id),
    INDEX idx_set (number_set_id)
);
```

### 17. affiliate_commissions
```sql
CREATE TABLE affiliate_commissions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,         -- ผู้ได้รับคอมมิชชั่น
    from_user_id    BIGINT UNSIGNED NOT NULL,         -- สมาชิกที่แทง
    ticket_id       BIGINT UNSIGNED NULL,
    bet_amount      DECIMAL(12,2) NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL,             -- เปอร์เซ็นต์
    commission      DECIMAL(12,2) NOT NULL,
    status          ENUM('pending','paid','cancelled') DEFAULT 'pending',
    paid_at         TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (from_user_id) REFERENCES users(id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

### 18. messages
```sql
CREATE TABLE messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id       BIGINT UNSIGNED NOT NULL,
    receiver_id     BIGINT UNSIGNED NULL,             -- NULL = broadcast
    message         TEXT NOT NULL,
    is_read         BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_sender (sender_id),
    INDEX idx_is_read (is_read)
);
```

### 19. notifications
```sql
CREATE TABLE notifications (
    id              CHAR(36) PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    type            VARCHAR(100) NOT NULL,
    title           VARCHAR(255) NOT NULL,
    body            TEXT NULL,
    data            JSON NULL,
    read_at         TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_read (user_id, read_at)
);
```

### 20. settings
```sql
CREATE TABLE settings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key             VARCHAR(100) NOT NULL UNIQUE,
    value           TEXT NULL,
    group           VARCHAR(50) DEFAULT 'general',
    type            ENUM('string','integer','boolean','json','text') DEFAULT 'string',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_group (group)
);
```

### 21. admin_logs
```sql
CREATE TABLE admin_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id        BIGINT UNSIGNED NOT NULL,
    action          VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    target_type     VARCHAR(50) NULL,
    target_id       BIGINT UNSIGNED NULL,
    ip_address      VARCHAR(45) NULL,
    user_agent      TEXT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id) REFERENCES users(id),
    INDEX idx_admin (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);
```
