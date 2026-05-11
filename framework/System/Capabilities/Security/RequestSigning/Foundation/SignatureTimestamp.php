<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation;

final readonly class SignatureTimestamp
{
    public function __construct(
        public int $epochSeconds,
    ) {
    }

    public static function now(): self
    {
        return new self(time());
    }

    public function isWithinTolerance(int $toleranceSeconds, int|null $now = null) : bool
    {
        $now ??= time();

        return abs($now - $this->epochSeconds) <= $toleranceSeconds;
    }
}
