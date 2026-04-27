<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RequestScope;

use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final readonly class RequestScopeId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalizedValue = trim(string: $value);

        if ($normalizedValue === '') {
            throw new FrameworkMisconfigured(message: 'Request scope id cannot be empty.');
        }

        $this->value = $normalizedValue;
    }

    /**
     * @throws \Random\RandomException
     */
    public static function generate(): self
    {
        return new self(value: bin2hex(string: random_bytes(length: 16)));
    }

    public function toString(): string
    {
        return $this->value;
    }
}
