<?php

namespace App\Admin\Controllers\Sys;

use App\Admin\Repositories\Sys\SysNoticeReads;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;

class SysNoticeReadsController extends AdminController
{
    /**
     * 当前公告 ID（index 中赋值，grid 固定筛选）
     *
     * @var int|null
     */
    protected $noticeId;

    /**
     * 当前公告标题
     *
     * @var string|null
     */
    protected $noticeTitle;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SysNoticeReads(), function (Grid $grid) {
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableBatchActions();

            $grid->column('user.id', __('sys-notice-reads.fields.user_id'));
            $grid->column('user.nickname', __('sys-notice-reads.fields.nickname'));
            $grid->column('user.avatar', __('sys-notice-reads.fields.avatar'))->display(fn ($v) => $v ? '<img src="'.e(full_url($v)).'" style="height:40px;border-radius:4px;">' : '');
            $grid->column('user.phone', __('sys-notice-reads.fields.phone'));
            $grid->column('user.email', __('sys-notice-reads.fields.email'));

            // 固定筛选当前公告（弹框带 notice_id 传入，不被用户筛选顶掉）
            $grid->model()->where('notice_id', $this->noticeId);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('user.id', __('sys-notice-reads.fields.user_id'));
                $filter->like('user.nickname', __('sys-notice-reads.fields.nickname'));
                $filter->like('user.phone', __('sys-notice-reads.fields.phone'));
                $filter->like('user.email', __('sys-notice-reads.fields.email'));
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $noticeId = (int) request('notice_id', 0);
        $notice = \App\Models\Sys\SysNotices::find($noticeId);
        if (! $notice) {
            // 未传或不存在时默认最新公告，防止空列表
            $notice = \App\Models\Sys\SysNotices::query()->orderByDesc('id')->first();
        }

        $this->noticeId = $notice?->id;
        $this->noticeTitle = $notice?->title;

        $title = $this->noticeId
            ? '公告 #'.$this->noticeId.'：'.($this->noticeTitle ?? '')
            : '暂无公告';

        return $content
            ->title($title)
            ->description(__('sys-notice-reads.labels.SysNoticeReadsController'))
            ->body($this->grid());
    }
}
