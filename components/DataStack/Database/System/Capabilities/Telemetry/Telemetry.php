<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Config\Config;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Support\ExecutionScope;

/**
 * Public capability owner for database telemetry and correlation state.
 */
final readonly class Telemetry
{
    public function __construct(
        private EventBus $eventBus,
        private ?Config $config = null,
        private ExecutionScope $scope = new ExecutionScope(correlationId: 'database'),
    ) {}

    public function eventBus(): EventBus
    {
        return $this->eventBus;
    }

    public function config(): ?Config
    {
        return $this->config;
    }

    public function scope(): ExecutionScope
    {
        return $this->scope;
    }
}
