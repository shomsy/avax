<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration;

use InvalidArgumentException;

final readonly class SessionLifetime
{
    public function __construct(
        public int $idleTimeoutSeconds = 900,
        public int $absoluteTimeoutSeconds = 43200
    )
    {
        if ($this->idleTimeoutSeconds < 1) {
            throw new InvalidArgumentException('Idle timeout must be at least 1 second.');
        }

        if ($this->absoluteTimeoutSeconds < 1) {
            throw new InvalidArgumentException('Absolute timeout must be at least 1 second.');
        }

        if ($this->absoluteTimeoutSeconds < $this->idleTimeoutSeconds) {
            throw new InvalidArgumentException('Absolute timeout must be greater than or equal to idle timeout.');
        }
    }
}
