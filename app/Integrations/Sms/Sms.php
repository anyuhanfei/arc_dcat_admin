<?php

namespace App\Integrations\Sms;

use Illuminate\Support\Facades\Cache;

/**
 * 短信统一发送门面
 *
 * 只做公司选择 + 验证码生成 + 分发，不写业务逻辑。
 * 业务层用法：
 *   Sms::make()->sendCode($phone)            // 验证码短信，code 自动写入缓存供 verifyCode 校验
 *   Sms::make()->sendTemplate($phone, 'notice', ['name' => '张三'])  // 通知模板短信
 */
class Sms
{
    public function __construct(protected SmsBao|AliyunSms $driver) {}

    public static function make(): static
    {
        $driver = config('services.sms.default');

        if ($driver === 'aliyun') {
            return new static(AliyunSms::make());
        }

        if ($driver === 'smsbao') {
            return new static(SmsBao::make());
        }

        throwBusinessException('短信服务未配置');
    }

    /**
     * 发送验证码短信
     *
     * 生成 6 位验证码后写入缓存（key: sms_code:{phone}，TTL 300 秒，与模板文案"5分钟内有效"一致），再发送短信。
     *
     * @return array ['success' => bool, 'data' => ['code' => string]]，code 已写入缓存，可用 verifyCode() 校验
     */
    public function sendCode(string $phone): array
    {
        $code = (string) mt_rand(100000, 999999);
        Cache::put('sms_code:' . $phone, $code, 300);
        $this->driver->sendCode($phone, $code);

        return ['success' => true, 'data' => ['code' => $code]];
    }

    /**
     * 校验短信验证码
     *
     * 验证通过不销毁缓存；业务接口成功后请调用 destroyCode() 主动销毁，防止验证码重放。
     * 万能验证码 272272：仅 local 开发环境生效，输入即直接通过验证（不校验缓存、不操作缓存）；⚠️ 生产/其他环境不生效。
     *
     * @param string $phone 手机号
     * @param string $code  用户输入的验证码
     */
    public function verifyCode(string $phone, string $code): bool
    {
        // 万能验证码（仅 local 开发环境生效，输入 272272 直接通过验证；⚠️ 其他环境不生效）
        if (config('app.env') === 'local' && (string) $code === '272272') {
            return true;
        }

        $stored = Cache::get('sms_code:' . $phone);
        if ($stored === null || !hash_equals((string) $stored, (string) $code)) {
            return false;
        }

        return true;
    }

    /**
     * 销毁指定手机号的验证码缓存（业务接口成功后调用）
     */
    public function destroyCode(string $phone): void
    {
        Cache::forget('sms_code:' . $phone);
    }

    /**
     * 发送通知模板短信
     *
     * @return array driver 的返回结果 ['success' => bool, 'data' => ['phone' => string]]
     */
    public function sendTemplate(string $phone, string $template, array $params = []): array
    {
        return $this->driver->sendTemplate($phone, $template, $params);
    }
}
