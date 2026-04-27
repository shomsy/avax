<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\PublicSurface;

use Avax\Components\Data\System\Capabilities\Arrays\ArrayReader;
use Avax\Components\Data\System\Capabilities\Arrays\ArrayWriter;
use Avax\Components\Data\System\Capabilities\Collections\Collection;

final readonly class Data implements DataInterface
{
    public function __construct(
        private ArrayReader $arrayReader,
        private ArrayWriter $arrayWriter,
        private Collection $collection,
    ) {
    }

    public function array(): ArrayReader
    {
        return $this->arrayReader;
    }

    public function write(): ArrayWriter
    {
        return $this->arrayWriter;
    }

    public function collection(): Collection
    {
        return $this->collection;
    }
}