<?php

namespace App\Api\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackSubmitRequest extends FormRequest
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
            'title'         => ['required', 'string', 'max:200'],
            'content'       => ['required', 'string'],
            'images'        => ['nullable', 'array', 'max:9'],
            'images.*'      => ['string', 'url'],
            'contact_name'  => ['required', 'string', 'max:50'],
            'contact_phone' => ['required', 'string', 'regex:/^1[3-9]\d{9}$/'],
        ];
    }
}
