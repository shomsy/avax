<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\PublicSurface;

use Avax\Components\Security\System\Capabilities\Csrf\CsrfToken;
use Avax\Components\Security\System\Capabilities\Csrf\CsrfVerifier;
use Avax\Components\Security\System\Capabilities\Headers\SecurityHeaders;
use Avax\Components\Security\System\Capabilities\SignedUrls\SignedUrlGenerator;
use Avax\Components\Security\System\Capabilities\SignedUrls\SignedUrlVerifier;
use DateInterval;

final class Security
{
    private static bool $initialized = false;

    public static function generateCsrfToken() : string
    {
        return CsrfToken::generate();
    }

    public static function verifyCsrfToken(string $token) : bool
    {
        return CsrfVerifier::verify($token);
    }

    public static function applySecurityHeaders(ResponseFormatting\ResponseFormatter $responseFormatter) : void
    {
        SecurityHeaders::apply($responseFormatter);
    }

    public static function generateSignedUrl(string $path, DateInterval $ttl) : string
    {
        return SignedUrlGenerator::generate($path, $ttl);
    }

    public static function verifySignedUrl(string $url) : bool
    {
        return SignedUrlVerifier::verify($url);
    }

    public static function hash(string $password) : string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify(string $password, string $hash) : bool
    {
        return password_verify($password, $hash);
    }

    public static function generateToken(int $length = 32) : string
    {
        return bin2hex(random_bytes($length / 2));
    }
}

final class ResponseFormatter
{
    private array $headers = [];

    public function withHeader(string $name, string $value) : self
    {
        $clone          = new self();
        $clone->headers = [...$this->headers, $name => $value];

        return $clone;
    }

    public function headers() : array
    {
        return $this->headers;
    }
}