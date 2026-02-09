<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserBankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET /api/user/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('primaryBankAccount');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'role' => $user->role?->value,
                'status' => $user->status?->value,
                'balance' => (float) $user->balance,
                'vip_level' => $user->vip_level?->value,
                'xp' => $user->xp,
                'referral_code' => $user->referral_code,
                'bank_account' => $user->primaryBankAccount ? [
                    'bank_code' => $user->primaryBankAccount->bank_code,
                    'bank_name' => $user->primaryBankAccount->bank_name,
                    'account_number' => $user->primaryBankAccount->account_number,
                    'account_name' => $user->primaryBankAccount->account_name,
                ] : null,
                'last_login_at' => $user->last_login_at?->toISOString(),
                'created_at' => $user->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * PUT /api/user/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:100',
            'line_id' => 'sometimes|nullable|string|max:100',
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'line_id']));

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตข้อมูลสำเร็จ',
        ]);
    }

    /**
     * PUT /api/user/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'เปลี่ยนรหัสผ่านสำเร็จ',
        ]);
    }

    /**
     * GET /api/user/bank-accounts
     */
    public function bankAccounts(Request $request): JsonResponse
    {
        $accounts = UserBankAccount::where('user_id', $request->user()->id)->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    /**
     * POST /api/user/bank-accounts
     */
    public function addBankAccount(Request $request): JsonResponse
    {
        $request->validate([
            'bank_code' => 'required|string|max:10',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:20',
            'account_name' => 'required|string|max:100',
        ]);

        $user = $request->user();

        // Max 3 bank accounts
        if ($user->bankAccounts()->count() >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'มีบัญชีธนาคารครบ 3 บัญชีแล้ว',
            ], 422);
        }

        $isPrimary = $user->bankAccounts()->count() === 0;

        $account = UserBankAccount::create([
            'user_id' => $user->id,
            'bank_code' => $request->bank_code,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'is_primary' => $isPrimary,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'เพิ่มบัญชีธนาคารสำเร็จ',
            'data' => $account,
        ], 201);
    }
}
