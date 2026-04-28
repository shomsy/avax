<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Environment;

use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final readonly class EnvironmentName
{
    private string $value;

    public function __construct(string $value)
    {
        $normalizedName = trim(string: $value);

        if ($normalizedName === '') {
            throw new FrameworkMisconfigured(message: 'Environment name cannot be empty.');
        }

        $this->value = $normalizedName;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
