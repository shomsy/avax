<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Foundation;

use Avax\DataFoundation\DataTransfer\DataTransferException;

final readonly class FieldName
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new DataTransferException(message: 'Data field name cannot be empty.');
        }
    }
}
