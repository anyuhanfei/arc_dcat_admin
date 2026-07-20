<?php

namespace App\Api\Requests\Sys;

use Illuminate\Foundation\Http\FormRequest;

class AgreementsShowRequest extends FormRequest
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
            'id' => 'required|integer|exists:sys_agreements,id',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'id.required' => '协议 ID 不能为空',
            'id.integer'  => '协议 ID 必须是整数',
            'id.exists'   => '协议不存在',
        ];
    }
}
