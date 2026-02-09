<?php

namespace App\Http\Requests\Lottery;

use Illuminate\Foundation\Http\FormRequest;

class PlaceBetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'round_id' => ['required', 'integer', 'exists:lottery_rounds,id'],
            'bets' => ['required', 'array', 'min:1', 'max:' . config('lottery.max_items_per_ticket', 100)],
            'bets.*.bet_type_id' => ['required', 'integer', 'exists:bet_types,id'],
            'bets.*.number' => ['required', 'string', 'regex:/^[0-9]+$/'],
            'bets.*.amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'round_id.required' => 'กรุณาเลือกรอบหวย',
            'round_id.exists' => 'ไม่พบรอบหวยที่เลือก',
            'bets.required' => 'กรุณาเพิ่มรายการแทง',
            'bets.min' => 'ต้องมีรายการแทงอย่างน้อย 1 รายการ',
            'bets.*.number.regex' => 'เลขต้องเป็นตัวเลขเท่านั้น',
            'bets.*.amount.min' => 'จำนวนเงินต้องมากกว่า 0',
        ];
    }
}
