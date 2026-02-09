<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:' . config('payment.withdrawal.min_amount', 300),
                'max:' . config('payment.withdrawal.max_amount', 50000),
            ],
            'bank_account_id' => ['required', 'integer', 'exists:user_bank_accounts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'กรุณาระบุจำนวนเงิน',
            'amount.min' => 'ถอนขั้นต่ำ ' . config('payment.withdrawal.min_amount', 300) . ' บาท',
            'amount.max' => 'ถอนสูงสุด ' . config('payment.withdrawal.max_amount', 50000) . ' บาท',
            'bank_account_id.required' => 'กรุณาเลือกบัญชีธนาคาร',
            'bank_account_id.exists' => 'ไม่พบบัญชีธนาคาร',
        ];
    }
}
