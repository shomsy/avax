<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Foundation;

use Avax\DataFoundation\DataTransfer\DataTransferException;

final readonly class ClassName
{
    /**
     * @param class-string $value
     */
    public function __construct(public string $value)
    {
        if (! class_exists(class: $value)) {
            throw new DataTransferException(message: sprintf('Data object class does not exist: %s', $value));
        }
    }
}
