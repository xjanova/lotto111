# Database Schema - Risk Management & Profit Control
# ตาราง Database สำหรับระบบควบคุมกำไร

---

## 1. risk_settings (ตั้งค่าความเสี่ยงทั้งระบบ)
```sql
CREATE TABLE risk_settings (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`                   VARCHAR(100) NOT NULL UNIQUE,
    value                   TEXT NOT NULL,
    data_type               ENUM('integer','float','boolean','json') DEFAULT 'float',
    description             VARCHAR(255) NULL,
    updated_by              BIGINT UNSIGNED NULL,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Default data:
INSERT INTO risk_settings (`key`, value, data_type, description) VALUES
('global_target_margin', '15', 'float', 'เป้ากำไร % ของระบบ'),
('global_max_win_rate', '35', 'float', 'Win Rate สูงสุดที่ยอมให้ %'),
('global_min_win_rate', '20', 'float', 'Win Rate ต่ำสุด %'),
('auto_adjust_enabled', 'true', 'boolean', 'เปิด AI ปรับอัตโนมัติ'),
('adjustment_sensitivity', '5', 'integer', 'ความไวในการปรับ (1-10)'),
('max_single_payout', '500000', 'float', 'จ่ายสูงสุดต่อโพย'),
('max_daily_payout', '5000000', 'float', 'จ่ายสูงสุดต่อวัน (ทั้งระบบ)'),
('auto_block_threshold_l1', '50000', 'float', 'ยอดแทงเลขเดียว ระดับ 1 แจ้งเตือน'),
('auto_block_threshold_l2', '100000', 'float', 'ระดับ 2 ลดอัตราจ่าย 20%'),
('auto_block_threshold_l3', '200000', 'float', 'ระดับ 3 ลดอัตราจ่าย 50%'),
('auto_block_threshold_l4', '500000', 'float', 'ระดับ 4 อั้นเลข'),
('anomaly_consecutive_wins', '5', 'integer', 'ถูกติดกี่ครั้งถึงแจ้งเตือน'),
('anomaly_bet_speed', '20', 'integer', 'โพยต่อนาทีที่ถือว่าผิดปกติ'),
('emergency_loss_threshold', '100000', 'float', 'ขาดทุนต่อชั่วโมงที่ alert'),
('fish_win_rate_boost', '5', 'float', '% ที่เพิ่มให้ Fish user'),
('danger_win_rate_cut', '10', 'float', '% ที่ลดจาก Danger user');
```

## 2. user_risk_profiles (โปรไฟล์ความเสี่ยงรายบุคคล)
```sql
CREATE TABLE user_risk_profiles (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL UNIQUE,

    -- Risk classification
    risk_level              ENUM('fish','normal','watch','danger','whale') DEFAULT 'normal',
    risk_score              DECIMAL(5,2) DEFAULT 50.00,      -- 0=ปลอดภัย, 100=อันตราย

    -- Win Rate control
    win_rate_override       DECIMAL(5,2) NULL,                -- NULL = ใช้ค่า global
    rate_adjustment_percent DECIMAL(5,2) DEFAULT 0.00,        -- ปรับอัตราจ่าย +/-  %
    is_auto_adjust          BOOLEAN DEFAULT TRUE,             -- AI ปรับอัตโนมัติ

    -- Bet limits (per-user override)
    max_bet_per_ticket      DECIMAL(12,2) NULL,               -- NULL = ใช้ค่า default
    max_bet_per_number      DECIMAL(12,2) NULL,
    max_payout_per_day      DECIMAL(12,2) NULL,
    max_payout_per_ticket   DECIMAL(12,2) NULL,

    -- Blocked numbers (JSON array)
    blocked_numbers         JSON NULL,                        -- ["123","456","789"]

    -- Cumulative stats (updated real-time)
    total_bet_amount        DECIMAL(14,2) DEFAULT 0.00,
    total_win_amount        DECIMAL(14,2) DEFAULT 0.00,
    total_deposit           DECIMAL(14,2) DEFAULT 0.00,
    total_withdraw          DECIMAL(14,2) DEFAULT 0.00,
    net_profit_for_system   DECIMAL(14,2) DEFAULT 0.00,       -- + = ระบบได้กำไร
    current_win_rate        DECIMAL(5,2) DEFAULT 0.00,
    total_tickets           INT DEFAULT 0,
    total_wins              INT DEFAULT 0,

    -- Today stats
    today_bet_amount        DECIMAL(12,2) DEFAULT 0.00,
    today_win_amount        DECIMAL(12,2) DEFAULT 0.00,
    today_payout            DECIMAL(12,2) DEFAULT 0.00,
    today_tickets           INT DEFAULT 0,

    -- Streak tracking
    consecutive_wins        INT DEFAULT 0,
    consecutive_losses      INT DEFAULT 0,
    last_bet_at             TIMESTAMP NULL,
    bets_per_minute         DECIMAL(5,2) DEFAULT 0.00,

    -- Admin notes
    admin_note              TEXT NULL,
    last_reviewed_by        BIGINT UNSIGNED NULL,
    last_reviewed_at        TIMESTAMP NULL,

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (last_reviewed_by) REFERENCES users(id),
    INDEX idx_risk_level (risk_level),
    INDEX idx_net_profit (net_profit_for_system),
    INDEX idx_win_rate (current_win_rate),
    INDEX idx_risk_score (risk_score)
);
```

## 3. profit_snapshots (สแนปช็อตกำไรรายช่วงเวลา)
```sql
CREATE TABLE profit_snapshots (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    period_type             ENUM('hourly','daily','weekly','monthly') NOT NULL,
    period_start            TIMESTAMP NOT NULL,
    period_end              TIMESTAMP NOT NULL,

    -- Revenue
    total_bet_amount        DECIMAL(14,2) DEFAULT 0.00,
    total_payout            DECIMAL(14,2) DEFAULT 0.00,
    total_deposit           DECIMAL(14,2) DEFAULT 0.00,
    total_withdraw          DECIMAL(14,2) DEFAULT 0.00,
    total_commission_paid   DECIMAL(14,2) DEFAULT 0.00,

    -- Profit
    gross_profit            DECIMAL(14,2) DEFAULT 0.00,       -- bet - payout
    net_profit              DECIMAL(14,2) DEFAULT 0.00,       -- gross - commission - expenses
    margin_percent          DECIMAL(5,2) DEFAULT 0.00,

    -- User metrics
    active_users            INT DEFAULT 0,
    new_users               INT DEFAULT 0,
    total_tickets           INT DEFAULT 0,
    total_wins              INT DEFAULT 0,
    avg_win_rate            DECIMAL(5,2) DEFAULT 0.00,

    -- By lottery type (JSON)
    breakdown_by_type       JSON NULL,
    -- {"government": {"bet": 1000, "payout": 700}, "yeekee": {...}}

    -- By bet type (JSON)
    breakdown_by_bet        JSON NULL,
    -- {"three_top": {"bet": 500, "payout": 300}, "two_bottom": {...}}

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_period (period_type, period_start),
    UNIQUE idx_unique_period (period_type, period_start)
);
```

## 4. number_exposure (ความเสี่ยงต่อเลข - real-time)
```sql
CREATE TABLE number_exposure (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_round_id        BIGINT UNSIGNED NOT NULL,
    bet_type_id             BIGINT UNSIGNED NOT NULL,
    number                  VARCHAR(10) NOT NULL,

    -- Exposure data
    total_bet_amount        DECIMAL(12,2) DEFAULT 0.00,       -- ยอดแทงรวม
    bet_count               INT DEFAULT 0,                    -- จำนวนคนแทง
    potential_payout        DECIMAL(14,2) DEFAULT 0.00,       -- ถ้าออก จ่ายเท่าไร
    effective_rate          DECIMAL(10,2) DEFAULT 0.00,       -- อัตราจ่ายจริง (หลังปรับ)

    -- Control
    is_blocked              BOOLEAN DEFAULT FALSE,            -- อั้นแล้วหรือยัง
    rate_reduction_percent  DECIMAL(5,2) DEFAULT 0.00,        -- ลดอัตราจ่ายกี่ %
    risk_level              ENUM('safe','warning','danger','critical') DEFAULT 'safe',

    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (lottery_round_id) REFERENCES lottery_rounds(id) ON DELETE CASCADE,
    FOREIGN KEY (bet_type_id) REFERENCES bet_types(id),
    UNIQUE idx_round_bet_number (lottery_round_id, bet_type_id, number),
    INDEX idx_risk (risk_level),
    INDEX idx_exposure (total_bet_amount DESC)
);
```

## 5. risk_alerts (แจ้งเตือนความเสี่ยง)
```sql
CREATE TABLE risk_alerts (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alert_type              ENUM(
                                'consecutive_wins',
                                'high_bet_single_number',
                                'suspected_collusion',
                                'suspected_bot',
                                'system_loss_hourly',
                                'new_user_big_win',
                                'pattern_detected',
                                'exposure_critical',
                                'margin_below_target',
                                'unusual_deposit_pattern'
                            ) NOT NULL,
    severity                ENUM('info','warning','critical','emergency') DEFAULT 'warning',
    user_id                 BIGINT UNSIGNED NULL,             -- NULL = system-wide alert
    lottery_round_id        BIGINT UNSIGNED NULL,

    title                   VARCHAR(255) NOT NULL,
    description             TEXT NOT NULL,
    data                    JSON NULL,                        -- ข้อมูลเพิ่มเติม

    -- Status
    status                  ENUM('new','acknowledged','resolved','dismissed') DEFAULT 'new',
    acknowledged_by         BIGINT UNSIGNED NULL,
    acknowledged_at         TIMESTAMP NULL,
    resolved_by             BIGINT UNSIGNED NULL,
    resolved_at             TIMESTAMP NULL,
    resolution_note         TEXT NULL,

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (lottery_round_id) REFERENCES lottery_rounds(id),
    FOREIGN KEY (acknowledged_by) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_type (alert_type),
    INDEX idx_created (created_at DESC)
);
```

## 6. rate_adjustments_log (ประวัติการปรับอัตรา)
```sql
CREATE TABLE rate_adjustments_log (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    target_type             ENUM('global','user','lottery_type','number') NOT NULL,
    target_id               BIGINT UNSIGNED NULL,              -- user_id / lottery_type_id
    adjusted_by             ENUM('ai','admin') NOT NULL,
    admin_id                BIGINT UNSIGNED NULL,

    -- What changed
    field_changed           VARCHAR(50) NOT NULL,              -- 'win_rate','rate_adjustment','blocked_number'
    old_value               TEXT NULL,
    new_value               TEXT NOT NULL,
    reason                  TEXT NULL,

    -- Context at time of adjustment
    context_data            JSON NULL,
    -- {"current_margin": 12, "user_win_rate": 45, "trigger": "consecutive_wins"}

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id) REFERENCES users(id),
    INDEX idx_target (target_type, target_id),
    INDEX idx_adjusted_by (adjusted_by),
    INDEX idx_created (created_at DESC)
);
```

## 7. user_daily_stats (สถิติรายวันรายบุคคล)
```sql
CREATE TABLE user_daily_stats (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL,
    stat_date               DATE NOT NULL,

    bet_count               INT DEFAULT 0,
    bet_amount              DECIMAL(12,2) DEFAULT 0.00,
    win_count               INT DEFAULT 0,
    win_amount              DECIMAL(12,2) DEFAULT 0.00,
    payout_amount           DECIMAL(12,2) DEFAULT 0.00,
    deposit_amount          DECIMAL(12,2) DEFAULT 0.00,
    withdraw_amount         DECIMAL(12,2) DEFAULT 0.00,
    net_for_system          DECIMAL(12,2) DEFAULT 0.00,       -- + = ระบบได้
    win_rate                DECIMAL(5,2) DEFAULT 0.00,

    -- Breakdown
    bet_by_type             JSON NULL,
    -- {"three_top": 5000, "two_bottom": 3000}

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE idx_user_date (user_id, stat_date),
    INDEX idx_date (stat_date),
    INDEX idx_net (net_for_system)
);
```

## 8. system_real_time_stats (สถิติ real-time ทั้งระบบ)
```sql
CREATE TABLE system_realtime_stats (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stat_key                VARCHAR(50) NOT NULL UNIQUE,
    stat_value              DECIMAL(14,2) DEFAULT 0.00,
    metadata                JSON NULL,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default data:
INSERT INTO system_realtime_stats (stat_key, stat_value) VALUES
('today_total_bet', 0),
('today_total_payout', 0),
('today_gross_profit', 0),
('today_margin_percent', 0),
('today_active_users', 0),
('today_new_users', 0),
('today_total_deposit', 0),
('today_total_withdraw', 0),
('today_total_tickets', 0),
('today_total_wins', 0),
('today_avg_win_rate', 0),
('current_open_exposure', 0),
('current_worst_case_payout', 0),
('lifetime_total_profit', 0);
```
