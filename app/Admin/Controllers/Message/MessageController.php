<?php

namespace App\Admin\Controllers\Message;

use App\Admin\Actions\Message\RecallMessage;
use App\Admin\Repositories\Message\Message;
use App\Enums\MessageStatus;
use App\Enums\MessageTargetType;
use App\Enums\MessageType;
use App\Models\User\UserMessage;
use App\Models\User\UserMessageRead;
use App\Models\User\Users;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Displayers\Actions;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class MessageController extends AdminController
{
    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new Message(), function (Grid $grid) {
            $grid->column('id', admin_trans_field('id'))->sortable();

            $grid->column('type', admin_trans_field('type'))->display(function () {
                $type = $this->type;
                $value = $type instanceof MessageType ? $type->value : (int) ($type ?? 0);
                $label = MessageType::options()[$value] ?? $value;

                $styleName = match ($value) {
                    MessageType::SYSTEM->value => 'info',
                    MessageType::ORDER->value => 'primary',
                    default => 'default',
                };

                if ($styleName === 'default') {
                    $background = 'background:#d2d6de;color: #555';
                } else {
                    $color = \Dcat\Admin\Admin::color()->get($styleName, $styleName);
                    $background = "background:{$color}";
                }

                return "<span class='label' style='{$background}'>".e($label).'</span>';
            });

            $grid->column('title', admin_trans_field('title'))->limit(30);

            $grid->column('target_type', admin_trans_field('target_type'))->display(function () {
                $targetType = $this->target_type;
                if ($targetType instanceof MessageTargetType) {
                    return $targetType->label();
                }
                $value = (int) ($targetType ?? 0);
                return MessageTargetType::options()[$value] ?? $value;
            });

            $grid->column('status', admin_trans_field('status'))->display(function () {
                $status = $this->status;
                $value = $status instanceof MessageStatus ? $status->value : (int) ($status ?? 0);
                $label = MessageStatus::options()[$value] ?? $value;

                $styleName = match ($value) {
                    MessageStatus::DRAFT->value => 'default',
                    MessageStatus::SENT->value => 'success',
                    MessageStatus::RECALLED->value => 'warning',
                    default => 'default',
                };

                if ($styleName === 'default') {
                    $background = 'background:#d2d6de;color: #555';
                } else {
                    $color = \Dcat\Admin\Admin::color()->get($styleName, $styleName);
                    $background = "background:{$color}";
                }

                return "<span class='label' style='{$background}'>".e($label).'</span>';
            });

            $grid->column('is_system_generated', admin_trans_field('is_system_generated'))->display(function () {
                return $this->is_system_generated ? '是' : '否';
            });

            $grid->column('created_at', admin_trans_field('created_at'))->sortable();

            $grid->model()->orderBy('created_at', 'desc');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('type', admin_trans_field('type'))->select(MessageType::options());
                $filter->equal('status', admin_trans_field('status'))->select(MessageStatus::options());
                $filter->between('created_at', admin_trans_field('created_at'))->datetime();
            });

            $grid->actions(function (Actions $actions) {
                if ($this->status instanceof MessageStatus && $this->status === MessageStatus::SENT) {
                    $actions->append(new RecallMessage());
                }
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     */
    protected function detail($id): Show
    {
        return Show::make($id, new Message(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            $show->field('type', admin_trans_field('type'))->using(MessageType::options());
            $show->field('title', admin_trans_field('title'));
            $show->field('image', admin_trans_field('image'))->unescape()->as(function ($v) {
                return $v ? '<img src="'.e(full_url($v)).'" style="height:80px;border-radius:4px;">' : '-';
            });
            $show->field('content', admin_trans_field('content'));
            $show->field('target_type', admin_trans_field('target_type'))->using(MessageTargetType::options());
            $show->field('status', admin_trans_field('status'))->using(MessageStatus::options());
            $show->field('is_system_generated', admin_trans_field('is_system_generated'))->using([0 => '否', 1 => '是']);
            // 根据实际业务展示此关联详情
            $show->field('relation_info', admin_trans_field('relation_info'))->unescape()->value('');
            $show->field('created_at', admin_trans_field('created_at'));
            $show->field('updated_at', admin_trans_field('updated_at'));
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new Message(), function (Form $form) {
            $id = $form->model()->id;
            $message = $id ? UserMessage::find($id) : null;

            $form->display('id', admin_trans_field('id'));

            $form->select('type', admin_trans_field('type'))
                ->options(MessageType::options())
                ->required()
                ->default(MessageType::SYSTEM->value);

            $form->text('title', admin_trans_field('title'))->required();

            $form->adminImage('image', admin_trans_field('image'));

            $form->textarea('content', admin_trans_field('content'))->rows(5)->required();

            // 目标用户：自定义选择时级联显示用户多选
            $form->select('target_type', admin_trans_field('target_type'))
                ->options(MessageTargetType::options())
                ->default(MessageTargetType::CUSTOM->value)
                ->when(MessageTargetType::CUSTOM->value, function (Form $form) use ($message) {
                    $selectedUsers = [];
                    if ($message) {
                        $selectedUsers = $message->reads()->pluck('user_id')->toArray();
                    }
                    $users = Users::query()->orderBy('id')->pluck('nickname', 'id');
                    $form->multipleSelect('user_ids', '选择用户')
                        ->options($users)
                        ->value($selectedUsers);
                });

            $form->select('status', admin_trans_field('status'))
                ->options(MessageStatus::options())
                ->default(MessageStatus::DRAFT->value);

            $form->radio('is_system_generated', admin_trans_field('is_system_generated'))
                ->options([0 => '否', 1 => '是'])
                ->default(0);

            // user_ids 是虚拟字段，不保存到 user_messages 表
            $form->ignore('user_ids');

            // saved 回调：维护 user_message_reads 关联表
            $form->saved(function (Form $form) {
                $id = $form->model()->id;
                $targetType = (int) $form->input('target_type');
                $status = (int) $form->input('status');
                $userIds = $form->input('user_ids', []);

                // 先删除该消息的所有关联记录
                UserMessageRead::where('message_id', $id)->delete();

                // 自定义选择且已发送时，插入关联记录
                if ($targetType === MessageTargetType::CUSTOM->value
                    && $status === MessageStatus::SENT->value
                    && ! empty($userIds)) {
                    $now = now();
                    $data = array_map(function ($userId) use ($id, $now): array {
                        return [
                            'message_id' => $id,
                            'user_id' => (int) $userId,
                            'is_read' => 0,
                            'read_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $userIds);
                    UserMessageRead::upsert($data, ['message_id', 'user_id'], ['is_read', 'read_at', 'updated_at']);
                }
            });
        });
    }

    /**
     * 撤回消息（AJAX PUT）
     *
     * @param int|string $id
     * @return mixed
     */
    public function recall($id): mixed
    {
        $message = UserMessage::findOrFail($id);
        $message->status = MessageStatus::RECALLED;
        $message->save();

        return (new \Dcat\Admin\Http\JsonResponse())->success('消息已撤回')->refresh();
    }
}
