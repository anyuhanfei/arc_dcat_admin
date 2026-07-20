<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('admin.name') }} - {{ trans('admin.login') }}</title>
    <link rel="stylesheet" href="{{ admin_asset('@admin/css/dcat-admin-all.css') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background: url("{{ $background ?? '/static/logo2.jpeg' }}") center center / cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        /* 登录卡片 */
        .login-card {
            width: 420px;
            margin-right: 8%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
            padding: 48px 40px 40px;
            backdrop-filter: blur(10px);
        }

        .login-card .logo {
            text-align: center;
            margin-bottom: 12px;
        }

        .login-card .logo img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
        }

        .login-card .app-name {
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .login-card .welcome {
            text-align: center;
            font-size: 14px;
            color: #999;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .form-group .form-control {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            color: #333;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group .form-control:focus {
            border-color: #5b73e8;
            box-shadow: 0 0 0 3px rgba(91, 115, 232, 0.1);
        }

        .form-group .form-control.is-invalid {
            border-color: #e74c3c;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group .form-control {
            flex: 1;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group .captcha-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 130px;
            height: 44px;
            background: #f0f4ff;
            border: 1px solid #e0e0e0;
            border-left: none;
            border-radius: 0 6px 6px 0;
            font-size: 18px;
            font-weight: 700;
            color: #5b73e8;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s;
        }

        .input-group .captcha-box:hover {
            background: #e4eaff;
        }

        .input-group .captcha-box .op {
            margin: 0 4px;
            color: #999;
        }

        .input-group .captcha-box .refresh-icon {
            margin-left: 8px;
            font-size: 14px;
            color: #ccc;
        }

        .help-block {
            font-size: 12px;
            color: #e74c3c;
            margin-top: 4px;
            min-height: 18px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .form-options .checkbox label {
            font-size: 13px;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-options .checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #5b73e8;
        }

        .btn-login {
            width: 100%;
            height: 46px;
            background: #5b73e8;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-login:hover {
            background: #4a61d6;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        /* 小屏适配 */
        @media (max-width: 576px) {
            .login-card {
                width: 92%;
                padding: 32px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo">
        <img src="/static/logo2.jpeg" alt="logo">
    </div>
    <div class="app-name">{{ config('admin.name') }}</div>
    <div class="welcome">{{ trans('admin.welcome_back') }}</div>

    <form id="login-form" method="POST" action="{{ admin_url('auth/login') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-group">
            <label for="username">{{ trans('admin.username') }}</label>
            <input type="text" id="username" name="username"
                   class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                   placeholder="{{ trans('admin.username') }}"
                   value="{{ old('username') }}" required autofocus>
            @if($errors->has('username'))
                <div class="help-block">{{ $errors->first('username') }}</div>
            @endif
        </div>

        <div class="form-group">
            <label for="password">{{ trans('admin.password') }}</label>
            <input type="password" id="password" name="password"
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   placeholder="{{ trans('admin.password') }}" required>
            @if($errors->has('password'))
                <div class="help-block">{{ $errors->first('password') }}</div>
            @endif
        </div>

        <div class="form-group">
            <label>{{ trans('admin.login_captcha') }}</label>
            <div class="input-group">
                <input type="text" id="captcha" name="captcha"
                       class="form-control {{ $errors->has('captcha') ? 'is-invalid' : '' }}"
                       placeholder="{{ trans('admin.login_captcha') }}" required autocomplete="off">
                <div class="captcha-box" id="captcha-box" title="点击刷新">
                    <span id="captcha-num1">{{ $captcha_num1 }}</span>
                    <span class="op">{{ $captcha_op }}</span>
                    <span id="captcha-num2">{{ $captcha_num2 }}</span>
                    <span class="op">=</span>
                    <span class="refresh-icon">&#x21bb;</span>
                </div>
            </div>
            @if($errors->has('captcha'))
                <div class="help-block">{{ $errors->first('captcha') }}</div>
            @endif
        </div>

        <div class="form-options">
            @if(config('admin.auth.remember'))
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        {{ trans('admin.remember_me') }}
                    </label>
                </div>
            @endif
        </div>

        <button type="submit" class="btn-login">{{ trans('admin.login') }}</button>
    </form>
</div>

<script>
    (function () {
        var captchaBox = document.getElementById('captcha-box');
        if (captchaBox) {
            captchaBox.addEventListener('click', function () {
                fetch('{{ admin_url("auth/captcha") }}')
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        document.getElementById('captcha-num1').textContent = data.num1;
                        document.getElementById('captcha-num2').textContent = data.num2;
                        document.getElementById('captcha').value = '';
                    });
            });
        }
    })();
</script>
</body>
</html>
