<style>
    .webuploader-pick {
        background-color: @primary;
    }

    .web-uploader .placeholder .flashTip a {
        color: @primary(-10);
    }

    .web-uploader .statusBar .upload-progress span.percentage,
    .web-uploader .filelist li p.upload-progress span {
        background: @primary(-8);
    }

    .web-uploader .dnd-area.webuploader-dnd-over {
        border: 3px dashed #999 !important;
    }
    .web-uploader .dnd-area.webuploader-dnd-over .placeholder {
        border: none;
    }

    /* 媒体库按钮 */
    .admin-image-media-bar {
        margin-top: 8px;
    }
    .admin-image-media-bar .btn {
        margin-right: 8px;
    }
</style>

<div class="{{$viewClass['form-group']}} {{ $class }} admin-image-field">

    <label for="{{$column}}" class="{{$viewClass['label']}} control-label">{!! $label !!}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <input name="{{ $name }}" class="file-input" type="hidden" {!! $attributes !!}/>

        <div class="web-uploader {{ $fileType }}">
            <div class="queueList dnd-area">
                <div class="placeholder">
                    <div class="file-picker"></div>
                    <p>{{trans('admin.uploader.drag_file')}}</p>
                </div>
            </div>
            <div class="statusBar" style="display:none;">
                <div class="upload-progress progress progress-bar-primary pull-left">
                    <div class="progress-bar progress-bar-striped active" style="line-height:18px">0%</div>
                </div>
                <div class="info"></div>
                <div class="btns">
                    <div class="add-file-button"></div>
                    @if($showUploadBtn)
                    &nbsp;
                    <div class="upload-btn btn btn-primary"><i class="feather icon-upload"></i> &nbsp;{{trans('admin.upload')}}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-image-media-bar">
            <button type="button" class="btn btn-outline-primary btn-sm admin-image-open-media">
                <i class="feather icon-image"></i>&nbsp;{{ trans('media-selector.select_button') }}
            </button>
        </div>

        @include('admin::form.help-block')
    </div>
</div>

<script require="@webuploader" init="{!! $selector !!}">
    var uploader,
        newPage,
        options = {!! $options !!},
        events = options.events;

    init();

    function init() {
        var opts = $.extend({
            selector: $this,
            addFileButton: $this.find('.add-file-button'),
            inputSelector: $this.find('.file-input'),
        }, options);

        opts.upload = $.extend({
            pick: {
                id: $this.find('.file-picker'),
                name: '_file_',
                label: '<i class="feather icon-folder"><\/i>&nbsp; {!! trans('admin.uploader.add_new_media') !!}'
            },
            dnd: $this.find('.dnd-area'),
            paste: $this.find('.web-uploader'),
            formData: options.formData || {},
            server: options.server || '',
            fileVal: options.fileVal || '_file_',
            name: options.name || '_file_',
            deleteUrl: options.deleteUrl || '',
            deleteData: options.deleteData || {},
            fileNumLimit: options.fileNumLimit,
            fileSingleSizeLimit: options.fileSingleSizeLimit,
            accept: options.accept || {},
            compress: options.compress,
            chunked: options.chunked,
            threads: options.threads,
            disableGlobalDnd: options.disableGlobalDnd,
        }, opts.upload || {});

        uploader = Dcat.Uploader(opts);
        uploader.build();
        uploader.preview();

        // 暴露 uploader 实例到 DOM，供媒体库回填调用
        $this.data('uploader', uploader);

        for (var i = 0; i < events.length; i++) {
            var evt = events[i];
            if (evt.event && evt.script) {
                if (evt.once) {
                    uploader.uploader.once(evt.event, evt.script.bind(uploader))
                } else {
                    uploader.uploader.on(evt.event, evt.script.bind(uploader))
                }
            }
        }

        function resize() {
            setTimeout(function () {
                if (! uploader) return;

                uploader.refreshButton();
                resize();

                if (! newPage) {
                    newPage = 1;
                    $(document).one('pjax:complete', function () {
                        uploader = null;
                    });
                }
            }, 250);
        }
        resize();
    }
</script>
