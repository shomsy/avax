<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime;

use RuntimeException;

final class FederationFailed extends RuntimeException
{
    public static function notFound(): self
    {
        return new self(message: 'Federation connection was not found.');
    }

    public static function runtimeNotConfigured(): self
    {
        return new self(message: 'Federation runtime is not configured.');
    }

    public static function domainConflict(): self
    {
        return new self(message: 'Federation domain is already registered for another tenant.');
    }

    public static function invalidGroupRoleMapping(): self
    {
        return new self(message: 'Federation group-to-role mapping is invalid.');
    }

    public static function invalidDomainVerificationToken(): self
    {
        return new self(message: 'Federation domain verification token is invalid.');
    }

    public static function domainNotVerified(): self
    {
        return new self(message: 'Federation domain is not verified.');
    }

    public static function metadataRuntimeNotConfigured(): self
    {
        return new self(message: 'Federation metadata runtime is not configured.');
    }

    public static function healthCheckNotConfigured(): self
    {
        return new self(message: 'Federation health checks are not configured.');
    }

    public static function metadataUrlMissing(): self
    {
        return new self(message: 'Federation metadata URL is not configured.');
    }

    public static function connectionUnavailable(): self
    {
        return new self(message: 'Federation connection is unavailable.');
    }

    public static function invalidBreakGlassPolicy(): self
    {
        return new self(message: 'Break-glass bypass requires an SSO-only federation connection.');
    }
}
