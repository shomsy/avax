<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\WriteDataValue;

use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayWriter;

final class WriteDataValue
{
    public function __construct(
        private readonly ArrayWriter $writer,
    ) {}

    public function write(array &$data, string $key, mixed $value) : void
    {
        $this->writer->set($data, $key, $value);
    }
}