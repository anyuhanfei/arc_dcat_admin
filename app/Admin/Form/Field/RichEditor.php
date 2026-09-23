<?php

namespace App\Admin\Form\Field;

use Dcat\Admin\Admin;
use Dcat\Admin\Form\Field;

/**
 * 富文本编辑器字段（基于 wangEditor v5）.
 *
 * 用法：$form->richEditor('content', '正文');
 *       $form->richEditor('content', '正文')->height(600);
 *
 * 特性：
 * - 图片 / 视频 / 音频上传（统一走 admin/rich-editor/upload）
 * - 编辑回显稳定（setHtml / getHtml，视频和音频均用 <video> 标签，不丢链）
 * - 音频说明：wangEditor v5 不支持自定义 void 元素，故音频用 HTML5 <video> 标签嵌入播放（原生支持音频）
 * - Dcat 弹窗表单内浮层高 z-index，不被弹窗压住
 */
class RichEditor extends Field
{
    /** 编辑器高度（px） */
    protected int $editorHeight = 500;

    /**
     * 设置编辑器高度.
     */
    public function height(int $px): static
    {
        $this->editorHeight = $px;

        return $this;
    }

    public function render()
    {
        // 容器 ID 加 uniqid 后缀，避免 Dcat 弹窗重复打开时同一 selector 冲突
        $fieldId = 'rich-editor-'.$this->column.'-'.uniqid();

        $this->defaultAttribute('type', 'hidden')
            ->defaultAttribute('name', $this->getElementName())
            ->defaultAttribute('value', $this->value())
            ->defaultAttribute('id', $fieldId);

        // 加载 wangEditor 静态资源（项目本地，不走 CDN）
        Admin::css('/vendor/wangeditor/style.css');
        Admin::js('/vendor/wangeditor/index.js');

        Admin::style($this->style());
        Admin::script($this->buildScript($fieldId));

        return $this->inlineHtml($fieldId);
    }

    protected function inlineHtml(string $fieldId): string
    {
        $viewClass = $this->getViewElementClasses();
        $label = e($this->label);
        $attributes = $this->formatAttributes();
        $editorId = $fieldId.'-editor';
        $toolbarId = $fieldId.'-toolbar';
        $height = (int) $this->editorHeight;

        return <<<HTML
<div class="{$viewClass['form-group']}">
    <div class="{$viewClass['label']} control-label">
        <span>{$label}</span>
    </div>
    <div class="{$viewClass['field']}">
        <div class="help-block with-errors"></div>
        <input {$attributes}>
        <div id="{$toolbarId}" style="border:1px solid #ccc;border-bottom:none;"></div>
        <div id="{$editorId}" style="border:1px solid #ccc;height:{$height}px;overflow-y:auto;"></div>
    </div>
</div>
HTML;
    }

    protected function style(): string
    {
        return <<<'CSS'
/* wangEditor 浮层高 z-index：Dcat layer 弹窗 zIndex 起始 19891014，必须超过它 */
.w-e-modal,
.w-e-drop-panel,
.w-e-textarea-tooltip,
.w-e-bar-dropdown {
    z-index: 99999999 !important;
}
/* 编辑器内容区视频/音频自适应（音频也用 video 标签播放） */
.w-e-text-container video {
    max-width: 100%;
    min-height: 40px;
}
CSS;
    }

    protected function buildScript(string $fieldId): string
    {
        $uploadUrl = admin_base_path('rich-editor/upload');
        $editorId = $fieldId.'-editor';
        $toolbarId = $fieldId.'-toolbar';
        $columnKey = $this->column;

        $template = <<<'JS'
(function () {
    var UPLOAD_URL = %s1;
    var FIELD_ID = %s2;
    var EDITOR_ID = %s3;
    var TOOLBAR_ID = %s4;
    var COLUMN_KEY = %s5;

    if (!window.wangEditor) {
        console.error('[rich-editor] wangEditor 未加载，请检查 /vendor/wangeditor/index.js');
        return;
    }

    var Boot = window.wangEditor.Boot;
    var createEditor = window.wangEditor.createEditor;
    var createToolbar = window.wangEditor.createToolbar;

    // Dcat 弹窗关闭后 DOM 被销毁但 editor 实例残留，再次打开时先销毁旧实例
    if (!window.__richEditors) window.__richEditors = {};
    if (!window.__richToolbars) window.__richToolbars = {};
    if (window.__richEditors[COLUMN_KEY]) {
        try { window.__richEditors[COLUMN_KEY].destroy(); } catch (e) {}
        try { window.__richToolbars[COLUMN_KEY].destroy(); } catch (e) {}
        delete window.__richEditors[COLUMN_KEY];
        delete window.__richToolbars[COLUMN_KEY];
    }

    // 统一上传函数（图片/视频/音频共用）
    function doUpload(file, onSuccess) {
        var formData = new FormData();
        formData.append('file', file);
        $.ajax({
            url: UPLOAD_URL,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (!res || res.code !== 0) {
                    Dcat.error((res && res.message) || '上传失败');
                    return;
                }
                onSuccess(res.data.url);
            },
            error: function () {
                Dcat.error('上传失败');
            }
        });
    }

    // 注册音频上传菜单（全局只注册一次）
    // factory 返回菜单对象，wangEditor 调用 factory() 创建实例
    if (!window.__richAudioMenuRegistered) {
        window.__richAudioMenuRegistered = true;
        Boot.registerMenu({
            key: 'audioUpload',
            factory: function () {
                return {
                    tag: 'button',
                    title: '音频',
                    iconSvg: '<svg viewBox="0 0 1024 1024" width="1em" height="1em"><path d="M768 256a32 32 0 0 1 32 32v448a32 32 0 0 1-32 32h-32a32 32 0 0 1-32-32V288a32 32 0 0 1 32-32h32zM512 128a32 32 0 0 1 32 32v704a32 32 0 0 1-32 32h-32a32 32 0 0 1-32-32V160a32 32 0 0 1 32-32h32zM256 384a32 32 0 0 1 32 32v256a32 32 0 0 1-32 32h-32a32 32 0 0 1-32-32V416a32 32 0 0 1 32-32h32z" fill="currentColor"></path></svg>',
                    getValue: function () { return ''; },
                    isActive: function () { return false; },
                    isDisabled: function () { return false; },
                    exec: function (editor) {
                        var input = document.createElement('input');
                        input.type = 'file';
                        input.accept = '.mp3,.wav,.ogg,.m4a,.aac,.flac,.wma,audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/aac';
                        input.onchange = function () {
                            var file = this.files && this.files[0];
                            if (!file) return;
                            doUpload(file, function (url) {
                                editor.dangerouslyInsertHtml('<p><video controls src="' + url + '" style="max-width:100%;"></video></p>');
                            });
                        };
                        input.click();
                    }
                };
            }
        });
    }

    var $input = $('#' + FIELD_ID);

    var editor = createEditor({
        selector: '#' + EDITOR_ID,
        html: $input.val() || '<p><br></p>',
        config: {
            placeholder: '请输入内容...',
            MENU_CONF: {
                uploadImage: {
                    customUpload: function (file, insertFn) {
                        doUpload(file, function (url) {
                            insertFn(url, file.name, url);
                        });
                    }
                },
                uploadVideo: {
                    // 视频按钮同时接受音频文件（后端自动识别类型，用 video 标签嵌入播放）
                    accept: 'video/*,audio/*',
                    customUpload: function (file, insertFn) {
                        doUpload(file, function (url) {
                            insertFn(url, '');
                        });
                    }
                }
            },
            onChange: function (ed) {
                $input.val(ed.getHtml());
            }
        },
        mode: 'default'
    });

    var toolbar = createToolbar({
        editor: editor,
        selector: '#' + TOOLBAR_ID,
        config: {
            insertKeys: {
                index: 24,
                keys: ['audioUpload']
            }
        },
        mode: 'default'
    });

    // 保存实例，下次打开弹窗时先销毁
    window.__richEditors[COLUMN_KEY] = editor;
    window.__richToolbars[COLUMN_KEY] = toolbar;
})();
JS;

        return str_replace(
            ['%s1', '%s2', '%s3', '%s4', '%s5'],
            [
                json_encode($uploadUrl),
                json_encode($fieldId),
                json_encode($editorId),
                json_encode($toolbarId),
                json_encode($columnKey),
            ],
            $template
        );
    }
}
