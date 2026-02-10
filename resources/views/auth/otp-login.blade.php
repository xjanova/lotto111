<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>เข้าสู่ระบบ - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Prompt', 'Noto Sans Thai', sans-serif; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 2.5rem; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .title { color: #f6c90e; font-size: 1.8rem; font-weight: 700; text-align: center; margin-bottom: 0.5rem; }
        .subtitle { color: rgba(255,255,255,0.6); text-align: center; margin-bottom: 2rem; font-size: 0.9rem; }
        .input-group { position: relative; margin-bottom: 1.5rem; }
        .input-group label { color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 0.5rem; display: block; }
        .input-group input { width: 100%; padding: 14px 16px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; color: #fff; font-size: 1.1rem; outline: none; transition: border-color 0.3s; box-sizing: border-box; }
        .input-group input:focus { border-color: #f6c90e; }
        .input-group input::placeholder { color: rgba(255,255,255,0.3); }
        .btn { width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #f6c90e, #e8b30e); color: #1a1a2e; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(246,201,14,0.3); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .otp-inputs { display: flex; gap: 8px; justify-content: center; margin-bottom: 1.5rem; }
        .otp-inputs input { width: 48px; height: 56px; text-align: center; font-size: 1.4rem; font-weight: 700; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; color: #fff; outline: none; }
        .otp-inputs input:focus { border-color: #f6c90e; }
        .error { color: #ff6b6b; font-size: 0.85rem; margin-top: 0.5rem; text-align: center; display: none; }
        .success { color: #51cf66; font-size: 0.85rem; margin-top: 0.5rem; text-align: center; display: none; }
        .timer { color: rgba(255,255,255,0.5); text-align: center; font-size: 0.85rem; margin-top: 1rem; }
        .step { display: none; }
        .step.active { display: block; }
        .logo { text-align: center; margin-bottom: 1rem; font-size: 3rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">🎰</div>
        <h1 class="title">{{ config('app.name') }}</h1>
        <p class="subtitle">สมัครสมาชิก / เข้าสู่ระบบด้วย OTP</p>

        <!-- Step 1: Phone Input -->
        <div id="step-phone" class="step active">
            <div class="input-group">
                <label>เบอร์โทรศัพท์</label>
                <input type="tel" id="phone" placeholder="0812345678" maxlength="10" pattern="[0-9]*" inputmode="numeric">
            </div>
            <button id="btn-send-otp" class="btn btn-primary" onclick="handleSendOtp()">
                ส่งรหัส OTP
            </button>
            <div id="error-phone" class="error"></div>
        </div>

        <!-- Step 2: OTP Input -->
        <div id="step-otp" class="step">
            <p style="color: rgba(255,255,255,0.6); text-align: center; margin-bottom: 1rem; font-size: 0.9rem;">
                กรอกรหัส OTP 6 หลักที่ส่งไปยัง<br>
                <span id="display-phone" style="color: #f6c90e; font-weight: 600;"></span>
            </p>
            <div class="otp-inputs" id="otp-inputs">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="0">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
            </div>
            <button id="btn-verify-otp" class="btn btn-primary" onclick="handleVerifyOtp()">
                ยืนยัน OTP
            </button>
            <div id="error-otp" class="error"></div>
            <div id="success-otp" class="success"></div>
            <div class="timer" id="timer"></div>
            <p style="text-align: center; margin-top: 1rem;">
                <a href="#" onclick="resetToPhone()" style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.85rem;">
                    เปลี่ยนเบอร์โทร
                </a>
            </p>
        </div>
    </div>

    <script type="module">
        import { setupRecaptcha, sendOtp, verifyOtp, loginWithToken } from '/resources/js/firebase.js';

        let countdown = null;

        // Auto-setup on DOM ready
        window.addEventListener('DOMContentLoaded', () => {
            setupRecaptcha('btn-send-otp');
            setupOtpInputs();
        });

        window.handleSendOtp = async function() {
            const phone = document.getElementById('phone').value.trim();
            const errorEl = document.getElementById('error-phone');
            const btn = document.getElementById('btn-send-otp');

            errorEl.style.display = 'none';

            if (!/^0[689]\d{8}$/.test(phone)) {
                errorEl.textContent = 'กรุณากรอกเบอร์มือถือให้ถูกต้อง (เช่น 0812345678)';
                errorEl.style.display = 'block';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'กำลังส่ง OTP...';

            try {
                await sendOtp(phone);
                document.getElementById('display-phone').textContent = phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
                document.getElementById('step-phone').classList.remove('active');
                document.getElementById('step-otp').classList.add('active');
                document.querySelector('#otp-inputs input').focus();
                startTimer(60);
            } catch (error) {
                console.error('Send OTP error:', error);
                if (error.code === 'auth/too-many-requests') {
                    errorEl.textContent = 'ส่ง OTP บ่อยเกินไป กรุณารอสักครู่';
                } else if (error.code === 'auth/invalid-phone-number') {
                    errorEl.textContent = 'เบอร์โทรศัพท์ไม่ถูกต้อง';
                } else {
                    errorEl.textContent = 'ไม่สามารถส่ง OTP ได้ กรุณาลองใหม่';
                }
                errorEl.style.display = 'block';
                setupRecaptcha('btn-send-otp');
            } finally {
                btn.disabled = false;
                btn.textContent = 'ส่งรหัส OTP';
            }
        };

        window.handleVerifyOtp = async function() {
            const inputs = document.querySelectorAll('#otp-inputs input');
            const otp = Array.from(inputs).map(i => i.value).join('');
            const errorEl = document.getElementById('error-otp');
            const successEl = document.getElementById('success-otp');
            const btn = document.getElementById('btn-verify-otp');

            errorEl.style.display = 'none';
            successEl.style.display = 'none';

            if (otp.length !== 6) {
                errorEl.textContent = 'กรุณากรอก OTP ให้ครบ 6 หลัก';
                errorEl.style.display = 'block';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'กำลังตรวจสอบ...';

            try {
                const idToken = await verifyOtp(otp);
                successEl.textContent = 'OTP ถูกต้อง กำลังเข้าสู่ระบบ...';
                successEl.style.display = 'block';

                const result = await loginWithToken(idToken);

                if (result.success) {
                    localStorage.setItem('auth_token', result.token);
                    localStorage.setItem('user', JSON.stringify(result.user));
                    successEl.textContent = result.message;
                    window.location.href = '/';
                } else {
                    errorEl.textContent = result.message || 'เข้าสู่ระบบไม่สำเร็จ';
                    errorEl.style.display = 'block';
                    successEl.style.display = 'none';
                }
            } catch (error) {
                console.error('Verify OTP error:', error);
                if (error.code === 'auth/invalid-verification-code') {
                    errorEl.textContent = 'รหัส OTP ไม่ถูกต้อง';
                } else if (error.code === 'auth/code-expired') {
                    errorEl.textContent = 'รหัส OTP หมดอายุ กรุณาส่งใหม่';
                } else {
                    errorEl.textContent = 'ตรวจสอบ OTP ไม่สำเร็จ กรุณาลองใหม่';
                }
                errorEl.style.display = 'block';
                successEl.style.display = 'none';
            } finally {
                btn.disabled = false;
                btn.textContent = 'ยืนยัน OTP';
            }
        };

        window.resetToPhone = function() {
            document.getElementById('step-otp').classList.remove('active');
            document.getElementById('step-phone').classList.add('active');
            document.querySelectorAll('#otp-inputs input').forEach(i => i.value = '');
            if (countdown) clearInterval(countdown);
            setupRecaptcha('btn-send-otp');
        };

        function startTimer(seconds) {
            const timerEl = document.getElementById('timer');
            let remaining = seconds;
            timerEl.textContent = `ส่ง OTP ใหม่ได้ใน ${remaining} วินาที`;

            if (countdown) clearInterval(countdown);
            countdown = setInterval(() => {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(countdown);
                    timerEl.innerHTML = '<a href="#" onclick="handleSendOtp()" style="color: #f6c90e; text-decoration: none;">ส่ง OTP อีกครั้ง</a>';
                } else {
                    timerEl.textContent = `ส่ง OTP ใหม่ได้ใน ${remaining} วินาที`;
                }
            }, 1000);
        }

        function setupOtpInputs() {
            const inputs = document.querySelectorAll('#otp-inputs input');
            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    const val = e.target.value;
                    if (val && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    if (index === inputs.length - 1 && val) {
                        handleVerifyOtp();
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !input.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });

                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text').trim();
                    if (/^\d{6}$/.test(paste)) {
                        inputs.forEach((inp, i) => inp.value = paste[i] || '');
                        inputs[5].focus();
                        handleVerifyOtp();
                    }
                });
            });
        }
    </script>
</body>
</html>
