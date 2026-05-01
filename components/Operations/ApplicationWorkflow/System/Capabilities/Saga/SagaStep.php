<?php
declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga;

use Closure;

final readonly class SagaStep
{
    public string $name;

    public Closure $action;

    public Closure|null $compensation;

    public function __construct(string $name, callable $action, callable|null $compensation = null)
    {
        $this->name         = $name;
        $this->action       = Closure::fromCallable($action);
        $this->compensation = $compensation === null ? null : Closure::fromCallable($compensation);
    }
}
