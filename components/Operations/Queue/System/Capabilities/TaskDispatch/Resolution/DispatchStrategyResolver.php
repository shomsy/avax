<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Resolution;

final readonly class DispatchStrategyResolver
{
    public function __construct(private string $defaultStrategy = 'sync')
    {
    }

    public function resolve() : string
    {
        $config = $this->loadTaskConfig();
        if ($config['strategy'] ?? '') {
            return $config['strategy'];
        }

        return $this->defaultStrategy;
    }

    private function loadTaskConfig() : array
    {
        return [];
    }
}
