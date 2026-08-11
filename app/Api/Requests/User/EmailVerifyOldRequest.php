<?php

namespace App\Api\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerifyOldRequest extends FormRequest
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
            'old_email' => 'required|email',
            'old_code'  => 'required|digits:6',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'old_email.required' => '旧邮箱不能为空',
            'old_email.email'    => '旧邮箱格式不正确',
            'old_code.required'  => '旧邮箱验证码不能为空',
            'old_code.digits'    => '验证码为6位数字',
        ];
    }
}
