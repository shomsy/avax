<?php

declare(strict_types=1);

namespace Avax\Auth\System\Foundation\Ids;

use InvalidArgumentException;
use Stringable;

final readonly class DirectoryId implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidArgumentException('DirectoryId cannot be empty.');
        }

        $this->value = $normalized;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
