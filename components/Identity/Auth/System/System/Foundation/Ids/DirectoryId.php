<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Foundation\Ids;

use InvalidArgumentException;
use Stringable;

final readonly class DirectoryId implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = trim(string: $value);

        if ($normalized === '') {
            throw new InvalidArgumentException(message: 'DirectoryId cannot be empty.');
        }

        $this->value = $normalized;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
