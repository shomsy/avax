<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Result;

final readonly class Result
{
    private function __construct(
        private mixed $value,
        private Failure|null $failure,
    ) {
    }

    public static function ok(mixed $value = null): self
    {
        return new self(value: $value, failure: null);
    }

    public static function fail(Failure $failure): self
    {
        return new self(value: null, failure: $failure);
    }

    public function succeeded(): bool
    {
        return ! $this->failure instanceof Failure;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function failure() : Failure|null
    {
        return $this->failure;
    }
}
