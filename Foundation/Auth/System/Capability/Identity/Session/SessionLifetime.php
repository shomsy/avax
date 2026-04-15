<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Session;

use InvalidArgumentException;

/**
 * Session lifetime policy for idle and absolute expiration.
 */
final readonly class SessionLifetime
{
    public int $absoluteTimeoutSeconds;
    public int $idleTimeoutSeconds;

    public function __construct(
        int|null $idleTimeoutSeconds = null,
        int      $absoluteTimeoutSeconds = 43200
    )
    {
        $idleTimeoutSeconds           ??= 900;
        $this->idleTimeoutSeconds     = $idleTimeoutSeconds;
        $this->absoluteTimeoutSeconds = $absoluteTimeoutSeconds;
        if ($this->idleTimeoutSeconds < 1) {
            throw new InvalidArgumentException(message: 'Idle timeout must be at least 1 second.');
        }

        if ($this->absoluteTimeoutSeconds < 1) {
            throw new InvalidArgumentException(message: 'Absolute timeout must be at least 1 second.');
        }

        if ($this->absoluteTimeoutSeconds < $this->idleTimeoutSeconds) {
            throw new InvalidArgumentException(message: 'Absolute timeout must be greater than or equal to idle timeout.');
        }
    }
}
