<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration;

use DateInterval;

/**
 * IdentityConfiguration — value object for Identity token and session settings.
 *
 * Adapted from the enterprise reference package.
 * Holds the token signing secret and default TTLs for tokens and sessions.
 */
final readonly class IdentityConfiguration
{
    public function __construct(
        private string $tokenSecret,
        private DateInterval $defaultTokenTtl = new DateInterval('PT1H'),
        private DateInterval $defaultSessionTtl = new DateInterval('PT24H'),
        private DateInterval $defaultRefreshTokenTtl = new DateInterval('P30D'),
        private string $tokenIssuer = 'avax-auth-system',
        private int $sessionIdleTimeoutSeconds = 1800,
        private int $sessionAbsoluteTimeoutSeconds = 86400,
    ) {}

    public function tokenSecret(): string
    {
        return $this->tokenSecret;
    }

    public function defaultTokenTtl(): DateInterval
    {
        return $this->defaultTokenTtl;
    }

    public function defaultSessionTtl(): DateInterval
    {
        return $this->defaultSessionTtl;
    }

    public function defaultRefreshTokenTtl(): DateInterval
    {
        return $this->defaultRefreshTokenTtl;
    }

    public function tokenIssuer(): string
    {
        return $this->tokenIssuer;
    }

    public function sessionLifetime(): \Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionLifetime
    {
        return new \Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionLifetime(
            idleTimeoutSeconds: $this->sessionIdleTimeoutSeconds,
            absoluteTimeoutSeconds: $this->sessionAbsoluteTimeoutSeconds,
        );
    }
}
