<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga;

final class SagaDefinition
{
    private array $steps = [];

    public function step(string $name, callable $action, callable $compensation = null) : self
    {
        $this->steps[] = new SagaStep($name, $action, $compensation);

        return $this;
    }

    public function getSteps() : array
    {
        return $this->steps;
    }
}
