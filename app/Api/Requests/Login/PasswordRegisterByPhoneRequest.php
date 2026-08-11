<?php

namespace App\Api\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;

class PasswordRegisterByPhoneRequest extends FormRequest
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
            'account'  => 'required|regex:/^[a-zA-Z][a-zA-Z0-9_]{3,19}$/',
            'password' => 'required|min:6|confirmed',
            'phone'    => 'required|regex:/^1[3-9]\d{9}$/',
            'code'     => 'required|digits:6',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'account.required'  => '账号不能为空',
            'account.regex'     => '账号格式不正确（字母开头，字母数字下划线 4-20 位）',
            'password.required' => '密码不能为空',
            'password.min'      => '密码至少6位',
            'password.confirmed' => '两次输入的密码不一致',
            'phone.required'    => '手机号不能为空',
            'phone.regex'       => '手机号格式不正确',
            'code.required'     => '验证码不能为空',
            'code.digits'       => '验证码为6位数字',
        ];
    }
}
