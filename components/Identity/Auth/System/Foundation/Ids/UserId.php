<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Ids;

use InvalidArgumentException;
use Stringable;

final readonly class UserId implements Stringable
{
    public string $value;

    public function __construct(string|int $value)
    {
        $normalized = trim(string: (string) $value);

        if ($normalized === '') {
            throw new InvalidArgumentException(message: 'UserId cannot be empty.');
        }

        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
