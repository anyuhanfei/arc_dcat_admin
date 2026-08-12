{{-- 后台语言切换下拉（顶栏右侧） --}}
<div class="nav-item dropdown" style="margin-right: 8px;">
    <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown" aria-expanded="false" title="切换语言">
        <i class="feather icon-globe"></i>&nbsp;
        <span>
            @switch(app()->getLocale())
                @case('en') English @break
                @case('zh_TW') 繁體中文 @break
                @default 简体中文
            @endswitch
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right" style="min-width: 140px;">
        <a href="javascript:void(0)" class="dropdown-item {{ app()->getLocale() === 'zh_CN' ? 'active' : '' }}" data-lang="zh_CN">简体中文</a>
        <a href="javascript:void(0)" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" data-lang="en">English</a>
        <a href="javascript:void(0)" class="dropdown-item {{ app()->getLocale() === 'zh_TW' ? 'active' : '' }}" data-lang="zh_TW">繁體中文</a>
    </div>
</div>
<script>
(function () {
    // 点击语言选项：设置/替换当前 URL 的 lang 查询参数（保留其他参数）后刷新
    document.addEventListener('click', function (e) {
        var el = e.target && e.target.closest ? e.target.closest('[data-lang]') : null;
        if (!el) {
            return;
        }
        var url = new URL(window.location.href);
        url.searchParams.set('lang', el.getAttribute('data-lang'));
        window.location.href = url.toString();
    });
})();
</script>
