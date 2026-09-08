<?php

namespace App\Api\Controllers\Feedback;

use App\Api\Requests\Feedback\FeedbackDetailRequest;
use App\Api\Requests\Feedback\FeedbackListRequest;
use App\Api\Requests\Feedback\FeedbackSubmitRequest;
use App\Api\Resources\Feedback\FeedbackDetailResource;
use App\Api\Resources\Feedback\FeedbackResource;
use App\Api\Services\Feedback\FeedbackService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class FeedbackController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected FeedbackService $service,
    ) {}

    #[
        OA\Post(
            path: '/api/feedback/submit',
            summary: '提交意见反馈',
            tags: ['用户/意见反馈'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', description: '反馈标题', example: '功能异常反馈'),
                        new OA\Property(property: 'content', type: 'string', description: '反馈内容', example: '点击提交按钮无响应...'),
                        new OA\Property(property: 'images', type: 'array', description: '截图列表（最多9张）', items: new OA\Items(type: 'string', format: 'uri', example: 'https://example.com/uploads/xxx.jpg')),
                        new OA\Property(property: 'contact_name', type: 'string', description: '联系人姓名', example: '张三'),
                        new OA\Property(property: 'contact_phone', type: 'string', description: '联系手机号', example: '13800138000'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: '成功',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 1),
                            new OA\Property(property: 'msg', type: 'string', example: '提交成功'),
                            new OA\Property(property: 'data', type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function submit(FeedbackSubmitRequest $request): JsonResponse
    {
        $userId = auth('sanctum')->user()->id;
        $feedback = $this->service->submit($request->validated(), $userId);

        return success('提交成功', ['id' => $feedback->id]);
    }

    #[
        OA\Post(
            path: '/api/feedback/list',
            summary: '我的反馈列表',
            tags: ['用户/意见反馈'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'page', type: 'integer', description: '页码，默认 1', example: 1),
                        new OA\Property(property: 'per_page', type: 'integer', description: '每页条数，默认 10，最大 50', example: 10),
                    ]
                )
            ),
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
                                    new OA\Property(property: 'total', type: 'integer', example: 10),
                                    new OA\Property(property: 'count', type: 'integer', example: 10),
                                    new OA\Property(property: 'perPage', type: 'integer', example: 10),
                                    new OA\Property(property: 'currentPage', type: 'integer', example: 1),
                                    new OA\Property(property: 'lastPage', type: 'integer', example: 1),
                                    new OA\Property(property: 'firstItem', type: 'integer', example: 1),
                                    new OA\Property(property: 'lastItem', type: 'integer', example: 10),
                                    new OA\Property(property: 'datas', type: 'array', items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'title', type: 'string', example: '功能异常反馈'),
                                            new OA\Property(property: 'status', type: 'integer', example: 0),
                                            new OA\Property(property: 'status_name', type: 'string', example: '待处理'),
                                            new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                            new OA\Property(property: 'handled_at', type: 'string', nullable: true, example: null),
                                        ],
                                        type: 'object'
                                    )),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function list(FeedbackListRequest $request): JsonResponse
    {
        $userId = auth('sanctum')->user()->id;
        $paginator = $this->service->list($userId, $request->integer('page', 1), $request->integer('per_page', 10));

        $data = [
            'total'       => $paginator->total(),
            'count'       => $paginator->count(),
            'perPage'     => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage'    => $paginator->lastPage(),
            'firstItem'   => $paginator->firstItem(),
            'lastItem'    => $paginator->lastItem(),
            'datas'       => FeedbackResource::collection($paginator->getCollection())->toArray($request),
        ];

        return success('success', $data);
    }

    #[
        OA\Post(
            path: '/api/feedback/detail',
            summary: '反馈详情',
            tags: ['用户/意见反馈'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', description: '反馈ID', example: 1),
                    ]
                )
            ),
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
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'user_id', type: 'integer', example: 1001),
                                    new OA\Property(property: 'title', type: 'string', example: '功能异常反馈'),
                                    new OA\Property(property: 'content', type: 'string', example: '点击提交按钮无响应...'),
                                    new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string', example: 'https://example.com/uploads/xxx.jpg')),
                                    new OA\Property(property: 'contact_name', type: 'string', example: '张三'),
                                    new OA\Property(property: 'contact_phone', type: 'string', example: '13800138000'),
                                    new OA\Property(property: 'status', type: 'integer', example: 0),
                                    new OA\Property(property: 'status_name', type: 'string', example: '待处理'),
                                    new OA\Property(property: 'handler_id', type: 'integer', nullable: true, example: null),
                                    new OA\Property(property: 'handled_at', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'reply', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'replied_at', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'created_at', type: 'string', example: '2025-01-01 12:00:00'),
                                    new OA\Property(property: 'updated_at', type: 'string', example: '2025-01-01 12:00:00'),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function detail(FeedbackDetailRequest $request): JsonResponse
    {
        $userId = auth('sanctum')->user()->id;
        $result = $this->service->detail($request->integer('id'), $userId);

        return success('success', (new FeedbackDetailResource($result))->toArray($request));
    }
}
