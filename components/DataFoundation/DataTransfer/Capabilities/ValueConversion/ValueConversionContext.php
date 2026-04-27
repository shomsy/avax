<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\ValueConversion;

use Avax\DataFoundation\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataFoundation\DataTransfer\Foundation\FieldPath;

final readonly class ValueConversionContext
{
    public function __construct(
        public DataTransferConfig $config,
        public FieldPath          $path,
    ) {}

    public function at(string|int $segment) : self
    {
        return new self(config: $this->config, path: $this->path->append(segment: $segment));
    }
}
