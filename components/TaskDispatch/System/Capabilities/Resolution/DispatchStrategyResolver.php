<?php

declare(strict_types=1);

namespace Avax\Components\TaskDispatch\System\Capabilities\Resolution;

final class DispatchStrategyResolver
{
    private string $defaultStrategy;

    public function __construct(string $defaultStrategy = 'sync')
    {
        $this->defaultStrategy = $defaultStrategy;
    }

    public function resolve(object $task) : string
    {
        $class  = $task::class;
        $config = $this->loadTaskConfig($class);

        if ($config['strategy'] ?? '') {
            return $config['strategy'];
        }

        return $this->defaultStrategy;
    }

    private function loadTaskConfig(string $class) : array
    {
        return [];
    }
}