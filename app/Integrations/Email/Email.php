<?php

namespace App\Integrations\Email;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * 邮件发送封装（邮箱验证码 + 邮件通知）
 *
 * 纯封装 Laravel 自带 Mail（无需新增依赖），配置走 .env 的 MAIL_*（config('mail.default') = MAIL_MAILER）。
 * 业务层用法：
 *   Email::sendCode($email)                                        // 验证码邮件，code 自动写入缓存供 verifyCode 校验
 *   Email::sendCode($email, '<p>自定义 HTML</p>')                  // 验证码邮件，自定义 HTML
 *   Email::sendTemplate($email, '通知主题', '<p>通知内容</p>')      // 通知邮件
 *   Email::verifyCode($email, $code)                               // 校验验证码（成功即销毁）
 */
class Email
{
    /**
     * 发送邮箱验证码
     *
     * 生成 6 位验证码后写入缓存（key: email_code:{email}，TTL 300 秒），再发送邮件。
     *
     * @param string $email 收件邮箱
     * @param string|null $html 自定义 HTML；为 null 时使用内置验证码模板
     * @return array ['success' => bool, 'data' => ['code' => string]]，code 已写入缓存，可用 verifyCode() 校验
     */
    public function sendCode(string $email, ?string $html = null): array
    {
        $code = (string) mt_rand(100000, 999999);
        Cache::put('email_code:' . $email, $code, 300);

        $subject = '【' . config('app.name') . '】验证码';
        $this->send($email, $subject, $html ?? $this->defaultCodeHtml($code));

        return ['success' => true, 'data' => ['code' => $code]];
    }

    /**
     * 发送邮件通知（自定义 HTML 内容）
     *
     * @return array ['success' => bool, 'data' => ['email' => string]]
     */
    public function sendTemplate(string $email, string $subject, string $html): array
    {
        $subject = '【' . config('app.name') . '】' . $subject;

        $this->send($email, $subject, $html);

        return ['success' => true, 'data' => ['email' => $email]];
    }

    /**
     * 校验邮箱验证码
     *
     * 验证通过不销毁缓存；业务接口成功后请调用 destroyCode() 主动销毁，防止验证码重放。
     * 万能验证码 272272：仅 local 开发环境生效，输入即直接通过验证（不校验缓存、不操作缓存）；⚠️ 生产/其他环境不生效。
     *
     * @param string $email 邮箱
     * @param string $code  用户输入的验证码
     */
    public function verifyCode(string $email, string $code): bool
    {
        // 万能验证码（仅 local 开发环境生效，输入 272272 直接通过验证；⚠️ 其他环境不生效）
        if (config('app.env') === 'local' && (string) $code === '272272') {
            return true;
        }

        $stored = Cache::get('email_code:' . $email);
        if ($stored === null || !hash_equals((string) $stored, (string) $code)) {
            return false;
        }

        return true;
    }

    /**
     * 销毁指定邮箱的验证码缓存（业务接口成功后调用）
     */
    public function destroyCode(string $email): void
    {
        Cache::forget('email_code:' . $email);
    }

    /**
     * 统一发送入口：Mail::html 发送 HTML 邮件，失败抛业务异常
     */
    protected function send(string $email, string $subject, string $html): void
    {
        try {
            Mail::html($html, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (\Throwable $e) {
            throwBusinessException('邮件发送失败，请稍后重试');
        }
    }

    /**
     * 内置验证码 HTML 模板（内联 CSS，兼容邮箱客户端）
     *
     * 如需自定义邮件样式：
     *   方式一：调用 sendCode 时传入自定义 $html 参数（如 Email::sendCode($email, '<p>自定义内容</p>')）；
     *   方式二：将本方法改造为 Blade 视图（resources/views/emails/code.blade.php），
     *           用 view()->make('emails.code', ['code' => $code])->render() 渲染后返回。
     */
    protected function defaultCodeHtml(string $code): string
    {
        return '<div style="max-width:480px;margin:0 auto;padding:24px;font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;">'
            . '<div style="background:#ffffff;border-radius:8px;padding:32px;text-align:center;">'
            . '<h2 style="margin:0 0 16px;font-size:18px;color:#333333;">邮箱验证码</h2>'
            . '<p style="margin:0 0 24px;font-size:14px;color:#666666;">您正在使用邮箱验证，本次验证码为：</p>'
            . '<div style="display:inline-block;padding:12px 32px;background:#f0f5ff;border-radius:6px;font-size:32px;font-weight:bold;letter-spacing:6px;color:#1677ff;">' . $code . '</div>'
            . '<p style="margin:24px 0 0;font-size:13px;color:#999999;">5分钟内有效，请勿泄露给他人。</p>'
            . '</div>'
            . '</div>';
    }
}
