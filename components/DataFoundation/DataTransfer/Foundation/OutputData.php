<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Foundation;

final readonly class OutputData
{
    public function __construct(private array $values) {}

    public function all() : array
    {
        return $this->values;
    }
}
