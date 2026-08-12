<?php

namespace App\Admin\Form\Field;

use Dcat\Admin\Admin;
use Dcat\Admin\Form\Field;

class MediaSelector extends Field
{
    /**
     * 渲染字段：预览图 + 路径输入框 + 「从媒体库选择」按钮.
     *
     * @return string
     */
    public function render()
    {
        $this->defaultAttribute('type', 'text')
            ->defaultAttribute('name', $this->getElementName())
            ->defaultAttribute('value', $this->value())
            ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
            ->defaultAttribute('placeholder', __('media-selector.placeholder'));

        Admin::style($this->style());
        Admin::script($this->buildScript());

        return $this->inlineHtml();
    }

    /**
     * 字段内联 HTML：预览图 + 路径输入框（保存值）+ 「从媒体库选择」按钮.
     */
    protected function inlineHtml()
    {
        $viewClass = $this->getViewElementClasses();

        $value = $this->value();
        // 已有值时用 full_url 拼完整地址做预览
        $previewUrl = $value ? full_url($value) : '';
        $previewHidden = $value ? '' : ' style="display:none"';

        $label = e($this->label);
        $attributes = $this->formatAttributes();

        return <<<HTML
<div class="{$viewClass['form-group']}">
    <div class="{$viewClass['label']} control-label">
        <span>{$label}</span>
    </div>
    <div class="{$viewClass['field']}">
        <div class="help-block with-errors"></div>
        <div class="input-group">
            <span class="input-group-prepend">
                <img src="{$previewUrl}" class="media-selector-preview"{$previewHidden}>
            </span>
            <input {$attributes}>
            <span class="input-group-append">
                <button type="button" class="btn btn-primary media-selector-open">
                    <i class="feather icon-image"></i>&nbsp;<?= e(__('media-selector.select_button')) ?>
                </button>
            </span>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * 字段样式（缩略图与选择弹窗网格）.
     */
    protected function style()
    {
        return <<<'CSS'
@keyframes ms-spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
.media-selector-preview{height:34px;max-width:60px;border-radius:4px;object-fit:cover;background:#f4f6f8;vertical-align:middle;}
.media-selector-upload{padding:12px 15px;border-bottom:1px solid #e5e7eb;background:#fafbfc;flex-shrink:0;}
.media-selector-list{display:flex;flex-wrap:wrap;align-content:flex-start;gap:12px;}
.media-selector-list .media-selector-item{width:110px;border:1px solid #e5e7eb;border-radius:6px;padding:6px;cursor:pointer;text-align:center;background:#fff;transition:box-shadow .2s;}
.media-selector-list .media-selector-item:hover{border-color:#5b8ff9;box-shadow:0 2px 8px rgba(91,143,249,.25);}
.media-selector-list .media-selector-item img{width:96px;height:72px;object-fit:cover;border-radius:4px;display:block;margin:0 auto 4px;background:#f4f6f8;}
.media-selector-list .media-selector-item span{font-size:12px;color:#555;word-break:break-all;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
CSS;
    }

    /**
     * 字段脚本：点击按钮弹窗（上传区 + 文件列表），点击文件回填路径 + 更新预览.
     */
    protected function buildScript()
    {
        $template = <<<'JS'
(function () {
    var MEDIA_LIST_URL = %s1;
    var MEDIA_UPLOAD_URL = %s2;
    var MS_TEXT = %s3;

    // 渲染单个媒体项（列表加载与上传共用），prepend=true 时插入列表顶部
    function renderItem(item, $box, $input, $preview, layerIndex, prepend) {
        var $item = $('<div class="media-selector-item"></div>');
        $('<img>').attr('src', item.url).attr('loading', 'lazy').appendTo($item);
        $('<span>').text(item.name).appendTo($item);
        $item.on('click', function () {
            $input.val(item.path).trigger('change');
            if (item.url) {
                $preview.attr('src', item.url).show();
            }
            layer.close(layerIndex);
        });
        if (prepend) {
            $box.prepend($item);
        } else {
            $box.append($item);
        }
    }

    Dcat.ready(function () {
        $(document).off('click.dcat-media-selector').on('click.dcat-media-selector', '.media-selector-open', function () {
            var $group = $(this).closest('.input-group');
            var $input = $group.find('input');
            var $preview = $group.find('.media-selector-preview');

            var layerIndex = layer.open({
                type: 1,
                title: MS_TEXT.modal_title,
                area: ['85%', '85%'],
                content: '<div style="height:100%;display:flex;flex-direction:column;">' +
                    '<div class="media-selector-upload">' +
                        '<button type="button" class="btn btn-success media-selector-upload-btn"><i class="feather icon-upload"></i>&nbsp;' + MS_TEXT.upload_button + '</button>' +
                        '<input type="file" accept="image/*" class="media-selector-file" style="display:none;">' +
                    '</div>' +
                    '<div class="media-selector-list" style="flex:1;overflow:auto;"><div class="text-center" style="width:100%;padding:60px 0;color:#999;"><i class="feather icon-loader" style="font-size:36px;display:inline-block;animation:ms-spin 1.2s linear infinite;"></i><br>' + MS_TEXT.loading + '</div></div>' +
                '</div>'
            });

            var $uploadArea = $('.media-selector-upload');
            var $uploadBtn = $uploadArea.find('.media-selector-upload-btn');
            var $fileInput = $uploadArea.find('.media-selector-file');
            var $box = $('.media-selector-list');

            // 上传区交互（绑定弹窗内元素，弹窗关闭即销毁，不累积）
            $uploadBtn.on('click', function () {
                $fileInput.trigger('click');
            });

            $fileInput.on('change', function () {
                var file = this.files && this.files[0];
                if (!file) {
                    return;
                }

                var originalHtml = $uploadBtn.html();
                $uploadBtn.prop('disabled', true).html('<i class="feather icon-loader" style="display:inline-block;animation:ms-spin 1.2s linear infinite;"></i>&nbsp;' + MS_TEXT.uploading);

                var formData = new FormData();
                formData.append('file', file);

                $.ajax({
                    url: MEDIA_UPLOAD_URL,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (res) {
                        $uploadBtn.prop('disabled', false).html(originalHtml);

                        if (!res || res.code !== 0) {
                            Dcat.error((res && res.message) || MS_TEXT.upload_failed);
                            return;
                        }

                        Dcat.success(MS_TEXT.upload_success);
                        // 新图片插入列表顶部；若当前是空态提示则先清空
                        if ($box.children('.text-center').length) {
                            $box.empty();
                        }
                        renderItem(res.data, $box, $input, $preview, layerIndex, true);
                    },
                    error: function () {
                        $uploadBtn.prop('disabled', false).html(originalHtml);
                        Dcat.error(MS_TEXT.upload_failed);
                    }
                });

                this.value = '';
            });

            $.ajax({
                url: MEDIA_LIST_URL,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (!res || res.code !== 0) {
                        Dcat.error((res && res.message) || MS_TEXT.load_failed);
                        layer.close(layerIndex);
                        return;
                    }

                    $box.empty();

                    var list = res.data || [];
                    if (!list.length) {
                        $box.html('<div class="text-center" style="width:100%;padding:60px 0;color:#999;">' + MS_TEXT.empty + '</div>');
                        return;
                    }

                    list.forEach(function (item) {
                        renderItem(item, $box, $input, $preview, layerIndex, false);
                    });
                },
                error: function () {
                    Dcat.error(MS_TEXT.load_failed);
                    layer.close(layerIndex);
                }
            });
        });
    });
})();
JS;

        return str_replace(
            ['%s1', '%s2', '%s3'],
            [
                json_encode(admin_base_path('media-selector/list')),
                json_encode(admin_base_path('media-selector/upload')),
                json_encode([
                    'select_button'  => __('media-selector.select_button'),
                    'upload_button'  => __('media-selector.upload_button'),
                    'modal_title'    => __('media-selector.modal_title'),
                    'uploading'      => __('media-selector.uploading'),
                    'upload_success' => __('media-selector.upload_success'),
                    'empty'          => __('media-selector.empty'),
                    'load_failed'    => __('media-selector.load_failed'),
                    'upload_failed'  => __('media-selector.upload_failed'),
                    'loading'        => __('media-selector.loading'),
                ]),
            ],
            $template
        );
    }
}
