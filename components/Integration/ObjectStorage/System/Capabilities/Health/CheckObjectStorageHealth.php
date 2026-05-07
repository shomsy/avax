<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Capabilities\Health;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Throwable;

class CheckObjectStorageHealth
{
    public function __construct(
        private ObjectStoragePort $adapter,
    ) {}

    public function check() : ObjectStorageHealthReport
    {
        try {
            $connected = $this->adapter->exists('.health-check');

            return new ObjectStorageHealthReport(
                healthy  : true,
                message  : 'Object storage connection successful',
                latencyMs: 0,
            );
        } catch (Throwable $e) {
            return new ObjectStorageHealthReport(
                healthy  : false,
                message  : $e->getMessage(),
                latencyMs: 0,
            );
        }
    }
}
