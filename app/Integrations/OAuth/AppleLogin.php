<?php

namespace App\Integrations\OAuth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Apple 登录（Sign in with Apple）- 服务端身份令牌验签封装
 *
 * identityToken 由 iOS 端 ASAuthorizationAppleIDProvider 或 Web 端 Apple JS 登录获取；
 * sub 为苹果用户的唯一标识（跨 App/Web 稳定）。
 *
 * 验签流程：
 * 1. 解码 JWT header 取 kid；
 * 2. 获取 Apple JWKS 公钥（https://appleid.apple.com/auth/keys，缓存 6 小时）；
 * 3. 按 kid 取 x5c[0] 证书 DER → PEM → 提取 RSA 公钥；
 * 4. JWT::decode RS256 验签（自动校验 exp）；
 * 5. 校验 claims.aud === 配置的 clientId（Bundle ID / Service ID）。
 */
class AppleLogin
{
    private const JWKS_URL = 'https://appleid.apple.com/auth/keys';

    private const JWKS_CACHE_KEY = 'apple_jwks';

    private const JWKS_CACHE_TTL = 21600; // 6 小时

    public function __construct(
        protected string $clientId,
    ) {}

    public static function make(): static
    {
        $clientId = config('services.apple.client_id');

        if (empty($clientId)) {
            throwBusinessException('Apple 登录配置错误，请检查 .env 中的 APPLE_CLIENT_ID 配置');
        }

        return new static($clientId);
    }

    /**
     * 验证 Apple identityToken 并返回用户信息
     *
     * @param string $identityToken iOS 端 ASAuthorizationAppleIDProvider / Web 端 Apple JS 登录返回的身份令牌
     * @return array ['success' => bool, 'data' => ['openid' => string, 'email' => ?string, 'is_private_email' => bool, 'email_verified' => bool]]
     */
    public function oauth(string $identityToken): array
    {
        // 1. 解码 JWT header 取 kid
        $segments = explode('.', $identityToken);
        if (count($segments) < 2) {
            throwBusinessException('Apple 登录验证失败');
        }
        try {
            $header = JWT::jsonDecode(JWT::urlsafeB64Decode($segments[0]));
        } catch (\Throwable $e) {
            throwBusinessException('Apple 登录验证失败');
        }
        $kid = $header->kid ?? null;

        // 2. 获取 Apple JWKS 公钥（缓存 6 小时，缓存未命中才请求；请求失败且有缓存则用缓存）
        $keys = Cache::get(self::JWKS_CACHE_KEY);
        if ($keys === null) {
            try {
                $response = Http::timeout(5)->get(self::JWKS_URL);
                if ($response->failed()) {
                    throw new \RuntimeException('Apple JWKS 请求失败');
                }
                $keys = $response->json();
                Cache::put(self::JWKS_CACHE_KEY, $keys, self::JWKS_CACHE_TTL);
            } catch (\Throwable $e) {
                throwBusinessException('Apple 登录验证失败');
            }
        }

        // 3. 按 kid 找对应 key，x5c[0] 证书 DER → PEM → RSA 公钥
        $keyData = null;
        foreach (($keys['keys'] ?? []) as $candidate) {
            if (($candidate['kid'] ?? null) === $kid) {
                $keyData = $candidate;
                break;
            }
        }
        if ($keyData === null || empty($keyData['x5c'][0])) {
            throwBusinessException('Apple 登录验证失败');
        }

        $certPem = "-----BEGIN CERTIFICATE-----\n" . chunk_split($keyData['x5c'][0], 64, "\n") . "-----END CERTIFICATE-----\n";
        $publicKey = openssl_pkey_get_public($certPem);
        if ($publicKey === false) {
            throwBusinessException('Apple 登录验证失败');
        }
        $details = openssl_pkey_get_details($publicKey);
        $publicKeyPem = $details['key'] ?? '';

        // 4. RS256 验签（自动校验 exp）
        try {
            $payload = JWT::decode($identityToken, new Key($publicKeyPem, 'RS256'));
        } catch (\Throwable $e) {
            throwBusinessException('Apple 登录验证失败');
        }

        // 5. 校验 aud === clientId
        if (($payload->aud ?? null) !== $this->clientId) {
            throwBusinessException('Apple 登录验证失败');
        }

        return [
            'success' => true,
            'data' => [
                'openid'            => $payload->sub ?? '',
                'email'             => $payload->email ?? null,
                'is_private_email'  => (bool) ($payload->is_private_email ?? false),
                'email_verified'    => (bool) ($payload->email_verified ?? false),
            ],
        ];
    }
}
