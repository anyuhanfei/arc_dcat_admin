<?php

namespace App\Admin\Form\Field\Concerns;

use Dcat\Admin\Admin;
use Intervention\Image\Facades\Image as InterventionImage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 图片上传字段共享配置（自动上传 / 重命名 / 日期目录 / 缩略图 / 媒体库按钮）.
 *
 * 在 AdminImage 和 AdminMultipleImage 中 use 本 trait.
 */
trait HasMediaUpload
{
    /**
     * 媒体库弹窗是否多选.
     */
    protected bool $mediaMultiple = false;

    /**
     * 缩略图最大宽度（px）.
     */
    protected int $thumbWidth = 400;

    /**
     * 默认配置：自动上传、日期目录、重命名.
     */
    protected function setupMediaUploadDefaults(): void
    {
        // 自动上传（选择图片后立即上传，隐藏手动上传按钮）
        $this->autoUpload();

        // 上传目录：按日期分目录（disk root 已是 public/uploads）
        $this->dir(date('Ymd'));

        // 文件重命名：Ymd_His_随机8位.ext
        $this->name(function (UploadedFile $file) {
            return date('Ymd_His').'_'.\Illuminate\Support\Str::random(8).'.'.$file->getClientOriginalExtension();
        });

        // 大小限制 10MB
        $this->maxSize(10240);

        // 仅图片
        $this->accept('jpg,jpeg,png,gif,webp', 'image/*');
    }

    /**
     * 设置媒体库多选模式.
     */
    public function mediaMultiple(bool $multiple = true): static
    {
        $this->mediaMultiple = $multiple;

        return $this;
    }

    /**
     * 设置缩略图宽度.
     */
    public function thumbWidth(int $width): static
    {
        $this->thumbWidth = $width;

        return $this;
    }

    /**
     * 上传后生成 thumb 前缀缩略图.
     *
     * 原图：uploads/20260928/abc.jpg
     * 缩略图：uploads/20260928/thumbabc.jpg
     */
    protected function generateThumb(UploadedFile $file): void
    {
        try {
            $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
            $image = $manager->make($file->getRealPath());

            // 等比缩放，不放大原图
            if ($image->width() > $this->thumbWidth) {
                $image->resize($this->thumbWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $dir = $this->getDirectory();
            $thumbName = 'thumb'.$this->name;
            $encoded = (string) $image->encode(null, 80);

            $this->getStorage()->put("{$dir}/{$thumbName}", $encoded);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('生成图片缩略图失败: '.$e->getMessage(), [
                'file' => $file->getClientOriginalName(),
            ]);
        }
    }

    /**
     * 删除表单字段中的图片时，不物理删除磁盘文件（文件属于媒体库共享资源）.
     */
    public function deleteFile($paths)
    {
        // 不删除磁盘文件，仅从表单字段中移除
    }

    /**
     * 渲染时注入媒体库弹窗 JS.
     */
    protected function renderMediaScript(): void
    {
        Admin::script($this->buildMediaScript());
    }

    /**
     * 构建媒体库弹窗 JS.
     */
    protected function buildMediaScript(): string
    {
        $multiple = $this->mediaMultiple ? 'true' : 'false';
        $listUrl = json_encode(admin_base_path('media-selector/list'));
        $uploadUrl = json_encode(admin_base_path('media-selector/upload'));
        $deleteUrl = json_encode(admin_base_path('media-selector/delete'));

        $texts = json_encode([
            'select_button'  => __('media-selector.select_button'),
            'modal_title'   => __('media-selector.modal_title'),
            'empty'         => __('media-selector.empty'),
            'load_failed'   => __('media-selector.load_failed'),
            'drag_hint'     => __('media-selector.drag_hint'),
            'confirm'       => __('media-selector.confirm_select') ?? '确定选择',
            'page_prev'     => '上一页',
            'page_next'     => '下一页',
        ], JSON_UNESCAPED_UNICODE);

        return <<<JS
(function () {
    var MEDIA_LIST_URL = {$listUrl};
    var UPLOAD_URL = {$uploadUrl};
    var MS_TEXT = {$texts};
    var IS_MULTIPLE = {$multiple};
    var PAGE_SIZE = 48;

    $(document).off('click.dcat-admin-image-media').on('click.dcat-admin-image-media', '.admin-image-open-media', function () {
        var \$field = \$(this).closest('.admin-image-field');
        var uploader = \$field.data('uploader');
        if (! uploader) {
            Dcat.error('上传组件未初始化');
            return;
        }

        var page = 1;
        var selected = {};

        var DELETE_URL = {$deleteUrl};

        var layerIndex = layer.open({
            type: 1,
            title: MS_TEXT.modal_title + (IS_MULTIPLE ? '（多选）' : ''),
            area: ['80%', '80%'],
            btn: IS_MULTIPLE ? [MS_TEXT.confirm, '取消'] : [],
            yes: function (index) {
                var items = Object.values(selected);
                if (! items.length) { layer.close(index); return; }
                fillToUploader(uploader, items);
                layer.close(index);
            },
            content: '<div style="height:100%;display:flex;flex-direction:column;background:#fff;">' +
                '<div style="padding:10px 14px;border-bottom:1px solid #eee;display:flex;align-items:center;gap:10px;flex-shrink:0;">' +
                    '<button type="button" class="btn btn-sm btn-primary admin-media-upload-btn"><i class="feather icon-upload"></i> 上传图片</button>' +
                    '<input type="file" class="admin-media-file-input" multiple accept="image/*" style="display:none;">' +
                    '<span style="color:#999;font-size:12px;">或拖拽图片到下方区域上传</span>' +
                '</div>' +
                '<div class="admin-media-grid" style="flex:1;overflow:auto;padding:14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;align-content:start;align-items:start;"></div>' +
                '<div style="padding:10px;border-top:1px solid #eee;text-align:center;flex-shrink:0;background:#fafafa;">' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary admin-media-prev">'+MS_TEXT.page_prev+'</button> ' +
                    '<span class="admin-media-page-info" style="margin:0 12px;font-size:13px;color:#666;"></span> ' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary admin-media-next">'+MS_TEXT.page_next+'</button>' +
                '</div>' +
            '</div>'
        });

        var \$layer = \$('#layui-layer' + layerIndex);
        var \$grid = \$layer.find('.admin-media-grid');
        var \$pageInfo = \$layer.find('.admin-media-page-info');
        var \$fileInput = \$layer.find('.admin-media-file-input');

        \$layer.find('.admin-media-upload-btn').on('click', function(){ \$fileInput.trigger('click'); });
        \$fileInput.on('change', function(){ uploadFiles(this.files); \$fileInput.val(''); });
        \$grid.on('dragover', function(e){ e.preventDefault(); \$grid.css('outline','2px dashed #409eff'); })
             .on('dragleave', function(e){ e.preventDefault(); \$grid.css('outline','none'); })
             .on('drop', function(e){ e.preventDefault(); \$grid.css('outline','none'); if(e.originalEvent.dataTransfer.files.length) uploadFiles(e.originalEvent.dataTransfer.files); });

        function uploadFiles(files) {
            var fd = new FormData();
            for (var i = 0; i < files.length; i++) fd.append('file[]', files[i]);
            var loading = layer.load(1, {shade:[0.3,'#000']});
            \$.ajax({
                url: UPLOAD_URL, type: 'POST', data: fd,
                processData: false, contentType: false, dataType: 'json',
                success: function(res){ layer.close(loading); if(res.code===0){ Dcat.success('上传成功'); loadPage(page); } else { Dcat.error(res.message||'上传失败'); } },
                error: function(){ layer.close(loading); Dcat.error('上传失败'); }
            });
        }

        function loadPage(p) {
            \$grid.html('<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#999;">加载中...</div>');
            \$.ajax({
                url: MEDIA_LIST_URL,
                type: 'GET',
                data: { page: p, page_size: PAGE_SIZE },
                dataType: 'json',
                success: function (res) {
                    if (!res || res.code !== 0) {
                        Dcat.error((res && res.message) || MS_TEXT.load_failed);
                        return;
                    }
                    renderList(res.data || {});
                },
                error: function () { Dcat.error(MS_TEXT.load_failed); }
            });
        }

        function renderList(data) {
            var list = data.list || [];
            var total = data.total || 0;
            page = data.page || page;
            var totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
            \$pageInfo.text('第 ' + page + ' / ' + totalPages + ' 页，共 ' + total + ' 张');

            if (! list.length) {
                \$grid.html('<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#999;">' + MS_TEXT.empty + '</div>');
                return;
            }

            \$grid.empty();
            list.forEach(function (item) {
                var \$item = \$('<div class="admin-media-item" data-path="'+item.path+'" style="position:relative;border:2px solid #e8e8e8;border-radius:6px;overflow:hidden;cursor:pointer;background:#fff;">' +
                    '<img src="'+item.thumb_url+'" loading="lazy" style="width:100%;height:96px;object-fit:cover;display:block;background:#f4f6f8;">' +
                    '<div style="font-size:11px;color:#666;padding:5px 7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+item.name+'</div>' +
                    '<span class="admin-media-del" style="position:absolute;top:4px;right:4px;width:20px;height:20px;line-height:18px;text-align:center;background:rgba(0,0,0,.5);color:#fff;border-radius:50%;font-size:14px;cursor:pointer;display:none;">&times;</span>' +
                '</div>');

                if (selected[item.path]) {
                    \$item.css({borderColor:'#409eff', boxShadow:'0 0 0 2px rgba(64,158,255,.2)'});
                }

                \$item.on('mouseenter', function(){
                        \$item.find('.admin-media-del').show();
                        if(!selected[item.path]) \$item.css('border-color','#a0cfff');
                    })
                    .on('mouseleave', function(){
                        \$item.find('.admin-media-del').hide();
                        if(!selected[item.path]) \$item.css('border-color','#e8e8e8');
                    });

                \$item.find('.admin-media-del').on('click', function(e){
                    e.stopPropagation();
                    layer.confirm('确定从媒体库移除这张图片吗？（文件不会从磁盘删除）', function(idx){
                        layer.close(idx);
                        \$.ajax({
                            url: DELETE_URL, type: 'POST',
                            data: {path: item.path, _token: Dcat.token},
                            dataType: 'json',
                            success: function(res){
                                if(res.code===0){ Dcat.success('已删除'); delete selected[item.path]; loadPage(page); }
                                else { Dcat.error(res.message||'删除失败'); }
                            },
                            error: function(){ Dcat.error('删除失败'); }
                        });
                    });
                });

                \$item.on('click', function (e) {
                    if (\$(e.target).hasClass('admin-media-del')) return;
                    if (IS_MULTIPLE) {
                        if (selected[item.path]) {
                            delete selected[item.path];
                            \$item.css({borderColor:'#e8e8e8', boxShadow:'none'});
                        } else {
                            selected[item.path] = item;
                            \$item.css({borderColor:'#409eff', boxShadow:'0 0 0 2px rgba(64,158,255,.2)'});
                        }
                    } else {
                        fillToUploader(uploader, [item]);
                        layer.close(layerIndex);
                    }
                });

                \$grid.append(\$item);
            });
        }

        \$layer.find('.admin-media-prev').on('click', function () {
            if (page > 1) { loadPage(page - 1); }
        });
        \$layer.find('.admin-media-next').on('click', function () {
            loadPage(page + 1);
        });

        loadPage(1);
    });

    function fillToUploader(uploader, items) {
        if (! items || ! items.length) return;

        if (! IS_MULTIPLE) {
            // 单图：清空所有旧文件（DOM + 数据 + input），只保留新选的第一张
            uploader.\$files.empty();
            Object.keys(uploader.formFiles).forEach(function (id) {
                uploader.input.delete(id);
                delete uploader.formFiles[id];
            });
            items = [items[0]];
        } else {
            // 多图：检查上传上限
            var currentCount = Object.keys(uploader.formFiles).length;
            var limit = parseInt(uploader.options.fileNumLimit) || -1;
            if (limit > 0 && currentCount + items.length > limit) {
                Dcat.error('最多只能上传 ' + limit + ' 张图片，当前已选 ' + currentCount + ' 张');
                return;
            }
        }

        items.forEach(function (item) {
            var ext = (item.path.split('.').pop() || 'jpg');
            var file = {
                serverId: item.path,
                serverUrl: item.url,
                serverPath: item.path,
                ext: ext,
                fake: 1
            };
            uploader.addUploadedFile.render(file);
            uploader.addUploadedFile.add(file);
            uploader.status.switch('incrOriginalFileNum');
            uploader.status.switch('decrFileNumLimit');
        });

        // 隐藏上传区域（placeholder），显示已上传文件列表
        uploader.status.switch('ready');
    }
})();
JS;
    }
}
