<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\PublicSurface;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Health\CheckObjectStorageHealth;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Health\ObjectStorageHealthReport;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult;
use Avax\Components\Integration\ObjectStorage\System\Configuration\ObjectStorageConfiguration;
use Avax\Components\Integration\ObjectStorage\System\Flows\DeleteObject\DeleteObject;
use Avax\Components\Integration\ObjectStorage\System\Flows\ReadObject\ReadObject;
use Avax\Components\Integration\ObjectStorage\System\Flows\StoreObject\StoreObject;

final readonly class ObjectStorage
{
    public function __construct(
        private ObjectStoragePort $port,
    ) {}

    public static function make(ObjectStoragePort $port) : self
    {
        return new self($port);
    }

    public function store(string $key, string $content) : ObjectStorageResult
    {
        return $this->port->store($key, $content);
    }

    public function read(string $key) : ObjectStorageResult
    {
        $content = $this->port->read($key);

        if ($content === null) {
            return ObjectStorageResult::failure('Object not found.');
        }

        return ObjectStorageResult::success(content: $content);
    }

    public function delete(string $key) : ObjectStorageResult
    {
        $result = $this->port->delete($key);

        return $result
            ? ObjectStorageResult::success()
            : ObjectStorageResult::failure('Delete failed.');
    }

    public function health() : ObjectStorageHealthReport
    {
        return (new CheckObjectStorageHealth($this->port))->check();
    }
}
