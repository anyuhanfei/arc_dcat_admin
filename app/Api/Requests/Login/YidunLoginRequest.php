<?php

namespace App\Api\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;

class YidunLoginRequest extends FormRequest
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
            'token'        => 'required|string',
            'access_token' => 'required|string',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'token.required'        => 'token不能为空',
            'access_token.required' => 'access_token不能为空',
        ];
    }
}
