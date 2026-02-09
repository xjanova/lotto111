<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->phone) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณายืนยันเบอร์โทรศัพท์',
            ], 403);
        }

        return $next($request);
    }
}
