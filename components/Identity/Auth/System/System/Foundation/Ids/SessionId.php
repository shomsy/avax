<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Foundation\Ids;

use InvalidArgumentException;
use Stringable;

final readonly class SessionId implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = trim(string: $value);

        if ($normalized === '') {
            throw new InvalidArgumentException(message: 'SessionId cannot be empty.');
        }

        $this->value = $normalized;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
