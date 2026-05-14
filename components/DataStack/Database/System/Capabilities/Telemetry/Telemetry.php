<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Config\Config;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\QueryEvents\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;

/**
 * Public capability owner for database telemetry and correlation state.
 */
final readonly class Telemetry
{
    public function __construct(
        private EventBus       $eventBus,
        private Config|null    $config = null,
        private ExecutionScope|null $executionScope = null,
    ) {
    }

    public function eventBus(): EventBus
    {
        return $this->eventBus;
    }

    public function config() : Config|null
    {
        return $this->config;
    }

    public function scope(): ExecutionScope|null
    {
        return $this->executionScope;
    }
}
