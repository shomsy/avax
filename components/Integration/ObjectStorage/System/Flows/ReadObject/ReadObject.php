<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Flows\ReadObject;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult;

final readonly class ReadObject
{
    public function read(ObjectStoragePort $port, string $key) : ObjectStorageResult
    {
        $content = $port->read($key);

        if ($content === null) {
            return ObjectStorageResult::failure('Object not found.');
        }

        return ObjectStorageResult::success(content: $content);
    }
}
