# SMS Auto-Deposit System
# ระบบฝากเงินอัตโนมัติผ่าน SMS (ไม่ต้องสนใจแบงค์)

---

## Overview

ระบบฝากเงินอัตโนมัติที่ใช้ **smschecker** (Android App) ดัก SMS แจ้งเตือนจากธนาคาร
แล้วส่งข้อมูลเข้า Backend เพื่อ **จับคู่ยอดเงิน** กับรายการฝากที่รอดำเนินการ

**ลูกค้าไม่ต้องสนใจว่าโอนจากธนาคารไหน** - ระบบรองรับทุกธนาคารที่ส่ง SMS

---

## Architecture Flow

```
┌──────────────┐     ┌──────────────┐     ┌──────────────────┐
│   ลูกค้า      │     │  Android App │     │   Laravel Backend │
│  (เว็บ/แอพ)   │     │ (smschecker) │     │   (lotto system)  │
└──────┬───────┘     └──────┬───────┘     └────────┬─────────┘
       │                     │                      │
       │ 1. กดฝากเงิน 500 บาท │                      │
       │─────────────────────────────────────────────>│
       │                     │          2. สร้าง unique amount │
       │ 3. แสดง 500.37 บาท  │              (500.37)  │
       │<─────────────────────────────────────────────│
       │                     │                      │
       │ 4. โอนเงิน 500.37   │                      │
       │    ไปที่บัญชีรับเงิน   │                      │
       │                     │                      │
       │                     │ 5. SMS จากธนาคาร      │
       │                     │    "ฝาก 500.37 บาท"    │
       │                     │──────────────────────>│
       │                     │  6. AES-256 encrypted │
       │                     │  7. HMAC-SHA256 signed│
       │                     │                      │
       │                     │          8. Decrypt & Match │
       │                     │          9. จับคู่ 500.37  │
       │                     │             กับ Deposit    │
       │                     │         10. เติมเงินใน wallet│
       │ 11. แจ้งเตือน        │                      │
       │     "ฝากสำเร็จ!"     │                      │
       │<─────────────────────────────────────────────│
       │                     │                      │
```

---

## Key Features

### 1. Bankless Deposit (ไม่ต้องสนใจแบงค์)
- ลูกค้าโอนจากธนาคารไหนก็ได้
- รองรับ 15 ธนาคารไทย + PromptPay
- ระบบจับคู่อัตโนมัติจาก **ยอดเงินทศนิยมที่ไม่ซ้ำ**

### 2. Unique Decimal Amount Matching
- ยอดฝาก 500 บาท → ระบบสร้างเป็น 500.XX (XX = 01-99)
- ไม่มียอดซ้ำกัน ณ เวลาเดียวกัน
- หมดอายุใน 30 นาที (ปรับได้)

### 3. Security Layers
- **AES-256-GCM** encryption สำหรับ payload
- **HMAC-SHA256** request signing
- **Nonce** ป้องกัน replay attack
- **Timestamp** validation (±5 นาที)
- **Device authentication** ผ่าน API Key

### 4. Auto-Confirm Flow
- SMS เข้า → ถอดรหัส → จับคู่ยอด → เติมเครดิต → แจ้งลูกค้า
- ทั้งหมดใน 3-5 วินาที (real-time)

---

## Database Tables

### sms_checker_devices
อุปกรณ์ Android ที่ลงทะเบียนกับระบบ

| Column | Type | Description |
|--------|------|-------------|
| device_id | VARCHAR | ID เครื่อง (unique) |
| device_name | VARCHAR | ชื่อเครื่อง |
| api_key | VARCHAR(64) | API Key สำหรับ authenticate |
| secret_key | VARCHAR(64) | Secret Key สำหรับ encrypt/sign |
| status | ENUM | active, inactive, blocked |
| last_active_at | TIMESTAMP | เวลาที่ active ล่าสุด |

### sms_payment_notifications
ข้อมูล SMS ที่รับมาจากอุปกรณ์

| Column | Type | Description |
|--------|------|-------------|
| bank | VARCHAR(20) | รหัสธนาคาร (KBANK, SCB, etc.) |
| type | ENUM | credit (ฝาก) / debit (ถอน) |
| amount | DECIMAL(15,2) | จำนวนเงิน |
| account_number | VARCHAR(50) | เลขบัญชี |
| reference_number | VARCHAR(100) | เลข reference |
| status | ENUM | pending, matched, confirmed, rejected, expired |
| matched_transaction_id | BIGINT | ID รายการฝากที่จับคู่ได้ |

### unique_payment_amounts
ยอดเงินทศนิยมที่ไม่ซ้ำ สำหรับจับคู่

| Column | Type | Description |
|--------|------|-------------|
| base_amount | DECIMAL(15,2) | ยอดเงินต้น |
| unique_amount | DECIMAL(15,2) | ยอดจริงที่ต้องโอน |
| decimal_suffix | SMALLINT | ทศนิยม 01-99 |
| transaction_id | BIGINT | FK → deposits.id |
| status | ENUM | reserved, used, expired, cancelled |
| expires_at | TIMESTAMP | หมดอายุเมื่อไร |

### sms_payment_nonces
ป้องกัน replay attack

---

## Integration with Lotto System

### Deposit Flow (ฝากเงินฝั่งลูกค้า)

```
1. User เลือก "ฝากเงินอัตโนมัติ (SMS)"
2. กรอกจำนวนเงินที่ต้องการฝาก (เช่น 500 บาท)
3. Backend เรียก SmsDepositService::createDeposit()
   → สร้าง unique amount (500.37)
   → สร้าง Deposit record (status: pending)
   → return unique amount + QR code/bank info
4. แสดงหน้าจอให้ลูกค้า:
   - ยอดที่ต้องโอน: ฿500.37
   - บัญชีรับเงิน: XXX-X-XXXXX-X
   - QR Code PromptPay
   - Countdown timer (30 นาที)
5. ลูกค้าโอนเงิน
6. SMS เข้าอุปกรณ์ → smschecker ส่งมา Backend
7. Backend จับคู่ → เติม credit ให้ user
8. WebSocket แจ้งเตือน real-time
```

### Services Architecture

```
SmsDepositService (ใหม่ - bridge service)
├── createDeposit()           # สร้างรายการฝาก + unique amount
├── handleSmsMatch()          # เมื่อจับคู่ SMS ได้ → เติมเงิน
├── getDepositStatus()        # เช็คสถานะ real-time
├── cancelDeposit()           # ยกเลิกรายการ
├── getDepositHistory()       # ประวัติฝากเงิน
└── reconcile()               # ตรวจสอบยอดคงค้าง

SmsPaymentService (จาก smschecker plugin)
├── processNotification()     # รับ SMS จากอุปกรณ์
├── decryptPayload()          # ถอดรหัส AES-256-GCM
├── verifySignature()         # ตรวจ HMAC
├── generateUniqueAmount()    # สร้างยอดไม่ซ้ำ
└── cleanup()                 # ลบข้อมูลหมดอายุ

BalanceService (มีอยู่แล้ว)
├── credit()                  # เติมเงินเข้ากระเป๋า
├── debit()                   # หักเงิน
└── getBalance()              # เช็คยอดคงเหลือ
```

---

## Supported Banks (15 ธนาคาร + PromptPay)

| Code | Bank Name |
|------|-----------|
| KBANK | ธนาคารกสิกรไทย (K PLUS) |
| SCB | ธนาคารไทยพาณิชย์ (SCB EASY) |
| KTB | ธนาคารกรุงไทย (Krungthai NEXT) |
| BBL | ธนาคารกรุงเทพ (Bualuang) |
| GSB | ธนาคารออมสิน (MyMo) |
| BAY | ธนาคารกรุงศรีอยุธยา (KMA) |
| TTB | ธนาคารทหารไทยธนชาต (ttb touch) |
| CIMB | ธนาคารซีไอเอ็มบี ไทย |
| KKP | ธนาคารเกียรตินาคินภัทร |
| LHBANK | ธนาคารแลนด์ แอนด์ เฮ้าส์ |
| TISCO | ธนาคารทิสโก้ |
| UOB | ธนาคารยูโอบี |
| ICBC | ธนาคารไอซีบีซี (ไทย) |
| BAAC | ธนาคารเพื่อการเกษตรฯ (ธ.ก.ส.) |
| PROMPTPAY | พร้อมเพย์ |

---

## Admin Features

### SMS Device Management
- เพิ่ม/แก้ไข/ลบอุปกรณ์
- สร้าง QR Code สำหรับ setup อุปกรณ์
- ดูสถานะ online/offline
- Block อุปกรณ์ที่น่าสงสัย

### SMS Notification Monitor
- ดูรายการ SMS ทั้งหมด real-time
- Filter ตามธนาคาร, สถานะ, ช่วงเวลา
- Manual match สำหรับรายการที่ไม่จับคู่อัตโนมัติ
- Export รายงาน

### Auto-Deposit Dashboard
- ยอดฝากอัตโนมัติวันนี้
- อัตราจับคู่สำเร็จ
- รายการที่รอจับคู่
- รายการหมดอายุ
- เวลาเฉลี่ยในการจับคู่

---

## Configuration (.env)

```env
# SMS Checker
SMSCHECKER_TIMESTAMP_TOLERANCE=300
SMSCHECKER_AMOUNT_EXPIRY=30
SMSCHECKER_MAX_PENDING=99
SMSCHECKER_RATE_LIMIT=30
SMSCHECKER_AUTO_CONFIRM=true
SMSCHECKER_NOTIFY_ON_MATCH=true
SMSCHECKER_LOG_LEVEL=info
SMSCHECKER_NONCE_EXPIRY=24

# Receiving Bank Account (บัญชีรับเงิน)
DEPOSIT_BANK_NAME="ธนาคารกสิกรไทย"
DEPOSIT_BANK_CODE=KBANK
DEPOSIT_ACCOUNT_NUMBER=XXX-X-XXXXX-X
DEPOSIT_ACCOUNT_NAME="บริษัท XXX จำกัด"
DEPOSIT_PROMPTPAY_NUMBER=0XXXXXXXXX

# Deposit Limits
DEPOSIT_MIN_AMOUNT=100
DEPOSIT_MAX_AMOUNT=50000
DEPOSIT_DAILY_LIMIT=200000
```

---

## Composer Dependency

```json
{
    "require": {
        "thaiprompt/smschecker-laravel": "^1.0"
    }
}
```

```bash
composer require thaiprompt/smschecker-laravel
php artisan migrate
php artisan smschecker:create-device "Device 1"
```
