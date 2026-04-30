<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\Capabilities\SignedUrls;

use DateInterval;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final readonly class SignedUrlGenerator
{
    private static string $secret = '';
    private static string $algo   = 'HS256';

    public static function configure(string $secret, string $algo = 'HS256') : void
    {
        self::$secret = $secret;
        self::$algo   = $algo;
    }

    public static function generate(string $path, DateInterval $ttl) : string
    {
        $expires = time() + ($ttl->i * 60 + $ttl->s);

        $payload = [
            'path' => $path,
            'exp'  => $expires,
            'iat'  => time(),
        ];

        $token = JWT::encode($payload, self::$secret, self::$algo);

        return $path . '?signature=' . $token;
    }
}

final readonly class SignedUrlVerifier
{
    public static function verify(string $url) : bool
    {
        $parsed = parse_url($url, PHP_URL_QUERY);
        $query  = [];
        parse_str($parsed ?? '', $query);

        if (! isset($query['signature'])) {
            return false;
        }

        try {
            $decoded = JWT::decode(
                $query['signature'],
                new Key(SignedUrlGenerator::$secret, SignedUrlGenerator::$algo),
            );

            return $decoded->exp > time();
        } catch (Exception) {
            return false;
        }
    }
}