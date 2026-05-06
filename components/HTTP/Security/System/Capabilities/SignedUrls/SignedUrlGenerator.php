<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Capabilities\SignedUrls;

use DateInterval;
use Firebase\JWT\JWT;

final class SignedUrlGenerator
{
    private static string $secret = '';

    private static string $algo = 'HS256';

    public static function configure(string $secret, string $algo = 'HS256'): void
    {
        self::$secret = $secret;
        self::$algo = $algo;
    }

    public static function generate(string $path, DateInterval $dateInterval): string
    {
        $expires = time() + ($dateInterval->i * 60 + $dateInterval->s);

        $payload = [
            'path' => $path,
            'exp' => $expires,
            'iat' => time(),
        ];

        $token = JWT::encode($payload, self::secret(), self::$algo);

        return $path.'?signature='.$token;
    }

    public static function secret(): string
    {
        return self::$secret !== '' ? self::$secret : 'avax-signed-url';
    }

    public static function algorithm(): string
    {
        return self::$algo;
    }
}
