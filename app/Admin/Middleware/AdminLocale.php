<?php

namespace App\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AdminLocale
{
    /** 支持的后台语言 */
    private const LANGS = ['zh_CN', 'en', 'zh_TW'];

    public function handle(Request $request, Closure $next)
    {
        // URL ?lang= 优先，校验合法后写入 session
        if ($lang = $request->query('lang')) {
            if (in_array($lang, self::LANGS, true)) {
                session(['admin_locale' => $lang]);
            }
        }

        $lang = session('admin_locale', config('app.locale', 'zh_CN'));
        if (in_array($lang, self::LANGS, true)) {
            App::setLocale($lang);
        }

        return $next($request);
    }
}
