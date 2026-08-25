<?php

namespace App\Api\Requests\Sys;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannersListRequest extends FormRequest
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
     *
     * position：当 config('business.banner.positions') 仅配置一个位置时可省略（缺省取该唯一位置）；
     * 配置多个位置时必填。
     */
    public function rules(): array
    {
        return [
            'position' => [
                'nullable',
                'string',
                Rule::requiredIf(count(config('business.banner.positions')) > 1),
            ],
        ];
    }
}
