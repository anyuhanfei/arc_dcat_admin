<?php

namespace App\Api\Requests\Ms;

use Illuminate\Foundation\Http\FormRequest;

class EmailSendRequest extends FormRequest
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
            'email' => 'nullable|email',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'email.email' => '邮箱格式不正确',
        ];
    }
}
