<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\PublicSurface;

use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfToken;
use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfVerifier;
use Avax\Components\HTTP\Security\System\Capabilities\Headers\SecurityHeaders;
use Avax\Components\HTTP\Security\System\Capabilities\SignedUrls\SignedUrlGenerator;
use Avax\Components\HTTP\Security\System\Capabilities\SignedUrls\SignedUrlVerifier;
use Avax\Components\Security\System\Capabilities\Audit\SecurityAuditLog;
use Avax\Components\Security\System\Capabilities\Escape\OutputEscaper;
use Avax\Components\Security\System\Capabilities\MassAssignment\MassAssignmentGuard;
use DateInterval;

final class Security
{
    public function __construct(
        private SecurityAuditLog $securityAuditLog,
    ) {}

    public static function generateCsrfToken(): string
    {
        return CsrfToken::generate();
    }

    public static function rotateCsrfToken(): string
    {
        return CsrfToken::rotate();
    }

    public function verifyCsrfToken(string $token, string|null $sessionToken = null) : bool
    {
        $valid = CsrfVerifier::verify(token: $token, sessionToken: $sessionToken);
        $this->audit(event: $valid ? 'csrf.accepted' : 'csrf.rejected');

        return $valid;
    }

    public static function escape(string $value): string
    {
        return OutputEscaper::html(value: $value);
    }

    public static function escapeAttribute(string $value): string
    {
        return OutputEscaper::attribute(value: $value);
    }

    public static function safeJson(mixed $value): string
    {
        return OutputEscaper::json(value: $value);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  list<string>  $fillable
     * @return array<string, mixed>
     */
    public static function fillable(array $input, array $fillable): array
    {
        return new MassAssignmentGuard()->onlyFillable(input: $input, fillable: $fillable);
    }

    public static function applySecurityHeaders(ResponseFormatter $responseFormatter): ResponseFormatter
    {
        return SecurityHeaders::apply(responseFormatter: $responseFormatter);
    }

    public static function generateSignedUrl(string $path, DateInterval $dateInterval): string
    {
        return SignedUrlGenerator::generate(path: $path, dateInterval: $dateInterval);
    }

    public static function verifySignedUrl(string $url): bool
    {
        return SignedUrlVerifier::verify(url: $url);
    }

    public static function hash(string $password): string
    {
        return password_hash(password: $password, algo: PASSWORD_BCRYPT);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify(password: $password, hash: $hash);
    }

    public static function generateToken(int $length = 32): string
    {
        $length = max(2, $length);
        /** @var int<1, max> $bytes */
        $bytes = intdiv(num1: $length, num2: 2);

        return bin2hex(random_bytes(length: $bytes));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function audit(string $event, array $context = []): void
    {
        $this->securityAuditLog->record(event: $event, context: $context);
    }

    /**
     * @return list<array{event:string,context:array<string,mixed>,recorded_at:string}>
     */
    public function auditEvents(): array
    {
        return $this->securityAuditLog->all();
    }
}
