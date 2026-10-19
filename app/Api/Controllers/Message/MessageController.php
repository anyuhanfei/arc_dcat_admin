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
        OA\Post(
            path: '/api/messages/unread-count',
            summary: '未读消息统计',
            description: '统计当前用户各类型未读消息总数及每类型最新一条消息标题/时间，用于消息入口红点和分类角标展示。',
            tags: ['消息'],
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
                                    new OA\Property(property: 'total', type: 'integer', description: '未读消息总数（所有类型合计）', example: 5),
                                    new OA\Property(property: 'types', type: 'array', description: '按消息类型分组的未读统计', items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'type', type: 'integer', description: '消息类型：1=系统消息，2=订单消息', example: 1),
                                            new OA\Property(property: 'type_label', type: 'string', description: '消息类型中文名称', example: '系统'),
                                            new OA\Property(property: 'unread_count', type: 'integer', description: '该类型未读条数', example: 3),
                                            new OA\Property(property: 'latest_title', type: 'string', description: '该类型最新一条消息标题（无消息时为空字符串）', example: '系统升级通知'),
                                            new OA\Property(property: 'latest_time', type: 'string', description: '该类型最新一条消息的相对时间（如"10分钟前"）', example: '10分钟前'),
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
        OA\Post(
            path: '/api/messages',
            summary: '消息列表',
            description: '分页查询当前用户的消息列表，可按消息类型筛选；按时间倒序返回。',
            tags: ['消息'],
            requestBody: new OA\RequestBody(
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'page', type: 'integer', description: '页码，默认 1', example: 1),
                            new OA\Property(property: 'per_page', type: 'integer', description: '每页条数，默认 15，最大 50', example: 15),
                            new OA\Property(property: 'type', type: 'integer', description: '消息类型：1=系统消息，2=订单消息；不传则返回全部类型', example: 1),
                        ],
                        type: 'object'
                    )
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
                                    new OA\Property(property: 'total', type: 'integer', description: '符合条件的消息总条数', example: 10),
                                    new OA\Property(property: 'count', type: 'integer', description: '当前页返回的条数', example: 10),
                                    new OA\Property(property: 'perPage', type: 'integer', description: '每页条数', example: 15),
                                    new OA\Property(property: 'currentPage', type: 'integer', description: '当前页码', example: 1),
                                    new OA\Property(property: 'lastPage', type: 'integer', description: '最后一页页码', example: 1),
                                    new OA\Property(property: 'firstItem', type: 'integer', description: '当前页第一条消息在全量结果中的序号（从 1 开始）', example: 1),
                                    new OA\Property(property: 'lastItem', type: 'integer', description: '当前页最后一条消息在全量结果中的序号', example: 10),
                                    new OA\Property(property: 'datas', type: 'array', description: '当前页消息列表', items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', description: '消息ID', example: 1),
                                            new OA\Property(property: 'type', type: 'integer', description: '消息类型：1=系统消息，2=订单消息', example: 1),
                                            new OA\Property(property: 'type_label', type: 'string', description: '消息类型中文名称', example: '系统'),
                                            new OA\Property(property: 'title', type: 'string', description: '消息标题', example: '系统升级通知'),
                                            new OA\Property(property: 'image', type: 'string', description: '消息封面图完整 URL（无图时为空字符串）', example: 'https://example.com/uploads/xxx.jpg'),
                                            new OA\Property(property: 'content', type: 'string', description: '消息内容摘要（纯文本或 HTML，详情见 content 完整字段）', example: '系统将于本周六凌晨进行升级维护...'),
                                            new OA\Property(property: 'is_read', type: 'integer', description: '是否已读：0=未读，1=已读', example: 0),
                                            new OA\Property(property: 'created_at', type: 'string', description: '发送时间的相对时间描述（如"10分钟前"）', example: '10分钟前'),
                                            new OA\Property(property: 'relation_type', type: 'string', description: '关联业务类型（如订单号），无关联时为 null', nullable: true, example: null),
                                            new OA\Property(property: 'relation_id', type: 'integer', description: '关联业务ID，无关联时为 null', nullable: true, example: null),
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
        OA\Post(
            path: '/api/messages/{id}',
            summary: '消息详情',
            description: '获取单条消息完整内容；同时把该消息标记为已读。只能查看自己收到的消息。',
            tags: ['消息'],
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
                                    new OA\Property(property: 'id', type: 'integer', description: '消息ID', example: 1),
                                    new OA\Property(property: 'type', type: 'integer', description: '消息类型：1=系统消息，2=订单消息', example: 1),
                                    new OA\Property(property: 'type_label', type: 'string', description: '消息类型中文名称', example: '系统'),
                                    new OA\Property(property: 'title', type: 'string', description: '消息标题', example: '系统升级通知'),
                                    new OA\Property(property: 'image', type: 'string', description: '消息封面图完整 URL（无图时为空字符串）', example: 'https://example.com/uploads/xxx.jpg'),
                                    new OA\Property(property: 'content', type: 'string', description: '消息完整正文（HTML 富文本）', example: '<p>系统将于本周六凌晨进行升级维护，期间服务可能短暂中断...</p>'),
                                    new OA\Property(property: 'is_read', type: 'integer', description: '是否已读：0=未读，1=已读（查看后变为 1）', example: 1),
                                    new OA\Property(property: 'created_at', type: 'string', description: '发送时间的相对时间描述（如"10分钟前"）', example: '10分钟前'),
                                    new OA\Property(property: 'relation_type', type: 'string', description: '关联业务类型（如订单号），无关联时为 null', nullable: true, example: null),
                                    new OA\Property(property: 'relation_id', type: 'integer', description: '关联业务ID，无关联时为 null', nullable: true, example: null),
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
            description: '将当前用户的未读消息批量标记为已读；可传 type 只标记某一类型，不传则全部标记。',
            tags: ['消息'],
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
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'), example: []),
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
