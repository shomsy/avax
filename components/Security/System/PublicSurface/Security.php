<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\System\PublicSurface;

use Avax\Components\Security\System\System\Capabilities\Audit\SecurityAuditLog;
use Avax\Components\Security\System\System\Capabilities\Csrf\CsrfToken;
use Avax\Components\Security\System\System\Capabilities\Csrf\CsrfVerifier;
use Avax\Components\Security\System\System\Capabilities\Escape\OutputEscaper;
use Avax\Components\Security\System\System\Capabilities\Headers\SecurityHeaders;
use Avax\Components\Security\System\System\Capabilities\MassAssignment\MassAssignmentGuard;
use Avax\Components\Security\System\System\Capabilities\SignedUrls\SignedUrlGenerator;
use Avax\Components\Security\System\System\Capabilities\SignedUrls\SignedUrlVerifier;
use DateInterval;

final class Security
{
    private static SecurityAuditLog|null $auditLog = null;

    public static function generateCsrfToken() : string
    {
        return CsrfToken::generate();
    }

    public static function rotateCsrfToken() : string
    {
        return CsrfToken::rotate();
    }

    public static function verifyCsrfToken(string $token, string|null $sessionToken = null) : bool
    {
        $valid = CsrfVerifier::verify(token: $token, sessionToken: $sessionToken);
        self::audit(event: $valid ? 'csrf.accepted' : 'csrf.rejected');

        return $valid;
    }

    public static function escape(string $value) : string
    {
        return OutputEscaper::html(value: $value);
    }

    public static function escapeAttribute(string $value) : string
    {
        return OutputEscaper::attribute(value: $value);
    }

    public static function safeJson(mixed $value) : string
    {
        return OutputEscaper::json(value: $value);
    }

    public static function fillable(array $input, array $fillable) : array
    {
        return (new MassAssignmentGuard())->onlyFillable(input: $input, fillable: $fillable);
    }

    public static function applySecurityHeaders(ResponseFormatter $responseFormatter) : ResponseFormatter
    {
        return SecurityHeaders::apply(formatter: $responseFormatter);
    }

    public static function generateSignedUrl(string $path, DateInterval $ttl) : string
    {
        return SignedUrlGenerator::generate(path: $path, ttl: $ttl);
    }

    public static function verifySignedUrl(string $url) : bool
    {
        return SignedUrlVerifier::verify(url: $url);
    }

    public static function hash(string $password) : string
    {
        return password_hash(password: $password, algo: PASSWORD_BCRYPT);
    }

    public static function verify(string $password, string $hash) : bool
    {
        return password_verify(password: $password, hash: $hash);
    }

    public static function generateToken(int $length = 32) : string
    {
        return bin2hex(random_bytes(length: intdiv(num1: $length, num2: 2)));
    }

    public static function audit(string $event, array $context = []) : void
    {
        self::auditLog()->record(event: $event, context: $context);
    }

    public static function auditEvents() : array
    {
        return self::auditLog()->all();
    }

    private static function auditLog() : SecurityAuditLog
    {
        if (self::$auditLog === null) {
            self::$auditLog = new SecurityAuditLog();
        }

        return self::$auditLog;
    }
}

final class ResponseFormatter
{
    /** @var array<string, string> */
    private array $headers = [];

    public function withHeader(string $name, string $value) : self
    {
        $clone                 = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function headers() : array
    {
        return $this->headers;
    }
}
