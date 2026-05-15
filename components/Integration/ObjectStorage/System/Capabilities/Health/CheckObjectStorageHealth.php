<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Capabilities\Health;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Throwable;

class CheckObjectStorageHealth
{
    public function __construct(
        private ObjectStoragePort $adapter,
    ) {}

    public function check() : HealthReport
    {
        try {
            $start = hrtime(true);
            $this->adapter->exists('.health-check');
            $latencyMs = (int) ((hrtime(true) - $start) / 1_000_000);

            $latencyStatus = match (true) {
                $latencyMs > 500 => HealthStatus::Yellow,
                default          => HealthStatus::Green,
            };

            return new HealthReport(
                findings: [
                    new HealthFinding('objectstorage.connectivity', HealthStatus::Green, 'Object storage connection successful'),
                    new HealthFinding('objectstorage.latency', $latencyStatus, sprintf('Latency: %dms', $latencyMs)),
                ],
                overall: $latencyStatus,
            );
        } catch (Throwable $e) {
            return new HealthReport(
                findings: [new HealthFinding('objectstorage.connectivity', HealthStatus::Red, sprintf('Connection failed: %s', $e->getMessage()))],
                overall : HealthStatus::Red,
            );
        }
    }
}
