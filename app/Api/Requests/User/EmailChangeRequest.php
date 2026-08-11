<?php

namespace App\Api\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class EmailChangeRequest extends FormRequest
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
            'new_email'    => 'required|email',
            'new_code'     => 'required|digits:6',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'verify_token.required' => 'verify_token不能为空',
            'new_email.required'    => '新邮箱不能为空',
            'new_email.email'       => '新邮箱格式不正确',
            'new_code.required'     => '新邮箱验证码不能为空',
            'new_code.digits'       => '验证码为6位数字',
        ];
    }
}
