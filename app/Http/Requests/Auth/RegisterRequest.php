<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9}$/', 'unique:users,phone'],
            'name' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'otp_code' => ['required', 'string', 'size:6'],
            'bank_code' => ['required', 'string', 'max:10'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:20'],
            'account_name' => ['required', 'string', 'max:100'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'phone.regex' => 'เบอร์โทรศัพท์ไม่ถูกต้อง',
            'phone.unique' => 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว',
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
            'otp_code.required' => 'กรุณากรอกรหัส OTP',
            'otp_code.size' => 'รหัส OTP ต้องเป็น 6 หลัก',
        ];
    }
}
