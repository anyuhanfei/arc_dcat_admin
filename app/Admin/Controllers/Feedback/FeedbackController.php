<?php

namespace App\Admin\Controllers\Feedback;

use App\Admin\Repositories\Feedback\Feedback;
use App\Models\User\UserFeedback;
use App\Models\User\Users;
use App\Enums\FeedbackStatus;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class FeedbackController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Feedback(), function (Grid $grid) {
            $grid->column('id', admin_trans_field('id'))->sortable();
            $grid->column('user_id', admin_trans_field('user_id'))->display(function ($v) {
                $user = Users::find($v);
                return $user ? admin_user_card($user) : '已注销';
            });
            $grid->column('title', admin_trans_field('title'))->limit(30);
            $grid->column('status', admin_trans_field('status'))->display(function () {
                $status = $this->status;
                $value = $status instanceof FeedbackStatus ? $status->value : (int) ($status ?? 0);
                $label = FeedbackStatus::options()[$value] ?? $value;

                $styleName = match ($value) {
                    FeedbackStatus::PENDING->value => 'warning',
                    FeedbackStatus::PROCESSING->value => 'info',
                    FeedbackStatus::COMPLETED->value => 'success',
                    FeedbackStatus::ON_HOLD->value => 'default',
                    default => 'default',
                };

                // 完全模拟 Dcat Admin Label::formatStyle() 的逻辑
                if ($styleName === 'default') {
                    $background = 'background:#d2d6de;color: #555';
                } else {
                    $color = \Dcat\Admin\Admin::color()->get($styleName, $styleName);
                    $background = "background:{$color}";
                }

                return "<span class='label' style='{$background}'>".e($label).'</span>';
            });
            $grid->column('handler_id', admin_trans_field('handler_id'))->display(function ($v) {
                if (! $v) {
                    return '-';
                }
                $admin = \Dcat\Admin\Models\Administrator::find($v);
                return $admin ? $admin->name : '-';
            });
            $grid->column('created_at', admin_trans_field('created_at'))->sortable();
            $grid->column('handled_at', admin_trans_field('handled_at'));

            $grid->model()->orderBy('created_at', 'desc');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('user_id', admin_trans_field('user_id'));
                $filter->equal('status', admin_trans_field('status'))->select(FeedbackStatus::options());
                $filter->like('contact_phone', admin_trans_field('contact_phone'));
                $filter->between('created_at', admin_trans_field('created_at'))->datetime();
            });

            $grid->disableCreateButton();
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Feedback(), function (Show $show) {
            $show->field('id', admin_trans_field('id'));
            $show->field('user_id', admin_trans_field('user_id'))->display(function ($v) {
                $user = Users::find($v);
                return $user ? admin_user_card($user) : '已注销';
            });
            $show->field('title', admin_trans_field('title'));
            $show->field('content', admin_trans_field('content'));
            $show->field('images', admin_trans_field('images'))->unescape()->as(function ($v) {
                if (! is_array($v) || empty($v)) {
                    return '-';
                }
                $html = '';
                foreach ($v as $img) {
                    $html .= '<img src="'.e(full_url($img)).'" style="height:80px;margin:2px;border-radius:4px;">';
                }
                return $html;
            });
            $show->field('contact_name', admin_trans_field('contact_name'));
            $show->field('contact_phone', admin_trans_field('contact_phone'));
            $show->field('status', admin_trans_field('status'))->display(function ($v) {
                return $v instanceof FeedbackStatus ? $v->value : $v;
            })->using(FeedbackStatus::options());
            $show->field('handler_id', admin_trans_field('handler_id'))->display(function ($v) {
                if (! $v) {
                    return '-';
                }
                $admin = \Dcat\Admin\Models\Administrator::find($v);
                return $admin ? $admin->name : '-';
            });
            $show->field('handled_at', admin_trans_field('handled_at'));
            $show->field('reply', admin_trans_field('reply'));
            $show->field('replied_at', admin_trans_field('replied_at'));
            $show->field('admin_remark', admin_trans_field('admin_remark'));
            $show->field('created_at', admin_trans_field('created_at'));
            $show->field('updated_at', admin_trans_field('updated_at'));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Feedback(), function (Form $form) {
            $id = $form->model()->id;
            $feedback = $id ? UserFeedback::find($id) : null;

            $form->display('id', admin_trans_field('id'));

            // 提交用户（display 不自动从 model 取值，需手动 ->value()）
            $user = $feedback?->user;
            $form->display('user_info', admin_trans_field('user_id'))->value($user ? admin_user_card($user) : '已注销');

            $form->display('title_show', admin_trans_field('title'))->value($feedback?->title ?? '');
            $form->display('content_show', admin_trans_field('content'))->value($feedback?->content ?? '');

            // 图片展示
            $imagesHtml = '-';
            if (is_array($feedback?->images) && ! empty($feedback->images)) {
                $imagesHtml = '';
                foreach ($feedback->images as $img) {
                    $imagesHtml .= '<img src="'.e(full_url($img)).'" style="height:80px;margin:2px;border-radius:4px;">';
                }
            }
            $form->display('images_show', admin_trans_field('images'))->value($imagesHtml);

            $form->display('contact_name_show', admin_trans_field('contact_name'))->value($feedback?->contact_name ?? '');
            $form->display('contact_phone_show', admin_trans_field('contact_phone'))->value($feedback?->contact_phone ?? '');

            // 状态字段：根据当前状态动态设置可选选项
            $currentStatus = $feedback?->status;
            if ($currentStatus instanceof FeedbackStatus && $currentStatus === FeedbackStatus::COMPLETED) {
                // 处理完成是最终状态，只读展示
                $form->display('status_show', admin_trans_field('status'))->value($currentStatus->label());
            } else {
                $options = FeedbackStatus::options();
                if ($currentStatus instanceof FeedbackStatus) {
                    if ($currentStatus === FeedbackStatus::PROCESSING || $currentStatus === FeedbackStatus::ON_HOLD) {
                        // 处理中/搁置状态不可回退到待处理
                        unset($options[FeedbackStatus::PENDING->value]);
                    }
                }
                $form->select('status', admin_trans_field('status'))->options($options)->required();
            }

            // 处理人（只读展示）
            $handlerName = '-';
            if ($feedback?->handler_id) {
                $admin = \Dcat\Admin\Models\Administrator::find($feedback->handler_id);
                $handlerName = $admin ? $admin->name : '-';
            }
            $form->display('handler_name', admin_trans_field('handler_id'))->value($handlerName);

            $form->display('handled_at_show', admin_trans_field('handled_at'))->value($feedback?->handled_at ? $feedback->handled_at->format('Y-m-d H:i:s') : '-');

            $form->textarea('reply', admin_trans_field('reply'))->rows(3);
            $form->display('replied_at_show', admin_trans_field('replied_at'))->value($feedback?->replied_at ? $feedback->replied_at->format('Y-m-d H:i:s') : '-');
            $form->textarea('admin_remark', admin_trans_field('admin_remark'))->rows(3);

            // 状态流转自动处理（只读字段不经过 form 持久化，直接用 Model 更新数据库）
            $form->saving(function (Form $form) {
                $id = $form->model()->id;
                if (! $id) {
                    return;
                }

                $original = UserFeedback::find($id);
                $newStatus = (int) $form->input('status');
                $oldStatus = $original?->status;

                // 处理完成是最终状态，强制不可修改
                if ($oldStatus instanceof FeedbackStatus && $oldStatus === FeedbackStatus::COMPLETED) {
                    $form->input('status', FeedbackStatus::COMPLETED->value);
                    $newStatus = FeedbackStatus::COMPLETED->value;
                }

                $updates = [];

                // 1. 状态变为处理中且无处理人：自动设置为当前登录管理员
                if ($newStatus === FeedbackStatus::PROCESSING->value && empty($original?->handler_id)) {
                    $updates['handler_id'] = Admin::user()->id;
                    $updates['handled_at'] = now();
                }

                // 2. 状态变为处理完成或搁置：自动设置/更新处理时间
                if (in_array($newStatus, [FeedbackStatus::COMPLETED->value, FeedbackStatus::ON_HOLD->value], true)) {
                    $updates['handler_id'] = Admin::user()->id;
                    $updates['handled_at'] = now();
                }

                // 3. 状态从搁置变为处理中：清除处理时间
                if ($oldStatus instanceof FeedbackStatus && $oldStatus === FeedbackStatus::ON_HOLD && $newStatus === FeedbackStatus::PROCESSING->value) {
                    $updates['handler_id'] = Admin::user()->id;
                    $updates['handled_at'] = null;
                }

                // 4. 对用户回复有变更且非空：自动更新回复时间
                $newReply = $form->input('reply');
                $oldReply = $original?->reply;
                if ($newReply !== $oldReply && $newReply !== null && $newReply !== '') {
                    $updates['replied_at'] = now();
                }

                if (! empty($updates)) {
                    UserFeedback::where('id', $id)->update($updates);
                }
            });
        });
    }
}
