<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga;

use Closure;

final readonly class SagaStep
{
    public Closure $action;

    public ?Closure $compensation;

    public function __construct(public string $name, callable $action, ?callable $compensation = null)
    {
        $this->action       = Closure::fromCallable($action);
        $this->compensation = $compensation === null ? null : Closure::fromCallable($compensation);
    }
}
