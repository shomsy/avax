<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Flows\DeleteObject;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult;

final readonly class DeleteObject
{
    public function delete(ObjectStoragePort $port, string $key) : ObjectStorageResult
    {
        $result = $port->delete($key);

        return $result
            ? ObjectStorageResult::success()
            : ObjectStorageResult::failure('Delete failed.');
    }
}
