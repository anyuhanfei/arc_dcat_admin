# Agent：三方接口对接 Agent

**负责第三方服务接口的对接实现（短信、支付、在线合同、OAuth 等外部服务调用）。**

## 前置必读

- **AGENTS.md「规则 3」** — 所有编码任务派发子 Agent 执行
- **[agent-default.md](agent-default.md)** — 兜底规范（配置 / 命令速查）
- **[agent-api.md](agent-api.md)** — API 层规范（了解业务层如何调用三方封装）

## 目录结构规范

三方对接代码**统一放在 `app/Integrations/`**（不使用 `app/Tools` —— 与 `app/helpers.php` 工具函数语义重叠且含义模糊），按「功能域 → 三方公司」两级组织：

- **一级子目录 = 功能域**：短信 `Sms`、支付 `Pay`、在线合同 `Contract`、OAuth 等
- **二级 = 三方公司**，按复杂度分两种形态：
  - **简单三方**（1 个或几个接口即可完成，如一个短信服务商）：单个 PHP 文件 `app/Integrations/{功能域}/{公司PascalCase}.php`，如 `app/Integrations/Sms/AliyunSms.php`
  - **复杂三方**（多接口，如在线合同：签名生成、注册认证、签合同等）：创建公司目录 `app/Integrations/{功能域}/{公司PascalCase}/`，按功能拆多个 PHP 文件，如 `app/Integrations/Contract/Esign/Sign.php`、`Esign/Auth.php`、`Esign/Contract.php`

```
app/Integrations/
├── Sms/                      # 功能域：短信
│   ├── AliyunSms.php         # 简单三方：一个文件
│   └── TencentSms.php
├── Pay/                      # 功能域：支付
│   └── WechatPay/            # 复杂三方：公司目录
│       ├── Pay.php           # 按功能拆分
│       ├── Refund.php
│       └── Transfer.php
└── Contract/                 # 功能域：在线合同
    └── Esign/                # 复杂三方：公司目录
        ├── Sign.php          # 签名生成
        ├── Auth.php          # 注册认证
        └── Contract.php      # 签合同
```

## 命名规则

| 对象 | 规则 | 示例 |
|------|------|------|
| 功能域目录 | 语义化英文（名词） | `Sms`、`Pay`、`Contract`、`OAuth` |
| 公司目录 / 文件 | 公司品牌名 PascalCase | `Aliyun`、`Tencent`、`WechatPay`、`Esign` |
| 类名 | 与文件名一致（PSR-4 自动加载） | `AliyunSms` ↔ `app/Integrations/Sms/AliyunSms.php` |
| namespace | `App\Integrations\{功能域}\{...}` | `App\Integrations\Sms`、`App\Integrations\Contract\Esign` |
| 方法名 | 接口动作（动词短语） | `sendCode`、`createSign`、`verifySign` |

## 代码规范

| 规则 | 说明 |
|------|------|
| HTTP 客户端 | 统一使用 Laravel HTTP Client（`Http::` facade，`Http::baseUrl()` 等），禁止裸 Guzzle 客户端直接调用 |
| 配置与密钥 | `.env` + `config/services.php`（如 `services.alisms.access_key`），禁止在代码中硬编码密钥 / token |
| 超时与重试 | 外呼必须设置 `->timeout()`；幂等接口按需 `->retry(2, 100)` 重试 |
| 异常处理 | 封装类抛 `throwBusinessException()` 或自定义异常，由业务调用方捕获处理；封装类内不做 HTTP 响应包装 |
| 日志 | 外呼请求可记录日志，但必须脱敏（不记密钥、token、验证码、完整手机号等） |
| 职责边界 | 封装类只做该三方的 API 封装（拼参数、签名、发请求、解析响应），不写业务逻辑；业务层（API Service / Admin / Artisan 命令）通过封装类调用，**禁止在 Controller / Service 中直接 `Http::` 调用三方** |
| 返回 | 统一返回数组或简单值对象（如 `['success' => true, 'data' => [...]]`），不做 HTTP 响应格式包装（那是 API 层 `success()` 的职责） |

密钥配置示例：

```php
// config/services.php
return [
    'alisms' => [
        'access_key'    => env('ALISMS_ACCESS_KEY'),
        'access_secret' => env('ALISMS_ACCESS_SECRET'),
        'sign_name'     => env('ALISMS_SIGN_NAME'),
    ],
];
```

```bash
# .env（密钥只放 .env，不提交仓库）
ALISMS_ACCESS_KEY=your_access_key
ALISMS_ACCESS_SECRET=your_access_secret
ALISMS_SIGN_NAME=您的短信签名
```

## 简单示例

以 `AliyunSms` 为例（简单三方，单文件）。配置在构造函数注入（由 `make()` 从 `config/services.php` 读取），签名等鉴权细节按各家文档实现，此处省略：

```php
<?php

namespace App\Integrations\Sms;

use Illuminate\Support\Facades\Http;

class AliyunSms
{
    public function __construct(
        protected string $accessKey,
        protected string $accessSecret,
        protected string $signName,
    ) {}

    public static function make(): static
    {
        return new static(
            config('services.alisms.access_key'),
            config('services.alisms.access_secret'),
            config('services.alisms.sign_name'),
        );
    }

    public function sendCode(string $phone, string $code): array
    {
        $response = Http::baseUrl('https://dysmsapi.aliyuncs.com')
            ->withToken($this->accessKey)
            ->timeout(5)
            ->post('/send', ['phone' => $phone, 'signName' => $this->signName, 'code' => $code]);

        return ['success' => $response->successful(), 'data' => $response->json()];
    }
}
```

业务层调用（如 API Service 中）：

```php
$result = AliyunSms::make()->sendCode($phone, $code);
// $result['success'] 为 false 时按业务处理（如 throwBusinessException）
```

## 行为准则

| 规则 | 说明 |
|------|------|
| 目录归属 | 三方对接代码只写 `app/Integrations/` 下，按功能域 + 公司组织，不散落各处（不使用 `app/Tools`） |
| 命名一致 | 目录 / 文件 / 类名 PascalCase 且与文件名一致，namespace 与路径匹配 |
| 密钥走配置 | 密钥 / token 一律 `.env` + `config/services.php`，禁止硬编码 |
| 外呼必设超时 | 每次外呼必须设置 `->timeout()`，幂等接口按需 `->retry()` 重试 |
| 日志脱敏 | 记录外呼日志时脱敏，不记密钥、token、验证码、完整手机号 |
| 不写业务逻辑 | 封装类只做该三方的 API 封装，业务逻辑在业务层（API Service / Admin / Artisan 命令） |
