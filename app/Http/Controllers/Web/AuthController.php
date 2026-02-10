<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(Request $request): View
    {
        $ref = $request->query('ref');
        $referrer = null;
        if ($ref) {
            $referrer = User::where('referral_code', $ref)->first();
        }

        return view('auth.register', compact('ref', 'referrer'));
    }

    public function showLogin()
    {
        if (Auth::check() && !Auth::user()->isAdmin()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    /**
     * Register via Firebase Phone Auth (AJAX)
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9}$/'],
            'password' => 'required|string|min:6|confirmed',
            'firebase_uid' => 'required|string',
            'referral_code' => 'nullable|string|max:10',
        ], [
            'phone.regex' => 'เบอร์โทรศัพท์ไม่ถูกต้อง (ตัวอย่าง: 0812345678)',
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'name.required' => 'กรุณากรอกชื่อ',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านไม่ตรงกัน',
        ]);

        // Check if phone already exists
        if (User::where('phone', $validated['phone'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว',
            ], 422);
        }

        // Find referrer
        $referredBy = null;
        if (!empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
            $referredBy = $referrer?->id;
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['phone'] . '@lotto.local',
            'password' => Hash::make($validated['password']),
            'role' => UserRole::Member,
            'status' => UserStatus::Active,
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by' => $referredBy,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Login the user
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'สมัครสมาชิกสำเร็จ',
            'redirect' => '/member',
        ]);
    }

    /**
     * Login via phone + password (AJAX)
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
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

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Redirect admin users to admin panel
        $redirect = $user->isAdmin() ? '/admin' : '/member';

        return response()->json([
            'success' => true,
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'redirect' => $redirect,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
