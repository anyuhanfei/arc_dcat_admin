{{-- 后台语言切换下拉（顶栏右侧） --}}
<style>
    .locale-switcher .locale-switcher-btn {
        border-radius: 6px;
        padding: 5px 12px !important;
        background-color: rgba(0, 0, 0, .06);
        transition: all .15s ease;
    }

    .locale-switcher .locale-switcher-btn:hover,
    .locale-switcher .locale-switcher-btn:focus {
        background-color: rgba(0, 0, 0, .1);
    }

    .locale-switcher .locale-switcher-caret {
        font-size: 12px;
        margin-left: 4px;
    }

    .locale-switcher .locale-switcher-menu {
        min-width: 150px;
        padding: 6px;
        border-radius: 8px;
        border: 1px solid rgba(0, 0, 0, .08) !important;
        background-color: #fff !important;
        box-shadow: 0 6px 24px rgba(0, 0, 0, .12) !important;
    }

    .locale-switcher .locale-switcher-menu .dropdown-item {
        position: relative;
        border-radius: 6px;
        padding: 8px 12px 8px 46px;
        margin: 2px 0;
        transition: background-color .15s ease;
    }

    .locale-switcher .locale-switcher-menu .dropdown-item:hover {
        background-color: rgba(0, 0, 0, .05);
    }

    .locale-switcher .locale-switcher-menu .dropdown-item i {
        font-size: 14px;
        color: rgba(0, 0, 0, .45);
        margin-right: 14px;
        vertical-align: -2px;
    }

    .locale-switcher .locale-switcher-menu .dropdown-item.active,
    .locale-switcher .locale-switcher-menu .dropdown-item:active {
        background-color: rgba(59, 130, 246, .1);
        color: #3b82f6;
    }

    .locale-switcher .locale-switcher-menu .dropdown-item.active i {
        color: #3b82f6;
    }

    .locale-switcher .locale-switcher-menu .dropdown-item.active::before {
        content: "";
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #3b82f6;
    }
</style>
<div class="nav-item dropdown locale-switcher" style="margin-right: 8px;">
    <a href="javascript:void(0)" class="nav-link locale-switcher-btn" data-toggle="dropdown" aria-expanded="false" title="切换语言">
        <i class="feather icon-globe"></i>&nbsp;
        <span>
            @switch(app()->getLocale())
                @case('en') English @break
                @default 简体中文
            @endswitch
        </span>
        <i class="feather icon-chevron-down locale-switcher-caret"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right locale-switcher-menu">
        <a href="javascript:void(0)" class="dropdown-item {{ app()->getLocale() === 'zh_CN' ? 'active' : '' }}" data-lang="zh_CN"><i class="feather icon-globe"></i>简体中文</a>
        <a href="javascript:void(0)" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" data-lang="en"><i class="feather icon-flag"></i>English</a>
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
