<?php

namespace App\Api\Controllers\Message;

use App\Api\Requests\Message\MessageDetailRequest;
use App\Api\Requests\Message\MessageListRequest;
use App\Api\Requests\Message\MessageReadAllRequest;
use App\Api\Resources\Message\MessageDetailResource;
use App\Api\Resources\Message\MessageResource;
use App\Api\Services\Message\MessageService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class MessageController extends \Illuminate\Routing\Controller
{
    public function __construct(
        protected MessageService $service,
    ) {}

    #[
        OA\Get(
            path: '/api/messages/unread-count',
            summary: '未读消息统计',
            tags: ['用户/系统消息'],
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
                                    new OA\Property(property: 'total', type: 'integer', example: 5),
                                    new OA\Property(property: 'types', type: 'array', items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'type', type: 'integer', example: 1),
                                            new OA\Property(property: 'type_label', type: 'string', example: '系统'),
                                            new OA\Property(property: 'unread_count', type: 'integer', example: 3),
                                            new OA\Property(property: 'latest_title', type: 'string', example: '系统升级通知'),
                                            new OA\Property(property: 'latest_time', type: 'string', example: '10分钟前'),
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
    public function unreadCount(): JsonResponse
    {
        $userId = auth('sanctum')->user()->id;

        return success('success', $this->service->unreadCount($userId));
    }

    #[
        OA\Get(
            path: '/api/messages',
            summary: '消息列表',
            tags: ['用户/系统消息'],
            parameters: [
                new OA\Parameter(name: 'page', in: 'query', description: '页码，默认 1', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
                new OA\Parameter(name: 'per_page', in: 'query', description: '每页条数，默认 15，最大 50', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
                new OA\Parameter(name: 'type', in: 'query', description: '消息类型：1=系统，2=订单', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            ],
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
                                    new OA\Property(property: 'perPage', type: 'integer', example: 15),
                                    new OA\Property(property: 'currentPage', type: 'integer', example: 1),
                                    new OA\Property(property: 'lastPage', type: 'integer', example: 1),
                                    new OA\Property(property: 'firstItem', type: 'integer', example: 1),
                                    new OA\Property(property: 'lastItem', type: 'integer', example: 10),
                                    new OA\Property(property: 'datas', type: 'array', items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'type', type: 'integer', example: 1),
                                            new OA\Property(property: 'type_label', type: 'string', example: '系统'),
                                            new OA\Property(property: 'title', type: 'string', example: '系统升级通知'),
                                            new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
                                            new OA\Property(property: 'content', type: 'string', example: '系统将于本周六凌晨进行升级维护...'),
                                            new OA\Property(property: 'is_read', type: 'integer', example: 0),
                                            new OA\Property(property: 'created_at', type: 'string', example: '10分钟前'),
                                            new OA\Property(property: 'relation_type', type: 'string', nullable: true, example: null),
                                            new OA\Property(property: 'relation_id', type: 'integer', nullable: true, example: null),
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
    public function index(MessageListRequest $request): JsonResponse
    {
        $userId = auth('sanctum')->user()->id;
        $paginator = $this->service->list(
            $userId,
            $request->integer('page', 1),
            $request->integer('per_page', 15),
            $request->has('type') ? $request->integer('type') : null
        );

        $data = [
            'total'       => $paginator->total(),
            'count'       => $paginator->count(),
            'perPage'     => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage'    => $paginator->lastPage(),
            'firstItem'   => $paginator->firstItem(),
            'lastItem'    => $paginator->lastItem(),
            'datas'       => MessageResource::collection($paginator->getCollection())->toArray($request),
        ];

        return success('success', $data);
    }

    #[
        OA\Get(
            path: '/api/messages/{id}',
            summary: '消息详情',
            tags: ['用户/系统消息'],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', description: '消息ID', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            ],
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
                                    new OA\Property(property: 'type', type: 'integer', example: 1),
                                    new OA\Property(property: 'type_label', type: 'string', example: '系统'),
                                    new OA\Property(property: 'title', type: 'string', example: '系统升级通知'),
                                    new OA\Property(property: 'image', type: 'string', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'content', type: 'string', example: '<p>系统将于本周六凌晨进行升级维护，期间服务可能短暂中断...</p>'),
                                    new OA\Property(property: 'is_read', type: 'integer', example: 1),
                                    new OA\Property(property: 'created_at', type: 'string', example: '10分钟前'),
                                    new OA\Property(property: 'relation_type', type: 'string', nullable: true, example: null),
                                    new OA\Property(property: 'relation_id', type: 'integer', nullable: true, example: null),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function show(int $id, MessageDetailRequest $request): JsonResponse
    {
        $userId = auth('sanctum')->user()->id;
        $result = $this->service->detail($id, $userId);

        return success('success', (new MessageDetailResource($result))->toArray($request));
    }

    #[
        OA\Post(
            path: '/api/messages/read-all',
            summary: '全部标记已读',
            tags: ['用户/系统消息'],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'type', type: 'integer', description: '消息类型（可选）：1=系统，2=订单；不传则全部标记', example: 1),
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
                            new OA\Property(property: 'msg', type: 'string', example: '操作成功'),
                            new OA\Property(property: 'data', type: 'array', example: []),
                        ]
                    )
                ),
            ]
        ),
    ]
    public function readAll(MessageReadAllRequest $request): JsonResponse
    {
        $userId = auth('sanctum')->user()->id;
        $type = $request->has('type') ? $request->integer('type') : null;

        $this->service->readAll($userId, $type);

        return success('操作成功');
    }
}
