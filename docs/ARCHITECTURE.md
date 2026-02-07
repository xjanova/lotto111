# Architecture Document
# ระบบหวยออนไลน์ - Lotto Platform

## 1. System Architecture

```
                    ┌─────────────┐
                    │   Browser   │
                    │  (Tailwind  │
                    │  Alpine.js) │
                    └──────┬──────┘
                           │ HTTPS
                    ┌──────▼──────┐
                    │   Nginx     │
                    │  (Reverse   │
                    │   Proxy)    │
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐
              │            │            │
       ┌──────▼──────┐ ┌──▼───┐ ┌──────▼──────┐
       │   Laravel    │ │ Reverb│ │   Laravel   │
       │   App (Web)  │ │ (WS)  │ │   Queue     │
       │   Livewire   │ │       │ │   Worker    │
       └──────┬──────┘ └──┬───┘ └──────┬──────┘
              │            │            │
              └────────────┼────────────┘
                           │
              ┌────────────┼────────────┐
              │            │            │
       ┌──────▼──────┐ ┌──▼───┐ ┌──────▼──────┐
       │   MySQL      │ │Redis │ │  Storage    │
       │   Database   │ │Cache │ │  (S3/Local) │
       └─────────────┘ └──────┘ └─────────────┘
```

## 2. Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | 8.3+ |
| Framework | Laravel | 12.x |
| Frontend Engine | Blade + Livewire 3 | Latest |
| CSS | Tailwind CSS | 4.x |
| JS | Alpine.js | 3.x |
| Database | MySQL | 8.0+ |
| Cache | Redis | 7.x |
| Queue | Redis (Laravel Queue) | - |
| WebSocket | Laravel Reverb | Latest |
| Auth | Laravel Sanctum | Latest |
| File Storage | Local / S3 | - |
| SMS | ThaiSMS API | - |
| Server | Nginx + PHP-FPM | - |

## 3. Directory Structure

```
lotto/
├── app/
│   ├── Actions/                    # Single-purpose action classes
│   │   ├── Auth/
│   │   │   ├── RegisterUser.php
│   │   │   ├── SendOtp.php
│   │   │   └── VerifyOtp.php
│   │   ├── Lottery/
│   │   │   ├── PlaceBet.php
│   │   │   ├── ProcessResult.php
│   │   │   └── CalculateWinnings.php
│   │   ├── Finance/
│   │   │   ├── ProcessDeposit.php
│   │   │   ├── ProcessWithdrawal.php
│   │   │   └── CalculateCommission.php
│   │   └── Gamification/
│   │       ├── AwardXp.php
│   │       ├── ProcessMission.php
│   │       └── PerformSpin.php
│   │
│   ├── Enums/                      # PHP Enums
│   │   ├── UserRole.php
│   │   ├── UserStatus.php
│   │   ├── LotteryCategory.php
│   │   ├── RoundStatus.php
│   │   ├── TicketStatus.php
│   │   ├── TransactionType.php
│   │   ├── DepositMethod.php
│   │   ├── DepositStatus.php
│   │   ├── VipLevel.php
│   │   ├── MissionType.php
│   │   ├── BadgeRarity.php
│   │   ├── RiskLevel.php
│   │   ├── AlertSeverity.php
│   │   └── SmsDepositStatus.php
│   │
│   ├── Console/Commands/            # Artisan Commands
│   │   ├── SmsDepositReconcile.php
│   │   └── SmsCheckerCreateDevice.php
│   │
│   ├── Events/                     # Event classes
│   │   ├── BetPlaced.php
│   │   ├── ResultAnnounced.php
│   │   ├── DepositApproved.php
│   │   ├── WithdrawalCompleted.php
│   │   ├── DepositMatched.php       # SMS matched to deposit
│   │   └── DepositCredited.php      # Credit added to wallet
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/               # API Controllers (Sanctum)
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── LotteryController.php
│   │   │   │   ├── TicketController.php
│   │   │   │   ├── FinanceController.php
│   │   │   │   ├── DepositController.php        # SMS Auto-Deposit API
│   │   │   │   └── V1/
│   │   │   │       └── SmsPaymentController.php # smschecker Device API
│   │   │   ├── Admin/             # Admin Controllers
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── MemberController.php
│   │   │   │   ├── LotteryManageController.php
│   │   │   │   ├── FinanceManageController.php
│   │   │   │   ├── SettingsController.php
│   │   │   │   ├── SmsDepositController.php     # SMS Deposit Admin
│   │   │   │   └── RiskControlController.php    # Risk Management Admin
│   │   │   └── App/               # Member Web Controllers
│   │   │       ├── DashboardController.php
│   │   │       ├── LotteryController.php
│   │   │       └── ProfileController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── EnsurePhoneVerified.php
│   │   │   ├── CheckUserStatus.php
│   │   │   ├── AdminOnly.php
│   │   │   └── VerifySmsCheckerDevice.php  # smschecker auth
│   │   │
│   │   └── Requests/              # Form Requests
│   │       ├── Auth/
│   │       │   ├── RegisterRequest.php
│   │       │   ├── LoginRequest.php
│   │       │   └── OtpRequest.php
│   │       ├── Lottery/
│   │       │   └── PlaceBetRequest.php
│   │       └── Finance/
│   │           ├── DepositRequest.php
│   │           └── WithdrawRequest.php
│   │
│   ├── Jobs/                       # Queue Jobs
│   │   ├── SendOtpSms.php
│   │   ├── ProcessLotteryResult.php
│   │   ├── CalculateTicketResults.php
│   │   ├── AutoApproveDeposit.php
│   │   └── SendNotification.php
│   │
│   ├── Listeners/                  # Event Listeners
│   │   ├── DeductBalance.php
│   │   ├── CreditWinnings.php
│   │   ├── RecordTransaction.php
│   │   └── CalculateAffiliateCommission.php
│   │
│   ├── Livewire/                   # Livewire Components
│   │   ├── Auth/
│   │   │   ├── LoginForm.php
│   │   │   ├── RegisterForm.php
│   │   │   └── OtpVerification.php
│   │   ├── Dashboard/
│   │   │   ├── BalanceCard.php
│   │   │   └── QuickMenu.php
│   │   ├── Lottery/
│   │   │   ├── LotteryList.php
│   │   │   ├── BettingBoard.php
│   │   │   ├── BetCart.php
│   │   │   ├── RateTable.php
│   │   │   ├── NumberPad.php
│   │   │   └── CountdownTimer.php
│   │   ├── Yeekee/
│   │   │   ├── YeekeeRounds.php
│   │   │   └── YeekeeBetting.php
│   │   ├── Result/
│   │   │   └── ResultBoard.php
│   │   ├── Ticket/
│   │   │   ├── TicketList.php
│   │   │   └── TicketDetail.php
│   │   ├── Finance/
│   │   │   ├── DepositForm.php
│   │   │   ├── WithdrawForm.php
│   │   │   ├── TransactionHistory.php
│   │   │   └── FinancialReport.php
│   │   ├── NumberSet/
│   │   │   ├── NumberSetList.php
│   │   │   └── NumberSetForm.php
│   │   ├── Affiliate/
│   │   │   ├── AffiliateDashboard.php
│   │   │   └── AffiliateMembers.php
│   │   ├── Chat/
│   │   │   └── ChatBox.php
│   │   └── Profile/
│   │       ├── ProfileInfo.php
│   │       └── ChangePassword.php
│   │
│   ├── Models/                     # Eloquent Models
│   │   ├── User.php
│   │   ├── UserBankAccount.php
│   │   ├── OtpVerification.php
│   │   ├── LotteryType.php
│   │   ├── LotteryRound.php
│   │   ├── LotteryResult.php
│   │   ├── BetType.php
│   │   ├── BetTypeRate.php
│   │   ├── BetLimit.php
│   │   ├── Ticket.php
│   │   ├── TicketItem.php
│   │   ├── Deposit.php
│   │   ├── Withdrawal.php
│   │   ├── Transaction.php
│   │   ├── NumberSet.php
│   │   ├── NumberSetItem.php
│   │   ├── AffiliateCommission.php
│   │   ├── Message.php
│   │   ├── Notification.php
│   │   ├── Setting.php
│   │   └── AdminLog.php
│   │
│   ├── Notifications/              # Notification classes
│   │   ├── OtpNotification.php
│   │   ├── DepositApprovedNotification.php
│   │   ├── WithdrawalCompletedNotification.php
│   │   └── ResultAnnouncedNotification.php
│   │
│   ├── Policies/                   # Authorization Policies
│   │   ├── TicketPolicy.php
│   │   ├── NumberSetPolicy.php
│   │   └── WithdrawalPolicy.php
│   │
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   │
│   └── Services/                   # Business Logic Services
│       ├── OtpService.php
│       ├── SmsService.php
│       ├── LotteryService.php
│       ├── BettingService.php
│       ├── ResultService.php
│       ├── BalanceService.php
│       ├── DepositService.php
│       ├── WithdrawalService.php
│       ├── AffiliateService.php
│       ├── GamificationService.php      # VIP, Missions, Spin, Badges
│       ├── NotificationService.php      # Smart multi-channel notifications
│       ├── GroupPlayService.php         # Syndicate/group betting
│       ├── UserStatisticsService.php    # Personal analytics
│       ├── AI/
│       │   └── NumberAnalysisService.php # AI Smart Number analysis
│       ├── Deposit/                     # SMS Auto-Deposit System
│       │   ├── SmsDepositService.php         # Bridge: deposit ↔ smschecker
│       │   └── SmsPaymentProcessorService.php # SMS processing & matching
│       └── Risk/                        # Risk Management Engine
│           └── RiskEngineService.php     # AI P&L control, user profiling
│
├── config/
│   ├── lottery.php                 # Lottery-specific config
│   ├── sms.php                     # SMS API config
│   ├── payment.php                 # Payment config
│   └── smschecker.php              # SMS Auto-Deposit config
│
├── database/
│   ├── factories/
│   ├── migrations/
│   │   ├── 0001_create_users_table.php
│   │   ├── 0002_create_user_bank_accounts_table.php
│   │   ├── 0003_create_otp_verifications_table.php
│   │   ├── 0004_create_lottery_types_table.php
│   │   ├── 0005_create_lottery_rounds_table.php
│   │   ├── 0006_create_lottery_results_table.php
│   │   ├── 0007_create_bet_types_table.php
│   │   ├── 0008_create_bet_type_rates_table.php
│   │   ├── 0009_create_bet_limits_table.php
│   │   ├── 0010_create_tickets_table.php
│   │   ├── 0011_create_ticket_items_table.php
│   │   ├── 0012_create_deposits_table.php
│   │   ├── 0013_create_withdrawals_table.php
│   │   ├── 0014_create_transactions_table.php
│   │   ├── 0015_create_number_sets_table.php
│   │   ├── 0016_create_number_set_items_table.php
│   │   ├── 0017_create_affiliate_commissions_table.php
│   │   ├── 0018_create_messages_table.php
│   │   ├── 0019_create_notifications_table.php
│   │   ├── 0020_create_settings_table.php
│   │   └── 0021_create_admin_logs_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── LotteryTypeSeeder.php
│       ├── BetTypeSeeder.php
│       ├── BetTypeRateSeeder.php
│       ├── SettingsSeeder.php
│       └── AdminUserSeeder.php
│
├── resources/
│   ├── css/
│   │   └── app.css                 # Tailwind imports
│   ├── js/
│   │   ├── app.js                  # Alpine.js + Echo
│   │   └── countdown.js            # Countdown timer utility
│   └── views/
│       ├── components/             # Blade Components
│       │   ├── layouts/
│       │   │   ├── app.blade.php       # Member layout
│       │   │   ├── admin.blade.php     # Admin layout
│       │   │   └── guest.blade.php     # Guest layout (login/register)
│       │   ├── ui/
│       │   │   ├── button.blade.php
│       │   │   ├── card.blade.php
│       │   │   ├── modal.blade.php
│       │   │   ├── badge.blade.php
│       │   │   ├── countdown.blade.php
│       │   │   └── toast.blade.php
│       │   └── navigation/
│       │       ├── navbar.blade.php
│       │       ├── sidebar.blade.php
│       │       └── quick-menu.blade.php
│       │
│       ├── livewire/               # Livewire views
│       │   ├── auth/
│       │   ├── dashboard/
│       │   ├── lottery/
│       │   ├── yeekee/
│       │   ├── result/
│       │   ├── ticket/
│       │   ├── finance/
│       │   ├── number-set/
│       │   ├── affiliate/
│       │   ├── chat/
│       │   └── profile/
│       │
│       ├── pages/                  # Full page views
│       │   ├── app/
│       │   │   ├── dashboard.blade.php
│       │   │   ├── lottery.blade.php
│       │   │   ├── betting.blade.php
│       │   │   ├── yeekee.blade.php
│       │   │   ├── set-lottery.blade.php
│       │   │   ├── results.blade.php
│       │   │   ├── tickets.blade.php
│       │   │   ├── number-sets.blade.php
│       │   │   ├── deposit.blade.php
│       │   │   ├── withdraw.blade.php
│       │   │   ├── transactions.blade.php
│       │   │   ├── financial-report.blade.php
│       │   │   ├── affiliate.blade.php
│       │   │   ├── inbox.blade.php
│       │   │   ├── topup.blade.php
│       │   │   └── profile.blade.php
│       │   ├── admin/
│       │   │   ├── dashboard.blade.php
│       │   │   ├── members/
│       │   │   ├── lottery/
│       │   │   ├── finance/
│       │   │   └── settings/
│       │   └── auth/
│       │       ├── login.blade.php
│       │       ├── register.blade.php
│       │       └── verify-otp.blade.php
│       │
│       └── emails/                 # Email templates (optional)
│
├── routes/
│   ├── web.php                     # Web routes (Livewire pages)
│   ├── api.php                     # API routes (Sanctum)
│   ├── admin.php                   # Admin routes
│   └── channels.php               # WebSocket channels
│
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Lottery/
│   │   ├── Finance/
│   │   └── Affiliate/
│   └── Unit/
│       ├── Services/
│       └── Actions/
│
├── docs/                           # Documentation
│   ├── PRD.md                     # Product Requirements Document
│   ├── DATABASE.md                # Core Database Schema (21 tables)
│   ├── DATABASE_RISK_CONTROL.md   # Risk Management Schema (8 tables)
│   ├── DATABASE_SMS_DEPOSIT.md    # SMS Deposit Schema (4 tables + alter)
│   ├── ARCHITECTURE.md            # System Architecture
│   ├── SETUP.md                   # Installation Guide
│   ├── ADVANCED_FEATURES.md       # 12 Advanced Features
│   ├── ADMIN_RISK_CONTROL.md      # Risk Control Admin Docs
│   ├── SMS_AUTO_DEPOSIT.md        # SMS Auto-Deposit Docs
│   └── API.md
│
├── composer.json
├── package.json
├── tailwind.config.js
├── vite.config.js
└── .env.example
```

## 4. Design Patterns

### 4.1 Action Pattern
ใช้สำหรับ business logic ที่ซับซ้อน ให้แต่ละ class ทำงานเดียว

```php
// app/Actions/Lottery/PlaceBet.php
class PlaceBet
{
    public function execute(User $user, LotteryRound $round, array $bets): Ticket
    {
        // 1. Validate bets
        // 2. Check balance
        // 3. Create ticket + items
        // 4. Deduct balance
        // 5. Record transaction
        // 6. Dispatch event
    }
}
```

### 4.2 Service Pattern
ใช้สำหรับ business logic ที่ reuse บ่อย

```php
// app/Services/BalanceService.php
class BalanceService
{
    public function deduct(User $user, float $amount, string $description): Transaction;
    public function credit(User $user, float $amount, string $description): Transaction;
    public function getBalance(User $user): float;
}
```

### 4.3 Event-Driven
ใช้ Events + Listeners สำหรับ side effects

```
BetPlaced → DeductBalance, RecordTransaction, CalculateAffiliateCommission
ResultAnnounced → CalculateTicketResults, CreditWinnings, SendNotification
DepositApproved → CreditBalance, RecordTransaction, SendNotification
```

### 4.4 Repository Pattern (Optional)
ใช้ถ้าต้องการ abstract database layer สำหรับ testing

## 5. Caching Strategy

| Data | Cache Duration | Key Pattern |
|------|---------------|-------------|
| Lottery types list | 1 hour | `lottery:types` |
| Active rounds | 1 min | `lottery:rounds:active` |
| Bet type rates | 1 hour | `lottery:rates:{typeId}` |
| User balance | 5 min | `user:{id}:balance` |
| Results | 1 day | `results:{date}` |
| Settings | 1 hour | `settings:{key}` |

## 6. Queue Jobs Priority

| Queue | Jobs | Workers |
|-------|------|---------|
| `high` | SendOtpSms, AutoApproveDeposit | 2 |
| `default` | ProcessLotteryResult, CalculateTicketResults | 2 |
| `low` | SendNotification, CalculateAffiliateCommission | 1 |

## 7. WebSocket Channels

| Channel | Purpose | Broadcast Event |
|---------|---------|----------------|
| `private-user.{id}` | Balance update, notifications | BalanceUpdated |
| `lottery.{roundId}` | Countdown, round status | RoundStatusChanged |
| `results` | New result announced | ResultAnnounced |
| `admin` | New deposit/withdrawal | TransactionCreated |
| `private-deposit.{id}` | SMS deposit status update | DepositMatched |
| `private-user.{id}.wallet` | Wallet credited | DepositCredited |
| `private-admin.sms-deposits` | SMS deposit monitoring | DepositMatched, DepositCredited |
| `private-admin.risk` | Risk alerts, P&L updates | RiskAlert, MarginUpdate |

## 8. Security Measures

1. **Authentication**: Laravel Sanctum (SPA tokens)
2. **Phone Verification**: OTP via SMS (mandatory)
3. **Rate Limiting**: Login (5/min), OTP (3/min), API (60/min)
4. **CSRF**: Blade forms auto-protected
5. **XSS**: Blade auto-escaping + CSP headers
6. **SQL Injection**: Eloquent ORM parameterized queries
7. **Admin Access**: IP whitelist middleware
8. **Audit Trail**: admin_logs table for all admin actions
9. **Encryption**: AES-256 for sensitive data at rest
10. **HTTPS**: Forced SSL in production
11. **SMS Checker**: AES-256-GCM encryption + HMAC-SHA256 signing
12. **Nonce Protection**: Replay attack prevention for SMS API
13. **Device Auth**: API Key based auth for smschecker devices

## 9. SMS Auto-Deposit Architecture

```
┌──────────────┐     AES-256-GCM      ┌──────────────┐
│  Android App │────encrypted SMS────→│ Laravel API  │
│ (smschecker) │  + HMAC-SHA256 sign  │              │
└──────────────┘                       │ POST /api/v1/│
                                       │ sms-payment/ │
       ┌───────────────────────────────│ notify       │
       │                               └──────┬───────┘
       │                                      │
       │  ┌──────────────────────┐            │ Decrypt & Validate
       │  │ unique_payment_amounts│◄───match──│
       │  │ (500.37 = reserved)  │            │
       │  └──────────┬───────────┘            │
       │             │                        │
       │  ┌──────────▼───────────┐  ┌────────▼────────┐
       │  │     deposits         │  │  sms_payment_   │
       │  │  (status → credited) │  │  notifications  │
       │  └──────────┬───────────┘  └─────────────────┘
       │             │
       │  ┌──────────▼───────────┐
       │  │   BalanceService     │
       │  │   credit(user, amt)  │
       │  └──────────┬───────────┘
       │             │
       │  ┌──────────▼───────────┐
       │  │   WebSocket Event    │──→ User notification (real-time)
       │  │   DepositCredited    │──→ Admin dashboard update
       │  └──────────────────────┘
       │
       └── Scheduler: sms-deposit:reconcile (every 5 min)
           ├── Expire old deposits
           ├── Cleanup nonces
           └── Detect orphaned SMS
```

## 10. Risk Management Architecture

```
┌─────────────────────────────────────────────────────┐
│                 RiskEngineService                     │
│                                                       │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────┐│
│  │ getEffective │  │ validateBet  │  │ afterWin     ││
│  │ Rate(user)   │  │ (pre-check)  │  │ (post-proc)  ││
│  └──────┬──────┘  └──────┬───────┘  └──────┬───────┘│
│         │                │                  │         │
│  ┌──────▼──────────────▼─────────────────▼─────────┐│
│  │           user_risk_profiles                     ││
│  │  risk_level | win_rate_override | rate_adjust    ││
│  │  blocked_numbers | cumulative_stats              ││
│  └─────────────────────────────────────────────────┘│
│                                                       │
│  ┌───────────────┐  ┌──────────────┐                 │
│  │ runAutoBalance │  │ number_      │                 │
│  │ (every 5 min)  │  │ exposure     │                 │
│  │ AI algorithm   │  │ (real-time)  │                 │
│  └───────────────┘  └──────────────┘                 │
│                                                       │
│  ┌───────────────┐  ┌──────────────┐                 │
│  │ risk_alerts    │  │ profit_      │                 │
│  │ (anomaly)      │  │ snapshots    │                 │
│  └───────────────┘  └──────────────┘                 │
└─────────────────────────────────────────────────────┘

Scheduler:
  - runAutoBalance()    → ทุก 5 นาที
  - resetDailyStats()   → ทุกวัน 00:00
  - profitSnapshot()    → ทุกชั่วโมง / ทุกวัน
```
