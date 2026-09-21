<?php

namespace App\Admin\Actions\Message;

use Dcat\Admin\Grid\RowAction;

class RecallMessage extends RowAction
{
    /**
     * 按钮标题
     */
    public function title(): string
    {
        return __('message.buttons.recall');
    }

    /**
     * 按钮 HTML：点击弹确认框，确认后 AJAX PUT 撤回
     */
    public function html(): string
    {
        $url = admin_base_path('messages/' . $this->getKey() . '/recall');

        return <<<HTML
<a href="javascript:void(0)" data-url="{$url}" data-action="recall-message" style="color:#dd4b39;">
    <i class="feather icon-rotate-ccw"></i> {$this->title()}
</a>
<script>
if (!window.__recall_message_bound) {
    window.__recall_message_bound = true;
    $(document).on('click', '[data-action="recall-message"]', function () {
        var url = $(this).data('url');
        Dcat.confirm('确认撤回该消息？撤回后用户端将不再显示。', function () {
            $.ajax({
                url: url,
                type: 'PUT',
                success: function (res) {
                    if (res.status || res.code) {
                        Dcat.success(res.msg || '操作成功');
                        Dcat.reload();
                    } else {
                        Dcat.error(res.msg || '操作失败');
                    }
                },
                error: function () {
                    Dcat.error('请求失败');
                }
            });
        });
    });
}
</script>
HTML;
    }
}
