<?php

namespace App\Api\Requests\Ms;

use Illuminate\Foundation\Http\FormRequest;

class SmsSendRequest extends FormRequest
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
            'phone' => 'nullable|regex:/^1[3-9]\d{9}$/',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'phone.regex' => '手机号格式不正确',
        ];
    }
}
