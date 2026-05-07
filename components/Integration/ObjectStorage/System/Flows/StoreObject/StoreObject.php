<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Flows\StoreObject;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult;

final readonly class StoreObject
{
    public function store(ObjectStoragePort $port, string $key, string $content) : ObjectStorageResult
    {
        return $port->store($key, $content);
    }
}
