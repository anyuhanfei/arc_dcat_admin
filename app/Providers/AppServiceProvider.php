<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 加载后台自定义 CSS（display 只读字段淡灰色背景等全局样式覆盖）
        \Dcat\Admin\Admin::asset()->css(asset('css/admin-custom.css'));
    }
}
