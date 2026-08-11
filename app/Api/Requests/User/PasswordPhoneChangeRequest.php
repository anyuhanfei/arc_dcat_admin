<?php

namespace App\Api\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class PasswordPhoneChangeRequest extends FormRequest
{
    /**
     * 确定用户是否有权发出此请求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取应用于请求的验证规则
     */
    public function rules(): array
    {
        return [
            'verify_token' => 'required|string',
            'password'     => 'required|min:6|confirmed',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'verify_token.required' => 'verify_token不能为空',
            'password.required'     => '密码不能为空',
            'password.min'          => '密码至少6位',
            'password.confirmed'    => '两次输入的密码不一致',
        ];
    }
}
