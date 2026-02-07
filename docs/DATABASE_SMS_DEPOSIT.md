# Database Schema - SMS Auto-Deposit System
# ตาราง Database สำหรับระบบฝากเงินอัตโนมัติผ่าน SMS

---

## 1. sms_checker_devices (อุปกรณ์ SMS Checker)
```sql
CREATE TABLE sms_checker_devices (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id           VARCHAR(50) NOT NULL UNIQUE,
    device_name         VARCHAR(100) NULL,
    api_key             VARCHAR(64) NOT NULL UNIQUE,
    secret_key          VARCHAR(64) NOT NULL,
    platform            VARCHAR(20) DEFAULT 'android',
    app_version         VARCHAR(20) NULL,
    status              ENUM('active','inactive','blocked') DEFAULT 'active',
    last_active_at      TIMESTAMP NULL,
    user_id             BIGINT UNSIGNED NULL,
    ip_address          VARCHAR(45) NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_api_key (api_key),
    INDEX idx_device_id (device_id),
    INDEX idx_status (status)
);
```

## 2. sms_payment_notifications (SMS ที่รับจากอุปกรณ์)
```sql
CREATE TABLE sms_payment_notifications (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank                    VARCHAR(20) NOT NULL,
    type                    ENUM('credit','debit') NOT NULL,
    amount                  DECIMAL(15,2) NOT NULL,
    account_number          VARCHAR(50) NULL,
    sender_or_receiver      VARCHAR(255) NULL,
    reference_number        VARCHAR(100) NULL,
    sms_timestamp           TIMESTAMP NOT NULL,
    device_id               VARCHAR(50) NOT NULL,
    nonce                   VARCHAR(50) NOT NULL,
    status                  ENUM('pending','matched','confirmed','rejected','expired') DEFAULT 'pending',
    matched_transaction_id  BIGINT UNSIGNED NULL,
    raw_payload             TEXT NULL,
    ip_address              VARCHAR(45) NULL,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_amount_status (amount, status),
    INDEX idx_bank_type (bank, type),
    INDEX idx_reference (reference_number),
    INDEX idx_device (device_id),
    INDEX idx_nonce (nonce),
    INDEX idx_matched (matched_transaction_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at DESC)
);
```

## 3. unique_payment_amounts (ยอดเงินทศนิยมที่ไม่ซ้ำ)
```sql
CREATE TABLE unique_payment_amounts (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    base_amount         DECIMAL(15,2) NOT NULL,
    unique_amount       DECIMAL(15,2) NOT NULL,
    decimal_suffix      SMALLINT NOT NULL,
    transaction_id      BIGINT UNSIGNED NULL,
    transaction_type    VARCHAR(50) DEFAULT 'deposit',
    status              ENUM('reserved','used','expired','cancelled') DEFAULT 'reserved',
    expires_at          TIMESTAMP NOT NULL,
    matched_at          TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_base_suffix_status (base_amount, decimal_suffix, status),
    INDEX idx_unique_status (unique_amount, status),
    INDEX idx_transaction (transaction_id),
    INDEX idx_expires (expires_at)
);
```

## 4. sms_payment_nonces (Nonce tracking - ป้องกัน replay attack)
```sql
CREATE TABLE sms_payment_nonces (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nonce               VARCHAR(50) NOT NULL UNIQUE,
    device_id           VARCHAR(50) NOT NULL,
    used_at             TIMESTAMP NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_nonce_device (nonce, device_id),
    INDEX idx_used_at (used_at)
);
```

## 5. deposits table - เพิ่มฟิลด์สำหรับ SMS Auto-Deposit
```sql
-- Alter existing deposits table
ALTER TABLE deposits
    ADD COLUMN unique_amount        DECIMAL(15,2) NULL AFTER amount,
    ADD COLUMN unique_amount_id     BIGINT UNSIGNED NULL AFTER unique_amount,
    ADD COLUMN sms_notification_id  BIGINT UNSIGNED NULL AFTER unique_amount_id,
    ADD COLUMN matched_at           TIMESTAMP NULL,
    ADD COLUMN credited_at          TIMESTAMP NULL,
    ADD COLUMN cancelled_at         TIMESTAMP NULL,
    ADD COLUMN matched_bank         VARCHAR(20) NULL,
    ADD COLUMN matched_reference    VARCHAR(100) NULL,
    ADD COLUMN manual_matched_by    BIGINT UNSIGNED NULL,
    ADD COLUMN expires_at           TIMESTAMP NULL,
    ADD INDEX idx_method_status (method, status),
    ADD INDEX idx_expires (expires_at),
    ADD INDEX idx_unique_amount (unique_amount);
```

---

## Relationships Diagram

```
sms_checker_devices
  │
  │ 1:N
  ▼
sms_payment_notifications ──── matched ────> deposits
  │                                            │
  │                                            │ N:1
  │                                            ▼
  │                                          users
  │
  └──── nonce check ────> sms_payment_nonces

unique_payment_amounts ──── reserved for ────> deposits
```

---

## Data Flow

```
1. User สร้าง Deposit (amount=500)
   └── unique_payment_amounts: base=500, unique=500.37, status=reserved

2. SMS เข้า (amount=500.37)
   └── sms_payment_notifications: status=pending

3. Auto-match: unique_amount=500.37
   ├── sms_payment_notifications: status=matched
   ├── unique_payment_amounts: status=used
   └── deposits: status=credited

4. Expire (30 min timeout)
   ├── unique_payment_amounts: status=expired
   └── deposits: status=expired
```
