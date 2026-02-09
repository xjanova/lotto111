<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class OtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9}$/'],
            'purpose' => ['required', 'string', 'in:register,login,reset_password,verify'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'phone.regex' => 'เบอร์โทรศัพท์ไม่ถูกต้อง',
            'purpose.required' => 'กรุณาระบุจุดประสงค์',
            'purpose.in' => 'จุดประสงค์ไม่ถูกต้อง',
        ];
    }
}
