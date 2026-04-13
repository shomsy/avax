<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation;

use RuntimeException;

final class FederationFailed extends RuntimeException
{
    public static function notFound() : self
    {
        return new self('Federation connection was not found.');
    }

    public static function runtimeNotConfigured() : self
    {
        return new self('Federation runtime is not configured.');
    }

    public static function domainConflict() : self
    {
        return new self('Federation domain is already registered for another tenant.');
    }

    public static function invalidGroupRoleMapping() : self
    {
        return new self('Federation group-to-role mapping is invalid.');
    }

    public static function invalidDomainVerificationToken() : self
    {
        return new self('Federation domain verification token is invalid.');
    }

    public static function domainNotVerified() : self
    {
        return new self('Federation domain is not verified.');
    }

    public static function metadataRuntimeNotConfigured() : self
    {
        return new self('Federation metadata runtime is not configured.');
    }

    public static function healthCheckNotConfigured() : self
    {
        return new self('Federation health checks are not configured.');
    }

    public static function metadataUrlMissing() : self
    {
        return new self('Federation metadata URL is not configured.');
    }

    public static function connectionUnavailable() : self
    {
        return new self('Federation connection is unavailable.');
    }

    public static function invalidBreakGlassPolicy() : self
    {
        return new self('Break-glass bypass requires an SSO-only federation connection.');
    }
}
