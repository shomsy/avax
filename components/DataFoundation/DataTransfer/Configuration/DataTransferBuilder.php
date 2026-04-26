<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Configuration;

use Avax\DataFoundation\DataTransfer\DataTransfer;
use Avax\DataFoundation\DataTransfer\DataTransferResult;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataShape;

final readonly class DataTransferBuilder
{
    /**
     * @param class-string $class
     */
    public function __construct(
        private string                  $class,
        private DataTransferConfig|null $config = null,
    ) {}

    public function create(array|object $input) : object
    {
        return DataTransfer::create(class: $this->class, input: $input, config: $this->config);
    }

    public function tryCreate(array|object $input) : DataTransferResult
    {
        return DataTransfer::tryCreate(class: $this->class, input: $input, config: $this->config);
    }

    public function shape() : DataShape
    {
        return DataTransfer::inspect(class: $this->class, config: $this->config);
    }
}
