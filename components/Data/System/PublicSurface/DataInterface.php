<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\PublicSurface;

use Avax\Components\Data\System\Capabilities\Arrays\ArrayReader;
use Avax\Components\Data\System\Capabilities\Arrays\ArrayWriter;
use Avax\Components\Data\System\Capabilities\Collections\Collection;

interface DataInterface
{
    public function array(): ArrayReader;

    public function write(): ArrayWriter;

    public function collection(): Collection;
}