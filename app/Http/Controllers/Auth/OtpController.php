<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    /**
     * Verify Firebase ID token and register/login user.
     *
     * Frontend sends the Firebase ID token after OTP verification.
     * Backend verifies with Google, then creates or logs in the user.
     */
    public function verifyAndLogin(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        // Verify Firebase ID token with Google
        $firebaseUser = $this->verifyFirebaseToken($request->id_token);

        if (! $firebaseUser) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP token',
            ], 401);
        }

        $phone = $firebaseUser['phone_number'] ?? null;

        if (! $phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found in token',
            ], 422);
        }

        // Normalize Thai phone: +66812345678 -> 0812345678
        $normalizedPhone = $this->normalizeThaiPhone($phone);

        // Find or create user
        $user = User::where('phone', $normalizedPhone)
            ->orWhere('phone', $phone)
            ->first();

        $isNewUser = false;

        if (! $user) {
            $isNewUser = true;
            $user = User::create([
                'name' => 'User ' . substr($normalizedPhone, -4),
                'phone' => $normalizedPhone,
                'email' => null,
                'password' => bcrypt(Str::random(32)),
                'role' => UserRole::User,
                'status' => UserStatus::Active,
                'referral_code' => strtoupper(Str::random(8)),
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
        } else {
            if ($user->isBanned()) {
                return response()->json([
                    'success' => false,
                    'message' => 'บัญชีถูกระงับ กรุณาติดต่อแอดมิน',
                ], 403);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
        }

        // Create Sanctum token
        $token = $user->createToken('otp-login')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $isNewUser ? 'สมัครสมาชิกสำเร็จ' : 'เข้าสู่ระบบสำเร็จ',
            'is_new_user' => $isNewUser,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'role' => $user->role,
                'balance' => $user->balance ?? '0.00',
                'vip_level' => $user->vip_level,
            ],
        ]);
    }

    /**
     * Verify Firebase ID token using Google's tokeninfo endpoint.
     */
    private function verifyFirebaseToken(string $idToken): ?array
    {
        $projectId = config('firebase.project_id');

        try {
            $response = Http::get(
                "https://www.googleapis.com/identitytoolkit/v3/relyingparty/getAccountInfo?key=" . config('firebase.api_key'),
                ['idToken' => $idToken]
            );

            // Alternative: use secure token verification
            if (! $response->successful()) {
                $response = Http::post(
                    "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=" . config('firebase.api_key'),
                    ['idToken' => $idToken]
                );
            }

            if ($response->successful()) {
                $users = $response->json('users', []);
                return $users[0] ?? null;
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    /**
     * Normalize Thai phone number format.
     * +66812345678 -> 0812345678
     */
    private function normalizeThaiPhone(string $phone): string
    {
        if (str_starts_with($phone, '+66')) {
            return '0' . substr($phone, 3);
        }

        return $phone;
    }
}
