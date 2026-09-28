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
     * 字段内联 HTML：预览卡片（可点击/拖拽上传）+ 路径输入框 + 「从媒体库选择」按钮.
     */
    protected function inlineHtml()
    {
        $viewClass = $this->getViewElementClasses();

        $value = $this->value();
        // 已有值时用 full_url 拼完整地址做预览
        $previewUrl = $value ? full_url($value) : '';
        $hasValue = (bool) $value;
        $previewStyle = $hasValue ? '' : ' style="display:none"';
        $placeholderStyle = $hasValue ? ' style="display:none"' : '';
        $noImageText = e(__('media-selector.no_image'));
        $clickUploadText = e(__('media-selector.click_upload'));
        $replaceText = e(__('media-selector.replace'));

        $label = e($this->label);
        $attributes = $this->formatAttributes();

        return <<<HTML
<div class="{$viewClass['form-group']}">
    <div class="{$viewClass['label']} control-label">
        <span>{$label}</span>
    </div>
    <div class="{$viewClass['field']}">
        <div class="help-block with-errors"></div>
        <div class="media-selector-card">
            <div class="media-selector-dropzone media-selector-preview-wrap" title="{$clickUploadText}">
                <img src="{$previewUrl}" class="media-selector-preview"{$previewStyle}>
                <div class="media-selector-placeholder"{$placeholderStyle}>
                    <i class="feather icon-image"></i>
                    <span>{$noImageText}</span>
                    <span class="media-selector-drop-hint">{$clickUploadText}</span>
                </div>
                <div class="media-selector-replace-mask"{$placeholderStyle}>{$replaceText}</div>
                <input type="file" accept="image/*" class="media-selector-direct-file" style="display:none;">
            </div>
            <div class="input-group media-selector-input-group">
                <input {$attributes}>
                <span class="input-group-append">
                    <button type="button" class="btn btn-primary media-selector-open">
                        <i class="feather icon-image"></i>&nbsp;<?= e(__('media-selector.select_button')) ?>
                    </button>
                </span>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * 字段样式（预览卡片与选择弹窗网格）.
     */
    protected function style()
    {
        return <<<'CSS'
@keyframes ms-spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
.media-selector-card{margin-bottom:8px;}
.media-selector-preview-wrap{width:160px;height:160px;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;background:#f4f6f8;display:flex;align-items:center;justify-content:center;margin-bottom:8px;}
.media-selector-preview{width:100%;height:100%;object-fit:cover;display:block;}
.media-selector-placeholder{color:#bbb;font-size:13px;text-align:center;line-height:1.4;}
.media-selector-placeholder i{font-size:28px;display:block;margin-bottom:4px;}
.media-selector-input-group{width:160px;}
.media-selector-upload{padding:12px 15px;border-bottom:1px solid #e5e7eb;background:#fafbfc;flex-shrink:0;display:flex;align-items:center;}
.media-selector-drag-hint{margin-left:12px;color:#999;font-size:12px;}
.media-selector-list{display:flex;flex-wrap:wrap;align-content:flex-start;gap:12px;padding:12px;}
.media-selector-list .media-selector-item{width:110px;border:1px solid #e5e7eb;border-radius:6px;padding:6px;cursor:pointer;text-align:center;background:#fff;transition:box-shadow .2s;}
.media-selector-list .media-selector-item:hover{border-color:#5b8ff9;box-shadow:0 2px 8px rgba(91,143,249,.25);}
.media-selector-list .media-selector-item img{width:96px;height:72px;object-fit:cover;border-radius:4px;display:block;margin:0 auto 4px;background:#f4f6f8;}
.media-selector-list .media-selector-item span{font-size:12px;color:#555;word-break:break-all;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.media-selector-modal.ms-drag-over{box-shadow:inset 0 0 0 3px #5b8ff9;background:rgba(91,143,249,.05);}
/* 直接上传卡片：可点击/拖拽 */
.media-selector-dropzone{position:relative;cursor:pointer;transition:border-color .2s,box-shadow .2s;}
.media-selector-dropzone:hover{border-color:#5b8ff9;box-shadow:0 0 0 2px rgba(91,143,249,.15);}
.media-selector-dropzone.ms-direct-dragover{border-color:#5b8ff9;box-shadow:inset 0 0 0 3px #5b8ff9;background:rgba(91,143,249,.08);}
.media-selector-drop-hint{display:block;font-size:12px;color:#5b8ff9;margin-top:4px;}
.media-selector-replace-mask{position:absolute;inset:0;background:rgba(0,0,0,.55);color:#fff;font-size:13px;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s;pointer-events:none;}
.media-selector-dropzone:hover .media-selector-replace-mask{opacity:1;}
.media-selector-dropzone .feather.icon-loader{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:28px;color:#5b8ff9;animation:ms-spin 1.2s linear infinite;}
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
    function renderItem(item, $box, $input, $preview, $placeholder, layerIndex, prepend) {
        var $item = $('<div class="media-selector-item"></div>');
        $('<img>').attr('src', item.url).attr('loading', 'lazy').appendTo($item);
        $('<span>').text(item.name).appendTo($item);
        $item.on('click', function () {
            $input.val(item.path).trigger('change');
            if (item.url) {
                $preview.attr('src', item.url).show();
                $placeholder.hide();
            }
            layer.close(layerIndex);
        });
        if (prepend) {
            $box.prepend($item);
        } else {
            $box.append($item);
        }
    }

    // 批量上传（点击按钮选择 / 拖拽进入弹窗共用）
    function uploadFiles(files, $uploadBtn, $box, $input, $preview, $placeholder, layerIndex) {
        if (!files || !files.length) {
            return;
        }

        var originalHtml = $uploadBtn.html();
        $uploadBtn.prop('disabled', true).html('<i class="feather icon-loader" style="display:inline-block;animation:ms-spin 1.2s linear infinite;"></i>&nbsp;' + MS_TEXT.uploading);

        var formData = new FormData();
        for (var i = 0; i < files.length; i++) {
            formData.append('file[]', files[i]);
        }

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

                var items = res.data || [];
                Dcat.success(MS_TEXT.upload_success + (items.length > 1 ? ' (' + items.length + ')' : ''));
                // 新图片插入列表顶部；若当前是空态提示则先清空
                if ($box.children('.text-center').length) {
                    $box.empty();
                }
                items.forEach(function (item) {
                    renderItem(item, $box, $input, $preview, $placeholder, layerIndex, true);
                });
            },
            error: function () {
                $uploadBtn.prop('disabled', false).html(originalHtml);
                Dcat.error(MS_TEXT.upload_failed);
            }
        });
    }

    // 卡片直接上传：点击选择 / 拖拽文件到卡片，上传成功后回填路径和预览（不弹窗）
    function directUpload(files, $card) {
        if (!files || !files.length) return;

        var $input = $card.find('input');
        var $preview = $card.find('.media-selector-preview');
        var $placeholder = $card.find('.media-selector-placeholder');
        var $mask = $card.find('.media-selector-replace-mask');

        var $zone = $card.find('.media-selector-dropzone');
        $zone.css('pointer-events', 'none');
        $zone.append('<i class="feather icon-loader"></i>');

        var formData = new FormData();
        formData.append('file[]', files[0]);

        $.ajax({
            url: MEDIA_UPLOAD_URL,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                $zone.css('pointer-events', '').find('.icon-loader').remove();
                if (!res || res.code !== 0) {
                    Dcat.error((res && res.message) || MS_TEXT.upload_failed);
                    return;
                }
                var item = (res.data || [])[0];
                if (!item) { Dcat.error(MS_TEXT.upload_failed); return; }

                $input.val(item.path).trigger('change');
                if (item.url) {
                    $preview.attr('src', item.url).show();
                    $placeholder.hide();
                    $mask.show();
                }
                Dcat.success(MS_TEXT.upload_success);
            },
            error: function () {
                $zone.css('pointer-events', '').find('.icon-loader').remove();
                Dcat.error(MS_TEXT.upload_failed);
            }
        });
    }

    // 卡片点击：触发隐藏 file input
    $(document).off('click.dcat-ms-drop').on('click.dcat-ms-drop', '.media-selector-dropzone', function (e) {
        // 点在遮罩上也允许上传
        $(this).closest('.media-selector-card').find('.media-selector-direct-file').trigger('click');
    });

    // file input 变化：直接上传（阻止冒泡，避免触发卡片再次 click）
    $(document).off('change.dcat-ms-file').on('change.dcat-ms-file', '.media-selector-direct-file', function (e) {
        e.stopPropagation();
        var $card = $(this).closest('.media-selector-card');
        directUpload(this.files, $card);
        this.value = '';
    });

    // 拖拽到卡片
    $(document).off('dragover.dcat-ms-drop dragleave.dcat-ms-drop drop.dcat-ms-drop')
        .on('dragover.dcat-ms-drop', '.media-selector-dropzone', function (e) {
            e.preventDefault(); e.stopPropagation();
            $(this).addClass('ms-direct-dragover');
        })
        .on('dragleave.dcat-ms-drop', '.media-selector-dropzone', function (e) {
            e.preventDefault(); e.stopPropagation();
            $(this).removeClass('ms-direct-dragover');
        })
        .on('drop.dcat-ms-drop', '.media-selector-dropzone', function (e) {
            e.preventDefault(); e.stopPropagation();
            $(this).removeClass('ms-direct-dragover');
            var files = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
            directUpload(files, $(this).closest('.media-selector-card'));
        });

    $(document).off('click.dcat-media-selector').on('click.dcat-media-selector', '.media-selector-open', function () {
            var $card = $(this).closest('.media-selector-card');
            var $input = $card.find('input');
            var $preview = $card.find('.media-selector-preview');
            var $placeholder = $card.find('.media-selector-placeholder');

            var layerIndex = layer.open({
                type: 1,
                title: MS_TEXT.modal_title,
                area: ['85%', '85%'],
                content: '<div class="media-selector-modal" style="height:100%;display:flex;flex-direction:column;">' +
                    '<div class="media-selector-upload">' +
                        '<button type="button" class="btn btn-success media-selector-upload-btn"><i class="feather icon-upload"></i>&nbsp;' + MS_TEXT.upload_button + '</button>' +
                        '<input type="file" accept="image/*" multiple class="media-selector-file" style="display:none;">' +
                        '<span class="media-selector-drag-hint">' + MS_TEXT.drag_hint + '</span>' +
                    '</div>' +
                    '<div class="media-selector-list" style="flex:1;overflow:auto;"><div class="text-center" style="width:100%;padding:60px 0;color:#999;"><i class="feather icon-loader" style="font-size:36px;display:inline-block;animation:ms-spin 1.2s linear infinite;"></i><br>' + MS_TEXT.loading + '</div></div>' +
                '</div>'
            });

            var $layer = $('#layui-layer' + layerIndex);
            var $uploadArea = $layer.find('.media-selector-upload');
            var $uploadBtn = $uploadArea.find('.media-selector-upload-btn');
            var $fileInput = $uploadArea.find('.media-selector-file');
            var $box = $layer.find('.media-selector-list');

            // 上传区交互（绑定弹窗内元素，弹窗关闭即销毁，不累积）
            $uploadBtn.on('click', function () {
                $fileInput.trigger('click');
            });

            $fileInput.on('change', function () {
                uploadFiles(this.files, $uploadBtn, $box, $input, $preview, $placeholder, layerIndex);
                this.value = '';
            });

            // 拖拽上传：整个弹窗内容区均可拖入多个文件
            $layer.on('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('ms-drag-over');
            });
            $layer.on('dragleave', function (e) {
                e.preventDefault();
                $(this).removeClass('ms-drag-over');
            });
            $layer.on('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('ms-drag-over');
                var files = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
                uploadFiles(files, $uploadBtn, $box, $input, $preview, $placeholder, layerIndex);
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
                        renderItem(item, $box, $input, $preview, $placeholder, layerIndex, false);
                    });
                },
                error: function () {
                    Dcat.error(MS_TEXT.load_failed);
                    layer.close(layerIndex);
                }
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
                    'drag_hint'      => __('media-selector.drag_hint'),
                ]),
            ],
            $template
        );
    }
}
