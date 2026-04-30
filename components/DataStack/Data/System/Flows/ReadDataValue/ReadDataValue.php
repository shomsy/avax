<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\ReadDataValue;

use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayReader;

final readonly class ReadDataValue
{
    public function __construct(
        private ArrayReader $arrayReader,
    ) {}

    public function read(array $data, string $key, mixed $default = null) : mixed
    {
        return $this->arrayReader->get($data, $key, $default);
    }
}
