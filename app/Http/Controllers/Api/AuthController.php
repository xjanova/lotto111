<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private OtpService $otpService,
    ) {}

    /**
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Verify OTP
        $otpResult = $this->otpService->verify($validated['phone'], $validated['otp_code'], 'register');
        if (! $otpResult['success']) {
            return response()->json($otpResult, 422);
        }

        // Find referrer
        $referredBy = null;
        if (! empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
            $referredBy = $referrer?->id;
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['phone'] . '@lotto.local',
            'password' => Hash::make($validated['password']),
            'role' => 'member',
            'status' => 'active',
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by' => $referredBy,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Create bank account
        UserBankAccount::create([
            'user_id' => $user->id,
            'bank_code' => $validated['bank_code'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'is_primary' => true,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'สมัครสมาชิกสำเร็จ',
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * POST /api/auth/send-otp
     */
    public function sendOtp(OtpRequest $request): JsonResponse
    {
        $result = $this->otpService->send($request->phone, $request->purpose);

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    /**
     * POST /api/auth/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'otp_code' => 'required|string|size:6',
            'purpose' => 'required|string|in:register,login,reset_password,verify',
        ]);

        $result = $this->otpService->verify($request->phone, $request->otp_code, $request->purpose);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'เบอร์โทรศัพท์หรือรหัสผ่านไม่ถูกต้อง',
            ], 401);
        }

        if ($user->isBanned()) {
            return response()->json([
                'success' => false,
                'message' => 'บัญชีของคุณถูกระงับการใช้งาน',
            ], 403);
        }

        if ($user->isSuspended()) {
            return response()->json([
                'success' => false,
                'message' => 'บัญชีของคุณถูกระงับชั่วคราว กรุณาติดต่อแอดมิน',
            ], 403);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'ออกจากระบบสำเร็จ',
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $result = $this->otpService->send($request->phone, 'reset_password');

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'otp_code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $otpResult = $this->otpService->verify($request->phone, $request->otp_code, 'reset_password');
        if (! $otpResult['success']) {
            return response()->json($otpResult, 422);
        }

        $user = User::where('phone', $request->phone)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'รีเซ็ตรหัสผ่านสำเร็จ กรุณาเข้าสู่ระบบใหม่',
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'role' => $user->role?->value,
            'status' => $user->status?->value,
            'balance' => (float) $user->balance,
            'vip_level' => $user->vip_level?->value,
            'xp' => $user->xp,
            'referral_code' => $user->referral_code,
        ];
    }
}
