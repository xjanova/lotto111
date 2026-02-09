<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NumberSet;
use App\Models\NumberSetItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NumberSetController extends Controller
{
    /**
     * GET /api/number-sets
     */
    public function index(Request $request): JsonResponse
    {
        $sets = NumberSet::where('user_id', $request->user()->id)
            ->with('items.betType')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sets,
        ]);
    }

    /**
     * POST /api/number-sets
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.bet_type_id' => 'required|integer|exists:bet_types,id',
            'items.*.number' => 'required|string|regex:/^[0-9]+$/',
            'items.*.amount' => 'nullable|numeric|min:0',
        ]);

        $set = NumberSet::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
        ]);

        foreach ($request->items as $item) {
            NumberSetItem::create([
                'number_set_id' => $set->id,
                'bet_type_id' => $item['bet_type_id'],
                'number' => $item['number'],
                'amount' => $item['amount'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'สร้างเลขชุดสำเร็จ',
            'data' => $set->load('items.betType'),
        ], 201);
    }

    /**
     * PUT /api/number-sets/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $set = NumberSet::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'items' => 'sometimes|array|min:1',
            'items.*.bet_type_id' => 'required_with:items|integer|exists:bet_types,id',
            'items.*.number' => 'required_with:items|string|regex:/^[0-9]+$/',
            'items.*.amount' => 'nullable|numeric|min:0',
        ]);

        if ($request->has('name')) {
            $set->update(['name' => $request->name]);
        }

        if ($request->has('items')) {
            $set->items()->delete();
            foreach ($request->items as $item) {
                NumberSetItem::create([
                    'number_set_id' => $set->id,
                    'bet_type_id' => $item['bet_type_id'],
                    'number' => $item['number'],
                    'amount' => $item['amount'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตเลขชุดสำเร็จ',
            'data' => $set->load('items.betType'),
        ]);
    }

    /**
     * DELETE /api/number-sets/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $set = NumberSet::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $set->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบเลขชุดสำเร็จ',
        ]);
    }
}
