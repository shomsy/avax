<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use RuntimeException;
use SensitiveParameter;

final class TrustedProxyViolation extends RuntimeException
{
    public static function untrustedForwardedHeader(#[SensitiveParameter] string $header) : self
    {
        return new self(message: "Untrusted forwarded header detected: {$header}");
    }

    public static function untrustedClientCertificateMetadata(#[SensitiveParameter] string $header) : self
    {
        return new self(message: "Untrusted client-certificate metadata detected: {$header}");
    }
}
