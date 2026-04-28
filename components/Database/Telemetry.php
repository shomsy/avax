<?php

declare(strict_types=1);

namespace Avax\Components\Database;

use Avax\Components\Database\System\Capabilities\Telemetry\Config\Config;
use Avax\Components\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Components\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Avax\Components\Database\System\Capabilities\Telemetry\Telemetry as TelemetryCapability;

final readonly class Telemetry
{
    public function __construct(private TelemetryCapability $telemetry) {}

    public function eventBus() : EventBus
    {
        return $this->telemetry->eventBus();
    }

    public function config() : Config|null
    {
        return $this->telemetry->config();
    }

    public function scope() : ExecutionScope
    {
        return $this->telemetry->scope();
    }
}
