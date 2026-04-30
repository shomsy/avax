<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState;

use Closure;
use Throwable;

/**
 * Represents a single step in a saga with action and compensation logic.
 */
final class SagaStep
{
    public function __construct(
        public string   $name,
        public Closure  $action,
        public ?Closure $compensation = null,
    ) {}

    /**
     * Execute the step's action with the given context.
     *
     * @param mixed $context The saga context/data
     *
     * @return mixed The result of the action
     * @throws Throwable If the action fails
     */
    public function execute(mixed $context) : mixed
    {
        return ($this->action)($context);
    }

    /**
     * Execute the step's compensation logic with the given context.
     *
     * @param mixed $context The saga context/data
     *
     * @return mixed The result of the compensation
     * @throws Throwable If the compensation fails
     */
    public function compensate(mixed $context) : mixed
    {
        if ($this->compensation === null) {
            return null;
        }

        return ($this->compensation)($context);
    }

    /**
     * Check if this step has a compensation handler.
     */
    public function hasCompensation() : bool
    {
        return $this->compensation !== null;
    }
}
