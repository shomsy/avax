<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\ValueConversion;

use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\Foundation\FieldPath;

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
