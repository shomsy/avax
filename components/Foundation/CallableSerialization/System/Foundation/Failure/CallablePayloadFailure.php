<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Foundation\Failure;

final readonly class CallablePayloadFailure
{
    public function __construct(
        public string $reason,
        public string $message,
    ) {}

    public function isUnsigned() : bool
    {
        return $this->reason === 'unsigned';
    }

    public function isCorrupted() : bool
    {
        return $this->reason === 'corrupted';
    }

    public function isInvalid() : bool
    {
        return $this->reason === 'invalid';
    }

    public function isExpired() : bool
    {
        return $this->reason === 'expired';
    }
}
