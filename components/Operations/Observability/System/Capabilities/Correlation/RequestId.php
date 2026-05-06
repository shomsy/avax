<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Correlation;

use Stringable;

final readonly class RequestId implements Stringable
{
    private function __construct(public string $value)
    {
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(16)));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
