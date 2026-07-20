<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Dcat\Admin\Layout\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends BaseAuthController
{
    /**
     * 生成验证码算式
     *
     * @return array{num1: int, num2: int, op: string}
     */
    protected function generateCaptcha(): array
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);

        session()->put('captcha_result', $num1 + $num2);

        return [
            'num1' => $num1,
            'num2' => $num2,
            'op'   => '+',
        ];
    }

    /**
     * 刷新验证码（AJAX）
     *
     * @return JsonResponse
     */
    public function getCaptcha(): JsonResponse
    {
        $captcha = $this->generateCaptcha();

        return response()->json($captcha);
    }

    /**
     * Show the login page.
     *
     * @return View|\Illuminate\Http\RedirectResponse
     */
    public function getLogin(Content $content)
    {
        if ($this->guard()->check()) {
            return redirect($this->getRedirectPath());
        }

        $captcha = $this->generateCaptcha();

        return view('admin::pages.login', [
            'captcha_num1' => $captcha['num1'],
            'captcha_num2' => $captcha['num2'],
            'captcha_op'   => $captcha['op'],
            'background'   => config('admin.login_background'),
        ]);
    }

    /**
     * Handle a login request.
     *
     * @param  Request  $request
     * @return RedirectResponse|mixed
     */
    public function postLogin(Request $request)
    {
        // 验证验证码
        $captchaInput = $request->input('captcha');
        $captchaResult = session()->pull('captcha_result');

        $captchaValidator = Validator::make(
            ['captcha' => $captchaInput],
            ['captcha' => 'required|integer']
        );

        if ($captchaValidator->fails() || (int) $captchaInput !== $captchaResult) {
            return redirect()->back()->withInput()->withErrors([
                'captcha' => trans('admin.login_captcha_error'),
            ]);
        }

        $credentials = $request->only([$this->username(), 'password']);
        $remember = (bool) $request->input('remember', false);

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($credentials, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        if ($this->guard()->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended($this->getRedirectPath());
        }

        return redirect()->back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }
}
