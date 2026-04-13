<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Session;

use InvalidArgumentException;

/**
 * Session lifetime policy for idle and absolute expiration.
 */
final readonly class SessionLifetime
{
    public function __construct(
        public int $idleTimeoutSeconds = 900,
        public int $absoluteTimeoutSeconds = 43200
    )
    {
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
