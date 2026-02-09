<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * GET /api/messages
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $messages = Message::where(function ($q) use ($userId) {
            $q->where('receiver_id', $userId)
              ->orWhere('sender_id', $userId);
        })
            ->with(['sender:id,name,role', 'receiver:id,name,role'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Mark as read
        Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * POST /api/messages
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => null, // Admin will receive
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ส่งข้อความสำเร็จ',
            'data' => $message,
        ], 201);
    }
}
