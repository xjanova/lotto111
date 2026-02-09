<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->status === UserStatus::Banned) {
            return response()->json([
                'success' => false,
                'message' => 'บัญชีของคุณถูกระงับการใช้งาน',
            ], 403);
        }

        if ($user->status === UserStatus::Suspended) {
            return response()->json([
                'success' => false,
                'message' => 'บัญชีของคุณถูกระงับชั่วคราว กรุณาติดต่อแอดมิน',
            ], 403);
        }

        return $next($request);
    }
}
