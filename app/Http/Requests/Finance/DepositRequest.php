<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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
                'min:' . config('payment.deposit.min_amount', 100),
                'max:' . config('payment.deposit.max_amount', 100000),
            ],
            'method' => ['nullable', 'string', 'in:bank_transfer,promptpay,truewallet,sms_auto'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'กรุณาระบุจำนวนเงิน',
            'amount.min' => 'ฝากขั้นต่ำ ' . config('payment.deposit.min_amount', 100) . ' บาท',
            'amount.max' => 'ฝากสูงสุด ' . config('payment.deposit.max_amount', 100000) . ' บาท',
        ];
    }
}
