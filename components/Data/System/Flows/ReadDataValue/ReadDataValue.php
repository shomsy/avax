<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\ReadDataValue;

use Avax\Components\Data\System\Capabilities\Arrays\ArrayReader;

final class ReadDataValue
{
    public function __construct(
        private readonly ArrayReader $reader,
    ) {
    }

    public function read(array $data, string $key, mixed $default = null): mixed
    {
        return $this->reader->get($data, $key, $default);
    }
}