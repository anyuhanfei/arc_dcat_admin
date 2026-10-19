<?php

namespace App\Api\Controllers\Docs;

use OpenApi\Attributes as OA;

class IntroductionController extends \Illuminate\Routing\Controller
{
    #[
        OA\Get(
            path: '/api/docs/introduction',
            summary: 'API 对接须知',
            tags: [],
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: 'success'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'response_format', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'common', type: 'string', example: 'HTTP 状态码恒为 200，业务结果看 code 字段：1=成功，0=业务错误'),
                                            new OA\Property(property: 'success', type: 'object',
                                                properties: [
                                                    new OA\Property(property: 'code', type: 'string', example: 'int，恒为 1'),
                                                    new OA\Property(property: 'msg', type: 'string', example: 'string，整体提示文案，前端可直接弹框展示；成功时一般为 "success"'),
                                                    new OA\Property(property: 'data', type: 'string', example: 'mixed，业务数据；其中的 null 值已由后端递归转为空字符串 ""，前端无需再判空'),
                                                ]
                                            ),
                                            new OA\Property(property: 'failure', type: 'object',
                                                properties: [
                                                    new OA\Property(property: 'code', type: 'string', example: 'int，恒为 0'),
                                                    new OA\Property(property: 'msg', type: 'string', example: 'string，整体错误提示文案'),
                                                    new OA\Property(property: 'errors', type: 'string', example: 'object，字段级错误明细，键为字段名、值为错误信息数组（如 {"phone":["手机号格式错误"]}）；无字段错误时为 {}（空对象，不是 null 也不是 []）'),
                                                    new OA\Property(property: 'data', type: 'string', example: 'mixed，附加数据，一般为空数组 []'),
                                                    new OA\Property(property: 'error_flag', type: 'string', example: 'string，特殊错误标识，普通业务错误为空字符串 ""；用于区分需要前端特殊处理的场景（如未登录、未绑定微信等，见 error_flags 一节）'),
                                                ]
                                            ),
                                        ]
                                    ),
                                    new OA\Property(property: 'auth', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'type', type: 'string', example: 'Bearer Token（Laravel Sanctum）'),
                                            new OA\Property(property: 'header', type: 'string', example: 'Authorization: Bearer {token}'),
                                            new OA\Property(property: 'how_to_get', type: 'string', example: '通过登录接口获取 token'),
                                            new OA\Property(property: 'no_login_behavior', type: 'string', example: 'error_flag="no_login" 时跳转登录页'),
                                            new OA\Property(property: 'request_example', type: 'string', example: 'Headers: { "Authorization": "Bearer 12|xxx" }'),
                                        ]
                                    ),
                                    new OA\Property(property: 'error_flags', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'no_login', type: 'string', example: '未登录或登录已过期'),
                                            new OA\Property(property: 'no_bind_wx', type: 'string', example: '当前账号未绑定微信'),
                                            new OA\Property(property: 'no_bind_alipay', type: 'string', example: '当前账号未绑定支付宝'),
                                            new OA\Property(property: 'no_bind_phone', type: 'string', example: '当前账号未绑定手机号'),
                                        ]
                                    ),
                                    new OA\Property(property: 'pagination', type: 'object',
                                        properties: [
                                            new OA\Property(property: 'description', type: 'string', example: '分页列表接口的 data 字段统一结构'),
                                            new OA\Property(property: 'fields', type: 'object',
                                                properties: [
                                                    new OA\Property(property: 'total', type: 'string', example: 'int，符合条件的总记录数'),
                                                    new OA\Property(property: 'count', type: 'string', example: 'int，当前页返回的记录数'),
                                                    new OA\Property(property: 'perPage', type: 'string', example: 'int，每页条数'),
                                                    new OA\Property(property: 'currentPage', type: 'string', example: 'int，当前页码'),
                                                    new OA\Property(property: 'lastPage', type: 'string', example: 'int，最后一页页码'),
                                                    new OA\Property(property: 'firstItem', type: 'string', example: 'int，当前页第一条序号'),
                                                    new OA\Property(property: 'lastItem', type: 'string', example: 'int，当前页最后一条序号'),
                                                    new OA\Property(property: 'datas', type: 'string', example: 'array，当前页数据列表'),
                                                ]
                                            ),
                                            new OA\Property(property: 'request_params', type: 'object',
                                                properties: [
                                                    new OA\Property(property: 'page', type: 'string', example: 'int，页码，默认 1'),
                                                    new OA\Property(property: 'per_page', type: 'string', example: 'int，每页条数，默认 15'),
                                                ]
                                            ),
                                        ]
                                    ),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function index(): \Illuminate\Http\JsonResponse
    {
        $notice = [
            'response_format' => [
                'common'   => 'HTTP 状态码恒为 200，业务结果看 code 字段：1=成功，0=业务错误；成功与失败响应结构不同，errors/error_flag 仅在失败时返回',
                'success'  => [
                    'code' => 'int，恒为 1',
                    'msg'  => 'string，整体提示文案，前端可直接弹框展示；成功时一般为 "success"',
                    'data' => 'mixed，业务数据，结构随接口而定；其中的 null 值已由后端递归转为空字符串 ""，前端无需再判空',
                ],
                'failure'  => [
                    'code'       => 'int，恒为 0',
                    'msg'        => 'string，整体错误提示文案',
                    'errors'     => 'object，字段级错误明细，键为字段名、值为错误信息数组（如 {"phone": ["手机号格式错误"]}）；无字段错误时为 {}（空对象，不是 null 也不是 []）',
                    'data'       => 'mixed，附加数据，一般为空数组 []',
                    'error_flag' => 'string，特殊错误标识，普通业务错误为空字符串 ""；用于区分需要前端特殊处理的场景（如未登录、未绑定微信等，见 error_flags 一节）',
                ],
            ],
            'auth' => [
                'type'       => 'Bearer Token（Laravel Sanctum）',
                'header'     => 'Authorization: Bearer {token}',
                'how_to_get' => '通过手机号验证码登录（POST /api/login/phone/login）、密码登录（POST /api/login/password/login）、微信小程序/公众号/App 授权登录、Apple 登录等接口获取 token',
                'no_login_behavior' => '当响应 error_flag="no_login" 时，表示未登录或 token 已过期/失效，前端应清除本地 token 并跳转登录页；此时 code=0',
                'request_example' => 'Headers: { "Authorization": "Bearer 12|xxxxxxxxxxxx", "Accept": "application/json" }',
            ],
            'error_flags' => [
                'no_login'       => '未登录或登录已过期（常量 NO_LOGIN）',
                'no_bind_wx'     => '当前账号未绑定微信，需先走微信绑定流程（常量 NO_BIND_WX）',
                'no_bind_alipay' => '当前账号未绑定支付宝（常量 NO_BIND_ALIPAY）',
                'no_bind_phone'  => '当前账号未绑定手机号（常量 NO_BIND_PHONE）',
            ],
            'pagination' => [
                'description' => '分页列表接口的 data 字段统一为以下结构（参照文章列表 POST /api/sys/articles/list 的实际返回）：',
                'fields' => [
                    'total'       => 'int，符合条件的总记录数',
                    'count'       => 'int，当前页返回的记录数',
                    'perPage'     => 'int，每页条数（请求参数名为 per_page，默认 15）',
                    'currentPage'=> 'int，当前页码（请求参数名为 page，默认 1）',
                    'lastPage'    => 'int，最后一页页码；当 currentPage >= lastPage 表示已到最后一页',
                    'firstItem'   => 'int，当前页第一条记录在全量结果中的序号（从 1 开始）',
                    'lastItem'    => 'int，当前页最后一条记录在全量结果中的序号',
                    'datas'       => 'array，当前页数据列表，元素结构随业务接口而定（注意字段名是 datas，不是 list 也不是 data）',
                ],
                'request_params' => [
                    'page'     => 'int，页码，默认 1',
                    'per_page' => 'int，每页条数，默认 15',
                ],
            ],
        ];

        return success('success', $notice);
    }
}
