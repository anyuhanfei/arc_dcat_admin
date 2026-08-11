<?php

namespace App\Api\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;

class PhoneLoginRequest extends FormRequest
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
            'phone' => 'required|regex:/^1[3-9]\d{9}$/',
            'code'  => 'required|digits:6',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'phone.required' => '手机号不能为空',
            'phone.regex'    => '手机号格式不正确',
            'code.required'  => '验证码不能为空',
            'code.digits'    => '验证码为6位数字',
        ];
    }
}
