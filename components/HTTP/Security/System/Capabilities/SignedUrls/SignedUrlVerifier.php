<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\System\Capabilities\SignedUrls;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final readonly class SignedUrlVerifier
{
    public static function verify(string $url): bool
    {
        $queryString = parse_url(url: $url, component: PHP_URL_QUERY);
        $query = [];
        parse_str(string: $queryString ?? '', result: $query);

        if (! isset($query['signature'])) {
            return false;
        }

        try {
            $decoded = JWT::decode(
                jwt          : (string) $query['signature'],
                keyOrKeyArray: new Key(
                    keyMaterial: SignedUrlGenerator::secret(),
                    algorithm  : SignedUrlGenerator::algorithm(),
                ),
            );

            return $decoded->exp > time();
        } catch (Exception) {
            return false;
        }
    }
}
