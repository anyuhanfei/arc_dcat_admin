<?php

namespace App\Api\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class PasswordOldVerifyRequest extends FormRequest
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
            'old_password' => 'required|string',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'old_password.required' => '旧密码不能为空',
        ];
    }
}
