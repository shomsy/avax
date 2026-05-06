<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\WriteDataValue;

use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayWriter;

final readonly class WriteDataValue
{
    public function __construct(
        private ArrayWriter $arrayWriter,
    ) {
    }

    public function write(array &$data, string $key, mixed $value): void
    {
        $this->arrayWriter->set($data, $key, $value);
    }
}
